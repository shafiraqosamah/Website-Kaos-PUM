@extends('layouts.app')

@section('content')
@php
    $statusClass = static function (string $status): string {
        return \App\Support\OrderStatusPresenter::customerClass($status);
    };

    $statusLabel = static function (string $status): string {
        return \App\Support\OrderStatusPresenter::customerLabel($status);
    };

    $resolveOrderStatus = static function ($order, $lastPayment): string {
        return \App\Support\OrderStatusPresenter::resolveForCustomer($order, $lastPayment);
    };

    $finishedStatuses = ['completed'];
    $activeRecentOrders = $recentOrders
        ->filter(static fn ($order): bool => ! in_array($order->order_status, $finishedStatuses, true))
        ->values();
    $completedRecentOrders = $recentOrders
        ->filter(static fn ($order): bool => in_array($order->order_status, $finishedStatuses, true))
        ->values();
@endphp

<style>
    .customer-dashboard {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .customer-greeting h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .customer-greeting p {
        margin: 0.45rem 0 1.2rem;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.95rem;
        margin-bottom: 1.2rem;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.03);
    }

    .summary-card.orders {
        border-top-color: #142730;
    }

    .summary-card.active {
        border-top-color: #0c7fb6;
    }

    .summary-card.pending {
        border-top-color: #d95f18;
    }

    .summary-card.completed {
        border-top-color: #0f8f60;
    }

    .due-payment-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid #f3c4b8;
        background: #fff4ef;
        padding: 0.72rem 0.95rem;
        color: #b63b22;
        font-size: 0.86rem;
    }

    .due-revision-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid #f1c7c7;
        background: #fff3f3;
        padding: 0.72rem 0.95rem;
        color: #b42318;
        font-size: 0.86rem;
    }

    .due-settlement-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid #f2c7be;
        background: #fff1ed;
        padding: 0.72rem 0.95rem;
        color: #a5361f;
        font-size: 0.86rem;
    }

    .due-payment-alert strong,
    .due-revision-alert strong,
    .due-settlement-alert strong {
        font-weight: 700;
    }

    .due-payment-alert a,
    .due-revision-alert a,
    .due-settlement-alert a {
        color: #b63b22;
        font-weight: 700;
        text-decoration: underline;
    }

    .ready-pickup-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid #9fd5c6;
        background: linear-gradient(135deg, #ebfff7 0%, #def7f0 100%);
        padding: 0.78rem 1rem;
        color: #0f6648;
        font-size: 0.88rem;
        box-shadow: 0 6px 16px rgba(12, 115, 80, 0.12);
    }

    .ready-pickup-alert strong {
        font-weight: 700;
    }

    .ready-pickup-alert a {
        color: #0f6648;
        font-weight: 700;
        text-decoration: underline;
    }

    .summary-card .label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .summary-card .value {
        margin-top: 0.55rem;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .summary-card .note {
        margin-top: 0.42rem;
        color: #7f96ae;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .recent-orders {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        overflow: hidden;
    }

    .recent-orders-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 0.95rem 1.05rem;
        border-bottom: 1px solid #dde6ef;
    }

    .recent-orders-head h2 {
        margin: 0;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .btn-create-order {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid #c8a949;
        background: #eccc6a;
        color: #060607;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.56rem 1.1rem;
    }

    .btn-create-order:hover {
        background: #dfbf65;
        border-color: #dfbf65;
    }

    .recent-orders-wrap {
        overflow-x: auto;
    }

    .recent-orders-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 780px;
    }

    .recent-orders-table thead {
        background: #e9eef4;
    }

    .recent-orders-table th {
        padding: 0.72rem 0.85rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.72rem;
        color: #768ea7;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #dbe4ed;
    }

    .recent-orders-table td {
        padding: 0.76rem 0.85rem;
        border-bottom: 1px solid #edf2f7;
        color: #1d3548;
        font-size: 0.78rem;
    }

    .recent-orders-table tbody tr:hover {
        background: #fbfdff;
    }

    .table-empty {
        text-align: center;
        color: #7e96ae;
        font-size: 0.84rem;
        padding: 1rem 0.85rem;
    }

    .table-empty a {
        color: #c2a042;
        font-weight: 700;
    }

    .col-product {
        max-width: 220px;
    }

    .col-product span {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .action-link {
        color: #0f7b8f;
        font-weight: 700;
        text-decoration: none;
    }

    .action-link:hover {
        text-decoration: underline;
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

    .payment-cell {
        display: grid;
        gap: 0.35rem;
    }

    .payment-cell-center {
        align-items: center;
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
        font-size: 0.76rem;
        color: #6f87a1;
        font-weight: 600;
    }

    .payment-waiting-note {
        color: #d51919;
        font-weight: 700;
    }

    .status-head {
        text-align: center !important;
    }

    .status-cell {
        text-align: center !important;
        white-space: nowrap;
    }

    .status-cell .status-pill {
        margin-inline: auto;
    }

    @media (max-width: 980px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .customer-dashboard {
            padding: 1rem;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .recent-orders-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-create-order {
            width: 100%;
        }
    }
</style>

<section class="customer-dashboard">
    <div class="customer-greeting">
        <h1>Halo, {{ strtolower(auth()->user()->name) }}! 👋</h1>
        <p>Pantau pesanan dan mulai order custom Anda dari sini</p>
    </div>

    @if ($dueWaitingPaymentOrder)
        @php
            $dueWaitingPayment = $dueWaitingPaymentOrder->payments
                ->sortByDesc('id')
                ->first(static function ($payment) use ($dueWaitingPaymentOrder): bool {
                    if ($payment->status !== 'pending') {
                        return false;
                    }

                    if (in_array($payment->midtrans_status, ['settlement', 'capture'], true)) {
                        return false;
                    }

                    if ($payment->method === 'settlement') {
                        return $dueWaitingPaymentOrder->order_status === 'finishing_waiting_settlement';
                    }

                    return (string) ($dueWaitingPaymentOrder->admin_verification_status ?? 'pending') === 'verified';
                });
        @endphp
        @if ($dueWaitingPayment)
            <div class="due-payment-alert">
                <span>🔔</span>
                <span>
                    @if (($waitingPaymentAlertCount ?? 0) > 1)
                        Ada <b>{{ $waitingPaymentAlertCount }}</b> pesanan yang <b>Menunggu Pembayaran</b>. Contoh: <b>{{ $dueWaitingPaymentOrder->order_code }}</b>.
                    @else
                        Pesanan <b>{{ $dueWaitingPaymentOrder->order_code }}</b> sudah <b>Terverifikasi</b> dan <b>Menunggu Pembayaran</b>.
                    @endif
                </span>
                <a href="{{ route('customer.orders.payments.edit', [$dueWaitingPaymentOrder, $dueWaitingPayment]) }}">Lanjut Pembayaran</a>
            </div>
        @endif
    @endif

    @if ($dueRevisionOrder)
        <div class="due-revision-alert">
            <span>⚠️</span>
            <span>Pesanan <b>{{ $dueRevisionOrder->order_code }}</b> sedang <b>Menunggu Persetujuan Perubahan</b> dari Anda.</span>
            <a href="{{ route('customer.orders.show', $dueRevisionOrder) }}">Tinjau Pesanan</a>
        </div>
    @endif

    @if ($dueSettlementOrder)
        @php
            $pendingSettlementPayment = $dueSettlementOrder->payments
                ->sortByDesc('id')
                ->first(static function ($payment) use ($dueSettlementOrder): bool {
                    if ($payment->status !== 'pending' || $payment->method !== 'settlement') {
                        return false;
                    }

                    if (in_array($payment->midtrans_status, ['settlement', 'capture'], true)) {
                        return false;
                    }

                    return $dueSettlementOrder->order_status === 'finishing_waiting_settlement';
                });
        @endphp
        <div class="due-settlement-alert">
            <span>🔔</span>
            <span>
                @if (($settlementAlertCount ?? 0) > 1)
                    Ada <b>{{ $settlementAlertCount }}</b> pesanan yang <b>Menunggu Pelunasan</b>. Contoh: <b>{{ $dueSettlementOrder->order_code }}</b>.
                @else
                    Pesanan <b>{{ $dueSettlementOrder->order_code }}</b> sudah masuk tahap finishing dan <b>Menunggu Pelunasan</b>.
                @endif
            </span>
            @if ($pendingSettlementPayment)
                <a href="{{ route('customer.orders.payments.edit', [$dueSettlementOrder, $pendingSettlementPayment]) }}">Lanjut Pelunasan</a>
            @else
                <form method="POST" action="{{ route('customer.orders.settlement', $dueSettlementOrder) }}" style="margin:0;">
                    @csrf
                    <button type="submit" style="border:none; background:none; color:#b63b22; font-weight:700; text-decoration:underline; cursor:pointer; padding:0;">Lanjut Pelunasan</button>
                </form>
            @endif
        </div>
    @endif

    @if ($readyPickupOrder)
        <div class="ready-pickup-alert">
            <span>✅</span>
            <span>
                @if (($readyPickupAlertCount ?? 0) > 1)
                    Ada <strong>{{ $readyPickupAlertCount }}</strong> pesanan siap ambil. Produksi selesai. Pesanan Anda telah melalui pengecekan akhir dan siap untuk diambil.
                @else
                    Produksi selesai. Pesanan Anda telah melalui pengecekan akhir dan siap untuk diambil. <strong>{{ $readyPickupOrder->order_code }}</strong> sudah siap.
                @endif
            </span>
            <a href="{{ route('customer.orders.index', ['focus' => 'status']) }}">Lihat Status Produksi</a>
        </div>
    @endif

    <div class="summary-grid">
        <article class="summary-card orders">
            <div class="label">Total Pesanan</div>
            <div class="value">{{ $totalOrders }}</div>
            <div class="note">Semua pesanan</div>
        </article>
        <article class="summary-card active">
            <div class="label">Dalam Produksi</div>
            <div class="value">{{ $inProgressOrders }}</div>
            <div class="note">Sedang diproses tim produksi</div>
        </article>
        <article class="summary-card pending">
            <div class="label">Menunggu Verifikasi</div>
            <div class="value">{{ $waitingPaymentOrders }}</div>
            <div class="note">Proses review admin</div>
        </article>
        <article class="summary-card completed">
            <div class="label">Pesanan Selesai</div>
            <div class="value">{{ $completedOrders ?? 0 }}</div>
            <div class="note">Pesanan Sudah Selesai</div>
        </article>
    </div>

    <section class="recent-orders">
        <div class="recent-orders-head">
            <h2>Pesanan Terbaru</h2>
            <a class="btn-create-order" href="{{ route('customer.orders.create') }}">Buat Pesanan Baru</a>
        </div>

        <div class="recent-orders-wrap">
            <table class="recent-orders-table">
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th class="col-product">Produk</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th class="status-head">Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeRecentOrders as $order)
                        @php
                            $latestPayment = $order->payments->sortByDesc('id')->first();
                            $latestVerifiedPayment = $order->payments
                                ->where('status', 'verified')
                                ->sortByDesc(fn ($payment) => $payment->verified_at ?? $payment->updated_at ?? $payment->created_at)
                                ->first();
                            $statusForDisplay = $resolveOrderStatus($order, $latestPayment);
                            $isAdminVerified = (string) ($order->admin_verification_status ?? 'pending') === 'verified';
                            $isWaitingPayment = $statusForDisplay === 'admin_verified_waiting_payment';
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
                            $displayPayment = $latestVerifiedPayment ?: $latestPayment;
                            $paymentMethod = $displayPayment?->method ?: ($order->payment_type ?? null);

                            $paymentMethodClass = match ($paymentMethod) {
                                'dp' => 'payment-pill-dp',
                                'full' => 'payment-pill-full',
                                'settlement' => 'payment-pill-settlement',
                                default => 'payment-pill-dp',
                            };

                            $paymentMethodLabel = match ($paymentMethod) {
                                'dp' => 'DP 50%',
                                'full' => 'Lunas Awal',
                                'settlement' => 'Pelunasan',
                                default => strtoupper((string) $paymentMethod),
                            };
                        @endphp
                        <tr>
                            <td>{{ $order->order_code }}</td>
                            <td class="col-product"><span>{{ $order->product_name ?: '-' }}</span></td>
                            <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                            <td>
                                <div class="payment-cell">
                                    @if (! $isAdminVerified)
                                        <span class="payment-amount-note">Menunggu verifikasi admin</span>
                                    @elseif ($latestVerifiedPayment)
                                        <span class="payment-pill {{ $paymentMethodClass }}">{{ $paymentMethodLabel }}</span>
                                        @if ((string) $paymentMethod === 'dp')
                                            <span class="payment-amount-note">DP Rp {{ number_format((float) $order->dp_amount, 0, ',', '.') }}</span>
                                            <span class="payment-amount-note">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</span>
                                        @elseif ((string) $paymentMethod === 'settlement')
                                            <span class="payment-amount-note">Pelunasan Rp {{ number_format((float) ($displayPayment->amount ?? $order->remaining_amount), 0, ',', '.') }}</span>
                                            <span class="payment-amount-note">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="payment-amount-note">Lunas Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                                            <span class="payment-amount-note">Sudah lunas</span>
                                        @endif
                                    @elseif ($isWaitingPayment)
                                        <span class="payment-amount-note payment-waiting-note">Menunggu Pembayaran</span>
                                        <span class="payment-amount-note">Silakan lanjut pembayaran</span>
                                    @else
                                        <span class="payment-pill {{ $paymentMethodClass }}">{{ $paymentMethodLabel }}</span>
                                        @if ((float) $order->remaining_amount > 0)
                                            <span class="payment-amount-note">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="payment-amount-note">Sudah lunas</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="status-cell">
                                <span class="status-pill {{ $statusClass($statusForDisplay) }}">{{ $statusLabel($statusForDisplay) }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a class="action-btn action-btn-outline" href="{{ route('customer.orders.show', $order) }}">Detail</a>
                                    @if ($isWaitingPayment && $pendingActionPayment)
                                        <a class="action-btn action-btn-primary" href="{{ route('customer.orders.payments.edit', [$order, $pendingActionPayment]) }}">Lanjut Pembayaran</a>
                                    @elseif ($pendingActionPayment && $pendingActionPayment->method === 'settlement')
                                        <a class="action-btn action-btn-primary" href="{{ route('customer.orders.payments.edit', [$order, $pendingActionPayment]) }}">Lanjut Pelunasan</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">Belum ada pesanan berjalan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($completedRecentOrders->isNotEmpty())
        <section class="recent-orders" style="margin-top: 1rem;">
            <div class="recent-orders-head">
                <h2>Pesanan Selesai</h2>
                <a class="btn-create-order" href="{{ route('customer.orders.index') }}">Lihat Semua Riwayat</a>
            </div>

            <div class="recent-orders-wrap">
                <table class="recent-orders-table">
                    <thead>
                        <tr>
                            <th>No. Order</th>
                            <th class="col-product">Produk</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th class="status-head">Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($completedRecentOrders as $order)
                            @php
                                $latestPayment = $order->payments->sortByDesc('id')->first();
                                $latestVerifiedPayment = $order->payments
                                    ->where('status', 'verified')
                                    ->sortByDesc(fn ($payment) => $payment->verified_at ?? $payment->updated_at ?? $payment->created_at)
                                    ->first();
                                $statusForDisplay = $resolveOrderStatus($order, $latestPayment);
                                $displayPayment = $latestVerifiedPayment ?: $latestPayment;
                                $paymentMethod = $displayPayment?->method ?: ($order->payment_type ?? null);

                                $paymentMethodClass = match ($paymentMethod) {
                                    'dp' => 'payment-pill-dp',
                                    'full' => 'payment-pill-full',
                                    'settlement' => 'payment-pill-settlement',
                                    default => 'payment-pill-dp',
                                };

                                $paymentMethodLabel = match ($paymentMethod) {
                                    'dp' => 'DP 50%',
                                    'full' => 'Lunas Awal',
                                    'settlement' => 'Pelunasan',
                                    default => strtoupper((string) $paymentMethod),
                                };
                            @endphp
                            <tr>
                                <td>{{ $order->order_code }}</td>
                                <td class="col-product"><span>{{ $order->product_name ?: '-' }}</span></td>
                                <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <div class="payment-cell">
                                        <span class="payment-pill {{ $paymentMethodClass }}">{{ $paymentMethodLabel }}</span>
                                        <span class="payment-amount-note">Rp {{ number_format((float) ($displayPayment->amount ?? $order->subtotal), 0, ',', '.') }}</span>
                                        <span class="payment-amount-note">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="status-cell">
                                    <span class="status-pill {{ $statusClass($statusForDisplay) }}">{{ $statusLabel($statusForDisplay) }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="action-btn action-btn-outline" href="{{ route('customer.orders.show', $order) }}">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</section>
@endsection
