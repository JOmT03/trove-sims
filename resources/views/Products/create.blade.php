<x-app-layout>
<x-slot name="header">Add Product</x-slot>
<x-slot name="subheader">Create a product and define what it's made from</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
input[type=file]{padding:8px;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.add-row-btn{padding:8px 14px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:13px;font-weight:600;cursor:pointer;}
.preview{margin-top:10px;width:110px;height:110px;border-radius:10px;object-fit:cover;border:1px solid #EDE0D0;display:none;}
</style>

@php
    $invData = $inventoryItems->map(function ($i) {
        return ['id' => $i->id, 'name' => $i->item_name, 'unit' => $i->unit];
    });
@endphp

@if($errors->any())
<div style="background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;">
<strong>Please fix:</strong><ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
@csrf

<div class="card">
    <div class="card-title">Product Details</div>
    <div class="form-grid">
        <div style="grid-column:1/-1;">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" value="{{ old('product_name') }}" placeholder="e.g. Banana Cake" required>
        </div>
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" rows="2" style="width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;resize:vertical;" placeholder="Short description shown on the product card...">{{ old('description') }}</textarea>
        </div>
        <div>
            <label>Category <span style="color:red">*</span></label>
            <select name="category" required>
                <option value="">- Select -</option>
                <option value="Cake">Cake</option>
                <option value="Pastry">Pastry</option>
                <option value="Coffee">Coffee</option>
            </select>
        </div>
        <div>
            <label>Price (&#8369;) <span style="color:red">*</span></label>
            <input type="number" name="price" step="0.01" min="0" value="{{ old('price') }}" required>
        </div>
        <div>
            <label>Starting Finished Stock</label>
            <input type="number" name="stock_quantity" min="0" value="{{ old('stock_quantity', 0) }}">
        </div>
        <div>
            <label>Site</label>
            <select name="site_id">
                <option value="">- Unassigned -</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                @endforeach
            </select>
        </div>
        <div style="grid-column:1/-1;">
            <label>Product Photo</label>
            <input type="file" name="image" accept="image/*" onchange="previewImg(this)">
            <img id="preview" class="preview" alt="preview">
            <p style="font-size:11px;color:#8A7460;margin-top:6px;">Optional. JPG or PNG, up to 2MB.</p>
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Recipe (Raw Materials Needed)</div>
        <button type="button" class="add-row-btn" onclick="addRow()">+ Add Ingredient</button>
    </div>
    <p style="font-size:12px;color:#8A7460;margin-bottom:12px;">Optional - define how much of each inventory item is needed to make <strong>one unit</strong> of this product. This enables automatic stock deduction.</p>
    <table>
        <thead><tr><th style="width:50%">Inventory Item</th><th>Qty Needed (per unit)</th><th></th></tr></thead>
        <tbody id="recipeBody"></tbody>
    </table>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Save Product</button>
</div>
</form>

<script>
const inventoryItems = {!! $invData->toJson() !!};
let rowIndex = 0;

function previewImg(input){
    const img = document.getElementById('preview');
    if(input.files && input.files[0]){
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    } else { img.style.display='none'; }
}
function itemOptions(){
    return inventoryItems.map(function(i){ return '<option value="'+i.id+'">'+i.name+' ('+i.unit+')</option>'; }).join('');
}
function addRow(){
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.id = 'recipe_row_'+i;
    tr.innerHTML =
        '<td><select name="recipe['+i+'][inventory_id]" required><option value="">- Select item -</option>'+itemOptions()+'</select></td>' +
        '<td><input type="number" name="recipe['+i+'][quantity_needed]" step="0.01" min="0.01" required></td>' +
        '<td><button type="button" onclick="document.getElementById(\'recipe_row_'+i+'\').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;">&times;</button></td>';
    document.getElementById('recipeBody').appendChild(tr);
}
</script>
</x-app-layout>