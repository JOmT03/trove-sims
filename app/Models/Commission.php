<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id', 'site_id', 'customer_name', 'client_type', 'order_type',
        'design_description', 'needed_by_date', 'total_amount', 'deposit_amount', 'status',
    ];

    protected $casts = [
        'needed_by_date' => 'date',
    ];

    public const STAGES = ['Inquiry', 'Confirmed', 'Baking', 'Ready', 'Completed'];

    public function items() { return $this->hasMany(OrderItem::class, 'order_id'); }
    public function site()  { return $this->belongsTo(Site::class); }

    public function balance() { return $this->total_amount - $this->deposit_amount; }
}