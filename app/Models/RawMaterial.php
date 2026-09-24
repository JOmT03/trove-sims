<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $fillable = ['name', 'unit', 'stock_quantity', 'site_id'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_raw_materials')
                     ->withPivot('quantity_needed');
    }
}