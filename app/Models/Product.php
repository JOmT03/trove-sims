<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_name',
        'description',
        'price',
        'category',
        'status',
    ];

    protected $casts = [
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the materials/inventory items used in this product.
     * Many-to-many: A product uses many inventory items
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(
            Inventory::class,
            'product_materials',
            'product_id',
            'inventory_id'
        )->withPivot('quantity_used');
    }
}