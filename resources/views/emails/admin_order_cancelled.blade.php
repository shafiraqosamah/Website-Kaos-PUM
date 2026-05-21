<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #d95f18; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fdfdfd; padding: 30px; border: 1px solid #e2e8f0; border-top: 0; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #64748b; }
        .info-box { background: #fee8e8; border-left: 4px solid #9a1d1d; padding: 15px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table th { width: 40%; color: #64748b; font-weight: normal; }
        .btn { display: inline-block; background: #0f2947; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Pesanan Dibatalkan Oleh Sistem</h2>
        </div>
        <div class="content">
            <p>Halo Admin {{ strtolower($admin->name) }},</p>
            
            <p>Sistem telah <strong>membatalkan otomatis</strong> pesanan berikut karena telah melewati batas waktu tunggu yang telah ditetapkan.</p>

            <div class="info-box">
                <h4 style="margin: 0 0 10px 0; color: #9a1d1d;">Alasan Pembatalan:</h4>
                <p style="margin: 0;">{{ $order->admin_verification_note ?? 'Melewati batas waktu verifikasi atau pelunasan.' }}</p>
            </div>

            <table class="table">
                <tr>
                    <th>Nomor Pesanan</th>
                    <td><strong>{{ $order->order_code }}</strong></td>
                </tr>
                <tr>
                    <th>Nama Pelanggan</th>
                    <td>{{ $order->customer_name ?: ($order->user->name ?? 'Guest') }}</td>
                </tr>
                <tr>
                    <th>Email Pelanggan</th>
                    <td>{{ $order->user->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Waktu Dibuat</th>
                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Waktu Dibatalkan</th>
                    <td>{{ now()->format('d M Y H:i') }}</td>
                </tr>
            </table>

            <p style="margin-top: 25px;">Sistem juga telah mengirimkan pemberitahuan pembatalan ke email pelanggan terkait. Anda dapat melihat detail pesanan melalui dashboard admin.</p>
            
            <center>
                <a href="{{ route('reports.orders.show', $order) }}" class="btn">Lihat Detail Pesanan</a>
            </center>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PT Panji Usaha Mulia - Automated System Notification
        </div>
    </div>
</body>
</html>
