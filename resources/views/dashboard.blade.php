<x-app-layout>
<x-slot name="header">Dashboard</x-slot>
<x-slot name="subheader">Batch &amp; stock monitoring overview</x-slot>

<style>
.dash{--good:#15803D;--good-bg:#E7F3EA;--amber:#B45309;--amber-bg:#FBEBD6;--crit:#C2410C;--crit-bg:#FBE4DA;--info:#3F5B8B;--info-bg:#E6ECF5;--purple:#6D4C9F;--purple-bg:#EDE6F5;--bd:#EDE0D0;--mut:#8A7460;}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.kpi{background:#fff;border:1px solid var(--bd);border-radius:14px;padding:16px;box-shadow:0 1px 6px rgba(0,0,0,.06);position:relative;overflow:hidden;}
.kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;}
.kpi.a::before{background:#2a78d6;} .kpi.b::before{background:var(--good);} .kpi.c::before{background:var(--amber);} .kpi.d::before{background:var(--purple);}
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
.sum{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1px;background:var(--bd);margin:12px 18px 2px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);}
.sum div{background:#fff;padding:12px 6px;text-align:center;min-width:0;}
.sum .sl{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--mut);font-weight:700;}
.sum .sn{font-weight:800;font-size:15px;white-space:nowrap;margin-top:3px;font-variant-numeric:tabular-nums;}
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
.lvl-f{display:block;height:100%;border-radius:5px;}
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
.dash{--s1:#2a78d6;--s2:#eb6834;--s3:#1baf7a;--bar:#C8732F;--grid:#F0E6D8;}
.mbar{display:flex;justify-content:flex-end;margin:-6px 0 14px;}
.month{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--bd);border-radius:999px;padding:4px;}
.month a,.month span.off{width:30px;height:30px;border-radius:999px;display:grid;place-items:center;color:#5B4636;text-decoration:none;font-size:16px;}
.month a:hover{background:#FDF6EC;} .month span.off{color:#D8CBBB;}
.month b{min-width:124px;text-align:center;font-size:13px;font-weight:800;color:#2E1C10;}
.row{display:grid;gap:16px;margin-bottom:16px;}
.r21{grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);align-items:stretch;}
.r11{grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:start;}
.legend{display:flex;gap:14px;flex-wrap:wrap;padding:6px 18px 0;font-size:12px;color:#5B4636;}
.legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px;vertical-align:-1px;}
.sub{padding:2px 18px 4px;color:var(--mut);font-size:12px;}
.chart{position:relative;padding:6px 12px 12px;}
.chart svg{display:block;width:100%;height:auto;overflow:visible;}
.chart .ax{fill:#8A7460;font-size:10.5px;}
.chart .gl{stroke:var(--grid);stroke-width:1;}
.tip{position:absolute;pointer-events:none;background:#2E1C10;color:#fff;border-radius:8px;padding:7px 10px;font-size:12px;line-height:1.45;white-space:nowrap;box-shadow:0 6px 18px rgba(0,0,0,.2);transform:translate(-50%,-100%);margin-top:-10px;z-index:3;}
.tip i{display:inline-block;width:8px;height:8px;border-radius:2px;margin-right:5px;}
.tblbtn{border:0;background:none;color:var(--mut);font-size:11.5px;font-weight:600;cursor:pointer;text-decoration:underline;padding:0;}
.datatbl{max-height:260px;overflow:auto;}
.up{color:var(--good);font-weight:700;} .down{color:var(--crit);font-weight:700;}
@media(max-width:900px){.kpis{grid-template-columns:1fr 1fr;}}
@media(max-width:860px){.r21,.r11{grid-template-columns:1fr;}}
@media(max-width:820px){.grid{grid-template-columns:1fr;}}
</style>

<div class="dash">
@php
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
    $nextMonth = $month->copy()->addMonthNoOverflow();
    $change = $prevSales > 0 ? ($monthSales - $prevSales) / $prevSales * 100 : null;
@endphp
<div class="mbar">
    <div class="month" aria-label="Month">
        <a href="{{ route('dashboard', ['month' => $prevMonth->format('Y-m')]) }}" aria-label="Previous month">&#8249;</a>
        <b>{{ $month->format('F Y') }}</b>
        @if($isCurrent)<span class="off">&#8250;</span>@else<a href="{{ route('dashboard', ['month' => $nextMonth->format('Y-m')]) }}" aria-label="Next month">&#8250;</a>@endif
    </div>
</div>

<section class="kpis">
    <a href="{{ Route::has('financial-statement.index') ? route('financial-statement.index') : (Route::has('reports.index') ? route('reports.index') : '#') }}" class="kpi a"><div class="l">Sales ({{ $month->format('M') }})</div><div class="v">&#8369;{{ number_format($monthSales) }}</div>
        <div class="s">@if($change !== null)<span class="{{ $change >= 0 ? 'up' : 'down' }}">{!! $change >= 0 ? '&#9650;' : '&#9660;' !!} {{ number_format(abs($change)) }}%</span> vs {{ $prevMonth->format('M') }}@else counter + branch + orders @endif</div></a>
    <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="kpi b"><div class="l">Products Sold ({{ $month->format('M') }})</div><div class="v">{{ number_format($soldPcs) }} <span style="font-size:14px;color:#8A7460;">pcs</span></div><span class="kpi-go">View Reports &rarr;</span></a>
    <a href="{{ route('inventory.index') }}" class="kpi c"><div class="l">Low Ingredients</div><div class="v">{{ $lowCount }}</div><span class="kpi-go">View Inventory &rarr;</span></a>
    <a href="{{ Route::has('branch-transfers.index') ? route('branch-transfers.index') : '#' }}" class="kpi d"><div class="l">Batches Sent ({{ $month->format('M') }})</div><div class="v">{{ $batchesThisMonth }} <span style="font-size:14px;color:#8A7460;">{{ $batchesThisMonth == 1 ? 'batch' : 'batches' }}</span></div><span class="kpi-go">View Branch Transfers &rarr;</span></a>
</section>

<div class="row r21">
    <section class="card">
        <div class="hd"><h3>Daily Sales</h3><button type="button" class="tblbtn" onclick="var t=document.getElementById('salesTbl');t.hidden=!t.hidden">View as table</button></div>
        <div class="legend"><span><i style="background:var(--s1)"></i>Counter (POS)</span><span><i style="background:var(--s2)"></i>Branch transfers</span><span><i style="background:var(--s3)"></i>Custom orders</span></div>
        <div class="chart" id="salesChart"></div>
        <div class="datatbl" id="salesTbl" hidden></div>
    </section>

    <section class="card star">
        <div class="hd"><h3>Latest Batch</h3><span class="m">{{ $latestBatch ? $latestBatch->batch_date->format('M d, Y') : '' }}</span></div>
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
</div>

<div class="row r11">
    <section class="card">
        <div class="hd"><h3>Best Sellers</h3><span class="m">pcs sold in {{ $month->format('M') }}</span></div>
        <div class="sub">Counter sales, branch transfers and custom orders</div>
        <div class="chart" id="topChart"></div>
    </section>
    <section class="card">
        <div class="hd"><h3>Ingredients Used</h3><span class="m">FIFO cost in {{ $month->format('M') }}</span></div>
        <div class="sub">Where the ingredient money went (includes damaged / spoiled)</div>
        <div class="chart" id="ingChart"></div>
    </section>
</div>

<div class="row">
    <section class="card">
        <div class="hd"><h3>Revenue vs Expenses</h3><span class="m">last 6 months</span></div>
        <div class="legend"><span><i style="background:var(--s1)"></i>Revenue</span><span><i style="background:var(--s2)"></i>Expenses</span></div>
        <div class="chart" id="monthChart"></div>
    </section>
</div>

<div class="row r11">
    <section class="card">
        <div class="hd"><h3>Recent Stock Movements</h3><span class="m">ingredient activity</span></div>
        @if($movements->count())
            @foreach($movements as $mv)
            <div class="mv">
                @php $t = strtolower($mv->type); @endphp
                <span class="mv-ic {{ $t==='received' ? 'recv' : (in_array($t, ['used','damaged']) ? 'used' : 'adj') }}">{{ $t==='received' ? '+' : (in_array($t, ['used','damaged']) ? '-' : '~') }}</span>
                <div class="mv-b"><div class="t">{{ ucfirst($mv->type) }} {{ $fmt($mv->quantity) }} {{ $mv->inventory->unit ?? '' }} &middot; {{ $mv->inventory->item_name ?? '' }}</div><div class="s">{{ $mv->notes }}</div></div>
                <span class="mv-t">{{ $mv->created_at->diffForHumans(null, true) }} ago</span>
            </div>
            @endforeach
        @else
            <div class="empty">No stock movements logged yet. They appear when you create products (auto-deduction) or adjust inventory.</div>
        @endif
    </section>

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
                    <span class="lvl-n">{{ $inv->item_name }} <small>min {{ $fmt($inv->minimum_stock) }} {{ $inv->unit }}</small></span>
                    <span class="lvl-t"><span class="lvl-f {{ $cls }}" style="width:{{ $pct }}%"></span></span>
                    <span class="lvl-q">{{ $fmt($usable) }} {{ $inv->unit }}</span>
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

<script>
(function () {
    const D = @json($chart);
    const MS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const NS = 'http://www.w3.org/2000/svg';
    const SER = [['Counter', 'var(--s1)'], ['Branch', 'var(--s2)'], ['Orders', 'var(--s3)']];
    const peso = (n, d) => '\u20B1' + Number(n).toLocaleString('en-US', {minimumFractionDigits: d || 0, maximumFractionDigits: d || 0});
    const kfmt = v => v >= 1000 ? '\u20B1' + (v / 1000).toLocaleString('en-US', {maximumFractionDigits: 1}) + 'k' : '\u20B1' + v;
    const esc = t => String(t).replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));
    function el(tag, attrs, parent) { const e = document.createElementNS(NS, tag); for (const k in attrs) e.setAttribute(k, attrs[k]); if (parent) parent.appendChild(e); return e; }
    function niceStep(v) { if (v <= 0) return 250; const p = Math.pow(10, Math.floor(Math.log10(v))); for (const s of [1, 2, 2.5, 5, 10]) if (s * p >= v) return s * p; return 10 * p; }
    function tip(host, html, x, y) { let t = host.querySelector('.tip'); if (!t) { t = document.createElement('div'); t.className = 'tip'; host.appendChild(t); } t.innerHTML = html; t.style.left = x + 'px'; t.style.top = y + 'px'; t.hidden = false; }
    function untip(host) { const t = host.querySelector('.tip'); if (t) t.hidden = true; }
    function onHover(hit, host, html, mark) {
        hit.addEventListener('mousemove', ev => { const b = host.getBoundingClientRect(); if (mark) mark.setAttribute('opacity', .8); tip(host, html, ev.clientX - b.left, ev.clientY - b.top); });
        hit.addEventListener('mouseleave', () => { if (mark) mark.setAttribute('opacity', 1); untip(host); });
    }
    // bar shapes: 3px rounded data-end, flat at the baseline
    function vbar(x, y, w, h, r) { r = Math.min(r, h, w / 2); return `M${x},${y + h}V${y + r}Q${x},${y} ${x + r},${y}H${x + w - r}Q${x + w},${y} ${x + w},${y + r}V${y + h}Z`; }
    function hbar(x, y, w, h, r) { r = Math.min(r, w, h / 2); return `M${x},${y}H${x + w - r}Q${x + w},${y} ${x + w},${y + r}V${y + h - r}Q${x + w},${y + h} ${x + w - r},${y + h}H${x}Z`; }
    function yAxis(svg, max, y, L, iw) { for (let k = 0; k <= 4; k++) { const v = max * k / 4; el('line', {x1: L, x2: L + iw, y1: y(v), y2: y(v), class: 'gl'}, svg); el('text', {x: L - 8, y: y(v) + 3.5, 'text-anchor': 'end', class: 'ax'}, svg).textContent = kfmt(v); } }

    // Daily sales: stacked bars per day (counter, branch, orders)
    (function () {
        const host = document.getElementById('salesChart'), days = D.daily.length, mon = MS[D.month];
        const W = 640, H = 320, L = 46, R = 8, T = 12, B = 26, iw = W - L - R, ih = H - T - B;
        const svg = el('svg', {viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': 'Daily sales this month'}, host);
        const tot = r => r ? r[0] + r[1] + r[2] : 0;
        const max = niceStep(Math.max(...D.daily.map(tot)) / 4) * 4;
        const slot = iw / days, bw = Math.max(3, slot - 4), y = v => T + ih - v / max * ih;
        yAxis(svg, max, y, L, iw);
        [1, 8, 15, 22, 29].filter(d => d <= days).forEach(d => { el('text', {x: L + slot * (d - .5), y: H - 6, 'text-anchor': 'middle', class: 'ax'}, svg).textContent = mon + ' ' + d; });
        D.daily.forEach((r, i) => {
            if (!r) return;
            const xx = L + slot * i + (slot - bw) / 2; let base = 0;
            const parts = r.map((v, k) => [v, k]).filter(p => p[0] > 0);
            parts.forEach(([v, k], j) => {
                const top = j === parts.length - 1, y0 = y(base), y1 = y(base + v), h = Math.max(0, y0 - y1 - (j ? 2 : 0));
                el('path', {d: top ? vbar(xx, y1, bw, h, 3) : `M${xx},${y1}h${bw}v${h}h${-bw}Z`, fill: SER[k][1]}, svg);
                base += v;
            });
            const hit = el('rect', {x: L + slot * i, y: T, width: slot, height: ih, fill: 'transparent'}, svg);
            onHover(hit, host, `<b>${mon} ${i + 1}</b><br>` + SER.map((s, k) => `<i style="background:${s[1]}"></i>${s[0]} ${peso(r[k])}`).join('<br>') + `<br>Total <b>${peso(tot(r))}</b>`);
        });
        if (!D.daily.some(r => tot(r) > 0)) el('text', {x: L + iw / 2, y: T + ih / 2, 'text-anchor': 'middle', class: 'ax', style: 'font-size:12.5px'}, svg).textContent = 'No sales recorded this month';
        document.getElementById('salesTbl').innerHTML = '<table><thead><tr><th>Day</th><th>Counter</th><th>Branch</th><th>Orders</th><th>Total</th></tr></thead><tbody>'
            + D.daily.map((r, i) => r && tot(r) ? `<tr><td>${mon} ${i + 1}</td><td>${peso(r[0], 2)}</td><td>${peso(r[1], 2)}</td><td>${peso(r[2], 2)}</td><td>${peso(tot(r), 2)}</td></tr>` : '').join('') + '</tbody></table>';
    })();

    // Horizontal bars, single series, values labeled at the bar end
    function hbars(id, rows, fmt, unit, empty) {
        const host = document.getElementById(id);
        if (!rows.length) { host.innerHTML = `<div class="empty">${empty}</div>`; return; }
        const W = 420, rowH = 30, L = 128, R = 70, H = rows.length * rowH + 6, iw = W - L - R;
        const svg = el('svg', {viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': unit}, host);
        const max = Math.max(...rows.map(r => r.v));
        rows.forEach((r, k) => {
            const yy = 4 + k * rowH, w = Math.max(2, r.v / max * iw), name = r.n.length > 20 ? r.n.slice(0, 19) + '\u2026' : r.n;
            el('text', {x: L - 10, y: yy + 15, 'text-anchor': 'end', class: 'ax', style: 'fill:#5B4636;font-size:11.5px'}, svg).textContent = name;
            const bar = el('path', {d: hbar(L, yy + 4, w, 16, 4), fill: 'var(--bar)'}, svg);
            el('text', {x: L + w + 6, y: yy + 16, class: 'ax', style: 'fill:#2E1C10;font-weight:700'}, svg).textContent = fmt(r.v);
            onHover(el('rect', {x: 0, y: yy, width: W, height: rowH, fill: 'transparent'}, svg), host, `<b>${esc(r.n)}</b><br>${fmt(r.v)} ${unit}`, bar);
        });
    }
    hbars('topChart', D.top, v => v.toLocaleString('en-US') + ' pcs', 'sold', 'No products sold this month yet.');
    hbars('ingChart', D.ing, v => peso(v, 2), 'ingredient cost', 'No ingredient usage with FIFO cost this month yet.');

    // Revenue vs expenses: paired bars per month, one axis
    (function () {
        const host = document.getElementById('monthChart'), list = D.months;
        const W = 900, H = 220, L = 52, R = 12, T = 12, B = 28, iw = W - L - R, ih = H - T - B;
        const svg = el('svg', {viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': 'Revenue and expenses for the last six months'}, host);
        const max = niceStep(Math.max(...list.map(o => Math.max(o.revenue, o.expenses))) / 4) * 4;
        const y = v => T + ih - v / max * ih, slot = iw / list.length, bw = Math.min(34, slot / 3.2);
        yAxis(svg, max, y, L, iw);
        list.forEach((o, k) => {
            const cx = L + slot * k + slot / 2, last = k === list.length - 1;
            [[o.revenue, 'var(--s1)', cx - bw - 1], [o.expenses, 'var(--s2)', cx + 1]].forEach(([v, c, xx]) => { if (v > 0) el('path', {d: vbar(xx, y(v), bw, T + ih - y(v), 4), fill: c}, svg); });
            el('text', {x: cx, y: H - 8, 'text-anchor': 'middle', class: 'ax', style: last ? 'fill:#2E1C10;font-weight:800' : ''}, svg).textContent = o.label;
            onHover(el('rect', {x: cx - slot / 2, y: T, width: slot, height: ih, fill: 'transparent'}, svg), host,
                `<b>${o.full}</b><br><i style="background:var(--s1)"></i>Revenue ${peso(o.revenue, 2)}<br><i style="background:var(--s2)"></i>Expenses ${peso(o.expenses, 2)}<br>Net <b>${peso(o.revenue - o.expenses, 2)}</b>`);
        });
    })();
})();
</script>
</x-app-layout>
