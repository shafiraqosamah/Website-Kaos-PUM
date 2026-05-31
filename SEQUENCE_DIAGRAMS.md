# Sequence Diagrams - Website Kaos PUM

## Sequence Diagram 1: Pembuatan Pesanan & Verifikasi Admin

```mermaid
sequenceDiagram
    participant Customer
    participant Website
    participant Order as Order Database
    participant Admin
    participant Email

    Customer->>Website: Akses halaman create order
    Website->>Customer: Tampilkan form order (material, type, size, dll)

    Customer->>Website: Submit order dengan detail
    Website->>Website: Validasi data (min 60 pcs, dll)
    Website->>Order: Generate order_code & simpan Order
    Order-->>Website: Order berhasil dibuat

    Website->>Email: Kirim notifikasi order created
    Email-->>Customer: Email notifikasi order
    Website-->>Customer: Redirect ke detail order

    activate Admin
    Admin->>Website: Buka halaman admin order verification
    Website->>Order: Ambil order yang belum diverifikasi
    Order-->>Website: List order pending verification
    Website-->>Admin: Tampilkan order untuk dicek

    Admin->>Website: Review detail order & design file
    Admin->>Website: Submit verifikasi (approved/rejected)

    alt Admin Approved
        Website->>Order: Update admin_verification_status = 'approved'
        Website->>Order: Update order_status = 'ready_for_production'
        Order-->>Website: Order siap produksi
    else Admin Rejected
        Website->>Order: Update admin_verification_status = 'rejected'
        Website->>Order: Update order_status = 'rejected'
        Website->>Email: Kirim notifikasi order rejected
        Email-->>Customer: Email penolakan order
    end
    deactivate Admin
```

---

## Sequence Diagram 2: Proses Pembayaran (DP/Full) dengan Midtrans

```mermaid
sequenceDiagram
    participant Customer
    participant Website
    participant Midtrans as Midtrans Payment Gateway
    participant Order as Order Database
    participant Payment as Payment Database
    participant Finance
    participant Email

    Customer->>Website: Klik tombol "Bayar" di order detail
    Website->>Website: Hitung amount (DP 50% / Full / Settlement)
    Website->>Midtrans: Initiate payment (buat Snap token)

    Midtrans-->>Website: Return Snap token
    Website-->>Customer: Tampilkan Midtrans Snap interface

    Customer->>Midtrans: Pilih payment method & masukkan data
    Midtrans->>Midtrans: Proses transaksi

    alt Pembayaran Berhasil
        Midtrans-->>Website: Callback dengan status success
        Website->>Payment: Simpan payment record dengan status 'pending'
        Website->>Order: Update payment_status berdasarkan tipe pembayaran
        Website-->>Customer: Tampilkan konfirmasi pembayaran
        Website->>Email: Kirim notifikasi pembayaran berhasil
        Email-->>Customer: Email konfirmasi pembayaran

        activate Finance
        Finance->>Website: Buka halaman payment verification
        Website->>Payment: Ambil payment dengan status 'pending'
        Payment-->>Website: List payment untuk diverifikasi
        Website-->>Finance: Tampilkan payment details

        Finance->>Website: Verifikasi & approve payment
        Website->>Payment: Update status = 'verified'
        Website->>Order: Update payment_status & order_status
        Website->>Email: Kirim notifikasi pembayaran terverifikasi
        Email-->>Customer: Email pembayaran terverifikasi
        deactivate Finance
    else Pembayaran Gagal
        Midtrans-->>Website: Callback dengan status failed
        Website->>Payment: Update status = 'failed'
        Website-->>Customer: Tampilkan pesan error
        Website->>Email: Kirim notifikasi pembayaran gagal
        Email-->>Customer: Email gagal transaksi
    end
```

---

## Sequence Diagram 3: Proses Produksi & Tracking

```mermaid
sequenceDiagram
    participant Customer
    participant Website
    participant Order as Order Database
    participant Production as Production Database
    participant ProductionStaff
    participant Email

    activate ProductionStaff
    ProductionStaff->>Website: Buka halaman produksi dashboard
    Website->>Order: Ambil order dengan status 'ready_for_production'
    Order-->>Website: List order siap produksi
    Website-->>ProductionStaff: Tampilkan daftar order

    ProductionStaff->>Website: Klik order untuk mulai produksi
    Website->>Production: Generate WorkOrder (SPK)
    Website->>Production: Buat ProductionStep records (Sablon → Finishing → QC → Packing)
    Website->>Order: Update order_status = 'in_production'
    Production-->>Website: Produksi dimulai

    loop Tahapan Produksi
        ProductionStaff->>Website: Update status ProductionStep (started → completed)
        Website->>Production: Update ProductionStep (started_at, completed_at, updated_by)
        Website->>Order: Update order_status berdasarkan progress
        Website->>Email: Kirim update produksi ke customer
        Email-->>Customer: Email progress produksi
    end

    ProductionStaff->>Website: Tandai semua step selesai
    Website->>Order: Update order_status = 'completed'
    Website->>Email: Kirim notifikasi order selesai
    Email-->>Customer: Email order sudah jadi

    deactivate ProductionStaff

    activate Customer
    Customer->>Website: Buka halaman tracking order
    Website->>Production: Ambil ProductionStep dengan order_id
    Production-->>Website: Tampilkan timeline produksi
    Website-->>Customer: Tampilkan progress produksi (visual timeline)
    deactivate Customer
```

