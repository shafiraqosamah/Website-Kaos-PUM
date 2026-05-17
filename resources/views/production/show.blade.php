@extends('layouts.app')

@section('content')
@php
    $steps = $order->productionSteps;
    $isAdminViewer = $user?->hasRole(\App\Models\User::ROLE_ADMIN) ?? false;

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

    $displayProductionSteps = collect();

    foreach ($steps->sortBy('step_order')->values() as $step) {
        $displayName = $timelineStepName((string) $step->step_name);
        if ($displayName === null) {
            continue;
        }

        $displayProductionSteps->push([
            'step' => $step,
            'title' => $displayName,
            'status' => $step->status,
            'updated_at' => $step->updated_at,
        ]);
    }

    $totalSteps = max($displayProductionSteps->count(), 1);
    $doneSteps = $displayProductionSteps->where('status', 'done')->count();
    $activeSteps = $displayProductionSteps->where('status', 'in_progress')->count();
    $pendingSteps = $displayProductionSteps->where('status', 'pending')->count();
    $progress = (int) round(($doneSteps / $totalSteps) * 100);

    $paymentVerifiedAt = optional(
        $order->payments
            ->where('status', 'verified')
            ->sortByDesc('verified_at')
            ->first()
    )->verified_at;

    $isVerificationDone = in_array($order->order_status, ['verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement', 'production_done_waiting_admin', 'ready_for_pickup', 'completed'], true);

    $displaySteps = collect([
        [
            'sequence' => 1,
            'title' => 'Verifikasi & Konfirmasi Pesanan',
            'status' => $isVerificationDone ? 'done' : 'pending',
            'updated_at' => $isVerificationDone ? $paymentVerifiedAt : null,
            'is_verification' => true,
            'step' => null,
        ],
    ]);

    foreach ($displayProductionSteps->values() as $index => $stepData) {
        $displaySteps->push([
            'sequence' => $index + 2,
            'title' => $stepData['title'],
            'status' => $stepData['status'],
            'updated_at' => $stepData['updated_at'],
            'is_verification' => false,
            'step' => $stepData['step'],
        ]);
    }

    $allProductionStepsDone = $displayProductionSteps->where('status', '!=', 'done')->isEmpty();
    $canAdminVerifyFinal = $isAdminViewer
        && $allProductionStepsDone
        && ((float) $order->remaining_amount <= 0)
        && ($order->order_status === 'production_done_waiting_admin');
@endphp

