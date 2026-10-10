<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#4A2C17">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Trove">
    <link rel="apple-touch-icon" href="/trove-192.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Trove') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --navy:#4A2C17;
            --navy-mid:#6B3E22;
            --gold:#D9782C;
            --gold-light:#E89552;
            --sidebar-w:240px;
            --topbar-h:60px;
            --bg:#FDF6EC;
            --white:#fff;
            --text:#2E1C10;
            --muted:#8A7460;
            --border:#EDE0D0;
            --green:#22c55e;
            --red:#ef4444;
            --orange:#f97316;
            --f-display:'Bricolage Grotesque',system-ui,sans-serif;
            --f-body:'Plus Jakarta Sans',system-ui,sans-serif;
        }
        body{font-family:var(--f-body);background:var(--bg);color:var(--text);display:flex;min-height:100vh}
        a{text-decoration:none}

        .sidebar{width:var(--sidebar-w);background:var(--navy);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
        .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,.08)}
        .logo-mark{display:flex;align-items:center;gap:10px}
        .logo-icon{width:38px;height:38px;background:var(--gold);border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:var(--f-display);font-weight:800;font-size:14px;color:var(--navy)}
        .logo-text .brand{font-family:var(--f-display);font-weight:800;font-size:17px;color:var(--white);letter-spacing:-.3px}
        .logo-text .sub{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.5px}

        .sidebar-nav{flex:1;padding:16px 0;overflow-y:auto}
        .nav-label{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);padding:16px 20px 8px}
        .nav-item{display:flex;align-items:center;gap:12px;padding:11px 20px;color:rgba(255,255,255,.6);font-size:14px;font-weight:500;transition:all .2s;border-left:3px solid transparent}
        .nav-item:hover{background:rgba(255,255,255,.06);color:var(--white)}
        .nav-item.active{background:rgba(232,160,32,.12);color:var(--gold);border-left-color:var(--gold)}
        .nav-icon{width:18px;height:18px;flex-shrink:0}

        .sidebar-user{padding:16px 20px;border-top:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
        .user-avatar{width:34px;height:34px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--f-display);font-weight:700;font-size:13px;color:var(--navy);flex-shrink:0}
        .user-info .name{font-size:13px;font-weight:600;color:var(--white)}
        .user-info .role{font-size:11px;color:rgba(255,255,255,.4)}
        .signout-btn{margin-left:auto;background:none;border:none;color:rgba(255,255,255,.3);cursor:pointer;padding:4px;transition:color .2s}
        .signout-btn:hover{color:var(--red)}

        .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
        .topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 32px;height:var(--topbar-h);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
        .page-title{font-family:var(--f-display);font-weight:700;font-size:20px;color:var(--text);letter-spacing:-.3px}
        .page-subtitle{font-size:13px;color:var(--muted);margin-top:1px}
        .topbar-right{display:flex;align-items:center;gap:12px}
        .role-badge{background:var(--navy);color:var(--white);font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;letter-spacing:.5px}
        .page-content{padding:28px 32px;flex:1}
    </style>
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
      }
    </script>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-mark">
            <div class="logo-icon">TR</div>
            <div class="logo-text">
                <div class="brand">Trove</div>
                <div class="sub">Food &amp; Cake Shop</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        @if(Route::has('commissions.index'))
            <a href="{{ route('commissions.index') }}" class="nav-item {{ request()->routeIs('commissions.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Commissions
            </a>
        @endif

        <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Products
        </a>

        <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            Inventory
        </a>

        <div class="nav-label">Audit</div>

        @if(Route::has('branch-transfers.index'))
            <a href="{{ route('branch-transfers.index') }}" class="nav-item {{ request()->routeIs('branch-transfers.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h11m0 0l-4-4m4 4l-4 4m9 6H9m0 0l4 4m-4-4l4-4"/></svg>
                Branch Transfers
            </a>
        @endif

        @if(Route::has('expenses.index'))
            <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Expenses
            </a>
        @endif

        @if(Route::has('financial-statement.index'))
    <a href="{{ route('financial-statement.index') }}" class="nav-item {{ request()->routeIs('financial-statement.*') ? 'active' : '' }}">
        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Financial Statement
    </a>
@endif

        @if(Route::has('reports.index'))
            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h2v6m4-10v10M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Reports
            </a>
        @endif

        <div class="nav-label">Account</div>
        <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Settings
        </a>

        @can('admin')
        @if(Route::has('users.index'))
        <div class="nav-label">Administration</div>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 00-1-7.75"/></svg>
            Users
        </a>
        @endif
        @endcan
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar">{{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}</div>
        <div class="user-info">
            <div class="name">{{ trim((auth()->user()->first_name ?? '').' '.(auth()->user()->last_name ?? '')) }}</div>
            <div class="role">{{ ucfirst(auth()->user()->role ?? 'user') }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
            @csrf
            <button type="submit" class="signout-btn" title="Sign Out">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div>
            @isset($header)<div class="page-title">{{ $header }}</div>@endisset
            @isset($subheader)<div class="page-subtitle">{{ $subheader }}</div>@endisset
        </div>
        <div class="topbar-right">
            <span class="role-badge">{{ strtoupper(auth()->user()->role ?? 'USER') }}</span>
        </div>
    </header>

    <div class="page-content">
        {{ $slot ?? '' }}
    </div>
</div>

</body>
</html>