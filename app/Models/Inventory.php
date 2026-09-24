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

    public function usableQuantity()
    {
        return $this->quantity_on_hand - $this->quantity_damaged;
    }

    public function isLowStock()
    {
        return $this->usableQuantity() <= $this->minimum_stock;
    }
}