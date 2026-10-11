# ============================================================
#  TROVE - Create the missing product_raw_materials table (local fix)
#     powershell -ExecutionPolicy Bypass -File setup-fix-raw-materials.ps1
#  The merged partner code references product_raw_materials, which the
#  local DB is missing (Dashboard errors). This adds a guarded migration
#  and runs it. Safe online too (guarded by Schema::hasTable).
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)

$mig = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_raw_materials')) {
            Schema::create('product_raw_materials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('inventory_id');
                $table->decimal('quantity_needed', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_raw_materials');
    }
};
'@

$dir = Join-Path $root "database\migrations"
$file = Join-Path $dir "2026_10_11_000001_create_product_raw_materials_table.php"
if (Test-Path $file) {
    Write-Host "  migration already exists (skipped writing)" -ForegroundColor DarkGray
} else {
    [System.IO.File]::WriteAllText($file, $mig, $Utf8NoBom)
    Write-Host "  wrote migration: 2026_10_11_000001_create_product_raw_materials_table.php" -ForegroundColor Green
}

Write-Host ""
php artisan migrate

Write-Host ""
Write-Host "DONE. Reload /dashboard locally - it should load now (no missing-table error)." -ForegroundColor Cyan
Write-Host "(Optional) to keep the repo in sync, you can later:" -ForegroundColor DarkGray
Write-Host "  git add database/migrations/2026_10_11_000001_create_product_raw_materials_table.php" -ForegroundColor DarkGray
Write-Host "  git commit -m ""Add guarded product_raw_materials migration"" ; git push" -ForegroundColor DarkGray
