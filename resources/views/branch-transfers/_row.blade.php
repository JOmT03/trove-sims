{{-- One row of the Branch Transfers list: an order (with its deliveries under it) or a delivery with no order. --}}
@if($o instanceof \App\Models\Batch)
@php
    $isVoid = !empty($o->voided_at);
    $ret = $o->items->sum('qty_returned');
@endphp
<tr>
    <td>{{ $o->batch_date->format('M d, Y') }}<div class="muted" style="font-size:11px;">No order</div></td>
    <td class="l" style="font-size:12.5px;">
        @foreach($o->items as $it){{ $it->product->product_name ?? '?' }} {{ $it->qty_sent }}@if(!$loop->last), @endif @endforeach
        @if($ret > 0)<div class="note">Returned {{ $ret }} (older batch, before final sale)</div>@endif
        @if($isVoid && $o->void_reason)<div class="muted" style="font-size:11.5px;">Voided: {{ $o->void_reason }}</div>@endif
    </td>
    <td><span class="muted">&mdash;</span></td>
    <td class="{{ $isVoid ? 'strike' : '' }}">{{ $o->items->sum('qty_sent') }}</td>
    <td><span class="muted">&mdash;</span></td>
    <td class="{{ $isVoid ? 'strike' : '' }}" style="font-weight:700;">&#8369;{{ number_format($batchRev($o),2) }}</td>
    <td class="st"><span class="badge {{ $isVoid ? 'voided' : 'sent' }}">{{ $isVoid ? 'Voided' : 'Sent' }}</span></td>
    <td class="ac"><div class="act">
        <details class="km"><summary class="kbtn" aria-label="More actions">&#8943;</summary><div class="kmenu">
            <button type="button" class="mi" onclick="viewBatch({{ $o->id }})">View receipt</button>
            @if($tab === 'archived')
                <form method="POST" action="{{ route('branch-transfers.restore') }}">@csrf<input type="hidden" name="type" value="batch"><input type="hidden" name="id" value="{{ $o->id }}"><button class="mi">Restore to Active</button></form>
            @else
                <button type="button" class="mi" onclick="askArchive('batch', {{ $o->id }}, 'this delivery', '{{ $o->batch_date->format('F Y') }}')">Archive<small>Filed under {{ $o->batch_date->format('F Y') }}</small></button>
            @endif
        </div></details>
    </div></td>
</tr>
@else
@php
    $isPending = in_array($o->status, ['requested','produced','partial']);
    if ($o->status === 'complete') { $label = $o->sent_qty > 0 ? 'Sent' : 'Closed'; $cls = $o->sent_qty > 0 ? 'sent' : 'cancelled'; }
    elseif ($o->status === 'cancelled') { $label = 'Cancelled'; $cls = 'cancelled'; }
    else { $label = 'Pending'; $cls = ''; }
    $closedQty = $o->items->sum('qty_closed');
    $deliv = $byOrder->get($o->id, collect());
    $orderRev = $deliv->filter(fn($b) => empty($b->voided_at))->sum($batchRev);
