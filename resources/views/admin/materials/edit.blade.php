@extends('layouts.app')

@section('content')
<style>
    .material-edit-card {
        background: #ffffff;
        border: 1px solid #d9e2ea;
        border-radius: 14px;
        padding: 1.2rem 1.3rem;
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

    /* Color management section */
    .color-section {
        margin-top: 1.4rem;
        border-top: 1px solid #e4ecf3;
        padding-top: 1.2rem;
    }

    .color-section h2 {
        margin: 0 0 0.25rem;
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        color: #0d2749;
    }

    .color-section .desc {
        margin: 0 0 0.85rem;
        color: #7f96ae;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .color-checklist {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
        gap: 0.45rem;
    }

    .color-check-item {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.42rem 0.55rem;
        border: 1px solid #dce5ee;
        border-radius: 9px;
        background: #fbfdff;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
    }

    .color-check-item:hover {
        border-color: #b3c6d9;
        background: #f0f5fa;
    }

    .color-check-item.is-checked {
        border-color: #4a7fc1;
        background: #edf3fb;
    }

    .color-check-item input[type="checkbox"] {
        accent-color: #1c4e8a;
        width: 15px;
        height: 15px;
        cursor: pointer;
    }

    .color-swatch {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1px solid rgba(0,0,0,0.12);
        flex-shrink: 0;
    }

    .color-check-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1d3548;
    }

    .color-check-hex {
        font-size: 0.7rem;
        color: #8a9fb5;
        margin-left: auto;
    }

    .color-actions {
        margin-top: 0.65rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .color-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        padding: 0.45rem 0.8rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .btn-save-colors {
        background: #1c6a47;
        border-color: #1c6a47;
        color: #fff;
    }

    .btn-save-colors:hover {
        background: #258a5c;
    }

    /* Add new color form */
    .add-color-form {
        margin-top: 1rem;
        border: 1px solid #dce5ee;
        border-radius: 10px;
        padding: 0.75rem 0.85rem;
        background: #f8fbfe;
    }

    .add-color-form h3 {
        margin: 0 0 0.5rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #20435c;
    }

    .add-color-grid {
        display: grid;
        grid-template-columns: 1fr 100px auto;
        gap: 0.5rem;
        align-items: end;
    }

    .add-color-grid label {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: #5f7a94;
    }

    .add-color-grid input[type="text"] {
        width: 100%;
        border: 1px solid #c3d2e2;
        border-radius: 8px;
        padding: 0.45rem 0.58rem;
        font-size: 0.8rem;
        color: #13283a;
    }

    .add-color-grid input[type="color"] {
        width: 100%;
        height: 36px;
        border: 1px solid #c3d2e2;
        border-radius: 8px;
        padding: 2px;
        cursor: pointer;
        background: #fff;
    }

    .btn-add-color {
        background: #20435c;
        border-color: #20435c;
        color: #ffffff;
        border-radius: 8px;
        padding: 0.45rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        height: 36px;
    }

    .btn-add-color:hover {
        background: #265d88;
    }

    @media (max-width: 760px) {
        .material-edit-card {
            padding: 1rem;
        }

        .material-edit-grid {
            grid-template-columns: 1fr;
        }

        .color-checklist {
            grid-template-columns: 1fr;
        }

        .add-color-grid {
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

    {{-- Color Management Section --}}
    <div class="color-section">
        <h2>🎨 Warna Tersedia</h2>
        <p class="desc">Centang warna yang tersedia untuk bahan ini. Warna yang dipilih akan muncul di form pemesanan customer.</p>

        <form method="POST" action="{{ route('admin.materials.colors.sync', $material) }}">
            @csrf

            <div class="color-checklist">
                @foreach ($allColors as $color)
                    @php $isChecked = in_array($color->id, $assignedColorIds); @endphp
                    <label class="color-check-item {{ $isChecked ? 'is-checked' : '' }}">
                        <input
                            type="checkbox"
                            name="color_ids[]"
                            value="{{ $color->id }}"
                            {{ $isChecked ? 'checked' : '' }}
                            onchange="this.closest('.color-check-item').classList.toggle('is-checked', this.checked)"
                        >
                        <span class="color-swatch" style="background-color: {{ $color->hex_code ?? '#ccc' }}"></span>
                        <span class="color-check-label">{{ $color->name }}</span>
                        <span class="color-check-hex">{{ $color->hex_code }}</span>
                    </label>
                @endforeach
            </div>

            @if ($allColors->isEmpty())
                <p style="color:#8a9fb5; font-size:0.8rem;">Belum ada data warna. Tambahkan warna baru di bawah.</p>
            @endif

            <div class="color-actions">
                <button type="submit" class="btn btn-save-colors">💾 Simpan Warna ({{ count($assignedColorIds) }} dipilih)</button>
            </div>
        </form>

        {{-- Add new color --}}
        <div class="add-color-form">
            <h3>➕ Tambah Warna Baru</h3>
            <form method="POST" action="{{ route('admin.materials.colors.store', $material) }}">
                @csrf
                <div class="add-color-grid">
                    <div>
                        <label for="color_name">Nama Warna</label>
                        <input id="color_name" name="color_name" type="text" placeholder="cth: Merah Marun" required>
                    </div>
                    <div>
                        <label for="hex_code">Kode Warna</label>
                        <input id="hex_code" name="hex_code" type="color" value="#1E1E1E">
                    </div>
                    <button type="submit" class="btn-add-color">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
