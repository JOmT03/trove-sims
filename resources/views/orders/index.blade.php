<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — ConSupMan</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #0f1f3d; --navy-mid: #1a2f52; --gold: #e8a020; --gold-light: #f5b942;
            --sidebar-w: 240px; --bg: #f4f6fa; --white: #ffffff; --text: #1a1a2e;
            --muted: #7a8499; --border: #e2e8f0; --green: #22c55e; --red: #ef4444; --orange: #f97316;
        }

        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Sidebar (reuse) */
        .sidebar { width: var(--sidebar-w); background: var(--navy); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; }
        .sidebar-logo { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .logo-mark { display: flex; align-items: center; gap: 10px; }
        .logo-icon { width: 38px; height: 38px; background: var(--gold); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 800; font-size: 14px; color: var(--navy); }
        .logo-text .brand { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 16px; color: var(--white); }
        .logo-text .sub { font-size: 10px; color: rgba(255,255,255,0.4); letter-spacing: 0.5px; }
        .sidebar-nav { flex: 1; padding: 16px 0; }
        .nav-section-label { font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 16px 20px 8px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 14px; transition: all 0.2s; border-left: 3px solid transparent; }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: var(--white); }
        .nav-item.active { background: rgba(232,160,32,0.12); color: var(--gold); border-left-color: var(--gold); }
        .nav-icon { width: 18px; height: 18px; flex-shrink: 0; }
        .sidebar-user { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 10px; }
        .user-avatar { width: 34px; height: 34px; background: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; color: var(--navy); flex-shrink: 0; }
        .user-info .name { font-size: 13px; font-weight: 500; color: var(--white); }
        .user-info .role { font-size: 11px; color: rgba(255,255,255,0.4); }
        .signout-btn { margin-left: auto; background: none; border: none; color: rgba(255,255,255,0.3); cursor: pointer; padding: 4px; transition: color 0.2s; }
        .signout-btn:hover { color: var(--red); }
        svg { display: block; }

        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .topbar { background: var(--white); border-bottom: 1px solid var(--border); padding: 0 32px; height: 60px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .page-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 20px; }
        .page-subtitle { font-size: 13px; color: var(--muted); margin-top: 1px; }
        .btn-primary { background: var(--gold); color: var(--navy); font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; padding: 9px 18px; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s; }
        .btn-primary:hover { background: var(--gold-light); }
        .content { padding: 28px 32px; }

        /* Stats row */
        .mini-stats { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .mini-stat { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 14px 20px; flex: 1; min-width: 120px; }
        .mini-stat .label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; }
        .mini-stat .val { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 28px; margin-top: 4px; }

        /* Toolbar */
        .toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
        .filter-btn { padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border); background: var(--white); font-size: 13px; cursor: pointer; color: var(--muted); transition: all 0.15s; display: flex; align-items: center; gap: 6px; }
        .filter-btn:hover, .filter-btn.active { background: var(--navy); color: var(--white); border-color: var(--navy); }
        .filter-btn.active-gold { background: rgba(232,160,32,0.1); color: var(--gold); border-color: var(--gold); font-weight: 600; }

        .search-wrap { position: relative; margin-left: auto; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); }
        .search-input { padding: 8px 12px 8px 36px; border: 1px solid var(--border); border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 13px; background: var(--white); outline: none; width: 220px; }
        .search-input:focus { border-color: var(--gold); }

        /* Table */
        .table-card { background: var(--white); border-radius: 14px; border: 1px solid var(--border); overflow: hidden; }
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th { text-align: left; font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; padding: 14px 20px; background: #fafbfc; border-bottom: 1px solid var(--border); }
        .orders-table td { padding: 15px 20px; font-size: 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .orders-table tr:last-child td { border-bottom: none; }
        .orders-table tbody tr:hover td { background: #fafcff; }

        .order-id { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; color: var(--navy); }
        .supplier-name { font-weight: 500; }
        .supplier-cat { font-size: 12px; color: var(--muted); }

        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .status-badge.pending { background: rgba(249,115,22,0.12); color: #c2410c; }
        .status-badge.confirmed { background: rgba(59,130,246,0.12); color: #1d4ed8; }
        .status-badge.shipped { background: rgba(139,92,246,0.12); color: #7c3aed; }
        .status-badge.delivered { background: rgba(34,197,94,0.12); color: #15803d; }
        .status-badge.cancelled { background: rgba(239,68,68,0.1); color: #dc2626; }

        .total-val { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; }

        .actions-cell { display: flex; gap: 5px; }
        .btn-sm { padding: 5px 11px; font-size: 12px; font-weight: 500; border-radius: 7px; border: 1px solid var(--border); cursor: pointer; background: var(--white); color: var(--text); text-decoration: none; transition: all 0.15s; }
        .btn-sm:hover { background: var(--navy); color: var(--white); border-color: var(--navy); }
        .btn-sm.red { border-color: transparent; background: rgba(239,68,68,0.1); color: var(--red); }
        .btn-sm.red:hover { background: var(--red); color: var(--white); }

        .empty-state { text-align: center; padding: 60px 32px; color: var(--muted); }
        .empty-state .big-icon { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; margin-bottom: 16px; }

        .alert { padding: 12px 20px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #15803d; }
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
        <div class="nav-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <div class="nav-section-label">Procurement</div>
        <a href="{{ route('suppliers.index') }}" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            Suppliers
        </a>
        <a href="{{ route('orders.index') }}" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Orders
        </a>
        <a href="{{ route('deliveries.index') }}" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            Deliveries
        </a>
        <a href="{{ route('inventory.index') }}" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Inventory
        </a>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
        <div class="user-info">
            <div class="name">{{ auth()->user()->name }}</div>
            <div class="role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
            @csrf
            <button type="submit" class="signout-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div>
            <div class="page-title">Purchase Orders</div>
            <div class="page-subtitle">Manage your procurement orders</div>
        </div>
        <a href="{{ route('orders.create') }}" class="btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            New Order
        </a>
    </header>

    <div class="content">

        @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        <!-- Mini stats -->
        @php
            $allOrders = \App\Models\Order::all();
        @endphp
        <div class="mini-stats">
            <div class="mini-stat">
                <div class="label">Total</div>
                <div class="val">{{ $allOrders->count() }}</div>
            </div>
            <div class="mini-stat">
                <div class="label" style="color:#c2410c">Pending</div>
                <div class="val" style="color:#c2410c">{{ $allOrders->where('status','pending')->count() }}</div>
            </div>
            <div class="mini-stat">
                <div class="label" style="color:#1d4ed8">Confirmed</div>
                <div class="val" style="color:#1d4ed8">{{ $allOrders->where('status','confirmed')->count() }}</div>
            </div>
            <div class="mini-stat">
                <div class="label" style="color:#15803d">Delivered</div>
                <div class="val" style="color:#15803d">{{ $allOrders->where('status','delivered')->count() }}</div>
            </div>
            <div class="mini-stat">
                <div class="label" style="color:#dc2626">Cancelled</div>
                <div class="val" style="color:#dc2626">{{ $allOrders->where('status','cancelled')->count() }}</div>
            </div>
        </div>

        <!-- Filter toolbar -->
        <div class="toolbar">
            <button class="filter-btn active-gold" data-filter="">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                All Status
            </button>
            <button class="filter-btn" data-filter="pending">Pending</button>
            <button class="filter-btn" data-filter="confirmed">Confirmed</button>
            <button class="filter-btn" data-filter="shipped">Shipped</button>
            <button class="filter-btn" data-filter="delivered">Delivered</button>
            <button class="filter-btn" data-filter="cancelled">Cancelled</button>
            <div class="search-wrap">
                <svg class="search-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" id="searchInput" class="search-input" placeholder="Search orders...">
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            @if($orders->count())
            <table class="orders-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr class="order-row" data-status="{{ $order->status }}" data-search="{{ strtolower($order->order_number . ' ' . ($order->supplier->name ?? '')) }}">
                        <td><div class="order-id">#{{ substr($order->order_number, -5) }}</div></td>
                        <td>
                            <div class="supplier-name">{{ $order->supplier->name ?? 'N/A' }}</div>
                            <div class="supplier-cat">{{ $order->supplier->category ?? '' }}</div>
                        </td>
                        <td style="color:var(--muted);font-size:13px">
                            📅 {{ $order->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <span class="status-badge {{ $order->status }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td style="color:var(--muted);font-size:13px">{{ $order->items->count() }} item(s)</td>
                        <td><div class="total-val">₱{{ number_format($order->total_amount, 2) }}</div></td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('orders.show', $order) }}" class="btn-sm">View</a>
                                @if($order->status === 'pending')
                                <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Cancel order {{ $order->order_number }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-sm red">Cancel</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="big-icon">📋</div>
                <p>No orders yet. Create your first purchase order.</p>
                <a href="{{ route('orders.create') }}" class="btn-primary" style="display:inline-flex;margin:0 auto">+ New Order</a>
            </div>
            @endif
        </div>

    </div>
</div>

<script>
    const filterBtns = document.querySelectorAll('.filter-btn');
    const orderRows = document.querySelectorAll('.order-row');
    const searchInput = document.getElementById('searchInput');
    let currentFilter = '';

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active-gold'));
            btn.classList.add('active-gold');
            currentFilter = btn.dataset.filter;
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    function applyFilters() {
        const q = searchInput?.value.toLowerCase() || '';
        orderRows.forEach(row => {
            const matchStatus = !currentFilter || row.dataset.status === currentFilter;
            const matchQ = !q || row.dataset.search.includes(q);
            row.style.display = matchStatus && matchQ ? '' : 'none';
        });
    }
</script>

</body>
</html>