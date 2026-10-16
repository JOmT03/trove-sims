<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'all'); // all | individual | institutional

        $query = Commission::with('items.product')->orderBy('created_at', 'desc');
        if (in_array($type, ['individual', 'institutional'])) {
            $query->where('client_type', $type);
        }
        $commissions = $query->get();

        $counts = [
            'all'           => Commission::count(),
            'individual'    => Commission::where('client_type', 'individual')->count(),
            'institutional' => Commission::where('client_type', 'institutional')->count(),
        ];

        return view('commissions.index', compact('commissions', 'type', 'counts'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('product_name')->get();
        $sites    = Site::orderBy('site_name')->get();
        return view('commissions.create', compact('products', 'sites'));
    }

    public function store(Request $request)
    {
        // drop blank product rows before validating (lets fully-custom orders through)
        $request->merge([
            'items' => array_values(array_filter((array) $request->input('items', []), function ($it) {
                return is_array($it) && !empty($it['product_id']);
            })),
        ]);

        $validated = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'client_type'        => 'nullable|string|max:50',
            'order_type'         => 'nullable|string|max:255',
            'needed_by_date'     => 'nullable|date',
            'deposit_amount'     => 'nullable|numeric|min:0',
            'custom_amount'      => 'nullable|numeric|min:0',
            'design_description' => 'nullable|string',
            'site_id'            => 'nullable|exists:sites,id',
            'items'              => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $items        = $validated['items'] ?? [];
        $customAmount = $validated['custom_amount'] ?? 0;

        if (count($items) === 0 && $customAmount <= 0) {
            return back()->withInput()->with('error', 'Add at least one product or enter a custom order amount.');
        }

        try {
            DB::beginTransaction();

            $total = 0;
            foreach ($items as $it) {
                $total += $it['quantity'] * $it['price'];
            }
            $total += $customAmount;   // custom cake with no catalog product

            $deposit = $validated['deposit_amount'] ?? 0;

            // single-branch: resolve a real site so the NOT NULL site_id is satisfied
            $defaultSiteId = $validated['site_id'] ?? (auth()->user()->site_id ?? Site::query()->value('id'));

            $commission = Commission::create([
                'user_id'            => auth()->id(),
                'site_id'            => $defaultSiteId,
                'customer_name'      => $validated['customer_name'],
                'client_type'        => $validated['client_type'] ?? 'individual',
                'order_type'         => $validated['order_type'] ?? null,
                'design_description' => $validated['design_description'] ?? null,
                'needed_by_date'     => $validated['needed_by_date'] ?? null,
                'total_amount'       => $total,
                'deposit_amount'     => $deposit,
                'status'             => $deposit > 0 ? 'Confirmed' : 'Inquiry',
            ]);

            foreach ($items as $it) {
                OrderItem::create([
                    'order_id'   => $commission->id,
                    'product_id' => $it['product_id'],
                    'quantity'   => $it['quantity'],
                    'price'      => $it['price'],
                ]);
            }

            DB::commit();

            return redirect()->route('commissions.show', $commission)
                ->with('success', 'Commission created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Commission create failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to save commission: ' . $e->getMessage());
        }
    }

    public function show(Commission $commission)
    {
        $commission->load(['items.product', 'site']);
        return view('commissions.show', compact('commission'));
    }

    public function updateStatus(Request $request, Commission $commission)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Commission::STAGES),
        ]);
        if ($commission->completed_at && \App\Models\PeriodClosure::isClosed($commission->completed_at)) {
            return back()->with('error', 'This commission was completed in a closed month and can no longer be changed.');
        }
        $commission->update([
            'status'       => $validated['status'],
            'completed_at' => $validated['status'] === 'Completed' ? ($commission->completed_at ?? now()) : null,
        ]);
        if ($validated['status'] === 'Completed') {
            \App\Models\FinancialAuditLog::record('order.completed', $commission, ['total' => (string) $commission->total_amount]);
        }

        return back()->with('success', 'Status updated to ' . $validated['status'] . '.');
    }
}