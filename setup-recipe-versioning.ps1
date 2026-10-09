# ============================================================
#  TROVE - Recipe versioning + production (keeps product_materials as the active recipe)
#     powershell -ExecutionPolicy Bypass -File setup-recipe-versioning.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Setting up recipe versioning + production..." -ForegroundColor Yellow

# ---- 0. Remove the empty product_materials migration (if truly empty) ----
$empty = Join-Path $root "database\migrations\2026_10_01_104615_create_product_materials_table.php"
if (Test-Path $empty) {
    $c = Get-Content $empty -Raw -ErrorAction SilentlyContinue
    if ([string]::IsNullOrWhiteSpace($c)) {
        Remove-Item $empty -Force
        Write-Host "  removed empty product_materials migration" -ForegroundColor Green
    } else {
        Write-Host "  product_materials migration has content - left as-is" -ForegroundColor DarkGray
    }
}

# ---- 1. Migration: product_materials (guarded, create if missing) ----
$migPM = @'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('product_materials')) {
            Schema::create('product_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
                $table->decimal('quantity_used', 10, 2)->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'inventory_id']);
            });
        }
    }
    public function down(): void {
        // intentionally left in place
    }
};
'@
$p = Join-Path $root "database\migrations\2026_10_10_000001_create_product_materials_table.php"
[System.IO.File]::WriteAllText($p, $migPM, $Utf8NoBom)
Write-Host "  wrote product_materials migration (guarded)" -ForegroundColor Green

