<x-app-layout>
<x-slot name="header">Settings</x-slot>
<x-slot name="subheader">Manage your account, security, and help</x-slot>

<style>
.set-wrap{max-width:960px;margin:0 auto;display:grid;grid-template-columns:210px 1fr;gap:22px;align-items:start;}
.set-rail{display:flex;flex-direction:column;gap:4px;}
.set-rail button{display:flex;align-items:center;gap:10px;text-align:left;border:none;background:none;font-family:var(--f-body);font-size:13.5px;font-weight:600;color:var(--text);padding:11px 13px;border-radius:10px;cursor:pointer;width:100%;}
.set-rail button:hover{background:#FDF6EC;}
.set-rail button.active{background:var(--gold);color:#fff;}
.set-rail svg{width:17px;height:17px;flex-shrink:0;}
.set-rail .ownertag{margin-left:auto;font-size:9px;font-weight:700;letter-spacing:.3px;color:var(--gold);background:#FDF6EC;border-radius:5px;padding:2px 6px;text-transform:uppercase;}
.set-rail button.active .ownertag{color:#fff;background:rgba(255,255,255,.22);}
.set-panel{background:#fff;border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.06);}
.set-h2{font-family:var(--f-display);font-weight:800;font-size:17px;margin:0 0 3px;color:var(--text);}
.set-sub{font-size:12.5px;color:var(--muted);margin:0 0 18px;}
.set-bk-info{font-size:13px;color:var(--text);background:#FDF6EC;border:1px solid var(--border);border-radius:11px;padding:13px 15px;line-height:1.5;}
.set-btn{display:inline-flex;align-items:center;gap:8px;border:none;background:var(--gold);color:#fff;font-family:var(--f-body);font-weight:700;font-size:13.5px;padding:11px 18px;border-radius:10px;cursor:pointer;text-decoration:none;}
.set-btn:hover{background:#B5651D;}
.set-btn.ghost{background:#fff;color:var(--text);border:1px solid var(--border);}
.set-card2{display:flex;gap:16px;align-items:center;background:#FDF6EC;border:1px solid var(--border);border-radius:13px;padding:17px;}
.set-ico{width:52px;height:64px;flex-shrink:0;border-radius:8px;background:linear-gradient(150deg,#D9782C,#B5651D);display:grid;place-items:center;color:#fff;}
.set-warn{background:#FBE7E7;border-left:3px solid #C62828;border-radius:11px;padding:13px 15px;font-size:12.5px;color:#5c2a22;line-height:1.5;}
.set-tip{background:#F3DCC4;border-left:3px solid #D9782C;border-radius:11px;padding:13px 15px;font-size:12.5px;line-height:1.5;color:var(--text);}
.set-chips{display:flex;flex-wrap:wrap;gap:6px;}
.set-chip{font-size:11px;font-weight:600;color:var(--text);background:#fff;border:1px solid var(--border);border-radius:999px;padding:3px 10px;}
.set-ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.set-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:11px 15px;border-radius:9px;margin-bottom:14px;font-size:13px;}
.set-inside-h{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
@media(max-width:760px){.set-wrap{grid-template-columns:1fr;}.set-rail{flex-direction:row;overflow-x:auto;}}
</style>

<div class="set-wrap">
  <nav class="set-rail" id="setRail">
    <button data-t="profile" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>Profile</button>
    <button data-t="security"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Security</button>
    @if(auth()->user()->role === 'Owner')
    <button data-t="backup"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v12c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>Backup &amp; Restore<span class="ownertag">Owner</span></button>
    @endif
    <button data-t="help"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 .3c0 1.7-2.5 2-2.5 3.7M12 17h.01"/></svg>Help &amp; User Manual</button>
  </nav>

  <div>
    <section class="set-panel" data-p="profile">
      @include('profile.partials.update-profile-information-form')
    </section>

    <section class="set-panel" data-p="security" hidden>
      @include('profile.partials.update-password-form')
    </section>

    @if(auth()->user()->role === 'Owner')
    <section class="set-panel" data-p="backup" hidden>
      <h2 class="set-h2">Backup &amp; Restore</h2>
      <p class="set-sub">Keep a safe copy of your data in case anything happens to this computer.</p>
      @if(session('success'))<div class="set-ok">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="set-err">{{ session('error') }}</div>@endif
      <div class="set-bk-info">A backup includes <strong>all your records</strong> (products, inventory, orders, batches, expenses, users) and your <strong>product photos</strong>, saved as a single .zip file.</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:16px 0 18px;">
        <a href="{{ route('settings.backup.download') }}" class="set-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>Download Backup</a>
        <form method="POST" action="{{ route('settings.backup.restore') }}" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" onsubmit="return confirm('Restoring will OVERWRITE the current data with the backup. Continue?');">
          @csrf
          <input type="file" name="backup" accept=".zip,.sql" required style="font-size:12px;">
          <button type="submit" class="set-btn ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21V9m0 0 4 4m-4-4-4 4M4 3h16"/></svg>Restore</button>
        </form>
      </div>
      <div class="set-tip" style="margin-bottom:12px;"><strong>KEEP IT SAFE:</strong> After downloading, save a copy to a USB drive or Google Drive. Back up weekly, or right after a big change like a reconciliation.</div>
      <div class="set-warn"><strong>RESTORING REPLACES CURRENT DATA.</strong> Only restore to recover from data loss. You may be signed out afterward.</div>
    </section>
    @endif

    <section class="set-panel" data-p="help" hidden>
      <h2 class="set-h2">Help &amp; User Manual</h2>
      <p class="set-sub">The complete guide to using Trove. Open it anytime you need a refresher.</p>
      <div class="set-card2">
        <div class="set-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4a2 2 0 0 1 2-2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M14 2v5h5M8 13h8M8 17h6"/></svg></div>
        <div><div style="font-family:var(--f-display);font-weight:800;font-size:15px;">Trove User Manual</div><div style="font-size:12px;color:var(--muted);margin-top:3px;">PDF &bull; Owner edition</div></div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin:16px 0 18px;">
        <a href="{{ asset('docs/Trove_User_Manual.pdf') }}" target="_blank" class="set-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14 21 3"/></svg>Open Manual</a>
        <a href="{{ asset('docs/Trove_User_Manual.pdf') }}" download class="set-btn ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>Download PDF</a>
      </div>
      <div class="set-inside-h">What is inside</div>
      <div class="set-chips">
        <span class="set-chip">Logging In</span><span class="set-chip">Dashboard</span><span class="set-chip">Commissions</span><span class="set-chip">Products</span><span class="set-chip">Inventory</span><span class="set-chip">Branch Transfers</span><span class="set-chip">Expenses</span><span class="set-chip">Reports</span><span class="set-chip">User Management</span><span class="set-chip">Archiving</span>
      </div>
    </section>
  </div>
</div>

<script>
(function(){
  var rail=document.getElementById('setRail');
  if(!rail) return;
  rail.querySelectorAll('button').forEach(function(b){
    b.addEventListener('click',function(){
      var t=b.getAttribute('data-t');
      rail.querySelectorAll('button').forEach(function(x){x.classList.toggle('active',x===b);});
      document.querySelectorAll('[data-p]').forEach(function(p){p.hidden=p.getAttribute('data-p')!==t;});
    });
  });
})();
</script>
</x-app-layout>
