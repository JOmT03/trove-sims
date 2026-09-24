<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $productCount = Product::count();
        $productsByCategory = Product::all()
            ->groupBy('category')
            ->map->count()
            ->sortByDesc(fn ($v) => $v)
            ->take(8);

        $activeOrderCount = Order::whereIn('status', ['Pending', 'Processing'])->count();
        $orderPending      = Order::where('status', 'Pending')->count();
        $completedOrders   = Order::where('status', 'Completed')->count();

        $deliveryTotal      = Delivery::count();
        $deliveriesThisWeek = Delivery::whereBetween('created_at', [
            now()->startOfWeek(), now()->endOfWeek(),
        ])->count();

        $inventoryCount = Inventory::count();
        $lowStock       = Inventory::all()->filter->isLowStock()->count();

        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('dashboard', compact(
            'productCount', 'productsByCategory',
            'activeOrderCount', 'orderPending', 'completedOrders',
            'deliveryTotal', 'deliveriesThisWeek',
            'inventoryCount', 'lowStock',
            'recentOrders'
        ));
    }
}