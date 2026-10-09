<x-app-layout>
<x-slot name="header">Recipes &amp; Production</x-slot>
<x-slot name="subheader">{{ $product->product_name }}</x-slot>

<style>
.rc-wrap{max-width:1000px;margin:0 auto;}
.rc-card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.06);padding:20px;margin-bottom:18px;}
.rc-h{font-family:var(--f-display);font-weight:800;font-size:16px;color:var(--text);margin:0 0 4px;}
.rc-sub{font-size:12.5px;color:var(--muted);margin:0 0 14px;}
.rc-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.rc-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.rc-wrap label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px;}
.rc-wrap input,.rc-wrap select{padding:9px 11px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;}
.rc-wrap .btn{padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:var(--f-body);}
.btn-gold{background:var(--gold);color:#fff;} .btn-gold:hover{background:#B5651D;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.btn-sm{padding:6px 11px;font-size:12px;}
.btn-danger{background:#fff;color:#C2410C;border:1px solid #f0d0c0;}
.ing-ref summary{cursor:pointer;font-family:var(--f-display);font-weight:800;font-size:15px;color:var(--text);}
.ing-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1e9dd;font-size:13px;}
.ing-row:last-child{border-bottom:none;}
.ing-q{font-weight:700;font-variant-numeric:tabular-nums;}
table.rec{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:8px;}
table.rec th{padding:8px 10px;text-align:left;font-size:11px;text-transform:uppercase;color:#8A7460;background:#FDF6EC;}
table.rec td{padding:8px 10px;border-bottom:1px solid #f9fafb;}
.badge{font-size:10px;font-weight:700;padding:2px 9px;border-radius:999px;text-transform:uppercase;}
.badge-active{background:#E6F2E6;color:#2E7D32;} .badge-arch{background:#eee;color:#888;}
.ver{border:1px solid var(--border);border-radius:11px;padding:14px;margin-bottom:11px;}
.ver.arch{opacity:.65;}
.ver-head{display:flex;align-items:center;gap:9px;justify-content:space-between;}
.ver-name{font-weight:700;font-size:14px;}
.ver-items{font-size:12px;color:var(--muted);margin-top:6px;line-height:1.5;}
.ver-acts{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;}
.maxline{font-size:13px;background:#FDF6EC;border-radius:9px;padding:10px 13px;margin:10px 0;}
.maxline b{color:#B5651D;font-family:var(--f-display);}
.addrow-btn{padding:7px 12px;border:1.5px dashed var(--border);border-radius:9px;background:#FDF6EC;color:#8A7460;font-size:12.5px;font-weight:600;cursor:pointer;}
.inline-form{display:inline;}
</style>

<div class="rc-wrap">
  @if(session('success'))<div class="rc-ok">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="rc-err">{{ session('error') }}</div>@endif

  <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm" style="margin-bottom:14px;">&larr; Back to Products</a>

  <div class="rc-card">
    <h2 class="rc-h">Active Recipe &amp; Production</h2>
    <p class="rc-sub">Finished stock on hand: <strong>{{ (int) $product->stock_quantity }}</strong> pcs</p>
    @if($active && !empty($active->items))
      <table class="rec">
        <thead><tr><th style="width:60%">Ingredient (active: {{ $active->name }})</th><th>Qty / unit</th></tr></thead>
        <tbody>
          @foreach($active->items as $it)
            @php $g = $inventoryItems->firstWhere('id', $it['inventory_id']); @endphp
            <tr><td>{{ $g?->item_name ?? 'Item #'.$it['inventory_id'] }}</td><td>{{ rtrim(rtrim(number_format($it['quantity_used'],2),'0'),'.') }} {{ $g?->unit }}</td></tr>
          @endforeach
        </tbody>
      </table>
      <div class="maxline">With current stock, you can make up to <b>{{ $activeMax }}</b> more unit(s).</div>
      <form method="POST" action="{{ route('products.produce', $product) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
        @csrf
        <div><label>Produce quantity</label><input type="number" name="quantity" min="1" value="1" style="width:120px;"></div>
        <button type="submit" class="btn btn-gold">Produce</button>
      </form>
    @else
      <p class="rc-sub">No active recipe yet. Add a version below to start producing.</p>
    @endif
  </div>

  <details class="rc-card ing-ref">
    <summary>Ingredient Stock (reference)</summary>
    <div style="margin-top:12px;">
      @forelse($inventoryItems as $g)
        <div class="ing-row"><span>{{ $g->item_name }}</span><span class="ing-q">{{ rtrim(rtrim(number_format($g->quantity_on_hand,2),'0'),'.') }} {{ $g->unit }}</span></div>
      @empty
        <p class="rc-sub" style="margin:0;">No inventory items yet.</p>
      @endforelse
    </div>
  </details>

  <div class="rc-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <h2 class="rc-h" style="margin:0;">Recipe Versions</h2>
      <button type="button" class="btn btn-gold btn-sm" onclick="rcToggle('addForm')">+ Add Version</button>
    </div>

    <div id="addForm" style="display:none;border:1px solid var(--border);border-radius:11px;padding:14px;margin-bottom:14px;background:#FDFBF7;">
      <form method="POST" action="{{ route('products.recipes.store', $product) }}">
        @csrf
        <div style="margin-bottom:10px;"><label>Recipe name</label><input type="text" name="name" value="New recipe" style="width:100%;max-width:320px;"></div>
        <table class="rec"><thead><tr><th style="width:55%">Ingredient</th><th>Qty / unit</th><th></th></tr></thead><tbody id="addBody"></tbody></table>
        <button type="button" class="addrow-btn" onclick="rcAddRow('addBody')">+ Add ingredient</button>
        <div style="margin-top:12px;"><button type="submit" class="btn btn-gold btn-sm">Save &amp; set active</button> <button type="button" class="btn btn-outline btn-sm" onclick="rcToggle('addForm')">Cancel</button></div>
      </form>
    </div>

    @foreach($versions as $v)
      <div class="ver {{ $v->is_archived ? 'arch' : '' }}">
        <div class="ver-head">
          <span class="ver-name">{{ $v->name }}</span>
          <span>
            @if($v->is_archived)<span class="badge badge-arch">Archived</span>
            @elseif($v->is_active)<span class="badge badge-active">Active</span>@endif
          </span>
        </div>
        <div class="ver-items">
          @foreach(($v->items ?? []) as $it)
            @php $g = $inventoryItems->firstWhere('id', $it['inventory_id']); @endphp
            {{ $g?->item_name ?? '#'.$it['inventory_id'] }} {{ rtrim(rtrim(number_format($it['quantity_used'],2),'0'),'.') }}{{ $g?->unit }}@if(!$loop->last) &bull; @endif
          @endforeach
        </div>
        <div class="ver-acts">
          @if($v->is_archived)
            <form class="inline-form" method="POST" action="{{ route('products.recipes.restore', [$product, $v]) }}">@csrf<button class="btn btn-outline btn-sm">Restore</button></form>
            <form class="inline-form" method="POST" action="{{ route('products.recipes.destroy', [$product, $v]) }}" onsubmit="return confirm('Permanently delete this recipe?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
          @else
            @unless($v->is_active)<form class="inline-form" method="POST" action="{{ route('products.recipes.activate', [$product, $v]) }}">@csrf<button class="btn btn-gold btn-sm">Set Active</button></form>@endunless
            <button type="button" class="btn btn-outline btn-sm" onclick="rcToggle('edit{{ $v->id }}')">Edit</button>
            <form class="inline-form" method="POST" action="{{ route('products.recipes.archive', [$product, $v]) }}">@csrf<button class="btn btn-outline btn-sm">Archive</button></form>
            <form class="inline-form" method="POST" action="{{ route('products.recipes.destroy', [$product, $v]) }}" onsubmit="return confirm('Permanently delete this recipe?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
          @endif
        </div>

        @unless($v->is_archived)
        <div id="edit{{ $v->id }}" style="display:none;border-top:1px solid var(--border);margin-top:12px;padding-top:12px;">
          <form method="POST" action="{{ route('products.recipes.update', [$product, $v]) }}">
            @csrf @method('PUT')
            <div style="margin-bottom:10px;"><label>Recipe name</label><input type="text" name="name" value="{{ $v->name }}" style="width:100%;max-width:320px;"></div>
            <table class="rec"><thead><tr><th style="width:55%">Ingredient</th><th>Qty / unit</th><th></th></tr></thead>
              <tbody id="editBody{{ $v->id }}">
                @foreach(($v->items ?? []) as $ix => $it)
                  <tr>
                    <td><select name="items[{{ $ix }}][inventory_id]" required><option value="">- Select -</option>
                      @foreach($inventoryItems as $g)<option value="{{ $g->id }}" {{ $g->id == $it['inventory_id'] ? 'selected' : '' }}>{{ $g->item_name }} ({{ $g->unit }})</option>@endforeach
                    </select></td>
                    <td><input type="number" step="0.01" min="0.01" name="items[{{ $ix }}][quantity_used]" value="{{ $it['quantity_used'] }}" required style="width:120px;"></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">&times;</button></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <button type="button" class="addrow-btn" onclick="rcAddRow('editBody{{ $v->id }}')">+ Add ingredient</button>
            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
              <button type="submit" name="mode" value="update" class="btn btn-gold btn-sm">Update this version</button>
              <button type="submit" name="mode" value="new" class="btn btn-outline btn-sm">Save as new version</button>
            </div>
          </form>
        </div>
        @endunless
      </div>
    @endforeach
  </div>
</div>

<script>
const rcInv = {!! $inventoryItems->map(fn($i)=>['id'=>$i->id,'name'=>$i->item_name,'unit'=>$i->unit])->toJson() !!};
let rcIdx = 1000;
function rcOptions(){ return '<option value="">- Select -</option>' + rcInv.map(function(i){ return '<option value="'+i.id+'">'+i.name+' ('+i.unit+')</option>'; }).join(''); }
function rcAddRow(bodyId){
  const i = rcIdx++;
  const tr = document.createElement('tr');
  tr.innerHTML = '<td><select name="items['+i+'][inventory_id]" required>'+rcOptions()+'</select></td>'+
    '<td><input type="number" step="0.01" min="0.01" name="items['+i+'][quantity_used]" required style="width:120px;"></td>'+
    '<td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
  document.getElementById(bodyId).appendChild(tr);
}
function rcToggle(id){ var e=document.getElementById(id); var open = e.style.display==='none'; e.style.display = open ? 'block':'none'; if(open && id==='addForm' && document.getElementById('addBody').children.length===0){ rcAddRow('addBody'); } }
</script>
</x-app-layout>