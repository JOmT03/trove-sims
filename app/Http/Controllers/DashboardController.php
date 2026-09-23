<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\SupplierProduct;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ── Supplier sees their own dashboard ──
        if ($user->isSupplier()) {
            $products = SupplierProduct::where('user_id', $user->id)->latest()->get();

            // Find orders linked to this supplier
            // (orders where supplier.user_id = this user)
            $supplierRecord = Supplier::where('user_id', $user->id)->first();
            $ordersQuery = $supplierRecord
                ? Order::where('supplier_id', $supplierRecord->id)->with(['user', 'items'])
                : Order::whereRaw('1=0'); // no supplier record yet = no orders

            $recentOrders    = $ordersQuery->latest()->take(5)->get();
            $totalOrders     = $ordersQuery->count();
            $pendingOrders   = $ordersQuery->where('status', 'pending')->count();
            $confirmedOrders = Order::where('supplier_id', $supplierRecord?->id)->where('status', 'confirmed')->count();
            $deliveredOrders = Order::where('supplier_id', $supplierRecord?->id)->where('status', 'delivered')->count();

            $productCount = $products->count();

            return view('supplier.dashboard', compact(
                'products', 'recentOrders',
                'productCount', 'totalOrders',
                'pendingOrders', 'confirmedOrders', 'deliveredOrders'
            ));
        }

        // ── Admin/Buyer sees main dashboard ──
        $supplierCount = Supplier::count();

        $suppliersByCategory = Supplier::all()
            ->groupBy('category')
            ->map->count()
            ->sortByDesc(fn($v) => $v)
            ->take(8);

        $currentSuppliers = Supplier::latest()->take(10)->get();

        $activeOrderCount = Order::whereIn('status', ['pending', 'confirmed', 'shipped'])->count();
        $orderPending     = Order::where('status', 'pending')->count();
        $orderConfirmed   = Order::where('status', 'confirmed')->count();
        $inTransit        = Order::where('status', 'shipped')->count();

        $deliveryTotal      = Delivery::count();
        $deliveriesThisWeek = Delivery::whereBetween('created_at', [
            now()->startOfWeek(), now()->endOfWeek(),
        ])->count();

        $inventoryCount = Inventory::count();
        $lowStock       = Inventory::all()->filter->isLowStock()->count();
        $damagedItems   = DeliveryItem::where('condition', '!=', 'good')->count();

        $recentOrders = Order::with(['supplier', 'user', 'items'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'supplierCount', 'suppliersByCategory', 'currentSuppliers',
            'activeOrderCount', 'orderPending', 'orderConfirmed', 'inTransit',
            'deliveryTotal', 'deliveriesThisWeek',
            'inventoryCount', 'lowStock', 'damagedItems',
            'recentOrders'
        ));
    }
}