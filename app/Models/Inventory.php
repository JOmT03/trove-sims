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
        'supplier_id',
        'notes',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function usableQuantity()
    {
        return $this->quantity_on_hand - $this->quantity_damaged;
    }

    public function isLowStock()
    {
        return $this->usableQuantity() <= $this->minimum_stock;
    }
}