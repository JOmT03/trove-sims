<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'source_site_id', 'destination_site_id', 'batch_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = ['batch_date' => 'date'];

    public function items()           { return $this->hasMany(BatchItem::class); }
    public function sourceSite()      { return $this->belongsTo(Site::class, 'source_site_id'); }
    public function destinationSite() { return $this->belongsTo(Site::class, 'destination_site_id'); }
    public function creator()         { return $this->belongsTo(User::class, 'created_by'); }

    // â”€â”€ Reconciliation totals (computed, no internet needed) â”€â”€
    public function totalSent()      { return (int) $this->items->sum('qty_sent'); }
    public function totalReturned()  { return (int) $this->items->sum('qty_returned'); }
    public function totalNetSold()   { return $this->items->sum(fn ($i) => $i->qty_sent - $i->qty_returned); }
    public function dispatchedValue(){ return $this->items->sum(fn ($i) => $i->qty_sent * $i->unit_price); }
    public function totalRevenue()   { return $this->items->sum(fn ($i) => ($i->qty_sent - $i->qty_returned) * $i->unit_price); }
    public function lossValue()      { return $this->items->sum(fn ($i) => $i->qty_returned * $i->unit_price); }
}