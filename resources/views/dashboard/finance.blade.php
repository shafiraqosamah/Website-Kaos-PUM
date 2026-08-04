@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
@php
    $needsAction = ($pendingPaymentsCount ?? 0) > 0;

    $paymentTypeLabel = static function (string $method): string {
        return match ($method) {
            'dp' => 'DP 50%',
            'settlement' => 'Pelunasan',
            'full' => 'Lunas Awal',
            default => strtoupper($method),
        };
    };

    $paymentTypeClass = static function (string $method): string {
        return match ($method) {
            'dp' => 'pay-type-dp',
            'settlement' => 'pay-type-settlement',
            'full' => 'pay-type-full',
            default => 'pay-type-default',
        };
    };
@endphp

<style>
    .finance-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .finance-header h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .finance-header p {
        margin: 0.45rem 0 1.2rem;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .finance-alert {
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

    .finance-alert a {
        color: #b63b22;
        font-weight: 700;
        text-decoration: underline;
    }

    .finance-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.95rem;
    }

    .finance-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 6px 16px rgba(13, 39, 73, 0.05);
    }

    .finance-card.verify {
        border-top-color: #cf3c2c;
    }

    .finance-card.income {
        border-top-color: #1f7a48;
    }

    .finance-card.outstanding {
        border-top-color: #c5a53f;
    }

    .finance-card.total {
        border-top-color: #2c7ebe;
    }

    .finance-card .label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .finance-card .value {
        margin-top: 0.55rem;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .finance-card .note {
        margin-top: 0.42rem;
        color: #7f96ae;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .pay-type {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.22rem 0.5rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        border: 1px solid transparent;
        text-align: center;
        line-height: 1.2;
        min-width: 80px;
    }

    .pay-type-dp {
        background: #e8f3ff;
        color: #0b4f8a;
        border-color: #bfdbfe;
    }

    .pay-type-settlement {
        background: #fff1e8;
        color: #9a3412;
        border-color: #fdba74;
    }

    .pay-type-full {
        background: #eafaf0;
        color: #166534;
        border-color: #86efac;
    }

    .pay-type-default {
        background: #f1f5f9;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-detail {
        background: #0d2749;
        color: #ffffff;
        border: 1px solid #0d2749;
        border-radius: 6px;
        padding: 0.35rem 0.7rem;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-detail:hover {
        background: #16395f;
        border-color: #16395f;
        color: #ffffff;
    }

    @media (max-width: 1100px) {
        .finance-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .finance-page {
            padding: 1rem;
        }

        .finance-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="finance-page">
    <div class="finance-header">
        <h1>Dashboard Keuangan</h1>
        <p>Pantau pembayaran dan verifikasi masuk hari ini</p>
    </div>

    @if (($pendingPaymentsCount ?? 0) > 0)
        @php
            $pendingOrderCodes = isset($pendingInitialPayments) ? $pendingInitialPayments->map(fn($p) => $p->order->order_code ?? '')->filter()->unique()->implode(', ') : '';
        @endphp
        <div class="finance-alert" style="border-color: #cbd5e1; background: #f8fafc; color: #475569;">
            <span>🔔</span>
            <span>Ada <strong>{{ $pendingPaymentsCount }} pesanan</strong> dengan nomor order <strong>{{ $pendingOrderCodes }}</strong> belum melakukan pembayaran.</span>
            <a href="{{ route('finance.index') }}" style="color: #475569; font-weight: 700; text-decoration: underline;">Pantau Sekarang →</a>
        </div>
    @endif

    @if ($waitingSettlementCount > 0)
        @php
            $waitingOrderCodes = isset($waitingSettlementOrders) ? $waitingSettlementOrders->map(fn($o) => $o->order_code)->filter()->unique()->implode(', ') : '';
        @endphp
        <div class="finance-alert" style="border-color: #fce7cf; background: #fffaf4; color: #b45309; margin-top: 0.5rem;">
            <span>💰</span>
            <span>Ada <strong>{{ $waitingSettlementCount }} pesanan</strong> dengan nomor order <strong>{{ $waitingOrderCodes }}</strong> membutuhkan pelunasan dari pelanggan.</span>
            <a href="{{ route('finance.index') }}" style="color: #b45309; font-weight: 700; text-decoration: underline;">Pantau Sekarang →</a>
        </div>
    @endif

    <div class="finance-grid">
        <article class="finance-card verify">
            <div class="label">Pending Pembayaran</div>
            <div class="value">{{ number_format((int) ($pendingPaymentsCount ?? 0), 0, ',', '.') }}</div>
            <div class="note">Pembayaran awal</div>
        </article>

        <article class="finance-card income">
            <div class="label">Uang Masuk (Bulan Ini)</div>
            <div class="value">Rp {{ number_format((float) $monthlyVerifiedAmount, 0, ',', '.') }}</div>
            <div class="note">{{ number_format((int) $verifiedToday, 0, ',', '.') }} diverifikasi hari ini</div>
        </article>

        <article class="finance-card outstanding">
            <div class="label">Pelunasan</div>
            <div class="value">Rp {{ number_format((float) $outstandingSettlement, 0, ',', '.') }}</div>
            <div class="note">Belum lunas</div>
        </article>

        <article class="finance-card total">
            <div class="label">Total Tagihan</div>
            <div class="value">Rp {{ number_format((float) $monthlyTotalTagihan, 0, ',', '.') }}</div>
            <div class="note">Bulan ini</div>
        </article>
    </div>

    <div class="finance-recent-section" style="margin-top: 1.8rem; background: #ffffff; border: 1px solid #d9e2ea; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(13, 39, 73, 0.03);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h2 style="margin: 0; font-size: 1.15rem; color: #0d2749; font-family: 'Playfair Display', serif; font-weight: 700;">
                📊 Transaksi Terbaru (Midtrans)
            </h2>
            <a href="{{ route('finance.index') }}" style="color: #d95f18; font-weight: 700; text-decoration: none; font-size: 0.82rem; display: flex; align-items: center; gap: 4px;">
                Lihat Semua Transaksi →
            </a>
        </div>

        @if($recentPayments->isEmpty())
            <div style="text-align: center; padding: 2.5rem 1rem; color: #7f96ae; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <span style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">💳</span>
                Belum ada transaksi pembayaran masuk.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e4ecf2;">
                            <th style="padding: 0.75rem 0.5rem; text-align: left; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">Tanggal</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: left; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">No.Order</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: left; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">Pelanggan</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: left; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">Metode</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">Nominal</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">Status</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center; font-size: 0.72rem; color: #7893ae; text-transform: uppercase; font-weight: 700;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedCodes = $recentPayments->groupBy(fn($p) => $p->order->order_code ?? '');
                            $colorMap = [];
                            $colors = ['#d97706', '#2563eb', '#10b981', '#8b5cf6', '#ec4899'];
                            $colorIdx = 0;
                            foreach($groupedCodes as $code => $items) {
                                if ($items->count() > 1 && $code) {
                                    $colorMap[$code] = $colors[$colorIdx % count($colors)];
                                    $colorIdx++;
                                }
                            }
                        @endphp
                        @foreach($recentPayments as $index => $payment)
                            @php
                                $orderCode = $payment->order->order_code ?? '';
                                $groupColor = $colorMap[$orderCode] ?? null;
                                $hasSameNext = isset($recentPayments[$index + 1]) && ($recentPayments[$index + 1]->order->order_code ?? '') === $orderCode;
                                $hasSamePrev = isset($recentPayments[$index - 1]) && ($recentPayments[$index - 1]->order->order_code ?? '') === $orderCode;

                                if ($groupColor) {
                                    if ($hasSameNext) {
                                        $borderStyle = 'border-bottom: 1px dashed #cbd5e1;';
                                    } else {
                                        $borderStyle = 'border-bottom: 2.5px solid #94a3b8;';
                                    }
                                } else {
                                    $borderStyle = 'border-bottom: 1px solid #f1f5f9;';
                                }
                            @endphp
                            <tr style="{{ $borderStyle }} transition: background 0.2s;">
                                <td style="padding: 0.85rem 0.5rem; color: #4f6173; font-size: 0.8rem; {{ $groupColor ? 'border-left: 5px solid ' . $groupColor . '; padding-left: 0.85rem !important;' : '' }}">
                                    {{ $payment->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: #0d2749; font-size: 0.8rem;">
                                    {{ $payment->order->order_code }}
                                </td>
                                <td style="padding: 0.85rem 0.5rem; color: #1d3548; font-size: 0.8rem;">
                                    {{ $payment->order->user->name }}
                                </td>
                                <td style="padding: 0.85rem 0.5rem; font-size: 0.8rem;">
                                    <span class="pay-type {{ $paymentTypeClass($payment->method) }}">
                                        {{ $paymentTypeLabel($payment->method) }}
                                    </span>
                                </td>
                                <td style="padding: 0.85rem 0.5rem; text-align: right; font-weight: 700; color: #0d2749; font-size: 0.8rem;">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td style="padding: 0.85rem 0.5rem; text-align: center;">
                                    @if($payment->status === 'verified')
                                        <span class="status-pill status-success">Berhasil</span>
                                    @elseif($payment->status === 'rejected')
                                        <span class="status-pill status-danger">Gagal</span>
                                    @else
                                        <span class="status-pill status-warning">Pending</span>
                                    @endif
                                </td>
                                <td style="padding: 0.85rem 0.5rem; text-align: center;">
                                    @if($payment->status === 'pending')
                                        <a href="{{ route('finance.index') }}" class="btn-detail" style="display: inline-block; text-decoration: none; padding: 0.35rem 0.7rem; font-size: 0.72rem;">
                                            Detail
                                        </a>
                                    @elseif($payment->status === 'verified' && $payment->invoice_number)
                                        <a href="{{ route('finance.invoices.show', $payment) }}" target="_blank" class="btn-detail" style="display: inline-block; text-decoration: none; padding: 0.35rem 0.7rem; font-size: 0.72rem; background: #1f7a48; border-color: #1f7a48;">
                                            Invoice
                                        </a>
                                    @else
                                        <span style="color: #cbd5e1; font-size: 0.8rem;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recentPayments->hasPages())
                <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
                    {{ $recentPayments->links() }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
