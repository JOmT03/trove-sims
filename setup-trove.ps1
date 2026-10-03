# ============================================================
#  TROVE SETUP SCRIPT  (writes UTF-8 without BOM)
#  Run from project root:
#     powershell -ExecutionPolicy Bypass -File setup-trove.ps1
# ============================================================

$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }

Write-Host ""
Write-Host "TROVE SETUP starting in: $root" -ForegroundColor Cyan

if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: 'artisan' not found here. Put setup-trove.ps1 in your project root and run again." -ForegroundColor Red
    exit 1
}

# UTF-8 encoder WITHOUT byte-order-mark (PHP-safe)
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)

Write-Host ""
Write-Host "Writing files..." -ForegroundColor Yellow

$p = Join-Path $root "database\migrations\0001_01_01_000001_create_cache_table.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote database\migrations\0001_01_01_000001_create_cache_table.php" -ForegroundColor Green

$p = Join-Path $root "database\migrations\2026_09_23_163226_create_trove_tables.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trove — full database schema (single migration).
 * Tables are created in foreign-key dependency order so everything
 * migrates cleanly in one pass.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. SITES (Matina commissary, Jacinto branch, etc.)
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 100);
            $table->string('street', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();
        });

        // 2. USERS (Owner / Manager / Staff)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('mobile_no', 20)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('role', 50)->default('Staff');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        // Laravel password reset + sessions (standard)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 3. SUPPLIERS (optional — links to a supplier User account)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        // 4. PRODUCTS (finished goods: cakes, pastries, coffee)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 150);
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->timestamps();
        });

        // 5. INVENTORY (raw materials / ingredients)
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category');
            $table->string('unit');
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->decimal('quantity_damaged', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. ORDERS (sales / customer orders)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('customer_name', 100)->nullable();
            $table->string('order_type', 50)->default('Dine-in');
            $table->text('design_description')->nullable();
            $table->date('needed_by_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('deposit_amount', 12, 2)->default(0.00);
            $table->string('status', 50)->default('Pending');
            $table->timestamps();
        });

        // 7. ORDER ITEMS
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 8. PRODUCT MATERIALS (recipe: which ingredients a product uses)
        Schema::create('product_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->decimal('quantity_used', 8, 2);
            $table->timestamps();
            $table->unique(['product_id', 'inventory_id']);
        });

        // 9. DELIVERIES (commissary -> branch)
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('delivery_number')->unique();
            $table->date('delivered_at')->nullable();
            $table->string('received_by')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. DELIVERY ITEMS
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('item_name');
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity_ordered', 10, 2)->default(0);
            $table->decimal('quantity_delivered', 10, 2)->default(0);
            $table->decimal('quantity_damaged', 10, 2)->default(0);
            $table->string('condition')->default('good');
            $table->text('damage_notes')->nullable();
            $table->timestamps();
        });

        // 11. INVENTORY LOGS (audit trail of stock movements)
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
            $table->string('type');          // received | used | damaged | adjustment
            $table->decimal('quantity', 10, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('product_materials');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sites');
    }
};
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote database\migrations\2026_09_23_163226_create_trove_tables.php" -ForegroundColor Green

