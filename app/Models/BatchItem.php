<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchItem extends Model
{
    protected $fillable = [
        'batch_id', 'product_id', 'qty_sent', 'qty_returned', 'unit_price',
    ];

    public function batch()   { return $this->belongsTo(Batch::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function netSold() { return $this->qty_sent - $this->qty_returned; }
    public function revenue() { return $this->netSold() * $this->unit_price; }
}