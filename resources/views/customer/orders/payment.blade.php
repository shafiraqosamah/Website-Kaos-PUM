@extends('layouts.app')

@section('content')
<!-- Load Midtrans Snap JS -->
@php
    $clientKey = config('midtrans.client_key');
    if (!$clientKey) {
        \Log::warning('Midtrans Client Key not configured');
    }
@endphp
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
<script>
    // Verify Snap library loaded
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.snap) {
            console.error('Midtrans Snap library failed to load. Client Key:', '{{ $clientKey }}');
        } else {
            console.log('Midtrans Snap library loaded successfully');
        }
    });
</script>

@php
    $sizeBreakdown = $order->sizes
        ->map(fn ($size) => $size->size_name . ' (' . $size->qty . ')')
        ->implode(', ');

    $isSettlementPayment = $payment->method === 'settlement';
    $selectedOption = old('payment_option', $payment->method === 'full' ? 'full' : 'dp');
    $baseSubtotal = (float) $order->subtotal;
    $dpAmount = $baseSubtotal * 0.5;
    $fullAmount = $baseSubtotal;
    $settlementTotal = (float) $order->subtotal;
    $settlementRemaining = (float) ($order->remaining_amount ?? 0);
    $settlementDpPaid = max($settlementTotal - $settlementRemaining, 0);

    $rejectionReasonCode = null;
    $rejectionReasonLabel = null;
    $rejectionActionText = null;

    $rejectionMap = [
        'amount_mismatch' => [
            'label' => 'Nominal tidak sesuai',
            'action' => 'Sesuaikan nominal transfer sesuai tagihan, lalu upload ulang bukti pembayaran.',
        ],
        'proof_unreadable' => [
            'label' => 'Bukti blur/tidak terbaca',
            'action' => 'Upload ulang bukti pembayaran dengan gambar/file yang lebih jelas.',
        ],
        'wrong_destination' => [
            'label' => 'Transfer ke rekening tujuan yang salah',
            'action' => 'Lakukan transfer ke rekening tujuan yang benar lalu upload bukti terbaru.',
        ],
        'identity_mismatch' => [
            'label' => 'Data pengirim tidak sesuai',
            'action' => 'Perbaiki data bank/nama pengirim lalu kirim ulang bukti pembayaran.',
        ],
        'duplicate_proof' => [
            'label' => 'Bukti duplikat/tidak sesuai transaksi',
            'action' => 'Upload bukti transaksi yang benar dan belum pernah digunakan.',
        ],
        'other' => [
            'label' => 'Alasan lain',
            'action' => 'Ikuti instruksi pada catatan keuangan lalu kirim ulang bukti pembayaran.',
        ],
    ];

    $paymentNoteLines = preg_split('/\R/', (string) ($payment->notes ?? '')) ?: [];
    foreach ($paymentNoteLines as $line) {
        $trimmedLine = trim((string) $line);

        if (\Illuminate\Support\Str::startsWith($trimmedLine, 'Alasan penolakan kode:')) {
            $rejectionReasonCode = trim((string) \Illuminate\Support\Str::after($trimmedLine, 'Alasan penolakan kode:'));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmedLine, 'Alasan penolakan:')) {
            $rejectionReasonLabel = trim((string) \Illuminate\Support\Str::after($trimmedLine, 'Alasan penolakan:'));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmedLine, 'Tindakan customer:')) {
            $rejectionActionText = trim((string) \Illuminate\Support\Str::after($trimmedLine, 'Tindakan customer:'));
        }
    }

    if ($rejectionReasonCode && isset($rejectionMap[$rejectionReasonCode])) {
        $rejectionReasonLabel = $rejectionReasonLabel ?: $rejectionMap[$rejectionReasonCode]['label'];
        $rejectionActionText = $rejectionActionText ?: $rejectionMap[$rejectionReasonCode]['action'];
    }

    $customerVisibleNoteLines = array_values(array_filter($paymentNoteLines, static function (string $line): bool {
        $normalized = \Illuminate\Support\Str::lower(trim($line));

        return $normalized !== ''
            && ! \Illuminate\Support\Str::startsWith($normalized, 'alasan penolakan kode:')
            && ! \Illuminate\Support\Str::startsWith($normalized, 'alasan penolakan:')
            && ! \Illuminate\Support\Str::startsWith($normalized, 'tindakan customer:')
            && ! \Illuminate\Support\Str::startsWith($normalized, 'catatan keuangan:');
    }));

    $customerVisibleNotes = trim(implode(PHP_EOL, $customerVisibleNoteLines));
