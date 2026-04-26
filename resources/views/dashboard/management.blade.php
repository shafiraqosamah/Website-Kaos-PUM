@extends('layouts.app')

@section('content')
<style>
    .management-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .management-header h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .management-header p {
        margin: 0.45rem 0 1.2rem;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .management-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid #f3c4b8;
        background: #fff4ef;
        padding: 0.72rem 0.95rem;
        color: #b63b22;
        font-size: 0.86rem;
    }

    .management-alert a {
        color: #b63b22;
        font-weight: 700;
        text-decoration: underline;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.95rem;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 6px 16px rgba(13, 39, 73, 0.05);
    }

    .metric-card.orders {
        border-top-color: #2c7ebe;
    }

    .metric-card.pending {
        border-top-color: #cf3c2c;
    }

    .metric-card.production {
        border-top-color: #0c7fb6;
    }

    .metric-card.completed {
        border-top-color: #0f8f60;
    }

    .metric-label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .metric-value {
        margin-top: 0.55rem;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-weight: 700;
    }

    .metric-indicator {
        margin-top: 0.42rem;
        color: #7f96ae;
        font-size: 0.78rem;
        font-weight: 600;
    }

    @media (max-width: 1200px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .management-page {
            padding: 1rem;
        }

        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="management-page">
    <div class="management-header">
        <h1>Dashboard Admin</h1>
        <p>Overview sistem dan manajemen data</p>
    </div>

    @if (($pendingOrderVerification ?? 0) > 0)
        <div class="management-alert">
            <span>🔔</span>
            <span>Ada <strong>{{ $pendingOrderVerification }}</strong> pesanan yang menunggu verifikasi admin.</span>
            <a href="{{ route('reports.orders') }}">Verifikasi Sekarang →</a>
        </div>
    @endif

    @if (($productionWaitingVerification ?? 0) > 0)
        <div class="management-alert" style="border-color: #fbc4a8; background: #fffbf7; color: #c87f2d;">
            <span>✓</span>
            <span>Ada <strong>{{ $productionWaitingVerification }}</strong> produksi yang menunggu verifikasi hasil.</span>
            <a href="{{ route('production.index') }}" style="color: #c87f2d;">Verifikasi Sekarang →</a>
        </div>
    @endif

    <div class="metrics-grid">
        <div class="metric-card orders">
            <div class="metric-label">Total Order</div>
            <div class="metric-value">{{ $summary['total_orders'] }}</div>
            <div class="metric-indicator">Seluruh pesanan</div>
        </div>
        <div class="metric-card pending">
            <div class="metric-label">Menunggu Verifikasi</div>
            <div class="metric-value">{{ $summary['pending_verification'] }}</div>
            <div class="metric-indicator">Verifikasi pesanan pelanggan</div>
        </div>
        <div class="metric-card production">
            <div class="metric-label">Sedang Produksi</div>
            <div class="metric-value">{{ $summary['in_production'] }}</div>
            <div class="metric-indicator">Dalam Pengerjaan</div>
        </div>
        <div class="metric-card completed">
            <div class="metric-label">Selesai</div>
            <div class="metric-value">{{ $summary['completed'] }}</div>
            <div class="metric-indicator">Order selesai</div>
        </div>
    </div>
</section>
@endsection
