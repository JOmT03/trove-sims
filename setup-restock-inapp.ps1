# ============================================================
#  TROVE - Restock List: move it INTO the app shell (x-app-layout)
#     powershell -ExecutionPolicy Bypass -File setup-restock-inapp.ps1
#  Replaces the standalone printable page with a normal in-app page
#  (real sidebar/navbar). Print CSS hides the chrome for a clean PDF.
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
Write-Host "Moving the Restock List into the app layout..." -ForegroundColor Yellow

$viewPath = Join-Path $root "resources\views\inventory\low-stock-report.blade.php"
if (-not (Test-Path $viewPath)) {
    Write-Host "ERROR: low-stock-report.blade.php not found. Run setup-lowstock-report.ps1 first." -ForegroundColor Red; exit 1
}

# Backup the original standalone version once
$bak = $viewPath + ".bak"
if (-not (Test-Path $bak)) {
    Copy-Item $viewPath $bak
    Write-Host "  backed up original to low-stock-report.blade.php.bak" -ForegroundColor DarkGray
}

$view = @'
<x-app-layout>
<style>
  .rl-wrap{--gold:#D9782C;--gold-dark:#B5651D;--navy:#4A2C17;--border:#EBDCCA;--muted:#8A7460;--soft:#F3DCC4;--surface:#fff;--ink:#2E1C10;--out:#991B1B;--crit:#C2410C;--low:#B45309;max-width:1000px;margin:0 auto;padding:8px 4px 40px;color:var(--ink);font-family:'Plus Jakarta Sans',system-ui,Arial,sans-serif;}
  .rl-wrap *{box-sizing:border-box}
  .rl-crumb{font-size:12.5px;color:var(--muted);margin-bottom:10px}
  .rl-crumb a{color:var(--muted);text-decoration:none}
  .rl-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px}
  .rl-head h1{font-weight:800;font-size:23px;margin:0}
  .rl-head .s{font-size:13px;color:var(--muted);margin-top:3px}
  .rl-btns{display:flex;gap:9px}
  .rl-btn{display:inline-flex;align-items:center;gap:7px;border-radius:10px;padding:10px 16px;font-weight:700;font-size:13.5px;cursor:pointer;text-decoration:none;font-family:inherit;border:1px solid transparent}
  .rl-btn.gold{background:var(--gold);color:#fff}
  .rl-btn.gold:hover{background:var(--gold-dark)}
  .rl-btn.outline{background:var(--surface);color:var(--navy);border-color:var(--border)}
  .rl-sum{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
  .rl-sc{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px 18px}
  .rl-sc .n{font-weight:800;font-size:27px;line-height:1}
  .rl-sc .k{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-top:7px}
  .rl-sc.out .n{color:var(--out)}.rl-sc.crit .n{color:var(--crit)}.rl-sc.low .n{color:var(--low)}
  .rl-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden}
  .rl-ch{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:8px}
  .rl-ch .ttl{font-weight:800;font-size:16px}
  .rl-ch .dt{font-size:12px;color:var(--muted)}
  .rl-wrap table{width:100%;border-collapse:collapse;font-size:13.5px}
  .rl-wrap thead th{text-align:right;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);background:#FDF6EC;padding:11px 16px;font-weight:700}
  .rl-wrap thead th:first-child,.rl-wrap thead th:nth-child(2){text-align:left}
  .rl-wrap tbody td{padding:12px 16px;border-top:1px solid var(--border);text-align:right}
  .rl-wrap tbody td:first-child{text-align:left;font-weight:600}
  .rl-wrap tbody td:nth-child(2){text-align:left;color:var(--muted)}
  .rl-badge{display:inline-block;font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;text-transform:uppercase}
  .rl-badge.out{background:#FBE0E0;color:var(--out)}.rl-badge.crit{background:#FBE4DA;color:var(--crit)}.rl-badge.low{background:#FBEBD6;color:var(--low)}
  .rl-short{font-weight:800;color:var(--gold-dark)}
  .rl-foot{padding:14px 20px;border-top:1px solid var(--border);font-size:11.5px;color:var(--muted)}
  .rl-empty{padding:44px 20px;text-align:center;color:var(--muted);font-size:14px}
  @media (max-width:640px){.rl-sum{grid-template-columns:repeat(2,1fr)}}
  @media print{
    body *{visibility:hidden !important}
    #restock-print,#restock-print *{visibility:visible !important}
    #restock-print{position:absolute;left:0;top:0;width:100%;padding:0}
    .rl-noprint{display:none !important}
  }
</style>

<div id="restock-print" class="rl-wrap">
  <div class="rl-crumb"><a href="{{ route('inventory.index') }}">Inventory</a> &rsaquo; Restock List</div>

  <div class="rl-head">
    <div>
      <h1>Restock List</h1>
      <div class="s">Low &amp; critical ingredients to buy</div>
    </div>
    <div class="rl-btns rl-noprint">
      <a href="{{ route('inventory.index') }}" class="rl-btn outline">&larr; Back to Inventory</a>
      <button type="button" class="rl-btn gold" onclick="window.print()">Print / Save PDF</button>
    </div>
  </div>

  @php
    $out  = $items->filter(fn($i)=>(float)$i->quantity_on_hand<=0)->count();
    $crit = $items->filter(fn($i)=>(float)$i->quantity_on_hand>0 && (float)$i->quantity_on_hand<=(float)$i->minimum_stock)->count();
    $low  = $items->filter(fn($i)=>(float)$i->quantity_on_hand>(float)$i->minimum_stock)->count();
  @endphp

  <div class="rl-sum">
    <div class="rl-sc out"><div class="n">{{ $out }}</div><div class="k">Out of stock</div></div>
    <div class="rl-sc crit"><div class="n">{{ $crit }}</div><div class="k">Critical</div></div>
    <div class="rl-sc low"><div class="n">{{ $low }}</div><div class="k">Low</div></div>
    <div class="rl-sc"><div class="n">{{ $items->count() }}</div><div class="k">Items to buy</div></div>
  </div>

  <div class="rl-card">
    <div class="rl-ch"><span class="ttl">Ingredients to restock</span><span class="dt">Generated {{ now()->format('M d, Y g:i A') }}</span></div>
    @if($items->count())
    <table>
      <thead><tr><th>Ingredient</th><th>Category</th><th>Current</th><th>Min stock</th><th>Shortfall</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($items as $it)
          @php
            $on=(float)$it->quantity_on_hand; $min=(float)$it->minimum_stock;
            $tier = $on<=0 ? 'out' : ($on<=$min ? 'crit' : 'low');
            $labels=['out'=>'Out','crit'=>'Critical','low'=>'Low'];
            $short = max(0, $min - $on);
          @endphp
          <tr>
            <td>{{ $it->item_name }}</td>
            <td>{{ $it->category }}</td>
            <td>{{ rtrim(rtrim(number_format($on,2),'0'),'.') }} {{ $it->unit }}</td>
            <td>{{ rtrim(rtrim(number_format($min,2),'0'),'.') }} {{ $it->unit }}</td>
            <td class="rl-short">{{ $short>0 ? '+'.rtrim(rtrim(number_format($short,2),'0'),'.').' '.$it->unit : '-' }}</td>
            <td><span class="rl-badge {{ $tier }}">{{ $labels[$tier] }}</span></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="rl-foot">Shortfall = how much below the minimum. Buy at least this much to get back to the minimum level.</div>
    @else
    <div class="rl-empty">All ingredients are above their minimum stock. Nothing to restock right now.</div>
    @endif
  </div>
</div>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($viewPath, $view, $Utf8NoBom)
Write-Host "  rewrote low-stock-report.blade.php as an in-app page" -ForegroundColor Green

Write-Host ""
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE - Inventory -> Restock List now opens inside the app (with the sidebar)." -ForegroundColor Cyan
Write-Host "  Tip: the 'Restock List' button still opens it; remove target=_blank if you want it in the same tab." -ForegroundColor White
