@extends('layouts.app')

@section('content')
<style>
    .material-edit-card {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.2rem 1.3rem;
        max-width: 760px;
    }

    .material-edit-card h1 {
        margin: 0 0 0.35rem;
        color: #0d2749;
        font-size: clamp(1.16rem, 1.8vw, 1.35rem);
        font-family: 'Playfair Display', serif;
    }

    .material-edit-card p {
        margin: 0 0 1rem;
        color: #7f96ae;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .material-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem 0.85rem;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .material-edit-grid label {
        display: block;
        margin-bottom: 0.3rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #20435c;
    }

    .material-edit-grid input,
    .material-edit-grid select {
        width: 100%;
        border: 1px solid #c3d2e2;
        border-radius: 10px;
        padding: 0.55rem 0.68rem;
        font-size: 0.82rem;
        color: #13283a;
        background: #fff;
    }

    .material-edit-actions {
        margin-top: 0.95rem;
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .material-edit-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 0.56rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-primary {
        background: #122c5f;
        border-color: #122c5f;
        color: #fff;
    }

    .btn-primary:hover {
        background: #314f88;
        border-color: #314f88;
    }

    .btn-secondary {
        background: #ffffff;
        border-color: #d3dde7;
        color: #214a65;
    }

    .btn-secondary:hover {
        background: #f5f9fc;
    }

    @media (max-width: 760px) {
        .material-edit-card {
            padding: 1rem;
        }

        .material-edit-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="material-edit-card">
    <h1>Edit Bahan</h1>
    <p>Perbarui data bahan dan harga per pcs.</p>

    <form method="POST" action="{{ route('admin.materials.update', $material) }}">
        @csrf
        @method('PUT')

        <div class="material-edit-grid">
            <div>
                <label for="name">Nama Bahan</label>
                <input id="name" name="name" type="text" value="{{ old('name', $material->name) }}" required>
            </div>

            <div>
                <label for="slug">Kode Bahan (opsional)</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug', $material->slug) }}" placeholder="otomatis dari nama bahan">
            </div>

            <div>
                <label for="base_price">Harga /pcs (Rp)</label>
                <input id="base_price" name="base_price" type="number" min="1" value="{{ old('base_price', (int) ($material->base_price ?? 85000)) }}" required>
            </div>

            <div>
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active">
                    <option value="1" {{ old('is_active', $material->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('is_active', $material->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="material-edit-actions">
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            <a class="btn btn-secondary" href="{{ route('admin.materials.index') }}">Kembali</a>
        </div>
    </form>
</section>
@endsection
