<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'supplier_id', 'delivery_number',
        'delivered_at', 'received_by', 'status', 'notes',
    ];

    protected $casts = [
        'delivered_at' => 'date',
    ];

    public const STATUSES = [
        'pending'  => ['label' => 'Pending',  'color' => 'bg-yellow-100 text-yellow-800'],
        'partial'  => ['label' => 'Partial',  'color' => 'bg-orange-100 text-orange-800'],
        'complete' => ['label' => 'Complete', 'color' => 'bg-green-100 text-green-800'],
    ];

    public function order()    { return $this->belongsTo(Order::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items()    { return $this->hasMany(DeliveryItem::class); }

    protected static function booted(): void
    {
        static::creating(function (Delivery $d) {
            $d->delivery_number = 'DEL-' . date('Y') . '-' . str_pad(
                static::whereYear('created_at', date('Y'))->count() + 1,
                4, '0', STR_PAD_LEFT
            );
        });
    }
}