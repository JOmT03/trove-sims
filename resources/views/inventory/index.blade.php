<x-app-layout>
<x-slot name="header">Inventory</x-slot>
<x-slot name="subheader">Raw materials and ingredients across all sites</x-slot>

<style>
.summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;}
.summary-card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:20px;border-left:4px solid #D9782C;}
.summary-card.green{border-left-color:#22c55e;}
.summary-card.red{border-left-color:#ef4444;}
.summary-card .label{font-size:11px;color:#8A7460;text-transform:uppercase;font-weight:700;margin-bottom:4px;}
.summary-card .value{font-size:28px;font-weight:800;color:#4A2C17;}
.summary-card.green .value{color:#15803d;}
.summary-card.red .value{color:#dc2626;}
.table-card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th{padding:12px 20px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
td{padding:12px 20px;border-top:1px solid #EDE0D0;}
.low-stock{color:#dc2626;font-weight:700;}
.btn-gold{background:#D9782C;color:#fff;font-weight:700;padding:9px 18px;border-radius:10px;text-decoration:none;display:inline-flex;}
</style>

@if(session('success'))<div style="padding:12px 20px;border-radius:10px;margin-bottom:16px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d;">✓ {{ session('success') }}</div>@endif

<div class="summary-grid">
    <div class="summary-card">
        <div class="label">Total Items</div>
        <div class="value">{{ $inventories->count() }}</div>
    </div>
    <div class="summary-card green">
        <div class="label">Low Stock Items</div>
        <div class="value">{{ $lowStock }}</div>
    </div>
    <div class="summary-card red">
        <div class="label">Total Damaged</div>
        <div class="value">{{ number_format($damaged, 0) }}</div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <a href="{{ route('inventory.create') }}" class="btn-gold">+ Add Item</a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr><th>Item</th><th>Category</th><th>Stock</th><th>Min. Stock</th><th>Site</th></tr>
        </thead>
        <tbody>
            @forelse($inventories as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->category }}</td>
                    <td class="{{ $item->isLowStock() ? 'low-stock' : '' }}">
                        {{ $item->quantity_on_hand }} {{ $item->unit }}
                        @if($item->isLowStock()) ⚠️ @endif
                    </td>
                    <td>{{ $item->minimum_stock }} {{ $item->unit }}</td>
                    <td>{{ $item->site->site_name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:#8A7460;">No inventory found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</x-app-layout>