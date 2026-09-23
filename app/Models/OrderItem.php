<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'item_name', 'category',
        'unit', 'quantity', 'unit_price', 'notes',
    ];

    public const UNITS = ['bags', 'pcs', 'liters', 'gallons', 'meters', 'kg', 'tons', 'rolls', 'sheets', 'sets'];

    public function order()         { return $this->belongsTo(Order::class); }
    public function deliveryItems() { return $this->hasMany(DeliveryItem::class); }

    public function totalDelivered(): float
    {
        return $this->deliveryItems->sum('quantity_delivered');
    }
}