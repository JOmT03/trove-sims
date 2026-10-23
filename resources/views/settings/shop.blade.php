<x-app-layout>
<x-slot name="header">Receipt &amp; Terms</x-slot>
<x-slot name="subheader">Shop details and the terms printed on every order receipt</x-slot>

<style>
.ss-wrap{max-width:640px;}
.ss-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.ss-card{background:var(--white);border:1px solid var(--border);border-radius:16px;box-shadow:0 1px 6px rgba(74,44,23,.06);padding:20px;margin-bottom:16px;}
.ss-card h3{font-family:var(--f-display);font-size:16px;margin:0 0 14px;}
.fg{margin-bottom:14px;}
label{display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:6px;}
input,textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:9px;font-size:14px;background:var(--white);color:var(--text);font-family:var(--f-body);box-sizing:border-box;}
input:focus,textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(217,120,44,.14);}
textarea{min-height:150px;resize:vertical;line-height:1.6;}
.hint{font-size:11.5px;color:var(--muted);margin-top:6px;}
.btn-primary{background:var(--gold);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--f-body);}
</style>

<div class="ss-wrap">
  @if(session('success'))<div class="ss-ok">{{ session('success') }}</div>@endif

  <form method="POST" action="{{ route('settings.shop.update') }}">
    @csrf
    <div class="ss-card">
      <h3>Shop details</h3>
      <div class="fg"><label>Shop name</label><input type="text" name="shop_name" value="{{ old('shop_name', $shopName) }}"></div>
      <div class="fg"><label>Address</label><input type="text" name="shop_address" value="{{ old('shop_address', $shopAddress) }}" placeholder="e.g. Matina, Davao City"></div>
      <div class="fg"><label>Contact</label><input type="text" name="shop_contact" value="{{ old('shop_contact', $shopContact) }}" placeholder="e.g. 0917 000 0000 / fb.com/trove"></div>
    </div>

    <div class="ss-card">
      <h3>Terms &amp; Conditions</h3>
      <div class="fg">
        <label>Printed on every order receipt</label>
        <textarea name="order_terms" placeholder="One term per line...">{{ old('order_terms', $orderTerms) }}</textarea>
        <div class="hint">One line per term. These appear in the Terms &amp; Conditions section of the customer receipt.</div>
      </div>
    </div>

    <button type="submit" class="btn-primary">Save settings</button>
  </form>
</div>
</x-app-layout>