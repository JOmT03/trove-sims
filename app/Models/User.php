<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile_no',
        'email',
        'password',
        'role',
        'site_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Role helpers ──
    public function isOwner(): bool   { return $this->role === 'Owner'; }
    public function isManager(): bool { return $this->role === 'Manager'; }
    public function isStaff(): bool   { return $this->role === 'Staff'; }

    // ── Convenience accessor ──
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // ── Relationships ──
    public function site()   { return $this->belongsTo(Site::class); }
    public function orders() { return $this->hasMany(Order::class); }
}