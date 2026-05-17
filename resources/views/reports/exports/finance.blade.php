<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="14" style="text-align: center; font-size: 16px; font-weight: bold;">LAPORAN KEUANGAN (PEMBUKUAN)</th>
            </tr>
            <tr>
                <th colspan="14" style="text-align: center;">Periode: {{ $monthLabel }}</th>
            </tr>
            <tr>
                <th colspan="14"></th>
            </tr>
            <tr>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">No</th>
                <th style="font-weight: bold; border: 1px solid #000;">Nama</th>
                <th style="font-weight: bold; border: 1px solid #000;">Pesanan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Qty</th>
                <th style="font-weight: bold; border: 1px solid #000;">Harga @</th>
                <th style="font-weight: bold; border: 1px solid #000;">Total Tagihan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Jumlah Masuk</th>
                <th style="font-weight: bold; border: 1px solid #000;">DP</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tgl DP</th>
                <th style="font-weight: bold; border: 1px solid #000;">Pelunasan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tgl Pelunasan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Lunas Awal</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tgl Lunas Awal</th>
                <th style="font-weight: bold; border: 1px solid #000;">Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledgerByOrder as $index => $group)
                <tr>
                    <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ $group['customer_name'] }}</td>
                    <td style="border: 1px solid #000;">{{ $group['order_code'] }} - {{ $group['product'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['qty'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['unit_price'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['order_subtotal'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['verified_total'] }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['dp_verified'] }}</td>
                    <td style="border: 1px solid #000;">{{ $group['dp_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['settlement_verified'] }}</td>
                    <td style="border: 1px solid #000;">{{ $group['settlement_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['full_verified'] }}</td>
                    <td style="border: 1px solid #000;">{{ $group['full_verified_at']?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $group['remaining'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="border: 1px solid #000; text-align: center;">Belum ada data omset pada bulan ini.</td>
                </tr>
            @endforelse
            <tr>
                <th colspan="14"></th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: right; font-weight: bold;">TOTAL TAGIHAN:</th>
                <th style="font-weight: bold; text-align: right;">{{ $ledgerSummary['order_subtotal'] }}</th>
                <th colspan="8"></th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: right; font-weight: bold;">TOTAL KAS MASUK TERVERIFIKASI:</th>
                <th style="font-weight: bold; text-align: right;">{{ $ledgerSummary['verified_total'] }}</th>
                <th colspan="8"></th>
            </tr>
            <tr>
                <th colspan="5" style="text-align: right; font-weight: bold;">TOTAL PIUTANG:</th>
                <th style="font-weight: bold; text-align: right;">{{ $ledgerSummary['receivable'] }}</th>
                <th colspan="8"></th>
            </tr>
        </tbody>
    </table>
</body>
</html>
