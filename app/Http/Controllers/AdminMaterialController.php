<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMaterialController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = Material::query()->orderBy('sort_order')->orderBy('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        }

        if ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $materials = $query->paginate(15)->withQueryString();
        $usedMaterialNames = Order::query()
            ->select('fabric')
            ->whereNotNull('fabric')
            ->where('fabric', '!=', '')
            ->distinct()
            ->pluck('fabric')
            ->map(static fn ($name): string => trim((string) $name))
            ->filter(static fn (string $name): bool => $name !== '')
            ->values()
            ->all();

        return view('admin.materials.index', [
            'materials' => $materials,
            'search' => $search,
            'status' => $status,
            'usedMaterialNames' => $usedMaterialNames,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:materials,name'],
            'slug' => ['nullable', 'string', 'max:170', 'unique:materials,slug'],
            'base_price' => ['required', 'integer', 'min:1', 'max:2000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        $nextSortOrder = (int) Material::query()->max('sort_order') + 1;

        Material::create([
            'name' => trim((string) $validated['name']),
            'slug' => $slug !== '' ? Str::slug($slug) : Str::slug((string) $validated['name']),
            'base_price' => (int) $validated['base_price'],
            'sort_order' => $nextSortOrder,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('admin.materials.index')->with('success', 'Bahan baru berhasil ditambahkan.');
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('materials', 'name')->ignore($material->id)],
            'slug' => ['nullable', 'string', 'max:170', Rule::unique('materials', 'slug')->ignore($material->id)],
            'base_price' => ['required', 'integer', 'min:1', 'max:2000000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));

        $material->update([
            'name' => trim((string) $validated['name']),
            'slug' => $slug !== '' ? Str::slug($slug) : Str::slug((string) $validated['name']),
            'base_price' => (int) $validated['base_price'],
            'is_active' => (bool) ($validated['is_active'] ?? $material->is_active),
        ]);

        return redirect()->route('admin.materials.index')->with('success', 'Data bahan berhasil diperbarui.');
    }

    public function edit(Material $material): View
    {
        return view('admin.materials.edit', [
            'material' => $material,
        ]);
    }

    public function toggle(Material $material): RedirectResponse
    {
        $material->update([
            'is_active' => ! (bool) $material->is_active,
        ]);

        return redirect()->route('admin.materials.index')->with('success', 'Status bahan berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $isUsedInOrders = Order::query()
            ->where('fabric', $material->name)
            ->exists();

        if ($isUsedInOrders) {
            return redirect()
                ->route('admin.materials.index')
                ->withErrors(['material' => 'Bahan tidak bisa dihapus karena sudah dipakai pada data pesanan. Nonaktifkan bahan ini jika ingin menyembunyikannya.']);
        }

        $material->delete();

        return redirect()->route('admin.materials.index')->with('success', 'Bahan berhasil dihapus.');
    }
}
