<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Site;
use App\Models\FinancialAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        // drop blank add-on rows before validating (lets fully-custom orders through)
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
            'reference_photo'    => 'nullable|image|max:4096',
            'items'              => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $items        = $validated['items'] ?? [];
        $customAmount = $validated['custom_amount'] ?? 0;

        if (count($items) === 0 && $customAmount <= 0) {
            return back()->withInput()->with('error', 'Add at least one add-on or enter a custom cake price.');
        }

        try {
            $commission = DB::transaction(function () use ($request, $validated, $items, $customAmount) {
                $total = (float) $customAmount;
                foreach ($items as $it) {
                    $total += $it['quantity'] * $it['price'];
                }

                $deposit = $validated['deposit_amount'] ?? 0;
                $status  = $deposit > 0 ? 'Confirmed' : 'Inquiry';
                $defaultSiteId = $validated['site_id'] ?? (auth()->user()->site_id ?? Site::query()->value('id'));

                $photoPath = null;
                if ($request->hasFile('reference_photo')) {
                    $photoPath = $request->file('reference_photo')->store('commission-refs', 'local');
                }

                $commission = Commission::create([
                    'user_id'              => auth()->id(),
                    'site_id'              => $defaultSiteId,
                    'customer_name'        => $validated['customer_name'],
                    'client_type'          => $validated['client_type'] ?? 'individual',
                    'order_type'           => $validated['order_type'] ?? 'Customized',
                    'design_description'   => $validated['design_description'] ?? null,
                    'reference_photo_path' => $photoPath,
                    'needed_by_date'       => $validated['needed_by_date'] ?? null,
                    'total_amount'         => $total,
                    'deposit_amount'       => $deposit,
                    'status'               => $status,
                ]);

                // Add-ons are finished catalog products: record them, and (once the
                // order is Confirmed) deduct finished stock, same as a POS sale.
                foreach ($items as $it) {
                    $product = Product::where('id', $it['product_id'])->lockForUpdate()->first();

                    if ($status === 'Confirmed' && (float) $product->stock_quantity < (int) $it['quantity']) {
                        throw new \RuntimeException(
                            "Not enough stock for {$product->product_name} (have {$product->stock_quantity}, need {$it['quantity']})."
                        );
                    }

                    OrderItem::create([
                        'order_id'   => $commission->id,
                        'product_id' => $it['product_id'],
                        'quantity'   => $it['quantity'],
                        'price'      => $it['price'],
                    ]);

                    if ($status === 'Confirmed') {
                        $product->decrement('stock_quantity', (int) $it['quantity']);
                    }
                }

                FinancialAuditLog::record('commission.created', $commission, [
                    'customer' => $commission->customer_name,
                    'total'    => (string) $commission->total_amount,
                    'addons'   => count($items),
                    'status'   => $status,
                ]);

                return $commission;
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Failed to save order: ' . $e->getMessage());
        }

        return redirect()->route('commissions.show', $commission)
            ->with('success', 'Order created successfully.');
    }

    public function show(Commission $commission)
    {
        $commission->load(['items.product', 'site']);
        return view('commissions.show', compact('commission'));
    }

    public function receipt(Commission $commission)
    {
        $commission->load(['items.product', 'site']);
        return view('commissions.receipt', [
            'c'           => $commission,
            'shopName'    => \App\Models\Setting::get('shop_name', 'Trove - Food & Cake Shop'),
            'shopAddress' => \App\Models\Setting::get('shop_address', ''),
            'shopContact' => \App\Models\Setting::get('shop_contact', ''),
            'terms'       => \App\Models\Setting::get('order_terms', \App\Http\Controllers\ShopSettingsController::DEFAULT_TERMS),
        ]);
    }
    public function photo(Commission $commission)
    {
        abort_unless(
            $commission->reference_photo_path && Storage::disk('local')->exists($commission->reference_photo_path),
            404
        );
        return Storage::disk('local')->response($commission->reference_photo_path);
    }

    public function updateStatus(Request $request, Commission $commission)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Commission::STAGES),
        ]);
        if ($commission->completed_at && \App\Models\PeriodClosure::isClosed($commission->completed_at)) {
            return back()->with('error', 'This order was completed in a closed month and can no longer be changed.');
        }
        $commission->update([
            'status'       => $validated['status'],
            'completed_at' => $validated['status'] === 'Completed' ? ($commission->completed_at ?? now()) : null,
        ]);
        if ($validated['status'] === 'Completed') {
            FinancialAuditLog::record('order.completed', $commission, ['total' => (string) $commission->total_amount]);
        }

        return back()->with('success', 'Status updated to ' . $validated['status'] . '.');
    }
}