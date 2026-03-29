@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1>Laporan Keuangan (Pembukuan)</h1>
            <p class="muted" style="margin:0;">Format pembukuan bulanan untuk finance: transaksi masuk, status verifikasi, piutang, dan surplus/defisit pada {{ $monthLabel }}.</p>
        </div>
        <form method="GET" action="{{ route('reports.finance') }}" style="display:flex; gap:0.6rem; align-items:end;">
            <div>
                <label for="month" style="margin-bottom:0.2rem;">Pilih Bulan</label>
                <input id="month" type="month" name="month" value="{{ $monthInput }}">
            </div>
            <button class="btn btn-brand" type="submit">Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-3" style="margin-top:1rem;">
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Tagihan Pesanan</div>
            <div class="metric">Rp {{ number_format($ledgerSummary['order_subtotal'], 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Kas Masuk Terverifikasi</div>
            <div class="metric">Rp {{ number_format($ledgerSummary['verified_total'], 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Piutang Berjalan</div>
            <div class="metric">Rp {{ number_format($ledgerSummary['receivable'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-3" style="margin-top:0.8rem;">
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Pending</div>
            <div class="metric">Rp {{ number_format($ledgerSummary['pending_total'], 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Rejected</div>
            <div class="metric">Rp {{ number_format($ledgerSummary['rejected_total'], 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Surplus / Defisit Realisasi</div>
            <div class="metric" style="color: {{ $ledgerSummary['surplus_deficit'] >= 0 ? '#176841' : '#a11d1d' }};">
                Rp {{ number_format($ledgerSummary['surplus_deficit'], 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3 style="margin-bottom:0.6rem;">Rekap Omset per Pesanan (Format Pembukuan)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Pesanan</th>
                <th>Qty</th>
                <th>Harga @</th>
                <th>Total</th>
                <th>Jumlah Masuk</th>
                <th>DP</th>
                <th>Tgl DP</th>
                <th>Pelunasan</th>
                <th>Tgl Pelunasan</th>
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
                        <div class="muted">{{ $group['product'] }}</div>
                    </td>
                    <td>{{ number_format($group['qty'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($group['unit_price'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($group['order_subtotal'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($group['verified_total'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($group['dp_verified'], 0, ',', '.') }}</td>
                    <td>{{ $group['dp_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($group['settlement_verified'], 0, ',', '.') }}</td>
                    <td>{{ $group['settlement_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($group['full_verified'], 0, ',', '.') }}</td>
                    <td>{{ $group['full_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($group['remaining'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="muted">Belum ada data omset pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
