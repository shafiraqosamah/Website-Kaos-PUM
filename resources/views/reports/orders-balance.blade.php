@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1>Laporan Pemesanan & Balancing</h1>
            <p class="muted" style="margin:0;">Khusus kontrol admin/keuangan untuk cek kesesuaian nilai order dan pembayaran pada {{ $monthLabel }}.</p>
        </div>
        <form method="GET" action="{{ route('reports.orders') }}" style="display:flex; gap:0.6rem; align-items:end;">
            <div>
                <label for="month" style="margin-bottom:0.2rem;">Pilih Bulan</label>
                <input id="month" type="month" name="month" value="{{ $monthInput }}">
            </div>
            <button class="btn btn-brand" type="submit">Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-3" style="margin-top:1rem;">
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Jumlah Pesanan</div>
            <div class="metric">{{ $orderCount }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Nilai Pesanan</div>
            <div class="metric">Rp {{ number_format($orderSubtotal, 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Pembayaran Terverifikasi</div>
            <div class="metric">Rp {{ number_format($verifiedPaymentsTotal, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="alert {{ abs($balanceGap) < 0.01 ? 'alert-ok' : 'alert-err' }}" style="margin-top:1rem; margin-bottom:0;">
        Status balancing bulan ini:
        <strong>{{ abs($balanceGap) < 0.01 ? 'BALANCE' : 'TIDAK BALANCE' }}</strong>
        dengan selisih Rp {{ number_format(abs($balanceGap), 0, ',', '.') }}.
    </div>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3 style="margin-bottom:0.6rem;">Rincian Balancing per Pesanan</h3>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>PCS</th>
                <th>Subtotal</th>
                <th>DP 50% (Verified)</th>
                <th>Tanggal DP</th>
                <th>Pelunasan (Verified)</th>
                <th>Tanggal Pelunasan</th>
                <th>Lunas Awal (Verified)</th>
                <th>Tanggal Lunas Awal</th>
                <th>Terverifikasi</th>
                <th>Sisa</th>
                <th>Delta</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($financePerOrder as $row)
                <tr>
                    <td>{{ $row['order_code'] }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td>{{ $row['product_name'] ?: '-' }}</td>
                    <td>{{ number_format($row['total_pcs'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['dp_verified'], 0, ',', '.') }}</td>
                    <td>{{ $row['dp_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($row['settlement_verified'], 0, ',', '.') }}</td>
                    <td>{{ $row['settlement_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($row['full_verified'], 0, ',', '.') }}</td>
                    <td>{{ $row['full_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($row['verified'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['remaining_amount'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['balance_delta'], 0, ',', '.') }}</td>
                    <td>
                        @if($row['is_balanced'])
                            <span class="status-pill status-success">Balance</span>
                        @else
                            <span class="status-pill status-warning">Belum Balance</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="muted">Belum ada data pesanan pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
