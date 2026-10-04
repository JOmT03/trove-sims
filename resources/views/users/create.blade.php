<x-app-layout>
<x-slot name="header">Add User</x-slot>
<x-slot name="subheader">Create a Manager or Staff account</x-slot>

<style>
.uf{max-width:560px;}
.uf-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(74,44,23,.06);margin-bottom:16px;}
.uf-title{font-family:var(--f-display);font-size:15px;font-weight:800;color:var(--text);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);}
.uf-err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:9px;font-size:12.5px;margin-bottom:14px;}
.uf-err ul{margin:0;padding-left:18px;}
.uf-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.uf-grid .full{grid-column:1/-1;}
.uf-grid label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin-bottom:6px;}
.uf-grid input,.uf-grid select{width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;background:var(--bg);color:var(--text);font-family:var(--f-body);}
.uf-actions{display:flex;gap:12px;justify-content:flex-end;}
.u-btn{display:inline-flex;align-items:center;gap:6px;border:none;border-radius:9px;padding:10px 18px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;font-family:var(--f-body);}
.u-btn.gold{background:var(--gold);color:#fff;}
.u-btn.ghost{background:var(--white);color:var(--text);border:1px solid var(--border);}
</style>

<form method="POST" action="{{ route('users.store') }}" class="uf">
    @csrf
    <div class="uf-card">
        <div class="uf-title">Account Details</div>
        @if($errors->any())
            <div class="uf-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="uf-grid">
            <div><label>First Name</label><input name="first_name" value="{{ old('first_name') }}" required placeholder="Maria"></div>
            <div><label>Last Name</label><input name="last_name" value="{{ old('last_name') }}" required placeholder="Santos"></div>
            <div class="full"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" required placeholder="maria@trove.com"></div>
            <div><label>Role</label><select name="role" required><option value="Manager">Manager</option><option value="Staff">Staff</option></select></div>
            <div></div>
            <div><label>Password</label><input type="password" name="password" required></div>
            <div><label>Confirm Password</label><input type="password" name="password_confirmation" required></div>
        </div>
    </div>
    <div class="uf-actions">
        <a href="{{ route('users.index') }}" class="u-btn ghost">Cancel</a>
        <button class="u-btn gold">Create Account</button>
    </div>
</form>
</x-app-layout>