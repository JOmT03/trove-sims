<?php

namespace App\Http\Controllers;

use App\Models\BranchOrder;
use App\Models\BranchOrderItem;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchOrderController extends Controller
{
    public function index()
    {
        $open = BranchOrder::decorate(
            BranchOrder::with(['items.product', 'destinationSite'])
                ->whereIn('status', ['requested', 'produced', 'partial'])
                ->orderBy('needed_by')->get()
        );

        $recent = BranchOrder::decorate(
            BranchOrder::with(['items.product', 'destinationSite'])
                ->whereIn('status', ['complete', 'cancelled'])
                ->orderBy('updated_at', 'desc')->limit(15)->get()
        );

        $products = Product::where('status', 'active')->orderBy('product_name')->get();
        $sites = Site::orderBy('site_name')->get();

        return redirect()->route('branch-transfers.index');
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'destination_site_id' => 'nullable|exists:sites,id',
            'needed_by'           => 'required|date',
            'notes'               => 'nullable|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.qty'         => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($v) {
            $order = BranchOrder::create([
                'destination_site_id' => $v['destination_site_id'] ?? null,
                'needed_by'           => $v['needed_by'],
                'notes'               => $v['notes'] ?? null,
                'status'              => 'requested',
                'created_by'          => auth()->id(),
            ]);
            foreach ($v['items'] as $it) {
                $order->items()->create(['product_id' => $it['product_id'], 'qty' => $it['qty']]);
            }
            $this->audit('branch_order.requested', $order, ['needed_by' => $v['needed_by'], 'items' => count($v['items'])]);
        });

        return redirect()->route('branch-transfers.index')->with('success', 'Order recorded.');
    }

    public function produce(BranchOrder $order)
    {
        if ($order->status === 'requested') {
            $order->update(['status' => 'produced']);
            $this->audit('branch_order.produced', $order, []);
        }
        return back()->with('success', 'Order marked as produced.');
    }

    public function cancel(BranchOrder $order)
    {
        if (in_array($order->status, ['requested', 'produced'], true)) {
            $order->update(['status' => 'cancelled']);
            $this->audit('branch_order.cancelled', $order, []);
            return back()->with('success', 'Order cancelled.');
        }
        return back()->with('error', 'Only orders with nothing sent yet can be cancelled. Use Close short instead.');
    }

    /** Stop waiting for the remaining balance. The balance is not sold and earns no revenue. */
    public function closeShort(Request $request, BranchOrder $order)
    {
        $v = $request->validate(['reason' => 'required|string|min:3|max:500']);

        if (!in_array($order->status, ['requested', 'produced', 'partial'], true)) {
            return back()->with('error', 'This order is already closed.');
        }

        $fresh = BranchOrder::with('items')->find($order->id);
        BranchOrder::decorate(collect([$fresh]));
        $short = $fresh->balance_qty;

        DB::transaction(function () use ($fresh, $v) {
            foreach ($fresh->items as $it) {
                if ($it->balance > 0) {
                    BranchOrderItem::whereKey($it->id)->update(['qty_closed' => (int) $it->qty_closed + $it->balance]);
                }
            }
            BranchOrder::whereKey($fresh->id)->update(['status' => 'complete', 'short_reason' => $v['reason']]);
        });

        $this->audit('branch_order.closed_short', $order, ['short_qty' => $short, 'reason' => $v['reason']]);

        return back()->with('success', "Order closed. {$short} pcs not delivered.");
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