@extends('layouts.app')

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
    .verification-header {
        background: linear-gradient(135deg, #0d2749 0%, #102945 50%, #1a3d5c 100%);
        border-radius: 16px;
        padding: clamp(1.5rem, 3vw, 2.2rem);
        margin-bottom: 1.8rem;
        border: 1px solid rgba(198, 166, 71, 0.2);
        position: relative;
        overflow: hidden;
    }

    .verification-header::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -5%;
        width: 400px;
        height: 400px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(198, 166, 71, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .verification-header-content {
        position: relative;
        z-index: 1;
    }

    .verification-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.34rem, 2.1vw, 1.75rem);
        color: #ffffff;
        margin: 0 0 0.4rem;
        font-weight: 700;
    }

    .verification-header p {
        color: #c8d6e8;
        margin: 0;
        font-size: 0.82rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.95rem;
        margin-top: 1.2rem;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(198, 166, 71, 0.2);
        border-radius: 12px;
        padding: 0.8rem 1rem;
    }

    .stat-item .label {
        font-size: 0.7rem;
        color: #a0b8d0;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        font-weight: 700;
        margin-bottom: 0.3rem;
    }

    .stat-item .value {
        font-family: 'Playfair Display', serif;
        font-size: 1.36rem;
        color: #c6a647;
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

    .proof-preview {
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

    .proof-preview strong {
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

<div class="verification-header">
    <div class="verification-header-content">
        <h1>Modul Verifikasi Pembayaran</h1>
        <p>Kelola dan verifikasi semua pembayaran DP, Pelunasan, atau Lunas Awal dari pelanggan</p>
        <div class="stats-row">
            <div class="stat-item">
                <div class="label">Pending DP</div>
                <div class="value">{{ $pendingDp->count() }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Pending Pelunasan</div>
                <div class="value">{{ $pendingSettlement->count() }}</div>
            </div>
            <div class="stat-item">
                <div class="label">Pending Lunas Awal</div>
                <div class="value">{{ $pendingFull->count() }}</div>
            </div>
        </div>
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
                        <th>Pengirim</th>
                        <th>Bank Tujuan</th>
                        <th>Atas Nama</th>
                        <th>Nominal Bayar</th>
                        <th>Harus Bayar</th>
                        <th>Bukti</th>
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
                            <td>{{ $payment->sender_bank_name }}</td>
                            <td>
                                @php($bank = $payment->destinationBankDetails())
                                <strong>{{ $bank['label'] ?? '-' }}</strong><br>
                                <span style="font-size: 0.8rem; color: #7893ae;">{{ $bank['account_number'] ?? '-' }}</span>
                            </td>
                            <td>{{ $payment->sender_account_name }}</td>
                            <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($expected, 0, ',', '.') }}</strong></td>
                            <td>
                                <div class="action-links">
                                    @if ($payment->proof_path)
                                        <a class="btn-link" href="{{ route('finance.payments.proof', $payment) }}" target="_blank">Lihat Bukti</a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn-detail js-open-detail"
                                    data-verify-url="{{ route('finance.verify', $payment) }}"
                                    data-proof-url="{{ $payment->proof_path ? route('finance.payments.proof', $payment) : '' }}"
                                    data-order-code="{{ $payment->order->order_code }}"
                                    data-customer="{{ $payment->order->user->name }}"
                                    data-product="{{ $payment->order->product_name ?: '-' }}"
                                    data-qty="{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}"
                                    data-method="{{ $paymentTypeLabel($payment->method) }}"
                                    data-total="Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}"
                                    data-dp="Rp {{ number_format((float) ($payment->order->dp_amount ?? 0), 0, ',', '.') }}"
                                    data-sisa="Rp {{ number_format((float) ($payment->order->remaining_amount ?? 0), 0, ',', '.') }}"
                                    data-bank="{{ $bank['label'] ?? '-' }}"
                                    data-bank-account="{{ $bank['account_number'] ?? '-' }}"
                                    data-sender-bank="{{ $payment->sender_bank_name }}"
                                    data-sender-name="{{ $payment->sender_account_name }}"
                                    data-proof-name="{{ $payment->proof_path ? basename($payment->proof_path) : '-' }}"
                                    data-readonly="0"
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
    <h3>✓ Invoice Verifikasi Terbit</h3>
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
                        @php($bank = $payment->destinationBankDetails())
                        <tr>
                            <td><strong>{{ $payment->invoice_number ?? '-' }}</strong></td>
                            <td>{{ $payment->order->order_code }}</td>
                            <td>{{ $payment->order->user->name }}</td>
                            <td><span class="pay-type {{ $paymentTypeClass($payment->method) }}">{{ $paymentTypeLabel($payment->method) }}</span></td>
                            <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                            <td>{{ $payment->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                <div class="action-links">
                                    @if($payment->proof_path)
                                        <a class="btn-link" href="{{ route('finance.payments.proof', $payment) }}" target="_blank">Lihat Bukti</a>
                                    @endif
                                    <a class="btn-link" href="{{ route('finance.invoices.show', $payment) }}" target="_blank">Download Invoice</a>
                                    <button
                                        type="button"
                                        class="btn-detail js-open-detail"
                                        data-verify-url=""
                                        data-proof-url="{{ $payment->proof_path ? route('finance.payments.proof', $payment) : '' }}"
                                        data-invoice-url="{{ route('finance.invoices.show', $payment) }}"
                                        data-order-code="{{ $payment->order->order_code }}"
                                        data-customer="{{ $payment->order->user->name }}"
                                        data-product="{{ $payment->order->product_name ?: '-' }}"
                                        data-qty="{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}"
                                        data-method="{{ $paymentTypeLabel($payment->method) }}"
                                        data-total="Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}"
                                        data-dp="Rp {{ number_format((float) ($payment->order->dp_amount ?? 0), 0, ',', '.') }}"
                                        data-sisa="Rp {{ number_format((float) ($payment->order->remaining_amount ?? 0), 0, ',', '.') }}"
                                        data-bank="{{ $bank['label'] ?? '-' }}"
                                        data-bank-account="{{ $bank['account_number'] ?? '-' }}"
                                        data-sender-bank="{{ $payment->sender_bank_name }}"
                                        data-sender-name="{{ $payment->sender_account_name }}"
                                        data-proof-name="{{ $payment->proof_path ? basename($payment->proof_path) : '-' }}"
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

<div class="section-card">
    <h3>✕ Gagal Pembayaran</h3>
    <p>Bukti bayar yang ditolak oleh tim keuangan ditampilkan di sini untuk ditinjau ulang</p>

    @if($rejectedPayments->isEmpty())
        <div class="empty-state">
            <h4>Belum Ada Penolakan</h4>
            <p>Data pembayaran yang ditolak akan muncul pada bagian ini.</p>
        </div>
    @else
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
                        @php($bank = $payment->destinationBankDetails())
                        <tr>
                            <td><strong>{{ $payment->order->order_code }}</strong></td>
                            <td>{{ $payment->order->user->name }}</td>
                            <td><span class="pay-type {{ $paymentTypeClass($payment->method) }}">{{ $paymentTypeLabel($payment->method) }}</span></td>
                            <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                            <td>{{ $payment->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $payment->notes ?: '-' }}</td>
                            <td>
                                <div class="action-links">
                                    @if($payment->proof_path)
                                        <a class="btn-link" href="{{ route('finance.payments.proof', $payment) }}" target="_blank">Lihat Bukti</a>
                                    @endif
                                    <button
                                        type="button"
                                        class="btn-detail js-open-detail"
                                        data-verify-url=""
                                        data-proof-url="{{ $payment->proof_path ? route('finance.payments.proof', $payment) : '' }}"
                                        data-invoice-url=""
                                        data-order-code="{{ $payment->order->order_code }}"
                                        data-customer="{{ $payment->order->user->name }}"
                                        data-product="{{ $payment->order->product_name ?: '-' }}"
                                        data-qty="{{ number_format((int) $payment->order->total_pcs, 0, ',', '.') }}"
                                        data-method="{{ $paymentTypeLabel($payment->method) }}"
                                        data-total="Rp {{ number_format((float) $payment->order->subtotal, 0, ',', '.') }}"
                                        data-dp="Rp {{ number_format((float) ($payment->order->dp_amount ?? 0), 0, ',', '.') }}"
                                        data-sisa="Rp {{ number_format((float) ($payment->order->remaining_amount ?? 0), 0, ',', '.') }}"
                                        data-bank="{{ $bank['label'] ?? '-' }}"
                                        data-bank-account="{{ $bank['account_number'] ?? '-' }}"
                                        data-sender-bank="{{ $payment->sender_bank_name }}"
                                        data-sender-name="{{ $payment->sender_account_name }}"
                                        data-proof-name="{{ $payment->proof_path ? basename($payment->proof_path) : '-' }}"
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
            <p><strong>Bank Pengirim:</strong> <span id="modalSenderBank">-</span></p>
            <p><strong>Bank Tujuan:</strong> <span id="modalBank">-</span> (<span id="modalBankAccount">-</span>)</p>
            <p><strong>Atas Nama:</strong> <span id="modalSenderName">-</span></p>
        </div>

        <div class="proof-preview">
            <div>
                <strong>Preview Bukti Transfer</strong><br>
                <span id="modalProofName">-</span>
            </div>
            <div class="action-links">
                <a id="modalProofLink" class="btn-link" href="#" target="_blank" style="display:none;">Lihat Bukti Bayar</a>
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
        const proofLink = document.getElementById('modalProofLink');
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
                setText('modalBank', data.bank);
                setText('modalBankAccount', data.bankAccount);
                setText('modalSenderBank', data.senderBank);
                setText('modalSenderName', data.senderName);
                setText('modalProofName', data.proofName);

                if (data.proofUrl) {
                    proofLink.href = data.proofUrl;
                    proofLink.style.display = 'inline';
                } else {
                    proofLink.removeAttribute('href');
                    proofLink.style.display = 'none';
                }

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
