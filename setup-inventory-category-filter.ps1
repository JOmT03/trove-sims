# ============================================================
#  TROVE - Add a Category dropdown filter to the Inventory list
#     powershell -ExecutionPolicy Bypass -File setup-inventory-category-filter.ps1
#  View-only change (resources/views/inventory/index.blade.php).
#  Client-side filter: a pill-style "All Categories" dropdown next to
#  the search box, auto-populated from the categories actually present.
#  Backs up to .bak. Safe to revert (copy .bak back).
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function BackupOnce($p){ $b=$p+".bak"; if((Test-Path $p) -and -not(Test-Path $b)){ Copy-Item $p $b } }

$f = Join-Path $root "resources\views\inventory\index.blade.php"
if (-not (Test-Path $f)) { Write-Host "ERROR: inventory/index.blade.php not found." -ForegroundColor Red; exit 1 }
BackupOnce $f

$c = ([System.IO.File]::ReadAllText($f)) -replace "`r`n", "`n"
$changes = 0

# ---------- 1. CSS: add .inv-catfilter right after .addbtn rule ----------
$anchorCss = '.addbtn{background:var(--gold);color:#fff;border:none;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;white-space:nowrap;}'
$newCss = $anchorCss + "`n" + '.inv-catfilter{padding:10px 14px;border:1px solid var(--border);border-radius:999px;font-size:13px;background:var(--white);color:var(--text);font-family:var(--f-body);cursor:pointer;}' + "`n" + '.inv-catfilter:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}'
if ($c.Contains($anchorCss) -and -not $c.Contains('.inv-catfilter{')) {
    $c = $c.Replace($anchorCss, $newCss); $changes++
    Write-Host "  [1/4] added .inv-catfilter styles" -ForegroundColor Green
} else { Write-Host "  [1/4] CSS anchor not found or already added (skipped)" -ForegroundColor DarkGray }

# ---------- 2. markup: add the <select> right after the search box ----------
$anchorSearch = '<input type="text" id="search" placeholder="Search items..." oninput="applyFilters()">' + "`n" + '        </div>'
$newSearch = '<input type="text" id="search" placeholder="Search items..." oninput="applyFilters()">' + "`n" + '        </div>' + "`n" + '        <select id="catfilter" class="inv-catfilter" onchange="applyFilters()"><option value="">All Categories</option></select>'
if ($c.Contains($anchorSearch) -and -not $c.Contains('id="catfilter"')) {
    $c = $c.Replace($anchorSearch, $newSearch); $changes++
    Write-Host "  [2/4] added Category dropdown markup" -ForegroundColor Green
} else { Write-Host "  [2/4] search anchor not found or dropdown already added (skipped)" -ForegroundColor DarkGray }

# ---------- 3. rows: add data-cat attribute ----------
$anchorRow = 'data-name="{{ strtolower($inv->item_name) }}" class="{{ $archived ? ''arch'' : '''' }}">'
$newRow = 'data-name="{{ strtolower($inv->item_name) }}" data-cat="{{ strtolower($inv->category) }}" class="{{ $archived ? ''arch'' : '''' }}">'
if ($c.Contains($anchorRow) -and -not $c.Contains('data-cat=')) {
    $c = $c.Replace($anchorRow, $newRow); $changes++
    Write-Host "  [3/4] added data-cat to rows" -ForegroundColor Green
} else { Write-Host "  [3/4] row anchor not found or data-cat already added (skipped)" -ForegroundColor DarkGray }

# ---------- 4. JS: read the category in applyFilters + populate dropdown ----------
$anchorTerm = 'var term=(document.getElementById(''search'').value||'''').toLowerCase().trim();'
$newTerm = $anchorTerm + "`n" + '    var _cf=document.getElementById(''catfilter''); window.__catSel=_cf?_cf.value.toLowerCase():'''';'
if ($c.Contains($anchorTerm) -and -not $c.Contains('window.__catSel')) {
    $c = $c.Replace($anchorTerm, $newTerm)
    Write-Host "  [4/4a] applyFilters reads category" -ForegroundColor Green
} else { Write-Host "  [4/4a] term anchor not found or already patched (skipped)" -ForegroundColor DarkGray }

$anchorShow = 'var okName = nm.indexOf(term)!==-1;' + "`n" + '        var show = okFilter && okName;'
$newShow = 'var okName = nm.indexOf(term)!==-1;' + "`n" + '        var okCat = !window.__catSel || (r.getAttribute(''data-cat'')||'''')===window.__catSel;' + "`n" + '        var show = okFilter && okName && okCat;'
if ($c.Contains($anchorShow)) {
    $c = $c.Replace($anchorShow, $newShow)
    Write-Host "  [4/4b] applyFilters applies category" -ForegroundColor Green
} else { Write-Host "  [4/4b] show anchor not found (skipped)" -ForegroundColor DarkGray }

$anchorInit = "document.addEventListener('click', closeMenus);"
$initScript = $anchorInit + "`n" + '(function(){var sel=document.getElementById("catfilter");if(!sel)return;var seen={};document.querySelectorAll(".inv-card tbody tr[data-cat]").forEach(function(r){var k=r.getAttribute("data-cat");if(!k||seen[k])return;seen[k]=1;var o=document.createElement("option");o.value=k;o.textContent=k.replace(/\b\w/g,function(m){return m.toUpperCase();});sel.appendChild(o);});})();'
if ($c.Contains($anchorInit) -and -not $c.Contains('tr[data-cat]')) {
    $c = $c.Replace($anchorInit, $initScript); $changes++
    Write-Host "  [4/4c] dropdown auto-populate added" -ForegroundColor Green
} else { Write-Host "  [4/4c] init anchor not found or already added (skipped)" -ForegroundColor DarkGray }

[System.IO.File]::WriteAllText($f, $c, $Utf8NoBom)

Write-Host ""
php artisan view:clear
php artisan optimize:clear

Write-Host ""
if ($changes -ge 3) {
    Write-Host "DONE. Reload Inventory - a 'Category' dropdown is now beside the search box." -ForegroundColor Cyan
} else {
    Write-Host "WARNING: fewer changes than expected - check the messages above." -ForegroundColor Yellow
}
Write-Host "Revert anytime: copy resources\views\inventory\index.blade.php.bak back over the file." -ForegroundColor DarkGray
Write-Host "Push only if you like it:" -ForegroundColor Cyan
Write-Host "  git add resources/views/inventory/index.blade.php" -ForegroundColor White
Write-Host "  git commit -m ""Add category filter to inventory list"" ; git push" -ForegroundColor White
