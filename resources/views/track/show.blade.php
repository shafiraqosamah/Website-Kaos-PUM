@extends('layouts.app')

@section('title', 'Detail Pelacakan | PT Panji Usaha Mulia')

@section('content')
<style>
.tracking-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.tracking-header { border-bottom: 1px solid #eee; padding-bottom: 1.5rem; margin-bottom: 2rem; }
.tracking-title { color: #0d2749; margin-top: 0; }
.order-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; }
.meta-item { display: flex; flex-direction: column; }
.meta-label { font-size: 0.85rem; color: #666; font-weight: bold; }
.meta-value { font-size: 1.1rem; color: #333; }
.status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-weight: bold; }
.status-badge.submitted { background: #e2e8f0; color: #475569; }
.status-badge.verified_payment { background: #cffafe; color: #0891b2; }
.status-badge.in_production { background: #fef08a; color: #854d0e; }
.status-badge.finishing_waiting_settlement { background: #fed7aa; color: #c2410c; }
.status-badge.ready_for_pickup { background: #d9f99d; color: #3f6212; }
.status-badge.completed { background: #bbf7d0; color: #166534; }
.status-badge.cancelled { background: #fecaca; color: #991b1b; }
    .timeline-list { list-style: none; margin: 0; padding: 0; }
    .timeline-item { display: grid; grid-template-columns: 34px minmax(0, 1fr); gap: 0.72rem; position: relative; padding-bottom: 1.5rem; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-item::before { content: ""; position: absolute; left: 16px; top: 22px; bottom: -4px; width: 2px; background: #d6e1eb; }
    .timeline-item:last-child::before { display: none; }
    .timeline-dot { width: 28px; height: 28px; border-radius: 999px; border: 2px solid #c6d6e5; background: #f7fbff; color: #6281a0; font-size: 0.74rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; position: relative; z-index: 1; }
    .timeline-dot::after { content: ""; position: absolute; inset: -8px; border-radius: 999px; border: 2px solid transparent; opacity: 0; pointer-events: none; }
    .timeline-item-done .timeline-dot { border-color: #8cc8ad; background: #eaf7ef; color: #186847; }
    .timeline-item-progress .timeline-dot { border-color: #e0c578; background: #fdf6e7; color: #9a6a00; box-shadow: 0 0 0 4px rgba(224, 197, 120, 0.22); }
    .timeline-item-progress .timeline-dot::after { border-color: rgba(224, 197, 120, 0.75); animation: timeline-pulse 1.6s ease-out infinite; }
    @keyframes timeline-pulse { 0% { transform: scale(0.78); opacity: 0.95; } 70% { transform: scale(1.18); opacity: 0; } 100% { transform: scale(1.18); opacity: 0; } }
    .timeline-item-title { margin: 0; font-size: 0.98rem; font-weight: 700; color: #102a43; }
    .timeline-item-done .timeline-item-title { color: #1f7a48; }
    .timeline-item-progress .timeline-item-title { color: #b17f11; }
    .timeline-item-note { margin: 0.12rem 0 0; color: #728da8; font-size: 0.82rem; }
    .timeline-item-date { margin: 0.12rem 0 0; color: #7d97b1; font-size: 0.78rem; font-weight: 600; }
</style>

<div class="auth-container">
    <div class="tracking-container">
        <div class="tracking-header">
            <h2 class="tracking-title">Detail Status Produksi</h2>
            <div class="order-meta">
                <div class="meta-item">
                    <span class="meta-label">Nomor Pesanan</span>
                    <span class="meta-value">{{ $order->order_code }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status Pesanan</span>
                    <span class="meta-value">
                        @php
                            $statusLabels = [
                                'submitted' => 'Menunggu Verifikasi Admin (Max 2x24 Jam)',
                                'verified_payment' => 'Pembayaran Diverifikasi',
                                'in_production' => 'Sedang Diproduksi',
                                'finishing_waiting_settlement' => 'Finishing (Menunggu Pelunasan Max 2x24 Jam)',
                                'ready_for_pickup' => 'Siap Diambil',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ];
                        @endphp
                        <span class="status-badge {{ $order->order_status }}">{{ $statusLabels[$order->order_status] ?? $order->order_status }}</span>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Model Produk</span>
                    <span class="meta-value">{{ $order->product_model }}</span>
                </div>
            </div>
        </div>

        <h3>Status Produksi</h3>
        @if ($order->productionSteps->isEmpty())
            <p style="color: #666; font-style: italic;">Tahapan produksi belum dimulai.</p>
        @else
            @php
                $timelineStepName = function (string $name): ?string {
                    $normalized = strtolower(trim($name));
                    if (str_contains($normalized, 'cutting')) return 'Cutting';
                    if (str_contains($normalized, 'persiapan bahan')) return null;
                    return match ($normalized) {
                        'jahit' => 'Jahit / Obras',
                        'sablon' => 'Sablon / Bordir / Printing',
                        'steam' => 'Steam & Pressing',
                        'finishing' => 'Finishing',
                        default => $name,
                    };
                };

                $timelineStateLabel = function (string $status): string {
                    return match ($status) {
                        'done' => 'Selesai',
                        'in_progress' => 'Sedang dikerjakan',
                        default => 'Menunggu giliran',
                    };
                };

                $timelineStateClass = function (string $status): string {
                    return match ($status) {
                        'done' => 'timeline-item-done',
                        'in_progress' => 'timeline-item-progress',
                        default => 'timeline-item-pending',
                    };
                };

                $productionSteps = $order->productionSteps->sortBy('step_order')->values();
                $timelineSteps = [];
                $stepCounter = 1;
                foreach ($productionSteps as $step) {
                    $displayName = $timelineStepName((string) $step->step_name);
                    if ($displayName === null) continue;

                    $timelineSteps[] = [
                        'order' => $stepCounter,
                        'title' => $displayName,
                        'status' => $step->status,
                        'updated_at' => $step->updated_at,
                    ];
                    $stepCounter++;
                }
            @endphp
            <ol class="timeline-list" style="margin-top: 1.5rem;">
                @foreach ($timelineSteps as $timeline)
                    @php
                        $timelineClass = $timelineStateClass((string) $timeline['status']);
                    @endphp
                    <li class="timeline-item {{ $timelineClass }}">
                        <span class="timeline-dot">
                            @if ($timeline['status'] === 'done')
                                ✓
                            @else
                                {{ $timeline['order'] }}
                            @endif
                        </span>
                        <div>
                            <p class="timeline-item-title">{{ $timeline['title'] }}</p>
                            <p class="timeline-item-note">{{ $timelineStateLabel((string) $timeline['status']) }}</p>
                            @if (($timeline['status'] === 'done') && ! empty($timeline['updated_at']))
                                <p class="timeline-item-date">{{ optional($timeline['updated_at'])->translatedFormat('d M Y') }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        <div style="margin-top: 3rem; text-align: center;">
            <a href="{{ route('track.index') }}" class="btn btn-outline" style="padding: 0.5rem 1rem; border: 1px solid #ccc; border-radius: 6px; text-decoration: none; color: #333;">Lacak Pesanan Lain</a>
        </div>
    </div>
</div>
@endsection
