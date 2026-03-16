<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->role === User::ROLE_CUSTOMER) {
            $orders = Order::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard.customer', compact('orders'));
        }

        if ($user->hasRole(User::ROLE_FINANCE)) {
            $pendingPayments = Payment::where('status', 'pending')->count();
            $verifiedToday = Payment::where('status', 'verified')->whereDate('updated_at', now()->toDateString())->count();

            return view('dashboard.finance', compact('pendingPayments', 'verifiedToday'));
        }

        if ($user->hasRole(User::ROLE_PRODUCTION)) {
            $activeOrders = Order::whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement'])->count();
            $waitingSettlement = Order::where('order_status', 'finishing_waiting_settlement')->count();

            return view('dashboard.production', compact('activeOrders', 'waitingSettlement'));
        }

        $summary = [
            'total_orders' => Order::count(),
            'pending_verification' => Payment::where('status', 'pending')->count(),
            'in_production' => Order::whereIn('order_status', ['in_production', 'finishing_waiting_settlement'])->count(),
            'completed' => Order::where('order_status', 'completed')->count(),
        ];

        return view('dashboard.management', compact('summary'));
    }
}
