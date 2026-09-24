<x-app-layout>
<x-slot name="header">Products</x-slot>
<x-slot name="subheader">Cakes, pastries, and coffee items</x-slot>

<style>
.table-card{background:#fff;border-radius:14px;border:1px solid #EDE0D0;overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:11px;font-weight:600;color:#8A7460;text-transform:uppercase;padding:14px 20px;background:#FDF6EC;border-bottom:1px solid #EDE0D0;}
td{padding:15px 20px;font-size:14px;border-bottom:1px solid #EDE0D0;}
.btn-gold{background:#D9782C;color:#fff;font-weight:700;padding:9px 18px;border-radius:10px;text-decoration:none;}
</style>

@if(session('success'))<div style="padding:12px 20px;border-radius:10px;margin-bottom:16px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d;">✓ {{ session('success') }}</div>@endif

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <a href="{{ route('products.create') }}" class="btn-gold">+ Add Product</a>
</div>

<div class="table-card">
    <table>
        <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Finished Stock</th><th>Recipe Items</th><th>Site</th></tr></thead>
        <tbody>
            @forelse($products as $p)
            <tr>
                <td>{{ $p->product_name }}</td>
                <td>{{ $p->category }}</td>
                <td>₱{{ number_format($p->price, 2) }}</td>
                <td>{{ $p->stock_quantity }}</td>
                <td>{{ $p->recipe->count() }} ingredient(s)</td>
                <td>{{ $p->site->site_name ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#8A7460;padding:40px;">No products yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>