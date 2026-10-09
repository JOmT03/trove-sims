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