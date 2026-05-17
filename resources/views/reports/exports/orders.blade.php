<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemesanan</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="8" style="text-align: center; font-size: 16px; font-weight: bold;">LAPORAN PEMESANAN PELANGGAN</th>
            </tr>
            <tr>
                <th colspan="8" style="text-align: center;">Periode: {{ $monthLabel }}</th>
            </tr>
            <tr>
                <th colspan="8"></th>
            </tr>
            <tr>
                <th style="font-weight: bold; border: 1px solid #000; text-align: center;">No</th>
                <th style="font-weight: bold; border: 1px solid #000;">No. Order</th>
                <th style="font-weight: bold; border: 1px solid #000;">Pemesan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Tanggal Pesan</th>
                <th style="font-weight: bold; border: 1px solid #000;">Jumlah PCS</th>
                <th style="font-weight: bold; border: 1px solid #000;">Total Harga</th>
                <th style="font-weight: bold; border: 1px solid #000;">Produk</th>
                <th style="font-weight: bold; border: 1px solid #000;">Status Produksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td style="border: 1px solid #000; text-align: center;">{{ $loop->iteration }}</td>
                    <td style="border: 1px solid #000;">{{ $order->order_code }}</td>
                    <td style="border: 1px solid #000;">{{ $order->customer_name ?: ($order->user->name ?? '-') }}</td>
                    <td style="border: 1px solid #000;">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $order->total_pcs }}</td>
                    <td style="border: 1px solid #000; text-align: right;">{{ $order->subtotal }}</td>
                    <td style="border: 1px solid #000;">{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                    <td style="border: 1px solid #000;">{{ \App\Support\OrderStatusPresenter::customerLabel($order->order_status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="border: 1px solid #000; text-align: center;">Belum ada data pesanan pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
