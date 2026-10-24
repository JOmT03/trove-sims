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
        $pending = BranchOrder::with(['items.product', 'destinationSite'])
            ->whereIn('status', ['requested', 'produced', 'partial'])
            ->orderBy('needed_by')->get();
        $done = BranchOrder::with(['items.product', 'destinationSite'])
            ->whereIn('status', ['complete', 'cancelled'])
            ->orderBy('needed_by', 'desc')->limit(10)->get();
        $orders = BranchOrder::decorate($pending->concat($done));

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

        return view('branch-transfers.index', compact('batches', 'orders', 'notes', 'products', 'sites'));
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