@extends('layouts.app')

@section('header_title', 'Daftar SPK & Produksi')

@section('content')
@php
    $isAdminViewer = $user?->hasRole(\App\Models\User::ROLE_ADMIN) ?? false;

    $statusLabel = static function (string $status): string {
        return match ($status) {
            'verified_payment' => 'Pembayaran Terverifikasi',
            'in_production' => 'Sedang Produksi',
            'finishing_waiting_settlement' => 'Finishing Menunggu Pelunasan',
            'production_done_waiting_admin' => 'Selesai Produksi',
            'ready_for_pickup' => 'Pesanan Siap Ambil',
            'completed' => 'Pesanan Selesai',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    };

    $statusClass = static function (string $status): string {
        return match ($status) {
            'verified_payment' => 'status-info',
            'in_production' => 'status-warning',
            'finishing_waiting_settlement' => 'status-danger',
            'production_done_waiting_admin' => 'status-accent',
            'ready_for_pickup' => 'status-success',
            'completed' => 'status-success',
            default => 'status-neutral',
        };
    };

    $totalOrder = $activeOrders->count();
    $inProgress = $activeOrders->where('order_status', 'in_production')->count();
    $needSettlement = $activeOrders->where('order_status', 'finishing_waiting_settlement')->count();
    $historyCount = $activeOrders->whereIn('order_status', ['production_done_waiting_admin', 'ready_for_pickup'])->count() + $completedOrders->count();
@endphp

<style>
    .production-module-shell {
        width: min(1240px, 100% - 2.75rem);
        margin: 0 auto;
    }

    .production-module-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .production-module-header {
        margin-bottom: 1rem;
    }

    .production-module-header h1 {
        margin: 0 0 0.35rem;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .production-module-header p {
        margin: 0;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .prod-kpi {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        max-width: 980px;
        width: 100%;
        margin: 0 auto 1rem;
    }

    .prod-kpi .item {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-top: 4px solid #c6d3df;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.03);
        transition: all 0.2s;
    }

    .prod-kpi .item.order {
        border-top-color: #142730;
    }

    .prod-kpi .item.active {
        border-top-color: #0c7fb6;
    }

    .prod-kpi .item.completed {
        border-top-color: #0f8f60;
    }

    .prod-kpi .item.blocked {
        border-top-color: #d95f18;
    }

    .prod-kpi .item .label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .prod-kpi .item .value {
        margin-top: 0.55rem;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        font-weight: 700;
        color: #0d2749;
    }

    .prod-progress {
        min-width: 190px;
    }

    .prod-progress-track {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #e4edf4;
        overflow: hidden;
        margin-top: 0.2rem;
    }

    .prod-progress-fill {
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0f7b8f, #1ca4bc);
    }

    .history-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.6rem;
    }

    .prod-table-card {
        background: #fbfdff;
        border: 1px solid #e3eaf1;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
        margin-top: 1rem;
    }

    .prod-table-card h3 {
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 700;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    @media (max-width: 1200px) {
        .prod-kpi {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            max-width: 720px;
        }
    }

    @media (max-width: 980px) {
        .production-module-shell {
            width: min(100%, calc(100% - 1.5rem));
        }

        .prod-kpi {
            grid-template-columns: 1fr;
            max-width: 420px;
        }

        .production-module-page,
        .prod-table-card {
            padding: 1.2rem 1.1rem;
        }
    }

    .action-detail {
        width: auto;
        min-width: 0;
        align-self: center;
        background: #ffffff;
        border: 1px solid #d9e2ec;
        color: #49637a;
        font-size: 0.76rem;
        padding: 0.42rem 0.7rem;
        border-radius: 9px;
    }

    .action-detail:hover {
        background: #f3f7fb;
    }
</style>

<div class="production-module-shell">
    <div class="production-module-page">
        <div class="production-module-header">
            <h1>Daftar SPK & Produksi</h1>
            <p>{{ $isAdminViewer ? 'Pantau surat perintah kerja dan verifikasi hasil akhir produksi' : 'Kelola surat perintah kerja dan update tahapan produksi' }}</p>
        </div>

        <div class="prod-kpi">
            <div class="item order">
                <div class="label">Total Order Produksi</div>
                <div class="value">{{ $totalOrder }}</div>
            </div>
            <div class="item active">
                <div class="label">Sedang Diproduksi</div>
                <div class="value">{{ $inProgress }}</div>
            </div>
            <div class="item blocked">
                <div class="label">Menunggu Pelunasan</div>
                <div class="value">{{ $needSettlement }}</div>
            </div>
            <div class="item completed">
                <div class="label">Selesai Produksi</div>
                <div class="value">{{ $historyCount }}</div>
            </div>
        </div>

        <div class="prod-table-card">
            <h3>Order Aktif Produksi</h3>
            <table>
                <thead>
                    <tr>
                        <th>SPK</th>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Estimasi Selesai</th>
                        <th>Status</th>
                        <th>Progres Tahap</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($activeOrders as $order)
                    @php
                        $steps = $order->productionSteps;
                        $totalSteps = max($steps->count(), 1);
                        $doneSteps = $steps->where('status', 'done')->count();
                        $progress = (int) round(($doneSteps / $totalSteps) * 100);
                    @endphp
                    <tr>
                        <td>{{ $order->workOrder?->spk_number ?? '-' }}</td>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->estimated_finish_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="status-badge {{ $statusClass($order->order_status) }}">{{ $statusLabel($order->order_status) }}</span>
                        </td>
                        <td class="prod-progress">
                            <strong>{{ $doneSteps }}/{{ $steps->count() }}</strong>
                            <div class="prod-progress-track">
                                <div class="prod-progress-fill" style="width: {{ $progress }}%;"></div>
                            </div>
                            <small class="muted">{{ $progress }}% selesai</small>
                        </td>
                        <td style="display:flex; gap:0.45rem; flex-wrap:wrap;">
                            <a class="btn btn-outline" href="{{ route('production.show', $order) }}">{{ $isAdminViewer ? 'Lihat Tahap' : 'Kelola Tahap' }}</a>
                            @if($isAdminViewer && $order->order_status === 'production_done_waiting_admin')
                                <form method="POST" action="{{ route('production.verify-final', $order) }}" style="margin:0;">
                                    @csrf
                                    <button class="btn btn-brand" type="submit">Verifikasi Hasil</button>
                                </form>
                            @endif
                            @if($isAdminViewer && $order->order_status === 'ready_for_pickup')
                                <form method="POST" action="{{ route('production.pickup-status', $order) }}" style="margin:0; display:flex; gap:0.35rem; align-items:center; flex-wrap:wrap;">
                                    @csrf
                                    <select name="order_status" class="input" style="min-width: 220px; height: 40px;">
                                        <option value="ready_for_pickup" selected>Pesanan Siap Diambil</option>
                                        <option value="completed">Pesanan Selesai</option>
                                    </select>
                                    <button class="btn btn-brand" type="submit">Simpan Status</button>
                                </form>
                            @endif
                            @if($order->workOrder)
                                <a class="btn btn-alt" href="{{ route('production.spk', $order) }}" target="_blank">Dokumen SPK</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada order untuk produksi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="prod-table-card" style="overflow:auto;">
            <div class="history-head">
                <h3>Riwayat Produksi Selesai</h3>
                <span class="muted">Rekap order yang sudah selesai diproduksi</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>SPK</th>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Spesifikasi</th>
                        <th>Total Pcs</th>
                        <th>Estimasi Selesai</th>
                        <th>Selesai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($completedOrders as $order)
                    @php
                        $finishingStep = $order->productionSteps
                            ->firstWhere('step_name', 'Finishing')
                            ?? $order->productionSteps->firstWhere('step_name', 'finishing');
                        $completedAt = $finishingStep?->completed_at ?? $order->updated_at;
                    @endphp
                    <tr>
                        <td>{{ $order->workOrder?->spk_number ?? '-' }}</td>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>
                            {{ $order->product_model ?? '-' }}<br>
                            <small class="muted">{{ $order->fabric }} | {{ $order->production_type ?? '-' }}</small>
                        </td>
                        <td>{{ $order->total_pcs }} pcs</td>
                        <td>{{ $order->estimated_finish_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $completedAt?->format('d/m/Y H:i') }}</td>
                        <td style="display:flex; gap:0.45rem; flex-wrap:wrap;">
                            <a class="btn action-detail" href="{{ route('production.show', $order) }}">Detail</a>
                            @if($order->workOrder)
                                <a class="btn btn-alt" href="{{ route('production.spk', $order) }}" target="_blank">SPK</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Belum ada riwayat produksi selesai.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
