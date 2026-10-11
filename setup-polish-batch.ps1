# ============================================================
#  TROVE - Polish batch: 4 UI fixes in one run
#     powershell -ExecutionPolicy Bypass -File setup-polish-batch.ps1
#  1) Products: Filter button -> icon only (remove the word "Filter")
#  2) Inventory: remove the Site field (create + edit)
#  3) Expenses: remove Link Inventory + Restock Qty fields (+ Linked Item column)
#  4) Dashboard: remove the descriptive subtitles under each KPI card
#  Every file backed up to .bakp. Safe to re-run (guarded).
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function BackupOnce($p){ $b=$p+".bakp"; if((Test-Path $p) -and -not(Test-Path $b)){ Copy-Item $p $b } }
function ReadLF($p){ return ([System.IO.File]::ReadAllText($p)) -replace "`r`n","`n" }
function RxRemove($text,$pattern){ return [System.Text.RegularExpressions.Regex]::Replace($text,$pattern,'',[System.Text.RegularExpressions.RegexOptions]::Singleline) }

# ---------- 1) Products: filter button icon-only ----------
$pf = Join-Path $root "resources\views\products\index.blade.php"
if (Test-Path $pf) {
    BackupOnce $pf
    $c = ReadLF $pf
    $oldBtn = '            <button class="filter-btn" onclick="toggleFilter(event)">' + "`n" +
              '                <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>' + "`n" +
              '                Filter' + "`n" +
              '            </button>'
    $newBtn = '            <button class="filter-btn" onclick="toggleFilter(event)" title="Filter" aria-label="Filter">' + "`n" +
              '                <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>' + "`n" +
              '            </button>'
    if ($c.Contains($oldBtn)) {
        $c = $c.Replace($oldBtn,$newBtn)
        $c = $c.Replace('gap:7px;background:var(--gold)','gap:0;background:var(--gold)')  # tighten icon-only pill
        [System.IO.File]::WriteAllText($pf,$c,$Utf8NoBom)
        Write-Host "  [1] Products filter button is now icon-only" -ForegroundColor Green
    } elseif ($c.Contains('aria-label="Filter"')) {
        Write-Host "  [1] Products filter already icon-only (skipped)" -ForegroundColor DarkGray
    } else { Write-Host "  [1] WARNING: filter button not found - skipped" -ForegroundColor Yellow }
} else { Write-Host "  [1] products/index.blade.php not found - skipped" -ForegroundColor Yellow }

# ---------- 2) Inventory: remove Site field (create + edit) ----------
$sitePat = '(?s)\s*<div[^>]*>\s*<label>\s*Site\s*</label>.*?</div>'
foreach ($rel in @("resources\views\inventory\create.blade.php","resources\views\inventory\edit.blade.php")) {
    $f = Join-Path $root $rel
    if (-not (Test-Path $f)) { Write-Host ("  [2] not found: "+$rel) -ForegroundColor Yellow; continue }
    BackupOnce $f
    $c = ReadLF $f
    if ($c -match '<label>\s*Site\s*</label>') {
        $c = RxRemove $c $sitePat
        [System.IO.File]::WriteAllText($f,$c,$Utf8NoBom)
        Write-Host ("  [2] removed Site field from "+(Split-Path $f -Leaf)) -ForegroundColor Green
    } else { Write-Host ("  [2] "+(Split-Path $f -Leaf)+": no Site field (skipped)") -ForegroundColor DarkGray }
}

# ---------- 3) Expenses: remove Link Inventory + Restock Qty + Linked Item column ----------
$expFile = $null
foreach ($rel in @("resources\views\expenses\index.blade.php","resources\views\expenses\create.blade.php")) {
    $p = Join-Path $root $rel
    if ((Test-Path $p) -and ((ReadLF $p) -match 'Link Inventory')) { $expFile = $p; break }
}
if ($expFile) {
    BackupOnce $expFile
    $c = ReadLF $expFile
    $c = RxRemove $c '(?s)\s*<div>\s*<label[^>]*>Link Inventory \(Optional\)</label>.*?</select>\s*</div>'
    $c = RxRemove $c '(?s)\s*<div>\s*<label[^>]*>Restock Qty \(Optional\)</label>.*?</div>'
    $c = $c.Replace('                    <th style="padding: 10px;">Linked Item</th>' + "`n",'')
    $c = $c.Replace('<th style="padding: 10px;">Linked Item</th>','')
    $c = RxRemove $c '(?s)\s*<td style="padding: 10px;">\{\{ \$expense->inventoryItem->item_name.*?</td>'
    $c = $c.Replace('colspan="6"','colspan="5"')
    [System.IO.File]::WriteAllText($expFile,$c,$Utf8NoBom)
    Write-Host ("  [3] removed Link Inventory / Restock Qty from "+(Split-Path $expFile -Leaf)) -ForegroundColor Green
} else { Write-Host "  [3] expenses view with 'Link Inventory' not found - skipped" -ForegroundColor Yellow }

# ---------- 4) Dashboard: remove KPI subtitles ----------
$dashFile = $null
foreach ($rel in @("resources\views\dashboard.blade.php","resources\views\dashboard\index.blade.php")) {
    $p = Join-Path $root $rel
    if ((Test-Path $p) -and ((ReadLF $p) -match 'dispatched to Jacinto')) { $dashFile = $p; break }
}
if ($dashFile) {
    BackupOnce $dashFile
    $c = ReadLF $dashFile
    $subs = @(
        '<div class="s">dispatched to Jacinto</div>',
        '<div class="s">&#8369;{{ number_format($netSoldRevenue,2) }} &middot; {{ $returnedQty }} returned</div>',
        '<div class="s">need restock at Matina</div>',
        '<div class="s">across {{ $totalProducts }} products</div>'
    )
    $n = 0
    foreach ($su in $subs) { if ($c.Contains($su)) { $c = $c.Replace($su,''); $n++ } }
    [System.IO.File]::WriteAllText($dashFile,$c,$Utf8NoBom)
    Write-Host ("  [4] removed "+$n+" dashboard subtitle(s) from "+(Split-Path $dashFile -Leaf)) -ForegroundColor Green
} else { Write-Host "  [4] dashboard view not found - skipped" -ForegroundColor Yellow }

Write-Host ""
php artisan view:clear
php artisan optimize:clear
Write-Host ""
Write-Host "DONE locally. Test each page, then push:" -ForegroundColor Cyan
Write-Host "  git add -A ; git commit -m ""Polish: icon-only filter, remove Site field, trim Expenses & Dashboard"" ; git push" -ForegroundColor White
Write-Host "Revert any file: copy its .bakp back over it." -ForegroundColor DarkGray
