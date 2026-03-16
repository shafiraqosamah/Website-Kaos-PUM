<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'PT Panji Usaha Mulia' }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=Space+Grotesk:wght@400;500;700&display=swap');

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
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 420px at 10% -5%, #d2ecf3 10%, transparent 55%),
                radial-gradient(900px 320px at 90% 0%, #ffe2ce 10%, transparent 60%),
                var(--paper);
        }

        .shell {
            width: min(1160px, 92vw);
            margin: 1.2rem auto 2.5rem;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0.8rem 1rem;
            position: sticky;
            top: 8px;
            backdrop-filter: blur(8px);
            z-index: 50;
        }

        .brand {
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: var(--ink);
            text-decoration: none;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .menu a, .menu button {
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            border-radius: 10px;
            padding: 0.45rem 0.8rem;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
        }

        .menu a:hover, .menu button:hover {
            border-color: var(--brand);
            color: var(--brand);
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
            font-family: 'Sora', sans-serif;
            line-height: 1.2;
        }

        .muted { color: var(--muted); }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 11px;
            padding: 0.58rem 0.95rem;
            text-decoration: none;
            font-weight: 700;
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
            font-size: 0.92rem;
        }

        .alert-ok { background: #e8f9f1; border: 1px solid #bbe9d1; color: #0d6747; }
        .alert-err { background: #fdeaea; border: 1px solid #f3c4c4; color: #902020; }

        label { display: block; font-weight: 700; font-size: 0.92rem; margin-bottom: 0.3rem; }
        input, select, textarea {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #cfdbe6;
            padding: 0.6rem 0.7rem;
            font: inherit;
            background: #fff;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.56rem; border-bottom: 1px solid #e4ecf2; font-size: 0.94rem; }
        th { color: #31495d; font-weight: 700; }

        .status-pill {
            display: inline-block;
            padding: 0.28rem 0.54rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            background: #e6eef6;
        }

        .metric {
            font-size: 1.7rem;
            font-weight: 700;
            font-family: 'Sora', sans-serif;
            margin-top: 0.2rem;
        }

        @media (max-width: 820px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .topbar { position: static; }
            .menu { justify-content: flex-end; }
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="topbar">
        <a class="brand" href="{{ route('home') }}">PT Panji Usaha Mulia</a>
        <div class="menu">
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                @if (auth()->user()->role === 'customer')
                    <a href="{{ route('customer.orders.index') }}">Pesanan Saya</a>
                    <a href="{{ route('customer.orders.create') }}">Custom Kaos</a>
                @endif
                @if (in_array(auth()->user()->role, ['finance', 'admin'], true))
                    <a href="{{ route('finance.index') }}">Keuangan</a>
                @endif
                @if (in_array(auth()->user()->role, ['production', 'admin'], true))
                    <a href="{{ route('production.index') }}">Produksi</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
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
</div>
</body>
</html>
