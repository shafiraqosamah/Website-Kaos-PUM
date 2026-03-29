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
            $totalOrders = Order::where('user_id', $user->id)->count();
            $inProgressOrders = Order::where('user_id', $user->id)
                ->whereIn('order_status', ['submitted', 'pending_verification', 'verified_payment', 'verified_dp', 'in_production'])
                ->count();
            $waitingPaymentOrders = Order::where('user_id', $user->id)
                ->where('remaining_amount', '>', 0)
                ->where('order_status', 'finishing_waiting_settlement')
                ->count();
            $duePaymentOrder = Order::where('user_id', $user->id)
                ->where('remaining_amount', '>', 0)
                ->where('order_status', 'finishing_waiting_settlement')
                ->latest('updated_at')
                ->first();
            $recentOrders = Order::with('payments')
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard.customer', compact('totalOrders', 'inProgressOrders', 'waitingPaymentOrders', 'duePaymentOrder', 'recentOrders'));
        }

        if ($user->hasRole(User::ROLE_FINANCE)) {
            $pendingPayments = Payment::where('status', 'pending')
                ->whereNotNull('proof_path')
                ->whereNotNull('destination_bank')
                ->whereNotNull('sender_bank_name')
                ->whereNotNull('sender_account_name')
                ->count();
            $verifiedToday = Payment::where('status', 'verified')->whereDate('updated_at', now()->toDateString())->count();
            $monthlyVerifiedAmount = Payment::where('status', 'verified')
                ->whereYear('updated_at', now()->year)
                ->whereMonth('updated_at', now()->month)
                ->sum('amount');
            $outstandingSettlement = Order::where('order_status', 'finishing_waiting_settlement')
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');
            $monthlyTotalTagihan = Order::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('subtotal');

            return view('dashboard.finance', compact('pendingPayments', 'verifiedToday', 'monthlyVerifiedAmount', 'outstandingSettlement', 'monthlyTotalTagihan'));
        }

        if ($user->hasRole(User::ROLE_PRODUCTION)) {
            $activeOrders = Order::whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement'])->count();
            $waitingSettlement = Order::where('order_status', 'finishing_waiting_settlement')->count();

            return view('dashboard.production', compact('activeOrders', 'waitingSettlement'));
        }

        $summary = [
            'total_orders' => Order::count(),
            'pending_verification' => Payment::where('status', 'pending')
                ->whereNotNull('proof_path')
                ->whereNotNull('destination_bank')
                ->whereNotNull('sender_bank_name')
                ->whereNotNull('sender_account_name')
                ->count(),
            'in_production' => Order::whereIn('order_status', ['in_production', 'finishing_waiting_settlement'])->count(),
            'completed' => Order::where('order_status', 'completed')->count(),
        ];

        return view('dashboard.management', compact('summary'));
    }
}