<style>
    .prod-detail-shell {
        width: min(1240px, 100% - 2.75rem);
        margin: 0 auto;
    }

    .prod-detail-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .prod-detail-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.7rem;
        flex-wrap: wrap;
        margin-bottom: 1.2rem;
    }

    .prod-detail-head h1 {
        margin: 0 0 0.35rem;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .prod-summary {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 1rem;
        margin: 0 0 1rem;
    }

    .prod-summary .card-mini {
        border: 1px solid #d9e2ec;
        border-top: 4px solid #c6d3df;
        border-radius: 12px;
        background: #ffffff;
        padding: 1rem 1.05rem;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.03);
    }

    .prod-summary .card-mini.progress {
        border-top-color: #0c7fb6;
    }

    .prod-summary .card-mini.active {
        border-top-color: #0f8f60;
    }

    .prod-summary .card-mini.pending {
        border-top-color: #d95f18;
    }

    .production-spk-link {
     margin: 0 0 0.35rem;
        font-size: clamp(1.18rem, 1.8vw, 1.2rem);
        line-height: 1.08;
        color: #0f2e56;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
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

    .prod-steps-card {
        background: #fbfdff;
        border: 1px solid #e3eaf1;
        border-radius: 14px;
        padding: 1.2rem 1.35rem;
    }

    .prod-steps-list {
        display: grid;
        gap: 1rem;
    }

    .prod-step-item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 0.9rem;
        align-items: flex-start;
    }

    .prod-step-marker {
        position: relative;
        min-height: 100%;
        display: flex;
        justify-content: center;
    }

    .prod-step-line {
        position: absolute;
        top: 34px;
        bottom: -12px;
        width: 2px;
        background: #d5e0ea;
    }

    .prod-step-item.done .prod-step-line {
        background: #0f8f60;
    }

    .prod-step-item.in_progress .prod-step-line {
        background: #c6a647;
    }

    .prod-step-dot {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.74rem;
        border: 2px solid #c6d6e5;
        background: #f7fbff;
        color: #6281a0;
        z-index: 1;
    }

    .prod-step-item.done .prod-step-dot {
        border-color: #8cc8ad;
        background: #eaf7ef;
        color: #186847;
    }

    .prod-step-item.in_progress .prod-step-dot {
        border-color: #e0c578;
        background: #fdf6e7;
        color: #9a6a00;
        box-shadow: 0 0 0 4px rgba(224, 197, 120, 0.22);
        position: relative;
    }

    .prod-step-item.in_progress .prod-step-dot::after {
        content: "";
        position: absolute;
        inset: -7px;
        border-radius: 999px;
        border: 2px solid rgba(224, 197, 120, 0.75);
        opacity: 0;
        pointer-events: none;
        animation: prod-step-pulse 1.7s ease-out infinite;
    }

    .prod-step-content {
        border: 1px solid #e3eaf1;
        border-radius: 12px;
        background: #ffffff;
        padding: 0.85rem 0.95rem;
    }

    .prod-step-head {
        display: flex;
        justify-content: space-between;
        gap: 0.9rem;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .prod-step-title {
        margin: 0;
        font-size: 1rem;
        color: #0d2749;
        font-weight: 700;
    }

    .prod-step-item.done .prod-step-title {
        color: #0f8f60;
    }

    .prod-step-item.in_progress .prod-step-title {
        color: #c49a2e;
    }

    .prod-step-state {
        margin: 0.2rem 0 0;
        color: #8da1b7;
        font-size: 0.86rem;
        font-weight: 600;
    }

    .prod-step-date {
        margin: 0.12rem 0 0;
        color: #b9871e;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .prod-step-item.done .prod-step-state {
        color: #0f8f60;
    }

    .prod-step-item.in_progress .prod-step-state {
        color: #c49a2e;
    }

    .prod-step-actions {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .prod-step-actions form {
        margin: 0;
    }

    .prod-step-actions.readonly {
        align-items: center;
    }

    .prod-step-readonly-note {
        font-size: 0.75rem;
        font-weight: 700;
        color: #7b90a7;
        background: #f3f7fb;
        border: 1px solid #d7e2ec;
        border-radius: 999px;
        padding: 0.26rem 0.55rem;
        white-space: nowrap;
    }

    .step-btn {
        border: 1px solid #d7e2ec;
        border-radius: 8px;
        background: #ffffff;
        color: #2b445f;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 0.35rem 0.62rem;
        cursor: pointer;
    }

    .step-btn:hover {
        border-color: #b9cde2;
        background: #f7fbff;
    }

    .step-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        border-color: #d8e1ea;
        background: #f3f7fb;
        color: #8398ad;
    }

    .step-btn.is-active.pending {
        border-color: #d95f18;
        color: #9a481a;
        background: #fff5ef;
    }

    .step-btn.is-active.in-progress {
        border-color: #c6a647;
        color: #7a6b1a;
        background: #fff8e8;
    }

    .step-btn.is-active.done {
        border-color: #0f8f60;
        color: #0f8f60;
        background: #edf9f3;
    }

    .prod-step-lock-note {
        margin-top: 0.45rem;
        font-size: 0.74rem;
        font-weight: 700;
        color: #8b5a11;
        background: #fff5e7;
        border: 1px solid #ecd6af;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
        display: inline-flex;
        align-items: center;
    }

    .production-settlement-alert {
        margin-top: 0.95rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #b53025, #c73729);
        padding: 0.85rem 0.95rem;
        color: #fff;
        display: flex;
        gap: 0.8rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .production-settlement-alert h4 {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
    }

    .production-settlement-alert p {
        margin: 0.3rem 0 0;
        font-size: 0.84rem;
        line-height: 1.4;
        max-width: 680px;
    }

    .production-settlement-alert strong {
        color: #ffe9c2;
    }

    .production-settlement-alert-note {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        border: 1px solid rgba(255, 233, 194, 0.55);
        background: rgba(255, 233, 194, 0.2);
        color: #fff7e5;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.36rem 0.7rem;
        white-space: nowrap;
    }

    @keyframes prod-step-pulse {
        0% { transform: scale(0.78); opacity: 0.95; }
        70% { transform: scale(1.18); opacity: 0; }
        100% { transform: scale(1.18); opacity: 0; }
    }

    @media (max-width: 980px) {
        .prod-detail-shell {
            width: min(100%, calc(100% - 1.5rem));
        }

        .prod-detail-page {
            padding: 1.2rem 1.1rem;
        }

        .prod-summary {
            grid-template-columns: 1fr;
        }

        .prod-step-item {
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 0.7rem;
        }

        .prod-step-dot {
            width: 28px;
            height: 28px;
            font-size: 0.74rem;
        }

        .prod-step-line {
            top: 30px;
        }
    }
</style>

<div class="prod-detail-shell">
    <div class="prod-detail-page">
        <div class="prod-detail-head">
        <div>
            <h1>Progress Produksi {{ $order->order_code }}</h1>
            <p class="muted" style="margin:0;">SPK: {{ $order->workOrder?->spk_number ?? '-' }} | Pelanggan: {{ $order->user->name }}</p>
            @if($order->estimated_finish_date)
                <p class="muted" style="margin:0.3rem 0 0; color:#d95f18; font-weight:700;">Estimasi Selesai: {{ \Carbon\Carbon::parse($order->estimated_finish_date)->format('d M Y') }}</p>
            @endif
        </div>
        @if($order->workOrder)
            <a class="btn btn-alt production-spk-link" href="{{ route('production.spk', $order) }}" target="_blank">📝 Dokumen SPK</a>
        @endif
    </div>

    <div class="prod-summary">
    <div class="card-mini progress">
        <div class="muted">Progres Tahapan</div>
        <div style="display:flex; justify-content:space-between; align-items:baseline; gap:0.5rem;">
            <strong>{{ $doneSteps }}/{{ $displayProductionSteps->count() }} tahap selesai</strong>
            <strong>{{ $progress }}%</strong>
        </div>
        <div class="prod-track">
            <div class="prod-fill" style="width: {{ $progress }}%;"></div>
        </div>
    </div>
    <div class="card-mini active">
        <div class="muted">Sedang Dikerjakan</div>
        <div style="font-size:1.5rem; font-family:'Sora', sans-serif; line-height:1.1;">{{ $activeSteps }}</div>
    </div>
    <div class="card-mini pending">
        <div class="muted">Belum Mulai</div>
        <div style="font-size:1.5rem; font-family:'Sora', sans-serif; line-height:1.1;">{{ $pendingSteps }}</div>
    </div>
</div>

<div class="prod-steps-card">
    <div class="prod-steps-list">
        @foreach($displaySteps as $stepData)
            @php
                $previousProductionStep = $displaySteps
                    ->take($loop->index)
                    ->where('is_verification', false)
                    ->last();

                $isLockedByPreviousStep = ! $stepData['is_verification']
                    && $previousProductionStep
                    && $previousProductionStep['status'] !== 'done';
            @endphp
            <article class="prod-step-item {{ $stepData['status'] }}">
                <div class="prod-step-marker">
                    <span class="prod-step-dot">
                        @if($stepData['status'] === 'done')
                            ✓
                        @else
                            {{ $stepData['sequence'] }}
                        @endif
                    </span>
                    @if(!$loop->last)
                        <span class="prod-step-line"></span>
                    @endif
                </div>

                <div class="prod-step-content">
                    <div class="prod-step-head">
                        <div>
                            <h4 class="prod-step-title">{{ $stepData['title'] }}</h4>
                            <p class="prod-step-state">{{ $stepLabel($stepData['status']) }}</p>
                            @if($stepData['status'] === 'done' && !empty($stepData['updated_at']))
                                <p class="prod-step-date">{{ optional($stepData['updated_at'])->format('Y-m-d') }}</p>
                            @endif
                        </div>

                        @if($stepData['is_verification'])
                            <div class="prod-step-actions readonly">
                                <span class="prod-step-readonly-note">Tahap non produksi</span>
                            </div>
                        @else
                            @if($isAdminViewer)
                                <div class="prod-step-actions readonly">
                                    <span class="prod-step-readonly-note">Hanya dapat diubah oleh tim produksi</span>
                                </div>
                            @elseif($isLockedByPreviousStep)
                                <span class="prod-step-lock-note">Selesaikan tahap sebelumnya terlebih dahulu</span>
                            @else
                                @php($isStepDone = $stepData['status'] === 'done')
                                @php($isStepPending = $stepData['status'] === 'pending')
                                @php($isFinishingStep = strtolower(trim($stepData['title'])) === 'finishing')
                                <div class="prod-step-actions">
                                    <form method="POST" action="{{ route('production.step.update', [$order, $stepData['step']]) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="step-btn {{ $stepData['status'] === 'pending' ? 'is-active pending' : '' }}" {{ $isStepDone ? 'disabled' : '' }}>Belum Mulai</button>
                                    </form>

                                    <form method="POST" action="{{ route('production.step.update', [$order, $stepData['step']]) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="step-btn {{ $stepData['status'] === 'in_progress' ? 'is-active in-progress' : '' }}" {{ $isStepDone || ($isFinishingStep && $order->order_status === 'finishing_waiting_settlement') ? 'disabled' : '' }}>Sedang Dikerjakan</button>
                                    </form>

                                    <form method="POST" action="{{ route('production.step.update', [$order, $stepData['step']]) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="done">
                                        <button type="submit" class="step-btn {{ $stepData['status'] === 'done' ? 'is-active done' : '' }}" {{ $isStepDone || $isStepPending || ($isFinishingStep && $order->order_status === 'finishing_waiting_settlement') ? 'disabled' : '' }}>Selesai</button>
                                    </form>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if($order->order_status === 'finishing_waiting_settlement')
        <div class="production-settlement-alert">
            <div>
                <h4>Menunggu Pelunasan Pelanggan</h4>
                <p>Tahapan finishing tidak dapat dilakukan sebelum pelunasan terverifikasi.</p>
            </div>
            <span class="production-settlement-alert-note">Finishing Menunggu Pelunasan</span>
        </div>
    @endif

    @if($isAdminViewer && $order->order_status === 'production_done_waiting_admin')
        <div class="alert" style="margin-top:0.9rem; display:flex; justify-content:space-between; align-items:center; gap:0.8rem; flex-wrap:wrap;">
            <span>Produksi sudah selesai. Lakukan pengecekan dan verifikasi akhir kelayakan produk serta informasi <strong>siap diambil</strong> ke customer.</span>
            @if($canAdminVerifyFinal)
                <form method="POST" action="{{ route('production.verify-final', $order) }}" style="margin:0;">
                    @csrf
                    <button class="btn btn-brand" type="submit">Verifikasi Hasil</button>
                </form>
            @else
                <span class="prod-step-readonly-note">Selesaikan seluruh tahap & pelunasan sebelum ACC</span>
            @endif
        </div>
    @endif

    @if($isAdminViewer && in_array($order->order_status, ['ready_for_pickup', 'completed'], true))
        <div class="alert" style="margin-top:0.9rem; display:flex; justify-content:space-between; align-items:center; gap:0.8rem; flex-wrap:wrap; border-color:#c7e7d6; background:#effaf3; color:#13603d;">
            <span>Status pengambilan dapat diperbarui oleh admin sesuai kondisi barang: <strong>Pesanan Siap Ambil</strong> atau <strong>Pesanan Selesai</strong>.</span>
            <form method="POST" action="{{ route('production.pickup-status', $order) }}" style="margin:0; display:flex; align-items:center; gap:0.55rem; flex-wrap:wrap;">
                @csrf
                <select name="order_status" class="input" style="min-width: 220px; height: 40px;" @if($order->order_status === 'completed') disabled @endif>
                    <option value="ready_for_pickup" @selected($order->order_status === 'ready_for_pickup')>Pesanan Siap Diambil</option>
                    <option value="completed" @selected($order->order_status === 'completed')>Pesanan Selesai</option>
                </select>
                @if($order->order_status === 'completed')
                    <span class="prod-step-readonly-note">Pesanan sudah selesai final.</span>
                @else
                    <button class="btn btn-brand" type="submit">Simpan Status</button>
                @endif
            </form>
        </div>
    @endif
</div>
    </div>
</div>
@endsection
