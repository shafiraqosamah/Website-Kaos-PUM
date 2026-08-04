<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $payment->invoice_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            color: #111;
            background: #f2f4f7;
            font-family: Arial, Helvetica, sans-serif;
        }
        .sheet {
            width: min(900px, 100%);
            margin: 0 auto;
            background: #fff;
            padding: 28px 34px 44px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }
        .top {
            display: flex;
            gap: 14px;
            align-items: center;
            border-bottom: 4px solid #111;
            padding-bottom: 12px;
        }
        .brand-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
            display: block;
        }
        .brand {
            min-width: 0;
            flex: 1;
        }
        .brand-title {
            font-size: 48px;
            font-weight: 800;
            color: #d60f0f;
            line-height: 1;
            letter-spacing: 0.01em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .brand-contact {
            font-size: 22px;
            letter-spacing: 0.16em;
            color: #111;
        }
        .section-title {
            text-align: center;
            margin: 18px 0 4px;
            font-size: 24px;
            font-weight: 700;
        }
        .invoice-no {
            text-align: center;
            margin-bottom: 22px;
        }
        .meta {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 22px;
        }
        .meta strong {
            display: inline-block;
            min-width: 88px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }
        th, td {
            border: 2px solid #202020;
            padding: 8px 10px;
        }
        th {
            background: #f3f3f3;
            text-align: center;
        }
        .right { text-align: right; }
        .muted { color: #555; }
        .bank-box {
            margin-top: 30px;
        }
        .footer {
            margin-top: 42px;
            display: flex;
            justify-content: flex-end;
        }
        .signature {
            width: 240px;
            text-align: center;
            line-height: 1.7;
        }
        .actions {
            width: min(900px, 100%);
            margin: 0 auto 14px;
            display: flex;
            justify-content: flex-end;
        }
        .actions button {
            border: 0;
            background: #0c1a26;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { box-shadow: none; width: 100%; }
            .actions { display: none; }
        }
        @media (max-width: 900px) {
            .top {
                align-items: flex-start;
            }
            .brand-title {
                font-size: 36px;
            }
            .brand-contact {
                font-size: 15px;
                letter-spacing: 0.1em;
            }
        }
    </style>
</head>
<body>
<div class="actions">
    <button onclick="window.print()">Cetak Invoice</button>
</div>
<div class="sheet">
    <div class="top">
        <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Logo PT Panji Usaha Mulia">
        <div class="brand">
            <div class="brand-title">PT PANJI USAHA MULIA</div>
            <div class="brand-contact">Jl. Jendral A. Yani 909 Bandung | 022 721 5924</div>
        </div>
    </div>

    <div class="section-title">{{ $invoiceTitle }}</div>
    <div class="invoice-no">NO : {{ $payment->invoice_number ?? '-' }}</div>

    <div class="meta">
        <div>
            <div><strong>Kepada Yth,</strong> {{ $order->customer_name }}</div>
            <div><strong>No.Order</strong> {{ $order->order_code }}</div>
            <div><strong>Jenis</strong> {{ $paymentLabel }}</div>
        </div>
        <div>
            <div><strong>Tanggal</strong> {{ ($payment->verified_at ?? now())->translatedFormat('d F Y') }}</div>
            <div><strong>Email</strong> {{ $order->user->email }}</div>
            <div><strong>Status</strong> TERVERIFIKASI</div>
            <div><strong>Pay Via</strong> {{ $payViaLabel }}</div>
        </div>
    </div>

    <p>Berikut kami kirimkan invoice pembayaran sebagai berikut:</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Items</th>
                <th>Harga/Pcs</th>
                <th>Qty/Pcs</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="right">1</td>
                <td>
                    {{ strtoupper($order->product_name) }}
                    <div class="muted">Bahan {{ strtoupper($order->fabric) }} | Warna {{ strtoupper($order->dominant_color) }}{{ $order->secondary_color ? ' / ' . strtoupper($order->secondary_color) : '' }}</div>
                </td>
                <td class="right">{{ number_format($order->unit_price, 0, ',', '.') }}</td>
                <td class="right">{{ $order->total_pcs }}</td>
                <td class="right">{{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="right"><strong>TOTAL ORDER</strong></td>
                <td class="right"><strong>{{ number_format($order->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="right"><strong>{{ $paymentLabel }}</strong></td>
                <td class="right"><strong>{{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="right"><strong>SISA</strong></td>
                <td class="right"><strong>{{ number_format($order->remaining_amount, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="bank-box">
        <p style="margin-bottom:8px;"><strong>Detail Pembayaran Midtrans:</strong></p>
        <div>Pay Via: {{ $payViaLabel }}</div>
        @if (!empty($payViaDetail))
            <div class="muted" style="margin-top:6px;">{{ $payViaDetail }}</div>
        @endif
        <div class="muted" style="margin-top:6px;">Transaction ID: {{ $payment->midtrans_transaction_id ?? '-' }}</div>
        <div class="muted" style="margin-top:6px;">Order ID Midtrans: {{ $payment->midtrans_order_id ?? '-' }}</div>
        @if ($payment->notes)
            <div style="margin-top:8px;" class="muted">Keterangan: {!! nl2br(e($payment->notes)) !!}</div>
        @endif
    </div>

    <div class="footer">
        <div class="signature">
            <div>PT Panji Usaha Mulia</div>
            <div>Hormat kami,</div>
            <div style="height:72px;"></div>
            <div><strong>{{ $payment->verifiedBy->name ?? 'Keuangan' }}</strong></div>
            <div class="muted">Bagian Keuangan</div>
        </div>
    </div>
</div>
</body>
</html>
