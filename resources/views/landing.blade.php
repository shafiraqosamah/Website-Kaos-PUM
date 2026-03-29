@extends('layouts.app')

@section('content')
<style>
    .landing-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        border: 1px solid rgba(223, 191, 101, 0.3);
        width: 100%;
        min-height: clamp(350px, 48vw, 530px);
        margin: 0;
        background:
            linear-gradient(115deg, rgba(7, 29, 58, 0.97) 0%, rgba(15, 45, 84, 0.95) 50%, rgba(20, 59, 106, 0.94) 100%),
            repeating-linear-gradient(50deg, rgba(255, 255, 255, 0.05) 0, rgba(255, 255, 255, 0.05) 2px, transparent 2px, transparent 18px);
        box-shadow: 0 24px 50px rgba(8, 19, 37, 0.28);
        padding: clamp(1rem, 2.4vw, 1.75rem);
    }

    .landing-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url('{{ asset('images/homepage.png') }}') center / cover no-repeat;
        opacity: 0.22;
        filter: saturate(0.95) contrast(1.05);
        pointer-events: none;
        z-index: 0;
    }

    .hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(290px, 360px);
        gap: clamp(0.9rem, 1.8vw, 1.35rem);
        align-items: center;
    }

    .hero-copy {
        color: #f4f7fb;
        max-width: 860px;
        padding-left: clamp(0.7rem, 3vw, 2rem);
    }

    .hero-badge {
        margin: 0 0 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.95rem;
        border-radius: 999px;
        border: 1px solid rgba(240, 202, 99, 0.45);
        background: rgba(223, 191, 101, 0.12);
        color: #dfbf65;
        font-size: 0.83rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .hero-copy h1 {
        margin: 0;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.45rem, 3.5vw, 2.55rem);
        line-height: 1.08;
        letter-spacing: 0.01em;
        font-weight: 700;
        color: #ffffff;
    }

    .hero-copy .highlight {
        color: #dfbf65;
    }

    .hero-lead {
        margin: 1rem 0 0;
        font-family: 'DM Sans', sans-serif;
        font-size: clamp(0.88rem, 1.2vw, 0.96rem);
        line-height: 1.5;
        color: #c8d6e8;
        max-width: 620px;
    }

    .hero-points {
        margin: 1.15rem 0 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 0.55rem;
    }

    .hero-points li {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-family: 'DM Sans', sans-serif;
        color: #e4edf8;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .hero-points li::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #dfbf65;
        flex: 0 0 auto;
    }

    .hero-copy .hero-actions {
        margin-top: 1.25rem;
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .hero-actions .btn-brand {
        background: #dfbf65;
        color: #0d2749;
        font-weight: 800;
    }

    .hero-actions .btn-alt {
        background: transparent;
        color: #dfbf65;
        border: 1px solid rgba(223, 191, 101, 0.75);
    }

    .hero-stats {
        display: grid;
        gap: 1rem;
        width: 100%;
        align-self: center;
        justify-items: start;
        padding-right: clamp(0.3rem, 1.2vw, 0.8rem);
    }

    .hero-stat {
        border-radius: 16px;
        border: 1px solid rgba(194, 212, 233, 0.22);
        background: rgba(145, 170, 204, 0.18);
        padding: 0.7rem 0.82rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        width: max-content;
        min-width: 0;
        max-width: 250px;
        justify-self: start;
    }

    .hero-stat:nth-child(3) {
        max-width: 265px;
    }

    .hero-stat strong {
        display: block;
        font-family: 'Playfair Display', serif;
        color: #dfbf65;
        font-size: clamp(1.05rem, 2vw, 1.8rem);
        line-height: 1;
        margin-bottom: 0.42rem;
        font-weight: 800;
    }

    .hero-stat span {
        font-family: 'Playfair Display', serif;
        color: #c7d6ea;
        font-weight: 600;
        font-size: 0.8rem;
        line-height: 1.35;
        white-space: nowrap;
    }

    @media (max-width: 980px) {
        .hero-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: start;
        }

        .hero-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            justify-items: stretch;
            align-self: auto;
            padding-right: 0;
        }

        .hero-stat,
        .hero-stat:nth-child(3) {
            width: 100%;
            max-width: none;
        }

        .hero-stat span {
            white-space: normal;
        }
    }

    @media (max-width: 720px) {
        .landing-hero {
            min-height: auto;
            padding: 1.1rem;
        }

        .hero-copy h1 {
            font-size: clamp(1.32rem, 6.6vw, 1.88rem);
        }

        .hero-stats {
            grid-template-columns: 1fr;
        }

        .hero-copy {
            padding-left: 0;
        }
    }

    .catalog-wrap {
        margin-top: 1.2rem;
        background: linear-gradient(160deg, #f4f7fb, #eef4f8);
        border: 1px solid #d8e3ec;
        border-radius: 18px;
        padding: 0;
        overflow: hidden;
    }

    .catalog-header {
        text-align: center;
        padding: clamp(1.8rem, 4vw, 3rem) 1rem;
        border-bottom: 1px solid #dfe8ef;
        background:
            repeating-linear-gradient(45deg, rgba(16, 43, 69, 0.03) 0, rgba(16, 43, 69, 0.03) 1px, transparent 1px, transparent 18px),
            linear-gradient(180deg, #f8fbfe 0%, #f4f8fc 100%);
    }

    .catalog-heading {
        margin: 0;
        font-size: clamp(1.55rem, 2.8vw, 2.2rem);
        color: #102945;
    }

    .catalog-subheading {
        margin: 0.7rem 0 0;
        font-size: clamp(0.82rem, 1.2vw, 1rem);
        color: #7893ae;
        font-weight: 500;
    }

    .catalog-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.9rem;
        padding: 1rem;
    }

    .catalog-item {
        border: 1px solid #d4e0ea;
        border-radius: 14px;
        background: #fff;
        padding: 0.7rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        box-shadow: 0 8px 20px rgba(18, 50, 72, 0.07);
    }

    .catalog-image {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: contain;
        border-radius: 10px;
        border: 1px solid #d8e2eb;
        background: #ffffff;
    }

    .catalog-image-fallback {
        width: 100%;
        aspect-ratio: 4 / 3;
        border-radius: 10px;
        border: 1px dashed #c9d8e5;
        background: #ffffff;
        color: #7992a6;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 0.9rem;
        text-align: center;
        padding: 0.7rem;
    }

    .catalog-meta h3 {
        margin: 0;
        font-size: 1.1rem;
    }

    .catalog-price {
        margin: 0.25rem 0 0;
        color: var(--brand);
        font-weight: 700;
    }

    .catalog-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.1rem;
    }

    .catalog-specs {
        margin-top: 0.2rem;
        padding: 0.6rem 0.65rem;
        border: 1px dashed #cbdae8;
        border-radius: 10px;
        background: #f7fbff;
    }

    .catalog-specs p {
        margin: 0.2rem 0;
        font-size: 0.87rem;
        color: #2a4a61;
    }

    .btn-ghost {
        display: inline-block;
        border: 1px solid #b9cfde;
        border-radius: 11px;
        padding: 0.58rem 0.9rem;
        text-decoration: none;
        font-weight: 700;
        color: #1d4f6d;
        background: #f4fbff;
        cursor: pointer;
        font-family: inherit;
    }

    .btn-ghost:hover {
        border-color: #7eb4cf;
        color: #0f3d57;
        background: #e9f7ff;
    }

    @media (max-width: 1180px) {
        .catalog-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .catalog-wrap {
            padding: 0.8rem;
        }

        .catalog-strip {
            grid-template-columns: 1fr;
        }
    }

    .footer {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        margin-top: 1rem;
        padding: 1rem;
    }

    .footer-container {
        max-width: 480px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
    }

    .footer-section {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .footer-section-title {
        font-size: 1rem;
        font-weight: 500;
        color: #102945;
        margin: 0;
    }

    .footer-contact-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .footer-contact-icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f4f8;
        border-radius: 8px;
    }

    .footer-contact-icon img {
        width: 24px;
        height: 24px;
        object-fit: contain;
    }

    .footer-contact-text {
        flex: 1;
        min-height: 40px;
        display: flex;
        align-items: center;
    }


    .footer-contact-text p {
        margin: 0;
        color: #102945;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .footer-contact-text a {
        display: inline-block;
        line-height: 1.2;
        color: #102945;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .footer-contact-text a:hover {
        color: #dfbf65;
    }

    .footer-info-text {
        color: #4b5d72;
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 0.5rem 0 0;
    }

    .footer-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 1rem 0;
    }

    .footer-bottom {
        text-align: center;
        color: #7893ae;
        font-size: 0.85rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .footer {
            padding: 2rem 1rem;
        }
    }
