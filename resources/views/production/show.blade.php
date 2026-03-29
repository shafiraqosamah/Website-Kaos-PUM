@extends('layouts.app')

@section('content')
@php
    $steps = $order->productionSteps;
    $totalSteps = max($steps->count(), 1);
    $doneSteps = $steps->where('status', 'done')->count();
    $activeSteps = $steps->where('status', 'in_progress')->count();
    $progress = (int) round(($doneSteps / $totalSteps) * 100);

    $stepLabel = static function (string $status): string {
        return match ($status) {
            'pending' => 'Belum Mulai',
            'in_progress' => 'Sedang Dikerjakan',
            'done' => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    };

    $stepClass = static function (string $status): string {
        return match ($status) {
            'pending' => 'status-warning',
            'in_progress' => 'status-info',
            'done' => 'status-success',
            default => 'status-neutral',
        };
    };
@endphp

<style>
    .prod-detail-head {
        background: linear-gradient(145deg, #f2fbff, #fff7ec 76%);
        border: 1px solid #d8e4ee;
    }

    .prod-summary {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }

    .prod-summary .card-mini {
        border: 1px solid #d8e4ee;
        border-radius: 14px;
        background: #fff;
        padding: 0.9rem 1rem;
    }

    .prod-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e4edf4;
        overflow: hidden;
        margin-top: 0.45rem;
    }

    .prod-fill {
        height: 10px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0f7b8f, #1ca4bc);
    }

    .prod-form {
        display: flex;
        gap: 0.55rem;
        max-width: 360px;
    }

    @media (max-width: 980px) {
        .prod-summary {
            grid-template-columns: 1fr;
        }

        .prod-form {
            max-width: 100%;
        }
    }
</style>

<div class="card prod-detail-head">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.7rem; flex-wrap:wrap;">
        <div>
            <h1 style="margin-bottom:0.35rem;">Progress Produksi {{ $order->order_code }}</h1>
            <p class="muted" style="margin:0;">SPK: {{ $order->workOrder?->spk_number ?? '-' }} | Pelanggan: {{ $order->user->name }}</p>
        </div>
        @if($order->workOrder)
            <a class="btn btn-alt" href="{{ route('production.spk', $order) }}" target="_blank">Buka Dokumen SPK</a>
        @endif
    </div>
</div>

<div class="prod-summary">
    <div class="card-mini">
        <div class="muted">Progres Tahapan</div>
        <div style="display:flex; justify-content:space-between; align-items:baseline; gap:0.5rem;">
            <strong>{{ $doneSteps }}/{{ $steps->count() }} tahap selesai</strong>
            <strong>{{ $progress }}%</strong>
        </div>
        <div class="prod-track">
            <div class="prod-fill" style="width: {{ $progress }}%;"></div>
        </div>
    </div>
    <div class="card-mini">
        <div class="muted">Sedang Dikerjakan</div>
        <div style="font-size:1.5rem; font-family:'Sora', sans-serif; line-height:1.1;">{{ $activeSteps }}</div>
    </div>
    <div class="card-mini">
        <div class="muted">Belum Mulai</div>
        <div style="font-size:1.5rem; font-family:'Sora', sans-serif; line-height:1.1;">{{ $steps->where('status', 'pending')->count() }}</div>
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <table>
        <thead><tr><th>Tahap</th><th>Status Saat Ini</th><th>Update</th></tr></thead>
        <tbody>
        @foreach($steps as $step)
            <tr>
                <td>
                    <strong>{{ $step->step_order }}. {{ $step->step_name }}</strong>
                </td>
                <td>
                    <span class="status-badge {{ $stepClass($step->status) }}">{{ $stepLabel($step->status) }}</span>
                </td>
                <td>
                    <form method="POST" action="{{ route('production.step.update', [$order, $step]) }}" class="prod-form">
                        @csrf
                        <select name="status">
                            <option value="pending" @selected($step->status==='pending')>Belum mulai</option>
                            <option value="in_progress" @selected($step->status==='in_progress')>Sedang dikerjakan</option>
                            <option value="done" @selected($step->status==='done')>Selesai</option>
                        </select>
                        <button class="btn btn-brand" type="submit">Simpan</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($order->order_status === 'finishing_waiting_settlement')
        <p class="alert alert-err" style="margin-top:0.9rem;">Menunggu pelunasan pelanggan. Tahap finishing tidak dapat ditandai selesai sebelum lunas terverifikasi.</p>
    @endif
</div>
@endsection
