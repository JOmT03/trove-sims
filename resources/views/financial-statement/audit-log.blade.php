<x-app-layout>
    <x-slot name="header">Financial Audit Log</x-slot>
    <x-slot name="subheader">Permanent record of every financial action &mdash; read-only</x-slot>

    <div style="background: var(--white); padding: 24px; border-radius: 12px; border: 1px solid var(--border);">
        <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
            <select name="action" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;">
                <option value="">All actions</option>
                @foreach(['expense.recorded'=>'Expense recorded','expense.voided'=>'Expense voided','expense.receipt_viewed'=>'Receipt viewed','order.completed'=>'Order completed','period.closed'=>'Month closed','system'=>'System'] as $k=>$label)
                    <option value="{{ $k }}" @selected($action===$k)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" style="background:var(--navy);color:#fff;border:none;font-weight:700;padding:9px 18px;border-radius:6px;cursor:pointer;">Filter</button>
        </form>

        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
            <thead><tr style="border-bottom:2px solid var(--border);color:var(--muted);">
                <th style="padding:10px;">When</th><th style="padding:10px;">Who</th><th style="padding:10px;">Action</th><th style="padding:10px;">Record</th><th style="padding:10px;">Details</th><th style="padding:10px;">IP</th>
            </tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr style="border-bottom:1px solid var(--border);vertical-align:top;">
                    <td style="padding:10px;white-space:nowrap;">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td style="padding:10px;">{{ $log->user_name ?? 'System' }}<div style="font-size:11px;color:var(--muted);">{{ $log->user_role }}</div></td>
                    <td style="padding:10px;"><code>{{ $log->action }}</code></td>
                    <td style="padding:10px;">{{ $log->entity_type ? $log->entity_type.' #'.$log->entity_id : '—' }}</td>
                    <td style="padding:10px;font-size:12px;">
                        @foreach($log->detailsArray() as $k => $v)
                            @if($v !== null && $v !== '')<div><span style="color:var(--muted);">{{ str_replace('_',' ',$k) }}:</span> {{ is_bool($v) ? ($v?'yes':'no') : $v }}</div>@endif
                        @endforeach
                    </td>
                    <td style="padding:10px;color:var(--muted);">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:20px;text-align:center;color:var(--muted);">No activity logged yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        <div style="margin-top:16px;display:flex;gap:12px;font-size:13px;">
            @if($page > 1)<a style="color:var(--navy);" href="{{ route('financial-audit-log.index', array_filter(['action'=>$action,'page'=>$page-1])) }}">&larr; Newer</a>@endif
            @if($hasMore)<a style="color:var(--navy);" href="{{ route('financial-audit-log.index', array_filter(['action'=>$action,'page'=>$page+1])) }}">Older &rarr;</a>@endif
        </div>
    </div>
</x-app-layout>
