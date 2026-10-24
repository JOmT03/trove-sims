<x-app-layout>
<x-slot name="header">Branch Orders</x-slot>
<x-slot name="subheader">Orders from Jacinto &mdash; produce only what is ordered</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #EDE0D0;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;padding:9px 12px;}
td{padding:8px 12px;border-bottom:1px solid #f3f4f6;vertical-align:top;}
.btn{padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-block;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.btn-green{background:#166534;color:#fff;} .btn-red{background:#fff;color:#B91C1C;border:1px solid #fecaca;}
.addrow{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:600;cursor:pointer;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.badge{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:999px;background:#FBEBD6;color:#B45309;}
.badge.produced{background:#E0E7FF;color:#3730A3;} .badge.partial{background:#FEF3C7;color:#92400E;}
.badge.complete{background:#E7F3EA;color:#166534;} .badge.cancelled{background:#f3f4f6;color:#6b7280;}
.rm{background:none;border:none;color:#ef4444;cursor:pointer;font-size:15px;}
.ord{border:1px solid #EDE0D0;border-radius:10px;padding:14px;margin-bottom:12px;}
.line{font-size:13px;padding:2px 0;} .bal{color:#C2410C;font-weight:700;}
details summary{cursor:pointer;font-size:12px;color:#8A7460;margin-top:10px;}
</style>

@php
    $productData = $products->map(fn($p) => ['id' => $p->id, 'name' => $p->product_name]);
@endphp

<div style="max-width:920px;margin:0 auto;">
@if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert-err"><ul style="margin:0 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-title">Open Orders (produce and send these)</div>
    @forelse($open as $o)
    <div class="ord">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:8px;">
            <div><strong>#{{ $o->id }}</strong> &middot; needed by <strong>{{ $o->needed_by->format('M d, Y') }}</strong>
                <span class="badge {{ $o->status }}">{{ ucfirst($o->status) }}</span></div>
            <div style="display:flex;gap:8px;">
                @if($o->status === 'requested')
                <form method="POST" action="{{ route('branch-orders.produce', $o) }}">@csrf<button class="btn btn-outline">Mark Produced</button></form>
                @endif
                <a href="{{ route('branch-transfers.create', ['order' => $o->id]) }}" class="btn btn-green">Send</a>
                @if(in_array($o->status, ['requested','produced']))
                <form method="POST" action="{{ route('branch-orders.cancel', $o) }}" onsubmit="return confirm('Cancel this order?');">@csrf<button class="btn btn-red">Cancel</button></form>
                @endif
            </div>
        </div>
        @foreach($o->items as $it)
        <div class="line">{{ $it->product->product_name ?? '?' }}: ordered <strong>{{ $it->qty }}</strong> &middot; sent <strong>{{ $it->delivered }}</strong> &middot; balance <span class="{{ $it->balance > 0 ? 'bal' : '' }}">{{ $it->balance }}</span></div>
        @endforeach
        @if($o->notes)<div style="font-size:12px;color:#8A7460;margin-top:6px;">{{ $o->notes }}</div>@endif

        @if($o->balance_qty > 0)
        <details>
            <summary>Close short ({{ $o->balance_qty }} pcs will not be delivered)</summary>
            <form method="POST" action="{{ route('branch-orders.close-short', $o) }}" style="margin-top:10px;display:flex;gap:8px;" onsubmit="return confirm('Close this order? The balance will NOT be sold.');">
                @csrf
                <input type="text" name="reason" placeholder="Reason (required)" required minlength="3" maxlength="500">
                <button class="btn btn-red" style="white-space:nowrap;">Close short</button>
            </form>
        </details>
        @endif
    </div>
    @empty
    <p style="font-size:13px;color:#8A7460;">No open orders.</p>
    @endforelse
</div>

<div class="card">
    <div class="card-title">Record New Order</div>
    <form method="POST" action="{{ route('branch-orders.store') }}">
        @csrf
        <div class="grid">
            <div>
                <label>Branch *</label>
                <select name="destination_site_id">
                    @foreach($sites as $s)<option value="{{ $s->id }}" {{ stripos($s->site_name,'jacinto')!==false?'selected':'' }}>{{ $s->site_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label>Needed By *</label>
                <input type="date" name="needed_by" value="{{ old('needed_by', date('Y-m-d', strtotime('+1 day'))) }}" required>
            </div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <label style="margin:0;">Products</label>
            <button type="button" class="addrow" onclick="addRow()">+ Add Product</button>
        </div>
        <table><thead><tr><th style="width:65%">Product</th><th>Qty</th><th></th></tr></thead><tbody id="rows"></tbody></table>
        <div style="margin-top:14px;"><label>Notes</label><textarea name="notes" rows="2" maxlength="500" placeholder="e.g. ordered via Messenger"></textarea></div>
        <div style="text-align:right;margin-top:14px;"><button type="submit" class="btn btn-gold">Save Order</button></div>
    </form>
</div>

<div class="card">
    <div class="card-title">Recent (Complete / Cancelled)</div>
    @forelse($recent as $o)
    <div style="font-size:13px;padding:8px 0;border-bottom:1px solid #f3f4f6;">
        #{{ $o->id }} &middot; {{ $o->needed_by->format('M d') }} &middot; ordered {{ $o->ordered_qty }}, sent {{ $o->sent_qty }}
        <span class="badge {{ $o->status }}">{{ ucfirst($o->status) }}</span>
        @if($o->short_reason)<div style="font-size:12px;color:#C2410C;margin-top:3px;">Closed short: {{ $o->short_reason }}</div>@endif
    </div>
    @empty
    <p style="font-size:13px;color:#8A7460;">Nothing yet.</p>
    @endforelse
</div>
</div>

<script>
const products = {!! $productData->toJson() !!};
let i = 0;
function addRow(){
    const n = i++;
    const tr = document.createElement('tr');
    tr.id = 'r'+n;
    tr.innerHTML =
        '<td><select name="items['+n+'][product_id]" required><option value="">- Select -</option>' +
        products.map(function(p){ return '<option value="'+p.id+'">'+p.name+'</option>'; }).join('') + '</select></td>' +
        '<td><input type="number" name="items['+n+'][qty]" min="1" value="1" required></td>' +
        '<td><button type="button" class="rm" onclick="document.getElementById(\'r'+n+'\').remove()">&times;</button></td>';
    document.getElementById('rows').appendChild(tr);
}
addRow();
</script>
</x-app-layout>