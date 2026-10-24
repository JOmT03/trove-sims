<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\DayClosure;
use App\Models\Site;
use App\Models\FinancialAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * POS â€” counter sale of FINISHED products (walk-in).
 * Deducts finished stock (Product.stock_quantity) only. Raw materials are NOT
 * touched here: they were already consumed when the product was produced.
 * A sale is a ledger entry â€” it is voided (with a reason), never deleted.
 */
class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'active')
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'category', 'price', 'stock_quantity', 'image_path']);

        $recent = Sale::with('cashier')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('pos.index', compact('products', 'recent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'payments'             => 'required|array|min:1',
            'payments.*.method'    => 'required|in:' . implode(',', SalePayment::METHODS),
            'payments.*.amount'    => 'required|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:100',
        ]);

        // Digital payments must carry a reference number.
        foreach ($data['payments'] as $p) {
            if ($p['method'] !== 'cash' && blank($p['reference'] ?? null)) {
                return back()->withInput()
                    ->with('error', 'A reference number is required for ' . strtoupper($p['method']) . ' payments.');
            }
        }

        try {
            $sale = DB::transaction(function () use ($data) {
                // Lock the products we are about to sell so two cashiers cannot
                // oversell the same stock at the same time.
                $ids = collect($data['items'])->pluck('product_id')->all();
                $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

                $total = 0;
                $lines = [];
                foreach ($data['items'] as $row) {
                    $product = $products[$row['product_id']];
                    $qty = (int) $row['quantity'];
                    if ((float) $product->stock_quantity < $qty) {
                        throw new \RuntimeException(
                            "Not enough stock for {$product->product_name} (have {$product->stock_quantity}, need {$qty})."
                        );
                    }
                    $lineTotal = round((float) $product->price * $qty, 2);
                    $total += $lineTotal;
                    $lines[] = [$product, $qty, $lineTotal];
                }

                $paid = round(collect($data['payments'])->sum(fn ($p) => (float) $p['amount']), 2);
                if ($paid + 0.009 < $total) {
                    throw new \RuntimeException(
                        'Payments (' . number_format($paid, 2) . ') are less than the total (' . number_format($total, 2) . ').'
                    );
                }

                $siteId = auth()->user()->site_id ?? Site::query()->value('id');

                $sale = Sale::create([
                    'sale_number'  => 'S-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'site_id'      => $siteId,
                    'cashier_id'   => auth()->id(),
                    'total'        => $total,
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);

                foreach ($lines as [$product, $qty, $lineTotal]) {
                    $sale->items()->create([
                        'product_id'   => $product->id,
                        'product_name' => $product->product_name,
                        'quantity'     => $qty,
                        'unit_price'   => $product->price,
                        'line_total'   => $lineTotal,
                    ]);
                    $product->decrement('stock_quantity', $qty); // finished stock only
                }

                foreach ($data['payments'] as $p) {
                    if ((float) $p['amount'] <= 0) {
                        continue;
                    }
                    $sale->payments()->create([
                        'method'    => $p['method'],
                        'amount'    => round((float) $p['amount'], 2),
                        'reference' => $p['method'] === 'cash' ? null : ($p['reference'] ?? null),
                    ]);
                }

                FinancialAuditLog::record('sale.recorded', $sale, [
                    'total'    => (string) $sale->total,
                    'items'    => count($lines),
                    'payments' => collect($data['payments'])
                        ->map(fn ($p) => $p['method'] . ':' . $p['amount'])->implode(', '),
                ]);

                return $sale;
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.index')
            ->with('success', 'Sale ' . $sale->sale_number . ' recorded. Total: ' . number_format($sale->total, 2) . '.');
    }

    public function void(Request $request, Sale $sale)
    {
        $data = $request->validate(['reason' => 'required|string|max:255']);

        if ($sale->isVoided()) {
            return back()->with('error', 'That sale is already voided.');
        }
        if (DayClosure::isClosed($sale->completed_at)) {
            return back()->with('error', 'That sale falls on a day that is already closed. It can no longer be voided.');
        }

        DB::transaction(function () use ($sale, $data) {
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    $product?->increment('stock_quantity', $item->quantity); // put the stock back
                }
            }

            $sale->update([
                'status'      => 'voided',
                'voided_at'   => now(),
                'voided_by'   => auth()->id(),
                'void_reason' => $data['reason'],
            ]);

            FinancialAuditLog::record('sale.voided', $sale, [
                'reason' => $data['reason'],
                'total'  => (string) $sale->total,
            ]);
        });

        return back()->with('success', 'Sale ' . $sale->sale_number . ' voided and stock restored.');
    }
}