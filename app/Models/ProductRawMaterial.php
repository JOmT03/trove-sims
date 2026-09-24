<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRawMaterial extends Model
{
    protected $fillable = ['product_id', 'raw_material_id', 'quantity_needed'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}