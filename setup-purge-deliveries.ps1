# ============================================================
#  TROVE - Purge legacy DELIVERIES (DB tables + code)
#     powershell -ExecutionPolicy Bypass -File setup-purge-deliveries.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Purging legacy Deliveries..." -ForegroundColor Yellow

# ---- 1. Migration: drop delivery tables safely ----
$mig = @'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1) Remove the delivery_id FK + column from inventory_logs (if present)
        if (Schema::hasTable('inventory_logs') && Schema::hasColumn('inventory_logs', 'delivery_id')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                $table->dropForeign(['delivery_id']);
                $table->dropColumn('delivery_id');
            });
        }

        // 2) Drop legacy delivery tables (child first, then parent)
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
    }

    public function down(): void {
        // Legacy tables are intentionally not recreated.
    }
};
'@
$p = Join-Path $root "database\migrations\2026_10_05_000002_purge_deliveries.php"
New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
[System.IO.File]::WriteAllText($p, $mig, $Utf8NoBom)
Write-Host "  wrote purge migration" -ForegroundColor Green

# ---- 2. Clean deliveries references out of routes/web.php ----
$webPath = Join-Path $root "routes\web.php"
$web = [System.IO.File]::ReadAllText($webPath)
$lines = [regex]::Split($web, "\r?\n")
$kept = @()
$removed = 0
foreach ($ln in $lines) {
    if ($ln -match 'DeliveryController' -or $ln -match 'deliveries:check-upcoming' -or $ln -match '//.*DELIVERIES') {
        $removed++
        continue
    }
    $kept += $ln
}
$web = ($kept -join "`r`n")
[System.IO.File]::WriteAllText($webPath, $web, $Utf8NoBom)
Write-Host "  web.php: removed $removed deliveries line(s)" -ForegroundColor Green

# ---- 3. Delete deliveries code files ----
$toDelete = @(
    "app\Http\Controllers\DeliveryController.php",
    "app\Models\Delivery.php",
    "app\Models\DeliveryItem.php",
    "app\Console\Commands\CheckUpcomingDeliveries.php"
)
foreach ($rel in $toDelete) {
    $f = Join-Path $root $rel
    if (Test-Path $f) { Remove-Item $f -Force; Write-Host "  deleted $rel" -ForegroundColor Green }
}
$viewsDir = Join-Path $root "resources\views\deliveries"
if (Test-Path $viewsDir) { Remove-Item $viewsDir -Recurse -Force; Write-Host "  deleted resources\views\deliveries\" -ForegroundColor Green }

# ---- 4. Run migration + clear caches ----
Write-Host ""
php artisan migrate --force
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - deliveries purged from the database and code." -ForegroundColor Cyan
Write-Host "  (orders, suppliers, and everything else are untouched)" -ForegroundColor White
