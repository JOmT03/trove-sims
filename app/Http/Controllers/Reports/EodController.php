<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\DayClosure;
use App\Models\Inventory;
use App\Models\FinancialAuditLog;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Reports > End of Day. For one business date: completed POS sales, payment
 * reconciliation, money out (expenses), FIFO ingredient cost used, and net.
 * The Owner can close the day, which writes a locked snapshot (day_closures).
 */
class EodController extends Controller
{
    /** Recompute all figures for a date (never trust the client). */
    private function summarize(string $dateStr): array
    {
        $sales = Sale::with(['items', 'payments', 'cashier'])
            ->whereDate('completed_at', $dateStr)->orderBy('id')->get();

        $completed = $sales->where('status', 'completed');
        $voided    = $sales->where('status', 'voided');

        $methods = ['cash' => 0.0, 'gcash' => 0.0, 'maya' => 0.0, 'bank' => 0.0];
        foreach ($completed as $s) {
            foreach ($s->payments as $p) {
                if (array_key_exists($p->method, $methods)) {
                    $methods[$p->method] += (float) $p->amount;
                }
            }
        }

        $gross     = round($completed->sum(fn ($s) => (float) $s->total), 2);
        $txCount   = $completed->count();
        $itemsSold = (int) $completed->sum(fn ($s) => $s->items->sum('quantity'));

        $expenses     = Expense::whereDate('expense_date', $dateStr)->whereNull('voided_at')
            ->orderByDesc('amount')->get();
        $expenseTotal = round((float) $expenses->sum('amount'), 2);

        // FIFO cost of raw materials consumed (produced / damaged) on this date
        $cogs = 0.0;
        foreach (Inventory::all() as $inv) {
            $cogs += InventoryFifoService::cogs($inv->id, $dateStr, $dateStr);
        }
        $cogs = round($cogs, 2);

        return [
            'completed'    => $completed,
            'voided'       => $voided,
            'methods'      => $methods,
            'gross'        => $gross,
            'txCount'      => $txCount,
            'itemsSold'    => $itemsSold,
            'expenses'     => $expenses,
            'expenseTotal' => $expenseTotal,
            'cogs'         => $cogs,
            'net'          => round($gross - $expenseTotal, 2),
        ];
    }

    public function index(Request $request)
    {
        $dateStr = Carbon::parse($request->input('date', now()->toDateString()))->toDateString();
        $closure = DayClosure::whereDate('business_date', $dateStr)->first();

        return view('reports.eod', array_merge($this->summarize($dateStr), [
            'date'    => $dateStr,
            'closure' => $closure,
            'isOwner' => auth()->user()->role === 'Owner',
        ]));
    }

    public function close(Request $request)
    {
        $data    = $request->validate(['date' => 'required|date']);
        $dateStr = Carbon::parse($data['date'])->toDateString();

        if (auth()->user()->role !== 'Owner') {
            return back()->with('error', 'Only the Owner can close a day.');
        }
        if (DayClosure::whereDate('business_date', $dateStr)->exists()) {
            return back()->with('error', 'That day is already closed.');
        }

        $s = $this->summarize($dateStr);
        $u = auth()->user();

        $closure = DayClosure::create([
            'business_date'  => $dateStr,
            'gross_sales'    => $s['gross'],
            'cash_total'     => $s['methods']['cash'],
            'gcash_total'    => $s['methods']['gcash'],
            'maya_total'     => $s['methods']['maya'],
            'bank_total'     => $s['methods']['bank'],
            'expenses_total' => $s['expenseTotal'],
            'net'            => $s['net'],
            'tx_count'       => $s['txCount'],
            'closed_by'      => $u->id,
            'closed_by_name' => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')),
            'closed_at'      => now(),
        ]);

        FinancialAuditLog::record('eod.closed', $closure, [
            'date' => $dateStr, 'gross' => (string) $s['gross'],
            'net' => (string) $s['net'], 'tx' => $s['txCount'],
        ]);

        return redirect()->route('reports.eod', ['date' => $dateStr])
            ->with('success', "End of Day for {$dateStr} closed and locked.");
    }
}