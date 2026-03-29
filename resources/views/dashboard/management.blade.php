@extends('layouts.app')

@section('content')
<style>
    .management-hero {
        background: linear-gradient(135deg, #0d2749 0%, #102945 50%, #1a3d5c 100%);
        border-radius: 16px;
        padding: clamp(1.5rem, 3vw, 2.2rem);
        margin-bottom: 2rem;
        border: 1px solid rgba(198, 166, 71, 0.2);
        position: relative;
        overflow: hidden;
    }

    .management-hero::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -5%;
        width: 400px;
        height: 400px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(198, 166, 71, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .management-hero-content {
        position: relative;
        z-index: 1;
    }

    .management-hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.42rem, 2.3vw, 1.9rem);
        color: #ffffff;
        margin: 0 0 0.6rem;
        font-weight: 700;
    }

    .management-hero p {
        color: #c8d6e8;
        margin: 0;
        font-size: clamp(0.8rem, 1vw, 0.88rem);
        max-width: 700px;
        line-height: 1.5;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.2rem;
        margin-top: 2rem;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid #dfe6ee;
        border-radius: 14px;
        padding: 1.2rem;
        box-shadow: 0 2px 8px rgba(15, 43, 61, 0.04);
        transition: all 0.2s;
        text-align: center;
    }

    .metric-card:hover {
        border-color: #c8d6e8;
        box-shadow: 0 4px 16px rgba(15, 43, 61, 0.08);
        transform: translateY(-2px);
    }

    .metric-label {
        font-size: 0.72rem;
        color: #7893ae;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 700;
        margin-bottom: 0.6rem;
    }

    .metric-value {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.4rem, 2vw, 1.85rem);
        color: #0d2749;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.4rem;
    }

    .metric-indicator {
        font-size: 0.68rem;
        color: #7893ae;
        font-weight: 600;
    }

    @media (max-width: 1200px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="management-hero">
    <div class="management-hero-content">
        <h1>Dashboard Owner / Manager</h1>
        <p>📊 Monitoring realtime lintas modul customer, keuangan, dan produksi. Pantau semua metrik penting dalam satu dashboard.</p>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Total Order</div>
        <div class="metric-value">{{ $summary['total_orders'] }}</div>
        <div class="metric-indicator">📦 Seluruh Pesanan</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Menunggu Verifikasi</div>
        <div class="metric-value" style="color: #d95f18;">{{ $summary['pending_verification'] }}</div>
        <div class="metric-indicator">⏳ Pembayaran Pending</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Sedang Produksi</div>
        <div class="metric-value" style="color: #0f7b8f;">{{ $summary['in_production'] }}</div>
        <div class="metric-indicator">🔧 Dalam Pipeline</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Selesai</div>
        <div class="metric-value" style="color: #0f8f60;">{{ $summary['completed'] }}</div>
        <div class="metric-indicator">✓ Order Selesai</div>
    </div>
</div>
@endsection
