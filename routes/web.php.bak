<?php
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('welcome');

Route::middleware(['auth', \App\Http\Middleware\EnsureActive::class])->group(function () {

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

    Route::middleware('admin')->group(function () {
    });


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
    
    Route::middleware('admin')->group(function () {
    Route::get('/products/create',                  [ProductController::class, 'create'])->name('products.create');
    Route::post('/products',                        [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit',          [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}',               [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}',            [ProductController::class, 'destroy'])->name('products.destroy');
});

});

// Disable public registration - Owner/Manager create accounts in Users module
Route::match(['get', 'post'], 'register', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';

// ===== Branch Transfers (Trove) =====
Route::middleware(['auth'])->group(function () {
    Route::get('/branch-transfers', [\App\Http\Controllers\BranchTransferController::class, 'index'])->name('branch-transfers.index');
    Route::get('/branch-transfers/create', [\App\Http\Controllers\BranchTransferController::class, 'create'])->name('branch-transfers.create');
    Route::post('/branch-transfers', [\App\Http\Controllers\BranchTransferController::class, 'store'])->name('branch-transfers.store');
    Route::get('/branch-transfers/{batch}', [\App\Http\Controllers\BranchTransferController::class, 'show'])->name('branch-transfers.show');
    Route::put('/branch-transfers/{batch}/returns', [\App\Http\Controllers\BranchTransferController::class, 'logReturns'])->name('branch-transfers.returns');
});

// ===== Reports (Trove) =====
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
});

// ===== Commissions (Trove) =====
Route::middleware(['auth'])->group(function () {
    Route::get('/commissions', [\App\Http\Controllers\CommissionController::class, 'index'])->name('commissions.index');
    Route::get('/commissions/create', [\App\Http\Controllers\CommissionController::class, 'create'])->name('commissions.create');
    Route::post('/commissions', [\App\Http\Controllers\CommissionController::class, 'store'])->name('commissions.store');
    Route::get('/commissions/{commission}', [\App\Http\Controllers\CommissionController::class, 'show'])->name('commissions.show');
    Route::put('/commissions/{commission}/status', [\App\Http\Controllers\CommissionController::class, 'updateStatus'])->name('commissions.status');
});

// ---- User Management (Owner & Manager) ----
Route::middleware(['auth', 'admin', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/users',                 [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create',          [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/users',                [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit',     [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',          [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle');
});

// ===== Expenses & Financial Statement (Trove) =====
Route::middleware(['auth'])->group(function () {
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    Route::get('/financial-statement', [\App\Http\Controllers\FinancialStatementController::class, 'index'])->name('financial-statement.index');
});
// ---- Inventory Archive (Owner & Manager) ----
Route::middleware(['auth', 'admin', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::patch('/inventory/{inventory}/archive', [\App\Http\Controllers\InventoryController::class, 'archive'])->name('inventory.archive');
    Route::patch('/inventory/{inventory}/restore', [\App\Http\Controllers\InventoryController::class, 'restore'])->name('inventory.restore');
});

// ===== Settings: Backup & Restore (Owner only) =====
Route::middleware(['auth', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/settings/backup/download', [\App\Http\Controllers\SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::post('/settings/backup/restore', [\App\Http\Controllers\SettingsController::class, 'restoreBackup'])->name('settings.backup.restore');
});

// ===== Recipes & Production (Owner & Manager) =====
Route::middleware(['auth', 'admin', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/products/{product}/recipes', [\App\Http\Controllers\RecipeController::class, 'index'])->name('products.recipes');
    Route::post('/products/{product}/produce', [\App\Http\Controllers\RecipeController::class, 'produce'])->name('products.produce');
    Route::post('/products/{product}/recipes', [\App\Http\Controllers\RecipeController::class, 'storeVersion'])->name('products.recipes.store');
    Route::put('/products/{product}/recipes/{version}', [\App\Http\Controllers\RecipeController::class, 'updateVersion'])->name('products.recipes.update');
    Route::post('/products/{product}/recipes/{version}/activate', [\App\Http\Controllers\RecipeController::class, 'activate'])->name('products.recipes.activate');
    Route::post('/products/{product}/recipes/{version}/archive', [\App\Http\Controllers\RecipeController::class, 'archive'])->name('products.recipes.archive');
    Route::post('/products/{product}/recipes/{version}/restore', [\App\Http\Controllers\RecipeController::class, 'restore'])->name('products.recipes.restore');
    Route::delete('/products/{product}/recipes/{version}', [\App\Http\Controllers\RecipeController::class, 'destroy'])->name('products.recipes.destroy');
});

// ===== Low Stock / Restock Report (Owner & Manager) =====
Route::middleware(['auth', 'admin', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/inventory-report/low-stock', [\App\Http\Controllers\InventoryController::class, 'lowStockReport'])->name('inventory.low-stock-report');
});
