<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receipt - {{ $c->customer_name }}</title>
@php
    $addons = $c->items->sum(fn($it) => $it->quantity * $it->price);
    $custom = max(0, (float)$c->total_amount - (float)$addons);
@endphp
<style>
  *{box-sizing:border-box;}
  body{margin:0;background:#EDE4D6;font-family:'Segoe UI',system-ui,Arial,sans-serif;color:#2E1C10;padding:24px;}
  .toolbar{max-width:720px;margin:0 auto 16px;display:flex;gap:10px;justify-content:flex-end;}
  .tb{padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
  .tb.gold{background:#D9782C;color:#fff;} .tb.ghost{background:#fff;color:#4A2C17;border:1px solid #D8C7B2;}
  .receipt{max-width:720px;margin:0 auto;background:#fff;border:1px solid #E4D7C5;border-radius:4px;padding:40px;}
  .r-head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #2E1C10;padding-bottom:18px;margin-bottom:22px;}
  .r-shop{font-size:22px;font-weight:800;letter-spacing:-.4px;}
  .r-shop .meta{font-size:12px;font-weight:500;color:#7A6A58;margin-top:4px;line-height:1.5;}
  .r-tag{text-align:right;}
  .r-tag .t{font-size:13px;font-weight:800;letter-spacing:2px;color:#D9782C;}
  .r-tag .no{font-size:13px;font-weight:700;margin-top:4px;} .r-tag .dt{font-size:12px;color:#7A6A58;margin-top:2px;}
  .r-cols{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;}
  .r-cols .k{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#9A8974;font-weight:700;}
  .r-cols .v{font-size:14px;font-weight:600;margin-top:3px;}
  .r-sec-t{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#9A8974;font-weight:800;margin:0 0 8px;}
  .r-design{font-size:13.5px;line-height:1.6;background:#FBF6EE;border:1px solid #EDE0D0;border-radius:8px;padding:12px 14px;margin-bottom:20px;}
  table{width:100%;border-collapse:collapse;font-size:13.5px;margin-bottom:6px;}
  th{text-align:right;font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:#9A8974;padding:8px 10px;border-bottom:1.5px solid #E4D7C5;}
  th:first-child{text-align:left;}
  td{padding:9px 10px;border-bottom:1px solid #F0E8DC;text-align:right;font-variant-numeric:tabular-nums;}
  td:first-child{text-align:left;}
  .tot{display:flex;flex-direction:column;align-items:flex-end;gap:5px;margin:16px 0 24px;}
  .tot .row{display:flex;justify-content:space-between;width:260px;font-size:14px;}
  .tot .row.grand{border-top:1.5px solid #2E1C10;padding-top:8px;margin-top:4px;font-weight:800;font-size:16px;}
  .tot .row .bal{color:#C2410C;font-weight:800;}
  .r-terms{border-top:1px solid #E4D7C5;padding-top:18px;margin-top:8px;}
  .r-terms .body{font-size:12px;line-height:1.7;color:#4A3B2A;white-space:pre-line;}
  .r-sign{display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:40px;}
  .r-sign .line{border-top:1px solid #2E1C10;padding-top:6px;font-size:11px;color:#7A6A58;text-align:center;}
  .r-foot{text-align:center;font-size:12px;color:#9A8974;margin-top:30px;}
  @media print{
    body{background:#fff;padding:0;}
    .toolbar{display:none;}
    .receipt{border:none;border-radius:0;max-width:100%;padding:12px;}
  }
</style>
</head>
<body>
  <div class="toolbar">
    <a href="{{ route('commissions.show', $c) }}" class="tb ghost">&larr; Back</a>
    @can('admin')<a href="{{ route('settings.shop') }}" class="tb ghost">Edit terms</a>@endcan
    <button type="button" class="tb gold" onclick="window.print()">Print / Save PDF</button>
  </div>

  <div class="receipt">
    <div class="r-head">
      <div class="r-shop">{{ $shopName }}
        <div class="meta">{{ $shopAddress }}@if($shopAddress && $shopContact) &middot; @endif{{ $shopContact }}</div>
      </div>
      <div class="r-tag">
        <div class="t">ORDER RECEIPT</div>
        <div class="no">Order #{{ str_pad($c->id, 4, '0', STR_PAD_LEFT) }}</div>
        <div class="dt">{{ \Illuminate\Support\Carbon::parse($c->created_at)->format('M j, Y') }}</div>
      </div>
    </div>

    <div class="r-cols">
      <div>
        <div class="k">Billed to</div>
        <div class="v">{{ $c->customer_name }}</div>
        <div class="v" style="font-weight:500;color:#7A6A58;font-size:12.5px;">{{ ucfirst($c->client_type) }}</div>
      </div>
      <div style="text-align:right;">
        <div class="k">Needed by</div>
        <div class="v">{{ $c->needed_by_date ? $c->needed_by_date->format('M j, Y') : 'Not set' }}</div>
        <div class="v" style="font-weight:500;color:#7A6A58;font-size:12.5px;">Status: {{ $c->status }}</div>
      </div>
    </div>

    @if($c->design_description)
      <div class="r-sec-t">Custom cake</div>
      <div class="r-design">{{ $c->design_description }}</div>
    @endif

    @if($c->items->count())
      <div class="r-sec-t">Add-ons</div>
      <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
          @foreach($c->items as $it)
            <tr>
              <td>{{ $it->product->product_name ?? 'Item' }}</td>
              <td>{{ $it->quantity }}</td>
              <td>&#8369;{{ number_format($it->price, 2) }}</td>
              <td>&#8369;{{ number_format($it->quantity * $it->price, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <div class="tot">
      @if($custom > 0)<div class="row"><span>Custom cake</span><span>&#8369;{{ number_format($custom, 2) }}</span></div>@endif
      @if($addons > 0)<div class="row"><span>Add-ons</span><span>&#8369;{{ number_format($addons, 2) }}</span></div>@endif
      <div class="row grand"><span>Total</span><span>&#8369;{{ number_format($c->total_amount, 2) }}</span></div>
      <div class="row"><span>Downpayment</span><span>&#8369;{{ number_format($c->deposit_amount, 2) }}</span></div>
      <div class="row"><span>Balance due</span><span class="bal">&#8369;{{ number_format($c->balance(), 2) }}</span></div>
    </div>

    <div class="r-terms">
      <div class="r-sec-t">Terms &amp; Conditions</div>
      <div class="body">{{ $terms }}</div>
    </div>

    <div class="r-sign">
      <div class="line">Customer signature</div>
      <div class="line">{{ $shopName }}</div>
    </div>

    <div class="r-foot">Thank you for your order!</div>
  </div>
</body>
</html>