@extends('layouts.app')

@section('header_title', request('focus') === 'status' ? 'Status Produksi' : 'Riwayat Pesanan')

@section('content')
@php
    $isStatusFocus = request('focus') === 'status';

    $statusClass = function (string $status): string {
        return \App\Support\OrderStatusPresenter::customerClass($status);
    };

    $statusLabel = function (string $status): string {
        return \App\Support\OrderStatusPresenter::customerLabel($status);
    };

    $resolveOrderStatus = static function ($order, $latestPayment): string {
        return \App\Support\OrderStatusPresenter::resolveForCustomer($order, $latestPayment);
    };

    $paymentMethodLabel = function (?string $method): string {
        return match ($method) {
            'dp' => 'DP 50%',
            'settlement' => 'Pelunasan',
            'full' => 'Lunas Awal',
            default => strtoupper((string) $method),
        };
    };

    $isImage = function (?string $path): bool {
        if (! $path) {
            return false;
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
    };

    $timelineStepName = function (string $name): ?string {
        $normalized = strtolower(trim($name));

        if (str_contains($normalized, 'cutting')) {
            return 'Cutting';
        }

        if (str_contains($normalized, 'persiapan bahan')) {
            return null;
        }

        return match ($normalized) {
            'jahit' => 'Jahit / Obras',
            'sablon' => 'Sablon / Bordir / Printing',
            'steam' => 'Steam & Pressing',
            'finishing' => 'Finishing',
            default => $name,
        };
    };

    $timelineStateLabel = function (string $status): string {
        return match ($status) {
            'done' => 'Selesai',
            'in_progress' => 'Sedang dikerjakan',
            default => 'Menunggu giliran',
        };
    };

    $timelineStateClass = function (string $status): string {
        return match ($status) {
            'done' => 'timeline-item-done',
            'in_progress' => 'timeline-item-progress',
            default => 'timeline-item-pending',
        };
    };

    $finishedStatuses = ['completed'];
    $activeOrdersCollection = $orders->getCollection()
        ->filter(static fn ($order): bool => ! in_array($order->order_status, $finishedStatuses, true))
        ->values();
    $completedOrdersCollection = $orders->getCollection()
        ->filter(static fn ($order): bool => in_array($order->order_status, $finishedStatuses, true))
        ->values();
@endphp

<style>
    .orders-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .orders-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1320px;
    }

    .orders-table th,
    .orders-table td {
        padding: 0.62rem;
        border-bottom: 1px solid #e4ecf2;
        text-align: left;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .orders-table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #355068;
        font-weight: 700;
        background: #f8fbfe;
    }

    .orders-table tbody tr:hover {
        background: #fbfdff;
    }

    .order-code {
        font-weight: 700;
    }

    .order-code a {
        color: #0f7b8f;
        text-decoration: none;
    }

    .order-code a:hover {
        text-decoration: underline;
    }

    .design-preview {
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .design-thumb {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #d8e4ee;
        background: #fff;
    }

    .design-link {
        font-size: 0.82rem;
        color: #0f7b8f;
        text-decoration: none;
    }

    .design-link:hover {
        text-decoration: underline;
    }

    .invoice-pill {
        display: inline-block;
        padding: 0.22rem 0.5rem;
        border-radius: 999px;
        border: 1px solid #cfe0eb;
        background: #f4f9fc;
        color: #20465f;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .invoice-download {
        display: inline-block;
        margin-top: 0.32rem;
        color: #0f7b8f;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .invoice-download:hover {
        text-decoration: underline;
    }

    .status-center {
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .status-head {
        text-align: center;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        width: fit-content;
        align-items: flex-start;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.48rem 0.9rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .action-btn-outline {
        color: #0f7b8f;
        border-color: #0f7b8f;
        background: transparent;
        display: inline-flex;
        width: auto;
        align-self: flex-start;
    }

    .action-btn-outline:hover {
        background: #e8f4f5;
    }

    .action-btn-primary {
        color: #fff;
        background: #122c5f;
        border-color: #122c5f;
        display: flex;
        width: 100%;
        align-self: stretch;
    }

    .action-btn-primary:hover {
        background: #3a60ac;
        border-color: #3a60ac;
    }

    .action-btn-danger {
        color: #fff;
        background: #c73729;
        border-color: #c73729;
        display: flex;
        width: 100%;
        align-self: stretch;
    }

    .action-btn-danger:hover {
        background: #a92d21;
        border-color: #a92d21;
    }

    .payment-cell {
        display: grid;
        gap: 0.35rem;
    }

    .payment-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        padding: 0.24rem 0.7rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        border: 1px solid transparent;
        text-transform: uppercase;
    }

    .payment-pill-dp {
        background: #fdf4e8;
        color: #c27a07;
        border-color: #f2d5a4;
    }

    .payment-pill-full {
        background: #eaf7ee;
        color: #1f7a48;
        border-color: #bde4cc;
    }

    .payment-pill-settlement {
        background: #fff3ee;
        color: #b45309;
        border-color: #f5c6aa;
    }

    .payment-amount-note {
        font-size: 0.82rem;
        color: #5a7489;
        font-weight: 600;
    }

    .payment-waiting-text {
        color: #d70f0f;
        font-weight: 700;
    }

    .orders-empty {
        text-align: center;
        padding: 1rem 0;
        color: #6b8093;
    }

    .production-status-card {
        border: 1px solid #d8e4ee;
        border-radius: 16px;
        background: #fff;
        padding: 1rem 1.1rem;
        margin-top: 0.9rem;
    }

    .production-status-head h2 {
        margin: 0;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: 1.18rem;
    }

    .production-status-head p {
        margin: 0.35rem 0 0.95rem;
        color: #7f96ae;
        font-size: 0.84rem;
    }

    .timeline-order-card {
        border: 1px solid #d8e4ee;
        border-radius: 14px;
        background: #fcfdff;
        padding: 0.95rem 1rem;
        margin-bottom: 0.9rem;
    }

    .timeline-order-card:last-child {
        margin-bottom: 0;
    }

    .timeline-order-title {
        margin: 0;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: 0.98rem;
    }

    .timeline-order-meta {
        margin: 0.34rem 0 0.85rem;
        color: #7f96ae;
        font-size: 0.8rem;
    }

    .timeline-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .timeline-item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 0.72rem;
        position: relative;
        padding-bottom: 0.8rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item-detached {
        margin-top: 0.45rem;
    }

    .timeline-item-detached::before {
        display: none;
    }

    .timeline-item::before {
        content: "";
        position: absolute;
        left: 16px;
        top: 22px;
        bottom: -4px;
        width: 2px;
        background: #d6e1eb;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-dot {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        border: 2px solid #c6d6e5;
        background: #f7fbff;
        color: #6281a0;
        font-size: 0.74rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .timeline-dot::after {
        content: "";
        position: absolute;
        inset: -8px;
        border-radius: 999px;
        border: 2px solid transparent;
        opacity: 0;
        pointer-events: none;
    }

    .timeline-item-done .timeline-dot {
        border-color: #8cc8ad;
        background: #eaf7ef;
        color: #186847;
    }

    .timeline-item-progress .timeline-dot {
        border-color: #e0c578;
        background: #fdf6e7;
        color: #9a6a00;
        box-shadow: 0 0 0 4px rgba(224, 197, 120, 0.22);
    }

    .timeline-item-progress .timeline-dot::after {
        border-color: rgba(224, 197, 120, 0.75);
        animation: timeline-pulse 1.6s ease-out infinite;
    }

    @keyframes timeline-pulse {
        0% {
            transform: scale(0.78);
            opacity: 0.95;
        }
        70% {
            transform: scale(1.18);
            opacity: 0;
        }
        100% {
            transform: scale(1.18);
            opacity: 0;
        }
    }

    .timeline-item-title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: #102a43;
    }

    .timeline-item-done .timeline-item-title {
        color: #1f7a48;
    }

    .timeline-item-progress .timeline-item-title {
        color: #b17f11;
    }

    .timeline-item-note {
        margin: 0.12rem 0 0;
        color: #728da8;
        font-size: 0.82rem;
    }

    .timeline-item-date {
        margin: 0.12rem 0 0;
        color: #7d97b1;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .settlement-alert {
        margin-top: 0.85rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #b53025, #c73729);
        padding: 0.85rem 0.95rem;
        color: #fff;
        display: flex;
        gap: 0.8rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .settlement-alert h4 {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
    }

    .settlement-alert p {
        margin: 0.3rem 0 0;
        font-size: 0.84rem;
        line-height: 1.4;
        max-width: 620px;
    }

    .settlement-alert strong {
        color: #ffe9c2;
    }

    .settlement-alert .btn {
        background: #c8a949;
        border-color: #c8a949;
        color: #0f2947;
        font-weight: 700;
    }

    .settlement-alert .btn:hover {
        background: #dfbf65;
        border-color: #dfbf65;
    }

    @media (max-width: 768px) {
        .orders-toolbar {
            align-items: flex-start;
        }

        .timeline-item {
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 0.6rem;
        }

        .timeline-dot {
            width: 24px;
            height: 24px;
            font-size: 0.7rem;
        }

        .timeline-item::before {
            left: 13px;
            top: 18px;
        }
    }
</style>

@if ($isStatusFocus)
    <div class="card production-status-card">
        <div class="production-status-head">
            <h2>Status Produksi</h2>
            <p>Pantau perkembangan status produksi pesanan Anda</p>
        </div>

        @php
            $statusActiveOrders = $activeOrdersCollection->filter(static function ($order): bool {
                return $order->productionSteps->isNotEmpty()
                    || in_array($order->order_status, ['verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement', 'ready_for_pickup'], true);
            })->values();

            $statusCompletedOrders = $completedOrdersCollection->filter(static function ($order): bool {
                return $order->productionSteps->isNotEmpty()
                    || in_array($order->order_status, ['completed'], true);
            })->values();
        @endphp

        @if ($statusActiveOrders->isEmpty() && $statusCompletedOrders->isEmpty())
            <p class="orders-empty">Belum ada progres produksi untuk ditampilkan.</p>
        @else
            @if ($statusActiveOrders->isNotEmpty())
                <h3 style="margin:0 0 0.85rem; color:#0d2749; font-family:'Playfair Display', serif;">Pesanan Berjalan</h3>
            @endif

            @foreach ($statusActiveOrders as $order)
                @php
                    $productionSteps = $order->productionSteps->sortBy('step_order')->values();
                    $displaySteps = [];

                    $paymentVerifiedAt = optional(
                        $order->payments
                            ->where('status', 'verified')
                            ->sortByDesc('verified_at')
                            ->first()
                    )->verified_at;

                    $isVerificationDone = in_array($order->order_status, ['verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement', 'ready_for_pickup', 'completed'], true);

                    $displaySteps[] = [
                        'order' => 1,
                        'title' => 'Verifikasi & Konfirmasi Pesanan',
                        'status' => $isVerificationDone ? 'done' : 'pending',
                        'updated_at' => $isVerificationDone ? $paymentVerifiedAt : null,
                    ];

                    $stepCounter = 2;
                    foreach ($productionSteps as $step) {
                        $displayName = $timelineStepName((string) $step->step_name);
                        if ($displayName === null) {
                            continue;
                        }

                        $displaySteps[] = [
                            'order' => $stepCounter,
                            'title' => $displayName,
                            'status' => $step->status,
                            'updated_at' => $step->updated_at,
                        ];

                        $stepCounter++;
                    }

                    if (in_array($order->order_status, ['ready_for_pickup', 'completed'], true)) {
                        $pickupTitle = $order->order_status === 'completed'
                            ? '📦 Pesanan Sudah Diambil'
                            : '📦 Pesanan Siap Diambil';

                        $displaySteps[] = [
                            'order' => null,
                            'title' => $pickupTitle,
                            'status' => 'done',
                            'updated_at' => $order->updated_at,
                            'detached' => true,
                        ];
                    }
                @endphp

                <article class="timeline-order-card">
                    <h3 class="timeline-order-title">{{ $order->order_code }} - {{ $order->product_model ?: $order->product_name }}</h3>
                    <p class="timeline-order-meta">{{ number_format((int) $order->total_pcs, 0, ',', '.') }} pcs | Target: {{ $order->estimated_finish_date?->translatedFormat('d M Y') }}</p>

                    <ol class="timeline-list">
                        @foreach ($displaySteps as $timeline)
                            @php
                                $timelineClass = $timelineStateClass((string) $timeline['status']);
                            @endphp
                            <li class="timeline-item {{ $timelineClass }}">
                                <span class="timeline-dot">
                                    @if ($timeline['status'] === 'done')
                                        ✓
                                    @else
                                        {{ $timeline['order'] }}
                                    @endif
                                </span>
                                <div>
                                    <p class="timeline-item-title">{{ $timeline['title'] }}</p>
                                    <p class="timeline-item-note">{{ $timelineStateLabel((string) $timeline['status']) }}</p>
                                    @if (($timeline['status'] === 'done') && ! empty($timeline['updated_at']))
                                        <p class="timeline-item-date">{{ optional($timeline['updated_at'])->translatedFormat('d M Y') }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    @if ($order->order_status === 'finishing_waiting_settlement' && $order->isSettlementRequired())
                        <div class="settlement-alert">
                            <div>
                                <h4>🔔 Lakukan Pelunasan Sekarang!</h4>
                                <p>Pesanan Anda telah mencapai tahap Steam & Pressing. Tahapan finishing tidak dapat dilakukan sebelum pelunasan terverifikasi.<br>Sisa: <strong>Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</strong></p>
                            </div>
                            <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin:0;">
                                @csrf
                                <button class="btn" type="submit">Bayar Sekarang →</button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach

            @if ($statusCompletedOrders->isNotEmpty())
                <h3 style="margin:1rem 0 0.85rem; color:#0d2749; font-family:'Playfair Display', serif;">Pesanan Selesai</h3>
            @endif

            @foreach ($statusCompletedOrders as $order)
                @php
                    $productionSteps = $order->productionSteps->sortBy('step_order')->values();
                    $displaySteps = [];

                    $paymentVerifiedAt = optional(
                        $order->payments
                            ->where('status', 'verified')
                            ->sortByDesc('verified_at')
                            ->first()
                    )->verified_at;

                    $displaySteps[] = [
                        'order' => 1,
                        'title' => 'Verifikasi & Konfirmasi Pesanan',
                        'status' => 'done',
                        'updated_at' => $paymentVerifiedAt,
                    ];

                    $stepCounter = 2;
                    foreach ($productionSteps as $step) {
                        $displayName = $timelineStepName((string) $step->step_name);
                        if ($displayName === null) {
                            continue;
                        }

                        $displaySteps[] = [
                            'order' => $stepCounter,
                            'title' => $displayName,
                            'status' => 'done',
                            'updated_at' => $step->updated_at,
                        ];

                        $stepCounter++;
                    }

                    if (in_array($order->order_status, ['ready_for_pickup', 'completed'], true)) {
                        $pickupTitle = $order->order_status === 'completed'
                            ? '📦 Pesanan Sudah Diambil'
                            : '📦 Pesanan Siap Diambil';

                        $displaySteps[] = [
                            'order' => null,
                            'title' => $pickupTitle,
                            'status' => 'done',
                            'updated_at' => $order->updated_at,
                            'detached' => true,
                        ];
                    }
                @endphp

                <article class="timeline-order-card">
                    <h3 class="timeline-order-title">{{ $order->order_code }} - {{ $order->product_model ?: $order->product_name }}</h3>
                    <p class="timeline-order-meta">{{ number_format((int) $order->total_pcs, 0, ',', '.') }} pcs | Target: {{ $order->estimated_finish_date?->translatedFormat('d M Y') }}</p>

                    <ol class="timeline-list">
                        @foreach ($displaySteps as $timeline)
                            @php
                                $timelineClass = $timelineStateClass((string) $timeline['status']);
                            @endphp
                            <li class="timeline-item {{ $timelineClass }} {{ !empty($timeline['detached']) ? 'timeline-item-detached' : '' }}">
                                <span class="timeline-dot">✓</span>
                                <div>
                                    <p class="timeline-item-title">{{ $timeline['title'] }}</p>
                                    <p class="timeline-item-note">{{ $timelineStateLabel((string) $timeline['status']) }}</p>
                                    @if (! empty($timeline['updated_at']))
                                        <p class="timeline-item-date">{{ optional($timeline['updated_at'])->translatedFormat('d M Y') }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </article>
            @endforeach

            @if ($orders->hasPages())
                <div style="margin-top: 0.85rem;">
                    {{ $orders->appends(['focus' => 'status'])->links() }}
                </div>
            @endif
        @endif
    </div>
@else
    <div class="card">
        <div class="orders-toolbar">
            <div>
                <h1 style="margin:0 0 0.2rem;">Pesanan Saya</h1>
                <p class="muted" style="margin:0;">Detail lengkap pesanan, pembayaran, invoice, dan desain custom Anda.</p>
            </div>
            <a class="btn btn-brand" href="{{ route('customer.orders.create') }}">Pesan Custom Baru</a>
        </div>

        @if ($activeOrdersCollection->isEmpty() && $completedOrdersCollection->isEmpty())
            <p class="orders-empty">Belum ada pesanan.</p>
        @else
            <h3 style="margin:0 0 0.7rem; color:#0d2749; font-family:'Playfair Display', serif;">Pesanan Berjalan</h3>
            <div class="orders-table-wrap">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Order</th>
                            <th>Tanggal Pemesanan</th>
                            <th style="white-space: nowrap;">Estimasi Selesai</th>
                            <th>Jenis Produk</th>
                            <th>Total PCS</th>
                            <th>Total Harga</th>
                            <th class="status-head">Status Produksi</th>
                            <th>Opsi Pembayaran</th>
                            <th>Jumlah</th>
                            <th>Sisa</th>
                            <th>Invoice</th>
                            <th>Preview Desain</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeOrdersCollection as $order)
                            @php
                                $latestPayment = $order->payments->sortByDesc('id')->first();
                                $latestVerifiedPayment = $order->payments
                                    ->where('status', 'verified')
                                    ->sortByDesc(fn ($payment) => $payment->verified_at ?? $payment->updated_at ?? $payment->created_at)
                                    ->first();
                                $statusForDisplay = $resolveOrderStatus($order, $latestPayment);
                                $isWaitingPayment = $statusForDisplay === 'admin_verified_waiting_payment';
                                $invoicePayment = $order->payments
                                    ->where('status', 'verified')
                                    ->sortByDesc(fn ($payment) => $payment->verified_at ?? $payment->updated_at)
                                    ->first();
                                $invoiceNumber = $invoicePayment?->invoice_number;

                                $pendingActionPayment = $order->payments
                                    ->sortByDesc('id')
                                    ->first(static function ($payment) use ($order): bool {
                                        if ($payment->status !== 'pending') {
                                            return false;
                                        }

                                        if (in_array($payment->midtrans_status, ['settlement', 'capture'], true)) {
                                            return false;
                                        }

                                        if ($payment->method === 'settlement') {
                                            return $order->order_status === 'finishing_waiting_settlement';
                                        }

                                        if (($order->admin_verification_status ?? 'pending') !== 'verified') {
                                            return false;
                                        }

                                        return true;
                                    });

                                $frontPath = $order->design_front_file ?: $order->design_file;
                                $backPath = $order->design_back_file;

                                $paymentMethodClass = match ($latestVerifiedPayment?->method) {
                                    'dp' => 'payment-pill-dp',
                                    'full' => 'payment-pill-full',
                                    'settlement' => 'payment-pill-settlement',
                                    default => 'payment-pill-dp',
                                };

                                $paymentMethodLabel = match ($latestVerifiedPayment?->method) {
                                    'dp' => 'DP 50%',
                                    'full' => 'Lunas Awal',
                                    'settlement' => 'Pelunasan',
                                    default => '-',
                                };

                                $plannedPaymentMethod = $order->payment_type ?: 'dp';
                                $plannedPaymentAmount = $plannedPaymentMethod === 'full'
                                    ? (float) $order->subtotal
                                    : (float) $order->dp_amount;

                                $plannedRemainingAmount = $plannedPaymentMethod === 'full'
                                    ? 0.0
                                    : (float) $order->remaining_amount;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="order-code">
                                    <a href="{{ route('customer.orders.show', $order) }}">{{ $order->order_code }}</a>
                                </td>
                                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->estimated_finish_date ? \Carbon\Carbon::parse($order->estimated_finish_date)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                                <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                                <td class="status-center">
                                    <span class="status-pill {{ $statusClass($statusForDisplay) }}">{{ $statusLabel($statusForDisplay) }}</span>
                                </td>
                                <td>
                                    @if ($latestVerifiedPayment)
                                        <span class="payment-pill {{ $paymentMethodClass }}">{{ $paymentMethodLabel }}</span>
                                    @elseif ($isWaitingPayment)
                                        <span class="payment-waiting-text">Menunggu Pembayaran (Max 2x24 Jam)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($latestVerifiedPayment)
                                        <span class="payment-amount-note">Rp {{ number_format((float) $latestVerifiedPayment->amount, 0, ',', '.') }}</span>
                                    @elseif ($isWaitingPayment)
                                        <span class="payment-amount-note">Rp {{ number_format($plannedPaymentAmount, 0, ',', '.') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($latestVerifiedPayment)
                                        <span class="payment-amount-note">Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</span>
                                    @elseif ($isWaitingPayment)
                                        <span class="payment-amount-note">Rp {{ number_format($plannedRemainingAmount, 0, ',', '.') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($invoicePayment)
                                        <a
                                            class="invoice-pill"
                                            href="{{ route('customer.invoices.show', [$order, $invoicePayment]) }}"
                                            target="_blank"
                                        >
                                            {{ $invoiceNumber ?: 'Lihat Invoice' }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="design-preview">
                                        @if ($frontPath)
                                            @if ($isImage($frontPath))
                                                <a href="{{ asset('storage/' . $frontPath) }}" target="_blank" title="Desain Depan">
                                                    <img class="design-thumb" src="{{ asset('storage/' . $frontPath) }}" alt="Desain depan {{ $order->order_code }}">
                                                </a>
                                            @else
                                                <a class="design-link" href="{{ asset('storage/' . $frontPath) }}" target="_blank">File Depan</a>
                                            @endif
                                        @endif

                                        @if ($backPath)
                                            @if ($isImage($backPath))
                                                <a href="{{ asset('storage/' . $backPath) }}" target="_blank" title="Desain Belakang">
                                                    <img class="design-thumb" src="{{ asset('storage/' . $backPath) }}" alt="Desain belakang {{ $order->order_code }}">
                                                </a>
                                            @else
                                                <a class="design-link" href="{{ asset('storage/' . $backPath) }}" target="_blank">File Belakang</a>
                                            @endif
                                        @endif

                                        @if (! $frontPath && ! $backPath)
                                            <span>-</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="action-btn action-btn-outline" href="{{ route('customer.orders.show', $order) }}">Detail</a>
                                        @if ($isWaitingPayment && $pendingActionPayment)
                                            <a class="action-btn action-btn-primary" href="{{ route('customer.orders.payments.edit', [$order, $pendingActionPayment]) }}">Lanjut Pembayaran</a>
                                        @endif
                                        @if ($order->order_status === 'finishing_waiting_settlement' && $order->isSettlementRequired())
                                            @if ($pendingActionPayment && $pendingActionPayment->method === 'settlement')
                                                <a class="action-btn action-btn-danger" href="{{ route('customer.orders.payments.edit', [$order, $pendingActionPayment]) }}">Lanjut Pelunasan</a>
                                            @else
                                                <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin:0;">
                                                    @csrf
                                                    <button class="action-btn action-btn-danger" type="submit">Lanjut Pelunasan</button>
                                                </form>
                                            @endif
                                        @endif
                                        @if ($order->order_status === 'finishing_waiting_settlement' && $order->isSettlementRequired())
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="orders-empty">Belum ada pesanan berjalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($completedOrdersCollection->isNotEmpty())
                <h3 style="margin:1.1rem 0 0.7rem; color:#0d2749; font-family:'Playfair Display', serif;">Pesanan Selesai</h3>
                <div class="orders-table-wrap">
                    <table class="orders-table" style="min-width:980px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Order</th>
                                <th>Tanggal Pemesanan</th>
                                <th style="white-space: nowrap;">Estimasi Selesai</th>
                                <th>Jenis Produk</th>
                                <th>Total PCS</th>
                                <th>Total Harga</th>
                                <th class="status-head">Status Produksi</th>
                                <th>Invoice</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($completedOrdersCollection as $order)
                                @php
                                    $latestPayment = $order->payments->sortByDesc('id')->first();
                                    $statusForDisplay = $resolveOrderStatus($order, $latestPayment);
                                    $invoicePayment = $order->payments
                                        ->where('status', 'verified')
                                        ->sortByDesc(fn ($payment) => $payment->verified_at ?? $payment->updated_at)
                                        ->first();
                                    $invoiceNumber = $invoicePayment?->invoice_number;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="order-code"><a href="{{ route('customer.orders.show', $order) }}">{{ $order->order_code }}</a></td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $order->estimated_finish_date ? \Carbon\Carbon::parse($order->estimated_finish_date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                                    <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                                    <td class="status-center"><span class="status-pill {{ $statusClass($statusForDisplay) }}">{{ $statusLabel($statusForDisplay) }}</span></td>
                                    <td>
                                        @if ($invoicePayment)
                                            <a class="invoice-pill" href="{{ route('customer.invoices.show', [$order, $invoicePayment]) }}" target="_blank">
                                                {{ $invoiceNumber ?: 'Lihat Invoice' }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><a class="action-btn action-btn-outline" href="{{ route('customer.orders.show', $order) }}">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($orders->hasPages())
                <div style="margin-top: 0.85rem;">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
@endif
@endsection
