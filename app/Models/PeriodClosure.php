<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** A closed (locked) accounting month. Immutable once written. */
class PeriodClosure extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'period', 'revenue', 'cogs', 'opex', 'net_income',
        'closed_by', 'closed_by_name', 'closed_at', 'prev_hash', 'hash',
    ];

    protected $casts = [
        'period'    => 'date:Y-m-d',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('A closed period cannot be changed.'));
        static::deleting(fn () => throw new \LogicException('A closed period cannot be reopened or deleted.'));
    }

    public static function isClosed($date): bool
    {
        if (! $date) {
            return false;
        }
        return static::whereDate('period', Carbon::parse($date)->startOfMonth()->toDateString())->exists();
    }

    public static function computeHash(?string $prev, string $period, $revenue, $cogs, $opex, $net, string $closedAt, ?string $closedBy): string
    {
        $f = fn ($n) => number_format((float) $n, 2, '.', '');

        return hash('sha256', implode('|', [
            $prev ?? 'GENESIS', $period, $f($revenue), $f($cogs), $f($opex), $f($net), $closedAt, $closedBy ?? '',
        ]));
    }

    /** Recompute this row's hash from its stored fields. */
    public function hashIsValid(): bool
    {
        return hash_equals($this->hash, static::computeHash(
            $this->prev_hash,
            $this->period->toDateString(),
            $this->revenue, $this->cogs, $this->opex, $this->net_income,
            $this->closed_at->format('Y-m-d H:i:s'),
            $this->closed_by_name
        ));
    }
}
