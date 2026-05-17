@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
@php
    $needsAction = $pendingPayments > 0;
@endphp

<style>
    .finance-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem 1.5rem;
    }

    .finance-header h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .finance-header p {
        margin: 0.45rem 0 1.2rem;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .finance-alert {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid #f3c4b8;
        background: #fff4ef;
        padding: 0.72rem 0.95rem;
        color: #b63b22;
        font-size: 0.86rem;
    }

    .finance-alert a {
        color: #b63b22;
        font-weight: 700;
        text-decoration: underline;
    }

    .finance-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.95rem;
    }

    .finance-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 6px 16px rgba(13, 39, 73, 0.05);
    }

    .finance-card.verify {
        border-top-color: #cf3c2c;
    }

    .finance-card.income {
        border-top-color: #1f7a48;
    }

    .finance-card.outstanding {
        border-top-color: #c5a53f;
    }

    .finance-card.total {
        border-top-color: #2c7ebe;
    }

    .finance-card .label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .finance-card .value {
        margin-top: 0.55rem;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
    }

    .finance-card .note {
        margin-top: 0.42rem;
        color: #7f96ae;
        font-size: 0.78rem;
        font-weight: 600;
    }

    @media (max-width: 1100px) {
        .finance-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .finance-page {
            padding: 1rem;
        }

        .finance-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="finance-page">
    <div class="finance-header">
        <h1>Dashboard Keuangan</h1>
        <p>Pantau pembayaran dan verifikasi masuk hari ini</p>
    </div>

    @if ($pendingPayments > 0)
        <div class="finance-alert">
            <span>🔔</span>
            <span>Ada <strong>{{ $pendingPayments }} pembayaran</strong> yang menunggu verifikasi Anda.</span>
            <a href="{{ route('finance.index') }}">Verifikasi Sekarang →</a>
        </div>
    @endif

    <div class="finance-grid">
        <article class="finance-card verify">
            <div class="label">Perlu Verifikasi</div>
            <div class="value">{{ number_format((int) $pendingPayments, 0, ',', '.') }}</div>
            <div class="note">Bukti masuk</div>
        </article>

        <article class="finance-card income">
            <div class="label">Uang Masuk (Bulan Ini)</div>
            <div class="value">Rp {{ number_format((float) $monthlyVerifiedAmount, 0, ',', '.') }}</div>
            <div class="note">{{ number_format((int) $verifiedToday, 0, ',', '.') }} diverifikasi hari ini</div>
        </article>

        <article class="finance-card outstanding">
            <div class="label">Outstanding Pelunasan</div>
            <div class="value">Rp {{ number_format((float) $outstandingSettlement, 0, ',', '.') }}</div>
            <div class="note">Belum lunas</div>
        </article>

        <article class="finance-card total">
            <div class="label">Total Tagihan</div>
            <div class="value">Rp {{ number_format((float) $monthlyTotalTagihan, 0, ',', '.') }}</div>
            <div class="note">Bulan ini</div>
        </article>
    </div>
</section>
@endsection
