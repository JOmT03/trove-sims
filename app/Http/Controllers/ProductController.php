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