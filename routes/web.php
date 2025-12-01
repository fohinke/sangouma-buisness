<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientCreditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BankDepositController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ressources principales avec permissions
    Route::resource('suppliers', SupplierController::class)->middleware('permission:manage suppliers');
    Route::resource('products', ProductController::class)->middleware('permission:manage products');
    Route::resource('clients', ClientController::class)->middleware('permission:manage sales');
    Route::resource('bank-deposits', BankDepositController::class)->only(['index','create','store','show'])->middleware('permission:process payments');
    Route::resource('purchase-orders', PurchaseOrderController::class)->middleware('permission:manage purchases');
    Route::resource('sales', SaleController::class)->middleware('permission:manage sales');
    Route::get('users', [UserController::class, 'index'])->middleware('permission:manage users')->name('users.index');

    // Credits exceptionnels
    Route::get('client-credits', [ClientCreditController::class, 'index'])->middleware('permission:manage sales')->name('client-credits.index');
    Route::get('client-credits/create', [ClientCreditController::class, 'create'])->middleware('permission:manage sales')->name('client-credits.create');
    Route::post('client-credits', [ClientCreditController::class, 'store'])->middleware('permission:manage sales')->name('client-credits.store');
    Route::get('client-credits/{client_credit}', [ClientCreditController::class, 'show'])
        ->middleware('permission:manage sales')
        ->whereNumber('client_credit')
        ->name('client-credits.show');
    Route::post('client-credits/{client_credit}/refunds', [ClientCreditController::class, 'addRefund'])
        ->middleware('permission:manage sales')
        ->whereNumber('client_credit')
        ->name('client-credits.refunds.store');

    // Actions specifique Achats
    Route::post('purchase-orders/{purchase_order}/payments', [PurchaseOrderController::class, 'addPayment'])->middleware('permission:process payments')->name('purchase-orders.payments.store');
    
    Route::delete('purchase-orders/{purchase_order}/payments/{payment}', [PurchaseOrderController::class, 'removePayment'])->middleware('permission:process payments')->name('purchase-orders.payments.destroy');
    Route::post('purchase-orders/{purchase_order}/receptions', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receptions.store');
    Route::get('purchase-orders/{purchase_order}/invoice', [PurchaseOrderController::class, 'invoice'])->name('purchase-orders.invoice');

    // Actions specifique Ventes
    Route::post('sales/{sale}/payments', [SaleController::class, 'addPayment'])->middleware('permission:process payments')->name('sales.payments.store');
    
    Route::delete('sales/{sale}/payments/{payment}', [SaleController::class, 'removePayment'])->middleware('permission:process payments')->name('sales.payments.destroy');
    Route::post('sales/{sale}/deliver', [SaleController::class, 'deliver'])->name('sales.deliver');
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('sales/{sale}/delivery-note', [SaleController::class, 'deliveryNote'])->name('sales.delivery-note');

    // Exports (CSV/Excel) - protege par view reports
    Route::get('sales-export', [SaleController::class, 'export'])->middleware('permission:view reports')->name('sales.export');
    Route::get('purchase-orders-export', [PurchaseOrderController::class, 'export'])->middleware('permission:view reports')->name('purchase-orders.export');
    Route::get('clients-export', [ClientController::class, 'export'])->middleware('permission:view reports')->name('clients.export');
});

require __DIR__.'/auth.php';
