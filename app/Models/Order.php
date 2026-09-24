<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'site_id', 'customer_name', 'order_type',
        'design_description', 'needed_by_date',
        'total_amount', 'deposit_amount', 'status',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function site()  { return $this->belongsTo(Site::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
}