<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchOrderItem extends Model
{
    protected $fillable = ['branch_order_id', 'product_id', 'qty', 'qty_closed'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}