@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Dashboard Kontrol Owner / Manager / Admin</h1>
    <p class="muted">Monitoring lintas modul customer, keuangan, dan produksi.</p>
</div>

<div class="grid grid-2" style="margin-top:1rem;">
    <div class="card">
        <div class="muted">Total Order</div>
        <div class="metric">{{ $summary['total_orders'] }}</div>
    </div>
    <div class="card">
        <div class="muted">Pending Verifikasi Pembayaran</div>
        <div class="metric">{{ $summary['pending_verification'] }}</div>
    </div>
    <div class="card">
        <div class="muted">Sedang Produksi</div>
        <div class="metric">{{ $summary['in_production'] }}</div>
    </div>
    <div class="card">
        <div class="muted">Selesai</div>
        <div class="metric">{{ $summary['completed'] }}</div>
    </div>
</div>
@endsection