@endphp
<style>
    .payment-page-header {
        margin: 0.55rem 0 1.05rem;
        padding: 0.7rem 1.25rem 0.35rem;
    }

    .payment-page-header h1 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.66rem, 2.05vw, 1.96rem);
        line-height: 1.1;
        color: #0d2749;
    }

    .payment-page-header p {
        margin: 0.5rem 0 0;
        color: #7893ae;
        font-size: clamp(0.86rem, 0.96vw, 0.94rem);
        font-weight: 500;
    }

    .payment-form-layout {
        display: grid;
        gap: 1rem;
        padding: 0 1.25rem 1.1rem;
    }

    .payment-columns {
        display: grid;
        grid-template-columns: minmax(360px, 0.88fr) minmax(420px, 1.12fr);
        gap: 1rem;
        align-items: start;
    }

    .payment-card,
    .payment-form-card {
        border: 1px solid #c8d7e7;
        border-radius: 20px;
        background: #f2f4f6;
    }

    .payment-card {
        padding: 1.12rem;
    }

    .payment-summary-card {
        width: 100%;
        max-width: 760px;
        justify-self: start;
    }

    .payment-form-card {
        padding: 1rem;
    }

    .section-heading {
        margin: 0;
        font-size: 0.96rem;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .section-divider {
        width: 120px;
        height: 2px;
        background: #c8a949;
        margin: 0.44rem 0 0.82rem;
    }

    .payment-info-table {
        margin: 0;
    }

    .payment-info-table th {
        color: #35526a;
        width: 44%;
        font-size: 0.8rem;
    }

    .payment-info-table td {
        font-size: 0.84rem;
    }

    .status-pill {
        text-transform: capitalize;
    }

    .payment-option-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-bottom: 0.85rem;
    }

    .payment-option {
        position: relative;
    }

    .payment-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-option-label {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 2px solid #b9c8d6;
        border-radius: 16px;
        background: #f7f9fc;
        min-height: 106px;
        padding: 0.82rem 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .payment-option-title {
        margin: 0;
        font-size: 0.8rem;
        color: #0d2749;
        font-weight: 700;
    }

    .payment-option-value {
        margin: 0.35rem 0 0;
        font-size: 0.95rem;
        color: #0d2749;
        font-weight: 700;
    }

    .payment-option-desc {
        margin: 0.25rem 0 0;
        font-size: 0.78rem;
        color: #6f86a0;
        line-height: 1.35;
        max-width: 240px;
    }

    .payment-option input:checked + .payment-option-label {
        border-color: #c8a949;
        background: #f2f0ea;
    }

    .payment-breakdown {
        border: 1px solid #c7d6e5;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .payment-breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #d6e1ec;
        font-size: 0.84rem;
        color: #15344f;
    }

    .payment-breakdown-row:last-child {
        border-bottom: 0;
    }

    .payment-breakdown-row strong {
        font-size: 0.96rem;
    }

    .payment-breakdown-row.dp-now {
        background: #f4f0e8;
        color: #0d2749;
    }

    .payment-breakdown-row.dp-remain {
        background: #fdf2f2;
        color: #c13a2f;
    }

    .payment-breakdown-row.full-now {
        background: #081f3a;
        color: #fff;
    }

    .payment-breakdown-row.full-now strong {
        color: #fff;
    }

    .settlement-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        border: 1px solid #f2c3b9;
        border-radius: 14px;
        background: #fff4ef;
        color: #b63b22;
        padding: 0.78rem 0.92rem;
        margin-bottom: 0.85rem;
        font-size: 0.86rem;
        line-height: 1.4;
    }

    .settlement-summary {
        border: 1px solid #c7d6e5;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        margin-bottom: 0.9rem;
    }

    .settlement-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #d6e1ec;
        color: #15344f;
        font-size: 0.84rem;
    }

    .settlement-summary-row:last-child {
        border-bottom: 0;
    }

    .settlement-summary-row strong {
        font-size: 0.96rem;
    }

    .settlement-summary-row.total {
        background: #f4f8fc;
    }

    .settlement-summary-row.dp-paid {
        background: #eef6f2;
        color: #1f6f45;
    }

    .settlement-summary-row.remaining {
        background: #fdf2f2;
        color: #c13a2f;
        font-weight: 700;
    }

    .bank-list {
        display: grid;
        grid-template-rows: repeat(2, minmax(0, 1fr));
        grid-auto-flow: column;
        grid-auto-columns: minmax(0, 1fr);
        gap: 0.7rem;
    }

    .bank-item {
        padding: 0.8rem;
        border: 1px solid #cddbe8;
        border-radius: 12px;
        background: #fbfdff;
    }

    .bank-item strong {
        display: block;
        color: #13334b;
        font-size: 0.86rem;
        margin-bottom: 0.15rem;
    }

    .bank-item .muted {
        font-size: 0.8rem;
    }

    .payment-fields-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.72rem 0.9rem;
    }

    .payment-fields-grid label {
        font-size: 0.8rem;
        color: #0d2749;
        font-weight: 700;
    }

    .payment-fields-grid input,
    .payment-fields-grid select,
    .payment-fields-grid textarea {
        width: 100%;
        background: #fff;
        border: 1px solid #c3d2e2;
        border-radius: 12px;
        padding: 0.54rem 0.7rem;
        font-size: 0.8rem;
        color: #13283a;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .required-star {
        color: #c22b2b;
        font-weight: 700;
        margin-left: 0.12rem;
    }

    .submit-hint {
        margin: 0;
        font-size: 0.8rem;
        color: #7a4b13;
    }

    .payment-submit-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .payment-method-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .payment-method-tab {
        position: relative;
    }

    .payment-method-tab input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-method-tab-label {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border: 2px solid #c8d7e7;
        border-radius: 12px;
        background: #f7f9fc;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.88rem;
        font-weight: 600;
        color: #0d2749;
    }

    .payment-method-tab input:checked + .payment-method-tab-label {
        border-color: #c8a949;
        background: #f2f0ea;
        color: #0d2749;
    }

    .payment-method-content {
        display: none;
    }

    .payment-method-content.active {
        display: block;
    }

    .btn-pay-midtrans {
        width: 100%;
        padding: 0.95rem 1rem;
        background: linear-gradient(135deg, #2299dd, #1a74b8);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 0.96rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-pay-midtrans:hover {
        background: linear-gradient(135deg, #1a87cc, #1565a8);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(34, 153, 221, 0.3);
    }

    .btn-pay-midtrans:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .payment-status-info {
        padding: 0.75rem 1rem;
        background: #f0f4f8;
        border-left: 4px solid #2299dd;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #1a4a75;
        margin-bottom: 1rem;
    }

    .payment-status-info.settled {
        background: #e1f4e1;
        border-left-color: #1c6a47;
        color: #1c6a47;
    }

    .payment-status-info.rejected {
        background: #ffe1e1;
        border-left-color: #c13a2f;
        color: #8f2f2f;
    }

    #midtransStatusInfo {
        display: none;
    }

    #midtransStatusInfo.show {
        display: block;
    }

    .rejected-alert {
        grid-column: 1 / -1;
        border: 1px solid #efc1b8;
        border-radius: 14px;
        background: #fff4ef;
        padding: 0.82rem 0.95rem;
        color: #b63b22;
    }

    .rejected-alert h4 {
        margin: 0;
        font-size: 0.96rem;
        color: #9f2f1a;
        font-family: 'Playfair Display', serif;
    }

    .rejected-alert p {
        margin: 0.38rem 0 0;
        font-size: 0.82rem;
        line-height: 1.4;
        color: #9f2f1a;
    }

    .rejected-alert a {
        color: #9f2f1a;
        text-decoration: underline;
        font-weight: 700;
    }

    #paymentSubmitBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        filter: grayscale(0.1);
    }

    /* Success Modal Styles */
    .success-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .success-modal-overlay.show {
        display: flex;
    }

    .success-modal {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        max-width: 420px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .success-modal-icon {
        width: 60px;
        height: 60px;
        background: #1c6a47;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
    }

    .success-modal h2 {
        margin: 0 0 0.8rem;
        font-family: 'Playfair Display', serif;
        font-size: 1.6rem;
        color: #0d2749;
        line-height: 1.2;
    }

    .success-modal p {
        margin: 0 0 1.5rem;
        color: #6f86a0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .success-modal-order-code {
        background: #f4f0e8;
        border-radius: 12px;
        padding: 0.8rem;
        margin-bottom: 1.5rem;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        color: #0d2749;
        font-size: 0.95rem;
    }

    .success-modal-button {
        display: inline-block;
        width: 100%;
        padding: 0.95rem 1rem;
        background: linear-gradient(135deg, #1c6a47, #155638);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 0.96rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .success-modal-button:hover {
        background: linear-gradient(135deg, #155638, #0d4d2e);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(28, 106, 71, 0.3);
    }

    .countdown-timer {
        font-size: 0.85rem;
        color: #999;
        margin-top: 1rem;
    }

    @media (max-width: 1100px) {
        .payment-page-header,
        .payment-form-layout {
            padding-left: 0;
            padding-right: 0;
        }

        .payment-columns {
            grid-template-columns: 1fr;
        }

        .payment-fields-grid {
            grid-template-columns: 1fr;
        }

        .payment-option-grid {
            grid-template-columns: 1fr;
        }

        .bank-list {
            grid-template-rows: none;
            grid-auto-flow: row;
            grid-auto-columns: 1fr;
        }
    }
</style>

<div class="payment-page-header">
    <h1>{{ $isSettlementPayment ? 'Pelunasan Pesanan' : 'Pembayaran Pesanan' }}</h1>
    <p>{{ $isSettlementPayment ? 'Selesaikan pelunasan dan upload bukti transfer untuk melanjutkan proses produksi' : 'Pilih metode pembayaran dan upload bukti transfer' }}</p>
</div>

<div class="payment-form-layout">
    <div class="payment-columns">
        <div class="payment-card payment-summary-card">
            <h3 class="section-heading">Ringkasan Pembayaran</h3>
            <div class="section-divider"></div>
            <table class="payment-info-table">
                <tr><th>Kode Order</th><td>{{ $order->order_code }}</td></tr>
                <tr><th>Produk</th><td>{{ $order->product_model ?: $order->product_name }}</td></tr>
                <tr><th>Bahan</th><td>{{ $order->fabric }}</td></tr>
                <tr><th>Teknik Sablon</th><td>{{ $order->production_type ?: '-' }}</td></tr>
                <tr><th>Warna</th><td>{{ $order->dominant_color ?: '-' }}{{ $order->secondary_color ? ' / ' . $order->secondary_color : '' }}</td></tr>
                <tr><th>Ukuran QTY</th><td>{{ $sizeBreakdown !== '' ? $sizeBreakdown : '-' }}</td></tr>
                <tr><th>Status</th><td><span class="status-pill">{{ $payment->status }}</span></td></tr>
            </table>
        </div>

        <div class="payment-card">
            @if (! $isSettlementPayment)
                <h3 class="section-heading" style="margin-bottom:0.75rem;">Opsi Pembayaran</h3>
                <div class="section-divider"></div>
                <div class="payment-option-grid">
                    <label class="payment-option">
                        <input type="radio" name="payment_option" value="dp" @checked($selectedOption === 'dp')>
                        <span class="payment-option-label">
                            <p class="payment-option-title">💳 DP 50%</p>
                            <p class="payment-option-desc">Bayar sekarang 50%, sisanya saat finishing</p>
                        </span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_option" value="full" @checked($selectedOption === 'full')>
                        <span class="payment-option-label">
                            <p class="payment-option-title">✅ Lunas</p>
                            <p class="payment-option-desc">Bayar penuh sekarang</p>
                        </span>
                    </label>
                </div>

                <div id="paymentBreakdown" class="payment-breakdown" data-subtotal="{{ $baseSubtotal }}" data-dp="{{ $dpAmount }}" data-full="{{ $fullAmount }}">
                    <div class="payment-breakdown-row">
                        <span>Total Pesanan</span>
                        <strong id="breakdownTotal">Rp {{ number_format($baseSubtotal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="payment-breakdown-row dp-now" data-breakdown="dp-primary">
                        <span>DP 50% (Bayar Sekarang)</span>
                        <strong id="breakdownDpNow">Rp {{ number_format($dpAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="payment-breakdown-row dp-remain" data-breakdown="dp-secondary">
                        <span>Sisa Pelunasan (Saat Finishing)</span>
                        <strong id="breakdownDpRemain">Rp {{ number_format($dpAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="payment-breakdown-row full-now" data-breakdown="full-primary" style="display:none;">
                        <span>Bayar Lunas Sekarang</span>
                        <strong id="breakdownFullNow">Rp {{ number_format($fullAmount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            @else
                <div class="settlement-alert">
                    <span>⚠️</span>
                    <span>Pesanan memasuki tahap <strong>Steam & Pressing</strong>. Tahapan finishing tidak dapat dilakukan sebelum pelunasan terverifikasi.</span>
                </div>

                <div class="settlement-summary">
                    <div class="settlement-summary-row total">
                        <span>Total Pesanan</span>
                        <strong>Rp {{ number_format($settlementTotal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="settlement-summary-row dp-paid">
                        <span>DP Sudah Dibayar</span>
                        <strong>Rp {{ number_format($settlementDpPaid, 0, ',', '.') }}</strong>
                    </div>
                    <div class="settlement-summary-row remaining">
                        <span>Sisa Pelunasan</span>
                        <strong>Rp {{ number_format($settlementRemaining, 0, ',', '.') }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="payment-form-card">
        <h3 class="section-heading">Metode Pembayaran</h3>
        <div class="section-divider"></div>

        <div class="payment-method-tabs">
            <label class="payment-method-tab">
                <input type="radio" name="payment_method_choice" value="midtrans" id="methodMidtrans" checked>
                <span class="payment-method-tab-label">💳 Midtrans Payment Gateway</span>
            </label>
        </div>

        <!-- Midtrans Payment Method -->
        <div id="midtransMethod" class="payment-method-content active">
            <div class="payment-status-info">
                ℹ️ Metode pembayaran Midtrans | Bayar dengan VA, E-wallet, QRIS, atau Kartu Kredit
            </div>

            <div id="midtransStatusInfo" class="payment-status-info"></div>

            <div style="display: grid; gap: 0.65rem; margin-bottom: 1rem;">
                <p style="margin: 0; font-size: 0.9rem; color: #0d2749;"><strong>Jumlah Pembayaran yang Harus Dibayar:</strong></p>
                <p style="margin: 0; font-size: 1.2rem; color: #0d2749; font-weight: 700;">
                    <span id="midtransAmount">Rp 0</span>
                </p>
            </div>

            <button type="button" id="btnPayMidtrans" class="btn-pay-midtrans">Klik Bayar Sekarang</button>

            <p style="margin: 0.75rem 0 0; font-size: 0.8rem; color: #666; text-align: center;">Klik tombol untuk membuka halaman pembayaran Midtrans</p>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal-overlay">
    <div class="success-modal">
        <div class="success-modal-icon">✓</div>
        <h2>Pembayaran Berhasil!</h2>
        <p>Pembayaran Anda telah berhasil diproses. Pesanan Anda sedang dipersiapkan untuk produksi.</p>
        <div class="success-modal-order-code" id="successOrderCode">
            Order: #{{ $order->order_code }}
        </div>
        <button id="successModalButton" class="success-modal-button" type="button">
            Lihat Riwayat Transaksi
        </button>
        <div class="countdown-timer">
            Halaman akan otomatis redirect dalam <span id="countdown">5</span> detik
        </div>
    </div>
</div>

<script>
(() => {
    const btnPayMidtrans = document.getElementById('btnPayMidtrans');
    const midtransAmountSpan = document.getElementById('midtransAmount');
    const midtransStatusInfo = document.getElementById('midtransStatusInfo');
    const paymentOptionInputs = Array.from(document.querySelectorAll('input[name="payment_option"]'));
    const paymentBreakdown = document.getElementById('paymentBreakdown');
    const successModal = document.getElementById('successModal');
    const successModalButton = document.getElementById('successModalButton');
    const countdownSpan = document.getElementById('countdown');
    const isSettlementPayment = @json($isSettlementPayment);
    const settlementRemaining = Number(@json((float) $settlementRemaining));
    const ordersIndexUrl = @json(route('customer.orders.index'));

    let countdownValue = 5;
    let countdownInterval = null;
    let redirectInProgress = false;
    let activePaymentId = null;

    const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

    const getSelectedOption = () => paymentOptionInputs.find((input) => input.checked)?.value || 'dp';

    const getSelectedPaymentAmount = () => {
        if (isSettlementPayment) {
            return settlementRemaining;
        }

        if (!paymentBreakdown) return 0;

        const selectedOption = getSelectedOption();
        const subtotal = Number(paymentBreakdown.dataset.subtotal || 0);
        return selectedOption === 'full' ? subtotal : subtotal * 0.5;
    };

    const updatePaymentBreakdown = () => {
        if (isSettlementPayment || !paymentBreakdown) {
            return;
        }

        const selectedOption = getSelectedOption();
        const dpNowRow = paymentBreakdown.querySelector('[data-breakdown="dp-primary"]');
        const dpRemainRow = paymentBreakdown.querySelector('[data-breakdown="dp-secondary"]');
        const fullNowRow = paymentBreakdown.querySelector('[data-breakdown="full-primary"]');

        if (selectedOption === 'full') {
            if (dpNowRow) dpNowRow.style.display = 'none';
            if (dpRemainRow) dpRemainRow.style.display = 'none';
            if (fullNowRow) fullNowRow.style.display = 'flex';
            return;
        }

        if (dpNowRow) dpNowRow.style.display = 'flex';
        if (dpRemainRow) dpRemainRow.style.display = 'flex';
        if (fullNowRow) fullNowRow.style.display = 'none';
    };

    const updateMidtransAmount = () => {
        const amount = getSelectedPaymentAmount();
        midtransAmountSpan.textContent = `Rp ${formatCurrency(amount)}`;
    };

    const showSuccessModal = () => {
        successModal.classList.add('show');
        countdownValue = 5;
        countdownSpan.textContent = countdownValue;

        // Start countdown
        countdownInterval = setInterval(() => {
            countdownValue--;
            countdownSpan.textContent = countdownValue;

            if (countdownValue <= 0) {
                clearInterval(countdownInterval);
                triggerPostSuccessRedirect();
            }
        }, 1000);
    };

    const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    const checkPaymentStatus = async (paymentId) => {
        try {
            const response = await fetch('{{ route("midtrans.check-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                }),
            });

            const data = await response.json();

            if (data.success) {
                console.log('Payment status:', data.transaction_status);
                if (data.transaction_status === 'settlement' || data.transaction_status === 'capture') {
                    midtransStatusInfo.textContent = '✅ Pembayaran berhasil! Pesanan Anda sedang diproses.';
                    midtransStatusInfo.className = 'payment-status-info settled show';
                    btnPayMidtrans.disabled = true;
                    btnPayMidtrans.textContent = 'Pembayaran Berhasil';
                } else if (data.transaction_status === 'pending') {
                    midtransStatusInfo.textContent = '⏳ Pembayaran masih menunggu konfirmasi...';
                    midtransStatusInfo.className = 'payment-status-info show';
                    btnPayMidtrans.disabled = false;
                    btnPayMidtrans.textContent = 'Klik Bayar Sekarang';
                } else {
                    midtransStatusInfo.textContent = `⚠️ Status transaksi: ${data.transaction_status}`;
                    midtransStatusInfo.className = 'payment-status-info rejected show';
                    btnPayMidtrans.disabled = false;
                    btnPayMidtrans.textContent = 'Klik Bayar Sekarang';
                }

                return data;
            }

            return null;
        } catch (error) {
            console.error('Error checking status:', error);
            return null;
        }
    };

    const syncStatusBeforeRedirect = async () => {
        if (!activePaymentId) {
            return;
        }

        // Poll briefly so quick modal close/click still syncs latest Midtrans state.
        for (let attempt = 0; attempt < 5; attempt++) {
            const statusData = await checkPaymentStatus(activePaymentId);
            const transactionStatus = statusData?.transaction_status;

            if (transactionStatus === 'settlement' || transactionStatus === 'capture') {
                return;
            }

            await wait(1000);
        }
    };

    const triggerPostSuccessRedirect = async () => {
        if (redirectInProgress) {
            return;
        }

        redirectInProgress = true;
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        if (successModalButton) {
            successModalButton.disabled = true;
            successModalButton.textContent = 'Memeriksa Status...';
        }

        await syncStatusBeforeRedirect();
        window.location.href = ordersIndexUrl;
    };

    const initiateMidtransPayment = async () => {
        try {
            const amount = getSelectedPaymentAmount();
            if (amount <= 0) {
                alert('Jumlah pembayaran tidak valid');
                return;
            }

            btnPayMidtrans.disabled = true;
            btnPayMidtrans.textContent = 'Sedang memproses...';
            midtransStatusInfo.classList.remove('show');

            const paymentMethod = isSettlementPayment
                ? 'settlement'
                : (paymentOptionInputs.find((input) => input.checked)?.value || 'dp');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            console.log('Initiating payment:', {
                order_id: {{ $order->id }},
                payment_method: paymentMethod,
                amount: amount,
                csrf_token: csrfToken ? 'present' : 'MISSING',
            });

            const response = await fetch('{{ route("midtrans.initiate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    order_id: {{ $order->id }},
                    payment_method: paymentMethod,
                }),
            });

            console.log('Response status:', response.status);
            const responseText = await response.text();
            console.log('Response body:', responseText.substring(0, 200));

            const data = JSON.parse(responseText);

            if (!data.success) {
                throw new Error(data.message || 'Gagal membuat transaksi');
            }

            if (!window.snap) {
                throw new Error('Midtrans Snap library belum ter-load. Coba refresh halaman dan ulang lagi.');
            }

            window.snap.pay(data.snap_token, {
                onSuccess: (result) => {
                    console.log('Payment success:', result);
                    activePaymentId = data.payment_id;
                    showSuccessModal();
                    btnPayMidtrans.disabled = true;
                    btnPayMidtrans.textContent = 'Pembayaran Berhasil';
                },
                onPending: (result) => {
                    console.log('Payment pending:', result);
                    midtransStatusInfo.textContent = '⏳ Pembayaran sedang diproses. Silahkan tunggu konfirmasi...';
                    midtransStatusInfo.classList.add('show');
                    btnPayMidtrans.disabled = false;
                    btnPayMidtrans.textContent = 'Klik Bayar Sekarang';
                },
                onError: (result) => {
                    console.log('Payment error:', result);
                    midtransStatusInfo.textContent = '❌ Pembayaran gagal. Silahkan coba lagi.';
                    midtransStatusInfo.classList.add('show', 'rejected');
                    btnPayMidtrans.disabled = false;
                    btnPayMidtrans.textContent = 'Klik Bayar Sekarang';
                },
                onClose: () => {
                    console.log('Payment popup closed');
                    btnPayMidtrans.disabled = false;
                    btnPayMidtrans.textContent = 'Klik Bayar Sekarang';
                },
            });
        } catch (error) {
            console.error('Error:', error);
            midtransStatusInfo.textContent = `❌ Error: ${error.message}`;
            midtransStatusInfo.classList.add('show', 'rejected');
            btnPayMidtrans.disabled = false;
            btnPayMidtrans.textContent = 'Klik Bayar Sekarang';
        }
    };

    paymentOptionInputs.forEach((input) => {
        input.addEventListener('change', () => {
            updatePaymentBreakdown();
            updateMidtransAmount();
        });
    });

    if (successModalButton) {
        successModalButton.addEventListener('click', () => {
            triggerPostSuccessRedirect();
        });
    }

    btnPayMidtrans.addEventListener('click', initiateMidtransPayment);

    // Initialize
    updatePaymentBreakdown();
    updateMidtransAmount();
})();
</script>
@endsection
