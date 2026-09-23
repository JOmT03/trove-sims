<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order {{ $order->order_number }} — ConSupMan</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--navy:#0f1f3d;--navy-mid:#1a2f52;--gold:#e8a020;--gold-light:#f5b942;--sidebar-w:240px;--bg:#f4f6fa;--white:#fff;--text:#1a1a2e;--muted:#7a8499;--border:#e2e8f0;--green:#22c55e;--red:#ef4444;--orange:#f97316}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}
        svg{display:block}
        /* Sidebar */
        .sidebar{width:var(--sidebar-w);background:var(--navy);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
        .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,.08)}
        .logo-mark{display:flex;align-items:center;gap:10px}
        .logo-icon{width:38px;height:38px;background:var(--gold);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:14px;color:var(--navy)}
        .logo-text .brand{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;color:var(--white)}
        .logo-text .sub{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.5px}
        .sidebar-nav{flex:1;padding:16px 0}
        .nav-label{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);padding:16px 20px 8px}
        .nav-item{display:flex;align-items:center;gap:12px;padding:11px 20px;color:rgba(255,255,255,.6);text-decoration:none;font-size:14px;transition:all .2s;border-left:3px solid transparent}
        .nav-item:hover{background:rgba(255,255,255,.06);color:var(--white)}
        .nav-item.active{background:rgba(232,160,32,.12);color:var(--gold);border-left-color:var(--gold)}
        .nav-icon{width:18px;height:18px;flex-shrink:0}
        .sidebar-user{padding:16px 20px;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
        .user-avatar{width:34px;height:34px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:13px;color:var(--navy);flex-shrink:0}
        .user-info .name{font-size:13px;font-weight:500;color:var(--white)}
        .user-info .role{font-size:11px;color:rgba(255,255,255,.4)}
        .signout-btn{margin-left:auto;background:none;border:none;color:rgba(255,255,255,.3);cursor:pointer;padding:4px;transition:color .2s}
        .signout-btn:hover{color:var(--red)}
        /* Main */
        .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column}
        .topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
        .page-title{font-family:'Syne',sans-serif;font-weight:700;font-size:20px}
        .page-subtitle{font-size:13px;color:var(--muted);margin-top:1px}
        .content{padding:28px 32px;max-width:900px}
        .btn-back{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:13px;text-decoration:none;margin-bottom:20px;transition:color .2s}
        .btn-back:hover{color:var(--navy)}
        .card{background:var(--white);border-radius:14px;border:1px solid var(--border);margin-bottom:20px}
        .card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
        .card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:15px}
        .card-body{padding:24px}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .info-item .label{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
        .info-item .val{font-size:14px;font-weight:500;font-variant-numeric:tabular-nums}

        /* Status badge */
        .status-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700}
        .status-badge.pending{background:rgba(249,115,22,.12);color:#c2410c}
        .status-badge.confirmed{background:rgba(59,130,246,.12);color:#1d4ed8}
        .status-badge.shipped{background:rgba(139,92,246,.12);color:#7c3aed}
        .status-badge.delivered{background:rgba(34,197,94,.12);color:#15803d}
        .status-badge.cancelled{background:rgba(239,68,68,.1);color:#dc2626}

        /* Status flow */
        .status-flow{display:flex;align-items:center;gap:0;margin-bottom:24px}
        .step{display:flex;flex-direction:column;align-items:center;flex:1;position:relative}
        .step::after{content:'';position:absolute;top:16px;left:50%;right:-50%;height:2px;background:var(--border);z-index:0}
        .step:last-child::after{display:none}
        .step-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;border:2px solid var(--border);background:var(--white);z-index:1;position:relative}
        .step-dot.done{background:var(--green);border-color:var(--green);color:var(--white)}
        .step-dot.current{background:var(--gold);border-color:var(--gold);color:var(--navy)}
        .step-dot.future{background:var(--white);border-color:var(--border);color:var(--muted)}
        .step-label{font-size:11px;color:var(--muted);margin-top:6px;font-weight:500}
        .step-label.done{color:var(--green)}
        .step-label.current{color:var(--gold);font-weight:700}

        /* Action buttons */
        .btn-primary{background:var(--gold);color:var(--navy);font-family:'Syne',sans-serif;font-weight:700;font-size:13px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s}
        .btn-primary:hover{background:var(--gold-light)}
        .btn-secondary{background:var(--white);color:var(--text);font-size:13px;font-weight:500;padding:9px 18px;border-radius:10px;border:1px solid var(--border);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all .2s}
        .btn-secondary:hover{background:var(--navy);color:var(--white);border-color:var(--navy)}
        .btn-danger{background:rgba(239,68,68,.1);color:var(--red);font-size:13px;font-weight:500;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;transition:all .2s}
        .btn-danger:hover{background:var(--red);color:var(--white)}

        /* Items table */
        .items-table{width:100%;border-collapse:collapse}
        .items-table th{text-align:left;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;padding:12px 16px;background:#fafafa;border-bottom:1px solid var(--border)}
        .items-table td{padding:14px 16px;font-size:14px;border-bottom:1px solid var(--border);vertical-align:middle}
        .items-table tr:last-child td{border-bottom:none}
        .total-row td{font-family:'Syne',sans-serif;font-weight:700;background:#fafafa}

        .alert{padding:12px 20px;border-radius:10px;margin-bottom:16px;font-size:14px}
        .alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d}
        .alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#dc2626}

        .admin-actions{background:rgba(15,31,61,.03);border:1px solid var(--border);border-radius:12px;padding:20px 24px}
        .admin-actions h4{font-family:'Syne',sans-serif;font-weight:700;font-size:13px;margin-bottom:12px;color:var(--navy)}
        .status-btns{display:flex;gap:8px;flex-wrap:wrap}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-mark">
            <div class="logo-icon">CS</div>
            <div class="logo-text">
                <div class="brand">Con<span style="color:var(--gold)">Sup</span>Man</div>
                <div class="sub">Construction Supplier Mgmt</div>
            </div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Dashboard</a>
        <div class="nav-label">Procurement</div>
        <a href="{{ route('suppliers.index') }}" class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>Suppliers</a>
        <a href="{{ route('orders.index') }}" class="nav-item active"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Orders</a>
        <a href="{{ route('deliveries.index') }}" class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>Deliveries</a>
        <a href="{{ route('inventory.index') }}" class="nav-item"><svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Inventory</a>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar">{{ substr(auth()->user()->name,0,1) }}</div>
        <div class="user-info">
            <div class="name">{{ auth()->user()->name }}</div>
            <div class="role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">@csrf
            <button type="submit" class="signout-btn"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div>
            <div class="page-title">Order {{ $order->order_number }}</div>
            <div class="page-subtitle">Order details and status tracking</div>
        </div>
        <span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span>
    </header>

    <div class="content">
        @if(session('success'))<div class="alert alert-success">✓ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-error">✗ {{ session('error') }}</div>@endif

        <a href="{{ route('orders.index') }}" class="btn-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Orders
        </a>

        <!-- Status Progress Bar -->
        @php
            $steps = ['pending','confirmed','shipped','delivered'];
            $currentIdx = array_search($order->status, $steps);
            if($order->status === 'cancelled') $currentIdx = -1;
        @endphp
        @if($order->status !== 'cancelled')
        <div class="card" style="margin-bottom:20px">
            <div class="card-body" style="padding:24px 32px">
                <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:16px">Order Progress</div>
                <div class="status-flow">
                    @foreach($steps as $i => $step)
                    <div class="step">
                        <div class="step-dot {{ $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'current' : 'future') }}">
                            @if($i < $currentIdx) ✓ @else {{ $i + 1 }} @endif
                        </div>
                        <div class="step-label {{ $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'current' : '') }}">
                            {{ ucfirst($step) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Admin: Update Status -->
        @if(auth()->user()->role === 'admin' && $order->status !== 'delivered' && $order->status !== 'cancelled')
        <div class="admin-actions" style="margin-bottom:20px">
            <h4>🔧 Admin: Update Order Status</h4>
            <div class="status-btns">
                @if($order->status === 'pending')
                    <form method="POST" action="{{ route('orders.status', $order) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn-primary">✓ Confirm Order</button>
                    </form>
                    <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Cancel this order?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">✕ Cancel Order</button>
                    </form>
                @elseif($order->status === 'confirmed')
                    <form method="POST" action="{{ route('orders.status', $order) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit" class="btn-primary">🚚 Mark as Shipped</button>
                    </form>
                @elseif($order->status === 'shipped')
                    <form method="POST" action="{{ route('orders.status', $order) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="btn-primary">📦 Mark as Delivered</button>
                    </form>
                    <a href="{{ route('deliveries.create') }}?order_id={{ $order->id }}" class="btn-secondary">+ Record Delivery</a>
                @endif
            </div>
            <p style="font-size:12px;color:var(--muted);margin-top:10px">
                @if($order->status==='pending') Confirm this order to notify the supplier it's approved.
                @elseif($order->status==='confirmed') Mark as Shipped once the supplier dispatches the items.
                @elseif($order->status==='shipped') Mark as Delivered and record the delivery details.
                @endif
            </p>
        </div>
        @endif

        <!-- Order Info -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Order Information</div>
                <div style="font-size:13px;color:var(--muted)">{{ $order->created_at->format('M d, Y h:i A') }}</div>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Order Number</div>
                        <div class="val" style="font-family:'Syne',sans-serif;font-weight:700">{{ $order->order_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Supplier</div>
                        <div class="val">{{ $order->supplier->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Category</div>
                        <div class="val">{{ $order->supplier->category ?? '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Placed By</div>
                        <div class="val">{{ $order->user->name ?? auth()->user()->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Expected Delivery</div>
                        <div class="val">{{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('M d, Y') : '—' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Status</div>
                        <div class="val"><span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
                    </div>
                    @if($order->notes)
                    <div class="info-item" style="grid-column:1/-1">
                        <div class="label">Notes</div>
                        <div class="val">{{ $order->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Ordered Items</div>
                <div style="font-size:13px;color:var(--muted)">{{ $order->items->count() }} item(s)</div>
            </div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th style="text-align:right">Qty</th>
                        <th style="text-align:right">Unit Price</th>
                        <th style="text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $i => $item)
                    <tr>
                        <td style="color:var(--muted);font-size:12px">{{ $i+1 }}</td>
                        <td style="font-weight:500">{{ $item->item_name }}</td>
                        <td><span style="background:rgba(15,31,61,.07);color:var(--navy);padding:3px 9px;border-radius:20px;font-size:11px;font-weight:500">{{ $item->category }}</span></td>
                        <td style="color:var(--muted)">{{ $item->unit }}</td>
                        <td style="text-align:right">{{ number_format($item->quantity) }}</td>
                        <td style="text-align:right">₱{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align:right;font-weight:600">₱{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="6" style="text-align:right;padding-right:16px">Total Amount</td>
                        <td style="text-align:right">₱{{ number_format($order->items->sum(fn($i) => $i->quantity * $i->unit_price), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>