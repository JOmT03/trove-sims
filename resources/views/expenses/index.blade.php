<x-app-layout>
    <x-slot name="header">Expenses</x-slot>
    <x-slot name="subheader">Manage money going out and track purchases</x-slot>

    {{-- Add New Expense Form --}}
    <div style="background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 24px;">
        <h3 style="font-family: var(--f-display); font-size: 18px; margin-bottom: 16px;">Record New Expense</h3>
        
        <form action="{{ route('expenses.store') }}" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            @csrf
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Date</label>
                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Category</label>
                <select name="category" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="ingredients">Ingredients (Grocery/Palengke)</option>
                    <option value="packaging">Packaging</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Description</label>
                <input type="text" name="description" placeholder="e.g., Flour 5kg from Palengke" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Amount (₱)</label>
                <input type="number" step="0.01" name="amount" placeholder="0.00" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Link Inventory (Optional)</label>
                <select name="inventory_id" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="">-- None --</option>
                    @foreach($inventoryItems as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name ?? $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px;">Restock Qty (Optional)</label>
                <input type="number" name="quantity" placeholder="0" min="1" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;">
            </div>

            <div style="grid-column: 1 / -1; margin-top: 8px;">
                <button type="submit" style="background: var(--gold); color: var(--navy); border: none; font-weight: 700; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                    + Record Expense
                </button>
            </div>
        </form>
    </div>

    {{-- Expense List Table --}}
    <div style="background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
        <h3 style="font-family: var(--f-display); font-size: 18px; margin-bottom: 16px;">Expense History</h3>

        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--muted);">
                    <th style="padding: 10px;">Date</th>
                    <th style="padding: 10px;">Category</th>
                    <th style="padding: 10px;">Description</th>
                    <th style="padding: 10px;">Amount</th>
                    <th style="padding: 10px;">Linked Item</th>
                    <th style="padding: 10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                        <td style="padding: 10px;"><span style="background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 12px;">{{ ucfirst($expense->category) }}</span></td>
                        <td style="padding: 10px;">{{ $expense->description }}</td>
                        <td style="padding: 10px; font-weight: 700;">₱{{ number_format($expense->amount, 2) }}</td>
                        <td style="padding: 10px;">{{ $expense->inventoryItem->item_name ?? '—' }}</td>
                        <td style="padding: 10px;">
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Delete this expense entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: var(--red); background: none; border: none; cursor: pointer; font-size: 12px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: var(--muted);">No expenses recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>