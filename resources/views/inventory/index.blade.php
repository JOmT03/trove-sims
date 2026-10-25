<x-app-layout>
<x-slot name="header">Inventory</x-slot>
<x-slot name="subheader">Raw materials &amp; ingredients &mdash; Matina store</x-slot>

@php
    $lowCrit = $inventories->filter(function($i){ $on=(float)$i->quantity_on_hand; $min=(float)$i->minimum_stock; return $on <= $min*1.5; })->count();
    $dmgItems = collect($stats)->filter(fn($s) => $s['dmg'] > 0)->count();
    $isOwner = auth()->user()->role === 'Owner';
    $periodLabel = ['today' => 'today', 'week' => 'this week', 'month' => 'this month'][$period];
    $q2 = fn($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp

<style>
.inv-wrap{max-width:1180px;}
.inv-alert{padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.inv-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.inv-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.seg{display:inline-flex;background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:4px;gap:4px;margin-bottom:16px;}
.seg a{text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:var(--muted);font-family:var(--f-body);display:inline-flex;align-items:center;gap:7px;}
.seg a.on{background:var(--white);color:var(--gold);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.seg .count{font-size:11px;background:var(--border);color:var(--text);border-radius:999px;padding:1px 7px;font-weight:700;}
.seg a.on .count{background:var(--gold);color:#fff;}
.seg svg{width:14px;height:14px;stroke:currentColor;fill:none;}
.inv-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:12px;margin-bottom:18px;}
.ikpi{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:16px 18px;box-shadow:0 1px 6px rgba(74,44,23,.06);position:relative;overflow:hidden;cursor:pointer;text-align:left;font-family:var(--f-body);transition:transform .14s ease,box-shadow .14s ease,border-color .14s ease;}
.ikpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;}
.ikpi.k-all::before{background:#15803D;} .ikpi.k-val::before{background:#4A2C17;} .ikpi.k-rs::before{background:#D9782C;}
.ikpi.info{cursor:default;} .ikpi.info:hover{transform:none;box-shadow:0 1px 6px rgba(74,44,23,.06);}
.ikpi.k-val .v,.ikpi.k-rs .v{font-size:24px;} .ikpi.k-low::before{background:#B45309;} .ikpi.k-dmg::before{background:#C2410C;}
.ikpi:hover{transform:translateY(-2px);box-shadow:0 4px 8px rgba(74,44,23,.08),0 14px 30px rgba(74,44,23,.12);}
.ikpi.active{border-color:var(--gold);box-shadow:0 0 0 2px rgba(217,120,44,.22);}
.ikpi .l{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);}
.ikpi .v{font-family:var(--f-display);font-size:30px;font-weight:800;line-height:1;margin-top:8px;letter-spacing:-1px;font-variant-numeric:tabular-nums;}
.ikpi.k-low .v{color:#B45309;} .ikpi.k-dmg .v{color:#C2410C;}
.ikpi .hint{font-size:11px;color:var(--muted);margin-top:5px;opacity:.7;}
.ikpi.active .hint{opacity:1;color:var(--gold);font-weight:600;}
.inv-bar{display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap;}
.pchips{display:inline-flex;gap:3px;background:var(--bg);border:1px solid var(--border);padding:3px;border-radius:999px;}
.pchips a{padding:7px 12px;border-radius:999px;font-size:12px;font-weight:700;color:var(--muted);text-decoration:none;white-space:nowrap;}
.pchips a.on{background:var(--white);color:var(--text);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.inv-search{flex:1;min-width:180px;position:relative;}
.inv-search svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:17px;height:17px;stroke:var(--muted);fill:none;}
.inv-search input{width:100%;padding:11px 14px 11px 40px;border:1px solid var(--border);border-radius:999px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);}
.inv-search input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.addbtn{background:var(--gold);color:#fff;border:none;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;white-space:nowrap;}
.inv-catfilter{padding:10px 14px;border:1px solid var(--border);border-radius:999px;font-size:13px;background:var(--white);color:var(--text);font-family:var(--f-body);cursor:pointer;}
.inv-catfilter:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
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
.inv-card th.r,.inv-card td.r{text-align:right;}
.num{font-variant-numeric:tabular-nums;white-space:nowrap;}
.mv-in{color:#15803D;font-weight:700;} .mv-out{color:#C2410C;font-weight:700;} .mv-z{color:var(--muted);opacity:.6;}
.un{color:var(--muted);font-size:11.5px;font-weight:500;}
.costsub{display:block;font-size:11px;color:var(--muted);}
.inv-card tbody tr[data-id]{cursor:pointer;}
@media(max-width:900px){.c-cat,.c-min{display:none;}}
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
    @if($errors->any())
        <div class="inv-alert err">{{ $errors->first() }}</div>
    @endif

    @include('inventory._tabs', ['current' => $view])

    @if($view === 'active')
        <div class="inv-kpis" id="kpis">
            <button class="ikpi k-all active" data-filter="all" onclick="setFilter(this)"><div class="l">Total Items</div><div class="v">{{ $inventories->count() }}</div><div class="hint">showing all</div></button>
            <button class="ikpi k-low" data-filter="low" onclick="setFilter(this)"><div class="l">Low / Critical Stock</div><div class="v">{{ $lowCrit }}</div><div class="hint">click to filter</div></button>
            <button class="ikpi k-dmg" data-filter="dmg" onclick="setFilter(this)"><div class="l">Damaged {{ $periodLabel }}</div><div class="v">{{ $dmgItems }}</div><div class="hint">click to filter</div></button>
            <div class="ikpi k-val info"><div class="l">Stock value (FIFO)</div><div class="v">&#8369;{{ number_format($stockValue, 2) }}</div><div class="hint">on hand, at the cost of each lot</div></div>
            <div class="ikpi k-rs info"><div class="l">Restocked {{ $periodLabel }}</div><div class="v">&#8369;{{ number_format($restockedValue, 2) }}</div><div class="hint">total purchases</div></div>
        </div>
    @endif

    <div class="inv-bar">
        <div class="inv-search">
            <svg stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            <input type="text" id="search" placeholder="Search items..." oninput="applyFilters()">
        </div>
        <select id="catfilter" class="inv-catfilter" onchange="applyFilters()"><option value="">All Categories</option></select>
        <nav class="pchips" aria-label="Period for In and Out">
            @foreach(['today' => 'Today', 'week' => 'This week', 'month' => 'This month'] as $pk => $pl)
                <a href="{{ route('inventory.index', ['view' => $view, 'period' => $pk]) }}" class="{{ $period === $pk ? 'on' : '' }}">{{ $pl }}</a>
            @endforeach
        </nav>
        @if($view === 'active')
            @can('admin')
                <a class="addbtn" href="{{ route('inventory.low-stock-report') }}" style="background:#fff;color:var(--gold);border:1px solid var(--border);">Restock List</a>
                <button type="button" class="addbtn" onclick="openRestock()">+ Restock</button>
                <button type="button" class="addbtn" onclick="openAddInv()">+ Add Item</button>
            @endcan
        @endif
    </div>

    <div class="inv-card">
        <table>
            <thead><tr><th>Item</th><th class="c-cat">Category</th><th>Stock</th><th class="c-min">Min Stock</th><th class="r">In (+)</th><th class="r">Out (&minus;)</th><th class="r">Cost / unit</th><th class="r">Stock value</th><th class="actcell"></th></tr></thead>
            <tbody>
            @forelse($inventories as $inv)
                @php
                    $on=(float)$inv->quantity_on_hand; $min=(float)$inv->minimum_stock; $dmg=(float)$inv->quantity_damaged;
                    $t = $on<=0 ? 'out' : ($on<=$min ? 'crit' : ($on<=$min*1.5 ? 'low' : 'ok'));
                    $labels=['ok'=>'OK','low'=>'Low','crit'=>'Critical','out'=>'Out'];
                    $archived = $view === 'archived';
                    $st = $stats[$inv->id];
                    $unit1 = preg_replace('/s$/', '', $inv->unit);
                @endphp
                <tr data-id="{{ $inv->id }}" onclick="if(!event.target.closest('.actcell'))openLots({{ $inv->id }})" data-tier="{{ $t }}" data-dmg="{{ $st['dmg']>0?'1':'0' }}" data-name="{{ strtolower($inv->item_name) }}" data-cat="{{ strtolower($inv->category) }}" class="{{ $archived ? 'arch' : '' }}">
                    <td>
                        <div class="item">{{ $inv->item_name }}
                            @if($archived)
                                <span class="badge">Archived</span>
                            @endif
                        </div>
                        @if($archived && $inv->archived_at)
                            <div class="arch-meta">Archived {{ \Carbon\Carbon::parse($inv->archived_at)->format('M j, Y') }}</div>
                        @endif
                        @if(!$archived && $st['dmg'] > 0)
                            <div class="dmgnote">&#9888; {{ $q2($st['dmg']) }} {{ $inv->unit }} damaged {{ $periodLabel }}@if($st['dmgWhy']) &middot; {{ \Illuminate\Support\Str::limit($st['dmgWhy'], 40) }}@endif</div>
                        @endif
                    </td>
                    <td class="c-cat"><span class="cat">{{ $inv->category }}</span></td>
                    <td>
                        @if($archived)
                            <span class="stockval">{{ number_format($on,2) }} {{ $inv->unit }}</span>
                        @else
                            <div class="stockcell"><span class="stockval {{ in_array($t,['crit','out']) ? $t : '' }}">{{ number_format($on,2) }} {{ $inv->unit }}</span><span class="pill {{ $t }}">{{ $labels[$t] }}</span></div>
                        @endif
                    </td>
                    <td class="c-min"><span class="min">{{ number_format($min,2) }} {{ $inv->unit }}</span></td>
                    <td class="r num">{!! $st['in'] > 0 ? '<span class="mv-in">+'.$q2($st['in']).'</span> <span class="un">'.e($inv->unit).'</span>' : '<span class="mv-z">&mdash;</span>' !!}</td>
                    <td class="r num">{!! $st['out'] > 0 ? '<span class="mv-out">&minus;'.$q2($st['out']).'</span> <span class="un">'.e($inv->unit).'</span>' : '<span class="mv-z">&mdash;</span>' !!}</td>
                    <td class="r num">@if($st['cost'] !== null)&#8369;{{ number_format($st['cost'], 2) }}<span class="costsub">per {{ $unit1 }}</span>@else<span class="mv-z">&mdash;</span>@endif</td>
                    <td class="r num" style="font-weight:700;">&#8369;{{ number_format($st['value'], 2) }}</td>
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
                                <button type="button" onclick="openLots({{ $inv->id }})"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg> FIFO lots &amp; movements</button>
                                <a href="{{ route('inventory.show',$inv) }}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12C3.7 7.9 7.5 5 12 5s8.3 2.9 9.5 7c-1.2 4.1-5 7-9.5 7s-8.3-2.9-9.5-7z"/></svg> View</a>
                                @can('admin')
                                    <button type="button" onclick="openEdit({{ $inv->id }}, 'details')"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit</button>
                                    <button type="button" onclick="openEdit({{ $inv->id }}, 'damage')"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg> Record damaged / spoiled</button>
                                    <button class="warn" onclick="if(confirm('Archive this item? It stays in records but is hidden.')){document.getElementById('arch{{ $inv->id }}').submit();}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/></svg> Archive</button>
                                    <form id="arch{{ $inv->id }}" method="POST" action="{{ route('inventory.archive',$inv) }}" style="display:none">@csrf @method('PATCH')</form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--muted)">{{ $view==='archived' ? 'No archived items.' : 'No items yet.' }}</td></tr>
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
    var _cf=document.getElementById('catfilter'); window.__catSel=_cf?_cf.value.toLowerCase():'';
    var shown=0;
    document.querySelectorAll('.inv-card tbody tr').forEach(function(r){
        if(!r.getAttribute('data-name')) return;
        var t=r.getAttribute('data-tier'), d=r.getAttribute('data-dmg'), nm=r.getAttribute('data-name')||'';
        var okFilter = curFilter==='all' || (curFilter==='low' && (t==='low'||t==='crit'||t==='out')) || (curFilter==='dmg' && d==='1');
        var okName = nm.indexOf(term)!==-1;
        var okCat = !window.__catSel || (r.getAttribute('data-cat')||'')===window.__catSel;
        var show = okFilter && okName && okCat;
        r.style.display = show?'':'none'; if(show) shown++;
    });
    var nr=document.getElementById('noresult'); if(nr) nr.style.display = shown===0?'block':'none';
}
function closeMenus(){ document.querySelectorAll('.menu').forEach(function(m){m.hidden=true;}); }
function toggleMenu(e,btn){ e.stopPropagation(); var m=btn.nextElementSibling; var w=m.hidden; closeMenus(); m.hidden=!w; }
document.addEventListener('click', closeMenus);
(function(){var sel=document.getElementById("catfilter");if(!sel)return;var seen={};document.querySelectorAll(".inv-card tbody tr[data-cat]").forEach(function(r){var k=r.getAttribute("data-cat");if(!k||seen[k])return;seen[k]=1;var o=document.createElement("option");o.value=k;o.textContent=k.replace(/\b\w/g,function(m){return m.toUpperCase();});sel.appendChild(o);});})();
</script>

@php
    $jf = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE;
    $invData = $inventories->map(fn($i) => [
        'id' => (int) $i->id, 'n' => $i->item_name, 'c' => $i->category, 'u' => $i->unit,
        'upd' => route('inventory.update', $i), 'adj' => route('inventory.adjust', $i),
        'on' => (float) $i->quantity_on_hand, 'min' => (float) $i->minimum_stock,
        'last' => $stats[$i->id]['last'], 'cost' => $stats[$i->id]['cost'], 'value' => $stats[$i->id]['value'],
        'lots' => $stats[$i->id]['lots'], 'moves' => $recentLogs->get($i->id, collect())->values(),
    ])->values();
    $oldRows = collect(old('items', []))->map(fn($r) => ['id' => (string) ($r['inventory_id'] ?? ''), 'q' => (string) ($r['quantity'] ?? ''), 'p' => (string) ($r['unit_price'] ?? '')])->values();
@endphp

<!-- Restock + FIFO lots modal -->
<div class="aim-bg" id="rsBg">
  <div class="aim-modal rs-modal" role="dialog" aria-modal="true" aria-labelledby="rsTitle">
    <div class="aim-head">
      <div><h3 id="rsTitle"></h3><p class="aim-sub" id="rsSub"></p></div>
      <button type="button" class="aim-x" onclick="closeRs()" aria-label="Close">&times;</button>
    </div>
    <div id="rsBody"></div>
  </div>
</div>
<style>
.aim-modal.rs-modal{max-width:800px;max-height:calc(100vh - 32px);display:flex;flex-direction:column;}
.rs-modal form{display:flex;flex-direction:column;min-height:0;flex:1;}
.rs-scroll{padding:18px 20px;overflow:auto;}
.rs-top{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;}
.rs-grid{display:grid;grid-template-columns:minmax(150px,1.7fr) 120px 120px 110px 26px;gap:8px;align-items:start;}
.rs-grid.h{font-size:10.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
.rs-row{margin-bottom:10px;}
.rs-q{position:relative;} .rs-q input{padding-right:46px!important;} .rs-q .u{position:absolute;right:10px;top:11px;font-size:12px;color:var(--muted);}
.rs-lt{padding-top:10px;text-align:right;font-weight:700;font-variant-numeric:tabular-nums;}
.rs-hint{font-size:11px;color:var(--muted);margin-top:4px;}
.rs-rm{background:none;border:none;color:var(--muted);font-size:20px;cursor:pointer;padding:6px 0;line-height:1;}
.rs-add{border:1.5px dashed var(--border);background:none;color:var(--muted);border-radius:9px;padding:8px 12px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:var(--f-body);}
.rs-err{background:#FBE4DA;color:#991b1b;border-radius:9px;padding:9px 12px;font-size:12.5px;margin-top:10px;}
.rs-foot{justify-content:space-between;align-items:center;flex-wrap:wrap;}
.rs-total{font-size:13px;color:var(--muted);} .rs-total b{font-family:var(--f-display);font-size:22px;color:var(--text);margin-left:6px;font-variant-numeric:tabular-nums;}
.rs-kv{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;}
.rs-kv div{background:var(--bg);border-radius:10px;padding:8px 12px;} .rs-kv span{display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);} .rs-kv b{font-size:15px;font-variant-numeric:tabular-nums;}
.rs-modal h4{font-size:13px;margin:14px 0 6px;}
.rs-t{width:100%;border-collapse:collapse;font-size:12.5px;}
.rs-t th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);background:var(--bg);padding:8px 10px;}
.rs-t td{padding:8px 10px;border-top:1px solid var(--border);font-variant-numeric:tabular-nums;}
.rs-t .r{text-align:right;} .rs-t tr.done td{color:var(--muted);}
.ed-tabs{display:flex;gap:4px;background:var(--bg);border:1px solid var(--border);padding:3px;border-radius:10px;margin-bottom:16px;}
.ed-tabs button{flex:1;border:0;background:none;padding:8px;border-radius:8px;font-weight:700;font-size:13px;color:var(--muted);cursor:pointer;font-family:var(--f-body);}
.ed-tabs button.on{background:var(--white);color:var(--text);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.ed-chips{display:flex;gap:6px;flex-wrap:wrap;}
.ed-chip{border:1px solid var(--border);background:var(--white);color:var(--muted);border-radius:999px;padding:6px 12px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.ed-chip.on{background:#FBE4DA;border-color:#C2410C;color:#C2410C;}
@media(max-width:640px){.rs-grid{grid-template-columns:1fr 1fr;}.rs-grid.h{display:none;}.rs-top{grid-template-columns:1fr;}}
</style>
<script>
const INV = {!! json_encode($invData, $jf) !!};
const RS_OLD = {!! json_encode($oldRows, $jf) !!};
const RS_URL = "{{ route('inventory.restock') }}", RS_CSRF = "{{ csrf_token() }}";
const RS_TODAY = "{{ date('Y-m-d') }}", RS_DATE = {!! json_encode(old('restock_date', date('Y-m-d')), $jf) !!}, RS_FROM = {!! json_encode(old('supplier', ''), $jf) !!};
const peso = n => '\u20B1' + Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
const q2 = n => Number(n).toLocaleString('en-US', {maximumFractionDigits: 2});
const esc = s => String(s == null ? '' : s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const invById = id => INV.find(i => i.id == id);
function openRsModal(title, sub, html){
    document.getElementById('rsTitle').textContent = title; document.getElementById('rsSub').textContent = sub;
    document.getElementById('rsBody').innerHTML = html; document.getElementById('rsBg').classList.add('show');
}
function closeRs(){ document.getElementById('rsBg').classList.remove('show'); }
document.getElementById('rsBg').addEventListener('click', function(e){ if(e.target === this) closeRs(); });
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeRs(); });

/* ---------- Restock: several items in one form ---------- */
let RS = [];
function openRestock(rows){
    RS = rows && rows.length ? rows : [{id: '', q: '', p: ''}];
    openRsModal('Restock', 'Add every item you bought. Each line is also recorded in Expenses.',
        '<form method="POST" action="' + RS_URL + '" onsubmit="return rsCheck()"><input type="hidden" name="_token" value="' + RS_CSRF + '">'
        + '<div class="rs-scroll"><div class="rs-top"><div class="aim-fg" style="margin:0"><label for="rsDate">Date received *</label><input type="date" id="rsDate" name="restock_date" value="' + esc(RS_DATE) + '" max="' + RS_TODAY + '" required></div>'
        + '<div class="aim-fg" style="margin:0"><label for="rsFrom">Bought from</label><input type="text" id="rsFrom" name="supplier" maxlength="100" value="' + esc(RS_FROM) + '" placeholder="e.g. Gaisano Mall"></div></div>'
        + '<div class="rs-grid h"><span>Item</span><span>Quantity</span><span>Price per unit</span><span style="text-align:right">Total</span><span></span></div>'
        + '<div id="rsRows"></div><button type="button" class="rs-add" onclick="RS.push({id:\'\',q:\'\',p:\'\'});rsDraw()">+ Add item</button><div id="rsErr"></div></div>'
        + '<div class="aim-foot rs-foot"><div class="rs-total">Total amount<b id="rsTotal">' + peso(0) + '</b></div><div style="display:flex;gap:10px"><button type="button" class="aim-ghost" onclick="closeRs()">Cancel</button><button class="aim-primary">Save restock</button></div></div></form>');
    rsDraw();
}
const rsLine = r => { const q = parseFloat(r.q), p = parseFloat(r.p); return q > 0 && p > 0 ? q * p : 0; };
function rsDraw(){
    const opts = sel => '<option value="">Select an item</option>' + INV.map(i => '<option value="' + i.id + '"' + (sel == i.id ? ' selected' : '') + '>' + esc(i.n) + '</option>').join('');
    document.getElementById('rsRows').innerHTML = RS.map((r, k) => {
        const it = invById(r.id);
        return '<div class="rs-grid rs-row"><div><select name="items[' + k + '][inventory_id]" aria-label="Item" onchange="rsPick(' + k + ',this.value)">' + opts(r.id) + '</select>'
            + (it ? '<div class="rs-hint">On hand ' + q2(it.on) + ' ' + esc(it.u) + (it.last !== null ? ' &middot; last price ' + peso(it.last) : '') + '</div>' : '') + '</div>'
            + '<div class="rs-q"><input type="number" name="items[' + k + '][quantity]" min="0.01" step="0.01" inputmode="decimal" aria-label="Quantity" value="' + esc(r.q) + '" oninput="RS[' + k + '].q=this.value;rsTot(' + k + ')"><span class="u">' + (it ? esc(it.u) : '') + '</span></div>'
            + '<div><input type="number" name="items[' + k + '][unit_price]" min="0.01" step="0.01" inputmode="decimal" aria-label="Price per unit" value="' + esc(r.p) + '" oninput="RS[' + k + '].p=this.value;rsTot(' + k + ')"></div>'
            + '<div class="rs-lt" id="rsLt' + k + '">' + peso(rsLine(r)) + '</div>'
            + '<button type="button" class="rs-rm" aria-label="Remove row" onclick="RS.splice(' + k + ',1);if(!RS.length)RS.push({id:\'\',q:\'\',p:\'\'});rsDraw()">&times;</button></div>';
    }).join('');
    document.getElementById('rsTotal').textContent = peso(RS.reduce((a, r) => a + rsLine(r), 0));
}
function rsPick(k, v){ RS[k].id = v; const it = invById(v); if(it && !RS[k].p && it.last !== null) RS[k].p = String(it.last); rsDraw(); }
function rsTot(k){ document.getElementById('rsLt' + k).textContent = peso(rsLine(RS[k])); document.getElementById('rsTotal').textContent = peso(RS.reduce((a, r) => a + rsLine(r), 0)); }
function rsCheck(){
    const err = m => { document.getElementById('rsErr').innerHTML = '<div class="rs-err">' + esc(m) + '</div>'; return false; };
    const rows = RS.filter(r => r.id || r.q || r.p);
    if(!rows.length) return err('Add at least one item.');
    const seen = {};
    for(const r of rows){
        const it = invById(r.id);
        if(!it) return err('Pick an item on every row, or remove the empty row.');
        if(seen[r.id]) return err(it.n + ' is listed twice. Put it on one row.');
        seen[r.id] = 1;
        if(!(parseFloat(r.q) > 0)) return err('Enter the quantity for ' + it.n + '.');
        if(!(parseFloat(r.p) > 0)) return err('Enter the price per unit for ' + it.n + '.');
    }
    if(rows.length !== RS.length){ RS = rows; rsDraw(); }
    return true;
}

/* ---------- Edit an item / record damaged or spoiled units ---------- */
const CATS = ['Baking Essentials', 'Dairy & Eggs', 'Flavoring & Fillings', 'Packaging', 'Coffee & Beverage', 'Other'];
const UNITS = ['g', 'kg', 'ml', 'liters', 'pcs', 'dozen', 'pack'];
const REASONS = ['Broken', 'Spoiled / expired', 'Contaminated', 'Other'];
function selOpts(list, cur){ const l = list.indexOf(cur) < 0 && cur ? [cur].concat(list) : list; return l.map(v => '<option' + (v === cur ? ' selected' : '') + '>' + esc(v) + '</option>').join(''); }
function openEdit(id, tab){
    const it = invById(id); if(!it) return;
    const tabs = '<div class="ed-tabs"><button type="button" class="' + (tab === 'details' ? 'on' : '') + '" onclick="openEdit(' + id + ',\'details\')">Details</button>'
        + '<button type="button" class="' + (tab === 'damage' ? 'on' : '') + '" onclick="openEdit(' + id + ',\'damage\')">Damaged / spoiled</button></div>';
    let h;
    if(tab === 'details'){
        h = '<form method="POST" action="' + it.upd + '"><input type="hidden" name="_token" value="' + RS_CSRF + '"><input type="hidden" name="_method" value="PUT">'
            + '<div class="rs-scroll">' + tabs
            + '<div class="aim-fg"><label for="edName">Item name *</label><input id="edName" name="item_name" maxlength="255" required value="' + esc(it.n) + '"></div>'
            + '<div class="aim-fg2"><div class="aim-fg"><label for="edCat">Category *</label><select id="edCat" name="category" required>' + selOpts(CATS, it.c) + '</select></div>'
            + '<div class="aim-fg"><label for="edUnit">Unit *</label><select id="edUnit" name="unit" required>' + selOpts(UNITS, it.u) + '</select></div></div>'
            + '<div class="aim-fg2"><div class="aim-fg"><label for="edOn">Stock on hand *</label><input id="edOn" type="number" name="quantity_on_hand" step="0.01" min="0" required value="' + it.on + '"><div class="rs-hint">Change only to correct a count. The change is logged. Use Restock to add stock.</div></div>'
            + '<div class="aim-fg"><label for="edMin">Min stock (alert) *</label><input id="edMin" type="number" name="minimum_stock" step="0.01" min="0" required value="' + it.min + '"></div></div>'
            + '</div><div class="aim-foot"><button type="button" class="aim-ghost" onclick="closeRs()">Cancel</button><button class="aim-primary">Save changes</button></div></form>';
    } else {
        h = '<form method="POST" action="' + it.adj + '" onsubmit="return dmgCheck(' + it.on + ')"><input type="hidden" name="_token" value="' + RS_CSRF + '"><input type="hidden" name="_method" value="PATCH">'
            + '<input type="hidden" name="type" value="damaged"><input type="hidden" name="notes" id="dmgNotes">'
            + '<div class="rs-scroll">' + tabs
            + '<p class="rs-hint" style="margin:0 0 12px">Damaged or spoiled units are taken out of the stock and counted under Damaged. On hand now: <b>' + q2(it.on) + ' ' + esc(it.u) + '</b></p>'
            + '<div class="aim-fg"><label for="dmgQty">Quantity damaged *</label><div class="rs-q"><input id="dmgQty" type="number" name="quantity" min="0.01" step="0.01" max="' + it.on + '" inputmode="decimal" required><span class="u">' + esc(it.u) + '</span></div></div>'
            + '<div class="aim-fg"><label>Reason *</label><div class="ed-chips">' + REASONS.map((r, k) => '<button type="button" class="ed-chip' + (k === 0 ? ' on' : '') + '" onclick="dmgPick(this)">' + esc(r) + '</button>').join('') + '</div></div>'
            + '<div class="aim-fg"><label for="dmgWhy">Remarks *</label><textarea id="dmgWhy" rows="2" maxlength="400" placeholder="e.g., 4 eggs cracked during delivery"></textarea></div>'
            + '<div id="dmgErr"></div></div>'
            + '<div class="aim-foot"><button type="button" class="aim-ghost" onclick="closeRs()">Cancel</button><button class="aim-primary" style="background:#C2410C">Record damage</button></div></form>';
    }
    openRsModal(tab === 'details' ? 'Edit ' + it.n : 'Damaged / spoiled: ' + it.n, it.c, h);
}
function dmgPick(b){ document.querySelectorAll('.ed-chip').forEach(c => c.classList.remove('on')); b.classList.add('on'); }
function dmgCheck(on){
    const err = m => { document.getElementById('dmgErr').innerHTML = '<div class="rs-err">' + esc(m) + '</div>'; return false; };
    const q = parseFloat(document.getElementById('dmgQty').value), why = document.getElementById('dmgWhy').value.trim();
    const reason = (document.querySelector('.ed-chip.on') || {}).textContent || 'Other';
    if(!(q > 0)) return err('Enter how many units were damaged.');
    if(q > on) return err('Only ' + q2(on) + ' is on hand.');
    if(why.length < 3) return err('Write a short remark about what happened.');
    document.getElementById('dmgNotes').value = reason + ': ' + why;
    return true;
}

/* ---------- FIFO lots and movements of one item ---------- */
const MV = {received: ['Restock', 'mv-in', '+'], used: ['Used in production', 'mv-out', '\u2212'], damaged: ['Damaged', 'mv-out', '\u2212'], adjustment: ['Adjustment', '', '']};
function openLots(id){
    const it = invById(id); if(!it) return;
    const u1 = it.u.replace(/s$/, '');
    let h = '<div class="rs-scroll"><div class="rs-kv"><div><span>On hand</span><b>' + q2(it.on) + ' ' + esc(it.u) + '</b></div><div><span>Min stock</span><b>' + q2(it.min) + ' ' + esc(it.u) + '</b></div>'
        + '<div><span>Cost / ' + esc(u1) + '</span><b>' + (it.cost !== null ? peso(it.cost) : '&mdash;') + '</b></div><div><span>Stock value</span><b>' + peso(it.value) + '</b></div></div>'
        + '<h4>FIFO lots (the oldest is used first)</h4>';
    h += it.lots.length
        ? '<div style="overflow-x:auto"><table class="rs-t"><thead><tr><th>Received</th><th class="r">In</th><th class="r">Used</th><th class="r">Left</th><th class="r">Cost / ' + esc(u1) + '</th><th class="r">Value left</th></tr></thead><tbody>'
          + it.lots.map(l => '<tr class="' + (l.left <= 0 ? 'done' : '') + '"><td>' + esc(l.d) + (l.left <= 0 ? ' &middot; used up' : '') + '</td><td class="r mv-in">+' + q2(l.q) + '</td><td class="r">' + (l.used > 0 ? '\u2212' + q2(l.used) : '&mdash;') + '</td><td class="r"><b>' + q2(l.left) + '</b></td><td class="r">' + peso(l.cost) + '</td><td class="r">' + peso(l.value) + '</td></tr>').join('')
          + '</tbody></table></div>'
        : '<p class="rs-hint">No cost lots yet. The next restock starts one.</p>';
    h += '<h4>Recent movements</h4>' + (it.moves.length
        ? '<div style="overflow-x:auto"><table class="rs-t"><thead><tr><th>Date</th><th>Type</th><th class="r">Qty</th><th>Note</th></tr></thead><tbody>'
          + it.moves.map(m => { const t = MV[m.t] || [m.t, '', '']; return '<tr><td>' + esc(m.d) + '</td><td>' + esc(t[0]) + '</td><td class="r ' + t[1] + '">' + t[2] + q2(m.q) + ' ' + esc(it.u) + '</td><td>' + esc(m.n) + '</td></tr>'; }).join('')
          + '</tbody></table></div>'
        : '<p class="rs-hint">No movements yet.</p>');
    openRsModal(it.n, it.c, h + '</div>');
}
@if(old('items'))
openRestock(RS_OLD);
@endif
</script>

<!-- Add Inventory Item modal -->
@php $aimSites = \App\Models\Site::orderBy('site_name')->get(); $aimUserSite = auth()->user()->site_id; @endphp
<div class="aim-bg" id="addInvBg">
  <div class="aim-modal">
    <div class="aim-head">
      <div>
        <h3>Add inventory item</h3>
        <p class="aim-sub">A compact modal &mdash; no full-page dead space.</p>
      </div>
      <button type="button" class="aim-x" onclick="closeAddInv()" aria-label="Close">&times;</button>
    </div>
    <form method="POST" action="{{ route('inventory.store') }}">
      @csrf
      <div class="aim-body">
        <div class="aim-fg">
          <label>Item name <span class="aim-req">*</span></label>
          <input type="text" name="item_name" placeholder="e.g. All-Purpose Flour" required autofocus>
        </div>
        <div class="aim-fg2">
          <div class="aim-fg">
            <label>Category <span class="aim-req">*</span></label>
            <select name="category" required>
              <option value="">&mdash; Select &mdash;</option>
              @foreach(['Baking Essentials','Dairy & Eggs','Flavoring & Fillings','Packaging','Coffee & Beverage','Other'] as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
              @endforeach
            </select>
          </div>
          <div class="aim-fg">
            <label>Unit <span class="aim-req">*</span></label>
            <select name="unit" required>
              <option value="">&mdash; Select &mdash;</option>
              @foreach(['g','kg','ml','liters','pcs','dozen','pack'] as $u)
                <option value="{{ $u }}">{{ $u }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="aim-fg2">
          <div class="aim-fg">
            <label>Starting qty <span class="aim-req">*</span></label>
            <input type="number" name="quantity_on_hand" step="0.01" min="0" value="0" required>
          </div>
          <div class="aim-fg">
            <label>Unit cost (&#8369;)</label>
            <input type="number" name="unit_cost" step="0.01" min="0" value="0" placeholder="0.00">
          </div>
        </div>
        <div class="aim-fg2">
          <div class="aim-fg">
            <label>Min stock (alert) <span class="aim-req">*</span></label>
            <input type="number" name="minimum_stock" step="0.01" min="0" value="0" required>
          </div>
          <div class="aim-fg">
            <label>Site</label>
            <select name="site_id">
              <option value="">&mdash; None &mdash;</option>
              @foreach($aimSites as $s)
                <option value="{{ $s->id }}" {{ $aimUserSite == $s->id ? 'selected' : '' }}>{{ $s->site_name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="aim-fg">
          <label>Notes</label>
          <textarea name="notes" rows="2"></textarea>
        </div>
      </div>
      <div class="aim-foot">
        <button type="button" class="aim-ghost" onclick="closeAddInv()">Cancel</button>
        <button type="submit" class="aim-primary">Add item</button>
      </div>
    </form>
  </div>
</div>

<style>
.aim-bg{display:none;position:fixed;inset:0;background:rgba(46,28,16,.55);z-index:70;align-items:center;justify-content:center;padding:16px;}
.aim-bg.show{display:flex;}
.aim-modal{background:var(--white);border-radius:18px;max-width:500px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.35);}
.aim-head{display:flex;align-items:flex-start;justify-content:space-between;padding:18px 20px;border-bottom:1px solid var(--border);}
.aim-head h3{font-family:var(--f-display);font-size:18px;margin:0;}
.aim-sub{font-size:12px;color:var(--muted);margin:3px 0 0;}
.aim-x{background:none;border:none;color:var(--muted);font-size:22px;line-height:1;cursor:pointer;padding:0 2px;}
.aim-x:hover{color:var(--text);}
.aim-body{padding:18px 20px;}
.aim-fg{margin-bottom:14px;}
.aim-fg2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.aim-modal label{display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:6px;}
.aim-req{color:#C2410C;}
.aim-modal input,.aim-modal select,.aim-modal textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);box-sizing:border-box;}
.aim-modal input:focus,.aim-modal select:focus,.aim-modal textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.aim-foot{padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;}
.aim-ghost{background:var(--bg);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:11px 18px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.aim-primary{background:var(--gold);color:#fff;border:none;border-radius:10px;padding:11px 22px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
</style>

<script>
function openAddInv(){ document.getElementById('addInvBg').classList.add('show'); }
function closeAddInv(){ document.getElementById('addInvBg').classList.remove('show'); }
document.getElementById('addInvBg').addEventListener('click', function(e){ if(e.target === this) closeAddInv(); });
</script>
</x-app-layout>
