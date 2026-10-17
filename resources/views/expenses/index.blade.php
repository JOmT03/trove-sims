<x-app-layout>
    <x-slot name="header">Expenses</x-slot>
    <x-slot name="subheader">Expense ledger &mdash; entries are permanent. Mistakes are voided, never deleted.</x-slot>

    @php
        $card = 'background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 24px;';
        $inp  = 'width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;';
        $lbl  = 'display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;';
    @endphp

    @if(session('success'))<div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if($errors->any())<div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <div style="{{ $card }}">
        <h3 style="font-family: var(--f-display); font-size: 18px; margin-bottom: 4px;">Record New Expense</h3>
        <p style="font-size:12px;color:var(--muted);margin-bottom:16px;">Once saved, an entry cannot be edited or deleted. Double-check the amount before saving.</p>

        <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            @csrf
            <div>
                <label style="{{ $lbl }}">Date</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required style="{{ $inp }}">
            </div>
            <div>
                <label style="{{ $lbl }}">Category</label>
                <select name="category" id="exp-cat" required style="{{ $inp }}">
                    <optgroup label="Cost of Goods Sold (COGS)">
                        <option value="ingredients" @selected(old('category')==='ingredients')>Ingredients (Raw Materials / Grocery)</option>
                        <option value="packaging" @selected(old('category')==='packaging')>Packaging &amp; Supplies</option>
                    </optgroup>
                    <optgroup label="Operating Expenses (OpEx)">
                        <option value="utilities">Utilities (Water / Electricity / Gas)</option>
                        <option value="rent">Rent / Space Lease</option>
                        <option value="transport">Transportation / Delivery</option>
                        <option value="labor">Labor / Staff Allowance</option>
                        <option value="maintenance">Maintenance &amp; Repairs</option>
                        <option value="other">Other Operational Costs</option>
                    </optgroup>
                </select>
            </div>
            <div>
                <label style="{{ $lbl }}">Description</label>
                <input type="text" name="description" value="{{ old('description') }}" maxlength="255" placeholder="e.g., Flour 5kg from Palengke" required style="{{ $inp }}">
            </div>
            <div>
                <label style="{{ $lbl }}">Amount (₱)</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" placeholder="0.00" required style="{{ $inp }}">
            </div>
            <div>
                <label style="{{ $lbl }}">Restock inventory item <span style="font-weight:400;color:var(--muted)">(optional, Ingredients only)</span></label>
                <select name="inventory_id" id="exp-inv" style="{{ $inp }}">
                    <option value="">&mdash; not a stock purchase &mdash;</option>
                    @foreach($inventoryItems as $inv)
                        <option value="{{ $inv->id }}" @selected(old('inventory_id')==$inv->id)>{{ $inv->item_name }} ({{ $inv->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="{{ $lbl }}">Quantity bought <span style="font-weight:400;color:var(--muted)">(if restocking)</span></label>
                <input type="number" min="1" step="1" name="quantity" id="exp-qty" value="{{ old('quantity') }}" style="{{ $inp }}">
            </div>
            <div>
                <label style="{{ $lbl }}">Receipt <span style="font-weight:400;color:var(--muted)">(JPG/PNG/PDF, max 4MB)</span></label>
                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" style="{{ $inp }}">
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" style="background: var(--gold); color: var(--navy); border: none; font-weight: 700; padding: 10px 20px; border-radius: 6px; cursor: pointer;">+ Record Expense</button>
            </div>
        </form>
    </div>

    <div style="{{ $card }}">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-bottom:16px;">
            <div><label style="{{ $lbl }}">From</label><input type="date" name="from" value="{{ $from }}" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;"></div>
            <div><label style="{{ $lbl }}">To</label><input type="date" name="to" value="{{ $to }}" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;"></div>
            <div><label style="{{ $lbl }}">Category</label>
                <select name="category" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;">
                    <option value="">All</option>
                    @foreach(['ingredients','packaging','utilities','rent','transport','labor','maintenance','other'] as $c)
                        <option value="{{ $c }}" @selected($category===$c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" style="background:var(--navy);color:#fff;border:none;font-weight:700;padding:9px 18px;border-radius:6px;cursor:pointer;">Filter</button>
        </form>

        <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:12px;font-size:14px;">
            <div>Counted total: <strong>₱{{ number_format($activeTotal, 2) }}</strong></div>
            @if($voidedTotal > 0)<div style="color:var(--muted);">Voided (excluded): <strong>₱{{ number_format($voidedTotal, 2) }}</strong></div>@endif
        </div>

        <div style="overflow-x:auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--muted);">
                    <th style="padding: 10px;">Date</th><th style="padding: 10px;">Category</th><th style="padding: 10px;">Description</th>
                    <th style="padding: 10px;">Amount</th><th style="padding: 10px;">Logged by</th><th style="padding: 10px;">Receipt</th><th style="padding: 10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    @php $locked = in_array($expense->expense_date->format('Y-m'), $closedMonths); @endphp
                    <tr style="border-bottom: 1px solid var(--border); {{ $expense->isVoided() ? 'color:var(--muted);background:#fafafa;' : '' }}">
                        <td style="padding: 10px;">{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td style="padding: 10px;"><span style="background:#eee;padding:2px 8px;border-radius:4px;font-size:12px;">{{ ucfirst($expense->category) }}</span></td>
                        <td style="padding: 10px; {{ $expense->isVoided() ? 'text-decoration:line-through;' : '' }}">
                            {{ $expense->description }}
                            @if($expense->inventoryItem)<div style="font-size:11px;color:var(--muted);">+{{ $expense->quantity }} {{ $expense->inventoryItem->item_name }}</div>@endif
                        </td>
                        <td style="padding: 10px; font-weight: 700; {{ $expense->isVoided() ? 'text-decoration:line-through;' : '' }}">₱{{ number_format($expense->amount, 2) }}</td>
                        <td style="padding: 10px;">{{ $expense->creator->first_name ?? 'System' }}</td>
                        <td style="padding: 10px;">@if($expense->receipt_path)<a href="{{ route('expenses.receipt', $expense) }}" target="_blank" style="color:var(--navy);font-size:12px;">View</a>@else<span style="color:var(--muted);">&mdash;</span>@endif</td>
                        <td style="padding: 10px;">
                            @if($expense->isVoided())
                                <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:700;">VOID</span>
                                <div style="font-size:11px;margin-top:4px;">{{ $expense->void_reason }}<br>by {{ $expense->voider->first_name ?? 'System' }}, {{ $expense->voided_at->format('M d, Y g:i A') }}</div>
                            @elseif($locked)
                                <span style="background:#e5e7eb;padding:2px 8px;border-radius:4px;font-size:12px;">🔒 Closed month</span>
                            @else
                                <details>
                                    <summary style="cursor:pointer;color:var(--red);font-size:12px;">Void…</summary>
                                    <form action="{{ route('expenses.void', $expense) }}" method="POST" style="margin-top:6px;display:flex;flex-direction:column;gap:6px;min-width:200px;"
                                          onsubmit="return confirm('Void this expense? This cannot be undone.')">
                                        @csrf
                                        <input type="text" name="void_reason" required minlength="5" maxlength="255" placeholder="Reason (required)" style="padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;">
                                        <button type="submit" style="background:var(--red);color:#fff;border:none;padding:6px 10px;border-radius:6px;font-size:12px;cursor:pointer;">Confirm void</button>
                                    </form>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding: 20px; text-align: center; color: var(--muted);">No expenses in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
<script>
(function () {
    var cat = document.getElementById('exp-cat'), inv = document.getElementById('exp-inv'), qty = document.getElementById('exp-qty');
    if (!cat || !inv || !qty) return;
    function sync() {
        var on = cat.value === 'ingredients';
        [inv, qty].forEach(function (el) {
            el.disabled = !on;
            el.style.opacity = on ? '1' : '.5';
            el.style.cursor = on ? '' : 'not-allowed';
            if (!on) el.value = '';
        });
    }
    cat.addEventListener('change', sync);
    sync();
})();
</script>
</x-app-layout>
