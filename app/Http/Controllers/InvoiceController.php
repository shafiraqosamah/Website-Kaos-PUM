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
        ]);
    }
}
