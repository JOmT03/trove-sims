<x-app-layout>
<x-slot name="header">Dashboard</x-slot>
<x-slot name="subheader">Batch &amp; stock monitoring overview</x-slot>

<style>
.dash{--good:#15803D;--good-bg:#E7F3EA;--amber:#B45309;--amber-bg:#FBEBD6;--crit:#C2410C;--crit-bg:#FBE4DA;--info:#3F5B8B;--info-bg:#E6ECF5;--purple:#6D4C9F;--purple-bg:#EDE6F5;--bd:#EDE0D0;--mut:#8A7460;}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.kpi{background:#fff;border:1px solid var(--bd);border-radius:14px;padding:16px;box-shadow:0 1px 6px rgba(0,0,0,.06);position:relative;overflow:hidden;}
.kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;}
.kpi.a::before{background:var(--info);} .kpi.b::before{background:var(--good);} .kpi.c::before{background:var(--amber);} .kpi.d::before{background:var(--purple);}
.kpi .l{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--mut);}
.kpi .v{font-size:28px;font-weight:800;line-height:1;margin-top:8px;letter-spacing:-1px;font-variant-numeric:tabular-nums;}
.kpi .s{font-size:12px;color:var(--mut);margin-top:6px;}
a.kpi{text-decoration:none;color:inherit;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease;}
a.kpi:hover{transform:translateY(-3px);box-shadow:0 4px 8px rgba(0,0,0,.07),0 14px 30px rgba(0,0,0,.11);border-color:#8A7460;}
a.kpi:focus-visible{outline:2px solid var(--info);outline-offset:2px;}
.kpi-go{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:var(--mut);margin-top:8px;opacity:.5;transition:opacity .15s ease,color .15s ease;}
a.kpi:hover .kpi-go{opacity:1;color:#2E1C10;}
.grid{display:grid;grid-template-columns:1.5fr 1fr;gap:16px;align-items:start;}
.col{display:flex;flex-direction:column;gap:16px;}
.card{background:#fff;border:1px solid var(--bd);border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.06);overflow:hidden;}
.card.star{border-color:#E8C39A;}
.hd{display:flex;justify-content:space-between;align-items:center;padding:14px 18px 2px;}
.hd h3{font-size:14.5px;font-weight:800;margin:0;color:#2E1C10;}
.hd .m{font-size:12px;color:var(--mut);}
.sum{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--bd);margin:12px 18px 2px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);}
.sum div{background:#fff;padding:12px;text-align:center;}
.sum .sl{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--mut);font-weight:700;}
.sum .sn{font-weight:800;font-size:17px;margin-top:3px;font-variant-numeric:tabular-nums;}
.sum .sn.disp{color:var(--info);} .sum .sn.net{color:var(--good);} .sum .sn.loss{color:var(--crit);}
table{width:100%;border-collapse:collapse;font-size:12.5px;}
th{text-align:right;font-size:10px;text-transform:uppercase;color:var(--mut);padding:8px 14px;background:#FDF6EC;}
th:first-child{text-align:left;}
td{padding:9px 14px;border-top:1px solid var(--bd);text-align:right;font-variant-numeric:tabular-nums;}
td:first-child{text-align:left;font-weight:500;}
tr.tot td{font-weight:800;background:#FDF6EC;}
.ret{color:var(--crit);font-weight:600;}
.lvl{padding:10px 18px;border-top:1px solid var(--bd);display:flex;align-items:center;gap:12px;}
.lvl:first-of-type{border-top:none;}
.lvl-n{width:140px;flex-shrink:0;font-size:12.5px;font-weight:500;}
.lvl-n small{display:block;color:var(--mut);font-weight:400;font-size:10.5px;}
.lvl-t{flex:1;height:8px;border-radius:5px;background:#FDF6EC;overflow:hidden;min-width:0;}
.lvl-f{height:100%;border-radius:5px;}
.f-red{background:var(--crit);} .f-amber{background:var(--amber);} .f-green{background:var(--good);}
.lvl-q{width:70px;text-align:right;flex-shrink:0;font-size:11.5px;font-variant-numeric:tabular-nums;white-space:nowrap;}
.mv{display:flex;align-items:center;gap:11px;padding:10px 18px;border-top:1px solid var(--bd);}
.mv:first-of-type{border-top:none;}
.mv-ic{width:28px;height:28px;border-radius:8px;display:grid;place-items:center;flex-shrink:0;font-size:13px;}
.mv-ic.used{background:var(--amber-bg);color:var(--amber);} .mv-ic.recv{background:var(--good-bg);color:var(--good);} .mv-ic.adj{background:var(--info-bg);color:var(--info);}
.mv-b{flex:1;min-width:0;} .mv-b .t{font-size:12.5px;font-weight:500;} .mv-b .s{font-size:11px;color:var(--mut);}
.mv-t{font-size:11px;color:var(--mut);white-space:nowrap;flex-shrink:0;}
.pr{display:flex;align-items:center;gap:10px;padding:9px 18px;border-top:1px solid var(--bd);}
.pr:first-of-type{border-top:none;}
.pr .n{flex:1;min-width:0;font-size:12.5px;font-weight:500;}
.pr .bar{width:34%;height:7px;border-radius:4px;background:#FDF6EC;overflow:hidden;flex-shrink:0;}
.pr .bar i{display:block;height:100%;background:#D9782C;border-radius:4px;}
.pr .c{font-weight:800;font-size:15px;width:52px;text-align:right;font-variant-numeric:tabular-nums;}
.lead{padding:14px 18px 4px;font-size:12.5px;color:var(--mut);}
.empty{padding:36px 18px;text-align:center;color:var(--mut);font-size:13px;}
@media(max-width:900px){.kpis{grid-template-columns:1fr 1fr;}}
@media(max-width:820px){.grid{grid-template-columns:1fr;}}
</style>

<div class="dash">
<section class="kpis">
    <a href="{{ Route::has('branch-transfers.index') ? route('branch-transfers.index') : '#' }}" class="kpi a"><div class="l">Batches Sent</div><div class="v">{{ $totalBatches }} <span style="font-size:14px;color:#8A7460;">{{ $totalBatches == 1 ? 'batch' : 'batches' }}</span></div><div class="s">dispatched to Jacinto</div><span class="kpi-go">View Branch Transfers &rarr;</span></a>
    <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="kpi b"><div class="l">Net Sold (This Week)</div><div class="v">{{ $netSoldQty }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">&#8369;{{ number_format($netSoldRevenue,2) }} &middot; {{ $returnedQty }} returned</div><span class="kpi-go">View Reports &rarr;</span></a>
    <a href="{{ route('inventory.index') }}" class="kpi c"><div class="l">Low Ingredients</div><div class="v">{{ $lowCount }}</div><div class="s">need restock at Matina</div><span class="kpi-go">View Inventory &rarr;</span></a>
    <a href="{{ route('products.index') }}" class="kpi d"><div class="l">Finished Stock</div><div class="v">{{ $finishedStock }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><div class="s">across {{ $totalProducts }} products</div><span class="kpi-go">View Products &rarr;</span></a>
</section>

<div class="grid">
    <div class="col">
        <section class="card star">
            <div class="hd"><h3>Latest Batch &mdash; Reconciliation</h3><span class="m">{{ $latestBatch ? $latestBatch->batch_date->format('M d, Y') : '' }}</span></div>
            @if($latestBatch && $latestBatch->items->count())
            <div class="sum">
                <div><div class="sl">Dispatched</div><div class="sn disp">&#8369;{{ number_format($latestBatch->dispatchedValue(),2) }}</div></div>
                <div><div class="sl">Net Sold</div><div class="sn net">&#8369;{{ number_format($latestBatch->totalRevenue(),2) }}</div></div>
                <div><div class="sl">Returned (loss)</div><div class="sn loss">&#8369;{{ number_format($latestBatch->lossValue(),2) }}</div></div>
            </div>
            <table style="margin-top:8px;">
                <thead><tr><th>Flavor</th><th>Sent</th><th>Ret</th><th>Net Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                    @foreach($latestBatch->items as $it)
                    <tr><td>{{ $it->product->product_name ?? '-' }}</td><td>{{ $it->qty_sent }}</td><td class="ret">{{ $it->qty_returned }}</td><td style="font-weight:700;">{{ $it->netSold() }}</td><td>&#8369;{{ number_format($it->revenue(),2) }}</td></tr>
                    @endforeach
                    <tr class="tot"><td>Total</td><td>{{ $latestBatch->totalSent() }}</td><td>{{ $latestBatch->totalReturned() }}</td><td>{{ $latestBatch->totalNetSold() }}</td><td>&#8369;{{ number_format($latestBatch->totalRevenue(),2) }}</td></tr>
                </tbody>
            </table>
            <div style="padding:12px 18px;"><a href="{{ route('branch-transfers.index') }}" style="font-size:12.5px;color:#4A2C17;font-weight:700;text-decoration:none;">View all batches &rarr;</a></div>
            @else
            <div class="empty">No batches yet. <a href="{{ route('branch-transfers.create') }}" style="color:#D9782C;font-weight:700;">Create your first batch dispatch</a>.</div>
            @endif
        </section>

        <section class="card">
            <div class="hd"><h3>Recent Stock Movements</h3><span class="m">ingredient activity</span></div>
            @if($movements->count())
                @foreach($movements as $mv)
                <div class="mv">
                    @php $t = strtolower($mv->type); @endphp
                    <span class="mv-ic {{ $t==='received' ? 'recv' : ($t==='used' ? 'used' : 'adj') }}">{{ $t==='received' ? '+' : ($t==='used' ? '-' : '~') }}</span>
                    <div class="mv-b"><div class="t">{{ ucfirst($mv->type) }} {{ rtrim(rtrim(number_format($mv->quantity,2),'0'),'.') }} {{ $mv->inventory->unit ?? '' }} &middot; {{ $mv->inventory->item_name ?? '' }}</div><div class="s">{{ $mv->notes }}</div></div>
                    <span class="mv-t">{{ $mv->created_at->diffForHumans(null, true) }} ago</span>
                </div>
                @endforeach
            @else
                <div class="empty">No stock movements logged yet. They appear when you create products (auto-deduction) or adjust inventory.</div>
            @endif
        </section>
    </div>

    <div class="col">
        <section class="card">
            <div class="hd"><h3>Ingredient Stock Levels</h3><span class="m">lowest first</span></div>
            @forelse($stockLevels as $inv)
                @php
                    $usable = $inv->usableQuantity();
                    $min = $inv->minimum_stock > 0 ? $inv->minimum_stock : 1;
                    $pct = max(4, min(100, ($usable / ($min * 2)) * 100));
                    $cls = $usable <= 0 ? 'f-red' : ($usable <= $inv->minimum_stock ? 'f-amber' : 'f-green');
                @endphp
                <div class="lvl">
                    <span class="lvl-n">{{ $inv->item_name }} <small>min {{ rtrim(rtrim(number_format($inv->minimum_stock,2),'0'),'.') }} {{ $inv->unit }}</small></span>
                    <span class="lvl-t"><span class="lvl-f {{ $cls }}" style="width:{{ $pct }}%"></span></span>
                    <span class="lvl-q">{{ rtrim(rtrim(number_format($usable,2),'0'),'.') }} {{ $inv->unit }}</span>
                </div>
            @empty
                <div class="empty">No inventory items yet.</div>
            @endforelse
        </section>

        <section class="card">
            <div class="hd"><h3>Can Still Bake</h3><span class="m">from current stock</span></div>
            @if(count($canBake))
            <div class="lead">Based on your recipes and current ingredient stock, you can still make:</div>
            @foreach($canBake as $name => $qty)
                <div class="pr">
                    <span class="n">{{ $name }}</span>
                    <span class="bar"><i style="width:{{ $maxBake > 0 ? max(6, ($qty / $maxBake) * 100) : 0 }}%"></i></span>
                    <span class="c">~{{ $qty }}</span>
                </div>
            @endforeach
            @else
                <div class="empty">Add recipes to your products to see bake estimates.</div>
            @endif
        </section>
    </div>
</div>
</div>
</x-app-layout>