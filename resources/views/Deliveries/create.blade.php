<x-app-layout>
<x-slot name="header">Record Delivery</x-slot>
<x-slot name="subheader">Record items received from a supplier</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#0f1f3d;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 14px;border:1.5px solid #d1d5db;border-radius:9px;font-size:14px;color:#111827;background:#f9fafb;outline:none;transition:border .2s;}
input:focus,select:focus,textarea:focus{border-color:#f0ad1f;background:#fff;box-shadow:0 0 0 3px rgba(240,173,31,.12);}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:opacity .2s;}
.btn:hover{opacity:.85;}
.btn-gold{background:#f0ad1f;color:#0f1f3d;}
.btn-navy{background:#0f1f3d;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:700;background:#f8fafc;border-bottom:1px solid #f3f4f6;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;vertical-align:top;}
td input,td select{padding:8px 10px;font-size:13px;}
.condition-good{color:#166534;font-weight:700;}
.condition-partial{color:#9a3412;font-weight:700;}
.condition-damaged{color:#991b1b;font-weight:700;}
#orderPlaceholder{text-align:center;padding:24px;color:#9ca3af;font-size:13px;background:#fafafa;border-radius:10px;border:2px dashed #e5e7eb;}
</style>

<form method="POST" action="{{ route('deliveries.store') }}" id="deliveryForm">
@csrf

{{-- ORDER SELECTION --}}
<div class="card">
    <div class="card-title">Select Order</div>
    <div class="form-grid">
        <div>
            <label for="order_id">Order <span style="color:red">*</span></label>
            <select name="order_id" id="order_id" required onchange="loadOrderItems(this.value)">
                <option value="">— Select an approved/shipped order —</option>
                @foreach($orders as $o)
                    <option value="{{ $o->id }}" {{ (isset($selectedOrder) && $selectedOrder->id==$o->id) ? 'selected' : '' }}>
                        {{ $o->order_number }} — {{ $o->supplier->name }}
                        ({{ \App\Models\Order::STATUSES[$o->status]['label'] }})
                    </option>
                @endforeach
            </select>
            @if($orders->isEmpty())
                <p style="color:#d97706;font-size:12px;margin-top:6px;">
                    ⚠ No approved/shipped orders found. Please approve an order first before recording a delivery.
                </p>
            @endif
        </div>
        <div>
            <label for="delivered_at">Date Received <span style="color:red">*</span></label>
            <input type="date" name="delivered_at" id="delivered_at"
                   value="{{ date('Y-m-d') }}" required>
        </div>
        <div>
            <label for="received_by">Received By <span style="color:red">*</span></label>
            <input type="text" name="received_by" id="received_by"
                   placeholder="Name of person who received the delivery"
                   value="{{ auth()->user()->name }}" required>
        </div>
        <div>
            <label for="notes">Notes</label>
            <input type="text" name="notes" id="notes" placeholder="Optional notes about this delivery">
        </div>
    </div>
</div>

{{-- DELIVERY ITEMS — auto-populated when order is selected --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Delivery Items</div>
        <span id="supplierBadge" style="font-size:12px;color:#6b7280;display:none;">
            Supplier: <strong id="supplierName"></strong>
        </span>
    </div>

    <div id="orderPlaceholder">
        Select an order above to load its items automatically.
    </div>

    <div id="itemsTable" style="display:none;">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Ordered</th>
                    <th>Qty Delivered</th>
                    <th>Qty Damaged</th>
                    <th>Condition</th>
                    <th>Damage Notes</th>
                </tr>
            </thead>
            <tbody id="itemsBody"></tbody>
        </table>
    </div>
</div>

{{-- SUBMIT --}}
<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('deliveries.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold" id="submitBtn" disabled>
        <svg style="width:15px;height:15px;stroke:#0f1f3d" fill="none" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Save Delivery
    </button>
</div>
</form>

<script>
const conditions = @json(\App\Models\DeliveryItem::CONDITIONS);
const categories = @json(\App\Models\Supplier::CATEGORIES);

async function loadOrderItems(orderId) {
    const placeholder = document.getElementById('orderPlaceholder');
    const table       = document.getElementById('itemsTable');
    const body        = document.getElementById('itemsBody');
    const submitBtn   = document.getElementById('submitBtn');
    const badge       = document.getElementById('supplierBadge');

    if (!orderId) {
        placeholder.style.display = 'block';
        table.style.display = 'none';
        submitBtn.disabled = true;
        badge.style.display = 'none';
        return;
    }

    placeholder.innerHTML = '<span style="color:#f0ad1f">⏳ Loading items...</span>';

    try {
        const res  = await fetch(`/api/orders/${orderId}/items`);
        const data = await res.json();

        document.getElementById('supplierName').textContent = data.supplier;
        badge.style.display = 'inline';

        body.innerHTML = '';
        data.items.forEach((item, i) => {
            const catLabel = categories[item.category] || item.category;
            const condOptions = Object.entries(conditions).map(([k,v]) =>
                `<option value="${k}">${v.label}</option>`
            ).join('');

            body.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>
                        <input type="hidden" name="items[${i}][order_item_id]" value="${item.id}">
                        <input type="hidden" name="items[${i}][item_name]"     value="${item.name}">
                        <input type="hidden" name="items[${i}][category]"      value="${item.category}">
                        <input type="hidden" name="items[${i}][unit]"          value="${item.unit}">
                        <input type="hidden" name="items[${i}][quantity_ordered]" value="${item.quantity}">
                        <strong style="color:#0f1f3d">${item.name}</strong>
                    </td>
                    <td><span style="padding:2px 8px;background:#fef3c7;color:#92400e;border-radius:10px;font-size:11px;font-weight:600;">${catLabel}</span></td>
                    <td style="font-weight:700">${item.quantity} ${item.unit}</td>
                    <td>
                        <input type="number" name="items[${i}][quantity_delivered]"
                               value="${item.quantity}" min="0" max="${item.quantity}"
                               step="0.01" style="width:90px" required>
                    </td>
                    <td>
                        <input type="number" name="items[${i}][quantity_damaged]"
                               value="0" min="0" max="${item.quantity}"
                               step="0.01" style="width:90px"
                               oninput="updateCondition(this, ${i})">
                    </td>
                    <td>
                        <select name="items[${i}][condition]" id="cond_${i}" style="width:140px">
                            ${condOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="items[${i}][damage_notes]"
                               placeholder="Optional" style="width:160px">
                    </td>
                </tr>
            `);
        });

        placeholder.style.display = 'none';
        table.style.display = 'block';
        submitBtn.disabled = false;
    } catch(e) {
        placeholder.innerHTML = '<span style="color:red">Failed to load items. Please try again.</span>';
    }
}

function updateCondition(input, index) {
    const damaged = parseFloat(input.value) || 0;
    const sel = document.getElementById(`cond_${index}`);
    if (!sel) return;
    if (damaged === 0)       sel.value = 'good';
    else if (damaged > 0)    sel.value = 'partial_damage';
}

// Auto-load if order pre-selected
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('order_id');
    if (sel.value) loadOrderItems(sel.value);
});
</script>
</x-app-layout>