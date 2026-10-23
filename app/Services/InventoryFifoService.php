<?php

namespace App\Services;

use App\Models\InventoryLot;
use App\Models\InventoryLog;
use Illuminate\Support\Carbon;

/**
 * FIFO cost layer for raw materials.
 *
 * A "lot" is one received purchase with its own unit cost. The authoritative
 * remaining-per-lot is COMPUTED (not stored live) by allocating total
 * consumption (InventoryLog type used/damaged, from the first lot onward)
 * across the lots oldest-first. So it can never drift from the movement
 * ledger: the ledger is the single source of truth.
 */
class InventoryFifoService
{
    /** Record a received lot (a purchase) with its own unit cost. */
    public static function receive($inventoryId, $qty, $unitCost, $date = null, $expenseId = null): InventoryLot
    {
        $qty = (float) $qty;
        return InventoryLot::create([
            'inventory_id'  => $inventoryId,
            'qty_received'  => $qty,
            'qty_remaining' => $qty, // display convenience; lots() recomputes the truth
            'unit_cost'     => round((float) $unitCost, 2),
            'received_at'   => $date ? Carbon::parse($date)->toDateString() : now()->toDateString(),
            'expense_id'    => $expenseId,
        ]);
    }

    /** Undo (part of) a received lot when its expense is voided. */
    public static function reverseExpense($expenseId, $undoneQty = null): void
    {
        $lot = InventoryLot::where('expense_id', $expenseId)->orderByDesc('id')->first();
        if (! $lot) {
            return;
        }
        $cut  = $undoneQty === null ? (float) $lot->qty_received : min((float) $undoneQty, (float) $lot->qty_received);
        $left = round((float) $lot->qty_received - $cut, 2);
        if ($left <= 0) {
            $lot->delete();
        } else {
            $lot->update(['qty_received' => $left, 'qty_remaining' => min((float) $lot->qty_remaining, $left)]);
        }
    }

    /** Total consumed (used + damaged) for an item on/after a date. */
    protected static function consumed($inventoryId, $fromDate = null, $toDate = null): float
    {
        return (float) InventoryLog::where('inventory_id', $inventoryId)
            ->whereIn('type', ['used', 'damaged'])
            ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate,   fn ($q) => $q->whereDate('created_at', '<=', $toDate))
            ->sum('quantity');
    }

    /** Lots oldest-first, each with its FIFO-allocated remaining + value. */
    public static function lots($inventoryId): array
    {
        $lots = InventoryLot::where('inventory_id', $inventoryId)
            ->orderBy('received_at')->orderBy('id')->get();
        if ($lots->isEmpty()) {
            return [];
        }

        $toAllocate = self::consumed($inventoryId, $lots->first()->received_at);

        $out = [];
        foreach ($lots as $lot) {
            $recv = (float) $lot->qty_received;
            $take = min($recv, $toAllocate);
            $toAllocate -= $take;
            $remaining = round($recv - $take, 2);
            $out[] = [
                'lot'       => $lot,
                'received'  => $recv,
                'remaining' => $remaining,
                'unit_cost' => (float) $lot->unit_cost,
                'value'     => round($remaining * (float) $lot->unit_cost, 2),
            ];
        }
        return $out;
    }

    public static function remaining($inventoryId): float
    {
        return round(array_sum(array_column(self::lots($inventoryId), 'remaining')), 2);
    }

    public static function valuation($inventoryId): float
    {
        return round(array_sum(array_column(self::lots($inventoryId), 'value')), 2);
    }

    /** Weighted-average cost of what is still on hand (for display). */
    public static function avgUnitCost($inventoryId): float
    {
        $rem = self::remaining($inventoryId);
        return $rem > 0 ? round(self::valuation($inventoryId) / $rem, 2) : 0.0;
    }

    /** FIFO cost of goods used within [from, to] (dates). For EOD/Reports. */
    public static function cogs($inventoryId, $from, $to): float
    {
        $lots = InventoryLot::where('inventory_id', $inventoryId)
            ->orderBy('received_at')->orderBy('id')->get();
        if ($lots->isEmpty()) {
            return 0.0;
        }
        $since = $lots->first()->received_at;

        $before  = (float) InventoryLog::where('inventory_id', $inventoryId)->whereIn('type', ['used', 'damaged'])
            ->whereDate('created_at', '>=', $since)->whereDate('created_at', '<', $from)->sum('quantity');
        $inRange = (float) InventoryLog::where('inventory_id', $inventoryId)->whereIn('type', ['used', 'damaged'])
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->sum('quantity');

        $skip = $before; $need = $inRange; $cost = 0.0;
        foreach ($lots as $lot) {
            $avail = (float) $lot->qty_received;
            $s = min($avail, $skip); $skip -= $s; $avail -= $s;
            if ($avail <= 0) { continue; }
            $take = min($avail, $need); $need -= $take;
            $cost += $take * (float) $lot->unit_cost;
            if ($need <= 0) { break; }
        }
        return round($cost, 2);
    }
}