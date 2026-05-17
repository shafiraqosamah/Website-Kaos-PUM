@extends('layouts.app')

@section('content')
@php
    $primaryAction = auth()->check()
        ? (auth()->user()->hasRole('customer') ? route('customer.orders.create') : route('dashboard'))
        : route('register');

    $secondaryAction = auth()->check() ? route('dashboard') : route('login');

    $consultationLink = 'https://wa.me/6282129287094?text=Halo%20saya%20ingin%20konsultasi%20produksi%20kaos%20custom';

    $landingStats = [
        ['value' => '10+', 'label' => 'Tahun pengalaman produksi'],
        ['value' => '1000+', 'label' => 'Pelanggan yang dilayani'],
        ['value' => '30 hari', 'label' => 'Estimasi produksi maksimal'],
        ['value' => '60 pcs', 'label' => 'Minimal order custom'],
    ];

    $landingServices = [
        [
            'theme' => 'light',
            'icon' => 'images/icon/iconclothing.png',
            'title' => 'Produksi Kaos Custom',
            'text' => 'Kami memproduksi berbagai jenis kaos sesuai kebutuhan Anda, di antaranya:',
            'items' => ['Kaos oblong', 'Kaosdistro', 'Polo Shirt', 'Kaos panitia & komunitas', 'Merchandise apparel custom'],
        ],
        [
            'theme' => 'teal',
            'icon' => 'images/icon/iconsablon.png',
            'title' => 'Sablon & Bordir',
            'text' => 'Tersedia berbagai teknik cetak dan bordir untuk brand Anda',
            'items' => [
                'Sablon Manual: Cocok untuk pesanan terbatas dengan hasil warna tajam dan tahan lama.',
                'Sablon DTF: Cetak full color dan gradasi tanpa batas.',
                'Printing: Warna meresap sempurna dan anti luntur untuk desain cetak seluruh baju.',
                'Bordir: Jahitan tebal, rapi, dan presisi.',
            ],
        ],
        [
            'theme' => 'light',
            'icon' => 'images/icon/iconorder.png',
            'title' => 'Produksi Brand Retail & Clothing',
            'text' => 'Layanan produksi untuk brand clothing, perusahaan, instansi, dan event organizer dari skala kecil sampai besar.',
            'items' => ['Menerima pesanan partai besar (Big Order)', 'Produksi cepat dan tepat waktu', 'FOB: Kami tangani semuanya, dari bahan hingga produk jadi','Kerahasiaan desain brand sangat terjamin.'],
        ],
    ];

    $landingSteps = [
        ['step' => '01', 'icon' => 'images/icon/loginicon.jpg', 'title' => 'Masuk / Daftar Akun', 'text' => 'Buat akun baru atau login jika sudah terdaftar. Lengkapi data penting seperti nama, email, nomor WhatsApp, dan informasi profil lainnya.'],
        ['step' => '02', 'icon' => 'images/icon/formicon.jpg', 'title' => 'Buat Pesanan Detail', 'text' => 'Klik Buat Pesanan, isi form sesuai kebutuhan, unggah gambar desain, lalu tentukan tanggal estimasi. Pesanan akan masuk tahap verifikasi terlebih dahulu.'],
        ['step' => '03', 'icon' => 'images/icon/paymenticon.jpg', 'title' => 'Pilih Skema Pembayaran', 'text' => 'Tentukan metode pembayaran sesuai kebutuhan: DP 50% di awal atau langsung lunas di awal pemesanan.'],
        ['step' => '04', 'icon' => 'images/icon/ambilicon.jpg', 'title' => 'Quality Control & Pengambilan', 'text' => 'Tim melakukan quality control akhir untuk memastikan hasil sesuai standar sebelum pesanan diambil oleh pelanggan.'],
        ['step' => '05', 'icon' => 'images/icon/finishing.jpg', 'title' => 'Pelunasan Saat Finishing', 'text' => 'Ketika pesanan masuk tahap finishing, sistem akan memberi notifikasi bila pembayaran belum lunas agar segera dilakukan pelunasan.'],
        ['step' => '06', 'icon' => 'images/icon/produksiicon.jpg', 'title' => 'Produksi Dimulai', 'text' => 'Setelah pembayaran terkonfirmasi, tim kami langsung memproses produksi berdasarkan rincian pesanan dan target estimasi tanggal yang dipilih.'],

    ];

    $landingTestimonials = [
        ['name' => 'Ardiansyah Nazarudin', 'role' => 'Client', 'rating' => 5, 'text' => 'Bahan kaosnya bagus, halus, dan nyaman. Warna sablon tetap oke dipakai berulang dan hasilnya sesuai ekspektasi.'],
        ['name' => 'Bakti Baskoro', 'role' => 'Client', 'rating' => 5, 'text' => 'Service excellent. Order polo selesai cepat, kualitas bahan, jahitan, dan bordir sangat rapi. Harga juga bersaing.'],
        ['name' => 'Ibu Luh', 'role' => 'Client', 'rating' => 5, 'text' => 'Saya jika ada tender selalu memesan di PT Panji Usaha Mulia, pelayanannya memuaskan.'],
        ['name' => 'Agung', 'role' => 'Client', 'rating' => 5, 'text' => 'Konveksi amanah, pilihan beraneka ragam bahan sama jenis sablon, memuaskan.'],
    ];

    $landingFaqs = [
        [
            'question' => 'Berapa minimal pesanan di PT Panji Usaha Mulia?',
            'answer' => 'Minimal pemesanan yang dapat diproses adalah sebanyak 5 lusin atau 60 pcs untuk setiap satu desain.',
        ],
        [
            'question' => 'Bagaimana cara melakukan pemesanan kaos kustom?',
            'answer' => 'Seluruh proses pemesanan dilakukan melalui sistem website kami. Anda cukup membuat akun, mengisi formulir spesifikasi pakaian, dan mengunggah gambar desain yang diinginkan. Lihat pada cara melakukan pemesanan.',
        ],
        [
            'question' => 'Bagaimana sistem pembayaran yang berlaku?',
            'answer' => 'Pembayaran dilakukan dalam dua tahapan menggunakan sistem payment gateway. Tahap pertama adalah pembayaran uang muka sebesar 50% di awal pemesanan. Tahap kedua adalah pelunasan sisa tagihan saat pesanan berstatus finishing.',
        ],
        [
            'question' => 'Berapa lama waktu pengerjaan produksi?',
            'answer' => 'Waktu produksi maksimal adalah 30 hari. Anda bisa memilih sendiri estimasi tanggal selesai antara 10 hari setelah tanggal pemesanan hingga lebih dari 30 hari, dan kami akan menyesuaikannya dengan jumlah pesanan Anda.',
        ],
        [
            'question' => 'Apakah pesanan yang sudah selesai bisa dikirim ke alamat pelanggan?',
            'answer' => 'Tidak. Seluruh pesanan yang telah selesai diproduksi dan berstatus lunas wajib diambil secara langsung oleh pelanggan.',
        ],
        [
            'question' => 'Apakah ada batas waktu pembayaran?',
            'answer' => 'Seluruh verifikasi dan juga batas waktu diberikan rentang 2x24 jam, jika tidak dilakukan sesuai waktu yang telah ditentukan maka pesanan dibatalkan otomatis oleh sistem.',
        ],
    ];

    $clientLogoImage = 'images/icon/iconclient.png';

    $featuredProducts = $products;
