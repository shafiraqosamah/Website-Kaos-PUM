@extends('layouts.app')

@section('content')
<div class="grid grid-2">
    <div class="card">
        <h1>Detail Pesanan {{ $order->order_code }}</h1>
        <p class="muted">Status: <span class="status-pill">{{ $order->order_status }}</span></p>
        <table>
            <tr><th>Nama Pemesan</th><td>{{ $order->customer_name }}</td></tr>
            <tr><th>Total Pcs</th><td>{{ $order->total_pcs }}</td></tr>
            <tr><th>Bahan</th><td>{{ $order->fabric }}</td></tr>
            <tr><th>Warna</th><td>{{ $order->dominant_color }}</td></tr>
            <tr><th>Estimasi</th><td>{{ $order->estimated_finish_date?->format('d M Y') }}</td></tr>
            <tr><th>Subtotal</th><td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
            <tr><th>DP / Awal</th><td>Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td></tr>
            <tr><th>Sisa</th><td>Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="card">
        <h3>Ukuran</h3>
        <table>
            @foreach ($order->sizes as $size)
                <tr><th>{{ $size->size_name }}</th><td>{{ $size->qty }} pcs</td></tr>
            @endforeach
        </table>

        @if ($order->isSettlementRequired() && $order->order_status === 'finishing_waiting_settlement')
            <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin-top:0.9rem;">
                @csrf
                <button class="btn btn-danger" type="submit">Lakukan Pelunasan Sekarang</button>
            </form>
            <p class="muted" style="margin-bottom:0;">Produksi tidak dapat diselesaikan sebelum pelunasan diverifikasi.</p>
        @endif
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <h3>Progress Produksi</h3>
    @if ($order->productionSteps->isEmpty())
        <p class="muted">Menunggu verifikasi pembayaran dan penerbitan SPK.</p>
    @else
        <table>
            <thead><tr><th>Tahap</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
            @foreach ($order->productionSteps as $step)
                <tr>
                    <td>{{ $step->step_order }}. {{ $step->step_name }}</td>
                    <td><span class="status-pill">{{ $step->status }}</span></td>
                    <td>{{ $step->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card" style="margin-top:1rem;">
    <h3>Riwayat Pembayaran</h3>
    <table>
        <thead><tr><th>Metode</th><th>Transfer Ke</th><th>Pengirim</th><th>Nominal</th><th>Status</th><th>Bukti</th><th>Aksi</th><th>Catatan</th></tr></thead>
        <tbody>
        @foreach($order->payments as $payment)
            <tr>
                <td>{{ $payment->method }}</td>
                <td>{{ $payment->destinationBankDetails()['label'] ?? '-' }}</td>
                <td>
                    {{ $payment->sender_bank_name ?? '-' }}<br>
                    <span class="muted">{{ $payment->sender_account_name ?? '-' }}</span>
                </td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td><span class="status-pill">{{ $payment->status }}</span></td>
                <td>
                    @if ($payment->proof_path)
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_path) }}" target="_blank">Lihat</a>
                    @else
                        Belum ada
                    @endif
                </td>
                <td>
                    @if ($payment->status !== 'verified')
                        <a href="{{ route('customer.orders.payments.edit', [$order, $payment]) }}">Isi / ubah</a>
                    @else
                        <a href="{{ route('customer.invoices.show', [$order, $payment]) }}" target="_blank">Invoice</a>
                    @endif
                </td>
                <td>{{ $payment->notes }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
