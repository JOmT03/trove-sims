<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'item_name',
        'category',
        'unit',
        'quantity_on_hand',
        'quantity_damaged',
        'minimum_stock',
        'site_id',
        'notes',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Stock that can still be used. Recording damage already takes the units out of
     * quantity_on_hand; quantity_damaged is only a running tally of what was written off,
     * so it must not be subtracted a second time.
     */
    public function usableQuantity()
    {
        return (float) $this->quantity_on_hand;
    }

    public function isLowStock()
    {
        return $this->usableQuantity() <= $this->minimum_stock;
    }
}
