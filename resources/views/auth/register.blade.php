@extends('layouts.app')

@section('content')
<style>
    .auth-screen {
        min-height: calc(100vh - 140px);
        display: grid;
        place-items: center;
        padding: 1rem 0;
    }

    .auth-card {
        width: min(100%, 580px);
        border-radius: 24px;
        border: 1px solid #d6e0ea;
        background: #ffffff;
        box-shadow: 0 22px 48px rgba(12, 39, 68, 0.16);
        padding: 1.4rem;
    }

    .auth-title {
        font-size: clamp(1.7rem, 3.4vw, 2.2rem);
        margin-bottom: 0.2rem;
    }

    .auth-subtitle {
        margin: 0 0 1.1rem;
        color: #68839d;
        line-height: 1.55;
    }

    .auth-form {
        display: grid;
        gap: 0.9rem;
    }

    .auth-form input {
        height: 48px;
    }

    .auth-submit {
        width: 100%;
        height: 50px;
        font-size: 1.05rem;
        font-weight: 800;
        background: #dfbf65;
        color: #0f2745;
        margin-top: 0.25rem;
    }

    .auth-links {
        margin-top: 1rem;
        text-align: center;
        display: grid;
        gap: 0.45rem;
        font-size: 0.98rem;
    }

    .auth-links a {
        color: #ab8730;
        font-weight: 700;
        text-decoration: none;
    }

    .auth-links span {
        color: #7490aa;
    }

    @media (max-width: 640px) {
        .auth-card {
            border-radius: 18px;
            padding: 1.1rem;
        }
    }
</style>

<section class="auth-screen" aria-label="Halaman Register">
    <div class="auth-card">
        <h1 class="auth-title">Buat Akun Baru</h1>
        <p class="auth-subtitle">Daftar untuk mulai memesan kaos custom dengan proses yang cepat.</p>

        <form method="POST" action="{{ route('register.store') }}" class="auth-form">
            @csrf
            <div>
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required>
            </div>
            <div>
                <label>No. WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 8 karakter" required>
            </div>
            <div>
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn auth-submit">Daftar &amp; Mulai Pesan</button>
        </form>

        <div class="auth-links">
            <span>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></span>
            <a href="{{ route('home') }}">Kembali ke Beranda</a>
        </div>
    </div>
</section>
@endsection
