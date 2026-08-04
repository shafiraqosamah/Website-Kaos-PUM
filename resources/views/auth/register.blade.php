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

    /* Validation styles */
    .auth-form .form-group {
        display: grid;
        gap: 0.35rem;
    }

    .auth-form label {
        font-weight: 700;
        font-size: 0.88rem;
        color: #0f2745;
        margin-bottom: 0.1rem;
    }

    .auth-form input {
        height: 48px;
        width: 100%;
        padding: 0 1rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .auth-form input:focus {
        border-color: #dfbf65;
        box-shadow: 0 0 0 3px rgba(223, 191, 101, 0.15);
    }

    .auth-form input.is-invalid {
        border-color: #e53e3e !important;
        background-color: #fffafb;
    }

    .auth-form input.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.15);
    }

    .error-message {
        color: #e53e3e;
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.15rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
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
        <h1 class="auth-title" style="text-align: center;">Buat Akun Baru</h1>
        <p class="auth-subtitle" style="text-align: center;">Daftar untuk mulai memesan kaos custom dengan proses yang cepat.</p>

        <form method="POST" action="{{ route('register.store') }}" class="auth-form" novalidate>
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" class="@error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" class="@error('email') is-invalid @enderror" required>
                @error('email')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="phone">No. WhatsApp</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" class="@error('phone') is-invalid @enderror" required>
                @error('phone')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 karakter, kombinasi huruf & angka" class="@error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" class="@error('password_confirmation') is-invalid @enderror" required>
                @error('password_confirmation')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn auth-submit">Daftar &amp; Mulai Pesan</button>
        </form>

        <div class="auth-links">
            <span>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></span>
            <a href="{{ route('home') }}">Kembali ke Beranda</a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.auth-form');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');

    function showError(input, message) {
        input.classList.add('is-invalid');
        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.innerHTML = '⚠️ ' + message;
    }

    function removeError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    function validateName() {
        if (!nameInput.value.trim()) {
            showError(nameInput, 'Nama lengkap wajib diisi.');
            return false;
        }
        removeError(nameInput);
        return true;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            showError(emailInput, 'Email wajib diisi.');
            return false;
        } else if (!email.includes('@')) {
            showError(emailInput, 'Email harus memiliki tanda @.');
            return false;
        } else if (!emailRegex.test(email)) {
            showError(emailInput, 'Format email tidak valid.');
            return false;
        }
        removeError(emailInput);
        return true;
    }

    function validatePhone() {
        const phone = phoneInput.value.trim();
        const phoneRegex = /^(\+62|62|08)[0-9]{8,13}$/;
        if (!phone) {
            showError(phoneInput, 'Nomor WhatsApp wajib diisi.');
            return false;
        } else if (!phoneRegex.test(phone)) {
            showError(phoneInput, 'Format nomor WhatsApp salah (harus diawali 08/62/+62, panjang 10-15 angka).');
            return false;
        }
        removeError(phoneInput);
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;
        const letterRegex = /[a-zA-Z]/;
        const numberRegex = /[0-9]/;

        if (!password) {
            showError(passwordInput, 'Password wajib diisi.');
            return false;
        } else if (password.length < 8) {
            showError(passwordInput, 'Password minimal harus 8 karakter.');
            return false;
        } else if (!letterRegex.test(password) || !numberRegex.test(password)) {
            showError(passwordInput, 'Password harus berupa kombinasi huruf dan angka.');
            return false;
        }
        removeError(passwordInput);
        return true;
    }

    function validateConfirm() {
        const confirmVal = confirmInput.value;
        const passwordVal = passwordInput.value;
        if (!confirmVal) {
            showError(confirmInput, 'Konfirmasi password wajib diisi.');
            return false;
        } else if (confirmVal !== passwordVal) {
            showError(confirmInput, 'Konfirmasi password tidak cocok.');
            return false;
        }
        removeError(confirmInput);
        return true;
    }

    // Input listeners for real-time validation (on input/typing)
    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmail);
    phoneInput.addEventListener('input', validatePhone);
    passwordInput.addEventListener('input', validatePassword);
    confirmInput.addEventListener('input', validateConfirm);

    form.addEventListener('submit', function (e) {
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isPhoneValid = validatePhone();
        const isPasswordValid = validatePassword();
        const isConfirmValid = validateConfirm();

        if (!isNameValid || !isEmailValid || !isPhoneValid || !isPasswordValid || !isConfirmValid) {
            e.preventDefault();
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});
</script>
@endsection
