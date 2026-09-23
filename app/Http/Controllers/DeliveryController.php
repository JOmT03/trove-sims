<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::with('order.supplier')->latest()->get();
        return view('deliveries.index', compact('deliveries'));
    }

    public function create(Request $request)
    {
        // Show confirmed, shipped, OR delivered orders
        // (delivered is included so you can still record if the status jumped ahead)
        $orders = Order::with('supplier', 'items')
            ->whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->latest()
            ->get();

        $selectedOrder = null;
        if ($request->order_id) {
            $selectedOrder = Order::with('supplier', 'items')->find($request->order_id);
        }

        return view('deliveries.create', compact('orders', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id'                   => 'required|exists:orders,id',
            'delivered_at'               => 'required|date',
            'received_by'                => 'required|string|max:255',
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.item_name'          => 'required|string',
            'items.*.category'           => 'required|string',
            'items.*.unit'               => 'required|string',
            'items.*.quantity_ordered'   => 'required|numeric|min:0',
            'items.*.quantity_delivered' => 'required|numeric|min:0',
            'items.*.quantity_damaged'   => 'nullable|numeric|min:0',
            'items.*.condition'          => 'required|in:good,partial_damage,all_damaged',
            'items.*.damage_notes'       => 'nullable|string',
        ]);

        $count          = Delivery::count() + 1;
        $deliveryNumber = 'DEL-' . now()->year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $order = Order::findOrFail($request->order_id);

        $delivery = Delivery::create([
            'delivery_number' => $deliveryNumber,
            'order_id'        => $request->order_id,
            'supplier_id'     => $order->supplier_id,
            'delivered_at'    => $request->delivered_at,
            'received_by'     => $request->received_by,
            'notes'           => $request->notes,
            'status'          => 'received',
        ]);

        foreach ($request->items as $item) {
            $delivery->items()->create([
                'order_item_id'      => $item['order_item_id'],
                'item_name'          => $item['item_name'],
                'category'           => $item['category'],
                'unit'               => $item['unit'],
                'quantity_ordered'   => $item['quantity_ordered'],
                'quantity_delivered' => $item['quantity_delivered'],
                'quantity_damaged'   => $item['quantity_damaged'] ?? 0,
                'condition'          => $item['condition'],
                'damage_notes'       => $item['damage_notes'] ?? null,
            ]);

            // Update or create inventory record
            $inv = Inventory::firstOrCreate(
                [
                    'item_name' => $item['item_name'],
                    'category'  => $item['category'],
                ],
                [
                    'unit'             => $item['unit'],
                    'quantity_on_hand' => 0,
                    'quantity_damaged' => 0,
                    'minimum_stock'    => 0,
                ]
            );
            $inv->increment('quantity_on_hand', $item['quantity_delivered']);
            if (!empty($item['quantity_damaged'])) {
                $inv->increment('quantity_damaged', $item['quantity_damaged']);
            }
        }

        // Mark order as delivered
        Order::find($request->order_id)->update(['status' => 'delivered']);

        return redirect()->route('deliveries.show', $delivery)
            ->with('success', 'Delivery ' . $deliveryNumber . ' recorded and inventory updated.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load('order.supplier', 'items');
        return view('deliveries.show', compact('delivery'));
    }
}