# ============================================================
#  TROVE - Fix inventory View (show) + add editable quantity to Edit
#     powershell -ExecutionPolicy Bypass -File setup-inventory-view-editqty.ps1
#  1) Rewrites inventory/show.blade.php (works now, matches app, @can('admin'))
#  2) Adds "Current Quantity (On Hand)" to inventory/edit.blade.php
#  3) InventoryController@update accepts quantity_on_hand + logs an adjustment
#  Backs up each original to .bak.
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function BackupOnce($p){ $b=$p+".bak"; if((Test-Path $p) -and -not(Test-Path $b)){ Copy-Item $p $b } }
Write-Host "Fixing inventory View + editable quantity..." -ForegroundColor Yellow

# ---------- 1. show.blade.php ----------
$showPath = Join-Path $root "resources\views\inventory\show.blade.php"
BackupOnce $showPath
$show = @'
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
'@
[System.IO.File]::WriteAllText($showPath, $show, $Utf8NoBom)
Write-Host "  wrote inventory/show.blade.php (View fixed + redesigned)" -ForegroundColor Green

# ---------- 2. edit.blade.php ----------
$editPath = Join-Path $root "resources\views\inventory\edit.blade.php"
BackupOnce $editPath
$edit = @'
<x-app-layout>
<x-slot name="header">Edit Inventory Item</x-slot>
<x-slot name="subheader">{{ $inventory->item_name }}</x-slot>

<style>
.card{background:#fff;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:24px;max-width:680px;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;}
input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;background:#f8fafc;font-family:inherit;}
input:focus,select:focus{border-color:#D9782C;background:#fff;outline:none;}
.btn{padding:10px 20px;border-radius:9px;font-size:13px;font-weight:700;border:none;cursor:pointer;text-decoration:none;}
.btn-gold{background:#D9782C;color:#fff;}
.btn-outline{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;}
.hint{font-size:11.5px;color:#8A7460;margin-top:6px;}
</style>

@if($errors->any())
<div style="background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:9px;margin-bottom:16px;max-width:680px;font-size:13px;">
<strong>Please fix:</strong><ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('inventory.update', $inventory) }}" class="card">
@csrf
@method('PUT')
<div class="form-grid">
    <div style="grid-column:1/-1;">
        <label>Item Name</label>
        <input type="text" name="item_name" value="{{ old('item_name', $inventory->item_name) }}" required>
    </div>
    <div>
        <label>Category</label>
        <select name="category" required>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $inventory->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Unit</label>
        <select name="unit" required>
            @foreach($units as $u)
                <option value="{{ $u }}" {{ $inventory->unit === $u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Current Quantity (On Hand)</label>
        <input type="number" name="quantity_on_hand" step="0.01" min="0" value="{{ old('quantity_on_hand', $inventory->quantity_on_hand) }}" required>
        <div class="hint">Correcting this logs an adjustment in the item's history.</div>
    </div>
    <div>
        <label>Minimum Stock</label>
        <input type="number" name="minimum_stock" step="0.01" min="0" value="{{ old('minimum_stock', $inventory->minimum_stock) }}" required>
    </div>
    <div style="grid-column:1/-1;">
        <label>Site</label>
        <select name="site_id">
            <option value="">- Unassigned -</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}" {{ $inventory->site_id == $site->id ? 'selected' : '' }}>{{ $site->site_name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
    <a href="{{ route('inventory.index') }}" class="btn btn-outline">Cancel</a>
    <button type="submit" class="btn btn-gold">Save Changes</button>
</div>
</form>
</x-app-layout>
'@
[System.IO.File]::WriteAllText($editPath, $edit, $Utf8NoBom)
Write-Host "  wrote inventory/edit.blade.php (+ Current Quantity field)" -ForegroundColor Green

# ---------- 3. InventoryController@update ----------
$ctrlPath = Join-Path $root "app\Http\Controllers\InventoryController.php"
BackupOnce $ctrlPath
$c = [System.IO.File]::ReadAllText($ctrlPath)
$c = $c -replace "`r`n", "`n"

$oldA = @'
            'minimum_stock' => 'required|numeric|min:0',
            'site_id'       => 'nullable|exists:sites,id',
'@ -replace "`r`n", "`n"
$newA = @'
            'quantity_on_hand' => 'required|numeric|min:0',
            'minimum_stock'    => 'required|numeric|min:0',
            'site_id'          => 'nullable|exists:sites,id',
'@ -replace "`r`n", "`n"

$oldB = @'
        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
'@ -replace "`r`n", "`n"
$newB = @'
        $oldQty = (float) $inventory->quantity_on_hand;
        $newQty = isset($validated['quantity_on_hand']) ? (float) $validated['quantity_on_hand'] : $oldQty;

        $inventory->update($validated);

        if ($oldQty != $newQty) {
            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'type'         => 'adjustment',
                'quantity'     => abs($newQty - $oldQty),
                'reference'    => 'EDIT-CORRECTION',
                'notes'        => 'Quantity corrected via Edit: ' . $oldQty . ' -> ' . $newQty,
                'user_id'      => auth()->id(),
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
'@ -replace "`r`n", "`n"

$okA = $false; $okB = $false
if ($c.Contains($oldA)) { $c = $c.Replace($oldA, $newA); $okA = $true }
if ($c.Contains($oldB)) { $c = $c.Replace($oldB, $newB); $okB = $true }

if ($okA -and $okB) {
    [System.IO.File]::WriteAllText($ctrlPath, $c, $Utf8NoBom)
    Write-Host "  InventoryController@update: now saves quantity_on_hand + logs the change" -ForegroundColor Green
} else {
    Write-Host "  WARNING: could not patch update() (anchors not found). Controller left unchanged." -ForegroundColor Yellow
    Write-Host "    okA(validation)=$okA  okB(logging)=$okB  - tell me and I'll patch it another way." -ForegroundColor Yellow
}

# ---------- 4. Clear caches ----------
Write-Host ""
php artisan view:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE." -ForegroundColor Cyan
Write-Host "  View: Inventory -> item kebab -> View (no more error)." -ForegroundColor White
Write-Host "  Edit: Inventory -> item kebab -> Edit -> change 'Current Quantity' -> Save." -ForegroundColor White
