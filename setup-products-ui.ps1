# ============================================================
#  TROVE - PRODUCTS UI REDESIGN (photo cards + chips + menu + description)
#     powershell -ExecutionPolicy Bypass -File setup-products-ui.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host ""
Write-Host "Writing product UI files..." -ForegroundColor Yellow

$p = Join-Path $root "database\migrations\2026_10_04_000004_add_image_to_products.php"
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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote database\migrations\2026_10_04_000004_add_image_to_products.php" -ForegroundColor Green

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
        'image_path',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // Materials (inventory items) used in this product - the recipe
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
use Illuminate\Support\Facades\Storage;

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
                'description'   => 'nullable|string|max:1000',
                'category'      => 'nullable|string|max:255',
                'price'         => 'required|numeric|min:0',
                'stock_quantity' => 'nullable|numeric|min:0',
                'site_id'       => 'nullable|exists:sites,id',
                'image'         => 'nullable|image|max:2048',
                'recipe'        => 'nullable|array',
                'recipe.*.inventory_id' => 'numeric|exists:inventory,id',
                'recipe.*.quantity_needed' => 'numeric|min:0.01',
            ]);

            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'product_name'   => $validated['product_name'],
                'description'    => $validated['description'] ?? null,
                'category'       => $validated['category'] ?? null,
                'price'          => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'site_id'        => $validated['site_id'] ?? null,
                'status'         => 'active',
                'image_path'     => $imagePath,
            ]);

            if (! empty($validated['recipe'])) {
                $materials = [];
                foreach ($validated['recipe'] as $recipe_item) {
                    if (empty($recipe_item['inventory_id'])) continue;

                    $inventory_id    = $recipe_item['inventory_id'];
                    $quantity_needed = $recipe_item['quantity_needed'];
                    $inventory = Inventory::findOrFail($inventory_id);

                    if ($inventory->quantity_on_hand < $quantity_needed) {
                        throw new \Exception(
                            "Insufficient {$inventory->item_name}. Available: {$inventory->quantity_on_hand}, Needed: {$quantity_needed}"
                        );
                    }

                    $inventory->update([
                        'quantity_on_hand' => $inventory->quantity_on_hand - $quantity_needed
                    ]);

                    InventoryLog::create([
                        'inventory_id' => $inventory_id,
                        'type'         => 'used',
                        'quantity'     => $quantity_needed,
                        'reference'    => 'PRODUCT-CREATE',
                        'notes'        => "Material used for product: {$product->product_name}",
                        'user_id'      => auth()->id(),
                    ]);

                    $materials[$inventory_id] = ['quantity_used' => $quantity_needed];
                }

                if (! empty($materials)) {
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
                'description'   => 'nullable|string|max:1000',
                'category'      => 'nullable|string|max:255',
                'price'         => 'required|numeric|min:0',
                'site_id'       => 'nullable|exists:sites,id',
                'status'        => 'nullable|in:active,inactive',
                'image'         => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $validated['image_path'] = $request->file('image')->store('products', 'public');
            }

            unset($validated['image']);
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
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
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

$p = Join-Path $root "resources\views\products\index.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Products</x-slot>
<x-slot name="subheader">Trove menu - cakes, pastries &amp; coffee</x-slot>

<style>
.p-wrap{max-width:1040px;margin:0 auto;}
.p-top{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:22px;}
.chips{display:flex;gap:9px;flex-wrap:wrap;}
.chip{border:1px solid var(--border);background:var(--white);color:var(--muted);border-radius:999px;padding:8px 18px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--f-body);}
.chip.active{background:var(--gold);color:#fff;border-color:var(--gold);}
.newbtn{background:var(--gold);color:#fff;border:none;border-radius:10px;padding:10px 18px;font-weight:700;font-size:13.5px;cursor:pointer;text-decoration:none;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.p-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px;}
.p-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 2px rgba(74,44,23,.05),0 8px 22px rgba(74,44,23,.06);display:flex;flex-direction:column;position:relative;}
.p-photo{height:150px;display:flex;align-items:center;justify-content:center;position:relative;border-radius:16px 16px 0 0;background:linear-gradient(135deg,#F3DCC4,#E8C07A);overflow:hidden;}
.p-photo img{width:100%;height:100%;object-fit:cover;display:block;}
.p-photo .ph-emoji{font-size:44px;}
.st{position:absolute;top:10px;left:10px;font-size:10px;font-weight:700;padding:3px 9px;border-radius:999px;background:rgba(255,255,255,.92);color:#15803D;z-index:2;}
.st.low{color:#B45309;} .st.out{color:#C2410C;}
.kebab{position:absolute;top:9px;right:9px;width:30px;height:30px;border-radius:9px;border:none;background:rgba(255,255,255,.92);color:#4A2C17;font-size:18px;line-height:1;cursor:pointer;display:grid;place-items:center;box-shadow:0 1px 4px rgba(0,0,0,.12);z-index:3;}
.kebab:hover{background:#fff;}
.menu{position:absolute;top:42px;right:9px;background:var(--white);border:1px solid var(--border);border-radius:11px;box-shadow:0 8px 24px rgba(0,0,0,.16);overflow:hidden;z-index:5;min-width:130px;}
.menu a{display:block;padding:10px 14px;font-size:13px;font-weight:600;color:var(--text);text-decoration:none;cursor:pointer;}
.menu a:hover{background:#FDF6EC;}
.menu a.del{color:#C2410C;border-top:1px solid var(--border);}
.p-body{padding:14px 15px 16px;display:flex;flex-direction:column;gap:6px;flex:1;}
.p-row1{display:flex;align-items:baseline;justify-content:space-between;gap:8px;}
.p-nm{font-family:var(--f-display);font-weight:700;font-size:15.5px;letter-spacing:-.2px;min-width:0;}
.p-price{font-family:var(--f-display);font-weight:800;font-size:15.5px;color:var(--gold);white-space:nowrap;font-variant-numeric:tabular-nums;}
.p-desc{font-size:12px;color:var(--muted);line-height:1.45;min-height:34px;}
.p-meta{display:flex;align-items:center;gap:8px;font-size:11.5px;color:var(--muted);margin-top:2px;}
.p-cat{background:#FDF6EC;border:1px solid var(--border);padding:2px 9px;border-radius:999px;font-weight:600;}
.empty{background:#FDF6EC;border:1px solid var(--border);border-radius:14px;padding:50px 20px;text-align:center;color:var(--muted);}
</style>

@php $cats = $products->pluck('category')->filter()->unique()->values(); @endphp

<div class="p-wrap">
    @if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif

    <div class="p-top">
        <div class="chips" id="chips">
            <button class="chip active" data-cat="all">All</button>
            @foreach($cats as $c)<button class="chip" data-cat="{{ $c }}">{{ $c }}</button>@endforeach
        </div>
        @can('admin')<a href="{{ route('products.create') }}" class="newbtn">+ New Product</a>@endcan
    </div>

    @if($products && $products->count())
    <div class="p-grid" id="grid">
        @foreach($products as $product)
        @php $sq = (int) $product->stock_quantity; @endphp
        <div class="p-card" data-cat="{{ $product->category }}">
            <div class="p-photo">
                @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->product_name }}">
                @else
                    <span class="ph-emoji">&#127856;</span>
                @endif
                <span class="st {{ $sq<=0 ? 'out' : ($sq<=5 ? 'low' : '') }}">{{ $sq<=0 ? 'Out of stock' : $sq.' in stock' }}</span>
                <button class="kebab" aria-label="Menu" onclick="toggleMenu(event,this)">&#8942;</button>
                <div class="menu" hidden>
                    <a href="{{ route('products.show', $product) }}">View</a>
                    @can('admin')
                        <a href="{{ route('products.edit', $product) }}">Edit</a>
                        <a class="del" onclick="if(confirm('Delete this product?')){document.getElementById('del{{ $product->id }}').submit();}return false;">Delete</a>
                        <form id="del{{ $product->id }}" method="POST" action="{{ route('products.destroy', $product) }}" style="display:none;">@csrf @method('DELETE')</form>
                    @endcan
                </div>
            </div>
            <div class="p-body">
                <div class="p-row1"><span class="p-nm">{{ $product->product_name }}</span><span class="p-price">&#8369;{{ number_format($product->price, 2) }}</span></div>
                <div class="p-desc">{{ $product->description ?: 'No description yet.' }}</div>
                <div class="p-meta"><span class="p-cat">{{ $product->category ?: 'Uncategorized' }}</span><span>&middot; {{ $sq }} pcs on hand</span></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty">
        <p style="margin-bottom:16px;">No products yet.</p>
        @can('admin')<a href="{{ route('products.create') }}" class="newbtn">+ Create First Product</a>@endcan
    </div>
    @endif
</div>

<script>
function closeAllMenus(){ document.querySelectorAll('.menu').forEach(function(m){ m.hidden = true; }); }
function toggleMenu(e, btn){ e.stopPropagation(); var m = btn.nextElementSibling; var willOpen = m.hidden; closeAllMenus(); m.hidden = !willOpen; }
document.addEventListener('click', closeAllMenus);

var chips = document.getElementById('chips');
if(chips){
    chips.addEventListener('click', function(e){
        var b = e.target.closest('.chip'); if(!b) return;
        chips.querySelectorAll('.chip').forEach(function(c){ c.classList.remove('active'); });
        b.classList.add('active');
        var cat = b.getAttribute('data-cat');
        document.querySelectorAll('.p-card').forEach(function(c){
            c.hidden = !(cat === 'all' || c.getAttribute('data-cat') === cat);
        });
    });
}
</script>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\index.blade.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\products\create.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Add Product</x-slot>
<x-slot name="subheader">Create a product and define what it's made from</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
input[type=file]{padding:8px;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.add-row-btn{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:600;cursor:pointer;}
.preview{margin-top:10px;width:110px;height:110px;border-radius:10px;object-fit:cover;border:1px solid #EDE0D0;display:none;}
</style>

@php
    $invData = $inventoryItems->map(function ($i) {
        return ['id' => $i->id, 'name' => $i->item_name, 'unit' => $i->unit];
    });
@endphp

@if($errors->any())
<div style="background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;">
<strong>Please fix:</strong><ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
@csrf

<div class="card">
    <div class="card-title">Product Details</div>
    <div class="form-grid">
        <div style="grid-column:1/-1;">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" value="{{ old('product_name') }}" placeholder="e.g. Banana Cake" required>
        </div>
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" rows="2" style="width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;resize:vertical;" placeholder="Short description shown on the product card...">{{ old('description') }}</textarea>
        </div>
        <div>
            <label>Category <span style="color:red">*</span></label>
            <select name="category" required>
                <option value="">- Select -</option>
                <option value="Cake">Cake</option>
                <option value="Pastry">Pastry</option>
                <option value="Coffee">Coffee</option>
            </select>
        </div>
        <div>
            <label>Price (&#8369;) <span style="color:red">*</span></label>
            <input type="number" name="price" step="0.01" min="0" value="{{ old('price') }}" required>
        </div>
        <div>
            <label>Starting Finished Stock</label>
            <input type="number" name="stock_quantity" min="0" value="{{ old('stock_quantity', 0) }}">
        </div>
        <div>
            <label>Site</label>
            <select name="site_id">
                <option value="">- Unassigned -</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                @endforeach
            </select>
        </div>
        <div style="grid-column:1/-1;">
            <label>Product Photo</label>
            <input type="file" name="image" accept="image/*" onchange="previewImg(this)">
            <img id="preview" class="preview" alt="preview">
            <p style="font-size:11px;color:#8A7460;margin-top:6px;">Optional. JPG or PNG, up to 2MB.</p>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Recipe (Raw Materials Needed)</div>
        <button type="button" class="add-row-btn" onclick="addRow()">+ Add Ingredient</button>
    </div>
    <p style="font-size:12px;color:#8A7460;margin-bottom:12px;">Optional - define how much of each inventory item is needed to make <strong>one unit</strong> of this product. This enables automatic stock deduction.</p>
    <table>
        <thead><tr><th style="width:50%">Inventory Item</th><th>Qty Needed (per unit)</th><th></th></tr></thead>
        <tbody id="recipeBody"></tbody>
    </table>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Save Product</button>
</div>
</form>

<script>
const inventoryItems = {!! $invData->toJson() !!};
let rowIndex = 0;

function previewImg(input){
    const img = document.getElementById('preview');
    if(input.files && input.files[0]){
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    } else { img.style.display='none'; }
}
function itemOptions(){
    return inventoryItems.map(function(i){ return '<option value="'+i.id+'">'+i.name+' ('+i.unit+')</option>'; }).join('');
}
function addRow(){
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.id = 'recipe_row_'+i;
    tr.innerHTML =
        '<td><select name="recipe['+i+'][inventory_id]" required><option value="">- Select item -</option>'+itemOptions()+'</select></td>' +
        '<td><input type="number" name="recipe['+i+'][quantity_needed]" step="0.01" min="0.01" required></td>' +
        '<td><button type="button" onclick="document.getElementById(\'recipe_row_'+i+'\').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;">&times;</button></td>';
    document.getElementById('recipeBody').appendChild(tr);
}
</script>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\create.blade.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\products\edit.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Edit Product</x-slot>
<x-slot name="subheader">Update product details, photo &amp; materials</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
input[type=file]{padding:8px;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.thumb{width:110px;height:110px;object-fit:cover;border-radius:10px;border:1px solid #EDE0D0;}
.preview{margin-top:10px;width:110px;height:110px;border-radius:10px;object-fit:cover;border:1px solid #EDE0D0;display:none;}
</style>

<div style="max-width: 900px; margin: 0 auto;">
@if ($errors->any())
    <div class="alert-err"><strong>Errors:</strong><ul style="margin:8px 0 0;padding-left:20px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="card">
    <div class="card-title">Product Details</div>
    <div class="form-grid">
        <div style="grid-column:1/-1;">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" value="{{ old('product_name', $product->product_name) }}" required>
        </div>
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" rows="2" style="width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;resize:vertical;" placeholder="Short description shown on the product card...">{{ old('description', $product->description) }}</textarea>
        </div>
        <div>
            <label>Category</label>
            <select name="category">
                <option value="">- Select -</option>
                <option value="Cake" {{ old('category', $product->category) === 'Cake' ? 'selected' : '' }}>Cake</option>
                <option value="Pastry" {{ old('category', $product->category) === 'Pastry' ? 'selected' : '' }}>Pastry</option>
                <option value="Coffee" {{ old('category', $product->category) === 'Coffee' ? 'selected' : '' }}>Coffee</option>
            </select>
        </div>
        <div>
            <label>Price (&#8369;) <span style="color:red">*</span></label>
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
                <option value="">- Unassigned -</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ old('site_id', $product->site_id) == $site->id ? 'selected' : '' }}>{{ $site->site_name }}</option>
                @endforeach
            </select>
        </div>
        <div style="grid-column:1/-1;">
            <label>Product Photo</label>
            <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <div style="font-size:11px;color:#8A7460;margin-bottom:5px;">Current</div>
                    @if($product->image_path)
                        <img src="{{ asset('storage/'.$product->image_path) }}" class="thumb" alt="current">
                    @else
                        <div class="thumb" style="background:#FDF6EC;display:flex;align-items:center;justify-content:center;color:#C9B9A6;font-size:12px;">None</div>
                    @endif
                </div>
                <div style="flex:1;min-width:200px;">
                    <div style="font-size:11px;color:#8A7460;margin-bottom:5px;">Upload new (replaces current)</div>
                    <input type="file" name="image" accept="image/*" onchange="previewImg(this)">
                    <img id="preview" class="preview" alt="preview">
                    <p style="font-size:11px;color:#8A7460;margin-top:6px;">JPG or PNG, up to 2MB. Leave empty to keep current.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Materials Used in This Product</div>
    @if ($product->materials && $product->materials->count())
        <table>
            <thead><tr><th>Material</th><th>Quantity Used (per unit)</th><th>Unit</th></tr></thead>
            <tbody>
                @foreach ($product->materials as $material)
                    <tr><td>{{ $material->item_name }}</td><td>{{ number_format($material->pivot->quantity_used, 2) }}</td><td>{{ $material->unit }}</td></tr>
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

<script>
function previewImg(input){
    const img = document.getElementById('preview');
    if(input.files && input.files[0]){ img.src = URL.createObjectURL(input.files[0]); img.style.display='block'; }
    else { img.style.display='none'; }
}
</script>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\edit.blade.php" -ForegroundColor Green

$p = Join-Path $root "resources\views\products\show.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">{{ $product->product_name }}</x-slot>
<x-slot name="subheader">Product Details &amp; Materials</x-slot>

<style>
.card { background: var(--white); border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.card-title { font-size: 15px; font-weight: 800; color: var(--text); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.info-label { font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
.info-value { font-size: 18px; font-weight: 800; color: var(--text); }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: var(--muted); background: var(--bg); }
td { padding: 12px; border-bottom: 1px solid var(--border); }
.btn { display: inline-block; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; border: none; cursor: pointer; }
.btn-edit { background: var(--gold); color: var(--white); } .btn-delete { background: var(--red); color: var(--white); } .btn-back { background: var(--navy); color: var(--white); }
.status-badge { display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
.status-active { background: var(--green); color: var(--white); } .status-inactive { background: #e5e7eb; color: var(--text); }
.hero { width:100%; max-width:360px; height:240px; object-fit:cover; border-radius:12px; border:1px solid var(--border); }
.hero-ph { width:100%; max-width:360px; height:240px; border-radius:12px; border:1px solid var(--border); background:#FDF6EC; display:flex; align-items:center; justify-content:center; color:#C9B9A6; }
</style>

<div style="max-width: 1000px; margin: 0 auto;">
    <div class="card">
        <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start;">
            <div>
                @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" class="hero" alt="{{ $product->product_name }}">
                @else
                    <div class="hero-ph">No photo</div>
                @endif
            </div>
            <div style="flex:1; min-width:240px;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:18px;">
                    <h1 style="font-size:24px; font-weight:800; margin:0;">{{ $product->product_name }}</h1>
                    <span class="status-badge {{ $product->status == 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($product->status) }}</span>
                </div>
                <div class="info-grid">
                    <div><div class="info-label">Price</div><div class="info-value">&#8369;{{ number_format($product->price, 2) }}</div></div>
                    <div><div class="info-label">Category</div><div class="info-value">{{ $product->category ?? 'N/A' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    @if ($product->materials && $product->materials->count())
        <div class="card">
            <div class="card-title">Materials Used (Per Unit)</div>
            <table>
                <thead><tr><th>Material</th><th style="text-align:right;">Quantity Used</th><th style="text-align:center;">Unit</th></tr></thead>
                <tbody>
                    @foreach ($product->materials as $material)
                        <tr><td style="font-weight:500;">{{ $material->item_name }}</td><td style="text-align:right;">{{ number_format($material->pivot->quantity_used, 2) }}</td><td style="text-align:center;">{{ $material->unit }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card"><p style="color: var(--muted); margin: 0;">No materials defined for this product yet.</p></div>
    @endif

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
        <a href="{{ route('products.index') }}" class="btn btn-back">&larr; Back to Products</a>
        @can('admin')
            <a href="{{ route('products.edit', $product) }}" class="btn btn-edit">Edit</a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-delete" onclick="return confirm('Delete this product? This action cannot be undone.')">Delete</button>
            </form>
        @endcan
    </div>
</div>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\show.blade.php" -ForegroundColor Green

Write-Host ""
Write-Host "Linking storage + migrating (photo column, if missing)..." -ForegroundColor Yellow
php artisan storage:link
php artisan migrate
php artisan optimize:clear
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host " DONE! Open Products (Ctrl+F5)." -ForegroundColor Cyan
Write-Host " New card layout: photos, category chips, 3-dot menu," -ForegroundColor Cyan
Write-Host " stock badge, and a Description field in the forms." -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
