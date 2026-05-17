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
                <th colspan="7" style="text-align: center; font-size: 16px; font-weight: bold;">LAPORAN PRODUKSI BULANAN</th>
            </tr>
            <tr>
                <th colspan="7" style="text-align: center;">Periode: {{ $monthLabel }}</th>
            </tr>
            <tr>
                <th colspan="7"></th>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">Order Selesai:</th>
                <th>{{ $completedInMonth }}</th>
                <th colspan="4"></th>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">Total PCS Selesai:</th>
                <th>{{ $producedPcsInMonth }}</th>
                <th colspan="4"></th>
            </tr>
            <tr>
                <th colspan="7"></th>
            </tr>
            <tr>
                <th colspan="7" style="font-weight: bold; font-size: 14px;">Ringkasan Jenis Produksi</th>
            </tr>
            <tr>
                <th style="font-weight: bold; border: 1px solid #000;">Jenis Produksi</th>
                <th style="font-weight: bold; border: 1px solid #000;">Produk</th>
                <th style="font-weight: bold; border: 1px solid #000;">Order</th>
                <th style="font-weight: bold; border: 1px solid #000;">Total PCS</th>
                <th colspan="3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($productionByType as $row)
                <tr>
                    <td style="border: 1px solid #000;">{{ $row['production_type'] ?: '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['product_model'] ?? '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $row['total_orders'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $row['total_pcs'] }}</td>
                    <td colspan="3"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="border: 1px solid #000; text-align: center;">Belum ada data produksi.</td>
                    <td colspan="3"></td>
                </tr>
            @endforelse
            <tr>
                <th colspan="7"></th>
            </tr>
            <tr>
                <th colspan="7" style="font-weight: bold; font-size: 14px;">Progress Tiap Pesanan</th>
            </tr>
            <tr>
                <th style="font-weight: bold; border: 1px solid #000;">Order</th>
                <th style="font-weight: bold; border: 1px solid #000;">Pelanggan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Produk</th>
                <th style="font-weight: bold; border: 1px solid #000;">Jenis</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tanggal Pesan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tanggal Selesai</th>
                <th style="font-weight: bold; border: 1px solid #000;">PCS</th>
                <th style="font-weight: bold; border: 1px solid #000;">Progress Step</th>
                <th style="font-weight: bold; border: 1px solid #000;">Status</th>
            </tr>
            @forelse($productionRows as $row)
                <tr>
                    <td style="border: 1px solid #000;">{{ $row['order_code'] }}</td>
                    <td style="border: 1px solid #000;">{{ $row['customer_name'] }}</td>
                    <td style="border: 1px solid #000;">{{ $row['product_name'] ?: '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['production_type'] ?: '-' }}</td>
                    <td style="border: 1px solid #000;">{{ $row['created_at'] ? \Carbon\Carbon::parse($row['created_at'])->format('d M Y') : '-' }}</td>
                    <td style="border: 1px solid #000;">{{ in_array($row['order_status'], ['completed', 'done', 'ready_for_pickup']) && $row['updated_at'] ? \Carbon\Carbon::parse($row['updated_at'])->format('d M Y') : '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $row['total_pcs'] }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ "='" . $row['step_progress'] . "'" }}</td>
                    <td style="border: 1px solid #000;">{{ str_replace('_', ' ', $row['order_status']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="border: 1px solid #000; text-align: center;">Belum ada order untuk dipantau pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
