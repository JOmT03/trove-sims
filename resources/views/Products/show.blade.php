@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded shadow">
        <h1 class="text-3xl font-bold mb-4">{{ $product->product_name }}</h1>

        <div class="grid grid-cols-2 gap-4 mb-8">
            <div>
                <p class="text-gray-600">Price</p>
                <p class="text-2xl font-bold">₱{{ $product->price }}</p>
            </div>
            <div>
                <p class="text-gray-600">Category</p>
                <p class="text-lg">{{ $product->category ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600">Status</p>
                <span class="px-3 py-1 rounded {{ $product->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100' }}">
                    {{ ucfirst($product->status) }}
                </span>
            </div>
        </div>

        @if ($product->description)
            <div class="mb-8">
                <p class="text-gray-600">Description</p>
                <p>{{ $product->description }}</p>
            </div>
        @endif

        <!-- Materials Used -->
        @if ($product->materials->count())
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4">Materials Used</h2>
                <table class="w-full border-collapse border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2 text-left">Material</th>
                            <th class="border p-2 text-right">Quantity Used</th>
                            <th class="border p-2 text-center">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product->materials as $material)
                            <tr>
                                <td class="border p-2">{{ $material->item_name }}</td>
                                <td class="border p-2 text-right">{{ $material->pivot->quantity_used }}</td>
                                <td class="border p-2 text-center">{{ $material->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex gap-4">
            <a href="{{ route('products.edit', $product) }}" class="bg-blue-600 text-white px-6 py-2 rounded">
                Edit
            </a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded" 
                    onclick="return confirm('Delete this product?')">
                    Delete
                </button>
            </form>
            <a href="{{ route('products.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded">
                Back
            </a>
        </div>
    </div>
</div>
@endsection