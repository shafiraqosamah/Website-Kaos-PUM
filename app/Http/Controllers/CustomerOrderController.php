<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerOrderController extends Controller
{
    private const SIZES = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
    private const MATERIALS = [
        'Drill',
        'Taipan',
        'Tropical',
        'Oxford',
        'Twill',
        'Ribstop',
        'Lacoste Pique',
        'Cotton Combed 30s',
        'Cotton Combed 20s',
        'Drifit',
        'Lainnya',
    ];

    private const TYPES = ['Sablon Manual', 'DTF (Direct to Film)', 'Bordiran', 'Printing'];
    private const DESIGN_POSITIONS = ['Dada Kiri + Punggung', 'Dada Kiri Saja', 'Punggung Saja', 'Full Depan', 'Full Belakang', 'Lainnya'];
    private const MODELS = ['Polo Shirt', 'Kaos Oblong', 'Kaos Panjang', 'Raglan', 'T-Shirt'];
    private const SLEEVES = ['Lengan Pendek', 'Lengan Panjang'];

    public function index(Request $request): View
    {
        $orders = Order::with(['sizes', 'payments', 'productionSteps'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        $catalogPreset = [
            'fabric' => $request->query('fabric'),
            'production_type' => $request->query('production_type'),
            'design_position' => $request->query('design_position'),
            'product_model' => $request->query('product_model'),
            'dominant_color' => $request->query('dominant_color'),
            'unit_price' => $request->query('unit_price'),
            'total_pcs' => $request->query('total_pcs'),
            'production_qty' => $request->query('production_qty'),
        ];

        $catalogPreset = array_filter($catalogPreset, static fn ($value) => $value !== null && $value !== '');

        return view('customer.orders.create', [
            'sizes' => self::SIZES,
            'materials' => self::MATERIALS,
            'types' => self::TYPES,
            'designPositions' => self::DESIGN_POSITIONS,
            'models' => self::MODELS,
            'sleeves' => self::SLEEVES,
            'catalogPreset' => $catalogPreset,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $minimumFinishDate = now()->addDays(10)->toDateString();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'total_pcs' => ['required', 'integer', 'min:60'],
            'fabric' => ['required', 'in:' . implode(',', self::MATERIALS)],
            'other_fabric' => ['nullable', 'string', 'max:150', 'required_if:fabric,Lainnya'],
            'production_type' => ['required', 'in:' . implode(',', self::TYPES)],
            'production_qty' => ['required', 'integer', 'min:1'],
            'design_position' => ['required', 'string', 'max:150'],
            'design_position_other' => ['nullable', 'string', 'max:150', 'required_if:design_position,Lainnya'],
            'product_model' => ['required', 'in:' . implode(',', self::MODELS)],
            'sleeve_type' => ['required', 'in:' . implode(',', self::SLEEVES)],
            'dominant_color' => ['required', 'string', 'max:80'],
            'unit_price' => ['required', 'numeric', 'min:85000', 'max:200000'],
            'estimated_finish_date' => ['required', 'date', 'after_or_equal:' . $minimumFinishDate],
            'payment_type' => ['required', 'in:dp,full'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'design_front_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,svg', 'max:4096', 'required_without:design_back_file'],
            'design_back_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,svg', 'max:4096', 'required_without:design_front_file'],
            'design_notes' => ['nullable', 'string', 'max:1000'],
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

        if ((int) $validated['production_qty'] > (int) $validated['total_pcs']) {
            return back()->withErrors([
                'production_qty' => 'Jumlah proses tidak boleh melebihi total pcs.',
            ])->withInput();
        }

        $resolvedFabric = $validated['fabric'] === 'Lainnya'
            ? (string) ($validated['other_fabric'] ?? '')
            : $validated['fabric'];

        $resolvedDesignPosition = $validated['design_position'] === 'Lainnya'
            ? (string) ($validated['design_position_other'] ?? '')
            : $validated['design_position'];

        $resolvedDesignNotes = trim(implode("\n", array_filter([
            'Posisi Desain: ' . $resolvedDesignPosition,
            $validated['design_notes'] ?? null,
        ])));

        $plusSizeQty = (int) ($cleanSizes['XXL'] ?? 0) + (int) ($cleanSizes['XXXL'] ?? 0);
        $sizeSurcharge = $plusSizeQty * 5000;

        $subtotal = ((float) $validated['unit_price'] * (int) $validated['total_pcs']) + $sizeSurcharge;
        $isDp = $validated['payment_type'] === 'dp';
        $dpAmount = $isDp ? $subtotal * 0.5 : $subtotal;
        $remainingAmount = $subtotal - $dpAmount;
        $designFrontPath = $request->hasFile('design_front_file')
            ? $request->file('design_front_file')->store('designs', 'public')
            : null;

        $designBackPath = $request->hasFile('design_back_file')
            ? $request->file('design_back_file')->store('designs', 'public')
            : null;

        $createdOrder = null;
        $createdPayment = null;

        DB::transaction(function () use ($request, $validated, $cleanSizes, $resolvedFabric, $resolvedDesignPosition, $resolvedDesignNotes, $sizeSurcharge, $designFrontPath, $designBackPath, $subtotal, $dpAmount, $remainingAmount, $isDp, &$createdOrder, &$createdPayment): void {
            $detailNotes = [
                'Teknik Sablon: ' . $validated['production_type'],
                'Posisi Desain: ' . $resolvedDesignPosition,
                'Model: ' . $validated['product_model'],
                'Ukuran Lengan: ' . $validated['sleeve_type'],
            ];

            if ($sizeSurcharge > 0) {
                $detailNotes[] = 'Tambahan ukuran XXL/XXXL: Rp' . number_format($sizeSurcharge, 0, ',', '.');
            }

            if (!empty($validated['notes'])) {
                $detailNotes[] = 'Catatan pelanggan: ' . $validated['notes'];
            }

            if ($resolvedDesignNotes !== '') {
                $detailNotes[] = 'Catatan desain: ' . $resolvedDesignNotes;
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)),
                'customer_name' => $validated['customer_name'],
                'product_name' => 'Kaos Custom - ' . $validated['product_model'],
                'total_pcs' => (int) $validated['total_pcs'],
                'fabric' => $resolvedFabric,
                'production_type' => $validated['production_type'],
                'product_model' => $validated['product_model'],
                'sleeve_type' => $validated['sleeve_type'],
                'dominant_color' => $validated['dominant_color'],
                'design_file' => $designFrontPath,
                'design_front_file' => $designFrontPath,
                'design_back_file' => $designBackPath,
                'design_notes' => $resolvedDesignNotes !== '' ? $resolvedDesignNotes : null,
                'estimated_finish_date' => $validated['estimated_finish_date'],
                'unit_price' => $validated['unit_price'],
                'subtotal' => $subtotal,
                'payment_type' => $validated['payment_type'],
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => 'pending_verification',
                'order_status' => 'submitted',
                'notes' => implode("\n", $detailNotes),
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

        $order->loadMissing('sizes');

        return view('customer.orders.payment', [
            'order' => $order,
            'payment' => $payment,
            'banks' => Payment::destinationBanks(),
        ]);
    }

    public function viewPaymentProof(Request $request, Order $order, Payment $payment): StreamedResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($payment->order_id === $order->id, 404);
        abort_unless((bool) $payment->proof_path, 404, 'Bukti pembayaran tidak tersedia.');

        $disk = Storage::disk('public');
        abort_unless($disk->exists($payment->proof_path), 404, 'File bukti pembayaran tidak ditemukan.');

        return $disk->response($payment->proof_path);
    }

    public function updatePayment(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($payment->order_id === $order->id, 404);

        if ($payment->status === 'verified') {
            return back()->withErrors(['payment' => 'Pembayaran yang sudah diverifikasi tidak dapat diubah.']);
        }

        $rules = [
            'destination_bank' => ['required', 'in:' . implode(',', array_keys(Payment::destinationBanks()))],
            'sender_bank_name' => ['required', 'string', 'max:120'],
            'sender_account_name' => ['required', 'string', 'max:120'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($payment->method !== 'settlement') {
            $rules['payment_option'] = ['required', 'in:dp,full'];
        }

        $validated = $request->validate($rules);

        $proofPath = $payment->proof_path;

        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
        }

        $selectedMethod = $payment->method === 'settlement'
            ? 'settlement'
            : (string) ($validated['payment_option'] ?? $payment->method);

        $selectedAmount = match ($selectedMethod) {
            'full' => (float) $order->subtotal,
            'settlement' => (float) $order->remaining_amount,
            default => (float) $order->subtotal * 0.5,
        };

        $payment->update([
            'method' => $selectedMethod,
            'destination_bank' => $validated['destination_bank'],
            'sender_bank_name' => $validated['sender_bank_name'],
            'sender_account_name' => $validated['sender_account_name'],
            'proof_path' => $proofPath,
            'amount' => $selectedAmount,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? $payment->notes,
        ]);

        if ($selectedMethod !== 'settlement') {
            $order->update([
                'payment_type' => $selectedMethod,
                'dp_amount' => $selectedMethod === 'full' ? (float) $order->subtotal : (float) $order->subtotal * 0.5,
                'remaining_amount' => $selectedMethod === 'full' ? 0 : (float) $order->subtotal * 0.5,
                'payment_status' => 'pending_verification',
                'order_status' => 'submitted',
            ]);
        } else {
            $order->update([
                'payment_status' => 'pending_verification',
            ]);
        }

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
            ->latest('id')
            ->first();

        if ($pendingSettlement) {
            $isSettlementDataComplete = (bool) $pendingSettlement->proof_path
                && (bool) $pendingSettlement->destination_bank
                && (bool) $pendingSettlement->sender_bank_name
                && (bool) $pendingSettlement->sender_account_name;

            if (! $isSettlementDataComplete) {
                return redirect()
                    ->route('customer.orders.payments.edit', [$order, $pendingSettlement])
                    ->with('warning', 'Lengkapi data pelunasan dan upload bukti transfer agar bisa diverifikasi keuangan.');
            }

            return back()->withErrors(['payment' => 'Data pelunasan sudah dikirim dan sedang menunggu verifikasi keuangan.']);
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