</style>

<section class="landing-hero" aria-label="Landing Hero">
    <div class="hero-grid">
        <div class="hero-copy">
            <p class="hero-badge">Konveksi terpercaya sejak 2002</p>
            <h1>Kaos Custom</h1>
            <h1> Kualitas <span class="highlight">Premium</span></h1> 
            <h1> Harga Bersahabat</h1>
            <p class="hero-lead">PT Panji Usaha Mulia hadir untuk memenuhi kebutuhan konveksi kaos custom Anda. Dari komunitas, corporate, event, hingga merchandise. Kami siap produksi dengan standar terbaik.</p>
            <ul class="hero-points" aria-label="Keunggulan Utama">
                <li>Produksi paling lama 30 hari</li>
                <li>Min. 60 Pcs untuk minimum pemesanan custom</li>
                <li>Dipercaya ratusan client dari berbagai sektor</li>
            </ul>
            <div class="hero-actions">
                <a class="btn btn-brand" href="{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.orders.create') : (auth()->check() ? route('dashboard') : route('register')) }}">Mulai Custom Sekarang</a>
                <a class="btn btn-alt" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Masuk Akun</a>
            </div>
        </div>
        <div class="hero-stats" aria-label="Statistik Layanan">
            <article class="hero-stat">
                <strong>10+ Tahun</strong>
                <span>Pengalaman konveksi</span>
            </article>
            <article class="hero-stat">
                <strong>30 Hari</strong>
                <span>Maks. durasi produksi</span>
            </article>
            <article class="hero-stat">
                <strong>Min. 60 Pcs</strong>
                <span>Minimum order custom</span>
            </article>
        </div>
    </div>
