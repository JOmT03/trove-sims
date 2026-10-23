<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\InventoryLot;
use App\Models\Expense;
use App\Services\InventoryFifoService;
use Illuminate\Console\Command;

/**
 * One-time opening lots for stock that already exists (pre-FIFO).
 * Opening cost is taken from the item's most recent ingredient expense
 * (amount / quantity); if none is found it defaults to 0 and can be fixed
 * later. Safe to re-run: items that already have a lot are skipped.
 */
class FifoBackfill extends Command
{
    protected $signature = 'trove:fifo-backfill {--date= : opening date (default today)}';
    protected $description = 'Create opening FIFO lots for current inventory on-hand';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->toDateString();
        $made = 0; $skipped = 0;

        foreach (Inventory::all() as $inv) {
            if (InventoryLot::where('inventory_id', $inv->id)->exists()) { $skipped++; continue; }
            $onHand = (float) $inv->quantity_on_hand;
            if ($onHand <= 0) { $skipped++; continue; }

            $lastExpense = Expense::where('inventory_id', $inv->id)
                ->whereNull('voided_at')->where('quantity', '>', 0)
                ->orderByDesc('expense_date')->orderByDesc('id')->first();
            $unitCost = $lastExpense ? round((float) $lastExpense->amount / (float) $lastExpense->quantity, 2) : 0.0;

            InventoryFifoService::receive($inv->id, $onHand, $unitCost, $date, null);
            $made++;
            $this->line("  opening lot: {$inv->item_name}  x{$onHand} @ {$unitCost}");
        }

        $this->info("Done. Opening lots created: {$made}, skipped: {$skipped}.");
        return self::SUCCESS;
    }
}