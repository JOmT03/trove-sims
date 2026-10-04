# ============================================================
#  TROVE - Inventory UI (per-row Blade-rendered Adjust form, foolproof)
#     powershell -ExecutionPolicy Bypass -File setup-inv-adjust-fix.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Rebuilding Inventory page (foolproof Adjust)..." -ForegroundColor Yellow

$p = Join-Path $root "resources\views\inventory\index.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Inventory</x-slot>
<x-slot name="subheader">Raw materials &amp; ingredients &mdash; Matina store</x-slot>

@php
    $lowCrit = $inventories->filter(function($i){ $on=(float)$i->quantity_on_hand; $min=(float)$i->minimum_stock; return $on <= $min*1.5; })->count();
    $dmgItems = $inventories->filter(function($i){ return (float)$i->quantity_damaged > 0; })->count();
@endphp

<style>
.inv-wrap{max-width:1040px;}
.inv-alert{padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.inv-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.inv-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.inv-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
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
.inv-bar{display:flex;justify-content:flex-end;margin-bottom:12px;}
.addbtn{background:var(--gold);color:#fff;border:none;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;}
.inv-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);overflow:visible;}
.inv-card table{width:100%;border-collapse:collapse;font-size:13px;}
.inv-card th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:12px 16px;background:var(--bg);font-weight:700;}
.inv-card td{padding:13px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
.inv-card tbody tr:last-child td{border-bottom:none;}
.inv-card tbody tr{transition:background .12s ease;}
.inv-card tbody tr:hover{background:var(--bg);}
.item{font-weight:700;color:var(--text);}
.cat{color:var(--gold);font-size:12.5px;}
.stockcell{display:flex;align-items:center;gap:9px;flex-wrap:wrap;}
.stockval{font-variant-numeric:tabular-nums;font-weight:600;}
.stockval.crit,.stockval.out{color:#C2410C;}
.pill{font-size:10px;font-weight:800;letter-spacing:.3px;padding:2px 8px;border-radius:999px;text-transform:uppercase;}
.pill.ok{background:#E7F3EA;color:#15803D;} .pill.low{background:#FBEBD6;color:#B45309;} .pill.crit{background:#FBE4DA;color:#C2410C;} .pill.out{background:#FBE4DA;color:#C2410C;}
.dmgnote{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#C2410C;margin-top:3px;font-weight:600;}
.min{color:var(--muted);font-variant-numeric:tabular-nums;}
.actcell{width:46px;padding-right:10px;text-align:right;position:relative;}
.kebab{width:30px;height:30px;border-radius:8px;border:1px solid transparent;background:none;color:var(--muted);font-size:18px;line-height:1;cursor:pointer;display:inline-grid;place-items:center;opacity:0;transition:opacity .12s ease,background .12s ease;}
.inv-card tbody tr:hover .kebab{opacity:1;}
.kebab:hover{background:var(--white);border-color:var(--border);color:var(--text);}
.menu{position:absolute;top:38px;right:10px;background:var(--white);border:1px solid var(--border);border-radius:11px;box-shadow:0 8px 24px rgba(0,0,0,.17);overflow:hidden;z-index:20;min-width:155px;}
.menu a{display:flex;align-items:center;gap:9px;padding:10px 14px;font-size:13px;font-weight:600;color:var(--text);text-decoration:none;cursor:pointer;}
.menu a:hover{background:var(--bg);}
.menu a.del{color:#C2410C;border-top:1px solid var(--border);}
.menu a svg{width:15px;height:15px;stroke:currentColor;fill:none;}
.noresult{display:none;text-align:center;color:var(--muted);padding:34px;font-size:14px;}
.legend{display:flex;gap:16px;flex-wrap:wrap;margin-top:14px;font-size:11.5px;color:var(--muted);}
.legend span{display:inline-flex;align-items:center;gap:6px;}
.legend i{width:9px;height:9px;border-radius:50%;display:inline-block;}
.modal-ov{position:fixed;inset:0;background:rgba(46,28,16,.45);display:flex;align-items:center;justify-content:center;z-index:200;padding:16px;}
.modal{background:var(--white);border-radius:16px;width:100%;max-width:400px;padding:22px;box-shadow:0 20px 50px rgba(0,0,0,.25);}
.modal-h{font-family:var(--f-display);font-size:16px;font-weight:800;color:var(--text);margin-bottom:16px;}
.modal-h span{color:var(--gold);}
.modal label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin:12px 0 5px;}
.modal select,.modal input{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);font-family:var(--f-body);}
.modal-act{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
.mbtn{border:none;border-radius:9px;padding:9px 16px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.mbtn.gold{background:var(--gold);color:#fff;} .mbtn.ghost{background:var(--bg);color:var(--text);border:1px solid var(--border);}
</style>

<div class="inv-wrap">
    @if(session('success'))<div class="inv-alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="inv-alert err">{{ session('error') }}</div>@endif

    <div class="inv-kpis" id="kpis">
        <button class="ikpi k-all active" data-filter="all" onclick="setFilter(this)"><div class="l">Total Items</div><div class="v">{{ $inventories->count() }}</div><div class="hint">showing all</div></button>
        <button class="ikpi k-low" data-filter="low" onclick="setFilter(this)"><div class="l">Low / Critical Stock</div><div class="v">{{ $lowCrit }}</div><div class="hint">click to filter</div></button>
        <button class="ikpi k-dmg" data-filter="dmg" onclick="setFilter(this)"><div class="l">With Damaged Units</div><div class="v">{{ $dmgItems }}</div><div class="hint">click to filter</div></button>
    </div>

    @can('admin')<div class="inv-bar"><a class="addbtn" href="{{ route('inventory.create') }}">+ Add Item</a></div>@endcan

    <div class="inv-card">
        <table>
            <thead><tr><th>Item</th><th>Category</th><th>Stock</th><th>Min Stock</th><th class="actcell"></th></tr></thead>
            <tbody>
            @forelse($inventories as $inv)
                @php
                    $on=(float)$inv->quantity_on_hand; $min=(float)$inv->minimum_stock; $dmg=(float)$inv->quantity_damaged;
                    $t = $on<=0 ? 'out' : ($on<=$min ? 'crit' : ($on<=$min*1.5 ? 'low' : 'ok'));
                    $labels=['ok'=>'OK','low'=>'Low','crit'=>'Critical','out'=>'Out'];
                @endphp
                <tr data-tier="{{ $t }}" data-dmg="{{ $dmg>0?'1':'0' }}">
                    <td>
                        <div class="item">{{ $inv->item_name }}</div>
                        @if($dmg>0)<div class="dmgnote">&#9888; {{ number_format($dmg,2) }} {{ $inv->unit }} damaged</div>@endif
                    </td>
                    <td><span class="cat">{{ $inv->category }}</span></td>
                    <td><div class="stockcell"><span class="stockval {{ in_array($t,['crit','out']) ? $t : '' }}">{{ number_format($on,2) }} {{ $inv->unit }}</span><span class="pill {{ $t }}">{{ $labels[$t] }}</span></div></td>
                    <td><span class="min">{{ number_format($min,2) }} {{ $inv->unit }}</span></td>
                    <td class="actcell">
                        <button class="kebab" aria-label="Menu" onclick="toggleMenu(event,this)">&#8942;</button>
                        <div class="menu" hidden>
                            <a href="{{ route('inventory.show',$inv) }}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12C3.7 7.9 7.5 5 12 5s8.3 2.9 9.5 7c-1.2 4.1-5 7-9.5 7s-8.3-2.9-9.5-7z"/></svg> View</a>
                            @can('admin')
                                <a href="{{ route('inventory.edit',$inv) }}"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit</a>
                                <a onclick="event.stopPropagation(); document.getElementById('adjM{{ $inv->id }}').hidden=false;"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg> Adjust Stock</a>
                                <a class="del" onclick="if(confirm('Delete this item?')){document.getElementById('del{{ $inv->id }}').submit();}return false;"><svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg> Delete</a>
                                <form id="del{{ $inv->id }}" method="POST" action="{{ route('inventory.destroy',$inv) }}" style="display:none">@csrf @method('DELETE')</form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--muted)">No items yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="noresult" id="noresult">No items match this filter.</div>
    </div>

    <div class="legend">
        <span><i style="background:#15803D"></i> OK &mdash; above buffer</span>
        <span><i style="background:#B45309"></i> Low &mdash; near minimum</span>
        <span><i style="background:#C2410C"></i> Critical &mdash; at/below minimum</span>
        <span><i style="background:#C2410C"></i> Out &mdash; zero stock</span>
    </div>
</div>

@can('admin')
@foreach($inventories as $inv)
<div class="modal-ov" id="adjM{{ $inv->id }}" hidden onclick="if(event.target===this)this.hidden=true;">
    <div class="modal">
        <div class="modal-h">Adjust Stock &mdash; <span>{{ $inv->item_name }}</span></div>
        <form method="POST" action="{{ route('inventory.adjust', $inv) }}">
            @csrf @method('PATCH')
            <label>Type</label>
            <select name="type" required>
                <option value="adjustment">Add / Restock (+)</option>
                <option value="used">Used (-)</option>
                <option value="damaged">Mark Damaged (-)</option>
            </select>
            <label>Quantity ({{ $inv->unit }})</label>
            <input type="number" name="quantity" step="0.01" min="0.01" required>
            <label>Notes (optional)</label>
            <input type="text" name="notes" placeholder="e.g. restock from market">
            <div class="modal-act">
                <button type="button" class="mbtn ghost" onclick="document.getElementById('adjM{{ $inv->id }}').hidden=true;">Cancel</button>
                <button class="mbtn gold">Save</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endcan

<script>
var curFilter='all';
function setFilter(btn){
    document.querySelectorAll('.ikpi').forEach(function(k){k.classList.remove('active');});
    btn.classList.add('active');
    curFilter=btn.getAttribute('data-filter');
    document.querySelectorAll('.ikpi .hint').forEach(function(h){h.textContent='click to filter';});
    btn.querySelector('.hint').textContent = curFilter==='all'?'showing all':'filtered';
    var shown=0;
    document.querySelectorAll('.inv-card tbody tr').forEach(function(r){
        if(!r.getAttribute('data-tier')) return;
        var t=r.getAttribute('data-tier'), d=r.getAttribute('data-dmg');
        var show = curFilter==='all' || (curFilter==='low' && (t==='low'||t==='crit'||t==='out')) || (curFilter==='dmg' && d==='1');
        r.style.display = show?'':'none'; if(show) shown++;
    });
    document.getElementById('noresult').style.display = shown===0?'block':'none';
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
Write-Host "DONE - the Adjust form action is now Blade-rendered per item." -ForegroundColor Cyan
Write-Host "Open the Inventory with a fresh URL to dodge cache:" -ForegroundColor White
Write-Host "   http://127.0.0.1:8000/inventory?v=3" -ForegroundColor Yellow
