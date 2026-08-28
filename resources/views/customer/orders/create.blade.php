@extends('layouts.app')

@section('header_title', 'Buat Pesanan Custom')

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

    .main-fields-grid input[type="file"] {
        padding: 0.35rem 0.5rem;
    }

    .main-fields-grid input[type="file"]::file-selector-button {
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
        cursor: pointer;
        margin-right: 0.5rem;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    }

    .main-fields-grid input[type="file"]::file-selector-button:hover {
        background-color: #dee2e6;
        border-color: #c4c8cb;
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

    /* Material Picker Styles */
    .material-picker-intro {
        margin: 0 0 0.85rem;
        font-size: 0.82rem;
        color: #7893ae;
    }

    .material-choice-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.7rem;
        margin-bottom: 0.9rem;
    }

    .material-choice {
        border: 1px solid #c9d8e6;
        border-radius: 14px;
        background: #ffffff;
        padding: 0.85rem 0.8rem;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow: 0 1px 2px rgba(15, 43, 61, 0.03);
    }

    .material-choice:hover {
        transform: translateY(-1px);
        border-color: #b9cde2;
        box-shadow: 0 4px 10px rgba(15, 43, 61, 0.06);
    }

    .material-choice.is-active {
        border-color: #c8a949;
        box-shadow: 0 0 0 3px rgba(200, 169, 73, 0.16);
        background: #fffaf0;
    }

    .material-choice-title {
        display: block;
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #0d2749;
    }

    .material-choice-subtitle {
        display: block;
        margin-top: 0.28rem;
        font-size: 0.76rem;
        color: #6f86a0;
        line-height: 1.3;
    }

    .material-detail-card {
        border: 1px solid #d9e4ef;
        border-radius: 16px;
        background: linear-gradient(180deg, #fbfdff 0%, #f4f8fc 100%);
        padding: 0.9rem 0.95rem;
        margin-bottom: 0.85rem;
    }

    .material-detail-head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.7rem;
    }

    .material-detail-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 auto;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #e1e8f0;
        box-shadow: 0 1px 3px rgba(15, 43, 61, 0.04);
        font-size: 1.5rem;
    }

    .material-detail-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
        display: none;
    }

    .material-detail-icon img.is-loaded {
        display: block;
    }

    .material-detail-title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: #0d2749;
    }

    .material-detail-description {
        margin: 0.22rem 0 0;
        font-size: 0.82rem;
        line-height: 1.45;
        color: #6f86a0;
    }

    .material-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 0.85rem;
    }

    .material-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #d7e2ec;
        color: #35526a;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .material-meta-title {
        margin: 0.18rem 0 0.45rem;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 800;
        color: #6f86a0;
    }

    .material-meta-group {
        margin-bottom: 0.6rem;
    }

    .material-meta-list {
        margin: 0;
        padding-left: 1rem;
        font-size: 0.78rem;
        color: #4a6075;
        line-height: 1.5;
    }

    .color-picker-section {
        margin-top: 0.9rem;
    }

    .color-picker-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .color-option {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .color-option:hover {
        transform: scale(1.1);
    }

    .color-option.is-selected {
        border-color: #c8a949;
        box-shadow: 0 0 0 3px rgba(200, 169, 73, 0.25);
    }

    .color-option[data-name="Putih"] {
        border: 1px solid #d0dbe5;
    }

    .color-option[data-name="Putih"].is-selected {
        border-color: #c8a949;
    }

    .selected-color-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.6rem;
        padding: 0.35rem 0.65rem;
        background: #ffffff;
        border: 1px solid #d7e2ec;
        border-radius: 999px;
        font-size: 0.8rem;
        color: #0d2749;
        font-weight: 600;
    }

    .selected-color-chip .color-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .material-image-modal {
        position: fixed;
        inset: 0;
        background: rgba(9, 22, 39, 0.85);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 1.2rem;
    }

    .material-image-modal.is-open {
        display: flex;
    }

    .material-image-dialog {
        width: min(420px, 90vw);
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #d7e3ee;
        box-shadow: 0 14px 34px rgba(7, 21, 38, 0.32);
        overflow: hidden;
    }

    .material-image-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0.85rem;
        border-bottom: 1px solid #e2ebf3;
    }

    .material-image-head h4 {
        margin: 0;
        color: #14304f;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .material-image-close {
        background: none;
        border: none;
        font-size: 1.4rem;
        color: #6f86a0;
        cursor: pointer;
        padding: 0.2rem;
        line-height: 1;
    }

    .material-image-body {
        padding: 0.75rem;
    }

    .material-image-body img {
        width: 100%;
        max-height: 480px;
        object-fit: contain;
        height: auto;
        border-radius: 10px;
        background: transparent;
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
    <input type="hidden" id="fabricInput" name="fabric" value="{{ old('fabric', $preset['fabric'] ?? '') }}">
    <input type="hidden" id="dominantColorInput" name="dominant_color" value="{{ old('dominant_color', $preset['dominant_color'] ?? '') }}">
    <input type="hidden" id="secondaryColorInput" name="secondary_color" value="{{ old('secondary_color', $preset['secondary_color'] ?? '') }}">
    <input type="hidden" name="payment_type" value="{{ old('payment_type', 'dp') }}">
    <input type="hidden" id="productionQty" name="production_qty" value="{{ old('production_qty', $preset['production_qty'] ?? 60) }}">
    <div class="order-form-columns">
        <div class="order-main-card">
            <h2 class="section-heading">Detail Produk</h2>
            <div class="section-divider"></div>
            <div class="main-fields-grid">
                <div>
                    <label>Nama Pemesan <span class="required-star">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Masukkan nama pemesan..." required>
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
                <div style="grid-row: span 2;">
                    <label>Model <span class="required-star">*</span></label>
                    <select name="product_model" required style="margin-bottom: 0.6rem;">
                        @foreach ($models as $model)
                            <option value="{{ $model }}" @selected(old('product_model', $preset['product_model'] ?? '') === $model)>{{ $model }}</option>
                        @endforeach
                    </select>
                    <!-- Contoh Model Kecil -->
                    <div style="display: flex; gap: 0.5rem; padding-bottom: 0.3rem;">
                        <div style="text-align: center; flex: 1; cursor: pointer;" onclick="openMaterialImageModal('{{ asset('images/katalog/modelOblong.png') }}', 'Model Kaos Oblong')">
                            <img src="{{ asset('images/katalog/modelOblong.png') }}" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; object-position: center top; border-radius: 6px; background: #e2e8f0; border: 1px solid #c3d2e2;" alt="Kaos Oblong">
                            <span style="font-size: 0.6rem; color: #6f86a0; display: block; line-height: 1.1; margin-top: 0.2rem;">Oblong</span>
                        </div>
                        <div style="text-align: center; flex: 1; cursor: pointer;" onclick="openMaterialImageModal('{{ asset('images/katalog/modelRaglan.png') }}', 'Model Kaos Raglan')">
                            <img src="{{ asset('images/katalog/modelRaglan.png') }}" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; object-position: center top; border-radius: 6px; background: #e2e8f0; border: 1px solid #c3d2e2;" alt="Kaos Raglan">
                            <span style="font-size: 0.6rem; color: #6f86a0; display: block; line-height: 1.1; margin-top: 0.2rem;">Raglan</span>
                        </div>
                        <div style="text-align: center; flex: 1; cursor: pointer;" onclick="openMaterialImageModal('{{ asset('images/katalog/modelPolo.png') }}', 'Model Polo Shirt')">
                            <img src="{{ asset('images/katalog/modelPolo.png') }}" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; object-position: center top; border-radius: 6px; background: #e2e8f0; border: 1px solid #c3d2e2;" alt="Polo Shirt">
                            <span style="font-size: 0.6rem; color: #6f86a0; display: block; line-height: 1.1; margin-top: 0.2rem;">Polo</span>
                        </div>
                        <div style="text-align: center; flex: 1; cursor: pointer;" onclick="openMaterialImageModal('{{ asset('images/katalog/modelVneck.png') }}', 'Model Kaos V-Neck')">
                            <img src="{{ asset('images/katalog/modelVneck.png') }}" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; object-position: center top; border-radius: 6px; background: #e2e8f0; border: 1px solid #c3d2e2;" alt="V-Neck">
                            <span style="font-size: 0.6rem; color: #6f86a0; display: block; line-height: 1.1; margin-top: 0.2rem;">V-Neck</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label>Ukuran Lengan <span class="required-star">*</span></label>
                    <select name="sleeve_type" id="sleeveType" required>
                        @foreach ($sleeves as $sleeve)
                            <option value="{{ $sleeve }}" @selected(old('sleeve_type', 'Lengan Pendek') === $sleeve)>{{ $sleeve }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Pilih Bahan & Warna Section -->
            <div style="margin-top: 1.2rem;">
                <h2 class="section-heading">Pilih Bahan & Warna</h2>
                <div class="section-divider"></div>
                
                <p class="material-picker-intro">Pilih jenis bahan untuk melihat detail dan warna yang tersedia.</p>
                
                <div class="material-choice-grid" id="materialChoiceGrid">
                    @foreach ($materialCatalog as $materialKey => $materialData)
                        <button type="button" class="material-choice" data-material="{{ $materialKey }}">
                            <span class="material-choice-title">{{ $materialKey }}</span>
                            <span class="material-choice-subtitle">{{ $materialData['title'] ?? '' }}</span>
                        </button>
                    @endforeach
                </div>

                <div id="materialDetailCard" class="material-detail-card" style="display: none;">
                    <div class="material-detail-head">
                        <div class="material-detail-icon" onclick="if(materialDetailImage.src && materialDetailImage.src !== window.location.href) openMaterialImageModal(materialDetailImage.src, materialDetailTitle.textContent)" style="cursor: zoom-in;">
                            <img id="materialDetailImage" src="" alt="Material image">
                            <span id="materialDetailIcon">🧵</span>
                        </div>
                        <div>
                            <h3 class="material-detail-title" id="materialDetailTitle"></h3>
                            <p class="material-detail-description" id="materialDetailDescription"></p>
                        </div>
                    </div>
                    <div class="material-tags" id="materialTags"></div>
                    <div class="material-meta-group" id="suitableForGroup" style="display: none;">
                        <p class="material-meta-title">Cocok Untuk</p>
                        <ul class="material-meta-list" id="materialSuitableFor"></ul>
                    </div>
                    <div class="material-meta-group" id="designAppGroup" style="display: none;">
                        <p class="material-meta-title">Aplikasi Desain</p>
                        <ul class="material-meta-list" id="materialDesignApp"></ul>
                    </div>
                    
                    <div class="color-picker-section">
                        <p class="material-meta-title" id="dominantColorTitle">Pilih Warna</p>
                        <div class="color-picker-grid" id="colorPickerGrid"></div>
                        <div id="selectedColorChip" class="selected-color-chip" style="display: none;">
                            <span class="color-dot"></span>
                            <span id="selectedColorName"></span>
                        </div>
                    </div>
                    
                    <div class="color-picker-section" id="secondaryColorSection" style="display: none; margin-top: 1rem;">
                        <p class="material-meta-title">Pilih Warna Lengan</p>
                        <div class="color-picker-grid" id="secondaryColorPickerGrid"></div>
                        <div id="selectedSecondaryColorChip" class="selected-color-chip" style="display: none;">
                            <span class="color-dot"></span>
                            <span id="selectedSecondaryColorName"></span>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Estimasi, Upload, Catatan after Pilih Bahan & Warna -->
            <div style="margin-top: 1.2rem;">
                <div class="main-fields-grid">
                    <div class="field-full">
                        <label>Estimasi Tanggal Selesai <span class="required-star">*</span></label>
                        <input type="date" id="estimatedFinishDate" name="estimated_finish_date" min="{{ now()->addDays(10)->toDateString() }}" value="{{ old('estimated_finish_date') }}" required>
                        <small class="muted">Minimal 10 hari dari tanggal pemesanan. Estimasi produksi normal 10–21 hari kerja tergantung jumlah dan kompleksitas desain. Tim kami akan mengkonfirmasi kelayakan tanggal.</small>
                    </div>
                    <div class="field-full">
                        <label>Upload Desain (maksimal 2 file: jpg/png/pdf/svg)</label>
                        <div class="grid grid-2" style="gap:0.65rem;">
                            <div>
                                <label style="font-size:0.75rem; color:#6f86a0; font-weight:600; margin-bottom:0.3rem;">File Desain Depan</label>
                                <input type="file" name="design_front_file">
                            </div>
                            <div>
                                <label style="font-size:0.75rem; color:#6f86a0; font-weight:600; margin-bottom:0.3rem;">File Desain Belakang</label>
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
        <button id="submitOrderBtn" class="btn btn-brand" type="submit">Submit</button>
    </div>
</form>

<!-- Material Image Modal -->
<div id="materialImageModal" class="material-image-modal">
    <div class="material-image-dialog">
        <div class="material-image-head">
            <h4 id="materialImageTitle">Detail Bahan</h4>
            <button type="button" class="material-image-close" onclick="closeMaterialImageModal()">&times;</button>
        </div>
        <div class="material-image-body">
            <img id="materialImageSrc" src="" alt="Material detail">
        </div>
    </div>
</div>

<script>
(() => {
    const materialPrices = {
        'Cotton Combed 30s': 85000,
        'Cotton Combed 24s': 95000,
        'Cotton Combed 20s': 95000,
        'Cotton Bamboo': 105000,
        'Lacoste Cotton Pique': 140000,
        'Lacoste CVC': 130000,
        'Drifit': 105000,
        'Lainnya': 100000,
    };

    const techniqueSurcharge = {
        'Sablon Manual': 5000,
        'DTF (Direct to Film)': 6000,
        'Printing': 7000,
        'Bordiran': 6000,
    };

    const materialCatalog = @json($materialCatalog ?? []);
    const fabricInput = document.getElementById('fabricInput');
    const dominantColorInput = document.getElementById('dominantColorInput');

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

    // Material picker elements
    const materialChoiceGrid = document.getElementById('materialChoiceGrid');
    const materialDetailCard = document.getElementById('materialDetailCard');
    const materialDetailTitle = document.getElementById('materialDetailTitle');
    const materialDetailDescription = document.getElementById('materialDetailDescription');
    const materialDetailImage = document.getElementById('materialDetailImage');
    const materialDetailIcon = document.getElementById('materialDetailIcon');
    const materialTags = document.getElementById('materialTags');
    const materialSuitableFor = document.getElementById('materialSuitableFor');
    const materialDesignApp = document.getElementById('materialDesignApp');
    const colorPickerGrid = document.getElementById('colorPickerGrid');
    const selectedColorChip = document.getElementById('selectedColorChip');
    const dominantColorTitle = document.getElementById('dominantColorTitle');
    
    const secondaryColorSection = document.getElementById('secondaryColorSection');
    const secondaryColorPickerGrid = document.getElementById('secondaryColorPickerGrid');
    const selectedSecondaryColorChip = document.getElementById('selectedSecondaryColorChip');
    const secondaryColorInput = document.getElementById('secondaryColorInput');

    let selectedMaterial = null;
    let selectedColor = null;
    let selectedSecondaryColor = null;

    const formatRupiah = (value) => {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(value || 0);
    };

    const updateMaterialAndPrice = () => {
        const fabric = fabricInput.value || 'Cotton Combed 30s';
        const basePrice = materialPrices[fabric] ?? 85000;
        const techniqueExtra = techniqueSurcharge[productionTypeSelect.value] ?? 0;
        const finalPrice = basePrice + techniqueExtra;
        unitPriceInput.value = Math.min(200000, Math.max(85000, finalPrice));
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

        submitHint.textContent = 'Submit untuk menunggu pesanan diverifikasi Admin max 3x24 jam. Data tidak dapat dilakukan perubahan setelah proses verifikasi';
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

        const resolvedFabric = fabricInput.value || '-';

        summaryFabric.textContent = resolvedFabric;
        summaryTotalPcs.textContent = `${totalPcs || 0} pcs`;
        summaryModel.textContent = modelSelect.value || '-';
        summaryProductionType.textContent = productionTypeSelect.value || '-';
        summarySurcharge.textContent = formatRupiah(surcharge);
        summaryGrandTotal.textContent = formatRupiah(estimatedSubtotal);
    };

    // Material picker functions
    const selectMaterial = (materialKey) => {
        selectedMaterial = materialKey;
        fabricInput.value = materialKey;
        
        // Update UI
        document.querySelectorAll('.material-choice').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.material === materialKey);
        });

        const data = materialCatalog[materialKey];
        if (!data) return;

        materialDetailCard.style.display = 'block';
        materialDetailTitle.textContent = materialKey;
        materialDetailDescription.textContent = data.description || '';

        // Handle image
        if (data.image) {
            materialDetailImage.src = '{{ asset("/") }}' + data.image;
            materialDetailImage.classList.add('is-loaded');
            materialDetailImage.style.display = 'block';
            materialDetailIcon.style.display = 'none';
        } else {
            materialDetailImage.style.display = 'none';
            materialDetailImage.classList.remove('is-loaded');
            materialDetailIcon.style.display = 'flex';
        }

        // Tags
        materialTags.innerHTML = '';
        if (data.tags && data.tags.length) {
            data.tags.forEach(tag => {
                const span = document.createElement('span');
                span.className = 'material-tag';
                span.textContent = tag;
                materialTags.appendChild(span);
            });
        }

        // Suitable for
        materialSuitableFor.innerHTML = '';
        const suitableForGroup = document.getElementById('suitableForGroup');
        if (data.suitable_for && data.suitable_for.length) {
            suitableForGroup.style.display = 'block';
            data.suitable_for.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                materialSuitableFor.appendChild(li);
            });
        } else {
            suitableForGroup.style.display = 'none';
        }

        // Design application
        materialDesignApp.innerHTML = '';
        const designAppGroup = document.getElementById('designAppGroup');
        if (data.design_application && data.design_application.length) {
            designAppGroup.style.display = 'block';
            data.design_application.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                materialDesignApp.appendChild(li);
            });
        } else {
            designAppGroup.style.display = 'none';
        }

        // Colors
        colorPickerGrid.innerHTML = '';
        secondaryColorPickerGrid.innerHTML = '';
        selectedColor = null;
        selectedSecondaryColor = null;
        dominantColorInput.value = '';
        secondaryColorInput.value = '';
        selectedColorChip.style.display = 'none';
        selectedSecondaryColorChip.style.display = 'none';

        if (data.colors && data.colors.length) {
            data.colors.forEach(color => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'color-option';
                btn.dataset.name = color.name;
                btn.dataset.hex = color.hex;
                btn.style.backgroundColor = color.hex;
                btn.title = color.name;
                btn.addEventListener('click', () => selectColor(color.name, color.hex));
                colorPickerGrid.appendChild(btn);
                
                const btnSec = document.createElement('button');
                btnSec.type = 'button';
                btnSec.className = 'color-option secondary-color-option';
                btnSec.dataset.name = color.name;
                btnSec.dataset.hex = color.hex;
                btnSec.style.backgroundColor = color.hex;
                btnSec.title = color.name;
                btnSec.addEventListener('click', () => selectSecondaryColor(color.name, color.hex));
                secondaryColorPickerGrid.appendChild(btnSec);
            });
        }

        // Show/hide other fabric input


        updateMaterialAndPrice();
        updateSurchargeAndEstimate();
    };

    const selectColor = (colorName, colorHex) => {
        selectedColor = colorName;
        dominantColorInput.value = colorName;

        colorPickerGrid.querySelectorAll('.color-option').forEach(btn => {
            btn.classList.toggle('is-selected', btn.dataset.name === colorName);
        });

        const chip = selectedColorChip;
        chip.style.display = 'inline-flex';
        chip.querySelector('.color-dot').style.backgroundColor = colorHex;
        chip.querySelector('#selectedColorName').textContent = colorName;
    };
    
    const selectSecondaryColor = (colorName, colorHex) => {
        selectedSecondaryColor = colorName;
        secondaryColorInput.value = colorName;

        secondaryColorPickerGrid.querySelectorAll('.secondary-color-option').forEach(btn => {
            btn.classList.toggle('is-selected', btn.dataset.name === colorName);
        });

        const chip = selectedSecondaryColorChip;
        chip.style.display = 'inline-flex';
        chip.querySelector('.color-dot').style.backgroundColor = colorHex;
        chip.querySelector('#selectedSecondaryColorName').textContent = colorName;
    };
    
    const updateRaglanView = () => {
        if (modelSelect.value === 'Raglan') {
            dominantColorTitle.textContent = 'Pilih Warna Body';
            secondaryColorSection.style.display = 'block';
        } else {
            dominantColorTitle.textContent = 'Pilih Warna';
            secondaryColorSection.style.display = 'none';
            secondaryColorInput.value = '';
            selectedSecondaryColor = null;
            selectedSecondaryColorChip.style.display = 'none';
            secondaryColorPickerGrid.querySelectorAll('.secondary-color-option').forEach(btn => {
                btn.classList.remove('is-selected');
            });
        }
    };
    
    modelSelect.addEventListener('change', updateRaglanView);

    // Initialize material picker
    const initMaterialPicker = () => {
        const buttons = materialChoiceGrid.querySelectorAll('.material-choice');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => selectMaterial(btn.dataset.material));
        });

        // Check for pre-selected values
        if (fabricInput.value && materialCatalog[fabricInput.value]) {
            selectMaterial(fabricInput.value);
            if (dominantColorInput.value) {
                const data = materialCatalog[fabricInput.value];
                if (data && data.colors) {
                    const color = data.colors.find(c => c.name === dominantColorInput.value);
                    if (color) {
                        selectColor(color.name, color.hex);
                    }
                }
            }
            if (secondaryColorInput.value) {
                const data = materialCatalog[fabricInput.value];
                if (data && data.colors) {
                    const color = data.colors.find(c => c.name === secondaryColorInput.value);
                    if (color) {
                        selectSecondaryColor(color.name, color.hex);
                    }
                }
            }
        }
    };

    // Event listeners
    [productionTypeSelect, designPositionSelect, designPositionOtherInput, modelSelect, sleeveTypeSelect, totalPcsInput, ...sizeInputs, ...Array.from(orderForm.querySelectorAll('input, select, textarea'))].forEach((el) => {
        if (!el) return;

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

    // Initialize
    initMaterialPicker();
    updateRaglanView();
    applySizeLimits();
    updateMaterialAndPrice();
    updateDesignPositionField();
    syncProductionQty();
    updateSurchargeAndEstimate();

    // Enforce minimum date (10 days from today) on estimated finish date
    const estimatedDateInput = document.getElementById('estimatedFinishDate');
    if (estimatedDateInput) {
        const minDate = "{{ now()->addDays(10)->toDateString() }}";
        estimatedDateInput.setAttribute('min', minDate);

        estimatedDateInput.addEventListener('change', () => {
            if (estimatedDateInput.value && estimatedDateInput.value < minDate) {
                estimatedDateInput.value = '';
                alert('Tanggal estimasi minimal 10 hari dari hari ini (' + minDate.split('-').reverse().join('/') + ').');
            }
        });
    }

    orderForm.addEventListener('submit', (event) => {
        const selected = getSelectedSizesTotal();
        const target = getTargetTotal();

        if (!(target > 0 && selected === target)) {
            event.preventDefault();
            submitHint.textContent = 'Jumlah ukuran belum sesuai. Samakan dulu dengan Total Pesanan.';
            submitHint.style.color = '#8f2f2f';
            return;
        }

        if (!fabricInput.value) {
            event.preventDefault();
            submitHint.textContent = 'Pilih bahan terlebih dahulu.';
            submitHint.style.color = '#8f2f2f';
            return;
        }

        if (!dominantColorInput.value) {
            event.preventDefault();
            submitHint.textContent = 'Pilih warna terlebih dahulu.';
            submitHint.style.color = '#8f2f2f';
            return;
        }

        if (modelSelect.value === 'Raglan' && !secondaryColorInput.value) {
            event.preventDefault();
            submitHint.textContent = 'Pilih warna lengan terlebih dahulu untuk model Raglan.';
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

    // Move modal to body so it escapes any grid/stacking-context constraints
    const imageModal = document.getElementById('materialImageModal');
    if (imageModal && imageModal.parentNode !== document.body) {
        document.body.appendChild(imageModal);
    }

    // Modal functions
    window.openMaterialImageModal = (imageSrc, title) => {
        document.getElementById('materialImageSrc').src = imageSrc;
        document.getElementById('materialImageTitle').textContent = title || 'Detail Bahan';
        document.getElementById('materialImageModal').classList.add('is-open');
    };

    window.closeMaterialImageModal = () => {
        document.getElementById('materialImageModal').classList.remove('is-open');
    };

    document.getElementById('materialImageModal').addEventListener('click', (e) => {
        if (e.target.id === 'materialImageModal') {
            closeMaterialImageModal();
        }
    });
})();
</script>
@endsection
