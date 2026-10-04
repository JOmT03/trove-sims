<x-app-layout>
<x-slot name="header">Batch &mdash; {{ $batch->batch_date->format('M d, Y') }}</x-slot>
<x-slot name="subheader">{{ $batch->sourceSite->site_name ?? 'Matina' }} &rarr; {{ $batch->destinationSite->site_name ?? 'Jacinto' }}</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;margin-bottom:20px;}
.card-pad{padding:22px;}
.sum{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:#EDE0D0;border-radius:12px;overflow:hidden;border:1px solid #EDE0D0;margin-bottom:20px;}
.sum div{background:#fff;padding:16px;text-align:center;}
.sum .l{font-size:10.5px;text-transform:uppercase;letter-spacing:.5px;color:#8A7460;font-weight:700;}
.sum .n{font-weight:800;font-size:22px;margin-top:4px;font-variant-numeric:tabular-nums;}
.sum .n.disp{color:#3F5B8B;} .sum .n.net{color:#166534;} .sum .n.loss{color:#C2410C;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:right;font-size:11px;text-transform:uppercase;color:#8A7460;padding:10px 14px;background:#FDF6EC;}
th:first-child{text-align:left;}
td{padding:11px 14px;border-top:1px solid #EDE0D0;text-align:right;font-variant-numeric:tabular-nums;}
td:first-child{text-align:left;font-weight:500;}
tr.tot td{font-weight:800;background:#FDF6EC;border-top:2px solid #EDE0D0;}
.ret{color:#C2410C;font-weight:600;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.ret-input{width:70px;padding:7px 9px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:13px;text-align:right;background:#f8fafc;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:4px;}
.badge{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:999px;}
.badge.sent{background:#FBEBD6;color:#B45309;} .badge.rec{background:#E7F3EA;color:#166534;}
</style>

<div style="max-width:850px;margin:0 auto;">
@if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline">&larr; All Batches</a>
    <span class="badge {{ $batch->status === 'reconciled' ? 'rec' : 'sent' }}">{{ ucfirst($batch->status) }}</span>
</div>

<div class="sum">
    <div><div class="l">Dispatched</div><div class="n disp">&#8369;{{ number_format($batch->dispatchedValue(),2) }}</div></div>
    <div><div class="l">Net Sold</div><div class="n net">&#8369;{{ number_format($batch->totalRevenue(),2) }}</div></div>
    <div><div class="l">Returned (loss)</div><div class="n loss">&#8369;{{ number_format($batch->lossValue(),2) }}</div></div>
</div>

<div class="card">
    <div class="card-pad" style="padding-bottom:0;"><div class="card-title">Reconciliation &mdash; Sent vs. Returned</div>
    <p style="font-size:12px;color:#8A7460;margin-bottom:14px;">Net Sold = Sent - Returned, computed offline.</p></div>
    <table>
        <thead><tr><th>Flavor</th><th>Sent</th><th>Returned</th><th>Net Sold</th><th>Revenue</th></tr></thead>
        <tbody>
            @foreach($batch->items as $item)
            <tr>
                <td>{{ $item->product->product_name ?? '&mdash;' }}</td>
                <td>{{ $item->qty_sent }}</td>
                <td class="ret">{{ $item->qty_returned }}</td>
                <td style="font-weight:700;">{{ $item->netSold() }}</td>
                <td>&#8369;{{ number_format($item->revenue(),2) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td>Total</td>
                <td>{{ $batch->totalSent() }}</td>
                <td>{{ $batch->totalReturned() }}</td>
                <td>{{ $batch->totalNetSold() }}</td>
                <td>&#8369;{{ number_format($batch->totalRevenue(),2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

@can('admin')
<div class="card">
    <div class="card-pad">
        <div class="card-title">Log Return Items</div>
        <p style="font-size:12px;color:#8A7460;margin-bottom:16px;">Enter how many of each flavor came back unsold from Jacinto. Net Sold updates automatically.</p>
        <form method="POST" action="{{ route('branch-transfers.returns', $batch) }}">
            @csrf @method('PUT')
            <table>
                <thead><tr><th>Flavor</th><th>Sent</th><th style="text-align:right;">Returned (unsold)</th></tr></thead>
                <tbody>
                    @foreach($batch->items as $item)
                    <tr>
                        <td>{{ $item->product->product_name ?? '&mdash;' }}</td>
                        <td>{{ $item->qty_sent }}</td>
                        <td><input type="number" class="ret-input" name="returns[{{ $item->id }}]" min="0" max="{{ $item->qty_sent }}" value="{{ $item->qty_returned }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="text-align:right;margin-top:16px;">
                <button type="submit" class="btn btn-gold">Save Returns &amp; Reconcile</button>
            </div>
        </form>
    </div>
</div>
@endcan

</div>
</x-app-layout>