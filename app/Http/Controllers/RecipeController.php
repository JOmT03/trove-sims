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