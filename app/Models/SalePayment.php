<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    public const METHODS = ['cash', 'gcash', 'maya', 'bank'];

    protected $fillable = ['sale_id', 'method', 'amount', 'reference'];
    protected $casts = ['amount' => 'decimal:2'];

    public function sale() { return $this->belongsTo(Sale::class); }

    public function isDigital(): bool { return $this->method !== 'cash'; }
}