$p = Join-Path $root "database\seeders\DatabaseSeeder.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Site;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        //  SITES
        // ─────────────────────────────────────────────
        $matina = Site::firstOrCreate(
            ['site_name' => 'Matina Commissary'],
            ['street' => 'McArthur Highway', 'city' => 'Davao City']
        );

        $jacinto = Site::firstOrCreate(
            ['site_name' => 'Jacinto Branch'],
            ['street' => 'C.M. Recto Ave', 'city' => 'Davao City']
        );

        // ─────────────────────────────────────────────
        //  USERS  (login: owner@trove.com / password)
        // ─────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'owner@trove.com'],
            [
                'first_name' => 'Trove',
                'last_name'  => 'Owner',
                'mobile_no'  => '09170000001',
                'password'   => Hash::make('password'),
                'role'       => 'Owner',
                'site_id'    => $matina->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@trove.com'],
            [
                'first_name' => 'Trove',
                'last_name'  => 'Manager',
                'mobile_no'  => '09170000002',
                'password'   => Hash::make('password'),
                'role'       => 'Manager',
                'site_id'    => $matina->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@trove.com'],
            [
                'first_name' => 'Trove',
                'last_name'  => 'Staff',
                'mobile_no'  => '09170000003',
                'password'   => Hash::make('password'),
                'role'       => 'Staff',
                'site_id'    => $jacinto->id,
            ]
        );

        // ─────────────────────────────────────────────
        //  INVENTORY  (raw materials / ingredients)
        // ─────────────────────────────────────────────
        $ingredients = [
            ['item_name' => 'All-Purpose Flour', 'category' => 'Baking Essentials',     'unit' => 'kg',   'qty' => 50,  'min' => 10],
            ['item_name' => 'White Sugar',       'category' => 'Baking Essentials',     'unit' => 'kg',   'qty' => 40,  'min' => 10],
            ['item_name' => 'Eggs',              'category' => 'Dairy & Eggs',          'unit' => 'dozen','qty' => 30,  'min' => 5],
            ['item_name' => 'Butter',            'category' => 'Dairy & Eggs',          'unit' => 'kg',   'qty' => 25,  'min' => 5],
            ['item_name' => 'Cocoa Powder',      'category' => 'Flavoring & Fillings',  'unit' => 'kg',   'qty' => 15,  'min' => 3],
            ['item_name' => 'Ube Halaya',        'category' => 'Flavoring & Fillings',  'unit' => 'kg',   'qty' => 12,  'min' => 3],
            ['item_name' => 'Cream Cheese',      'category' => 'Dairy & Eggs',          'unit' => 'kg',   'qty' => 18,  'min' => 4],
            ['item_name' => 'Ripe Bananas',      'category' => 'Flavoring & Fillings',  'unit' => 'kg',   'qty' => 20,  'min' => 5],
            ['item_name' => 'Whipping Cream',    'category' => 'Dairy & Eggs',          'unit' => 'liters','qty' => 15, 'min' => 3],
            ['item_name' => 'Cake Boxes',        'category' => 'Packaging',             'unit' => 'pcs',  'qty' => 100, 'min' => 20],
        ];

        $inv = [];
        foreach ($ingredients as $i) {
            $inv[$i['item_name']] = Inventory::firstOrCreate(
                ['item_name' => $i['item_name']],
                [
                    'category'         => $i['category'],
                    'unit'             => $i['unit'],
                    'quantity_on_hand' => $i['qty'],
                    'quantity_damaged' => 0,
                    'minimum_stock'    => $i['min'],
                    'site_id'          => $matina->id,
                ]
            );
        }

        // ─────────────────────────────────────────────
        //  PRODUCTS + RECIPES  (from the batch notes)
        // ─────────────────────────────────────────────
        $products = [
            [
                'product_name' => 'Chocolate Cake',
                'category'     => 'Cake',
                'price'        => 480.00,
                'recipe'       => [
                    'All-Purpose Flour' => 0.5,
                    'White Sugar'       => 0.4,
                    'Eggs'              => 0.5,
                    'Butter'            => 0.3,
                    'Cocoa Powder'      => 0.25,
                    'Cake Boxes'        => 1,
                ],
            ],
            [
                'product_name' => 'Banana Cake',
                'category'     => 'Cake',
                'price'        => 150.00,
                'recipe'       => [
                    'All-Purpose Flour' => 0.3,
                    'White Sugar'       => 0.2,
                    'Eggs'              => 0.25,
                    'Ripe Bananas'      => 0.4,
                    'Cake Boxes'        => 1,
                ],
            ],
            [
                'product_name' => 'Ube Cake',
                'category'     => 'Cake',
                'price'        => 490.00,
                'recipe'       => [
                    'All-Purpose Flour' => 0.5,
                    'White Sugar'       => 0.4,
                    'Eggs'              => 0.5,
                    'Ube Halaya'        => 0.4,
                    'Cake Boxes'        => 1,
                ],
            ],
            [
                'product_name' => 'Cheese Cake',
                'category'     => 'Cake',
                'price'        => 500.00,
                'recipe'       => [
                    'Cream Cheese'   => 0.5,
                    'White Sugar'    => 0.3,
                    'Eggs'           => 0.5,
                    'Butter'         => 0.2,
                    'Cake Boxes'     => 1,
                ],
            ],
            [
                'product_name' => 'Cotton Cake',
                'category'     => 'Cake',
                'price'        => 550.00,
                'recipe'       => [
                    'All-Purpose Flour' => 0.4,
                    'White Sugar'       => 0.35,
                    'Eggs'              => 0.6,
                    'Whipping Cream'    => 0.3,
                    'Cake Boxes'        => 1,
                ],
            ],
        ];

        foreach ($products as $p) {
            $product = Product::firstOrCreate(
                ['product_name' => $p['product_name']],
                [
                    'category' => $p['category'],
                    'price'    => $p['price'],
                    'status'   => 'active',
                    'site_id'  => $matina->id,
                ]
            );

            // Attach recipe (materials) if not already attached
            if ($product->materials()->count() === 0) {
                $attach = [];
                foreach ($p['recipe'] as $itemName => $qtyUsed) {
                    if (isset($inv[$itemName])) {
                        $attach[$inv[$itemName]->id] = ['quantity_used' => $qtyUsed];
                    }
                }
                $product->materials()->attach($attach);
            }
        }
    }
}
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote database\seeders\DatabaseSeeder.php" -ForegroundColor Green

