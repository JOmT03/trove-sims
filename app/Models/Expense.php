<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Expenses are a ledger: once recorded they cannot be edited or deleted.
 * A wrong entry is VOIDED (kept, struck-through, excluded from totals, reason + who logged).
 */
class Expense extends Model
{
    public const COGS_CATEGORIES = ['ingredients', 'packaging'];

    protected $fillable = [
        'expense_date', 'category', 'description', 'amount',
        'inventory_id', 'quantity', 'created_by', 'receipt_path',
        'voided_at', 'voided_by', 'void_reason',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
        'voided_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new \LogicException('Expenses cannot be deleted. Void the entry instead.'));

        static::updating(function (Expense $e) {
            $allowed = ['voided_at', 'voided_by', 'void_reason', 'updated_at'];
            $illegal = array_diff(array_keys($e->getDirty()), $allowed);
            if ($illegal || $e->getOriginal('voided_at') !== null) {
                throw new \LogicException('Expenses cannot be edited. Void the entry and record a new one.');
            }
        });
    }

    public function scopeActive($q)
    {
        return $q->whereNull('voided_at');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function isCogs(): bool
    {
        return in_array(strtolower($this->category), self::COGS_CATEGORIES, true);
    }

    public function inventoryItem() { return $this->belongsTo(Inventory::class, 'inventory_id'); }
    public function creator()       { return $this->belongsTo(User::class, 'created_by'); }
    public function voider()        { return $this->belongsTo(User::class, 'voided_by'); }
}
