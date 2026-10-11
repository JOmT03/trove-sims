# ============================================================
#  TROVE - Open Restock List in the SAME tab (not a new browser tab)
#     powershell -ExecutionPolicy Bypass -File setup-restock-sametab.ps1
#  Removes target="_blank" from the Restock List link in the inventory
#  list, so it opens in-app instead of a new browser tab. View-only.
#  Backs up to .bak.
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function BackupOnce($p){ $b=$p+".bak2"; if((Test-Path $p) -and -not(Test-Path $b)){ Copy-Item $p $b } }

$f = Join-Path $root "resources\views\inventory\index.blade.php"
if (-not (Test-Path $f)) { Write-Host "ERROR: inventory/index.blade.php not found." -ForegroundColor Red; exit 1 }
BackupOnce $f
$c = [System.IO.File]::ReadAllText($f)

$old = 'href="{{ route(''inventory.low-stock-report'') }}" target="_blank"'
$new = 'href="{{ route(''inventory.low-stock-report'') }}"'

if ($c.Contains($old)) {
    $c = $c.Replace($old, $new)
    [System.IO.File]::WriteAllText($f, $c, $Utf8NoBom)
    Write-Host "  Restock List now opens in the same tab (removed target=_blank)" -ForegroundColor Green
} elseif ($c.Contains('inventory.low-stock-report') -and -not ($c -match 'low-stock-report''\s*\)\s*}}"\s*target')) {
    Write-Host "  Already opens in the same tab (no target=_blank found) - skipped" -ForegroundColor DarkGray
} else {
    Write-Host "  WARNING: Restock link not found in expected form - left unchanged." -ForegroundColor Yellow
}

Write-Host ""
php artisan view:clear
php artisan optimize:clear
Write-Host ""
Write-Host "DONE. Click Restock List - it should open in the same page now." -ForegroundColor Cyan
Write-Host "Push: git add resources/views/inventory/index.blade.php ; git commit -m ""Restock List opens in same tab"" ; git push" -ForegroundColor White
