<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicTrackingController extends Controller
{
    public function index(): View
    {
        return view('track.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'order_code' => ['required', 'string', 'max:150'],
        ]);

        $orderCode = $request->input('order_code');

        $order = Order::with(['productionSteps', 'payments'])
            ->where('order_code', $orderCode)
            ->first();

        if (! $order) {
            return back()->withErrors(['order_code' => 'Nomor pesanan tidak ditemukan.'])->withInput();
        }

        return view('track.show', compact('order'));
    }
}
