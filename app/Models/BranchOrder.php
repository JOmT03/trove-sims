<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BranchOrder extends Model
{
    protected $fillable = ['destination_site_id', 'needed_by', 'status', 'notes', 'short_reason', 'created_by'];
    protected $casts = ['needed_by' => 'date'];

    public function items()
    {
        return $this->hasMany(BranchOrderItem::class);
    }

    public function destinationSite()
    {
        return $this->belongsTo(Site::class, 'destination_site_id');
    }

    /**
     * Attach delivery numbers (display only, never saved):
     * item->delivered, item->balance; order->ordered_qty, sent_qty, balance_qty.
     * Delivered = qty_sent of NON-voided batches linked to the order.
     * Balance   = ordered - delivered - closed short (never below 0).
     */
    public static function decorate($orders)
    {
        $orders = collect($orders);
        $ids = $orders->pluck('id')->all();
        $map = [];

        if (!empty($ids)) {
            $rows = DB::table('batch_items')
                ->join('batches', 'batches.id', '=', 'batch_items.batch_id')
                ->whereIn('batches.branch_order_id', $ids)
                ->whereNull('batches.voided_at')
                ->groupBy('batches.branch_order_id', 'batch_items.product_id')
                ->selectRaw('batches.branch_order_id as oid, batch_items.product_id as pid, SUM(batch_items.qty_sent) as q')
                ->get();
            foreach ($rows as $r) {
                $map[(int) $r->oid][(int) $r->pid] = (int) $r->q;
            }
        }

        foreach ($orders as $o) {
            $ord = $sent = $bal = 0;
            foreach ($o->items as $it) {
                $d = $map[(int) $o->id][(int) $it->product_id] ?? 0;
                $it->delivered = $d;
                $it->balance = max(0, (int) $it->qty - $d - (int) $it->qty_closed);
                $ord += (int) $it->qty;
                $sent += $d;
                $bal += $it->balance;
            }
            $o->ordered_qty = $ord;
            $o->sent_qty = $sent;
            $o->balance_qty = $bal;
        }

        return $orders;
    }

    /** Recompute requested/produced/partial/complete after a send or a void. */
    public function refreshStatus(): void
    {
        $fresh = static::with('items')->find($this->id);
        if (!$fresh || $fresh->status === 'cancelled') {
            return;
        }
        static::decorate(collect([$fresh]));

        if ($fresh->ordered_qty > 0 && $fresh->balance_qty <= 0) {
            $s = 'complete';
        } elseif ($fresh->sent_qty > 0) {
            $s = 'partial';
        } else {
            $s = in_array($fresh->status, ['requested', 'produced'], true) ? $fresh->status : 'requested';
        }

        if ($s !== $fresh->status) {
            static::whereKey($fresh->id)->update(['status' => $s]);
        }
    }
}