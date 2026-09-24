<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Site;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with('site');

        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->search) {
            $query->where('item_name', 'like', '%' . $request->search . '%');
        }

        $inventories = $query->latest()->get();
        $lowStock = $inventories->filter(fn($i) => $i->isLowStock())->count();
        $damaged = $inventories->sum('quantity_damaged');

        return view('inventory.index', compact('inventories', 'lowStock', 'damaged'));
    }

    public function create()
    {
        $sites = Site::orderBy('site_name')->get();
        $categories = ['Baking Essentials', 'Dairy & Eggs', 'Flavoring & Fillings', 'Packaging', 'Coffee & Beverage', 'Other'];
        $units = ['g', 'kg', 'ml', 'liters', 'pcs', 'dozen', 'pack'];
        return view('inventory.create', compact('sites', 'categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'        => 'required|string|max:255',
            'category'         => 'required|string|max:255',
            'unit'             => 'required|string|max:50',
            'quantity_on_hand' => 'required|numeric|min:0',
            'quantity_damaged' => 'nullable|numeric|min:0',
            'minimum_stock'    => 'required|numeric|min:0',
            'site_id'          => 'nullable|exists:sites,id',
            'notes'            => 'nullable|string',
        ]);

        $inventory = Inventory::create([
            'item_name'        => $validated['item_name'],
            'category'         => $validated['category'],
            'unit'             => $validated['unit'],
            'quantity_on_hand' => $validated['quantity_on_hand'],
            'quantity_damaged' => $validated['quantity_damaged'] ?? 0,
            'minimum_stock'    => $validated['minimum_stock'],
            'site_id'          => $validated['site_id'] ?? null,
            'notes'            => $validated['notes'] ?? null,
        ]);

        InventoryLog::create([
            'inventory_id' => $inventory->id,
            'type'         => 'adjustment',
            'quantity'     => $inventory->quantity_on_hand,
            'ref_note'     => 'INITIAL-STOCK',
            'notes'        => 'Initial inventory record created',
            'user_id'      => auth()->id(),
        ]);

        return redirect()->route('inventory.index')->with('success', 'Inventory item added successfully.');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['site', 'logs.user']);
        return view('inventory.show', compact('inventory'));
    }

    public function adjust(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'type'     => 'required|in:used,damaged,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'notes'    => 'nullable|string',
        ]);

        if ($validated['type'] === 'used') {
            $inventory->decrement('quantity_on_hand', $validated['quantity']);
        } elseif ($validated['type'] === 'damaged') {
            $inventory->decrement('quantity_on_hand', $validated['quantity']);
            $inventory->increment('quantity_damaged', $validated['quantity']);
        } else {
            $inventory->increment('quantity_on_hand', $validated['quantity']);
        }

        InventoryLog::create([
            'inventory_id' => $inventory->id,
            'type'         => $validated['type'],
            'quantity'     => $validated['quantity'],
            'notes'        => $validated['notes'] ?? null,
            'user_id'      => auth()->id(),
        ]);

        return back()->with('success', 'Inventory adjusted successfully.');
    }
      public function edit(Inventory $inventory)
    {
        $sites = Site::orderBy('site_name')->get();
        $categories = ['Baking Essentials', 'Dairy & Eggs', 'Flavoring & Fillings', 'Packaging', 'Coffee & Beverage', 'Other'];
        $units = ['g', 'kg', 'ml', 'liters', 'pcs', 'dozen', 'pack'];
        return view('inventory.edit', compact('inventory', 'sites', 'categories', 'units'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'item_name'     => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'unit'          => 'required|string|max:50',
            'minimum_stock' => 'required|numeric|min:0',
            'site_id'       => 'nullable|exists:sites,id',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Item deleted successfully.');
    }



}