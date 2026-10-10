@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Create New Product</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" class="bg-white p-8 rounded shadow">
        @csrf

        <!-- Product Details -->
        <div class="mb-6">
            <label class="block text-sm font-bold mb-2">Product Name *</label>
            <input type="text" name="product_name" required 
                class="w-full px-4 py-2 border rounded @error('product_name') border-red-500 @enderror"
                value="{{ old('product_name') }}">
            @error('product_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-bold mb-2">Description</label>
            <textarea name="description" class="w-full px-4 py-2 border rounded">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-bold mb-2">Price (₱) *</label>
                <input type="number" step="0.01" name="price" required 
                    class="w-full px-4 py-2 border rounded @error('price') border-red-500 @enderror"
                    value="{{ old('price') }}">
                @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold mb-2">Category</label>
                <input type="text" name="category" 
                    class="w-full px-4 py-2 border rounded"
                    value="{{ old('category') }}">
            </div>
        </div>

        <div class="mb-8">
            <label class="block text-sm font-bold mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2 border rounded">
                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Materials Used -->
        <h2 class="text-xl font-bold mb-4 border-t pt-4">Materials Used in This Product</h2>
        
        <div id="materials-container" class="mb-8">
            @if ($materials->count())
                @foreach ($materials as $material)
                    <div class="material-item p-4 border rounded mb-4 bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <input type="checkbox" name="material_ids[]" value="{{ $material->id }}" 
                                    class="material-checkbox mr-3">
                                <strong>{{ $material->item_name }}</strong> 
                                <span class="text-gray-600">({{ $material->unit }})</span>
                                <br>
                                <small class="text-gray-500">Available: {{ $material->quantity_on_hand }} {{ $material->unit }}</small>
                            </div>
                            
                            <input type="number" 
                                step="0.01"
                                name="materials[{{ $material->id }}]" 
                                placeholder="Qty used"
                                class="material-qty w-24 px-3 py-2 border rounded"
                                disabled>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500">No materials available. Add materials to inventory first.</p>
            @endif
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Create Product & Deduct Materials
            </button>
            <a href="{{ route('products.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
// Enable/disable quantity input based on checkbox
document.querySelectorAll('.material-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const qtyInput = this.closest('.material-item').querySelector('.material-qty');
        qtyInput.disabled = !this.checked;
        if (!this.checked) qtyInput.value = '';
    });
});
</script>
@endsection