# Class Diagram - Website Kaos PUM

Dokumen ini berisi Class Diagram sistem **Website Kaos PUM** berbasis Laravel, yang disederhanakan untuk hanya mencakup **Model data (Entitas Database)** dan relasinya. Ini adalah format standar yang umum digunakan untuk penulisan laporan skripsi.

```mermaid
---
id: 16149325-5689-48ad-934f-6d53e5c373d8
---
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string phone
        +string company_name
        +string role
        +string password
        -const ROLE_ADMIN = "admin"
        -const ROLE_CUSTOMER = "customer"
        -const ROLE_FINANCE = "finance"
        -const ROLE_PRODUCTION = "production"
        +staffRoles() array
        +hasRole(roles) bool
    }

    class Order {
        +int id
        +int user_id
        +string order_code
        +string customer_name
        +string product_name
        +int total_pcs
        +string fabric
        +string production_type
        +string product_model
        +string sleeve_type
        +string dominant_color
        +string secondary_color
        +string design_file
        +string design_front_file
        +string design_back_file
        +string design_notes
        +date estimated_finish_date
        +decimal unit_price
        +decimal subtotal
        +string payment_type
        +decimal dp_amount
        +decimal remaining_amount
        +string payment_status
        +string order_status
        +string admin_verification_status
        +string admin_verification_note
        +int admin_verified_by
        +datetime admin_verified_at
        +string notes
        +isSettlementRequired() bool
    }

    class Payment {
        +int id
        +int order_id
        +string method
        +string invoice_number
        +datetime invoiced_at
        +decimal amount
        +string status
        +int verified_by
        +datetime verified_at
        +string notes
        +string midtrans_order_id
        +string midtrans_transaction_id
        +string midtrans_status
        +string midtrans_payment_type
        +string midtrans_fraud_status
        +array midtrans_response
    }

    class WorkOrder {
        +int id
        +int order_id
        +string spk_number
        +int issued_by
        +datetime issued_at
        +string status
    }

    class ProductionStep {
        +int id
        +int order_id
        +int step_order
        +string step_name
        +string status
        +datetime started_at
        +datetime completed_at
        +int updated_by
    }

    class OrderItemSize {
        +int id
        +int order_id
        +string size_name
        +int qty
    }

    class Material {
        +int id
        +string name
        +string slug
        +int base_price
        +int sort_order
        +bool is_active
    }

    class Color {
        +int id
        +string name
        +string slug
        +string hex_code
        +string gradient_css
        +string swatch_image_path
        +int sort_order
        +bool is_active
    }

    class Setting {
        +int id
        +string key
        +string value
    }

    %% Relasi Eloquent Antar Model (Relasi Database)
    User "1" -- "*" Order : owns_orders
    Order "1" -- "*" Payment : payments
    Order "1" -- "1" WorkOrder : workOrder
    Order "1" -- "*" ProductionStep : productionSteps
    Order "1" -- "*" OrderItemSize : sizes
    Material "*" -- "*" Color : has_colors

    %% Relasi Tambahan Berdasarkan Foreign Key
    User "1" -- "*" Order : adminVerifiedBy
    User "1" -- "*" Payment : verifiedBy
    User "1" -- "*" WorkOrder : issuer
    User "1" -- "*" ProductionStep : updater
```

## Penjelasan Relasi & Struktur:

- **User**: Pengguna sistem yang memiliki otorisasi berbeda berdasarkan atribut `role` (Admin, Customer, Finance, Production).
- **Order**: Model pusat yang mencatat data transaksi pemesanan kaos custom.
- **OrderItemSize**: Detail kuantitas per ukuran baju (S, M, L, XL, dll.) untuk setiap pesanan.
- **Payment**: Transaksi pembayaran terkait pesanan yang terintegrasi secara otomatis menggunakan payment gateway Midtrans.
- **WorkOrder**: Surat Perintah Kerja (SPK) produksi yang diterbitkan saat pesanan terverifikasi.
- **ProductionStep**: Jalur pelacakan status produksi pesanan step-by-step (Cutting, Jahit, Sablon, Steam, Finishing).
- **Material & Color**: Master data katalog bahan dan warna kaos yang dikelola admin dan ditampilkan di landing page. Relasi Many-to-Many antara Material dan Color direpresentasikan di tingkat database relasional melalui tabel perantara (pivot table) bernama `material_colors` (yang menyimpan foreign key `material_id` dan `color_id` serta atribut pengurutan `sort_order`).
- **Setting**: Konfigurasi umum sistem (misalnya batas waktu auto-cancel pesanan).
