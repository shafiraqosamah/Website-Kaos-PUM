@extends('layouts.app')

@section('content')
<div class="card" style="overflow:hidden; padding: 1.5rem;">
    <div class="grid grid-2" style="align-items:center; gap: 1.4rem;">
        <div>
            <p class="muted" style="margin-top:0;">Konveksi Terintegrasi</p>
            <h1 style="font-size: clamp(1.8rem, 4vw, 2.9rem);">Dashboard Modern Pemesanan Kaos Custom</h1>
            <p class="muted" style="font-size:1.03rem; max-width:52ch;">
                Dari order, pembayaran DP 50%, penerbitan SPK, sampai tracking progres produksi.
                Semua modul terhubung untuk customer, keuangan, produksi, owner, dan manager.
            </p>
            <div style="display:flex; gap:0.6rem; flex-wrap:wrap; margin-top: 0.8rem;">
                <a class="btn btn-brand" href="{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.orders.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Pesan Custom Sekarang</a>
                <a class="btn btn-alt" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Masuk Dashboard</a>
            </div>
        </div>
        <div class="card" style="background: linear-gradient(140deg, #fef5ec, #ecf8fb);">
            <h3 style="margin-bottom:0.4rem;">Alur Cepat</h3>
            <ol class="muted" style="margin:0; padding-left:1.1rem; line-height:1.8;">
                <li>Pilih katalog kaos dan masuk menu custom.</li>
                <li>Registrasi pelanggan, isi jumlah, ukuran, bahan, warna, upload desain.</li>
                <li>Sistem hitung otomatis DP 50% dan sisa pelunasan.</li>
                <li>Keuangan verifikasi pembayaran.</li>
                <li>SPK otomatis keluar ke tim produksi.</li>
                <li>Pelanggan memantau progres jahit, sablon, steam, finishing.</li>
            </ol>
        </div>
    </div>
</div>

<div class="grid grid-3" style="margin-top: 1rem;">
    @foreach($products as $product)
        <article class="card">
            <h3>{{ $product['name'] }}</h3>
            <p class="muted">{{ $product['desc'] }}</p>
            <p style="font-weight:700; color: var(--brand); margin-bottom: 0.2rem;">{{ $product['price'] }}</p>
            <a href="{{ auth()->check() ? route('customer.orders.create') : route('register') }}" class="btn btn-brand" style="margin-top:0.5rem;">Custom Produk Ini</a>
        </article>
    @endforeach
</div>
@endsection
