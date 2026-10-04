<x-app-layout>
<x-slot name="header">{{ $commission->customer_name }}</x-slot>
<x-slot name="subheader">{{ ucfirst($commission->client_type) }} commission</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;margin-bottom:20px;}
.card-pad{padding:22px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:14px;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.btn{padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;} .btn-gold{background:#D9782C;color:#fff;}
.info{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
.info .l{font-size:10.5px;text-transform:uppercase;letter-spacing:.5px;color:#8A7460;font-weight:700;}
.info .v{font-size:15px;font-weight:700;color:#2E1C10;margin-top:4px;}
.track{display:flex;align-items:center;margin:4px 0;flex-wrap:wrap;gap:4px;}
.node{display:flex;flex-direction:column;align-items:center;gap:5px;flex:1;min-width:60px;}
.node .dot{width:16px;height:16px;border-radius:50%;background:#FDF6EC;border:2px solid #EDE0D0;}
.node.done .dot{background:#D9782C;border-color:#D9782C;}
.node.cur .dot{background:#6D4C9F;border-color:#6D4C9F;box-shadow:0 0 0 4px rgba(109,76,159,.2);}
.node .lbl{font-size:10.5px;color:#8A7460;}
.node.done .lbl,.node.cur .lbl{color:#2E1C10;font-weight:700;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:right;font-size:11px;text-transform:uppercase;color:#8A7460;padding:10px 14px;background:#FDF6EC;}
th:first-child{text-align:left;}
td{padding:11px 14px;border-top:1px solid #EDE0D0;text-align:right;font-variant-numeric:tabular-nums;}
td:first-child{text-align:left;font-weight:500;}
tr.tot td{font-weight:800;background:#FDF6EC;}
.money{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:#EDE0D0;border-radius:12px;overflow:hidden;border:1px solid #EDE0D0;}
.money div{background:#fff;padding:14px;text-align:center;}
.money .l{font-size:10px;text-transform:uppercase;color:#8A7460;font-weight:700;}
.money .n{font-size:18px;font-weight:800;margin-top:4px;font-variant-numeric:tabular-nums;}
.money .n.bal{color:#C2410C;}
select{padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
.type-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;}
.t-ind{background:#EDE6F5;color:#6D4C9F;} .t-inst{background:#E6ECF5;color:#3F5B8B;}
</style>

<div style="max-width:820px;margin:0 auto;">
@if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <a href="{{ route('commissions.index') }}" class="btn btn-outline">&larr; All Commissions</a>
    <span class="type-badge {{ $commission->client_type==='institutional'?'t-inst':'t-ind' }}">{{ ucfirst($commission->client_type) }}</span>
</div>

<div class="card"><div class="card-pad">
    <div class="info">
        <div><div class="l">Order Type</div><div class="v">{{ $commission->order_type ?: 'Custom cake' }}</div></div>
        <div><div class="l">Needed By</div><div class="v">{{ $commission->needed_by_date ? $commission->needed_by_date->format('M d, Y') : 'Not set' }}</div></div>
        <div><div class="l">Status</div><div class="v">{{ $commission->status }}</div></div>
    </div>
    @if($commission->design_description)
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #EDE0D0;">
        <div class="l" style="font-size:10.5px;text-transform:uppercase;color:#8A7460;font-weight:700;">Design</div>
        <div style="margin-top:5px;font-size:13.5px;color:#374151;">{{ $commission->design_description }}</div>
    </div>
    @endif
</div></div>

<div class="card"><div class="card-pad">
    <div class="card-title">Progress</div>
    @php $stages = \App\Models\Commission::STAGES; $curIndex = array_search($commission->status, $stages); @endphp
    <div class="track">
        @foreach($stages as $idx => $stage)
            <div class="node {{ $idx < $curIndex ? 'done' : ($idx === $curIndex ? 'cur' : '') }}">
                <span class="dot"></span><span class="lbl">{{ $stage }}</span>
            </div>
        @endforeach
    </div>
    @can('admin')
    <form method="POST" action="{{ route('commissions.status', $commission) }}" style="margin-top:16px;display:flex;gap:10px;align-items:center;justify-content:flex-end;">
        @csrf @method('PUT')
        <label style="font-size:12px;color:#8A7460;font-weight:700;">Update stage:</label>
        <select name="status">
            @foreach($stages as $stage)<option value="{{ $stage }}" {{ $commission->status===$stage?'selected':'' }}>{{ $stage }}</option>@endforeach
        </select>
        <button type="submit" class="btn btn-gold">Save</button>
    </form>
    @endcan
</div></div>

<div class="card">
    <div class="card-pad" style="padding-bottom:0;"><div class="card-title">Products</div></div>
    <table>
        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
            @foreach($commission->items as $it)
            <tr><td>{{ $it->product->product_name ?? '-' }}</td><td>{{ $it->quantity }}</td><td>&#8369;{{ number_format($it->price,2) }}</td><td>&#8369;{{ number_format($it->quantity*$it->price,2) }}</td></tr>
            @endforeach
            <tr class="tot"><td colspan="3">Total</td><td>&#8369;{{ number_format($commission->total_amount,2) }}</td></tr>
        </tbody>
    </table>
</div>

<div class="money">
    <div><div class="l">Total</div><div class="n">&#8369;{{ number_format($commission->total_amount,2) }}</div></div>
    <div><div class="l">Downpayment</div><div class="n">&#8369;{{ number_format($commission->deposit_amount,2) }}</div></div>
    <div><div class="l">Balance</div><div class="n bal">&#8369;{{ number_format($commission->balance(),2) }}</div></div>
</div>

</div>
</x-app-layout>