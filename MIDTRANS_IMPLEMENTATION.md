# Midtrans Sandbox Integration - Implementasi Lengkap

## ✅ Implementasi Selesai

### 1. Configuration

- `config/midtrans.php` - Konfigurasi Midtrans dari environment
- `.env` dengan Midtrans keys:
    - MIDTRANS_MERCHANT_ID
    - MIDTRANS_CLIENT_KEY
    - MIDTRANS_SERVER_KEY
    - MIDTRANS_IS_PRODUCTION=false (Sandbox mode)

### 2. Database

- Migration: `2026_03_29_000000_add_midtrans_fields_to_payments_table.php`
- Columns:
    - `midtrans_order_id` - Order ID untuk Midtrans
    - `midtrans_transaction_id` - Transaction ID dari Midtrans
    - `midtrans_status` - Status dari Midtrans (settlement, pending, deny, etc)
    - `midtrans_payment_type` - Tipe pembayaran (bank_transfer, gopay, dll)
    - `midtrans_fraud_status` - Status fraud detection
    - `midtrans_response` - JSON response dari Midtrans API

### 3. Models

- **Payment** (`app/Models/Payment.php`)
    - Updated `$fillable` dengan Midtrans fields
    - Updated `casts()` untuk `midtrans_response` as array

### 4. Controller

- **MidtransController** (`app/Http/Controllers/MidtransController.php`)
    - `initiatePayment()` - Create Snap token untuk payment
    - `checkStatus()` - Check transaction status from Midtrans API
    - `notification()` - Webhook handler untuk Midtrans notifications
    - Helper methods untuk mapping status

### 5. Routes

```php
POST /midtrans/notification     - Webhook public (no auth)
POST /midtrans/initiate         - Create Snap token (auth required)
POST /midtrans/check-status     - Check payment status (auth required)
```

### 6. Frontend

- **payment.blade.php** (`resources/views/customer/orders/payment.blade.php`)
    - Midtrans Snap JS library loaded
    - Payment method tabs:
        - 💳 Midtrans Online (default)
        - 🏦 Transfer Manual (fallback)
    - Midtrans section:
        - Amount calculation (DP 50% atau Full)
        - "Klik Bayar Sekarang" button
        - Status info display
    - JavaScript handler:
        - Tab switching
        - Snap token request
        - Snap popup trigger
        - Status checking after payment

## 🧪 How to Test

### Prerequisites

1. Akun Midtrans sudah terdaftar
2. Sandbox keys sudah di-setup di `.env` (sudah done)
3. Laravel app running via `php artisan serve` atau XAMPP

### Test Flow

#### 1. Login sebagai Customer

```
URL: http://localhost:8000/login
Username: customer account
```

#### 2. Buat atau buka Order

```
URL: http://localhost:8000/customer/orders
Buat order baru atau klik order yang ada
```

#### 3. Akses halaman Payment

```
URL: http://localhost:8000/customer/orders/{order_id}/payments/{payment_id}
Pastikan sudah ada payment record
```

#### 4. Masuk ke tab "💳 Midtrans Online"

- Pilih DP 50% atau Lunas
- Amati jumlah yang harus dibayar ter-update
- Klik "Klik Bayar Sekarang"

#### 5. Snap Popup akan muncul

- Pilih payment method (VA / e-wallet / dll)
- Isi sesuai sandbox test credentials
- Simulasi pembayaran berhasil/gagal

#### 6. Form akan diupdate

- Status info akan menampilkan konfirmasi
- Payment record di database akan update dengan:
    - `midtrans_order_id`
    - `midtrans_transaction_id`
    - `midtrans_status`
    - `midtrans_response`

### Sandbox Test Credentials

**Virtual Account (BCA)**

- Account: 91019129801
- Amount: berapa saja (dalam range yang diminta)
- Klik "Pay Directly"

**Credit Card**

- Number: 4811 1111 1111 1114
- Exp: 12/25
- CVV: 123
- OTP: 112233

**Gopay**

- Akan langsung redirect ke simulasi OTP
- OTP: berapa saja

**QRIS**

- Akan menampilkan QRIS code untuk di-scan

**Reference:** https://docs.midtrans.com/en/technical-reference/sandbox-test-credentials

## ⚠️ Important Notes

### Current Setup (Sandbox/Dummy)

- ✅ Payment dapat dibuat dan tercatat di database
- ✅ Snap popup berfungsi
- ✅ Status check bekerja tanpa webhook
- ⚠️ Webhook notification tidak otomatis (perlu ngrok/public URL)

### Manual Status Check

- Customer/Admin bisa klik "Cek Status" untuk pull status terbaru dari Midtrans
- Atau refresh halaman payment untuk check status terbaru

## 🚀 Production Checklist

Saat akan production:

1. **Update .env:**

    ```env
    MIDTRANS_IS_PRODUCTION=true
    MIDTRANS_CLIENT_KEY=Mid-prod-xxxxx
    MIDTRANS_SERVER_KEY=Mid-prod-xxxxx
    ```

2. **Setup Publik URL untuk Webhook:**
    - Install & run ngrok / Cloudflare Tunnel
    - Atau deploy ke server dengan domain publik
    - Update Notification URL di Midtrans Dashboard

3. **Activate Webhook Handler:**
    - Uncomment notification logic di MidtransController
    - Verify signature via openssl_digest

4. **Testing Production:**
    - Test dengan real payment methods
    - Monitor Midtrans Dashboard untuk transactions
    - Check logs di storage/logs/

## 📊 Status Mapping

| Midtrans Status      | Payment Status | Order Impact                     |
| -------------------- | -------------- | -------------------------------- |
| settlement, capture  | verified       | Payment marked paid              |
| pending              | pending        | Awaiting payment confirmation    |
| deny, cancel, expire | rejected       | Payment failed, customer retries |

## 🔗 Bank Accounts Tujuan

Dari Payment model `DESTINATION_BANKS`:

- BCA: 123-456-7891 a.n. Keuangan
- BNI: 123-456-7892 a.n. Keuangan
- BRI: 123-456-7892 a.n. Keuangan
- Mandiri: 123-456-7893 a.n. Keuangan

_Note: Ganti dengan nomor rekening nyata saat production_

## 📝 Finance Module Integration

- Payment status dari Midtrans akan auto-update `payments.status`
- Order `payment_status` akan update berdasarkan payment method:
    - DP 50%: `payment_status = 'partial'`
    - Full/Settlement: `payment_status = 'paid'`
- Finance module tetap berfungsi normal untuk:
    - Manual payment verification alternative
    - Invoice generation
    - Financial reporting

## 🐛 Troubleshooting

### Snap popup tidak muncul

- Check browser console untuk error
- Verify Midtrans Snap JS library loaded
- Check `config('midtrans.client_key')` tidak null

### Amount tidak sesuai

- Check data-subtotal di payment breakdown element
- Verify order subtotal di database

### Status tidak terupdate

- Check payment record punya `midtrans_order_id`
- Klik tombol "Cek Status" untuk manual sync
- Monitor network tab untuk POST /midtrans/check-status

### Database migration error

- Verify `payments` table exists
- Run `php artisan migrate` tanpa --force
- Check migration log di database
