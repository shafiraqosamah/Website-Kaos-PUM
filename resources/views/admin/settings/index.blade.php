@extends('layouts.app')

@section('header_title', 'Pengaturan Sistem')

@section('content')
<style>
    .settings-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 2rem;
        max-width: 800px;
        margin: 0 auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .settings-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 1rem;
    }

    .settings-header h1 {
        margin: 0 0 0.5rem 0;
        font-family: 'Playfair Display', serif;
        color: #0f2947;
        font-size: 1.5rem;
    }

    .settings-header p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #334155;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 1rem;
        color: #334155;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-submit {
        background: #0f2947;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-submit:hover {
        background: #1e3a8a;
    }

    .simulation-section {
        margin-top: 3rem;
        padding: 1.5rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
    }

    .simulation-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .simulation-title {
        margin: 0;
        color: #92400e;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .simulation-desc {
        color: #b45309;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .btn-simulate {
        background: #d97706;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s;
        text-decoration: none;
    }

    .btn-simulate:hover {
        background: #b45309;
    }

    .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
</style>

<div class="settings-page">
    <div class="settings-header">
        <h1>Pengaturan Sistem</h1>
        <p>Konfigurasi variabel dinamis aplikasi.</p>
    </div>

    @if(session('success'))
        <div class="alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label" for="auto_cancel_minutes">Batas Waktu Tunggu Verifikasi / Pembayaran (dalam Menit)</label>
            <input type="number" id="auto_cancel_minutes" name="auto_cancel_minutes" class="form-control" value="{{ $autoCancelMinutes }}" min="1" required>
            <p style="margin-top: 0.5rem; color: #64748b; font-size: 0.85rem;">
                Default normal: 2880 menit (48 Jam / 2 Hari).<br>
                Ubah ke angka kecil (misal 1 atau 2 menit) untuk keperluan testing saat demonstrasi sidang.
            </p>
        </div>
        
        <button type="submit" class="btn-submit">Simpan Pengaturan</button>
    </form>

    <div class="simulation-section">
        <div class="simulation-header">
            <span>⚠️</span>
            <h2 class="simulation-title">Simulasi Pengecekan Rentang Approval</h2>
        </div>
        <p class="simulation-desc">
            Tombol ini untuk membatalkan pesanan-pesanan yang sudah melewati batas waktu <b>{{ $autoCancelMinutes }} menit</b> sejak dibuat (untuk verifikasi admin) atau sejak disetujui (untuk pembayaran).
        </p>
        
        <form action="{{ route('admin.settings.run-check') }}" method="POST">
            @csrf
            <button type="submit" class="btn-simulate" onclick="return confirm('Apakah Anda yakin ingin menjalankan simulasi pembatalan otomatis sekarang? Proses ini tidak dapat dibatalkan.')">
                ▶ Jalankan Pengecekan Sistem (Simulasi)
            </button>
        </form>
    </div>
</div>
@endsection