@endphp

<style>
    .landing-page {
        color: #10213a;
        overflow: clip;
    }

    .landing-shell {
        display: grid;
        gap: 0;
    }

    .hero,
    .section-block,
    .cta-strip,
    .footer {
        position: relative;
        overflow: visible;
        border-radius: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
        backdrop-filter: none;
    }

    .hero {
        min-height: clamp(300px, 40vw, 480px);
        padding: clamp(0.8rem, 1.8vw, 1.2rem);
        overflow: hidden;
        background: linear-gradient(135deg, #08192f 0%, #0f2b4d 42%, #184f88 100%);
        color: #ffffff;
        border-top: 0;
        border-left: 0;
        border-right: 0;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        margin-top: -18px;
    }

    .hero::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 0;
        background: transparent;
    }

    .hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 0;
        background: url('{{ asset('images/homepage.png') }}') right center / cover no-repeat;
        opacity: 1;
    }

    .hero-grid {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        min-height: auto;
    }

    .hero-copy {
        padding: clamp(4rem, 10vw, 6.4rem) clamp(0.1rem, 1.2vw, 0.55rem) clamp(0.1rem, 1.2vw, 0.55rem);
        max-width: 780px;
    }

    .hero-badge {
        display: block;
        margin: 0.9rem 0 0;
        padding: 0;
        border: 0;
        background: transparent;
        color: #20325d;
        font-size: clamp(1rem, 1.8vw, 1.5rem);
        letter-spacing: 0;
        text-transform: none;
        font-weight: 700;
        animation: rise 0.8s ease both;
    }

    .hero-note {
        margin: 0.7rem 0 0;
        max-width: 700px;
        color: #425466;
        font-size: 1rem;
        line-height: 1.65;
        animation: rise 0.82s ease both;
    }

    .hero-copy h1 {
        margin: 0;
        display: block;
        max-width: none;
        white-space: nowrap;
        color: #10213a;
        font-family: 'Playfair Display', serif;
        font-size: clamp(2rem, 4vw, 4.2rem);
        line-height: 0.92;
        letter-spacing: -0.02em;
        animation: rise 0.85s ease 0.08s both;
    }

    .hero-copy .highlight {
        color: #d95f18;
    }

    .hero-lead {
        margin: 1.1rem 0 0;
        max-width: 720px;
        color: #425466;
        font-size: clamp(0.95rem, 1.45vw, 1.08rem);
        line-height: 1.7;
        animation: rise 0.85s ease 0.16s both;
    }

    .hero-points {
        display: grid;
        gap: 0.72rem;
        margin: 1.25rem 0 0;
        padding: 0;
        list-style: none;
        animation: rise 0.85s ease 0.22s both;
    }

    .hero-points li {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        color: #2f3f52;
        font-weight: 600;
        line-height: 1.55;
    }

    .hero-points li::before {
        content: "";
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #dfbf65;
        flex: 0 0 auto;
        margin-top: 0.44rem;
    }

    .hero-actions {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
        margin-top: 1rem;
        animation: rise 0.85s ease 0.3s both;
    }

    .hero-actions .btn-brand {
        background: #dfbf65;
        color: #0d2749;
        font-weight: 800;
    }

    .hero-actions .btn-alt {
        background: transparent;
        border: 1px solid rgba(223, 191, 101, 0.75);
        color: #dfbf65;
    }

    .hero-stats,
    .company-stats {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.9rem;
        margin-top: 1rem;
    }

    .hero-stat,
    .service-card,
    .value-card,
    .step-card,
    .portfolio-card,
    .featured-card,
    .testimonial-card {
        border-radius: 22px;
        border: 1px solid #dfe7ee;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 14px 35px rgba(12, 33, 55, 0.06);
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
    }

    .hero-stat:hover,
    .service-card:hover,
    .value-card:hover,
    .step-card:hover,
    .portfolio-card:hover,
    .featured-card:hover,
    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 22px 44px rgba(12, 33, 55, 0.12);
        border-color: rgba(217, 95, 24, 0.16);
    }

    .hero-stat {
        padding: 0.95rem;
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(194, 212, 233, 0.16);
        color: #edf4fb;
        backdrop-filter: blur(10px);
    }

    .hero-stat strong {
        display: block;
        margin-bottom: 0.35rem;
        color: #dfbf65;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.2rem, 2.5vw, 2rem);
        line-height: 1;
    }

    .hero-stat span {
        color: #d3deeb;
        font-size: 0.84rem;
        line-height: 1.5;
    }

    .company-stats .hero-stat {
        background: #ffffff;
        border-color: #dfe7ee;
        color: #11233a;
        backdrop-filter: none;
    }

    .company-stats .hero-stat strong {
        color: #0f2947;
    }

    .company-stats .hero-stat span {
        color: #5c7083;
    }

    .section-block {
        padding: clamp(1.2rem, 3vw, 2rem);
        scroll-margin-top: 120px;
        width: min(100% - 1.4rem, 1220px);
        margin-inline: auto;
    }

    .section-intro {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.15rem;
    }

    .section-kicker {
        margin: 0 0 0.35rem;
        color: #b77e27;
        font-size: 0.84rem;
        font-weight: 800;
        letter-spacing: 0.11em;
        text-transform: uppercase;
    }

    .section-title {
        margin: 0;
        font-size: clamp(1.75rem, 3vw, 2.75rem);
        color: #11233a;
        text-wrap: balance;
    }

    .section-lead {
        margin: 0.35rem 0 0;
        max-width: 760px;
        color: #587087;
        line-height: 1.7;
    }

    .section-divider {
        display: block;
        width: 88px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #dfbf65, #d95f18);
        margin-top: 0.75rem;
    }

    .section-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #0f4e74;
        text-decoration: none;
        font-weight: 800;
        font-size: 0.92rem;
    }

    .section-link:hover {
        color: #d95f18;
    }

    .about-grid,
    .services-grid,
    .portfolio-grid,
    .featured-grid,
    .steps-grid,
    .testimonials-grid,
    .footer-grid {
        display: grid;
        gap: 1rem;
    }

    .about-showcase {
        display: grid;
        grid-template-columns: 0.82fr 1.18fr;
        align-items: center;
        gap: 1.3rem;
        overflow: visible;
        border-radius: 0;
        background: transparent;
        border: 0;
    }

    .about-media {
        min-height: 240px;
    }

    .about-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 18px;
        transform-origin: 52% 48%;
        animation: shirtFlutter 4.1s ease-in-out infinite;
    }

    .about-content {
        padding: clamp(1.1rem, 2.3vw, 1.6rem);
        max-width: 820px;
        justify-self: start;
        width: min(100%, 820px);
        color: #f1f4fa;
        display: grid;
        align-content: start;
        gap: 0.7rem;
        background: linear-gradient(145deg, #112641 0%, #0f2239 100%);
        border-radius: 24px;
        will-change: transform, box-shadow;
        transition: transform 0.22s cubic-bezier(0.22, 0.9, 0.2, 1), box-shadow 0.22s ease;
    }

    .about-content:hover {
        transform: translateY(-7px) rotate(-0.7deg);
        box-shadow: 0 22px 44px rgba(9, 28, 49, 0.24);
    }

    .about-content h3 {
        margin: 0;
        color: #ffffff;
        font-size: clamp(1.55rem, 2.4vw, 2.05rem);
        font-family: 'DM Sans', sans-serif;
        font-weight: 800;
        line-height: 1;
    }

    .about-content p {
        margin: 0;
        color: #e3e7ef;
        line-height: 1.62;
        font-size: 0.93rem;
    }

    .about-intro-title {
        margin: 0;
        max-width: 860px;
        color: #33485d;
        font-size: clamp(1rem, 1.4vw, 1.22rem);
        font-weight: 700;
        line-height: 1.5;
    }

    .about-content strong {
        color: #ffffff;
        font-weight: 800;
    }

    .about-metrics {
        margin-top: 0.45rem;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
        border-top: 1px solid rgba(255, 255, 255, 0.14);
        border-bottom: 1px solid rgba(255, 255, 255, 0.14);
    }

    .about-metric {
        padding: 0.8rem 0;
    }

    .about-metric + .about-metric {
        border-left: 1px solid rgba(255, 255, 255, 0.14);
        padding-left: 1rem;
    }

    .about-metric strong {
        display: block;
        color: #e58b19;
        font-size: clamp(1.7rem, 2.6vw, 2.4rem);
        line-height: 1;
        margin-bottom: 0.45rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 800;
    }

    .about-metric span {
        color: #f3f5fa;
        font-size: 0.94rem;
    }

    .about-action {
        margin-top: 0.35rem;
    }

    .about-action a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.7rem 1.45rem;
        border-radius: 999px;
        text-decoration: none;
        color: #ffffff;
        background: linear-gradient(135deg, #0f9bab, #077489);
        font-weight: 700;
    }

    .values-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .value-card,
    .service-card,
    .step-card,
    .portfolio-card,
    .featured-card,
    .testimonial-card {
        padding: 1rem;
    }

    .value-icon,
    .service-icon,
    .step-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, #102745, #1d5b9d);
        color: #ffffff;
        font-weight: 900;
        font-size: 0.98rem;
        margin-bottom: 0.85rem;
    }

    .step-index {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #d95f18, #f1ac61);
    }

    .value-card h3,
    .service-card h3,
    .step-card h3,
    .portfolio-card h3,
    .featured-card h3,
    .testimonial-card h3 {
        margin: 0 0 0.45rem;
        font-size: 1.06rem;
        color: #11233a;
    }

    .value-card p,
    .service-card p,
    .step-card p,
    .portfolio-card p,
    .featured-card p,
    .testimonial-card p {
        margin: 0;
        color: #5c7083;
        line-height: 1.72;
    }

    .services-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        justify-items: center;
    }

    .service-card {
        position: relative;
        overflow: hidden;
        min-height: 470px;
        padding: 1.25rem 1.15rem 1.05rem;
        width: 100%;
        max-width: 360px;
        justify-self: center;
        border: 1px solid #dfe7ee;
        border-radius: 30px;
        box-shadow: 0 16px 34px rgba(15, 31, 50, 0.08);
        display: grid;
        gap: 0.8rem;
        align-content: start;
        justify-items: center;
        text-align: center;
        will-change: transform, box-shadow;
        transition: transform 0.2s cubic-bezier(0.22, 0.9, 0.2, 1), box-shadow 0.2s cubic-bezier(0.22, 0.9, 0.2, 1), border-color 0.2s ease;
    }

    .services-grid .service-card:hover {
        transform: translateY(-8px) scale(1.01) rotate(-0.6deg);
        box-shadow: 0 0 0 2px rgba(15, 155, 171, 0.5), 0 26px 52px rgba(12, 33, 55, 0.16);
        border-color: #0b8f97;
    }

    .services-grid .service-card:nth-child(even):hover {
        transform: translateY(-8px) scale(1.01) rotate(0.6deg);
    }

    .service-card--light {
        background: linear-gradient(145deg, #ffffff 0%, #ffffff 68%, #edf2f5 100%);
    }

    .services-grid .service-card--teal:hover {
        border-color: #ffffff;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.96), 0 26px 52px rgba(12, 33, 55, 0.2);
    }

    .service-card--teal {
        background: linear-gradient(145deg, #13949d 0%, #0e8892 68%, #0b7b84 100%);
        color: #ffffff;
        box-shadow: 0 18px 38px rgba(6, 97, 104, 0.18);
    }

    .service-top {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.8rem;
        min-height: 84px;
    }

    .service-mark {
        width: 112px;
        height: 112px;
        border-radius: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #f7fbfe 0%, #eef4f8 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8), 0 10px 22px rgba(15, 31, 50, 0.08);
        overflow: hidden;
        transition: transform 0.2s cubic-bezier(0.22, 0.9, 0.2, 1), box-shadow 0.2s ease;
    }

    .services-grid .service-card:hover .service-mark {
        transform: translateY(-3px) scale(1.05);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85), 0 14px 28px rgba(15, 31, 50, 0.14);
    }

    .service-mark img {
        width: 74%;
        height: 74%;
        object-fit: contain;
        display: block;
    }

    .service-card--teal .service-mark {
        width: 112px;
        height: 112px;
        padding: 0;
        background: transparent;
        box-shadow: none;
    }

    .service-card h3 {
        margin: 0;
        font-size: 1.16rem;
        line-height: 1.25;
        color: #11233a;
    }

    .service-card p {
        margin: 0;
        line-height: 1.7;
    }

    .service-summary {
        color: inherit;
        opacity: 0.96;
        max-width: 28ch;
        line-height: 1.68;
    }

    .service-card--teal h3,
    .service-card--teal .service-summary,
    .service-card--teal .service-list li {
        color: #f5fbff;
    }

    .service-list {
        display: grid;
        gap: 0.6rem;
        margin: 0.15rem auto 0;
        padding: 0 2rem;
        list-style: none;
        justify-items: start;
        width: calc(100% - 2rem);
        box-sizing: border-box;
    }

    .service-list li {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
        justify-content: flex-start;
        color: inherit;
        line-height: 1.5;
        text-align: left;
        max-width: 100%;
    }

    .service-list li::before {
        display: none;
    }

    .service-card--teal .service-list li::before {
        display: none;
    }

    .service-point-icon {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        object-fit: contain;
        margin-top: 0.2rem;
    }

    .service-card--teal {
        padding-top: 1.1rem;
    }

    .services-intro-center {
        width: 100%;
        max-width: 920px;
        margin: 0 auto;
        text-align: center;
        display: grid;
        justify-items: center;
        gap: 0.65rem;
    }

    .services-title {
        margin: 0;
        color: #11233a;
        font-size: clamp(1.55rem, 2.4vw, 2.05rem);
        font-family: 'DM Sans', sans-serif;
        font-weight: 800;
        line-height: 1;
        text-wrap: balance;
    }

    .services-copy {
        margin: 0;
        max-width: 900px;
        color: #587087;
        font-size: 1rem;
        line-height: 1.72;
    }

    .testimonials-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem;
        justify-content: center;
    }

    .testimonial-card {
        flex: 0 1 260px;
        width: 100%;
        max-width: 280px;
        min-height: 220px;
        background: linear-gradient(145deg, #118e98 0%, #0f8790 100%);
        border: 0;
        color: #ffffff;
        box-shadow: 0 18px 38px rgba(7, 91, 99, 0.2);
        padding: 0.95rem;
    }

    .testimonial-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .testimonial-avatar {
        width: 62px;
        height: 62px;
        border-radius: 999px;
        border: 3px solid rgba(255, 255, 255, 0.24);
        background: rgba(12, 29, 44, 0.22);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        letter-spacing: 0.03em;
        flex: 0 0 auto;
    }

    .testimonial-name {
        margin: 0;
        color: #ffffff;
        font-size: 0.95rem;
    }

    .testimonial-role {
        color: rgba(236, 250, 252, 0.9);
        font-size: 0.82rem;
    }

    .testimonial-stars {
        margin: 0.4rem 0 0;
        color: #ffd166;
        letter-spacing: 0.12em;
        font-size: 0.92rem;
        font-weight: 800;
        text-shadow: 0 1px 0 rgba(7, 48, 52, 0.28);
    }

    .testimonial-quote {
        margin: 0.75rem 0 0;
        color: #f3fcff;
        line-height: 1.68;
        font-size: 0.86rem;
    }

    .testimonial-card h3.testimonial-name,
    .testimonial-card p.testimonial-quote,
    .testimonial-card small.testimonial-role,
    .testimonial-card p.testimonial-stars {
        color: inherit;
    }

    .testimonial-card h3.testimonial-name {
        color: #ffffff;
    }

    .testimonial-card p.testimonial-quote {
        color: #edf9fc;
    }

    .testimonial-card small.testimonial-role {
        color: rgba(239, 252, 255, 0.95);
    }

    .testimonial-card p.testimonial-stars {
        color: #ffd166;
    }

    .testimonial-quote::before {
        content: none;
    }

    .clients-wrap {
        margin-top: 1.1rem;
        padding-top: 1rem;
        border-top: 1px solid #e6edf3;
        display: grid;
        gap: 0.7rem;
    }

    .help-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(240px, 0.75fr);
        gap: 1.25rem;
        align-items: start;
    }

    .help-intro-center {
        max-width: 940px;
    }

    .help-form-card,
    .help-info-card {
        border: 1px solid #dfe7ee;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 12px 30px rgba(12, 33, 55, 0.06);
    }

    .help-form-card {
        padding: 1rem 1rem 1.05rem;
    }

    .help-note {
        margin: 0 0 0.75rem;
        color: #2f455b;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .help-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem;
    }

    .help-field {
        display: grid;
        gap: 0.28rem;
    }

    .help-field--full {
        grid-column: 1 / -1;
    }

    .help-field label {
        font-size: 0.84rem;
        font-weight: 700;
        color: #355068;
    }

    .help-input,
    .help-textarea {
        width: 100%;
        border: 1px solid #cdd9e3;
        border-radius: 12px;
        background: #ffffff;
        color: #18324a;
        font-family: inherit;
        font-size: 0.92rem;
        padding: 0.62rem 0.72rem;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .help-input:focus,
    .help-textarea:focus {
        border-color: #7aa6c6;
        box-shadow: 0 0 0 3px rgba(122, 166, 198, 0.2);
    }

    .help-textarea {
        min-height: 102px;
        resize: vertical;
    }

    .help-submit {
        margin-top: 0.8rem;
        border: 0;
        border-radius: 999px;
        background: #0f2b4d;
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
        padding: 0.58rem 1.05rem;
        cursor: pointer;
    }

    .help-submit:hover {
        background: #123760;
    }

    .help-info-card {
        padding: 0.3rem 0 0.3rem 0.45rem;
        display: grid;
        gap: 0;
        align-content: start;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .help-contact-item {
        margin: 0;
        padding: 0.62rem 0.1rem 0.78rem;
        border-bottom: 1px solid #dbe5ee;
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: 0.6rem;
        align-items: start;
    }

    .help-contact-item:last-child {
        border-bottom: 0;
    }

    .help-contact-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 800;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #d9e4ed;
    }

    .help-contact-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        padding: 4px;
    }

    .help-contact-icon--office {
        background: #ffffff;
    }

    .help-contact-icon--tel,
    .help-contact-icon--wa {
        background: #ffffff;
    }

    .help-contact-icon--mail {
        background: #ffffff;
    }

    .help-contact-item h3 {
        margin: 0 0 0.2rem;
        font-size: 0.92rem;
        color: #0f2b4d;
    }

    .help-contact-item p,
    .help-contact-item a {
        margin: 0;
        font-size: 0.92rem;
        line-height: 1.55;
        color: #1f3952;
        text-decoration: none;
    }

    .help-contact-item a:hover {
        color: #0f4e74;
    }

    .faq-section {
        width: min(100% - 1.4rem, 1220px);
        padding: clamp(1.1rem, 2.2vw, 1.7rem);
    }

    .faq-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(0, 1.08fr);
        gap: clamp(0.9rem, 2.2vw, 1.45rem);
        align-items: start;
    }

    .faq-copy .services-intro-center {
        margin: 0;
        max-width: 680px;
        justify-items: start;
        text-align: left;
    }

    .faq-highlight {
        margin: 0;
        color: #11233a;
        font-family: 'Playfair Display', sans-serif;
        font-size: clamp(1.8rem, 3.3vw, 3rem);
        font-weight: 800;
        line-height: 1.08;
        text-wrap: balance;
    }

    .faq-accordion {
        display: grid;
        gap: 0.7rem;
    }

    .faq-item {
        overflow: hidden;
        border-radius: 20px;
        background: transparent;
        border: 1px solid #d7e2eb;
        box-shadow: 0 8px 20px rgba(20, 66, 101, 0.06);
    }

    .faq-item summary {
        list-style: none;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.92rem 1.1rem 0.92rem 1.2rem;
        cursor: pointer;
        font-weight: 800;
        font-size: clamp(0.95rem, 1.35vw, 1.02rem);
        color: #ffffff;
        background: #0f2b4d;
    }

    .faq-item[open] summary {
        border-bottom: 1px solid #d7e2eb;
    }

    .faq-item summary::-webkit-details-marker {
        display: none;
    }

    .faq-item summary::after {
        content: '';
        width: 0.66rem;
        height: 0.66rem;
        border-right: 2.2px solid currentColor;
        border-bottom: 2.2px solid currentColor;
        transform: rotate(45deg);
        flex: 0 0 auto;
        transition: transform 0.25s ease;
        margin-right: 0.08rem;
    }

    .faq-item[open] summary::after {
        transform: rotate(225deg);
    }

    .faq-answer {
        padding: 1rem 1.2rem 1.15rem;
        background: #ffffff;
        color: #31465b;
        line-height: 1.68;
        font-size: 0.95rem;
    }

    .help-list {
        margin: 0;
        padding-left: 1rem;
        display: grid;
        gap: 0.45rem;
    }

    .clients-title {
        margin: 0;
        text-align: center;
        color: #1b3550;
        font-size: 1.18rem;
        font-weight: 800;
    }

    .clients-logos {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .client-logo-item {
        min-height: 72px;
        border-radius: 0;
        border: 0;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.2rem;
        box-shadow: none;
    }

    .client-logo-item img {
        max-width: min(150%, 1150px);
        max-height: 200px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        filter: saturate(0.96);
    }

    .featured-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        justify-items: center;
        gap: 0.82rem;
    }

    .featured-card {
        overflow: hidden;
        display: grid;
        gap: 0;
        padding: 0;
        width: 100%;
        max-width: 300px;
    }

    .featured-image {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: contain;
        border-bottom: 1px solid #e7edf3;
        background: #ffffff;
        padding: 0.25rem;
    }

    .featured-body {
        padding: 0.88rem;
        display: grid;
        gap: 0.6rem;
    }

    .featured-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .tag-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.32rem 0.65rem;
        border-radius: 999px;
        background: #edf6fb;
        color: #15506f;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .catalog-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #b9cfde;
        border-radius: 11px;
        padding: 0.58rem 0.9rem;
        text-decoration: none;
        font-weight: 700;
        color: #1d4f6d;
        background: #ffffff;
        cursor: pointer;
        font-family: inherit;
    }

    .btn-ghost:hover {
        border-color: #7eb4cf;
        color: #0f3d57;
        background: #ffffff;
    }

    .steps-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem 1.9rem;
    }

    .steps-grid .step-card {
        position: relative;
        overflow: visible;
        border: 1px solid #d7e4ee;
        background: linear-gradient(165deg, #ffffff 0%, #f2f7fb 100%);
        box-shadow: 0 12px 24px rgba(13, 44, 72, 0.08);
        border-radius: 18px;
        padding: 0.85rem;
        display: grid;
        gap: 0.42rem;
        align-content: start;
        text-align: left;
        min-height: 100%;
    }

    .steps-grid .step-card:hover {
        transform: translateY(-4px);
        border-color: #9fc0d6;
        box-shadow: 0 16px 28px rgba(13, 44, 72, 0.14);
    }

    .step-illustration {
        width: min(100%, 124px);
        height: 92px;
        border-radius: 10px;
        object-fit: contain;
        margin-top: 0.35rem;
        justify-self: center;
        background: transparent;
        padding: 0;
        border: 0;
        box-shadow: none;
        display: block;
    }

    .steps-grid .step-card:nth-child(6) .step-illustration {
        transform: scale(1.18);
        transform-origin: center;
    }

    .steps-grid .step-card h3 {
        margin: 0;
        font-size: 0.98rem;
        line-height: 1.35;
        color: #11233a;
        text-transform: uppercase;
        text-align: center;
    }

    .steps-grid .step-card p {
        margin: 0;
        line-height: 1.52;
        color: #516a80;
        font-size: 0.88rem;
        max-width: 30ch;
        text-align: center;
        justify-self: center;
    }

    .steps-grid .step-card::after {
        content: "";
        position: absolute;
        width: 20px;
        height: 20px;
        background: url('{{ asset('images/icon/iconpoin.png') }}') center / contain no-repeat;
        top: 50%;
        opacity: 0;
        z-index: 3;
    }

    .steps-grid .step-card:nth-child(1)::after,
    .steps-grid .step-card:nth-child(2)::after {
        right: -1rem;
        transform: translateY(-50%);
        opacity: 1;
    }

    .steps-grid .step-card:nth-child(5)::after,
    .steps-grid .step-card:nth-child(6)::after {
        left: -1rem;
        transform: translateY(-50%) rotate(180deg);
        opacity: 1;
    }

    .order-cta {
        margin-top: 1.8rem;
        display: grid;
        justify-items: center;
        gap: 0.9rem;
    }

    .order-cta-title {
        margin: 0;
        color: #11233a;
        font-size: clamp(1rem, 2vw, 1.7rem);
        font-weight: 500;
        font-family: 'Playfair Display', serif;
        line-height: 1.05;
    }

    .order-cta .btn {
        padding: 0.72rem 1.6rem;
        border-radius: 8px;
        background: #dfbf65;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1rem, 1.4vw, 1.15rem);
        font-weight: 500;
        letter-spacing: 0.01em;
        border: 1px solid rgba(223, 191, 101, 0.9);
    }

    .order-cta .btn:hover {
        background: #d6b54e;
        color: #0d2749;
    }

    .testimonial-meta {
        display: grid;
        gap: 0.2rem;
    }

    .cta-strip {
        padding: clamp(1.4rem, 3vw, 2.4rem);
        background:
            radial-gradient(circle at top left, rgba(223, 191, 101, 0.28), transparent 28%),
            linear-gradient(135deg, #0a223f, #12315d 52%, #17477f 100%);
        color: #eef5fb;
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 26px 58px rgba(10, 31, 53, 0.18);
        width: min(100% - 1.4rem, 1220px);
        margin-inline: auto;
    }

    .cta-strip h2 {
        margin: 0;
        color: #ffffff;
        font-size: clamp(1.9rem, 3vw, 3rem);
    }

    .cta-strip p {
        margin: 0.75rem 0 0;
        max-width: 760px;
        color: #d5dfeb;
        line-height: 1.72;
    }

    .cta-actions {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
        margin-top: 1.2rem;
    }

    .footer {
        padding: clamp(1.35rem, 3vw, 2rem);
        background: linear-gradient(180deg, #091b31 0%, #081423 100%);
        color: #dbe6ef;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 -18px 50px rgba(5, 13, 22, 0.18);
        width: 100%;
        margin-inline: 0;
    }

    .footer-grid {
        grid-template-columns: 1.35fr 0.72fr 0.72fr 0.9fr 1fr;
    }

    .footer-title {
        margin: 0 0 0.75rem;
        font-size: 1.02rem;
        color: #ffffff;
    }

    .footer p,
    .footer a {
        color: #c4d2df;
        line-height: 1.72;
    }

    .footer a {
        text-decoration: none;
    }

    .footer a:hover {
        color: #f3cf73;
    }

    .footer-links {
        display: grid;
        gap: 0.55rem;
    }

    .footer-bottom {
        margin-top: 1.15rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        justify-content: center;
        gap: 0.8rem;
        flex-wrap: wrap;
        color: #a8b9ca;
        font-size: 0.88rem;
        text-align: center;
    }

    .footer-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 1rem;
    }

    .footer-badge {
        border-radius: 999px;
        padding: 0.32rem 0.68rem;
        background: rgba(255, 255, 255, 0.08);
        color: #f1f5f9;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .fade-up {
        animation: rise 0.85s ease both;
    }

    .fade-delay-1 { animation-delay: 0.08s; }
    .fade-delay-2 { animation-delay: 0.16s; }
    .fade-delay-3 { animation-delay: 0.24s; }
    .fade-delay-4 { animation-delay: 0.32s; }

    @keyframes rise {
        from {
            opacity: 0;
            transform: translateY(18px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shirtFlutter {
        0% {
            transform: rotate(0deg) translateY(0px);
        }
        25% {
            transform: rotate(1.1deg) translateY(-3px);
        }
        50% {
            transform: rotate(0deg) translateY(0px);
        }
        75% {
            transform: rotate(-1.1deg) translateY(-2px);
        }
        100% {
            transform: rotate(0deg) translateY(0px);
        }
    }

    @media (max-width: 1180px) {
        .services-grid,
        .featured-grid,
        .steps-grid,
        .footer-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .faq-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero-stats,
        .company-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .service-card,
        .portfolio-card,
        .featured-card,
        .step-card,
        .testimonial-card {
            min-height: auto;
        }

        .steps-grid .step-card::after {
            display: none;
        }

        .service-card {
            padding: 1.1rem 1rem 0.95rem;
        }

        .service-mark {
            width: 96px;
            height: 96px;
            border-radius: 24px;
        }

        .service-mark img {
            width: 72%;
            height: 72%;
        }

        .about-media {
            min-height: 180px;
        }
    }

    @media (max-width: 980px) {
        .hero-grid,
        .values-grid {
            grid-template-columns: 1fr;
        }

        .hero-grid {
            min-height: auto;
        }

        .about-showcase {
            grid-template-columns: minmax(220px, 0.85fr) minmax(0, 1.15fr);
            gap: 0.9rem;
        }

        .about-media {
            min-height: 185px;
            max-height: 230px;
        }

        .about-content {
            width: 100%;
            max-width: none;
        }

        .services-grid {
            gap: 0.85rem;
        }

        .help-layout {
            grid-template-columns: 1fr;
        }

        .help-info-card {
            padding-left: 0;
        }

        .help-form-grid {
            grid-template-columns: 1fr;
        }

        .faq-layout {
            grid-template-columns: 1fr;
        }

        .service-card {
            min-height: auto;
        }

        .service-summary {
            max-width: 34ch;
        }

        .featured-image {
            aspect-ratio: 16 / 11;
        }
    }

    @media (max-width: 720px) {
        .hero {
            min-height: auto;
            padding: 1rem;
        }

        .hero::before {
            background: transparent;
        }

        .hero::after {
            background-position: right center;
            background-size: cover;
            opacity: 1;
        }

        .hero-copy h1 {
            font-size: clamp(2rem, 9vw, 3rem);
            white-space: normal;
        }

        .hero-copy {
            padding-top: 3.2rem;
        }

        .hero-stats,
        .company-stats {
            grid-template-columns: 1fr;
        }

        .services-grid,
        .featured-grid,
        .steps-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .testimonials-grid {
            justify-content: stretch;
        }

        .testimonial-card {
            flex-basis: 100%;
            max-width: none;
        }

        .footer-grid {
            grid-template-columns: 1fr;
        }

        .section-block,
        .cta-strip,
        .footer {
            width: min(100% - 1rem, 1220px);
        }

        .footer {
            width: 100%;
        }

        .about-showcase {
            grid-template-columns: 0.9fr 1.1fr;
            gap: 0.75rem;
        }

        .about-metrics {
            grid-template-columns: 1fr;
        }

        .faq-copy .services-intro-center {
            max-width: none;
        }

        .about-metric + .about-metric {
            border-left: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            padding-left: 0;
        }

        .service-card {
            padding: 1rem 0.9rem 0.9rem;
            gap: 0.7rem;
            min-height: auto;
            width: 100%;
                max-width: 320px;
        }

        .service-top {
            min-height: 72px;
        }

        .service-mark {
            width: 82px;
            height: 82px;
            border-radius: 22px;
        }

        .service-mark img {
            width: 70%;
            height: 70%;
        }

        .service-card h3 {
            font-size: 1.05rem;
        }

        .service-summary,
        .service-list li {
            font-size: 0.94rem;
        }

        .service-list {
            padding: 0 1.4rem;
            width: calc(100% - 1rem);
        }

        .featured-image,
        .about-media img {
            aspect-ratio: 4 / 3;
        }

        .about-media {
            min-height: 150px;
            max-height: 190px;
        }

    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation: none !important;
            transition: none !important;
            scroll-behavior: auto !important;
        }
    }
</style>

<div class="landing-page">
    <div class="landing-shell">
        <section class="hero" id="home" aria-label="Landing Hero">
            <div class="hero-grid" style="justify-content: space-between; flex-wrap: wrap; gap: 2rem; padding-right: clamp(1rem, 5vw, 4rem);">
                <div class="hero-copy">
                    <h1>PT PANJI USAHA MULIA</h1>
                    <p class="hero-badge">Spesialis Konveksi Custom di Bandung sejak 2002</p>
                    <p class="hero-note">Punya desain keren tapi bingung bikinnya di mana? Serahkan saja sama ahlinya. Kami pastikan setiap baju yang kamu pesan punya detail jahitan mantap dan warna sablon yang keluar banget. Bikin tim kamu tampil makin pede.</p>
            
                    <div class="hero-actions">
                        <a class="btn btn-brand" href="{{ $primaryAction }}">Mulai Custom Sekarang</a>
                        <a class="btn btn-alt" href="#how-to-order">Lihat Cara Order</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block" id="about" aria-labelledby="about-title">
            <div class="section-intro">
                
            </div>

            <div class="about-showcase">
                <div class="about-media">
                    <img src="{{ asset('images/kaoslogo.png') }}" alt="Proses produksi konveksi PT Panji Usaha Mulia">
                </div>

                <div class="about-content">
                    <h3>Tentang</h3>
                    <p><strong>PT Panji Usaha Mulia</strong> bergerak di bidang <strong>konveksi custom</strong>, melayani kebutuhan <strong>komunitas, perusahaan, kampus, dan event organizer</strong> dengan proses kerja yang rapi dan terukur. Kami fokus menjaga kualitas dari pemilihan bahan, detail jahitan, hingga hasil akhir agar tetap konsisten.</p>
                    <p>Dengan pengalaman produksi yang matang dan alur komunikasi yang jelas, tim kami membantu setiap klien mendapatkan apparel custom yang cepat, tepat, dan sesuai identitas brand.</p>

                    <div class="about-metrics" aria-label="Statistik Tentang Kami">
                        <div class="about-metric">
                            <strong>{{ $landingStats[1]['value'] }}</strong>
                            <span>Konsumen</span>
                        </div>
                        <div class="about-metric">
                            <strong>{{ $landingStats[0]['value'] }}</strong>
                            <span>Pengalaman produksi</span>
                        </div>
                    </div>

        
                </div>
            </div>
        </section>

        <section class="section-block" id="services" aria-labelledby="services-title">
            <div class="section-intro">
                <div class="services-intro-center">
                    <h2 class="services-title" id="services-title">Layanan</h2>
                    <p class="services-copy">Kami melayani pemesanan untuk memenuhi kebutuhan komunitas, perusahaan, instansi, event organizer, brand clothing, hingga perorangan. Kami melayani pemesanan dalam jumlah kecil maupun besar dengan hasil yang berkualitas, rapi, dan tepat waktu.</p>
                </div>
            </div>

            <div class="services-grid">
                @foreach ($landingServices as $service)
                    <article class="service-card service-card--{{ $service['theme'] }}">
                        <div class="service-top">
                            <div class="service-mark">
                                <img src="{{ asset($service['icon']) }}" alt="{{ $service['title'] }}">
                            </div>
                        </div>
                        <h3>{{ $service['title'] }}</h3>
                        <p class="service-summary">{{ $service['text'] }}</p>
                        <ul class="service-list">
                            @foreach ($service['items'] as $item)
                                <li>
                                    <img class="service-point-icon" src="{{ asset($service['theme'] === 'teal' ? 'images/icon/iconpoin2.png' : 'images/icon/iconpoin.png') }}" alt="">
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section-block" id="testimonials" aria-labelledby="testimonials-title">
            <div class="section-intro">
                <div class="services-intro-center">
                    <h2 class="services-title" id="testimonials-title">Testimoni</h2>
                    <p class="services-copy">Kepercayaan klien adalah motivasi utama kami untuk terus memberikan layanan terbaik. Berikut beberapa testimoni dari pelanggan yang telah merasakan langsung kualitas produk dan pelayanan kami.</p>
                </div>
            </div>

            <div class="testimonials-grid">
                @foreach ($landingTestimonials as $testimonial)
                    <article class="testimonial-card">
                        <div class="testimonial-head">
                            <div class="testimonial-avatar">{{ strtoupper(mb_substr($testimonial['name'], 0, 2)) }}</div>
                            <div class="testimonial-meta">
                                <h3 class="testimonial-name">{{ $testimonial['name'] }}</h3>
                                <small class="testimonial-role">{{ $testimonial['role'] }}</small>
                            </div>
                        </div>
                        <p class="testimonial-stars">{{ str_repeat('★', $testimonial['rating']) }}</p>
                        <p class="testimonial-quote">{{ $testimonial['text'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="clients-wrap" aria-label="Client Kami">
                <h3 class="clients-title">Client Kami</h3>
                <div class="clients-logos">
                    <div class="client-logo-item">
                        <img src="{{ asset($clientLogoImage) }}" alt="Logo Client Kami">
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block" id="featured-products" aria-labelledby="featured-title">
            <div class="section-intro">
                <div class="services-intro-center">
                    <h2 class="services-title" id="featured-title">Produk Kaos yang Kami Produksi</h2>
                    <p class="services-copy">Pilihan produksi yang paling sering dipakai untuk kebutuhan brand, event, dan seragam.</p>
                </div>
            </div>

            <div class="featured-grid">
                @foreach ($featuredProducts as $product)
                    <article class="featured-card">
                        <div class="card-model-title" style="text-align: center; padding: 1.2rem 1rem 0.5rem; font-family: 'Playfair Display', serif; font-size: 1.35rem; color: #11233a; font-weight: 700; border-bottom: 1px solid #f0f4f8; margin-bottom: 0.5rem;">
                            {{ $product['category'] }}
                        </div>
                        @if (!empty($product['image']))
                            <img class="featured-image" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}">
                        @endif
                        <div class="featured-body">
                            <div>
                                <h3>{{ $product['name'] }}</h3>
                                <p>{{ $product['desc'] }}</p>
                            </div>
                            <div class="featured-tags">
                                <span class="tag-pill">{{ $product['category'] }}</span>
                                @if(isset($product['price']))
                                    <span class="tag-pill">{{ $product['price'] }}</span>
                                @endif
                                @if(isset($product['min_order']))
                                    <span class="tag-pill">{{ $product['min_order'] }}</span>
                                @endif
                            </div>
                            <div class="catalog-actions">
                                <a class="btn-ghost" href="{{ route('catalog.show', $product['slug']) }}">Detail</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <!-- New Section: Pilihan Model -->
        <section class="section-block" id="pilihan-model" aria-labelledby="model-title" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
            <div class="section-intro">
                <div class="services-intro-center">
                    <h2 class="services-title" id="model-title">Mau Pesan Kaos? Kenali Dulu Model-Modelnya!</h2>
                    <p class="services-copy">Sebelum memesan, pastikan Anda memilih model kaos yang paling sesuai dengan kebutuhan dan preferensi gaya Anda.</p>
                </div>
            </div>

            <div class="featured-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <!-- Model 1 -->
                <article class="featured-card" style="display: flex; flex-direction: column; align-items: center; text-align: center; border: none; background: transparent; padding: 0;">
                    <div style="width: 100%; aspect-ratio: 3/4; background: #e2e8f0; border-radius: 16px; margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ asset('images/katalog/modelOblong.png') }}" alt="Kaos Oblong" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
                    </div>
                    <h3 style="font-size: 1.15rem; color: #0d2749; margin: 0 0 0.5rem; font-weight: 700;">Kaos Oblong</h3>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0;">Model kaos paling klasik dengan kerah berbentuk bulat</p>
                </article>

                <!-- Model 2 -->
                <article class="featured-card" style="display: flex; flex-direction: column; align-items: center; text-align: center; border: none; background: transparent; padding: 0;">
                    <div style="width: 100%; aspect-ratio: 3/4; background: #e2e8f0; border-radius: 16px; margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ asset('images/katalog/modelRaglan.png') }}" alt="Kaos Raglan" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
                    </div>
                    <h3 style="font-size: 1.15rem; color: #0d2749; margin: 0 0 0.5rem; font-weight: 700;">Kaos Raglan</h3>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0;">Potongan lengan yang menyambung miring dari kerah ke ketiak, dengan warna berbeda</p>
                </article>

                <!-- Model 3 -->
                <article class="featured-card" style="display: flex; flex-direction: column; align-items: center; text-align: center; border: none; background: transparent; padding: 0;">
                    <div style="width: 100%; aspect-ratio: 3/4; background: #e2e8f0; border-radius: 16px; margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ asset('images/katalog/modelPolo.png') }}" alt="Kaos Polo (Polo Shirt)" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
                    </div>
                    <h3 style="font-size: 1.15rem; color: #0d2749; margin: 0 0 0.5rem; font-weight: 700;">Kaos Polo (Polo Shirt)</h3>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0;">Ciri khas kerah seperti kemeja ditambah 2–3 kancing di bagian depan</p>
                </article>

                <!-- Model 4 -->
                <article class="featured-card" style="display: flex; flex-direction: column; align-items: center; text-align: center; border: none; background: transparent; padding: 0;">
                    <div style="width: 100%; aspect-ratio: 3/4; background: #e2e8f0; border-radius: 16px; margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ asset('images/katalog/modelVneck.png') }}" alt="Kaos V-Neck" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
                    </div>
                    <h3 style="font-size: 1.15rem; color: #0d2749; margin: 0 0 0.5rem; font-weight: 700;">Kaos V-Neck</h3>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0;">Ciri khas kerah berbentuk huruf “V” di bagian depan</p>
                </article>
            </div>
        </section>

        <section class="section-block" id="how-to-order" aria-labelledby="order-title">
            <div class="section-intro">
                <div class="services-intro-center">
                    <h2 class="services-title" id="order-title">Cara Pemesanan</h2>
                    <p class="services-copy">Kami ingin proses pemesanan Anda mudah, cepat, dan nyaman. Berikut langkah-langkah sederhana untuk memesan produk di PT Panji Usaha Mulia:</p>
                </div>
            </div>

            <div class="steps-grid">
                @foreach ($landingSteps as $step)
                    <article class="step-card">
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['text'] }}</p>
                        <img class="step-illustration" src="{{ asset($step['icon']) }}" alt="{{ $step['title'] }}">
                    </article>
                @endforeach
            </div>

            <div class="order-cta">
                <p class="order-cta-title">Siap Order?</p>
                <a class="btn btn-brand" href="{{ $primaryAction }}">Mulai pesanan</a>
            </div>
        </section>

        <section class="section-block faq-section" id="faq" aria-labelledby="faq-title">
            <div class="faq-layout">
                <div class="faq-copy">
                    <div class="services-intro-center">
                        <h2 class="services-title" id="faq-title">FAQ</h2>
                        <h3 class="faq-highlight">Masih Bingung? Temukan Jawaban Cepat di Sini!</h3>
                        <p class="services-copy">Kami memahami bahwa setiap pelanggan punya kebutuhan dan pertanyaan berbeda. Berikut ini adalah jawaban atas beberapa pertanyaan yang paling sering ditanyakan seputar layanan kami di PT Panji Usaha Mulia.</p>
                    </div>
                </div>

                <div class="faq-accordion">
                    @foreach ($landingFaqs as $index => $faq)
                        <details class="faq-item" @if ($index === 0) open @endif>
                            <summary class="faq-summary">{{ $faq['question'] }}</summary>
                            <div class="faq-answer">{{ $faq['answer'] }}</div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-block" id="help-center" aria-labelledby="help-center-title">
            <div class="section-intro">
                <div class="services-intro-center help-intro-center">
                    <h2 class="services-title" id="help-center-title">Pusat Bantuan</h2>
                    <p class="services-copy">Anda bisa langsung menghubungi kami disini</p>
                </div>
            </div>

            <div class="help-layout">
                <div class="help-form-card">
                    <form class="help-form-grid" action="#" method="get">
                        <div class="help-field">
                            <label for="help-name">Nama</label>
                            <input class="help-input" id="help-name" name="name" type="text" placeholder="Masukkan nama">
                        </div>
                        <div class="help-field">
                            <label for="help-company">Perusahaan</label>
                            <input class="help-input" id="help-company" name="company" type="text" placeholder="Nama perusahaan">
                        </div>
                        <div class="help-field">
                            <label for="help-email">Email</label>
                            <input class="help-input" id="help-email" name="email" type="email" placeholder="contoh@email.com">
                        </div>
                        <div class="help-field">
                            <label for="help-address">Alamat</label>
                            <input class="help-input" id="help-address" name="address" type="text" placeholder="Alamat lengkap">
                        </div>
                        <div class="help-field">
                            <label for="help-phone">Telepon</label>
                            <input class="help-input" id="help-phone" name="phone" type="tel" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="help-field">
                            <label for="help-city">Kota</label>
                            <input class="help-input" id="help-city" name="city" type="text" placeholder="Kota">
                        </div>
                        <div class="help-field">
                            <label for="help-country">Negara</label>
                            <input class="help-input" id="help-country" name="country" type="text" value="Indonesia" readonly>
                        </div>
                        <div class="help-field help-field--full">
                            <label for="help-message">Pesan</label>
                            <textarea class="help-textarea" id="help-message" name="message" placeholder="Tulis kebutuhan Anda"></textarea>
                        </div>
                    </form>
                    <button type="button" class="help-submit">Kirim Pesan</button>
                </div>

                <aside class="help-info-card" aria-label="Info Kontak PT Panji Usaha Mulia">
                    <div class="help-contact-item">
                        <span class="help-contact-icon help-contact-icon--office">
                            <img src="{{ asset('images/location.png') }}" alt="Lokasi">
                        </span>
                        <div>
                            <h3>Head Office:</h3>
                            <p>Jl. Jendral Achmad Yani No. 909, (Komplek Pertokoan Cicaheum)<br>Bandung, Jawa Barat, Indonesia, 40125</p>
                        </div>
                    </div>

                    <div class="help-contact-item">
                        <span class="help-contact-icon help-contact-icon--tel">
                            <img src="{{ asset('images/telp.png') }}" alt="Telepon">
                        </span>
                        <div>
                            <h3>Telepon / Fax:</h3>
                            <p><a href="tel:+622272215924">(022) 7215924</a></p>
                        </div>
                    </div>

                    <div class="help-contact-item">
                        <span class="help-contact-icon help-contact-icon--wa">
                            <img src="{{ asset('images/whatsapp.jpg') }}" alt="WhatsApp">
                        </span>
                        <div>
                            <h3>Whatsapp / SMS / HP:</h3>
                            <p><a href="https://wa.me/628123456789" target="_blank" rel="noopener noreferrer">0812 3456 789</a></p>
                        </div>
                    </div>

                    <div class="help-contact-item">
                        <span class="help-contact-icon help-contact-icon--mail">
                            <img src="{{ asset('images/gmail.png') }}" alt="Email">
                        </span>
                        <div>
                            <h3>Email:</h3>
                            <p><a href="mailto:info@ptpanjiusahamulia.com">info@ptpanjiusahamulia.com</a></p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <footer class="footer" id="contact" aria-label="Footer">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title">PT Panji Usaha Mulia</h3>
                    <p>Kami adalah partner konveksi untuk kebutuhan apparel custom yang butuh kualitas konsisten, proses transparan, dan hasil yang mudah dijual kembali ke customer akhir.</p>
                </div>

                <div>
                    <h3 class="footer-title">Navigasi</h3>
                    <div class="footer-links">
                        <a href="#home">Home</a>
                        <a href="#about">Tentang</a>
                        <a href="#services">Layanan</a>
                        <a href="#testimonials">Testimoni</a>
                    </div>
                </div>

                <div>
                    <h3 class="footer-title">Bantuan</h3>
                    <div class="footer-links">
                        <a href="#pilihan-model">Pilihan Model</a>
                        <a href="#how-to-order">Cara Pemesanan</a>
                        <a href="#faq">FAQ</a>
                        <a href="#help-center">Pusat Bantuan</a>
                    </div>
                </div>

                <div>
                    <h3 class="footer-title">Kontak</h3>
                    <div class="footer-links">
                        <a href="https://wa.me/6282129287094" target="_blank" rel="noopener noreferrer">WhatsApp: +62 821 2928 7094</a>
                        <a href="tel:+622272215924">Telepon: 022-7215924</a>
                        <a href="mailto:info@ptpanjiusahamulia.com">Email: info@ptpanjiusahamulia.com</a>
                    </div>
                </div>

                <div>
                    <h3 class="footer-title">Alamat</h3>
                    <p>Jl. Jendral Achmad Yani No. 909<br>(Komplek Pertokoan Cicaheum)<br>Bandung, Jawa Barat 40125</p>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; 2026 PT Panji Usaha Mulia. All rights reserved.</span>
            </div>
        </footer>
    </div>
</div>

@endsection
