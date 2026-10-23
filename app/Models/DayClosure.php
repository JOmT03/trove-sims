<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** A locked End-of-Day snapshot. Immutable once written. */
class DayClosure extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'business_date', 'gross_sales', 'cash_total', 'gcash_total', 'maya_total',
        'bank_total', 'expenses_total', 'net', 'tx_count', 'closed_by', 'closed_by_name', 'closed_at',
    ];

    protected $casts = ['business_date' => 'date', 'closed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('A closed day cannot be changed.'));
        static::deleting(fn () => throw new \LogicException('A closed day cannot be reopened or deleted.'));
    }

    public static function isClosed($date): bool
    {
        return $date && static::whereDate('business_date', Carbon::parse($date)->toDateString())->exists();
    }
}