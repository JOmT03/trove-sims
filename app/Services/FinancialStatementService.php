<?php

namespace App\Services;

use App\Models\BatchItem;
use App\Models\Expense;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the Financial Statement (Income Statement).
 *
 * REVENUE
 *   1. Custom orders & commissions: total_amount of orders marked Completed, dated by completed_at.
 *   2. Branch sales, by batch_date:
 *        - BEFORE the cutoff: legacy rule, (qty_sent - qty_returned) x unit_price of RECONCILED batches.
 *        - ON/AFTER the cutoff: FINAL SALE, qty_sent x unit_price of every non-voided batch.
 * EXPENSES
 *   Active (non-voided) expenses dated by expense_date.
 *   COGS = ingredients + packaging; everything else is Operating Expense.
 * GROSS PROFIT = Revenue - COGS;  NET INCOME = Gross Profit - Operating Expenses.
 */
class FinancialStatementService
{
    /** Batches dated on/after this are final sales (no returns, no reconciliation). */
    public const BRANCH_FINAL_SALE_FROM = '2026-10-07';

    public function build(Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to   = $to->copy()->endOfDay();

        $orderRevenue = (float) Order::where('status', 'Completed')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$from, $to])
            ->sum('total_amount');

        $legacyBranch = (float) BatchItem::query()
            ->join('batches', 'batches.id', '=', 'batch_items.batch_id')
            ->where('batches.status', 'reconciled')
            ->where('batches.batch_date', '<', self::BRANCH_FINAL_SALE_FROM)
            ->whereBetween('batches.batch_date', [$from->toDateString(), $to->toDateString()])
            ->sum(DB::raw('(batch_items.qty_sent - batch_items.qty_returned) * batch_items.unit_price'));

        $finalBranch = (float) BatchItem::query()
            ->join('batches', 'batches.id', '=', 'batch_items.batch_id')
            ->whereNull('batches.voided_at')
            ->where('batches.batch_date', '>=', self::BRANCH_FINAL_SALE_FROM)
            ->whereBetween('batches.batch_date', [$from->toDateString(), $to->toDateString()])
            ->sum(DB::raw('(batch_items.qty_sent - batch_items.qty_returned) * batch_items.unit_price'));

        $branchRevenue = $legacyBranch + $finalBranch;

        $byCategory = Expense::active()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        $cogsLines = $opexLines = [];
        foreach ($byCategory as $cat => $amt) {
            if (in_array(strtolower($cat), Expense::COGS_CATEGORIES, true)) {
                $cogsLines[$cat] = $amt;
            } else {
                $opexLines[$cat] = $amt;
            }
        }
        ksort($cogsLines);
        ksort($opexLines);

        $revenue = round($orderRevenue + $branchRevenue, 2);
        $cogs    = round(array_sum($cogsLines), 2);
        $opex    = round(array_sum($opexLines), 2);
        $gross   = round($revenue - $cogs, 2);
        $net     = round($gross - $opex, 2);

        $voided = Expense::whereNotNull('voided_at')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);

        return [
            'from' => $from, 'to' => $to,
            'orderRevenue' => round($orderRevenue, 2), 'branchRevenue' => round($branchRevenue, 2),
            'revenue' => $revenue,
            'cogsLines' => $cogsLines, 'cogs' => $cogs,
            'opexLines' => $opexLines, 'opex' => $opex,
            'grossProfit' => $gross, 'netIncome' => $net,
            'grossMargin' => $revenue > 0 ? round($gross / $revenue * 100, 1) : null,
            'netMargin'   => $revenue > 0 ? round($net / $revenue * 100, 1) : null,
            'voidedCount' => (clone $voided)->count(),
            'voidedTotal' => round((float) (clone $voided)->sum('amount'), 2),
            'entryCount'  => Expense::active()->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])->count(),
        ];
    }
}