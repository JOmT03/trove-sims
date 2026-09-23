<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierProductController extends Controller
{
    // Supplier sees their own products
    public function index()
    {
        $this->requireSupplier();
        $products = SupplierProduct::where('user_id', auth()->id())->latest()->get();
        return view('supplier.products.index', compact('products'));
    }

    public function create()
    {
        $this->requireSupplier();
        $categories = Supplier::CATEGORIES;
        $units      = ['bags', 'pcs', 'liters', 'gallons', 'meters', 'kg',
                        'tons', 'rolls', 'sheets', 'sets', 'boxes', 'drums'];
        return view('supplier.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $this->requireSupplier();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'unit'        => 'required|string',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('supplier-products', 'public');
        }

        SupplierProduct::create([
            'user_id'      => auth()->id(),
            'name'         => $validated['name'],
            'category'     => $validated['category'],
            'unit'         => $validated['unit'],
            'price'        => $validated['price'],
            'description'  => $validated['description'] ?? null,
            'image'        => $imagePath,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('supplier.products.index')
            ->with('success', 'Product added successfully!');
    }

    public function edit(SupplierProduct $product)
    {
        $this->requireOwner($product);
        $categories = Supplier::CATEGORIES;
        $units      = ['bags', 'pcs', 'liters', 'gallons', 'meters', 'kg',
                        'tons', 'rolls', 'sheets', 'sets', 'boxes', 'drums'];
        return view('supplier.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, SupplierProduct $product)
    {
        $this->requireOwner($product);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string',
            'unit'        => 'required|string',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('supplier-products', 'public');
        }

        $product->update([
            'name'         => $validated['name'],
            'category'     => $validated['category'],
            'unit'         => $validated['unit'],
            'price'        => $validated['price'],
            'description'  => $validated['description'] ?? null,
            'image'        => $imagePath,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('supplier.products.index')
            ->with('success', 'Product updated!');
    }

    public function destroy(SupplierProduct $product)
    {
        $this->requireOwner($product);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return redirect()->route('supplier.products.index')
            ->with('success', 'Product deleted.');
    }

    // Supplier sees incoming orders for their products
    public function orders()
    {
        $this->requireSupplier();
        $orders = \App\Models\Order::with(['user', 'items', 'supplier'])
            ->where('supplier_id', auth()->user()->supplier?->id)
            ->latest()->get();
        return view('supplier.orders', compact('orders'));
    }

    // Supplier updates order status (confirm → ship → deliver)
    public function updateOrderStatus(Request $request, \App\Models\Order $order)
    {
        $this->requireSupplier();

        $request->validate([
            'status' => 'required|in:confirmed,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated to ' . ucfirst($request->status));
    }

    // ── Helpers ──
    private function requireSupplier()
    {
        if (!auth()->user()->isSupplier()) abort(403, 'Supplier access only.');
    }

    private function requireOwner(SupplierProduct $product)
    {
        $this->requireSupplier();
        if ($product->user_id !== auth()->id()) abort(403);
    }
}