@extends('layouts.app')

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
 
    .month-form input[type="month"] {
        padding: 0.45rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.88rem;
        outline: none;
        height: 38px;
        box-sizing: border-box;
        background: #fff;
    }
 
    .month-form button.btn-brand {
        background: #004b8f;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 0 1.2rem;
        height: 38px;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        transition: background 0.2s;
    }
 
    .month-form button.btn-brand:hover {
        background: #003666;
    }
 
    .month-form .btn-excel {
        background-color: #1e7e34;
        color: #ffffff;
        border: 1px solid #1c7430;
        padding: 0 1.2rem;
        border-radius: 6px;
        font-size: 0.88rem;
        font-weight: 600;
        text-decoration: none;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        transition: background-color 0.2s;
    }
 
    .month-form .btn-excel:hover {
        background-color: #19692c;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.95rem;
        margin-top: 1rem;
    }
 
    @media (max-width: 980px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
 
    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stats-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 6px 16px rgba(13, 39, 73, 0.05);
        text-align: center;
    }

    .stats-card.total { border-top-color: #2c7ebe; }
    .stats-card.verified { border-top-color: #0f8f60; }
    .stats-card.revision { border-top-color: #cf3c2c; }
    .stats-card.pending { border-top-color: #e0b024; }
    .stats-card.surplus { border-top-color: #7b61ff; }

    .stats-label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }
 
    .info-tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: help;
        color: #899eb5;
        font-size: 0.72rem;
        background: #f1f5f9;
        border-radius: 999px;
        width: 14px;
        height: 14px;
        font-weight: bold;
        font-family: inherit;
        text-transform: none;
    }
 
    .info-tooltip .tooltip-text {
        visibility: hidden;
        width: 240px;
        background-color: #0d2749;
        color: #fff;
        text-align: left;
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
        position: absolute;
        z-index: 100;
        bottom: 140%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        font-size: 0.75rem;
        line-height: 1.4;
        font-weight: 500;
        box-shadow: 0 8px 24px rgba(13, 39, 73, 0.15);
        font-family: 'DM Sans', sans-serif;
        text-transform: none;
        letter-spacing: normal;
        border: 1px solid #1e3d64;
    }
 
    .info-tooltip .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #0d2749 transparent transparent transparent;
    }
 
    .info-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
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
        aside, .filter-actions {
            display: none !important;
        }
        .main-content { padding-left: 0 !important; margin: 0 !important; }
        .report-page { border: none; padding: 0; box-shadow: none; }
        .report-table-card { border: none; }
        .stats-card { border: 1px solid #000; box-shadow: none; }
    }
</style>

<section class="report-page">
    <div class="report-head">
        <div>
            <h1>Laporan Keuangan (Pembukuan)</h1>
            <p>Pembukuan transaksi masuk, status verifikasi, dan piutang pada {{ $monthLabel }}.</p>
        </div>
        <div class="filter-actions">
             <form method="GET" action="{{ route('reports.finance') }}" class="month-form">
                <div>
                    <label for="month" style="margin-bottom:0.32rem; display:block; font-size:0.8rem; font-weight:700; color:#0d2749;">Pilih Bulan</label>
                    <input id="month" type="month" name="month" value="{{ $monthInput }}">
                </div>
                <button class="btn btn-brand" type="submit">Tampilkan</button>
                <a href="{{ route('reports.finance.export', ['month' => $monthInput]) }}" class="btn-excel">
                    ⇩ Excel
                </a>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stats-card total">
            <div class="stats-label">
                <span>Tagihan Pesanan</span>
                <span class="info-tooltip">i
                    <span class="tooltip-text">Total subtotal seluruh pesanan baru yang dibuat pelanggan pada bulan ini.</span>
                </span>
            </div>
            <div class="stats-value">Rp {{ number_format($ledgerSummary['order_subtotal'], 0, ',', '.') }}</div>
        </div>
        <div class="stats-card verified">
            <div class="stats-label">
                <span>Uang Masuk</span>
                <span class="info-tooltip">i
                    <span class="tooltip-text">Total pembayaran riil yang sudah sukses diterima dan terverifikasi lunas (DP/Pelunasan) di bulan ini.</span>
                </span>
            </div>
            <div class="stats-value">Rp {{ number_format($ledgerSummary['verified_total'], 0, ',', '.') }}</div>
        </div>
        <div class="stats-card revision">
            <div class="stats-label">
                <span>Piutang Berjalan</span>
                <span class="info-tooltip">i
                    <span class="tooltip-text">Sisa tagihan pesanan bulan ini yang belum terbayar oleh pelanggan (Nilai Pesanan dikurangi Uang Masuk).</span>
                </span>
            </div>
            <div class="stats-value">Rp {{ number_format($ledgerSummary['receivable'], 0, ',', '.') }}</div>
        </div>
        <div class="stats-card pending">
            <div class="stats-label">
                <span>Pending</span>
                <span class="info-tooltip">i
                    <span class="tooltip-text">Total transaksi pembayaran bulan ini yang masih menunggu pembayaran (belum ditransfer oleh pelanggan).</span>
                </span>
            </div>
            <div class="stats-value">Rp {{ number_format($ledgerSummary['pending_total'], 0, ',', '.') }}</div>
        </div>
        <div class="stats-card revision">
            <div class="stats-label">
                <span>Rejected</span>
                <span class="info-tooltip">i
                    <span class="tooltip-text">Total transaksi pembayaran bulan ini yang gagal, dibatalkan, atau kedaluwarsa batas waktunya.</span>
                </span>
            </div>
            <div class="stats-value">Rp {{ number_format($ledgerSummary['rejected_total'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="report-table-card">
        <table class="report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Pesanan</th>
                    <th>Qty</th>
                    <th>Harga @</th>
                    <th>Total</th>
                    <th>Masuk</th>
                    <th>DP</th>
                    <th>Tgl DP</th>
                    <th>Pelunasan</th>
                    <th>Tgl Lunas</th>
                    <th>Lunas Awal</th>
                    <th>Tgl Lunas Awal</th>
                    <th>Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgerByOrder as $index => $group)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $group['customer_name'] }}</td>
                        <td>
                            <div><strong>{{ $group['order_code'] }}</strong></div>
                            <div style="color:#728da8; font-size:0.7rem; margin-top:0.15rem;">{{ $group['product'] }}</div>
                        </td>
                        <td>{{ number_format($group['qty'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($group['unit_price'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($group['order_subtotal'], 0, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($group['verified_total'], 0, ',', '.') }}</strong></td>
                        <td>Rp {{ number_format($group['dp_verified'], 0, ',', '.') }}</td>
                        <td style="font-size:0.7rem;">{{ $group['dp_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>Rp {{ number_format($group['settlement_verified'], 0, ',', '.') }}</td>
                        <td style="font-size:0.7rem;">{{ $group['settlement_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>Rp {{ number_format($group['full_verified'], 0, ',', '.') }}</td>
                        <td style="font-size:0.7rem;">{{ $group['full_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>Rp {{ number_format($group['remaining'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" style="text-align:center; padding:2rem 1rem; color:#7f96ae;">Belum ada data omset pada bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@endsection
