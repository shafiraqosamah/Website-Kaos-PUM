@php
    $orderNoteLines = preg_split('/\R/', (string) ($order->notes ?? '')) ?: [];
    $extractedSecondaryColor = $order->secondary_color;
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

        if (\Illuminate\Support\Str::startsWith($trimmed, 'Warna Lengan: ')) {
            if (!$extractedSecondaryColor) {
                $extractedSecondaryColor = trim((string) \Illuminate\Support\Str::after($trimmed, 'Warna Lengan: '));
            }
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - {{ $order->workOrder->spk_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            color: #111;
            background: #f2f4f7;
            font-family: Arial, Helvetica, sans-serif;
        }
        .sheet {
            width: min(900px, 100%);
            margin: 0 auto;
            background: #fff;
            padding: 28px 34px 44px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }
        .top {
            display: flex;
            gap: 14px;
            align-items: center;
            border-bottom: 4px solid #111;
            padding-bottom: 12px;
        }
        .brand-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
            display: block;
        }
        .brand {
            min-width: 0;
            flex: 1;
        }
        .brand-title {
            font-size: 48px;
            font-weight: 800;
            color: #d60f0f;
            line-height: 1;
            letter-spacing: 0.01em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .brand-contact {
            font-size: 22px;
            letter-spacing: 0.16em;
            color: #111;
        }
        .section-title {
            text-align: center;
            margin: 18px 0 4px;
            font-size: 24px;
            font-weight: 700;
        }
        .invoice-no {
            text-align: center;
            margin-bottom: 22px;
        }
        .meta {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 22px;
            font-size: 14px;
        }
        .meta strong {
            display: inline-block;
            min-width: 130px;
        }
        .meta > div {
            flex: 1;
        }
        .meta-row {
            margin-bottom: 6px;
        }
        
        .spk-block {
            margin-top: 24px;
        }
        .spk-block h3 {
            margin: 0 0 10px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }
        th, td {
            border: 2px solid #202020;
            padding: 8px 10px;
        }
        th {
            background: #f3f3f3;
            text-align: center;
        }

        .spk-design-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .spk-design-box {
            border: 2px solid #202020;
            padding: 12px;
            min-height: 200px;
        }
        .spk-design-title {
            margin: 0 0 10px;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
        }
        .spk-design-box img {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
            display: block;
        }
        .muted { color: #555; font-size: 13px; }

        .spk-size-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .spk-size-card {
            border: 2px solid #202020;
            padding: 12px;
            text-align: center;
        }
        .spk-size-card.active {
            background: #f0f7f7;
            border-color: #17a89f;
        }
        .spk-size-card img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
        }
        .spk-size-card p {
            margin: 10px 0 0;
            font-size: 13px;
            font-weight: bold;
        }

        .footer {
            margin-top: 42px;
            display: flex;
            justify-content: flex-end;
            gap: 60px;
        }
        .signature {
            width: 200px;
            text-align: center;
            line-height: 1.7;
        }
        
        .actions {
            width: min(900px, 100%);
            margin: 0 auto 14px;
            display: flex;
            justify-content: flex-end;
        }
        .actions button {
            border: 0;
            background: #0c1a26;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { box-shadow: none; width: 100%; padding: 0; }
            .actions { display: none; }
            .spk-block, table, .spk-design-grid, .spk-size-grid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
        @media (max-width: 900px) {
            .top {
                align-items: flex-start;
            }
            .brand-title {
                font-size: 36px;
            }
            .brand-contact {
                font-size: 15px;
                letter-spacing: 0.1em;
            }
        }
    </style>
</head>
<body>
<div class="actions">
    <button onclick="window.print()">Cetak SPK</button>
</div>
<div class="sheet">
    <div class="top">
        <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Logo PT Panji Usaha Mulia">
        <div class="brand">
            <div class="brand-title">PT PANJI USAHA MULIA</div>
            <div class="brand-contact">Jl. Jendral A. Yani 909 Bandung | 022 721 5924</div>
        </div>
    </div>

    <div class="section-title">SPK (SURAT PERINTAH KERJA)</div>
    <div class="invoice-no">NO. SPK : {{ $order->workOrder->spk_number }}</div>

    <div class="meta">
        <div>
            <div class="meta-row"><strong>No. Order</strong> : {{ $order->order_code }}</div>
            <div class="meta-row"><strong>Nama Customer</strong> : {{ $order->customer_name }}</div>
            <div class="meta-row"><strong>Nama Perusahaan</strong> : -</div>
            <div class="meta-row"><strong>Produk Order</strong> : {{ $displayProductModel ?: $order->product_name }}</div>
            <div class="meta-row"><strong>Jumlah Pesanan</strong> : {{ $order->total_pcs }} pcs</div>
            <div class="meta-row"><strong>Tanggal Masuk</strong> : {{ $order->created_at?->format('d/m/Y') ?? '-' }}</div>
            <div class="meta-row"><strong>Tanggal Selesai</strong> : {{ $order->estimated_finish_date?->format('d/m/Y') ?? '-' }}</div>
        </div>
        <div>
            <div class="meta-row"><strong>Jenis Kain</strong> : {{ $order->fabric }}</div>
            <div class="meta-row"><strong>Warna Kain</strong> : {{ $order->dominant_color }}{{ $extractedSecondaryColor ? ' (Body) / ' . $extractedSecondaryColor . ' (Lengan)' : '' }}</div>
            <div class="meta-row"><strong>Jenis Produksi</strong> : {{ $displayProductionType ?: '-' }}</div>
            <div class="meta-row"><strong>Jenis Lengan</strong> : {{ $displaySleeveType ?: '-' }}</div>
            <div class="meta-row"><strong>Posisi Desain</strong> : {{ $displayDesignPosition ?: '-' }}</div>
            <div class="meta-row"><strong>Pembayaran</strong> : {{ $order->payment_type === 'dp' ? 'DP 50%' : 'Lunas Awal' }}</div>
            <div class="meta-row"><strong>Status SPK</strong> : {{ strtoupper($order->workOrder->status) }}</div>
        </div>
    </div>

    <div class="spk-block">
        <h3>Preview Desain</h3>
        <div class="spk-design-grid">
            <div class="spk-design-box">
                <p class="spk-design-title">Desain Bagian Depan</p>
                @if($designFrontPath)
                    @if($frontIsImage)
                        <img src="{{ asset('storage/' . $designFrontPath) }}" alt="Preview desain depan">
                    @else
                        <p class="muted">File depan berupa {{ strtoupper($frontExt) }} dan tidak memiliki preview gambar.</p>
                        <a href="{{ asset('storage/' . $designFrontPath) }}" target="_blank">Download Desain Depan</a>
                    @endif
                @else
                    <p class="muted">Belum ada file desain depan.</p>
                @endif
            </div>

            <div class="spk-design-box">
                <p class="spk-design-title">Desain Bagian Belakang</p>
                @if($designBackPath)
                    @if($backIsImage)
                        <img src="{{ asset('storage/' . $designBackPath) }}" alt="Preview desain belakang">
                    @else
                        <p class="muted">File belakang berupa {{ strtoupper($backExt) }} dan tidak memiliki preview gambar.</p>
                        <a href="{{ asset('storage/' . $designBackPath) }}" target="_blank">Download Desain Belakang</a>
                    @endif
                @else
                    <p class="muted">Belum ada file desain belakang.</p>
                @endif
            </div>
        </div>

        @if($displayDesignNotes !== '')
            <div style="margin-top:12px; border:2px solid #202020; padding:12px;">
                <strong>Catatan Posisi/Ukuran Desain:</strong>
                <div style="white-space:pre-wrap; margin-top:4px;">{{ $displayDesignNotes }}</div>
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
                        <td style="text-align:center;">{{ $size->size_name }}</td>
                        <td style="text-align:center;">{{ $size->qty }} pcs</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted" style="text-align:center;">Belum ada data ukuran.</td></tr>
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
        <div class="spk-block" style="border:2px solid #202020; padding:12px;">
            <strong>Catatan Pelanggan:</strong>
            <p style="margin:4px 0 0; white-space:pre-wrap;">{{ $displayCustomerNote }}</p>
        </div>
    @endif

    <div class="footer">
        <div class="signature">
            <div style="border-top:1px solid #111; padding-top:8px;">
                <strong>Diterima oleh</strong><br>
                Tim Produksi
            </div>
        </div>
        <div class="signature">
            <div style="border-top:1px solid #111; padding-top:8px;">
                <strong>Dibuat oleh</strong><br>
                Admin
            </div>
        </div>
    </div>
</div>
</body>
</html>
