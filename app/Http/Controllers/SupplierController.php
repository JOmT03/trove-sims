<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\User;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('user')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }

        $suppliers = $query->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $categories    = Supplier::CATEGORIES;
        // Supplier users who can be linked to this supplier record
        $supplierUsers = User::where('role', 'supplier')->get();
        return view('suppliers.create', compact('categories', 'supplierUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:suppliers,email',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
            'category' => 'nullable|string|in:' . implode(',', array_keys(Supplier::CATEGORIES)),
            'user_id'  => 'nullable|exists:users,id',
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added successfully!');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['user', 'orders.items']);

        // Get products listed by this supplier's linked user account
        $products = $supplier->user_id
            ? SupplierProduct::where('user_id', $supplier->user_id)->get()
            : collect();

        return view('suppliers.show', compact('supplier', 'products'));
    }

    public function edit(Supplier $supplier)
    {
        $categories    = Supplier::CATEGORIES;
        $supplierUsers = User::where('role', 'supplier')->get();
        return view('suppliers.edit', compact('supplier', 'categories', 'supplierUsers'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
            'category' => 'nullable|string|in:' . implode(',', array_keys(Supplier::CATEGORIES)),
            'user_id'  => 'nullable|exists:users,id',
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted.');
    }
}