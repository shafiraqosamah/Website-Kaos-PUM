@extends('layouts.app')

@section('header_title', 'Laporan Produksi')

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
    .stats-card.pending { border-top-color: #e0b024; }
    .stats-card.surplus { border-top-color: #7b61ff; }

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
        text-align: center;
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
        text-align: center;
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
            <h1>Laporan Produksi Bulanan</h1>
            <p>Laporan operasional produksi tanpa data keuangan untuk periode {{ $monthLabel }}.</p>
        </div>
        <div class="filter-actions">
            <form method="GET" action="{{ route('reports.production') }}" class="month-form">
                <div>
                    <label for="month" style="margin-bottom:0.32rem; display:block; font-size:0.8rem; font-weight:700; color:#0d2749;">Pilih Bulan</label>
                    <input id="month" type="month" name="month" value="{{ $monthInput }}">
                </div>
                <button class="btn btn-brand" type="submit">Tampilkan</button>
                <a href="{{ route('reports.production.export', ['month' => $monthInput]) }}" class="btn-excel">
                    ⇩ Excel
                </a>
            </form>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stats-card total">
            <div class="stats-label">Order Selesai</div>
            <div class="stats-value">{{ $completedInMonth }}</div>
        </div>
        <div class="stats-card verified">
            <div class="stats-label">Total PCS Selesai</div>
            <div class="stats-value">{{ number_format($producedPcsInMonth, 0, ',', '.') }}</div>
        </div>
        <div class="stats-card pending">
            <div class="stats-label">Order Dipantau</div>
            <div class="stats-value">{{ $productionRows->count() }}</div>
        </div>
    </div>

    <div class="grid grid-2" style="margin-top:1.5rem; gap:1.5rem;">
        <div class="report-table-card" style="margin-top:0;">
            <h3 style="margin:0; padding:1.2rem 1.2rem 0; font-size:1.1rem; color:#0d2749;">Ringkasan Jenis Produksi</h3>
            <table class="report-table" style="min-width:unset; margin-top:0.8rem;">
                <thead>
                    <tr>
                        <th>Jenis Produksi</th>
                        <th>Produk</th>
                        <th>NoOrder</th>
                        <th>Total PCS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productionByType as $row)
                        <tr>
                            <td>{{ $row['production_type'] ?: '-' }}</td>
                            <td>{{ $row['product_model'] ?? '-' }}</td>
                            <td>{{ $row['total_orders'] }}</td>
                            <td>{{ number_format($row['total_pcs'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center; padding:2rem 1rem; color:#7f96ae;">Belum ada data produksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-table-card" style="margin-top:0;">
            <h3 style="margin:0; padding:1.2rem 1.2rem 0; font-size:1.1rem; color:#0d2749;">Progress Tiap Pesanan</h3>
            <table class="report-table" style="min-width:unset; margin-top:0.8rem;">
                <thead>
                    <tr>
                        <th>No.Order</th>
                        <th>No SPK</th>
                        <th>Pelanggan</th>
                        <th>Produk / Jenis</th>
                        <th>Tgl Pesan</th>
                        <th>Tgl Selesai</th>
                        <th>Status</th>
                        <th>Dokumen SPK</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productionRows as $row)
                        <tr>
                            <td><strong>{{ $row['order_code'] }}</strong><br><span style="color:#728da8; font-size:0.7rem;">{{ number_format($row['total_pcs'], 0, ',', '.') }} pcs</span></td>
                            <td style="font-weight: 600;">{{ $row['spk_number'] ?? '-' }}</td>
                            <td>{{ $row['customer_name'] }}</td>
                            <td>{{ $row['product_name'] ?: '-' }}<br><span style="color:#728da8; font-size:0.7rem;">{{ $row['production_type'] ?: '-' }}</span></td>
                            <td style="font-size:0.75rem;">{{ $row['created_at'] ? \Carbon\Carbon::parse($row['created_at'])->format('d M Y') : '-' }}</td>
                            <td style="font-size:0.75rem;">{{ $row['finished_at'] ? \Carbon\Carbon::parse($row['finished_at'])->format('d M Y') : '-' }}</td>
                            <td style="font-size:0.75rem;"><strong>{{ str_replace('_', ' ', $row['order_status']) }}</strong><br><span style="color:#728da8; font-size:0.7rem;">{{ $row['step_progress'] }}</span></td>
                            <td>
                                @if($row['spk_number'])
                                    <a class="btn btn-xs" href="{{ route('production.spk', ['order' => $row['id']]) }}" target="_blank" style="background-color:#ffffff; color:#0d2749; border:1px solid #0d2749; padding:0.25rem 0.65rem; border-radius:4px; font-size:0.75rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; height:24px; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.backgroundColor='#0d2749'; this.style.color='#ffffff';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#0d2749';">Lihat SPK</a>
                                @else
                                    <span style="color: #a0aec0; font-size: 0.75rem;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; padding:2rem 1rem; color:#7f96ae;">Belum ada order untuk dipantau pada bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
