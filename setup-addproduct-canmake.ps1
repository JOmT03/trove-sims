# ============================================================
#  TROVE - Add Product: unified page (ingredient panel + recipe + live can-make
#          + produce starting batch) and fix store() to deduct recipe x qty.
#     powershell -ExecutionPolicy Bypass -File setup-addproduct-canmake.ps1
#  Keeps Description + Photo. Removes the Site field. Backs up both files to .bak.
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Rebuilding the Add Product page + fixing store()..." -ForegroundColor Yellow

# ---------- 1. New create.blade.php ----------
$viewPath = Join-Path $root "resources\views\products\create.blade.php"
if (-not (Test-Path $viewPath)) { Write-Host "ERROR: products/create.blade.php not found." -ForegroundColor Red; exit 1 }
$vbak = $viewPath + ".bak"
if (-not (Test-Path $vbak)) { Copy-Item $viewPath $vbak; Write-Host "  backed up create.blade.php -> .bak" -ForegroundColor DarkGray }

$view = @'
<x-app-layout>
<x-slot name="header">Add Product</x-slot>
<x-slot name="subheader">Create a product, set its recipe, and produce the first batch</x-slot>

<style>
.ap-layout{display:grid;grid-template-columns:260px 1fr;gap:18px;align-items:start;}
.ap-side{position:sticky;top:12px;}
.side-card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:16px 16px 14px;}
.side-head{width:100%;display:flex;align-items:center;justify-content:space-between;background:none;border:none;cursor:pointer;font-family:inherit;font-size:14px;font-weight:800;color:#2E1C10;padding:0 0 10px;}
.chev{transition:transform .15s;color:#8A7460;}
.chev.closed{transform:rotate(-90deg);}
.inv-list{display:flex;flex-direction:column;gap:11px;border-top:1px solid #EDE0D0;padding-top:12px;}
.inv-list.hide{display:none;}
.inv-row{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:13px;}
.inv-n{color:#2E1C10;font-weight:600;}
.inv-q{color:#8A7460;font-weight:700;white-space:nowrap;}
.inv-empty{font-size:12.5px;color:#8A7460;}
.inv-note{margin-top:12px;font-size:11.5px;color:#15803D;}
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:22px 24px;margin-bottom:18px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;}
input:focus,select:focus,textarea:focus{outline:none;border-color:#D9782C;background:#fff;}
input[type=file]{padding:8px;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f3f4f6;}
.add-row-btn{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;}
.hint{font-size:12px;color:#8A7460;}
.canmake{margin-top:16px;border-radius:10px;padding:13px 16px;font-size:13.5px;}
.canmake.ok{background:#FDF6EC;border:1px solid #EDE0D0;color:#4A2C17;}
.canmake.zero{background:#FBE0E0;border:1px solid #fecaca;color:#991B1B;}
.canmake.none{background:#F3F4F6;border:1px solid #e5e7eb;color:#6b7280;}
.canmake .big{font-size:21px;font-weight:800;color:#B5651D;}
.startbox{margin-top:16px;border-top:1px dashed #EDE0D0;padding-top:16px;max-width:300px;}
.warn{font-size:12px;color:#C2410C;margin-top:6px;display:none;}
.btn{padding:11px 22px;border-radius:9px;font-size:13.5px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.preview{margin-top:10px;width:110px;height:110px;border-radius:10px;object-fit:cover;border:1px solid #EDE0D0;display:none;}
@media (max-width:820px){.ap-layout{grid-template-columns:1fr;}.ap-side{position:static;}}
</style>

@php
    $invData = $inventoryItems->map(function ($i) {
        return ['id' => $i->id, 'name' => $i->item_name, 'unit' => $i->unit, 'on' => (float) $i->quantity_on_hand];
    });
@endphp

@if($errors->any())
<div style="background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;">
<strong>Please fix:</strong><ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="ap-layout">
  <aside class="ap-side">
    <div class="side-card">
      <button type="button" class="side-head" onclick="toggleInv()">
        <span>Ingredient Stock</span><span id="invchev" class="chev">&#9662;</span>
      </button>
      <div id="invlist" class="inv-list">
        @forelse($inventoryItems as $it)
          <div class="inv-row"><span class="inv-n">{{ $it->item_name }}</span><span class="inv-q">{{ rtrim(rtrim(number_format($it->quantity_on_hand,2),'0'),'.') }} {{ $it->unit }}</span></div>
        @empty
          <div class="inv-empty">No inventory items yet.</div>
        @endforelse
      </div>
      <div class="inv-note">&#10003; Deducts automatically when you produce.</div>
    </div>
  </aside>

  <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="ap-main">
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
          <textarea name="description" rows="2" style="resize:vertical;" placeholder="Short description shown on the product card...">{{ old('description') }}</textarea>
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
        <div style="grid-column:1/-1;">
          <label>Product Photo</label>
          <input type="file" name="image" accept="image/*" onchange="previewImg(this)">
          <img id="preview" class="preview" alt="preview">
          <p style="font-size:11px;color:#8A7460;margin-top:6px;">Optional. JPG or PNG, up to 2MB.</p>
        </div>
      </div>
    </div>

    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Recipe (Raw Materials Needed)</div>
        <button type="button" class="add-row-btn" onclick="addRow()">+ Add Ingredient</button>
      </div>
      <p class="hint" style="margin-bottom:12px;">Define how much of each inventory item is needed to make <strong>one unit</strong>. This enables automatic stock deduction and the can-make estimate below.</p>
      <table>
        <thead><tr><th style="width:52%">Inventory Item</th><th>Qty Needed (per unit)</th><th></th></tr></thead>
        <tbody id="recipeBody"></tbody>
      </table>

      <div id="canmake" class="canmake none">Add at least one ingredient to see how many you can make.</div>

      <div class="startbox">
        <label>Starting Finished Stock (produce now)</label>
        <input type="number" id="stockq" name="stock_quantity" min="0" value="{{ old('stock_quantity', 0) }}" oninput="recalc()">
        <div class="warn" id="warn"></div>
        <p class="hint" style="margin:6px 0 0;">How many pcs to make right now. This deducts the recipe from inventory. Max possible is shown above. Leave 0 to produce later.</p>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;">
      <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-gold">Save Product</button>
    </div>
  </form>
</div>

<script>
const inventoryItems = {!! $invData->toJson() !!};
let rowIndex = 0;

function previewImg(input){
    const img = document.getElementById('preview');
    if(input.files && input.files[0]){ img.src = URL.createObjectURL(input.files[0]); img.style.display='block'; }
    else { img.style.display='none'; }
}
function itemOptions(){
    return inventoryItems.map(function(i){ return '<option value="'+i.id+'">'+i.name+' ('+i.unit+')</option>'; }).join('');
}
function invById(id){ return inventoryItems.find(function(i){ return String(i.id)===String(id); }); }

function addRow(){
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.id = 'recipe_row_'+i;
    tr.innerHTML =
        '<td><select name="recipe['+i+'][inventory_id]" onchange="recalc()" required><option value="">- Select item -</option>'+itemOptions()+'</select></td>' +
        '<td><input type="number" name="recipe['+i+'][quantity_needed]" step="0.01" min="0.01" oninput="recalc()" required></td>' +
        '<td><button type="button" onclick="document.getElementById(\'recipe_row_'+i+'\').remove();recalc();" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;">&times;</button></td>';
    document.getElementById('recipeBody').appendChild(tr);
    recalc();
}

function recalc(){
    const rows = document.querySelectorAll('#recipeBody tr');
    const box = document.getElementById('canmake');
    const warn = document.getElementById('warn');
    let valid = 0, maxMake = Infinity;
    rows.forEach(function(r){
        const sel = r.querySelector('select');
        const inp = r.querySelector('input');
        const id = sel ? sel.value : '';
        const qty = inp ? parseFloat(inp.value) : NaN;
        if(id && qty > 0){ const it = invById(id); if(it){ valid++; maxMake = Math.min(maxMake, Math.floor(it.on / qty)); } }
    });
    warn.style.display = 'none';
    if(valid === 0){ box.className='canmake none'; box.innerHTML='Add at least one ingredient to see how many you can make.'; return; }
    if(maxMake <= 0){ box.className='canmake zero'; box.innerHTML='&#9888; Not enough stock to make even 1 unit with this recipe. Restock first.'; }
    else { box.className='canmake ok'; box.innerHTML='With current stock, you can make up to <span class="big">'+maxMake.toLocaleString('en-PH')+'</span> pcs of this product.'; }
    const sq = document.getElementById('stockq');
    const start = sq ? (parseFloat(sq.value) || 0) : 0;
    if(maxMake !== Infinity && start > maxMake){
        warn.style.display='block';
        warn.innerHTML='&#9888; You only have enough stock for '+maxMake.toLocaleString('en-PH')+' pcs. Lower the amount or restock first.';
    }
}
function toggleInv(){
    document.getElementById('invlist').classList.toggle('hide');
    document.getElementById('invchev').classList.toggle('closed');
}
addRow();
</script>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($viewPath, $view, $Utf8NoBom)
Write-Host "  wrote new products/create.blade.php" -ForegroundColor Green

# ---------- 2. ProductController with fixed store() ----------
$ctrlPath = Join-Path $root "app\Http\Controllers\ProductController.php"
if (-not (Test-Path $ctrlPath)) { Write-Host "ERROR: ProductController.php not found." -ForegroundColor Red; exit 1 }
$cbak = $ctrlPath + ".bak"
if (-not (Test-Path $cbak)) { Copy-Item $ctrlPath $cbak; Write-Host "  backed up ProductController.php -> .bak" -ForegroundColor DarkGray }

$ctrl = @'
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

            $qtyToMake = (float) ($validated['stock_quantity'] ?? 0);

            $product = Product::create([
                'product_name'   => $validated['product_name'],
                'description'    => $validated['description'] ?? null,
                'category'       => $validated['category'] ?? null,
                'price'          => $validated['price'],
                'stock_quantity' => $qtyToMake,
                'site_id'        => $validated['site_id'] ?? null,
                'status'         => 'active',
                'image_path'     => $imagePath,
            ]);

            if (! empty($validated['recipe'])) {
                $materials = [];
                foreach ($validated['recipe'] as $recipe_item) {
                    if (empty($recipe_item['inventory_id'])) continue;

                    $inventory_id = $recipe_item['inventory_id'];
                    $perUnit      = (float) $recipe_item['quantity_needed'];
                    $inventory    = Inventory::findOrFail($inventory_id);

                    // Deduct for the whole starting batch (recipe x quantity produced now)
                    $totalNeeded = $perUnit * $qtyToMake;

                    if ($totalNeeded > 0) {
                        if ($inventory->quantity_on_hand < $totalNeeded) {
                            throw new \Exception(
                                "Insufficient {$inventory->item_name}. Available: {$inventory->quantity_on_hand}, Needed: {$totalNeeded}"
                            );
                        }

                        $inventory->update([
                            'quantity_on_hand' => $inventory->quantity_on_hand - $totalNeeded
                        ]);

                        InventoryLog::create([
                            'inventory_id' => $inventory_id,
                            'type'         => 'used',
                            'quantity'     => $totalNeeded,
                            'reference'    => 'PRODUCT-CREATE',
                            'notes'        => "Produced {$qtyToMake} x {$product->product_name} on create",
                            'user_id'      => auth()->id(),
                        ]);
                    }

                    // Store the per-unit recipe (mirror for the active recipe / Recipes page)
                    $materials[$inventory_id] = ['quantity_used' => $perUnit];
                }

                if (! empty($materials)) {
                    $product->materials()->attach($materials);
                }
            }

            DB::commit();

            $msg = $qtyToMake > 0
                ? "Product '{$product->product_name}' created and {$qtyToMake} pcs produced. Ingredients deducted from inventory."
                : "Product '{$product->product_name}' created. You can produce a batch anytime from its Recipes page.";

            return redirect()->route('products.index')->with('success', $msg);
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
[System.IO.File]::WriteAllText($ctrlPath, $ctrl, $Utf8NoBom)
Write-Host "  wrote ProductController.php (store now deducts recipe x qty)" -ForegroundColor Green

# ---------- 3. Clear caches ----------
Write-Host ""
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - open Products -> + New Product." -ForegroundColor Cyan
Write-Host "  Set a recipe, watch the can-make estimate, enter a Starting Finished Stock, Save." -ForegroundColor White
Write-Host "  Rollback if needed: restore create.blade.php.bak and ProductController.php.bak." -ForegroundColor DarkGray
