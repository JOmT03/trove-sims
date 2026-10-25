<x-app-layout>
<x-slot name="header">Branch Transfers</x-slot>
<x-slot name="subheader">Matina &rarr; Jacinto: orders and their deliveries (final sale)</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);overflow:hidden;margin-bottom:22px;}
.card-h{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid #EDE0D0;gap:10px;flex-wrap:wrap;}
.card-h h3{font-size:15px;font-weight:800;color:#2E1C10;margin:0;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:right;font-size:11px;text-transform:uppercase;color:#8A7460;padding:10px 14px;background:#FDF6EC;}
th:first-child,th.l{text-align:left;}
td{padding:11px 14px;border-top:1px solid #EDE0D0;text-align:right;font-variant-numeric:tabular-nums;vertical-align:top;}
td:first-child,td.l{text-align:left;}
.ft th,.ft td{text-align:left;border-top:none;}
.btn{padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-block;}
.btn.sm{padding:6px 12px;font-size:12px;}
.btn-gold{background:#D9782C;color:#fff;} .btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.btn-green{background:#166534;color:#fff;} .btn-red{background:#fff;color:#B91C1C;border:1px solid #fecaca;}
.badge{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:999px;background:#FBEBD6;color:#B45309;}
.badge.sent{background:#E7F3EA;color:#166534;}
.badge.cancelled,.badge.voided{background:#f3f4f6;color:#6b7280;}
.neg{color:#C2410C;font-weight:700;} .muted{color:#9ca3af;} .strike{text-decoration:line-through;color:#9ca3af;}
.note{font-size:11.5px;color:#B45309;margin-top:4px;font-weight:600;}
.alert-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.alert-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
label.f{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
.in{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#fff;}
.addrow{padding:7px 12px;border:1.5px dashed #EDE0D0;border-radius:9px;background:#fff;color:#8A7460;font-size:12.5px;font-weight:600;cursor:pointer;}
.rm{background:none;border:none;color:#ef4444;cursor:pointer;font-size:15px;}
.twrap{overflow-x:auto;}
table.tbl{table-layout:fixed;min-width:900px;}
.tbl td{vertical-align:middle;}
th.st,td.st{text-align:center;} th.ac,td.ac{text-align:right;}
.act{display:flex;gap:6px;justify-content:flex-end;align-items:center;}
.drow td{background:#FFFBF5;border-top:1px dashed #F3E9DB;padding-top:8px;padding-bottom:8px;font-size:12.5px;}
.drow td:first-child{white-space:nowrap;}
.drow .arrow{color:#B9A895;margin-left:2px;}
details.km{display:inline-block;}
details.km summary{list-style:none;}
details.km summary::-webkit-details-marker{display:none;}
.kbtn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:28px;border:1px solid #EDE0D0;border-radius:8px;color:#8A7460;cursor:pointer;font-size:15px;background:#fff;}
details.km[open] .kbtn,.kbtn:hover{background:#FDF6EC;color:#2E1C10;}
.kmenu{display:none;position:fixed;z-index:50;background:#fff;border:1px solid #EDE0D0;border-radius:10px;box-shadow:0 10px 30px rgba(46,28,16,.18);padding:4px;min-width:240px;text-align:left;}
details.km[open] .kmenu{display:block;}
.kmenu form{margin:0;}
.mi{display:block;width:100%;text-align:left;background:none;border:0;padding:8px 10px;border-radius:7px;font-size:12.5px;font-weight:600;color:#2E1C10;cursor:pointer;font-family:inherit;}
.mi:hover{background:#FDF6EC;} .mi.bad{color:#B91C1C;}
.mi:disabled{color:#B9A895;cursor:not-allowed;background:none;}
.mi small{display:block;font-weight:500;color:#B9A895;font-size:11px;}
.mform{display:flex;gap:6px;padding:6px;} .mform .btn{white-space:nowrap;} .mform .in{padding:6px 8px;font-size:12px;}
.tabs{display:flex;gap:4px;background:#F4EEE7;padding:3px;border-radius:999px;}
.tabs a{padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700;color:#8A7460;text-decoration:none;}
.tabs a.on{background:#fff;color:#2E1C10;box-shadow:0 1px 4px rgba(0,0,0,.08);}
.tabs a span{color:#B9A895;margin-left:3px;}
details.folder{border-top:1px solid #EDE0D0;}
details.folder summary{list-style:none;display:flex;align-items:center;gap:12px;padding:12px 16px;cursor:pointer;}
details.folder summary::-webkit-details-marker{display:none;}
details.folder summary:hover{background:#FDF6EC;}
.fic{width:22px;height:16px;background:#D9782C;border-radius:2px 4px 4px 4px;position:relative;flex:none;margin-top:4px;}
.fic::before{content:"";position:absolute;left:0;top:-4px;width:10px;height:5px;background:#D9782C;border-radius:3px 3px 0 0;}
.ft1{font-weight:800;font-size:14px;color:#2E1C10;} .ft2{font-size:12px;color:#8A7460;font-variant-numeric:tabular-nums;}
.fright{margin-left:auto;display:flex;align-items:center;gap:10px;}
.chev{color:#8A7460;transition:transform .15s;} details.folder[open] .chev{transform:rotate(90deg);}
details.folder .tbl tr:first-child td{border-top:1px dashed #EDE0D0;}
.seg2{display:flex;gap:4px;background:#F4EEE7;padding:3px;border-radius:9px;margin-bottom:12px;}
.seg2 button{flex:1;border:0;background:none;padding:7px;border-radius:7px;font-weight:700;font-size:12.5px;color:#8A7460;cursor:pointer;}
.seg2 button.on{background:#fff;color:#2E1C10;box-shadow:0 1px 4px rgba(0,0,0,.08);}
.prev{margin-top:12px;background:#FDF6EC;border-radius:10px;padding:10px 12px;font-size:12.5px;}
.sm2{display:flex;gap:22px;flex-wrap:wrap;padding:10px 18px;background:#FDF6EC;border-bottom:1px solid #EDE0D0;font-size:13px;}
.bov{position:fixed;inset:0;background:rgba(46,28,16,.45);display:none;align-items:center;justify-content:center;padding:16px;z-index:60;}
.bov.on{display:flex;}
.bmd{background:#fff;border-radius:16px;width:100%;max-width:660px;max-height:100%;display:flex;flex-direction:column;box-shadow:0 20px 50px rgba(0,0,0,.3);}
.bmh{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #EDE0D0;}
.bmh h3{margin:0;font-size:16px;font-weight:800;color:#2E1C10;}
.bx{background:none;border:0;color:#8A7460;font-size:22px;cursor:pointer;line-height:1;padding:2px 6px;}
.bmb{padding:18px 20px;overflow:auto;}
.bmf{display:flex;gap:10px;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid #EDE0D0;}
.bg2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
@media(max-width:520px){.bg2{grid-template-columns:1fr;}}
.brw{display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;}
.brw input.q{width:84px;} .brw input.p{width:100px;}
.bcap{font-size:11px;color:#8A7460;display:flex;gap:8px;padding-right:34px;margin-bottom:4px;}
.bcap span:first-child{flex:1;} .bcap span:nth-child(2){width:84px;} .bcap span:nth-child(3){width:100px;}
.bh{font-size:11.5px;font-weight:600;color:#166534;margin-top:4px;} .bh.lo{color:#C2410C;}
.bwn{display:none;background:#FEF3C7;color:#92400E;padding:10px 13px;border-radius:9px;font-size:13px;margin-top:10px;}
.ber{display:none;background:#FBE4DA;color:#991b1b;padding:10px 13px;border-radius:9px;font-size:13px;margin-top:10px;}
.slip h4{margin:0 0 2px;font-size:18px;color:#2E1C10;}
.slip .mt{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:10px 0;align-items:center;}
.bpb{border:2px solid #C2410C;color:#C2410C;padding:2px 10px;border-radius:6px;font-weight:700;}
.bsg{display:flex;gap:40px;margin-top:34px;font-size:12px;} .bsg span{flex:1;border-top:1px solid #8A7460;padding-top:4px;color:#8A7460;}
.bmh .ha{display:flex;gap:6px;align-items:center;}
.btabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;}
.btab{padding:6px 12px;border-radius:999px;border:1px solid #EDE0D0;background:#fff;color:#8A7460;font-size:12px;font-weight:700;cursor:pointer;}
.btab.on{background:#2E1C10;border-color:#2E1C10;color:#fff;}
.slip .shop{font-size:12px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;color:#D9782C;margin-bottom:6px;}
.slip table{margin-top:8px;} .slip tr.tot td{font-weight:800;background:#FDF6EC;}
.nys{border:2px solid #C2410C;border-radius:10px;padding:10px 14px;margin-top:16px;color:#7C2D12;}
.nys b.h{display:block;color:#C2410C;font-size:13px;letter-spacing:.5px;margin-bottom:4px;}
.vdb{background:#f3f4f6;color:#374151;border-radius:9px;padding:9px 12px;margin:10px 0;font-size:13px;}
.bvl{display:block;margin-top:14px;font-size:12px;color:#8A7460;text-align:right;}
@media print{
 body *{visibility:hidden!important;}
 #slipArea,#slipArea *{visibility:visible!important;}
 .bov,.bmd,.bmb{position:static!important;display:block!important;overflow:visible!important;max-height:none!important;box-shadow:none!important;background:none!important;padding:0!important;border:0!important;}
 #slipArea{position:absolute;left:0;top:0;width:100%;}
}
</style>

@php
    $productData = $products->map(fn($p) => ['id' => $p->id, 'name' => $p->product_name])->values();
    $byOrder = $batches->groupBy('branch_order_id');
    $batchRev = fn($b) => $b->items->sum(fn($i) => ($i->qty_sent - $i->qty_returned) * $i->unit_price);
    $batches->loadMissing('items.product');
    $isOpen = fn($o) => in_array($o->status, ['requested','produced','partial']);
    // Deliveries with no order (or whose order no longer exists), on the current tab
    $other = $batches->filter(fn($b) => (!$b->branch_order_id || !in_array($b->branch_order_id, $orderIds))
        && (($tab === 'archived') === !empty($b->archived_at)));
    $rowDate = fn($r) => $r instanceof \App\Models\Batch ? $r->batch_date : $r->needed_by;
    $rowRev = fn($r) => $r instanceof \App\Models\Batch ? $batchRev($r)
        : $byOrder->get($r->id, collect())->filter(fn($b) => empty($b->voided_at))->sum($batchRev);
    // Open orders first, then finished orders and order-less deliveries, newest first
    $finished = $orders->reject($isOpen)->concat($other)->sortByDesc(fn($r) => $rowDate($r)->format('Y-m-d'))->values();
    $feed = $orders->filter($isOpen)->values()->concat($finished);
    $pendO = $orders->filter($isOpen);
    $cols = '<colgroup><col style="width:130px"><col><col style="width:78px"><col style="width:66px"><col style="width:78px"><col style="width:118px"><col style="width:104px"><col style="width:132px"></colgroup>';
@endphp

<div style="max-width:1050px;margin:0 auto;">
@if(session('success'))<div class="alert-ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert-err">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert-err"><ul style="margin:0 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-h">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <h3>Orders &amp; Deliveries</h3>
            <nav class="tabs">
                <a href="{{ route('branch-transfers.index') }}" class="{{ $tab === 'active' ? 'on' : '' }}">Active <span>{{ $activeCount }}</span></a>
                <a href="{{ route('branch-transfers.index', ['tab' => 'archived']) }}" class="{{ $tab === 'archived' ? 'on' : '' }}">Archived <span>{{ $archivedCount }}</span></a>
            </nav>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if($tab === 'active')<button type="button" class="btn btn-outline" onclick="openBulk()">Archive a period</button>@endif
            <button type="button" class="btn btn-outline" onclick="openNew()">+ New Order</button>
            <button type="button" class="btn btn-gold" onclick="openSend()">+ Send Batch</button>
        </div>
    </div>

    <div class="sm2">
        @if($tab === 'active')
            <span><b>{{ $pendO->count() }}</b> pending order{{ $pendO->count() == 1 ? '' : 's' }}</span>
            <span><b>{{ $pendO->sum('balance_qty') }}</b> pcs not yet sent</span>
            @if($pendO->count())<span class="muted">Earliest needed: {{ $pendO->sortBy('needed_by')->first()->needed_by->format('M d, Y') }}</span>
            @else<span class="muted">Everything is sent.</span>@endif
        @else
            <span class="muted">Archived orders and deliveries, filed by month. Nothing is deleted, and the sales still count in reports.</span>
        @endif
    </div>

    <div class="twrap">
    <table class="tbl">
        {!! $cols !!}
        <thead><tr><th>Date</th><th class="l">Items (sent / ordered)</th><th>Ordered</th><th>Sent</th><th>Balance</th><th>Revenue</th><th class="st">Status</th><th class="ac">Actions</th></tr></thead>
        @if($tab === 'active')
        <tbody>
        @forelse($feed as $o)
            @include('branch-transfers._row', ['o' => $o])
        @empty
            <tr><td colspan="8" class="l" style="color:#8A7460;">No orders yet. Click + New Order.</td></tr>
        @endforelse
        </tbody>
        @endif
    </table>
    </div>

    @if($tab === 'archived')
        @forelse($finished->groupBy(fn($r) => $rowDate($r)->format('Y-m')) as $ym => $rows)
        @php $mLabel = \Carbon\Carbon::createFromFormat('Y-m-d', $ym . '-01')->format('F Y'); @endphp
        <details class="folder" {{ $loop->first ? 'open' : '' }}>
            <summary>
                <span class="fic" aria-hidden="true"></span>
                <span><span class="ft1">{{ $mLabel }}</span><br><span class="ft2">{{ $rows->count() }} item{{ $rows->count() == 1 ? '' : 's' }} &middot; &#8369;{{ number_format($rows->sum($rowRev), 2) }}</span></span>
                <span class="fright">
                    <form method="POST" action="{{ route('branch-transfers.restore-month') }}" onsubmit="return confirm('Restore everything in {{ $mLabel }} to Active?');" style="margin:0;">
                        @csrf<input type="hidden" name="key" value="{{ $ym }}">
                        <button class="btn btn-outline sm" onclick="event.stopPropagation()">Restore all</button>
                    </form>
                    <span class="chev">&#9654;</span>
                </span>
            </summary>
            <div class="twrap"><table class="tbl">{!! $cols !!}<tbody>
                @foreach($rows as $o)
                    @include('branch-transfers._row', ['o' => $o])
                @endforeach
            </tbody></table></div>
        </details>
        @empty
        <div style="padding:30px 18px;text-align:center;color:#8A7460;">Nothing archived yet. Use &#8943; &rarr; Archive on a finished order or delivery, or Archive a period.</div>
        @endforelse
    @endif
</div>

</div>

<div id="bov" class="bov" role="dialog" aria-modal="true" aria-labelledby="bmt">
    <div class="bmd">
        <div class="bmh"><h3 id="bmt"></h3><div class="ha"><button type="button" class="btn btn-gold sm" id="bpr" style="display:none" onclick="doPrint()"></button><button type="button" class="bx" onclick="closeM()" aria-label="Close">&times;</button></div></div>
        <div class="bmb" id="bmb"></div>
    </div>
</div>

@php
    $jf = JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
    $pnames = $products->pluck('product_name', 'id');
    $sendProducts = $products->map(fn($p) => ['id' => (int) $p->id, 'name' => $p->product_name, 'price' => (float) $p->price, 'stock' => (int) $p->stock_quantity])->values();
    $siteData = $sites->map(fn($s) => ['id' => (int) $s->id, 'name' => $s->site_name])->values();
    $openOrders = $orders->filter(fn($o) => in_array($o->status, ['requested','produced','partial']) && $o->balance_qty > 0)
        ->map(fn($o) => [
            'id' => (int) $o->id, 'need' => $o->needed_by->format('M d, Y'), 'ord' => (int) $o->ordered_qty, 'bal' => (int) $o->balance_qty,
            'items' => $o->items->map(fn($i) => ['pid' => (int) $i->product_id, 'q' => (int) $i->qty, 'bl' => (int) $i->balance])->values(),
        ])->values();
    $slips = $orders->map(function ($o) use ($byOrder, $batchRev, $pnames) {
        $del = $byOrder->get($o->id, collect())
            ->filter(fn($b) => empty($b->voided_at))
            ->sortBy('id')
            ->values();
        $pend = in_array($o->status, ['requested', 'produced', 'partial']);

        return [
            'id' => (int) $o->id,
            'raw' => $o->needed_by->format('Y-m-d'),
            'need' => $o->needed_by->format('M d, Y'),
            // Same labels as the order table.
            'st' => $pend ? 'Pending' : ($o->status === 'complete' ? ($o->sent_qty > 0 ? 'Sent' : 'Closed') : 'Cancelled'),
            'ord' => (int) $o->ordered_qty,
            'sent' => (int) $o->sent_qty,
            'bal' => (int) $o->balance_qty,
            'note' => $o->notes,
            'why' => $o->short_reason,
            'closed' => (int) $o->items->sum('qty_closed'),
            'rev' => round($del->sum($batchRev), 2),
            'items' => $o->items->map(fn($i) => [
                'n' => $i->product->product_name ?? '?',
                'q' => (int) $i->qty,
                'd' => (int) $i->delivered,
                'bl' => (int) $i->balance,
            ])->values(),
            'dl' => $del->map(fn($b) => [
                'd' => $b->batch_date->format('M d, Y'),
                'pcs' => (int) $b->items->sum('qty_sent'),
                'amt' => round($batchRev($b), 2),
                'items' => $b->items->map(fn($i) => [
                    'n' => $pnames[$i->product_id] ?? '?',
                    'q' => (int) $i->qty_sent,
                    'ret' => (int) $i->qty_returned,
                    'net' => (int) $i->qty_sent - (int) $i->qty_returned,
                    'pr' => (float) $i->unit_price,
                    'amt' => round(((int) $i->qty_sent - (int) $i->qty_returned) * (float) $i->unit_price, 2),
                ])->values(),
            ])->values(),
        ];
    })->values();
    $archivable = $tab === 'active'
        ? $finished->map(fn($r) => ['raw' => $rowDate($r)->format('Y-m-d'), 'rev' => round($rowRev($r), 2)])->values()
        : collect();
    $batchData = $batches->map(fn($b) => [
        'id' => (int) $b->id,
        'raw' => $b->batch_date->format('Y-m-d'),
        'd' => $b->batch_date->format('M d, Y'),
        'from' => $b->sourceSite->site_name ?? 'Matina',
        'to' => $b->destinationSite->site_name ?? 'Jacinto',
        'oid' => $b->branch_order_id ? (int) $b->branch_order_id : null,
        'void' => !empty($b->voided_at),
        'why' => $b->void_reason,
        'items' => $b->items->map(fn($i) => [
            'n' => $pnames[$i->product_id] ?? '?',
            'q' => (int) $i->qty_sent,
            'ret' => (int) $i->qty_returned,
            'pr' => (float) $i->unit_price,
            'amt' => round(((int) $i->qty_sent - (int) $i->qty_returned) * (float) $i->unit_price, 2),
        ])->values(),
    ])->values();
@endphp

<script>
const PRODUCTS = {!! json_encode($sendProducts, $jf) !!};
const SITES = {!! json_encode($siteData, $jf) !!};
const OPEN = {!! json_encode($openOrders, $jf) !!};
const SLIPS = {!! json_encode($slips, $jf) !!};
const BATCHES = {!! json_encode($batchData, $jf) !!};
const ARCHIVABLE = {!! json_encode($archivable, $jf) !!};
const URL_ARCHIVE = "{{ route('branch-transfers.archive') }}", URL_ARCHIVE_PERIOD = "{{ route('branch-transfers.archive-period') }}";
const URL_SHOW = "{{ route('branch-transfers.show', ['batch' => '__ID__']) }}";
const OLD = {!! json_encode(old(), $jf) !!};
const URL_SEND = "{{ route('branch-transfers.store') }}";
const URL_ORDER = "{{ route('branch-orders.store') }}";
const CSRF = "{{ csrf_token() }}";
const TODAY = "{{ date('Y-m-d') }}", TODAY_LBL = "{{ date('M d, Y') }}", TMR = "{{ date('Y-m-d', strtotime('+1 day')) }}";
const PRINT_ICON = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>';

const $ = s => document.querySelector(s);
const esc = s => String(s == null ? '' : s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const peso = n => '\u20B1' + Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
const pb = id => PRODUCTS.find(p => p.id == id);
let S = null, N = null;

let PT = '';
function modal(t, h, pt){
    $('#bmt').textContent = t; $('#bmb').innerHTML = h; $('#bov').classList.add('on');
    PT = pt || ''; const b = $('#bpr'); b.style.display = PT ? '' : 'none'; b.innerHTML = PRINT_ICON + ' Print / Save PDF';
    setTimeout(function(){ const e = $('#bmb select, #bmb input'); if(e) e.focus(); }, 30);
}
function closeM(){ $('#bov').classList.remove('on'); }
$('#bov').addEventListener('mousedown', function(e){ if(e.target.id === 'bov') closeM(); });
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeM(); });
const er = m => { const e = $('#err'); e.textContent = m; e.style.display = 'block'; };
const siteOpts = k => SITES.map(s => '<option value="' + s.id + '"' + (s.name.toLowerCase().indexOf(k) > -1 ? ' selected' : '') + '>' + esc(s.name) + '</option>').join('');
const foot = b => '<div class="bmf"><button type="button" class="btn btn-outline" onclick="closeM()">Cancel</button>' + b + '</div>';

/* ---------- Send Batch ---------- */
const so = () => OPEN.find(o => o.id == S.o);
const avail = () => so().items.filter(x => x.bl > 0 && pb(x.pid));
function fill(){
    S.rows = S.o ? avail().map(function(x){ const p = pb(x.pid); return {pid: String(x.pid), qty: Math.max(1, Math.min(x.bl, p.stock)), pr: p.price}; }) : [{pid:'', qty:1, pr:''}];
}
function openSend(oid, old){
    S = {o: oid ? String(oid) : '', rows: [], warn: false};
    if(S.o && !so()) S.o = '';
    if(old && old.length) S.rows = old; else fill();
    const oo = OPEN.map(o => '<option value="' + o.id + '"' + (S.o == o.id ? ' selected' : '') + '>#' + o.id + ' \u00b7 needed ' + esc(o.need) + ' \u00b7 ordered ' + o.ord + ', balance ' + o.bal + '</option>').join('');
    modal('Send to Branch',
        '<form id="bf" method="POST" action="' + URL_SEND + '"><input type="hidden" name="_token" value="' + CSRF + '">'
        + '<div class="bg2"><div><label class="f">From *</label><select class="in" name="source_site_id">' + siteOpts('matina') + '</select></div>'
        + '<div><label class="f">To *</label><select class="in" name="destination_site_id">' + siteOpts('jacinto') + '</select></div></div>'
        + '<div class="bg2"><div><label class="f">Send date *</label><input class="in" type="date" name="batch_date" value="' + esc(OLD.batch_date || TODAY) + '" required></div>'
        + '<div><label class="f">Fulfill order</label><select class="in" name="branch_order_id" onchange="oc(this.value)"><option value="">No order (manual)</option>' + oo + '</select></div></div>'
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><label class="f" style="margin:0">Products sent</label><button type="button" class="addrow" onclick="addR()">+ Add product</button></div>'
        + '<div class="bcap"><span>Product</span><span>Qty</span><span>Unit price</span></div><div id="rws"></div>'
        + '<p class="muted" style="font-size:12px;margin:6px 0 0">Final sale: counted as revenue right away. If you send less than the balance, the shortage stays on the order for the next batch.</p>'
        + '<div class="bwn" id="wn"></div><div class="ber" id="err"></div>'
        + foot('<button type="button" class="btn btn-gold" id="sb" onclick="goSend()">Send batch</button>') + '</form>');
    drawRows();
}
function opts(sel){
    const l = S.o ? avail().map(x => [x.pid, pb(x.pid).name + ' \u2014 ordered ' + x.q + ', balance ' + x.bl + ' (' + pb(x.pid).stock + ' on hand)']) : PRODUCTS.map(p => [p.id, p.name + ' (' + p.stock + ' on hand)']);
    return '<option value="">Select a product</option>' + l.map(a => '<option value="' + a[0] + '"' + (sel == a[0] ? ' selected' : '') + '>' + esc(a[1]) + '</option>').join('');
}
function drawRows(){
    $('#rws').innerHTML = S.rows.map((r, i) =>
        '<div class="brw"><div style="flex:1;min-width:0"><select class="in" name="items[' + i + '][product_id]" onchange="pk(' + i + ',this.value)" aria-label="Product">' + opts(r.pid) + '</select><div class="bh" id="h' + i + '"></div></div>'
        + '<input class="in q" type="number" min="1" name="items[' + i + '][qty_sent]" value="' + esc(r.qty) + '" oninput="qy(' + i + ',this.value)" aria-label="Quantity">'
        + '<input class="in p" type="number" step="0.01" min="0" name="items[' + i + '][unit_price]" value="' + esc(r.pr) + '" oninput="S.rows[' + i + '].pr=this.value" aria-label="Unit price">'
        + '<button type="button" class="bx" onclick="rmR(' + i + ')" aria-label="Remove row">&times;</button></div>').join('');
    S.rows.forEach(function(_, i){ hint(i); });
}
function hint(i){
    const r = S.rows[i], el = $('#h' + i); if(!el) return;
    if(!r.pid){ el.textContent = ''; return; }
    const p = pb(r.pid); if(!p){ el.textContent = ''; return; }
    const q = +r.qty || 0; let m = 'On hand: ' + p.stock, c = 'bh';
    if(q > p.stock){ m = 'Only ' + p.stock + ' on hand'; c = 'bh lo'; }
    else if(S.o){
        const it = so().items.find(x => x.pid == r.pid);
        if(it){
            if(q > it.bl){ m += ' \u00b7 Over the order by ' + (q - it.bl); c = 'bh lo'; }
            else if(q < it.bl){ m += ' \u00b7 Short by ' + (it.bl - q) + ', to be delivered in the next batch'; }
            else { m += ' \u00b7 Matches the order'; }
        }
    }
    el.textContent = m; el.className = c;
}
function resetWarn(){ S.warn = false; $('#wn').style.display = 'none'; $('#err').style.display = 'none'; $('#sb').textContent = 'Send batch'; }
function oc(v){ S.o = v || ''; if(S.o && !so()) S.o = ''; fill(); drawRows(); resetWarn(); }
function pk(i, v){
    const r = S.rows[i]; r.pid = v;
    if(v){ const p = pb(v); r.pr = p.price; const it = S.o ? so().items.find(x => x.pid == v) : null; r.qty = Math.max(1, Math.min(it ? it.bl : 1, p.stock)); }
    drawRows(); resetWarn();
}
function qy(i, v){ S.rows[i].qty = v; hint(i); resetWarn(); }
function addR(){ S.rows.push({pid:'', qty:1, pr:''}); drawRows(); }
function rmR(i){ S.rows.splice(i, 1); drawRows(); resetWarn(); }
function goSend(){
    $('#err').style.display = 'none';
    const rs = S.rows.filter(r => r.pid);
    if(!rs.length) return er('Add at least one product.');
    for(const r of rs){
        const p = pb(r.pid), q = +r.qty;
        if(!(q >= 1)) return er('Quantity must be at least 1.');
        if(q > p.stock) return er('Only ' + p.stock + ' ' + p.name + ' on hand. Lower the quantity.');
    }
    const sh = [];
    if(S.o) avail().forEach(function(x){
        const s = rs.filter(r => r.pid == x.pid).reduce((a, r) => a + (+r.qty), 0);
        if(s < x.bl) sh.push(pb(x.pid).name + ' short ' + (x.bl - s));
    });
    if(sh.length && !S.warn){
        S.warn = true; const w = $('#wn');
        w.innerHTML = '<b>Short: ' + esc(sh.join(', ')) + '.</b> You can still send. The shortage is noted and delivered in the next batch.';
        w.style.display = 'block'; $('#sb').textContent = 'Send anyway'; return;
    }
    S.rows = rs; drawRows(); $('#bf').submit();
}

/* ---------- New Order ---------- */
function openNew(old){
    N = {rows: (old && old.length) ? old : [{pid:'', qty:1}]};
    modal('New order from Jacinto',
        '<form id="bf" method="POST" action="' + URL_ORDER + '"><input type="hidden" name="_token" value="' + CSRF + '">'
        + '<div class="bg2"><div><label class="f">Branch *</label><select class="in" name="destination_site_id">' + siteOpts('jacinto') + '</select></div>'
        + '<div><label class="f">Needed by *</label><input class="in" type="date" name="needed_by" value="' + esc(OLD.needed_by || TMR) + '" required></div></div>'
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><label class="f" style="margin:0">Products</label><button type="button" class="addrow" onclick="nAdd()">+ Add product</button></div>'
        + '<div id="nrw"></div><label class="f" style="margin-top:6px">Notes</label><input class="in" type="text" name="notes" maxlength="500" value="' + esc(OLD.notes || '') + '" placeholder="e.g. ordered via Messenger">'
        + '<div class="ber" id="err"></div>'
        + foot('<button type="button" class="btn btn-gold" onclick="nSave()">Save order</button>') + '</form>');
    nDraw();
}
function nDraw(){
    $('#nrw').innerHTML = N.rows.map((r, i) =>
        '<div class="brw"><div style="flex:1;min-width:0"><select class="in" name="items[' + i + '][product_id]" onchange="N.rows[' + i + '].pid=this.value" aria-label="Product"><option value="">Select a product</option>'
        + PRODUCTS.map(p => '<option value="' + p.id + '"' + (r.pid == p.id ? ' selected' : '') + '>' + esc(p.name) + '</option>').join('') + '</select></div>'
        + '<input class="in q" type="number" min="1" name="items[' + i + '][qty]" value="' + esc(r.qty) + '" oninput="N.rows[' + i + '].qty=this.value" aria-label="Quantity">'
        + '<button type="button" class="bx" onclick="N.rows.splice(' + i + ',1);nDraw()" aria-label="Remove row">&times;</button></div>').join('');
}
function nAdd(){ N.rows.push({pid:'', qty:1}); nDraw(); }
function nSave(){
    const rs = N.rows.filter(r => r.pid && +r.qty >= 1);
    if(!rs.length) return er('Add at least one product with a quantity.');
    N.rows = rs; nDraw(); $('#bf').submit();
}

/* ---------- View / Print / Save PDF ---------- */
/* Print only the slip: copy it with the page styles into a hidden frame, so the sidebar and modal never show on paper */
function doPrint(){
    const slip = $('#slipArea'); if(!slip) return;
    const old = $('#bprf'); if(old) old.remove();
    const f = document.createElement('iframe'); f.id = 'bprf';
    f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
    document.body.appendChild(f);
    const css = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(e => e.outerHTML).join('');
    const d = f.contentWindow.document;
    d.open();
    d.write('<!doctype html><html><head><meta charset="utf-8"><title>' + esc(PT || document.title) + '</title>' + css
        + '<style>html,body{background:#fff!important;display:block!important;min-height:0!important;margin:0;padding:0;}@page{margin:14mm;}#slipArea{position:static!important;}</style></head><body>' + slip.outerHTML + '</body></html>');
    d.close();
    const go = function(){ const t = document.title; document.title = PT || t; f.contentWindow.focus(); f.contentWindow.print(); setTimeout(function(){ document.title = t; }, 800); };
    if(d.fonts && d.fonts.ready) d.fonts.ready.then(function(){ setTimeout(go, 50); }); else setTimeout(go, 300);
}
const sign = '<div class="bsg"><span>Prepared by</span><span>Received by (Jacinto)</span></div>';

/* Box listing what the order still owes the branch */
function notYetSent(o){
    if(!o || o.st !== 'Pending' || o.bal <= 0) return '';
    return '<div class="nys"><b class="h">NOT YET SENT: ' + o.bal + ' pcs (order #' + o.id + ')</b>'
        + o.items.filter(i => i.bl > 0).map(i => esc(i.n) + ' \u00d7 ' + i.bl).join(', ')
        + '<div style="font-size:12px;margin-top:4px">To be delivered in the next batch.</div></div>';
}

/* Deliveries of one order, oldest first (voided ones included, marked) */
const orderBatches = oid => BATCHES.filter(b => b.oid == oid).sort((a, b) => a.raw < b.raw ? -1 : a.raw > b.raw ? 1 : a.id - b.id);

function orderHtml(o){
    let h = '<div id="slipArea" class="slip"><div class="shop">Trove</div>'
        + '<h4>Order #' + o.id + ' \u00b7 Jacinto Branch</h4>'
        + '<div class="muted">From Matina Commissary \u00b7 printed ' + TODAY_LBL + '</div>'
        + '<div class="mt"><span>Needed by: <b>' + esc(o.need) + '</b></span><span>Status: <b>' + esc(o.st) + '</b></span></div>'
        + '<table><thead><tr><th class="l">Product</th><th>Ordered</th><th>Sent</th><th>Not yet sent</th></tr></thead><tbody>'
        + o.items.map(i => '<tr><td class="l">' + esc(i.n) + '</td><td>' + i.q + '</td><td>' + i.d + '</td><td><b>' + i.bl + '</b></td></tr>').join('')
        + '<tr class="tot"><td class="l">Total</td><td>' + o.ord + '</td><td>' + o.sent + '</td><td>' + o.bal + '</td></tr></tbody></table>';
    if(o.closed > 0) h += '<p><b>Closed short ' + o.closed + ' pcs</b>' + (o.why ? ': ' + esc(o.why) : '') + '</p>';
    if(o.note) h += '<p class="muted">Note: ' + esc(o.note) + '</p>';
    if(o.dl.length){
        h += '<h4 style="font-size:14px;margin:16px 0 0">Deliveries</h4><table><thead><tr><th class="l">Date</th><th>Pcs</th><th>Amount</th></tr></thead><tbody>'
            + o.dl.map(d => '<tr><td class="l">' + esc(d.d) + '</td><td>' + d.pcs + '</td><td>' + peso(d.amt) + '</td></tr>').join('')
            + '<tr class="tot"><td class="l">Total sent value</td><td>' + o.sent + '</td><td>' + peso(o.rev) + '</td></tr></tbody></table>';
    }
    return h + notYetSent(o) + sign + '</div>';
}

function batchHtml(b, o, n){
    const hasRet = b.items.some(i => i.ret > 0);
    const pcs = b.items.reduce((a, i) => a + i.q, 0), ret = b.items.reduce((a, i) => a + i.ret, 0);
    const tot = b.items.reduce((a, i) => a + i.amt, 0);
    let h = '<div id="slipArea" class="slip"><div class="shop">Trove</div>'
        + '<h4>Delivery receipt \u00b7 ' + esc(b.d) + '</h4>'
        + '<div class="muted">' + esc(b.from) + ' \u2192 ' + esc(b.to) + ' \u00b7 batch #' + b.id
        + (o ? ' \u00b7 order #' + o.id + (n ? ', delivery ' + n : '') : '') + '</div>';
    if(b.void) h += '<div class="vdb"><b>VOIDED</b>' + (b.why ? ': ' + esc(b.why) : '') + '. Not counted as a sale.</div>';
    h += '<table><thead><tr><th class="l">Product</th><th>Pcs</th>' + (hasRet ? '<th>Returned</th>' : '') + '<th>Unit price</th><th>Amount</th></tr></thead><tbody>'
        + b.items.map(i => '<tr><td class="l">' + esc(i.n) + '</td><td>' + i.q + '</td>' + (hasRet ? '<td>' + i.ret + '</td>' : '') + '<td>' + peso(i.pr) + '</td><td>' + peso(i.amt) + '</td></tr>').join('')
        + '<tr class="tot"><td class="l">Total</td><td>' + pcs + '</td>' + (hasRet ? '<td>' + ret + '</td>' : '') + '<td></td><td>' + peso(tot) + '</td></tr></tbody></table>';
    if(hasRet) h += '<p class="muted" style="font-size:12px">Older batch: returns were logged before the final-sale rule. Amount = (pcs \u2212 returned) \u00d7 unit price.</p>';
    return h + (b.void ? '' : notYetSent(o)) + sign + '</div>';
}

/* key: 'o' for the whole order, or a batch id */
function showSlip(oid, key){
    const o = oid ? SLIPS.find(x => x.id == oid) : null;
    let h = '', title, pt, b = null;
    if(o){
        const bs = orderBatches(o.id); let n = 0;
        h += '<div class="btabs"><button type="button" class="btab' + (key === 'o' ? ' on' : '') + '" onclick="showSlip(' + o.id + ',\'o\')">Whole order</button>'
            + bs.map(x => { const lbl = x.void ? 'Voided ' + esc(x.d) : 'Delivery ' + (++n); return '<button type="button" class="btab' + (key == x.id ? ' on' : '') + '" onclick="showSlip(' + o.id + ',' + x.id + ')">' + lbl + '</button>'; }).join('')
            + '</div>';
    }
    if(key === 'o'){
        h += orderHtml(o); title = 'Order #' + o.id; pt = 'Order-' + o.id;
    } else {
        b = BATCHES.find(x => x.id == key); if(!b) return;
        const n = o ? orderBatches(o.id).filter(x => !x.void).findIndex(x => x.id == b.id) + 1 : 0;
        h += batchHtml(b, o, n); title = 'Delivery \u00b7 ' + b.d; pt = 'Delivery-' + b.raw + '-' + b.id;
        if(!b.void) h += '<a class="bvl" href="' + URL_SHOW.replace('__ID__', b.id) + '">Wrong qty or price? Open full page to void \u2192</a>';
    }
    modal(title, h, pt);
}
function viewBatch(id){
    const b = BATCHES.find(x => x.id == id); if(!b) return false;
    showSlip(b.oid && SLIPS.find(x => x.id == b.oid) ? b.oid : null, b.id); return true;
}
/* Plain click opens the modal; Ctrl/Cmd/Shift+click keeps the normal link (full page / new tab) */
function vb(e, id){
    if(e.ctrlKey || e.metaKey || e.shiftKey || e.button) return true;
    return !viewBatch(id);
}

/* ---------- Row menu (the "..." button) ---------- */
function placeMenu(d){
    const m = d.querySelector('.kmenu'), r = d.querySelector('summary').getBoundingClientRect();
    m.style.right = (window.innerWidth - r.right) + 'px';
    m.style.top = (r.bottom + 4) + 'px';
    const h = m.offsetHeight;
    if (r.bottom + 4 + h > window.innerHeight - 8) m.style.top = Math.max(8, r.top - h - 4) + 'px';
}
function closeMenus(except){ document.querySelectorAll('details.km[open]').forEach(function(d){ if(d !== except) d.removeAttribute('open'); }); }
document.querySelectorAll('details.km').forEach(function(d){
    d.addEventListener('toggle', function(){ if(d.open){ closeMenus(d); placeMenu(d); } });
    d.querySelectorAll('.kmenu button[type="button"]').forEach(function(b){ b.addEventListener('click', function(){ d.removeAttribute('open'); }); });
});
document.addEventListener('click', function(e){ if(!e.target.closest('details.km')) closeMenus(); });
window.addEventListener('scroll', function(){ closeMenus(); }, true);
window.addEventListener('resize', function(){ closeMenus(); });
document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeMenus(); });

/* ---------- Archive ---------- */
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const MS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const monthLabel = k => MONTHS[+k.slice(5,7) - 1] + ' ' + k.slice(0,4);
const isoDate = d => d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
function weekKey(raw){ const d = new Date(raw + 'T00:00:00'); d.setDate(d.getDate() - (d.getDay() + 6) % 7); return isoDate(d); }
function weekLabel(k){ const s = new Date(k + 'T00:00:00'), e = new Date(s); e.setDate(e.getDate() + 6);
    return MS[s.getMonth()] + ' ' + s.getDate() + ' \u2013 ' + (s.getMonth() !== e.getMonth() ? MS[e.getMonth()] + ' ' : '') + e.getDate() + ', ' + e.getFullYear(); }

function askArchive(type, id, what, month){
    modal('Archive ' + what + '?',
        '<form method="POST" action="' + URL_ARCHIVE + '"><input type="hidden" name="_token" value="' + CSRF + '">'
        + '<input type="hidden" name="type" value="' + type + '"><input type="hidden" name="id" value="' + id + '">'
        + '<p style="margin:0">It goes into the <b>' + esc(month) + '</b> folder in the Archived tab. Nothing is deleted, the sale still counts in your reports, and you can restore it any time.</p>'
        + foot('<button class="btn btn-gold">Archive</button>') + '</form>');
}

let BULK = {mode: 'month', key: ''};
function openBulk(){ BULK = {mode: 'month', key: ''}; drawBulk(); }
function bulkGroups(){
    const g = {};
    ARCHIVABLE.forEach(function(r){ const k = BULK.mode === 'month' ? r.raw.slice(0,7) : weekKey(r.raw); (g[k] = g[k] || []).push(r); });
    return g;
}
function drawBulk(){
    const g = bulkGroups(), keys = Object.keys(g).sort().reverse();
    if (!g[BULK.key]) BULK.key = keys[0] || '';
    const sel = g[BULK.key] || [], lbl = k => BULK.mode === 'month' ? monthLabel(k) : weekLabel(k);
    modal('Archive a period',
        '<form method="POST" action="' + URL_ARCHIVE_PERIOD + '"><input type="hidden" name="_token" value="' + CSRF + '">'
        + '<input type="hidden" name="mode" value="' + BULK.mode + '"><input type="hidden" name="key" value="' + BULK.key + '">'
        + '<div class="seg2"><button type="button" class="' + (BULK.mode === 'month' ? 'on' : '') + '" onclick="BULK.mode=\'month\';BULK.key=\'\';drawBulk()">By month</button>'
        + '<button type="button" class="' + (BULK.mode === 'week' ? 'on' : '') + '" onclick="BULK.mode=\'week\';BULK.key=\'\';drawBulk()">By week</button></div>'
        + (keys.length
            ? '<label class="f" for="bulkSel">' + (BULK.mode === 'month' ? 'Month' : 'Week (Mon to Sun)') + '</label><select class="in" id="bulkSel" onchange="BULK.key=this.value;drawBulk()">'
              + keys.map(k => '<option value="' + k + '"' + (k === BULK.key ? ' selected' : '') + '>' + lbl(k) + ' (' + g[k].length + ')</option>').join('') + '</select>'
              + '<div class="prev"><b>' + sel.length + '</b> item' + (sel.length === 1 ? '' : 's') + ' \u00b7 <b>' + peso(sel.reduce((a, r) => a + r.rev, 0)) + '</b> will move to the Archived tab, each filed under the month of its own date. Pending orders stay active.</div>'
            : '<div class="prev">Nothing to archive. Only sent, closed or cancelled orders and deliveries can be archived.</div>')
        + foot('<button class="btn btn-gold"' + (sel.length ? '' : ' disabled') + '>Archive ' + sel.length + '</button>') + '</form>');
}

/* ---------- Reopen the right modal after a server validation error ---------- */
(function(){
    if(!OLD || !OLD.items) return;
    const rows = Object.values(OLD.items); if(!rows.length) return;
    if('qty_sent' in rows[0]) openSend(OLD.branch_order_id || '', rows.map(r => ({pid: String(r.product_id || ''), qty: r.qty_sent, pr: r.unit_price})));
    else if('qty' in rows[0]) openNew(rows.map(r => ({pid: String(r.product_id || ''), qty: r.qty})));
})();
</script>
</x-app-layout>
