<x-app-layout>
<x-slot name="header">Inventory</x-slot>
<x-slot name="subheader">Raw materials &amp; ingredients &mdash; production yield &amp; audit</x-slot>

@php
    $fmt = function ($n) { return rtrim(rtrim(number_format((float) $n, 2), '0'), '.'); };
    $noMat = collect($yield)->filter(function ($y) { return $y['max'] <= 0; })->count();
@endphp

<style>
.pa-wrap{max-width:1040px;}
.pa-alert{padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.pa-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.pa-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.seg{display:inline-flex;background:var(--bg);border:1px solid var(--border);border-radius:11px;padding:4px;gap:4px;margin-bottom:16px;}
.seg a{text-decoration:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:var(--muted);font-family:var(--f-body);display:inline-flex;align-items:center;gap:7px;}
.seg a.on{background:var(--white);color:var(--gold);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.seg .count{font-size:11px;background:var(--border);color:var(--text);border-radius:999px;padding:1px 7px;font-weight:700;}
.seg a.on .count{background:var(--gold);color:#fff;}
.seg svg{width:14px;height:14px;stroke:currentColor;fill:none;}
.pa-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:8px;}
.pa-kpi{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:16px 18px;box-shadow:0 1px 6px rgba(74,44,23,.06);position:relative;overflow:hidden;}
.pa-kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:#15803D;}
.pa-kpi.amber::before{background:#B45309;} .pa-kpi.red::before{background:#C2410C;}
.pa-kpi .l{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);}
.pa-kpi .v{font-family:var(--f-display);font-size:30px;font-weight:800;line-height:1;margin-top:8px;letter-spacing:-1px;font-variant-numeric:tabular-nums;}
.pa-kpi.amber .v{color:#B45309;} .pa-kpi.red .v{color:#C2410C;}
.pa-kpi .h{font-size:11px;color:var(--muted);margin-top:5px;}
.pa-sec{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:24px 0 10px;}
.pa-sec h2{font-family:var(--f-display);font-size:18px;margin:0;}
.pa-sec p{font-size:12.5px;color:var(--muted);margin:3px 0 0;}
.pa-btn{background:var(--gold);color:#fff;border:none;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;white-space:nowrap;font-family:var(--f-body);}
.pa-btn.ghost{background:var(--white);color:var(--gold);border:1px solid var(--border);padding:7px 12px;font-size:12px;}
.pa-btn:disabled{opacity:.45;cursor:not-allowed;}
.pa-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);overflow-x:auto;}
.pa-card table{width:100%;border-collapse:collapse;font-size:13px;}
.pa-card th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:12px 16px;background:var(--bg);font-weight:700;white-space:nowrap;}
.pa-card td{padding:13px 16px;border-bottom:1px solid var(--border);vertical-align:top;}
.pa-card tbody tr:last-child td{border-bottom:none;}
.pa-card tbody tr:hover{background:var(--bg);}
.pa-card tr.flag td{background:#FDF3EF;}
.pa-name{font-weight:700;color:var(--text);}
.pa-sub{font-size:11.5px;color:var(--muted);margin-top:2px;}
.pa-yield{font-family:var(--f-display);font-size:22px;font-weight:800;font-variant-numeric:tabular-nums;color:#15803D;}
.pa-yield.zero{color:#C2410C;}
.pa-pill{font-size:10px;font-weight:800;letter-spacing:.3px;padding:2px 8px;border-radius:999px;text-transform:uppercase;display:inline-block;}
.pa-pill.ok{background:#E7F3EA;color:#15803D;} .pa-pill.warn{background:#FBEBD6;color:#B45309;} .pa-pill.bad{background:#FBE4DA;color:#C2410C;}
.pa-card details{margin-top:6px;font-size:12px;}
.pa-card summary{cursor:pointer;color:var(--gold);font-weight:700;}
.pa-card details ul{margin:6px 0 0;padding-left:16px;color:var(--muted);}
.pa-empty{text-align:center;padding:30px;color:var(--muted);font-size:14px;}
.pa-pager{display:flex;gap:10px;justify-content:flex-end;margin-top:12px;}
.pa-pager a{font-size:12.5px;font-weight:700;color:var(--gold);text-decoration:none;padding:7px 12px;border:1px solid var(--border);border-radius:8px;background:var(--white);}
.pm-bg{display:none;position:fixed;inset:0;background:rgba(46,28,16,.55);z-index:70;align-items:center;justify-content:center;padding:16px;}
.pm-bg.show{display:flex;}
.pm-modal{background:var(--white);border-radius:18px;max-width:640px;width:100%;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.35);}
.pm-head{display:flex;align-items:flex-start;justify-content:space-between;padding:18px 20px;border-bottom:1px solid var(--border);}
.pm-head h3{font-family:var(--f-display);font-size:18px;margin:0;}
.pm-sub{font-size:12px;color:var(--muted);margin:3px 0 0;}
.pm-x{background:none;border:none;color:var(--muted);font-size:22px;line-height:1;cursor:pointer;padding:0 2px;}
.pm-body{padding:18px 20px;}
.pm-fg{margin-bottom:14px;}
.pm-fg2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.pm-modal label{display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:6px;}
.pm-modal input,.pm-modal select,.pm-modal textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);box-sizing:border-box;}
.pm-modal input:focus,.pm-modal select:focus,.pm-modal textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.pm-hint{font-size:12px;color:var(--muted);margin:0 0 8px;}
.pm-mat{width:100%;border-collapse:collapse;font-size:12.5px;margin-bottom:14px;}
.pm-mat th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:7px 8px;background:var(--bg);}
.pm-mat td{padding:7px 8px;border-bottom:1px solid var(--border);}
.pm-mat td.neg{color:#C2410C;font-weight:700;}
.pm-var{padding:11px 14px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:14px;display:none;}
.pm-var.ok{display:block;background:#E7F3EA;color:#166534;}
.pm-var.warn{display:block;background:#FBEBD6;color:#92400E;}
.pm-var.bad{display:block;background:#FBE4DA;color:#991b1b;}
.pm-foot{padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;}
.pm-ghost{background:var(--bg);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:11px 18px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
</style>

<div class="pa-wrap">
    @if(session('success'))
        <div class="pa-alert ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="pa-alert err">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="pa-alert err">{{ $errors->first() }}</div>
    @endif

    @include('inventory._tabs', ['current' => 'production'])

    <div class="pa-kpis">
        <div class="pa-kpi"><div class="l">Products with a recipe</div><div class="v">{{ count($yield) }}</div><div class="h">yield computed from current stock</div></div>
        <div class="pa-kpi {{ $noMat > 0 ? 'red' : '' }}"><div class="l">Cannot produce now</div><div class="v">{{ $noMat }}</div><div class="h">missing materials</div></div>
        @if($isAdmin)
            <div class="pa-kpi"><div class="l">Production runs</div><div class="v">{{ $stats['runs'] }}</div><div class="h">last 30 days</div></div>
            <div class="pa-kpi {{ $stats['flagged'] > 0 ? 'red' : '' }}"><div class="l">With variance</div><div class="v">{{ $stats['flagged'] }}</div><div class="h">{{ $stats['missing'] }} unit(s) unaccounted &middot; 30 days</div></div>
        @endif
    </div>

    {{-- ===== 1. YIELD PREVIEW ===== --}}
    <div class="pa-sec">
        <div>
            <h2>What can we produce now?</h2>
            <p>Based on the active recipe and the raw materials currently in stock.</p>
        </div>
        @if($isAdmin)
            <button type="button" class="pa-btn" onclick="openProd()">+ Record production</button>
        @endif
    </div>

    <div class="pa-card">
        <table>
            <thead><tr><th>Product</th><th>Finished stock</th><th>Can produce</th><th>Limited by</th><th></th></tr></thead>
            <tbody>
            @forelse($yield as $pid => $y)
                <tr>
                    <td>
                        <div class="pa-name">{{ $y['name'] }}</div>
                        <div class="pa-sub">Recipe: {{ $y['recipe'] }}</div>
                    </td>
                    <td>{{ $y['stock'] }}</td>
                    <td>
                        <span class="pa-yield {{ $y['max'] <= 0 ? 'zero' : '' }}">{{ $y['max'] }}</span>
                        @if($y['max'] <= 0)<span class="pa-pill bad">No materials</span>@endif
                    </td>
                    <td>
                        {{ $y['limit'] ?? '-' }}
                        <details>
                            <summary>Ingredients ({{ count($y['items']) }})</summary>
                            <ul>
                                @foreach($y['items'] as $l)
                                    <li>{{ $l['name'] }}: {{ $fmt($l['on']) }} {{ $l['unit'] }} on hand &divide; {{ $fmt($l['per']) }} per unit = {{ $l['can'] }}</li>
                                @endforeach
                            </ul>
                        </details>
                    </td>
                    <td style="text-align:right;">
                        @if($isAdmin && $y['max'] > 0)
                            <button type="button" class="pa-btn ghost" onclick="openProd({{ $y['id'] }})">Record</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="pa-empty">No products have an active recipe yet. Set one up in Products &rarr; Recipes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== 2. AUDIT LOG (Owner/Manager only) ===== --}}
    @if($isAdmin)
        <div class="pa-sec" id="audit">
            <div>
                <h2>Production audit</h2>
                <p>Expected yield (from the materials issued) vs. what was actually produced.</p>
            </div>
            <div class="seg" style="margin:0;">
                <a href="{{ route('production.create') }}#audit" class="{{ $flag === 'all' ? 'on' : '' }}">All runs</a>
                <a href="{{ route('production.create', ['flag' => 'variance']) }}#audit" class="{{ $flag === 'variance' ? 'on' : '' }}">Variance only</a>
            </div>
        </div>

        <div class="pa-card">
            <table>
                <thead><tr><th>When</th><th>Product</th><th>Expected</th><th>Produced</th><th>Result</th><th>Reason</th><th>By</th></tr></thead>
                <tbody>
                @forelse($audits as $a)
                    @php $v = (int) $a->variance_qty; @endphp
                    <tr class="{{ $v !== 0 ? 'flag' : '' }}">
                        <td style="white-space:nowrap;">{{ $a->created_at->format('M j, Y') }}<div class="pa-sub">{{ $a->created_at->format('g:i A') }}</div></td>
                        <td>
                            <div class="pa-name">{{ $a->product_name }}</div>
                            @if($a->recipe_name)<div class="pa-sub">Recipe: {{ $a->recipe_name }}</div>@endif
                            @if(!empty($a->materials))
                                <details>
                                    <summary>Materials used</summary>
                                    <ul>
                                        @foreach($a->materials as $m)
                                            <li>{{ $m['name'] ?? '?' }}: {{ $fmt($m['used'] ?? 0) }} {{ $m['unit'] ?? '' }} ({{ $fmt($m['before'] ?? 0) }} &rarr; {{ $fmt($m['after'] ?? 0) }})</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>
                        <td><strong>{{ $a->expected_qty }}</strong></td>
                        <td><strong>{{ $a->actual_qty }}</strong></td>
                        <td>
                            @if($v > 0)<span class="pa-pill bad">{{ $v }} short</span>
                            @elseif($v < 0)<span class="pa-pill warn">{{ abs($v) }} over</span>
                            @else<span class="pa-pill ok">Match</span>@endif
                        </td>
                        <td>{{ $a->notes ?: '-' }}</td>
                        <td>{{ $a->user ? trim(($a->user->first_name ?? '') . ' ' . ($a->user->last_name ?? '')) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="pa-empty">{{ $flag === 'variance' ? 'No variance recorded. Everything matched.' : 'No production recorded yet.' }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($audits->previousPageUrl() || $audits->nextPageUrl())
            <div class="pa-pager">
                @if($audits->previousPageUrl())<a href="{{ $audits->previousPageUrl() }}#audit">&larr; Newer</a>@endif
                @if($audits->nextPageUrl())<a href="{{ $audits->nextPageUrl() }}#audit">Older &rarr;</a>@endif
            </div>
        @endif
    @endif
</div>

@if($isAdmin)
<div class="pm-bg" id="prodBg">
  <div class="pm-modal">
    <div class="pm-head">
      <div>
        <h3>Record production</h3>
        <p class="pm-sub">Materials are deducted for the expected batch. Finished stock increases by what was actually produced.</p>
      </div>
      <button type="button" class="pm-x" onclick="closeProd()" aria-label="Close">&times;</button>
    </div>
    <form method="POST" action="{{ route('production.store') }}">
      @csrf
      <div class="pm-body">
        <div class="pm-fg">
          <label>Product</label>
          <select name="product_id" id="pProd" onchange="onProd()" required>
            <option value="">&mdash; Select &mdash;</option>
            @foreach($yield as $pid => $y)
              <option value="{{ $pid }}" {{ (string) old('product_id') === (string) $pid ? 'selected' : '' }}>{{ $y['name'] }} (can make {{ $y['max'] }})</option>
            @endforeach
          </select>
        </div>

        <div id="prodInfo" style="display:none;">
          <p class="pm-hint">Current materials cover up to <strong id="pMax">0</strong> unit(s). <a href="#" onclick="useMax();return false;" style="color:var(--gold);font-weight:700;">Use max</a></p>
          <table class="pm-mat">
            <thead><tr><th>Ingredient</th><th>Stock before</th><th>Per unit</th><th>Needed</th><th>Stock after</th></tr></thead>
            <tbody id="pMatBody"></tbody>
          </table>
        </div>

        <div class="pm-fg2">
          <div class="pm-fg">
            <label>Expected yield (materials issued for)</label>
            <input type="number" name="expected_qty" id="pExp" min="1" step="1" value="{{ old('expected_qty') }}" oninput="recalc()" required>
          </div>
          <div class="pm-fg">
            <label>Actually produced</label>
            <input type="number" name="actual_qty" id="pAct" min="0" step="1" value="{{ old('actual_qty') }}" oninput="recalc()" required>
          </div>
        </div>

        <div class="pm-var" id="pVar"></div>

        <div class="pm-fg">
          <label>Reason (required if the numbers differ)</label>
          <textarea name="notes" id="pNotes" rows="2" oninput="recalc()">{{ old('notes') }}</textarea>
        </div>
      </div>
      <div class="pm-foot">
        <button type="button" class="pm-ghost" onclick="closeProd()">Cancel</button>
        <button type="submit" class="pa-btn" id="pSubmit" disabled>Record production</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  var PROD = @json($yield);
  function el(id){ return document.getElementById(id); }
  function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function f(n){ return (Math.round(Number(n)*100)/100).toLocaleString(undefined,{maximumFractionDigits:2}); }

  window.openProd = function(id){
    el('prodBg').classList.add('show');
    if(id){ el('pProd').value = String(id); }
    window.onProd();
  };
  window.closeProd = function(){ el('prodBg').classList.remove('show'); };
  window.onProd = function(){
    var p = PROD[el('pProd').value];
    el('prodInfo').style.display = p ? 'block' : 'none';
    if(p){ el('pMax').textContent = p.max; el('pExp').max = p.max; }
    window.recalc();
  };
  window.useMax = function(){
    var p = PROD[el('pProd').value];
    if(p){ el('pExp').value = p.max; window.recalc(); }
  };
  window.recalc = function(){
    var p = PROD[el('pProd').value];
    var btn = el('pSubmit'), box = el('pVar');
    box.className = 'pm-var'; box.textContent = '';
    if(!p){ btn.disabled = true; return; }

    var e = parseInt(el('pExp').value, 10), a = parseInt(el('pAct').value, 10);
    var eN = isNaN(e) ? 0 : e;
    var rows = '';
    p.items.forEach(function(it){
      var need = it.per * eN, after = it.on - need;
      rows += '<tr><td>'+esc(it.name)+'</td><td>'+f(it.on)+' '+esc(it.unit)+'</td><td>'+f(it.per)+'</td><td>'+(eN ? f(need) : '-')+'</td><td class="'+(eN && after < 0 ? 'neg' : '')+'">'+(eN ? f(after) : '-')+'</td></tr>';
    });
    el('pMatBody').innerHTML = rows;

    var valid = !isNaN(e) && e >= 1 && e <= p.max && !isNaN(a) && a >= 0;
    if(!isNaN(e) && e > p.max){
      box.className = 'pm-var bad';
      box.textContent = 'Not enough materials: stock only covers up to ' + p.max + '.';
    } else if(valid){
      var d = e - a;
      if(d > 0){
        box.className = 'pm-var bad';
        box.textContent = 'Materials were issued for ' + e + ', but only ' + a + ' produced: ' + d + ' SHORT. Where did they go?';
      } else if(d < 0){
        box.className = 'pm-var warn';
        box.textContent = 'Produced ' + a + ' which is ' + Math.abs(d) + ' MORE than the ' + e + ' the materials should give. Please re-check the count.';
      } else {
        box.className = 'pm-var ok';
        box.textContent = 'Match: ' + a + ' produced, as expected.';
      }
      if(d !== 0 && el('pNotes').value.trim() === ''){ valid = false; }
    }
    btn.disabled = !valid;
  };

  document.getElementById('prodBg').addEventListener('click', function(ev){ if(ev.target === this){ window.closeProd(); } });

  @if(old('product_id'))
    window.openProd({{ (int) old('product_id') }});
  @endif
})();
</script>
@endif
</x-app-layout>
