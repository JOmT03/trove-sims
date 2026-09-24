<x-app-layout>
<x-slot name="header">Dashboard</x-slot>
<x-slot name="subheader">Overview of Trove's sales and inventory</x-slot>

<style>
    .welcome-banner{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-mid) 100%);border-radius:16px;padding:24px 32px;color:var(--white);margin-bottom:24px;position:relative;overflow:hidden}
    .welcome-banner::after{content:'🍰';position:absolute;right:32px;top:50%;transform:translateY(-50%);font-size:56px;opacity:.25}
    .welcome-banner h2{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;margin-bottom:4px}
    .welcome-banner p{font-size:13px;color:rgba(255,255,255,.6)}

    .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
    .stat-card{background:var(--white);border-radius:14px;padding:20px 22px;border:1px solid var(--border);position:relative;overflow:hidden}
    .stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
    .stat-card.blue::before{background:var(--navy)}
    .stat-card.gold::before{background:var(--gold)}
    .stat-card.green::before{background:var(--green)}
    .stat-card.orange::before{background:var(--orange)}
    .stat-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px}
    .stat-label{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px}
    .stat-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center}
    .stat-card.blue .stat-icon{background:rgba(15,31,61,.08);color:var(--navy)}
    .stat-card.gold .stat-icon{background:rgba(232,160,32,.12);color:var(--gold)}
    .stat-card.green .stat-icon{background:rgba(34,197,94,.12);color:var(--green)}
    .stat-card.orange .stat-icon{background:rgba(249,115,22,.12);color:var(--orange)}
    .stat-value{font-family:'DM Sans',sans-serif;font-size:30px;font-weight:800;line-height:1;margin-bottom:5px;font-variant-numeric:tabular-nums}
    .stat-sub{font-size:12px;color:var(--muted)}
    .stat-sub .hi{color:var(--green);font-weight:600}
    .stat-sub .lo{color:var(--red);font-weight:600}

    .two-col{display:grid;grid-template-columns:1fr 320px;gap:18px;margin-bottom:20px}
    .card{background:var(--white);border-radius:14px;border:1px solid var(--border)}
    .card-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
    .card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px}
    .card-link{font-size:13px;color:var(--gold);text-decoration:none;font-weight:500}
    .card-link:hover{text-decoration:underline}

    .chart-wrap{padding:20px 24px 16px}
    .bar-chart{display:flex;align-items:flex-end;gap:8px;height:130px}
    .bar-col{display:flex;flex-direction:column;align-items:center;flex:1;gap:3px}
    .bar{width:100%;background:var(--navy);border-radius:4px 4px 0 0;min-height:4px}
    .bar-count{font-size:11px;font-weight:700;color:var(--navy);font-variant-numeric:tabular-nums}
    .bar-label{font-size:10px;color:var(--muted);text-align:center;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%}

    .sup-list{max-height:280px;overflow-y:auto}
    .sup-row{display:flex;align-items:center;gap:12px;padding:13px 20px;border-bottom:1px solid var(--border)}
    .sup-row:last-child{border-bottom:none}
    .sup-row:hover{background:#fafcff}
    .sup-avatar{width:32px;height:32px;border-radius:9px;background:rgba(15,31,61,.06);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
    .sup-info{flex:1;min-width:0}
    .sup-name{font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .sup-cat{font-size:11px;color:var(--muted)}
    .sup-badge{font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:rgba(34,197,94,.12);color:#15803d;flex-shrink:0}

    .bottom-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .mini-card{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px}
    .mini-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
    .mini-body .label{font-size:11px;color:var(--muted);font-weight:500}
    .mini-body .val{font-family:'DM Sans',sans-serif;font-weight:800;font-size:22px;font-variant-numeric:tabular-nums}
</style>

<div class="welcome-banner">
    <h2>Welcome back, {{ auth()->user()->name }}.</h2>
    <p>{{ now()->format('l, F d, Y') }} — Here's your sales and inventory overview.</p>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-header">
            <span class="stat-label">Total Products</span>
            <div class="stat-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        </div>
        <div class="stat-value">{{ $productCount }}</div>
        <div class="stat-sub">Cakes, pastries & coffee items</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-header">
            <span class="stat-label">Current Orders</span>
            <div class="stat-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
        </div>
        <div class="stat-value">{{ $activeOrderCount }}</div>
        <div class="stat-sub"><span class="lo">{{ $orderPending }} pending</span> · <span class="hi">{{ $completedOrders }} completed</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header">
            <span class="stat-label">Deliveries This Week</span>
            <div class="stat-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></div>
        </div>
        <div class="stat-value">{{ $deliveriesThisWeek }}</div>
        <div class="stat-sub"><span class="hi">{{ $deliveryTotal }} total</span> all time</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header">
            <span class="stat-label">Inventory Items</span>
            <div class="stat-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        </div>
        <div class="stat-value">{{ $inventoryCount }}</div>
        <div class="stat-sub">@if($lowStock > 0)<span class="lo">{{ $lowStock }} low stock</span>@else All stocked @endif</div>
    </div>
</div>

<div class="two-col">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Products by Category</div>
            <div style="font-size:12px;color:var(--muted)">Cake, pastry & coffee items</div>
        </div>
        <div class="chart-wrap">
            @php $maxVal = max(1, collect($productsByCategory)->max()); @endphp
            <div class="bar-chart">
                @forelse($productsByCategory as $cat => $count)
                @php $pct = round(($count / $maxVal) * 100); @endphp
                <div class="bar-col" title="{{ $cat }}: {{ $count }}">
                    <div class="bar-count">{{ $count }}</div>
                    <div class="bar" style="height:{{ max(4,$pct) }}%"></div>
                    <div class="bar-label">{{ \Illuminate\Support\Str::limit($cat, 8) }}</div>
                </div>
                @empty
                <div style="width:100%;text-align:center;color:var(--muted);font-size:13px;padding:20px 0">No products yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent Orders</div>
            <a href="{{ route('orders.index') }}" class="card-link">View all →</a>
        </div>
        <div class="sup-list">
            @forelse($recentOrders as $order)
            <div class="sup-row">
                <div class="sup-avatar">🧾</div>
                <div class="sup-info">
                    <div class="sup-name">{{ $order->customer_name ?? 'Walk-in' }}</div>
                    <div class="sup-cat">{{ $order->order_type }}</div>
                </div>
                <span class="sup-badge">{{ $order->status }}</span>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:var(--muted);font-size:14px">
                No orders yet.
            </div>
            @endforelse
        </div>
    </div>
</div>

<div class="bottom-row">
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(34,197,94,.1);color:var(--green)">✅</div>
        <div class="mini-body"><div class="label">Completed Orders</div><div class="val">{{ $completedOrders }}</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(239,68,68,.1);color:var(--red)">⚠️</div>
        <div class="mini-body"><div class="label">Low Stock Items</div><div class="val">{{ $lowStock }}</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(15,31,61,.08);color:var(--navy)">🚚</div>
        <div class="mini-body"><div class="label">Total Deliveries</div><div class="val">{{ $deliveryTotal }}</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(232,160,32,.12);color:var(--gold)">📋</div>
        <div class="mini-body"><div class="label">Pending Orders</div><div class="val">{{ $orderPending }}</div></div>
    </div>
</div>

</x-app-layout>