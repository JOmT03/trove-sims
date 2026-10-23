<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Services\InventoryFifoService;

class ValuationController extends Controller
{
    public function index()
    {
        $rows = [];
        $grand = 0.0;

        foreach (Inventory::orderBy('item_name')->get() as $inv) {
            $lots      = InventoryFifoService::lots($inv->id);
            $remaining = round(array_sum(array_column($lots, 'remaining')), 2);
            $value     = round(array_sum(array_column($lots, 'value')), 2);
            $grand    += $value;

            $rows[] = [
                'inv'       => $inv,
                'lots'      => $lots,
                'remaining' => $remaining,
                'onHand'    => (float) $inv->quantity_on_hand,
                'avgCost'   => $remaining > 0 ? round($value / $remaining, 2) : 0.0,
                'value'     => $value,
            ];
        }

        return view('inventory.valuation', [
            'rows'  => $rows,
            'grand' => round($grand, 2),
        ]);
    }
}