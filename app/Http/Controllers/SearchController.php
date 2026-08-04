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

        $referer = $request->headers->get('referer');
        $path = $referer ? parse_url($referer, PHP_URL_PATH) : '';

        if ($path) {
            if (str_contains($path, '/finance/payments')) {
                return redirect()->route('finance.index', ['search' => $query]);
            }
            if (str_contains($path, '/production/orders')) {
                return redirect()->route('production.index', ['search' => $query]);
            }
            if (str_contains($path, '/admin/users')) {
                return redirect()->route('admin.users.index', ['q' => $query]);
            }
            if (str_contains($path, '/admin/materials')) {
                return redirect()->route('admin.materials.index', ['q' => $query]);
            }
            if (str_contains($path, '/reports/orders-balance')) {
                return redirect()->route('reports.orders', ['search' => $query]);
            }
            if (str_contains($path, '/reports/orders-report')) {
                return redirect()->route('reports.orders-report', ['search' => $query]);
            }
            if (str_contains($path, '/customer/orders')) {
                return redirect()->route('customer.orders.index', ['search' => $query]);
            }
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
