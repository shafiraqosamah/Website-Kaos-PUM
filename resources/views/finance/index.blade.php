@extends('layouts.app')

@section('header_title', 'Data Pembayaran')

@section('content')
@php
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

    $pendingDp = $pendingPayments->where('method', 'dp')->values();
    $pendingSettlement = $pendingPayments->where('method', 'settlement')->values();
    $pendingFull = $pendingPayments->where('method', 'full')->values();

    $expectedAmount = static function ($payment): float {
        return match ($payment->method) {
            'dp' => (float) ($payment->order->dp_amount ?? 0),
            'settlement' => (float) ($payment->order->remaining_amount ?? 0),
            'full' => (float) ($payment->order->subtotal ?? 0),
            default => (float) $payment->amount,
        };
    };

    $methodPriority = ['dp' => 1, 'settlement' => 2, 'full' => 3];
    $verifiedPaymentsSorted = $verifiedPayments
        ->sortBy(fn ($payment) => sprintf(
            '%s-%02d-%s',
            (string) ($payment->order->order_code ?? ''),
            $methodPriority[$payment->method] ?? 9,
            $payment->verified_at?->format('YmdHis') ?? '000000000000'
        ))
        ->values();

    $rejectReasons = collect($rejectReasons ?? []);
@endphp

<style>
    .finance-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .finance-header {
        margin-bottom: 1.2rem;
    }

    .finance-header h1 {
        margin: 0 0 0.35rem;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .finance-header p {
        margin: 0;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-item {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        border-top: 4px solid #c6d3df;
        padding: 1rem 1.05rem;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.03);
        transition: all 0.2s;
    }

    .stat-item:hover {
        border-color: #c8d6e8;
        box-shadow: 0 3px 8px rgba(15, 43, 61, 0.06);
    }

    .stat-item.dp {
        border-top-color: #2c7ebe;
    }

    .stat-item.settlement {
        border-top-color: #d97706;
    }

    .stat-item.full {
        border-top-color: #0f8f60;
    }

    .stat-item .label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.01em;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stat-item .value {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-weight: 700;
    }

    .pay-type {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 0.65rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        border: 1px solid transparent;
        text-align: center;
        line-height: 1.2;
        min-width: 85px;
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

    .section-card {
        background: #ffffff;
        border: 1px solid #dfe6ee;
        border-radius: 14px;
        padding: clamp(1rem, 2vw, 1.4rem);
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(15, 43, 61, 0.04);
    }

    .section-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(0.96rem, 1.4vw, 1.12rem);
        color: #0d2749;
        margin: 0 0 0.8rem;
        font-weight: 700;
    }

    .section-card p {
        color: #7893ae;
        margin: 0 0 1rem;
        font-size: 0.8rem;
    }

    .finance-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
    }

    .finance-table thead {
        background: #f4f8fb;
    }

    .finance-table th {
        padding: 0.75rem 0.9rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.72rem;
        color: #7893ae;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 1px solid #dfe6ee;
    }

    .finance-table td {
        padding: 0.8rem 0.9rem;
        border-bottom: 1px solid #eef2f7;
        color: #1d3548;
        font-size: 0.78rem;
    }

    .finance-table tbody tr:hover {
        background: #fafbfd;
    }

    .finance-table tbody tr:last-child td {
        border-bottom: none;
    }

    .action-form {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .action-form input {
        padding: 0.45rem 0.7rem;
        border: 1px solid #d5dce6;
        border-radius: 6px;
        font-size: 0.85rem;
        min-width: 140px;
    }

    .btn-small {
        padding: 0.45rem 0.9rem;
        border: none;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-verify {
        background: #19ad2f;
        color: #ffffff;
    }

    .btn-verify:hover {
        background: #19ad2f;
    }

    .btn-reject {
        background: #c22b2b;
        color: #ffffff;
    }

    .btn-reject:hover {
        background: #a01f1f;
    }

    .btn-link {
        background: transparent;
        color: #0f7b8f;
        border: none;
        text-decoration: underline;
        cursor: pointer;
        padding: 0;
    }

    .btn-link:hover {
        color: #0a5c6f;
    }

    .btn-detail {
        background: #0f2947;
        color: #ffffff;
        border: 1px solid #0f2947;
        border-radius: 8px;
        padding: 0.42rem 0.78rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-detail:hover {
        background: #16395f;
        border-color: #16395f;
    }

    .action-links {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .action-links .btn-link {
        white-space: nowrap;
    }

    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #f5fbff 0%, #f9f6f1 100%);
        border: 1px solid #dfe8ef;
        border-radius: 12px;
        color: #7893ae;
    }

    .empty-state h4 {
        color: #0d2749;
        margin: 0 0 0.5rem;
        font-family: 'Playfair Display', serif;
    }

    .pending-empty {
        padding: 1rem 0.9rem;
    }

    .pending-empty h4 {
        margin-bottom: 0.25rem;
    }

    .pending-empty p {
        margin-bottom: 0;
    }

    .detail-modal {
        position: fixed;
        inset: 0;
        background: rgba(6, 23, 42, 0.62);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1200;
        padding: 1rem;
    }

    .detail-modal.open {
        display: flex;
    }

    .detail-modal-card {
        width: min(760px, 100%);
        background: #ffffff;
        border: 1px solid #d8e3ed;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(8, 23, 39, 0.25);
        padding: 1.1rem 1.15rem;
    }

    .detail-modal-head h4 {
        margin: 0;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: 1.35rem;
    }

    .detail-modal-head p {
        margin: 0.25rem 0 0;
        color: #8aa0b8;
        font-weight: 700;
    }

    .detail-grid {
        margin-top: 0.95rem;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem 1.25rem;
    }

    .detail-grid p {
        margin: 0;
        color: #1d3548;
        font-size: 0.95rem;
    }

    .detail-grid strong {
        color: #1f2d3d;
    }

    .transaction-preview {
        margin-top: 0.95rem;
        border: 1px solid #dbe6f0;
        border-radius: 12px;
        background: #f5f9fd;
        padding: 0.85rem 0.95rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .transaction-preview strong {
        color: #0d2749;
    }

    .modal-form {
        margin-top: 1rem;
        display: grid;
        gap: 0.6rem;
    }

    .modal-form input {
        width: 100%;
        border: 1px solid #ccd9e6;
        border-radius: 8px;
        padding: 0.58rem 0.7rem;
        font: inherit;
    }

    .modal-form select {
        width: 100%;
        border: 1px solid #ccd9e6;
        border-radius: 8px;
        padding: 0.58rem 0.7rem;
        font: inherit;
        background: #fff;
    }

    .modal-form label {
        font-size: 0.82rem;
        color: #0d2749;
        font-weight: 700;
    }

    .modal-form.hidden {
        display: none;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .btn-approve {
        background: #1c8a50;
        color: #ffffff;
        border: 1px solid #1c8a50;
        border-radius: 9px;
        padding: 0.55rem 1rem;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-close {
        background: #ffffff;
        color: #1f3448;
        border: 1px solid #c4d2e0;
        border-radius: 9px;
        padding: 0.55rem 1rem;
        font-weight: 700;
        cursor: pointer;
    }

    .modal-close-wrap {
        margin-top: 1rem;
        display: none;
        justify-content: flex-end;
    }

    .modal-close-wrap.open {
        display: flex;
    }

    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .finance-table {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 768px) {
        .finance-page {
            padding: 1rem;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .action-form {
            flex-direction: column;
        }

        .action-form input {
            width: 100%;
        }

        .btn-small {
            flex: 1;
            min-width: auto;
        }
    }
</style>

<section class="finance-page">
    <div class="finance-header">
        <h1>Data Pembayaran</h1>
        <p>Pantau status transaksi pembayaran pelanggan yang diproses otomatis melalui Midtrans.</p>
    </div>

    <div class="stats-row">
        <div class="stat-item dp">
            <div class="label">Pending DP</div>
            <div class="value">{{ $pendingDp->count() }}</div>
        </div>
        <div class="stat-item settlement">
            <div class="label">Pending Pelunasan</div>
            <div class="value">{{ $pendingSettlement->count() }}</div>
        </div>
        <div class="stat-item full">
            <div class="label">Pending Lunas Awal</div>
            <div class="value">{{ $pendingFull->count() }}</div>
        </div>
    </div>

@if($pendingPayments->isNotEmpty())
    <div class="section-card">
        <h3>📋 Menunggu Verifikasi</h3>
        <p>Verifikasi pembayaran untuk membuka tahapan produksi order</p>
        <div style="overflow-x: auto;">
            <table class="finance-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga @</th>
                        <th>Total Order</th>
                        <th>Metode</th>
                        <th>Status Midtrans</th>
                        <th>Nominal Bayar</th>
                        <th>Harus Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingPayments as $payment)
                        @php
                            $expected = $expectedAmount($payment);
                        @endphp
                        <tr>
                            <td><strong>{{ $payment->order->order_code }}</strong></td>
                            <td>{{ $payment->order->user->name }}</td>
                            <td>{{ $payment->order->product_name ?: '-' }}</td>
                            <td>{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $payment->order->unit_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}</td>
                            <td><span class="pay-type {{ $paymentTypeClass($payment->method) }}">{{ $paymentTypeLabel($payment->method) }}</span></td>
                            <td>{{ $payment->midtrans_status ?: '-' }}</td>
                            <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($expected, 0, ',', '.') }}</strong></td>
                            <td>
                                @php($isMidtransOnly = (bool) $payment->midtrans_order_id)
                                <button
                                    type="button"
                                    class="btn-detail js-open-detail"
                                    data-verify-url="{{ $isMidtransOnly ? '' : route('finance.verify', $payment) }}"
                                    data-invoice-url="{{ $payment->invoice_number ? route('finance.invoices.show', $payment) : '' }}"
                                    data-order-code="{{ $payment->order->order_code }}"
                                    data-customer="{{ $payment->order->user->name }}"
                                    data-product="{{ $payment->order->product_name ?: '-' }}"
                                    data-qty="{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}"
                                    data-method="{{ $paymentTypeLabel($payment->method) }}"
                                    data-total="Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}"
                                    data-dp="Rp {{ number_format((float) ($payment->order->dp_amount ?? 0), 0, ',', '.') }}"
                                    data-sisa="Rp {{ number_format((float) ($payment->order->remaining_amount ?? 0), 0, ',', '.') }}"
                                    data-midtrans-status="{{ $payment->midtrans_status ?: '-' }}"
                                    data-midtrans-order="{{ $payment->midtrans_order_id ?: '-' }}"
                                    data-midtrans-transaction="{{ $payment->midtrans_transaction_id ?: '-' }}"
                                    data-midtrans-channel="{{ $payment->midtrans_payment_type ?: '-' }}"
                                    data-readonly="{{ $isMidtransOnly ? '1' : '0' }}"
                                >
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="section-card">
    <h3>Data Pembayaran Pelanggan</h3>
    <p>Pembayaran yang telah berhasil diverifikasi dan invoice telah diterbitkan</p>
    
    @if($verifiedPayments->isEmpty())
        <div class="empty-state">
            <h4>Belum Ada</h4>
            <p>Invoice akan muncul setelah pembayaran diverifikasi.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table class="finance-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Nominal</th>
                        <th>Terverifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($verifiedPaymentsSorted as $payment)
                        <tr>
                            <td><strong>{{ $payment->invoice_number ?? '-' }}</strong></td>
                            <td>{{ $payment->order->order_code }}</td>
                            <td>{{ $payment->order->user->name }}</td>
                            <td><span class="pay-type {{ $paymentTypeClass($payment->method) }}">{{ $paymentTypeLabel($payment->method) }}</span></td>
                            <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                            <td>{{ $payment->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                <div class="action-links">
                                    <a class="btn-link" href="{{ route('finance.invoices.show', $payment) }}" target="_blank">Download Invoice</a>
                                    <button
                                        type="button"
                                        class="btn-detail js-open-detail"
                                        data-verify-url=""
                                        data-invoice-url="{{ route('finance.invoices.show', $payment) }}"
                                        data-order-code="{{ $payment->order->order_code }}"
                                        data-customer="{{ $payment->order->user->name }}"
                                        data-product="{{ $payment->order->product_name ?: '-' }}"
                                        data-qty="{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}"
                                        data-method="{{ $paymentTypeLabel($payment->method) }}"
                                        data-total="Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}"
                                        data-dp="Rp {{ number_format((float) ($payment->order->dp_amount ?? 0), 0, ',', '.') }}"
                                        data-sisa="Rp {{ number_format((float) ($payment->order->remaining_amount ?? 0), 0, ',', '.') }}"
                                        data-midtrans-status="{{ $payment->midtrans_status ?: '-' }}"
                                        data-midtrans-order="{{ $payment->midtrans_order_id ?: '-' }}"
                                        data-midtrans-transaction="{{ $payment->midtrans_transaction_id ?: '-' }}"
                                        data-midtrans-channel="{{ $payment->midtrans_payment_type ?: '-' }}"
                                        data-readonly="1"
                                    >
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($rejectedPayments->isNotEmpty())
    <div class="section-card">
        <h3>✕ Gagal Pembayaran</h3>
        <p>Bukti bayar yang ditolak oleh tim keuangan ditampilkan di sini untuk ditinjau ulang</p>

        <div style="overflow-x: auto;">
            <table class="finance-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Nominal</th>
                        <th>Ditolak</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rejectedPayments as $payment)
                        <tr>
                            <td><strong>{{ $payment->order->order_code }}</strong></td>
                            <td>{{ $payment->order->user->name }}</td>
                            <td><span class="pay-type {{ $paymentTypeClass($payment->method) }}">{{ $paymentTypeLabel($payment->method) }}</span></td>
                            <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                            <td>{{ $payment->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $payment->notes ?: '-' }}</td>
                            <td>
                                <div class="action-links">
                                    <button
                                        type="button"
                                        class="btn-detail js-open-detail"
                                        data-verify-url=""
                                        data-invoice-url=""
                                        data-order-code="{{ $payment->order->order_code }}"
                                        data-customer="{{ $payment->order->user->name }}"
                                        data-product="{{ $payment->order->product_name ?: '-' }}"
                                        data-qty="{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}"
                                        data-method="{{ $paymentTypeLabel($payment->method) }}"
                                        data-total="Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}"
                                        data-dp="Rp {{ number_format((float) ($payment->order->dp_amount ?? 0), 0, ',', '.') }}"
                                        data-sisa="Rp {{ number_format((float) ($payment->order->remaining_amount ?? 0), 0, ',', '.') }}"
                                        data-midtrans-status="{{ $payment->midtrans_status ?: '-' }}"
                                        data-midtrans-order="{{ $payment->midtrans_order_id ?: '-' }}"
                                        data-midtrans-transaction="{{ $payment->midtrans_transaction_id ?: '-' }}"
                                        data-midtrans-channel="{{ $payment->midtrans_payment_type ?: '-' }}"
                                        data-readonly="1"
                                    >
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

</section>

<div id="financeDetailModal" class="detail-modal" aria-hidden="true">
    <div class="detail-modal-card" role="dialog" aria-modal="true" aria-label="Detail Pesanan Verifikasi">
        <div class="detail-modal-head">
            <h4>Detail Pesanan - Verifikasi</h4>
            <p id="modalOrderCode">-</p>
        </div>

        <div class="detail-grid">
            <p><strong>Pelanggan:</strong> <span id="modalCustomer">-</span></p>
            <p><strong>Mode Bayar:</strong> <span id="modalMethod">-</span></p>
            <p><strong>Produk:</strong> <span id="modalProduct">-</span></p>
            <p><strong>Qty:</strong> <span id="modalQty">-</span></p>
            <p><strong>Total:</strong> <span id="modalTotal">-</span></p>
            <p><strong>DP:</strong> <span id="modalDp">-</span></p>
            <p><strong>Sisa:</strong> <span id="modalSisa">-</span></p>
            <p><strong>Status Midtrans:</strong> <span id="modalMidtransStatus">-</span></p>
            <p><strong>Kanal Pembayaran:</strong> <span id="modalMidtransChannel">-</span></p>
            <p><strong>Order ID Midtrans:</strong> <span id="modalMidtransOrder">-</span></p>
            <p><strong>Transaction ID:</strong> <span id="modalMidtransTransaction">-</span></p>
        </div>

        <div class="transaction-preview">
            <div>
                <strong>Ringkasan Transaksi</strong><br>
                <span>Data transaksi disinkronkan otomatis dari Midtrans.</span>
            </div>
            <div class="action-links">
                <a id="modalInvoiceLink" class="btn-link" href="#" target="_blank" style="display:none;">Lihat Invoice</a>
            </div>
        </div>

        <form id="modalVerifyForm" method="POST" class="modal-form" action="#">
            @csrf
            <div>
                <label for="modalRejectReason">Alasan Tolak (wajib jika menekan Tolak)</label>
                <select id="modalRejectReason" name="reject_reason">
                    <option value="">Pilih alasan penolakan</option>
                    @foreach ($rejectReasons as $reason)
                        <option value="{{ $reason['code'] }}">{{ $reason['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <input id="modalNoteInput" type="text" name="notes" placeholder="Catatan (opsional)..." />
            <div class="modal-actions">
                <button id="modalApproveBtn" type="submit" name="action" value="verify" class="btn-approve">Verifikasi</button>
                <button id="modalRejectBtn" type="submit" name="action" value="reject" class="btn-small btn-reject">Tolak</button>
                <button type="button" class="btn-close" id="modalCloseBtn">Tutup</button>
            </div>
        </form>

        <div id="modalCloseWrap" class="modal-close-wrap">
            <button type="button" class="btn-close" id="modalCloseReadonlyBtn">Tutup</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('financeDetailModal');
        if (!modal) {
            return;
        }

        const form = document.getElementById('modalVerifyForm');
        const invoiceLink = document.getElementById('modalInvoiceLink');
        const closeBtn = document.getElementById('modalCloseBtn');
        const closeReadonlyBtn = document.getElementById('modalCloseReadonlyBtn');
        const closeWrap = document.getElementById('modalCloseWrap');
        const rejectReasonSelect = document.getElementById('modalRejectReason');

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '-';
        };

        document.querySelectorAll('.js-open-detail').forEach((button) => {
            button.addEventListener('click', () => {
                const data = button.dataset;

                form.action = data.verifyUrl || '#';
                setText('modalOrderCode', data.orderCode);
                setText('modalCustomer', data.customer);
                setText('modalMethod', data.method);
                setText('modalProduct', data.product);
                setText('modalQty', data.qty);
                setText('modalTotal', data.total);
                setText('modalDp', data.dp);
                setText('modalSisa', data.sisa);
                setText('modalMidtransStatus', data.midtransStatus);
                setText('modalMidtransChannel', data.midtransChannel);
                setText('modalMidtransOrder', data.midtransOrder);
                setText('modalMidtransTransaction', data.midtransTransaction);

                if (data.invoiceUrl) {
                    invoiceLink.href = data.invoiceUrl;
                    invoiceLink.style.display = 'inline';
                } else {
                    invoiceLink.removeAttribute('href');
                    invoiceLink.style.display = 'none';
                }

                if (data.readonly === '1') {
                    form.classList.add('hidden');
                    closeWrap.classList.add('open');
                } else {
                    form.classList.remove('hidden');
                    closeWrap.classList.remove('open');
                    if (rejectReasonSelect) {
                        rejectReasonSelect.value = '';
                    }
                }

                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        const closeModal = () => {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        };

        form.addEventListener('submit', (event) => {
            const submitter = event.submitter;
            if (!submitter) {
                return;
            }

            if (submitter.value === 'reject' && rejectReasonSelect && !rejectReasonSelect.value) {
                event.preventDefault();
                rejectReasonSelect.focus();
                alert('Pilih alasan penolakan terlebih dahulu sebelum menolak pembayaran.');
            }
        });

        closeBtn.addEventListener('click', closeModal);
        closeReadonlyBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    })();
</script>
@endsection
