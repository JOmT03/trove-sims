<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A POS counter sale. A ledger entry: voided, never deleted. */
class Sale extends Model
{
    protected $fillable = [
        'sale_number', 'site_id', 'cashier_id', 'total',
        'status', 'completed_at', 'voided_at', 'voided_by', 'void_reason',
    ];

    protected $casts = [
        'total'        => 'decimal:2',
        'completed_at' => 'datetime',
        'voided_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \LogicException('Sales cannot be deleted. Void the sale instead.'));
    }

    public function items()    { return $this->hasMany(SaleItem::class); }
    public function payments() { return $this->hasMany(SalePayment::class); }
    public function cashier()  { return $this->belongsTo(User::class, 'cashier_id'); }
    public function site()     { return $this->belongsTo(Site::class); }

    public function isVoided(): bool { return $this->voided_at !== null; }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
}