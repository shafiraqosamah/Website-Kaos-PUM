@extends('layouts.app')

@section('content')
<style>
    .report-kpi {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .report-kpi .item {
        border: 1px solid #d7e3ed;
        border-radius: 12px;
        background: #fff;
        padding: 0.8rem 0.9rem;
    }

    .report-kpi .item .v {
        margin-top: 0.25rem;
        font-size: 1.45rem;
        line-height: 1;
        font-family: 'Sora', sans-serif;
    }

    .ok {
        color: #176841;
    }

    .bad {
        color: #a11d1d;
    }

    .section-title {
        margin-top: 0;
        margin-bottom: 0.65rem;
    }

    @media (max-width: 1100px) {
        .report-kpi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .report-kpi {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1>Laporan Bulanan</h1>
            <p class="muted" style="margin:0;">Rekap lintas modul Pesanan, Keuangan, dan Produksi untuk bulan {{ $monthLabel }}.</p>
        </div>
        <form method="GET" action="{{ route('reports.monthly') }}" style="display:flex; gap:0.6rem; align-items:end;">
            <div>
                <label for="month" style="margin-bottom:0.2rem;">Pilih Bulan</label>
                <input id="month" type="month" name="month" value="{{ $monthInput }}">
            </div>
            <button class="btn btn-brand" type="submit">Tampilkan</button>
        </form>
    </div>

    <div class="report-kpi">
        <div class="item">
            <div class="muted">Total Order</div>
            <div class="v">{{ $orderCount }}</div>
        </div>
        <div class="item">
            <div class="muted">Total Nilai Pesanan</div>
            <div class="v">Rp {{ number_format($orderSubtotal, 0, ',', '.') }}</div>
        </div>
        <div class="item">
            <div class="muted">Total PCS Dipesan</div>
            <div class="v">{{ number_format($totalPcs, 0, ',', '.') }}</div>
        </div>
        <div class="item">
            <div class="muted">Produksi Selesai</div>
            <div class="v">{{ $completedInMonth }}</div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-top:1rem;">
    <div class="card">
        <h3 class="section-title">Rekap Keuangan Bulanan</h3>
        <table>
            <tr><th>Total Order Bulan Ini</th><td>Rp {{ number_format($orderSubtotal, 0, ',', '.') }}</td></tr>
            <tr><th>Total Pembayaran TERVERIFIKASI</th><td>Rp {{ number_format($verifiedPaymentsTotal, 0, ',', '.') }}</td></tr>
            <tr><th>Pembayaran Pending</th><td>{{ $pendingPaymentsCount }}</td></tr>
            <tr><th>Pembayaran Ditolak</th><td>{{ $rejectedPaymentsCount }}</td></tr>
            <tr>
                <th>Status Balance</th>
                <td class="{{ abs($balanceGap) < 0.01 ? 'ok' : 'bad' }}">
                    {{ abs($balanceGap) < 0.01 ? 'BALANCE' : 'TIDAK BALANCE' }}
                    (Selisih Rp {{ number_format(abs($balanceGap), 0, ',', '.') }})
                </td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h3 class="section-title">Rekap Produksi Bulanan</h3>
        <table>
            <tr><th>Order Selesai</th><td>{{ $completedInMonth }}</td></tr>
            <tr><th>Total PCS Diproduksi (selesai)</th><td>{{ number_format($producedPcsInMonth, 0, ',', '.') }} pcs</td></tr>
        </table>

        <h4 style="margin:0.9rem 0 0.4rem;">Breakdown Jenis Produksi</h4>
        <table>
            <thead>
                <tr><th>Jenis</th><th>No.Order</th><th>Total PCS</th></tr>
            </thead>
            <tbody>
                @forelse($productionByType as $row)
                    <tr>
                        <td>{{ $row->production_type ?: '-' }}</td>
                        <td>{{ $row->total_orders }}</td>
                        <td>{{ number_format((int) $row->total_pcs, 0, ',', '.') }} pcs</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">Belum ada data produksi pada bulan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3 class="section-title">Detail Pesanan Bulanan</h3>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Spesifikasi</th>
                <th>Total PCS</th>
                <th>Subtotal</th>
                <th>Status Order</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_code }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->product_model ?? '-' }} | {{ $order->production_type ?? '-' }} | {{ $order->fabric }}</td>
                    <td>{{ $order->total_pcs }}</td>
                    <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                    <td>{{ str_replace('_', ' ', $order->order_status) }}</td>
                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Tidak ada pesanan pada bulan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3 class="section-title">Detail Keseimbangan Finance per Order</h3>
    <table>
        <thead>
            <tr>
                <th>No.Order</th>
                <th>Pelanggan</th>
                <th>Subtotal</th>
                <th>Verified</th>
                <th>Pending</th>
                <th>Rejected</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($financePerOrder as $row)
                <tr>
                    <td>{{ $row['order_code'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['verified'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['pending'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['rejected'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['remaining_amount'], 0, ',', '.') }}</td>
                    <td class="{{ $row['is_balanced'] ? 'ok' : 'bad' }}">{{ $row['is_balanced'] ? 'BALANCE' : 'BELUM BALANCE' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Tidak ada data keuangan per order pada bulan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
