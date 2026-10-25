<x-app-layout>
    <x-slot name="header">Financial Statement</x-slot>
    <x-slot name="subheader">Income Statement &mdash; view-only, generated from recorded sales and expenses</x-slot>

@php
    $peso = fn ($n) => '₱' . number_format(abs($n), 2);
    // Signed money: -₱200.00 in red, ₱200.00 in green, ₱0.00 neutral
    $col  = fn ($n) => round($n, 2) > 0 ? 'var(--green)' : (round($n, 2) < 0 ? 'var(--red)' : 'inherit');
    $v    = fn ($n) => new \Illuminate\Support\HtmlString(
        '<span style="color:' . $col($n) . ';font-weight:700;">' . (round($n, 2) < 0 ? '-' : '') . '₱' . number_format(abs(round($n, 2)), 2) . '</span>');
    $pct  = fn ($n) => new \Illuminate\Support\HtmlString(
        '<span style="color:' . $col($n) . ';">' . ($n > 0 ? '+' : '') . $n . '%</span>');
    $row  = 'display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:14px;';
    $lbl  = 'display:block;font-size:12px;font-weight:600;margin-bottom:6px;';
@endphp

<style>
.fs-card{background:var(--white);padding:24px;border-radius:12px;border:1px solid var(--border);margin-bottom:24px;}
.fs-print-only{display:none;}
.fs-steps li{margin-bottom:8px;font-size:13px;line-height:1.5;}
@media print{
    .sidebar,.topbar,.no-print{display:none !important;}
    .main{margin-left:0 !important;}
    .page-content{padding:0 !important;}
    body{background:#fff !important;}
    .fs-card{box-shadow:none;border:1px solid #999;page-break-inside:avoid;}
    .fs-print-only{display:block;}
}
</style>

@if(session('success'))<div class="no-print" style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:16px;">{{ session('success') }}</div>@endif
@if(session('error'))<div class="no-print" style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;">{{ session('error') }}</div>@endif
@if($errors->any())<div class="no-print" style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

{{-- Period selector --}}
<div class="fs-card no-print">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div><label style="{{ $lbl }}">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;"></div>
        <div><label style="{{ $lbl }}">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;"></div>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;font-weight:700;padding:9px 18px;border-radius:6px;cursor:pointer;">Generate</button>
        <a href="{{ route('financial-statement.index') }}" style="font-size:13px;color:var(--navy);padding:9px 6px;">This month</a>
        <a href="{{ route('financial-statement.index', ['from'=>now()->subMonthNoOverflow()->startOfMonth()->toDateString(),'to'=>now()->subMonthNoOverflow()->endOfMonth()->toDateString()]) }}" style="font-size:13px;color:var(--navy);padding:9px 6px;">Last month</a>
        <a href="{{ route('financial-statement.index', ['from'=>now()->startOfYear()->toDateString(),'to'=>now()->toDateString()]) }}" style="font-size:13px;color:var(--navy);padding:9px 6px;">Year to date</a>
        <button type="button" onclick="window.print()" style="margin-left:auto;background:var(--gold);color:var(--navy);border:none;font-weight:700;padding:9px 18px;border-radius:6px;cursor:pointer;">🖨 Print / Save as PDF</button>
    </form>
</div>

{{-- Status banner --}}
@if($closure)
    <div class="fs-card" style="border-left:5px solid {{ ($integrity['hash_ok'] && $integrity['figures_ok']) ? '#16a34a' : '#dc2626' }};">
        <strong>🔒 {{ $closure->period->format('F Y') }} is CLOSED and locked.</strong>
        <div style="font-size:13px;color:var(--muted);margin-top:4px;">Closed by {{ $closure->closed_by_name }} on {{ $closure->closed_at->format('M d, Y g:i A') }}. No sales, expenses or returns in this month can be added, changed or voided.</div>
        <div style="font-size:13px;margin-top:8px;">
            Integrity check:
            @if($integrity['hash_ok'] && $integrity['figures_ok'])
                <strong style="color:#16a34a;">✔ Verified</strong> &mdash; the figures below match the frozen snapshot and the record chain is intact.
            @else
                <strong style="color:#dc2626;">✖ MISMATCH</strong> &mdash;
                {{ !$integrity['figures_ok'] ? 'live figures differ from the figures frozen at closing. ' : '' }}{{ !$integrity['hash_ok'] ? 'the closing record itself was altered. ' : '' }}Investigate immediately.
            @endif
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:6px;font-family:monospace;word-break:break-all;">Seal: {{ $closure->hash }}</div>
    </div>
@else
    <div class="fs-card" style="border-left:5px solid #d97706;">
        <strong>PROVISIONAL &mdash; this period is still open.</strong>
        <div style="font-size:13px;color:var(--muted);margin-top:4px;">Figures can still change while sales and expenses are being recorded. Close the month to make this statement final.</div>
    </div>
@endif

{{-- The statement --}}
<div class="fs-card">
    <div style="text-align:center;margin-bottom:20px;">
        <div style="font-family:var(--f-display);font-size:22px;font-weight:800;">Trove</div>
        <div style="font-size:16px;font-weight:700;">Income Statement</div>
        <div style="font-size:13px;color:var(--muted);">For the period {{ $from->format('F j, Y') }} to {{ $to->format('F j, Y') }}</div>
        <div style="font-size:12px;color:var(--muted);">Amounts in Philippine Peso (₱)</div>
    </div>

    <div style="max-width:620px;margin:0 auto;">
        <div style="font-weight:800;text-transform:uppercase;font-size:12px;color:var(--muted);margin-top:8px;">Revenue</div>
        <div style="{{ $row }}"><span>Counter sales (POS)</span><span>{!! $v($s['posRevenue'] ?? 0) !!}</span></div>
        <div style="{{ $row }}"><span>Custom orders (completed)</span><span>{!! $v($s['orderRevenue']) !!}</span></div>
        <div style="{{ $row }}"><span>Branch transfers (final sale)</span><span>{!! $v($s['branchRevenue']) !!}</span></div>
        <div style="{{ $row }}font-weight:800;border-bottom:2px solid var(--navy);"><span>Total Revenue</span><span>{!! $v($s['revenue']) !!}</span></div>

        <div style="font-weight:800;text-transform:uppercase;font-size:12px;color:var(--muted);margin-top:18px;">Less: Cost of Goods Sold</div>
        @forelse($s['cogsLines'] as $cat => $amt)
            <div style="{{ $row }}"><span style="padding-left:14px;">{{ ucfirst($cat) }}</span><span>{!! $v(-$amt) !!}</span></div>
        @empty
            <div style="{{ $row }}color:var(--muted);"><span style="padding-left:14px;">None recorded</span><span>—</span></div>
        @endforelse
        <div style="{{ $row }}font-weight:700;"><span>Total COGS</span><span>{!! $v(-$s['cogs']) !!}</span></div>

        <div style="{{ $row }}font-weight:800;border-bottom:2px solid var(--navy);margin-top:8px;"><span>Gross Profit @if($s['grossMargin'] !== null)<small style="font-weight:400;">({!! $pct($s['grossMargin']) !!} margin)</small>@endif</span><span>{!! $v($s['grossProfit']) !!}</span></div>

        <div style="font-weight:800;text-transform:uppercase;font-size:12px;color:var(--muted);margin-top:18px;">Less: Operating Expenses</div>
        @forelse($s['opexLines'] as $cat => $amt)
            <div style="{{ $row }}"><span style="padding-left:14px;">{{ ucfirst($cat) }}</span><span>{!! $v(-$amt) !!}</span></div>
        @empty
            <div style="{{ $row }}color:var(--muted);"><span style="padding-left:14px;">None recorded</span><span>—</span></div>
        @endforelse
        <div style="{{ $row }}font-weight:700;"><span>Total Operating Expenses</span><span>{!! $v(-$s['opex']) !!}</span></div>

        <div style="display:flex;justify-content:space-between;padding:12px 0;margin-top:10px;font-size:18px;font-weight:800;border-top:2px solid var(--navy);border-bottom:4px double var(--navy);">
            <span>Net Income @if($s['netMargin'] !== null)<small style="font-weight:400;">({!! $pct($s['netMargin']) !!} margin)</small>@endif</span>
            <span>{!! $v($s['netIncome']) !!}</span>
        </div>

        @if($s['voidedCount'] > 0)
            <div style="font-size:12px;color:var(--muted);margin-top:12px;">Note: {{ $s['voidedCount'] }} voided expense {{ \Illuminate\Support\Str::plural('entry', $s['voidedCount']) }} totalling {{ $peso($s['voidedTotal']) }} {{ $s['voidedCount'] > 1 ? 'are' : 'is' }} excluded from the figures above and remain visible in the Expenses ledger and Audit Log.</div>
        @endif
    </div>

    <div class="fs-print-only" style="margin-top:40px;">
        <div style="display:flex;justify-content:space-around;font-size:12px;">
            <div style="text-align:center;width:40%;"><div style="border-top:1px solid #000;padding-top:4px;">Prepared by: {{ $preparedBy }}</div></div>
            <div style="text-align:center;width:40%;"><div style="border-top:1px solid #000;padding-top:4px;">Reviewed / approved by</div></div>
        </div>
        <div style="text-align:center;font-size:11px;color:#666;margin-top:12px;">Generated {{ now()->format('M d, Y g:i A') }} &middot; {{ $closure ? 'FINAL (closed period) · Seal ' . substr($closure->hash, 0, 16) : 'PROVISIONAL (open period)' }}</div>
    </div>
</div>

{{-- Close month (Owner) --}}
@if($canClose)
<div class="fs-card no-print" style="border:1px dashed var(--navy);">
    <h3 style="font-family:var(--f-display);font-size:18px;margin-bottom:6px;">Close {{ $from->format('F Y') }}</h3>
    <p style="font-size:13px;color:var(--muted);margin-bottom:12px;">This freezes the figures above and permanently locks {{ $from->format('F Y') }}: no new expenses can be dated in it, none can be voided, and related orders/branch returns can no longer change. <strong>This cannot be undone.</strong> Corrections after closing are recorded in the current open month.</p>
    <form method="POST" action="{{ route('financial-statement.close') }}" onsubmit="return confirm('Close and permanently lock {{ $from->format('F Y') }}? This cannot be undone.')">
        @csrf
        <input type="hidden" name="month" value="{{ $from->format('Y-m') }}">
        <button type="submit" style="background:var(--navy);color:#fff;border:none;font-weight:700;padding:10px 20px;border-radius:6px;cursor:pointer;">🔒 Close &amp; lock month</button>
    </form>
</div>
@endif

{{-- Clear process --}}
<div class="fs-card no-print">
    <h3 style="font-family:var(--f-display);font-size:18px;margin-bottom:10px;">How this Financial Statement is produced</h3>
    <ol class="fs-steps" style="padding-left:18px;">
        <li><strong>Sales are captured automatically.</strong> A counter sale counts as revenue when it is completed at the POS (a voided sale drops out). A custom order counts once it is marked <em>Completed</em> (dated by the completion date). A branch transfer is a <em>final sale</em>: pieces sent &times; unit price, dated by the batch date (a voided batch drops out). Pending or unfinished orders and deposits are not revenue yet.</li>
        <li><strong>Expenses are logged in the Expenses ledger</strong> with date, category, amount, optional receipt and optional stock purchase. Ingredients and packaging are <em>Cost of Goods Sold</em>; everything else is <em>Operating Expenses</em>.</li>
        <li><strong>Entries are permanent.</strong> Nothing can be edited or deleted. A wrong entry is <em>voided</em> with a written reason; it stays on record, is struck through, and drops out of the totals. A future-dated expense is not allowed.</li>
        <li><strong>The statement is calculated, not typed.</strong> Gross Profit = Revenue − COGS. Net Income = Gross Profit − Operating Expenses. This page is view-only; no one can change a figure here.</li>
        <li><strong>The Owner closes each month.</strong> Closing freezes the totals, locks the month, and seals the record with a tamper-evident code chained to the previous month. This page re-checks the seal every time a closed month is opened.</li>
        <li><strong>Everything is traceable.</strong> Every recording, voiding, receipt view, order completion and month closing is written to the <a href="{{ route('financial-audit-log.index') }}" style="color:var(--navy);text-decoration:underline;">Audit Log</a> with who, when and from where.</li>
    </ol>
    <p style="font-size:12px;color:var(--muted);">Access: Owner and Manager only. Only the Owner can close a month.</p>
</div>

{{-- Closed months --}}
@if($closures->count())
<div class="fs-card no-print">
    <h3 style="font-family:var(--f-display);font-size:18px;margin-bottom:10px;">Closed months</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
        <thead><tr style="border-bottom:2px solid var(--border);color:var(--muted);"><th style="padding:8px;">Month</th><th style="padding:8px;text-align:right;">Revenue</th><th style="padding:8px;text-align:right;">Net income</th><th style="padding:8px;">Closed by</th><th style="padding:8px;"></th></tr></thead>
        <tbody>
        @foreach($closures as $c)
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px;">{{ $c->period->format('F Y') }}</td>
                <td style="padding:8px;text-align:right;">{!! $v($c->revenue) !!}</td>
                <td style="padding:8px;text-align:right;>{!! $v($c->net_income) !!}</td>
                <td style="padding:8px;">{{ $c->closed_by_name }}</td>
                <td style="padding:8px;"><a style="color:var(--navy);" href="{{ route('financial-statement.index', ['from'=>$c->period->toDateString(),'to'=>$c->period->copy()->endOfMonth()->toDateString()]) }}">View</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
</x-app-layout>
