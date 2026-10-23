<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\FinancialAuditLog;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\PeriodClosure;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Expense ledger. There is intentionally NO edit and NO delete:
 * mistakes are voided (with a reason) and re-entered, and every action is audit-logged.
 */
class ExpenseController extends Controller
{
    private const CATEGORIES = 'ingredients,packaging,utilities,rent,transport,labor,maintenance,other';

    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date', 'to' => 'nullable|date', 'category' => 'nullable|string',
        ]);

        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->endOfDay();

        $query = Expense::with(['inventoryItem', 'creator', 'voider'])
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->get();
        $activeTotal = $expenses->whereNull('voided_at')->sum('amount');
        $voidedTotal = $expenses->whereNotNull('voided_at')->sum('amount');

        $inventoryItems = Inventory::orderBy('item_name')->get();
        $closedMonths = PeriodClosure::pluck('period')->map(fn ($d) => $d->format('Y-m'))->all();

        return view('expenses.index', [
            'expenses' => $expenses, 'inventoryItems' => $inventoryItems,
            'from' => $from->toDateString(), 'to' => $to->toDateString(),
            'category' => $request->category, 'activeTotal' => $activeTotal,
            'voidedTotal' => $voidedTotal, 'closedMonths' => $closedMonths,
        ]);
    }

    public function store(Request $request)
    {
        // Restocking only applies to Ingredients; ignore the stock fields for any other category.
        if ($request->input('category') !== 'ingredients') {
            $request->merge(['inventory_id' => null, 'quantity' => null]);
        }

        $v = $request->validate([
            'expense_date' => 'required|date|before_or_equal:today',
            'category'     => 'required|string|in:' . self::CATEGORIES,
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01|max:9999999.99',
            'inventory_id' => 'nullable|exists:inventory,id|required_with:quantity',
            'quantity'     => 'nullable|integer|min:1|required_with:inventory_id',
            'receipt'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if (PeriodClosure::isClosed($v['expense_date'])) {
            return back()->withInput()->with('error',
                Carbon::parse($v['expense_date'])->format('F Y') . ' is closed. Record this in the current open month instead.');
        }

        DB::transaction(function () use ($request, $v) {
            $data = collect($v)->except('receipt')->all();
            $data['created_by'] = auth()->id();
            if ($request->hasFile('receipt')) {
                $data['receipt_path'] = $request->file('receipt')->store('receipts', 'local');
            }

            $expense = Expense::create($data);

            // Linked ingredient purchase: stock the item in and write it to the inventory history
            if (!empty($data['inventory_id']) && !empty($data['quantity'])) {
                Inventory::where('id', $data['inventory_id'])->increment('quantity_on_hand', $data['quantity']);
                InventoryLog::create([
                    'inventory_id' => $data['inventory_id'],
                    'type'         => 'received',
                    'quantity'     => $data['quantity'],
                    'notes'        => '[EXPENSE-' . $expense->id . '] Purchased via Expenses: ' . $expense->description,
                    'user_id'      => auth()->id(),
                ]);

                // FIFO: record this purchase as a cost lot (oldest-first costing)
                InventoryFifoService::receive(
                    $data['inventory_id'],
                    $data['quantity'],
                    $data['quantity'] > 0 ? round($expense->amount / $data['quantity'], 2) : 0,
                    $expense->expense_date,
                    $expense->id
                );
            }

            FinancialAuditLog::record('expense.recorded', $expense, [
                'date' => $expense->expense_date->toDateString(), 'category' => $expense->category,
                'amount' => (string) $expense->amount, 'description' => $expense->description,
                'restocked' => !empty($data['inventory_id']) ? ($data['quantity'] . ' x inventory#' . $data['inventory_id']) : null,
                'has_receipt' => isset($data['receipt_path']),
            ]);
        });

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function void(Request $request, Expense $expense)
    {
        $request->validate(['void_reason' => 'required|string|min:5|max:255']);

        if (PeriodClosure::isClosed($expense->expense_date)) {
            return back()->with('error', 'This expense belongs to a closed month and can no longer be voided.');
        }

        $result = DB::transaction(function () use ($request, $expense) {
            $locked = Expense::lockForUpdate()->find($expense->id);
            if ($locked->isVoided()) {
                return 'already';
            }

            $locked->update([
                'voided_at' => now(), 'voided_by' => auth()->id(), 'void_reason' => $request->void_reason,
            ]);

            // Undo the restock this expense caused (never below zero)
            $note = null;
            if ($locked->inventory_id && $locked->quantity) {
                $inv = Inventory::lockForUpdate()->find($locked->inventory_id);
                if ($inv) {
                    $undo = min((int) $locked->quantity, (int) $inv->quantity_on_hand);
                    $inv->decrement('quantity_on_hand', $undo);
                    $note = "stock reversed by {$undo} of {$locked->quantity}";
                    InventoryLog::create([
                        'inventory_id' => $inv->id,
                        'type'         => 'adjustment',
                        'quantity'     => $undo,
                        'notes'        => '[EXPENSE-VOID-' . $locked->id . '] Expense voided, stock removed: ' . $locked->description,
                        'user_id'      => auth()->id(),
                    ]);

                    // FIFO: reverse this purchase's cost lot
                    InventoryFifoService::reverseExpense($locked->id, $undo);
                }
            }

            FinancialAuditLog::record('expense.voided', $locked, [
                'amount' => (string) $locked->amount, 'description' => $locked->description,
                'reason' => $request->void_reason, 'inventory' => $note,
            ]);
            return 'ok';
        });

        return $result === 'already'
            ? back()->with('error', 'That expense was already voided.')
            : back()->with('success', 'Expense voided. It stays on record but no longer counts toward totals.');
    }

    public function receipt(Expense $expense)
    {
        abort_unless($expense->receipt_path && Storage::disk('local')->exists($expense->receipt_path), 404);
        FinancialAuditLog::record('expense.receipt_viewed', $expense);

        return Storage::disk('local')->response($expense->receipt_path);
    }
}