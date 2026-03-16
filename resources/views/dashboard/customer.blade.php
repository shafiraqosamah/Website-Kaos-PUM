@extends('layouts.app')

@section('content')
<div class="grid grid-2">
    <div class="card">
        <h1>Dashboard Customer</h1>
        <p class="muted">Buat pesanan baru, cek status produksi, dan pantau sisa pembayaran.</p>
        <a class="btn btn-brand" href="{{ route('customer.orders.create') }}">Buat Pesanan Custom</a>
        <a class="btn btn-alt" href="{{ route('customer.orders.index') }}">Lihat Semua Pesanan</a>
    </div>
    <div class="card">
        <h3>5 Pesanan Terbaru</h3>
        @if ($orders->isEmpty())
            <p class="muted">Belum ada pesanan.</p>
        @else
            <table>
                <thead><tr><th>Kode</th><th>Status</th><th>Sisa</th></tr></thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><a href="{{ route('customer.orders.show', $order) }}">{{ $order->order_code }}</a></td>
                        <td><span class="status-pill">{{ $order->order_status }}</span></td>
                        <td>Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
