# ============================================================
#  TROVE - add Search + Filter bar to Products page
#     powershell -ExecutionPolicy Bypass -File update-products-search.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Updating Products page..." -ForegroundColor Yellow

$p = Join-Path $root "resources\views\products\index.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
$content = @'
<x-app-layout>
<x-slot name="header">Products</x-slot>
<x-slot name="subheader">Trove menu - cakes, pastries &amp; coffee</x-slot>

<style>
.p-wrap{max-width:1040px;margin:0 auto;}
.p-toolbar{display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;}
.p-search{flex:1;min-width:200px;position:relative;}
.p-search svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:17px;height:17px;stroke:var(--muted);}
.p-search input{width:100%;padding:11px 14px 11px 40px;border:1px solid var(--border);border-radius:999px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);}
.p-search input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.filter-wrap{position:relative;}
.filter-btn{display:inline-flex;align-items:center;gap:7px;background:var(--gold);color:#fff;border:none;border-radius:999px;padding:11px 18px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.filter-btn svg{width:15px;height:15px;stroke:#fff;}
.filter-menu{position:absolute;top:48px;right:0;background:var(--white);border:1px solid var(--border);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.16);overflow:hidden;z-index:6;min-width:160px;}
.filter-menu button{display:block;width:100%;text-align:left;padding:10px 16px;font-size:13px;font-weight:600;color:var(--text);background:none;border:none;cursor:pointer;font-family:var(--f-body);}
.filter-menu button:hover{background:#FDF6EC;}
.filter-menu button.active{color:var(--gold);}
.newbtn{background:var(--navy);color:#fff;border:none;border-radius:999px;padding:11px 18px;font-weight:700;font-size:13.5px;cursor:pointer;text-decoration:none;white-space:nowrap;}
.chips{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:22px;}
.chip{border:1px solid var(--border);background:var(--white);color:var(--muted);border-radius:999px;padding:8px 18px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--f-body);}
.chip.active{background:var(--gold);color:#fff;border-color:var(--gold);}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.p-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px;}
.p-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 2px rgba(74,44,23,.05),0 8px 22px rgba(74,44,23,.06);display:flex;flex-direction:column;position:relative;}
.p-photo{height:150px;display:flex;align-items:center;justify-content:center;position:relative;border-radius:16px 16px 0 0;background:linear-gradient(135deg,#F3DCC4,#E8C07A);overflow:hidden;}
.p-photo img{width:100%;height:100%;object-fit:cover;display:block;}
.ph-emoji{font-size:44px;}
.st{position:absolute;top:10px;left:10px;font-size:10px;font-weight:700;padding:3px 9px;border-radius:999px;background:rgba(255,255,255,.92);color:#15803D;z-index:2;}
.st.low{color:#B45309;} .st.out{color:#C2410C;}
.kebab{position:absolute;top:9px;right:9px;width:30px;height:30px;border-radius:9px;border:none;background:rgba(255,255,255,.92);color:#4A2C17;font-size:18px;line-height:1;cursor:pointer;display:grid;place-items:center;box-shadow:0 1px 4px rgba(0,0,0,.12);z-index:3;}
.kebab:hover{background:#fff;}
.menu{position:absolute;top:42px;right:9px;background:var(--white);border:1px solid var(--border);border-radius:11px;box-shadow:0 8px 24px rgba(0,0,0,.16);overflow:hidden;z-index:5;min-width:130px;}
.menu a{display:block;padding:10px 14px;font-size:13px;font-weight:600;color:var(--text);text-decoration:none;cursor:pointer;}
.menu a:hover{background:#FDF6EC;}
.menu a.del{color:#C2410C;border-top:1px solid var(--border);}
.p-body{padding:14px 15px 16px;display:flex;flex-direction:column;gap:6px;flex:1;}
.p-row1{display:flex;align-items:baseline;justify-content:space-between;gap:8px;}
.p-nm{font-family:var(--f-display);font-weight:700;font-size:15.5px;letter-spacing:-.2px;min-width:0;}
.p-price{font-family:var(--f-display);font-weight:800;font-size:15.5px;color:var(--gold);white-space:nowrap;font-variant-numeric:tabular-nums;}
.p-desc{font-size:12px;color:var(--muted);line-height:1.45;min-height:34px;}
.p-meta{display:flex;align-items:center;gap:8px;font-size:11.5px;color:var(--muted);margin-top:2px;}
.p-cat{background:#FDF6EC;border:1px solid var(--border);padding:2px 9px;border-radius:999px;font-weight:600;}
.empty{background:#FDF6EC;border:1px solid var(--border);border-radius:14px;padding:50px 20px;text-align:center;color:var(--muted);}
.noresult{display:none;text-align:center;color:var(--muted);padding:40px 20px;font-size:14px;}
</style>

@php $cats = $products->pluck('category')->filter()->unique()->values(); @endphp

<div class="p-wrap">
    @if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif

    <div class="p-toolbar">
        <div class="p-search">
            <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            <input type="text" id="search" placeholder="Search products..." oninput="applyFilters()">
        </div>
        <div class="filter-wrap">
            <button class="filter-btn" onclick="toggleFilter(event)">
                <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>
                Filter
            </button>
            <div class="filter-menu" id="filterMenu" hidden>
                <button data-status="all" class="active" onclick="setStatus(this)">All stock</button>
                <button data-status="in" onclick="setStatus(this)">In stock</button>
                <button data-status="low" onclick="setStatus(this)">Low / Out of stock</button>
            </div>
        </div>
        @can('admin')<a href="{{ route('products.create') }}" class="newbtn">+ New Product</a>@endcan
    </div>

    <div class="chips" id="chips">
        <button class="chip active" data-cat="all" onclick="setCat(this)">All</button>
        @foreach($cats as $c)<button class="chip" data-cat="{{ $c }}" onclick="setCat(this)">{{ $c }}</button>@endforeach
    </div>

    @if($products && $products->count())
    <div class="p-grid" id="grid">
        @foreach($products as $product)
        @php
            $sq = (int) $product->stock_quantity;
            $state = $sq <= 0 ? 'out' : ($sq <= 5 ? 'low' : 'in');
        @endphp
        <div class="p-card" data-cat="{{ $product->category }}" data-name="{{ strtolower($product->product_name) }}" data-state="{{ $state }}">
            <div class="p-photo">
                @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->product_name }}">
                @else
                    <span class="ph-emoji">&#127856;</span>
                @endif
                <span class="st {{ $state==='out' ? 'out' : ($state==='low' ? 'low' : '') }}">{{ $sq<=0 ? 'Out of stock' : $sq.' in stock' }}</span>
                <button class="kebab" aria-label="Menu" onclick="toggleMenu(event,this)">&#8942;</button>
                <div class="menu" hidden>
                    <a href="{{ route('products.show', $product) }}">View</a>
                    @can('admin')
                        <a href="{{ route('products.edit', $product) }}">Edit</a>
                        <a class="del" onclick="if(confirm('Delete this product?')){document.getElementById('del{{ $product->id }}').submit();}return false;">Delete</a>
                        <form id="del{{ $product->id }}" method="POST" action="{{ route('products.destroy', $product) }}" style="display:none;">@csrf @method('DELETE')</form>
                    @endcan
                </div>
            </div>
            <div class="p-body">
                <div class="p-row1"><span class="p-nm">{{ $product->product_name }}</span><span class="p-price">&#8369;{{ number_format($product->price, 2) }}</span></div>
                <div class="p-desc">{{ $product->description ?: 'No description yet.' }}</div>
                <div class="p-meta"><span class="p-cat">{{ $product->category ?: 'Uncategorized' }}</span><span>&middot; {{ $sq }} pcs on hand</span></div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="noresult" id="noresult">No products match your search.</div>
    @else
    <div class="empty">
        <p style="margin-bottom:16px;">No products yet.</p>
        @can('admin')<a href="{{ route('products.create') }}" class="newbtn">+ Create First Product</a>@endcan
    </div>
    @endif
</div>

<script>
var curCat = 'all', curStatus = 'all';

function setCat(btn){
    document.querySelectorAll('#chips .chip').forEach(function(c){ c.classList.remove('active'); });
    btn.classList.add('active'); curCat = btn.getAttribute('data-cat'); applyFilters();
}
function setStatus(btn){
    document.querySelectorAll('#filterMenu button').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active'); curStatus = btn.getAttribute('data-status');
    document.getElementById('filterMenu').hidden = true; applyFilters();
}
function applyFilters(){
    var term = (document.getElementById('search').value || '').toLowerCase().trim();
    var shown = 0;
    document.querySelectorAll('.p-card').forEach(function(c){
        var okName = c.getAttribute('data-name').indexOf(term) !== -1;
        var okCat  = curCat === 'all' || c.getAttribute('data-cat') === curCat;
        var state  = c.getAttribute('data-state');
        var okStat = curStatus === 'all' || (curStatus === 'in' && state === 'in') || (curStatus === 'low' && (state === 'low' || state === 'out'));
        var show = okName && okCat && okStat;
        c.hidden = !show; if(show) shown++;
    });
    var nr = document.getElementById('noresult'); if(nr) nr.style.display = shown === 0 ? 'block' : 'none';
}

function closeAllMenus(){ document.querySelectorAll('.menu').forEach(function(m){ m.hidden = true; }); }
function toggleMenu(e, btn){ e.stopPropagation(); var m = btn.nextElementSibling; var willOpen = m.hidden; closeAllMenus(); m.hidden = !willOpen; }
function toggleFilter(e){ e.stopPropagation(); var fm = document.getElementById('filterMenu'); fm.hidden = !fm.hidden; }
document.addEventListener('click', function(){ closeAllMenus(); var fm=document.getElementById('filterMenu'); if(fm) fm.hidden = true; });
</script>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($p, $content, $Utf8NoBom)
Write-Host "  wrote resources\views\products\index.blade.php" -ForegroundColor Green
php artisan view:clear
Write-Host ""
Write-Host "DONE - refresh Products (Ctrl+F5). Search bar + Filter are at the top." -ForegroundColor Cyan
