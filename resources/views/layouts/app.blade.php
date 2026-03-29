<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'PT Panji Usaha Mulia' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;700&display=swap');

        :root {
            --ink: #0c1a26;
            --muted: #4f6173;
            --line: #dbe5ec;
            --surface: #ffffff;
            --paper: #f4f8fb;
            --brand: #d95f18;
            --brand-2: #0f7b8f;
            --success: #0f8f60;
            --danger: #c22b2b;
            --customer-topbar-height: 52px;
            --app-sidebar-width: 230px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            min-height: 100vh;
            color: var(--ink);
            background:
                radial-gradient(1200px 420px at 10% -5%, #faf1eb 10%, transparent 55%),
                radial-gradient(900px 320px at 90% 0%, #faf1eb 10%, transparent 60%),
                var(--paper);
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }

        .shell {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: linear-gradient(180deg, #132141 100%, #f9fbfd 50%);
            border: 1px solid #d7e2ec;
            border-radius: 14px;
            padding: 0.74rem 1.02rem;
            position: sticky;
            top: 8px;
            backdrop-filter: blur(8px);
            z-index: 50;
        }

        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-family: 'DM Sans', sans-serif;
            font-size: clamp(0.95rem, 1.45vw, 1.15rem);
            font-weight: 800;
            color: #13283a;
            letter-spacing: 0.02em;
        }

        .topbar .brand {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.18rem, 1.95vw, 1.75rem);
            font-weight: 700;
            letter-spacing: 0;
            color: #ffffff;
        }

        .brand-accent {
            color: #e4b94e;
            margin-right: 0.35rem;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .topbar .menu a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #35506a;
            border-radius: 12px;
            padding: 0.58rem 1.25rem;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
        }

        .topbar .menu .menu-login {
            border-color: #1c3550;
            color: #1c3550;
            background: #ffffff;
        }

        .topbar .menu .menu-login:hover {
            border-color: #0d2749;
            color: #0d2749;
        }

        .topbar .menu .menu-register {
            border-color: #c6a647;
            background: #c6a647;
            color: #0f2947;
        }

        .topbar .menu .menu-register:hover {
            border-color: #b8983f;
            background: #b8983f;
            color: #0f2947;
        }

        .layout-auth {
            display: grid;
            grid-template-columns: var(--app-sidebar-width) 1fr;
            gap: 1rem;
            align-items: start;
        }

        .layout-auth.customer-layout {
            grid-template-columns: var(--app-sidebar-width) 1fr;
            gap: 0;
            min-height: calc(100vh - var(--customer-topbar-height));
            margin-top: var(--customer-topbar-height);
        }

        .layout-auth.sidebar-hidden {
            grid-template-columns: 1fr;
        }

        .layout-auth.sidebar-hidden .sidebar {
            display: none;
        }

        .sidebar {
            position: sticky;
            top: 10px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 0.9rem;
            display: grid;
            gap: 0.9rem;
        }

        .sidebar.customer-sidebar {
            position: fixed;
            top: var(--customer-topbar-height);
            left: 0;
            width: var(--app-sidebar-width);
            height: calc(100vh - var(--customer-topbar-height));
            min-height: 0;
            align-content: start;
            background: linear-gradient(180deg, #081f39 0%, #03182f 100%);
            border: 1px solid #0d2b4f;
            border-radius: 0;
            border-top: 0;
            color: #cfdae8;
            padding: 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 35;
        }

        .sidebar-brand {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.96rem;
            font-weight: 800;
            color: #10263a;
            text-decoration: none;
            padding: 0.2rem 0.15rem;
        }

        .sidebar-caption {
            margin: -0.4rem 0 0;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6d8093;
            font-weight: 700;
        }

        .sidebar-nav {
            display: grid;
            gap: 0.45rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.52rem;
            border: 1px solid #d5e1eb;
            border-radius: 10px;
            background: #fbfdff;
            color: #1d3448;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.48rem 0.6rem;
        }

        .customer-profile {
            padding: 1.15rem 1rem 0.92rem;
            border-bottom: 1px solid rgba(198, 166, 71, 0.16);
        }

        .customer-profile-name {
            margin: 0;
            color: #ffffff;
            font-family: 'Playfair Display', serif;
            font-size: 1.04rem;
            font-weight: 700;
            line-height: 1;
        }

        .customer-profile-role {
            margin: 0.22rem 0 0;
            color: #8fa8c0;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .customer-sidebar .sidebar-nav {
            gap: 0;
            padding: 0.9rem 0;
        }

        .customer-sidebar .sidebar-nav a {
            border: 0;
            border-radius: 0;
            background: transparent;
            color: #a8bbcf;
            padding: 0.86rem 1rem;
            font-size: 0.72rem;
            font-weight: 600;
            border-left: 3px solid transparent;
        }

        .customer-sidebar .sidebar-nav a.active {
            border-left-color: #dfbf65;
            background: rgba(255, 255, 255, 0.08);
            color: #e8b53f;
        }

        .customer-sidebar .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #dbe8f5;
        }

        .customer-sidebar .nav-count {
            margin-left: auto;
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.35rem;
            font-size: 0.7rem;
            line-height: 1;
            font-weight: 700;
            background: #d6432c;
            color: #ffffff;
        }

        .customer-sidebar .sidebar-nav a.active .nav-count {
            background: #e8b53f;
            color: #0f2947;
        }

        .customer-sidebar .nav-dot {
            display: none;
        }

        .nav-ico {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            font-size: 0.92rem;
            line-height: 1;
            opacity: 0.95;
        }

        .sidebar-nav a.active {
            border-color: #99ccd7;
            background: linear-gradient(135deg, #ecfbff, #f7fdff);
            color: #0d5a68;
        }

        .nav-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #9cb0c3;
            flex: 0 0 auto;
        }

        .sidebar-nav a.active .nav-dot {
            background: var(--brand-2);
        }

        .sidebar-footer {
            display: grid;
            gap: 0.55rem;
            border-top: 1px dashed #d7e4ee;
            padding-top: 0.75rem;
        }

        .customer-sidebar .sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(198, 166, 71, 0.16);
            padding: 0.8rem 1rem 0.95rem;
        }

        .customer-logout-btn {
            width: 100%;
            border: 1px solid #d9b34a;
            border-radius: 10px;
            background: transparent;
            color: #e8bd52;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.58rem 0.7rem;
            cursor: pointer;
            font-family: inherit;
        }

        .customer-logout-btn:hover {
            background: rgba(217, 179, 74, 0.14);
        }

        .sidebar-footer form {
            margin: 0;
        }

        .auth-main {
            min-width: 0;
        }

        .auth-main.customer-main {
            grid-column: 2;
            padding-top: 0;
            padding: 0;
        }

        .customer-shell-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, #122746 0%, #10223f 100%);
            border: 1px solid #193657;
            border-radius: 0;
            border-left: 0;
            border-right: 0;
            border-top: 0;
            margin-bottom: 0;
            padding: 0.68rem 0.95rem;
            min-height: var(--customer-topbar-height);
            z-index: 80;
        }

        .customer-shell-topbar .brand {
            font-family: 'Playfair Display', serif;
            color: #ffffff;
            font-size: clamp(1rem, 1.28vw, 1.18rem);
        }

        .auth-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.8rem;
            flex-wrap: wrap;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0.78rem 0.95rem;
            margin-bottom: 1rem;
        }

        .auth-topbar.customer-topbar {
            background: linear-gradient(180deg, #122746 0%, #10223f 100%);
            border-color: #193657;
            border-radius: 0 14px 14px 0;
            margin-bottom: 1rem;
            padding: 0.62rem 0.88rem;
        }

        .auth-topbar.customer-topbar .brand {
            font-family: 'Playfair Display', serif;
            color: #ffffff;
            font-size: clamp(0.86rem, 1.1vw, 1.02rem);
        }

        .customer-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .customer-role-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(223, 191, 101, 0.65);
            background: rgba(223, 191, 101, 0.16);
            color: #f2d58f;
            padding: 0.22rem 0.62rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .customer-name {
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: lowercase;
        }

        .customer-topbar-logout {
            border: 1px solid #d7b145;
            border-radius: 10px;
            background: transparent;
            color: #d7b145;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.48rem 1.05rem;
            cursor: pointer;
            font-family: inherit;
        }

        .customer-topbar-logout:hover {
            background: rgba(215, 177, 69, 0.16);
        }

        .auth-topbar-left {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sidebar-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #fff;
            color: #1f3448;
            font-size: 1.15rem;
            line-height: 1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-toggle:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        .role-pill {
            border-radius: 999px;
            border: 1px solid #bfd4e2;
            background: #f3faff;
            color: #0f6072;
            padding: 0.2rem 0.62rem;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 10px 30px rgba(15, 43, 61, 0.05);
        }

        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }

        h1, h2, h3 {
            margin: 0 0 0.6rem;
            font-family: 'Playfair Display', serif;
            line-height: 1.2;
        }

        .muted { color: var(--muted); }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 11px;
            padding: 0.52rem 0.86rem;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-brand { background: linear-gradient(135deg, var(--brand), #f59e5a); color: #fff; }
        .btn-alt { background: linear-gradient(135deg, var(--brand-2), #36a4bb); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }

        .alert {
            border-radius: 11px;
            padding: 0.7rem 0.85rem;
            margin-bottom: 0.8rem;
            font-size: 0.86rem;
        }

        .alert-ok { background: #e8f9f1; border: 1px solid #bbe9d1; color: #0d6747; }
        .alert-err { background: #fdeaea; border: 1px solid #f3c4c4; color: #902020; }

        label { display: block; font-weight: 700; font-size: 0.8rem; margin-bottom: 0.3rem; }
        input, select, textarea {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #cfdbe6;
            padding: 0.52rem 0.62rem;
            font: inherit;
            background: #fff;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.48rem; border-bottom: 1px solid #e4ecf2; font-size: 0.83rem; }
        th { color: #31495d; font-weight: 700; }

        .status-pill {
            display: inline-block;
            padding: 0.28rem 0.54rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            background: #e6eef6;
        }

        .status-neutral { background: #e6eef6; color: #24445e; }
        .status-warning { background: #fff3e4; color: #9a4b14; }
        .status-success { background: #e7f7ee; color: #176841; }
        .status-danger { background: #fee8e8; color: #9a1d1d; }
        .status-info { background: #e6f4ff; color: #0d5f8f; }
        .status-accent { background: #f3ecff; color: #5a3893; }

        .metric {
            font-size: 1.26rem;
            font-weight: 800;
            font-family: 'DM Sans', sans-serif;
            margin-top: 0.2rem;
        }

        @media (max-width: 980px) {
            .layout-auth {
                grid-template-columns: 1fr;
            }

            .layout-auth.customer-layout {
                grid-template-columns: 1fr;
                min-height: auto;
                margin-top: var(--customer-topbar-height);
            }

            .auth-main.customer-main {
                grid-column: 1;
            }

            .sidebar.customer-sidebar {
                position: static;
                top: auto;
                left: auto;
                width: auto;
                height: auto;
                min-height: 0;
                overflow: visible;
                z-index: auto;
                border-radius: 0;
            }

            .sidebar {
                position: static;
            }
        }

        @media (max-width: 820px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .topbar { position: static; }
            .menu { justify-content: flex-end; }
            .shell { padding: 0.75rem 0.75rem 1.3rem; }
        }
    </style>
</head>
<body>
<div class="shell">
    @auth
        @php($isCustomer = auth()->user()->hasRole('customer'))
        @php($isFinance = strtolower((string) auth()->user()->role) === 'finance')
        @php
            $financePendingCount = 0;
            if ($isFinance) {
                $financePendingCount = \App\Models\Payment::where('status', 'pending')
                    ->whereNotNull('proof_path')
                    ->whereNotNull('destination_bank')
                    ->whereNotNull('sender_bank_name')
                    ->whereNotNull('sender_account_name')
                    ->count();
            }
        @endphp
        <div class="customer-shell-topbar">
            <a class="brand" href="{{ route('dashboard') }}"><span class="brand-accent">PT Panji</span>Usaha Mulia</a>
            <div class="customer-topbar-right">
                <span class="customer-role-badge">
                    {{ match (strtolower((string) auth()->user()->role)) {
                        'customer' => '👤 Customer',
                        'finance' => '💰 Finance',
                        'production' => '🏭 Produksi',
                        'admin' => '⚙️ Admin',
                        'manager' => '👔 Manager',
                        'owner' => '👑 Owner',
                        default => ucfirst((string) auth()->user()->role),
                    } }}
                </span>
                <span class="customer-name">{{ strtolower(auth()->user()->name) }}</span>
            </div>
        </div>

        <div id="layoutAuth" class="layout-auth customer-layout">
            <aside id="appSidebar" class="sidebar customer-sidebar">
                <div class="customer-profile">
                    <p class="customer-profile-name">{{ strtolower(auth()->user()->name) }}</p>
                    <p class="customer-profile-role">{{ ucfirst(auth()->user()->role) }}</p>
                </div>

                <nav class="sidebar-nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-ico">🏠</span>
                        <span class="nav-dot"></span>
                        Dashboard
                    </a>

                    @if (($isCustomer ?? auth()->user()->hasRole('customer')))
                        <a href="{{ route('customer.orders.create') }}" class="{{ request()->routeIs('customer.orders.create') ? 'active' : '' }}">
                            <span class="nav-ico">🎨</span>
                            <span class="nav-dot"></span>
                            Buat Pesanan Custom
                        </a>
                        <a href="{{ route('customer.orders.index') }}" class="{{ request()->routeIs('customer.orders.index') && request('focus') !== 'status' ? 'active' : '' }}">
                            <span class="nav-ico">📄</span>
                            <span class="nav-dot"></span>
                            Riwayat Pesanan
                        </a>
                        <a href="{{ route('customer.orders.index', ['focus' => 'status']) }}" class="{{ request()->routeIs('customer.orders.index') && request('focus') === 'status' ? 'active' : '' }}">
                            <span class="nav-ico">🏭</span>
                            <span class="nav-dot"></span>
                            Status Produksi
                        </a>
                    @endif

                    @if (($isFinance ?? (strtolower((string) auth()->user()->role) === 'finance')))
                        <a href="{{ route('finance.index') }}" class="{{ request()->routeIs('finance.*') ? 'active' : '' }}">
                            <span class="nav-ico">🔍</span>
                            <span class="nav-dot"></span>
                            Verifikasi Pembayaran
                            <span class="nav-count">{{ $financePendingCount ?? \App\Models\Payment::where('status', 'pending')->whereNotNull('proof_path')->whereNotNull('destination_bank')->whereNotNull('sender_bank_name')->whereNotNull('sender_account_name')->count() }}</span>
                        </a>
                        <a href="{{ route('reports.finance') }}" class="{{ request()->routeIs('reports.finance') ? 'active' : '' }}">
                            <span class="nav-ico">📊</span>
                            <span class="nav-dot"></span>
                            Laporan Keuangan
                        </a>
                    @elseif (auth()->user()->hasRole('finance', 'admin') && ! ($isCustomer ?? auth()->user()->hasRole('customer')))
                        <a href="{{ route('finance.index') }}" class="{{ request()->routeIs('finance.*') ? 'active' : '' }}">
                            <span class="nav-ico">💰</span>
                            <span class="nav-dot"></span>
                            Keuangan
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('production', 'admin') && ! ($isCustomer ?? auth()->user()->hasRole('customer')))
                        <a href="{{ route('production.index') }}" class="{{ request()->routeIs('production.*') ? 'active' : '' }}">
                            <span class="nav-ico">🏭</span>
                            <span class="nav-dot"></span>
                            Produksi
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('admin', 'manager', 'owner'))
                        <a href="{{ route('reports.orders') }}" class="{{ request()->routeIs('reports.orders') ? 'active' : '' }}">
                            <span class="nav-ico">📊</span>
                            <span class="nav-dot"></span>
                            Laporan Pemesanan
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('finance', 'manager', 'owner') && ! ($isFinance ?? (strtolower((string) auth()->user()->role) === 'finance')))
                        <a href="{{ route('reports.finance') }}" class="{{ request()->routeIs('reports.finance') ? 'active' : '' }}">
                            <span class="nav-ico">📈</span>
                            <span class="nav-dot"></span>
                            Laporan Keuangan
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('production', 'manager', 'owner'))
                        <a href="{{ route('reports.production') }}" class="{{ request()->routeIs('reports.production') ? 'active' : '' }}">
                            <span class="nav-ico">🧵</span>
                            <span class="nav-dot"></span>
                            Laporan Produksi
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('manager', 'owner'))
                        <a href="{{ route('reports.executive') }}" class="{{ request()->routeIs('reports.executive') ? 'active' : '' }}">
                            <span class="nav-ico">🗂️</span>
                            <span class="nav-dot"></span>
                            Laporan Manajemen
                        </a>
                    @endif
                </nav>

                <div class="sidebar-footer">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="customer-logout-btn">Keluar</button>
                    </form>
                </div>
            </aside>

            <main class="auth-main customer-main">

                @if (session('success'))
                    <div class="alert alert-ok">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-err">
                        <ul style="margin:0; padding-left: 1rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    @else
        <div class="topbar">
            <a class="brand" href="{{ route('home') }}"><span class="brand-accent">PT Panji</span>Usaha Mulia</a>
            <div class="menu">
                <a class="menu-login" href="{{ route('login') }}">Masuk</a>
                <a class="menu-register" href="{{ route('register') }}">Daftar</a>
            </div>
        </div>

        <main style="margin-top: 1rem;">
            @if (session('success'))
                <div class="alert alert-ok">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-err">
                    <ul style="margin:0; padding-left: 1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    @endauth
</div>

@auth
<script>
(() => {
    const layoutAuth = document.getElementById('layoutAuth');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (!layoutAuth || !sidebarToggle) {
        return;
    }

    const storageKey = 'sidebar-collapsed';

    const applyState = (collapsed) => {
        layoutAuth.classList.toggle('sidebar-hidden', collapsed);
        sidebarToggle.setAttribute('aria-expanded', (!collapsed).toString());
        sidebarToggle.title = collapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar';
    };

    applyState(window.localStorage.getItem(storageKey) === '1');

    sidebarToggle.addEventListener('click', () => {
        const collapsed = !layoutAuth.classList.contains('sidebar-hidden');
        applyState(collapsed);
        window.localStorage.setItem(storageKey, collapsed ? '1' : '0');
    });
})();
</script>
@endauth
</body>
</html>
