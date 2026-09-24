<x-app-layout>
<x-slot name="header">Orders</x-slot>
<x-slot name="subheader">Manage walk-in, customized, and institutional orders</x-slot>

<style>
.mini-stats{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;}
.mini-stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px 20px;flex:1;min-width:120px;}
.mini-stat .label{font-size:11px;color:#7a8499;text-transform:uppercase;letter-spacing:.8px;font-weight:600;}
.mini-stat .val{font-family:'Syne',sans-serif;font-weight:800;font-size:28px;margin-top:4px;}
.toolbar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;}
.filter-btn{padding:8px 16px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;font-size:13px;cursor:pointer;color:#7a8499;}
.filter-btn.active-gold{background:rgba(232,160,32,.1);color:#e8a020;border-color:#e8a020;font-weight:600;}
.table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;}
.orders-table{width:100%;border-collapse:collapse;}
.orders-table th{text-align:left;font-size:11px;font-weight:600;color:#7a8499;text-transform:uppercase;letter-spacing:.8px;padding:14px 20px;background:#fafbfc;border-bottom:1px solid #e2e8f0;}
.orders-table td{padding:15px 20px;font-size:14px;border-bottom:1px solid #e2e8f0;vertical-align:middle;}
.status-badge{display:inline-flex;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-badge.Pending{background:rgba(249,115,22,.12);color:#c2410c;}
.status-badge.In-Production{background:rgba(59,130,246,.12);color:#1d4ed8;}
.status-badge.Ready{background:rgba(139,92,246,.12);color:#7c3aed;}
.status-badge.Completed{background:rgba(34,197,94,.12);color:#15803d;}
.btn-sm{padding:5px 11px;font-size:12px;border-radius:7px;border:1px solid #e2e8f0;cursor:pointer;background:#fff;color:#1a1a2e;text-decoration:none;}
.btn-sm.red{border-color:transparent;background:rgba(239,68,68,.1);color:#ef4444;}
.empty-state{text-align:center;padding:60px 32px;color:#7a8499;}
.alert-success{padding:12px 20px;border-radius:10px;margin-bottom:16px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d;}
</style>

@if(session('success'))<div class="alert-success">✓ {{ session('success') }}</div>@endif

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <a href="{{ route('orders.create') }}" class="btn-primary" style="background:#f0ad1f;color:#0f1f3d;font-weight:700;padding:9px 18px;border-radius:10px;text-decoration:none;">+ New Order</a>
</div>

<div class="mini-stats">
    <div class="mini-stat"><div class="label">Total</div><div class="val">{{ $orders->count() }}</div></div>
    <div class="mini-stat"><div class="label" style="color:#c2410c">Pending</div><div class="val" style="color:#c2410c">{{ $orders->where('status','Pending')->count() }}</div></div>
    <div class="mini-stat"><div class="label" style="color:#1d4ed8">In Production</div><div class="val" style="color:#1d4ed8">{{ $orders->where('status','In Production')->count() }}</div></div>
    <div class="mini-stat"><div class="label" style="color:#15803d">Completed</div><div class="val" style="color:#15803d">{{ $orders->where('status','Completed')->count() }}</div></div>
</div>

<div class="table-card">
    @if($orders->count())
    <table class="orders-table">
        <thead>
            <tr><th>Order #</th><th>Customer</th><th>Site</th><th>Type</th><th>Date</th><th>Status</th><th>Items</th><th>Total</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->site->site_name ?? '—' }}</td>
                <td>{{ $order->order_type }}</td>
                <td style="color:#7a8499;font-size:13px">{{ $order->created_at->format('M d, Y') }}</td>
                <td><span class="status-badge {{ str_replace(' ', '-', $order->status) }}">{{ $order->status }}</span></td>
                <td style="color:#7a8499;font-size:13px">{{ $order->items->count() }} item(s)</td>
                <td style="font-weight:700">₱{{ number_format($order->total_amount, 2) }}</td>
                <td>
                    <a href="{{ route('orders.show', $order) }}" class="btn-sm">View</a>
                    @if($order->status === 'Pending')
                    <form method="POST" action="{{ route('orders.destroy', $order) }}" style="display:inline" onsubmit="return confirm('Cancel this order?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-sm red">Cancel</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <p>No orders yet.</p>
        <a href="{{ route('orders.create') }}" style="color:#f0ad1f;font-weight:700;">+ Create your first order</a>
    </div>
    @endif
</div>
</x-app-layout>