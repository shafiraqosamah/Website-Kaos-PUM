@extends('layouts.app')

@section('content')
@php
    $isStatusFocus = request('focus') === 'status';

    $statusClass = function (string $status): string {
        return match ($status) {
            'pending', 'pending_verification', 'submitted' => 'status-warning',
            'verified', 'verified_payment', 'verified_dp', 'fully_paid', 'completed' => 'status-success',
            'rejected' => 'status-danger',
            'in_production' => 'status-info',
            'finishing_waiting_settlement' => 'status-accent',
            default => 'status-neutral',
        };
    };

    $statusLabel = function (string $status): string {
        return match ($status) {
            'submitted', 'pending_verification' => 'Menunggu Verifikasi',
            'verified_payment', 'verified_dp' => 'Menunggu Produksi',
            'in_production' => 'Sedang Proses',
            'finishing_waiting_settlement' => 'Menunggu Pelunasan',
            'completed', 'done' => 'Selesai',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
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

        if (str_contains($normalized, 'cutting') || str_contains($normalized, 'persiapan bahan')) {
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

    .payment-detail {
        margin-top: 0.18rem;
        color: #5a7489;
        font-size: 0.8rem;
        line-height: 1.35;
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

    .timeline-item-done .timeline-dot {
        border-color: #8cc8ad;
        background: #eaf7ef;
        color: #186847;
    }

    .timeline-item-progress .timeline-dot {
        border-color: #e0c578;
        background: #fdf6e7;
        color: #9a6a00;
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
            <p>Pantau perkembangan pesanan Anda secara real-time</p>
        </div>

        @php
            $statusOrders = $orders->getCollection()->filter(static function ($order): bool {
                return $order->productionSteps->isNotEmpty()
                    || in_array($order->order_status, ['verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement', 'completed'], true);
            })->values();
        @endphp

        @if ($statusOrders->isEmpty())
            <p class="orders-empty">Belum ada progres produksi untuk ditampilkan.</p>
        @else
            @foreach ($statusOrders as $order)
                @php
                    $productionSteps = $order->productionSteps->sortBy('step_order')->values();
                    $displaySteps = [];

                    $paymentVerifiedAt = optional(
                        $order->payments
                            ->where('status', 'verified')
                            ->sortByDesc('verified_at')
                            ->first()
                    )->verified_at;

                    $isVerificationDone = in_array($order->order_status, ['verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement', 'completed'], true);

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
                                <p>Pesanan Anda memasuki tahap finishing. Proses tidak akan dilanjutkan sampai pelunasan dikonfirmasi.<br>Sisa: <strong>Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</strong></p>
                            </div>
                            <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin:0;">
                                @csrf
                                <button class="btn" type="submit">Bayar Sekarang →</button>
                            </form>
                        </div>
                    @endif
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

        @if ($orders->isEmpty())
            <p class="orders-empty">Belum ada pesanan.</p>
        @else
            <div class="orders-table-wrap">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Order</th>
                            <th>Tanggal Pemesanan</th>
                            <th>Jenis Produk</th>
                            <th>Total PCS</th>
                            <th>Total Harga</th>
                            <th>Status Produksi</th>
                            <th>Metode Pembayaran</th>
                            <th>Bayar</th>
                            <th>Invoice</th>
                            <th>Preview Desain</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $latestPayment = $order->payments->sortByDesc('id')->first();
                                $statusForDisplay = ($latestPayment?->status === 'rejected')
                                    ? 'rejected'
                                    : $order->order_status;
                                $invoicePayment = $order->payments->whereNotNull('invoice_number')->sortByDesc('id')->first();
                                $invoiceNumber = optional($invoicePayment)->invoice_number;
                                $frontPath = $order->design_front_file ?: $order->design_file;
                                $backPath = $order->design_back_file;
                            @endphp
                            <tr>
                                <td>{{ ($orders->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="order-code">
                                    <a href="{{ route('customer.orders.show', $order) }}">{{ $order->order_code }}</a>
                                </td>
                                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                                <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-pill {{ $statusClass($statusForDisplay) }}">{{ $statusLabel($statusForDisplay) }}</span>
                                </td>
                                <td>{{ $paymentMethodLabel(optional($latestPayment)->method ?: $order->payment_type) }}</td>
                                <td>
                                    @if (($order->payment_type ?? '') === 'dp')
                                        <div>DP Rp {{ number_format((float) $order->dp_amount, 0, ',', '.') }}</div>
                                        <div class="payment-detail">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</div>
                                    @else
                                        <div>Rp {{ number_format((float) ($latestPayment->amount ?? $order->subtotal), 0, ',', '.') }}</div>
                                        @if ((float) $order->remaining_amount > 0)
                                            <div class="payment-detail">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($invoiceNumber)
                                        <a
                                            class="invoice-pill"
                                            href="{{ route('customer.invoices.show', [$order, $invoicePayment]) }}"
                                            target="_blank"
                                        >
                                            {{ $invoiceNumber }}
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
                                    <div style="display:flex; gap:0.45rem; flex-wrap:wrap;">
                                        <a class="btn btn-outline" href="{{ route('customer.orders.show', $order) }}">Detail</a>
                                        @if ($order->order_status === 'finishing_waiting_settlement' && $order->isSettlementRequired())
                                            <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin:0;">
                                                @csrf
                                                <button class="btn btn-danger" type="submit">Pelunasan</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div style="margin-top: 0.85rem;">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
@endif
@endsection
