@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Modul Produksi</h1>
    <p class="muted">SPK aktif dan progres tahapan pengerjaan produksi kaos.</p>
</div>

<div class="card" style="margin-top:1rem;">
    <table>
        <thead><tr><th>SPK</th><th>Order</th><th>Pelanggan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td>{{ $order->workOrder?->spk_number ?? '-' }}</td>
                <td>{{ $order->order_code }}</td>
                <td>{{ $order->user->name }}</td>
                <td><span class="status-pill">{{ $order->order_status }}</span></td>
                <td><a href="{{ route('production.show', $order) }}">Kelola Tahap</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada order untuk produksi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
