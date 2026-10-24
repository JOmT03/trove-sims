<x-app-layout>
<x-slot name="header">Edit Product</x-slot>
<x-slot name="subheader">Update product details, photo &amp; materials</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
input[type=file]{padding:8px;background:#fff;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.photo-row{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;}
.photo-col{flex:0 0 auto;}
.photo-cap{font-size:11px;color:#8A7460;margin-bottom:6px;font-weight:600;}
.thumb{width:120px;height:120px;object-fit:cover;border-radius:12px;border:1px solid #EDE0D0;display:block;}
.thumb.ph{background:#FDF6EC;display:flex;align-items:center;justify-content:center;color:#C9B9A6;font-size:12px;}
.thumb.new{border:2px solid #D9782C;}
.photo-arrow{align-self:center;font-size:22px;color:#D9782C;margin-top:18px;display:none;}
.photo-pick{flex:1;min-width:220px;}
.photo-hint{font-size:11.5px;color:#8A7460;margin-top:7px;}
.photo-err{font-size:12px;color:#C2410C;font-weight:700;margin-top:7px;display:none;}
</style>

<div style="max-width: 900px; margin: 0 auto;">
@if ($errors->any())
    <div class="alert-err"><strong>Errors:</strong><ul style="margin:8px 0 0;padding-left:20px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="card">
    <div class="card-title">Product Details</div>
    <div class="form-grid">
        <div style="grid-column:1/-1;">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" value="{{ old('product_name', $product->product_name) }}" required>
        </div>
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" rows="2" style="width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;resize:vertical;" placeholder="Short description shown on the product card...">{{ old('description', $product->description) }}</textarea>
        </div>
        <div>
            <label>Category</label>
            <select name="category">
                <option value="">- Select -</option>
                <option value="Cake" {{ old('category', $product->category) === 'Cake' ? 'selected' : '' }}>Cake</option>
                <option value="Pastry" {{ old('category', $product->category) === 'Pastry' ? 'selected' : '' }}>Pastry</option>
                <option value="Coffee" {{ old('category', $product->category) === 'Coffee' ? 'selected' : '' }}>Coffee</option>
            </select>
        </div>
        <div>
            <label>Price (&#8369;) <span style="color:red">*</span></label>
            <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="grid-column:1/-1;">
            <label>Product Photo</label>
            <div class="photo-row">
                <div class="photo-col">
                    <div class="photo-cap">Current</div>
                    @if($product->image_path)
                        <img src="{{ asset('storage/'.$product->image_path) }}" class="thumb" alt="current">
                    @else
                        <div class="thumb ph">None</div>
                    @endif
                </div>

                <div class="photo-arrow" id="photoArrow">&rarr;</div>

                <div class="photo-col" id="newCol" style="display:none;">
                    <div class="photo-cap">New (not saved yet)</div>
                    <img id="preview" class="thumb new" alt="preview">
                </div>

                <div class="photo-pick">
                    <div class="photo-cap">Upload new (replaces current)</div>
                    <input type="file" id="imgInput" name="image" accept="image/png,image/jpeg,image/webp" onchange="previewImg(this)">
                    <div class="photo-hint">JPG, PNG or WEBP, up to 2MB. Leave empty to keep current. Click Save Changes to apply.</div>
                    <div class="photo-err" id="imgErr"></div>
                    @error('image')<div class="photo-err" style="display:block;">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Materials Used in This Product</div>
    @if ($product->materials && $product->materials->count())
        <table>
            <thead><tr><th>Material</th><th>Quantity Used (per unit)</th><th>Unit</th></tr></thead>
            <tbody>
                @foreach ($product->materials as $material)
                    <tr><td>{{ $material->item_name }}</td><td>{{ number_format($material->pivot->quantity_used, 2) }}</td><td>{{ $material->unit }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:#8A7460;margin:0;">No materials defined for this product.</p>
    @endif
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Save Changes</button>
</div>
</form>
</div>

<script>
function previewImg(input){
    var img = document.getElementById('preview');
    var col = document.getElementById('newCol');
    var arrow = document.getElementById('photoArrow');
    var err = document.getElementById('imgErr');
    err.style.display = 'none';
    col.style.display = 'none'; arrow.style.display = 'none';

    if(!(input.files && input.files[0])){ return; }
    var f = input.files[0];
    if(f.size > 2 * 1024 * 1024){
        err.textContent = 'This file is ' + (f.size / 1048576).toFixed(1) + 'MB. Maximum is 2MB. Please choose a smaller photo.';
        err.style.display = 'block';
        input.value = '';
        return;
    }
    img.src = URL.createObjectURL(f);
    col.style.display = 'block'; arrow.style.display = 'block';
}
</script>
</x-app-layout>
