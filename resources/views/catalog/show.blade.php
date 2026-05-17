@extends('layouts.app')

@section('content')
<style>
    .catalog-detail {
        border: 1px solid #d7e3ec;
        border-radius: 18px;
        background: #fff;
        padding: 1rem;
    }

    .catalog-detail-grid {
        display: grid;
        grid-template-columns: minmax(320px, 1fr) minmax(360px, 1.15fr);
        gap: 1rem;
        align-items: start;
    }

    .catalog-detail-image-wrap {
        border: 1px solid #d5e1ea;
        border-radius: 14px;
        background: #fff;
        padding: 0.8rem;
        min-height: 420px;
        display: grid;
        place-items: center;
    }

    .catalog-detail-image {
        width: 100%;
        height: auto;
        max-height: 560px;
        object-fit: contain;
        border-radius: 10px;
    }

    .catalog-detail-image-fallback {
        width: 100%;
        min-height: 320px;
        border: 1px dashed #c8d8e5;
        border-radius: 10px;
        background: #f7fbff;
        color: #6f879b;
        font-size: 1rem;
        font-weight: 700;
        display: grid;
        place-items: center;
    }

    .catalog-breadcrumb {
        color: #6d8496;
        font-size: 0.95rem;
        margin-bottom: 0.45rem;
    }

    .catalog-title {
        margin: 0;
        font-family: 'Sora', sans-serif;
        font-size: clamp(2rem, 4.6vw, 3.3rem);
        line-height: 1.05;
        color: #31343b;
    }

    .catalog-sub {
        margin: 0.8rem 0 0;
        color: #5e7284;
        font-style: italic;
        font-size: 1.1rem;
    }

    .catalog-keterangan {
        margin-top: 1rem;
        color: #203748;
        font-size: 1.04rem;
        line-height: 1.55;
        display: grid;
        gap: 0.7rem;
    }

    .catalog-spec-box {
        margin-top: 1rem;
        border: 1px solid #d8e4ee;
        border-radius: 12px;
        background: #f8fcff;
        padding: 0.8rem;
    }

    .catalog-spec-box h3 {
        margin: 0 0 0.45rem;
        font-size: 1rem;
    }

    .catalog-spec-box ul {
        margin: 0;
        padding-left: 1rem;
        display: grid;
        gap: 0.25rem;
        color: #1f3f56;
    }

    .catalog-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    @media (max-width: 960px) {
        .catalog-detail-grid {
            grid-template-columns: 1fr;
        }

        .catalog-detail-image-wrap {
            min-height: 280px;
        }
    }
</style>

<section class="catalog-detail">
    <div class="catalog-detail-grid">
        <div class="catalog-detail-image-wrap">
            @if(!empty($product['image']))
                <img class="catalog-detail-image" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}">
            @else
                <div class="catalog-detail-image-fallback">Preview katalog segera hadir</div>
            @endif
        </div>

        <div>
            <p class="catalog-breadcrumb">Home / Katalog / {{ $product['name'] }}</p>
            <h1 class="catalog-title">{{ $product['name'] }}</h1>
            <p class="catalog-sub">- {{ $product['min_order'] ?? 'Minimal order 60 pcs' }}</p>

            <div class="catalog-keterangan">
                @foreach(($product['long_desc'] ?? []) as $text)
                    <p style="margin:0;">{{ $text }}</p>
                @endforeach
            </div>

            @if(!empty($product['specs']))
                <div class="catalog-spec-box">
                    <h3>Keterangan Spesifikasi</h3>
                    <ul>
                        @foreach($product['specs'] as $label => $value)
                            <li><strong>{{ $label }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="catalog-actions">
                <a class="btn btn-alt" href="{{ route('home') }}">Kembali ke Katalog</a>
            </div>
        </div>
    </div>
</section>
@endsection
