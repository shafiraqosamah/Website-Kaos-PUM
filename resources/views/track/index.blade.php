@extends('layouts.app')

@section('title', 'Lacak Pesanan | PT Panji Usaha Mulia')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 500px; width: 100%; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #0d2749; margin-bottom: 1.5rem; font-family: 'Playfair Display', serif;">Pantau Status Produksi Anda</h2>
        <p style="text-align: center; color: #666; margin-bottom: 2rem;">Masukkan nomor pesanan (Contoh: ORD-06-2026-0001) untuk melihat status produksi tanpa perlu login.</p>

        @if ($errors->any())
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('track.search') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="order_code" style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Nomor Pesanan</label>
                <input type="text" id="order_code" name="order_code" value="{{ old('order_code') }}" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 8px;" placeholder="ORD-XXXXXXXX-XXXXX" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; background: #0d2749; color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Lacak Sekarang</button>
        </form>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('home') }}" style="color: #666; text-decoration: none;">&larr; Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
