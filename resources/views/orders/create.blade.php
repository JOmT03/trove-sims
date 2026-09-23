<x-app-layout>
<x-slot name="header">Create Purchase Order</x-slot>
<x-slot name="subheader">Place a new order from a supplier</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:800;color:#0f1f3d;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;color:#1e293b;background:#f8fafc;outline:none;transition:border .2s;}
input:focus,select:focus,textarea:focus{border-color:#f0ad1f;background:#fff;box-shadow:0 0 0 3px rgba(240,173,31,.12);}
input[readonly]{background:#f1f5f9;color:#64748b;cursor:not-allowed;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:opacity .2s;}
.btn:hover{opacity:.85;}
.btn-gold{background:#f0ad1f;color:#0f1f3d;}
.btn-navy{background:#0f1f3d;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.btn-red{background:#fee2e2;color:#dc2626;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:9px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#6b7280;font-weight:700;background:#f8fafc;border-bottom:1px solid #f1f5f9;}
td{padding:10px 12px;border-bottom:1px solid #f9fafb;vertical-align:middle;}
td input,td select{padding:8px 10px;font-size:13px;}
.total-bar{display:flex;align-items:center;justify-content:flex-end;gap:12px;padding:14px 0;border-top:2px solid #f1f5f9;margin-top:8px;}
.total-label{font-size:14px;font-weight:700;color:#64748b;}
.total-value{font-size:20px;font-weight:900;color:#0f1f3d;}
.cod-badge{background:#dcfce7;color:#166534;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;}
.add-row-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px dashed #e2e8f0;border-radius:9px;background:#fafafa;color:#64748b;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;}
.add-row-btn:hover{border-color:#f0ad1f;color:#0f1f3d;background:#fffbeb;}
#noProductsMsg{display:none;color:#d97706;font-size:12px;margin-top:6px;padding:8px 12px;background:#fffbeb;border-radius:8px;border:1px solid #fcd34d;}
</style>

<form method="POST" action="{{ route('orders.store') }}" id="orderForm">
@csrf

{{-- ── ORDER INFO ── --}}
<div class="card">
    <div class="card-title">Order Details</div>
    <div class="form-grid">
        <div>
            <label for="supplier_id">Supplier <span style="color:red">*</span></label>
            <select name="supplier_id" id="supplier_id" required onchange="loadProducts(this.value)">
                <option value="">— Select a supplier —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" data-user="{{ $s->user_id ?? '' }}"
                        {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                        @if($s->category) ({{ \App\Models\Supplier::CATEGORIES[$s->category] ?? $s->category }}) @endif
                    </option>
                @endforeach
            </select>
            <div id="noProductsMsg">⚠ This supplier has no products listed yet.</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;padding-top:24px;">
            <span class="cod-badge">💵 Cash on Delivery</span>
            <span style="font-size:12px;color:#64748b;">Payment method</span>
        </div>
        <div style="grid-column:1/-1;">
            <label for="notes">Notes / Special Instructions</label>
            <textarea name="notes" id="notes" rows="2"
                      placeholder="Any special instructions for this order...">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

{{-- ── ORDER ITEMS ── --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div class="card-title" style="margin:0;padding:0;border:none;">Items to Order</div>
        <button type="button" class="add-row-btn" id="addRowBtn" onclick="addRow()" style="display:none;">
            + Add Another Item
        </button>
    </div>

    <div id="noSupplierMsg" style="text-align:center;padding:28px;color:#94a3b8;font-size:13px;background:#fafafa;border-radius:10px;border:2px dashed #e2e8f0;">
        👆 Select a supplier above to see their available products.
    </div>

    <div id="itemsWrap" style="display:none;">
        <table id="itemsTable">
            <thead>
                <tr>
                    <th style="width:30%">Product</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Unit Price (₱)</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="itemsBody"></tbody>
        </table>
        <div class="total-bar">
            <span class="total-label">Total Amount:</span>
            <span class="total-value">₱<span id="grandTotal">0.00</span></span>
        </div>
    </div>
</div>

{{-- ── SUBMIT ── --}}
<div style="display:flex;gap:12px;justify-content:flex-end;">
    <a href="{{ route('orders.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold" id="submitBtn" disabled style="opacity:.5;">
        ✓ Submit Order
    </button>
</div>
</form>

<script>
let products   = [];   // products for selected supplier
let rowIndex   = 0;

// Load products when supplier changes
async function loadProducts(supplierId) {
    const noSupMsg  = document.getElementById('noSupplierMsg');
    const noProMsg  = document.getElementById('noProductsMsg');
    const itemsWrap = document.getElementById('itemsWrap');
    const addBtn    = document.getElementById('addRowBtn');
    const submitBtn = document.getElementById('submitBtn');
    const tbody     = document.getElementById('itemsBody');

    noProMsg.style.display = 'none';

    if (!supplierId) {
        noSupMsg.style.display = 'block';
        itemsWrap.style.display = 'none';
        addBtn.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '.5';
        return;
    }

    noSupMsg.innerHTML = '⏳ Loading products...';

    try {
        const res  = await fetch(`/api/suppliers/${supplierId}/products`);
        const data = await res.json();
        products = data.products || [];

        if (products.length === 0) {
            noSupMsg.style.display = 'block';
            noSupMsg.innerHTML = '👆 Select a supplier above to see their available products.';
            noProMsg.style.display = 'block';
            itemsWrap.style.display = 'none';
            addBtn.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '.5';
            return;
        }

        // Reset and add first row
        tbody.innerHTML = '';
        rowIndex = 0;
        noSupMsg.style.display = 'none';
        itemsWrap.style.display = 'block';
        addBtn.style.display = 'inline-flex';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';

        addRow();

    } catch(e) {
        noSupMsg.innerHTML = '❌ Failed to load products. Please try again.';
        noSupMsg.style.display = 'block';
    }
}

function buildProductOptions(selectedId = '') {
    return products.map(p =>
        `<option value="${p.id}"
            data-name="${p.name}"
            data-category="${p.category}"
            data-unit="${p.unit}"
            data-price="${p.price}"
            ${p.id == selectedId ? 'selected' : ''}>
            ${p.name} — ₱${parseFloat(p.price).toLocaleString('en-PH', {minimumFractionDigits:2})}/${p.unit}
        </option>`
    ).join('');
}

function addRow() {
    const i   = rowIndex++;
    const tr  = document.createElement('tr');
    tr.id     = `row_${i}`;
    tr.innerHTML = `
        <td>
            <input type="hidden" name="items[${i}][item_name]"  id="name_${i}">
            <input type="hidden" name="items[${i}][category]"   id="cat_${i}">
            <input type="hidden" name="items[${i}][unit]"       id="unit_hidden_${i}">
            <select id="prod_${i}" onchange="onProductChange(${i})"
                    style="width:100%" required>
                <option value="">— Select product —</option>
                ${buildProductOptions()}
            </select>
        </td>
        <td><input type="text"   name="items[${i}][category_display]" id="catDisplay_${i}" readonly placeholder="Auto"></td>
        <td><input type="text"   name="items[${i}][unit_display]"     id="unitDisplay_${i}" readonly placeholder="Auto"></td>
        <td><input type="number" name="items[${i}][unit_price]"       id="price_${i}" readonly placeholder="0.00" step="0.01" min="0"></td>
        <td>
            <input type="number" name="items[${i}][quantity]" id="qty_${i}"
                   value="1" min="1" step="1" style="width:80px"
                   oninput="calcRow(${i})" required>
        </td>
        <td id="sub_${i}" style="font-weight:700;color:#0f1f3d;">₱0.00</td>
        <td>
            <button type="button" onclick="removeRow(${i})"
                    style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:18px;" title="Remove">✕</button>
        </td>
    `;
    document.getElementById('itemsBody').appendChild(tr);
}

function onProductChange(i) {
    const sel = document.getElementById(`prod_${i}`);
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;

    const name     = opt.dataset.name;
    const category = opt.dataset.category;
    const unit     = opt.dataset.unit;
    const price    = parseFloat(opt.dataset.price) || 0;

    document.getElementById(`name_${i}`).value        = name;
    document.getElementById(`cat_${i}`).value         = category;
    document.getElementById(`unit_hidden_${i}`).value = unit;
    document.getElementById(`catDisplay_${i}`).value  = category;
    document.getElementById(`unitDisplay_${i}`).value = unit;
    document.getElementById(`price_${i}`).value       = price.toFixed(2);

    calcRow(i);
}

function calcRow(i) {
    const price = parseFloat(document.getElementById(`price_${i}`)?.value) || 0;
    const qty   = parseFloat(document.getElementById(`qty_${i}`)?.value)   || 0;
    const sub   = price * qty;
    const subEl = document.getElementById(`sub_${i}`);
    if (subEl) subEl.textContent = '₱' + sub.toLocaleString('en-PH', {minimumFractionDigits:2});
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('[id^="sub_"]').forEach(el => {
        total += parseFloat(el.textContent.replace(/[₱,]/g, '')) || 0;
    });
    document.getElementById('grandTotal').textContent =
        total.toLocaleString('en-PH', {minimumFractionDigits:2});
}

function removeRow(i) {
    const row = document.getElementById(`row_${i}`);
    if (row) { row.remove(); calcTotal(); }
    // If no rows left, add one back
    if (document.getElementById('itemsBody').children.length === 0) addRow();
}
</script>

</x-app-layout>