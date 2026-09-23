<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — ConSupMan</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--navy:#0f1f3d;--navy-mid:#1a2f52;--gold:#e8a020;--gold-light:#f5b942;--sidebar-w:240px;--bg:#f4f6fa;--white:#fff;--text:#1a1a2e;--muted:#7a8499;--border:#e2e8f0;--green:#22c55e;--red:#ef4444;--orange:#f97316}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}
        svg{display:block}
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
        .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column}
        .topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
        .page-title{font-family:'Syne',sans-serif;font-weight:700;font-size:20px}
        .page-subtitle{font-size:13px;color:var(--muted);margin-top:1px}
        .topbar-right{display:flex;align-items:center;gap:12px}
        .role-badge{background:var(--navy);color:var(--white);font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;letter-spacing:.5px}
        .btn-primary{background:var(--gold);color:var(--navy);font-family:'Syne',sans-serif;font-weight:700;font-size:13px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s}
        .btn-primary:hover{background:var(--gold-light)}
        .content{padding:28px 32px}
        .welcome-banner{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-mid) 100%);border-radius:16px;padding:24px 32px;color:var(--white);margin-bottom:24px;position:relative;overflow:hidden}
        .welcome-banner::after{content:'🏗️';position:absolute;right:32px;top:50%;transform:translateY(-50%);font-size:56px;opacity:.25}
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
        .chart-label{font-size:11px;color:var(--muted);margin-bottom:12px}
        .bar-chart{display:flex;align-items:flex-end;gap:8px;height:130px}
        .bar-col{display:flex;flex-direction:column;align-items:center;flex:1;gap:3px}
        .bar{width:100%;background:var(--navy);border-radius:4px 4px 0 0;min-height:4px}
        .bar:hover{opacity:.7}
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
</head>
<body>
{{--
    resources/views/dashboard.blade.php
    Uses x-app-layout so the sidebar is always shown (same as all other pages).
--}}
<x-app-layout>
<x-slot name="header">Dashboard</x-slot>
<x-slot name="subheader">Overview of your supplier network</x-slot>

<style>
    /* ── CSS vars already set in layouts/app.blade.php, re-use them here ── */
    .welcome-banner{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-mid) 100%);border-radius:16px;padding:24px 32px;color:var(--white);margin-bottom:24px;position:relative;overflow:hidden}
    .welcome-banner::after{content:'🏗️';position:absolute;right:32px;top:50%;transform:translateY(-50%);font-size:56px;opacity:.25}
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
    .chart-label{font-size:11px;color:var(--muted);margin-bottom:12px}
    .bar-chart{display:flex;align-items:flex-end;gap:8px;height:130px}
    .bar-col{display:flex;flex-direction:column;align-items:center;flex:1;gap:3px}
    .bar{width:100%;background:var(--navy);border-radius:4px 4px 0 0;min-height:4px}
    .bar:hover{opacity:.7}
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

    /* Quick Actions button */
    .btn-primary{background:var(--gold);color:var(--navy);font-family:'Syne',sans-serif;font-weight:700;font-size:13px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s}
    .btn-primary:hover{background:var(--gold-light)}
</style>

{{-- Welcome Banner --}}
<div class="welcome-banner">
    <h2>Welcome back, {{ auth()->user()->name }}.</h2>
    <p>{{ now()->format('l, F d, Y') }} — Here's your supply chain overview.</p>
</div>

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-header">
            <span class="stat-label">Total Suppliers</span>
            <div class="stat-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $supplierCount }}</div>
        <div class="stat-sub">Registered suppliers</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-header">
            <span class="stat-label">Current Orders</span>
            <div class="stat-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $activeOrderCount }}</div>
        <div class="stat-sub"><span class="lo">{{ $orderPending }} pending</span> · <span class="hi">{{ $orderConfirmed }} confirmed</span></div>
    </div>
    <div class="stat-card green">
        <div class="stat-header">
            <span class="stat-label">Deliveries This Week</span>
            <div class="stat-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $deliveriesThisWeek }}</div>
        <div class="stat-sub"><span class="hi">{{ $deliveryTotal }} total</span> all time</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-header">
            <span class="stat-label">Inventory Items</span>
            <div class="stat-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $inventoryCount }}</div>
        <div class="stat-sub">@if($lowStock > 0)<span class="lo">{{ $lowStock }} low stock</span>@else All stocked @endif</div>
    </div>
</div>

{{-- Chart + Suppliers List --}}
<div class="two-col">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Suppliers by Category</div>
            <div style="font-size:12px;color:var(--muted)">Total registered grouped by category</div>
        </div>
        <div class="chart-wrap">
            @php $maxVal = max(1, collect($suppliersByCategory)->max()); @endphp
            <div class="bar-chart">
                @forelse($suppliersByCategory as $cat => $count)
                @php $pct = round(($count / $maxVal) * 100); @endphp
                <div class="bar-col" title="{{ $cat }}: {{ $count }}">
                    <div class="bar-count">{{ $count }}</div>
                    <div class="bar" style="height:{{ max(4,$pct) }}%"></div>
                    <div class="bar-label">{{ \Illuminate\Support\Str::limit($cat, 8) }}</div>
                </div>
                @empty
                <div style="width:100%;text-align:center;color:var(--muted);font-size:13px;padding:20px 0">No suppliers yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">Current Suppliers</div>
            <a href="{{ route('suppliers.index') }}" class="card-link">View all →</a>
        </div>
        <div class="sup-list">
            @forelse($currentSuppliers as $s)
            <div class="sup-row">
                <div class="sup-avatar">🏗️</div>
                <div class="sup-info">
                    <div class="sup-name">{{ $s->name }}</div>
                    <div class="sup-cat">{{ $s->category }}</div>
                </div>
                <span class="sup-badge">Active</span>
            </div>
            @empty
            <div style="padding:32px;text-align:center;color:var(--muted);font-size:14px">
                No suppliers yet. <a href="{{ route('suppliers.create') }}" style="color:var(--gold)">Add one →</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Bottom Row Mini Cards --}}
<div class="bottom-row">
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(34,197,94,.1);color:var(--green)">✅</div>
        <div class="mini-body"><div class="label">Confirmed Orders</div><div class="val">{{ $orderConfirmed }}</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(249,115,22,.1);color:var(--orange)">🚚</div>
        <div class="mini-body"><div class="label">In Transit / Shipped</div><div class="val">{{ $inTransit }}</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(239,68,68,.1);color:var(--red)">⚠️</div>
        <div class="mini-body"><div class="label">Damaged Items</div><div class="val">{{ $damagedItems }}</div></div>
    </div>
    <div class="mini-card">
        <div class="mini-icon" style="background:rgba(15,31,61,.08);color:var(--navy)">📋</div>
        <div class="mini-body"><div class="label">Pending Orders</div><div class="val">{{ $orderPending }}</div></div>
    </div>
</div>

</x-app-layout>