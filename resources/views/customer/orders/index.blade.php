@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Pesanan Saya</h1>
    <p class="muted">Pantau status order, pembayaran, dan progres produksi Anda.</p>
    <a class="btn btn-brand" href="{{ route('customer.orders.create') }}">Pesan Custom Baru</a>
</div>

<div class="card" style="margin-top:1rem;">
    <table>
        <thead>
        <tr>
            <th>Kode</th>
            <th>Total Pcs</th>
            <th>Status</th>
            <th>Sisa Bayar</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($orders as $order)
            <tr>
                <td>{{ $order->order_code }}</td>
                <td>{{ $order->total_pcs }}</td>
                <td><span class="status-pill">{{ $order->order_status }}</span></td>
                <td>Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</td>
                <td><a href="{{ route('customer.orders.show', $order) }}">Detail</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada pesanan.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:0.8rem;">{{ $orders->links() }}</div>
</div>
@endsection
