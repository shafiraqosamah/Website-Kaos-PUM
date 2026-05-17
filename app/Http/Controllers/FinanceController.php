<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Mail\PaymentRejectedMail;
use App\Mail\OrderFullyPaidMail;
use Midtrans\Config;
use Midtrans\Transaction;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->syncMidtransPayments();

        $pendingPayments = Payment::with('order.user')
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query
                    // Manual transfer entries.
                    ->where(function ($manual): void {
                        $manual
                            ->whereNotNull('proof_path')
                            ->whereNotNull('destination_bank')
                            ->whereNotNull('sender_bank_name')
                            ->whereNotNull('sender_account_name');
                    })
                    // Midtrans entries: no manual proof fields required.
                    ->orWhereNotNull('midtrans_order_id');
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->get();

        $verifiedPayments = Payment::with('order.user')
            ->where('status', 'verified')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%");
                });
            })
            ->latest('verified_at')
            ->take(20)
            ->get();

        $rejectedPayments = Payment::with('order.user')
            ->where('status', 'rejected')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('order', function ($q) use ($request) {
                    $q->where('order_code', 'like', "%{$request->search}%")
                      ->orWhere('customer_name', 'like', "%{$request->search}%");
                });
            })
            ->latest('verified_at')
            ->take(50)
            ->get();

        $rejectReasons = collect($this->rejectReasonCatalog())
            ->map(static fn (array $reason, string $code): array => [
                'code' => $code,
                'label' => $reason['label'],
                'customer_action' => $reason['customer_action'],
            ])
            ->values();

        return view('finance.index', compact('pendingPayments', 'verifiedPayments', 'rejectedPayments', 'rejectReasons'));
    }

    private function syncMidtransPayments(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $midtransPayments = Payment::with('order')
            ->whereNotNull('midtrans_order_id')
            ->whereIn('status', ['pending', 'rejected'])
            ->latest()
            ->limit(60)
            ->get();

        foreach ($midtransPayments as $payment) {
            try {
                $status = Transaction::status($payment->midtrans_order_id);

                $payment->update([
                    'midtrans_transaction_id' => $status->transaction_id ?? $payment->midtrans_transaction_id,
                    'midtrans_status' => $status->transaction_status ?? $payment->midtrans_status,
                    'midtrans_payment_type' => $status->payment_type ?? $payment->midtrans_payment_type,
                    'midtrans_fraud_status' => $status->fraud_status ?? $payment->midtrans_fraud_status,
                    'midtrans_response' => (array) $status,
                ]);

                $this->applyMidtransState($payment, (string) ($status->transaction_status ?? 'pending'));
            } catch (\Throwable $e) {
                // Keep finance page resilient when Midtrans API has temporary issues.
                report($e);
            }
        }
    }

    private function applyMidtransState(Payment $payment, string $midtransStatus): void
    {
        $order = $payment->order;

        if (in_array($midtransStatus, ['settlement', 'capture'], true)) {
            if ($payment->status !== 'verified') {
                $payment->update([
                    'status' => 'verified',
                    'invoice_number' => $payment->invoice_number ?: $this->generateInvoiceNumber($payment),
                    'invoiced_at' => $payment->invoiced_at ?: now(),
                    'verified_at' => now(),
                ]);
            }

            if ($payment->method === 'settlement') {
                $order->update([
                    'remaining_amount' => 0,
                    'payment_status' => 'fully_paid',
                    'order_status' => 'in_production',
                ]);

                return;
            }

            if ($payment->method === 'full') {
                $order->update([
                    'payment_status' => 'fully_paid',
                    'remaining_amount' => 0,
                    'order_status' => 'verified_payment',
                ]);
            } else {
                $order->update([
                    'payment_status' => 'verified_dp',
                    'order_status' => 'verified_payment',
                ]);
            }

            $this->ensureWorkOrderAndSteps($order, $order->user_id);

            return;
        }

        if (in_array($midtransStatus, ['deny', 'cancel', 'expire'], true)) {
            if ($payment->status !== 'rejected') {
                $payment->update([
                    'status' => 'rejected',
                    'verified_at' => now(),
                ]);
            }
        }
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:verify,reject'],
            'reject_reason' => ['nullable', 'required_if:action,reject', 'in:' . implode(',', array_keys($this->rejectReasonCatalog()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($payment->status !== 'pending') {
            return back()->withErrors(['payment' => 'Pembayaran sudah diproses sebelumnya.']);
        }

        if (! $payment->proof_path || ! $payment->destination_bank || ! $payment->sender_bank_name || ! $payment->sender_account_name) {
            return back()->withErrors(['payment' => 'Data pembayaran belum lengkap dan belum bisa diverifikasi.']);
        }

        DB::transaction(function () use ($request, $payment, $validated): void {
            $order = $payment->order()->lockForUpdate()->firstOrFail();

            $baseNotes = $this->withoutFinanceNotes($payment->notes);
            $financeNote = $validated['notes'] ?? null;

            if ($validated['action'] === 'reject') {
                $reasonCode = (string) ($validated['reject_reason'] ?? 'other');
                $reasonData = $this->rejectReasonCatalog()[$reasonCode] ?? $this->rejectReasonCatalog()['other'];
                $reasonLabel = $reasonData['label'];
                $customerAction = $reasonData['customer_action'];

                $noteLines = [];
                if ($baseNotes) {
                    $noteLines[] = $baseNotes;
                }
                $noteLines[] = 'Alasan penolakan kode: ' . $reasonCode;
                $noteLines[] = 'Alasan penolakan: ' . $reasonLabel;
                $noteLines[] = 'Tindakan customer: ' . $customerAction;
                if ($financeNote) {
                    $noteLines[] = 'Catatan keuangan: ' . $financeNote;
                }

                $mergedNotes = trim(implode(PHP_EOL, $noteLines));

                $payment->update([
                    'status' => 'rejected',
                    'verified_by' => $request->user()->id,
                    'verified_at' => now(),
                    'notes' => $mergedNotes,
                ]);
                
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new PaymentRejectedMail($order, $payment));
                }

                return;
            }

            $verifiedNotes = $financeNote
                ? trim(($baseNotes ? $baseNotes . PHP_EOL : '') . 'Catatan keuangan: ' . $financeNote)
                : ($baseNotes ?: 'Diverifikasi bagian keuangan.');

            $payment->update([
                'status' => 'verified',
                'invoice_number' => $payment->invoice_number ?: $this->generateInvoiceNumber($payment),
                'invoiced_at' => $payment->invoiced_at ?: now(),
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => $verifiedNotes,
            ]);

            if ($payment->method === 'settlement') {
                $order->update([
                    'remaining_amount' => 0,
                    'payment_status' => 'fully_paid',
                    'order_status' => 'in_production',
                ]);
                
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new OrderFullyPaidMail($order, $payment));
                }

                return;
            }

            if ($payment->method === 'full') {
                $order->update([
                    'payment_status' => 'fully_paid',
                    'remaining_amount' => 0,
                    'order_status' => 'verified_payment',
                ]);
                
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new OrderFullyPaidMail($order, $payment));
                }
            } else {
                $order->update([
                    'payment_status' => 'verified_dp',
                    'order_status' => 'verified_payment',
                ]);
            }

            $this->ensureWorkOrderAndSteps($order, $request->user()->id);
        });

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    public function viewProof(Payment $payment): StreamedResponse
    {
        abort_unless((bool) $payment->proof_path, 404, 'Bukti pembayaran tidak tersedia.');

        $disk = Storage::disk('public');
        abort_unless($disk->exists($payment->proof_path), 404, 'File bukti pembayaran tidak ditemukan.');

        return $disk->response($payment->proof_path);
    }

    private function ensureWorkOrderAndSteps(Order $order, int $issuerId): void
    {
        if (! $order->workOrder()->exists()) {
            $order->workOrder()->create([
                'spk_number' => 'SPK-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'issued_by' => $issuerId,
                'issued_at' => now(),
                'status' => 'open',
            ]);
        }

        if (! $order->productionSteps()->exists()) {
            $steps = ['Cutting', 'Jahit', 'Sablon', 'Steam', 'Finishing'];

            foreach ($steps as $index => $stepName) {
                $order->productionSteps()->create([
                    'step_order' => $index + 1,
                    'step_name' => $stepName,
                    'status' => 'pending',
                ]);
            }
        }
    }

    private function generateInvoiceNumber(Payment $payment): string
    {
        return 'INV/PUM/' . now()->format('m/Y') . '/' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) . '-' . Str::upper($payment->method);
    }

    private function withoutFinanceNotes(?string $notes): ?string
    {
        if (! $notes) {
            return null;
        }

        $lines = preg_split('/\R/', $notes) ?: [];
        $filtered = array_values(array_filter($lines, static function (string $line): bool {
            $normalized = Str::lower(trim($line));

            return ! Str::startsWith($normalized, 'catatan keuangan:')
                && ! Str::startsWith($normalized, 'alasan penolakan kode:')
                && ! Str::startsWith($normalized, 'alasan penolakan:')
                && ! Str::startsWith($normalized, 'tindakan customer:');
        }));

        $cleaned = trim(implode(PHP_EOL, $filtered));

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * @return array<string, array{label: string, customer_action: string}>
     */
    private function rejectReasonCatalog(): array
    {
        return [
            'amount_mismatch' => [
                'label' => 'Nominal tidak sesuai',
                'customer_action' => 'Sesuaikan nominal transfer sesuai tagihan, lalu upload ulang bukti pembayaran.',
            ],
            'proof_unreadable' => [
                'label' => 'Bukti blur/tidak terbaca',
                'customer_action' => 'Upload ulang bukti pembayaran dengan gambar/file yang lebih jelas.',
            ],
            'wrong_destination' => [
                'label' => 'Transfer ke rekening tujuan yang salah',
                'customer_action' => 'Lakukan transfer ke rekening tujuan yang benar lalu upload bukti terbaru.',
            ],
            'identity_mismatch' => [
                'label' => 'Data pengirim tidak sesuai',
                'customer_action' => 'Perbaiki data bank/nama pengirim lalu kirim ulang bukti pembayaran.',
            ],
            'duplicate_proof' => [
                'label' => 'Bukti duplikat/tidak sesuai transaksi',
                'customer_action' => 'Upload bukti transaksi yang benar dan belum pernah digunakan.',
            ],
            'other' => [
                'label' => 'Alasan lain (lihat catatan keuangan)',
                'customer_action' => 'Ikuti instruksi pada catatan keuangan, lalu kirim ulang bukti pembayaran.',
            ],
        ];
    }
}
