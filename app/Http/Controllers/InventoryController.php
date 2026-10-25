<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\FinancialAuditLog;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\InventoryLot;
use App\Models\PeriodClosure;
use App\Models\Site;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view') === 'archived' ? 'archived' : 'active';

        $base = Inventory::query();
        if ($request->search) {
            $base->where('item_name', 'like', '%' . $request->search . '%');
        }

        $activeCount   = (clone $base)->whereNull('archived_at')->count();
        $archivedCount = (clone $base)->whereNotNull('archived_at')->count();

        $q = clone $base;
        if ($view === 'archived') { $q->whereNotNull('archived_at'); }
        else { $q->whereNull('archived_at'); }

        $inventories = $q->orderBy('id')->get();
        $lowStock = $inventories->filter(fn($i) => $i->isLowStock())->count();
        $damaged  = $inventories->sum('quantity_damaged');

        // Movements in the chosen period: In = restocks, Out = used in production or damaged
        $period = in_array($request->get('period'), ['today', 'week', 'month'], true) ? $request->get('period') : 'month';
        $to   = now()->toDateString();
        $from = match ($period) {
            'today' => $to,
            'week'  => now()->startOfWeek(Carbon::MONDAY)->toDateString(),
            default => now()->startOfMonth()->toDateString(),
        };
        $ids = $inventories->pluck('id')->all();
        $moves = InventoryLog::whereIn('inventory_id', $ids)
            ->whereIn('type', ['received', 'used', 'damaged'])
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->selectRaw('inventory_id, type, SUM(quantity) as q')
            ->groupBy('inventory_id', 'type')->get();
        // A voided restock (expense void) takes its quantity back out of "In"
        $voidedIn = InventoryLog::whereIn('inventory_id', $ids)->where('type', 'adjustment')
            ->where('notes', 'like', '[EXPENSE-VOID-%')
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->selectRaw('inventory_id, SUM(quantity) as q')->groupBy('inventory_id')->pluck('q', 'inventory_id');

        // Latest damage reason per item in the period (shown under the item name)
        $dmgWhy = InventoryLog::whereIn('inventory_id', $ids)->where('type', 'damaged')
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->orderBy('created_at')->orderBy('id')->get(['inventory_id', 'notes'])
            ->mapWithKeys(fn ($l) => [$l->inventory_id => (string) $l->notes])->all();

        // FIFO cost layer per item: lots oldest-first, what is left of each, and its value
        $stats = [];
        foreach ($inventories as $inv) {
            $lots = InventoryFifoService::lots($inv->id);
            $left = array_sum(array_column($lots, 'remaining'));
            $value = array_sum(array_column($lots, 'value'));
            $last = $lots ? end($lots)['unit_cost'] : null;
            $mine = $moves->where('inventory_id', $inv->id);
            $stats[$inv->id] = [
                'dmg'   => round((float) $mine->where('type', 'damaged')->sum('q'), 2),
                'dmgWhy' => $dmgWhy[$inv->id] ?? null,
                'in'    => max(0, round((float) $mine->where('type', 'received')->sum('q') - (float) ($voidedIn[$inv->id] ?? 0), 2)),
                'out'   => (float) $mine->whereIn('type', ['used', 'damaged'])->sum('q'),
                'value' => round($value, 2),
                'cost'  => $left > 0 ? round($value / $left, 2) : $last,
                'last'  => $last,
                'lots'  => array_map(fn ($l) => [
                    'd' => $l['lot']->received_at->format('M d, Y'), 'q' => $l['received'],
                    'used' => round($l['received'] - $l['remaining'], 2), 'left' => $l['remaining'],
                    'cost' => $l['unit_cost'], 'value' => $l['value'],
                ], $lots),
            ];
        }
        $recentLogs = InventoryLog::whereIn('inventory_id', $ids)->latest('created_at')->latest('id')->limit(400)->get()
            ->groupBy('inventory_id')->map(fn ($g) => $g->take(12)->map(fn ($l) => [
                'd' => $l->created_at->format('M d, Y'), 't' => $l->type, 'q' => (float) $l->quantity,
                'n' => \Illuminate\Support\Str::limit(preg_replace('/^\[[A-Z-]+\d*\]\s*/', '', (string) $l->notes), 70),
            ])->values());
        $stockValue = round(array_sum(array_column($stats, 'value')), 2);
        $restockedValue = (float) InventoryLot::whereIn('inventory_id', $ids)
            ->whereDate('received_at', '>=', $from)->whereDate('received_at', '<=', $to)
            ->selectRaw('SUM(qty_received * unit_cost) as v')->value('v');

        return view('inventory.index', compact('inventories', 'lowStock', 'damaged', 'view', 'activeCount', 'archivedCount',
            'period', 'stats', 'recentLogs', 'stockValue', 'restockedValue'));
    }

    /**
     * Restock several items in one go. Each line raises stock, writes a "received" movement,
     * adds a FIFO cost lot, and records an expense (Ingredients, or Packaging for packaging items)
     * so the purchase reaches the Financial Statement. Voiding that expense reverses the line.
     */
    public function restock(Request $request)
    {
        $v = $request->validate([
            'restock_date'         => 'required|date|before_or_equal:today',
            'supplier'             => 'nullable|string|max:100',
            'items'                => 'required|array|min:1|max:50',
            'items.*.inventory_id' => 'required|exists:inventory,id',
            'items.*.quantity'     => 'required|numeric|min:0.01|max:999999',
            'items.*.unit_price'   => 'required|numeric|min:0.01|max:999999',
        ], [], [
            'items.*.inventory_id' => 'item', 'items.*.quantity' => 'quantity', 'items.*.unit_price' => 'price per unit',
        ]);

        if (PeriodClosure::isClosed($v['restock_date'])) {
            return back()->withInput()->with('error',
                Carbon::parse($v['restock_date'])->format('F Y') . ' is closed. Use a date in the current open month.');
        }

        $date = Carbon::parse($v['restock_date']);
        $when = $date->isToday() ? now() : $date->copy()->setTimeFrom(now());
        $from = trim((string) ($v['supplier'] ?? ''));

        $total = DB::transaction(function () use ($v, $date, $when, $from) {
            $total = 0;
            foreach ($v['items'] as $line) {
                $inv   = Inventory::lockForUpdate()->findOrFail($line['inventory_id']);
                $qty   = round((float) $line['quantity'], 2);
                $price = round((float) $line['unit_price'], 2);
                $amount = round($qty * $price, 2);
                $total += $amount;
                $qtyTxt = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');

                $expense = Expense::create([
                    'expense_date' => $date->toDateString(),
                    'category'     => strtolower($inv->category) === 'packaging' ? 'packaging' : 'ingredients',
                    'description'  => \Illuminate\Support\Str::limit("Restock: {$inv->item_name} {$qtyTxt} {$inv->unit} @ \u{20B1}" . number_format($price, 2)
                        . ($from !== '' ? " ({$from})" : ''), 250),
                    'amount'       => $amount,
                    'inventory_id' => $inv->id,
                    'quantity'     => $qty,
                    'created_by'   => auth()->id(),
                ]);

                $inv->update(['quantity_on_hand' => round((float) $inv->quantity_on_hand + $qty, 2)]);

                $log = new InventoryLog([
                    'inventory_id' => $inv->id,
                    'type'         => 'received',
                    'quantity'     => $qty,
                    'reference'    => 'RESTOCK',
                    'notes'        => '[EXPENSE-' . $expense->id . "] Restocked {$qtyTxt} {$inv->unit} @ \u{20B1}" . number_format($price, 2) . ($from !== '' ? " from {$from}" : ''),
                    'user_id'      => auth()->id(),
                ]);
                $log->created_at = $when;
                $log->save();

                InventoryFifoService::receive($inv->id, $qty, $price, $date->toDateString(), $expense->id);

                FinancialAuditLog::record('expense.recorded', $expense, [
                    'date' => $date->toDateString(), 'category' => $expense->category,
                    'amount' => (string) $expense->amount, 'description' => $expense->description,
                    'restocked' => $qtyTxt . ' x inventory#' . $inv->id, 'has_receipt' => false,
                ]);
            }
            return $total;
        });

        $n = count($v['items']);
        return redirect()->route('inventory.index')->with('success',
            "Restocked {$n} item" . ($n === 1 ? '' : 's') . " (\u{20B1}" . number_format($total, 2) . '). Recorded in Expenses.');
    }

    public function lowStockReport()
    {
        $items = Inventory::whereNull('archived_at')
            ->orderBy('item_name')
            ->get()
            ->filter(function ($i) {
                $on  = (float) $i->quantity_on_hand;
                $min = (float) $i->minimum_stock;
                return $on <= $min * 1.5; // low / critical / out
            })
            ->sortBy(function ($i) {
                $min = (float) $i->minimum_stock;
                return $min > 0 ? ((float) $i->quantity_on_hand) / $min : 999;
            })
            ->values();

        return view('inventory.low-stock-report', compact('items'));
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
            'unit_cost'        => 'nullable|numeric|min:0',
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

        // FIFO: opening lot for the starting quantity, at its unit cost
        if ((float) $inventory->quantity_on_hand > 0) {
            \App\Services\InventoryFifoService::receive(
                $inventory->id,
                $inventory->quantity_on_hand,
                $validated['unit_cost'] ?? 0,
                now()->toDateString(),
                null
            );
        }
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
            'quantity' => 'required|numeric|min:0.01|max:999999',
            'notes'    => 'nullable|string|max:500|required_if:type,damaged',
        ], ['notes.required_if' => 'Write the reason for the damage (e.g., broken or spoiled).']);

        if ($validated['type'] !== 'adjustment' && (float) $validated['quantity'] > (float) $inventory->quantity_on_hand) {
            return back()->with('error', 'Only ' . rtrim(rtrim(number_format((float) $inventory->quantity_on_hand, 2), '0'), '.')
                . ' ' . $inventory->unit . ' of ' . $inventory->item_name . ' is on hand.');
        }

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

        if ($validated['type'] === 'damaged') {
            return back()->with('success', 'Recorded ' . rtrim(rtrim(number_format((float) $validated['quantity'], 2), '0'), '.') . ' '
                . $inventory->unit . ' of ' . $inventory->item_name . ' as damaged.');
        }
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
            'quantity_on_hand' => 'required|numeric|min:0',
            'minimum_stock'    => 'required|numeric|min:0',
            'site_id'          => 'nullable|exists:sites,id',
        ]);

        $oldQty = (float) $inventory->quantity_on_hand;
        $newQty = isset($validated['quantity_on_hand']) ? (float) $validated['quantity_on_hand'] : $oldQty;

        $inventory->update($validated);

        if ($oldQty != $newQty) {
            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'type'         => 'adjustment',
                'quantity'     => abs($newQty - $oldQty),
                'reference'    => 'EDIT-CORRECTION',
                'notes'        => 'Quantity corrected via Edit: ' . $oldQty . ' -> ' . $newQty,
                'user_id'      => auth()->id(),
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
    }

    public function archive(Inventory $inventory)
    {
        $inventory->archived_at = now();
        $inventory->save();
        return redirect()->route('inventory.index')->with('success', $inventory->item_name . ' archived.');
    }

    public function restore(Inventory $inventory)
    {
        $inventory->archived_at = null;
        $inventory->save();
        return redirect()->route('inventory.index', ['view' => 'archived'])->with('success', $inventory->item_name . ' restored.');
    }

    public function destroy(Inventory $inventory)
    {
        if (auth()->user()->role !== 'Owner') {
            return back()->with('error', 'Only the Owner can permanently delete records.');
        }
        $inventory->delete();
        return redirect()->route('inventory.index', ['view' => 'archived'])->with('success', 'Item permanently deleted.');
    }
}
