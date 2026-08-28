@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
@php
    $settlementRisk = $waitingSettlement > 0;

    $timelineStepName = static function (string $name): ?string {
        $normalized = strtolower(trim($name));

        if (str_contains($normalized, 'cutting')) {
            return 'Cutting';
        }

        if (str_contains($normalized, 'persiapan bahan')) {
            return null;
        }

        if (str_contains($normalized, 'jahit') || str_contains($normalized, 'obras')) {
            return 'Jahit / Obras';
        }

        if (str_contains($normalized, 'sablon') || str_contains($normalized, 'bordir') || str_contains($normalized, 'printing')) {
            return 'Sablon / Bordir / Printing';
        }

        if (str_contains($normalized, 'steam')) {
            return 'Steam & Pressing';
        }

        if (str_contains($normalized, 'finishing') || str_contains($normalized, 'packing')) {
            return 'Finishing';
        }

        return $name;
    };

    $isProductionCompleted = static function ($order) use ($timelineStepName): bool {
        $displaySteps = collect();

        foreach ($order->productionSteps->sortBy('step_order')->values() as $step) {
            $displayName = $timelineStepName((string) $step->step_name);

            if ($displayName === null) {
                continue;
            }

            $displaySteps->push($step);
        }

        return $displaySteps->isNotEmpty() && $displaySteps->every(static fn ($step) => $step->status === 'done');
    };

    $completedProductionCount = $activeOrdersWithSteps->filter(static fn ($order) => $isProductionCompleted($order))->count();
    $processingCount = max($activeOrdersWithSteps->count() - $completedProductionCount, 0);
@endphp

