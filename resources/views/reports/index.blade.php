<x-app-layout>
<x-slot name="header">Reports</x-slot>
<x-slot name="subheader">Weekly batch report &mdash; products sent to Jacinto (final sale)</x-slot>

<style>
.wrap{max-width:900px;margin:0 auto;}
.filter{background:#fff;border:1px solid #EDE0D0;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.06);padding:18px;margin-bottom:20px;display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;}
.filter label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;}
.filter input{padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
.btn{padding:9px 18px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.btn-gold{background:#D9782C;color:#fff;} .btn-navy{background:#4A2C17;color:#fff;}
.report{background:#fff;border:1px solid #EDE0D0;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.06);overflow:hidden;}
.rep-head{padding:22px 24px;border-bottom:1px solid #EDE0D0;}
.rep-head h2{font-size:18px;font-weight:800;margin:0 0 4px;color:#2E1C10;}
.rep-head .range{font-size:13px;color:#8A7460;}
.sum{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:#EDE0D0;border-bottom:1px solid #EDE0D0;}
.sum div{background:#fff;padding:16px;text-align:center;}
.sum .l{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#8A7460;font-weight:700;}
.sum .n{font-size:19px;font-weight:800;margin-top:4px;font-variant-numeric:tabular-nums;}
.sum .n.disp{color:#3F5B8B;} .sum .n.net{color:#166534;} .sum .n.loss{color:#C2410C;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:right;font-size:11px;text-transform:uppercase;color:#8A7460;padding:11px 16px;background:#FDF6EC;}
th:first-child{text-align:left;}
td{padding:11px 16px;border-top:1px solid #EDE0D0;text-align:right;font-variant-numeric:tabular-nums;}
td:first-child{text-align:left;font-weight:500;}
tr.tot td{font-weight:800;background:#FDF6EC;border-top:2px solid #EDE0D0;}
.ret{color:#C2410C;font-weight:600;}
.empty{padding:50px 20px;text-align:center;color:#8A7460;}
.print-head{display:none;}
@media print{
    .sidebar,.topbar,.no-print{display:none !important;}
    .main{margin-left:0 !important;}
    .page-content{padding:0 !important;}
    .print-head{display:block;margin-bottom:16px;}
    .report{box-shadow:none;border:1px solid #999;}
    body{background:#fff !important;}
}
</style>

<div class="wrap">
    <div style="display:inline-flex;background:#FDF6EC;border:1px solid #EDE0D0;border-radius:11px;padding:4px;gap:4px;margin-bottom:16px;" class="no-print">
      <a href="{{ route('reports.index') }}" style="text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:#D9782C;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08);">Weekly Batch</a>
      <a href="{{ route('reports.eod') }}" style="text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:#8A7460;">End of Day</a>
    </div>
    <form method="GET" action="{{ route('reports.index') }}" class="filter no-print">
        <div>
            <label>From</label>
            <input type="date" name="from" value="{{ $from }}">
        </div>
        <div>
            <label>To</label>
            <input type="date" name="to" value="{{ $to }}">
        </div>
        <button type="submit" class="btn btn-navy">Generate</button>
        <button type="button" class="btn btn-gold" onclick="window.print()" style="margin-left:auto;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v4h12v-4m-12 0h12"/></svg>
            Print / Save PDF
        </button>
    </form>

    <div class="report">
        <div class="print-head">
            <div style="font-size:22px;font-weight:800;color:#4A2C17;">Trove &mdash; Food &amp; Cake Shop</div>
            <div style="font-size:12px;color:#666;">Weekly Batch Report (Matina &rarr; Jacinto)</div>
        </div>
        <div class="rep-head">
            <h2>Weekly Batch Report</h2>
            <div class="range">{{ \Illuminate\Support\Carbon::parse($from)->format('M d, Y') }} &mdash; {{ \Illuminate\Support\Carbon::parse($to)->format('M d, Y') }} &middot; {{ $batchCount }} batch(es)</div>
        </div>

        <div class="sum">
            <div><div class="l">Batches Sent</div><div class="n disp">{{ $batchCount }}</div></div>
            <div class="rkpi" onclick="repDetail('units')"><div class="l">Units Sold</div><div class="n">{{ $totals['sold'] }} pcs</div></div>
            <div class="rkpi" onclick="repDetail('sales')"><div class="l">Sales</div><div class="n net">&#8369;{{ number_format($totals['revenue'],2) }}</div></div>
        </div>

        @if(count($rows))
        <table>
            <thead><tr><th>Flavor</th><th>Units Sold</th><th>Sales</th></tr></thead>
            <tbody>
                @foreach($rows as $name => $r)
                <tr>
                    <td>{{ $name }}</td>
                    <td style="font-weight:700;">{{ $r['sold'] }}</td>
                    <td>&#8369;{{ number_format($r['revenue'],2) }}</td>
                </tr>
                @endforeach
                <tr class="tot">
                    <td>Total</td>
                    <td>{{ $totals['sold'] }}</td>
                    <td>&#8369;{{ number_format($totals['revenue'],2) }}</td>
                </tr>
            </tbody>
        </table>
        @if($legacyReturned > 0)
        <div style="padding:12px 16px;font-size:12px;color:#8A7460;border-top:1px solid #EDE0D0;">Units sold already exclude {{ $legacyReturned }} pc(s) returned on batches from before the final-sale rule (October 7, 2026).</div>
        @endif
        @else
        <div class="empty">No batches in this date range. Try a different range, or create a batch in Branch Transfers.</div>
        @endif
    </div>
</div>

<!-- KPI detail modal -->
<div class="rkpi-bg no-print" id="rkpiBg" onclick="if(event.target===this)rkpiClose()">
  <div class="rkpi-modal">
    <div class="rkpi-head"><h3 id="rkpiTitle">Detail</h3><button type="button" class="rkpi-x" onclick="rkpiClose()">&times;</button></div>
    <div class="rkpi-body"><table id="rkpiTable"></table></div>
  </div>
</div>
<style>
.rkpi{cursor:pointer;transition:background .12s ease,box-shadow .12s ease;position:relative;}
.rkpi:hover{background:#FBF2E6 !important;box-shadow:inset 0 0 0 2px #D9782C;}
.rkpi::after{content:"\0025B8";position:absolute;top:8px;right:10px;color:#C9B8A3;font-size:12px;}
.rkpi-bg{display:none;position:fixed;inset:0;background:rgba(46,28,16,.5);z-index:60;align-items:center;justify-content:center;padding:16px;}
.rkpi-bg.show{display:flex;}
.rkpi-modal{background:#fff;border-radius:14px;max-width:460px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);font-family:'Plus Jakarta Sans',system-ui,Arial,sans-serif;}
.rkpi-head{display:flex;justify-content:space-between;align-items:center;padding:15px 18px;border-bottom:1px solid #EDE0D0;}
.rkpi-head h3{margin:0;font-size:16px;color:#2E1C10;font-weight:800;}
.rkpi-x{background:none;border:none;font-size:20px;cursor:pointer;color:#8A7460;line-height:1;}
.rkpi-body{padding:6px 18px 18px;max-height:60vh;overflow:auto;}
.rkpi-body table{width:100%;border-collapse:collapse;font-size:13px;}
.rkpi-body th{text-align:right;font-size:10.5px;text-transform:uppercase;letter-spacing:.3px;color:#8A7460;padding:10px;background:#FDF6EC;}
.rkpi-body th:first-child{text-align:left;}
.rkpi-body td{padding:10px;border-top:1px solid #EDE0D0;text-align:right;font-variant-numeric:tabular-nums;}
.rkpi-body td:first-child{text-align:left;font-weight:600;}
</style>
<script>
const RKPI_ROWS = @json($rows);
function rkpiPeso(n){ return "\u20B1" + Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function rkpiClose(){ document.getElementById('rkpiBg').classList.remove('show'); }
function repDetail(metric){
  const defs = {
    sales:     {t:'Sales',      cols:['Flavor','Units Sold','Sales'], get:function(r){return [r.sold, rkpiPeso(r.revenue)];}},
    units:     {t:'Units Sold', cols:['Flavor','Units Sold (pcs)'],   get:function(r){return [r.sold];}}
  }[metric];
  if(!defs){ return; }
  document.getElementById('rkpiTitle').textContent = defs.t;
  var html = '<thead><tr>';
  defs.cols.forEach(function(ccc){ html += '<th>' + ccc + '</th>'; });
  html += '</tr></thead><tbody>';
  var keys = Object.keys(RKPI_ROWS);
  if(keys.length === 0){ html += '<tr><td colspan="'+defs.cols.length+'" style="text-align:center;color:#8A7460;">No data.</td></tr>'; }
  keys.forEach(function(name){
    var r = RKPI_ROWS[name];
    html += '<tr><td>' + name + '</td>';
    defs.get(r).forEach(function(val){ html += '<td>' + val + '</td>'; });
    html += '</tr>';
  });
  html += '</tbody>';
  document.getElementById('rkpiTable').innerHTML = html;
  document.getElementById('rkpiBg').classList.add('show');
}
</script>
</x-app-layout>
