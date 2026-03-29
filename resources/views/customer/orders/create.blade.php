@extends('layouts.app')

@section('content')
@php
    $preset = $catalogPreset ?? [];
@endphp
<style>
    .order-page-header {
        margin: 0.55rem 0 1.05rem;
        padding: 0.7rem 1.25rem 0.35rem;
    }

    .order-page-header h1 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.66rem, 2.05vw, 1.96rem);
        line-height: 1.1;
        color: #0d2749;
    }

    .order-page-header p {
        margin: 0.5rem 0 0;
        color: #7893ae;
        font-size: clamp(0.86rem, 0.96vw, 0.94rem);
        font-weight: 500;
    }

    .order-form-layout {
        display: grid;
        gap: 1rem;
        padding: 0 1.25rem 1.1rem;
    }

    .order-form-columns {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(340px, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .order-main-card,
    .order-side-card,
    .order-submit-card {
        border: 1px solid #c8d7e7;
        border-radius: 20px;
        background: #f2f4f6;
    }

    .order-main-card {
        padding: 1.12rem;
    }

    .order-side-card {
        padding: 1.06rem;
    }

    .order-submit-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.78rem 0.95rem;
    }

    .section-title {
        margin: 0;
        font-size: 2.02rem;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        line-height: 1.1;
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

    .main-fields-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.72rem 0.9rem;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .main-fields-grid label {
        font-size: 0.8rem;
        color: #0d2749;
        font-weight: 700;
    }

    .main-fields-grid input,
    .main-fields-grid select,
    .main-fields-grid textarea {
        background: #fff;
        border: 1px solid #c3d2e2;
        border-radius: 12px;
        padding: 0.54rem 0.7rem;
        font-size: 0.8rem;
        color: #13283a;
    }

    .main-fields-grid small.muted {
        font-size: 0.76rem;
        color: #6f86a0;
    }

    .design-other-wrap {
        display: none;
    }

    .size-chart-wrap {
        display: grid;
        gap: 0.7rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 0.7rem;
    }

    .size-chart-item {
        border: 1px solid #cedae6;
        border-radius: 12px;
        padding: 0.52rem;
        background: #f7fafc;
    }

    .size-chart-item img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        border: 1px solid #e6eef5;
        display: block;
    }

    .size-chart-item p {
        margin: 0.45rem 0 0;
        font-size: 0.78rem;
        color: var(--muted);
    }

    .calc-hint {
        margin-top: 0.7rem;
        border: 1px dashed #c8d8e6;
        border-radius: 12px;
        padding: 0.65rem 0.75rem;
        background: #f8fbfe;
        font-size: 0.8rem;
        color: #35526a;
    }

    .size-counter {
        margin-top: 0.6rem;
        border: 1px solid #ccdae7;
        border-radius: 12px;
        padding: 0.55rem 0.7rem;
        background: #f4f8fc;
        color: #2b4d67;
        font-size: 0.8rem;
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .size-counter strong {
        color: #12344c;
    }

    .size-counter.status-ok {
        border-color: #b6deca;
        background: #effbf4;
        color: #1c6a47;
    }

    .size-counter.status-over {
        border-color: #f0c3c3;
        background: #fff3f3;
        color: #8f2f2f;
    }

    .submit-hint {
        margin: 0;
        font-size: 0.8rem;
        color: #7a4b13;
    }

    .required-star {
        color: #c22b2b;
        font-weight: 700;
        margin-left: 0.12rem;
    }

    #submitOrderBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        filter: grayscale(0.15);
    }

    .billing-card {
        margin-top: 0.6rem;
        border: 1px solid #c7d6e5;
        border-radius: 12px;
        background: linear-gradient(180deg, #f5f9fd 0%, #ebf2f8 100%);
        padding: 0.85rem;
        box-shadow: 0 4px 12px rgba(26, 59, 84, 0.08);
    }

    .billing-title {
        margin: 0;
        font-size: 0.92rem;
        font-family: 'Playfair Display', serif;
        color: #13334b;
    }

    .billing-grid {
        margin-top: 0.7rem;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .billing-item {
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid #d8e4ef;
        border-radius: 12px;
        padding: 0.45rem 0.55rem;
    }

    .billing-item span {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #5a7489;
        font-weight: 700;
    }

    .billing-item strong {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.86rem;
        color: #173952;
        line-height: 1.2;
    }

    .billing-total {
        margin-top: 0.7rem;
        border-radius: 12px;
        border: 1px solid #f3c69f;
        background: linear-gradient(135deg, #fff3e5, #ffe7d1);
        padding: 0.55rem 0.65rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
    }

    .billing-total span {
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #96531e;
        font-weight: 700;
    }

    .billing-total strong {
        font-family: 'Playfair Display', serif;
        color: #b34910;
        font-size: 0.98rem;
    }

    @media (max-width: 820px) {
        .order-page-header {
            margin-top: 0.4rem;
            padding: 0.45rem 0.92rem 0.25rem;
        }

        .order-page-header h1 {
            font-size: clamp(1.45rem, 6.2vw, 2rem);
        }

        .order-page-header p {
            font-size: 0.9rem;
        }

        .order-form-columns {
            grid-template-columns: 1fr;
        }

        .main-fields-grid {
            grid-template-columns: 1fr;
        }

        .order-form-layout {
            padding: 0 0.92rem 0.92rem;
        }

        .order-submit-card {
            align-items: stretch;
        }

        .order-submit-card .btn {
            width: 100%;
            text-align: center;
        }

        .size-chart-wrap {
            grid-template-columns: 1fr;
        }

        .billing-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="order-page-header">
    <h1>Buat Pesanan Custom</h1>
    <p>Isi detail customisasi kaos Anda di bawah ini (minimal 60 pcs)</p>
</section>

<form id="orderForm" class="order-form-layout" method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="order-form-columns">
        <div class="order-main-card">
            <h2 class="section-heading">Detail Produk</h2>
            <div class="section-divider"></div>
            <div class="main-fields-grid">
                <div>
                    <label>Nama Pemesan <span class="required-star">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required>
                </div>
                <div>
                    <label>Bahan <span class="required-star">*</span></label>
                    <select name="fabric" id="fabricSelect" required>
                        @foreach ($materials as $material)
                            <option value="{{ $material }}" @selected(old('fabric', $preset['fabric'] ?? 'Cotton Combed 30s') === $material)>{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="otherFabricWrap" style="display:none;">
                    <label>Jenis Bahan Lain <span class="required-star">*</span></label>
                    <input type="text" id="otherFabricInput" name="other_fabric" value="{{ old('other_fabric') }}" placeholder="Contoh: Baby Terry">
                </div>
                <div>
                    <label>Total Pcs <span class="required-star">*</span></label>
                    <input type="number" id="totalPcs" name="total_pcs" min="60" value="{{ old('total_pcs', $preset['total_pcs'] ?? 60) }}" required>
                </div>
                <div>
                    <label>Teknik Sablon <span class="required-star">*</span></label>
                    <select name="production_type" id="productionType" required>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(old('production_type', $preset['production_type'] ?? '') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Harga per Pcs (Rp) <span class="required-star">*</span></label>
                    <input type="number" id="unitPrice" name="unit_price" min="85000" max="200000" value="{{ old('unit_price', $preset['unit_price'] ?? 85000) }}" readonly required>
                </div>
                <div>
                    <label>Posisi Desain <span class="required-star">*</span></label>
                    <select name="design_position" id="designPosition" required>
                        @foreach ($designPositions as $position)
                            <option value="{{ $position }}" @selected(old('design_position', $preset['design_position'] ?? 'Dada Kiri + Punggung') === $position)>{{ $position }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="designPositionOtherWrap" class="design-other-wrap field-full">
                    <label>Posisi Desain Lainnya <span class="required-star">*</span></label>
                    <input type="text" id="designPositionOther" name="design_position_other" value="{{ old('design_position_other') }}" placeholder="Contoh: Lengan kanan + punggung bawah">
                </div>
                <input type="hidden" id="productionQty" name="production_qty" value="{{ old('production_qty', $preset['production_qty'] ?? 60) }}">
                <div>
                    <label>Model <span class="required-star">*</span></label>
                    <select name="product_model" required>
                        @foreach ($models as $model)
                            <option value="{{ $model }}" @selected(old('product_model', $preset['product_model'] ?? '') === $model)>{{ $model }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Ukuran Lengan <span class="required-star">*</span></label>
                    <select name="sleeve_type" id="sleeveType" required>
                        @foreach ($sleeves as $sleeve)
                            <option value="{{ $sleeve }}" @selected(old('sleeve_type', 'Lengan Pendek') === $sleeve)>{{ $sleeve }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Warna Dominan <span class="required-star">*</span></label>
                    <input type="text" name="dominant_color" value="{{ old('dominant_color', $preset['dominant_color'] ?? 'Hitam') }}" required>
                </div>
                <div>
                    <label>Estimasi Tanggal Selesai <span class="required-star">*</span></label>
                    <input type="date" name="estimated_finish_date" min="{{ now()->addDays(10)->toDateString() }}" value="{{ old('estimated_finish_date') }}" required>
                    <small class="muted">Pilih tanggal estimasi di atas. Estimasi produksi normal 10-21 hari kerja tergantung jumlah dan kompleksitas desain. Tim kami akan mengkonfirmasi kelayakan tanggal.</small>
                </div>
                <input type="hidden" name="payment_type" value="{{ old('payment_type', 'dp') }}">
                <div class="field-full">
                    <label>Upload Desain (maksimal 2 file: jpg/png/pdf/svg)</label>
                    <div class="grid grid-2" style="gap:0.65rem;">
                        <div>
                            <label style="font-size:0.75rem; color:#6f86a0; font-weight:600; margin-bottom:0.3rem;">File Desain 1</label>
                            <input type="file" name="design_front_file">
                        </div>
                        <div>
                            <label style="font-size:0.75rem; color:#6f86a0; font-weight:600; margin-bottom:0.3rem;">File Desain 2</label>
                            <input type="file" name="design_back_file">
                        </div>
                    </div>
                </div>
                <div class="field-full">
                    <label>Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Tambahkan catatan kebutuhan desain atau produksi (opsional)">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="order-side-card">
            <h2 class="section-heading">Distribusi Ukuran</h2>
            <div class="section-divider"></div>
            <p class="muted">Jumlah total ukuran harus sama dengan Total Pcs.</p>
            <p class="muted" style="margin-top:0.35rem;">Tambahan biaya Rp5.000/pcs berlaku untuk ukuran XXL dan XXXL (lengan pendek maupun lengan panjang).</p>

            <div class="size-chart-wrap">
                <div class="size-chart-item">
                    <img src="{{ asset('images/katalog/sizependek.png') }}" alt="Size chart lengan pendek" onerror="this.style.display='none'">
                    <p>Size chart lengan pendek</p>
                </div>
                <div class="size-chart-item">
                    <img src="{{ asset('images/katalog/sizepanjang.png') }}" alt="Size chart lengan panjang" onerror="this.style.display='none'">
                    <p>Size chart lengan panjang</p>
                </div>
            </div>

            <div class="grid grid-2">
                @foreach ($sizes as $size)
                    <div>
                        <label>{{ $size }} <span class="required-star">*</span></label>
                        <input type="number" class="size-input" data-size="{{ $size }}" name="sizes[{{ $size }}]" min="0" value="{{ old('sizes.'.$size, 0) }}">
                    </div>
                @endforeach
            </div>

            <div id="sizeCounterBox" class="size-counter">
                <div>Terpilih: <strong id="selectedSizeCount">0</strong> pcs</div>
                <div>Total Pesanan: <strong id="targetSizeCount">0</strong> pcs</div>
                <div>Sisa: <strong id="remainingSizeCount">0</strong> pcs</div>
            </div>

            <div class="calc-hint">
                <div>Estimasi tambahan XXL/XXXL: <strong id="sizeSurchargeText">Rp0</strong></div>
            </div>

            <div class="billing-card">
                <p class="billing-title">Rincian Total Tagihan</p>
                <div class="billing-grid">
                    <div class="billing-item">
                        <span>Bahan</span>
                        <strong id="summaryFabric">-</strong>
                    </div>
                    <div class="billing-item">
                        <span>Jumlah Pesanan</span>
                        <strong id="summaryTotalPcs">0 pcs</strong>
                    </div>
                    <div class="billing-item">
                        <span>Model</span>
                        <strong id="summaryModel">-</strong>
                    </div>
                    <div class="billing-item">
                        <span>Jenis</span>
                        <strong id="summaryProductionType">-</strong>
                    </div>
                    <div class="billing-item" style="grid-column: 1 / -1;">
                        <span>Tambahan XXL/XXXL</span>
                        <strong id="summarySurcharge">Rp0</strong>
                    </div>
                </div>
                <div class="billing-total">
                    <span>Total</span>
                    <strong id="summaryGrandTotal">Rp0</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="order-submit-card">
        <p id="submitHint" class="submit-hint">Sistem akan otomatis menghitung DP dan sisa pelunasan setelah submit.</p>
        <button id="submitOrderBtn" class="btn btn-brand" type="submit">Lanjut ke Pembayaran</button>
    </div>
</form>

<script>
(() => {
    const materialPrices = {
        'Drill': 115000,
        'Taipan': 120000,
        'Tropical': 110000,
        'Oxford': 125000,
        'Twill': 118000,
        'Ribstop': 130000,
        'Lacoste Pique': 140000,
        'Cotton Combed 30s': 85000,
        'Cotton Combed 20s': 95000,
        'Drifit': 105000,
        'Lainnya': 100000,
    };

    const techniqueSurcharge = {
        'Sablon Manual': 5000,
        'DTF (Direct to Film)': 6000,
        'Printing': 7000,
        'Bordiran': 6000,
    };

    const fabricSelect = document.getElementById('fabricSelect');
    const otherFabricInput = document.getElementById('otherFabricInput');
    const otherFabricWrap = document.getElementById('otherFabricWrap');
    const unitPriceInput = document.getElementById('unitPrice');
    const productionTypeSelect = document.getElementById('productionType');
    const designPositionSelect = document.getElementById('designPosition');
    const designPositionOtherWrap = document.getElementById('designPositionOtherWrap');
    const designPositionOtherInput = document.getElementById('designPositionOther');
    const productionQtyInput = document.getElementById('productionQty');
    const modelSelect = document.querySelector('select[name="product_model"]');
    const sleeveTypeSelect = document.getElementById('sleeveType');
    const totalPcsInput = document.getElementById('totalPcs');
    const orderForm = document.getElementById('orderForm');
    const submitOrderBtn = document.getElementById('submitOrderBtn');
    const submitHint = document.getElementById('submitHint');
    const sizeInputs = Array.from(document.querySelectorAll('.size-input'));
    const sizeCounterBox = document.getElementById('sizeCounterBox');
    const selectedSizeCount = document.getElementById('selectedSizeCount');
    const targetSizeCount = document.getElementById('targetSizeCount');
    const remainingSizeCount = document.getElementById('remainingSizeCount');
    const surchargeText = document.getElementById('sizeSurchargeText');
    const summaryFabric = document.getElementById('summaryFabric');
    const summaryTotalPcs = document.getElementById('summaryTotalPcs');
    const summaryModel = document.getElementById('summaryModel');
    const summaryProductionType = document.getElementById('summaryProductionType');
    const summarySurcharge = document.getElementById('summarySurcharge');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');

    const formatRupiah = (value) => {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(value || 0);
    };

    const updateMaterialAndPrice = () => {
        const fabric = fabricSelect.value;
        const basePrice = materialPrices[fabric] ?? 85000;
        const techniqueExtra = techniqueSurcharge[productionTypeSelect.value] ?? 0;
        const finalPrice = basePrice + techniqueExtra;
        unitPriceInput.value = Math.min(200000, Math.max(85000, finalPrice));
        otherFabricWrap.style.display = fabric === 'Lainnya' ? 'block' : 'none';
        otherFabricInput.required = fabric === 'Lainnya';
    };

    const updateDesignPositionField = () => {
        const isOther = designPositionSelect.value === 'Lainnya';
        designPositionOtherWrap.style.display = isOther ? 'block' : 'none';
        designPositionOtherInput.required = isOther;
    };

    const updateSubmitState = (isBalanced) => {
        const formReady = orderForm.checkValidity();
        submitOrderBtn.disabled = !(isBalanced && formReady);

        if (!isBalanced) {
            submitHint.textContent = 'Buat Pesanan aktif jika jumlah ukuran tepat sama dengan Total Pesanan.';
            submitHint.style.color = '#7a4b13';
            return;
        }

        if (!formReady) {
            submitHint.textContent = 'Lengkapi semua field wajib.';
            submitHint.style.color = '#7a4b13';
            return;
        }

        submitHint.textContent = 'Semua data sudah lengkap.';
        submitHint.style.color = '#1c6a47';
    };

    const syncProductionQty = () => {
        const total = getTargetTotal();
        productionQtyInput.value = String(total > 0 ? total : 1);
    };

    const getTargetTotal = () => {
        const raw = Number(totalPcsInput.value || 0);
        return Number.isFinite(raw) && raw > 0 ? raw : 0;
    };

    const getSelectedSizesTotal = () => {
        return sizeInputs.reduce((sum, input) => sum + Number(input.value || 0), 0);
    };

    const applySizeLimits = (changedInput = null) => {
        const target = getTargetTotal();
        let selected = getSelectedSizesTotal();

        if (changedInput && selected > target) {
            const overflow = selected - target;
            const current = Number(changedInput.value || 0);
            changedInput.value = Math.max(0, current - overflow);
            selected = getSelectedSizesTotal();
        }

        sizeInputs.forEach((input) => {
            const self = Number(input.value || 0);
            const others = selected - self;
            const allowedMax = Math.max(0, target - others);
            input.max = String(allowedMax);
            input.disabled = target > 0 && selected >= target && self === 0;
        });

        const remaining = Math.max(0, target - selected);
        selectedSizeCount.textContent = String(selected);
        targetSizeCount.textContent = String(target);
        remainingSizeCount.textContent = String(remaining);

        sizeCounterBox.classList.remove('status-ok', 'status-over');
        if (target > 0 && selected === target) {
            sizeCounterBox.classList.add('status-ok');
        } else if (selected > target) {
            sizeCounterBox.classList.add('status-over');
        }

        const isBalanced = target > 0 && selected === target;
        updateSubmitState(isBalanced);
    };

    const updateSurchargeAndEstimate = () => {
        const xxlQty = Number((sizeInputs.find((input) => input.dataset.size === 'XXL') || {}).value || 0);
        const xxxlQty = Number((sizeInputs.find((input) => input.dataset.size === 'XXXL') || {}).value || 0);
        const plusQty = xxlQty + xxxlQty;
        const surcharge = plusQty * 5000;

        const unitPrice = Number(unitPriceInput.value || 0);
        const totalPcs = Number(totalPcsInput.value || 0);
        const estimatedSubtotal = (unitPrice * totalPcs) + surcharge;

        surchargeText.textContent = formatRupiah(surcharge);

        const resolvedFabric = fabricSelect.value === 'Lainnya'
            ? (otherFabricInput.value?.trim() || 'Lainnya')
            : fabricSelect.value;

        summaryFabric.textContent = resolvedFabric;
        summaryTotalPcs.textContent = `${totalPcs || 0} pcs`;
        summaryModel.textContent = modelSelect.value || '-';
        summaryProductionType.textContent = productionTypeSelect.value || '-';
        summarySurcharge.textContent = formatRupiah(surcharge);
        summaryGrandTotal.textContent = formatRupiah(estimatedSubtotal);
    };

    [fabricSelect, otherFabricInput, productionTypeSelect, designPositionSelect, designPositionOtherInput, modelSelect, sleeveTypeSelect, totalPcsInput, ...sizeInputs, ...Array.from(orderForm.querySelectorAll('input, select, textarea'))].forEach((el) => {
        if (!el) {
            return;
        }

        el.addEventListener('input', () => {
            if (el.classList.contains('size-input')) {
                applySizeLimits(el);
            } else {
                applySizeLimits();
            }
            updateMaterialAndPrice();
            updateDesignPositionField();
            syncProductionQty();
            updateSurchargeAndEstimate();
        });

        el.addEventListener('change', () => {
            if (el.classList.contains('size-input')) {
                applySizeLimits(el);
            } else {
                applySizeLimits();
            }
            updateMaterialAndPrice();
            updateDesignPositionField();
            syncProductionQty();
            updateSurchargeAndEstimate();
        });
    });

    applySizeLimits();
    updateMaterialAndPrice();
    updateDesignPositionField();
    syncProductionQty();
    updateSurchargeAndEstimate();

    orderForm.addEventListener('submit', (event) => {
        const selected = getSelectedSizesTotal();
        const target = getTargetTotal();

        if (!(target > 0 && selected === target)) {
            event.preventDefault();
            submitHint.textContent = 'Jumlah ukuran belum sesuai. Samakan dulu dengan Total Pesanan.';
            submitHint.style.color = '#8f2f2f';
            return;
        }

        if (!orderForm.checkValidity()) {
            event.preventDefault();
            orderForm.reportValidity();
            submitHint.textContent = 'Lengkapi semua field wajib (kecuali Catatan) sebelum membuat pesanan.';
            submitHint.style.color = '#8f2f2f';
        }
    });
})();
</script>
@endsection
