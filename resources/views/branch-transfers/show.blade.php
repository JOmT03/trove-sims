<x-app-layout>
<x-slot name="header">Batch &mdash; {{ $batch->batch_date->format('M d, Y') }}</x-slot>
<x-slot name="subheader">{{ $batch->sourceSite->site_name ?? 'Matina' }} &rarr; {{ $batch->destinationSite->site_name ?? 'Jacinto' }}</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;margin-bottom:20px;}
.card-pad{padding:22px;}
.sum{background:#fff;border-radius:12px;border:1px solid #EDE0D0;padding:18px;text-align:center;margin-bottom:20px;}
.sum .l{font-size:10.5px;text-transform:uppercase;letter-spacing:.5px;color:#8A7460;font-weight:700;}
.sum .n{font-weight:800;font-size:26px;margin-top:4px;color:#166534;font-variant-numeric:tabular-nums;}
.sum .n.void{color:#9ca3af;text-decoration:line-through;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:right;font-size:11px;text-transform:uppercase;color:#8A7460;padding:10px 14px;background:#FDF6EC;}
th:first-child{text-align:left;}
td{padding:11px 14px;border-top:1px solid #EDE0D0;text-align:right;font-variant-numeric:tabular-nums;}
td:first-child{text-align:left;font-weight:500;}
tr.tot td{font-weight:800;background:#FDF6EC;border-top:2px solid #EDE0D0;}
.ret{color:#C2410C;font-weight:600;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-red{background:#B91C1C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:4px;}
.badge{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:999px;}
.badge.sent{background:#E7F3EA;color:#166534;} .badge.void{background:#f3f4f6;color:#6b7280;}
.reason{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;margin-bottom:12px;}
</style>

@php
    $isVoid = !empty($voidInfo->voided_at);
    $hasRet = $batch->items->sum('qty_returned') > 0;
    $total = $batch->items->sum(fn($i) => ($i->qty_sent - $i->qty_returned) * $i->unit_price);
    $qtySent = $batch->items->sum('qty_sent');
    $qtyRet = $batch->items->sum('qty_returned');
@endphp

<div style="max-width:850px;margin:0 auto;">
@if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert-err">@foreach($errors->all() as $e){{ $e }} @endforeach</div>@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline">&larr; Branch Transfers</a>
    <span class="badge {{ $isVoid ? 'void' : 'sent' }}">{{ $isVoid ? 'Voided' : 'Sent' }}</span>
</div>

<div class="sum">
    <div class="l">Total Sale</div>
    <div class="n {{ $isVoid ? 'void' : '' }}">&#8369;{{ number_format($total,2) }}</div>
</div>

@if($isVoid)
<div class="alert-err"><strong>Voided:</strong> {{ $voidInfo->void_reason }}</div>
@endif

<div class="card">
    <div class="card-pad" style="padding-bottom:0;"><div class="card-title">Items Sent</div>
    <p style="font-size:12px;color:#8A7460;margin-bottom:14px;">{{ $hasRet ? 'Older batch: returns were logged before the final-sale rule.' : 'Final sale. No returns and no reconciliation.' }}</p></div>
    <table>
        <thead><tr><th>Flavor</th><th>Sent</th>@if($hasRet)<th>Returned</th>@endif<th>Unit Price</th><th>Amount</th></tr></thead>
        <tbody>
            @foreach($batch->items as $item)
            <tr>
                <td>{{ $item->product->product_name ?? '&mdash;' }}</td>
                <td>{{ $item->qty_sent }}</td>
                @if($hasRet)<td class="ret">{{ $item->qty_returned }}</td>@endif
                <td>&#8369;{{ number_format($item->unit_price,2) }}</td>
                <td>&#8369;{{ number_format(($item->qty_sent - $item->qty_returned) * $item->unit_price,2) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td>Total</td>
                <td>{{ $qtySent }}</td>
                @if($hasRet)<td>{{ $qtyRet }}</td>@endif
                <td></td>
                <td>&#8369;{{ number_format($total,2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

@can('admin')
@if(!$isVoid)
<div class="card">
    <div class="card-pad">
        <div class="card-title">Void this batch</div>
        <p style="font-size:12px;color:#8A7460;margin-bottom:14px;">Use only if the send was a mistake (wrong qty or price). Stock goes back to Matina and the sale is removed from revenue.</p>
        <form method="POST" action="{{ route('branch-transfers.void', $batch) }}" onsubmit="return confirm('Void this batch? Stock will be restored.');">
            @csrf
            <input type="text" name="reason" class="reason" placeholder="Reason (required)" required minlength="3" maxlength="500">
            <div style="text-align:right;"><button type="submit" class="btn btn-red">Void Batch</button></div>
        </form>
    </div>
</div>
@endif
@endcan

</div>
</x-app-layout>