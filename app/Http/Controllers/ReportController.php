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

        $batches = Batch::with('items.product')
            ->whereBetween('batch_date', [$from, $to])
            ->orderBy('batch_date')
            ->get();

        // Aggregate per flavor across all batches in the range
        $rows = [];
        foreach ($batches as $b) {
            foreach ($b->items as $it) {
                $name = $it->product->product_name ?? 'Unknown';
                if (! isset($rows[$name])) {
                    $rows[$name] = ['sent' => 0, 'returned' => 0, 'net' => 0, 'revenue' => 0];
                }
                $rows[$name]['sent']     += $it->qty_sent;
                $rows[$name]['returned'] += $it->qty_returned;
                $rows[$name]['net']      += $it->netSold();
                $rows[$name]['revenue']  += $it->revenue();
            }
        }
        ksort($rows);

        $totals = [
            'sent'     => array_sum(array_column($rows, 'sent')),
            'returned' => array_sum(array_column($rows, 'returned')),
            'net'      => array_sum(array_column($rows, 'net')),
            'revenue'  => array_sum(array_column($rows, 'revenue')),
        ];

        $dispatched = $batches->sum(fn ($b) => $b->dispatchedValue());
        $loss       = $batches->sum(fn ($b) => $b->lossValue());
        $batchCount = $batches->count();

        return view('reports.index', compact('rows', 'totals', 'from', 'to', 'dispatched', 'loss', 'batchCount'));
    }
}