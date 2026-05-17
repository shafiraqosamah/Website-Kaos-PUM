@extends('layouts.app')

@section('header_title', 'Laporan Pemesanan')

@section('content')
<style>
    .report-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem;
    }

    .report-head {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .report-head h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .report-head p {
        margin: 0.45rem 0 0;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .filter-actions {
        display: flex;
        gap: 0.8rem;
        align-items: end;
        flex-wrap: wrap;
    }

    .month-form {
        display: flex;
        gap: 0.6rem;
        align-items: end;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.95rem;
        margin-top: 1rem;
    }

    .stats-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 6px 16px rgba(13, 39, 73, 0.05);
    }

    .stats-card.total { border-top-color: #2c7ebe; }
    .stats-card.verified { border-top-color: #0f8f60; }
    .stats-card.revision { border-top-color: #cf3c2c; }

    .stats-label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .stats-value {
        margin-top: 0.55rem;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-weight: 700;
    }

    .report-table-card {
        margin-top: 1.5rem;
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        overflow: auto;
        background: #ffffff;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .report-table thead {
        background: #e9eef4;
    }

    .report-table th {
        padding: 0.72rem 0.85rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.72rem;
        color: #768ea7;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #dbe4ed;
    }

    .report-table td {
        padding: 0.76rem 0.85rem;
        border-bottom: 1px solid #edf2f7;
        color: #1d3548;
        font-size: 0.78rem;
    }

    @media print {
        body { background: #fff; }
        .customer-shell-topbar, .customer-sidebar, .filter-actions {
            display: none !important;
        }
        .layout-auth.customer-layout { padding-left: 0 !important; }
        .customer-content { padding: 0 !important; margin: 0 !important; }
        .report-page { border: none; padding: 0; box-shadow: none; }
        .report-table-card { border: none; }
        .stats-card { border: 1px solid #000; box-shadow: none; }
    }
</style>

<section class="report-page">
    <div class="report-head">
        <div>
            <h1>Laporan Pemesanan Pelanggan</h1>
            <p>Rekap data pesanan pada periode {{ $monthLabel }}</p>
        </div>
        <div class="filter-actions">
            <form method="GET" action="{{ route('reports.orders-report') }}" class="month-form">
                <div>
                    <label for="month" style="margin-bottom:0.2rem; display:block; font-size:0.8rem;">Pilih Bulan</label>
                    <input id="month" type="month" name="month" value="{{ $monthInput }}">
                </div>
                <button class="btn btn-brand" type="submit">Tampilkan</button>
                <a href="{{ route('reports.orders-report.export', ['month' => $monthInput]) }}" style="align-self:end; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; background-color:#1e7e34; color:#ffffff; border:1px solid #1c7430; padding:0.4rem 0.6rem; border-radius:4px; font-size:0.85rem; font-weight:600;">
                    ⇩ Excel
                </a>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stats-card total">
            <div class="stats-label">Jumlah Pesanan</div>
            <div class="stats-value">{{ number_format($orderCount, 0, ',', '.') }}</div>
        </div>
        <div class="stats-card verified">
            <div class="stats-label">Terverifikasi</div>
            <div class="stats-value">{{ number_format($verifiedCount, 0, ',', '.') }}</div>
        </div>
        <div class="stats-card revision">
            <div class="stats-label">Pengajuan Kembali</div>
            <div class="stats-value">{{ number_format($revisionRequestedCount, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="report-table-card">
        <table class="report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Order</th>
                    <th>Pemesan</th>
                    <th>Tanggal Pesan</th>
                    <th>Jumlah PCS</th>
                    <th>Total Harga</th>
                    <th>Produk</th>
                    <th>Status Produksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $resolvedStatus = \App\Support\OrderStatusPresenter::resolveForCustomer($order, $order->payments->last());
                        $statusClass = \App\Support\OrderStatusPresenter::customerClass($resolvedStatus);
                        $statusLabel = \App\Support\OrderStatusPresenter::customerLabel($resolvedStatus);
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $order->order_code }}</strong></td>
                        <td>{{ $order->customer_name ?: ($order->user->name ?? '-') }}</td>
                        <td>{{ $order->created_at?->format('d M Y') }}</td>
                        <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }} pcs</td>
                        <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                        <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                        <td><span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:2rem 1rem; color:#7f96ae;">Belum ada data pesanan pada bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
