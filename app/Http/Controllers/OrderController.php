<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('supplier', 'items', 'user')->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        // Get all suppliers (users with role=supplier who have a linked Supplier record)
        $suppliers = Supplier::with('user')->orderBy('name')->get();
        return view('orders.create', compact('suppliers'));
    }

    /**
     * AJAX — returns supplier's products when admin selects a supplier
     * Called via GET /api/suppliers/{supplier}/products
     */
    public function getSupplierProducts(Supplier $supplier)
    {
        // Get products from the supplier's user account
        $products = SupplierProduct::where('user_id', $supplier->user_id)
            ->where('is_available', true)
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'category' => $p->category,
                'unit'     => $p->unit,
                'price'    => $p->price,
            ]);

        return response()->json(['products' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'   => 'required|exists:suppliers,id',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.item_name'  => 'required|string',
            'items.*.category'   => 'required|string',
            'items.*.unit'       => 'required|string',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'supplier_id'    => $request->supplier_id,
            'user_id'        => auth()->id(),
            'status'         => 'pending',
            'payment_method' => 'cash_on_delivery',
            'notes'          => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $order->items()->create($item);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order ' . $order->order_number . ' placed! Waiting for supplier confirmation.');
    }

    public function show(Order $order)
    {
        $order->load('supplier', 'items', 'user', 'deliveries');
        return view('orders.show', compact('order'));
    }

    /**
     * AJAX — returns order items as JSON for delivery create page
     */
    public function getItems(Order $order)
    {
        $order->load('items', 'supplier');
        return response()->json([
            'order' => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'supplier'     => $order->supplier->name ?? '',
            ],
            'items' => $order->items->map(fn($item) => [
                'id'         => $item->id,
                'name'       => $item->item_name,
                'category'   => $item->category,
                'unit'       => $item->unit,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
            ]),
        ]);
    }

    /**
     * Admin records delivery receipt — does NOT update order status
     * Status is managed by supplier
     */
    public function destroy(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }
        $order->items()->delete();
        $order->delete();
        return redirect()->route('orders.index')
            ->with('success', 'Order cancelled.');
    }
}