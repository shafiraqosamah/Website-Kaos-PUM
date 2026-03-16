<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(): View
    {
        $pendingPayments = Payment::with('order.user')
            ->where('status', 'pending')
            ->whereNotNull('proof_path')
            ->latest()
            ->get();

        $verifiedPayments = Payment::with('order.user')
            ->where('status', 'verified')
            ->latest('verified_at')
            ->take(20)
            ->get();

        return view('finance.index', compact('pendingPayments', 'verifiedPayments'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:verify,reject'],
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

            $notes = $payment->notes;
            $financeNote = $validated['notes'] ?? null;
            $mergedNotes = $financeNote
                ? trim(($notes ? $notes . PHP_EOL : '') . 'Catatan keuangan: ' . $financeNote)
                : $notes;

            if ($validated['action'] === 'reject') {
                $payment->update([
                    'status' => 'rejected',
                    'verified_by' => $request->user()->id,
                    'verified_at' => now(),
                    'notes' => $mergedNotes ?: 'Ditolak bagian keuangan.',
                ]);

                return;
            }

            $payment->update([
                'status' => 'verified',
                'invoice_number' => $payment->invoice_number ?: $this->generateInvoiceNumber($payment),
                'invoiced_at' => $payment->invoiced_at ?: now(),
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => $mergedNotes ?: 'Diverifikasi bagian keuangan.',
            ]);

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

            $this->ensureWorkOrderAndSteps($order, $request->user()->id);
        });

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
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
}
