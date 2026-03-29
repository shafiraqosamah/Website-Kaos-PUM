@extends('layouts.app')

@section('content')
@php
    $statusLabel = static function (string $status): string {
        return match ($status) {
            'verified_payment' => 'Pembayaran Terverifikasi',
            'in_production' => 'Sedang Produksi',
            'finishing_waiting_settlement' => 'Finishing Menunggu Pelunasan',
            'completed' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    };

    $statusClass = static function (string $status): string {
        return match ($status) {
            'verified_payment' => 'status-info',
            'in_production' => 'status-warning',
            'finishing_waiting_settlement' => 'status-danger',
            'completed' => 'status-success',
            default => 'status-neutral',
        };
    };

    $totalOrder = $activeOrders->count();
    $inProgress = $activeOrders->where('order_status', 'in_production')->count();
    $needSettlement = $activeOrders->where('order_status', 'finishing_waiting_settlement')->count();
    $historyCount = $completedOrders->count();
@endphp

<style>
    .prod-head {
        background: linear-gradient(145deg, #f4fbff, #fff7ee 72%);
        border: 1px solid #d8e4ee;
    }

    .prod-kpi {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }

    .prod-kpi .item {
        background: #fff;
        border: 1px solid #d8e4ee;
        border-radius: 14px;
        padding: 0.9rem 1rem;
    }

    .prod-kpi .item .value {
        margin-top: 0.25rem;
        font-size: 1.6rem;
        line-height: 1;
        font-family: 'Sora', sans-serif;
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

    @media (max-width: 980px) {
        .prod-kpi {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card prod-head">
    <h1 style="margin-bottom:0.35rem;">Modul Produksi</h1>
    <p class="muted" style="margin:0;">SPK aktif dan progres tahapan pengerjaan produksi kaos dalam satu tampilan yang ringkas.</p>
</div>

<div class="prod-kpi">
    <div class="item">
        <div class="muted">Total Order Produksi</div>
        <div class="value">{{ $totalOrder }}</div>
    </div>
    <div class="item">
        <div class="muted">Sedang Diproduksi</div>
        <div class="value">{{ $inProgress }}</div>
    </div>
    <div class="item">
        <div class="muted">Tertahan Pelunasan</div>
        <div class="value">{{ $needSettlement }}</div>
    </div>
    <div class="item">
        <div class="muted">Riwayat Produksi Selesai</div>
        <div class="value">{{ $historyCount }}</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0; margin-bottom:0.6rem;">Order Aktif Produksi</h3>
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
                    <a class="btn btn-outline" href="{{ route('production.show', $order) }}">Kelola Tahap</a>
                    @if($order->workOrder)
                        <a class="btn btn-alt" href="{{ route('production.spk', $order) }}" target="_blank">Buka SPK</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Belum ada order untuk produksi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:1rem; overflow:auto;">
    <div class="history-head">
        <h3 style="margin:0;">Riwayat Produksi Selesai</h3>
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
                    <a class="btn btn-outline" href="{{ route('production.show', $order) }}">Detail</a>
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
@endsection
