<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('site', 'recipe')->latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $sites = Site::orderBy('site_name')->get();
        $inventoryItems = Inventory::orderBy('item_name')->get();
        return view('products.create', compact('sites', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name'  => 'required|string|max:150',
            'category'      => 'required|string|max:50',
            'price'         => 'required|numeric|min:0',
            'stock_quantity'=> 'nullable|integer|min:0',
            'site_id'       => 'nullable|exists:sites,id',
            'recipe'        => 'nullable|array',
            'recipe.*.inventory_id'   => 'required_with:recipe|exists:inventory,id',
            'recipe.*.quantity_needed'=> 'required_with:recipe|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::create([
                'product_name'   => $validated['product_name'],
                'category'       => $validated['category'],
                'price'          => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'] ?? 0,
                'site_id'        => $validated['site_id'] ?? null,
            ]);

            foreach ($validated['recipe'] ?? [] as $line) {
                $product->recipe()->attach($line['inventory_id'], [
                    'quantity_needed' => $line['quantity_needed'],
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function show(Product $product)
    {
        $product->load('site', 'recipe');
        return view('products.show', compact('product'));
    }
}