<style>
    .production-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .production-header {
        margin-bottom: 1.2rem;
    }

    .production-header h1 {
        margin: 0 0 0.35rem;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .production-header p {
        margin: 0;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .kpi-grid-modern {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card-modern {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.03);
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

    .kpi-card-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(13, 39, 73, 0.1);
    }

    .kpi-card-modern.active {
        border-top-color: #0c7fb6;
    }

    .kpi-card-modern.completed {
        border-top-color: #0f8f60;
    }

    .kpi-card-modern.blocked {
        border-top-color: #d95f18;
    }

    .kpi-title {
        margin: 0 0 0.5rem;
        color: #8da1b7;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.01em;
    }

    .kpi-value {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #0d2749;
    }

    .kpi-note {
        font-size: 0.75rem;
        line-height: 1.4;
        color: #8da1b7;
    }

    .finishing-alert {
        background: linear-gradient(135deg, #fff5f1 0%, #fff0e8 100%);
        border: 1px solid #f2d1bf;
        border-radius: 12px;
        padding: 1rem 1.2rem;
        margin-bottom: 1.2rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .finishing-alert-icon {
        font-size: 1.4rem;
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .finishing-alert-content {
        flex: 1;
    }

    .finishing-alert-text {
        margin: 0;
        color: #5a3a2a;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .finishing-alert-count {
        font-weight: 700;
        color: #7a2e0e;
    }

    .spk-dashboard-section {
        margin-top: 1.5rem;
    }

    .spk-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e1e8f0;
    }

    .spk-dashboard-title {
        font-family: 'Playfair Display', serif;
        font-size: 1rem;
        font-weight: 700;
        color: #0d2749;
        margin: 1.5rem 0 0;
    }

    .view-all-spk-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.2rem;
        background: #ebc658;
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

    .view-all-spk-btn:hover {
        background: #f5c959;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(198, 166, 71, 0.25);
    }

    .view-all-spk-btn:active {
        transform: translateY(0);
    }

    .spk-card {
        background: #ffffff;
        border: 1px solid #e1e8f0;
        border-radius: 12px;
        padding: 1.2rem;
        margin-bottom: 0.9rem;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.03);
        transition: all 0.2s;
    }

    .spk-card:hover {
        border-color: #d0dce9;
        box-shadow: 0 2px 6px rgba(15, 43, 61, 0.05);
    }

    .spk-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid #f0f3f7;
    }

    .spk-info {
        flex: 1;
    }

    .spk-code {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0d2749;
        margin: 0 0 0.15rem;
    }

    .spk-order-info {
        font-size: 0.82rem;
        color: #8da1b7;
        margin: 0.1rem 0;
        font-weight: 500;
    }

    .spk-status-badge {
        display: inline-block;
        padding: 0.3rem 0.65rem;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.01em;
        white-space: nowrap;
        border: 1px solid;
    }

    .spk-status-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.35rem;
        flex-shrink: 0;
    }

    .spk-detail-link {
        font-size: 0.75rem;
        font-weight: 700;
        color: #0c7fb6;
        text-decoration: none;
        line-height: 1;
    }

    .spk-detail-link:hover {
        color: #0a6b98;
        text-decoration: underline;
    }

    .spk-status-badge.in-production {
        background: rgba(198, 166, 71, 0.08);
        color: #7a6b1a;
        border-color: rgba(198, 166, 71, 0.2);
    }

    .spk-status-badge.verified-payment {
        background: rgba(12, 127, 182, 0.08);
        color: #1e3a4c;
        border-color: rgba(12, 127, 182, 0.2);
    }

    .spk-status-badge.waiting-settlement {
        background: rgba(217, 95, 24, 0.08);
        color: #6a4a1a;
        border-color: rgba(217, 95, 24, 0.2);
    }

    .spk-status-badge.production-complete {
        background: rgba(15, 143, 96, 0.1);
        color: #13603d;
        border-color: rgba(15, 143, 96, 0.28);
    }

    .spk-timeline {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        overflow-x: auto;
        padding-bottom: 0.3rem;
    }

    .spk-timeline::-webkit-scrollbar {
        height: 4px;
    }

    .spk-timeline::-webkit-scrollbar-track {
        background: #f0f3f7;
        border-radius: 2px;
    }

    .spk-timeline::-webkit-scrollbar-thumb {
        background: #d0dce9;
        border-radius: 2px;
    }

    .spk-timeline-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        flex: 0 0 auto;
        text-align: center;
        min-width: 75px;
    }

    .spk-timeline-dot {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        position: relative;
    }

    .spk-timeline-dot.pending {
        background: #f0f3f7;
        color: #8da1b7;
        border: 1.5px solid #d0dce9;
    }

    .spk-timeline-dot.in-progress {
        background: linear-gradient(135deg, #c6a647, #dfbf65);
        color: #ffffff;
        border: none;
        box-shadow: 0 0 0 3px rgba(198, 166, 71, 0.15);
    }

    .spk-timeline-dot.in-progress::after {
        content: "";
        position: absolute;
        inset: -7px;
        border-radius: 999px;
        border: 1.5px solid rgba(198, 166, 71, 0.6);
        opacity: 0;
        pointer-events: none;
        animation: timeline-pulse 1.6s ease-out infinite;
    }

    .spk-timeline-dot.done {
        background: #0f8f60;
        color: #ffffff;
        border: none;
    }

    .spk-timeline-connector {
        width: 16px;
        height: 1.5px;
        background: #d0dce9;
        flex: 0 0 auto;
    }

    .spk-timeline-label {
        font-size: 0.62rem;
        color: #8da1b7;
        font-weight: 500;
        line-height: 1.2;
        word-break: break-word;
    }

    .spk-timeline-item.in-progress .spk-timeline-label {
        color: #7a6b1a;
        font-weight: 600;
    }

    .spk-timeline-item.done .spk-timeline-label {
        color: #0f8f60;
    }

    .spk-empty-state {
        text-align: center;
        padding: 1.5rem;
        color: #8da1b7;
    }

    .spk-empty-state p {
        margin: 0;
        font-size: 0.85rem;
    }

    @keyframes timeline-pulse {
        0% { transform: scale(0.78); opacity: 0.95; }
        70% { transform: scale(1.18); opacity: 0; }
        100% { transform: scale(1.18); opacity: 0; }
    }

    @media (max-width: 1024px) {
        .kpi-grid-modern {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 720px) {
        .production-header h1 {
            font-size: 1.2rem;
        }

        .kpi-grid-modern {
            grid-template-columns: 1fr;
        }

        .spk-timeline {
            gap: 0.3rem;
        }

        .spk-timeline-item {
            min-width: 65px;
        }

        .spk-timeline-label {
            font-size: 0.58rem;
        }
    }
</style>

<div class="production-page">
    <div class="production-header">
        <h1>Dashboard Produksi</h1>
        <p>Monitor semua pekerjaan konveksi yang sedang berjalan</p>
    </div>

<!-- New Production Jobs Alert -->
@if($newProductionOrders->count() > 0)
    <div class="finishing-alert" style="background: linear-gradient(135deg, #eef7ff 0%, #e6f3ff 100%); border-color: #b8deff;">
        <div class="finishing-alert-icon">🔔</div>
        <div class="finishing-alert-content">
            <p class="finishing-alert-text" style="color: #0c4a7e;">
                Ada <span class="finishing-alert-count" style="color: #083c6b;">{{ $newProductionOrders->count() }} pesanan baru</span> yang sudah diverifikasi pembayarannya dan <strong>siap untuk mulai diproduksi</strong> 
                (Order: {{ $newProductionOrders->take(3)->pluck('order_code')->implode(', ') }}{{ $newProductionOrders->count() > 3 ? ', dll' : '' }}).
                <a href="#progress-produksi" style="color: #0c7fb6; font-weight: bold; text-decoration: underline; margin-left: 0.5rem;">Lihat Surat Perintah Kerja →</a>
            </p>
        </div>
    </div>
@endif

<!-- Ready for Finishing (Settled) Alert -->
@if($readyForFinishingOrders->count() > 0)
    <div class="finishing-alert" style="background: linear-gradient(135deg, #eff9f1 0%, #e5f6e8 100%); border-color: #b5e8c1;">
        <div class="finishing-alert-icon">✅</div>
        <div class="finishing-alert-content">
            <p class="finishing-alert-text" style="color: #12572b;">
                Terdapat <span class="finishing-alert-count" style="color: #0e4421;">{{ $readyForFinishingOrders->count() }} pesanan</span> yang pelunasannya sudah dikonfirmasi. Silakan <strong>lanjut selesaikan tahap Finishing</strong>
                (Order: {{ $readyForFinishingOrders->take(3)->pluck('order_code')->implode(', ') }}{{ $readyForFinishingOrders->count() > 3 ? ', dll' : '' }}).
                <a href="#progress-produksi" style="color: #0f8f60; font-weight: bold; text-decoration: underline; margin-left: 0.5rem;">Lihat Surat Perintah Kerja →</a>
            </p>
        </div>
    </div>
@endif

<!-- Finishing Settlement Alert -->
@if($finishingWaitingSettlement > 0)
    <div class="finishing-alert">
        <div class="finishing-alert-icon">🚫</div>
        <div class="finishing-alert-content">
            <p class="finishing-alert-text">
                Ada <span class="finishing-alert-count">{{ $finishingWaitingSettlement }} pesanan</span> pada tahap Finishing yang <strong>belum dapat dilanjutkan</strong> karena pelunasan belum dikonfirmasi.
            </p>
        </div>
    </div>
@endif

    <div class="kpi-grid-modern">
        <a href="{{ route('production.index') }}" style="text-decoration: none; color: inherit;">
            <article class="kpi-card-modern active">
                <p class="kpi-title">Sedang Proses</p>
                <div class="kpi-value">{{ $processingCount }}</div>
                <div class="kpi-note">Order aktif dalam produksi</div>
            </article>
        </a>

        <a href="{{ route('production.index') }}" style="text-decoration: none; color: inherit;">
            <article class="kpi-card-modern completed">
                <p class="kpi-title">Selesai Produksi</p>
                <div class="kpi-value">{{ $completedProductionCount }}</div>
                <div class="kpi-note">Tahap produksi sudah selesai</div>
            </article>
        </a>

        <a href="{{ route('production.index') }}" style="text-decoration: none; color: inherit;">
            <article class="kpi-card-modern blocked">
                <p class="kpi-title">Menunggu Pelunasan</p>
                <div class="kpi-value">{{ $waitingSettlement }}</div>
                <div class="kpi-note">Blocked finishing</div>
            </article>
        </a>
    </div>

<!-- SPK Production Dashboard -->
<div class="spk-dashboard-section" id="progress-produksi">
    <div class="spk-section-header">
        <h2 class="spk-dashboard-title">SPK Aktif (Sedang Dikerjakan)</h2>
        <a href="{{ route('production.index') }}" class="view-all-spk-btn">Lihat Semua SPK →</a>
    </div>

    @if($activeOrdersWithSteps->count() > 0)
        @foreach($activeOrdersWithSteps as $order)
            @php
                $displaySteps = collect();
                foreach ($order->productionSteps->sortBy('step_order')->values() as $step) {
                    $displayName = $timelineStepName((string) $step->step_name);
                    if ($displayName === null) {
                        continue;
                    }

                    $displaySteps->push([
                        'status' => $step->status,
                        'title' => $displayName,
                    ]);
                }

                $isOrderCompleted = $displaySteps->isNotEmpty() && $displaySteps->every(static fn ($step) => $step['status'] === 'done');

                if ($isOrderCompleted) {
                    $statusClass = 'production-complete';
                    $statusLabel = 'Selesai Produksi';
                } else {
                    $statusClass = match($order->order_status) {
                        'verified_payment' => 'verified-payment',
                        'finishing_waiting_settlement' => 'waiting-settlement',
                        default => 'in-production'
                    };

                    $statusLabel = match($order->order_status) {
                        'verified_payment' => 'Siap Produksi',
                        'finishing_waiting_settlement' => 'Menunggu Pelunasan',
                        default => 'Sedang Produksi'
                    };
                }
            @endphp
            
            <div class="spk-card">
                <div class="spk-card-header">
                    <div class="spk-info">
                        <h3 class="spk-code">
                            {{ $order->workOrder?->spk_number ?? 'SPK-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                        </h3>
                        <div class="spk-order-info">
                            {{ $order->order_code }} • {{ $order->customer_name }}
                        </div>
                        <div class="spk-order-info">
                            {{ $order->product_name }} ({{ $order->total_pcs }} pcs)
                            @if($order->estimated_finish_date)
                                <br><span style="color: #d95f18; font-weight: 700;">Estimasi Selesai: {{ \Carbon\Carbon::parse($order->estimated_finish_date)->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="spk-status-actions">
                        <span class="spk-status-badge {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                        <a href="{{ route('production.show', $order) }}" class="spk-detail-link">Detail</a>
                    </div>
                </div>

                @if($displaySteps->count() > 0)
                    <div class="spk-timeline">
                        @foreach($displaySteps as $index => $step)
                            @if($index > 0)
                                <div class="spk-timeline-connector"></div>
                            @endif
                            
                            <div class="spk-timeline-item">
                                @php
                                    $dotClass = str_replace('_', '-', $step['status']);
                                @endphp
                                <div class="spk-timeline-dot {{ $dotClass }}">
                                    @if($step['status'] === 'done')
                                        ✓
                                    @elseif($step['status'] === 'in_progress')
                                        →
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                                <div class="spk-timeline-label">
                                    {{ $step['title'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="spk-empty-state">
                        <p>Belum ada tahap produksi yang terdaftar</p>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="spk-empty-state">
            <p>✨ Tidak ada SPK yang sedang dikerjakan saat ini</p>
        </div>
    @endif
    </div>
</div>
@endsection
