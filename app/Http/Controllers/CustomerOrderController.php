<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Midtrans\Config;
use Midtrans\Transaction;
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
    private const TECHNIQUE_SURCHARGES = [
        'Sablon Manual' => 5000,
        'DTF (Direct to Film)' => 6000,
        'Bordiran' => 6000,
        'Printing' => 7000,
    ];
    private const MATERIAL_BASE_PRICES = [
        'Drill' => 115000,
        'Taipan' => 120000,
        'Tropical' => 110000,
        'Oxford' => 125000,
        'Twill' => 118000,
        'Ribstop' => 130000,
        'Lacoste Pique' => 140000,
        'Cotton Combed 30s' => 85000,
        'Cotton Combed 20s' => 95000,
        'Cotton Combed 24s' => 95000,
        'Cotton Combed 24a' => 95000,
        'Drifit' => 105000,
        'Lainnya' => 100000,
    ];

    public function index(Request $request): View
    {
        $this->syncMidtransPaymentsForCustomer((int) $request->user()->id);

        $orders = Order::with(['sizes', 'payments', 'productionSteps'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function create(Request $request): View
    {
        $sizes = $this->masterSizeOptions();
        $materials = $this->masterMaterialOptions();
        $types = $this->masterProductionTypeOptions();
        $designPositions = $this->masterDesignPositionOptions();
        $models = $this->masterProductModelOptions();
        $sleeves = $this->masterSleeveTypeOptions();

        $catalogPreset = [
            'fabric' => $request->query('fabric'),
            'production_type' => $request->query('production_type'),
            'design_position' => $request->query('design_position'),
            'product_model' => $request->query('product_model'),
            'sleeve_type' => $request->query('sleeve_type'),
            'dominant_color' => $request->query('dominant_color'),
            'unit_price' => $request->query('unit_price'),
            'total_pcs' => $request->query('total_pcs'),
            'production_qty' => $request->query('production_qty'),
            'notes' => $request->query('design_notes', $request->query('notes')),
        ];

        $catalogPreset = array_filter($catalogPreset, static fn ($value) => $value !== null && $value !== '');

        if (! empty($catalogPreset)) {
            if (isset($catalogPreset['fabric'])) {
                $rawFabric = trim((string) $catalogPreset['fabric']);
                $fabricMap = [
                    'lacoste' => 'Lacoste Pique',
                    'lacoste pique' => 'Lacoste Pique',
                    'laccoste' => 'Lacoste Pique',
                    'drifit' => 'Drifit',
                    'dryfit' => 'Drifit',
                ];

                $normalizedFabricKey = strtolower($rawFabric);
                $normalizedFabric = $fabricMap[$normalizedFabricKey] ?? $rawFabric;

                if (! in_array($normalizedFabric, $materials, true)) {
                    $catalogPreset['other_fabric'] = $rawFabric;
                    $catalogPreset['fabric'] = 'Lainnya';
                } else {
                    $catalogPreset['fabric'] = $normalizedFabric;
                }
            }

            if (isset($catalogPreset['production_type'])) {
                $rawType = strtolower(trim((string) $catalogPreset['production_type']));
                $typeMap = [
                    'sablon' => 'Sablon Manual',
                    'sablon manual' => 'Sablon Manual',
                    'dtf' => 'DTF (Direct to Film)',
                    'dtf (direct to film)' => 'DTF (Direct to Film)',
                    'bordir' => 'Bordiran',
                    'bordiran' => 'Bordiran',
                    'printing' => 'Printing',
                ];

                $catalogPreset['production_type'] = $typeMap[$rawType] ?? $catalogPreset['production_type'];
            }

            if (isset($catalogPreset['product_model'])) {
                $rawModel = strtolower(trim((string) $catalogPreset['product_model']));
                $modelMap = [
                    'kaos' => 'T-Shirt',
                    't-shirt' => 'T-Shirt',
                    'tshirt' => 'T-Shirt',
                    'poloshirt' => 'Polo Shirt',
                    'polo shirt' => 'Polo Shirt',
                    'polo-shirt' => 'Polo Shirt',
                ];

                $catalogPreset['product_model'] = $modelMap[$rawModel] ?? $catalogPreset['product_model'];
            }

            if (empty($catalogPreset['sleeve_type']) || ! in_array($catalogPreset['sleeve_type'], $sleeves, true)) {
                $catalogPreset['sleeve_type'] = 'Lengan Pendek';
            }
        }

        return view('customer.orders.create', [
            'sizes' => $sizes,
            'materials' => $materials,
            'materialPriceMap' => $this->masterMaterialBasePriceMap($materials),
            'techniqueSurchargeMap' => $this->masterProductionTypeSurchargeMap($types),
            'materialCatalog' => $this->materialCatalogData(),
            'types' => $types,
            'designPositions' => $designPositions,
            'models' => $models,
            'sleeves' => $sleeves,
            'catalogPreset' => $catalogPreset,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $sizes = $this->masterSizeOptions();
        $materials = $this->masterMaterialOptions();
        $types = $this->masterProductionTypeOptions();
        $models = $this->masterProductModelOptions();
        $sleeves = $this->masterSleeveTypeOptions();

        $minimumFinishDate = now()->addDays(10)->toDateString();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'total_pcs' => ['required', 'integer', 'min:60'],
            'fabric' => ['required', 'in:' . implode(',', $materials)],
            'other_fabric' => ['nullable', 'string', 'max:150', 'required_if:fabric,Lainnya'],
            'production_type' => ['required', 'in:' . implode(',', $types)],
            'production_qty' => ['required', 'integer', 'min:1'],
            'design_position' => ['required', 'string', 'max:150'],
            'design_position_other' => ['nullable', 'string', 'max:150', 'required_if:design_position,Lainnya'],
            'product_model' => ['required', 'in:' . implode(',', $models)],
            'sleeve_type' => ['required', 'in:' . implode(',', $sleeves)],
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

        foreach ($sizes as $size) {
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
            ->route('customer.orders.index')
            ->with('success', 'Pesanan custom berhasil dikirim. Silakan pantau status verifikasi admin di Riwayat Pesanan.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['sizes', 'payments.verifiedBy', 'workOrder', 'productionSteps']);

        return view('customer.orders.show', compact('order'));
    }

    public function approveRevision(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ((string) ($order->admin_verification_status ?? 'pending') !== 'revision_requested') {
            return back()->withErrors(['order' => 'Pesanan ini tidak sedang dalam status revisi.']);
        }

        $order->update([
            'admin_verification_status' => 'verified',
            'order_status' => 'submitted',
            'admin_verified_at' => now(),
        ]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Revisi disetujui. Silakan lanjutkan pembayaran.');
    }

    public function editPayment(Request $request, Order $order, Payment $payment): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($payment->order_id === $order->id, 404);

        if ($payment->method !== 'settlement' && $order->admin_verification_status !== 'verified') {
            return redirect()
                ->route('customer.orders.index')
                ->withErrors(['payment' => 'Pesanan Anda masih menunggu verifikasi admin sebelum melanjutkan pembayaran.']);
        }

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

        if ($payment->method !== 'settlement' && $order->admin_verification_status !== 'verified') {
            return redirect()
                ->route('customer.orders.index')
                ->withErrors(['payment' => 'Pesanan Anda masih menunggu verifikasi admin sebelum mengirim pembayaran.']);
        }

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

    private function syncMidtransPaymentsForCustomer(int $userId): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $payments = Payment::query()
            ->whereHas('order', static fn ($query) => $query->where('user_id', $userId))
            ->whereNotNull('midtrans_order_id')
            ->whereIn('status', ['pending', 'rejected'])
            ->latest('id')
            ->limit(40)
            ->get();

        foreach ($payments as $payment) {
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
            } catch (\Throwable $exception) {
                report($exception);
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
                    'invoice_number' => $payment->invoice_number ?: 'INV/PUM/' . now()->format('m/Y') . '/' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) . '-' . Str::upper((string) $payment->method),
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

            $this->ensureWorkOrderAndSteps($order, (int) $order->user_id);

            return;
        }

        if (in_array($midtransStatus, ['deny', 'cancel', 'expire'], true) && $payment->status !== 'rejected') {
            $payment->update([
                'status' => 'rejected',
                'verified_at' => now(),
            ]);
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

    /**
     * @return array<int, string>
     */
    private function masterMaterialOptions(): array
    {
        return $this->masterNameList('materials', self::MATERIALS);
    }

    /**
     * @return array<int, string>
     */
    private function masterProductionTypeOptions(): array
    {
        return $this->masterNameList('production_types', self::TYPES);
    }

    /**
     * @return array<int, string>
     */
    private function masterDesignPositionOptions(): array
    {
        return $this->masterNameList('design_positions', self::DESIGN_POSITIONS);
    }

    /**
     * @return array<int, string>
     */
    private function masterProductModelOptions(): array
    {
        return $this->masterNameList('product_models', self::MODELS);
    }

    /**
     * @return array<int, string>
     */
    private function masterSleeveTypeOptions(): array
    {
        return $this->masterNameList('sleeve_types', self::SLEEVES);
    }

    /**
     * @return array<int, string>
     */
    private function masterSizeOptions(): array
    {
        return $this->masterNameList('sizes', self::SIZES);
    }

    /**
     * @param array<int, string> $materials
     * @return array<string, int>
     */
    private function masterMaterialBasePriceMap(array $materials): array
    {
        $priceMap = [];

        foreach ($materials as $materialName) {
            $priceMap[$materialName] = (int) (self::MATERIAL_BASE_PRICES[$materialName] ?? 85000);
        }

        try {
            if (! Schema::hasTable('materials') || ! Schema::hasColumn('materials', 'base_price')) {
                return $priceMap;
            }

            $dbPrices = DB::table('materials')
                ->where('is_active', true)
                ->whereNotNull('base_price')
                ->pluck('base_price', 'name')
                ->all();

            foreach ($dbPrices as $name => $basePrice) {
                $materialName = (string) $name;

                if (! in_array($materialName, $materials, true)) {
                    continue;
                }

                $priceMap[$materialName] = (int) $basePrice;
            }

            return $priceMap;
        } catch (\Throwable $exception) {
            report($exception);

            return $priceMap;
        }
    }

    /**
     * @return array<string, array{title: string, description: string, tags: array<int, string>, colors: array<int, array{name: string, hex: string}>, image?: string, suitable_for?: array<int, string>, design_application?: array<int, string>}>
     */
    private function materialCatalogData(): array
    {
        return [
            'Drill' => [
                'title' => 'Tebal & Kokoh',
                'description' => 'Kain tenun diagonal yang kuat dan kaku. Cocok untuk seragam kerja lapangan, baju dinas, dan workwear industri berat.',
                'tags' => ['Tahan lama', 'Anti-robek', 'Formal', 'Heavy duty'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Abu gelap', 'hex' => '#575757'],
                    ['name' => 'Hijau militer', 'hex' => '#49653A'],
                    ['name' => 'Coklat tua', 'hex' => '#5A3A1D'],
                    ['name' => 'Krem', 'hex' => '#DCCDA6'],
                    ['name' => 'Abu medium', 'hex' => '#8B8B8B'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                ],
            ],
            'Taipan' => [
                'title' => 'Semi-formal',
                'description' => 'Bahan dengan tampilan rapi dan kokoh, cocok untuk seragam kantor, instansi, dan kebutuhan semi-formal.',
                'tags' => ['Rapi', 'Nyaman', 'Semi formal', 'Seragam kantor'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                    ['name' => 'Coklat', 'hex' => '#7B4B26'],
                    ['name' => 'Krem', 'hex' => '#DCCDA6'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                ],
            ],
            'Tropical' => [
                'title' => 'Ringan & Adem',
                'description' => 'Bahan yang ringan dan nyaman dipakai untuk aktivitas harian, pelatihan, dan seragam kerja indoor.',
                'tags' => ['Ringan', 'Adem', 'Fleksibel', 'Nyaman dipakai'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Abu gelap', 'hex' => '#5C5C5C'],
                    ['name' => 'Hijau', 'hex' => '#4F7D4D'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                ],
            ],
            'Oxford' => [
                'title' => 'Premium Formal',
                'description' => 'Tekstur halus dan tampilan formal. Cocok untuk kemeja kerja, seragam kantor, dan kebutuhan presentable.',
                'tags' => ['Formal', 'Halus', 'Presentable', 'Kemeja kerja'],
                'colors' => [
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                    ['name' => 'Biru muda', 'hex' => '#9CC8F2'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                    ['name' => 'Coklat', 'hex' => '#7B4B26'],
                ],
            ],
            'Twill' => [
                'title' => 'Kuat & Berat',
                'description' => 'Bahan tebal dengan serat rapat, cocok untuk seragam lapangan dan pakaian kerja yang membutuhkan durabilitas tinggi.',
                'tags' => ['Kuat', 'Berat', 'Durable', 'Lapangan'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Coklat tua', 'hex' => '#5A3A1D'],
                    ['name' => 'Krem', 'hex' => '#DCCDA6'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                ],
            ],
            'Ribstop' => [
                'title' => 'Anti-sobek',
                'description' => 'Memiliki struktur serat kotak yang membantu ketahanan kain, cocok untuk workwear dan seragam teknis.',
                'tags' => ['Anti-sobek', 'Teknis', 'Workwear', 'Kokoh'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Hijau militer', 'hex' => '#49653A'],
                    ['name' => 'Coklat tua', 'hex' => '#5A3A1D'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                    ['name' => 'Krem', 'hex' => '#DCCDA6'],
                ],
            ],
            'Lacoste Pique' => [
                'title' => 'Corporate Premium',
                'description' => 'Tekstur pique khas poloshirt, memberi kesan premium untuk perusahaan, kampus, dan instansi.',
                'tags' => ['Premium', 'Corporate', 'Poloshirt', 'Bordir cocok'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Hijau', 'hex' => '#4F7D4D'],
                    ['name' => 'Merah marun', 'hex' => '#7A1F2B'],
                    ['name' => 'Coklat', 'hex' => '#7B4B26'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                ],
            ],
            'Cotton Combed 30s' => [
                'title' => 'Tipis & Adem',
                'description' => 'Sangat sejuk dan menyerap keringat, namun kainnya tipis dan rentan susut.',
                'image' => 'images/Bahan/cotton30s.png',
                'tags' => ['100% Katun', 'Tipis & Adem', 'Menyerap keringat', 'Rentan susut'],
                'suitable_for' => ['Kaos distro', 'Pakaian harian', 'Kaos event', 'Merchandise brand'],
                'design_application' => [
                    'Cocok: Sablon manual',
                    'Cocok: DTF',
                    'Tidak disarankan: Bordir (rawan bolong/berkerut)',
                    'Tidak bisa: Printing/Sublim di katun',
                ],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Merah', 'hex' => '#B21F2D'],
                    ['name' => 'Hijau', 'hex' => '#4F7D4D'],
                    ['name' => 'Krem', 'hex' => '#DCCDA6'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                ],
            ],
            'Cotton Combed 20s' => [
                'title' => 'Heavy Weight',
                'description' => 'Lebih tebal dan berat, cocok untuk kaos premium yang ingin terasa solid dan tidak menerawang.',
                'tags' => ['Tebal', 'Premium', 'Tidak menerawang', 'Solid'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Merah', 'hex' => '#B21F2D'],
                    ['name' => 'Hijau', 'hex' => '#4F7D4D'],
                    ['name' => 'Abu gelap', 'hex' => '#5C5C5C'],
                ],
            ],
            'Drifit' => [
                'title' => 'Sport Performance',
                'description' => 'Material ringan dan cepat kering, cocok untuk jersey, event olahraga, dan aktivitas outdoor.',
                'tags' => ['Cepat kering', 'Sport', 'Ringan', 'Outdoor'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                    ['name' => 'Merah', 'hex' => '#B21F2D'],
                    ['name' => 'Hijau', 'hex' => '#4F7D4D'],
                    ['name' => 'Tosca', 'hex' => '#1CA4BC'],
                ],
            ],
            'Lainnya' => [
                'title' => 'Custom Request',
                'description' => 'Pilih jika bahan yang Anda inginkan belum tersedia di daftar utama. Tim kami akan menyesuaikan spesifikasi yang paling mendekati.',
                'tags' => ['Custom', 'Menyesuaikan', 'Request khusus', 'Konsultasi'],
                'colors' => [
                    ['name' => 'Hitam', 'hex' => '#1E1E1E'],
                    ['name' => 'Putih', 'hex' => '#F7F7F7'],
                    ['name' => 'Navy', 'hex' => '#223A70'],
                    ['name' => 'Abu', 'hex' => '#7C7C7C'],
                    ['name' => 'Krem', 'hex' => '#DCCDA6'],
                    ['name' => 'Custom', 'hex' => '#C8A949'],
                ],
            ],
        ];
    }

    /**
     * @param array<int, string> $types
     * @return array<string, int>
     */
    private function masterProductionTypeSurchargeMap(array $types): array
    {
        $surchargeMap = [];

        foreach ($types as $typeName) {
            $surchargeMap[$typeName] = (int) (self::TECHNIQUE_SURCHARGES[$typeName] ?? 0);
        }

        try {
            if (! Schema::hasTable('production_types') || ! Schema::hasColumn('production_types', 'surcharge_price')) {
                return $surchargeMap;
            }

            $dbSurcharges = DB::table('production_types')
                ->where('is_active', true)
                ->whereNotNull('surcharge_price')
                ->pluck('surcharge_price', 'name')
                ->all();

            foreach ($dbSurcharges as $name => $surchargePrice) {
                $typeName = (string) $name;

                if (! in_array($typeName, $types, true)) {
                    continue;
                }

                $surchargeMap[$typeName] = (int) $surchargePrice;
            }

            return $surchargeMap;
        } catch (\Throwable $exception) {
            report($exception);

            return $surchargeMap;
        }
    }

    /**
     * @param array<int, string> $fallback
     * @return array<int, string>
     */
    private function masterNameList(string $table, array $fallback): array
    {
        try {
            if (! Schema::hasTable($table)) {
                return $fallback;
            }

            $items = DB::table($table)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('name')
                ->filter(static fn ($value): bool => is_string($value) && trim($value) !== '')
                ->map(static fn ($value): string => trim((string) $value))
                ->unique()
                ->values()
                ->all();

            return empty($items) ? $fallback : $items;
        } catch (\Throwable $exception) {
            report($exception);

            return $fallback;
        }
    }
}
