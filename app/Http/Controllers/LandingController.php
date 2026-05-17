<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $products = $this->catalogProductsFromDatabase();

        if ($products === []) {
            $products = $this->catalogProducts();
        }

        return view('landing', [
            'products' => array_values($products),
        ]);
    }

    public function showCatalog(string $slug): View
    {
        $products = $this->catalogProductsFromDatabase();

        if ($products === []) {
            $products = $this->catalogProducts();
        }

        abort_if(! isset($products[$slug]), 404);

        return view('catalog.show', [
            'product' => $products[$slug],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function catalogProductsFromDatabase(): array
    {
        try {
            $rows = DB::table('catalog_products as cp')
                ->leftJoin('materials as m', 'm.id', '=', 'cp.material_id')
                ->leftJoin('production_types as pt', 'pt.id', '=', 'cp.production_type_id')
                ->leftJoin('product_models as pm', 'pm.id', '=', 'cp.product_model_id')
                ->leftJoin('colors as c', 'c.id', '=', 'cp.color_id')
                ->where('cp.is_active', true)
                ->orderBy('cp.sort_order')
                ->orderBy('cp.id')
                ->get([
                    'cp.slug',
                    'cp.name',
                    'cp.category',
                    'cp.short_description',
                    'cp.image_path',
                    'cp.unit_price',
                    'cp.minimum_order_qty',
                    'cp.design_notes',
                    'm.name as material_name',
                    'pt.name as production_type_name',
                    'pm.name as product_model_name',
                    'c.name as color_name',
                ]);

            if ($rows->isEmpty()) {
                return [];
            }

            $products = [];

            foreach ($rows as $row) {
                $priceValue = (int) ($row->unit_price ?? 0);
                $minimumOrder = (int) ($row->minimum_order_qty ?? 60);
                $material = (string) ($row->material_name ?? '-');
                $productionType = (string) ($row->production_type_name ?? '-');
                $productModel = (string) ($row->product_model_name ?? ($row->category ?? 'T-Shirt'));
                $color = (string) ($row->color_name ?? '-');

                $products[(string) $row->slug] = [
                    'slug' => (string) $row->slug,
                    'name' => (string) $row->name,
                    'category' => (string) ($row->category ?? $productModel),
                    'desc' => (string) ($row->short_description ?? ''),
                    'image' => (string) ($row->image_path ?? ''),
                    'min_order' => 'Minimal order ' . $minimumOrder . ' pcs',
                    'long_desc' => array_values(array_filter([
                        (string) ($row->short_description ?? ''),
                    ])),
                    'specs' => [
                        'Bahan' => $material,
                        'Jenis' => $productModel,
                        'Desain logo' => $productionType,
                        'Minimal order' => $minimumOrder . ' pcs',
                        'Warna' => $color,
                    ],
                    'preset' => [
                        'catalog' => (string) $row->slug,
                        'fabric' => $material,
                        'production_type' => $productionType,
                        'product_model' => $productModel,
                        'dominant_color' => $color,
                        'minimum_order_qty' => $minimumOrder,
                        'design_notes' => (string) ($row->design_notes ?? ''),
                    ],
                ];
            }

            return $products;
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function catalogProducts(): array
    {
        return [
            'pjmgroup' => [
                'slug' => 'pjmgroup',
                'name' => 'PJM Group',
                'category' => 'T-Shirt',
                'desc' => 'Template siap pesan. Spesifikasi dasar sudah terisi, pelanggan tinggal upload desain dan isi jumlah ukuran.',
                'image' => 'images/katalog/pjmgroup.png',
                'long_desc' => [
                    'Katalog ini dirancang untuk kebutuhan seragam atau event dengan material nyaman dan look profesional.',
                    'Spesifikasi utama sudah ditetapkan sehingga proses pemesanan lebih cepat, pelanggan tinggal fokus mengunggah desain depan-belakang serta menentukan distribusi ukuran.',
                    'Cocok untuk komunitas, kantor, instansi, dan kebutuhan produksi rutin dengan kualitas sablon rapi.',
                ],
                'specs' => [
                    'Bahan' => 'Cotton Combed 30s',
                    'Jenis' => 'T-Shirt',
                    'Desain logo' => 'Sablon',
                    'Warna' => 'Baby Blue',
                ],
                'preset' => [
                    'catalog' => 'pjmgroup',
                    'fabric' => 'Cotton Combed 30s',
                    'production_type' => 'Sablon',
                    'product_model' => 'T-Shirt',
                    'dominant_color' => 'Baby Blue',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Bagian depan kiri ukuran 5cm x 5cm',
                ],
            ],
            'dryfit-sport' => [
                'slug' => 'dryfit-sport',
                'name' => 'KLINIK IBUNDA',
                'category' => 'Kaos',
                'desc' => 'Kaos custom dengan bahan Lacoste dan logo bordir untuk kebutuhan seragam instansi.',
                'image' => 'images/katalog/kaoshijau.png',
                'long_desc' => [
                    'Katalog KLINIK IBUNDA menggunakan bahan Lacoste dengan jenis kaos yang nyaman dipakai harian.',
                    'Desain logo dikerjakan dengan bordir agar terlihat rapi, kuat, dan profesional untuk kebutuhan seragam.',
                ],
                'specs' => [
                    'Bahan' => 'Lacoste',
                    'Jenis' => 'Kaos',
                    'Desain logo' => 'Bordir',
                    'Warna' => 'Hijau Pucuk',
                ],
                'preset' => [
                    'catalog' => 'klinik-ibunda',
                    'fabric' => 'Lacoste',
                    'production_type' => 'Bordir',
                    'product_model' => 'Kaos',
                    'dominant_color' => 'Hijau Pucuk',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo bordir sesuai template KLINIK IBUNDA.',
                ],
            ],
            'heavyweight-premium' => [
                'slug' => 'heavyweight-premium',
                'name' => 'ADARO FIRE RESCUE',
                'category' => 'Kaos',
                'desc' => 'Kaos custom bahan Cotton Combed 24s dengan logo sablon untuk kebutuhan tim lapangan.',
                'image' => 'images/katalog/adarokaos.jpg',
                'long_desc' => [
                    'Katalog ADARO FIRE RESCUE memakai bahan Cotton Combed 24s dengan karakter halus dan nyaman dipakai.',
                    'Logo menggunakan sablon agar hasil visual tegas, cocok untuk kebutuhan seragam tim operasional.',
                ],
                'specs' => [
                    'Bahan' => 'Cotton Combed 24s',
                    'Jenis' => 'Kaos',
                    'Desain logo' => 'Sablon',
                    'Warna' => 'Hitam',
                ],
                'preset' => [
                    'catalog' => 'adaro-fire-rescue',
                    'fabric' => 'Cotton Combed 24s',
                    'production_type' => 'Sablon',
                    'product_model' => 'Kaos',
                    'dominant_color' => 'Hitam',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo sablon sesuai template ADARO FIRE RESCUE.',
                ],
            ],
            'jlifad-poloshirt' => [
                'slug' => 'jlifad-poloshirt',
                'name' => 'JLIFAD PoloShirt',
                'category' => 'Poloshirt',
                'desc' => 'Poloshirt bahan Lacoste dengan logo bordir untuk kebutuhan identitas tim atau instansi.',
                'image' => 'images/katalog/jlifad.png',
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Katalog JLIFAD PoloShirt dirancang dengan bahan Lacoste yang nyaman dan tampilan rapi.',
                    'Logo dikerjakan dengan bordir untuk hasil detail yang presisi dan tahan lama.',
                ],
                'specs' => [
                    'Bahan' => 'Lacoste',
                    'Jenis' => 'Poloshirt',
                    'Desain logo' => 'Bordir',
                    'Minimal order' => '60 pcs',
                    'Warna' => 'Coklat',
                ],
                'preset' => [
                    'catalog' => 'jlifad-poloshirt',
                    'fabric' => 'Lacoste',
                    'production_type' => 'Bordir',
                    'product_model' => 'Poloshirt',
                    'dominant_color' => 'Coklat',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo bordir sesuai template JLIFAD PoloShirt.',
                ],
            ],
            'william-kartika' => [
                'slug' => 'william-kartika',
                'name' => 'William Kartika',
                'category' => 'Kaos',
                'desc' => 'Kaos custom dengan material Cotton Combed 24a dan logo sablon untuk kebutuhan seragam.',
                'image' => 'images/katalog/sinarmas.png',
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Katalog William Kartika menggunakan bahan Cotton Combed 24a yang nyaman untuk pemakaian harian.',
                    'Desain logo sablon memberikan kesan kasual dan rapi untuk kebutuhan personal atau tim.',
                ],
                'specs' => [
                    'Bahan' => 'Cotton Combed 24a',
                    'Jenis' => 'Kaos',
                    'Desain logo' => 'Sablon',
                    'Minimal order' => '60 pcs',
                    'Warna' => 'Cream',
                ],
                'preset' => [
                    'catalog' => 'william-kartika',
                    'fabric' => 'Cotton Combed 24a',
                    'production_type' => 'Sablon',
                    'product_model' => 'Kaos',
                    'dominant_color' => 'Cream',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo sablon sesuai template William Kartika.',
                ],
            ],
            'universitas-nurtanio' => [
                'slug' => 'universitas-nurtanio',
                'name' => 'UNIVERSITAS NURTANIO',
                'category' => 'Poloshirt',
                'desc' => 'Poloshirt bahan Lacoste dengan bordir logo untuk kebutuhan seragam kampus.',
                'image' => 'images/katalog/univ.png',
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Katalog UNIVERSITAS NURTANIO menggunakan bahan Lacoste dengan tampilan formal dan nyaman.',
                    'Logo bordir dibuat presisi agar identitas institusi tampil jelas dan elegan.',
                ],
                'specs' => [
                    'Bahan' => 'Lacoste',
                    'Jenis' => 'Poloshirt',
                    'Desain logo' => 'Bordir',
                    'Minimal order' => '60 pcs',
                    'Warna' => 'Hitam',
                ],
                'preset' => [
                    'catalog' => 'universitas-nurtanio',
                    'fabric' => 'Lacoste',
                    'production_type' => 'Bordir',
                    'product_model' => 'Poloshirt',
                    'dominant_color' => 'Hitam',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo bordir sesuai template UNIVERSITAS NURTANIO.',
                ],
            ],
            'sps-siloam' => [
                'slug' => 'sps-siloam',
                'name' => 'SPS SILOAM',
                'category' => 'Kaos',
                'desc' => 'Kaos custom bahan Cotton Combed 30s dengan logo sablon untuk kebutuhan komunitas dan institusi.',
                'image' => 'images/katalog/santri.png',
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Katalog SPS SILOAM menggunakan bahan Cotton Combed 30s yang nyaman dipakai harian.',
                    'Logo dikerjakan dengan teknik sablon untuk tampilan tegas dan rapi pada seragam tim.',
                ],
                'specs' => [
                    'Bahan' => 'Cotton Combed 30s',
                    'Jenis' => 'Kaos',
                    'Desain logo' => 'Sablon',
                    'Minimal order' => '60 pcs',
                    'Warna' => 'Oranye',
                ],
                'preset' => [
                    'catalog' => 'sps-siloam',
                    'fabric' => 'Cotton Combed 30s',
                    'production_type' => 'Sablon',
                    'product_model' => 'Kaos',
                    'dominant_color' => 'Oranye',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo sablon sesuai template SPS SILOAM.',
                ],
            ],
            'pt-wiguna-artha-lestari' => [
                'slug' => 'pt-wiguna-artha-lestari',
                'name' => 'PT WIGUNA ARTHA LESTARI',
                'category' => 'Polo shirt',
                'desc' => 'Polo shirt bahan Laccoste dengan logo bordir untuk kebutuhan seragam perusahaan.',
                'image' => 'images/katalog/wiguna.png',
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Katalog PT WIGUNA ARTHA LESTARI menggunakan bahan Laccoste dengan tampilan formal dan nyaman.',
                    'Logo bordir memberikan hasil yang rapi dan profesional untuk identitas perusahaan.',
                ],
                'specs' => [
                    'Bahan' => 'Laccoste',
                    'Jenis' => 'Polo shirt',
                    'Desain logo' => 'Bordir',
                    'Minimal order' => '60 pcs',
                    'Warna' => 'Abu',
                ],
                'preset' => [
                    'catalog' => 'pt-wiguna-artha-lestari',
                    'fabric' => 'Laccoste',
                    'production_type' => 'Bordir',
                    'product_model' => 'Polo shirt',
                    'dominant_color' => 'Abu',
                    'minimum_order_qty' => 60,
                    'design_notes' => 'Logo bordir sesuai template PT WIGUNA ARTHA LESTARI.',
                ],
            ],
        ##disini kalau mau tamabah lagi
        ];
    }
}
