# ============================================================
#  TROVE - Settings module (Profile -> Settings: Profile / Security / Backup / Help)
#     powershell -ExecutionPolicy Bypass -File setup-settings-module.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Setting up the Settings module..." -ForegroundColor Yellow

# ---- 1. SettingsController (backup download + restore, Owner only) ----
$ctrl = @'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private function ownerOnly(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Owner', 403, 'Owner only.');
    }

    public function downloadBackup()
    {
        $this->ownerOnly();

        $sql  = $this->buildSqlDump();
        $base = 'trove_backup_' . date('Y-m-d_His');

        if (class_exists('\ZipArchive')) {
            $zipPath = storage_path('app/' . $base . '.zip');
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('database.sql', $sql);
                $imgDir = storage_path('app/public/products');
                if (is_dir($imgDir)) {
                    foreach (glob($imgDir . DIRECTORY_SEPARATOR . '*') as $f) {
                        if (is_file($f)) {
                            $zip->addFile($f, 'photos/' . basename($f));
                        }
                    }
                }
                $zip->close();
                return response()->download($zipPath, $base . '.zip')->deleteFileAfterSend(true);
            }
        }

        // Fallback: plain .sql
        return response($sql, 200, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $base . '.sql"',
        ]);
    }

    private function buildSqlDump(): string
    {
        $pdo = DB::getPdo();
        $out  = "-- Trove backup " . date('Y-m-d H:i:s') . "\n";
        $out .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = [];
        foreach (DB::select('SHOW TABLES') as $row) {
            $vals = array_values((array) $row);
            $tables[] = $vals[0];
        }

        foreach ($tables as $table) {
            $createRow = DB::select('SHOW CREATE TABLE `' . $table . '`');
            $createArr = (array) $createRow[0];
            $createSql = $createArr['Create Table'] ?? ($createArr['Create View'] ?? '');
            if ($createSql === '') { continue; }

            $out .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
            $out .= $createSql . ";\n\n";

            $rows = DB::select('SELECT * FROM `' . $table . '`');
            foreach ($rows as $r) {
                $r    = (array) $r;
                $cols = array_map(function ($c) { return '`' . $c . '`'; }, array_keys($r));
                $vals = array_map(function ($v) use ($pdo) {
                    if (is_null($v)) return 'NULL';
                    return $pdo->quote((string) $v);
                }, array_values($r));
                $out .= "INSERT INTO `" . $table . "` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
            }
            $out .= "\n";
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }

    public function restoreBackup(Request $request)
    {
        $this->ownerOnly();
        $request->validate(['backup' => 'required|file']);

        $file = $request->file('backup');
        $ext  = strtolower($file->getClientOriginalExtension());
        $sql  = null;

        try {
            if ($ext === 'zip' && class_exists('\ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) === true) {
                    $sql = $zip->getFromName('database.sql');
                    $zip->close();
                }
                if ($sql === false || $sql === null) {
                    return back()->with('error', 'Could not find database.sql inside the backup zip.');
                }
            } else {
                $sql = file_get_contents($file->getRealPath());
            }

            DB::unprepared($sql);
            return back()->with('success', 'Backup restored successfully. You may need to sign in again.');
        } catch (\Exception $e) {
            \Log::error('Restore failed: ' . $e->getMessage());
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}
'@
$p = Join-Path $root "app\Http\Controllers\SettingsController.php"
[System.IO.File]::WriteAllText($p, $ctrl, $Utf8NoBom)
Write-Host "  wrote app\Http\Controllers\SettingsController.php" -ForegroundColor Green

# ---- 2. Settings view (reframe profile/edit.blade.php; keep Breeze partials) ----
$viewDir = Join-Path $root "resources\views\profile"
$viewPath = Join-Path $viewDir "edit.blade.php"
if (Test-Path $viewPath) {
    Copy-Item $viewPath (Join-Path $viewDir "edit.blade.php.bak") -Force
    Write-Host "  backed up original edit.blade.php -> edit.blade.php.bak" -ForegroundColor DarkGray
}
$view = @'
<x-app-layout>
<x-slot name="header">Settings</x-slot>
<x-slot name="subheader">Manage your account, security, and help</x-slot>

<style>
.set-wrap{max-width:960px;margin:0 auto;display:grid;grid-template-columns:210px 1fr;gap:22px;align-items:start;}
.set-rail{display:flex;flex-direction:column;gap:4px;}
.set-rail button{display:flex;align-items:center;gap:10px;text-align:left;border:none;background:none;font-family:var(--f-body);font-size:13.5px;font-weight:600;color:var(--text);padding:11px 13px;border-radius:10px;cursor:pointer;width:100%;}
.set-rail button:hover{background:#FDF6EC;}
.set-rail button.active{background:var(--gold);color:#fff;}
.set-rail svg{width:17px;height:17px;flex-shrink:0;}
.set-rail .ownertag{margin-left:auto;font-size:9px;font-weight:700;letter-spacing:.3px;color:var(--gold);background:#FDF6EC;border-radius:5px;padding:2px 6px;text-transform:uppercase;}
.set-rail button.active .ownertag{color:#fff;background:rgba(255,255,255,.22);}
.set-panel{background:#fff;border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.06);}
.set-h2{font-family:var(--f-display);font-weight:800;font-size:17px;margin:0 0 3px;color:var(--text);}
.set-sub{font-size:12.5px;color:var(--muted);margin:0 0 18px;}
.set-bk-info{font-size:13px;color:var(--text);background:#FDF6EC;border:1px solid var(--border);border-radius:11px;padding:13px 15px;line-height:1.5;}
.set-btn{display:inline-flex;align-items:center;gap:8px;border:none;background:var(--gold);color:#fff;font-family:var(--f-body);font-weight:700;font-size:13.5px;padding:11px 18px;border-radius:10px;cursor:pointer;text-decoration:none;}
.set-btn:hover{background:#B5651D;}
.set-btn.ghost{background:#fff;color:var(--text);border:1px solid var(--border);}
.set-card2{display:flex;gap:16px;align-items:center;background:#FDF6EC;border:1px solid var(--border);border-radius:13px;padding:17px;}
.set-ico{width:52px;height:64px;flex-shrink:0;border-radius:8px;background:linear-gradient(150deg,#D9782C,#B5651D);display:grid;place-items:center;color:#fff;}
.set-warn{background:#FBE7E7;border-left:3px solid #C62828;border-radius:11px;padding:13px 15px;font-size:12.5px;color:#5c2a22;line-height:1.5;}
.set-tip{background:#F3DCC4;border-left:3px solid #D9782C;border-radius:11px;padding:13px 15px;font-size:12.5px;line-height:1.5;color:var(--text);}
.set-chips{display:flex;flex-wrap:wrap;gap:6px;}
.set-chip{font-size:11px;font-weight:600;color:var(--text);background:#fff;border:1px solid var(--border);border-radius:999px;padding:3px 10px;}
.set-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.set-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.set-inside-h{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
@media(max-width:760px){.set-wrap{grid-template-columns:1fr;}.set-rail{flex-direction:row;overflow-x:auto;}}
</style>

<div class="set-wrap">
  <nav class="set-rail" id="setRail">
    <button data-t="profile" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>Profile</button>
    <button data-t="security"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Security</button>
    @if(auth()->user()->role === 'Owner')
    <button data-t="backup"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>Backup &amp; Restore<span class="ownertag">Owner</span></button>
    @endif
    <button data-t="help"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 .3c0 1.7-2.5 2-2.5 3.7M12 17h.01"/></svg>Help &amp; User Manual</button>
  </nav>

  <div>
    <section class="set-panel" data-p="profile">
      @include('profile.partials.update-profile-information-form')
      <hr style="margin:26px 0;border:none;border-top:1px solid var(--border);">
      @include('profile.partials.delete-user-form')
    </section>

    <section class="set-panel" data-p="security" hidden>
      @include('profile.partials.update-password-form')
    </section>

    @if(auth()->user()->role === 'Owner')
    <section class="set-panel" data-p="backup" hidden>
      <h2 class="set-h2">Backup &amp; Restore</h2>
      <p class="set-sub">Keep a safe copy of your data in case anything happens to this computer.</p>
      @if(session('success'))<div class="set-ok">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="set-err">{{ session('error') }}</div>@endif
      <div class="set-bk-info">A backup includes <strong>all your records</strong> (products, inventory, orders, batches, expenses, users) and your <strong>product photos</strong>, saved as a single .zip file.</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:16px 0 18px;">
        <a href="{{ route('settings.backup.download') }}" class="set-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>Download Backup</a>
        <form method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" onsubmit="return confirm('Restoring will OVERWRITE the current data with the backup. Continue?');">
          @csrf
          <input type="file" name="backup" accept=".zip,.sql" required style="font-size:12px;">
          <button type="submit" class="set-btn ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21V9m0 0 4 4m-4-4-4 4M4 3h16"/></svg>Restore</button>
        </form>
      </div>
      <div class="set-tip" style="margin-bottom:12px;"><strong>KEEP IT SAFE:</strong> After downloading, save a copy to a USB drive or Google Drive. Back up weekly, or right after a big change like a reconciliation.</div>
      <div class="set-warn"><strong>RESTORING REPLACES CURRENT DATA.</strong> Only restore to recover from data loss. You may be signed out afterward.</div>
    </section>
    @endif

    <section class="set-panel" data-p="help" hidden>
      <h2 class="set-h2">Help &amp; User Manual</h2>
      <p class="set-sub">The complete guide to using Trove. Open it anytime you need a refresher.</p>
      <div class="set-card2">
        <div class="set-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4a2 2 0 0 1 2-2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M14 2v5h5M8 13h8M8 17h6"/></svg></div>
        <div><div style="font-family:var(--f-display);font-weight:800;font-size:15px;">Trove User Manual</div><div style="font-size:12px;color:var(--muted);margin-top:3px;">PDF &bull; Owner edition</div></div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin:16px 0 18px;">
        <a href="{{ asset('docs/Trove_User_Manual.pdf') }}" target="_blank" class="set-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14 21 3"/></svg>Open Manual</a>
        <a href="{{ asset('docs/Trove_User_Manual.pdf') }}" download class="set-btn ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>Download PDF</a>
      </div>
      <div class="set-inside-h">What is inside</div>
      <div class="set-chips">
        <span class="set-chip">Logging In</span><span class="set-chip">Dashboard</span><span class="set-chip">Commissions</span><span class="set-chip">Products</span><span class="set-chip">Inventory</span><span class="set-chip">Branch Transfers</span><span class="set-chip">Expenses</span><span class="set-chip">Reports</span><span class="set-chip">User Management</span><span class="set-chip">Archiving</span>
      </div>
    </section>
  </div>
</div>

<script>
(function(){
  var rail=document.getElementById('setRail');
  if(!rail) return;
  rail.querySelectorAll('button').forEach(function(b){
    b.addEventListener('click',function(){
      var t=b.getAttribute('data-t');
      rail.querySelectorAll('button').forEach(function(x){x.classList.toggle('active',x===b);});
      document.querySelectorAll('[data-p]').forEach(function(p){p.hidden=p.getAttribute('data-p')!==t;});
    });
  });
})();
</script>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($viewPath, $view, $Utf8NoBom)
Write-Host "  wrote resources\views\profile\edit.blade.php (sectioned Settings)" -ForegroundColor Green

# ---- 3. Routes: add backup download/restore (guarded) ----
$webPath = Join-Path $root "routes\web.php"
$web = [System.IO.File]::ReadAllText($webPath)
if ($web -notmatch "settings\.backup\.download") {
    $routes = @'

// ===== Settings: Backup & Restore (Owner only) =====
Route::middleware(['auth', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/settings/backup/download', [\App\Http\Controllers\SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::post('/settings/backup/restore', [\App\Http\Controllers\SettingsController::class, 'restoreBackup'])->name('settings.backup.restore');
});
'@
    $web = $web.TrimEnd() + "`r`n" + $routes + "`r`n"
    [System.IO.File]::WriteAllText($webPath, $web, $Utf8NoBom)
    Write-Host "  web.php: added backup routes" -ForegroundColor Green
} else {
    Write-Host "  web.php: backup routes already present (skipped)" -ForegroundColor DarkGray
}

# ---- 4. Navbar: rename Profile -> Settings (line that links to profile.edit) ----
$appPath = Join-Path $root "resources\views\layouts\app.blade.php"
if (Test-Path $appPath) {
    $app = [System.IO.File]::ReadAllText($appPath)
    $lines = [regex]::Split($app, "\r?\n")
    $changed = 0
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match "profile\.edit" -and $lines[$i] -cmatch "Profile") {
            $lines[$i] = $lines[$i] -creplace "Profile", "Settings"
            $changed++
        }
    }
    if ($changed -gt 0) {
        $app = ($lines -join "`r`n")
        [System.IO.File]::WriteAllText($appPath, $app, $Utf8NoBom)
        Write-Host "  navbar: renamed Profile -> Settings ($changed line)" -ForegroundColor Green
    } else {
        Write-Host "  navbar: could not find the Profile nav label - rename it to Settings manually." -ForegroundColor Yellow
    }
} else {
    Write-Host "  navbar: app.blade.php not found - skipped." -ForegroundColor Yellow
}

# ---- 5. public/docs for the user manual PDF ----
$docsDir = Join-Path $root "public\docs"
New-Item -ItemType Directory -Force -Path $docsDir | Out-Null
$dl = Join-Path $env:USERPROFILE "Downloads\Trove_User_Manual.pdf"
$dest = Join-Path $docsDir "Trove_User_Manual.pdf"
if ((Test-Path $dl) -and -not (Test-Path $dest)) {
    Copy-Item $dl $dest -Force
    Write-Host "  copied Trove_User_Manual.pdf from Downloads into public\docs" -ForegroundColor Green
} elseif (Test-Path $dest) {
    Write-Host "  public\docs\Trove_User_Manual.pdf already present" -ForegroundColor DarkGray
} else {
    Write-Host "  NOTE: put Trove_User_Manual.pdf into public\docs\ so the Help button works." -ForegroundColor Yellow
}

# ---- 6. Clear caches ----
Write-Host ""
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - Profile is now Settings (Profile / Security / Backup / Help)." -ForegroundColor Cyan
Write-Host "  Open Settings from the navbar, then refresh (Ctrl+F5)." -ForegroundColor White
