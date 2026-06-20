<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Initiate Snap Token untuk payment
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:dp,full,settlement',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $user = $order->user;

            // Hitung amount berdasarkan payment method
            $amount = match ($validated['payment_method']) {
                'dp' => (int) ($order->subtotal * 0.5),
                'full' => (int) $order->subtotal,
                'settlement' => (int) ($order->remaining_amount ?? 0),
            };

            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran tidak valid',
                ], 422);
            }

            // Generate unique order ID untuk Midtrans
            $midtransOrderId = 'ORDER-' . $order->id . '-' . time();

            // Persiapkan data transaksi
            $transactionDetails = [
                'order_id' => $midtransOrderId,
                'gross_amount' => $amount,
            ];

            $customerDetails = [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ];

            $itemDetails = [
                [
                    'id' => 'item-' . $order->id,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => $order->product_name,
                ]
            ];

            $params = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
                'credit_card' => [
                    'secure' => true,
                ],
            ];

            $enabledPayments = config('midtrans.enabled_payments', []);
            if (is_array($enabledPayments) && count($enabledPayments) > 0) {
                $params['enabled_payments'] = $enabledPayments;
            }

            // Generate Snap token
            $snapToken = Snap::getSnapToken($params);

            // Simpan payment record dengan status pending
            $payment = Payment::firstOrNew(
                ['order_id' => $order->id, 'method' => $validated['payment_method']],
                [
                    'amount' => $amount,
                    'status' => 'pending',
                ]
            );

            $payment->update([
                'midtrans_order_id' => $midtransOrderId,
                'amount' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Midtrans initiate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Snap Redirect URL untuk payment page Midtrans
     */
    public function createRedirectPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:dp,full,settlement',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $user = $order->user;

            $amount = match ($validated['payment_method']) {
                'dp' => (int) ($order->subtotal * 0.5),
                'full' => (int) $order->subtotal,
                'settlement' => (int) ($order->remaining_amount ?? 0),
            };

            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran tidak valid',
                ], 422);
            }

            $midtransOrderId = 'ORDER-' . $order->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                ],
                'item_details' => [
                    [
                        'id' => 'item-' . $order->id,
                        'price' => $amount,
                        'quantity' => 1,
                        'name' => $order->product_name,
                    ],
                ],
                'credit_card' => [
                    'secure' => true,
                ],
            ];

            $enabledPayments = config('midtrans.enabled_payments', []);
            if (is_array($enabledPayments) && count($enabledPayments) > 0) {
                $params['enabled_payments'] = $enabledPayments;
            }

            $redirectUrl = Snap::createTransaction($params)->redirect_url;

            $payment = Payment::firstOrNew(
                ['order_id' => $order->id, 'method' => $validated['payment_method']],
                [
                    'amount' => $amount,
                    'status' => 'pending',
                ]
            );

            $payment->update([
                'midtrans_order_id' => $midtransOrderId,
                'amount' => $amount,
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Midtrans redirect error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat redirect pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek status transaksi dari Midtrans
     */
    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);

        if (!$payment->midtrans_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi Midtrans tidak ditemukan',
            ], 404);
        }

        try {
            // Ambil status transaksi dari Midtrans
            $status = Transaction::status($payment->midtrans_order_id);

            // Update payment record dengan info terbaru dari Midtrans
            $payment->update([
                'midtrans_transaction_id' => $status->transaction_id ?? null,
                'midtrans_status' => $status->transaction_status,
                'midtrans_payment_type' => $status->payment_type ?? null,
                'midtrans_fraud_status' => $status->fraud_status ?? null,
                'midtrans_response' => (array) $status,
            ]);

            // Sinkronkan status internal setelah status Midtrans terbaru didapat.
            $this->updatePaymentStatus($payment, $status->transaction_status);
            $payment->refresh();

            // Map Midtrans status ke status internal
            $internalStatus = $this->mapMidtransStatusToInternal($status->transaction_status);

            return response()->json([
                'success' => true,
                'transaction_status' => $status->transaction_status,
                'payment_type' => $status->payment_type ?? null,
                'fraud_status' => $status->fraud_status ?? null,
                'internal_status' => $internalStatus,
                'payment' => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'status' => $internalStatus,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal cek status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notification handler dari Midtrans (webhook)
     * Akan diaktifkan nanti saat pakai tunnel
     */
    public function notification(Request $request)
    {
        $json = $request->getContent();
        $notification = json_decode($json);

        try {
            // Verifikasi signature
            $serverKey = config('midtrans.server_key');
            $orderId = $notification->order_id;
            $statusCode = $notification->status_code;
            $grossAmount = $notification->gross_amount;
            $serverSignature = $notification->signature_key;

            $mySignature = openssl_digest($orderId . $statusCode . $grossAmount . $serverKey, 'sha512');

            if ($serverSignature !== $mySignature) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $transactionStatus = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;

            // Cari payment berdasarkan midtrans_order_id
            $payment = Payment::where('midtrans_order_id', $orderId)->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Update payment dengan informasi dari notification
            $payment->update([
                'midtrans_transaction_id' => $notification->transaction_id,
                'midtrans_status' => $transactionStatus,
                'midtrans_payment_type' => $type,
                'midtrans_fraud_status' => $notification->fraud_status ?? null,
                'midtrans_response' => (array) $notification,
            ]);

            // Update status internal berdasarkan Midtrans status
            $this->updatePaymentStatus($payment, $transactionStatus);

            return response()->json(['message' => 'Notification processed']);
        } catch (\Exception $e) {
            \Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    /**
     * Map Midtrans transaction status ke status internal
     */
    private function mapMidtransStatusToInternal(string $midtransStatus): string
    {
        return match ($midtransStatus) {
            'settlement', 'capture' => 'verified',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'rejected',
            default => 'pending',
        };
    }

    /**
     * Update payment status berdasarkan Midtrans status
     */
    private function updatePaymentStatus(Payment $payment, string $transactionStatus): void
    {
        $internalStatus = $this->mapMidtransStatusToInternal($transactionStatus);

        if ($internalStatus === 'verified' && $payment->status !== 'verified') {
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'invoice_number' => $payment->invoice_number ?: $this->generateInvoiceNumber($payment),
                'invoiced_at' => $payment->invoiced_at ?: now(),
            ]);

            // Update order payment_status
            $order = $payment->order;
            if ($payment->method === 'dp') {
                $order->update([
                    'payment_status' => 'verified_dp',
                    'order_status' => 'verified_payment',
                ]);

                $this->ensureWorkOrderAndSteps($order);
            } elseif ($payment->method === 'full' || $payment->method === 'settlement') {
                if ($payment->method === 'settlement') {
                    $order->update([
                        'remaining_amount' => 0,
                        'payment_status' => 'fully_paid',
                        'order_status' => 'in_production',
                    ]);
                } else {
                    $order->update([
                        'remaining_amount' => 0,
                        'payment_status' => 'fully_paid',
                        'order_status' => 'verified_payment',
                    ]);
                    $this->ensureWorkOrderAndSteps($order);
                }
            }
        } elseif ($internalStatus === 'rejected' && $payment->status !== 'rejected') {
            $payment->update(['status' => 'rejected']);
        }
    }

    private function ensureWorkOrderAndSteps(Order $order): void
    {
        if (! $order->workOrder()->exists()) {
            $order->workOrder()->create([
                'spk_number' => 'SPK-' . now()->format('m-Y') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'issued_by' => $order->user_id,
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
