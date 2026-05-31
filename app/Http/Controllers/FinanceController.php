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
            ->whereNotNull('midtrans_order_id')
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
            ->whereNotNull('midtrans_order_id')
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
            ->whereNotNull('midtrans_order_id')
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
