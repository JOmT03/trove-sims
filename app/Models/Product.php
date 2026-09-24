<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['product_name', 'category', 'price', 'stock_quantity', 'site_id'];

    public function site() { return $this->belongsTo(Site::class); }

    public function recipe()
    {
        return $this->belongsToMany(Inventory::class, 'product_raw_materials', 'product_id', 'inventory_id')
                     ->withPivot('quantity_needed');
    }

    public function orderItems() { return $this->hasMany(OrderItem::class); }
}