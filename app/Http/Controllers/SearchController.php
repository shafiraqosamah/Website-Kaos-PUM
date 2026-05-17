<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function handle(Request $request)
    {
        $query = $request->input('q');

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $role = strtolower((string) auth()->user()->role);

        // Redirect with query string 'search' instead of 'q'
        // since 'search' is more commonly used in the controllers, if we implement it.
        $params = ['search' => $query];

        if ($role === 'customer') {
            return redirect()->route('customer.orders.index', $params);
        }

        if ($role === 'production') {
            return redirect()->route('production.index', $params);
        }

        if ($role === 'finance') {
            return redirect()->route('finance.index', $params);
        }

        // Admin, Manager, Owner
        return redirect()->route('reports.orders', $params);
    }
}
