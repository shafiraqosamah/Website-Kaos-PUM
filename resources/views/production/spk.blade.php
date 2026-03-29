@extends('layouts.app')

@section('content')
@php
    $orderNoteLines = preg_split('/\R/', (string) ($order->notes ?? '')) ?: [];
    $customerNotes = [];
    $legacyDesignNote = null;
    $legacySpec = [
        'production_type' => null,
        'product_model' => null,
        'sleeve_type' => null,
    ];
    $legacyDesignPosition = null;

    foreach ($orderNoteLines as $line) {
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

        if (preg_match('/^Ukuran\s*Lengan\s*:\s*(.+)$/i', $trimmed, $matches) === 1) {
            $legacySpec['sleeve_type'] = trim((string) ($matches[1] ?? ''));
            continue;
        }

        if (preg_match('/^Lengan\s*:\s*(.+)$/i', $trimmed, $matches) === 1) {
            $legacySpec['sleeve_type'] = trim((string) ($matches[1] ?? ''));
            continue;
        }

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Tambahan ukuran XXL/XXXL:')) {
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
            $customerNotes[] = \Illuminate\Support\Str::after($trimmed, 'Catatan pelanggan: ');
            continue;
        }

        $customerNotes[] = $trimmed;
    }

    $displayCustomerNote = trim(implode(PHP_EOL, array_filter($customerNotes)));
    $displayProductionType = $order->production_type ?: $legacySpec['production_type'];
    $displayProductModel = $order->product_model ?: $legacySpec['product_model'];
    $rawSleeveType = (string) ($order->sleeve_type ?: $legacySpec['sleeve_type'] ?: '');
    $displaySleeveType = match (strtolower(trim($rawSleeveType))) {
        'pendek', 'lengan pendek' => 'Lengan Pendek',
        'panjang', 'lengan panjang' => 'Lengan Panjang',
        default => $rawSleeveType,
    };
    $designFrontPath = $order->design_front_file ?: $order->design_file;
    $designBackPath = $order->design_back_file;

    if ($legacyDesignPosition === null && preg_match('/Posisi\s+Desain\s*:\s*(.+)$/mi', (string) ($order->design_notes ?? ''), $matches) === 1) {
        $legacyDesignPosition = trim((string) ($matches[1] ?? ''));
    }

    $legacyDesignInfo = trim(implode(PHP_EOL, array_filter([
        $legacyDesignPosition ? 'Posisi Desain: ' . $legacyDesignPosition : null,
        $legacyDesignNote,
    ])));

    $displayDesignPosition = $legacyDesignPosition;
    $displayDesignNotes = (string) ($order->design_notes ?: $legacyDesignInfo ?: '');

    $designExt = static function (?string $path): string {
        return strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));
    };

    $isDesignImage = static function (?string $path) use ($designExt): bool {
        if (! $path) {
            return false;
        }

        return in_array($designExt($path), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    };

    $frontExt = $designExt($designFrontPath);
    $backExt = $designExt($designBackPath);
    $frontIsImage = $isDesignImage($designFrontPath);
    $backIsImage = $isDesignImage($designBackPath);

    $sizeChartShort = asset('images/katalog/sizependek.png');
    $sizeChartLong = asset('images/katalog/sizepanjang.png');
    $isShortSleeve = strtolower((string) $displaySleeveType) === strtolower('Lengan Pendek');
    $isLongSleeve = strtolower((string) $displaySleeveType) === strtolower('Lengan Panjang');
@endphp

<style>
    .spk-page {
        max-width: 1100px;
        margin: 0 auto;
    }

    .spk-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0.8rem;
    }

    .spk-sheet {
        background: #fff;
        border: 1px solid #cfdbe6;
        border-radius: 14px;
        padding: 1.4rem;
        box-shadow: 0 12px 26px rgba(16, 62, 98, 0.08);
    }

    .spk-ribbon {
        height: 22px;
        border-radius: 8px 8px 0 0;
        background: linear-gradient(95deg, #17b5aa 0%, #35c9c0 52%, #17a89f 100%);
        margin: -1.4rem -1.4rem 1rem;
    }

    .spk-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        border-bottom: 2px dashed #cedbe6;
        padding-bottom: 0.9rem;
        margin-bottom: 1rem;
    }

    .spk-title {
        margin: 0;
        color: #0e8e87;
        font-size: 2rem;
        font-family: 'Sora', sans-serif;
        text-decoration: underline;
        text-underline-offset: 6px;
    }

    .spk-subtitle {
        margin-top: 0.55rem;
        color: #16364f;
        font-size: 0.95rem;
    }

    .spk-subtitle .order-number {
        font-size: 1.15rem;
        font-weight: 700;
    }

    .spk-subtitle .order-tag {
        color: #0ea89f;
        font-weight: 700;
    }

    .spk-version {
        margin-top: 0.25rem;
        color: #6e879a;
        font-size: 0.82rem;
        font-style: italic;
    }

    .spk-brand {
        text-align: right;
    }

    .spk-brand img {
        width: 200px;
        max-width: 100%;
        height: auto;
        display: inline-block;
    }

    .spk-brand-name {
        font-family: 'Sora', sans-serif;
        font-size: 1.55rem;
        color: #0f7b8f;
        font-weight: 700;
    }

    .spk-brand-note {
        font-size: 0.88rem;
        color: #6d8498;
    }

    .spk-info {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0.35rem;
    }

    .spk-info td {
        border-bottom: 1px solid #d4dee8;
        padding: 0.48rem 0.25rem;
        vertical-align: top;
    }

    .spk-info td:nth-child(odd) {
        color: #173952;
        width: 16%;
        font-weight: 600;
        white-space: nowrap;
    }

    .spk-info td:nth-child(even) {
        color: #16364f;
        width: 34%;
    }

    .spk-block {
        margin-top: 1.2rem;
    }

    .spk-block h3 {
        margin: 0 0 0.55rem;
        font-size: 1.08rem;
        color: #16364f;
        font-family: 'Sora', sans-serif;
    }

    .spk-design-box {
        border: 1px solid #cddae6;
        border-radius: 8px;
        padding: 0.75rem;
        background: #fff;
        min-height: 340px;
    }

    .spk-design-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .spk-design-title {
        margin: 0 0 0.55rem;
        font-size: 0.9rem;
        color: #1d3f58;
        font-family: 'Sora', sans-serif;
    }

    .spk-design-box img {
        width: 100%;
        max-height: 420px;
        object-fit: contain;
        border: 1px solid #dce6ef;
        border-radius: 6px;
        background: #fff;
    }

    .spk-size-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .spk-size-card {
        border: 1px solid #cfdbe6;
        border-radius: 10px;
        padding: 0.6rem;
        background: #fbfdff;
    }

    .spk-size-card img {
        width: 100%;
        height: auto;
        border: 1px solid #dbe6ef;
        border-radius: 8px;
        display: block;
    }

    .spk-size-card p {
        margin: 0.5rem 0 0;
        font-size: 0.86rem;
        color: #4f6b80;
    }

    .spk-size-card.active {
        border-color: #18a9a1;
        box-shadow: 0 0 0 2px rgba(24, 169, 161, 0.12) inset;
    }

    .spk-signatures {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.2rem;
    }

    .spk-sign {
        border-top: 1px dashed #bdccda;
        padding-top: 0.5rem;
        text-align: center;
        color: #5b768b;
        font-size: 0.85rem;
    }

    .spk-block,
    .spk-info,
    .spk-size-grid,
    .spk-signatures {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    @media (max-width: 1000px) {
        .spk-info td:nth-child(odd),
        .spk-info td:nth-child(even) {
            width: auto;
            display: block;
            border-bottom: 0;
            padding-bottom: 0.15rem;
        }

        .spk-info tr {
            border-bottom: 1px solid #d4dee8;
            display: block;
            padding: 0.4rem 0;
        }

        .spk-size-grid {
            grid-template-columns: 1fr;
        }

        .spk-design-grid {
            grid-template-columns: 1fr;
        }

        .spk-signatures {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .spk-actions,
        .sidebar,
        .topbar,
        .sidebar-toggle,
        .auth-topbar,
        .menu,
        .sidebar-footer {
            display: none !important;
        }

        html,
        body {
            background: #fff !important;
        }

        .shell {
            padding: 0 !important;
        }

        .layout-auth,
        .layout-auth.sidebar-hidden {
            display: block !important;
            grid-template-columns: 1fr !important;
            gap: 0 !important;
        }

        .auth-main {
            width: 100% !important;
            min-width: 0 !important;
        }

        .layout-auth main {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .spk-page {
            max-width: 100% !important;
        }

        .spk-sheet {
            border: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }

        .spk-ribbon {
            margin: 0 0 1rem 0;
            border-radius: 0;
        }

        .spk-title {
            font-size: 1.55rem !important;
        }

        .spk-info td {
            font-size: 11.5pt;
        }

        .spk-design-box {
            min-height: 240px;
        }

        .spk-design-box img {
            max-height: 300px;
        }

        .spk-size-card img {
            max-height: 250px;
            object-fit: contain;
        }

        @page {
            size: A4;
            margin: 10mm;
        }
    }
</style>

<div class="spk-page">
    <div class="spk-actions">
        <button type="button" class="btn btn-brand" onclick="window.print()">Print SPK</button>
    </div>

    <div class="spk-sheet">
        <div class="spk-ribbon"></div>

        <div class="spk-head">
            <div>
                <h1 class="spk-title">SPK (Surat Perintah Kerja)</h1>
                <div class="spk-subtitle">No. Order : <span class="order-number">{{ $order->order_code }}</span> <span class="order-tag">({{ strtoupper($displayProductionType ?: 'CUSTOM') }})</span></div>
                <div class="spk-version">Ver.1.0 Rev.0.0</div>
            </div>

            <div class="spk-brand">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Konveksi" onerror="this.style.display='none'">
                <div class="spk-brand-name">PT Panji Usaha Mulia</div>
                <div class="spk-brand-note">Production Work Sheet</div>
            </div>
        </div>

        <table class="spk-info">
            <tbody>
                <tr>
                    <td>Nama Customer</td><td>: {{ $order->customer_name }}</td>
                    <td>Jenis Kain</td><td>: {{ $order->fabric }}</td>
                </tr>
                <tr>
                    <td>Nama Perusahaan</td><td>: -</td>
                    <td>Warna Kain</td><td>: {{ $order->dominant_color }}</td>
                </tr>
                <tr>
                    <td>Produk Order</td><td>: {{ $displayProductModel ?: $order->product_name }}</td>
                    <td>Pembayaran</td><td>: {{ $order->payment_type === 'dp' ? 'DP 50%' : 'Lunas Awal' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Masuk</td><td>: {{ $order->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>Jumlah Pesanan</td><td>: {{ $order->total_pcs }} pcs</td>
                </tr>
                <tr>
                    <td>Tanggal Selesai</td><td>: {{ $order->estimated_finish_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>Jenis Lengan</td><td>: {{ $displaySleeveType ?: '-' }}</td>
                </tr>
                <tr>
                    <td>Posisi Desain</td><td>: {{ $displayDesignPosition ?: '-' }}</td>
                    <td>Jenis Produksi</td><td>: {{ $displayProductionType ?: '-' }}</td>
                </tr>
                <tr>
                    <td>No. SPK</td><td>: {{ $order->workOrder->spk_number }}</td>
                    <td>Status SPK</td><td>: {{ strtoupper($order->workOrder->status) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="spk-block">
            <h3>Preview Desain</h3>
            <div class="spk-design-grid">
                <div class="spk-design-box">
                    <p class="spk-design-title">Desain Bagian Depan</p>
                    @if($designFrontPath)
                        @if($frontIsImage)
                            <img src="{{ asset('storage/' . $designFrontPath) }}" alt="Preview desain depan">
                        @else
                            <p class="muted" style="margin:0 0 0.6rem;">File depan berupa {{ strtoupper($frontExt) }} dan tidak memiliki preview gambar.</p>
                            <a class="btn btn-outline" href="{{ asset('storage/' . $designFrontPath) }}" target="_blank">Buka / Download Desain Depan</a>
                        @endif
                    @else
                        <p class="muted" style="margin:0;">Belum ada file desain depan.</p>
                    @endif
                </div>

                <div class="spk-design-box">
                    <p class="spk-design-title">Desain Bagian Belakang</p>
                    @if($designBackPath)
                        @if($backIsImage)
                            <img src="{{ asset('storage/' . $designBackPath) }}" alt="Preview desain belakang">
                        @else
                            <p class="muted" style="margin:0 0 0.6rem;">File belakang berupa {{ strtoupper($backExt) }} dan tidak memiliki preview gambar.</p>
                            <a class="btn btn-outline" href="{{ asset('storage/' . $designBackPath) }}" target="_blank">Buka / Download Desain Belakang</a>
                        @endif
                    @else
                        <p class="muted" style="margin:0;">Belum ada file desain belakang.</p>
                    @endif
                </div>
            </div>

            @if($displayDesignNotes !== '')
                <div style="margin-top:0.7rem; border:1px solid #d7e4ea; border-radius:8px; padding:0.65rem 0.75rem; background:#f8fcff;">
                    <strong style="display:block; margin-bottom:0.25rem; color:#24516b;">Catatan Posisi/Ukuran Desain</strong>
                    <div style="white-space:pre-wrap; color:#16364f;">{{ $displayDesignNotes }}</div>
                </div>
            @endif
        </div>
        <div class="spk-block">
            <h3>Distribusi Ukuran</h3>
            <table>
                <thead>
                    <tr><th>Ukuran</th><th>Qty</th></tr>
                </thead>
                <tbody>
                    @forelse($order->sizes as $size)
                        <tr>
                            <td>{{ $size->size_name }}</td>
                            <td>{{ $size->qty }} pcs</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="muted">Belum ada data ukuran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="spk-block">
            <h3>Size Chart Referensi</h3>
            <div class="spk-size-grid">
                <div class="spk-size-card {{ $isShortSleeve ? 'active' : '' }}">
                    <img src="{{ $sizeChartShort }}" alt="Size chart lengan pendek" onerror="this.style.display='none'">
                    <p>Size chart lengan pendek {{ $isShortSleeve ? '(dipilih)' : '' }}</p>
                </div>
                <div class="spk-size-card {{ $isLongSleeve ? 'active' : '' }}">
                    <img src="{{ $sizeChartLong }}" alt="Size chart lengan panjang" onerror="this.style.display='none'">
                    <p>Size chart lengan panjang {{ $isLongSleeve ? '(dipilih)' : '' }}</p>
                </div>
            </div>
        </div>

        @if($displayCustomerNote !== '')
            <div class="spk-block">
                <h3>Catatan Pelanggan</h3>
                <p style="margin:0; white-space:pre-wrap;">{{ $displayCustomerNote }}</p>
            </div>
        @endif

        <div class="spk-signatures">
            <div class="spk-sign">Disiapkan oleh Production Admin</div>
            <div class="spk-sign">Diterima oleh Tim Produksi</div>
        </div>
    </div>
</div>
@endsection
