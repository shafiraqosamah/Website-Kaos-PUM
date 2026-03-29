@extends('layouts.app')

@section('content')
@php
    $statusClass = static function (string $status): string {
        return match ($status) {
            'submitted', 'pending_verification', 'verified_payment', 'verified_dp' => 'status-warning',
            'in_production' => 'status-info',
            'finishing_waiting_settlement' => 'status-accent',
            'completed' => 'status-success',
            'rejected' => 'status-danger',
            default => 'status-neutral',
        };
    };

    $statusLabel = static function (string $status): string {
        return match ($status) {
            'submitted', 'pending_verification' => 'Menunggu Verifikasi',
            'verified_payment', 'verified_dp' => 'Menunggu Produksi',
            'in_production' => 'Sedang Proses',
            'finishing_waiting_settlement' => 'Menunggu Bayar',
            'completed' => 'Selesai',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    };
@endphp

<style>
    .customer-dashboard {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.25rem 1.35rem 1.35rem;
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
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.95rem;
        margin-bottom: 1.2rem;
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

    .due-payment-alert strong {
        font-weight: 700;
    }

    .due-payment-alert a {
        color: #b63b22;
        font-weight: 700;
        text-decoration: underline;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
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
        background: #c8a949;
        color: #0f2947;
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
        font-size: 0.76rem;
        color: #6f87a1;
        font-weight: 600;
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

    @if ($duePaymentOrder)
        <div class="due-payment-alert">
            <span>🔔</span>
            <span> Pesanan <b>{{ $duePaymentOrder->order_code }}</b> memasuki tahap Finishing. Segera lakukan pelunasan sebesar <b>Rp {{ number_format((float) $duePaymentOrder->remaining_amount, 0, ',', '.') }}</b> agar produksi dapat diselesaikan.</span>
            <a href="{{ route('customer.orders.index', ['focus' => 'status']) }}">Lihat Detail →</a>
        </div>
    @endif

    <div class="summary-grid">
        <article class="summary-card">
            <div class="label">Total Pesanan</div>
            <div class="value">{{ $totalOrders }}</div>
            <div class="note">Semua pesanan</div>
        </article>
        <article class="summary-card">
            <div class="label">Sedang Proses</div>
            <div class="value">{{ $inProgressOrders }}</div>
            <div class="note">Dalam produksi</div>
        </article>
        <article class="summary-card">
            <div class="label">Menunggu Bayar</div>
            <div class="value">{{ $waitingPaymentOrders }}</div>
            <div class="note">Perlu tindakan</div>
        </article>
    </div>

    <section class="recent-orders">
        <div class="recent-orders-head">
            <h2>Pesanan Terbaru</h2>
            <a class="btn-create-order" href="{{ route('customer.orders.create') }}">+ Buat Pesanan Baru</a>
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
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    @php
                        $lastPayment = $order->payments->sortByDesc('id')->first();
                        $statusForDisplay = ($lastPayment?->status === 'rejected')
                            ? 'rejected'
                            : $order->order_status;
                        $paymentMethod = $lastPayment?->method ?: $order->payment_type;
                        $paymentMethodClass = match ($paymentMethod) {
                            'dp' => 'payment-pill-dp',
                            'full' => 'payment-pill-full',
                            'settlement' => 'payment-pill-settlement',
                            default => 'payment-pill-dp',
                        };
                        $paymentMethodLabel = match ($paymentMethod) {
                            'dp' => 'DP Terbayar',
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
                                @if ((float) $order->remaining_amount > 0)
                                    <span class="payment-amount-note">Sisa Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="payment-amount-note">Sudah lunas</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="status-pill {{ $statusClass($statusForDisplay) }}">{{ $statusLabel($statusForDisplay) }}</span>
                        </td>
                        <td><a class="action-link" href="{{ route('customer.orders.show', $order) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="table-empty">
                            Belum ada pesanan. <a href="{{ route('customer.orders.create') }}">Buat pesanan pertama Anda →</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
