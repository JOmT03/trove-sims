<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',   // ← links to supplier's User account
        'name',
        'email',
        'phone',
        'address',
        'category',
    ];

    public const CATEGORIES = [
        'cement'         => 'Cement Supplier',
        'steel'          => 'Steel & Rebar Supplier',
        'sand_gravel'    => 'Sand & Gravel Supplier',
        'hollow_blocks'  => 'Hollow Blocks / CHB Supplier',
        'lumber_wood'    => 'Lumber & Wood Supplier',
        'paint'          => 'Paint & Coatings Supplier',
        'tiles_flooring' => 'Tiles & Flooring Supplier',
        'glass_windows'  => 'Glass & Windows Supplier',
        'roofing'        => 'Roofing Materials Supplier',
        'insulation'     => 'Insulation Supplier',
        'plumbing'       => 'Plumbing & Pipes Supplier',
        'electrical'     => 'Electrical & Wiring Supplier',
        'hvac'           => 'HVAC / Ventilation Supplier',
        'equipment'      => 'Heavy Equipment Provider',
        'tools'          => 'Construction Tools Supplier',
        'scaffolding'    => 'Scaffolding Supplier',
        'safety'         => 'Safety Equipment Supplier',
        'hardware'       => 'General Hardware Supplier',
        'chemicals'      => 'Construction Chemicals Supplier',
        'other'          => 'Other',
    ];

    // The User account of this supplier
    public function user()    { return $this->belongsTo(User::class); }
    public function orders()  { return $this->hasMany(Order::class); }

    // Products listed by this supplier's user account
    public function products()
    {
        return $this->hasMany(SupplierProduct::class, 'user_id', 'user_id');
    }
}