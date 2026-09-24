<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('site', 'items', 'user')->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::orderBy('product_name')->get();
        $sites    = Site::orderBy('site_name')->get();
        return view('orders.create', compact('products', 'sites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id'            => 'required|exists:sites,id',
            'customer_name'      => 'nullable|string|max:100',
            'order_type'         => 'required|in:Walk-in,Customized,Institutional',
            'design_description' => 'required_if:order_type,Customized|nullable|string',
            'needed_by_date'     => 'required_if:order_type,Customized|nullable|date',
            'deposit_amount'     => 'nullable|numeric|min:0',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $total = 0;
        $lineItems = [];

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $lineTotal = $product->price * $item['quantity'];
            $total += $lineTotal;

            $lineItems[] = [
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'price'      => $product->price,
            ];
        }

        $order = Order::create([
            'user_id'            => auth()->id(),
            'site_id'            => $validated['site_id'],
            'customer_name'      => $validated['customer_name'] ?? 'Walk-in',
            'order_type'         => $validated['order_type'],
            'design_description' => $validated['design_description'] ?? null,
            'needed_by_date'     => $validated['needed_by_date'] ?? null,
            'total_amount'       => $total,
            'deposit_amount'     => $validated['deposit_amount'] ?? 0,
            'status'             => 'Pending',
        ]);

        foreach ($lineItems as $line) {
            $order->items()->create($line);

            // Deduct from the product's own finished-goods stock.
            // NOTE: this only deducts at the Product level for now.
            // Raw-material-level deduction (per recipe) is a planned
            // follow-up once Inventory and RawMaterial are unified.
            Product::where('id', $line['product_id'])
                ->decrement('stock_quantity', $line['quantity']);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully.');
    }

    public function show(Order $order)
    {
        $order->load('site', 'items.product', 'user');
        return view('orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        if ($order->status !== 'Pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }
        $order->items()->delete();
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order cancelled.');
    }
    public function updateStatus(Request $request, Order $order)
{
    $request->validate(['status' => 'required|in:Pending,In Production,Ready,Completed']);
    $order->update(['status' => $request->status]);
    return back()->with('success', 'Order status updated.');
}
}