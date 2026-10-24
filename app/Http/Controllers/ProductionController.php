<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductionAudit;
use App\Models\RecipeVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductionController extends Controller
{
    /**
     * Yield preview: for every product with an active recipe, how many units
     * the CURRENT raw-material stock can produce (and which ingredient limits it).
     */
    private function yieldRows(): array
    {
        $versions = RecipeVersion::where('is_active', true)
            ->where('is_archived', false)
            ->get()
            ->keyBy('product_id');

        if ($versions->isEmpty()) {
            return [];
        }

        $products = Product::whereIn('id', $versions->keys()->all())->orderBy('product_name')->get();

        $invIds = [];
        foreach ($versions as $v) {
            foreach (($v->items ?? []) as $it) {
                if (!empty($it['inventory_id'])) {
                    $invIds[] = (int) $it['inventory_id'];
                }
            }
        }
        $inv = Inventory::whereIn('id', array_unique($invIds))->get()->keyBy('id');

        $rows = [];
        foreach ($products as $p) {
            $v = $versions[$p->id];
            $lines = [];
            $max = null;
            $limit = null;

            foreach (($v->items ?? []) as $it) {
                $i   = $inv[(int) ($it['inventory_id'] ?? 0)] ?? null;
                $per = (float) ($it['quantity_used'] ?? 0);
                if (!$i || $per <= 0) {
                    continue;
                }
                $on  = (float) $i->quantity_on_hand;
                $can = max(0, (int) floor(round($on / $per, 6)));

                $lines[] = [
                    'name' => $i->item_name,
                    'unit' => $i->unit,
                    'on'   => $on,
                    'per'  => $per,
                    'can'  => $can,
                ];

                if ($max === null || $can < $max) {
                    $max   = $can;
                    $limit = $i->item_name;
                }
            }

            if (empty($lines)) {
                continue;
            }

            $rows[$p->id] = [
                'id'     => $p->id,
                'name'   => $p->product_name,
                'recipe' => $v->name,
                'stock'  => (int) $p->stock_quantity,
                'max'    => (int) $max,
                'limit'  => $limit,
                'items'  => $lines,
            ];
        }

        return $rows;
    }

    /** Inventory > Production tab (yield preview + record form + audit log). */
    public function create(Request $request)
    {
        $yield   = $this->yieldRows();
        $isAdmin = Gate::allows('admin');

        $audits = null;
        $stats  = ['runs' => 0, 'flagged' => 0, 'missing' => 0];

        if ($isAdmin) {
            $since = now()->subDays(30);

            $audits = ProductionAudit::with('user')
                ->when($request->get('flag') === 'variance', fn ($q) => $q->where('variance_qty', '!=', 0))
                ->orderByDesc('id')
                ->simplePaginate(15)
                ->withQueryString();

            $stats = [
                'runs'    => ProductionAudit::where('created_at', '>=', $since)->count(),
                'flagged' => ProductionAudit::where('created_at', '>=', $since)->where('variance_qty', '!=', 0)->count(),
                'missing' => (int) ProductionAudit::where('created_at', '>=', $since)->where('variance_qty', '>', 0)->sum('variance_qty'),
            ];
        }

        $flag = $request->get('flag') === 'variance' ? 'variance' : 'all';

        return view('inventory.production', compact('yield', 'isAdmin', 'audits', 'stats', 'flag'));
    }

    /** Record a production run. Expected yield is computed by the server (recipe + current stock). */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('admin'), 403);

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'actual_qty' => 'required|integer|min:0',
            'notes'      => 'nullable|string|max:500',
        ]);

        $actual = (int) $data['actual_qty'];
        $notes  = trim((string) ($data['notes'] ?? ''));

        try {
            $audit = DB::transaction(function () use ($data, $actual, $notes) {
                $product = Product::lockForUpdate()->findOrFail($data['product_id']);

                $version = RecipeVersion::where('product_id', $product->id)
                    ->where('is_active', true)
                    ->where('is_archived', false)
                    ->first();

                if (!$version || empty($version->items)) {
                    throw new \RuntimeException('This product has no active recipe.');
                }

                // Server computes the expected yield from CURRENT stock + recipe
                $lines    = [];
                $expected = null;
                foreach ($version->items as $it) {
                    $inv = Inventory::lockForUpdate()->find((int) ($it['inventory_id'] ?? 0));
                    $per = (float) ($it['quantity_used'] ?? 0);
                    if (!$inv || $per <= 0) {
                        continue;
                    }
                    $can      = max(0, (int) floor(round(((float) $inv->quantity_on_hand) / $per, 6)));
                    $expected = $expected === null ? $can : min($expected, $can);
                    $lines[]  = [$inv, $per];
                }

                if (empty($lines) || $expected < 1) {
                    throw new \RuntimeException('Current stock cannot produce even 1 unit of this product.');
                }
                if ($actual !== $expected && $notes === '') {
                    throw new \RuntimeException("Inventory says {$expected} should be produced, but you entered {$actual}. Please write the reason for the difference.");
                }

                // Materials issued for the full expected batch
                $snapshot = [];
                foreach ($lines as [$inv, $per]) {
                    $need   = round($per * $expected, 2);
                    $before = (float) $inv->quantity_on_hand;
                    $after  = round($before - $need, 2);

                    $inv->update(['quantity_on_hand' => $after]);

                    InventoryLog::create([
                        'inventory_id' => $inv->id,
                        'type'         => 'used',
                        'quantity'     => $need,
                        'reference'    => 'PRODUCTION-AUDIT',
                        'notes'        => "Issued for {$expected} x {$product->product_name} (actual produced: {$actual})",
                        'user_id'      => auth()->id(),
                    ]);

                    $snapshot[] = [
                        'inventory_id' => $inv->id,
                        'name'         => $inv->item_name,
                        'unit'         => $inv->unit,
                        'before'       => $before,
                        'used'         => $need,
                        'after'        => $after,
                    ];
                }

                $product->update(['stock_quantity' => (int) $product->stock_quantity + $actual]);

                $audit = ProductionAudit::create([
                    'product_id'        => $product->id,
                    'product_name'      => $product->product_name,
                    'recipe_version_id' => $version->id,
                    'recipe_name'       => $version->name,
                    'expected_qty'      => $expected,
                    'actual_qty'        => $actual,
                    'variance_qty'      => $expected - $actual,
                    'materials'         => $snapshot,
                    'notes'             => $notes !== '' ? $notes : null,
                    'user_id'           => auth()->id(),
                ]);

                \App\Models\FinancialAuditLog::record('production.recorded', $audit, [
                    'product'  => $product->product_name,
                    'expected' => $expected,
                    'actual'   => $actual,
                    'variance' => $expected - $actual,
                ]);

                return $audit;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Could not record production. Nothing was changed.');
        }

        $name = $audit->product_name;
        $exp  = $audit->expected_qty;
        $act  = $audit->actual_qty;

        if ($audit->variance_qty > 0) {
            return redirect()->route('production.create')->with('error',
                "VARIANCE: inventory should have produced {$exp} {$name}, but only {$act} were made ({$audit->variance_qty} short).");
        }
        if ($audit->variance_qty < 0) {
            return redirect()->route('production.create')->with('error',
                "VARIANCE: {$act} {$name} produced, " . abs($audit->variance_qty) . " MORE than the {$exp} expected.");
        }

        return redirect()->route('production.create')->with('success',
            "Recorded: {$act} {$name} produced, matches the expected {$exp}.");
    }
    /** Old "Production Audit" page now lives inside the Inventory > Production tab. */
    public function audit()
    {
        return redirect(route('production.create') . '#audit');
    }
}
