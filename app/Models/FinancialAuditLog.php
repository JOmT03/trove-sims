<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Append-only: rows can be created, never changed or removed. */
class FinancialAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'action',
        'entity_type', 'entity_id', 'details', 'ip_address',
    ];

    protected $casts = ['created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Audit log entries cannot be changed.'));
        static::deleting(fn () => throw new \LogicException('Audit log entries cannot be deleted.'));
    }

    public static function record(string $action, ?Model $entity = null, array $details = []): self
    {
        $u = auth()->user();

        return static::create([
            'user_id'     => $u?->id,
            'user_name'   => $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'System',
            'user_role'   => $u?->role,
            'action'      => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id'   => $entity?->getKey(),
            'details'     => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => request()?->ip(),
        ]);
    }

    public function detailsArray(): array
    {
        return $this->details ? (json_decode($this->details, true) ?: []) : [];
    }
}
