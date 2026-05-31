@extends('layouts.app')

@section('header_title', 'Riwayat Pesanan')

@section('content')
@php
    $statusClass = function (string $status): string {
        return match ($status) {
            'pending', 'pending_verification', 'submitted' => 'status-warning',
            'admin_verified_waiting_payment' => 'status-success',
            'production_done_waiting_admin' => 'status-info',
            'verified', 'verified_payment', 'verified_dp', 'fully_paid', 'ready_for_pickup', 'completed', 'done' => 'status-success',
            'rejected' => 'status-danger',
            'in_production', 'in_progress' => 'status-info',
            'finishing_waiting_settlement' => 'status-warning',
            'held_waiting_settlement', 'cancelled' => 'status-danger',
            default => 'status-neutral',
        };
    };

    $statusLabel = function (string $status): string {
        return match ($status) {
            'submitted' => 'Menunggu Verifikasi Admin (Max 2x24 Jam)',
            'admin_verified_waiting_payment' => 'Terverifikasi',
            'production_done_waiting_admin' => 'Selesai Produksi',
            'verified_payment', 'verified_dp' => 'Menunggu Produksi',
            'in_production', 'in_progress' => 'Sedang Proses',
            'finishing_waiting_settlement' => 'Menunggu Pelunasan (Max 2x24 Jam)',
            'held_waiting_settlement' => 'Status Ditahan',
            'cancelled' => 'Dibatalkan',
            'ready_for_pickup' => 'Pesanan Siap Ambil',
            'completed', 'done' => 'Pesanan Selesai',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    };

    $latestPayment = $order->payments->sortByDesc('id')->first();
    $isHeld = $order->order_status === 'finishing_waiting_settlement' && $order->payment_deadline_at && \Carbon\Carbon::now()->isAfter($order->payment_deadline_at);

    $statusForDisplay = ($latestPayment?->status === 'rejected')
        ? 'rejected'
        : (($order->order_status === 'submitted' && (string) ($order->admin_verification_status ?? 'pending') === 'verified')
            ? 'admin_verified_waiting_payment'
            : ($isHeld ? 'held_waiting_settlement' : $order->order_status));

    $timelineStepName = function (string $name): ?string {
        $normalized = strtolower(trim($name));

        if (str_contains($normalized, 'cutting')) {
            return 'Cutting';
        }

        if (str_contains($normalized, 'persiapan bahan')) {
            return null;
        }

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

    $noteLines = preg_split('/\R/', (string) ($order->notes ?? '')) ?: [];
    $legacySpec = [
        'production_type' => null,
        'product_model' => null,
        'sleeve_type' => null,
    ];
    $legacyDesignPosition = null;
    $legacyDesignNote = null;
    $customerNotes = [];

    foreach ($noteLines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Jenis: ')) {
            $legacySpec['production_type'] = trim((string) preg_replace('/\s*\([^)]*\)\s*$/', '', \Illuminate\Support\Str::after($trimmed, 'Jenis: ')));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Teknik Sablon: ')) {
            $legacySpec['production_type'] = trim((string) \Illuminate\Support\Str::after($trimmed, 'Teknik Sablon: '));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Model: ')) {
            $legacySpec['product_model'] = trim((string) \Illuminate\Support\Str::after($trimmed, 'Model: '));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Ukuran Lengan: ')) {
            $legacySpec['sleeve_type'] = trim((string) \Illuminate\Support\Str::after($trimmed, 'Ukuran Lengan: '));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Tambahan ukuran XXL/XXXL: ')) {
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Posisi Desain: ')) {
            $legacyDesignPosition = trim((string) \Illuminate\Support\Str::after($trimmed, 'Posisi Desain: '));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Catatan desain: ')) {
            $legacyDesignNote = trim((string) \Illuminate\Support\Str::after($trimmed, 'Catatan desain: '));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Catatan pelanggan: ')) {
            $customerNotes[] = trim((string) \Illuminate\Support\Str::after($trimmed, 'Catatan pelanggan: '));
            continue;
        }

        $customerNotes[] = $trimmed;
    }

    $displayProductionType = $order->production_type ?: $legacySpec['production_type'];
    $displayProductModel = $order->product_model ?: $legacySpec['product_model'];
    $displaySleeveType = $order->sleeve_type ?: $legacySpec['sleeve_type'];
    $displayCustomerNote = trim(implode(PHP_EOL, array_filter($customerNotes)));

    if ($legacyDesignPosition === null && preg_match('/Posisi\s+Desain\s*:\s*(.+)$/mi', (string) ($order->design_notes ?? ''), $matches) === 1) {
        $legacyDesignPosition = trim((string) ($matches[1] ?? ''));
    }

    $legacyDesignInfo = trim(implode(PHP_EOL, array_filter([
        $legacyDesignPosition ? 'Posisi Desain: ' . $legacyDesignPosition : null,
        $legacyDesignNote,
    ])));

    $displayDesignPosition = $legacyDesignPosition;
    $displayDesignNote = (string) ($order->design_notes ?: $legacyDesignInfo ?: '');

    $designFrontPath = $order->design_front_file ?: $order->design_file;
    $designBackPath = $order->design_back_file;

    $isPreviewableImage = static function (?string $path): bool {
        if (! $path) {
            return false;
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    };

    $frontIsImage = $isPreviewableImage($designFrontPath);
    $backIsImage = $isPreviewableImage($designBackPath);
    $isAdminVerified = (string) ($order->admin_verification_status ?? 'pending') === 'verified';
    $isRevisionRequested = (string) ($order->admin_verification_status ?? 'pending') === 'revision_requested';
    $visiblePayments = $order->payments->filter(static function ($payment): bool {
        if (in_array($payment->status, ['verified', 'rejected'], true)) {
            return true;
        }

        if ($payment->method === 'settlement') {
            return true;
        }

        return (bool) $payment->midtrans_order_id;
    });
@endphp

<style>
    .order-detail-hero {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .spec-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .spec-item {
        border: 1px solid #d8e4ee;
        border-radius: 12px;
        padding: 0.9rem;
        background: #f9fbff;
    }

    .spec-label {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #5a7489;
    }

    .spec-value {
        margin: 0.3rem 0 0;
        font-size: 1rem;
        font-weight: 600;
        color: #173952;
        word-break: break-word;
    }

    .design-preview {
        border: 1px solid #d8e4ee;
        border-radius: 12px;
        overflow: hidden;
        background: #f9fbff;
        padding: 1rem;
        text-align: center;
    }

    .design-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .design-title {
        margin: 0 0 0.6rem;
        font-size: 0.95rem;
        font-family: 'Sora', sans-serif;
        color: #1a3a52;
    }

    .design-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        max-height: 300px;
    }

    .design-preview .muted {
        margin-top: 0.5rem;
        font-size: 0.85rem;
    }

    .history-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: nowrap;
        border-radius: 9px;
        border: 1px solid #c8a949;
        background: #c8a949;
        color: #0f2947;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.42rem 0.95rem;
        line-height: 1.15;
        transition: all 0.2s ease;
    }

    .history-action-btn:hover {
        background: #dfbf65;
        border-color: #dfbf65;
        color: #0f2947;
    }

    .history-action-btn-reupload {
        border-color: #b63b22;
        background: #b63b22;
        color: #fff;
    }

    .history-action-btn-reupload:hover {
        border-color: #9f2f1a;
        background: #9f2f1a;
        color: #fff;
    }

    .progress-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        flex-wrap: wrap;
        margin-bottom: 0.9rem;
    }

    .progress-head h3 {
        margin: 0;
    }

    .timeline-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .timeline-item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 0.72rem;
        position: relative;
        padding-bottom: 0.8rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: "";
        position: absolute;
        left: 16px;
        top: 22px;
        bottom: -4px;
        width: 2px;
        background: #d6e1eb;
    }

    .timeline-item:last-child::before {
        display: none;
    }

    .timeline-dot {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        border: 2px solid #c6d6e5;
        background: #f7fbff;
        color: #6281a0;
        font-size: 0.74rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .timeline-dot::after {
        content: "";
        position: absolute;
        inset: -8px;
        border-radius: 999px;
        border: 2px solid transparent;
        opacity: 0;
        pointer-events: none;
    }

    .timeline-item-done .timeline-dot {
        border-color: #8cc8ad;
        background: #eaf7ef;
        color: #186847;
    }

    .timeline-item-progress .timeline-dot {
        border-color: #e0c578;
        background: #fdf6e7;
        color: #9a6a00;
        box-shadow: 0 0 0 4px rgba(224, 197, 120, 0.22);
    }

    .timeline-item-progress .timeline-dot::after {
        border-color: rgba(224, 197, 120, 0.75);
        animation: timeline-pulse 1.6s ease-out infinite;
    }

    @keyframes timeline-pulse {
        0% {
            transform: scale(0.78);
            opacity: 0.95;
        }
        70% {
            transform: scale(1.18);
            opacity: 0;
        }
        100% {
            transform: scale(1.18);
            opacity: 0;
        }
    }

    .timeline-item-title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: #102a43;
    }

    .timeline-item-done .timeline-item-title {
        color: #1f7a48;
    }

    .timeline-item-progress .timeline-item-title {
        color: #b17f11;
    }

    .timeline-item-note {
        margin: 0.12rem 0 0;
        color: #728da8;
        font-size: 0.82rem;
    }

    .timeline-item-date {
        margin: 0.12rem 0 0;
        color: #7d97b1;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .settlement-alert {
        margin-top: 0.85rem;
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

    .settlement-alert h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
    }

    .settlement-alert p {
        margin: 0.28rem 0 0;
        font-size: 0.84rem;
        line-height: 1.4;
        max-width: 620px;
    }

    .settlement-alert strong {
        color: #ffe9c2;
    }

    .settlement-alert .btn {
        background: #c8a949;
        border-color: #c8a949;
        color: #0f2947;
        font-weight: 700;
    }

    .settlement-alert .btn:hover {
        background: #dfbf65;
        border-color: #dfbf65;
    }

    @media (max-width: 1200px) {
        .order-detail-hero {
            grid-template-columns: 1fr;
        }

        .spec-grid {
            grid-template-columns: 1fr;
        }

        .design-grid {
            grid-template-columns: 1fr;
        }

        .timeline-item {
            grid-template-columns: 30px minmax(0, 1fr);
            gap: 0.6rem;
        }

        .timeline-dot {
            width: 24px;
            height: 24px;
            font-size: 0.7rem;
        }

        .timeline-item::before {
            left: 13px;
            top: 18px;
        }
    }
