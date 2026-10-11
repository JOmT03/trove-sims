<x-app-layout>
<x-slot name="header">New Commission</x-slot>
<x-slot name="subheader">Record a custom or institutional cake order</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;}
textarea{min-height:70px;resize:vertical;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;padding:9px 12px;}
td{padding:8px 12px;border-bottom:1px solid #f3f4f6;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.addrow{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:600;cursor:pointer;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.rm{background:none;border:none;color:#ef4444;cursor:pointer;font-size:15px;}
.totcell{font-weight:700;font-variant-numeric:tabular-nums;color:#2E1C10;}
.sumrow{display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:14px;}
.sumrow .lbl{color:#8A7460;font-weight:700;}
.sumrow .val{font-weight:800;color:#2E1C10;font-variant-numeric:tabular-nums;}
.grand .lbl,.grand .val{font-size:17px;}
.hint{font-size:12px;color:#8A7460;margin-top:6px;}
.secnote{font-size:12px;color:#8A7460;margin:-6px 0 12px;}
.divline{border:none;border-top:1px solid #EDE0D0;margin:14px 0;}
</style>

@php
    $productData = $products->map(function ($p) {
        return ['id' => $p->id, 'name' => $p->product_name, 'price' => $p->price];
    });
@endphp

<div style="max-width:900px;margin:0 auto;">
@if($errors->any())
<div class="alert-err"><strong>Please fix:</strong>
<ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif

<form method="POST" action="{{ route('commissions.store') }}">
@csrf

<div class="card">
    <div class="card-title">Client &amp; Order</div>
    <div class="grid">
        <div>
            <label>Client / Institution Name *</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="e.g. Maria Santos / University of Mindanao" required>
        </div>
        <div>
            <label>Order Type</label>
            <input type="text" name="order_type" value="{{ old('order_type') }}" placeholder="e.g. 2-Tier Birthday, 200 Cupcakes">
        </div>
        <div>
            <label>Needed By (due date)</label>
            <input type="date" name="needed_by_date" value="{{ old('needed_by_date') }}">
        </div>
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="design_description" placeholder="Colors, theme, message on cake, size, custom order details...">{{ old('design_description') }}</textarea>
        </div>
        <div style="grid-column:1/-1;">
            <label>Custom Order Amount (&#8369;)</label>
            <input type="number" step="0.01" min="0" name="custom_amount" id="customAmount" value="{{ old('custom_amount', 0) }}" oninput="recalc()" placeholder="0.00">
            <div class="hint">For a custom cake with no catalog product &mdash; enter the agreed price here. Leave 0 if you only pick products below.</div>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Products</div>
        <button type="button" class="addrow" onclick="addRow()">+ Add Product</button>
    </div>
    <div class="secnote">Optional &mdash; leave empty for a fully custom order (use the Custom Order Amount above).</div>
    <table>
        <thead><tr><th style="width:42%">Product</th><th>Qty</th><th>Price (&#8369;)</th><th>Total (&#8369;)</th><th></th></tr></thead>
        <tbody id="rows"></tbody>
    </table>
</div>

<div class="card">
    <div class="card-title">Payment Summary</div>
    <div class="sumrow grand"><span class="lbl">Overall Total</span><span class="val">&#8369;<span id="grandTotal">0.00</span></span></div>
    <hr class="divline">
    <div>
        <label>Downpayment (&#8369;)</label>
        <input type="number" step="0.01" min="0" name="deposit_amount" id="deposit" value="{{ old('deposit_amount', 0) }}" oninput="recalc()">
        <div class="hint">Amount verbally agreed between the client and Trove.</div>
    </div>
    <div class="sumrow" style="margin-top:12px;"><span class="lbl">Balance</span><span class="val">&#8369;<span id="balanceDue">0.00</span></span></div>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('commissions.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Create Commission</button>
</div>
</form>

<script>
const products = {!! $productData->toJson() !!};
let i = 0;
function opts(){ return products.map(function(p){ return '<option value="'+p.id+'" data-price="'+p.price+'">'+p.name+'</option>'; }).join(''); }
function addRow(){
    const n = i++;
    const tr = document.createElement('tr');
    tr.id = 'r'+n;
    tr.innerHTML =
        '<td><select name="items['+n+'][product_id]" onchange="fillPrice(this,'+n+')"><option value="">- Select -</option>'+opts()+'</select></td>' +
        '<td><input type="number" name="items['+n+'][quantity]" min="1" value="1" oninput="recalc()"></td>' +
        '<td><input type="number" step="0.01" min="0" id="price'+n+'" name="items['+n+'][price]" oninput="recalc()"></td>' +
        '<td class="totcell" id="tot'+n+'">0.00</td>' +
        '<td><button type="button" class="rm" onclick="document.getElementById(\'r'+n+'\').remove(); recalc()">&times;</button></td>';
    document.getElementById('rows').appendChild(tr);
}
function fillPrice(sel,n){
    const p = sel.options[sel.selectedIndex].dataset.price;
    if(p) document.getElementById('price'+n).value = p;
    recalc();
}
function recalc(){
    let grand = 0;
    document.querySelectorAll('#rows tr').forEach(function(tr){
        const id = tr.id.substring(1);
        const q  = parseFloat(tr.querySelector('input[name$="[quantity]"]').value) || 0;
        const pr = parseFloat(document.getElementById('price'+id).value) || 0;
        const line = q * pr;
        document.getElementById('tot'+id).textContent = line.toFixed(2);
        grand += line;
    });
    const custom = parseFloat(document.getElementById('customAmount').value) || 0;
    grand += custom;
    document.getElementById('grandTotal').textContent = grand.toFixed(2);
    const dep = parseFloat(document.getElementById('deposit').value) || 0;
    document.getElementById('balanceDue').textContent = (grand - dep).toFixed(2);
}
addRow();
recalc();
</script>
</x-app-layout>
