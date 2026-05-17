@extends('layouts.app')

@section('content')
@php
    $status = (string) ($order->admin_verification_status ?: 'pending');
    $resolvedStatus = \App\Support\OrderStatusPresenter::resolveForCustomer($order, $order->payments->last());
    $verificationLabel = \App\Support\OrderStatusPresenter::customerLabel($resolvedStatus);
    $verificationClass = \App\Support\OrderStatusPresenter::customerClass($resolvedStatus);

    $sizeSummary = $order->sizes
        ->sortBy('size_name')
        ->map(fn ($size) => $size->size_name . ': ' . number_format((int) $size->qty, 0, ',', '.'))
        ->implode(', ');

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

    $extractedSecondaryColor = $order->secondary_color;
    if (!$extractedSecondaryColor) {
        $tempLines = preg_split('/\R/', (string) ($order->notes ?? '')) ?: [];
        foreach ($tempLines as $line) {
            $trimmed = trim($line);
            if (\Illuminate\Support\Str::startsWith($trimmed, 'Warna Lengan: ')) {
                $extractedSecondaryColor = trim((string) \Illuminate\Support\Str::after($trimmed, 'Warna Lengan: '));
                break;
            }
        }
    }
@endphp

<style>
    .order-detail-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem;
    }

    .detail-top {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.1rem;
    }

    .detail-top h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .detail-top p {
        margin: 0.4rem 0 0;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
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

    .design-grid {
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .design-preview {
        border: 1px solid #d8e4ee;
        border-radius: 12px;
        overflow: hidden;
        background: #f9fbff;
        padding: 1rem;
        text-align: center;
    }

    .design-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        max-height: 300px;
    }

    .design-preview .title {
        margin: 0 0 0.6rem;
        font-size: 0.95rem;
        color: #1a3a52;
        font-weight: 700;
    }

    .actions-wrap {
        margin-top: 1rem;
        display: grid;
        gap: 0.8rem;
    }

    .actions-note {
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 0.9rem;
        background: #f9fbff;
        color: #5a7489;
        font-size: 0.84rem;
    }

    .action-card {
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 0.9rem;
        background: #ffffff;
    }

    .action-card h3 {
        margin: 0 0 0.6rem;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
    }

    .action-card form {
        margin: 0;
        display: grid;
        gap: 0.55rem;
    }

    .action-card .btn {
        justify-self: start;
    }

    .btn-revision {
        background: #b63b22;
        border: 1px solid #b63b22;
        color: #ffffff;
        font-weight: 700;
    }

    .btn-revision:hover {
        background: #9f2f1a;
        border-color: #9f2f1a;
        color: #ffffff;
    }

    .revision-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.52rem;
    }

    .revision-grid .full {
        grid-column: 1 / -1;
    }

    .muted-mini {
        color: #7f96ae;
        font-size: 0.75rem;
        line-height: 1.45;
    }

    @media (max-width: 1000px) {
        .order-detail-page {
            padding: 1rem;
        }

        .spec-grid,
        .design-grid,
        .revision-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="order-detail-page">
    <div class="detail-top">
        <div>
            <h1>Spesifikasi Pesanan</h1>
            <p>{{ $order->order_code }} • {{ $order->customer_name ?: ($order->user->name ?? '-') }}</p>
        </div>
        <span class="status-pill {{ $verificationClass }}">{{ $verificationLabel }}</span>
    </div>

    <div class="spec-grid">
        <div class="spec-item">
            <p class="spec-label">Nama Pemesan</p>
            <p class="spec-value">{{ $order->customer_name ?: ($order->user->name ?? '-') }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Kontak</p>
            <p class="spec-value">{{ $order->user->email ?? '-' }}<br>{{ $order->user->phone ?? '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Bahan</p>
            <p class="spec-value">{{ $order->fabric ?: '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Model</p>
            <p class="spec-value">{{ $order->product_model ?: '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Jenis Produksi</p>
            <p class="spec-value">{{ $order->production_type ?: '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Lengan</p>
            <p class="spec-value">{{ $order->sleeve_type ?: '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Warna Dominan</p>
            <p class="spec-value">{{ $order->dominant_color ?: '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Tanggal Selesai</p>
            <p class="spec-value">{{ optional($order->estimated_finish_date)->format('d/m/Y') ?: '-' }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Jumlah Pesanan</p>
            <p class="spec-value">{{ number_format((int) $order->total_pcs, 0, ',', '.') }} pcs</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Harga per Pcs</p>
            <p class="spec-value">Rp {{ number_format((float) $order->unit_price, 0, ',', '.') }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Total Harga Pesanan</p>
            <p class="spec-value">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</p>
        </div>
        <div class="spec-item">
            <p class="spec-label">Rincian Ukuran</p>
            <p class="spec-value">{{ $sizeSummary !== '' ? $sizeSummary : '-' }}</p>
        </div>
        @php
            $noteLines = preg_split('/\R/', (string) ($order->notes ?? '')) ?: [];
            $customerNotes = [];
            foreach ($noteLines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '') continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Jenis: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Teknik Sablon: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Model: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Ukuran Lengan: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Tambahan ukuran XXL/XXXL: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Posisi Desain: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Catatan desain: ')) continue;
                if (\Illuminate\Support\Str::startsWith($trimmed, 'Catatan pelanggan: ')) {
                    $customerNotes[] = trim((string) \Illuminate\Support\Str::after($trimmed, 'Catatan pelanggan: '));
                    continue;
                }
                $customerNotes[] = $trimmed;
            }
            $displayCustomerNote = trim(implode(PHP_EOL, array_filter($customerNotes)));
        @endphp
        @if ($displayCustomerNote !== '')
        <div class="spec-item" style="grid-column: 1 / -1; background-color: #f1f8ff; border: 1px solid #cce5ff;">
            <p class="spec-label" style="color: #4a5568;">Catatan Pesanan</p>
            <p class="spec-value" style="white-space:pre-wrap;">{{ $displayCustomerNote }}</p>
        </div>
        @endif
        <div class="spec-item" style="grid-column: 1 / -1;">
            <p class="spec-label">Catatan Desain</p>
            <p class="spec-value">{{ $order->design_notes ?: '-' }}</p>
        </div>
    </div>

    <div class="design-grid">
        <div class="design-preview">
            <p class="title">Desain Bagian Depan</p>
            @if ($designFrontPath)
                @if ($frontIsImage)
                    <img src="{{ asset('storage/' . $designFrontPath) }}" alt="Desain Depan">
                @else
                    <a class="btn btn-outline" href="{{ asset('storage/' . $designFrontPath) }}" target="_blank">Lihat File Depan</a>
                @endif
            @else
                <p class="muted-mini">Belum ada desain depan</p>
            @endif
        </div>
        <div class="design-preview">
            <p class="title">Desain Bagian Belakang</p>
            @if ($designBackPath)
                @if ($backIsImage)
                    <img src="{{ asset('storage/' . $designBackPath) }}" alt="Desain Belakang">
                @else
                    <a class="btn btn-outline" href="{{ asset('storage/' . $designBackPath) }}" target="_blank">Lihat File Belakang</a>
                @endif
            @else
                <p class="muted-mini">Belum ada desain belakang</p>
            @endif
        </div>
    </div>

    <div class="actions-wrap">
        @if ($status !== 'verified')
            <div id="verify-section" class="action-card">
                <h3>Verifikasi Pesanan</h3>
                <form method="POST" action="{{ route('reports.orders.verify', $order) }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <textarea name="admin_note" rows="2" placeholder="Catatan verifikasi (opsional)"></textarea>
                    <button class="btn btn-brand" type="submit">Verifikasi</button>
                </form>
            </div>

            <div id="revision-section" class="action-card">
                <h3>Ajukan Kembali (Revisi ke Customer)</h3>
                <form method="POST" action="{{ route('reports.orders.revision', $order) }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <div class="revision-grid">
                        <div>
                            <label>Nama Pemesan</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required>
                        </div>
                        <div>
                            <label>Bahan</label>
                            <select name="fabric" required>
                                @foreach ($materials as $material)
                                    <option value="{{ $material }}" {{ old('fabric', $order->fabric) === $material ? 'selected' : '' }}>{{ $material }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Jenis Produksi</label>
                            <select name="production_type" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type }}" {{ old('production_type', $order->production_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Model</label>
                            <select name="product_model" required>
                                @foreach ($models as $model)
                                    <option value="{{ $model }}" {{ old('product_model', $order->product_model) === $model ? 'selected' : '' }}>{{ $model }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Ukuran Lengan</label>
                            <select name="sleeve_type" required>
                                @foreach ($sleeves as $sleeve)
                                    <option value="{{ $sleeve }}" {{ old('sleeve_type', $order->sleeve_type) === $sleeve ? 'selected' : '' }}>{{ $sleeve }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Warna Dominan</label>
                            <input type="text" name="dominant_color" id="dominant_color" value="{{ old('dominant_color', $order->dominant_color) }}" required>
                        </div>
                        <div id="secondary-color-group">
                            <label>Warna Lengan</label>
                            <input type="text" name="secondary_color" id="secondary_color" value="{{ old('secondary_color', $extractedSecondaryColor) }}">
                        </div>
                        <div>
                            <label>Tanggal Selesai</label>
                            <input type="date" name="estimated_finish_date" value="{{ old('estimated_finish_date', optional($order->estimated_finish_date)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="full">
                            <label>Catatan Desain</label>
                            <textarea name="design_notes" rows="2">{{ old('design_notes', $order->design_notes) }}</textarea>
                        </div>
                        <div class="full">
                            <label>Catatan untuk Customer (wajib)</label>
                            <textarea name="admin_revision_note" rows="3" required>{{ old('admin_revision_note') }}</textarea>
                        </div>
                        <div class="full">
                            <button class="btn btn-revision" type="submit">Kirim Ajukan Kembali</button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="actions-note">
                Pesanan ini sudah terverifikasi. 
            </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modelSelect = document.querySelector('select[name="product_model"]');
        const secondaryGroup = document.getElementById('secondary-color-group');
        const secondaryInput = document.getElementById('secondary_color');

        if (!modelSelect || !secondaryGroup) return;

        function updateColorFields() {
            if (modelSelect.value === 'Raglan') {
                secondaryGroup.style.display = 'block';
            } else {
                secondaryGroup.style.display = 'none';
                secondaryInput.value = '';
            }
        }

        updateColorFields();
        modelSelect.addEventListener('change', updateColorFields);
    });
</script>
@endsection
