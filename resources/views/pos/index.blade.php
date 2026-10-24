<x-app-layout>
<x-slot name="header">POS</x-slot>
<x-slot name="subheader">Counter sale &mdash; walk-in</x-slot>

@php
    $grouped = $products->groupBy(function ($p) { return $p->category ?: 'Uncategorized'; });
    $grouped = $grouped->sortBy(function ($items, $k) { return $k === 'Uncategorized' ? 'zzzz' : strtolower($k); });
    $cats = $grouped->keys()->values();
@endphp

<style>
.pos-wrap{display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start;}
@media(max-width:1000px){.pos-wrap{grid-template-columns:1fr;}}
.pos-alert{padding:12px 16px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.pos-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.pos-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.pos-bar{display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;}
.pos-search{flex:1;min-width:180px;position:relative;}
.pos-search svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:17px;height:17px;stroke:var(--muted);fill:none;}
.pos-search input{width:100%;padding:11px 14px 11px 40px;border:1px solid var(--border);border-radius:999px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);}
.pos-search input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.pchips{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:6px;}
.pchip{border:1px solid var(--border);background:var(--white);color:var(--muted);border-radius:999px;padding:8px 18px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--f-body);}
.pchip.active{background:var(--gold);color:#fff;border-color:var(--gold);}
.cat-sec{margin-top:20px;}
.cat-head{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.cat-head h2{font-family:var(--f-display);font-weight:800;font-size:16px;color:#4A2C17;margin:0;white-space:nowrap;}
.cat-head .count{font-size:11px;font-weight:700;color:#B5651D;background:#F3DCC4;padding:3px 10px;border-radius:999px;}
.cat-head .line{flex:1;height:1px;background:var(--border);}
.pos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;}
.pcard{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:0;overflow:hidden;display:flex;flex-direction:column;text-align:left;cursor:pointer;font-family:var(--f-body);box-shadow:0 1px 6px rgba(74,44,23,.06);transition:transform .12s ease,box-shadow .12s ease,border-color .12s ease;}
.pcard:hover:not(.out){transform:translateY(-2px);box-shadow:0 6px 16px rgba(74,44,23,.12);border-color:var(--gold);}
.pcard .pph{height:96px;width:100%;background:linear-gradient(135deg,#F3DCC4,#E8C07A);display:flex;align-items:center;justify-content:center;overflow:hidden;}
.pcard .pph img{width:100%;height:100%;object-fit:cover;display:block;}
.pcard .pemo{font-size:34px;}
.pcard .pb{padding:11px 13px 13px;}
.pcard .pn{font-weight:700;color:var(--text);font-size:13.5px;line-height:1.3;}
.pcard .pp{font-family:var(--f-display);font-weight:800;font-size:19px;margin-top:7px;letter-spacing:-.5px;}
.pcard .ps{font-size:11px;color:var(--muted);margin-top:3px;}
.pcard.out{opacity:.55;cursor:not-allowed;}
.pcard.out .pph img{filter:grayscale(1);}
.pcard.out .ps{color:#C2410C;font-weight:700;}
.pos-none{display:none;text-align:center;color:var(--muted);padding:34px;font-size:14px;}

.cart{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);position:sticky;top:14px;overflow:hidden;}
.cart h3{font-family:var(--f-display);font-size:16px;padding:15px 16px;border-bottom:1px solid var(--border);margin:0;}
.cart-body{max-height:48vh;overflow:auto;}
.cart-empty{padding:34px 16px;text-align:center;color:var(--muted);font-size:13px;}
.cline{display:flex;align-items:center;gap:10px;padding:11px 16px;border-bottom:1px solid var(--border);}
.cline .ci{flex:1;min-width:0;}
.cline .cn{font-weight:700;font-size:13px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cline .cu{font-size:11px;color:var(--muted);margin-top:2px;}
.qbtn{width:26px;height:26px;border-radius:7px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-size:15px;line-height:1;cursor:pointer;display:grid;place-items:center;font-family:var(--f-body);}
.qbtn:hover{border-color:var(--gold);color:var(--gold);}
.qn{min-width:22px;text-align:center;font-weight:700;font-size:13px;font-variant-numeric:tabular-nums;}
.clt{font-weight:700;font-size:13px;font-variant-numeric:tabular-nums;min-width:66px;text-align:right;}
.crm{background:none;border:none;color:var(--muted);cursor:pointer;font-size:16px;line-height:1;padding:2px 4px;}
.crm:hover{color:#C2410C;}
.cart-foot{padding:14px 16px;border-top:1px solid var(--border);background:var(--bg);}
.cart-total{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:12px;}
.cart-total .l{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;font-weight:700;}
.cart-total .v{font-family:var(--f-display);font-size:28px;font-weight:800;letter-spacing:-1px;font-variant-numeric:tabular-nums;}
.btn-primary{width:100%;background:var(--gold);color:#fff;border:none;border-radius:10px;padding:13px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.btn-primary:disabled{opacity:.45;cursor:not-allowed;}
.recent{margin-top:14px;font-size:11.5px;color:var(--muted);}
.recent .rh{text-transform:uppercase;letter-spacing:.5px;font-weight:700;margin-bottom:6px;}
.recent .rr{display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dashed var(--border);}
.recent .rr.void{text-decoration:line-through;opacity:.6;}

.modal-bg{display:none;position:fixed;inset:0;background:rgba(46,28,16,.5);z-index:60;align-items:center;justify-content:center;padding:16px;}
.modal-bg.show{display:flex;}
.modal{background:var(--white);border-radius:18px;max-width:440px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);}
.modal h3{font-family:var(--f-display);font-size:18px;padding:18px 20px;margin:0;border-bottom:1px solid var(--border);}
.modal-body{padding:18px 20px;}
.mtotal{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--border);}
.mtotal .l{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;font-weight:700;}
.mtotal .v{font-family:var(--f-display);font-size:26px;font-weight:800;font-variant-numeric:tabular-nums;}
.prow{margin-bottom:12px;}
.prow .pl{display:flex;align-items:center;gap:9px;}
.prow label{font-size:13px;font-weight:700;color:var(--text);min-width:64px;}
.prow input,.prow select{flex:1;padding:9px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);font-variant-numeric:tabular-nums;}
.prow input:focus,.prow select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
.online-box{margin-top:9px;margin-left:73px;display:none;}
.online-box.show{display:block;}
.online-box input{width:100%;margin-bottom:7px;}
.online-box input:last-child{margin-bottom:0;}
.msum{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;}
.msum.rem .v{font-weight:800;}
.msum.rem.ok .v{color:#15803D;} .msum.rem.short .v{color:#C2410C;}
.mfoot{padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;}
.btn-ghost{flex:1;background:var(--bg);color:var(--text);border:1px solid var(--border);border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
.mfoot .btn-primary{flex:2;}
</style>

<div class="pos-wrap">
  <div>
    @if(session("success"))<div class="pos-alert ok">{{ session("success") }}</div>@endif
    @if(session("error"))<div class="pos-alert err">{{ session("error") }}</div>@endif

    <div class="pos-bar">
      <div class="pos-search">
        <svg stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
        <input type="text" id="posSearch" placeholder="Search products..." oninput="posFilter()">
      </div>
    </div>

    <div class="pchips" id="posChips">
      <button type="button" class="pchip active" data-cat="" onclick="posSetCat(this)">All</button>
      @foreach($cats as $c)<button type="button" class="pchip" data-cat="{{ $c }}" onclick="posSetCat(this)">{{ $c }}</button>@endforeach
    </div>

    <div id="posSections">
      @foreach($grouped as $catName => $items)
        <div class="cat-sec" data-cat="{{ $catName }}">
          <div class="cat-head"><h2>{{ $catName }}</h2><span class="count">{{ $items->count() }}</span><span class="line"></span></div>
          <div class="pos-grid">
            @foreach($items as $p)
              <button type="button"
                class="pcard {{ (float)$p->stock_quantity <= 0 ? 'out' : '' }}"
                data-id="{{ $p->id }}"
                data-name="{{ $p->product_name }}"
                data-price="{{ $p->price }}"
                data-stock="{{ (int)$p->stock_quantity }}"
                data-cat="{{ $catName }}"
                @if((float)$p->stock_quantity > 0) onclick="posAdd(this)" @else disabled @endif>
                <div class="pph">
                  @if($p->image_path)
                    <img src="{{ asset('storage/'.$p->image_path) }}" alt="{{ $p->product_name }}" loading="lazy">
                  @else
                    <span class="pemo">&#127856;</span>
                  @endif
                </div>
                <div class="pb">
                  <div class="pn">{{ $p->product_name }}</div>
                  <div class="pp">&#8369;{{ number_format($p->price, 2) }}</div>
                  <div class="ps">{{ (float)$p->stock_quantity <= 0 ? 'Out of stock' : $p->stock_quantity.' in stock' }}</div>
                </div>
              </button>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
    <div class="pos-none" id="posNone">No products match your search.</div>
  </div>

  <div>
    <div class="cart">
      <h3>Current Order</h3>
      <div class="cart-body" id="cartBody">
        <div class="cart-empty" id="cartEmpty">Tap a product to start an order.</div>
      </div>
      <div class="cart-foot">
        <div class="cart-total"><span class="l">Total</span><span class="v" id="cartTotal">&#8369;0.00</span></div>
        <button class="btn-primary" id="chargeBtn" onclick="posOpenPay()" disabled>Charge</button>
      </div>
    </div>

    @if($recent->count())
    <div class="recent">
      <div class="rh">Recent sales</div>
      @foreach($recent as $r)
        <div class="rr {{ $r->status === 'voided' ? 'void' : '' }}">
          <span>{{ $r->sale_number }}</span>
          <span>&#8369;{{ number_format($r->total, 2) }}</span>
        </div>
      @endforeach
    </div>
    @endif
  </div>
</div>

<!-- Payment modal -->
<div class="modal-bg" id="payBg">
  <div class="modal">
    <h3>Payment</h3>
    <form method="POST" action="{{ route('pos.sales.store') }}" id="posForm">
      @csrf
      <div class="modal-body">
        <div class="mtotal"><span class="l">Amount due</span><span class="v" id="payDue">&#8369;0.00</span></div>

        <div class="prow">
          <div class="pl"><label>Cash</label><div style="display:flex;gap:8px;align-items:center;"><input type="number" step="0.01" min="0" id="cashAmt" placeholder="0.00" oninput="posRecalc()" style="flex:1;"><button type="button" onclick="posExact()" style="padding:9px 14px;border:1px solid #D9782C;background:#FBF2E6;color:#8A3B0F;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;">Exact</button></div></div>
        </div>

        <div class="prow">
          <div class="pl">
            <label>Online</label>
            <select id="onlineMethod" onchange="posRecalc()">
              <option value="">E-wallet / Online Payment&hellip;</option>
              <option value="gcash">GCash</option>
              <option value="maya">Maya</option>
              <option value="bank">Bank transfer</option>
            </select>
          </div>
          <div class="online-box" id="onlineBox">
            <input type="number" step="0.01" min="0" id="onlineAmt" placeholder="Amount received" oninput="posRecalc()">
            <input type="text" id="onlineRef" maxlength="100" placeholder="Reference no." oninput="posRecalc()">
          </div>
        </div>

        <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);">
          <div class="msum"><span>Paid</span><span class="v" id="paySum">&#8369;0.00</span></div>
          <div class="msum rem" id="payRemRow"><span id="payRemLbl">Remaining</span><span class="v" id="payRem">&#8369;0.00</span></div>
        </div>
      </div>
      <div class="mfoot">
        <button type="button" class="btn-ghost" onclick="posClosePay()">Back</button>
        <button type="submit" class="btn-primary" id="payConfirm" disabled>Complete Sale</button>
      </div>
      <div id="posHidden"></div>
    </form>
  </div>
</div>

<script>
const cart = new Map(); // id -> {id,name,price,stock,qty}
var posCurCat = "";

function peso(n){ return "\u20B1" + Number(n).toLocaleString("en-PH",{minimumFractionDigits:2,maximumFractionDigits:2}); }
function cartTotal(){ let t=0; cart.forEach(i=> t += i.price*i.qty); return Math.round(t*100)/100; }

function posAdd(btn){
  const id = btn.dataset.id;
  const stock = parseInt(btn.dataset.stock,10);
  const cur = cart.get(id);
  if (cur){ if (cur.qty < stock) cur.qty++; }
  else { cart.set(id, {id, name:btn.dataset.name, price:parseFloat(btn.dataset.price), stock, qty:1}); }
  renderCart();
}
function posSet(id, d){
  const i = cart.get(id); if(!i) return;
  i.qty += d;
  if (i.qty <= 0) cart.delete(id);
  else if (i.qty > i.stock) i.qty = i.stock;
  renderCart();
}
function posRemove(id){ cart.delete(id); renderCart(); }

function renderCart(){
  const body = document.getElementById("cartBody");
  const empty = document.getElementById("cartEmpty");
  body.querySelectorAll(".cline").forEach(e=>e.remove());
  if (cart.size === 0){ empty.style.display="block"; }
  else {
    empty.style.display="none";
    cart.forEach(i=>{
      const row = document.createElement("div");
      row.className="cline";
      row.innerHTML =
        '<div class="ci"><div class="cn">'+i.name+'</div><div class="cu">'+peso(i.price)+' each</div></div>'+
        '<button type="button" class="qbtn" onclick="posSet(\''+i.id+'\',-1)">&minus;</button>'+
        '<span class="qn">'+i.qty+'</span>'+
        '<button type="button" class="qbtn" onclick="posSet(\''+i.id+'\',1)">+</button>'+
        '<span class="clt">'+peso(i.price*i.qty)+'</span>'+
        '<button type="button" class="crm" title="Remove" onclick="posRemove(\''+i.id+'\')">&times;</button>';
      body.appendChild(row);
    });
  }
  document.getElementById("cartTotal").textContent = peso(cartTotal());
  document.getElementById("chargeBtn").disabled = cart.size === 0;
}

function posSetCat(btn){
  document.querySelectorAll("#posChips .pchip").forEach(c=>c.classList.remove("active"));
  btn.classList.add("active");
  posCurCat = btn.dataset.cat || "";
  posFilter();
}
function posFilter(){
  const q = document.getElementById("posSearch").value.toLowerCase().trim();
  let shown = 0;
  document.querySelectorAll("#posSections .cat-sec").forEach(sec=>{
    const catOk = !posCurCat || sec.dataset.cat === posCurCat;
    let n = 0;
    sec.querySelectorAll(".pcard").forEach(c=>{
      const show = catOk && c.dataset.name.toLowerCase().includes(q);
      c.style.display = show ? "" : "none";
      if (show) { n++; shown++; }
    });
    sec.style.display = n ? "" : "none";
  });
  document.getElementById("posNone").style.display = shown ? "none" : "block";
}

/* ----- payment modal ----- */
function posOpenPay(){
  if (cart.size === 0) return;
  document.getElementById("payDue").textContent = peso(cartTotal());
  document.getElementById("cashAmt").value = "";
  document.getElementById("onlineMethod").value = "";
  document.getElementById("onlineAmt").value = "";
  document.getElementById("onlineRef").value = "";
  posRecalc();
  document.getElementById("payBg").classList.add("show");
}
function posClosePay(){ document.getElementById("payBg").classList.remove("show"); }

function posExact(){
  const total  = cartTotal();
  const method = document.getElementById("onlineMethod").value;
  const oAmt   = method ? (parseFloat(document.getElementById("onlineAmt").value) || 0) : 0;
  const due    = Math.max(0, Math.round((total - oAmt) * 100) / 100);
  document.getElementById("cashAmt").value = due.toFixed(2);
  posRecalc();
}

function posRecalc(){
  const total  = cartTotal();
  const cash   = parseFloat(document.getElementById("cashAmt").value) || 0;
  const method = document.getElementById("onlineMethod").value;
  const oAmt   = parseFloat(document.getElementById("onlineAmt").value) || 0;
  const oRef   = document.getElementById("onlineRef").value.trim();

  const box = document.getElementById("onlineBox");
  if (method) box.classList.add("show"); else box.classList.remove("show");

  const paid = Math.round((cash + (method ? oAmt : 0)) * 100) / 100;
  const rem  = Math.round((total - paid) * 100) / 100;

  document.getElementById("paySum").textContent = peso(paid);
  const remRow = document.getElementById("payRemRow");
  if (rem > 0.009){
    document.getElementById("payRemLbl").textContent = "Remaining";
    document.getElementById("payRem").textContent = peso(rem);
    remRow.classList.add("short"); remRow.classList.remove("ok");
  } else {
    document.getElementById("payRemLbl").textContent = rem < -0.009 ? "Change (give manually)" : "Fully paid";
    document.getElementById("payRem").textContent = peso(Math.abs(rem));
    remRow.classList.add("ok"); remRow.classList.remove("short");
  }

  const refMissing = method && oAmt > 0 && !oRef;
  document.getElementById("payConfirm").disabled = (paid + 0.009 < total) || refMissing || cart.size === 0;
}

document.getElementById("posForm").addEventListener("submit", function(e){
  const box = document.getElementById("posHidden"); box.innerHTML = "";
  let n = 0;
  cart.forEach(i=>{
    box.insertAdjacentHTML("beforeend",
      '<input type="hidden" name="items['+n+'][product_id]" value="'+i.id+'">'+
      '<input type="hidden" name="items['+n+'][quantity]" value="'+i.qty+'">');
    n++;
  });
  let m = 0;
  const cash = parseFloat(document.getElementById("cashAmt").value) || 0;
  if (cash > 0){
    box.insertAdjacentHTML("beforeend",
      '<input type="hidden" name="payments['+m+'][method]" value="cash">'+
      '<input type="hidden" name="payments['+m+'][amount]" value="'+cash.toFixed(2)+'">'+
      '<input type="hidden" name="payments['+m+'][reference]" value="">');
    m++;
  }
  const method = document.getElementById("onlineMethod").value;
  const oAmt   = parseFloat(document.getElementById("onlineAmt").value) || 0;
  const oRef   = document.getElementById("onlineRef").value.trim();
  if (method && oAmt > 0){
    box.insertAdjacentHTML("beforeend",
      '<input type="hidden" name="payments['+m+'][method]" value="'+method+'">'+
      '<input type="hidden" name="payments['+m+'][amount]" value="'+oAmt.toFixed(2)+'">'+
      '<input type="hidden" name="payments['+m+'][reference]" value="'+oRef+'">');
    m++;
  }
});
</script>
</x-app-layout>
