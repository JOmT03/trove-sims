<x-app-layout>
<x-slot name="header">Add Product</x-slot>
<x-slot name="subheader">List a new product for buyers to order</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:28px;max-width:700px;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.full{grid-column:1/-1;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
label .req{color:#ef4444;}
input,select,textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;color:#1e293b;background:#f8fafc;outline:none;transition:border .2s;}
input:focus,select:focus,textarea:focus{border-color:#f0ad1f;background:#fff;box-shadow:0 0 0 3px rgba(240,173,31,.12);}
.field-error{font-size:11px;color:#dc2626;margin-top:3px;}
.img-upload{border:2px dashed #e2e8f0;border-radius:12px;padding:24px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa;}
.img-upload:hover{border-color:#f0ad1f;background:#fffbeb;}
.img-preview{width:100%;max-height:200px;object-fit:contain;border-radius:10px;margin-top:12px;display:none;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:opacity .2s;}
.btn:hover{opacity:.85;}
.btn-gold{background:#f0ad1f;color:#0f1f3d;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.toggle-wrap{display:flex;align-items:center;gap:10px;padding:12px 0;}
.toggle{width:44px;height:24px;background:#e2e8f0;border-radius:20px;position:relative;cursor:pointer;transition:background .2s;}
.toggle.on{background:#10b981;}
.toggle-dot{width:18px;height:18px;background:#fff;border-radius:50%;position:absolute;top:3px;left:3px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.toggle.on .toggle-dot{left:23px;}
</style>

<div class="card">
    <form method="POST" action="{{ route('supplier.products.store') }}" enctype="multipart/form-data">
        @csrf

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
                <strong>Please fix:</strong>
                <ul style="margin-left:16px;margin-top:4px;">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="form-grid">

            {{-- Product Name --}}
            <div class="full">
                <label>Product Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="e.g. Waterproof Portland Cement">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Category --}}
            <div>
                <label>Category <span class="req">*</span></label>
                <select name="category" required>
                    <option value="">— Select category —</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Unit --}}
            <div>
                <label>Unit <span class="req">*</span></label>
                <select name="unit" required>
                    <option value="">— Select unit —</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" {{ old('unit') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
                @error('unit')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Price --}}
            <div>
                <label>Price per Unit (₱) <span class="req">*</span></label>
                <input type="number" name="price" value="{{ old('price') }}" required
                       min="0" step="0.01" placeholder="0.00">
                @error('price')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Available toggle --}}
            <div style="display:flex;align-items:center;gap:12px;padding-top:24px;">
                <input type="hidden" name="is_available" value="0">
                <input type="checkbox" name="is_available" id="availToggle" value="1"
                       {{ old('is_available', true) ? 'checked' : '' }}
                       style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;">
                <label for="availToggle" style="margin:0;cursor:pointer;font-weight:600;color:#374151;">
                    Available for ordering
                </label>
            </div>

            {{-- Description --}}
            <div class="full">
                <label>Description</label>
                <textarea name="description" rows="3"
                          placeholder="Describe your product — specs, brand, quality grade...">{{ old('description') }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            {{-- Image --}}
            <div class="full">
                <label>Product Image</label>
                <div class="img-upload" onclick="document.getElementById('imgInput').click()">
                    <svg style="width:32px;height:32px;stroke:#94a3b8;margin:0 auto 8px;display:block;" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p style="font-size:13px;color:#64748b;">Click to upload image</p>
                    <p style="font-size:11px;color:#94a3b8;margin-top:4px;">JPEG, PNG, WebP — max 2MB</p>
                    <img id="imgPreview" class="img-preview" src="" alt="Preview">
                </div>
                <input type="file" name="image" id="imgInput" accept="image/*"
                       style="display:none;" onchange="previewImg(this)">
                @error('image')<p class="field-error">{{ $message }}</p>@enderror
            </div>

        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('supplier.products.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-gold">
                <svg style="width:15px;height:15px;stroke:#0f1f3d" fill="none" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Add Product
            </button>
        </div>
    </form>
</div>

<script>
function previewImg(input) {
    const preview = document.getElementById('imgPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</x-app-layout>