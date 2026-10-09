# ============================================================
#  TROVE - Dashboard "Batch Sent" -> total batch count (clickable to Branch Transfers)
#     powershell -ExecutionPolicy Bypass -File setup-dashboard-batches.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Updating the Dashboard 'Batch Sent' card..." -ForegroundColor Yellow

# ---- 1. DashboardController: add total batch count ----
$ctrlPath = Join-Path $root "app\Http\Controllers\DashboardController.php"
$c = [System.IO.File]::ReadAllText($ctrlPath)
if ($c -notmatch 'totalBatches') {
    $oldRev = '$netSoldRevenue = $weekBatches->sum(fn ($b) => $b->totalRevenue());'
    $newRev = $oldRev + "`r`n`r`n        // Total batches ever dispatched (sent + reconciled)`r`n        `$totalBatches = Batch::count();"
    $c = $c.Replace($oldRev, $newRev)

    $oldCompact = "'batchSent', 'returnedQty', 'netSoldQty', 'netSoldRevenue',"
    $newCompact = "'batchSent', 'totalBatches', 'returnedQty', 'netSoldQty', 'netSoldRevenue',"
    $c = $c.Replace($oldCompact, $newCompact)

    [System.IO.File]::WriteAllText($ctrlPath, $c, $Utf8NoBom)
    Write-Host "  DashboardController: added `$totalBatches = Batch::count()" -ForegroundColor Green
} else {
    Write-Host "  DashboardController: totalBatches already present (skipped)" -ForegroundColor DarkGray
}

# ---- 2. dashboard.blade.php: show batch count, relabel ----
$viewPath = Join-Path $root "resources\views\dashboard.blade.php"
$v = [System.IO.File]::ReadAllText($viewPath)
$oldKpi = '<div class="l">Batch Sent (This Week)</div><div class="v">{{ $batchSent }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">to Jacinto</div>'
$newKpi = '<div class="l">Batches Sent</div><div class="v">{{ $totalBatches }} <span style="font-size:14px;color:#8A7460;">{{ $totalBatches == 1 ? ''batch'' : ''batches'' }}</span></div><div class="s">dispatched to Jacinto</div>'
if ($v.Contains($oldKpi)) {
    $v = $v.Replace($oldKpi, $newKpi)
    [System.IO.File]::WriteAllText($viewPath, $v, $Utf8NoBom)
    Write-Host "  dashboard.blade.php: card now shows total batch count" -ForegroundColor Green
} elseif ($v.Contains('Batches Sent')) {
    Write-Host "  dashboard.blade.php: already updated (skipped)" -ForegroundColor DarkGray
} else {
    Write-Host "  dashboard.blade.php: could not find the Batch Sent card - update it manually." -ForegroundColor Yellow
}

# ---- 3. Clear caches ----
Write-Host ""
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - the card now shows the TOTAL number of batches and links to Branch Transfers." -ForegroundColor Cyan
Write-Host "  Refresh the Dashboard (Ctrl+F5)." -ForegroundColor White
