@extends('layouts.app')

@section('content')
@php
    $roleMeta = [
        'customer' => ['label' => 'CUSTOMER', 'class' => 'pill-customer', 'icon' => '👤'],
        'finance' => ['label' => 'KEUANGAN/FINANCE', 'class' => 'pill-finance', 'icon' => '💰'],
        'production' => ['label' => 'PRODUKSI', 'class' => 'pill-production', 'icon' => '🏭'],
        'admin' => ['label' => 'ADMIN', 'class' => 'pill-admin', 'icon' => '🛠️'],
        'manager' => ['label' => 'MANAGER', 'class' => 'pill-manager', 'icon' => '👔'],
        'owner' => ['label' => 'OWNER', 'class' => 'pill-owner', 'icon' => '👑'],
    ];
@endphp

<style>
    .users-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .users-header h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .users-header p {
        margin: 0.45rem 0 1.2rem;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .users-toolbar {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: end;
        margin-bottom: 1rem;
    }

    .filter-form {
        display: grid;
        grid-template-columns: minmax(160px, 200px) minmax(240px, 1fr) auto;
        gap: 0.7rem;
        align-items: end;
    }

    .filter-form label {
        margin-bottom: 0.3rem;
        color: #6f859d;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .filter-submit {
        border: 1px solid #c8a949;
        background: #c8a949;
        color: #0f2947;
        border-radius: 10px;
        padding: 0.56rem 1rem;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        height: 40px;
    }

    .filter-submit:hover {
        background: #dfbf65;
        border-color: #dfbf65;
    }

    .users-count {
        justify-self: end;
        color: #6f859d;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .users-table-wrap {
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        overflow: auto;
        background: #ffffff;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 920px;
    }

    .users-table thead {
        background: #e9eef4;
    }

    .users-table th {
        padding: 0.8rem 1rem;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 700;
        color: #768ea7;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #dbe4ed;
    }

    .users-table td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #edf2f7;
        color: #1d3548;
        font-size: 0.82rem;
        vertical-align: middle;
    }

    .users-table tbody tr:hover {
        background: #fbfdff;
    }

    .user-name {
        font-weight: 700;
        color: #0d2749;
        font-size: 0.88rem;
    }

    .role-pill-custom {
        display: inline-flex;
        align-items: center;
        gap: 0.42rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.3rem 0.72rem;
        line-height: 1;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .pill-customer {
        background: #eaf4ff;
        border-color: #c6ddf4;
        color: #2e79b2;
    }

    .pill-finance {
        background: #eaf6ef;
        border-color: #c8ddd2;
        color: #0f7a50;
    }

    .pill-production {
        background: #efeafd;
        border-color: #d6ccf5;
        color: #6a47ad;
    }

    .pill-admin {
        background: #fff3e5;
        border-color: #efd9be;
        color: #c57e06;
    }

    .pill-manager {
        background: #fdeeee;
        border-color: #f1d3d3;
        color: #c43e2a;
    }

    .pill-owner {
        background: #f0f2f5;
        border-color: #d8dde3;
        color: #253446;
    }

    .detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.45rem 0.9rem;
        border-radius: 10px;
        border: 1px solid #d9dfe6;
        background: #f3f5f7;
        color: #0d2749;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .detail-btn:hover {
        background: #e9edf2;
    }

    .empty-row {
        text-align: center;
        color: #7f96ae;
        font-size: 0.85rem;
        padding: 1rem;
    }

    .new-user-card {
        margin-top: 1rem;
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        padding: 1rem;
        background: #ffffff;
    }

    .new-user-card h2 {
        margin: 0;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-size: 0.95rem;
    }

    .new-user-card p {
        margin: 0.35rem 0 0.9rem;
        color: #7f96ae;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .new-user-form {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .new-user-form .form-col-full {
        grid-column: span 3;
    }

    .create-btn {
        border: 1px solid #c8a949;
        background: #c8a949;
        color: #0f2947;
        border-radius: 10px;
        padding: 0.6rem 1.1rem;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
    }

    .create-btn:hover {
        background: #dfbf65;
        border-color: #dfbf65;
    }

    .pagination-wrap {
        margin-top: 0.85rem;
    }

    @media (max-width: 1080px) {
        .users-toolbar {
            grid-template-columns: 1fr;
        }

        .users-count {
            justify-self: start;
        }

        .new-user-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .new-user-form .form-col-full {
            grid-column: span 2;
        }
    }

    @media (max-width: 720px) {
        .users-page {
            padding: 1rem;
        }

        .filter-form {
            grid-template-columns: 1fr;
        }

        .new-user-form {
            grid-template-columns: 1fr;
        }

        .new-user-form .form-col-full {
            grid-column: span 1;
        }
    }
</style>

<section class="users-page">
    <div class="users-header">
        <h1>Kelola User</h1>
        <p>Semua akun yang terdaftar di sistem</p>
    </div>

    <div class="users-toolbar">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-form">
            <div>
                <label for="role">Sorting Role</label>
                <select id="role" name="role" onchange="this.form.submit()">
                    @foreach ($roleOptions as $value => $label)
                        <option value="{{ $value }}" {{ $roleFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="q">Cari User</label>
                <input id="q" type="text" name="q" value="{{ $search }}" placeholder="Cari nama, email, no. HP, perusahaan">
            </div>
            <button type="submit" class="filter-submit">Terapkan</button>
        </form>
        <div class="users-count">Daftar User ({{ number_format($users->total(), 0, ',', '.') }})</div>
    </div>

    <div class="users-table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Perusahaan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $row)
                    @php
                        $meta = $roleMeta[$row->role] ?? ['label' => strtoupper((string) $row->role), 'class' => 'pill-owner', 'icon' => '•'];
                    @endphp
                    <tr>
                        <td><span class="user-name">{{ $row->name }}</span></td>
                        <td>
                            <span class="role-pill-custom {{ $meta['class'] }}">
                                <span>{{ $meta['icon'] }}</span>
                                <span>{{ $meta['label'] }}</span>
                            </span>
                        </td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->phone ?: '-' }}</td>
                        <td>{{ $row->company_name ?: '-' }}</td>
                        <td><a href="{{ route('admin.users.show', $row) }}" class="detail-btn">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-row">Belum ada data user untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $users->links() }}
    </div>

    <section class="new-user-card">
        <h2>Tambah Pengguna</h2>
        <p>Admin dapat menambahkan akun baru seperti Finance, Produksi, Manager, atau Customer.</p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="new-user-form">
            @csrf
            <div>
                <label for="name">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label for="role_new">Role</label>
                <select id="role_new" name="role" required>
                    <option value="">Pilih role</option>
                    @foreach ($roleOptions as $value => $label)
                        @if ($value !== 'all')
                            <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="phone">No. HP</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label for="company_name">Perusahaan (jika ada)</label>
                <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Nama perusahaan">
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div>
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <div class="form-col-full">
                <button type="submit" class="create-btn">Submit</button>
            </div>
        </form>
    </section>
</section>
@endsection
