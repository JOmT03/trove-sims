<x-app-layout>
<x-slot name="header">New Custom Order</x-slot>
<x-slot name="subheader">Made-to-order cake &middot; add catalog items as add-ons</x-slot>

@php
    $productData = $products->map(fn($p) => ['id'=>$p->id,'name'=>$p->product_name,'price'=>(float)$p->price,'stock'=>(int)$p->stock_quantity]);
@endphp

<style>
.ord-wrap{max-width:720px;}
.ord-alert{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.ord-alert ul{margin:6px 0 0 18px;}
.sec{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);padding:18px 20px;margin-bottom:16px;}
.sec-h{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:14px;}
.sec-h h3{font-family:var(--f-display);font-size:16px;margin:0;}
.sec-h .note{font-size:11.5px;color:var(--muted);}
.fg{margin-bottom:14px;}
.fg:last-child{margin-bottom:0;}
.fg2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
label{display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:6px;}
input,select,textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);box-sizing:border-box;}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
textarea{min-height:66px;resize:vertical;}
.seg{display:inline-flex;background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:3px;gap:3px;}
.seg button{background:none;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--f-body);}
.seg button.on{background:var(--white);color:var(--gold);box-shadow:0 1px 3px rgba(0,0,0,.08);}
.stocknote{font-size:11.5px;color:var(--muted);line-height:1.5;}
.stocknote b{color:var(--text);}
.photo-drop{border:1.5px dashed var(--border);border-radius:11px;background:var(--bg);padding:16px;text-align:center;cursor:pointer;font-size:13px;color:var(--muted);}
.photo-drop:hover{border-color:var(--gold);color:var(--gold);}
.photo-prev{margin-top:10px;display:none;}
.photo-prev img{max-width:100%;max-height:220px;border-radius:10px;border:1px solid var(--border);}
.photo-prev .rmv{margin-top:6px;background:none;border:none;color:#C2410C;font-size:12px;font-weight:700;cursor:pointer;}
.addon{display:grid;grid-template-columns:1fr 70px 96px 32px;gap:9px;align-items:center;margin-bottom:9px;}
.addon .lt{font-weight:700;font-size:13px;text-align:right;font-variant-numeric:tabular-nums;}
.addon .x{background:none;border:none;color:var(--muted);font-size:17px;cursor:pointer;}
.addon .x:hover{color:#C2410C;}
.addon-head{display:grid;grid-template-columns:1fr 70px 96px 32px;gap:9px;font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);font-weight:700;margin-bottom:6px;padding:0 2px;}
.addrow{width:100%;padding:10px;border:1.5px dashed var(--border);border-radius:9px;background:var(--bg);color:var(--muted);font-size:13px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.addrow:hover{border-color:var(--gold);color:var(--gold);}
.sumrow{display:flex;justify-content:space-between;align-items:center;padding:7px 0;font-size:14px;}
.sumrow .lbl{color:var(--text);}
.sumrow .val{font-weight:700;font-variant-numeric:tabular-nums;}
.sumrow.grand{border-top:1px solid var(--border);margin-top:6px;padding-top:12px;}
.sumrow.grand .lbl{font-weight:800;}
.sumrow.grand .val{font-family:var(--f-display);font-size:22px;font-weight:800;color:var(--gold);}
.sumrow.bal .val{color:#166534;font-weight:800;}
.saveas{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);margin-top:4px;}
.saveas .pill{background:var(--bg);border:1px solid var(--border);color:var(--gold);font-weight:800;font-size:11px;padding:3px 10px;border-radius:999px;}
.actions{display:flex;gap:10px;}
.btn-primary{flex:1;background:var(--gold);color:#fff;border:none;border-radius:10px;padding:13px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.btn-ghost{background:var(--white);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:13px 22px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
</style>

<div class="ord-wrap">
  @if($errors->any())
    <div class="ord-alert"><strong>Please fix:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  @if(session('error'))<div class="ord-alert">{{ session('error') }}</div>@endif

  <form method="POST" action="{{ route('commissions.store') }}" enctype="multipart/form-data" id="ordForm">
    @csrf
    <input type="hidden" name="client_type" id="clientType" value="individual">

    <div class="sec">
      <div class="sec-h"><h3>Customer</h3></div>
      <div class="fg2">
        <div class="fg">
          <label>Customer name <span style="color:#C2410C">*</span></label>
          <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="e.g. Dela Cruz family" required>
        </div>
        <div class="fg">
          <label>Client type</label>
          <div class="seg" id="ctSeg">
            <button type="button" class="on" data-ct="individual" onclick="setCt(this)">Individual</button>
            <button type="button" data-ct="institutional" onclick="setCt(this)">Institutional</button>
          </div>
        </div>
      </div>
      <div class="fg">
        <label>Needed by</label>
        <input type="date" name="needed_by_date" value="{{ old('needed_by_date') }}">
      </div>
    </div>

    <div class="sec">
      <div class="sec-h"><h3>Custom cake</h3><span class="note">made to order</span></div>
      <div class="fg">
        <label>Design description</label>
        <textarea name="design_description" placeholder="Tiers, colors, theme, message on top, flavor, fondant...">{{ old('design_description') }}</textarea>
      </div>
      <div class="fg2">
        <div class="fg">
          <label>Custom cake price (&#8369;)</label>
          <input type="number" name="custom_amount" id="customAmt" step="0.01" min="0" value="{{ old('custom_amount', 0) }}" oninput="recalc()">
        </div>
        <div class="fg">
          <label>Stock effect</label>
          <p class="stocknote"><b>No finished-stock change.</b> Raw materials leave inventory (FIFO) when marked <b>Baking</b>.</p>
        </div>
      </div>
      <div class="fg">
        <label>Reference photo <span style="color:var(--muted);font-weight:500;">(optional &mdash; staff or customer can attach the design they want)</span></label>
        <div class="photo-drop" id="photoDrop" onclick="document.getElementById('photoInput').click()">
          &#128247;&nbsp; Click to upload a reference image (JPG/PNG, max 4MB)
        </div>
        <input type="file" name="reference_photo" id="photoInput" accept="image/*" style="display:none" onchange="previewPhoto(this)">
        <div class="photo-prev" id="photoPrev">
          <img id="photoImg" alt="reference preview">
          <div><button type="button" class="rmv" onclick="clearPhoto()">Remove photo</button></div>
        </div>
      </div>
    </div>

    <div class="sec">
      <div class="sec-h"><h3>Add-ons</h3><span class="note">catalog items &middot; deduct stock</span></div>
      <div class="addon-head"><span>Add-on</span><span style="text-align:center">Qty</span><span style="text-align:right">Price</span><span></span></div>
      <div id="addonRows"></div>
      <button type="button" class="addrow" onclick="addRow()">+ Add add-on</button>
    </div>

    <div class="sec">
      <div class="sec-h"><h3>Payment summary</h3></div>
      <div class="sumrow"><span class="lbl">Custom cake</span><span class="val">&#8369;<span id="sCustom">0.00</span></span></div>
      <div class="sumrow"><span class="lbl">Add-ons</span><span class="val">&#8369;<span id="sAddons">0.00</span></span></div>
      <div class="sumrow grand"><span class="lbl">Total</span><span class="val">&#8369;<span id="sTotal">0.00</span></span></div>
      <div class="fg" style="margin-top:12px;">
        <label>Downpayment (&#8369;)</label>
        <input type="number" name="deposit_amount" id="deposit" step="0.01" min="0" value="{{ old('deposit_amount', 0) }}" oninput="recalc()">
      </div>
      <div class="sumrow bal"><span class="lbl">Balance due</span><span class="val">&#8369;<span id="sBalance">0.00</span></span></div>
      <div class="saveas">Order will be saved as <span class="pill" id="statusPill">Inquiry</span></div>
    </div>

    <div class="actions">
      <button type="submit" class="btn-primary">Save order</button>
      <button type="reset" class="btn-ghost" onclick="setTimeout(resetAll,0)">Reset</button>
    </div>
  </form>
</div>

<script>
const PRODUCTS = @json($productData);
let rowId = 0;

function setCt(btn){
  document.querySelectorAll('#ctSeg button').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('clientType').value = btn.dataset.ct;
}

function money(n){ return Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function optionsHtml(){
  let o = '<option value="">&mdash; Select product &mdash;</option>';
  PRODUCTS.forEach(p=>{ o += '<option value="'+p.id+'" data-price="'+p.price+'">'+p.name+' &mdash; &#8369;'+money(p.price)+'</option>'; });
  return o;
}

function addRow(){
  const i = rowId++;
  const div = document.createElement('div');
  div.className = 'addon';
  div.id = 'row'+i;
  div.innerHTML =
    '<select name="items['+i+'][product_id]" onchange="pick(this,'+i+')">'+optionsHtml()+'</select>'+
    '<input type="number" name="items['+i+'][quantity]" value="1" min="1" style="text-align:center" oninput="recalc()">'+
    '<input type="number" name="items['+i+'][price]" value="0" min="0" step="0.01" class="aprice" oninput="recalc()">'+
    '<button type="button" class="x" onclick="document.getElementById(\'row'+i+'\').remove();recalc();">&times;</button>';
  document.getElementById('addonRows').appendChild(div);
}

function pick(sel, i){
  const opt = sel.options[sel.selectedIndex];
  const price = opt ? parseFloat(opt.dataset.price||0) : 0;
  document.querySelector('#row'+i+' .aprice').value = price.toFixed(2);
  recalc();
}

function recalc(){
  const custom = parseFloat(document.getElementById('customAmt').value)||0;
  let addons = 0;
  document.querySelectorAll('#addonRows .addon').forEach(r=>{
    const q = parseFloat(r.querySelector('[name$="[quantity]"]').value)||0;
    const p = parseFloat(r.querySelector('.aprice').value)||0;
    addons += q*p;
  });
  const total = custom + addons;
  const dep = parseFloat(document.getElementById('deposit').value)||0;
  document.getElementById('sCustom').textContent  = money(custom);
  document.getElementById('sAddons').textContent  = money(addons);
  document.getElementById('sTotal').textContent   = money(total);
  document.getElementById('sBalance').textContent = money(Math.max(0, total - dep));
  document.getElementById('statusPill').textContent = dep > 0 ? 'Confirmed' : 'Inquiry';
}

function previewPhoto(inp){
  if(!inp.files || !inp.files[0]) return;
  const r = new FileReader();
  r.onload = e => {
    document.getElementById('photoImg').src = e.target.result;
    document.getElementById('photoPrev').style.display = 'block';
    document.getElementById('photoDrop').style.display = 'none';
  };
  r.readAsDataURL(inp.files[0]);
}
function clearPhoto(){
  document.getElementById('photoInput').value = '';
  document.getElementById('photoPrev').style.display = 'none';
  document.getElementById('photoDrop').style.display = 'block';
}
function resetAll(){
  document.getElementById('addonRows').innerHTML = '';
  clearPhoto(); setCt(document.querySelector('#ctSeg button')); recalc();
}

addRow();
recalc();
</script>
</x-app-layout>