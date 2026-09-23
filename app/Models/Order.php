<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'supplier_id',
        'user_id',
        'status',
        'expected_delivery_date',
        'notes',
        'total_amount',
        'payment_method',
    ];

    public const STATUSES = [
        'pending'   => ['label' => 'Pending',   'color' => 'bg-orange-100 text-orange-700'],
        'confirmed' => ['label' => 'Confirmed', 'color' => 'bg-blue-100 text-blue-700'],
        'shipped'   => ['label' => 'Shipped',   'color' => 'bg-purple-100 text-purple-700'],
        'delivered' => ['label' => 'Delivered', 'color' => 'bg-green-100 text-green-700'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'bg-red-100 text-red-700'],
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $order->order_number = 'ORD-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier()   { return $this->belongsTo(Supplier::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }
    // ← FIX: was missing
    public function deliveries() { return $this->hasMany(Delivery::class); }

    public function getTotalAmountAttribute($value)
    {
        if ($value) return $value;
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}