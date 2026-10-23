<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One received batch of a raw material, with its own unit cost (FIFO). */
class InventoryLot extends Model
{
    protected $fillable = ['inventory_id', 'qty_received', 'qty_remaining', 'unit_cost', 'received_at', 'expense_id'];

    protected $casts = [
        'qty_received'  => 'decimal:2',
        'qty_remaining' => 'decimal:2',
        'unit_cost'     => 'decimal:2',
        'received_at'   => 'date',
    ];

    public function inventory() { return $this->belongsTo(Inventory::class); }

    /** Oldest lots that still have stock, first. */
    public function scopeFifo($q)
    {
        return $q->where('qty_remaining', '>', 0)->orderBy('received_at')->orderBy('id');
    }
}