# ---- 2. Migration: recipe_versions (+ seed from product_materials) ----
$migRV = @'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('recipe_versions')) {
            Schema::create('recipe_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name')->default('Original');
                $table->json('items')->nullable();
                $table->boolean('is_active')->default(false);
                $table->boolean('is_archived')->default(false);
                $table->timestamps();
            });
        }

        // Seed one "Original" active version per product from existing product_materials
        if (Schema::hasTable('product_materials') && Schema::hasTable('products')) {
            foreach (DB::table('products')->pluck('id') as $pid) {
                if (DB::table('recipe_versions')->where('product_id', $pid)->exists()) continue;
                $items = [];
                foreach (DB::table('product_materials')->where('product_id', $pid)->get() as $m) {
                    $items[] = ['inventory_id' => (int) $m->inventory_id, 'quantity_used' => (float) $m->quantity_used];
                }
                DB::table('recipe_versions')->insert([
                    'product_id'  => $pid,
                    'name'        => 'Original',
                    'items'       => json_encode($items),
                    'is_active'   => true,
                    'is_archived' => false,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
    public function down(): void {
        Schema::dropIfExists('recipe_versions');
    }
};
'@
$p = Join-Path $root "database\migrations\2026_10_10_000002_create_recipe_versions_table.php"
[System.IO.File]::WriteAllText($p, $migRV, $Utf8NoBom)
Write-Host "  wrote recipe_versions migration (+ seed)" -ForegroundColor Green

# ---- 3. Model: RecipeVersion ----
$model = @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeVersion extends Model
{
    protected $table = 'recipe_versions';

    protected $fillable = ['product_id', 'name', 'items', 'is_active', 'is_archived'];

    protected $casts = [
        'items'       => 'array',
        'is_active'   => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
'@
$p = Join-Path $root "app\Models\RecipeVersion.php"
[System.IO.File]::WriteAllText($p, $model, $Utf8NoBom)
Write-Host "  wrote app\Models\RecipeVersion.php" -ForegroundColor Green

# ---- 4. Controller: RecipeController ----
$ctrl = @'
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\RecipeVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index(Product $product)
    {
        $this->ensureSeed($product);
        $inventoryItems = Inventory::orderBy('item_name')->get();
        $versions = RecipeVersion::where('product_id', $product->id)
            ->orderBy('is_archived')->orderByDesc('is_active')->orderBy('name')->get();
        $active = $versions->firstWhere('is_active', true);
        $activeMax = $active ? $this->maxMake($active->items ?? []) : 0;
        return view('products.recipes', compact('product', 'inventoryItems', 'versions', 'active', 'activeMax'));
    }

    private function ensureSeed(Product $product): void
    {
        if (RecipeVersion::where('product_id', $product->id)->exists()) return;
        $items = [];
        foreach ($product->materials as $m) {
            $items[] = ['inventory_id' => $m->id, 'quantity_used' => (float) $m->pivot->quantity_used];
        }
        RecipeVersion::create([
            'product_id'  => $product->id,
            'name'        => 'Original',
            'items'       => $items,
            'is_active'   => true,
            'is_archived' => false,
        ]);
    }

    private function maxMake(array $items): int
    {
        if (empty($items)) return 0;
        $m = null;
        foreach ($items as $it) {
            $inv = Inventory::find($it['inventory_id'] ?? 0);
            $q   = (float) ($it['quantity_used'] ?? 0);
            if (! $inv || $q <= 0) continue;
            $can = (int) floor($inv->quantity_on_hand / $q);
            $m = is_null($m) ? $can : min($m, $can);
        }
        return is_null($m) ? 0 : $m;
    }

    // Keep product_materials in sync with the active recipe so legacy code keeps working
    private function syncActiveToPivot(Product $product, array $items): void
    {
        $sync = [];
        foreach ($items as $it) {
            if (empty($it['inventory_id'])) continue;
            $sync[$it['inventory_id']] = ['quantity_used' => $it['quantity_used'] ?? 0];
        }
        $product->materials()->sync($sync);
    }

    private function parseItems(Request $request): array
    {
        $items = [];
        foreach ((array) $request->input('items', []) as $row) {
            $inv = $row['inventory_id'] ?? null;
            $q   = $row['quantity_used'] ?? null;
            if (! $inv || ! is_numeric($q) || (float) $q <= 0) continue;
            $items[] = ['inventory_id' => (int) $inv, 'quantity_used' => (float) $q];
        }
        return $items;
    }

    private function guard(Product $product, RecipeVersion $version): void
    {
        abort_unless($version->product_id === $product->id, 404);
    }

    public function produce(Request $request, Product $product)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $qty  = (int) $data['quantity'];

        $active = RecipeVersion::where('product_id', $product->id)
            ->where('is_active', true)->where('is_archived', false)->first();
        if (! $active || empty($active->items)) {
            return back()->with('error', 'No active recipe to produce from.');
        }
        $items = $active->items;
        $max   = $this->maxMake($items);
        if ($qty > $max) {
            return back()->with('error', "Not enough stock. You can make up to {$max}.");
        }
        try {
            DB::beginTransaction();
            foreach ($items as $it) {
                $inv  = Inventory::findOrFail($it['inventory_id']);
                $need = (float) $it['quantity_used'] * $qty;
                $inv->update(['quantity_on_hand' => $inv->quantity_on_hand - $need]);
                InventoryLog::create([
                    'inventory_id' => $inv->id,
                    'type'         => 'used',
                    'quantity'     => $need,
                    'reference'    => 'PRODUCE',
                    'notes'        => "Produced {$qty} x {$product->product_name}",
                    'user_id'      => auth()->id(),
                ]);
            }
            $product->update(['stock_quantity' => $product->stock_quantity + $qty]);
            DB::commit();
            return back()->with('success', "Produced {$qty} {$product->product_name}. Stock updated.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Produce failed: ' . $e->getMessage());
        }
    }

    public function storeVersion(Request $request, Product $product)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $items = $this->parseItems($request);
        if (empty($items)) return back()->with('error', 'Add at least one ingredient.');

        RecipeVersion::where('product_id', $product->id)->update(['is_active' => false]);
        RecipeVersion::create([
            'product_id'  => $product->id,
            'name'        => $request->name,
            'items'       => $items,
            'is_active'   => true,
            'is_archived' => false,
        ]);
        $this->syncActiveToPivot($product, $items);
        return back()->with('success', 'New recipe version saved and set active.');
    }

    public function updateVersion(Request $request, Product $product, RecipeVersion $version)
    {
        $this->guard($product, $version);
        $request->validate(['name' => 'required|string|max:255', 'mode' => 'required|in:update,new']);
        $items = $this->parseItems($request);
        if (empty($items)) return back()->with('error', 'Add at least one ingredient.');

        if ($request->mode === 'update') {
            $version->update(['name' => $request->name, 'items' => $items]);
            if ($version->is_active) $this->syncActiveToPivot($product, $items);
            return back()->with('success', 'Recipe updated in place.');
        }

        $name = $request->name;
        if ($name === $version->name) $name .= ' (copy)';
        RecipeVersion::where('product_id', $product->id)->update(['is_active' => false]);
        RecipeVersion::create([
            'product_id'  => $product->id,
            'name'        => $name,
            'items'       => $items,
            'is_active'   => true,
            'is_archived' => false,
        ]);
        $this->syncActiveToPivot($product, $items);
        return back()->with('success', 'Saved as a new version and set active.');
    }

    public function activate(Product $product, RecipeVersion $version)
    {
        $this->guard($product, $version);
        RecipeVersion::where('product_id', $product->id)->update(['is_active' => false]);
        $version->update(['is_active' => true, 'is_archived' => false]);
        $this->syncActiveToPivot($product, $version->items ?? []);
        return back()->with('success', '"' . $version->name . '" is now the active recipe.');
    }

    public function archive(Product $product, RecipeVersion $version)
    {
        $this->guard($product, $version);
        if ($version->is_active) {
            $other = RecipeVersion::where('product_id', $product->id)
                ->where('id', '!=', $version->id)->where('is_archived', false)->first();
            if ($other) {
                $other->update(['is_active' => true]);
                $this->syncActiveToPivot($product, $other->items ?? []);
            }
            $version->update(['is_active' => false]);
        }
        $version->update(['is_archived' => true]);
        return back()->with('success', 'Recipe archived.');
    }

    public function restore(Product $product, RecipeVersion $version)
    {
        $this->guard($product, $version);
        $version->update(['is_archived' => false]);
        return back()->with('success', 'Recipe restored.');
    }

    public function destroy(Product $product, RecipeVersion $version)
    {
        $this->guard($product, $version);
        if (RecipeVersion::where('product_id', $product->id)->count() <= 1) {
            return back()->with('error', 'Keep at least one recipe.');
        }
        $wasActive = $version->is_active;
        $version->delete();
        if ($wasActive) {
            $next = RecipeVersion::where('product_id', $product->id)->where('is_archived', false)->first()
                ?? RecipeVersion::where('product_id', $product->id)->first();
            if ($next) {
                $next->update(['is_active' => true]);
                $this->syncActiveToPivot($product, $next->items ?? []);
            }
        }
        return back()->with('success', 'Recipe permanently deleted.');
    }
}
'@
$p = Join-Path $root "app\Http\Controllers\RecipeController.php"
[System.IO.File]::WriteAllText($p, $ctrl, $Utf8NoBom)
Write-Host "  wrote app\Http\Controllers\RecipeController.php" -ForegroundColor Green

# ---- 5. Routes (guarded append) ----
$webPath = Join-Path $root "routes\web.php"
$web = [System.IO.File]::ReadAllText($webPath)
if ($web -notmatch "products\.recipes\.store") {
    $routes = @'

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
'@
    $web = $web.TrimEnd() + "`r`n" + $routes + "`r`n"
    [System.IO.File]::WriteAllText($webPath, $web, $Utf8NoBom)
    Write-Host "  web.php: added recipe routes" -ForegroundColor Green
} else {
    Write-Host "  web.php: recipe routes already present (skipped)" -ForegroundColor DarkGray
}

# ---- 6. View: products/recipes.blade.php ----
$view = @'
<x-app-layout>
<x-slot name="header">Recipes &amp; Production</x-slot>
<x-slot name="subheader">{{ $product->product_name }}</x-slot>

<style>
.rc-wrap{max-width:1000px;margin:0 auto;}
.rc-card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.06);padding:20px;margin-bottom:18px;}
.rc-h{font-family:var(--f-display);font-weight:800;font-size:16px;color:var(--text);margin:0 0 4px;}
.rc-sub{font-size:12.5px;color:var(--muted);margin:0 0 14px;}
.rc-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.rc-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.rc-wrap label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;}
.rc-wrap input,.rc-wrap select{padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
.rc-wrap .btn{padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:var(--f-body);}
.btn-gold{background:var(--gold);color:#fff;} .btn-gold:hover{background:#B5651D;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.btn-sm{padding:6px 11px;font-size:12px;}
.btn-danger{background:#fff;color:#C2410C;border:1px solid #f0d0c0;}
.ing-ref summary{cursor:pointer;font-family:var(--f-display);font-weight:800;font-size:15px;color:var(--text);}
.ing-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1e9dd;font-size:13px;}
.ing-row:last-child{border-bottom:none;}
.ing-q{font-weight:700;font-variant-numeric:tabular-nums;}
table.rec{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:8px;}
table.rec th{padding:8px 10px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
table.rec td{padding:8px 10px;border-bottom:1px solid #f9fafb;}
.badge{font-size:10px;font-weight:700;padding:2px 9px;border-radius:999px;text-transform:uppercase;}
.badge-active{background:#E6F2E6;color:#2E7D32;} .badge-arch{background:#eee;color:#888;}
.ver{border:1px solid var(--border);border-radius:11px;padding:14px;margin-bottom:11px;}
.ver.arch{opacity:.65;}
.ver-head{display:flex;align-items:center;gap:9px;justify-content:space-between;}
.ver-name{font-weight:700;font-size:14px;}
.ver-items{font-size:12px;color:var(--muted);margin-top:6px;line-height:1.5;}
.ver-acts{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;}
.maxline{font-size:13px;background:#FDF6EC;border-radius:9px;padding:10px 13px;margin:10px 0;}
.maxline b{color:#B5651D;font-family:var(--f-display);}
.addrow-btn{padding:7px 12px;border:1.5px dashed var(--border);border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:12.5px;font-weight:600;cursor:pointer;}
.inline-form{display:inline;}
</style>

<div class="rc-wrap">
  @if(session('success'))<div class="rc-ok">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="rc-err">{{ session('error') }}</div>@endif

  <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm" style="margin-bottom:14px;">&larr; Back to Products</a>

  <div class="rc-card">
    <h2 class="rc-h">Active Recipe &amp; Production</h2>
    <p class="rc-sub">Finished stock on hand: <strong>{{ (int) $product->stock_quantity }}</strong> pcs</p>
    @if($active && !empty($active->items))
      <table class="rec">
        <thead><tr><th style="width:60%">Ingredient (active: {{ $active->name }})</th><th>Qty / unit</th></tr></thead>
        <tbody>
          @foreach($active->items as $it)
            @php $g = $inventoryItems->firstWhere('id', $it['inventory_id']); @endphp
            <tr><td>{{ $g?->item_name ?? 'Item #'.$it['inventory_id'] }}</td><td>{{ rtrim(rtrim(number_format($it['quantity_used'],2),'0'),'.') }} {{ $g?->unit }}</td></tr>
          @endforeach
        </tbody>
      </table>
      <div class="maxline">With current stock, you can make up to <b>{{ $activeMax }}</b> more unit(s).</div>
      <form method="POST" action="{{ route('products.produce', $product) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
        @csrf
        <div><label>Produce quantity</label><input type="number" name="quantity" min="1" value="1" style="width:120px;"></div>
        <button type="submit" class="btn btn-gold">Produce</button>
      </form>
    @else
      <p class="rc-sub">No active recipe yet. Add a version below to start producing.</p>
    @endif
  </div>

  <details class="rc-card ing-ref">
    <summary>Ingredient Stock (reference)</summary>
    <div style="margin-top:12px;">
      @forelse($inventoryItems as $g)
        <div class="ing-row"><span>{{ $g->item_name }}</span><span class="ing-q">{{ rtrim(rtrim(number_format($g->quantity_on_hand,2),'0'),'.') }} {{ $g->unit }}</span></div>
      @empty
        <p class="rc-sub" style="margin:0;">No inventory items yet.</p>
      @endforelse
    </div>
  </details>

  <div class="rc-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <h2 class="rc-h" style="margin:0;">Recipe Versions</h2>
      <button type="button" class="btn btn-gold btn-sm" onclick="rcToggle('addForm')">+ Add Version</button>
    </div>

    <div id="addForm" style="display:none;border:1px solid var(--border);border-radius:11px;padding:14px;margin-bottom:14px;background:#FDFBF7;">
      <form method="POST" action="{{ route('products.recipes.store', $product) }}">
        @csrf
        <div style="margin-bottom:10px;"><label>Recipe name</label><input type="text" name="name" value="New recipe" style="width:100%;max-width:320px;"></div>
        <table class="rec"><thead><tr><th style="width:55%">Ingredient</th><th>Qty / unit</th><th></th></tr></thead><tbody id="addBody"></tbody></table>
        <button type="button" class="addrow-btn" onclick="rcAddRow('addBody')">+ Add ingredient</button>
        <div style="margin-top:12px;"><button type="submit" class="btn btn-gold btn-sm">Save &amp; set active</button> <button type="button" class="btn btn-outline btn-sm" onclick="rcToggle('addForm')">Cancel</button></div>
      </form>
    </div>

    @foreach($versions as $v)
      <div class="ver {{ $v->is_archived ? 'arch' : '' }}">
        <div class="ver-head">
          <span class="ver-name">{{ $v->name }}</span>
          <span>
            @if($v->is_archived)<span class="badge badge-arch">Archived</span>
            @elseif($v->is_active)<span class="badge badge-active">Active</span>@endif
          </span>
        </div>
        <div class="ver-items">
          @foreach(($v->items ?? []) as $it)
            @php $g = $inventoryItems->firstWhere('id', $it['inventory_id']); @endphp
            {{ $g?->item_name ?? '#'.$it['inventory_id'] }} {{ rtrim(rtrim(number_format($it['quantity_used'],2),'0'),'.') }}{{ $g?->unit }}@if(!$loop->last) &bull; @endif
          @endforeach
        </div>
        <div class="ver-acts">
          @if($v->is_archived)
            <form class="inline-form" method="POST" action="{{ route('products.recipes.restore', [$product, $v]) }}">@csrf<button class="btn btn-outline btn-sm">Restore</button></form>
            <form class="inline-form" method="POST" action="{{ route('products.recipes.destroy', [$product, $v]) }}" onsubmit="return confirm('Permanently delete this recipe?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
          @else
            @unless($v->is_active)<form class="inline-form" method="POST" action="{{ route('products.recipes.activate', [$product, $v]) }}">@csrf<button class="btn btn-gold btn-sm">Set Active</button></form>@endunless
            <button type="button" class="btn btn-outline btn-sm" onclick="rcToggle('edit{{ $v->id }}')">Edit</button>
            <form class="inline-form" method="POST" action="{{ route('products.recipes.archive', [$product, $v]) }}">@csrf<button class="btn btn-outline btn-sm">Archive</button></form>
            <form class="inline-form" method="POST" action="{{ route('products.recipes.destroy', [$product, $v]) }}" onsubmit="return confirm('Permanently delete this recipe?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
          @endif
        </div>

        @unless($v->is_archived)
        <div id="edit{{ $v->id }}" style="display:none;border-top:1px solid var(--border);margin-top:12px;padding-top:12px;">
          <form method="POST" action="{{ route('products.recipes.update', [$product, $v]) }}">
            @csrf @method('PUT')
            <div style="margin-bottom:10px;"><label>Recipe name</label><input type="text" name="name" value="{{ $v->name }}" style="width:100%;max-width:320px;"></div>
            <table class="rec"><thead><tr><th style="width:55%">Ingredient</th><th>Qty / unit</th><th></th></tr></thead>
              <tbody id="editBody{{ $v->id }}">
                @foreach(($v->items ?? []) as $ix => $it)
                  <tr>
                    <td><select name="items[{{ $ix }}][inventory_id]" required><option value="">- Select -</option>
                      @foreach($inventoryItems as $g)<option value="{{ $g->id }}" {{ $g->id == $it['inventory_id'] ? 'selected' : '' }}>{{ $g->item_name }} ({{ $g->unit }})</option>@endforeach
                    </select></td>
                    <td><input type="number" step="0.01" min="0.01" name="items[{{ $ix }}][quantity_used]" value="{{ $it['quantity_used'] }}" required style="width:120px;"></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">&times;</button></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <button type="button" class="addrow-btn" onclick="rcAddRow('editBody{{ $v->id }}')">+ Add ingredient</button>
            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
              <button type="submit" name="mode" value="update" class="btn btn-gold btn-sm">Update this version</button>
              <button type="submit" name="mode" value="new" class="btn btn-outline btn-sm">Save as new version</button>
            </div>
          </form>
        </div>
        @endunless
      </div>
    @endforeach
  </div>
</div>

<script>
const rcInv = {!! $inventoryItems->map(fn($i)=>['id'=>$i->id,'name'=>$i->item_name,'unit'=>$i->unit])->toJson() !!};
let rcIdx = 1000;
function rcOptions(){ return '<option value="">- Select -</option>' + rcInv.map(function(i){ return '<option value="'+i.id+'">'+i.name+' ('+i.unit+')</option>'; }).join(''); }
function rcAddRow(bodyId){
  const i = rcIdx++;
  const tr = document.createElement('tr');
  tr.innerHTML = '<td><select name="items['+i+'][inventory_id]" required>'+rcOptions()+'</select></td>'+
    '<td><input type="number" step="0.01" min="0.01" name="items['+i+'][quantity_used]" required style="width:120px;"></td>'+
    '<td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
  document.getElementById(bodyId).appendChild(tr);
}
function rcToggle(id){ var e=document.getElementById(id); var open = e.style.display==='none'; e.style.display = open ? 'block':'none'; if(open && id==='addForm' && document.getElementById('addBody').children.length===0){ rcAddRow('addBody'); } }
</script>
</x-app-layout>
'@
$viewDir = Join-Path $root "resources\views\products"
New-Item -ItemType Directory -Force -Path $viewDir | Out-Null
$p = Join-Path $viewDir "recipes.blade.php"
[System.IO.File]::WriteAllText($p, $view, $Utf8NoBom)
Write-Host "  wrote resources\views\products\recipes.blade.php" -ForegroundColor Green

# ---- 7. Add a "Recipes" link to the products index kebab menu ----
$idxPath = Join-Path $viewDir "index.blade.php"
if (Test-Path $idxPath) {
    $idx = [System.IO.File]::ReadAllText($idxPath)
    $old = '<a href="{{ route(''products.show'', $product) }}">View</a>'
    $new = $old + "`r`n" + '                    @can(''admin'')<a href="{{ route(''products.recipes'', $product) }}">Recipes</a>@endcan'
    if ($idx.Contains($old) -and -not $idx.Contains('products.recipes')) {
        $idx = $idx.Replace($old, $new)
        [System.IO.File]::WriteAllText($idxPath, $idx, $Utf8NoBom)
        Write-Host "  products/index: added Recipes link to kebab menu" -ForegroundColor Green
    } else {
        Write-Host "  products/index: could not add Recipes link - add it manually (or already present)." -ForegroundColor Yellow
    }
}

# ---- 8. Migrate + clear caches ----
Write-Host ""
php artisan migrate --force
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - Recipe versioning + production is ready." -ForegroundColor Cyan
Write-Host "  Open a product's kebab menu -> Recipes, then refresh (Ctrl+F5)." -ForegroundColor White