@endphp
<tr>
    <td>{{ $o->needed_by->format('M d, Y') }}<div class="muted" style="font-size:11px;">Order #{{ $o->id }}</div></td>
    <td class="l" style="font-size:12.5px;">
        @foreach($o->items as $it){{ $it->product->product_name ?? '?' }} {{ $it->delivered }}/{{ $it->qty }}@if(!$loop->last), @endif @endforeach
        @if($o->status === 'produced')<span class="muted"> &middot; produced</span>@endif
        @if($isPending && $o->sent_qty > 0 && $o->balance_qty > 0)
            <div class="note">Short {{ $o->balance_qty }}: to be delivered in the next batch</div>
        @endif
        @if($o->status === 'complete' && $o->short_reason)
            <div class="note">Closed short {{ $closedQty }}: {{ $o->short_reason }}</div>
        @endif
        @if($o->notes)<div class="muted" style="font-size:11.5px;">{{ $o->notes }}</div>@endif
    </td>
    <td>{{ $o->ordered_qty }}</td>
    <td>{{ $o->sent_qty }}</td>
    <td>
        @if($isPending)<span class="{{ $o->sent_qty > 0 && $o->balance_qty > 0 ? 'neg' : '' }}">{{ $o->balance_qty }}</span>
        @else<span class="muted">&mdash;</span>@endif
    </td>
    <td style="font-weight:700;">{!! $orderRev > 0 ? '&#8369;'.number_format($orderRev,2) : '<span class="muted">&mdash;</span>' !!}</td>
    <td class="st"><span class="badge {{ $cls }}">{{ $label }}</span></td>
    <td class="ac"><div class="act">
        @if($isPending)<button type="button" class="btn btn-green sm" onclick="openSend({{ $o->id }})">Send</button>@endif
        <details class="km"><summary class="kbtn" aria-label="More actions">&#8943;</summary><div class="kmenu">
            <button type="button" class="mi" onclick="showSlip({{ $o->id }}, 'o')">View order</button>
            @if($isPending)
                @if($o->status === 'requested')
                <form method="POST" action="{{ route('branch-orders.produce', $o) }}">@csrf<button class="mi">Mark as produced</button></form>
                @endif
                @if($o->sent_qty > 0 && $o->balance_qty > 0)
                <form method="POST" action="{{ route('branch-orders.close-short', $o) }}" class="mform" onsubmit="return confirm('Close this order? The {{ $o->balance_qty }} pcs balance will NOT be delivered or sold.');">
                    @csrf
                    <input type="text" name="reason" class="in" placeholder="Reason for closing short" required minlength="3" maxlength="500">
                    <button class="btn btn-red sm">Close short</button>
                </form>
                @endif
                @if($o->sent_qty == 0)
                <form method="POST" action="{{ route('branch-orders.cancel', $o) }}" onsubmit="return confirm('Cancel this order?');">@csrf<button class="mi bad">Cancel order</button></form>
                @endif
                <button type="button" class="mi" disabled>Archive<small>Send or close the order first</small></button>
            @elseif($tab === 'archived')
                <form method="POST" action="{{ route('branch-transfers.restore') }}">@csrf<input type="hidden" name="type" value="order"><input type="hidden" name="id" value="{{ $o->id }}"><button class="mi">Restore to Active</button></form>
            @else
                <button type="button" class="mi" onclick="askArchive('order', {{ $o->id }}, 'order #{{ $o->id }} and its deliveries', '{{ $o->needed_by->format('F Y') }}')">Archive<small>Filed under {{ $o->needed_by->format('F Y') }}</small></button>
            @endif
        </div></details>
    </div></td>
</tr>
@foreach($deliv as $b)
    @php $v = !empty($b->voided_at); @endphp
    <tr class="drow">
        <td><span class="arrow">&#8627;</span> {{ $b->batch_date->format('M d, Y') }}</td>
        <td class="l muted" style="font-size:12px;">@foreach($b->items as $it){{ $it->product->product_name ?? '?' }} &times;{{ $it->qty_sent }}@if(!$loop->last), @endif @endforeach</td>
        <td></td>
        <td class="{{ $v ? 'strike' : '' }}">{{ $b->items->sum('qty_sent') }}</td>
        <td></td>
        <td class="{{ $v ? 'strike' : '' }}">&#8369;{{ number_format($batchRev($b),2) }}</td>
        <td class="st"><span class="badge {{ $v ? 'voided' : 'sent' }}">{{ $v ? 'Voided' : 'Delivered' }}</span></td>
        <td class="ac"><div class="act">
            <details class="km"><summary class="kbtn" aria-label="More actions">&#8943;</summary><div class="kmenu">
                <button type="button" class="mi" onclick="viewBatch({{ $b->id }})">View receipt</button>
            </div></details>
        </div></td>
    </tr>
@endforeach
@endif
