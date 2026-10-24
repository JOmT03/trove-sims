<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An audited production run. Each run records, per ingredient, the stock
 * BEFORE, the recipe-based EXPECTED usage, the ACTUAL remaining (counted after
 * production), and the resulting VARIANCE. A non-zero variance means more (or
 * less) was consumed than the recipe allows - the signal used to deter and
 * detect theft or waste.
 */
class ProductionRun extends Model
{
    protected $fillable = [
        'product_id', 'product_name', 'quantity', 'items',
        'has_variance', 'produced_by', 'produced_by_name', 'note',
    ];

    protected $casts = [
        'items'        => 'array',
        'has_variance' => 'boolean',
    ];

    public function producer()
    {
        return $this->belongsTo(User::class, 'produced_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}