$p = Join-Path $root "app\Providers\AppServiceProvider.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

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
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Admin gate — Owner and Manager can manage products, inventory, etc.
        // This makes @can('admin') work in Blade views.
        Gate::define('admin', function ($user) {
            return in_array($user->role, ['Owner', 'Manager']);
        });
    }
}
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote app\Providers\AppServiceProvider.php" -ForegroundColor Green

$p = Join-Path $root "app\Http\Controllers\ProductController.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('materials')->orderBy('created_at', 'desc')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $inventoryItems = Inventory::orderBy('item_name')->get();
        $sites = Site::all();
        return view('products.create', compact('inventoryItems', 'sites'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_name'  => 'required|string|max:255|unique:products',
                'category'      => 'nullable|string|max:255',
                'price'         => 'required|numeric|min:0',
                'stock_quantity' => 'nullable|numeric|min:0',
                'site_id'       => 'nullable|exists:sites,id',
                'recipe'        => 'nullable|array',
                'recipe.*.inventory_id' => 'numeric|exists:inventory,id',
                'recipe.*.quantity_needed' => 'numeric|min:0.01',
            ]);

            // Start transaction to ensure atomicity
            DB::beginTransaction();

            // Create the product
            $product = Product::create([
                'product_name'   => $validated['product_name'],
                'category'       => $validated['category'] ?? null,
                'price'          => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'site_id'        => $validated['site_id'] ?? null,
                'status'         => 'active',
            ]);

            // Attach materials to product (recipe) + deduct from inventory
            if (!empty($validated['recipe'])) {
                $materials = [];

                foreach ($validated['recipe'] as $recipe_item) {
                    if (empty($recipe_item['inventory_id'])) continue;

                    $inventory_id    = $recipe_item['inventory_id'];
                    $quantity_needed = $recipe_item['quantity_needed'];

                    $inventory = Inventory::findOrFail($inventory_id);

                    // Check if enough material available
                    if ($inventory->quantity_on_hand < $quantity_needed) {
                        throw new \Exception(
                            "Insufficient {$inventory->item_name}. Available: {$inventory->quantity_on_hand}, Needed: {$quantity_needed}"
                        );
                    }

                    // Deduct from inventory
                    $inventory->update([
                        'quantity_on_hand' => $inventory->quantity_on_hand - $quantity_needed
                    ]);

                    // Log the deduction (matches inventory_logs columns)
                    InventoryLog::create([
                        'inventory_id' => $inventory_id,
                        'type'         => 'used',
                        'quantity'     => $quantity_needed,
                        'reference'    => 'PRODUCT-CREATE',
                        'notes'        => "Material used for product: {$product->product_name}",
                        'user_id'      => auth()->id(),
                    ]);

                    // Store for attaching to product
                    $materials[$inventory_id] = ['quantity_used' => $quantity_needed];
                }

                if (!empty($materials)) {
                    $product->materials()->attach($materials);
                }
            }

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', "Product '{$product->product_name}' created successfully! Materials have been deducted from inventory.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load('materials');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $inventoryItems = Inventory::orderBy('item_name')->get();
        $sites = Site::all();
        $product->load('materials');
        return view('products.edit', compact('product', 'inventoryItems', 'sites'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'product_name'  => 'required|string|max:255|unique:products,product_name,' . $product->id,
                'category'      => 'nullable|string|max:255',
                'price'         => 'required|numeric|min:0',
                'site_id'       => 'nullable|exists:sites,id',
                'status'        => 'nullable|in:active,inactive',
            ]);

            $product->update($validated);

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Product update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Product deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }
}
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote app\Http\Controllers\ProductController.php" -ForegroundColor Green

