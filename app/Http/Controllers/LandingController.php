<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing', [
            'products' => [
                [
                    'name' => 'Kaos Combed 24s',
                    'desc' => 'Nyaman untuk event, komunitas, dan corporate merchandise.',
                    'price' => 'Mulai Rp85.000 / pcs',
                ],
                [
                    'name' => 'Kaos Dryfit Sport',
                    'desc' => 'Ringan, cepat kering, cocok untuk jersey atau fun run.',
                    'price' => 'Mulai Rp95.000 / pcs',
                ],
                [
                    'name' => 'Kaos Heavyweight Premium',
                    'desc' => 'Kesan eksklusif untuk brand fashion dan rilisan terbatas.',
                    'price' => 'Mulai Rp120.000 / pcs',
                ],
            ],
        ]);
    }
}
