<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminMaterialController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PublicTrackingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/katalog/{slug}', [LandingController::class, 'showCatalog'])->name('catalog.show');

Route::get('/track', [PublicTrackingController::class, 'index'])->name('track.index');
Route::post('/track', [PublicTrackingController::class, 'search'])->name('track.search');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Midtrans Payment Routes
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');

Route::middleware('auth')->group(function (): void {
    Route::post('/midtrans/initiate', [MidtransController::class, 'initiatePayment'])->name('midtrans.initiate');
    Route::post('/midtrans/redirect', [MidtransController::class, 'createRedirectPayment'])->name('midtrans.redirect');
    Route::post('/midtrans/check-status', [MidtransController::class, 'checkStatus'])->name('midtrans.check-status');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [SearchController::class, 'handle'])->name('search');

    Route::middleware('role:' . User::ROLE_CUSTOMER)->group(function (): void {
        Route::get('/customer/orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
        Route::get('/customer/orders/create', [CustomerOrderController::class, 'create'])->name('customer.orders.create');
        Route::post('/customer/orders', [CustomerOrderController::class, 'store'])->name('customer.orders.store');
        Route::get('/customer/orders/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
        Route::post('/customer/orders/{order}/revision/approve', [CustomerOrderController::class, 'approveRevision'])->name('customer.orders.revision.approve');
        Route::get('/customer/orders/{order}/payments/{payment}', [CustomerOrderController::class, 'editPayment'])->name('customer.orders.payments.edit');
        Route::get('/customer/orders/{order}/payments/{payment}/invoice', [InvoiceController::class, 'showForCustomer'])->name('customer.invoices.show');
        Route::post('/customer/orders/{order}/settlement', [CustomerOrderController::class, 'requestSettlement'])->name('customer.orders.settlement');
    });

    Route::middleware('role:' . User::ROLE_FINANCE . ',' . User::ROLE_ADMIN)->group(function (): void {
        Route::get('/finance/payments', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/payments/{payment}/invoice', [InvoiceController::class, 'showForFinance'])->name('finance.invoices.show');
    });

    Route::middleware('role:' . User::ROLE_PRODUCTION . ',' . User::ROLE_ADMIN)->group(function (): void {
        Route::get('/production/orders', [ProductionController::class, 'index'])->name('production.index');
        Route::get('/production/orders/{order}', [ProductionController::class, 'show'])->name('production.show');
        Route::get('/production/orders/{order}/spk', [ProductionController::class, 'spk'])->name('production.spk');
        Route::post('/production/orders/{order}/steps/{step}', [ProductionController::class, 'updateStep'])->name('production.step.update');
    });

    Route::middleware('role:' . User::ROLE_ADMIN)->group(function (): void {
        Route::post('/production/orders/{order}/verify-final', [ProductionController::class, 'verifyFinalResult'])->name('production.verify-final');
        Route::post('/production/orders/{order}/pickup-status', [ProductionController::class, 'updatePickupStatus'])->name('production.pickup-status');
        
        Route::get('/admin/settings', [AdminSettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings', [AdminSettingController::class, 'update'])->name('admin.settings.update');
        Route::post('/admin/settings/run-check', [AdminSettingController::class, 'runCheck'])->name('admin.settings.run-check');
    });

    Route::middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_FINANCE . ',' . User::ROLE_PRODUCTION . ',' . User::ROLE_MANAGER . ',' . User::ROLE_OWNER)->group(function (): void {
        Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    });

    Route::middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_FINANCE)->group(function (): void {
        Route::get('/reports/orders-balance', [ReportController::class, 'orders'])->name('reports.orders');
        Route::get('/reports/orders-report', [ReportController::class, 'ordersReport'])->name('reports.orders-report');
        Route::get('/reports/orders-report/export', [ReportController::class, 'exportOrders'])->name('reports.orders-report.export');
        Route::get('/reports/orders-balance/{order}', [ReportController::class, 'showOrder'])->name('reports.orders.show');
        Route::post('/reports/orders-balance/{order}/verify', [ReportController::class, 'verifyOrder'])->name('reports.orders.verify');
        Route::post('/reports/orders-balance/{order}/revision', [ReportController::class, 'requestRevision'])->name('reports.orders.revision');
    });

    Route::middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_FINANCE . ',' . User::ROLE_MANAGER . ',' . User::ROLE_OWNER)->group(function (): void {
        Route::get('/reports/finance-ledger', [ReportController::class, 'finance'])->name('reports.finance');
        Route::get('/reports/finance-ledger/export', [ReportController::class, 'exportFinance'])->name('reports.finance.export');
    });

    Route::middleware('role:' . User::ROLE_ADMIN . ',' . User::ROLE_PRODUCTION . ',' . User::ROLE_MANAGER . ',' . User::ROLE_OWNER)->group(function (): void {
        Route::get('/reports/production-monthly', [ReportController::class, 'production'])->name('reports.production');
        Route::get('/reports/production-monthly/export', [ReportController::class, 'exportProduction'])->name('reports.production.export');
    });

    Route::middleware('role:' . User::ROLE_MANAGER . ',' . User::ROLE_OWNER)->group(function (): void {
        Route::get('/reports/executive', [ReportController::class, 'executive'])->name('reports.executive');
    });

    Route::middleware('role:' . User::ROLE_ADMIN)->group(function (): void {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');

        Route::get('/admin/materials', [AdminMaterialController::class, 'index'])->name('admin.materials.index');
        Route::get('/admin/materials/{material}/edit', [AdminMaterialController::class, 'edit'])->name('admin.materials.edit');
        Route::post('/admin/materials', [AdminMaterialController::class, 'store'])->name('admin.materials.store');
        Route::put('/admin/materials/{material}', [AdminMaterialController::class, 'update'])->name('admin.materials.update');
        Route::patch('/admin/materials/{material}/toggle', [AdminMaterialController::class, 'toggle'])->name('admin.materials.toggle');
        Route::delete('/admin/materials/{material}', [AdminMaterialController::class, 'destroy'])->name('admin.materials.destroy');

        // Color management for materials
        Route::post('/admin/materials/{material}/colors/sync', [AdminMaterialController::class, 'syncColors'])->name('admin.materials.colors.sync');
        Route::post('/admin/materials/{material}/colors', [AdminMaterialController::class, 'storeColor'])->name('admin.materials.colors.store');
        Route::delete('/admin/colors/{color}', [AdminMaterialController::class, 'destroyColor'])->name('admin.colors.destroy');
    });
});
