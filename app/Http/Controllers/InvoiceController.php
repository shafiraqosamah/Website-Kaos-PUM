<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function showForCustomer(Request $request, Order $order, Payment $payment): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($payment->order_id === $order->id, 404);
        abort_if($payment->status !== 'verified', 404);

        return $this->renderInvoice($payment);
    }

    public function showForFinance(Request $request, Payment $payment): View
    {
        abort_unless($request->user()->hasRole(User::ROLE_FINANCE, User::ROLE_ADMIN, User::ROLE_OWNER, User::ROLE_MANAGER), 403);
        abort_if($payment->status !== 'verified', 404);

        return $this->renderInvoice($payment);
    }

    private function renderInvoice(Payment $payment): View
    {
        $payment->loadMissing(['order.user', 'verifiedBy']);
        $order = $payment->order;

        [$payViaLabel, $payViaDetail] = $this->resolvePayVia($payment);

        return view('invoices.show', [
            'payment' => $payment,
            'order' => $order,
            'invoiceTitle' => $payment->method === 'settlement' ? 'INVOICE PELUNASAN' : 'INVOICE',
            'paymentLabel' => match ($payment->method) {
                'dp' => 'DP 50%',
                'full' => 'LUNAS',
                'settlement' => 'PELUNASAN',
                default => strtoupper($payment->method),
            },
            'payViaLabel' => $payViaLabel,
            'payViaDetail' => $payViaDetail,
        ]);
    }

    private function resolvePayVia(Payment $payment): array
    {
        $type = (string) ($payment->midtrans_payment_type ?? '');
        $response = is_array($payment->midtrans_response) ? $payment->midtrans_response : [];

        if ($type === '') {
            return ['Manual Transfer', null];
        }

        if ($type === 'bank_transfer') {
            $vaNumbers = $response['va_numbers'] ?? [];
            if (is_array($vaNumbers) && isset($vaNumbers[0]['bank'], $vaNumbers[0]['va_number'])) {
                $bank = strtoupper((string) $vaNumbers[0]['bank']);
                $va = (string) $vaNumbers[0]['va_number'];

                return ['VA ' . $bank, 'VA: ' . $va];
            }

            if (! empty($response['permata_va_number'])) {
                return ['VA Permata', 'VA: ' . (string) $response['permata_va_number']];
            }

            return ['Virtual Account', null];
        }

        if ($type === 'echannel') {
            $billKey = (string) ($response['bill_key'] ?? '');
            $billerCode = (string) ($response['biller_code'] ?? '');

            return ['Mandiri Bill Payment', trim('Bill Key: ' . $billKey . ($billerCode !== '' ? ' | Biller: ' . $billerCode : ''))];
        }

        if ($type === 'qris') {
            return ['QRIS', null];
        }

        if ($type === 'gopay') {
            return ['GoPay', null];
        }

        if ($type === 'shopeepay') {
            return ['ShopeePay', null];
        }

        if ($type === 'credit_card') {
            $issuer = (string) ($response['bank'] ?? 'Kartu');

            return ['Kartu Kredit/Debit', $issuer !== '' ? 'Issuer: ' . strtoupper($issuer) : null];
        }

        return [ucwords(str_replace('_', ' ', $type)), null];
    }
}
