<x-app-layout>
<x-slot name="header">New Order</x-slot>
<x-slot name="subheader">Record a walk-in, customized, or institutional order</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#0f1f3d;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;color:#1e293b;background:#f8fafc;outline:none;}
input:focus,select:focus,textarea:focus{border-color:#f0ad1f;background:#fff;box-shadow:0 0 0 3px rgba(240,173,31,.12);}
input[readonly]{background:#f1f5f9;color:#64748b;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#f0ad1f;color:#0f1f3d;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#6b7280;background:#f8fafc;border-bottom:1px solid #f1f5f9;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;}
.total-bar{display:flex;justify-content:flex-end;gap:12px;padding:14px 0;border-top:2px solid #f1f5f9;margin-top:8px;}
.total-value{font-size:20px;font-weight:900;color:#0f1f3d;}
.add-row-btn{padding:8px 14px;border:1.5px dashed #e2e8f0;border-radius:9px;background:#fafafa;color:#64748b;font-size:13px;font-weight:600;cursor:pointer;}
#customFields{display:none;}
</style>

<form method="POST" action="{{ route('orders.store') }}">
@csrf

<div class="card">
    <div class="card-title">Order Details</div>
    <div class="form-grid">
        <div>
            <label>Site <span style="color:red">*</span></label>
            <select name="site_id" required>
                <option value="">— Select site —</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Customer Name</label>
            <input type="text" name="customer_name" placeholder="Leave blank for walk-in">
        </div>
        <div>
            <label>Order Type <span style="color:red">*</span></label>
            <select name="order_type" id="orderType" required onchange="toggleCustomFields()">
                <option value="">— Select type —</option>
                <option value="Walk-in">Walk-in</option>
                <option value="Customized">Customized</option>
                <option value="Institutional">Institutional</option>
            </select>
        </div>
        <div id="depositField">
            <label>Deposit Amount (₱)</label>
            <input type="number" name="deposit_amount" step="0.01" min="0" value="0">
        </div>
    </div>

    <div id="customFields" class="form-grid" style="margin-top:16px;">
        <div style="grid-column:1/-1;">
            <label>Design Description <span style="color:red">*</span></label>
            <textarea name="design_description" rows="2" placeholder="Theme, colors, message, size, etc."></textarea>
        </div>
        <div>
            <label>Needed By Date <span style="color:red">*</span></label>
            <input type="date" name="needed_by_date">
        </div>
    </div>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Items</div>
        <button type="button" class="add-row-btn" onclick="addRow()">+ Add Item</button>
    </div>
    <table>
        <thead>
            <tr><th style="width:40%">Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
        </thead>
        <tbody id="itemsBody"></tbody>
    </table>
    <div class="total-bar">
        <span style="font-size:14px;font-weight:700;color:#64748b;">Total:</span>
        <span class="total-value">₱<span id="grandTotal">0.00</span></span>
    </div>
</div>

<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('orders.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Submit Order</button>
</div>
</form>

<script>
const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->product_name, 'price' => $p->price]));
let rowIndex = 0;

function toggleCustomFields() {
    const type = document.getElementById('orderType').value;
    document.getElementById('customFields').style.display = type === 'Customized' ? 'grid' : 'none';
}

function productOptions() {
    return products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name} — ₱${parseFloat(p.price).toFixed(2)}</option>`).join('');
}

function addRow() {
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.id = `row_${i}`;
    tr.innerHTML = `
        <td>
            <select name="items[${i}][product_id]" onchange="calcRow(${i})" required>
                <option value="">— Select product —</option>
                ${productOptions()}
            </select>
        </td>
        <td><input type="text" id="price_${i}" readonly value="0.00"></td>
        <td><input type="number" name="items[${i}][quantity]" id="qty_${i}" value="1" min="1" oninput="calcRow(${i})" required style="width:80px"></td>
        <td id="sub_${i}" style="font-weight:700;">₱0.00</td>
        <td><button type="button" onclick="removeRow(${i})" style="background:none;border:none;color:#ef4444;cursor:pointer;">✕</button></td>
    `;
    document.getElementById('itemsBody').appendChild(tr);
}

function calcRow(i) {
    const select = document.querySelector(`#row_${i} select`);
    const price = parseFloat(select.options[select.selectedIndex]?.dataset.price) || 0;
    const qty = parseFloat(document.getElementById(`qty_${i}`).value) || 0;
    document.getElementById(`price_${i}`).value = price.toFixed(2);
    document.getElementById(`sub_${i}`).textContent = '₱' + (price * qty).toFixed(2);
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('[id^="sub_"]').forEach(el => total += parseFloat(el.textContent.replace('₱','')) || 0);
    document.getElementById('grandTotal').textContent = total.toFixed(2);
}

function removeRow(i) {
    document.getElementById(`row_${i}`)?.remove();
    calcTotal();
    if (document.getElementById('itemsBody').children.length === 0) addRow();
}

addRow();
</script>
</x-app-layout>