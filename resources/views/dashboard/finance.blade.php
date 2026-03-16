@extends('layouts.app')

@section('content')
<div class="grid grid-2">
    <div class="card">
        <h1>Dashboard Keuangan</h1>
        <p class="muted">Verifikasi DP dan pelunasan agar proses produksi tetap berjalan.</p>
        <a class="btn btn-brand" href="{{ route('finance.index') }}">Buka Modul Keuangan</a>
    </div>
    <div class="grid">
        <div class="card">
            <div class="muted">Pembayaran Menunggu Verifikasi</div>
            <div class="metric">{{ $pendingPayments }}</div>
        </div>
        <div class="card">
            <div class="muted">Terverifikasi Hari Ini</div>
            <div class="metric">{{ $verifiedToday }}</div>
        </div>
    </div>
</div>
@endsection