</section>

<section class="catalog-wrap" aria-label="Katalog Produk">
    <div class="catalog-header">
        <h2 class="catalog-heading">Katalog Produk</h2>
        <p class="catalog-subheading">Pilih produk atau langsung lakukan customisasi sesuai kebutuhan Anda</p>
    </div>
    <div class="catalog-strip">
        @foreach($products as $product)
            <article class="catalog-item">
                @if(!empty($product['image']))
                    <img class="catalog-image" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}">
                @else
                    <div class="catalog-image-fallback">Preview katalog<br>segera hadir</div>
                @endif

                <div class="catalog-meta">
                    <h3>{{ $product['name'] }}</h3>
                    <p class="catalog-price">{{ $product['price_label'] ?? $product['price'] ?? '-' }}</p>
                </div>

                <div class="catalog-actions">
                    @if(!empty($product['specs']))
                        <a class="btn-ghost" href="{{ route('catalog.show', $product['slug']) }}">Lihat Detail</a>
                        <a href="{{ auth()->check() ? route('customer.orders.create', $product['preset']) : route('register') }}" class="btn btn-brand">Pesan</a>
                    @else
                        <a href="{{ route('catalog.show', $product['slug']) }}" class="btn-ghost">Lihat Detail</a>
                        <a href="{{ auth()->check() ? route('customer.orders.create') : route('register') }}" class="btn btn-brand">Pesan</a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</section>

<footer class="footer" aria-label="Footer">
    <div class="footer-container">
        <!-- Customer Service Section -->
        <div class="footer-section">
            <h3 class="footer-section-title">Customer Service</h3>
            
            <div class="footer-contact-item">
                <div class="footer-contact-icon">
                    <img src="{{ asset('images/whatsapp.jpg') }}" alt="WhatsApp">
                </div>
                <div class="footer-contact-text">
                    <a href="https://wa.me/6282129287094" target="_blank" rel="noopener noreferrer">
                        +62 821 2928 7094
                    </a>
                </div>
            </div>

            <div class="footer-contact-item">
                <div class="footer-contact-icon">
                    <img src="{{ asset('images/telp.png') }}" alt="Telepon">
                </div>
                <div class="footer-contact-text">
                    <a href="tel:+622272215924">
                        022-7215924
                    </a>
                </div>
            </div>
        </div>

        <!-- Information Section -->
        <div class="footer-section">
            <h3 class="footer-section-title">Information</h3>
            
            <div>
                <h4 style="margin: 0 0 0.5rem; font-size: 0.9rem; color: #7893ae; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Head Office</h4>
                <p class="footer-info-text">
                    Jl. Jendral Achmad Yani No. 909<br>
                    (Komplek Pertokoan Cicaheum)<br>
                    Bandung, Jawa Barat, Indonesia<br>
                    40125
                </p>
            </div>
        </div>
    </div>

    <div class="footer-divider"></div>

    <div class="footer-bottom">
        <p>&copy; 2026 PT Panji Usaha Mulia. All rights reserved.</p>
    </div>
</footer>

@endsection
