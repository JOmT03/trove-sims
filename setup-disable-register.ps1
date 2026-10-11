# ============================================================
#  TROVE - Disable public registration (back-office system)
#     powershell -ExecutionPolicy Bypass -File setup-disable-register.ps1
#  - routes/auth.php: /register now redirects to login (no account creation)
#  - login.blade.php: removes the "Register here" top-nav link
#  The Owner creates users via User Management instead.
#  Backs up each file to .bak (keeps existing .bak if present).
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function BackupOnce($p){ $b=$p+".bak"; if((Test-Path $p) -and -not(Test-Path $b)){ Copy-Item $p $b } }

# ---------- 1. routes/auth.php ----------
$ap = Join-Path $root "routes\auth.php"
if (-not (Test-Path $ap)) { Write-Host "ERROR: routes/auth.php not found." -ForegroundColor Red; exit 1 }
BackupOnce $ap
$a = ([System.IO.File]::ReadAllText($ap)) -replace "`r`n", "`n"

$oldReg = @'
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);
'@ -replace "`r`n", "`n"

$newReg = @'
    // Public self-registration is disabled (Trove is a back-office system).
    // The Owner creates users via User Management. /register just goes to login.
    Route::get('register', fn () => redirect()->route('login'))->name('register');
'@ -replace "`r`n", "`n"

if ($a.Contains($oldReg)) {
    $a = $a.Replace($oldReg, $newReg)
    [System.IO.File]::WriteAllText($ap, $a, $Utf8NoBom)
    Write-Host "  routes/auth.php: registration disabled (redirects to login)" -ForegroundColor Green
} elseif ($a -match "redirect\(\)->route\('login'\)\)->name\('register'\)") {
    Write-Host "  routes/auth.php: already disabled (skipped)" -ForegroundColor DarkGray
} else {
    Write-Host "  WARNING: could not find the register routes in auth.php - left unchanged." -ForegroundColor Yellow
}

# ---------- 2. login.blade.php: remove "Register here" link ----------
$lp = Join-Path $root "resources\views\auth\login.blade.php"
if (Test-Path $lp) {
    $l = ([System.IO.File]::ReadAllText($lp)) -replace "`r`n", "`n"
    $oldLink = @'
        <a href="{{ route('register') }}" class="nav-register">
            No account? <strong>Register here</strong>
        </a>
'@ -replace "`r`n", "`n"
    if ($l.Contains($oldLink)) {
        $l = $l.Replace($oldLink, '')
        [System.IO.File]::WriteAllText($lp, $l, $Utf8NoBom)
        Write-Host "  login.blade.php: removed the 'Register here' link" -ForegroundColor Green
    } else {
        Write-Host "  login.blade.php: 'Register here' link not found (maybe already removed)" -ForegroundColor DarkGray
    }
} else {
    Write-Host "  login.blade.php not found (skipped)" -ForegroundColor DarkGray
}

# ---------- 3. Clear caches ----------
Write-Host ""
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE locally. Test: visiting /register should bounce you to /login." -ForegroundColor Cyan
Write-Host "To push online:" -ForegroundColor Cyan
Write-Host "  git add routes/auth.php resources/views/auth/login.blade.php" -ForegroundColor White
Write-Host "  git commit -m ""Disable public registration (back-office)""" -ForegroundColor White
Write-Host "  git push" -ForegroundColor White
