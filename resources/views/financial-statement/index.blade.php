<x-app-layout>
    <x-slot name="header">Financial Statement</x-slot>
    <x-slot name="subheader">Income Statement, COGS, and Operational Profit/Loss Overview</x-slot>

    {{-- Financial Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <div style="background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <div style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase;">Total Sales Revenue</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--navy); margin-top: 6px;">
                ₱{{ number_format($totalRevenue, 2) }}
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">From confirmed orders</div>
        </div>

        <div style="background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <div style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase;">Cost of Goods Sold (COGS)</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--orange); margin-top: 6px;">
                ₱{{ number_format($cogsTotal, 2) }}
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Ingredients & Packaging</div>
        </div>

        <div style="background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <div style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase;">Operating Expenses (OpEx)</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--red); margin-top: 6px;">
                ₱{{ number_format($operatingExpensesTotal, 2) }}
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Utilities, Transport, Other</div>
        </div>

        <div style="background: var(--white); padding: 20px; border-radius: 12px; border: 1px solid var(--border);">
            <div style="font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase;">Net Income / Profit</div>
            <div style="font-size: 24px; font-weight: 800; color: {{ $netProfit >= 0 ? 'var(--green)' : 'var(--red)' }}; margin-top: 6px;">
                ₱{{ number_format($netProfit, 2) }}
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Revenue − Total Expenses</div>
        </div>

    </div>

    {{-- Expense Breakdown by Category Table --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
        
        <div style="background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="font-family: var(--f-display); font-size: 18px; margin-bottom: 16px;">Expense Breakdown by Category</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--muted);">
                        <th style="padding: 8px 0;">Category</th>
                        <th style="padding: 8px 0; text-align: right;">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expensesByCategory as $category => $amount)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px 0; font-weight: 600;">{{ ucfirst($category) }}</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: 700;">₱{{ number_format($amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding: 16px 0; text-align: center; color: var(--muted);">No expenses recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Income Statement Summary --}}
        <div style="background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
            <h3 style="font-family: var(--f-display); font-size: 18px; margin-bottom: 16px;">Income Statement Summary</h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                    <span>Gross Revenue</span>
                    <strong style="color: var(--green);">₱{{ number_format($totalRevenue, 2) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                    <span>Less: Cost of Goods Sold (COGS)</span>
                    <strong style="color: var(--red);">(₱{{ number_format($cogsTotal, 2) }})</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 2px solid var(--navy); font-weight: 700;">
                    <span>Gross Profit</span>
                    <span>₱{{ number_format($grossProfit, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border);">
                    <span>Less: Operating Expenses</span>
                    <strong style="color: var(--red);">(₱{{ number_format($operatingExpensesTotal, 2) }})</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 4px; font-size: 16px; font-weight: 800; color: {{ $netProfit >= 0 ? 'var(--green)' : 'var(--red)' }};">
                    <span>Net Income</span>
                    <span>₱{{ number_format($netProfit, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Recent Expenses Activity Log --}}
    <div style="background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
        <h3 style="font-family: var(--f-display); font-size: 18px; margin-bottom: 16px;">Recent Expense Entries</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--muted);">
                    <th style="padding: 10px;">Date</th>
                    <th style="padding: 10px;">Category</th>
                    <th style="padding: 10px;">Description</th>
                    <th style="padding: 10px;">Amount</th>
                    <th style="padding: 10px;">Logged By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentExpenses as $expense)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 10px;">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                        <td style="padding: 10px;"><span style="background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 12px;">{{ ucfirst($expense->category) }}</span></td>
                        <td style="padding: 10px;">{{ $expense->description }}</td>
                        <td style="padding: 10px; font-weight: 700;">₱{{ number_format($expense->amount, 2) }}</td>
                        <td style="padding: 10px;">{{ $expense->creator->first_name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: var(--muted);">No recent expenses logged.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>