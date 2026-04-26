@extends('layouts.app')

@section('content')
@php
    $roleBadge = match ($userData->role) {
        'customer' => ['CUSTOMER', 'status-info'],
        'finance' => ['KEUANGAN/FINANCE', 'status-success'],
        'production' => ['PRODUKSI', 'status-accent'],
        'admin' => ['ADMIN', 'status-warning'],
        'manager' => ['MANAGER', 'status-danger'],
        'owner' => ['OWNER', 'status-neutral'],
        default => [strtoupper((string) $userData->role), 'status-neutral'],
    };
@endphp

<style>
    .user-detail-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .user-detail-title {
        margin: 0;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.1rem, 1.7vw, 1.35rem);
    }

    .user-detail-subtitle {
        margin: 0.4rem 0 1.1rem;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .detail-item {
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 0.8rem 0.9rem;
        background: #ffffff;
    }

    .detail-item .label {
        font-size: 0.72rem;
        color: #7f96ae;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .detail-item .value {
        margin-top: 0.35rem;
        font-size: 0.88rem;
        color: #0d2749;
        font-weight: 700;
        word-break: break-word;
    }

    .detail-actions {
        margin-top: 1rem;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 1rem;
        border-radius: 10px;
        border: 1px solid #d9dfe6;
        background: #f3f5f7;
        color: #0d2749;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .back-btn:hover {
        background: #e9edf2;
    }

    @media (max-width: 720px) {
        .user-detail-page {
            padding: 1rem;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="user-detail-page">
    <h1 class="user-detail-title">Detail User</h1>
    <p class="user-detail-subtitle">Informasi akun terdaftar di sistem</p>

    <div class="detail-grid">
        <div class="detail-item">
            <div class="label">Nama</div>
            <div class="value">{{ $userData->name }}</div>
        </div>
        <div class="detail-item">
            <div class="label">Role</div>
            <div class="value"><span class="status-pill {{ $roleBadge[1] }}">{{ $roleBadge[0] }}</span></div>
        </div>
        <div class="detail-item">
            <div class="label">Email</div>
            <div class="value">{{ $userData->email }}</div>
        </div>
        <div class="detail-item">
            <div class="label">No. HP</div>
            <div class="value">{{ $userData->phone ?: '-' }}</div>
        </div>
        <div class="detail-item">
            <div class="label">Perusahaan</div>
            <div class="value">{{ $userData->company_name ?: '-' }}</div>
        </div>
        <div class="detail-item">
            <div class="label">Terdaftar</div>
            <div class="value">{{ optional($userData->created_at)->translatedFormat('d F Y H:i') }}</div>
        </div>
    </div>

    <div class="detail-actions">
        <a href="{{ route('admin.users.index') }}" class="back-btn">Kembali ke Manajemen User</a>
    </div>
</section>
@endsection
