<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesanan Lunas</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Halo {{ $order->customer_name }},</h2>
    <p>Kabar gembira! Pembayaran untuk pesanan <strong>{{ $order->order_code }}</strong> telah berhasil diverifikasi dan dinyatakan lunas.</p>
    
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Status: LUNAS</h3>
        <p><strong>Nominal Dibayar:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
        <p><strong>Nomor Invoice:</strong> {{ $payment->invoice_number }}</p>
    </div>

    <p>Pesanan Anda kini akan dilanjutkan ke tahapan produksi/penyelesaian. Anda dapat memantau status produksi melalui Dashboard akun Anda atau fitur Lacak Pesanan.</p>

    <div style="margin-top: 30px; text-align: center;">
        <a href="{{ route('customer.orders.show', $order) }}" style="background-color: #28a745; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Cek Status Pesanan</a>
    </div>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #888;">Ini adalah pesan otomatis dari sistem PT Panji Usaha Mulia. Mohon tidak membalas email ini.</p>
</body>
</html>
