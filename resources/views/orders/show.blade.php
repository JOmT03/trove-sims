<x-app-layout>
<x-slot name="header">Order #{{ $order->id }}</x-slot>
<x-slot name="subheader">{{ $order->order_type }} order — {{ $order->customer_name }}</x-slot>

<style>
.card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;margin-bottom:20px;}
.card-header{padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;}
.card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px;}
.card-body{padding:24px;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.info-item .label{font-size:11px;font-weight:600;color:#7a8499;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;}
.info-item .val{font-size:14px;font-weight:500;}
.status-badge{display:inline-flex;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;}
.status-badge.Pending{background:rgba(249,115,22,.12);color:#c2410c;}
.status-badge.In-Production{background:rgba(59,130,246,.12);color:#1d4ed8;}
.status-badge.Ready{background:rgba(139,92,246,.12);color:#7c3aed;}
.status-badge.Completed{background:rgba(34,197,94,.12);color:#15803d;}
.items-table{width:100%;border-collapse:collapse;}
.items-table th{text-align:left;font-size:11px;font-weight:600;color:#7a8499;text-transform:uppercase;padding:12px 16px;background:#fafafa;border-bottom:1px solid #e2e8f0;}
.items-table td{padding:14px 16px;font-size:14px;border-bottom:1px solid #e2e8f0;}
.total-row td{font-family:'Syne',sans-serif;font-weight:700;background:#fafafa;}
.btn-primary{background:#f0ad1f;color:#0f1f3d;font-weight:700;font-size:13px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;}
.admin-actions{background:rgba(15,31,61,.03);border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;margin-bottom:20px;}
.alert-success{padding:12px 20px;border-radius:10px;margin-bottom:16px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d;}
.alert-error{padding:12px 20px;border-radius:10px;margin-bottom:16px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#dc2626;}
</style>

@if(session('success'))<div class="alert-success">✓ {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert-error">✗ {{ session('error') }}</div>@endif

<a href="{{ route('orders.index') }}" style="color:#7a8499;font-size:13px;text-decoration:none;display:inline-block;margin-bottom:20px;">← Back to Orders</a>

@if(in_array(auth()->user()->role, ['Owner','Manager']) && $order->status !== 'Completed')
<div class="admin-actions">
    <h4 style="font-family:'Syne',sans-serif;font-size:13px;margin-bottom:12px;">Update Order Status</h4>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if($order->status === 'Pending')
            <form method="POST" action="{{ route('orders.status', $order) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="In Production">
                <button type="submit" class="btn-primary">Start Production</button>
            </form>
        @elseif($order->status === 'In Production')
            <form method="POST" action="{{ route('orders.status', $order) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="Ready">
                <button type="submit" class="btn-primary">Mark as Ready</button>
            </form>
        @elseif($order->status === 'Ready')
            <form method="POST" action="{{ route('orders.status', $order) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="Completed">
                <button type="submit" class="btn-primary">Mark as Completed</button>
            </form>
        @endif
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">Order Information</div>
        <span class="status-badge {{ str_replace(' ', '-', $order->status) }}">{{ $order->status }}</span>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item"><div class="label">Customer</div><div class="val">{{ $order->customer_name }}</div></div>
            <div class="info-item"><div class="label">Site</div><div class="val">{{ $order->site->site_name ?? '—' }}</div></div>
            <div class="info-item"><div class="label">Order Type</div><div class="val">{{ $order->order_type }}</div></div>
            <div class="info-item"><div class="label">Placed By</div><div class="val">{{ $order->user->name ?? '—' }}</div></div>
            @if($order->order_type === 'Customized')
            <div class="info-item" style="grid-column:1/-1"><div class="label">Design Description</div><div class="val">{{ $order->design_description }}</div></div>
            <div class="info-item"><div class="label">Needed By</div><div class="val">{{ $order->needed_by_date ? \Carbon\Carbon::parse($order->needed_by_date)->format('M d, Y') : '—' }}</div></div>
            <div class="info-item"><div class="label">Deposit Paid</div><div class="val">₱{{ number_format($order->deposit_amount, 2) }}</div></div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">Ordered Items</div></div>
    <table class="items-table">
        <thead><tr><th>Product</th><th style="text-align:right">Qty</th><th style="text-align:right">Price</th><th style="text-align:right">Subtotal</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                <td style="text-align:right">{{ $item->quantity }}</td>
                <td style="text-align:right">₱{{ number_format($item->price, 2) }}</td>
                <td style="text-align:right;font-weight:600">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align:right;padding-right:16px">Total</td>
                <td style="text-align:right">₱{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
</x-app-layout>