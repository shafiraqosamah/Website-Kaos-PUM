@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1>Laporan Manajemen Terpadu</h1>
            <p class="muted" style="margin:0;">Akses penuh manager/owner untuk ringkasan pemesanan, keuangan, dan produksi dalam satu halaman periode {{ $monthLabel }}.</p>
        </div>
        <form method="GET" action="{{ route('reports.executive') }}" style="display:flex; gap:0.6rem; align-items:end;">
            <div>
                <label for="month" style="margin-bottom:0.2rem;">Pilih Bulan</label>
                <input id="month" type="month" name="month" value="{{ $monthInput }}">
            </div>
            <button class="btn btn-brand" type="submit">Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-3" style="margin-top:1rem;">
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Nilai Pesanan</div>
            <div class="metric">Rp {{ number_format($orderSubtotal, 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Kas Masuk Verified</div>
            <div class="metric">Rp {{ number_format($ledgerSummary['verified_total'], 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Order Selesai Produksi</div>
            <div class="metric">{{ $completedInMonth }}</div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-top:1rem;">
    <div class="card">
        <h3 style="margin-bottom:0.6rem;">Snapshot Balancing</h3>
        <table>
            <tr><th>Jumlah Pesanan</th><td>{{ $orderCount }}</td></tr>
            <tr><th>Total PCS</th><td>{{ number_format($totalPcs, 0, ',', '.') }}</td></tr>
            <tr><th>Gap Bulanan</th><td>Rp {{ number_format($balanceGap, 0, ',', '.') }}</td></tr>
            <tr><th>Status</th><td>{{ abs($balanceGap) < 0.01 ? 'BALANCE' : 'TIDAK BALANCE' }}</td></tr>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-bottom:0.6rem;">Snapshot Keuangan</h3>
        <table>
            <tr><th>Piutang</th><td>Rp {{ number_format($ledgerSummary['receivable'], 0, ',', '.') }}</td></tr>
            <tr><th>Pending</th><td>Rp {{ number_format($ledgerSummary['pending_total'], 0, ',', '.') }}</td></tr>
            <tr><th>Rejected</th><td>Rp {{ number_format($ledgerSummary['rejected_total'], 0, ',', '.') }}</td></tr>
        </table>
    </div>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3 style="margin-bottom:0.6rem;">Top 20 Pembukuan Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>No.Order</th>
                <th>Pelanggan</th>
                <th>Transfer Tujuan</th>
                <th>Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $ledgerLimit = $ledgerRows->take(20)->values();
                $groupedLedger = $ledgerLimit->groupBy('order_code');
                $ledgerColorMap = [];
                $colors = ['#d97706', '#2563eb', '#10b981', '#8b5cf6', '#ec4899'];
                $colorIdx = 0;
                foreach($groupedLedger as $code => $items) {
                    if ($items->count() > 1 && $code) {
                        $ledgerColorMap[$code] = $colors[$colorIdx % count($colors)];
                        $colorIdx++;
                    }
                }
            @endphp
            @forelse($ledgerLimit as $idx => $row)
                @php
                    $orderCode = $row['order_code'] ?? '';
                    $groupColor = $ledgerColorMap[$orderCode] ?? null;
                    $hasSameNext = isset($ledgerLimit[$idx + 1]) && ($ledgerLimit[$idx + 1]['order_code'] ?? '') === $orderCode;
                    $hasSamePrev = isset($ledgerLimit[$idx - 1]) && ($ledgerLimit[$idx - 1]['order_code'] ?? '') === $orderCode;

                    if ($groupColor) {
                        if ($hasSameNext) {
                            $borderStyle = 'border-bottom: 1px dashed #cbd5e1;';
                        } else {
                            $borderStyle = 'border-bottom: 2.5px solid #94a3b8;';
                        }
                    } else {
                        $borderStyle = '';
                    }
                @endphp
                <tr style="{{ $borderStyle }}">
                    <td style="{{ $groupColor ? 'border-left: 5px solid ' . $groupColor . '; padding-left: 0.85rem !important;' : '' }}">{{ $row['date']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $row['invoice'] }}</td>
                    <td>{{ $row['order_code'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['destination'] }}</td>
                    <td>Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Belum ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3 style="margin-bottom:0.6rem;">Top 20 Progress Produksi</h3>
    <table>
        <thead>
            <tr>
                <th>No.Order</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>Jenis</th>
                <th>PCS</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productionRows->take(20) as $row)
                <tr>
                    <td>{{ $row['order_code'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['product_name'] ?: '-' }}</td>
                    <td>{{ $row['production_type'] ?: '-' }}</td>
                    <td>{{ number_format($row['total_pcs'], 0, ',', '.') }}</td>
                    <td>{{ $row['step_progress'] }}</td>
                    <td>{{ str_replace('_', ' ', $row['order_status']) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Belum ada data produksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
