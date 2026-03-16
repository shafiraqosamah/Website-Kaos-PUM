@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Form Customisasi Kaos</h1>
    <p class="muted">Minimal custom adalah 60 pcs. Isi detail pesanan Anda dengan lengkap.</p>
</div>

<form class="grid" style="gap:1rem; margin-top:1rem;" method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-2">
        <div class="card">
            <h3>Detail Utama</h3>
            <div class="grid" style="gap:0.7rem;">
                <div>
                    <label>Nama Pemesan</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required>
                </div>
                <div>
                    <label>Total Pcs</label>
                    <input type="number" name="total_pcs" min="60" value="{{ old('total_pcs', 60) }}" required>
                </div>
                <div>
                    <label>Harga per Pcs (Rp)</label>
                    <input type="number" name="unit_price" min="1000" value="{{ old('unit_price', 85000) }}" required>
                </div>
                <div>
                    <label>Bahan</label>
                    <input type="text" name="fabric" value="{{ old('fabric', 'Combed 24s') }}" required>
                </div>
                <div>
                    <label>Warna Dominan</label>
                    <input type="text" name="dominant_color" value="{{ old('dominant_color', 'Hitam') }}" required>
                </div>
                <div>
                    <label>Estimasi Tanggal Selesai</label>
                    <input type="date" name="estimated_finish_date" value="{{ old('estimated_finish_date') }}" required>
                </div>
                <div>
                    <label>Pembayaran Awal</label>
                    <select name="payment_type" required>
                        <option value="dp" @selected(old('payment_type')==='dp')>DP 50%</option>
                        <option value="full" @selected(old('payment_type')==='full')>Lunas di Awal</option>
                    </select>
                </div>
                <div>
                    <label>Upload Desain (jpg/png/pdf/svg)</label>
                    <input type="file" name="design_file">
                </div>
                <div>
                    <label>Catatan</label>
                    <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Distribusi Ukuran</h3>
            <p class="muted">Jumlah total ukuran harus sama dengan Total Pcs.</p>
            <div class="grid grid-2">
                @foreach ($sizes as $size)
                    <div>
                        <label>{{ $size }}</label>
                        <input type="number" name="sizes[{{ $size }}]" min="0" value="{{ old('sizes.'.$size, 0) }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card" style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
        <p class="muted" style="margin:0;">Sistem akan otomatis menghitung DP dan sisa pelunasan setelah submit.</p>
        <button class="btn btn-brand" type="submit">Buat Pesanan Kustomisasi</button>
    </div>
</form>
@endsection
