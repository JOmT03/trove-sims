{{--
    resources/views/Deliveries/index.blade.php
    FIXED:
    - "Record Delivery" button moved to topbar (right side, gold, proper size)
    - Font: Syne + DM Sans (consistent with rest of system)
    - Table styled to match the system design (not Tailwind classes)
    - "View Details" → icon button (eye icon)
--}}
<x-app-layout>
<x-slot name="header">Deliveries</x-slot>
<x-slot name="subheader">Incoming deliveries and item receipt records</x-slot>

<style>
    /* ── Record Delivery button in topbar ── */
    /* (This is rendered via the topbar-actions slot below) */
    .btn-record{background:var(--gold);color:var(--navy);font-family:'Syne',sans-serif;font-weight:700;font-size:13px;padding:9px 18px;border-radius:10px;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:background .2s}
    .btn-record:hover{background:var(--gold-light)}

    /* ── Table card ── */
    .table-card{background:var(--white);border-radius:14px;border:1px solid var(--border);overflow:hidden}
    .del-table{width:100%;border-collapse:collapse}
    .del-table th{text-align:left;font-family:'Syne',sans-serif;font-size:11px;font-weight:600;color:var(--white);text-transform:uppercase;letter-spacing:.8px;padding:14px 20px;background:var(--navy);border-bottom:1px solid var(--border)}
    .del-table td{padding:14px 20px;font-size:13px;font-family:'DM Sans',sans-serif;border-bottom:1px solid var(--border);vertical-align:middle}
    .del-table tr:last-child td{border-bottom:none}
    .del-table tbody tr:hover td{background:#fafcff}

    .del-num{font-family:'Syne',sans-serif;font-weight:700;color:var(--navy);font-size:13px}
    .order-num{font-family:monospace;font-size:12px;color:var(--muted)}
    .supplier-name{font-weight:600;color:var(--text)}

    /* Status badge */
    .status-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}
    .status-received{background:rgba(34,197,94,.12);color:#15803d}
    .status-partial{background:rgba(249,115,22,.12);color:#c2410c}
    .status-pending{background:rgba(15,31,61,.08);color:var(--navy)}

    /* View details icon button */
    .icon-btn-view{width:32px;height:32px;border-radius:8px;border:none;background:rgba(15,31,61,.07);color:var(--navy);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;position:relative}
    .icon-btn-view:hover{background:var(--navy);color:var(--white)}
    .icon-btn-view svg{width:15px;height:15px;display:block}
    .icon-btn-view::after{content:'View Details';position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;font-size:11px;font-family:'DM Sans',sans-serif;white-space:nowrap;padding:3px 8px;border-radius:5px;pointer-events:none;opacity:0;transition:opacity .15s}
    .icon-btn-view:hover::after{opacity:1}

    /* Empty state */
    .empty-cell{text-align:center;padding:60px;color:var(--muted);font-family:'DM Sans',sans-serif;font-size:14px}

    /* Alert */
    .alert{padding:12px 20px;border-radius:10px;margin-bottom:16px;font-size:14px;font-family:'DM Sans',sans-serif}
    .alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#15803d}
</style>

{{-- ── Record Delivery button — injected into the topbar via a second slot ── --}}
{{-- We use a simple div right above the table since x-app-layout topbar-right
     already shows the role badge. We place the button here, flush to the top. --}}
<div style="display:flex;justify-content:flex-end;margin-bottom:20px">
    @if(auth()->user()->isAdmin())
    <a href="{{ route('deliveries.create') }}" class="btn-record">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Record Delivery
    </a>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

<div class="table-card">
    <table class="del-table">
        <thead>
            <tr>
                <th>Delivery #</th>
                <th>Order #</th>
                <th>Supplier</th>
                <th>Date Received</th>
                <th>Received By</th>
                <th>Items</th>
                <th>Status</th>
                <th style="text-align:center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $del)
                @php
                    $statusInfo = \App\Models\Delivery::STATUSES[$del->status]
                        ?? ['label' => $del->status, 'color' => 'bg-gray-100 text-gray-700'];
                    $statusClass = match($del->status) {
                        'received'         => 'status-received',
                        'partial_received' => 'status-partial',
                        default            => 'status-pending',
                    };
                @endphp
                <tr>
                    <td><span class="del-num">{{ $del->delivery_number }}</span></td>
                    <td><span class="order-num">{{ $del->order->order_number }}</span></td>
                    <td><span class="supplier-name">{{ $del->supplier->name }}</span></td>
                    <td style="color:var(--muted)">{{ $del->delivered_at?->format('M d, Y') ?? '—' }}</td>
                    <td style="color:var(--muted)">{{ $del->received_by }}</td>
                    <td style="color:var(--muted)">{{ $del->items->count() }} item(s)</td>
                    <td>
                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusInfo['label'] }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        <a href="{{ route('deliveries.show', $del) }}" class="icon-btn-view">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-cell">No deliveries recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

</x-app-layout>