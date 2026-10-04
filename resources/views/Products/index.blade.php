<x-app-layout>
<div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="font-size: 28px; font-weight: 800;">Products</h1>
        @can('admin')
            <a href="{{ route('products.create') }}" style="background-color: var(--gold); color: var(--white); padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">+ New Product</a>
        @endcan
    </div>

    @if (session('success'))
        <div style="background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 15px; border-radius: 6px; margin-bottom: 20px;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #7f1d1d; padding: 15px; border-radius: 6px; margin-bottom: 20px;">{{ session('error') }}</div>
    @endif

    @if ($products && $products->count())
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            @foreach ($products as $product)
                <div style="background: var(--white); border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    @if ($product->image_path)
                        <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->product_name }}" style="width:100%;height:170px;object-fit:cover;display:block;">
                    @else
                        <div style="width:100%;height:170px;background:#FDF6EC;display:flex;align-items:center;justify-content:center;color:#C9B9A6;font-size:13px;">No photo</div>
                    @endif
                    <div style="padding: 20px;">
                        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">{{ $product->product_name }}</h2>
                        <p style="color: var(--muted); font-size: 13px; margin-bottom: 14px;">{{ $product->category ?? 'N/A' }}</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; border-top: 1px solid var(--border); padding-top: 14px;">
                            <div>
                                <p style="color: var(--muted); font-size: 12px;">Price</p>
                                <p style="font-size: 18px; font-weight: 800;">&#8369;{{ number_format($product->price, 2) }}</p>
                            </div>
                            <div>
                                <p style="color: var(--muted); font-size: 12px;">Status</p>
                                <span style="display: inline-block; padding: 5px 10px; border-radius: 4px; font-size: 12px; background-color: {{ $product->status == 'active' ? 'var(--green)' : '#e5e7eb' }}; color: {{ $product->status == 'active' ? 'var(--white)' : 'var(--text)' }};">{{ ucfirst($product->status) }}</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('products.show', $product) }}" style="flex: 1; background-color: var(--navy); color: var(--white); padding: 10px; border-radius: 4px; text-align: center; text-decoration: none; font-size: 14px;">View</a>
                            @can('admin')
                                <a href="{{ route('products.edit', $product) }}" style="flex: 1; background-color: var(--gold); color: var(--white); padding: 10px; border-radius: 4px; text-align: center; text-decoration: none; font-size: 14px;">Edit</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" style="flex: 1;">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width: 100%; background-color: var(--red); color: var(--white); padding: 10px; border-radius: 4px; border: none; font-size: 14px; cursor: pointer;" onclick="return confirm('Delete this product?')">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background-color: #f3f4f6; padding: 40px; border-radius: 8px; text-align: center;">
            <p style="color: var(--muted); margin-bottom: 20px;">No products yet. Create one to get started!</p>
            @can('admin')<a href="{{ route('products.create') }}" style="display: inline-block; background-color: var(--gold); color: var(--white); padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600;">Create First Product</a>@endcan
        </div>
    @endif
</div>
</x-app-layout>