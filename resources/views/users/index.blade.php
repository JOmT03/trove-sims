<x-app-layout>
<x-slot name="header">User Management</x-slot>
<x-slot name="subheader">Only the Owner &amp; Managers can create and manage accounts</x-slot>

<style>
.u-wrap{max-width:900px;}
.u-alert{padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;}
.u-alert.ok{background:#E7F3EA;border:1px solid #bbf7d0;color:#166534;}
.u-alert.err{background:#FBE4DA;border:1px solid #fecaca;color:#991b1b;}
.u-banner{display:flex;align-items:center;gap:10px;background:#F3DCC4;border:1px solid var(--border);border-radius:12px;padding:12px 16px;font-size:13px;color:var(--navy);font-weight:600;margin-bottom:18px;}
.u-banner svg{width:18px;height:18px;stroke:var(--gold);fill:none;flex-shrink:0;}
.u-bar{display:flex;justify-content:flex-end;margin-bottom:14px;}
.u-btn{display:inline-flex;align-items:center;gap:6px;border:none;border-radius:9px;padding:10px 16px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;font-family:var(--f-body);}
.u-btn.gold{background:var(--gold);color:#fff;}
.u-card{background:var(--white);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(74,44,23,.06);}
.u-card table{width:100%;border-collapse:collapse;font-size:13px;}
.u-card th{text-align:left;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:12px 16px;background:var(--bg);font-weight:700;}
.u-card th.r{text-align:right;}
.u-card td{padding:13px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
.u-card td.r{text-align:right;}
.u-card tr:last-child td{border-bottom:none;}
.nm{font-weight:700;color:var(--text);}
.em{color:var(--muted);font-size:12px;}
.role{display:inline-block;font-size:11px;font-weight:700;padding:3px 11px;border-radius:999px;}
.role.owner{background:var(--navy);color:#fff;}
.role.manager{background:#F3DCC4;color:var(--navy);}
.role.staff{background:var(--bg);color:var(--muted);border:1px solid var(--border);}
.st{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;}
.st i{width:7px;height:7px;border-radius:50%;display:inline-block;}
.st.active{color:#15803D;} .st.active i{background:#15803D;}
.st.inactive{color:var(--muted);} .st.inactive i{background:var(--muted);}
.acts{display:flex;gap:6px;justify-content:flex-end;align-items:center;}
.acts form{display:inline;margin:0;}
.mini{border:1px solid var(--border);background:var(--white);color:var(--text);border-radius:8px;padding:6px 11px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;font-family:var(--f-body);}
.mini.danger{color:#C2410C;border-color:#fecaca;}
.mini.go{color:#15803D;border-color:#bbf7d0;}
.self{font-size:11px;color:var(--muted);font-style:italic;}
.u-note{margin-top:18px;font-size:12px;color:var(--muted);text-align:center;}
</style>

<div class="u-wrap">
    @if(session('success'))<div class="u-alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="u-alert err">{{ session('error') }}</div>@endif

    <div class="u-banner">
        <svg viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        Public registration is turned off. New Managers &amp; Staff can only sign in with accounts created here.
    </div>

    <div class="u-bar"><a href="{{ route('users.create') }}" class="u-btn gold">+ Add User</a></div>

    <div class="u-card">
        <table>
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th class="r">Actions</th></tr></thead>
            <tbody>
            @foreach($users as $u)
                @php $fullname = trim(($u->first_name ?? '').' '.($u->last_name ?? '')); @endphp
                <tr>
                    <td><div class="nm">{{ $fullname ?: 'Unnamed' }}</div><div class="em">{{ $u->email }}</div></td>
                    <td><span class="role {{ strtolower($u->role) }}">{{ $u->role }}</span></td>
                    <td>
                        @if($u->is_active)
                            <span class="st active"><i></i>Active</span>
                        @else
                            <span class="st inactive"><i></i>Inactive</span>
                        @endif
                    </td>
                    <td class="r">
                        @if($u->role === 'Owner')
                            <span class="self">master account</span>
                        @elseif(auth()->id() === $u->id)
                            <div class="acts"><a href="{{ route('users.edit',$u) }}" class="mini">Edit</a><span class="self">that&rsquo;s you</span></div>
                        @else
                            <div class="acts">
                                <a href="{{ route('users.edit',$u) }}" class="mini">Edit</a>
                                <form method="POST" action="{{ route('users.toggle',$u) }}" onsubmit="return confirm('{{ $u->is_active ? 'Deactivate' : 'Activate' }} {{ $u->first_name }}?');">
                                    @csrf @method('PATCH')
                                    <button class="mini {{ $u->is_active ? 'danger' : 'go' }}">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <p class="u-note">The Owner is the master account and cannot be deactivated. Deactivated users stay in records but cannot sign in.</p>
</div>
</x-app-layout>