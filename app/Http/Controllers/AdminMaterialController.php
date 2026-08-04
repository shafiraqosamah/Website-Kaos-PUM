<?php

namespace App\Http\Controllers;

use App\Models\Color;
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

        $query = Material::query()->with('colors')->orderBy('sort_order')->orderBy('id');

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
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_file' => ['nullable', 'image', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:500'],
            'suitable_for' => ['nullable', 'string', 'max:500'],
            'design_application' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));

        $updateData = [
            'name' => trim((string) $validated['name']),
            'slug' => $slug !== '' ? Str::slug($slug) : Str::slug((string) $validated['name']),
            'base_price' => (int) $validated['base_price'],
            'is_active' => (bool) ($validated['is_active'] ?? $material->is_active),
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('image_file')) {
            if ($material->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($material->image_path);
            }
            $updateData['image_path'] = $request->file('image_file')->store('materials', 'public');
        }

        $processArray = function ($input) {
            if (!$input) return null;
            $items = preg_split('/[\n,]+/', $input);
            $items = array_map('trim', $items);
            $items = array_filter($items, fn($v) => $v !== '');
            return empty($items) ? null : array_values($items);
        };

        $updateData['tags'] = $processArray($validated['tags'] ?? null);
        $updateData['suitable_for'] = $processArray($validated['suitable_for'] ?? null);
        $updateData['design_application'] = $processArray($validated['design_application'] ?? null);

        $material->update($updateData);

        return back()->with('success', 'Data bahan berhasil diperbarui.');
    }

    public function edit(Material $material): View
    {
        $material->load('colors');

        $allColors = Color::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $assignedColorIds = $material->colors->pluck('id')->all();

        return view('admin.materials.edit', [
            'material' => $material,
            'allColors' => $allColors,
            'assignedColorIds' => $assignedColorIds,
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

    /**
     * Sync assigned colors for a material (checkbox form).
     */
    public function syncColors(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer', 'exists:colors,id'],
        ]);

        $colorIds = $validated['color_ids'] ?? [];

        $syncData = [];
        foreach ($colorIds as $index => $colorId) {
            $syncData[(int) $colorId] = ['sort_order' => $index + 1];
        }

        $material->colors()->sync($syncData);

        return redirect()
            ->route('admin.materials.edit', $material)
            ->with('success', 'Warna bahan berhasil diperbarui. (' . count($colorIds) . ' warna aktif)');
    }

    /**
     * Create a new global color and assign it to the material.
     */
    public function storeColor(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'color_name' => ['required', 'string', 'max:120'],
            'hex_code' => ['required', 'string', 'max:10', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
        ]);

        $name = trim((string) $validated['color_name']);
        $hexCode = trim((string) $validated['hex_code']);

        // Check if color with this name already exists
        $existing = Color::where('name', $name)->first();

        if ($existing) {
            // Just assign it to the material if not already
            if (! $material->colors()->where('color_id', $existing->id)->exists()) {
                $nextSort = (int) $material->colors()->max('material_colors.sort_order') + 1;
                $material->colors()->attach($existing->id, ['sort_order' => $nextSort]);
            }

            return redirect()
                ->route('admin.materials.edit', $material)
                ->with('success', "Warna '{$name}' sudah ada dan berhasil ditambahkan ke bahan ini.");
        }

        $nextGlobalSort = (int) Color::max('sort_order') + 1;

        $color = Color::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'hex_code' => $hexCode,
            'sort_order' => $nextGlobalSort,
            'is_active' => true,
        ]);

        $nextSort = (int) $material->colors()->max('material_colors.sort_order') + 1;
        $material->colors()->attach($color->id, ['sort_order' => $nextSort]);

        return redirect()
            ->route('admin.materials.edit', $material)
            ->with('success', "Warna baru '{$name}' berhasil dibuat dan ditambahkan ke bahan ini.");
    }

    /**
     * Delete a global color (only if not used).
     */
    public function destroyColor(Color $color): RedirectResponse
    {
        $materialCount = $color->materials()->count();

        if ($materialCount > 0) {
            return back()->withErrors(['color' => "Warna '{$color->name}' masih digunakan di {$materialCount} bahan. Hapus dari semua bahan terlebih dahulu."]);
        }

        $color->delete();

        return back()->with('success', "Warna '{$color->name}' berhasil dihapus.");
    }
}
