<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produksi</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="11" style="text-align: center; font-size: 16px; font-weight: bold;">LAPORAN PRODUKSI BULANAN</th>
            </tr>
            <tr>
                <th colspan="11" style="text-align: center;">Periode: {{ $monthLabel }}</th>
            </tr>
            <tr>
                <th colspan="11"></th>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">Order Selesai:</th>
                <th>{{ $completedInMonth }}</th>
                <th colspan="8"></th>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">Total PCS Selesai:</th>
                <th>{{ $producedPcsInMonth }}</th>
                <th colspan="8"></th>
            </tr>
            <tr>
                <th colspan="11"></th>
            </tr>
            <tr>
                <th colspan="11" style="font-weight: bold; font-size: 14px;">Ringkasan Jenis Produksi</th>
            </tr>
            <tr>
                <th style="font-weight: bold; border: 1px solid #000;">Jenis Produksi</th>
                <th style="font-weight: bold; border: 1px solid #000;">Produk</th>
                <th style="font-weight: bold; border: 1px solid #000;">No.Order</th>
                <th style="font-weight: bold; border: 1px solid #000;">Total PCS</th>
                <th colspan="7"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($productionByType as $row)
                <tr>
                    <td style="border: 1px solid #000;">{{ $row['production_type'] ?: '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['product_model'] ?? '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $row['total_orders'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $row['total_pcs'] }}</td>
                    <td colspan="7"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border: 1px solid #000; text-align: center;">Belum ada data produksi.</td>
                    <td colspan="7"></td>
                </tr>
            @endforelse
            <tr>
                <th colspan="11"></th>
            </tr>
            <tr>
                <th colspan="11" style="font-weight: bold; font-size: 14px;">Progress Tiap Pesanan</th>
            </tr>
            <tr>
                <th style="font-weight: bold; border: 1px solid #000;">No.Order</th>
                <th style="font-weight: bold; border: 1px solid #000;">No SPK</th>
                <th style="font-weight: bold; border: 1px solid #000;">Pelanggan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Produk</th>
                <th style="font-weight: bold; border: 1px solid #000;">Jenis</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tanggal Pesan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tanggal Selesai</th>
                <th style="font-weight: bold; border: 1px solid #000;">PCS</th>
                <th style="font-weight: bold; border: 1px solid #000;">Progress Step</th>
                <th style="font-weight: bold; border: 1px solid #000;">Status</th>
                <th style="font-weight: bold; border: 1px solid #000;">Dokumen SPK</th>
            </tr>
            @forelse($productionRows as $row)
                <tr>
                    <td style="border: 1px solid #000;">{{ $row['order_code'] }}</td>
                    <td style="border: 1px solid #000; font-weight: bold;">{{ $row['spk_number'] ?? '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['customer_name'] }}</td>
                    <td style="border: 1px solid #000;">{{ $row['product_name'] ?: '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['production_type'] ?: '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['created_at'] ? \Carbon\Carbon::parse($row['created_at'])->format('d M Y') : '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['finished_at'] ? \Carbon\Carbon::parse($row['finished_at'])->format('d M Y') : '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $row['total_pcs'] }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ "='" . $row['step_progress'] . "'" }}</td>
                    <td style="border: 1px solid #000;">{{ str_replace('_', ' ', $row['order_status']) }}</td>
                    <td style="border: 1px solid #000;">
                        @if($row['spk_number'])
                            <a href="{{ route('production.spk', ['order' => $row['id']]) }}">Lihat SPK</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="border: 1px solid #000; text-align: center;">Belum ada order untuk dipantau pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
