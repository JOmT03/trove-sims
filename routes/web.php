<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierProductController;
use Illuminate\Support\Facades\Route;

// ── Public Landing Page ─────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('welcome');

// ── Authenticated Routes ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::resource('orders', OrderController::class);
    Route::resource('deliveries', DeliveryController::class);
    Route::resource('inventory', InventoryController::class)->only(['index', 'show']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── SUPPLIER PORTAL (role = supplier) ───────────────────────────────────
    Route::middleware('supplier')->prefix('supplier')->name('supplier.')->group(function () {
        // My Products
        Route::get('/products',              [SupplierProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',       [SupplierProductController::class, 'create'])->name('products.create');
        Route::post('/products',             [SupplierProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit',   [SupplierProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}',        [SupplierProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}',     [SupplierProductController::class, 'destroy'])->name('products.destroy');

        // Incoming Orders (supplier sees orders placed for them)
        Route::get('/orders',                            [SupplierProductController::class, 'orders'])->name('orders');
        Route::patch('/orders/{order}/status',           [SupplierProductController::class, 'updateOrderStatus'])->name('orders.status');
    });

    // ── AJAX: supplier products (for order create form) ─────────────────────
    Route::get('/api/suppliers/{supplier}/products', [OrderController::class, 'getSupplierProducts'])
         ->name('suppliers.products');

    // ── AJAX: order items (for delivery create form) ─────────────────────────
    Route::get('/api/orders/{order}/items', [OrderController::class, 'getItems'])
         ->name('orders.items');

    // ── SUPPLIERS (admin manages supplier accounts) ─────────────────────────
    Route::get('/suppliers',                   [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}',        [SupplierController::class, 'show'])->name('suppliers.show');

    Route::middleware('admin')->group(function () {
        Route::get('/suppliers/create',            [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers',                  [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit',   [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}',        [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}',     [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    // ── ORDERS ──────────────────────────────────────────────────────────────
    Route::get('/orders',         [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::middleware('admin')->group(function () {
        Route::get('/orders/create',       [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders',             [OrderController::class, 'store'])->name('orders.store');
        // NOTE: updateStatus is now supplier-only (moved to supplier portal)
        Route::delete('/orders/{order}',   [OrderController::class, 'destroy'])->name('orders.destroy');
    });

    // ── DELIVERIES ──────────────────────────────────────────────────────────
    Route::get('/deliveries',              [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/{delivery}',   [DeliveryController::class, 'show'])->name('deliveries.show');

    Route::middleware('admin')->group(function () {
        Route::get('/deliveries/create',   [DeliveryController::class, 'create'])->name('deliveries.create');
        Route::post('/deliveries',         [DeliveryController::class, 'store'])->name('deliveries.store');
    });

    // ── INVENTORY ───────────────────────────────────────────────────────────
    Route::get('/inventory',              [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventory}',  [InventoryController::class, 'show'])->name('inventory.show');

    Route::middleware('admin')->group(function () {
        Route::get('/inventory/create',   [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory',         [InventoryController::class, 'store'])->name('inventory.store');
        Route::patch('/inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    });
});

require __DIR__ . '/auth.php';