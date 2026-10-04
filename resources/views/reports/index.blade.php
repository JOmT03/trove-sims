<x-app-layout>
<x-slot name="header">Reports</x-slot>
<x-slot name="subheader">Weekly batch report &mdash; sent, returned &amp; net sold</x-slot>

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
.sum{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#EDE0D0;border-bottom:1px solid #EDE0D0;}
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
            <div><div class="l">Dispatched</div><div class="n disp">&#8369;{{ number_format($dispatched,2) }}</div></div>
            <div><div class="l">Net Sold</div><div class="n net">&#8369;{{ number_format($totals['revenue'],2) }}</div></div>
            <div><div class="l">Returned (loss)</div><div class="n loss">&#8369;{{ number_format($loss,2) }}</div></div>
            <div><div class="l">Units Sold</div><div class="n">{{ $totals['net'] }} pcs</div></div>
        </div>

        @if(count($rows))
        <table>
            <thead><tr><th>Flavor</th><th>Sent</th><th>Returned</th><th>Net Sold</th><th>Revenue</th></tr></thead>
            <tbody>
                @foreach($rows as $name => $r)
                <tr>
                    <td>{{ $name }}</td>
                    <td>{{ $r['sent'] }}</td>
                    <td class="ret">{{ $r['returned'] }}</td>
                    <td style="font-weight:700;">{{ $r['net'] }}</td>
                    <td>&#8369;{{ number_format($r['revenue'],2) }}</td>
                </tr>
                @endforeach
                <tr class="tot">
                    <td>Total</td>
                    <td>{{ $totals['sent'] }}</td>
                    <td>{{ $totals['returned'] }}</td>
                    <td>{{ $totals['net'] }}</td>
                    <td>&#8369;{{ number_format($totals['revenue'],2) }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <div class="empty">No batches in this date range. Try a different range, or create a batch in Branch Transfers.</div>
        @endif
    </div>
</div>
</x-app-layout>