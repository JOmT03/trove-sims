# ============================================================
#  TROVE - FIX Inventory archive view (clean Blade directives)
#     powershell -ExecutionPolicy Bypass -File setup-archive-inv-fix.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
$p = Join-Path $root "resources\views\inventory\index.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Inventory</x-slot>
<x-slot name="subheader">Raw materials &amp; ingredients &mdash; Matina store</x-slot>

@php
    $lowCrit = $inventories->filter(function($i){ $on=(float)$i->quantity_on_hand; $min=(float)$i->minimum_stock; return $on <= $min*1.5; })->count();
    $dmgItems = $inventories->filter(function($i){ return (float)$i->quantity_damaged > 0; })->count();
    $isOwner = auth()->user()->role === 'Owner';
@endphp

<style>
.inv-wrap{max-width:1040px;}
.inv-alert{padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.inv-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.inv-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.seg{display:inline-flex;background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:4px;gap:4px;margin-bottom:16px;}
.seg a{text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:var(--muted);font-family:var(--f-body);display:inline-flex;align-items:center;gap:7px;}
.seg a.on{background:var(--white);color:var(--gold);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.seg .count{font-size:11px;background:var(--border);color:var(--text);border-radius:999px;padding:1px 7px;font-weight:700;}
.seg a.on .count{background:var(--gold);color:#fff;}
.seg svg{width:14px;height:14px;stroke:currentColor;fill:none;}
.inv-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;}
@media(max-width:640px){.inv-kpis{grid-template-columns:1fr;}}
.ikpi{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:16px 18px;box-shadow:0 1px 6px rgba(74,44,23,.06);position:relative;overflow:hidden;cursor:pointer;text-align:left;font-family:var(--f-body);transition:transform .14s ease,box-shadow .14s ease,border-color .14s ease;}
.ikpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;}
.ikpi.k-all::before{background:#15803D;} .ikpi.k-low::before{background:#B45309;} .ikpi.k-dmg::before{background:#C2410C;}
.ikpi:hover{transform:translateY(-2px);box-shadow:0 4px 8px rgba(74,44,23,.08),0 14px 30px rgba(74,44,23,.12);}
.ikpi.active{border-color:var(--gold);box-shadow:0 0 0 2px rgba(217,120,44,.22);}
.ikpi .l{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);}
.ikpi .v{font-family:var(--f-display);font-size:30px;font-weight:800;line-height:1;margin-top:8px;letter-spacing:-1px;font-variant-numeric:tabular-nums;}
.ikpi.k-low .v{color:#B45309;} .ikpi.k-dmg .v{color:#C2410C;}
.ikpi .hint{font-size:11px;color:var(--muted);margin-top:5px;opacity:.7;}
.ikpi.active .hint{opacity:1;color:var(--gold);font-weight:600;}
.inv-bar{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.inv-search{flex:1;min-width:180px;position:relative;}
.inv-search svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:17px;height:17px;stroke:var(--muted);fill:none;}
.inv-search input{width:100%;padding:11px 14px 11px 40px;border:1px solid var(--border);border-radius:999px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);}
.inv-search input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.addbtn{background:var(--gold);color:#fff;border:none;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;white-space:nowrap;}
.inv-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);overflow:visible;}
.inv-card table{width:100%;border-collapse:collapse;font-size:13px;}
.inv-card th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:12px 16px;background:var(--bg);font-weight:700;}
.inv-card td{padding:13px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
.inv-card tbody tr:last-child td{border-bottom:none;}
.inv-card tbody tr{transition:background .12s ease;}
.inv-card tbody tr:hover{background:var(--bg);}
.item{font-weight:700;color:var(--text);}
tr.arch .item{color:var(--muted);}
.cat{color:var(--gold);font-size:12.5px;}
.arch-meta{font-size:11px;color:var(--muted);margin-top:2px;}
.stockcell{display:flex;align-items:center;gap:9px;flex-wrap:wrap;}
.stockval{font-variant-numeric:tabular-nums;font-weight:600;}
.stockval.crit,.stockval.out{color:#C2410C;}
.pill{font-size:10px;font-weight:800;letter-spacing:.3px;padding:2px 8px;border-radius:999px;text-transform:uppercase;}
.pill.ok{background:#E7F3EA;color:#15803D;} .pill.low{background:#FBEBD6;color:#B45309;} .pill.crit{background:#FBE4DA;color:#C2410C;} .pill.out{background:#FBE4DA;color:#C2410C;}
.badge{display:inline-block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;padding:2px 8px;border-radius:999px;background:var(--bg);color:var(--muted);border:1px solid var(--border);}
.dmgnote{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#C2410C;margin-top:3px;font-weight:600;}
.min{color:var(--muted);font-variant-numeric:tabular-nums;}
.actcell{width:46px;padding-right:10px;text-align:right;position:relative;}
.kebab{width:30px;height:30px;border-radius:8px;border:1px solid transparent;background:none;color:var(--muted);font-size:18px;line-height:1;cursor:pointer;display:inline-grid;place-items:center;opacity:0;transition:opacity .12s ease,background .12s ease;}
.inv-card tbody tr:hover .kebab{opacity:1;}
.kebab:hover{background:var(--white);border-color:var(--border);color:var(--text);}
.menu{position:absolute;top:38px;right:10px;background:var(--white);border:1px solid var(--border);border-radius:11px;box-shadow:0 8px 24px rgba(0,0,0,.17);overflow:hidden;z-index:20;min-width:165px;}
.menu a,.menu button{display:flex;width:100%;align-items:center;gap:9px;padding:10px 14px;font-size:13px;font-weight:600;color:var(--text);text-decoration:none;cursor:pointer;background:none;border:none;font-family:var(--f-body);text-align:left;}
.menu a:hover,.menu button:hover{background:var(--bg);}
.menu .warn{color:#C2410C;border-top:1px solid var(--border);}
.menu .good{color:#15803D;}
.menu svg{width:15px;height:15px;stroke:currentColor;fill:none;}
.noresult{display:none;text-align:center;color:var(--muted);padding:34px;font-size:14px;}
.legend{display:flex;gap:16px;flex-wrap:wrap;margin-top:14px;font-size:11.5px;color:var(--muted);}
.legend span{display:inline-flex;align-items:center;gap:6px;}
.legend i{width:9px;height:9px;border-radius:50%;display:inline-block;}
</style>

<div class="inv-wrap">
    @if(session('success'))
        <div class="inv-alert ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="inv-alert err">{{ session('error') }}</div>
    @endif

    <div class="seg">
        <a href="{{ route('inventory.index', ['view' => 'active']) }}" class="{{ $view === 'active' ? 'on' : '' }}">Active <span class="count">{{ $activeCount }}</span></a>
        <a href="{{ route('inventory.index', ['view' => 'archived']) }}" class="{{ $view === 'archived' ? 'on' : '' }}">
            <svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/></svg>
            Archived <span class="count">{{ $archivedCount }}</span>
        </a>
    </div>

    @if($view === 'active')
        <div class="inv-kpis" id="kpis">
            <button class="ikpi k-all active" data-filter="all" onclick="setFilter(this)"><div class="l">Total Items</div><div class="v">{{ $inventories->count() }}</div><div class="hint">showing all</div></button>
            <button class="ikpi k-low" data-filter="low" onclick="setFilter(this)"><div class="l">Low / Critical Stock</div><div class="v">{{ $lowCrit }}</div><div class="hint">click to filter</div></button>
            <button class="ikpi k-dmg" data-filter="dmg" onclick="setFilter(this)"><div class="l">With Damaged Units</div><div class="v">{{ $dmgItems }}</div><div class="hint">click to filter</div></button>
        </div>
    @endif

    <div class="inv-bar">
        <div class="inv-search">
            <svg stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            <input type="text" id="search" placeholder="Search items..." oninput="applyFilters()">
        </div>
        @if($view === 'active')
            @can('admin')
                <a class="addbtn" href="{{ route('inventory.create') }}">+ Add Item</a>
            @endcan
        @endif
    </div>

    <div class="inv-card">
        <table>
            <thead><tr><th>Item</th><th>Category</th><th>Stock</th><th>Min Stock</th><th class="actcell"></th></tr></thead>
            <tbody>
            @forelse($inventories as $inv)
                @php
                    $on=(float)$inv->quantity_on_hand; $min=(float)$inv->minimum_stock; $dmg=(float)$inv->quantity_damaged;
                    $t = $on<=0 ? 'out' : ($on<=$min ? 'crit' : ($on<=$min*1.5 ? 'low' : 'ok'));
                    $labels=['ok'=>'OK','low'=>'Low','crit'=>'Critical','out'=>'Out'];
                    $archived = $view === 'archived';
                @endphp
                <tr data-tier="{{ $t }}" data-dmg="{{ $dmg>0?'1':'0' }}" data-name="{{ strtolower($inv->item_name) }}" class="{{ $archived ? 'arch' : '' }}">
                    <td>
                        <div class="item">{{ $inv->item_name }}
                            @if($archived)
                                <span class="badge">Archived</span>
                            @endif
                        </div>
                        @if($archived && $inv->archived_at)
                            <div class="arch-meta">Archived {{ \Carbon\Carbon::parse($inv->archived_at)->format('M j, Y') }}</div>
                        @endif
                        @if(!$archived && $dmg>0)
                            <div class="dmgnote">&#9888; {{ number_format($dmg,2) }} {{ $inv->unit }} damaged</div>
                        @endif
                    </td>
                    <td><span class="cat">{{ $inv->category }}</span></td>
                    <td>
                        @if($archived)
                            <span class="stockval">{{ number_format($on,2) }} {{ $inv->unit }}</span>
                        @else
                            <div class="stockcell"><span class="stockval {{ in_array($t,['crit','out']) ? $t : '' }}">{{ number_format($on,2) }} {{ $inv->unit }}</span><span class="pill {{ $t }}">{{ $labels[$t] }}</span></div>
                        @endif
                    </td>
                    <td><span class="min">{{ number_format($min,2) }} {{ $inv->unit }}</span></td>
                    <td class="actcell">
                        <button class="kebab" aria-label="Menu" onclick="toggleMenu(event,this)">&#8942;</button>
                        <div class="menu" hidden>
                            @if($archived)
                                @can('admin')
                                    <button class="good" onclick="document.getElementById('rest{{ $inv->id }}').submit();"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 109-9 9 9 0 00-7 3.3M3 4v4h4"/></svg> Restore</button>
                                    <form id="rest{{ $inv->id }}" method="POST" action="{{ route('inventory.restore',$inv) }}" style="display:none">@csrf @method('PATCH')</form>
                                @endcan
                                @if($isOwner)
                                    <button class="warn" onclick="if(confirm('Permanently DELETE this item? This cannot be undone.')){document.getElementById('del{{ $inv->id }}').submit();}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg> Delete permanently</button>
                                    <form id="del{{ $inv->id }}" method="POST" action="{{ route('inventory.destroy',$inv) }}" style="display:none">@csrf @method('DELETE')</form>
                                @endif
                            @else
                                <a href="{{ route('inventory.show',$inv) }}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12C3.7 7.9 7.5 5 12 5s8.3 2.9 9.5 7c-1.2 4.1-5 7-9.5 7s-8.3-2.9-9.5-7z"/></svg> View</a>
                                @can('admin')
                                    <a href="{{ route('inventory.edit',$inv) }}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit</a>
                                    <button class="warn" onclick="if(confirm('Archive this item? It stays in records but is hidden.')){document.getElementById('arch{{ $inv->id }}').submit();}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/></svg> Archive</button>
                                    <form id="arch{{ $inv->id }}" method="POST" action="{{ route('inventory.archive',$inv) }}" style="display:none">@csrf @method('PATCH')</form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--muted)">{{ $view==='archived' ? 'No archived items.' : 'No items yet.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="noresult" id="noresult">No items match your search.</div>
    </div>

    @if($view === 'active')
        <div class="legend">
            <span><i style="background:#15803D"></i> OK</span>
            <span><i style="background:#B45309"></i> Low</span>
            <span><i style="background:#C2410C"></i> Critical</span>
            <span><i style="background:#C2410C"></i> Out</span>
        </div>
    @endif
</div>

<script>
var curFilter='all';
function setFilter(btn){
    document.querySelectorAll('.ikpi').forEach(function(k){k.classList.remove('active');});
    btn.classList.add('active');
    curFilter=btn.getAttribute('data-filter');
    document.querySelectorAll('.ikpi .hint').forEach(function(h){h.textContent='click to filter';});
    btn.querySelector('.hint').textContent = curFilter==='all'?'showing all':'filtered';
    applyFilters();
}
function applyFilters(){
    var term=(document.getElementById('search').value||'').toLowerCase().trim();
    var shown=0;
    document.querySelectorAll('.inv-card tbody tr').forEach(function(r){
        if(!r.getAttribute('data-name')) return;
        var t=r.getAttribute('data-tier'), d=r.getAttribute('data-dmg'), nm=r.getAttribute('data-name')||'';
        var okFilter = curFilter==='all' || (curFilter==='low' && (t==='low'||t==='crit'||t==='out')) || (curFilter==='dmg' && d==='1');
        var okName = nm.indexOf(term)!==-1;
        var show = okFilter && okName;
        r.style.display = show?'':'none'; if(show) shown++;
    });
    var nr=document.getElementById('noresult'); if(nr) nr.style.display = shown===0?'block':'none';
}
function closeMenus(){ document.querySelectorAll('.menu').forEach(function(m){m.hidden=true;}); }
function toggleMenu(e,btn){ e.stopPropagation(); var m=btn.nextElementSibling; var w=m.hidden; closeMenus(); m.hidden=!w; }
document.addEventListener('click', closeMenus);
</script>
</x-app-layout>

'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\inventory\index.blade.php" -ForegroundColor Green
php artisan view:clear
php artisan optimize:clear
Write-Host ""
Write-Host "DONE - open: http://127.0.0.1:8000/inventory?v=6" -ForegroundColor Cyan
