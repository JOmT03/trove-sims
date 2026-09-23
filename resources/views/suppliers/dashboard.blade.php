<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard — ConSupMan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f1f5f9;color:#1e293b;display:flex;min-height:100vh;}

        /* SIDEBAR */
        .sidebar{width:240px;background:#0f1f3d;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;}
        .sb-logo{padding:20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px;text-decoration:none;}
        .sb-logo-box{width:36px;height:36px;background:#f0ad1f;border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px;color:#0f1f3d;}
        .sb-logo-name{font-size:16px;font-weight:900;color:#fff;line-height:1;}
        .sb-logo-name span{color:#f0ad1f;}
        .sb-logo-sub{font-size:10px;color:rgba(255,255,255,.4);margin-top:2px;}
        .sb-nav{flex:1;padding:12px 10px;overflow-y:auto;}
        .sb-section{font-size:10px;font-weight:700;letter-spacing:1px;color:rgba(255,255,255,.3);text-transform:uppercase;padding:10px 8px 4px;}
        .sb-link{display:flex;align-items:center;gap:10px;padding:10px 11px;border-radius:9px;text-decoration:none;color:rgba(255,255,255,.55);font-size:13px;font-weight:500;transition:all .15s;margin-bottom:2px;}
        .sb-link:hover{background:rgba(255,255,255,.07);color:#fff;}
        .sb-link.active{background:#f0ad1f;color:#0f1f3d;font-weight:700;}
        .sb-link svg{width:17px;height:17px;stroke:currentColor;flex-shrink:0;}
        .sb-footer{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);}
        .sb-user{display:flex;align-items:center;gap:10px;padding:10px 11px;background:rgba(255,255,255,.06);border-radius:9px;}
        .sb-avatar{width:32px;height:32px;border-radius:50%;background:#f0ad1f;color:#0f1f3d;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px;flex-shrink:0;}
        .sb-uname{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .sb-urole{font-size:10px;color:rgba(255,255,255,.4);}
        .sb-logout{background:none;border:none;cursor:pointer;color:rgba(255,255,255,.35);padding:4px;display:flex;transition:color .15s;margin-left:auto;}
        .sb-logout:hover{color:#f87171;}
        .sb-logout svg{width:15px;height:15px;stroke:currentColor;}

        /* MAIN */
        .main{margin-left:240px;flex:1;display:flex;flex-direction:column;}
        .topbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:0 28px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
        .topbar h1{font-size:18px;font-weight:800;color:#0f1f3d;}
        .topbar p{font-size:12px;color:#94a3b8;}
        .supplier-pill{padding:4px 12px;border-radius:20px;background:#dcfce7;color:#166534;font-size:11px;font-weight:700;}
        .page{padding:24px 28px;}
        footer{text-align:center;padding:14px;font-size:11px;color:#94a3b8;border-top:1px solid #e2e8f0;}

        /* STATS */
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
        .stat{background:#fff;border-radius:13px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);border-left:4px solid #f0ad1f;}
        .stat.blue{border-color:#3b82f6;} .stat.green{border-color:#10b981;} .stat.red{border-color:#ef4444;} .stat.purple{border-color:#8b5cf6;}
        .stat-label{font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
        .stat-val{font-size:26px;font-weight:900;color:#0f1f3d;line-height:1;margin:4px 0 2px;}
        .stat-sub{font-size:11px;color:#94a3b8;}

        /* CARDS */
        .card{background:#fff;border-radius:13px;box-shadow:0 1px 4px rgba(0,0,0,.06);overflow:hidden;margin-bottom:20px;}
        .card-head{padding:15px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
        .card-title{font-size:14px;font-weight:800;color:#0f1f3d;}
        .btn{padding:8px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:opacity .2s;}
        .btn:hover{opacity:.85;}
        .btn-gold{background:#f0ad1f;color:#0f1f3d;}
        table{width:100%;border-collapse:collapse;font-size:13px;}
        th{padding:10px 16px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;font-weight:700;background:#f8fafc;border-bottom:1px solid #f1f5f9;}
        td{padding:12px 16px;border-bottom:1px solid #f8fafc;color:#334155;}
        tr:last-child td{border-bottom:none;}
        tr:hover td{background:#fafafa;}
        .pill{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;}
        .pill-pending{background:#fef9c3;color:#854d0e;}
        .pill-confirmed{background:#dbeafe;color:#1e40af;}
        .pill-shipped{background:#ede9fe;color:#5b21b6;}
        .pill-delivered{background:#dcfce7;color:#166534;}
        .pill-cancelled{background:#fee2e2;color:#991b1b;}

        .flash-ok{background:#f0fdf4;border:1px solid #86efac;color:#166534;border-radius:10px;padding:11px 16px;font-size:13px;margin-bottom:16px;}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <a href="{{ route('dashboard') }}" class="sb-logo">
        <div class="sb-logo-box">CS</div>
        <div>
            <div class="sb-logo-name">ConSup<span>Man</span></div>
            <div class="sb-logo-sub">Supplier Portal</div>
        </div>
    </a>
    <nav class="sb-nav">
        <div class="sb-section">My Store</div>
        <a href="{{ route('dashboard') }}" class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('supplier.products.index') }}" class="sb-link {{ request()->routeIs('supplier.products.*') ? 'active' : '' }}">
            <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            My Products
        </a>
        <a href="{{ route('supplier.orders') }}" class="sb-link {{ request()->routeIs('supplier.orders') ? 'active' : '' }}">
            <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Incoming Orders
        </a>
        <div class="sb-section">Account</div>
        <a href="{{ route('profile.edit') }}" class="sb-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            My Profile
        </a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="sb-uname">{{ auth()->user()->name }}</div>
                <div class="sb-urole">Supplier</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout" title="Sign Out">
                    <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <header class="topbar">
        <div>
            <h1>Supplier Dashboard</h1>
            <p>{{ auth()->user()->company_name ?? auth()->user()->name }}</p>
        </div>
        <span class="supplier-pill">🏭 Supplier</span>
    </header>

    <div class="page">

        @if(session('success'))
            <div class="flash-ok">✓ {{ session('success') }}</div>
        @endif

        <!-- Stats -->
        <div class="stats">
            <div class="stat blue">
                <div class="stat-label">My Products</div>
                <div class="stat-val">{{ $productCount }}</div>
                <div class="stat-sub">Listed products</div>
            </div>
            <div class="stat gold" style="border-color:#f0ad1f;">
                <div class="stat-label">Incoming Orders</div>
                <div class="stat-val">{{ $totalOrders }}</div>
                <div class="stat-sub">{{ $pendingOrders }} pending</div>
            </div>
            <div class="stat green">
                <div class="stat-label">Confirmed</div>
                <div class="stat-val">{{ $confirmedOrders }}</div>
                <div class="stat-sub">Ready to ship</div>
            </div>
            <div class="stat purple">
                <div class="stat-label">Delivered</div>
                <div class="stat-val">{{ $deliveredOrders }}</div>
                <div class="stat-sub">Completed orders</div>
            </div>
        </div>

        <!-- My Products -->
        <div class="card">
            <div class="card-head">
                <div class="card-title">My Products</div>
                <a href="{{ route('supplier.products.create') }}" class="btn btn-gold">+ Add Product</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td style="font-weight:700;color:#0f1f3d;">{{ $p->name }}</td>
                            <td><span style="padding:2px 8px;background:#fef3c7;color:#92400e;border-radius:10px;font-size:11px;font-weight:600;">{{ $p->category }}</span></td>
                            <td style="color:#64748b;">{{ $p->unit }}</td>
                            <td style="font-weight:700;">₱{{ number_format($p->price, 2) }}</td>
                            <td>
                                @if($p->is_available)
                                    <span class="pill pill-delivered">Available</span>
                                @else
                                    <span class="pill pill-cancelled">Unavailable</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <a href="{{ route('supplier.products.edit', $p) }}"
                                       style="padding:4px 12px;background:#dbeafe;color:#1e40af;border-radius:7px;font-size:12px;font-weight:600;text-decoration:none;">Edit</a>
                                    <form method="POST" action="{{ route('supplier.products.destroy', $p) }}"
                                          onsubmit="return confirm('Delete {{ addslashes($p->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button style="padding:4px 12px;background:#fee2e2;color:#dc2626;border-radius:7px;font-size:12px;font-weight:600;border:none;cursor:pointer;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:28px;color:#94a3b8;">
                                No products yet. <a href="{{ route('supplier.products.create') }}" style="color:#f0ad1f;font-weight:700;">Add your first product →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Incoming Orders -->
        <div class="card">
            <div class="card-head">
                <div class="card-title">Recent Incoming Orders</div>
                <a href="{{ route('supplier.orders') }}" style="font-size:12px;color:#f0ad1f;font-weight:700;text-decoration:none;">View all →</a>
            </div>
            <table>
                <thead>
                    <tr><th>Order #</th><th>Buyer</th><th>Items</th><th>Total</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $o)
                        @php
                            $pillMap = ['pending'=>'pill-pending','confirmed'=>'pill-confirmed','shipped'=>'pill-shipped','delivered'=>'pill-delivered','cancelled'=>'pill-cancelled'];
                            $lbl = \App\Models\Order::STATUSES[$o->status]['label'] ?? $o->status;
                        @endphp
                        <tr>
                            <td style="font-family:monospace;font-weight:700;color:#0f1f3d;">{{ $o->order_number }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $o->user->name }}</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ $o->user->company_name ?? '' }}</div>
                            </td>
                            <td style="color:#64748b;">{{ $o->items->count() }} item(s)</td>
                            <td style="font-weight:700;">₱{{ number_format($o->totalValue(), 2) }}</td>
                            <td><span class="pill {{ $pillMap[$o->status] ?? '' }}">{{ $lbl }}</span></td>
                            <td>
                                @if($o->status === 'pending')
                                    <form method="POST" action="{{ route('supplier.orders.status', $o) }}" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button style="padding:4px 12px;background:#dcfce7;color:#166534;border-radius:7px;font-size:12px;font-weight:700;border:none;cursor:pointer;">✓ Confirm</button>
                                    </form>
                                @elseif($o->status === 'confirmed')
                                    <form method="POST" action="{{ route('supplier.orders.status', $o) }}" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="shipped">
                                        <button style="padding:4px 12px;background:#ede9fe;color:#5b21b6;border-radius:7px;font-size:12px;font-weight:700;border:none;cursor:pointer;">🚚 Ship</button>
                                    </form>
                                @elseif($o->status === 'shipped')
                                    <form method="POST" action="{{ route('supplier.orders.status', $o) }}" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="delivered">
                                        <button style="padding:4px 12px;background:#dbeafe;color:#1e40af;border-radius:7px;font-size:12px;font-weight:700;border:none;cursor:pointer;">📦 Deliver</button>
                                    </form>
                                @else
                                    <span style="color:#94a3b8;font-size:12px;">{{ $lbl }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
    <footer>© {{ date('Y') }} ConSupMan. All rights reserved.</footer>
</div>

</body>
</html>