---

## Sequence Diagram 4: Settlement Pembayaran (Sisa Pembayaran)

```mermaid
sequenceDiagram
    participant Customer
    participant Website
    participant Order as Order Database
    participant Finance
    participant Email

    Note over Website,Finance: Ketika order hampir selesai & masih ada sisa pembayaran

    Website->>Order: Check isSettlementRequired()
    alt Ada sisa pembayaran
        Website->>Email: Kirim email settlement required
        Email-->>Customer: Email permintaan settlement

        Customer->>Website: Akses halaman order & klik "Bayar Sisa"
        Website->>Website: Hitung remaining_amount
        Note over Website: Flow sama seperti Sequence Diagram 2 (Midtrans payment)
        Website->>Order: Update remaining_amount = 0
        Website->>Order: Update payment_status = 'fully_paid'

        Finance->>Website: Verifikasi settlement payment
        Website->>Email: Kirim notifikasi pembayaran settlement terverifikasi
        Email-->>Customer: Email pembayaran lunas
    end
```

---

## Sequence Diagram 5: Pembatalan Order

```mermaid
sequenceDiagram
    participant Customer
    participant Website
    participant Order as Order Database
    participant Admin
    participant Finance
    participant Email

    Customer->>Website: Klik tombol "Cancel Order"
    Website->>Website: Check apakah order bisa dibatalkan

    alt Order masih pending verification
        Website->>Order: Update order_status = 'cancelled'
        Website-->>Customer: Order dibatalkan
    else Order sudah approved/in production
        Website->>Admin: Kirim notifikasi permintaan pembatalan
        Email-->>Admin: Email permintaan pembatalan order

        activate Admin
        Admin->>Website: Review permintaan pembatalan
        Admin->>Website: Approve/reject pembatalan

        alt Admin Approve
            Website->>Order: Update order_status = 'cancelled'
            Website->>Finance: Flag payment untuk refund (jika sudah ada pembayaran)
            Website->>Email: Kirim notifikasi order dibatalkan
            Email-->>Customer: Email order dibatalkan
        else Admin Reject
            Website->>Email: Kirim notifikasi pembatalan ditolak
            Email-->>Customer: Email pembatalan ditolak
        end
        deactivate Admin
    end
```

---

## Alur Utama Aplikasi

1. **Customer Registration & Login** → Akses dashboard customer
2. **Order Creation** → Input detail pesanan (material, jumlah, design, dll)
3. **Admin Verification** → Admin verifikasi design & detail order
4. **Payment Processing** → Customer bayar via Midtrans (DP/Full)
5. **Finance Verification** → Finance verifikasi pembayaran masuk
6. **Production** → Staf produksi eksekusi order dengan tracking step by step
7. **Settlement** (optional) → Customer bayar sisa jika belum lunas
8. **Completion** → Order selesai & customer notifikasi
9. **Order Cancellation** (optional) → Cancel dengan persetujuan admin jika diperlukan

---

## Status Order yang Digunakan

- `pending` → Menunggu verifikasi admin
- `verified` → Sudah diverifikasi admin, menunggu pembayaran
- `ready_for_production` → Pembayaran terverifikasi, siap produksi
- `in_production` → Sedang dalam proses produksi
- `completed` → Produksi selesai, siap diambil
- `rejected` → Ditolak oleh admin
- `cancelled` → Dibatalkan oleh customer

---

## Status Payment yang Digunakan

- `pending` → Pembayaran pending di Midtrans
- `verified` → Pembayaran sudah diverifikasi finance
- `failed` → Transaksi gagal
- `expired` → Link pembayaran expired

---

## Aktor Sistem

- **Customer** - Pengguna yang membuat pesanan
- **Admin** - Verifikasi design dan detail pesanan
- **Finance** - Verifikasi pembayaran yang masuk
- **Production Staff** - Eksekusi produksi dan tracking progress
- **Website** - Aplikasi Laravel yang mengatur alur bisnis
- **Midtrans** - Payment gateway untuk transaksi pembayaran
- **Email** - Sistem notifikasi ke stakeholder