</style>

<div class="card">
    @if ($statusForDisplay === 'cancelled')
        <div class="settlement-alert" style="background: linear-gradient(135deg, #b53025, #c73729); margin-top:0; margin-bottom:1rem;">
            <div>
                <h4>Pesanan Dibatalkan</h4>
                <p>{{ $order->admin_verification_note ?: 'Pesanan dibatalkan karena melewati batas waktu yang ditentukan.' }}</p>
            </div>
            <a href="https://wa.me/6281234567890" class="btn" style="background:#fff; color:#b53025; font-weight:700;">Hubungi CS</a>
        </div>
    @elseif ($statusForDisplay === 'held_waiting_settlement')
        <div class="settlement-alert" style="background: linear-gradient(135deg, #d97706, #f59e0b); margin-top:0; margin-bottom:1rem;">
            <div>
                <h4>Status Ditahan (Menunggu Pelunasan)</h4>
                <p>Pesanan telah melewati batas waktu pelunasan (2x24 Jam). Proses finishing <strong>ditahan sementara</strong>. Segera lakukan pelunasan agar pesanan dapat dilanjutkan.</p>
            </div>
            <a href="https://wa.me/6281234567890" class="btn" style="background:#fff; color:#d97706; font-weight:700;">Konfirmasi via WhatsApp</a>
        </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:start; gap:1rem; flex-wrap:wrap; margin-bottom:1.2rem;">
        <div>
            <h1 style="margin-bottom:0.3rem;">{{ $order->order_code }}</h1>
            <p class="muted" style="margin:0;">Nama Pemesan: {{ $order->customer_name }}</p>
        </div>
        <span class="status-pill {{ $statusClass($statusForDisplay) }}" style="font-size:0.9rem;">{{ $statusLabel($statusForDisplay) }}</span>
    </div>

    <div class="order-detail-hero">
        <div>
            <h3 style="margin-top:0; margin-bottom:1rem;">Spesifikasi Pesanan</h3>
            <div class="spec-grid">
                <div class="spec-item">
                    <p class="spec-label">Bahan</p>
                    <p class="spec-value">{{ $order->fabric }}</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Model</p>
                    <p class="spec-value">{{ $displayProductModel ?: '-' }}</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Jenis Produksi</p>
                    <p class="spec-value">{{ $displayProductionType ?: '-' }}</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Posisi Desain</p>
                    <p class="spec-value">{{ $displayDesignPosition ?: '-' }}</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Lengan</p>
                    <p class="spec-value">{{ $displaySleeveType ?: '-' }}</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Warna Dominan</p>
                    <p class="spec-value">{{ $order->dominant_color }}{{ $order->secondary_color ? ' / ' . $order->secondary_color : '' }}</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Total Pcs</p>
                    <p class="spec-value">{{ $order->total_pcs }} pcs</p>
                </div>
                <div class="spec-item">
                    <p class="spec-label">Estimasi Selesai</p>
                    <p class="spec-value">{{ $order->estimated_finish_date?->format('d M Y') }}</p>
                </div>
                @if ($isAdminVerified)
                    <div class="spec-item">
                        <p class="spec-label">Tipe Pembayaran Awal</p>
                        <p class="spec-value">{{ $order->payment_type === 'dp' ? 'DP 50%' : 'Lunas' }}</p>
                    </div>
                @endif
            </div>

            @if ($displayCustomerNote !== '')
                <div style="margin-top:1rem; border: 1px solid #d8e4ee; border-radius: 12px; padding: 0.9rem; background: #f0f8ff;">
                    <p style="margin:0; font-size:0.8rem; font-weight:700; color:#5a7489; text-transform:uppercase; letter-spacing:0.02em;">Catatan Pesanan</p>
                    <p style="margin:0.4rem 0 0; color:#173952; line-height:1.5; white-space:pre-wrap;">{{ $displayCustomerNote }}</p>
                </div>
            @endif
        </div>

        <div>
            <div class="design-grid">
                <div class="design-preview">
                    <p class="design-title">Desain Bagian Depan</p>
                    @if ($designFrontPath)
                        @if ($frontIsImage)
                            <img src="{{ asset('storage/' . $designFrontPath) }}" alt="Desain Depan">
                        @else
                            @php
                                $frontExt = strtolower((string) pathinfo($designFrontPath, PATHINFO_EXTENSION));
                            @endphp
                            <div style="padding:2rem 1rem;">
                                <div style="font-size:3rem; margin-bottom:0.5rem;">📄</div>
                                <p style="margin:0.5rem 0 0; color:#5a7489;">{{ strtoupper($frontExt) }} File</p>
                                <a class="btn btn-outline" href="{{ asset('storage/' . $designFrontPath) }}" download style="margin-top:0.6rem;">Download Desain Depan</a>
                            </div>
                        @endif
                    @else
                        <div style="padding:2rem 1rem;">
                            <div style="font-size:3rem; margin-bottom:0.5rem; color:#adc4d8;">🖼</div>
                            <p class="muted">Belum ada desain depan</p>
                        </div>
                    @endif
                </div>

                <div class="design-preview">
                    <p class="design-title">Desain Bagian Belakang</p>
                    @if ($designBackPath)
                        @if ($backIsImage)
                            <img src="{{ asset('storage/' . $designBackPath) }}" alt="Desain Belakang">
                        @else
                            @php
                                $backExt = strtolower((string) pathinfo($designBackPath, PATHINFO_EXTENSION));
                            @endphp
                            <div style="padding:2rem 1rem;">
                                <div style="font-size:3rem; margin-bottom:0.5rem;">📄</div>
                                <p style="margin:0.5rem 0 0; color:#5a7489;">{{ strtoupper($backExt) }} File</p>
                                <a class="btn btn-outline" href="{{ asset('storage/' . $designBackPath) }}" download style="margin-top:0.6rem;">Download Desain Belakang</a>
                            </div>
                        @endif
                    @else
                        <div style="padding:2rem 1rem;">
                            <div style="font-size:3rem; margin-bottom:0.5rem; color:#adc4d8;">🖼</div>
                            <p class="muted">Belum ada desain belakang</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($displayDesignNote !== '')
                <div style="margin-top:1rem; border: 1px solid #d8e4ee; border-radius: 12px; padding: 0.9rem; background: #f8fdf9;">
                    <p style="margin:0; font-size:0.8rem; font-weight:700; color:#4a6f58; text-transform:uppercase; letter-spacing:0.02em;">Catatan Posisi/Ukuran Desain</p>
                    <p style="margin:0.4rem 0 0; color:#173952; line-height:1.5; white-space:pre-wrap;">{{ $displayDesignNote }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="grid {{ $isAdminVerified ? 'grid-2' : 'grid-1' }}" style="margin-top:1rem;">
    <div class="card">
        <h3 style="margin-top:0;">Distribusi Ukuran</h3>
        <table>
            @foreach ($order->sizes as $size)
                <tr><th>{{ $size->size_name }}</th><td>{{ $size->qty }} pcs</td></tr>
            @endforeach
        </table>
    </div>

    @if ($isAdminVerified)
        <div class="card">
            <h3 style="margin-top:0;">Rincian Pembayaran</h3>
            <table>
                <tr><th>Subtotal</th><td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
                <tr><th>DP / Awal</th><td>Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td></tr>
                <tr><th>Sisa Bayar</th><td>Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</td></tr>
            </table>

            @if ($order->isSettlementRequired() && $order->order_status === 'finishing_waiting_settlement')
                <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin-top:0.9rem;">
                    @csrf
                    <button class="btn btn-danger" type="submit">Lakukan Pelunasan Sekarang</button>
                </form>
                <p class="muted" style="margin-bottom:0; margin-top:0.6rem;">Tahapan finishing tidak dapat dilakukan sebelum pelunasan terverifikasi.</p>
            @endif
        </div>
    @endif
</div>

<div class="card" style="margin-top:1rem;">
    <div class="progress-head">
        <h3>Status Produksi</h3>
        <a class="btn btn-outline" href="{{ route('customer.orders.index', ['focus' => 'status']) }}">Halaman Status Produksi</a>
    </div>

    @php
        $productionSteps = $order->productionSteps->sortBy('step_order')->values();
        $timelineSteps = [];

        $paymentVerifiedAt = optional(
            $order->payments
                ->where('status', 'verified')
                ->sortByDesc('verified_at')
                ->first()
        )->verified_at;

        $isVerificationDone = in_array($order->order_status, ['verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement', 'completed'], true);

        $timelineSteps[] = [
            'order' => 1,
            'title' => 'Verifikasi & Konfirmasi Pesanan',
            'status' => $isVerificationDone ? 'done' : 'pending',
            'updated_at' => $isVerificationDone ? $paymentVerifiedAt : null,
        ];

        $stepCounter = 2;
        foreach ($productionSteps as $step) {
            $displayName = $timelineStepName((string) $step->step_name);
            if ($displayName === null) {
                continue;
            }

            $timelineSteps[] = [
                'order' => $stepCounter,
                'title' => $displayName,
                'status' => $step->status,
                'updated_at' => $step->updated_at,
            ];

            $stepCounter++;
        }
    @endphp

    @if (count($timelineSteps) <= 1 && $timelineSteps[0]['status'] === 'pending')
        <p class="muted">Menunggu verifikasi pembayaran dan penerbitan SPK.</p>
    @else
        <ol class="timeline-list">
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

    @if ($order->order_status === 'finishing_waiting_settlement' && $order->isSettlementRequired())
        <div class="settlement-alert">
            <div>
                <h4>Lakukan Pelunasan Sekarang!</h4>
                <p>Pesanan Anda telah mencapai tahap Steam & Pressing. Tahapan finishing tidak dapat dilakukan sebelum pelunasan terverifikasi.<br>Sisa: <strong>Rp {{ number_format((float) $order->remaining_amount, 0, ',', '.') }}</strong></p>
            </div>
            <form method="POST" action="{{ route('customer.orders.settlement', $order) }}" style="margin:0;">
                @csrf
                <button class="btn" type="submit">Bayar Sekarang →</button>
            </form>
        </div>
    @endif
</div>

@if ($isRevisionRequested)
    <div class="card" style="margin-top:1rem;">
        <h3 style="margin-top:0;">Pengajuan Kembali dari Admin</h3>
        <p class="muted" style="margin-top:0;">{{ $order->admin_verification_note ?: 'Admin meminta revisi pada pesanan ini.' }}</p>
        <form method="POST" action="{{ route('customer.orders.revision.approve', $order) }}" style="margin:0;">
            @csrf
            <button class="btn btn-brand" type="submit">Setujui Revisi</button>
        </form>
    </div>
@endif

@if ($isAdminVerified)
    <div class="card" style="margin-top:1rem;">
        <h3>Riwayat Pembayaran</h3>
        @if ($visiblePayments->isEmpty())
            <p class="muted" style="margin:0;">Riwayat pembayaran akan tampil setelah customer memilih metode pembayaran dan mengirim pembayaran.</p>
        @else
            <table>
                <thead><tr><th>Tipe Bayar</th><th>Nominal</th><th>Status</th><th>Status Midtrans</th><th>Channel</th><th>ID Transaksi</th><th>Aksi</th><th>Catatan</th></tr></thead>
                <tbody>
            @foreach($visiblePayments as $payment)
            @php
                $displayNotes = $payment->notes;

                if (!empty($displayNotes)) {
                    $lines = preg_split('/\R/', $displayNotes) ?: [];
                    $cleaned = array_values(array_filter($lines, static function (string $line): bool {
                        $normalized = \Illuminate\Support\Str::lower(trim($line));

                        return ! \Illuminate\Support\Str::startsWith($normalized, 'catatan keuangan:')
                            && ! \Illuminate\Support\Str::startsWith($normalized, 'alasan penolakan kode:');
                    }));

                    $displayNotes = trim(implode(PHP_EOL, $cleaned));
                }
            @endphp
                <tr>
                <td>{{ $payment->method }}</td>
                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                <td><span class="status-pill {{ $statusClass($payment->status) }}">{{ str_replace('_', ' ', $payment->status) }}</span></td>
                <td>{{ $payment->midtrans_status ?: '-' }}</td>
                <td>{{ $payment->midtrans_payment_type ?: '-' }}</td>
                <td>
                    @if ($payment->midtrans_transaction_id)
                        <span class="muted">{{ $payment->midtrans_transaction_id }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if ($payment->status === 'verified')
                        <a href="{{ route('customer.invoices.show', [$order, $payment]) }}" target="_blank">Invoice</a>
                    @elseif (in_array($payment->midtrans_status, ['settlement', 'capture'], true))
                        <span class="muted">Menunggu sinkronisasi</span>
                    @elseif ($payment->status === 'rejected')
                        <a class="history-action-btn history-action-btn-reupload" href="{{ route('customer.orders.payments.edit', [$order, $payment]) }}">Bayar Ulang</a>
                    @else
                        <a href="{{ route('customer.orders.payments.edit', [$order, $payment]) }}">Lanjut Bayar</a>
                    @endif
                </td>
                <td>{{ $displayNotes !== '' ? $displayNotes : '-' }}</td>
                </tr>
            @endforeach
                </tbody>
            </table>
        @endif
    </div>
@else
    <div class="card" style="margin-top:1rem;">
        <h3>Riwayat Pembayaran</h3>
        <p class="muted" style="margin:0;">Pembayaran akan tersedia setelah pesanan selesai diverifikasi admin.</p>
    </div>
@endif
@endsection
