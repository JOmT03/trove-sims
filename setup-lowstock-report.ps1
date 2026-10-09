# ============================================================
#  TROVE - Low Stock / Restock Report (printable) on the Inventory page
#     powershell -ExecutionPolicy Bypass -File setup-lowstock-report.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Adding the Low Stock / Restock Report..." -ForegroundColor Yellow

# ---- 1. Controller: add lowStockReport() before create() ----
$ctrlPath = Join-Path $root "app\Http\Controllers\InventoryController.php"
$c = [System.IO.File]::ReadAllText($ctrlPath)
if ($c -notmatch 'lowStockReport') {
    $method = @'
    public function lowStockReport()
    {
        $items = Inventory::whereNull('archived_at')
            ->orderBy('item_name')
            ->get()
            ->filter(function ($i) {
                $on  = (float) $i->quantity_on_hand;
                $min = (float) $i->minimum_stock;
                return $on <= $min * 1.5; // low / critical / out
            })
            ->sortBy(function ($i) {
                $min = (float) $i->minimum_stock;
                return $min > 0 ? ((float) $i->quantity_on_hand) / $min : 999;
            })
            ->values();

        return view('inventory.low-stock-report', compact('items'));
    }

'@
    $c = $c.Replace("    public function create()", $method + "    public function create()")
    [System.IO.File]::WriteAllText($ctrlPath, $c, $Utf8NoBom)
    Write-Host "  InventoryController: added lowStockReport()" -ForegroundColor Green
} else {
    Write-Host "  InventoryController: lowStockReport already present (skipped)" -ForegroundColor DarkGray
}

# ---- 2. Route (guarded) ----
$webPath = Join-Path $root "routes\web.php"
$web = [System.IO.File]::ReadAllText($webPath)
if ($web -notmatch "inventory\.low-stock-report") {
    $routes = @'

// ===== Low Stock / Restock Report (Owner & Manager) =====
Route::middleware(['auth', 'admin', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/inventory-report/low-stock', [\App\Http\Controllers\InventoryController::class, 'lowStockReport'])->name('inventory.low-stock-report');
});
'@
    $web = $web.TrimEnd() + "`r`n" + $routes + "`r`n"
    [System.IO.File]::WriteAllText($webPath, $web, $Utf8NoBom)
    Write-Host "  web.php: added low-stock-report route" -ForegroundColor Green
} else {
    Write-Host "  web.php: route already present (skipped)" -ForegroundColor DarkGray
}

