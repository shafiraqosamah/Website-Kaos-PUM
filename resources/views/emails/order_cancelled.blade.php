<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesanan Dibatalkan</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Halo {{ $order->customer_name }},</h2>
    <p>Kami ingin memberitahukan bahwa pesanan Anda dengan nomor <strong>{{ $order->order_code }}</strong> telah <strong>dibatalkan</strong> oleh sistem kami.</p>
    
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Alasan Pembatalan:</h3>
        <p>{{ $order->admin_verification_note ?: 'Melewati batas waktu verifikasi atau pembayaran (2x24 Jam).' }}</p>
    </div>

    <p>Apabila Anda masih berminat untuk melakukan pemesanan, silakan membuat pesanan baru melalui website kami.</p>

    <div style="margin-top: 30px; text-align: center;">
        <a href="https://wa.me/6281234567890" style="background-color: #25D366; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Hubungi WhatsApp Admin</a>
    </div>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #888;">Ini adalah pesan otomatis dari sistem PT Panji Usaha Mulia. Mohon tidak membalas email ini.</p>
</body>
</html>
