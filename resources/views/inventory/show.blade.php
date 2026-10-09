<x-app-layout>
<x-slot name="header">{{ $inventory->item_name }}</x-slot>
<x-slot name="subheader">{{ $inventory->category }} &middot; {{ $inventory->unit }}</x-slot>

<style>
.iv-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.iv-lowban{background:#FBEBD6;border:1px solid #f5d9ad;color:#B45309;padding:10px 15px;border-radius:9px;margin-bottom:16px;font-size:13px;font-weight:600;}
.iv-topbtns{display:flex;justify-content:flex-end;gap:9px;margin-bottom:18px;flex-wrap:wrap;}
.iv-btn{display:inline-flex;align-items:center;gap:7px;border-radius:9px;padding:10px 18px;font-weight:700;font-size:13px;cursor:pointer;text-decoration:none;border:1px solid transparent;font-family:inherit;}
.iv-btn.gold{background:#D9782C;color:#fff;}
.iv-btn.out{background:#fff;color:#4A2C17;border:1px solid #EBDCCA;}
.iv-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.iv-sc{background:#fff;border:1px solid #EBDCCA;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:15px 17px;}
.iv-sc .k{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#8A7460;}
.iv-sc .n{font-family:'Bricolage Grotesque',system-ui,sans-serif;font-weight:800;font-size:25px;line-height:1.1;margin-top:6px;}
.iv-sc .u{font-size:11px;color:#8A7460;margin-top:2px;}
.iv-sc.dmg .n{color:#C2410C;}.iv-sc.use .n{color:#15803D;}.iv-sc.min .n{color:#B45309;}
.iv-card{background:#fff;border:1px solid #EBDCCA;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);margin-bottom:18px;overflow:hidden;}
.iv-card h3{font-family:'Bricolage Grotesque',system-ui,sans-serif;font-weight:800;font-size:15px;margin:0;padding:16px 20px;border-bottom:1px solid #EDE0D0;}
.iv-adjust{display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;padding:18px 20px;}
.iv-adjust .fld label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;}
.iv-adjust select,.iv-adjust input{padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;background:#f8fafc;font-family:inherit;}
.iv-adjust .notes{flex:1;min-width:180px;}.iv-adjust .notes input{width:100%;}
.iv-card table{width:100%;border-collapse:collapse;font-size:13px;}
.iv-card thead th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:#8A7460;background:#FDF6EC;padding:10px 16px;font-weight:700;}
.iv-card tbody td{padding:11px 16px;border-top:1px solid #f3f4f6;color:#4A2C17;}
.pill{display:inline-block;font-size:10px;font-weight:700;padding:3px 9px;border-radius:999px;text-transform:uppercase;}
.pill.used{background:#E0ECFB;color:#1D4ED8;}.pill.damaged{background:#FBE0E0;color:#991B1B;}
.pill.adjustment{background:#EDE7DF;color:#6b5a48;}.pill.received{background:#E7F3EA;color:#15803D;}
.qpos{color:#15803D;font-weight:800;}.qneg{color:#C2410C;font-weight:800;}
.muted{color:#8A7460;}
.iv-empty{padding:30px 16px;text-align:center;color:#8A7460;}
@media (max-width:640px){.iv-summary{grid-template-columns:repeat(2,1fr);}}
</style>

@php
  $on = (float) $inventory->quantity_on_hand;
  $dmg = (float) $inventory->quantity_damaged;
  $usable = max(0, $on - $dmg);
  $fmt = function ($x) { return rtrim(rtrim(number_format((float)$x, 2), '0'), '.'); };
@endphp

@if(session('success'))<div class="iv-ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="iv-lowban" style="background:#FBE0E0;border-color:#fecaca;color:#991b1b;">{{ session('error') }}</div>@endif
@if($inventory->isLowStock())<div class="iv-lowban">&#9888; This item is at or below its minimum stock level.</div>@endif

<div class="iv-topbtns">
  <a href="{{ route('inventory.index') }}" class="iv-btn out">&larr; Back to Inventory</a>
  @can('admin')<a href="{{ route('inventory.edit', $inventory) }}" class="iv-btn gold">Edit Item</a>@endcan
</div>

<div class="iv-summary">
  <div class="iv-sc"><div class="k">On Hand</div><div class="n">{{ $fmt($on) }}</div><div class="u">{{ $inventory->unit }}</div></div>
  <div class="iv-sc dmg"><div class="k">Damaged</div><div class="n">{{ $fmt($dmg) }}</div><div class="u">{{ $inventory->unit }}</div></div>
  <div class="iv-sc use"><div class="k">Usable</div><div class="n">{{ $fmt($usable) }}</div><div class="u">{{ $inventory->unit }}</div></div>
  <div class="iv-sc min"><div class="k">Min. Stock</div><div class="n">{{ $fmt($inventory->minimum_stock) }}</div><div class="u">{{ $inventory->unit }}</div></div>
</div>

@can('admin')
<div class="iv-card">
  <h3>Adjust Inventory</h3>
  <form class="iv-adjust" action="{{ route('inventory.adjust', $inventory) }}" method="POST">
    @csrf @method('PATCH')
    <div class="fld">
      <label>Type *</label>
      <select name="type" required>
        <option value="used">Used / Consumed</option>
        <option value="damaged">Report Damaged</option>
        <option value="adjustment">Manual Adjustment (+)</option>
      </select>
    </div>
    <div class="fld">
      <label>Quantity *</label>
      <input type="number" name="quantity" min="0.01" step="0.01" required placeholder="0" style="width:120px;">
    </div>
    <div class="fld notes">
      <label>Notes</label>
      <input type="text" name="notes" placeholder="Reason for adjustment...">
    </div>
    <button type="submit" class="iv-btn gold">Apply</button>
  </form>
</div>
@endcan

<div class="iv-card">
  <h3>Movement History</h3>
  <table>
    <thead><tr><th>Date</th><th>Type</th><th>Quantity</th><th>Reference</th><th>Notes</th><th>By</th></tr></thead>
    <tbody>
      @forelse($inventory->logs->sortByDesc('created_at') as $log)
        @php $neg = in_array($log->type, ['used','damaged']); @endphp
        <tr>
          <td class="muted">{{ $log->created_at->format('M d, Y g:i A') }}</td>
          <td><span class="pill {{ $log->type }}">{{ ucfirst($log->type) }}</span></td>
          <td class="{{ $neg ? 'qneg' : 'qpos' }}">{{ $neg ? '-' : '+' }}{{ $fmt($log->quantity) }} {{ $inventory->unit }}</td>
          <td class="muted">{{ $log->reference ?? '-' }}</td>
          <td class="muted">{{ $log->notes ?? '-' }}</td>
          <td class="muted">{{ $log->user?->name ?? '-' }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="iv-empty">No movement recorded yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
</x-app-layout>