<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'product_name',
        'description',
        'category',
        'price',
        'stock_quantity',
        'status',
        'site_id',
        'image_path',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // Materials (inventory items) used in this product - the recipe
    public function materials()
    {
        return $this->belongsToMany(Inventory::class, 'product_materials')
                    ->withPivot('quantity_used')
                    ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}