@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Products</h1>
        @can('admin')
            <a href="{{ route('products.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                + New Product
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if ($products->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <div class="bg-white p-6 rounded shadow-md hover:shadow-lg transition">
                    <h2 class="text-xl font-bold mb-2">{{ $product->product_name }}</h2>
                    
                    <div class="mb-4">
                        <p class="text-gray-600 text-sm">{{ $product->description ?? 'No description' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 border-t pt-4">
                        <div>
                            <p class="text-gray-600 text-sm">Price</p>
                            <p class="text-lg font-bold">₱{{ $product->price }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Category</p>
                            <p class="text-lg">{{ $product->category ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="px-3 py-1 rounded text-sm {{ $product->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('products.show', $product) }}" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded text-center hover:bg-gray-700 text-sm">
                            View
                        </a>
                        @can('admin')
                            <a href="{{ route('products.edit', $product) }}" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700 text-sm">
                                Edit
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" style="flex: 1;">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm"
                                    onclick="return confirm('Delete this product?')">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-100 p-8 rounded text-center">
            <p class="text-gray-600 mb-4">No products yet.</p>
            @can('admin')
                <a href="{{ route('products.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Create First Product
                </a>
            @endcan
        </div>
    @endif
</div>
@endsection