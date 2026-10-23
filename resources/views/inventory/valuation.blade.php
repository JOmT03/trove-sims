<x-app-layout>
<x-slot name="header">Stock Value</x-slot>
<x-slot name="subheader">FIFO inventory valuation &mdash; raw materials</x-slot>

<style>
.val-wrap{max-width:1040px;}
.val-top{display:flex;align-items:baseline;gap:12px;margin-bottom:16px;}
.val-top .l{font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);}
.val-top .v{font-family:var(--f-display);font-size:34px;font-weight:800;letter-spacing:-1px;font-variant-numeric:tabular-nums;color:var(--text);}
.val-note{font-size:12px;color:var(--muted);margin-bottom:16px;line-height:1.5;}
.val-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);overflow:hidden;}
.val-card table{width:100%;border-collapse:collapse;font-size:13px;}
.val-card th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:12px 16px;background:var(--bg);font-weight:700;}
.val-card td{padding:12px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
.val-card tbody tr:last-child td{border-bottom:none;}
.val-card .num{text-align:right;font-variant-numeric:tabular-nums;}
.val-card .item{font-weight:700;color:var(--text);cursor:pointer;}
.val-card .muted{color:var(--muted);}
.val-card .val{font-weight:700;}
.lotrow{display:none;background:var(--bg);}
.lotrow.show{display:table-row;}
.lotrow td{padding:0;border-bottom:1px solid var(--border);}
.lots{padding:10px 16px 12px 32px;}
.lots table{width:100%;}
.lots th{background:transparent;padding:6px 10px;font-size:9.5px;}
.lots td{padding:6px 10px;border-bottom:1px dashed var(--border);font-size:12px;}
.lots tr:last-child td{border-bottom:none;}
.lot-empty{color:var(--muted);font-size:12px;padding:6px 0;}
.caret{display:inline-block;width:0;height:0;border-left:5px solid var(--muted);border-top:4px solid transparent;border-bottom:4px solid transparent;margin-right:7px;transition:transform .15s ease;}
.item.open .caret{transform:rotate(90deg);}
</style>

<div class="val-wrap">
  <div class="val-top">
    <span class="l">Total stock value (FIFO)</span>
    <span class="v">&#8369;{{ number_format($grand, 2) }}</span>
  </div>
  <div class="val-note">
    Each purchase is a lot with its own cost. Value = what is still on hand, costed oldest-stock-first.
    Tap an item to see its lots. Figures start from when FIFO was switched on (opening lots).
  </div>

  <div class="val-card">
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <th class="num">On hand</th>
          <th class="num">FIFO remaining</th>
          <th class="num">Avg unit cost</th>
          <th class="num">Stock value</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $i => $r)
          <tr>
            <td><span class="item" id="it{{ $i }}" onclick="toggleLots({{ $i }})"><span class="caret"></span>{{ $r['inv']->item_name }}</span></td>
            <td class="num">{{ rtrim(rtrim(number_format($r['onHand'], 2), '0'), '.') }} <span class="muted">{{ $r['inv']->unit }}</span></td>
            <td class="num">{{ rtrim(rtrim(number_format($r['remaining'], 2), '0'), '.') }}</td>
            <td class="num muted">&#8369;{{ number_format($r['avgCost'], 2) }}</td>
            <td class="num val">&#8369;{{ number_format($r['value'], 2) }}</td>
          </tr>
          <tr class="lotrow" id="lot{{ $i }}">
            <td colspan="5">
              <div class="lots">
                @if(count($r['lots']))
                  <table>
                    <thead><tr><th>Received</th><th class="num">Qty in</th><th class="num">Remaining</th><th class="num">Unit cost</th><th class="num">Value</th></tr></thead>
                    <tbody>
                      @foreach($r['lots'] as $l)
                        <tr>
                          <td>{{ \Illuminate\Support\Carbon::parse($l['lot']->received_at)->format('M j, Y') }}</td>
                          <td class="num">{{ rtrim(rtrim(number_format($l['received'], 2), '0'), '.') }}</td>
                          <td class="num">{{ rtrim(rtrim(number_format($l['remaining'], 2), '0'), '.') }}</td>
                          <td class="num">&#8369;{{ number_format($l['unit_cost'], 2) }}</td>
                          <td class="num">&#8369;{{ number_format($l['value'], 2) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                @else
                  <div class="lot-empty">No lots yet. Record an ingredient purchase in Expenses, or run the backfill.</div>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="muted" style="text-align:center;padding:30px;">No inventory items.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleLots(i){
  document.getElementById("lot"+i).classList.toggle("show");
  document.getElementById("it"+i).classList.toggle("open");
}
</script>
</x-app-layout>