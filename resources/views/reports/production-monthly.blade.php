@extends('layouts.app')

@section('header_title', 'Laporan Produksi')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:end; gap:1rem; flex-wrap:wrap;">
        <div>
            <h1>Laporan Produksi Bulanan</h1>
            <p class="muted" style="margin:0;">Laporan operasional produksi tanpa data keuangan untuk periode {{ $monthLabel }}.</p>
        </div>
        <form method="GET" action="{{ route('reports.production') }}" style="display:flex; gap:0.6rem; align-items:end;">
            <div>
                <label for="month" style="margin-bottom:0.2rem;">Pilih Bulan</label>
                <input id="month" type="month" name="month" value="{{ $monthInput }}">
            </div>
            <button class="btn btn-brand" type="submit">Tampilkan</button>
            <a href="{{ route('reports.production.export', ['month' => $monthInput]) }}" style="align-self:end; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; background-color:#1e7e34; color:#ffffff; border:1px solid #1c7430; padding:0.4rem 0.6rem; border-radius:4px; font-size:0.85rem; font-weight:600;">
                ⇩ Excel
            </a>
        </form>
    </div>

    <div class="grid grid-3" style="margin-top:1rem;">
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Order Selesai</div>
            <div class="metric">{{ $completedInMonth }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Total PCS Selesai</div>
            <div class="metric">{{ number_format($producedPcsInMonth, 0, ',', '.') }}</div>
        </div>
        <div class="card" style="padding:0.8rem;">
            <div class="muted">Order Dipantau</div>
            <div class="metric">{{ $productionRows->count() }}</div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-top:1rem;">
    <div class="card" style="overflow:auto;">
        <h3 style="margin-bottom:0.6rem;">Ringkasan Jenis Produksi</h3>
        <table>
            <thead>
                <tr>
                    <th>Jenis Produksi</th>
                    <th>Produk</th>
                    <th>Order</th>
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
                    <tr><td colspan="4" class="muted">Belum ada data produksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card" style="overflow:auto;">
        <h3 style="margin-bottom:0.6rem;">Progress Tiap Pesanan</h3>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Jenis</th>
                    <th>Tanggal Pesan</th>
                    <th>Tanggal Selesai</th>
                    <th>PCS</th>
                    <th>Progress Step</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productionRows as $row)
                    <tr>
                        <td>{{ $row['order_code'] }}</td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td>{{ $row['product_name'] ?: '-' }}</td>
                        <td>{{ $row['production_type'] ?: '-' }}</td>
                        <td>{{ $row['created_at'] ? \Carbon\Carbon::parse($row['created_at'])->format('d M Y') : '-' }}</td>
                        <td>{{ in_array($row['order_status'], ['completed', 'done', 'ready_for_pickup']) && $row['updated_at'] ? \Carbon\Carbon::parse($row['updated_at'])->format('d M Y') : '-' }}</td>
                        <td>{{ number_format($row['total_pcs'], 0, ',', '.') }}</td>
                        <td>{{ $row['step_progress'] }}</td>
                        <td>{{ str_replace('_', ' ', $row['order_status']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">Belum ada order untuk dipantau pada bulan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
