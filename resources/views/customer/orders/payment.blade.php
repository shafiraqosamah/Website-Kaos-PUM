@extends('layouts.app')

@section('content')
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
                <tr><th>Warna</th><td>{{ $order->dominant_color ?: '-' }}</td></tr>
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
                        <input type="radio" name="payment_option" value="dp" form="paymentForm" @checked($selectedOption === 'dp')>
                        <span class="payment-option-label">
                            <p class="payment-option-title">💳 DP 50%</p>
                            <p class="payment-option-desc">Bayar sekarang 50%, sisanya saat finishing</p>
                        </span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_option" value="full" form="paymentForm" @checked($selectedOption === 'full')>
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
                    <span>Pesanan memasuki tahap <strong>Finishing</strong>. Produksi tidak akan diselesaikan sebelum pelunasan dikonfirmasi.</span>
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
        <form id="paymentForm" method="POST" action="{{ route('customer.orders.payments.update', [$order, $payment]) }}" enctype="multipart/form-data" class="payment-fields-grid">
            @csrf
            @if ($payment->status === 'rejected')
                <div class="rejected-alert">
                    <h4>⚠️ Bukti pembayaran ditolak</h4>
                    <p><strong>Alasan:</strong> {{ $rejectionReasonLabel ?: 'Lihat catatan keuangan pada detail pembayaran.' }}</p>
                    <p><strong>Tindakan:</strong> {{ $rejectionActionText ?: 'Perbaiki data pembayaran lalu upload ulang bukti pembayaran.' }}</p>
                    @if ($payment->proof_path)
                        <p><a href="{{ route('customer.orders.payments.proof', [$order, $payment]) }}" target="_blank">Lihat bukti yang sebelumnya diupload</a></p>
                    @endif
                </div>
            @endif

            <div class="field-full">
                <h3 class="section-heading">Rekening Tujuan</h3>
                <div class="section-divider"></div>
                <div class="bank-list">
                    @foreach ($banks as $bankKey => $bank)
                        <div class="bank-item">
                            <strong>{{ $bank['label'] }}</strong>
                            <div class="muted">No. Rek: {{ $bank['account_number'] }}</div>
                            <div class="muted">a.n. {{ $bank['account_name'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label>Transfer ke Bank <span class="required-star">*</span></label>
                <select name="destination_bank" required>
                    <option value="">Pilih bank tujuan</option>
                    @foreach ($banks as $bankKey => $bank)
                        <option value="{{ $bankKey }}" @selected(old('destination_bank', $payment->destination_bank) === $bankKey)>{{ $bank['label'] }} - {{ $bank['account_number'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Bank Pengirim <span class="required-star">*</span></label>
                <input type="text" name="sender_bank_name" value="{{ old('sender_bank_name', $payment->sender_bank_name) }}" placeholder="Contoh: BRI / BCA / Dana" required>
            </div>
            <div>
                <label>Atas Nama Rekening Pengirim <span class="required-star">*</span></label>
                <input type="text" name="sender_account_name" value="{{ old('sender_account_name', $payment->sender_account_name) }}" required>
            </div>
            <div>
                <label>Upload Bukti Pembayaran <span class="required-star">*</span></label>
                <input type="file" name="payment_proof" required>
                @if ($payment->proof_path)
                    <p class="muted" style="margin:0.45rem 0 0;">Bukti saat ini sudah tersimpan. Untuk kirim ulang data, upload bukti terbaru wajib dilakukan.</p>
                @endif
            </div>
            <div class="field-full">
                <label>Catatan Pembayaran</label>
                <textarea name="notes" rows="3" placeholder="Opsional, misalnya tanggal transfer atau keterangan tambahan">{{ old('notes', $customerVisibleNotes) }}</textarea>
            </div>
            <div class="field-full payment-submit-row">
                <p
                    id="paymentSubmitHint"
                    class="submit-hint"
                    data-default-hint="{{ $payment->status === 'rejected' ? 'Lengkapi field wajib untuk mengirim ulang bukti pembayaran sesuai arahan keuangan.' : 'Lengkapi semua field wajib untuk mengaktifkan tombol Kirim Data Pembayaran.' }}"
                    data-success-hint="{{ $payment->status === 'rejected' ? 'Semua field wajib sudah diisi. Anda bisa kirim ulang bukti pembayaran.' : 'Semua field wajib sudah diisi. Anda bisa kirim data pembayaran.' }}"
                >{{ $payment->status === 'rejected' ? 'Lengkapi field wajib untuk mengirim ulang bukti pembayaran sesuai arahan keuangan.' : 'Lengkapi semua field wajib untuk mengaktifkan tombol Kirim Data Pembayaran.' }}</p>
                <button id="paymentSubmitBtn" type="submit" class="btn btn-brand" disabled>{{ $payment->status === 'rejected' ? 'Kirim Ulang Bukti Pembayaran' : 'Kirim Data Pembayaran' }}</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const paymentForm = document.getElementById('paymentForm');
    const submitBtn = document.getElementById('paymentSubmitBtn');
    const submitHint = document.getElementById('paymentSubmitHint');
    const optionInputs = Array.from(document.querySelectorAll('input[name="payment_option"][form="paymentForm"], #paymentForm input[name="payment_option"]'));
    const paymentBreakdown = document.getElementById('paymentBreakdown');
    const breakdownDpPrimary = paymentBreakdown ? paymentBreakdown.querySelector('[data-breakdown="dp-primary"]') : null;
    const breakdownDpSecondary = paymentBreakdown ? paymentBreakdown.querySelector('[data-breakdown="dp-secondary"]') : null;
    const breakdownFullPrimary = paymentBreakdown ? paymentBreakdown.querySelector('[data-breakdown="full-primary"]') : null;

    const requiredFields = Array.from(paymentForm.querySelectorAll('input[required], select[required], textarea[required]'));
    const defaultHintText = submitHint.dataset.defaultHint || 'Lengkapi semua field wajib untuk mengaktifkan tombol kirim.';
    const successHintText = submitHint.dataset.successHint || 'Semua field wajib sudah diisi.';

    const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));

    const refreshBreakdown = () => {
        if (!paymentBreakdown || optionInputs.length === 0) {
            return;
        }

        const selectedOption = optionInputs.find((input) => input.checked)?.value || 'dp';
        const subtotal = Number(paymentBreakdown.dataset.subtotal || 0);
        const half = Number(paymentBreakdown.dataset.dp || 0);

        document.getElementById('breakdownTotal').textContent = `Rp ${formatCurrency(subtotal)}`;
        document.getElementById('breakdownDpNow').textContent = `Rp ${formatCurrency(half)}`;
        document.getElementById('breakdownDpRemain').textContent = `Rp ${formatCurrency(half)}`;
        document.getElementById('breakdownFullNow').textContent = `Rp ${formatCurrency(subtotal)}`;

        if (selectedOption === 'full') {
            breakdownDpPrimary.style.display = 'none';
            breakdownDpSecondary.style.display = 'none';
            breakdownFullPrimary.style.display = 'flex';
        } else {
            breakdownDpPrimary.style.display = 'flex';
            breakdownDpSecondary.style.display = 'flex';
            breakdownFullPrimary.style.display = 'none';
        }
    };

    const refreshSubmitState = () => {
        const isValid = paymentForm.checkValidity();
        submitBtn.disabled = !isValid;

        if (isValid) {
            submitHint.textContent = successHintText;
            submitHint.style.color = '#1c6a47';
        } else {
            submitHint.textContent = defaultHintText;
            submitHint.style.color = '#7a4b13';
        }
    };

    requiredFields.forEach((field) => {
        field.addEventListener('input', refreshSubmitState);
        field.addEventListener('change', refreshSubmitState);
        field.addEventListener('blur', refreshSubmitState);
    });

    optionInputs.forEach((option) => {
        option.addEventListener('change', () => {
            refreshBreakdown();
            refreshSubmitState();
        });
    });

    paymentForm.addEventListener('submit', (event) => {
        if (!paymentForm.checkValidity()) {
            event.preventDefault();
            paymentForm.reportValidity();
            submitHint.textContent = 'Masih ada field wajib yang belum diisi.';
            submitHint.style.color = '#8f2f2f';
        }
    });

    refreshBreakdown();
    refreshSubmitState();
})();
</script>
@endsection
