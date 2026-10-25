<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Date range (defaults to this week, Mon-Sun)
        $from = $request->input('from', Carbon::now()->startOfWeek()->toDateString());
        $to   = $request->input('to',   Carbon::now()->endOfWeek()->toDateString());

        // Dispatched batches are final sales; voided batches do not count.
        $batches = Batch::with('items.product')
            ->whereNull('voided_at')
            ->whereBetween('batch_date', [$from, $to])
            ->orderBy('batch_date')
            ->get();

        // Aggregate per flavor across all batches in the range
        $rows = [];
        $legacyReturned = 0;   // returns recorded on batches from before the final-sale rule
        foreach ($batches as $b) {
            foreach ($b->items as $it) {
                $name = $it->product->product_name ?? 'Unknown';
                $rows[$name] ??= ['sold' => 0, 'revenue' => 0];
                $rows[$name]['sold']    += $it->netSold();
                $rows[$name]['revenue'] += $it->revenue();
                $legacyReturned         += $it->qty_returned;
            }
        }
        ksort($rows);

        $totals = [
            'sold'    => array_sum(array_column($rows, 'sold')),
            'revenue' => array_sum(array_column($rows, 'revenue')),
        ];
        $batchCount = $batches->count();

        return view('reports.index', compact('rows', 'totals', 'from', 'to', 'batchCount', 'legacyReturned'));
    }
}
