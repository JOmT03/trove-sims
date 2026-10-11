# ============================================================
#  TROVE - Move PWA icons to public/ root (fixes the /icons 404)
#     powershell -ExecutionPolicy Bypass -File setup-pwa-icons-root.ps1
#  The manifest serves from public/ root, so icons placed next to it
#  (public/trove-192.png, public/trove-512.png) will serve too.
#  Updates manifest + apple-touch-icon links to the root paths.
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)

# ---------- 1. copy icons to public/ root ----------
$pub = Join-Path $root "public"
$src192 = Join-Path $pub "icons\trove-192.png"
$src512 = Join-Path $pub "icons\trove-512.png"
if (-not (Test-Path $src192) -or -not (Test-Path $src512)) {
    Write-Host "ERROR: public/icons/trove-192.png or trove-512.png missing. Run setup-pwa.ps1 first." -ForegroundColor Red; exit 1
}
Copy-Item $src192 (Join-Path $pub "trove-192.png") -Force
Copy-Item $src512 (Join-Path $pub "trove-512.png") -Force
Write-Host "  copied icons to public/trove-192.png + public/trove-512.png" -ForegroundColor Green

# ---------- 2. update manifest icon paths ----------
$mp = Join-Path $pub "manifest.webmanifest"
if (Test-Path $mp) {
    $m = [System.IO.File]::ReadAllText($mp)
    $m = $m.Replace('/icons/trove-192.png', '/trove-192.png').Replace('/icons/trove-512.png', '/trove-512.png')
    [System.IO.File]::WriteAllText($mp, $m, $Utf8NoBom)
    Write-Host "  manifest.webmanifest: icons now point to /trove-192.png + /trove-512.png" -ForegroundColor Green
} else {
    Write-Host "  manifest.webmanifest not found (skipped)" -ForegroundColor Yellow
}

# ---------- 3. update apple-touch-icon in blades ----------
$targets = @(
    (Join-Path $root "resources\views\layouts\app.blade.php"),
    (Join-Path $root "resources\views\auth\login.blade.php")
)
foreach ($t in $targets) {
    if (-not (Test-Path $t)) { continue }
    $c = [System.IO.File]::ReadAllText($t)
    if ($c.Contains('href="/icons/trove-192.png"')) {
        $c = $c.Replace('href="/icons/trove-192.png"', 'href="/trove-192.png"')
        [System.IO.File]::WriteAllText($t, $c, $Utf8NoBom)
        Write-Host ("  updated apple-touch-icon in " + (Split-Path $t -Leaf)) -ForegroundColor Green
    }
}

# ---------- 4. clear caches ----------
Write-Host ""
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE locally. Push to go live:" -ForegroundColor Cyan
Write-Host "  git add public/trove-192.png public/trove-512.png public/manifest.webmanifest resources/views/layouts/app.blade.php resources/views/auth/login.blade.php" -ForegroundColor White
Write-Host "  git commit -m ""PWA icons at public root (fix /icons 404)""" -ForegroundColor White
Write-Host "  git push" -ForegroundColor White
Write-Host ""
Write-Host "After redeploy, check: https://trove-eq9l.onrender.com/trove-192.png (should show the icon)." -ForegroundColor White
