@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Modul Keuangan</h1>
    <p class="muted">Verifikasi pembayaran DP atau pelunasan untuk membuka tahapan berikutnya.</p>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <table>
        <thead>
        <tr>
            <th>Order</th>
            <th>Pelanggan</th>
            <th>Metode</th>
            <th>Transfer Ke</th>
            <th>Bank Pengirim</th>
            <th>Atas Nama</th>
            <th>Nominal</th>
            <th>Bukti</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @forelse($pendingPayments as $payment)
            <tr>
                <td>{{ $payment->order->order_code }}</td>
                <td>{{ $payment->order->user->name }}</td>
                <td>{{ strtoupper($payment->method) }}</td>
                <td>
                    @php($bank = $payment->destinationBankDetails())
                    {{ $bank['label'] ?? '-' }}<br>
                    <span class="muted">{{ $bank['account_number'] ?? '-' }}</span>
                </td>
                <td>{{ $payment->sender_bank_name }}</td>
                <td>{{ $payment->sender_account_name }}</td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td>
                    @if ($payment->proof_path)
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_path) }}" target="_blank">Lihat bukti</a>
                    @else
                        -
                    @endif
                </td>
                <td><span class="status-pill">{{ $payment->status }}</span></td>
                <td>
                    <form method="POST" action="{{ route('finance.verify', $payment) }}" style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                        @csrf
                        <input type="text" name="notes" placeholder="Catatan (opsional)">
                        <button class="btn btn-brand" name="action" value="verify" type="submit">Verifikasi</button>
                        <button class="btn btn-danger" name="action" value="reject" type="submit">Tolak</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="10">Tidak ada pembayaran yang menunggu verifikasi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <h3>Invoice Pembayaran Terverifikasi</h3>
    <table>
        <thead>
        <tr>
            <th>Invoice</th>
            <th>Order</th>
            <th>Nama Pemesan</th>
            <th>Metode</th>
            <th>Nominal</th>
            <th>Terverifikasi</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @forelse($verifiedPayments as $payment)
            <tr>
                <td>{{ $payment->invoice_number ?? '-' }}</td>
                <td>{{ $payment->order->order_code }}</td>
                <td>{{ $payment->order->customer_name }}</td>
                <td>{{ strtoupper($payment->method) }}</td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td>{{ $payment->verified_at?->format('d/m/Y H:i') }}</td>
                <td><a href="{{ route('finance.invoices.show', $payment) }}" target="_blank">Buka Invoice</a></td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada invoice terbit.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
