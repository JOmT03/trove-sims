<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeVersion extends Model
{
    protected $table = 'recipe_versions';

    protected $fillable = ['product_id', 'name', 'items', 'is_active', 'is_archived'];

    protected $casts = [
        'items'       => 'array',
        'is_active'   => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}