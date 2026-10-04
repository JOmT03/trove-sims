<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Inventory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('inventoryItem')->latest('expense_date')->get();
        $inventoryItems = Inventory::all();

        return view('expenses.index', compact('expenses', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category'     => 'required|string|max:50',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'inventory_id' => 'nullable|exists:inventory,id',
            'quantity'     => 'nullable|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully!');
    }
}