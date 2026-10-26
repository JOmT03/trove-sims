@php
    $paper = $width === 58 ? 48 : 72;   // printable width in mm
    $peso = fn ($n) => number_format((float) $n, 2);
    $qty = (int) $sale->items->sum('quantity');
    $methods = ['cash' => 'Cash', 'gcash' => 'GCash', 'maya' => 'Maya', 'bank' => 'Bank transfer'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receipt {{ $sale->sale_number }}</title>
<style>
/* Thermal slip for the Epson EU-m30: {{ $width }} mm roll, {{ $paper }} mm printable. */
@page { size: {{ $width }}mm 297mm; margin: 0; }
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body { background: #e9dfd2; color: #000; font-family: "Consolas", "Courier New", ui-monospace, monospace; font-size: {{ $width === 58 ? '10px' : '11.5px' }}; line-height: 1.35; }
.slip { width: {{ $paper }}mm; margin: 16px auto; background: #fff; padding: 3mm {{ $width === 58 ? '0' : '1mm' }} 6mm; box-shadow: 0 6px 18px rgba(0,0,0,.18); }
.c { text-align: center; }
.shop { font-weight: 700; font-size: 1.3em; letter-spacing: .5px; }
.small { font-size: .9em; }
.rule { border-top: 1px dashed #000; margin: 5px 0; }
.rule2 { border-top: 2px solid #000; margin: 5px 0; }
.row { display: flex; justify-content: space-between; gap: 6px; }
.row span:last-child { text-align: right; white-space: nowrap; }
.item { margin-top: 3px; }
.big { font-weight: 700; font-size: 1.25em; }
.stamp { border: 2px solid #000; text-align: center; font-weight: 700; font-size: 1.35em; letter-spacing: 3px; margin: 5px 0; padding: 1px; }
.tools { text-align: center; font-family: Arial, sans-serif; font-size: 13px; margin: 14px 0 0; }
.tools button { padding: 9px 18px; border: 0; border-radius: 9px; background: #D9782C; color: #fff; font-weight: 700; cursor: pointer; }
@media print {
  body { background: #fff; }
  .slip { margin: 0; box-shadow: none; }
  .tools { display: none; }
}
</style>
</head>
<body>
<div class="tools"><button type="button" onclick="window.print()">Print</button></div>
<div class="slip" id="slip">
    <div class="c shop">{{ $shop['name'] }}</div>
    @if($shop['address'])<div class="c small">{{ $shop['address'] }}</div>@endif
    @if($shop['contact'])<div class="c small">{{ $shop['contact'] }}</div>@endif
    <div class="rule"></div>
    <div class="c" style="font-weight:700">SALES RECEIPT</div>
    @if($sale->voided_at)<div class="stamp">VOIDED</div>@endif
    <div class="row"><span>No.</span><span>{{ $sale->sale_number }}</span></div>
    <div class="row"><span>Date</span><span>{{ $sale->completed_at?->format('M d, Y g:i A') }}</span></div>
    <div class="row"><span>Cashier</span><span>{{ trim(($sale->cashier->first_name ?? '') . ' ' . ($sale->cashier->last_name ?? '')) ?: '-' }}</span></div>
    <div class="rule"></div>
    @foreach($sale->items as $it)
        <div class="item">
            <div>{{ $it->product_name }}</div>
            <div class="row"><span>&nbsp;&nbsp;{{ $it->quantity }} x {{ $peso($it->unit_price) }}</span><span>{{ $peso($it->line_total) }}</span></div>
        </div>
    @endforeach
    <div class="rule"></div>
    <div class="row small"><span>{{ $qty }} {{ $qty === 1 ? 'item' : 'items' }}</span><span></span></div>
    <div class="row big"><span>TOTAL</span><span>&#8369;{{ $peso($sale->total) }}</span></div>
    <div class="rule2"></div>
    @foreach($sale->payments as $p)
        <div class="row"><span>{{ $methods[$p->method] ?? ucfirst($p->method) }}</span><span>{{ $peso($p->amount) }}</span></div>
        @if($p->reference)<div class="row small"><span>&nbsp;&nbsp;Ref. no.</span><span>{{ $p->reference }}</span></div>@endif
    @endforeach
    @if($change > 0)<div class="row"><span>Change</span><span>{{ $peso($change) }}</span></div>@endif
    @if($sale->voided_at)
        <div class="rule"></div>
        <div class="small">Voided {{ $sale->voided_at->format('M d, Y g:i A') }}: {{ $sale->void_reason }}</div>
    @endif
    <div class="rule"></div>
    @if($shop['footer'])<div class="c">{{ $shop['footer'] }}</div>@endif
    <div class="c small">This is not an official receipt.</div>
    @if($reprint)<div class="c small" style="margin-top:4px">*** REPRINT ***</div>@endif
</div>
<script>
/* Fit the page length to this slip so the printer feeds and cuts right after it. */
(function () {
    var mm = Math.ceil(document.getElementById('slip').offsetHeight * 25.4 / 96) + 8;
    var st = document.createElement('style');
    st.textContent = '@page { size: {{ $width }}mm ' + mm + 'mm; margin: 0; }';
    document.head.appendChild(st);
})();
@if($autoPrint)
window.addEventListener('load', function () { window.focus(); window.print(); });
@endif
</script>
</body>
</html>
