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
                ->whereIn('order_status', ['submitted', 'pending_verification', 'verified_payment', 'verified_dp', 'in_production', 'finishing_waiting_settlement'])
                ->count();
            $pendingVerificationOrdersCount = Order::where('user_id', $user->id)
                ->where('order_status', 'submitted')
                ->where('admin_verification_status', 'pending')
                ->count();

            $pendingPaymentOrdersCount = Order::where('user_id', $user->id)
                ->where('order_status', 'submitted')
                ->where('admin_verification_status', 'verified')
                ->whereHas('payments', static function ($query): void {
                    $query->where('status', 'pending');
                })
                ->count();

            $dueWaitingPaymentOrder = Order::with(['payments' => static function ($query): void {
                    $query->where('status', 'pending')->latest('id');
                }])
                ->where('user_id', $user->id)
                ->where('order_status', 'submitted')
                ->where('admin_verification_status', 'verified')
                ->whereHas('payments', static function ($query): void {
                    $query->where('status', 'pending');
                })
                ->latest('updated_at')
                ->first();

            $waitingPaymentAlertCount = Order::where('user_id', $user->id)
                ->where('order_status', 'submitted')
                ->where('admin_verification_status', 'verified')
                ->whereHas('payments', static function ($query): void {
                    $query->where('status', 'pending');
                })
                ->count();

            $dueRevisionOrder = Order::where('user_id', $user->id)
                ->where('order_status', 'submitted')
                ->where('admin_verification_status', 'revision_requested')
                ->latest('updated_at')
                ->first();

            $dueSettlementOrder = Order::with(['payments' => static function ($query): void {
                    $query->where('method', 'settlement')->where('status', 'pending')->latest('id');
                }])
                ->where('user_id', $user->id)
                ->where('order_status', 'finishing_waiting_settlement')
                ->where('remaining_amount', '>', 0)
                ->latest('updated_at')
                ->first();

            $settlementAlertCount = Order::where('user_id', $user->id)
                ->where('order_status', 'finishing_waiting_settlement')
                ->where('remaining_amount', '>', 0)
                ->count();

            $completedOrders = Order::where('user_id', $user->id)
                ->whereIn('order_status', ['ready_for_pickup', 'completed'])
                ->count();

            $readyPickupOrder = Order::where('user_id', $user->id)
                ->where('order_status', 'ready_for_pickup')
                ->latest('updated_at')
                ->first();

            $readyPickupAlertCount = Order::where('user_id', $user->id)
                ->where('order_status', 'ready_for_pickup')
                ->count();

            $recentOrders = Order::with('payments')
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard.customer', compact(
                'totalOrders',
                'inProgressOrders',
                'pendingVerificationOrdersCount',
                'pendingPaymentOrdersCount',
                'dueWaitingPaymentOrder',
                'waitingPaymentAlertCount',
                'dueRevisionOrder',
                'dueSettlementOrder',
                'settlementAlertCount',
                'completedOrders',
                'readyPickupOrder',
                'readyPickupAlertCount',
                'recentOrders'
            ));
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
            $activeOrders = Order::whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement', 'production_done_waiting_admin', 'ready_for_pickup'])->count();
            $waitingSettlement = Order::where('order_status', 'finishing_waiting_settlement')->count();
            
            // Count orders in finishing stage waiting for settlement
            $finishingWaitingSettlement = Order::where('order_status', 'finishing_waiting_settlement')
                ->where('remaining_amount', '>', 0)
                ->count();
            
            // Fetch active orders with their production steps for SPK dashboard
            $activeOrdersWithSteps = Order::with(['workOrder', 'productionSteps'])
                ->whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement', 'production_done_waiting_admin', 'ready_for_pickup'])
                ->latest()
                ->take(10)
                ->get();

            $newProductionOrders = Order::where('order_status', 'verified_payment')->get();
            
            $readyForFinishingOrders = Order::where('order_status', 'in_production')
                ->where('payment_type', 'dp')
                ->where('remaining_amount', 0)
                ->whereHas('productionSteps', function ($query) {
                    $query->where('step_name', 'Finishing')
                          ->whereIn('status', ['pending', 'in_progress']);
                })
                ->get();

            return view('dashboard.production', compact('activeOrders', 'waitingSettlement', 'activeOrdersWithSteps', 'finishingWaitingSettlement', 'newProductionOrders', 'readyForFinishingOrders'));
        }

        $pendingOrderVerification = Order::where('admin_verification_status', 'pending')->count();
        $productionWaitingVerification = Order::where('order_status', 'production_done_waiting_admin')->count();

        $summary = [
            'total_orders' => Order::count(),
            'pending_verification' => $pendingOrderVerification,
            'in_production' => Order::whereIn('order_status', ['in_production', 'finishing_waiting_settlement'])->count(),
            'completed' => Order::where('order_status', 'completed')->count(),
        ];

        $pendingVerificationOrders = Order::with('user')
            ->where('admin_verification_status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        $pendingPayments = Payment::with(['order', 'order.user'])
            ->where('status', 'pending')
            ->whereNotNull('proof_path')
            ->latest()
            ->take(10)
            ->get();

        $activeProductionOrders = Order::with(['user', 'productionSteps', 'workOrder'])
            ->whereIn('order_status', ['verified_payment', 'in_production', 'finishing_waiting_settlement', 'production_done_waiting_admin', 'ready_for_pickup'])
            ->latest()
            ->take(10)
            ->get();

        $completedOrders = Order::with(['user', 'payments'])
            ->where('order_status', 'completed')
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard.management', compact(
            'summary', 
            'pendingOrderVerification', 
            'productionWaitingVerification',
            'pendingVerificationOrders',
            'pendingPayments',
            'activeProductionOrders',
            'completedOrders'
        ));
    }
}