$p = Join-Path $root "app\Models\Product.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'product_name',
        'description',
        'category',
        'price',
        'stock_quantity',
        'status',
        'site_id',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // Materials (inventory items) used in this product — the recipe
    public function materials()
    {
        return $this->belongsToMany(Inventory::class, 'product_materials')
                    ->withPivot('quantity_used')
                    ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote app\Models\Product.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\auth\register.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account — Trove</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #FDF6EC;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-nav {
            width: 100%; padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }
        .logo-link { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-box {
            width: 42px; height: 42px; border-radius: 10px;
            background: #4A2C17; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; letter-spacing: -.5px;
        }
        .logo-name { font-size: 20px; font-weight: 900; line-height: 1; }
        .logo-name .brown { color: #4A2C17; }
        .logo-sub { font-size: 11px; color: #8A7460; }
        .nav-register { font-size: 13px; color: #374151; text-decoration: none; }
        .nav-register strong { color: #D9782C; }

        main {
            flex: 1; display: flex;
            align-items: center; justify-content: center;
            padding: 40px 16px;
        }
        .wrap { width: 100%; max-width: 440px; }

        .icon-ring {
            width: 80px; height: 80px; border-radius: 50%;
            border: 2.5px solid #D9782C; background: #fff;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 16px rgba(217,120,44,.25);
        }
        .icon-ring svg { width: 38px; height: 38px; stroke: #D9782C; }

        .card {
            background: #fff; border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.10);
            padding: 36px 40px;
        }
        .card-title {
            font-size: 26px; font-weight: 900; color: #4A2C17;
            text-align: center; margin-bottom: 4px;
        }
        .card-sub {
            text-align: center; color: #8A7460;
            font-size: 14px; margin-bottom: 28px;
        }

        .alert {
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; margin-bottom: 18px;
        }
        .alert-red { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert-red ul { margin-left: 16px; margin-top: 4px; }

        .field { margin-bottom: 18px; }
        .field label {
            display: block; font-size: 13px;
            font-weight: 700; color: #374151; margin-bottom: 6px;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            stroke: #8A7460; display: flex;
        }
        .field-icon svg { width: 18px; height: 18px; }
        .field-wrap input {
            width: 100%; padding: 12px 42px;
            border: 1.5px solid #EDE0D0; border-radius: 10px;
            font-size: 14px; color: #2E1C10;
            background: #FDF6EC; outline: none;
            transition: border .2s, box-shadow .2s;
        }
        .field-wrap input:focus {
            border-color: #D9782C; background: #fff;
            box-shadow: 0 0 0 3px rgba(217,120,44,.15);
        }
        .field-wrap input.has-error { border-color: #f87171; }
        .eye-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            stroke: #8A7460; display: flex; padding: 0;
        }
        .eye-toggle svg { width: 18px; height: 18px; }
        .field-error { font-size: 12px; color: #dc2626; margin-top: 4px; }

        .btn-signin {
            width: 100%; padding: 14px; margin-top: 6px;
            background: #4A2C17; color: #fff; border: none;
            border-radius: 10px; font-size: 15px; font-weight: 900;
            letter-spacing: .5px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .2s;
        }
        .btn-signin:hover { opacity: .88; }
        .btn-signin svg { width: 18px; height: 18px; stroke: #fff; }

        .separator { text-align: center; color: #8A7460; font-size: 13px; margin: 16px 0; }

        .btn-register {
            width: 100%; padding: 13px;
            background: #fff; color: #4A2C17;
            border: 2px solid #4A2C17; border-radius: 10px;
            font-size: 14px; font-weight: 800; cursor: pointer;
            text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .2s, color .2s;
        }
        .btn-register:hover { background: #4A2C17; color: #fff; }
        .btn-register svg { width: 18px; height: 18px; }

        footer { text-align: center; padding: 16px; font-size: 12px; color: #8A7460; }
    </style>
</head>
<body>

    <header class="top-nav">
        <a href="{{ url('/') }}" class="logo-link">
            <div class="logo-box">TR</div>
            <div>
                <div class="logo-name"><span class="brown">Trove</span></div>
                <div class="logo-sub">Food & Cake Shop</div>
            </div>
        </a>
        <a href="{{ route('login') }}" class="nav-register">
            Already have an account? <strong>Sign in</strong>
        </a>
    </header>

    <main>
        <div class="wrap">

            <div class="icon-ring">
                <svg fill="none" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8 2 6 5 6 8c0 2 1 3 1 5 0 3 2 5 5 5s5-2 5-5c0-2 1-3 1-5 0-3-2-6-6-6z"/>
                </svg>
            </div>

            <div class="card">
                <h2 class="card-title">Create Your Account</h2>
                <p class="card-sub">Join Trove to manage your shop</p>

                @if($errors->any())
                    <div class="alert alert-red">
                        <strong>Please fix the following:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="field">
                        <label for="name">Full Name</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g. Maria Santos"
                                   class="{{ $errors->has('name') ? 'has-error' : '' }}"
                                   required autofocus autocomplete="name">
                        </div>
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Email Address</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email"
                                   class="{{ $errors->has('email') ? 'has-error' : '' }}"
                                   required autocomplete="username">
                        </div>
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password"
                                   placeholder="At least 8 characters"
                                   class="{{ $errors->has('password') ? 'has-error' : '' }}"
                                   required autocomplete="new-password">
                            <button type="button" class="eye-toggle" onclick="togglePw('password')">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   placeholder="Re-type your password"
                                   required autocomplete="new-password">
                            <button type="button" class="eye-toggle" onclick="togglePw('password_confirmation')">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-signin">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        CREATE ACCOUNT
                    </button>

                    <div class="separator">— or —</div>

                    <a href="{{ route('login') }}" class="btn-register">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24" style="stroke:currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Back to Sign In
                    </a>
                </form>
            </div>

        </div>
    </main>

    <footer>© {{ date('Y') }} Trove. All rights reserved.</footer>

    <script>
        function togglePw(id) {
            const pw = document.getElementById(id);
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\auth\register.blade.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\products\index.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="font-size: 28px; font-weight: bold;">Products</h1>
        @can('admin')
            <a href="{{ route('products.create') }}" style="background-color: var(--gold); color: var(--white); padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500;">
                + New Product
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div style="background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #7f1d1d; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    @if ($products && $products->count())
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            @foreach ($products as $product)
                <div style="background: var(--white); padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: box-shadow 0.3s;">
                    <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">{{ $product->product_name }}</h2>
                    
                    <div style="margin-bottom: 15px;">
                        <p style="color: var(--muted); font-size: 14px;">{{ $product->category ?? 'N/A' }}</p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <div>
                            <p style="color: var(--muted); font-size: 12px;">Price</p>
                            <p style="font-size: 18px; font-weight: bold;">₱{{ number_format($product->price, 2) }}</p>
                        </div>
                        <div>
                            <p style="color: var(--muted); font-size: 12px;">Status</p>
                            <span style="display: inline-block; padding: 5px 10px; border-radius: 4px; font-size: 12px; background-color: {{ $product->status == 'active' ? 'var(--green)' : '#e5e7eb' }}; color: {{ $product->status == 'active' ? 'var(--white)' : 'var(--text)' }};">
                                {{ ucfirst($product->status) }}
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('products.show', $product) }}" style="flex: 1; background-color: var(--navy); color: var(--white); padding: 10px; border-radius: 4px; text-align: center; text-decoration: none; font-size: 14px;">
                            View
                        </a>
                        @can('admin')
                            <a href="{{ route('products.edit', $product) }}" style="flex: 1; background-color: var(--gold); color: var(--white); padding: 10px; border-radius: 4px; text-align: center; text-decoration: none; font-size: 14px;">
                                Edit
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" style="flex: 1;">
                                @csrf @method('DELETE')
                                <button type="submit" style="width: 100%; background-color: var(--red); color: var(--white); padding: 10px; border-radius: 4px; border: none; font-size: 14px; cursor: pointer;"
                                    onclick="return confirm('Delete this product?')">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background-color: #f3f4f6; padding: 40px; border-radius: 8px; text-align: center;">
            <p style="color: var(--muted); margin-bottom: 20px;">No products yet. Create one to get started!</p>
            @can('admin')
                <a href="{{ route('products.create') }}" style="display: inline-block; background-color: var(--gold); color: var(--white); padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 500;">
                    Create First Product
                </a>
            @endcan
        </div>
    @endif
</div>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\index.blade.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\products\show.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">{{ $product->product_name }}</x-slot>
<x-slot name="subheader">Product Details & Materials</x-slot>

<style>
.card { background: var(--white); border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.card-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.info-item { }
.info-label { font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
.info-value { font-size: 18px; font-weight: 700; color: var(--text); }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: var(--muted); background: var(--bg); }
td { padding: 12px; border-bottom: 1px solid var(--border); }
.btn { display: inline-block; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; border: none; cursor: pointer; }
.btn-edit { background: var(--gold); color: var(--white); }
.btn-delete { background: var(--red); color: var(--white); }
.btn-back { background: var(--navy); color: var(--white); }
.status-badge { display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
.status-active { background: var(--green); color: var(--white); }
.status-inactive { background: #e5e7eb; color: var(--text); }
</style>

<div style="max-width: 1000px; margin: 0 auto;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 800; color: var(--text); margin: 0;">{{ $product->product_name }}</h1>
            </div>
            <span class="status-badge {{ $product->status == 'active' ? 'status-active' : 'status-inactive' }}">
                {{ ucfirst($product->status) }}
            </span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Price</div>
                <div class="info-value">₱{{ number_format($product->price, 2) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Category</div>
                <div class="info-value">{{ $product->category ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    @if ($product->materials && $product->materials->count())
        <div class="card">
            <div class="card-title">Materials Used (Per Unit)</div>
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th style="text-align: right;">Quantity Used</th>
                        <th style="text-align: center;">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($product->materials as $material)
                        <tr>
                            <td style="font-weight: 500;">{{ $material->item_name }}</td>
                            <td style="text-align: right;">{{ number_format($material->pivot->quantity_used, 2) }}</td>
                            <td style="text-align: center;">{{ $material->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <p style="color: var(--muted); margin: 0;">No materials defined for this product yet.</p>
        </div>
    @endif

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
        <a href="{{ route('products.index') }}" class="btn btn-back">← Back to Products</a>
        @can('admin')
            <a href="{{ route('products.edit', $product) }}" class="btn btn-edit">Edit</a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-delete" onclick="return confirm('Delete this product? This action cannot be undone.')">
                    Delete
                </button>
            </form>
        @endcan
    </div>
</div>

</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\show.blade.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\products\edit.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Edit Product</x-slot>
<x-slot name="subheader">Update product details and materials</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
</style>

<div style="max-width: 900px; margin: 0 auto;">
@if ($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#7f1d1d;padding:15px;border-radius:8px;margin-bottom:20px;">
        <strong>Errors:</strong>
        <ul style="margin:8px 0 0;padding-left:20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('products.update', $product) }}">
@csrf
@method('PUT')

<div class="card">
    <div class="card-title">Product Details</div>
    <div class="form-grid">
        <div style="grid-column:1/-1;">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" value="{{ old('product_name', $product->product_name) }}" required>
        </div>
        <div>
            <label>Category</label>
            <select name="category">
                <option value="">— Select —</option>
                <option value="Cake" {{ old('category', $product->category) === 'Cake' ? 'selected' : '' }}>Cake</option>
                <option value="Pastry" {{ old('category', $product->category) === 'Pastry' ? 'selected' : '' }}>Pastry</option>
                <option value="Coffee" {{ old('category', $product->category) === 'Coffee' ? 'selected' : '' }}>Coffee</option>
            </select>
        </div>
        <div>
            <label>Price (₱) <span style="color:red">*</span></label>
            <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div>
            <label>Site</label>
            <select name="site_id">
                <option value="">— Unassigned —</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ old('site_id', $product->site_id) == $site->id ? 'selected' : '' }}>
                        {{ $site->site_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Materials Used in This Product</div>
    @if ($product->materials && $product->materials->count())
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Quantity Used (per unit)</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->materials as $material)
                    <tr>
                        <td>{{ $material->item_name }}</td>
                        <td>{{ number_format($material->pivot->quantity_used, 2) }}</td>
                        <td>{{ $material->unit }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:#8A7460;margin:0;">No materials defined for this product.</p>
    @endif
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Save Changes</button>
</div>
</form>
</div>

</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\edit.blade.php" -ForegroundColor Green

Write-Host ""
Write-Host "Removing leftover files..." -ForegroundColor Yellow

$old = Join-Path $root "database\migrations\2026_10_01_104615_create_product_materials_table.php"
if (Test-Path $old) { Remove-Item $old -Force; Write-Host "  removed old product_materials migration" -ForegroundColor Green }

$hot = Join-Path $root "public\hot"
if (Test-Path $hot) { Remove-Item $hot -Force; Write-Host "  removed stale Vite hot file" -ForegroundColor Green }

Write-Host ""
Write-Host "Running database migration + seed..." -ForegroundColor Yellow
php artisan migrate:fresh --seed
php artisan optimize:clear

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host " DONE! Start the server with:  php artisan serve" -ForegroundColor Cyan
Write-Host " Then log in at http://127.0.0.1:8000/login" -ForegroundColor Cyan
Write-Host "    Email:    owner@trove.com" -ForegroundColor White
Write-Host "    Password: password" -ForegroundColor White
Write-Host "============================================================" -ForegroundColor Cyan
