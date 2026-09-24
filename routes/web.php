<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('welcome');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── ORDERS ── (create/store BEFORE the {order} wildcard)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::middleware('admin')->group(function () {
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    });
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // ── DELIVERIES ──
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::middleware('admin')->group(function () {
        Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
        Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    });
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');


    // ── INVENTORY ──
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::middleware('admin')->group(function () {
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::patch('/inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });
    Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');

    // ── PRODUCTS ──
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::middleware('admin')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    

});

require __DIR__ . '/auth.php';