<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $notifications = [];
            
            if (auth()->check()) {
                $role = strtolower((string) auth()->user()->role);
                $isCustomer = auth()->user()->hasRole('customer');

                if ($role === 'admin' || $role === 'manager' || $role === 'owner') {
                    $pendingVerification = \App\Models\Order::where('admin_verification_status', 'pending')->count();
                    if ($pendingVerification > 0) {
                        $notifications[] = [
                            'text' => "Ada {$pendingVerification} pesanan yang menunggu verifikasi (Max 2x24 Jam).",
                            'url' => route('dashboard'),
                            'icon' => '📝'
                        ];
                    }

                    $productionDone = \App\Models\Order::where('order_status', 'production_done_waiting_admin')->count();
                    if ($productionDone > 0) {
                        $notifications[] = [
                            'text' => "Ada {$productionDone} pesanan selesai produksi menunggu verifikasi akhir.",
                            'url' => route('dashboard'),
                            'icon' => '✅'
                        ];
                    }
                }

                if ($role === 'finance' || $role === 'admin' || $role === 'owner') {
                    $pendingPayments = \App\Models\Payment::where('status', 'pending')->count();
                    if ($pendingPayments > 0) {
                        $notifications[] = [
                            'text' => "Ada {$pendingPayments} pembayaran menunggu verifikasi.",
                            'url' => route('finance.index'),
                            'icon' => '💰'
                        ];
                    }
                }

                if ($role === 'production' || $role === 'admin' || $role === 'owner') {
                    $newProduction = \App\Models\Order::whereIn('order_status', ['verified_payment', 'in_production'])
                        ->doesntHave('productionSteps')
                        ->count();
                    if ($newProduction > 0) {
                        $notifications[] = [
                            'text' => "Ada {$newProduction} pesanan baru yang butuh SPK / langkah produksi.",
                            'url' => route('production.index'),
                            'icon' => '🏭'
                        ];
                    }
                }

                if ($isCustomer) {
                    $waitingSettlement = \App\Models\Order::where('user_id', auth()->id())
                        ->where('order_status', 'finishing_waiting_settlement')
                        ->count();
                    if ($waitingSettlement > 0) {
                        $notifications[] = [
                            'text' => "Ada {$waitingSettlement} pesanan Anda yang menunggu pelunasan.",
                            'url' => route('customer.orders.index'),
                            'icon' => '💵'
                        ];
                    }

                    $readyPickup = \App\Models\Order::where('user_id', auth()->id())
                        ->whereIn('order_status', ['ready_for_pickup', 'completed'])
                        ->where('updated_at', '>=', now()->subDays(3))
                        ->count();
                    if ($readyPickup > 0) {
                        $notifications[] = [
                            'text' => "Ada pesanan Anda yang siap diambil / selesai.",
                            'url' => route('customer.orders.index'),
                            'icon' => '🎁'
                        ];
                    }
                }
            }

            $view->with('globalNotifications', collect($notifications));
        });
    }
}
