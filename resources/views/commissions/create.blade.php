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
.radio-row{display:flex;gap:10px;}
.radio-opt{flex:1;border:1.5px solid #e2e8f0;border-radius:9px;padding:12px;text-align:center;cursor:pointer;font-size:13px;font-weight:700;color:#8A7460;}
.radio-opt input{display:none;}
.radio-opt.sel{border-color:#4A2C17;background:#4A2C17;color:#fff;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;padding:9px 12px;}
td{padding:8px 12px;border-bottom:1px solid #f3f4f6;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.addrow{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:600;cursor:pointer;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.rm{background:none;border:none;color:#ef4444;cursor:pointer;font-size:15px;}
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

<form method="POST" action="{{ route('commissions.store') }}">
@csrf

<div class="card">
    <div class="card-title">Client &amp; Order</div>
    <div class="grid">
        <div style="grid-column:1/-1;">
            <label>Client Type *</label>
            <div class="radio-row">
                <label class="radio-opt sel" id="opt-ind"><input type="radio" name="client_type" value="individual" checked onchange="pickType('ind')"> Individual (walk-in / custom)</label>
                <label class="radio-opt" id="opt-inst"><input type="radio" name="client_type" value="institutional" onchange="pickType('inst')"> Institutional (UM, Stella Maris)</label>
            </div>
        </div>
        <div>
            <label>Client / Institution Name *</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="e.g. Maria Santos / University of Mindanao" required>
        </div>
        <div>
            <label>Cake / Order Type</label>
            <input type="text" name="order_type" value="{{ old('order_type') }}" placeholder="e.g. 2-Tier Birthday, 200 Cupcakes">
        </div>
        <div>
            <label>Needed By (due date)</label>
            <input type="date" name="needed_by_date" value="{{ old('needed_by_date') }}">
        </div>
        <div>
            <label>Downpayment (&#8369;)</label>
            <input type="number" step="0.01" min="0" name="deposit_amount" value="{{ old('deposit_amount', 0) }}">
        </div>
        <div style="grid-column:1/-1;">
            <label>Design Description</label>
            <textarea name="design_description" placeholder="Colors, theme, message on cake, size...">{{ old('design_description') }}</textarea>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Products</div>
        <button type="button" class="addrow" onclick="addRow()">+ Add Product</button>
    </div>
    <table>
        <thead><tr><th style="width:45%">Product</th><th>Qty</th><th>Price (&#8369;)</th><th></th></tr></thead>
        <tbody id="rows"></tbody>
    </table>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('commissions.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Create Commission</button>
</div>
</form>

<script>
const products = {!! $productData->toJson() !!};
let i = 0;
function pickType(which){
    document.getElementById('opt-ind').classList.toggle('sel', which==='ind');
    document.getElementById('opt-inst').classList.toggle('sel', which==='inst');
}
function opts(){ return products.map(function(p){ return '<option value="'+p.id+'" data-price="'+p.price+'">'+p.name+'</option>'; }).join(''); }
function addRow(){
    const n = i++;
    const tr = document.createElement('tr');
    tr.id = 'r'+n;
    tr.innerHTML =
        '<td><select name="items['+n+'][product_id]" onchange="fillPrice(this,'+n+')" required><option value="">- Select -</option>'+opts()+'</select></td>' +
        '<td><input type="number" name="items['+n+'][quantity]" min="1" value="1" required></td>' +
        '<td><input type="number" step="0.01" min="0" id="price'+n+'" name="items['+n+'][price]" required></td>' +
        '<td><button type="button" class="rm" onclick="document.getElementById(\'r'+n+'\').remove()">&times;</button></td>';
    document.getElementById('rows').appendChild(tr);
}
function fillPrice(sel,n){
    const p = sel.options[sel.selectedIndex].dataset.price;
    if(p) document.getElementById('price'+n).value = p;
}
addRow();
</script>
</x-app-layout>