<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Production Capacity. Reads each product's recipe (product_raw_materials:
 * quantity_needed per unit) against current raw-material stock, and shows how
 * many units of each product can still be produced right now, plus the
 * ingredient that runs out first (the limiting ingredient). This lets the user
 * see producible output straight from inventory, instead of recording a batch
 * first and only then learning what stock allows.
 */
class ProductionCapacityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $products = Product::with('materials')
            ->when($search !== '', fn ($q) => $q->where('product_name', 'like', '%' . $search . '%'))
            ->orderBy('product_name')
            ->get();

        $rows = [];
        foreach ($products as $p) {
            if (strtolower((string) $p->status) === 'archived') {
                continue;
            }

            $materials = $p->materials;
            if ($materials->isEmpty()) {
                $rows[] = ['product' => $p, 'producible' => null, 'limiting' => null, 'recipe' => []];
                continue;
            }

            $minUnits = null;
            $limiting = null;
            $recipe   = [];

            foreach ($materials as $m) {
                $needed    = (float) $m->pivot->quantity_needed;
                $available = (float) $m->quantity_on_hand - (float) $m->quantity_damaged;
                if ($available < 0) {
                    $available = 0.0;
                }
                $canMake = $needed > 0 ? (int) floor($available / $needed) : null;

                $recipe[] = [
                    'name'      => $m->item_name,
                    'unit'      => $m->unit,
                    'needed'    => $needed,
                    'available' => $available,
                    'canMake'   => $canMake,
                ];

                if ($canMake !== null && ($minUnits === null || $canMake < $minUnits)) {
                    $minUnits = $canMake;
                    $limiting = $m->item_name;
                }
            }

            $rows[] = [
                'product'    => $p,
                'producible' => $minUnits,
                'limiting'   => $limiting,
                'recipe'     => $recipe,
            ];
        }

        return view('inventory.capacity', compact('rows', 'search'));
    }
}