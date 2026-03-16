@extends('layouts.app')

@section('content')
<div class="card" style="max-width: 520px; margin: 0 auto;">
    <h1>Login Pelanggan / Staff</h1>
    <p class="muted">Masuk untuk mengakses dashboard sesuai peran Anda.</p>

    <form method="POST" action="{{ route('login.store') }}" class="grid" style="gap:0.8rem;">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <label style="display:flex; align-items:center; gap:0.4rem; font-weight:500;">
            <input type="checkbox" name="remember" style="width:auto;"> Ingat saya
        </label>
        <button type="submit" class="btn btn-brand">Masuk</button>
    </form>
</div>
@endsection
