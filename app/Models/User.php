<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_number',
        'role',
        'company_name',
        'company_address',
        'company_city',
        'company_zip',
        'company_email',
        'company_tel',
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
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isSupplier(): bool { return $this->role === 'supplier'; }
    public function isUser(): bool     { return $this->role === 'user'; }

    // ── Relationships ──
    public function orders()         { return $this->hasMany(Order::class); }
    public function products()       { return $this->hasMany(SupplierProduct::class); }

    // The Supplier record linked to this user account
    // Used in SupplierController to find unlinked supplier users
    public function supplierRecord() { return $this->hasOne(Supplier::class, 'user_id'); }
}