<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing', [
            'products' => array_values($this->catalogProducts()),
        ]);
    }

    public function showCatalog(string $slug): View
    {
        $products = $this->catalogProducts();
        abort_if(! isset($products[$slug]), 404);

        return view('catalog.show', [
            'product' => $products[$slug],
        ]);
    }

    private function catalogProducts(): array
    {
        return [
            'pjmgroup' => [
                'slug' => 'pjmgroup',
                'name' => 'Katalog PJM Group',
                'category' => 'T-Shirt',
                'desc' => 'Template siap pesan. Spesifikasi dasar sudah terisi, pelanggan tinggal upload desain dan isi jumlah ukuran.',
                'price' => 'Rp85.000 / pcs',
                'image' => 'images/katalog/pjmgroup.png',
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Katalog ini dirancang untuk kebutuhan seragam atau event dengan material nyaman dan look profesional.',
                    'Spesifikasi utama sudah ditetapkan sehingga proses pemesanan lebih cepat, pelanggan tinggal fokus mengunggah desain depan-belakang serta menentukan distribusi ukuran.',
                    'Cocok untuk komunitas, kantor, instansi, dan kebutuhan produksi rutin dengan kualitas sablon rapi.',
                ],
                'specs' => [
                    'Bahan' => 'Cotton Combed 30s',
                    'Jenis' => 'T-Shirt',
                    'Desain logo' => 'Sablon',
                    'Minimal order' => '60 pcs',
                    'Warna' => 'Baby Blue',
                    'Harga' => 'Rp85.000',
                ],
                'preset' => [
                    'catalog' => 'pjmgroup',
                    'fabric' => 'Cotton Combed 30s',
                    'production_type' => 'Sablon',
                    'product_model' => 'T-Shirt',
                    'dominant_color' => 'Baby Blue',
                    'unit_price' => 85000,
                    'total_pcs' => 60,
                    'production_qty' => 60,
                    'design_notes' => 'Bagian depan kiri ukuran 5cm x 5cm',
                ],
            ],
            'dryfit-sport' => [
                'slug' => 'dryfit-sport',
                'name' => 'Kaos Dryfit Sport',
                'category' => 'Sport Jersey',
                'desc' => 'Ringan, cepat kering, cocok untuk jersey atau fun run.',
                'price' => 'Mulai Rp95.000 / pcs',
                'image' => null,
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Pilihan tepat untuk aktivitas intensif dengan material ringan dan breathable.',
                ],
                'specs' => [],
                'preset' => [],
            ],
            'heavyweight-premium' => [
                'slug' => 'heavyweight-premium',
                'name' => 'Kaos Heavyweight Premium',
                'category' => 'Premium Tee',
                'desc' => 'Kesan eksklusif untuk brand fashion dan rilisan terbatas.',
                'price' => 'Mulai Rp120.000 / pcs',
                'image' => null,
                'min_order' => 'Minimal order 60 pcs',
                'long_desc' => [
                    'Material tebal dan struktur jatuh yang premium untuk produk brand.',
                ],
                'specs' => [],
                'preset' => [],
            ],
        ];
    }
}
