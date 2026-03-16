@extends('layouts.app')

@section('content')
<div class="grid grid-2">
    <div class="card">
        <h1>{{ $payment->method === 'settlement' ? 'Form Pembayaran Pelunasan' : 'Form Pembayaran ' . strtoupper($payment->method) }}</h1>
        <p class="muted">Lengkapi data transfer dan upload bukti pembayaran agar dapat diverifikasi bagian keuangan.</p>
        <table>
            <tr><th>Kode Order</th><td>{{ $order->order_code }}</td></tr>
            <tr><th>Jenis Pembayaran</th><td>{{ strtoupper($payment->method) }}</td></tr>
            <tr><th>Nominal</th><td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
            <tr><th>Status</th><td><span class="status-pill">{{ $payment->status }}</span></td></tr>
        </table>
    </div>

    <div class="card">
        <h3>Rekening Tujuan</h3>
        <div class="grid" style="gap:0.7rem;">
            @foreach ($banks as $bankKey => $bank)
                <div style="padding:0.8rem; border:1px solid var(--line); border-radius:12px; background:#fbfdff;">
                    <div style="font-weight:700;">{{ $bank['label'] }}</div>
                    <div class="muted">No. Rek: {{ $bank['account_number'] }}</div>
                    <div class="muted">a.n. {{ $bank['account_name'] }}</div>
                </div>
            @endforeach
        </div>
        <p class="muted" style="margin-top:0.8rem;">Catatan: detail rekening BNI dan Mandiri masih bisa disesuaikan jika Anda ingin mengganti nomor rekeningnya.</p>
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <form method="POST" action="{{ route('customer.orders.payments.update', [$order, $payment]) }}" enctype="multipart/form-data" class="grid grid-2">
        @csrf
        <div>
            <label>Transfer ke Bank</label>
            <select name="destination_bank" required>
                <option value="">Pilih bank tujuan</option>
                @foreach ($banks as $bankKey => $bank)
                    <option value="{{ $bankKey }}" @selected(old('destination_bank', $payment->destination_bank) === $bankKey)>{{ $bank['label'] }} - {{ $bank['account_number'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Bank Pengirim</label>
            <input type="text" name="sender_bank_name" value="{{ old('sender_bank_name', $payment->sender_bank_name) }}" placeholder="Contoh: BRI / BCA / Dana" required>
        </div>
        <div>
            <label>Atas Nama Rekening Pengirim</label>
            <input type="text" name="sender_account_name" value="{{ old('sender_account_name', $payment->sender_account_name) }}" required>
        </div>
        <div>
            <label>Upload Bukti Pembayaran</label>
            <input type="file" name="payment_proof" {{ $payment->proof_path ? '' : 'required' }}>
            @if ($payment->proof_path)
                <p class="muted" style="margin:0.45rem 0 0;">Bukti saat ini sudah tersimpan. Upload ulang jika ingin mengganti.</p>
            @endif
        </div>
        <div style="grid-column: 1 / -1;">
            <label>Catatan Pembayaran</label>
            <textarea name="notes" rows="3" placeholder="Opsional, misalnya tanggal transfer atau keterangan tambahan">{{ old('notes', $payment->notes) }}</textarea>
        </div>
        <div style="grid-column: 1 / -1; display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; align-items:center;">
            <p class="muted" style="margin:0;">Setelah dikirim, pembayaran akan masuk ke antrian verifikasi bagian keuangan.</p>
            <button type="submit" class="btn btn-brand">Kirim Data Pembayaran</button>
        </div>
    </form>
</div>
@endsection
