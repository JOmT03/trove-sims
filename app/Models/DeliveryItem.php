<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id', 'order_item_id', 'item_name', 'category',
        'unit', 'quantity_ordered', 'quantity_delivered',
        'quantity_damaged', 'condition', 'damage_notes',
    ];

    public const CONDITIONS = [
        'good'           => ['label' => 'Good',           'color' => 'bg-green-100 text-green-800'],
        'partial_damage' => ['label' => 'Partial Damage', 'color' => 'bg-orange-100 text-orange-800'],
        'all_damaged'    => ['label' => 'All Damaged',    'color' => 'bg-red-100 text-red-800'],
    ];

    public function delivery()  { return $this->belongsTo(Delivery::class); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }
}