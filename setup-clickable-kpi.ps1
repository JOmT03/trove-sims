# ============================================================
#  TROVE - Make dashboard KPI cards clickable
#     powershell -ExecutionPolicy Bypass -File setup-clickable-kpi.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
$dash = Join-Path $root "resources\views\dashboard.blade.php"
if (-not (Test-Path $dash)) { Write-Host "ERROR: dashboard.blade.php not found." -ForegroundColor Red; exit 1 }

$c = [System.IO.File]::ReadAllText($dash)
Write-Host "Making KPI cards clickable..." -ForegroundColor Yellow

if ($c -match "kpi-go") {
    Write-Host "  already clickable - nothing to do." -ForegroundColor DarkGray
    exit 0
}

# ---- KPI A: Batch Sent -> Branch Transfers ----
$oldA = @'
    <div class="kpi a"><div class="l">Batch Sent (This Week)</div><div class="v">{{ $batchSent }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">to Jacinto</div></div>
'@
$newA = @'
    <a href="{{ Route::has('branch-transfers.index') ? route('branch-transfers.index') : '#' }}" class="kpi a"><div class="l">Batch Sent (This Week)</div><div class="v">{{ $batchSent }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">to Jacinto</div><span class="kpi-go">View Branch Transfers &rarr;</span></a>
'@

# ---- KPI B: Net Sold -> Reports ----
$oldB = @'
    <div class="kpi b"><div class="l">Net Sold (This Week)</div><div class="v">{{ $netSoldQty }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">&#8369;{{ number_format($netSoldRevenue,2) }} &middot; {{ $returnedQty }} returned</div></div>
'@
$newB = @'
    <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="kpi b"><div class="l">Net Sold (This Week)</div><div class="v">{{ $netSoldQty }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">&#8369;{{ number_format($netSoldRevenue,2) }} &middot; {{ $returnedQty }} returned</div><span class="kpi-go">View Reports &rarr;</span></a>
'@

# ---- KPI C: Low Ingredients -> Inventory ----
$oldC = @'
    <div class="kpi c"><div class="l">Low Ingredients</div><div class="v">{{ $lowCount }}</div><div class="s">need restock at Matina</div></div>
'@
$newC = @'
    <a href="{{ route('inventory.index') }}" class="kpi c"><div class="l">Low Ingredients</div><div class="v">{{ $lowCount }}</div><div class="s">need restock at Matina</div><span class="kpi-go">View Inventory &rarr;</span></a>
'@

# ---- KPI D: Finished Stock -> Products ----
$oldD = @'
    <div class="kpi d"><div class="l">Finished Stock</div><div class="v">{{ $finishedStock }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">across {{ $totalProducts }} products</div></div>
'@
$newD = @'
    <a href="{{ route('products.index') }}" class="kpi d"><div class="l">Finished Stock</div><div class="v">{{ $finishedStock }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">across {{ $totalProducts }} products</div><span class="kpi-go">View Products &rarr;</span></a>
'@

$c = $c.Replace($oldA, $newA)
$c = $c.Replace($oldB, $newB)
$c = $c.Replace($oldC, $newC)
$c = $c.Replace($oldD, $newD)

# ---- hover CSS (append after the existing .kpi .s rule) ----
$cssAnchor = '.kpi .s{font-size:12px;color:var(--mut);margin-top:6px;}'
$cssAdd = @'
.kpi .s{font-size:12px;color:var(--mut);margin-top:6px;}
a.kpi{text-decoration:none;color:inherit;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease;}
a.kpi:hover{transform:translateY(-3px);box-shadow:0 4px 8px rgba(0,0,0,.07),0 14px 30px rgba(0,0,0,.11);border-color:#8A7460;}
a.kpi:focus-visible{outline:2px solid var(--info);outline-offset:2px;}
.kpi-go{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:var(--mut);margin-top:8px;opacity:.5;transition:opacity .15s ease,color .15s ease;}
a.kpi:hover .kpi-go{opacity:1;color:#2E1C10;}
'@
$c = $c.Replace($cssAnchor, $cssAdd)

if ($c -match "kpi-go") {
    [System.IO.File]::WriteAllText($dash, $c, $Utf8NoBom)
    Write-Host "  dashboard.blade.php updated - KPIs are now clickable" -ForegroundColor Green
} else {
    Write-Host "  WARNING: KPI markup did not match. Your dashboard.blade.php may differ." -ForegroundColor Yellow
    Write-Host "  Send me resources\views\dashboard.blade.php and I'll adjust." -ForegroundColor Yellow
    exit 1
}

php artisan view:clear
Write-Host ""
Write-Host "DONE - refresh the Dashboard (Ctrl+F5). Hover a KPI, then click it." -ForegroundColor Cyan
