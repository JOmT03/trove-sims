<x-app-layout>
<x-slot name="header">Send to Branch</x-slot>
<x-slot name="subheader">Final sale: products sent from Matina to Jacinto</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;padding:9px 12px;}
td{padding:8px 12px;border-bottom:1px solid #f3f4f6;vertical-align:top;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.addrow{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:600;cursor:pointer;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.rm{background:none;border:none;color:#ef4444;cursor:pointer;font-size:15px;}
.avail{font-size:11px;margin-top:5px;font-weight:600;}
.avail.ok{color:#166534;} .avail.low{color:#C2410C;}
.hint{font-size:12px;color:#8A7460;margin-bottom:14px;}
</style>

@php
    $productData = $products->map(function ($p) {
        return ['id' => (int) $p->id, 'name' => $p->product_name, 'price' => $p->price, 'stock' => (int) $p->stock_quantity];
    })->values();
    $orderData = $orders->map(function ($o) {
        return [
            'id'    => (int) $o->id,
            'items' => $o->items->map(fn($i) => [
                'product_id' => (int) $i->product_id,
                'qty'        => (int) $i->qty,
                'balance'    => (int) $i->balance,
            ])->values(),
        ];
    })->values();
@endphp

<div style="max-width:920px;margin:0 auto;">
@if($errors->any())
<div class="alert-err"><strong>Please fix:</strong>
<ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif

<form method="POST" action="{{ route('branch-transfers.store') }}">
@csrf

<div class="card">
    <div class="card-title">Batch Details</div>
    <div class="grid">
        <div>
            <label>From (Source) *</label>
            <select name="source_site_id">
                @foreach($sites as $s)<option value="{{ $s->id }}" {{ stripos($s->site_name,'matina')!==false?'selected':'' }}>{{ $s->site_name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label>To (Destination) *</label>
            <select name="destination_site_id">
                @foreach($sites as $s)<option value="{{ $s->id }}" {{ stripos($s->site_name,'jacinto')!==false?'selected':'' }}>{{ $s->site_name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label>Send Date *</label>
            <input type="date" name="batch_date" value="{{ old('batch_date', date('Y-m-d')) }}" required>
        </div>
    </div>
    <div style="margin-top:16px;">
        <label>Fulfill Order</label>
        <select name="branch_order_id" id="orderSel" onchange="loadOrder()">
            <option value="">- No order (manual) -</option>
            @foreach($orders as $o)
            <option value="{{ $o->id }}" {{ (string)($selected ?? '') === (string)$o->id ? 'selected' : '' }}>#{{ $o->id }} &middot; needed {{ $o->needed_by->format('M d') }} &middot; ordered {{ $o->ordered_qty }}, balance {{ $o->balance_qty }} ({{ ucfirst($o->status) }})</option>
            @endforeach
        </select>
        <p class="hint" style="margin:6px 0 0;">Pick an order and the product list shows only what Jacinto ordered, with the remaining balance.</p>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Products Sent</div>
        <button type="button" class="addrow" onclick="addRow()">+ Add Product</button>
    </div>
    <p class="hint">Final sale. It is counted as revenue right away and cannot be returned. If you send less than the balance you can still send. The shortage is noted and stays on the order for the next batch.</p>
    <table>
        <thead><tr><th style="width:46%">Product</th><th>Qty Sent</th><th>Unit Price (&#8369;)</th><th></th></tr></thead>
        <tbody id="rows"></tbody>
    </table>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold" onclick="return confirmSend();">Send Batch</button>
</div>
</form>

<script>
const products = {!! $productData->toJson() !!};
const orders = {!! $orderData->toJson() !!};
let i = 0;
let cur = null;

function allowed(){
    if(!cur){
        return products.map(function(p){ return {id:p.id, price:p.price, stock:p.stock, bal:'', label:p.name+' ('+p.stock+' on hand)'}; });
    }
    return cur.items.map(function(it){
        const p = products.find(function(x){ return x.id === it.product_id; });
        if(!p) return null;
        return {id:p.id, price:p.price, stock:p.stock, bal:it.balance,
                label:p.name+' \u2014 ordered '+it.qty+', balance '+it.balance+' ('+p.stock+' on hand)'};
    }).filter(Boolean);
}
function opts(){
    return allowed().map(function(a){
        return '<option value="'+a.id+'" data-price="'+a.price+'" data-stock="'+a.stock+'" data-bal="'+a.bal+'">'+a.label+'</option>';
    }).join('');
}
function addRow(pid){
    const n = i++;
    const tr = document.createElement('tr');
    tr.id = 'r'+n;
    tr.innerHTML =
        '<td>' +
            '<select name="items['+n+'][product_id]" id="sel'+n+'" onchange="pick(this,'+n+')" required>' +
                '<option value="">- Select -</option>' + opts() +
            '</select>' +
            '<div class="avail" id="avail'+n+'"></div>' +
        '</td>' +
        '<td><input type="number" id="qty'+n+'" name="items['+n+'][qty_sent]" min="1" value="1" oninput="chk('+n+')" required></td>' +
        '<td><input type="number" step="0.01" min="0" id="price'+n+'" name="items['+n+'][unit_price]" required></td>' +
        '<td><button type="button" class="rm" onclick="document.getElementById(\'r'+n+'\').remove()">&times;</button></td>';
    document.getElementById('rows').appendChild(tr);
    if(pid){
        const sel = document.getElementById('sel'+n);
        sel.value = String(pid);
        pick(sel, n);
    }
}
function pick(sel,n){
    const o = sel.options[sel.selectedIndex];
    const price = o.dataset.price;
    const stock = parseInt(o.dataset.stock || '0', 10);
    const bal = o.dataset.bal;
    if(price) document.getElementById('price'+n).value = price;
    const qty = document.getElementById('qty'+n);
    const av = document.getElementById('avail'+n);
    if(sel.value){
        qty.max = stock;
        if(stock <= 0){ av.textContent = 'Out of stock - cannot send'; av.className='avail low'; qty.value = 0; return; }
        let q = stock;
        if(bal !== '' && bal !== undefined){ q = Math.min(parseInt(bal,10), stock); }
        qty.value = q;
        chk(n);
    } else { av.textContent=''; qty.max=''; }
}
function chk(n){
    const sel = document.getElementById('sel'+n);
    const o = sel.options[sel.selectedIndex];
    const av = document.getElementById('avail'+n);
    if(!sel.value) return;
    const stock = parseInt(o.dataset.stock || '0', 10);
    const bal = o.dataset.bal;
    const q = parseInt(document.getElementById('qty'+n).value || '0', 10);
    let msg = 'Available to send: ' + stock + ' pcs';
    let cls = 'avail ok';
    if(bal !== '' && bal !== undefined){
        const b = parseInt(bal,10);
        if(q > b){ msg += ' | Over the order by ' + (q-b); cls = 'avail low'; }
        else if(q < b){ msg += ' | Short by ' + (b-q) + ' (to be delivered in the next batch)'; }
        else { msg += ' | Matches the order'; }
    }
    if(q > stock){ msg = 'Only ' + stock + ' on hand'; cls = 'avail low'; }
    av.textContent = msg; av.className = cls;
}
function confirmSend(){
    const shorts = [];
    document.querySelectorAll('#rows tr').forEach(function(tr){
        const sel = tr.querySelector('select');
        const qty = tr.querySelector('input[type=number]');
        if(!sel || !sel.value) return;
        const o = sel.options[sel.selectedIndex];
        const bal = o.dataset.bal;
        if(bal === '' || bal === undefined) return;
        const b = parseInt(bal, 10);
        const q = parseInt(qty.value || '0', 10);
        if(q < b){ shorts.push(o.text.split(' \u2014')[0] + ' short ' + (b - q)); }
    });
    let m = 'Send this batch? It is a final sale.';
    if(shorts.length){ m += '\n\nShort: ' + shorts.join(', ') + '.\nThe shortage will be noted and delivered in the next batch.'; }
    return confirm(m);
}
function loadOrder(){
    const id = parseInt(document.getElementById('orderSel').value || '0', 10);
    document.getElementById('rows').innerHTML = '';
    cur = id ? (orders.find(function(x){ return x.id === id; }) || null) : null;
    if(!cur){ addRow(); return; }
    cur.items.forEach(function(it){ if(it.balance > 0) addRow(it.product_id); });
}
loadOrder();
</script>
</x-app-layout>