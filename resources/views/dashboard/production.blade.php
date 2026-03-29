@extends('layouts.app')

@section('content')
@php
    $settlementRisk = $waitingSettlement > 0;
@endphp

<style>
    .production-header {
        background: linear-gradient(135deg, #0d2749 0%, #102945 50%, #1a3d5c 100%);
        border-radius: 16px;
        padding: clamp(1.5rem, 3vw, 2.2rem);
        margin-bottom: 2rem;
        border: 1px solid rgba(198, 166, 71, 0.2);
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .production-header::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -8%;
        width: 400px;
        height: 400px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(198, 166, 71, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .production-header-content {
        position: relative;
        z-index: 1;
        flex: 1;
        min-width: 300px;
    }

    .production-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.34rem, 2.1vw, 1.76rem);
        color: #ffffff;
        margin: 0 0 0.5rem;
        font-weight: 700;
    }

    .production-header p {
        color: #c8d6e8;
        margin: 0;
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .prod-btn {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.6rem;
        background: #c6a647;
        color: #0f2947;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .prod-btn:hover {
        background: #dfbf65;
        transform: translateY(-2px);
    }

    .kpi-grid-modern {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.5rem;
    }

    .kpi-card-modern {
        border-radius: 14px;
        border: 1px solid #d5e1eb;
        background: #ffffff;
        padding: 1.4rem;
        box-shadow: 0 2px 8px rgba(15, 43, 61, 0.04);
        transition: all 0.2s;
    }

    .kpi-card-modern:hover {
        border-color: #c8d6e8;
        box-shadow: 0 4px 12px rgba(15, 43, 61, 0.08);
    }

    .kpi-card-modern.warn {
        border-color: #f2d1bf;
        background: linear-gradient(135deg, #fff9f5 0%, #fff1e7 100%);
    }

    .kpi-card-modern.ok {
        border-color: #bfe5d0;
        background: linear-gradient(135deg, #f2fcf6 0%, #e9f9ef 100%);
    }

    .kpi-title {
        margin: 0 0 0.7rem;
        color: #38536a;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .kpi-value {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.42rem, 2.05vw, 1.86rem);
        line-height: 1;
        font-weight: 700;
        margin-bottom: 0.8rem;
    }

    .kpi-card-modern.ok .kpi-value {
        color: #0d5a34;
    }

    .kpi-card-modern.warn .kpi-value {
        color: #7a2e0e;
    }

    .kpi-note {
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .kpi-card-modern.ok .kpi-note {
        color: #1e6f46;
    }

    .kpi-card-modern.warn .kpi-note {
        color: #a44b16;
    }

    @media (max-width: 1024px) {
        .production-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .kpi-grid-modern {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .production-header {
            flex-direction: column;
        }

        .prod-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="production-header">
    <div class="production-header-content">
        <h1>Dashboard Produksi</h1>
        <p>🏭 Kelola alur kerja produksi dari cutting sampai finishing. Pantau order yang terhambat dan status pembayaran real-time.</p>
    </div>
    <a class="prod-btn" href="{{ route('production.index') }}">→ Buka Modul Produksi</a>
</div>

<div class="kpi-grid-modern">
    <article class="kpi-card-modern ok">
        <p class="kpi-title">Order Aktif</p>
        <div class="kpi-value">{{ $activeOrders }}</div>
        <div class="kpi-note">📦 Sedang berjalan dalam pipeline produksi.</div>
    </article>

    <article class="kpi-card-modern {{ $settlementRisk ? 'warn' : 'ok' }}">
        <p class="kpi-title">Menunggu Pelunasan</p>
        <div class="kpi-value">{{ $waitingSettlement }}</div>
        <div class="kpi-note">
            {{ $settlementRisk ? '⚠️ Perlu follow up keuangan/customer agar finishing bisa ditutup.' : '✓ Tidak ada hambatan pelunasan saat ini.' }}
        </div>
    </article>
</div>
@endsection
