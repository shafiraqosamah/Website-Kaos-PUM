@extends('layouts.app')

@section('content')
@php
    $verificationLabel = static function (?string $status): string {
        return match ($status) {
            'verified' => 'Terverifikasi',
            'revision_requested' => 'Ajukan Kembali',
            default => 'Menunggu Verifikasi',
        };
    };

    $verificationClass = static function (?string $status): string {
        return match ($status) {
            'verified' => 'status-success',
            'revision_requested' => 'status-danger',
            default => 'status-warning',
        };
    };
@endphp

<style>
    .orders-control-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.5rem 2rem;
    }

    .orders-head {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .orders-head h1 {
        margin: 0;
        font-size: clamp(1.18rem, 1.8vw, 1.4rem);
        line-height: 1.08;
        color: #0d2749;
        font-family: 'Playfair Display', serif;
    }

    .orders-head p {
        margin: 0.45rem 0 0;
        color: #8ca0b7;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .month-form {
        display: flex;
        gap: 0.6rem;
        align-items: end;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.95rem;
        margin-top: 1rem;
    }

    .stats-card {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 1rem 1.05rem;
        border-top: 4px solid #c6d3df;
        box-shadow: 0 6px 16px rgba(13, 39, 73, 0.05);
    }

    .stats-card.total {
        border-top-color: #2c7ebe;
    }

    .stats-card.verified {
        border-top-color: #0f8f60;
    }

    .stats-card.revision {
        border-top-color: #cf3c2c;
    }

    .stats-label {
        color: #8da1b7;
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }

    .stats-value {
        margin-top: 0.55rem;
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.15rem, 1.55vw, 1.38rem);
        line-height: 1;
        color: #0d2749;
        font-weight: 700;
    }

    .orders-table-card {
        margin-top: 1rem;
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        overflow: auto;
        background: #ffffff;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    .orders-table thead {
        background: #e9eef4;
    }

    .orders-table th {
        padding: 0.72rem 0.85rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.72rem;
        color: #768ea7;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #dbe4ed;
        vertical-align: top;
    }

    .orders-table td {
        padding: 0.76rem 0.85rem;
        border-bottom: 1px solid #edf2f7;
        color: #1d3548;
        font-size: 0.78rem;
        vertical-align: top;
    }

    .orders-table td:last-child {
        text-align: right;
    }

    .orders-table tbody tr:hover {
        background: #fbfdff;
    }

    .orders-table .status-col {
        text-align: center;
    }

    .orders-table .status-col .muted-mini {
        text-align: center;
    }

    .note-preview {
        margin-top: 0.42rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 320px;
        margin-left: auto;
        margin-right: auto;
    }

    .note-open-btn {
        margin-top: 0.38rem;
        border: none;
        background: transparent;
        color: #0f5f8e;
        border-radius: 0;
        font-size: 0.74rem;
        font-weight: 700;
        padding: 0;
        cursor: pointer;
        text-decoration: underline;
    }

    .note-open-btn:hover {
        color: #0b4c71;
    }

    .note-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(9, 27, 43, 0.54);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 1100;
    }

    .note-modal-backdrop.is-open {
        display: flex;
    }

    .note-modal {
        width: min(640px, 100%);
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #d6e3ef;
        box-shadow: 0 20px 42px rgba(8, 26, 42, 0.28);
        overflow: hidden;
    }

    .note-modal-head {
        padding: 0.8rem 0.95rem;
        border-bottom: 1px solid #e5eef6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
    }

    .note-modal-head h3 {
        margin: 0;
        color: #0d2749;
        font-size: 0.98rem;
        font-family: 'Playfair Display', serif;
    }

    .note-modal-close {
        border: 1px solid #d2dfe9;
        background: #ffffff;
        color: #49637a;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.28rem 0.62rem;
        cursor: pointer;
    }

    .note-modal-close:hover {
        background: #f3f7fb;
    }

    .note-modal-body {
        padding: 0.9rem 0.95rem 1rem;
    }

    .note-modal-order {
        color: #5f7890;
        font-size: 0.78rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .note-modal-text {
        margin: 0;
        color: #24445e;
        font-size: 0.85rem;
        line-height: 1.58;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .muted-mini {
        color: #7f96ae;
        font-size: 0.73rem;
    }

    .action-buttons {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        gap: 0.55rem;
    }

    .action-buttons .btn {
        width: 118px;
        text-align: center;
        white-space: nowrap;
    }

    .action-buttons .action-detail {
        width: auto;
        min-width: 0;
        align-self: center;
    }

    .action-buttons .action-stack {
        display: grid;
        gap: 0.4rem;
        justify-items: end;
    }

    .btn-xs {
        font-size: 0.76rem;
        padding: 0.42rem 0.7rem;
        border-radius: 9px;
    }

    .btn-revision-link {
        background: #b63b22;
        border: 1px solid #b63b22;
        color: #ffffff;
    }

    .btn-revision-link:hover {
        background: #9f2f1a;
        border-color: #9f2f1a;
        color: #ffffff;
    }

    @media (max-width: 1080px) {
        .orders-control-page {
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .month-form {
            width: 100%;
        }
    }
</style>

<section class="orders-control-page">
    <div class="orders-head">
        <div>
            <h1>Data Pesanan Pelanggan</h1>
            <p>Kontrol pesanan pelanggan dengan kesediaan</p>
        </div>
        <form method="GET" action="{{ route('reports.orders') }}" class="month-form">
            <div>
                <label for="month" style="margin-bottom:0.2rem;">Pilih Bulan</label>
                <input id="month" type="month" name="month" value="{{ $monthInput }}">
            </div>
            <button class="btn btn-brand" type="submit">Tampilkan</button>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stats-card total">
            <div class="stats-label">Jumlah Pesanan</div>
            <div class="stats-value">{{ number_format($orderCount, 0, ',', '.') }}</div>
        </div>
        <div class="stats-card verified">
            <div class="stats-label">Terverifikasi</div>
            <div class="stats-value">{{ number_format($verifiedCount, 0, ',', '.') }}</div>
        </div>
        <div class="stats-card revision">
            <div class="stats-label">Pengajuan Kembali</div>
            <div class="stats-value">{{ number_format((int) ($revisionRequestedCount ?? 0), 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="orders-table-card">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Order</th>
                    <th>Pemesan</th>
                    <th>Tanggal Pesan</th>
                    <th>Jumlah PCS</th>
                    <th>Total Harga</th>
                    <th>Produk</th>
                    <th class="status-col">Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $status = (string) ($order->admin_verification_status ?: 'pending');
                    @endphp
                    <tr>
                        <td>{{ ($orders->firstItem() ?? 1) + $loop->index }}</td>
                        <td>
                            <strong>{{ $order->order_code }}</strong>
                        </td>
                        <td>
                            <div><strong>{{ $order->customer_name ?: ($order->user->name ?? '-') }}</strong></div>
                            <div>{{ $order->user->email ?? '-' }}</div>
                            <div>{{ $order->user->phone ?? '-' }}</div>
                        </td>
                        <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ number_format((int) $order->total_pcs, 0, ',', '.') }} pcs</td>
                        <td>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</td>
                        <td>{{ $order->product_name ?: ($order->product_model ?: '-') }}</td>
                        <td class="status-col">
                            <span class="status-pill {{ $verificationClass($status) }}">{{ $verificationLabel($status) }}</span>
                            @if ($order->admin_verification_note)
                                <div class="muted-mini note-preview">Catatan: {{ $order->admin_verification_note }}</div>
                                <button
                                    type="button"
                                    class="note-open-btn"
                                    data-note-order="{{ $order->order_code }}"
                                    data-note-text="{{ $order->admin_verification_note }}"
                                >
                                    Lihat Catatan
                                </button>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a class="btn btn-outline btn-xs action-detail" href="{{ route('reports.orders.show', ['order' => $order, 'month' => request('month')]) }}">Detail</a>
                                @if ($status !== 'verified')
                                    <div class="action-stack">
                                        <a class="btn btn-brand btn-xs" href="{{ route('reports.orders.show', ['order' => $order, 'month' => request('month')]) }}#verify-section">Verifikasi</a>
                                        <a class="btn btn-xs btn-revision-link" href="{{ route('reports.orders.show', ['order' => $order, 'month' => request('month')]) }}#revision-section">Ajukan Kembali</a>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="muted">Belum ada data pesanan pada bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div style="margin-top:0.8rem;">
            {{ $orders->links() }}
        </div>
    @endif
</section>

<div id="noteModalBackdrop" class="note-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="noteModalTitle">
    <div class="note-modal">
        <div class="note-modal-head">
            <h3 id="noteModalTitle">Catatan Verifikasi</h3>
            <button type="button" id="noteModalClose" class="note-modal-close">Tutup</button>
        </div>
        <div class="note-modal-body">
            <div id="noteModalOrder" class="note-modal-order">-</div>
            <p id="noteModalText" class="note-modal-text"></p>
        </div>
    </div>
</div>

<script>
(() => {
    const backdrop = document.getElementById('noteModalBackdrop');
    const closeBtn = document.getElementById('noteModalClose');
    const orderText = document.getElementById('noteModalOrder');
    const noteText = document.getElementById('noteModalText');

    if (!backdrop || !closeBtn || !orderText || !noteText) {
        return;
    }

    const closeModal = () => {
        backdrop.classList.remove('is-open');
        orderText.textContent = '-';
        noteText.textContent = '';
    };

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const openBtn = target.closest('.note-open-btn');

        if (openBtn instanceof HTMLElement) {
            orderText.textContent = 'Order: ' + (openBtn.dataset.noteOrder || '-');
            noteText.textContent = openBtn.dataset.noteText || '-';
            backdrop.classList.add('is-open');
            return;
        }

        if (target === backdrop || target === closeBtn) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && backdrop.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
</script>
@endsection