# ---- 3. View: standalone printable report ----
$view = @'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Low Stock Report - Trove</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#FBF2E4;color:#2E1C10;font-family:'Segoe UI',system-ui,Arial,sans-serif;line-height:1.45;}
.wrap{max-width:860px;margin:0 auto;padding:26px 18px 48px;}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:18px;}
.brand{display:flex;align-items:center;gap:11px;}
.logo{width:40px;height:40px;border-radius:10px;background:#D9782C;color:#fff;display:grid;place-items:center;font-weight:800;font-size:15px;}
h1{font-size:21px;font-weight:800;margin:0;color:#2E1C10;}
.sub{font-size:12.5px;color:#8A7460;margin-top:2px;}
.actions{display:flex;gap:9px;}
.btn{display:inline-flex;align-items:center;gap:7px;border:none;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#fff;color:#4A2C17;border:1px solid #EBDCCA;}
.summary{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px;}
.sc{flex:1;min-width:110px;background:#fff;border:1px solid #EBDCCA;border-radius:12px;padding:13px 15px;}
.sc .n{font-weight:800;font-size:24px;line-height:1;}
.sc .k{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#8A7460;margin-top:5px;}
.sc.out .n{color:#991B1B;} .sc.crit .n{color:#C2410C;} .sc.low .n{color:#B45309;}
.doc{background:#fff;border:1px solid #EBDCCA;border-radius:14px;overflow:hidden;}
.doc-h{padding:15px 20px;border-bottom:1px solid #EBDCCA;display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:6px;}
.doc-h .ttl{font-weight:800;font-size:16px;}
.doc-h .dt{font-size:12px;color:#8A7460;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:right;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:#8A7460;background:#FDF6EC;padding:10px 14px;font-weight:700;}
th:first-child,th:nth-child(2){text-align:left;}
td{padding:11px 14px;border-top:1px solid #EBDCCA;text-align:right;}
td:first-child{text-align:left;font-weight:600;}
td:nth-child(2){text-align:left;color:#8A7460;}
.badge{display:inline-block;font-size:10px;font-weight:700;padding:3px 9px;border-radius:999px;text-transform:uppercase;}
.b-out{background:#FBE0E0;color:#991B1B;} .b-crit{background:#FBE4DA;color:#C2410C;} .b-low{background:#FBEBD6;color:#B45309;}
.short{font-weight:800;color:#B5651D;}
.foot{padding:14px 20px;border-top:1px solid #EBDCCA;font-size:11.5px;color:#8A7460;}
.empty{padding:40px 20px;text-align:center;color:#8A7460;}
@media print{
  body{background:#fff;}
  .actions{display:none!important;}
  .wrap{max-width:none;padding:0;}
}
</style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <div class="brand">
      <div class="logo">TR</div>
      <div><h1>Low Stock Report</h1><div class="sub">Ingredients to restock - Matina commissary</div></div>
    </div>
    <div class="actions">
      <a href="{{ route('inventory.index') }}" class="btn btn-outline">&larr; Back</a>
      <button class="btn btn-gold" onclick="window.print()">Print / Save PDF</button>
    </div>
  </div>

  @php
    $out  = $items->filter(fn($i)=>(float)$i->quantity_on_hand<=0)->count();
    $crit = $items->filter(fn($i)=>(float)$i->quantity_on_hand>0 && (float)$i->quantity_on_hand<=(float)$i->minimum_stock)->count();
    $low  = $items->filter(fn($i)=>(float)$i->quantity_on_hand>(float)$i->minimum_stock)->count();
  @endphp

  <div class="summary">
    <div class="sc out"><div class="n">{{ $out }}</div><div class="k">Out of stock</div></div>
    <div class="sc crit"><div class="n">{{ $crit }}</div><div class="k">Critical</div></div>
    <div class="sc low"><div class="n">{{ $low }}</div><div class="k">Low</div></div>
    <div class="sc"><div class="n">{{ $items->count() }}</div><div class="k">Items to buy</div></div>
  </div>

  <div class="doc">
    <div class="doc-h"><span class="ttl">Restock List</span><span class="dt">Generated {{ now()->format('M d, Y g:i A') }}</span></div>
    @if($items->count())
    <table>
      <thead><tr><th>Ingredient</th><th>Category</th><th>Current</th><th>Min stock</th><th>Shortfall</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($items as $it)
          @php
            $on=(float)$it->quantity_on_hand; $min=(float)$it->minimum_stock;
            $tier = $on<=0 ? 'out' : ($on<=$min ? 'crit' : 'low');
            $labels=['out'=>'Out','crit'=>'Critical','low'=>'Low'];
            $short = max(0, $min - $on);
          @endphp
          <tr>
            <td>{{ $it->item_name }}</td>
            <td>{{ $it->category }}</td>
            <td>{{ rtrim(rtrim(number_format($on,2),'0'),'.') }} {{ $it->unit }}</td>
            <td>{{ rtrim(rtrim(number_format($min,2),'0'),'.') }} {{ $it->unit }}</td>
            <td class="short">{{ $short>0 ? '+'.rtrim(rtrim(number_format($short,2),'0'),'.').' '.$it->unit : '-' }}</td>
            <td><span class="badge b-{{ $tier }}">{{ $labels[$tier] }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="foot">Shortfall = how much below the minimum. Buy at least this much to get back to the minimum level.</div>
    @else
    <div class="empty">All ingredients are above their minimum stock. Nothing to restock right now.</div>
    @endif
  </div>
</div>
</body>
</html>
'@
$viewPath = Join-Path $root "resources\views\inventory\low-stock-report.blade.php"
New-Item -ItemType Directory -Force -Path (Split-Path $viewPath) | Out-Null
[System.IO.File]::WriteAllText($viewPath, $view, $Utf8NoBom)
Write-Host "  wrote resources\views\inventory\low-stock-report.blade.php" -ForegroundColor Green

# ---- 4. Add "Restock List" button to the Inventory toolbar ----
$idxPath = Join-Path $root "resources\views\inventory\index.blade.php"
$idx = [System.IO.File]::ReadAllText($idxPath)
$oldBtn = '<a class="addbtn" href="{{ route(''inventory.create'') }}">+ Add Item</a>'
$newBtn = '<a class="addbtn" href="{{ route(''inventory.low-stock-report'') }}" target="_blank" style="background:#fff;color:var(--gold);border:1px solid var(--border);margin-right:8px;">Restock List</a>' + "`r`n                " + '<a class="addbtn" href="{{ route(''inventory.create'') }}">+ Add Item</a>'
if ($idx.Contains($oldBtn) -and -not $idx.Contains('inventory.low-stock-report')) {
    $idx = $idx.Replace($oldBtn, $newBtn)
    [System.IO.File]::WriteAllText($idxPath, $idx, $Utf8NoBom)
    Write-Host "  inventory/index: added Restock List button" -ForegroundColor Green
} else {
    Write-Host "  inventory/index: Restock List button already present or anchor not found." -ForegroundColor Yellow
}

# ---- 5. Clear caches ----
Write-Host ""
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - Open Inventory, click 'Restock List' (opens a printable report)." -ForegroundColor Cyan
