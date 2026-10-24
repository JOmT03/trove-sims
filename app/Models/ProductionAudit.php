<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionAudit extends Model
{
    protected $table = 'production_yield_audits';

    protected $fillable = [
        'product_id',
        'product_name',
        'recipe_version_id',
        'recipe_name',
        'expected_qty',
        'actual_qty',
        'variance_qty',
        'materials',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'materials' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
