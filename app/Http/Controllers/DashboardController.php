<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\FinancialStatementService;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        //  Selected month (?month=YYYY-MM), never in the future
        $now   = Carbon::now();
        $month = $now->copy()->startOfMonth();
        if (preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month'))) {
            $pick = Carbon::createFromFormat('Y-m-d', $request->query('month') . '-01')->startOfDay();
            if ($pick->lte($month)) {
                $month = $pick;
            }
        }
        $monthEnd  = $month->copy()->endOfMonth();
        $prevMonth = $month->copy()->subMonthNoOverflow();
        $isCurrent = $month->equalTo($now->copy()->startOfMonth());

        //  Daily sales for the month, split by source
        $byDay = $this->salesByDay($month, $monthEnd);
        $daily = [];
        for ($d = $month->copy(); $d->lte($monthEnd); $d->addDay()) {
            $k = $d->toDateString();
            $daily[] = $d->gt($now) ? null : ($byDay[$k] ?? [0, 0, 0]);
        }
        $monthSales = array_sum(array_map(fn ($r) => $r ? array_sum($r) : 0, $daily));
        $prevSales  = array_sum(array_map('array_sum', $this->salesByDay($prevMonth, $prevMonth->copy()->endOfMonth())));

        //  Best sellers (pcs) and ingredient cost for the month
        $topProducts = $this->productsSold($month, $monthEnd);
        $soldPcs     = array_sum($topProducts);
        $topProducts = array_slice($topProducts, 0, 6, true);

        $ingredientCost = [];
        $usedIds = InventoryLog::whereIn('type', ['used', 'damaged'])
            ->whereBetween('created_at', [$month, $monthEnd])
            ->distinct()->pluck('inventory_id');
        foreach (Inventory::whereIn('id', $usedIds)->get() as $inv) {
            $cost = InventoryFifoService::cogs($inv->id, $month->toDateString(), $monthEnd->toDateString());
            if ($cost > 0) {
                $ingredientCost[$inv->item_name] = $cost;
            }
        }
        arsort($ingredientCost);
        $ingredientCost = array_slice($ingredientCost, 0, 6, true);

        //  Revenue vs expenses, last 6 months ending at the selected month
        $sixFrom = $month->copy()->subMonthsNoOverflow(5);
        $months  = [];
        for ($m = $sixFrom->copy(); $m->lte($month); $m->addMonthNoOverflow()) {
            $months[$m->format('Y-m')] = ['label' => $m->format('M'), 'full' => $m->format('F Y'), 'revenue' => 0.0, 'expenses' => 0.0];
        }
        foreach ($this->salesByDay($sixFrom, $monthEnd) as $day => $r) {
            $months[substr($day, 0, 7)]['revenue'] += array_sum($r);
        }
        $expenses = Expense::active()
            ->whereBetween('expense_date', [$sixFrom->toDateString(), $monthEnd->toDateString()])
            ->get(['expense_date', 'amount']);
        foreach ($expenses as $e) {
            $k = Carbon::parse($e->expense_date)->format('Y-m');
            if (isset($months[$k])) {
                $months[$k]['expenses'] += (float) $e->amount;
            }
        }

        $batchesThisMonth = Batch::whereNull('voided_at')
            ->whereBetween('batch_date', [$month->toDateString(), $monthEnd->toDateString()])
            ->count();

        //  Inventory (raw materials) 
        $inventories = Inventory::orderBy('item_name')->get();
        $lowCount    = $inventories->filter(fn ($i) => $i->isLowStock())->count();

        // lowest-stock first, show up to 6
        $stockLevels = $inventories
            ->sortBy(fn ($i) => $i->minimum_stock > 0 ? $i->usableQuantity() / $i->minimum_stock : 999)
            ->take(6)
            ->values();

        //  Finished products 
        $finishedStock = (int) Product::sum('stock_quantity');
        $totalProducts = Product::count();

        //  Latest batch (dashboard card) 
        $latestBatch = Batch::with(['items.product', 'sourceSite', 'destinationSite'])
            ->whereNull('voided_at')
            ->orderBy('batch_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        //  Can Still Bake (limiting-ingredient math from recipes) 
        $canBake = [];
        foreach (Product::with('materials')->where('status', 'active')->get() as $product) {
            if ($product->materials->isEmpty()) {
                continue;
            }
            $caps = [];
            foreach ($product->materials as $mat) {
                $need = (float) $mat->pivot->quantity_used;
                if ($need > 0) {
                    $caps[] = (int) floor($mat->quantity_on_hand / $need);
                }
            }
            if (! empty($caps)) {
                $canBake[$product->product_name] = min($caps);
            }
        }
        arsort($canBake);
        $canBake = array_slice($canBake, 0, 5, true);
        $maxBake = ! empty($canBake) ? max(array_values($canBake)) : 0;

        //  Recent stock movements (ingredient audit trail) 
        $movements = InventoryLog::with('inventory')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $chart = [
            'year' => $month->year, 'month' => $month->month - 1,
            'daily' => $daily,
            'top' => collect($topProducts)->map(fn ($v, $n) => ['n' => $n, 'v' => $v])->values(),
            'ing' => collect($ingredientCost)->map(fn ($v, $n) => ['n' => $n, 'v' => round($v, 2)])->values(),
            'months' => array_values($months),
        ];

        return view('dashboard', compact(
            'month', 'prevMonth', 'isCurrent', 'monthSales', 'prevSales', 'soldPcs', 'batchesThisMonth',
            'lowCount', 'stockLevels', 'finishedStock', 'totalProducts',
            'latestBatch', 'canBake', 'maxBake', 'movements', 'chart'
        ));
    }

    /**
     * Sales per day as [counter (POS), branch transfers, custom orders].
     * Branch and order revenue follow the same rules as FinancialStatementService.
     */
    private function salesByDay(Carbon $from, Carbon $to): array
    {
        $out = [];
        $add = function ($day, $slot, $amt) use (&$out) {
            $out[$day] ??= [0, 0, 0];
            $out[$day][$slot] += (float) $amt;
        };

        Sale::completed()->whereNull('voided_at')
            ->whereBetween('completed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get(['completed_at', 'total'])
            ->each(fn ($s) => $add($s->completed_at->toDateString(), 0, $s->total));

        $this->branchItems($from, $to)
            ->select('batches.batch_date', DB::raw('SUM((batch_items.qty_sent - batch_items.qty_returned) * batch_items.unit_price) as amt'))
            ->groupBy('batches.batch_date')->get()
            ->each(fn ($r) => $add(Carbon::parse($r->batch_date)->toDateString(), 1, $r->amt));

        Order::where('status', 'Completed')->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get(['completed_at', 'total_amount'])
            ->each(fn ($o) => $add($o->completed_at->toDateString(), 2, $o->total_amount));

        foreach ($out as &$r) {
            $r = array_map(fn ($v) => round($v, 2), $r);
        }
        ksort($out);
        return $out;
    }

    /** Batch items that count as branch sales (legacy reconciled before the cutoff, final sale after). */
    private function branchItems(Carbon $from, Carbon $to)
    {
        $cut = FinancialStatementService::BRANCH_FINAL_SALE_FROM;
        return BatchItem::query()
            ->join('batches', 'batches.id', '=', 'batch_items.batch_id')
            ->whereBetween('batches.batch_date', [$from->toDateString(), $to->toDateString()])
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('batches.status', 'reconciled')->where('batches.batch_date', '<', $cut))
                ->orWhere(fn ($q) => $q->whereNull('batches.voided_at')->where('batches.batch_date', '>=', $cut)));
    }

    /** Pieces sold per product across the counter, branch transfers and completed orders, most first. */
    private function productsSold(Carbon $from, Carbon $to): array
    {
        $pcs = [];
        $add = function ($name, $qty) use (&$pcs) {
            $name = $name ?: 'Other';
            $pcs[$name] = ($pcs[$name] ?? 0) + (int) $qty;
        };

        SaleItem::query()->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')->whereNull('sales.voided_at')
            ->whereBetween('sales.completed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->groupBy('sale_items.product_name')
            ->select('sale_items.product_name as n', DB::raw('SUM(sale_items.quantity) as q'))->get()
            ->each(fn ($r) => $add($r->n, $r->q));

        $this->branchItems($from, $to)
            ->leftJoin('products', 'products.id', '=', 'batch_items.product_id')
            ->groupBy('products.product_name')
            ->select('products.product_name as n', DB::raw('SUM(batch_items.qty_sent - batch_items.qty_returned) as q'))->get()
            ->each(fn ($r) => $add($r->n, $r->q));

        OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'Completed')
            ->whereBetween('orders.completed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->groupBy('products.product_name')
            ->select('products.product_name as n', DB::raw('SUM(order_items.quantity) as q'))->get()
            ->each(fn ($r) => $add($r->n, $r->q));

        $pcs = array_filter($pcs, fn ($q) => $q > 0);
        arsort($pcs);
        return $pcs;
    }
}
