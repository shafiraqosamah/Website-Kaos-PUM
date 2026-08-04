@extends('layouts.app')

@section('header_title', 'Kelola Bahan')

@section('content')
<style>
    .materials-page {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.4rem 1.5rem;
    }

    .materials-page h1 {
        margin: 0 0 0.75rem;
        color: #0d2749;
        font-size: clamp(1.16rem, 1.8vw, 1.35rem);
        font-family: 'Playfair Display', serif;
    }

    .materials-page .desc {
        margin: 0.4rem 0 1rem;
        color: #7f96ae;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .materials-toolbar {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.8rem;
        margin-bottom: 0.9rem;
    }

    .materials-filter {
        display: grid;
        grid-template-columns: minmax(180px, 230px) minmax(180px, 220px) max-content;
        gap: 0.65rem;
        align-items: end;
    }

    .materials-filter label {
        display: block;
        margin-bottom: 0.3rem;
        color: #6f859d;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .materials-count {
        color: #4b5f75;
        font-size: 0.8rem;
        font-weight: 700;
        align-self: end;
    }

    .btn-filter {
        border: 1px solid #c8a949;
        background: #c8a949;
        color: #0f2947;
        border-radius: 10px;
        padding: 0.56rem 1.15rem;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        height: 40px;
        min-width: 120px;
        width: auto;
        justify-self: start;
    }

    .btn-filter:hover {
        background: #dfbf65;
        border-color: #dfbf65;
    }

    .materials-form-card {
        border: 1px solid #d9e2ec;
        border-radius: 12px;
        padding: 0.9rem;
        margin-bottom: 0.9rem;
        background: #fbfdff;
    }

    .materials-form-grid {
        display: grid;
        grid-template-columns: minmax(170px, 1fr) minmax(150px, 1fr) minmax(140px, 1fr) 130px auto;
        gap: 0.6rem;
        align-items: end;
    }

    .materials-table-wrap {
        border: 1px solid #d9e2ec;
        border-radius: 14px;
        overflow: auto;
        background: #ffffff;
    }

    .materials-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .materials-table thead {
        background: #e9eef4;
    }

    .materials-table th {
        padding: 0.72rem 0.8rem;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 700;
        color: #768ea7;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #dbe4ed;
    }

    .materials-table td {
        padding: 0.72rem 0.8rem;
        border-bottom: 1px solid #edf2f7;
        color: #1d3548;
        font-size: 0.82rem;
        vertical-align: middle;
    }

    .materials-table .action-col {
        text-align: center;
    }

    .materials-table .status-col {
        text-align: center;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.24rem 0.62rem;
        font-size: 0.74rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .status-active {
        background: #eaf7ee;
        border-color: #bde4cc;
        color: #1f7a48;
    }

    .status-inactive {
        background: #fbeff1;
        border-color: #eac6cb;
        color: #a33546;
    }

    .field-input {
        width: 100%;
        min-width: 120px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        padding: 0.46rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid transparent;
        white-space: nowrap;
        text-align: center;
    }

    .btn-toggle {
        background: #ffffff;
        border-color: #d3dde7;
        color: #214a65;
    }

    .btn-toggle:hover {
        background: #f5f9fc;
    }

    .btn-create {
        background: #122c5f;
        border-color: #122c5f;
        color: #ffffff;
    }

    .btn-create:hover {
        background: #314f88;
        border-color: #314f88;
    }

    .btn-delete {
        background: #ffffff;
        border-color: #e8c6cb;
        color: #a33546;
    }

    .btn-delete:hover {
        background: #fff5f6;
    }

    .btn-delete:disabled {
        background: #f6f7f9;
        border-color: #e4e7ed;
        color: #9aa8b7;
        cursor: not-allowed;
    }

    .btn-edit {
        background: #122c5f;
        border-color: #122c5f;
        color: #ffffff;
        text-decoration: none;
    }

    .btn-edit:hover {
        background: #314f88;
        border-color: #314f88;
    }

    .status-note {
        display: block;
        margin-top: 0.28rem;
        font-size: 0.72rem;
        color: #8799ac;
        text-align: center;
    }

    .pagination-wrap {
        margin-top: 0.8rem;
    }

    @media (max-width: 1100px) {
        .materials-toolbar {
            grid-template-columns: 1fr;
        }

        .materials-count {
            align-self: start;
        }

        .materials-filter,
        .materials-form-grid {
            grid-template-columns: 1fr 1fr;
        }

    }

    @media (max-width: 700px) {
        .materials-page {
            padding: 1rem;
        }

        .materials-filter,
        .materials-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="materials-page">
    <h1>Kelola Bahan</h1>

    <div class="materials-toolbar">
        <form method="GET" action="{{ route('admin.materials.index') }}" class="materials-filter">
            <div>
                <label for="q">Cari Bahan</label>
                <input id="q" type="text" name="q" value="{{ $search }}" placeholder="Nama atau kode bahan">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">Terapkan</button>
        </form>
        <div class="materials-count">Total: {{ number_format($materials->total(), 0, ',', '.') }} bahan</div>
    </div>

    <div class="materials-form-card">
        <form method="POST" action="{{ route('admin.materials.store') }}" class="materials-form-grid">
            @csrf
            <div>
                <label for="name">Nama Bahan</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="slug">Kode Bahan (opsional)</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="otomatis dari nama bahan">
            </div>
            <div>
                <label for="base_price">Harga /pcs (Rp)</label>
                <input id="base_price" name="base_price" type="number" min="1" value="{{ old('base_price') }}" required>
            </div>
            <div>
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active">
                    <option value="1" selected>Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-create">Tambah Bahan</button>
        </form>
    </div>

    <div class="materials-table-wrap">
        <table class="materials-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Kode Bahan</th>
                    <th>Harga /pcs</th>
                    <th>Warna</th>
                    <th class="status-col">Status Tampil</th>
                    <th class="action-col">Aksi Status</th>
                    <th class="action-col">Aksi Hapus</th>
                    <th class="action-col">Aksi Edit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($materials as $material)
                    @php
                        $isUsedInOrders = in_array($material->name, $usedMaterialNames ?? [], true);
                    @endphp
                    <tr>
                        <td>{{ ($materials->firstItem() ?? 1) + $loop->index }}</td>
                        <td>
                            <span>{{ $material->name }}</span>
                        </td>
                        <td>
                            <span>{{ $material->slug }}</span>
                        </td>
                        <td>
                            <span>Rp {{ number_format((int) ($material->base_price ?? 0), 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:3px; flex-wrap:wrap;">
                                @forelse ($material->colors->take(6) as $color)
                                    <span
                                        title="{{ $color->name }}"
                                        style="width:16px; height:16px; border-radius:50%; background:{{ $color->hex_code ?? '#ccc' }}; border:1px solid rgba(0,0,0,0.1); display:inline-block;"
                                    ></span>
                                @empty
                                    <span style="color:#a0b0c0; font-size:0.72rem;">—</span>
                                @endforelse
                                @if ($material->colors->count() > 6)
                                    <span style="font-size:0.7rem; color:#6f86a0; font-weight:600;">+{{ $material->colors->count() - 6 }}</span>
                                @endif
                            </div>
                            <span style="display:block; font-size:0.7rem; color:#8a9fb5; margin-top:2px;">{{ $material->colors->count() }} warna</span>
                        </td>
                        <td class="status-col">
                            <span class="status-pill {{ $material->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if ($isUsedInOrders)
                                <span class="status-note">Dipakai pada data pesanan</span>
                            @endif
                        </td>
                        <td class="action-col">
                            <form method="POST" action="{{ route('admin.materials.toggle', $material) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-toggle">{{ $material->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                        <td class="action-col">
                            <form method="POST" action="{{ route('admin.materials.destroy', $material) }}" onsubmit="return confirm('Hapus bahan {{ $material->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete" {{ $isUsedInOrders ? 'disabled' : '' }}>
                                    {{ $isUsedInOrders ? 'Tidak Bisa Dihapus' : 'Hapus' }}
                                </button>
                            </form>
                        </td>
                        <td class="action-col">
                            <a class="btn btn-edit" href="{{ route('admin.materials.edit', $material) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#7f96ae;">Belum ada data bahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($materials->hasPages())
        <div class="pagination-wrap">{{ $materials->links() }}</div>
    @endif
</section>
@endsection
