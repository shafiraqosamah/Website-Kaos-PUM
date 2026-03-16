<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProductionController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:' . User::ROLE_CUSTOMER)->group(function (): void {
        Route::get('/customer/orders', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
        Route::get('/customer/orders/create', [CustomerOrderController::class, 'create'])->name('customer.orders.create');
        Route::post('/customer/orders', [CustomerOrderController::class, 'store'])->name('customer.orders.store');
        Route::get('/customer/orders/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
        Route::get('/customer/orders/{order}/payments/{payment}', [CustomerOrderController::class, 'editPayment'])->name('customer.orders.payments.edit');
        Route::post('/customer/orders/{order}/payments/{payment}', [CustomerOrderController::class, 'updatePayment'])->name('customer.orders.payments.update');
        Route::get('/customer/orders/{order}/payments/{payment}/invoice', [InvoiceController::class, 'showForCustomer'])->name('customer.invoices.show');
        Route::post('/customer/orders/{order}/settlement', [CustomerOrderController::class, 'requestSettlement'])->name('customer.orders.settlement');
    });

    Route::middleware('role:' . User::ROLE_FINANCE . ',' . User::ROLE_ADMIN)->group(function (): void {
        Route::get('/finance/payments', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance/payments/{payment}/verify', [FinanceController::class, 'verify'])->name('finance.verify');
        Route::get('/finance/payments/{payment}/invoice', [InvoiceController::class, 'showForFinance'])->name('finance.invoices.show');
    });

    Route::middleware('role:' . User::ROLE_PRODUCTION . ',' . User::ROLE_ADMIN)->group(function (): void {
        Route::get('/production/orders', [ProductionController::class, 'index'])->name('production.index');
        Route::get('/production/orders/{order}', [ProductionController::class, 'show'])->name('production.show');
        Route::post('/production/orders/{order}/steps/{step}', [ProductionController::class, 'updateStep'])->name('production.step.update');
    });
});
