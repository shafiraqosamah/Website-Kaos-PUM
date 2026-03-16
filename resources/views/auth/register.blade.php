@extends('layouts.app')

@section('content')
<div class="card" style="max-width: 560px; margin: 0 auto;">
    <h1>Register Pelanggan</h1>
    <p class="muted">Silakan daftar terlebih dahulu sebelum melakukan pemesanan kustomisasi.</p>

    <form method="POST" action="{{ route('register.store') }}" class="grid" style="gap:0.8rem;">
        @csrf
        <div>
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label>No. HP</label>
            <input type="text" name="phone" value="{{ old('phone') }}">
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-brand">Buat Akun</button>
    </form>
</div>
@endsection
