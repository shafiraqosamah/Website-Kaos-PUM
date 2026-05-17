<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $materials = [
            ['name' => 'Drill', 'base_price' => 115000],
            ['name' => 'Taipan', 'base_price' => 120000],
            ['name' => 'Tropical', 'base_price' => 110000],
            ['name' => 'Ribstop', 'base_price' => 130000],
            ['name' => 'Lacoste Pique', 'base_price' => 140000],
            ['name' => 'Cotton Combed 30s', 'base_price' => 85000],
            ['name' => 'Cotton Combed 20s', 'base_price' => 95000],
            ['name' => 'Cotton Combed 24s', 'base_price' => 95000],
            ['name' => 'Cotton Combed 24a', 'base_price' => 95000],
            ['name' => 'Drifit', 'base_price' => 105000],
            ['name' => 'Lainnya', 'base_price' => 100000],
        ];

        DB::table('materials')->upsert(
            collect($materials)->values()->map(fn (array $material, int $index): array => [
                'name' => $material['name'],
                'slug' => Str::slug((string) $material['name']),
                'base_price' => (int) $material['base_price'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'base_price', 'sort_order', 'is_active', 'updated_at']
        );

        $productionTypes = [
            ['name' => 'Sablon Manual', 'surcharge_price' => 5000],
            ['name' => 'DTF (Direct to Film)', 'surcharge_price' => 6000],
            ['name' => 'Bordiran', 'surcharge_price' => 6000],
            ['name' => 'Printing', 'surcharge_price' => 7000],
        ];

        DB::table('production_types')->upsert(
            collect($productionTypes)->values()->map(fn (array $type, int $index): array => [
                'name' => $type['name'],
                'slug' => Str::slug((string) $type['name']),
                'surcharge_price' => (int) $type['surcharge_price'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'surcharge_price', 'sort_order', 'is_active', 'updated_at']
        );

        $designPositions = ['Dada Kiri + Punggung', 'Dada Kiri Saja', 'Punggung Saja', 'Full Depan', 'Full Belakang', 'Lainnya'];

        DB::table('design_positions')->upsert(
            collect($designPositions)->values()->map(fn (string $name, int $index): array => [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'sort_order', 'is_active', 'updated_at']
        );

        $productModels = ['Polo Shirt', 'Kaos Oblong', 'Kaos Panjang', 'Raglan', 'T-Shirt'];

        DB::table('product_models')->upsert(
            collect($productModels)->values()->map(fn (string $name, int $index): array => [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'sort_order', 'is_active', 'updated_at']
        );

        $sleeveTypes = ['Lengan Pendek', 'Lengan Panjang'];

        DB::table('sleeve_types')->upsert(
            collect($sleeveTypes)->values()->map(fn (string $name, int $index): array => [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'sort_order', 'is_active', 'updated_at']
        );

        $sizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

        DB::table('sizes')->upsert(
            collect($sizes)->values()->map(fn (string $name, int $index): array => [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'sort_order', 'is_active', 'updated_at']
        );

        $colors = [
            ['name' => 'Baby Blue', 'hex_code' => '#89CFF0'],
            ['name' => 'Hijau Pucuk', 'hex_code' => '#70A83B'],
            ['name' => 'Hitam', 'hex_code' => '#1E1E1E'],
            ['name' => 'Coklat', 'hex_code' => '#8B5A2B'],
            ['name' => 'Hijau Fuji', 'hex_code' => '#4E8B57'],
            ['name' => 'Hijau', 'hex_code' => '#2F8F2F'],
            ['name' => 'Abu', 'hex_code' => '#9CA3AF'],
            ['name' => 'Cream', 'hex_code' => '#FFFDD0'],
            ['name' => 'Oranye', 'hex_code' => '#FFA500'],
        ];

        DB::table('colors')->upsert(
            collect($colors)->values()->map(fn (array $item, int $index): array => [
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'hex_code' => $item['hex_code'],
                'gradient_css' => null,
                'swatch_image_path' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['name', 'hex_code', 'sort_order', 'is_active', 'updated_at']
        );

        $materialBySlug = DB::table('materials')->pluck('id', 'slug');
        $productionTypeBySlug = DB::table('production_types')->pluck('id', 'slug');
        $productModelBySlug = DB::table('product_models')->pluck('id', 'slug');
        $defaultSleeveId = DB::table('sleeve_types')->where('slug', 'lengan-pendek')->value('id');
        $colorBySlug = DB::table('colors')->pluck('id', 'slug');

        $catalogProducts = [
            [
                'slug' => 'pjmgroup',
                'name' => 'PJM Group',
                'category' => 'T-Shirt',
                'short_description' => 'Template siap pesan. Spesifikasi dasar sudah terisi, pelanggan tinggal upload desain dan isi jumlah ukuran.',
                'image_path' => 'images/katalog/pjmgroup.png',
                'unit_price' => 85000,
                'minimum_order_qty' => 60,
                'material_slug' => 'cotton-combed-30s',
                'production_type_slug' => 'sablon-manual',
                'product_model_slug' => 't-shirt',
                'color_slug' => 'baby-blue',
                'design_notes' => 'Bagian depan kiri ukuran 5cm x 5cm',
            ],
            [
                'slug' => 'dryfit-sport',
                'name' => 'KLINIK IBUNDA',
                'category' => 'Kaos',
                'short_description' => 'Kaos custom dengan bahan Lacoste dan logo bordir untuk kebutuhan seragam instansi.',
                'image_path' => 'images/katalog/kaoshijau.png',
                'unit_price' => 90000,
                'minimum_order_qty' => 60,
                'material_slug' => 'lacoste-pique',
                'production_type_slug' => 'bordiran',
                'product_model_slug' => 't-shirt',
                'color_slug' => 'hijau-pucuk',
                'design_notes' => 'Logo bordir sesuai template KLINIK IBUNDA.',
            ],
            [
                'slug' => 'heavyweight-premium',
                'name' => 'ADARO FIRE RESCUE',
                'category' => 'Kaos',
                'short_description' => 'Kaos custom bahan Cotton Combed 24s dengan logo sablon untuk kebutuhan tim lapangan.',
                'image_path' => 'images/katalog/adarokaos.jpg',
                'unit_price' => 95000,
                'minimum_order_qty' => 60,
                'material_slug' => 'cotton-combed-24s',
                'production_type_slug' => 'sablon-manual',
                'product_model_slug' => 't-shirt',
                'color_slug' => 'hitam',
                'design_notes' => 'Logo sablon sesuai template ADARO FIRE RESCUE.',
            ],
            [
                'slug' => 'jlifad-poloshirt',
                'name' => 'JLIFAD PoloShirt',
                'category' => 'Poloshirt',
                'short_description' => 'Poloshirt bahan Lacoste dengan logo bordir untuk kebutuhan identitas tim atau instansi.',
                'image_path' => 'images/katalog/jlifad.png',
                'unit_price' => 125000,
                'minimum_order_qty' => 60,
                'material_slug' => 'lacoste-pique',
                'production_type_slug' => 'bordiran',
                'product_model_slug' => 'polo-shirt',
                'color_slug' => 'coklat',
                'design_notes' => 'Logo bordir sesuai template JLIFAD PoloShirt.',
            ],
            [
                'slug' => 'william-kartika',
                'name' => 'William Kartika',
                'category' => 'Kaos',
                'short_description' => 'Kaos custom dengan material Cotton Combed 24a dan logo sablon untuk kebutuhan seragam.',
                'image_path' => 'images/katalog/sinarmas.png',
                'unit_price' => 95000,
                'minimum_order_qty' => 60,
                'material_slug' => 'cotton-combed-24a',
                'production_type_slug' => 'sablon-manual',
                'product_model_slug' => 't-shirt',
                'color_slug' => 'cream',
                'design_notes' => 'Logo sablon sesuai template William Kartika.',
            ],
            [
                'slug' => 'universitas-nurtanio',
                'name' => 'UNIVERSITAS NURTANIO',
                'category' => 'Poloshirt',
                'short_description' => 'Poloshirt bahan Lacoste dengan bordir logo untuk kebutuhan seragam kampus.',
                'image_path' => 'images/katalog/univ.png',
                'unit_price' => 105000,
                'minimum_order_qty' => 60,
                'material_slug' => 'lacoste-pique',
                'production_type_slug' => 'bordiran',
                'product_model_slug' => 'polo-shirt',
                'color_slug' => 'hitam',
                'design_notes' => 'Logo bordir sesuai template UNIVERSITAS NURTANIO.',
            ],
            [
                'slug' => 'sps-siloam',
                'name' => 'SPS SILOAM',
                'category' => 'Kaos',
                'short_description' => 'Kaos custom bahan Cotton Combed 30s dengan logo sablon untuk kebutuhan komunitas dan institusi.',
                'image_path' => 'images/katalog/santri.png',
                'unit_price' => 100000,
                'minimum_order_qty' => 60,
                'material_slug' => 'cotton-combed-30s',
                'production_type_slug' => 'sablon-manual',
                'product_model_slug' => 't-shirt',
                'color_slug' => 'oranye',
                'design_notes' => 'Logo sablon sesuai template SPS SILOAM.',
            ],
            [
                'slug' => 'pt-wiguna-artha-lestari',
                'name' => 'PT WIGUNA ARTHA LESTARI',
                'category' => 'Polo shirt',
                'short_description' => 'Polo shirt bahan Laccoste dengan logo bordir untuk kebutuhan seragam perusahaan.',
                'image_path' => 'images/katalog/wiguna.png',
                'unit_price' => 95000,
                'minimum_order_qty' => 60,
                'material_slug' => 'lacoste-pique',
                'production_type_slug' => 'bordiran',
                'product_model_slug' => 'polo-shirt',
                'color_slug' => 'abu',
                'design_notes' => 'Logo bordir sesuai template PT WIGUNA ARTHA LESTARI.',
            ],
        ];

        DB::table('catalog_products')->upsert(
            collect($catalogProducts)->values()->map(fn (array $item, int $index): array => [
                'slug' => $item['slug'],
                'name' => $item['name'],
                'category' => $item['category'],
                'image_path' => $item['image_path'],
                'short_description' => $item['short_description'],
                'unit_price' => $item['unit_price'],
                'minimum_order_qty' => $item['minimum_order_qty'],
                'material_id' => $materialBySlug[$item['material_slug']] ?? null,
                'production_type_id' => $productionTypeBySlug[$item['production_type_slug']] ?? null,
                'product_model_id' => $productModelBySlug[$item['product_model_slug']] ?? null,
                'sleeve_type_id' => $defaultSleeveId,
                'color_id' => $colorBySlug[$item['color_slug']] ?? null,
                'design_notes' => $item['design_notes'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            [
                'name',
                'category',
                'image_path',
                'short_description',
                'unit_price',
                'minimum_order_qty',
                'material_id',
                'production_type_id',
                'product_model_id',
                'sleeve_type_id',
                'color_id',
                'design_notes',
                'sort_order',
                'is_active',
                'updated_at',
            ]
        );
    }
}
