<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PT Panji Usaha Mulia' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        .sidebar-whatsapp-btn {
            width: 100%;
            border: 1px solid #25d366;
            border-radius: 10px;
            background: #25d366;
            color: #ffffff !important;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.58rem 0.7rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .sidebar-whatsapp-btn:hover {
            background: #20ba59;
            border-color: #20ba59;
            color: #ffffff !important;
            text-decoration: none;
        }

        .sidebar-whatsapp-btn svg {
            width: 14px;
            height: auto;
            fill: currentColor;
        }

        /* Fix Laravel Pagination */
        nav[role="navigation"] {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        nav[role="navigation"] > div:last-child > div:last-child > span {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        nav[role="navigation"] span.relative.inline-flex, 
        nav[role="navigation"] a.relative.inline-flex {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.85rem !important;
            text-decoration: none !important;
            line-height: 1 !important;
            border-radius: 6px !important;
            border: 1px solid #d7e4ee;
            background: #fff;
            color: #0d2749 !important;
            min-height: 38px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        nav[role="navigation"] a.relative.inline-flex:hover {
            background: #f8fafc;
            border-color: #99ccd7;
        }
        nav[role="navigation"] span[aria-current="page"] > span.relative.inline-flex {
            background: #0d2749 !important;
            color: #fff !important;
            border-color: #0d2749;
            font-weight: 700;
        }
        nav[role="navigation"] svg.w-5.h-5 {
            width: 1.2rem;
            height: 1.2rem;
            display: block;
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 400 700;
            font-display: swap;
            src: url("{{ asset('fonts/dm-sans/DMSans-latin.woff2') }}") format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA,
                U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191,
                U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 400 700;
            font-display: swap;
            src: url("{{ asset('fonts/dm-sans/DMSans-latin-ext.woff2') }}") format('woff2');
            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF,
                U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF,
                U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }

        @font-face {
            font-family: 'Playfair Display';
            font-style: normal;
            font-weight: 600 700;
            font-display: swap;
            src: url("{{ asset('fonts/playfair-display/PlayfairDisplay-latin.woff2') }}") format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA,
                U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191,
                U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        @font-face {
            font-family: 'Playfair Display';
            font-style: normal;
            font-weight: 600 700;
            font-display: swap;
            src: url("{{ asset('fonts/playfair-display/PlayfairDisplay-latin-ext.woff2') }}") format('woff2');
            unicode-range: U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF,
                U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF,
                U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }

        :root {
            --ink: #0c1a26;
            --muted: #4f6173;
            --line: #dbe5ec;
            --surface: #ffffff;
            --paper: #f4f8fb;
            --brand: #d95f18;
            --app-sidebar-width: 264px;
            --customer-topbar-height: 84px;
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
            justify-content: flex-start;
            gap: 1.2rem;
            background: linear-gradient(180deg, #132141 0%, #132141 100%);
            border: 0;
            border-radius: 0 0 40px 40px;
            padding: 1.35rem 1.45rem 1.8rem;
            position: sticky;
            top: 0;
            z-index: 1200;
            overflow: visible;
            box-shadow: 0 6px 14px rgba(8, 21, 40, 0.14);
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
            margin-right: auto;
            transform: translateY(4px);
        }

        .brand-accent {
            color: #e4b94e;
            margin-right: 0.35rem;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
            justify-content: flex-start;
            transform: translateY(4px);
        }

        .topbar-links {
            display: flex;
            align-items: center;
            gap: 1.55rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .topbar-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 0;
            padding: 0;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            color: #ffffff;
            background: transparent;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .topbar-links a:hover {
            color: #f3cf73;
            transform: translateY(-1px);
        }

        .topbar-help {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .topbar-help-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border: 0;
            border-radius: 0;
            padding: 0;
            background: transparent;
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .topbar-help-toggle:hover,
        .topbar-help:focus-within .topbar-help-toggle {
            color: #f3cf73;
            transform: translateY(-1px);
        }

        .topbar-help-toggle::after {
            content: "▾";
            font-size: 0.78rem;
            line-height: 1;
        }

        .topbar-help-menu {
            position: absolute;
            top: calc(100% + 0.55rem);
            right: 0;
            min-width: 210px;
            padding: 0.55rem;
            border: 1px solid rgba(12, 33, 55, 0.1);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 14px 32px rgba(12, 33, 55, 0.16);
            display: grid;
            gap: 0.28rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            z-index: 1300;
        }

        .topbar-help:hover .topbar-help-menu,
        .topbar-help:focus-within .topbar-help-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .topbar-help-menu a {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.75rem;
            border-radius: 10px;
            background: transparent;
            color: #10213a;
            font-size: 0.84rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .topbar-help-menu a:hover {
            background: #edf4fb;
            color: #0f4e74;
        }

        .topbar-auth {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .topbar-auth a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #35506a;
            border-radius: 999px;
            padding: 0.56rem 1rem;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .topbar-auth .menu-login {
            border-color: #1c3550;
            color: #1c3550;
            background: #ffffff;
        }

        .topbar-auth .menu-register {
            border-color: #c6a647;
            background: #c6a647;
            color: #0f2947;
        }

        .topbar-auth .menu-login:hover {
            border-color: #0d2749;
            color: #0d2749;
            transform: translateY(-1px);
        }

        .topbar-auth .menu-register:hover {
            border-color: #b8983f;
            background: #b8983f;
            color: #0f2947;
            transform: translateY(-1px);
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

        .customer-sidebar .sidebar-dropdown {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .customer-sidebar .sidebar-dropdown summary {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.52rem;
            border-left: 3px solid transparent;
            padding: 0.86rem 1rem;
            color: #a8bbcf;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }

        .customer-sidebar .sidebar-dropdown summary::-webkit-details-marker {
            display: none;
        }

        .customer-sidebar .sidebar-dropdown summary::after {
            content: "▾";
            margin-left: auto;
            font-size: 0.72rem;
            opacity: 0.8;
            transition: transform 0.2s ease;
        }

        .customer-sidebar .sidebar-dropdown[open] summary::after {
            transform: rotate(180deg);
        }

        .customer-sidebar .sidebar-dropdown summary:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #dbe8f5;
        }

        .customer-sidebar .sidebar-dropdown summary.active {
            border-left-color: #dfbf65;
            background: rgba(255, 255, 255, 0.08);
            color: #e8b53f;
        }

        .customer-sidebar .sidebar-dropdown-items {
            display: grid;
            padding: 0.2rem 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .customer-sidebar .sidebar-dropdown-items a {
            padding: 0.74rem 1rem 0.74rem 2.6rem;
            font-size: 0.71rem;
        }

        .sidebar-heading {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 800;
            color: #6d8496;
            letter-spacing: 0.05em;
            padding: 1.2rem 1rem 0.4rem;
            margin: 0;
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
            padding-bottom: 80px;
        }

        .auth-main.customer-main {
            grid-column: 2;
            padding-top: 0;
            padding: 0;
        }

        .auth-main.customer-main > * {
            width: min(1240px, calc(100% - 2.75rem));
            margin-left: auto;
            margin-right: auto;
        }

        .customer-shell-topbar {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, #122746 0%, #10223f 100%);
            border-bottom: 1px solid #193657;
            margin-bottom: 0;
            padding: 0 1.5rem 0 0;
            height: var(--customer-topbar-height);
            z-index: 80;
        }

        .topbar-search input:focus {
            background: rgba(255,255,255,0.1) !important;
            border-color: rgba(255,255,255,0.4) !important;
        }

        .topbar-notif-btn:hover {
            background: rgba(255,255,255,0.1) !important;
        }

        .notif-dropdown {
            transform-origin: top right;
            animation: dropdownFadeIn 0.2s ease-out;
        }

        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .topbar-profile:hover {
            opacity: 0.9;
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
            text-align: center;
        }

        .status-neutral { background: #e6eef6; color: #24445e; }
        .status-warning { background: #fff3e4; color: #9a4b14; }
        .status-success { background: #e7f7ee; color: #176841; }
        .status-danger { background: #fee8e8; color: #9a1d1d; }
        .status-info { background: #e6f4ff; color: #0d5f8f; }
        .status-accent { background: #f3ecff; color: #5a3893; }
        .status-primary { background: #ebefff; color: #2045a1; }
        .status-teal { background: #e0f5f2; color: #106c64; }
        .status-dark { background: #e6eaef; color: #1d2c3e; }

        .metric {
            font-size: 1.26rem;
            font-weight: 800;
            font-family: 'DM Sans', sans-serif;
            margin-top: 0.2rem;
        }

        .page-back-wrap {
            padding: 0.22rem 1rem 0.08rem;
        }

        .page-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            color: #b3b8bc;
            text-decoration: none;
            font-size: 0.94rem;
            font-weight: 700;
        }

        .page-back-btn:hover {
            color: #095999;
            text-decoration: underline;
        }

        .page-back-btn .arrow-icon {
            width: 18px;
            height: 18px;
            object-fit: contain;
            display: inline-block;
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
            .topbar {
                position: static;
                border-radius: 0 0 26px 26px;
                padding: 1rem 0.95rem 1.3rem;
            }
            .topbar .brand { margin-right: 0; }
            .menu { justify-content: flex-end; }
            .topbar-links,
            .topbar-auth {
                width: 100%;
                justify-content: flex-start;
            }
            .shell { padding: 0 0 1.3rem; }
        }
    </style>
</head>
<body>
<div class="shell">
    @auth
        @php
            $isCustomer = auth()->user()->hasRole('customer');
            $isFinance = strtolower((string) auth()->user()->role) === 'finance';
            $financePendingCount = 0;
            if ($isFinance) {
                $financePendingCount = \App\Models\Payment::where('status', 'pending')
                    ->whereHas('order', function ($query) {
                        $query->where('order_status', '!=', 'rejected');
                    })
                    ->count();
            }
        @endphp
        <div class="customer-shell-topbar">
            <!-- Header Kiri (Logo) -->
            <div class="topbar-left" style="width: var(--app-sidebar-width); border-right: 1px solid rgba(255,255,255,0.08); height: 100%; display: flex; align-items: center; padding-left: 1.5rem; flex-shrink: 0;">
                <a class="brand" href="{{ route('home') }}" style="margin: 0; padding: 0;">
                    <span class="brand-accent">PT PANJI</span>Usaha Mulia
                </a>
            </div>

            <!-- Header Kanan (Topbar Utama) -->
            <div class="topbar-right-content" style="flex: 1; display: flex; align-items: center; justify-content: space-between; padding-left: 1.5rem;">
                <!-- Judul Halaman -->
                <div class="topbar-title" style="color: #fff; font-size: 1.18rem; font-weight: 700; letter-spacing: 0.01em; font-family: 'Playfair Display', serif;">
                    @yield('header_title', $title ?? 'Dashboard')
                </div>

                <!-- Tools (Search, Notif, Profile) -->
                <div class="topbar-tools" style="display: flex; align-items: center; gap: 1.5rem;">
                    <!-- Search Bar -->
                    <div class="topbar-search" style="position: relative;">
                        <form action="/search" method="GET" style="margin: 0;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #8fa8c0; font-size: 0.9rem;">🔍︎</span>
                            <input type="text" name="q" value="{{ request('q', request('search')) }}" placeholder="Cari pesanan..." style="padding: 0.5rem 1rem 0.5rem 2.4rem; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.42); background: rgba(255, 255, 255, 0.09); color: #fff; font-size: 0.85rem; width: 240px; outline: none; transition: background 0.2s, border-color 0.2s;">
                        </form>
                    </div>

                    <!-- Notification Bell -->
                    <div class="topbar-notif-wrapper" style="position: relative;">
                        @php
                            $notifs = $globalNotifications ?? collect([]);
                            $notifCount = $notifs->count();
                        @endphp
                        <button class="topbar-notif-btn" onclick="document.getElementById('notifDropdown').style.display = document.getElementById('notifDropdown').style.display === 'none' ? 'block' : 'none';" style="background: none; border: none; color: #fff; font-size: 1.25rem; cursor: pointer; padding: 0.4rem; border-radius: 50%; transition: background 0.2s; position: relative; display: flex; align-items: center; justify-content: center; height: 38px; width: 38px;">
                            🔔
                            @if($notifCount > 0)
                                <span class="notif-badge" style="position: absolute; top: -2px; right: -2px; background: #d95f18; color: #fff; font-size: 0.6rem; font-weight: bold; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid #132141;">{{ $notifCount }}</span>
                            @endif
                        </button>
                        <!-- Dropdown Notifikasi -->
                        <div id="notifDropdown" class="notif-dropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: -10px; background: #ffffff; width: 340px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); padding: 0; z-index: 1000; border: 1px solid #eaeaea; overflow: hidden;">
                            <div style="padding: 1rem 1.2rem; border-bottom: 1px solid #eaeaea; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                                <h4 style="margin: 0; font-size: 0.95rem; color: #132141; font-weight: 700;">Notifikasi</h4>
                                @if($notifCount > 0)
                                    <span style="font-size: 0.7rem; color: #d95f18; font-weight: 600; cursor: pointer;">Tandai semua dibaca</span>
                                @endif
                            </div>
                            
                            <div style="max-height: 350px; overflow-y: auto;">
                                @forelse($notifs as $notif)
                                    <a href="{{ $notif['url'] }}" style="display: flex; align-items: flex-start; gap: 0.8rem; padding: 1rem 1.2rem; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: background 0.2s;">
                                        <div style="font-size: 1.2rem; flex-shrink: 0; padding-top: 0.1rem;">{{ $notif['icon'] }}</div>
                                        <div style="font-size: 0.82rem; color: #334155; line-height: 1.4;">
                                            {{ $notif['text'] }}
                                        </div>
                                    </a>
                                @empty
                                    <div style="font-size: 0.85rem; color: #64748b; text-align: center; padding: 2.5rem 1rem;">
                                        <div style="font-size: 2.5rem; margin-bottom: 0.8rem; opacity: 0.2;">🔕</div>
                                        Belum ada notifikasi baru.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Profile Avatar -->
                    <div class="topbar-profile" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 1.5rem;">
                        <div style="text-align: right; display: flex; flex-direction: column;">
                            <span style="color: #fff; font-size: 0.85rem; font-weight: 700; line-height: 1.2;">{{ strtolower(auth()->user()->name) }}</span>
                            <span style="color: #8fa8c0; font-size: 0.7rem; font-weight: 600; line-height: 1.2;">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                        <div class="avatar" style="width: 38px; height: 38px; border-radius: 50%; background: #d95f18; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; box-shadow: 0 2px 8px rgba(217, 95, 24, 0.4);">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="layoutAuth" class="layout-auth customer-layout">
            <aside id="appSidebar" class="sidebar customer-sidebar">
                <div class="customer-profile">
                    <p class="customer-profile-name">{{ strtolower(auth()->user()->name) }}</p>
                    <p class="customer-profile-role">{{ ucfirst(auth()->user()->role) }}</p>
                </div>

                <nav class="sidebar-nav">
                    @if(! ($isCustomer ?? auth()->user()->hasRole('customer')))
                        <div class="sidebar-heading">Utama</div>
                    @endif
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-ico"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/></svg></span>
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
                        <a href="{{ route('reports.orders') }}" class="{{ request()->routeIs('reports.orders') ? 'active' : '' }}">
                            <span class="nav-ico">📋</span>
                            <span class="nav-dot"></span>
                            Data Pesanan
                        </a>
                        <a href="{{ route('finance.index') }}" class="{{ request()->routeIs('finance.*') ? 'active' : '' }}">
                            <span class="nav-ico">💰</span>
                            <span class="nav-dot"></span>
                            Data Pembayaran
                            <span class="nav-count">{{ $financePendingCount ?? \App\Models\Payment::where('status', 'pending')->whereHas('order', function ($query) { $query->where('order_status', '!=', 'rejected'); })->count() }}</span>
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
                            Pembayaran
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('production', 'admin') && ! ($isCustomer ?? auth()->user()->hasRole('customer')))
                        <a href="{{ route('production.index') }}" class="{{ request()->routeIs('production.*') ? 'active' : '' }}">
                            <span class="nav-ico">🏭</span>
                            <span class="nav-dot"></span>
                            SPK & Produksi
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('admin'))
                        <a href="{{ route('reports.orders') }}" class="{{ request()->routeIs('reports.orders') ? 'active' : '' }}">
                            <span class="nav-ico">🛍️</span>
                            <span class="nav-dot"></span>
                            Pemesanan
                        </a>

                        <div class="sidebar-heading">Laporan</div>
                        <a href="{{ route('reports.orders-report') }}" class="{{ request()->routeIs('reports.orders-report') ? 'active' : '' }}">
                            <span class="nav-ico">📄</span>
                            <span class="nav-dot"></span>
                            Laporan Pemesanan
                        </a>
                        <a href="{{ route('reports.production') }}" class="{{ request()->routeIs('reports.production') ? 'active' : '' }}">
                            <span class="nav-ico">📄</span>
                            <span class="nav-dot"></span>
                            Laporan Produksi
                        </a>
                    @elseif (auth()->user()->hasRole('manager', 'owner'))
                        <a href="{{ route('reports.orders-report') }}" class="{{ request()->routeIs('reports.orders-report') ? 'active' : '' }}">
                            <span class="nav-ico">📊</span>
                            <span class="nav-dot"></span>
                            Laporan Pemesanan
                        </a>
                    @endif

                    @if (auth()->user()->hasRole('admin'))
                        <div class="sidebar-heading">Manajemen Data</div>
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span class="nav-ico">👥</span>
                            <span class="nav-dot"></span>
                            Kelola User
                        </a>
                        <a href="{{ route('admin.materials.index') }}" class="{{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
                            <span class="nav-ico">🧶</span>
                            <span class="nav-dot"></span>
                            Kelola Bahan
                        </a>
                        
                        <div class="sidebar-heading">Sistem</div>
                        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <span class="nav-ico">⚙️</span>
                            <span class="nav-dot"></span>
                            Pengaturan Sistem
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
                            <span class="nav-ico">📄</span>
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
                    <a href="https://wa.me/6282129287094" target="_blank" rel="noopener noreferrer" class="sidebar-whatsapp-btn">
                        <svg viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.504-5.714-1.464L0 24zm6.59-4.846c1.6.95 3.197 1.451 4.785 1.453 5.486 0 9.94-4.453 9.944-9.943.002-2.66-1.023-5.162-2.887-7.028-1.866-1.866-4.35-2.891-7.014-2.892-5.485 0-9.94 4.454-9.944 9.945-.001 1.684.449 3.327 1.307 4.795l-.997 3.64 3.73-.978zm11.235-6.721c-.3-.149-1.772-.874-2.047-.974-.275-.1-.475-.149-.675.15-.2.299-.775.974-.95 1.174-.175.2-.35.224-.65.075-.3-.15-1.265-.467-2.41-1.485-.89-.793-1.49-1.771-1.665-2.07-.175-.3-.019-.462.13-.611.135-.134.3-.349.45-.523.15-.174.2-.299.3-.499.1-.2.05-.375-.025-.524-.075-.15-.675-1.625-.925-2.224-.244-.589-.493-.51-.675-.519-.174-.009-.374-.01-.574-.01-.2 0-.525.075-.8.374-.275.299-1.05 1.024-1.05 2.499 0 1.475 1.075 2.899 1.225 3.099.15.199 2.116 3.23 5.125 4.532.715.31 1.273.495 1.707.633.718.228 1.37.195 1.887.118.575-.085 1.772-.724 2.022-1.424.25-.699.25-1.299.175-1.424-.075-.125-.275-.199-.575-.349z"/>
                        </svg>
                        <span>Hubungi WhatsApp</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="customer-logout-btn">Keluar</button>
                    </form>
                </div>
            </aside>

            <main class="auth-main customer-main">
                @php
                    $hideBackButton = request()->routeIs('home', 'login', 'register');
                @endphp
                @unless ($hideBackButton)
                    @php
                        $backFallback = ($isCustomer ?? auth()->user()->hasRole('customer')) ? route('home') : route('dashboard');
                    @endphp
                    <div class="page-back-wrap">
                        <a class="page-back-btn" href="{{ $backFallback }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
                            <img class="arrow-icon" src="{{ asset('images/leftarrow.png') }}" alt="Back">
                            <span>Back</span>
                        </a>
                    </div>
                @endunless

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
            <a class="brand" href="{{ route('home') }}"><span class="brand-accent">PT PANJI</span>Usaha Mulia</a>
            @if (request()->routeIs('home'))
                <div class="topbar-links" aria-label="Navigasi Landing">
                    <a href="#home">Home</a>
                    <a href="#about">Tentang</a>
                    <a href="#services">Layanan</a>
                    <a href="#testimonials">Testimoni</a>
                </div>
                <div class="topbar-help" aria-label="Bantuan Landing">
                    <button type="button" class="topbar-help-toggle" aria-haspopup="true" aria-expanded="false">Bantuan</button>
                    <div class="topbar-help-menu">
                        <a href="#pilihan-model">Pilihan Model</a>
                        <a href="#how-to-order">Cara Pemesanan</a>
                        <a href="#faq">FAQ</a>
                        <a href="#help-center">Pusat Bantuan</a>
                    </div>
                </div>
            @endif

            <div class="menu topbar-auth">
                <a class="menu-login" href="{{ route('track.index') }}" style="border-color: #0f9bab; color: #0f9bab; margin-right: 0.5rem;">Lacak Pesanan</a>
                <a class="menu-login" href="{{ route('login') }}">Login</a>
                <a class="menu-register" href="{{ route('register') }}">Register</a>
            </div>
        </div>

        <main style="margin-top: {{ request()->routeIs('home') ? '0' : '0.35rem' }};">
            @unless (request()->routeIs('home', 'login', 'register'))
                <div class="page-back-wrap" style="padding-left: 0; padding-right: 0;">
                    <a class="page-back-btn" href="{{ route('home') }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
                        <img class="arrow-icon" src="{{ asset('images/leftarrow.png') }}" alt="Back">
                        <span>Back</span>
                    </a>
                </div>
            @endunless

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
