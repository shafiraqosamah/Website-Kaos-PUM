<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesanan Berhasil Dibuat</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Halo {{ $order->customer_name }},</h2>
    <p>Terima kasih telah mempercayakan pembuatan kaos Anda kepada PT Panji Usaha Mulia!</p>
    
    <div style="background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #0d2749;">Detail Pesanan</h3>
        <p><strong>Nomor Pesanan:</strong> {{ $order->order_code }}</p>
        <p><strong>Model Produk:</strong> {{ $order->product_model }}</p>
        <p><strong>Bahan:</strong> {{ $order->fabric }}</p>
        <p><strong>Total Jumlah:</strong> {{ $order->total_pcs }} pcs</p>
        <p><strong>Total Tagihan:</strong> Rp {{ number_format($order->subtotal, 0, ',', '.') }}</p>
    </div>

    <p>Pesanan Anda saat ini sedang dalam status <strong>Menunggu Verifikasi Admin</strong>. Admin kami akan memeriksa detail pesanan Anda paling lambat <strong>2x24 Jam</strong> ke depan.</p>
    
    <p>Setelah diverifikasi, Anda akan mendapatkan email pemberitahuan lebih lanjut beserta panduan pembayaran (DP/Pelunasan Awal) agar pesanan dapat segera diproses ke tahap produksi.</p>

    <div style="margin-top: 30px; text-align: center;">
        <a href="{{ route('customer.orders.show', $order) }}" style="background-color: #0d2749; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Lihat Detail Pesanan</a>
    </div>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #888;">Ini adalah pesan otomatis dari sistem PT Panji Usaha Mulia. Mohon tidak membalas email ini.</p>
</body>
</html>
