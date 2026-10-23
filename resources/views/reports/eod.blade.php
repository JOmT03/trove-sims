<x-app-layout>
<x-slot name="header">End of Day</x-slot>
<x-slot name="subheader">Daily sales, cash reconciliation &amp; close</x-slot>

@php $peso = fn($n) => "&#8369;" . number_format($n, 2); @endphp

<style>
.eod-wrap{max-width:1000px;}
.eod-tabs{display:inline-flex;background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:4px;gap:4px;margin-bottom:16px;}
.eod-tabs a{text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:var(--muted);font-family:var(--f-body);}
.eod-tabs a.on{background:var(--white);color:var(--gold);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.eod-alert{padding:12px 16px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.eod-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.eod-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.eod-bar{display:flex;align-items:flex-end;gap:12px;margin-bottom:18px;flex-wrap:wrap;}
.eod-bar label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:5px;}
.eod-bar input[type=date]{padding:9px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);}
.eod-btn{padding:10px 18px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;font-family:var(--f-body);}
.eod-btn.navy{background:var(--navy);color:#fff;}
.eod-btn.gold{background:var(--gold);color:#fff;}
.eod-btn.ghost{background:var(--white);color:var(--text);border:1px solid var(--border);}
.locked{display:flex;align-items:center;gap:10px;background:#F3EEE6;border:1px solid var(--border);border-radius:11px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--text);}
.locked b{color:var(--navy);}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
@media(max-width:720px){.kpis{grid-template-columns:repeat(2,1fr);}}
.kpi{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:16px 18px;box-shadow:0 1px 6px rgba(74,44,23,.06);}
.kpi .l{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);}
.kpi .v{font-family:var(--f-display);font-size:26px;font-weight:800;line-height:1;margin-top:8px;letter-spacing:-1px;font-variant-numeric:tabular-nums;}
.kpi .v.net{color:#166534;} .kpi .v.net.neg{color:#C2410C;}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;}
@media(max-width:720px){.grid2{grid-template-columns:1fr;}}
.card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);overflow:hidden;}
.card h3{font-family:var(--f-display);font-size:15px;padding:14px 16px;margin:0;border-bottom:1px solid var(--border);}
.row{display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--border);font-size:13px;}
.row:last-child{border-bottom:none;}
.row .k{color:var(--text);} .row .k small{color:var(--muted);}
.row .v{font-variant-numeric:tabular-nums;font-weight:600;}
.row.tot{background:var(--bg);font-weight:800;}
.row.tot .v{font-weight:800;}
.card table{width:100%;border-collapse:collapse;font-size:13px;}
.card th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:11px 16px;background:var(--bg);font-weight:700;}
.card td{padding:11px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
.card tbody tr:last-child td{border-bottom:none;}
.card .num{text-align:right;font-variant-numeric:tabular-nums;}
.pill{font-size:10px;font-weight:800;letter-spacing:.3px;padding:2px 8px;border-radius:999px;text-transform:uppercase;}
.pill.ok{background:#E7F3EA;color:#15803D;} .pill.void{background:#FBE4DA;color:#C2410C;}
tr.void td{opacity:.55;text-decoration:line-through;}
.muted{color:var(--muted);}
.empty{text-align:center;color:var(--muted);padding:26px;font-size:13px;}
.cogs-note{font-size:11.5px;color:var(--muted);padding:8px 16px 12px;line-height:1.5;}
@media print{
  .sidebar,.topbar,.no-print{display:none !important;}
  .main{margin-left:0 !important;}
  .page-content{padding:0 !important;}
  .eod-wrap{max-width:100% !important;}
  .kpis{grid-template-columns:repeat(4,1fr) !important;}
  .grid2{grid-template-columns:1fr 1fr !important;}
  .card,.kpi{box-shadow:none !important;border:1px solid #bbb !important;break-inside:avoid;}
  body{background:#fff !important;}
}
</style>

<div class="eod-wrap">
  <div class="eod-tabs no-print">
    <a href="{{ route('reports.index') }}">Weekly Batch</a>
    <a href="{{ route('reports.eod') }}" class="on">End of Day</a>
  </div>

  @if(session('success'))<div class="eod-alert ok">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="eod-alert err">{{ session('error') }}</div>@endif

  <form method="GET" action="{{ route('reports.eod') }}" class="eod-bar no-print">
    <div>
      <label>Business date</label>
      <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}">
    </div>
    <button type="submit" class="eod-btn navy">View</button>
    <button type="button" class="eod-btn gold" onclick="window.print()" style="margin-left:auto;">Print / PDF</button>
  </form>

  @if($closure)
    <div class="locked">
      &#128274;&nbsp; This day is <b>closed</b> &mdash; locked by {{ $closure->closed_by_name ?: 'Owner' }} on
      {{ \Illuminate\Support\Carbon::parse($closure->closed_at)->format('M j, Y g:i A') }}. Figures below are final.
    </div>
  @endif

  <div class="kpis">
    <div class="kpi"><div class="l">Gross Sales</div><div class="v">{!! $peso($gross) !!}</div></div>
    <div class="kpi"><div class="l">Transactions</div><div class="v">{{ $txCount }}</div></div>
    <div class="kpi"><div class="l">Items Sold</div><div class="v">{{ $itemsSold }}</div></div>
    <div class="kpi"><div class="l">Net (Sales &minus; Expenses)</div><div class="v net {{ $net < 0 ? 'neg' : '' }}">{!! $peso($net) !!}</div></div>
  </div>

  <div class="grid2">
    <div class="card">
      <h3>Payment reconciliation</h3>
      <div class="row"><span class="k">Cash</span><span class="v">{!! $peso($methods['cash']) !!}</span></div>
      <div class="row"><span class="k">GCash</span><span class="v">{!! $peso($methods['gcash']) !!}</span></div>
      <div class="row"><span class="k">Maya</span><span class="v">{!! $peso($methods['maya']) !!}</span></div>
      <div class="row"><span class="k">Bank transfer</span><span class="v">{!! $peso($methods['bank']) !!}</span></div>
      <div class="row tot"><span class="k">Total collected</span><span class="v">{!! $peso($methods['cash'] + $methods['gcash'] + $methods['maya'] + $methods['bank']) !!}</span></div>
      <div class="row"><span class="k">Cash expected in drawer <small>(cash sales)</small></span><span class="v">{!! $peso($methods['cash']) !!}</span></div>
    </div>

    <div class="card">
      <h3>Money out &amp; cost</h3>
      @forelse($expenses as $e)
        <div class="row"><span class="k">{{ $e->description }} <small>&middot; {{ ucfirst($e->category) }}</small></span><span class="v">{!! $peso($e->amount) !!}</span></div>
      @empty
        <div class="empty">No expenses recorded for this day.</div>
      @endforelse
      <div class="row tot"><span class="k">Total expenses</span><span class="v">{!! $peso($expenseTotal) !!}</span></div>
      <div class="row"><span class="k">Ingredient cost used <small>(FIFO)</small></span><span class="v">{!! $peso($cogs) !!}</span></div>
      <div class="cogs-note">FIFO cost of raw materials consumed in production today, costed oldest-stock-first. This is production cost, separate from the cash expenses above.</div>
    </div>
  </div>

  <div class="card">
    <h3>Sales this day</h3>
    @if($completed->count() || $voided->count())
      <table>
        <thead><tr><th>Sale #</th><th>Time</th><th>Cashier</th><th class="num">Items</th><th class="num">Total</th><th>Payment</th><th>Status</th></tr></thead>
        <tbody>
          @foreach($completed->concat($voided)->sortBy('id') as $s)
            <tr class="{{ $s->status === 'voided' ? 'void' : '' }}">
              <td>{{ $s->sale_number }}</td>
              <td class="muted">{{ \Illuminate\Support\Carbon::parse($s->completed_at)->format('g:i A') }}</td>
              <td class="muted">{{ $s->cashier->first_name ?? '&mdash;' }}</td>
              <td class="num">{{ $s->items->sum('quantity') }}</td>
              <td class="num">{!! $peso($s->total) !!}</td>
              <td class="muted">{{ $s->payments->pluck('method')->map(fn($m)=>ucfirst($m))->implode(' + ') }}</td>
              <td><span class="pill {{ $s->status === 'voided' ? 'void' : 'ok' }}">{{ $s->status }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="empty">No sales recorded for this day.</div>
    @endif
  </div>

  @if(!$closure && $isOwner)
    <form method="POST" action="{{ route('reports.eod.close') }}" class="no-print" style="margin-top:18px;text-align:right;"
          onsubmit="return confirm('Close {{ $date }}? This locks the day\'s figures and cannot be undone.');">
      @csrf
      <input type="hidden" name="date" value="{{ $date }}">
      <button type="submit" class="eod-btn gold">&#128274; Close this day</button>
    </form>
  @elseif(!$closure && !$isOwner)
    <div class="muted" style="margin-top:16px;font-size:12px;text-align:right;">Only the Owner can close a day.</div>
  @endif
</div>
</x-app-layout>