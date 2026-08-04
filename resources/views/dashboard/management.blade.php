@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
@php
    $statusLabel = static function (string $status): string {
        return match ($status) {
            'verified_payment' => 'Pembayaran Terverifikasi',
            'in_production' => 'Sedang Produksi',
            'finishing_waiting_settlement' => 'Menunggu Pelunasan',
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
@endphp
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

    .dashboard-section {
        margin-top: 2.5rem;
        border: 1px solid #e1e8ef;
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .dashboard-section-header {
        background: #f8fafc;
        padding: 1.15rem 1.25rem;
        border-bottom: 1px solid #e1e8ef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dashboard-section-title {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: 1.15rem;
        color: #0f2947;
        font-weight: 700;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table th, .dashboard-table td {
        padding: 0.85rem 1.25rem;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.84rem;
        color: #334155;
    }

    .dashboard-table th {
        background: #ffffff;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
    }

    .dashboard-table th.col-center, .dashboard-table td.col-center {
        text-align: center;
    }

    .dashboard-table tr:last-child td {
        border-bottom: none;
    }

    .dashboard-table tbody tr:hover {
        background: #fafbfc;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        background: #ecf3f9;
        color: #1a568b;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.76rem;
        font-weight: 700;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: #1a568b;
        color: #ffffff;
    }

    .action-buttons {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 0.55rem;
    }

    .action-buttons .btn {
        width: 118px;
        text-align: center;
        white-space: nowrap;
    }

    .action-buttons .action-detail {
        width: auto;
        min-width: 0;
        align-self: center;
        background: #ffffff;
        border: 1px solid #d9e2ec;
        color: #49637a;
    }

    .action-buttons .action-detail:hover {
        background: #f3f7fb;
    }

    .action-buttons .action-stack {
        display: grid;
        gap: 0.4rem;
        justify-items: start;
    }

    .btn-xs {
        font-size: 0.76rem;
        padding: 0.42rem 0.7rem;
        border-radius: 9px;
    }

    .btn-revision-link {
        background: #b63b22;
        border: 1px solid #b63b22;
        color: #ffffff;
    }

    .btn-revision-link:hover {
        background: #9f2f1a;
        border-color: #9f2f1a;
        color: #ffffff;
    }

    .prod-progress {
        min-width: 150px;
    }

    .prod-progress-track {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #e4edf4;
        overflow: hidden;
        margin-top: 0.35rem;
        margin-bottom: 0.2rem;
    }

    .prod-progress-fill {
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0f7b8f, #1ca4bc);
    }

    .muted {
        color: #64748b;
        font-size: 0.75rem;
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
            <span>Ada <strong>{{ $pendingOrderVerification }}</strong> pesanan yang menunggu verifikasi admin (Max 2x24 Jam).</span>
            <a href="{{ route('reports.orders') }}">Verifikasi Sekarang →</a>
        </div>
    @endif

    @if (($pendingPaymentsCount ?? 0) > 0)
        @php
            $pendingOrderCodes = isset($pendingInitialPayments) ? $pendingInitialPayments->map(fn($p) => $p->order->order_code ?? '')->filter()->unique()->implode(', ') : '';
        @endphp
        <div class="management-alert" style="border-color: #cbd5e1; background: #f8fafc; color: #475569; margin-top: 0.5rem;">
            <span>🔔</span>
            <span>Pesanan dengan nomor order <strong>{{ $pendingOrderCodes }}</strong> belum melakukan pembayaran.</span>
            <a href="{{ route('finance.index') }}" style="color: #475569; font-weight: 700; text-decoration: underline;">Pantau Sekarang →</a>
        </div>
    @endif

    @if (($productionWaitingVerification ?? 0) > 0)
        <div class="management-alert" style="border-color: #fbc4a8; background: #fffbf7; color: #c87f2d;">
            <span>✓</span>
            <span>Ada <strong>{{ $productionWaitingVerification }}</strong> produksi yang menunggu verifikasi hasil.</span>
            <a href="{{ route('production.index') }}" style="color: #c87f2d;">Verifikasi Sekarang →</a>
        </div>
    @endif

    @if (($waitingSettlementCount ?? 0) > 0)
        @php
            $waitingOrderCodes = isset($waitingSettlementOrders) ? $waitingSettlementOrders->map(fn($o) => $o->order_code)->filter()->unique()->implode(', ') : '';
        @endphp
        <div class="management-alert" style="border-color: #fce7cf; background: #fffaf4; color: #b45309; margin-top: 0.5rem;">
            <span>💰</span>
            <span>Ada <strong>{{ $waitingSettlementCount }}</strong> pesanan dengan nomor order <strong>{{ $waitingOrderCodes }}</strong> membutuhkan pelunasan dari pelanggan.</span>
            <a href="{{ route('finance.index') }}" style="color: #b45309; font-weight: 700; text-decoration: underline;">Pantau Sekarang →</a>
        </div>
    @endif

    <div class="metrics-grid">
        <div class="metric-card orders">
            <div class="metric-label">Total Order</div>
            <div class="metric-value">{{ $summary['total_orders'] }}</div>
            <div class="metric-indicator">Seluruh pesanan</div>
        </div>
        <div class="metric-card pending">
            <div class="metric-label">Menunggu Verifikasi (Max 2x24 Jam)</div>
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

    <div class="dashboard-section">
        <div class="dashboard-section-header">
            <h2 class="dashboard-section-title">Pesanan Menunggu Verifikasi (Max 2x24 Jam)</h2>
            <a href="{{ route('reports.orders') }}" class="action-btn">Lihat Semua</a>
        </div>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingVerificationOrders ?? [] as $order)
                <tr>
                    <td><strong>#{{ $order->order_code }}</strong></td>
                    <td>{{ $order->customer_name ?: ($order->user->name ?? 'Guest') }}</td>
                    <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                    <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    <td>
                        <div class="action-buttons">
                            <a class="btn btn-xs action-detail" href="{{ route('reports.orders.show', ['order' => $order]) }}">Detail</a>
                            <div class="action-stack">
                                <a class="btn btn-brand btn-xs" href="{{ route('reports.orders.show', ['order' => $order]) }}#verify-section">Verifikasi</a>
                                <a class="btn btn-xs btn-revision-link" href="{{ route('reports.orders.show', ['order' => $order]) }}#revision-section">Ajukan Kembali</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b; padding: 2rem;">Tidak ada pesanan yang menunggu verifikasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($pendingVerificationOrders->hasPages())
            <div style="margin-top: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: flex-end;">
                {{ $pendingVerificationOrders->links() }}
            </div>
        @endif
    </div>



    <div class="dashboard-section">
        <div class="dashboard-section-header">
            <h2 class="dashboard-section-title">Status SPK & Produksi</h2>
            <a href="{{ route('production.index') }}" class="action-btn">Lihat Semua</a>
        </div>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Progress</th>
                    <th class="col-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeProductionOrders ?? [] as $order)
                @php
                    $steps = $order->productionSteps;
                    $totalSteps = max($steps->count(), 1);
                    $doneSteps = $steps->where('status', 'done')->count();
                    $progress = (int) round(($doneSteps / $totalSteps) * 100);
                @endphp
                <tr>
                    <td><strong>#{{ $order->order_code }}</strong></td>
                    <td>
                        {{ $order->customer_name ?: ($order->user->name ?? 'Guest') }}
                        @if($order->estimated_finish_date)
                            <div style="font-size: 0.75rem; color: #d95f18; margin-top: 0.2rem; font-weight: 600;">
                                Estimasi Selesai: {{ \Carbon\Carbon::parse($order->estimated_finish_date)->format('d M Y') }}
                            </div>
                        @endif
                    </td>
                    <td class="prod-progress">
                        <strong>{{ $doneSteps }}/{{ $steps->count() }} Tahap</strong>
                        <div class="prod-progress-track">
                            <div class="prod-progress-fill" style="width: {{ $progress }}%;"></div>
                        </div>
                        <span class="muted">{{ $progress }}% selesai</span>
                    </td>
                    <td class="col-center">
                        <span class="status-pill {{ $statusClass($order->order_status) }}" style="display:inline-block; text-align:center;">
                            {{ $statusLabel($order->order_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 2rem;">Tidak ada pesanan dalam proses produksi saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($activeProductionOrders->hasPages())
            <div style="margin-top: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: flex-end;">
                {{ $activeProductionOrders->links() }}
            </div>
        @endif
    </div>

    <div class="dashboard-section">
        <div class="dashboard-section-header">
            <h2 class="dashboard-section-title">Riwayat Produksi Selesai</h2>
        </div>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Total Pesanan</th>
                    <th>Riwayat Pembayaran</th>
                    <th class="col-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($completedOrders ?? [] as $order)
                <tr>
                    <td><strong>#{{ $order->order_code }}</strong></td>
                    <td>
                        {{ $order->customer_name ?: ($order->user->name ?? 'Guest') }}
                        @if($order->estimated_finish_date)
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                                Estimasi Selesai: {{ \Carbon\Carbon::parse($order->estimated_finish_date)->format('d M Y') }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                    <td><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></td>
                    <td>
                        @php
                            $dp = $order->payments->where('method', 'dp')->where('status', 'verified')->first();
                            $lunas = $order->payments->where('method', 'settlement')->where('status', 'verified')->first();
                            $lunasDirect = $order->payments->where('method', 'lunas')->where('status', 'verified')->first();
                        @endphp
                        @if($lunasDirect)
                            <div style="font-size: 0.75rem; color: #0f8f60;">Lunas (Awal): Rp {{ number_format($lunasDirect->amount, 0, ',', '.') }}</div>
                        @else
                            @if($dp)
                                <div style="font-size: 0.75rem; color: #0c7fb6;">DP: Rp {{ number_format($dp->amount, 0, ',', '.') }} ✓</div>
                            @endif
                            @if($lunas)
                                <div style="font-size: 0.75rem; color: #0f8f60;">Pelunasan: Rp {{ number_format($lunas->amount, 0, ',', '.') }} ✓</div>
                            @endif
                        @endif
                    </td>
                    <td class="col-center"><span class="status-pill status-success" style="display:inline-block; text-align:center;">Selesai</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">Belum ada pesanan selesai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($completedOrders->hasPages())
            <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                {{ $completedOrders->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
