@extends('layouts.app')

@section('content')
<div class="grid grid-2">
    <div class="card">
        <h1>Dashboard Produksi</h1>
        <p class="muted">Kelola tahapan jahit, sablon, steam, finishing berdasarkan SPK.</p>
        <a class="btn btn-brand" href="{{ route('production.index') }}">Buka Modul Produksi</a>
    </div>
    <div class="grid">
        <div class="card">
            <div class="muted">Order Aktif</div>
            <div class="metric">{{ $activeOrders }}</div>
        </div>
        <div class="card">
            <div class="muted">Menunggu Pelunasan</div>
            <div class="metric">{{ $waitingSettlement }}</div>
        </div>
    </div>
</div>
@endsection
