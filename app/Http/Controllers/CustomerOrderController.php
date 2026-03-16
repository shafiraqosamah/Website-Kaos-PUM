<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    private const SIZES = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

    public function index(Request $request): View
    {
        $orders = Order::with(['sizes', 'payments', 'productionSteps'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('customer.orders.create', ['sizes' => self::SIZES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'total_pcs' => ['required', 'integer', 'min:60'],
            'fabric' => ['required', 'string', 'max:150'],
            'dominant_color' => ['required', 'string', 'max:80'],
            'unit_price' => ['required', 'numeric', 'min:1000'],
            'estimated_finish_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_type' => ['required', 'in:dp,full'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'design_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,svg', 'max:4096'],
            'sizes' => ['required', 'array'],
        ]);

        $cleanSizes = [];
        $sum = 0;

        foreach (self::SIZES as $size) {
            $qty = (int) ($request->input('sizes.' . $size) ?? 0);
            if ($qty > 0) {
                $cleanSizes[$size] = $qty;
                $sum += $qty;
            }
        }

        if ($sum !== (int) $validated['total_pcs']) {
            return back()->withErrors([
                'sizes' => 'Total ukuran harus sama dengan total pcs pesanan.',
            ])->withInput();
        }

        $subtotal = (float) $validated['unit_price'] * (int) $validated['total_pcs'];
        $isDp = $validated['payment_type'] === 'dp';
        $dpAmount = $isDp ? $subtotal * 0.5 : $subtotal;
        $remainingAmount = $subtotal - $dpAmount;
        $designPath = $request->hasFile('design_file')
            ? $request->file('design_file')->store('designs', 'public')
            : null;

        $createdOrder = null;
        $createdPayment = null;

        DB::transaction(function () use ($request, $validated, $cleanSizes, $designPath, $subtotal, $dpAmount, $remainingAmount, $isDp, &$createdOrder, &$createdPayment): void {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'customer_name' => $validated['customer_name'],
                'product_name' => 'Kaos Custom',
                'total_pcs' => (int) $validated['total_pcs'],
                'fabric' => $validated['fabric'],
                'dominant_color' => $validated['dominant_color'],
                'design_path' => $designPath,
                'estimated_finish_date' => $validated['estimated_finish_date'],
                'unit_price' => $validated['unit_price'],
                'subtotal' => $subtotal,
                'payment_type' => $validated['payment_type'],
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => 'pending_verification',
                'order_status' => 'submitted',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cleanSizes as $sizeName => $qty) {
                $order->sizes()->create([
                    'size_name' => $sizeName,
                    'qty' => $qty,
                ]);
            }

            $payment = $order->payments()->create([
                'method' => $isDp ? 'dp' : 'full',
                'amount' => $dpAmount,
                'status' => 'pending',
                'notes' => $isDp ? 'Pembayaran DP awal 50%' : 'Pembayaran lunas saat pemesanan',
            ]);

            $createdOrder = $order;
            $createdPayment = $payment;
        });

        return redirect()
            ->route('customer.orders.payments.edit', [$createdOrder, $createdPayment])
            ->with('success', 'Pesanan custom berhasil dibuat. Lengkapi data pembayaran awal Anda.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['sizes', 'payments.verifiedBy', 'workOrder', 'productionSteps']);

        return view('customer.orders.show', compact('order'));
    }

    public function editPayment(Request $request, Order $order, Payment $payment): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($payment->order_id === $order->id, 404);

        return view('customer.orders.payment', [
            'order' => $order,
            'payment' => $payment,
            'banks' => Payment::destinationBanks(),
        ]);
    }

    public function updatePayment(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($payment->order_id === $order->id, 404);

        if ($payment->status === 'verified') {
            return back()->withErrors(['payment' => 'Pembayaran yang sudah diverifikasi tidak dapat diubah.']);
        }

        $validated = $request->validate([
            'destination_bank' => ['required', 'in:' . implode(',', array_keys(Payment::destinationBanks()))],
            'sender_bank_name' => ['required', 'string', 'max:120'],
            'sender_account_name' => ['required', 'string', 'max:120'],
            'payment_proof' => [$payment->proof_path ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofPath = $payment->proof_path;

        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
        }

        $payment->update([
            'destination_bank' => $validated['destination_bank'],
            'sender_bank_name' => $validated['sender_bank_name'],
            'sender_account_name' => $validated['sender_account_name'],
            'proof_path' => $proofPath,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? $payment->notes,
        ]);

        return redirect()->route('customer.orders.show', $order)->with('success', 'Data pembayaran berhasil dikirim dan menunggu verifikasi keuangan.');
    }

    public function requestSettlement(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->remaining_amount <= 0) {
            return back()->with('success', 'Pesanan ini sudah tidak memiliki sisa pembayaran.');
        }

        $pendingSettlement = Payment::where('order_id', $order->id)
            ->where('method', 'settlement')
            ->where('status', 'pending')
            ->exists();

        if ($pendingSettlement) {
            return back()->withErrors(['payment' => 'Permintaan pelunasan sedang menunggu verifikasi.']);
        }

        $payment = $order->payments()->create([
            'method' => 'settlement',
            'amount' => $order->remaining_amount,
            'status' => 'pending',
            'notes' => 'Permintaan pelunasan dari pelanggan.',
        ]);

        return redirect()
            ->route('customer.orders.payments.edit', [$order, $payment])
            ->with('success', 'Silakan isi data pelunasan dan upload bukti transfer Anda.');
    }
}
