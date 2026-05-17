<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembayaran Ditolak</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Halo {{ $order->customer_name }},</h2>
    <p>Mohon maaf, bukti pembayaran yang Anda unggah untuk pesanan <strong>{{ $order->order_code }}</strong> tidak dapat kami verifikasi.</p>
    
    <div style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Catatan dari Finance:</h3>
        <p>{{ $payment->notes ?: 'Bukti pembayaran tidak valid atau nominal tidak sesuai.' }}</p>
    </div>

    <p>Silakan melakukan pembayaran ulang dan mengunggah bukti transfer yang benar melalui tautan di bawah ini.</p>
    
    <p><em>Penting: Apabila Anda tidak melakukan pembayaran ulang dalam batas waktu 2x24 jam sejak pesanan disetujui, sistem kami akan membatalkan pesanan secara otomatis.</em></p>

    <div style="margin-top: 30px; text-align: center;">
        <a href="{{ route('customer.orders.payments.edit', [$order, $payment]) }}" style="background-color: #b63b22; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Upload Ulang Bukti Pembayaran</a>
    </div>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #888;">Ini adalah pesan otomatis dari sistem PT Panji Usaha Mulia. Mohon tidak membalas email ini.</p>
</body>
</html>
