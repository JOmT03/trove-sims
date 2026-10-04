<x-app-layout>
<x-slot name="header">Edit Product</x-slot>
<x-slot name="subheader">Update product details and materials</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#2E1C10;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #EDE0D0;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
</style>

<div style="max-width: 900px; margin: 0 auto;">
@if ($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#7f1d1d;padding:15px;border-radius:8px;margin-bottom:20px;">
        <strong>Errors:</strong>
        <ul style="margin:8px 0 0;padding-left:20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('products.update', $product) }}">
@csrf
@method('PUT')

<div class="card">
    <div class="card-title">Product Details</div>
    <div class="form-grid">
        <div style="grid-column:1/-1;">
            <label>Product Name <span style="color:red">*</span></label>
            <input type="text" name="product_name" value="{{ old('product_name', $product->product_name) }}" required>
        </div>
        <div>
            <label>Category</label>
            <select name="category">
                <option value="">&mdash; Select &mdash;</option>
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
        <div>
            <label>Site</label>
            <select name="site_id">
                <option value="">&mdash; Unassigned &mdash;</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ old('site_id', $product->site_id) == $site->id ? 'selected' : '' }}>
                        {{ $site->site_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">Materials Used in This Product</div>
    @if ($product->materials && $product->materials->count())
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Quantity Used (per unit)</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->materials as $material)
                    <tr>
                        <td>{{ $material->item_name }}</td>
                        <td>{{ number_format($material->pivot->quantity_used, 2) }}</td>
                        <td>{{ $material->unit }}</td>
                    </tr>
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

</x-app-layout>