<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\BranchOrder;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchTransferController extends Controller
{
    public function index()
    {
        $batches = Batch::with(['items', 'sourceSite', 'destinationSite'])
            ->orderBy('batch_date', 'desc')
            ->get();

        $orders = BranchOrder::decorate(
            BranchOrder::with(['items.product', 'destinationSite'])
                ->orderBy('needed_by', 'desc')->limit(20)->get()
        );

        return $this->board($batches);
    }

    private function board($batches)
    {
        // Active tab: open orders + finished orders not archived. Archived tab: archived finished orders.
        $tab = request('tab') === 'archived' ? 'archived' : 'active';
        $pending = $tab === 'archived' ? collect() : BranchOrder::with(['items.product', 'destinationSite'])
            ->whereIn('status', ['requested', 'produced', 'partial'])
            ->orderBy('needed_by')->get();
        $done = BranchOrder::with(['items.product', 'destinationSite'])
            ->whereIn('status', ['complete', 'cancelled'])
            ->when($tab === 'archived', fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->orderBy('needed_by', 'desc')->get();
        $orders = BranchOrder::decorate($pending->concat($done));
        $orderIds = BranchOrder::pluck('id')->all();
        $loose = $batches->filter(fn ($b) => !$b->branch_order_id || !in_array($b->branch_order_id, $orderIds));
        $archivedCount = BranchOrder::whereNotNull('archived_at')->count() + $loose->filter(fn ($b) => !empty($b->archived_at))->count();
        $activeCount = BranchOrder::whereNull('archived_at')->count() + $loose->filter(fn ($b) => empty($b->archived_at))->count();

        // Note on the latest live batch of each order that is still short (or was closed short)
        $linked = $batches->filter(fn ($b) => $b->branch_order_id && empty($b->voided_at))
            ->sortByDesc(fn ($b) => $b->batch_date->format('Y-m-d') . str_pad((string) $b->id, 10, '0', STR_PAD_LEFT));
        $byOrder = BranchOrder::decorate(
            BranchOrder::with('items')->whereIn('id', $linked->pluck('branch_order_id')->unique()->all())->get()
        )->keyBy('id');

        $notes = [];
        $seen = [];
        foreach ($linked as $b) {
            $oid = $b->branch_order_id;
            if (isset($seen[$oid])) {
                continue;
            }
            $seen[$oid] = true;
            $o = $byOrder[$oid] ?? null;
            if (!$o) {
                continue;
            }
            if ($o->balance_qty > 0) {
                $notes[$b->id] = "Short {$o->balance_qty} (order #{$oid}): to be delivered in the next batch";
            } elseif ($o->short_reason) {
                $notes[$b->id] = 'Closed short ' . $o->items->sum('qty_closed') . " (order #{$oid}): " . $o->short_reason;
            }
        }

        $products = Product::where('status', 'active')->orderBy('product_name')->get();
        $sites = Site::orderBy('site_name')->get();

        return view('branch-transfers.index', compact('batches', 'orders', 'notes', 'products', 'sites', 'tab', 'orderIds', 'archivedCount', 'activeCount'));
    }

    public function create(Request $request)
    {
        $sites = Site::orderBy('site_name')->get();
        $products = Product::where('status', 'active')->orderBy('product_name')->get();

        $orders = BranchOrder::decorate(
            BranchOrder::with('items.product')
                ->whereIn('status', ['requested', 'produced', 'partial'])
                ->orderBy('needed_by')->get()
        )->filter(fn ($o) => $o->balance_qty > 0)->values();

        $selected = $request->query('order');

        return view('branch-transfers.create', compact('sites', 'products', 'orders', 'selected'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_site_id'      => 'nullable|exists:sites,id',
            'destination_site_id' => 'nullable|exists:sites,id',
            'branch_order_id'     => 'nullable|exists:branch_orders,id',
            'batch_date'          => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.qty_sent'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($item['qty_sent'] > $product->stock_quantity) {
                    throw new \Exception(
                        "Not enough {$product->product_name}: only {$product->stock_quantity} on hand, but you tried to send {$item['qty_sent']}."
                    );
                }
            }

            $batch = Batch::create([
                'source_site_id'      => $validated['source_site_id'] ?? null,
                'destination_site_id' => $validated['destination_site_id'] ?? null,
                'batch_date'          => $validated['batch_date'],
                'status'              => 'sent',
                'created_by'          => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                BatchItem::create([
                    'batch_id'     => $batch->id,
                    'product_id'   => $item['product_id'],
                    'qty_sent'     => $item['qty_sent'],
                    'qty_returned' => 0,
                    'unit_price'   => $item['unit_price'],
                ]);

                Product::where('id', $item['product_id'])->decrement('stock_quantity', $item['qty_sent']);
            }

            if (!empty($validated['branch_order_id'])) {
                DB::table('batches')->where('id', $batch->id)->update(['branch_order_id' => $validated['branch_order_id']]);
                $order = BranchOrder::find($validated['branch_order_id']);
                if ($order) {
                    $order->refreshStatus();
                }
            }

            $this->audit('branch_transfer.sent', $batch, [
                'batch_date' => $validated['batch_date'],
                'items'      => count($validated['items']),
                'order_id'   => $validated['branch_order_id'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('branch-transfers.show', $batch)
                ->with('success', 'Batch sent. This is a final sale and was counted as revenue.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Batch send failed: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Batch $batch)
    {
        $batch->load(['items.product', 'sourceSite', 'destinationSite', 'creator']);
        $voidInfo = DB::table('batches')->where('id', $batch->id)->first(['voided_at', 'void_reason', 'branch_order_id']);
        return view('branch-transfers.show', compact('batch', 'voidInfo'));
    }

    /** Undo a mistaken send: restore stock, exclude from revenue. Reason required. */
    public function void(Request $request, Batch $batch)
    {
        $request->validate(['reason' => 'required|string|min:3|max:500']);

        $row = DB::table('batches')->where('id', $batch->id)->first();
        if ($row->voided_at) {
            return back()->with('error', 'This batch is already voided.');
        }
        if (\App\Models\PeriodClosure::isClosed($batch->batch_date)) {
            return back()->with('error', 'This batch belongs to a closed month and cannot be voided.');
        }

        try {
            DB::beginTransaction();

            foreach ($batch->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock_quantity', $item->qty_sent);
                }
            }

            DB::table('batches')->where('id', $batch->id)->update([
                'voided_at'   => now(),
                'void_reason' => $request->reason,
                'voided_by'   => auth()->id(),
            ]);

            if ($row->branch_order_id) {
                $order = BranchOrder::find($row->branch_order_id);
                if ($order) {
                    $order->refreshStatus();
                }
            }

            $this->audit('branch_transfer.voided', $batch, ['reason' => $request->reason]);

            DB::commit();

            return redirect()->route('branch-transfers.show', $batch)
                ->with('success', 'Batch voided. Stock restored and revenue removed.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Batch void failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to void: ' . $e->getMessage());
        }
    }

    /** Archive one finished order (with its deliveries) or one delivery that has no order. */
    public function archive(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:order,batch', 'id' => 'required|integer']);

        if ($data['type'] === 'order') {
            $order = BranchOrder::findOrFail($data['id']);
            if (!in_array($order->status, ['complete', 'cancelled'], true)) {
                return back()->with('error', 'Send or close order #' . $order->id . ' before archiving it.');
            }
            DB::table('branch_orders')->where('id', $order->id)->update(['archived_at' => now()]);
            $when = $order->needed_by;
            $what = 'Order #' . $order->id;
        } else {
            $batch = Batch::findOrFail($data['id']);
            if ($batch->branch_order_id && BranchOrder::whereKey($batch->branch_order_id)->exists()) {
                return back()->with('error', 'This delivery belongs to order #' . $batch->branch_order_id . '. Archive the order instead.');
            }
            DB::table('batches')->where('id', $batch->id)->update(['archived_at' => now()]);
            $when = $batch->batch_date;
            $what = 'Delivery of ' . $batch->batch_date->format('M d, Y');
        }

        return redirect()->route('branch-transfers.index')
            ->with('success', $what . ' archived under ' . $when->format('F Y') . '.');
    }

    public function restore(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:order,batch', 'id' => 'required|integer']);
        $table = $data['type'] === 'order' ? 'branch_orders' : 'batches';
        DB::table($table)->where('id', $data['id'])->update(['archived_at' => null]);

        return redirect()->route('branch-transfers.index', ['tab' => 'archived'])
            ->with('success', 'Restored to Active.');
    }

    /** Archive every finished order and order-less delivery dated in one month or one week (Mon-Sun). */
    public function archivePeriod(Request $request)
    {
        $data = $request->validate([
            'mode' => 'required|in:month,week',
            'key'  => ['required', 'string', 'regex:/^\d{4}-\d{2}(-\d{2})?$/'],
        ]);
        [$from, $to, $label] = $this->period($data['mode'], $data['key']);

        $orders = DB::table('branch_orders')->whereIn('status', ['complete', 'cancelled'])->whereNull('archived_at')
            ->where('needed_by', '>=', $from)->where('needed_by', '<', $to)->update(['archived_at' => now()]);
        $batches = DB::table('batches')->whereNull('archived_at')
            ->where(fn ($q) => $q->whereNull('branch_order_id')->orWhereNotIn('branch_order_id', DB::table('branch_orders')->select('id')))
            ->where('batch_date', '>=', $from)->where('batch_date', '<', $to)->update(['archived_at' => now()]);

        $n = $orders + $batches;
        return redirect()->route('branch-transfers.index')
            ->with($n ? 'success' : 'error', $n ? "{$n} item(s) from {$label} archived." : "Nothing to archive in {$label}.");
    }

    public function restoreMonth(Request $request)
    {
        $data = $request->validate(['key' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/']]);
        [$from, $to, $label] = $this->period('month', $data['key']);

        DB::table('branch_orders')->whereNotNull('archived_at')->where('needed_by', '>=', $from)->where('needed_by', '<', $to)->update(['archived_at' => null]);
        DB::table('batches')->whereNotNull('archived_at')->where('batch_date', '>=', $from)->where('batch_date', '<', $to)->update(['archived_at' => null]);

        return redirect()->route('branch-transfers.index', ['tab' => 'archived'])
            ->with('success', $label . ' restored to Active.');
    }

    /** [first day, day after the last day, label] for a month key (Y-m) or a week key (any date in that week). */
    private function period(string $mode, string $key): array
    {
        if ($mode === 'month') {
            $start = \Carbon\Carbon::createFromFormat('Y-m-d', substr($key, 0, 7) . '-01')->startOfDay();
            return [$start->toDateString(), $start->copy()->addMonth()->toDateString(), $start->format('F Y')];
        }
        $start = \Carbon\Carbon::parse($key)->startOfWeek(\Carbon\Carbon::MONDAY);
        $end = $start->copy()->addDays(6);
        return [$start->toDateString(), $end->copy()->addDay()->toDateString(), 'the week of ' . $start->format('M d') . ' - ' . $end->format('M d, Y')];
    }

    private function audit(string $action, $model, array $data): void
    {
        try {
            if (class_exists(\App\Models\FinancialAuditLog::class)) {
                \App\Models\FinancialAuditLog::record($action, $model, $data);
            }
        } catch (\Throwable $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }
}
