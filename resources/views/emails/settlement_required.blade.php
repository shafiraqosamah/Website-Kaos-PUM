<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Waktunya Pelunasan</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Halo {{ $order->customer_name }},</h2>
    <p>Pesanan kaos Anda dengan nomor <strong>{{ $order->order_code }}</strong> telah selesai melalui tahap Steam & Pressing dan sekarang siap untuk tahap Finishing!</p>
    
    <div style="background-color: #e8f4fd; border: 1px solid #b8daff; color: #004085; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Sisa Tagihan: Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</h3>
        <p><strong>Batas Waktu Pelunasan:</strong> {{ $order->payment_deadline_at ? \Carbon\Carbon::parse($order->payment_deadline_at)->translatedFormat('l, d F Y H:i') : '-' }}</p>
    </div>

    <p>Tahapan Finishing (pengemasan dan penyelesaian akhir) <strong>tidak dapat dilanjutkan</strong> sebelum pelunasan Anda terverifikasi oleh tim Finance kami.</p>

    <p><em>Penting: Apabila Anda tidak melakukan pelunasan melewati batas waktu 5x24 jam (sejak tahap steam selesai), pesanan Anda akan otomatis ditahan oleh sistem.</em></p>

    <div style="margin-top: 30px; text-align: center;">
        <a href="{{ route('dashboard') }}" style="background-color: #0d2749; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Lakukan Pelunasan Sekarang</a>
    </div>

    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
    <p style="font-size: 12px; color: #888;">Ini adalah pesan otomatis dari sistem PT Panji Usaha Mulia. Mohon tidak membalas email ini.</p>
</body>
</html>
