# ============================================================
#  TROVE - User Management + disable public Register
#     powershell -ExecutionPolicy Bypass -File setup-user-management.ps1
# ============================================================
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
if ([string]::IsNullOrEmpty($root)) { $root = Get-Location }
if (-not (Test-Path (Join-Path $root "artisan"))) {
    Write-Host "ERROR: run from your project root (where artisan is)." -ForegroundColor Red; exit 1
}
$Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function WriteFile($rel, $text) {
    $p = Join-Path $root $rel
    New-Item -ItemType Directory -Force -Path (Split-Path $p) | Out-Null
    [System.IO.File]::WriteAllText($p, $text, $Utf8NoBom)
    Write-Host "  wrote $rel" -ForegroundColor Green
}
Write-Host "Setting up User Management..." -ForegroundColor Yellow

# ---- 1. Migration: add is_active to users ----
$mig = @'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $t) {
                $t->boolean('is_active')->default(true)->after('role');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $t) { $t->dropColumn('is_active'); });
        }
    }
};
'@
WriteFile "database\migrations\2026_10_04_000005_add_is_active_to_users.php" $mig

# ---- 2. Middleware: block deactivated users ----
$mw = @'
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActive
{
    public function handle(Request $request, Closure $next)
    {
        $u = Auth::user();
        if ($u && isset($u->is_active) && !$u->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been deactivated. Please contact the owner.']);
        }
        return $next($request);
    }
}
'@
WriteFile "app\Http\Middleware\EnsureActive.php" $mw

# ---- 3. Controller ----
$ctrl = @'
<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("FIELD(role,'Owner','Manager','Staff')")
                      ->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:Manager,Staff',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = new User();
        $user->name      = $data['name'];
        $user->email     = $data['email'];
        $user->role      = $data['role'];
        $user->password  = Hash::make($data['password']);
        $user->is_active = true;
        $user->save();

        return redirect()->route('users.index')
            ->with('success', $data['name'].' added as '.$data['role'].'.');
    }

    public function edit(User $user)
    {
        if ($user->role === 'Owner') {
            return redirect()->route('users.index')->with('error', 'The Owner account cannot be edited here.');
        }
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'Owner') {
            return redirect()->route('users.index')->with('error', 'The Owner account cannot be edited here.');
        }
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'role'     => 'required|in:Manager,Staff',
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = $data['role'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.index')->with('success', $user->name.' updated.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->role === 'Owner') {
            return redirect()->route('users.index')->with('error', 'The Owner account cannot be deactivated.');
        }
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'You cannot deactivate your own account.');
        }
        $user->is_active = !$user->is_active;
        $user->save();
        $state = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('users.index')->with('success', $user->name.' '.$state.'.');
    }
}
'@
WriteFile "app\Http\Controllers\UserController.php" $ctrl

# ---- 4a. users/index.blade.php ----
$vIndex = @'
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
                <tr>
                    <td><div class="nm">{{ $u->name }}</div><div class="em">{{ $u->email }}</div></td>
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
                                <form method="POST" action="{{ route('users.toggle',$u) }}" onsubmit="return confirm('{{ $u->is_active ? 'Deactivate' : 'Activate' }} {{ $u->name }}?');">
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
'@
WriteFile "resources\views\users\index.blade.php" $vIndex

# ---- 4b. users/create.blade.php ----
$vCreate = @'
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
            <div class="full"><label>Full Name</label><input name="name" value="{{ old('name') }}" required placeholder="e.g. Maria Santos"></div>
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
'@
WriteFile "resources\views\users\create.blade.php" $vCreate

# ---- 4c. users/edit.blade.php ----
$vEdit = @'
<x-app-layout>
<x-slot name="header">Edit User</x-slot>
<x-slot name="subheader">Update account details or reset the password</x-slot>

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
.uf-hint{font-size:11px;color:var(--muted);margin-top:4px;}
.uf-actions{display:flex;gap:12px;justify-content:flex-end;}
.u-btn{display:inline-flex;align-items:center;gap:6px;border:none;border-radius:9px;padding:10px 18px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;font-family:var(--f-body);}
.u-btn.gold{background:var(--gold);color:#fff;}
.u-btn.ghost{background:var(--white);color:var(--text);border:1px solid var(--border);}
</style>

<form method="POST" action="{{ route('users.update', $user) }}" class="uf">
    @csrf @method('PUT')
    <div class="uf-card">
        <div class="uf-title">Account Details</div>
        @if($errors->any())
            <div class="uf-err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="uf-grid">
            <div class="full"><label>Full Name</label><input name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="full"><label>Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
            <div><label>Role</label>
                <select name="role" required>
                    <option value="Manager" {{ $user->role === 'Manager' ? 'selected' : '' }}>Manager</option>
                    <option value="Staff" {{ $user->role === 'Staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div></div>
            <div><label>New Password</label><input type="password" name="password"><div class="uf-hint">Leave blank to keep current</div></div>
            <div><label>Confirm New Password</label><input type="password" name="password_confirmation"></div>
        </div>
    </div>
    <div class="uf-actions">
        <a href="{{ route('users.index') }}" class="u-btn ghost">Cancel</a>
        <button class="u-btn gold">Save Changes</button>
    </div>
</form>
</x-app-layout>
'@
WriteFile "resources\views\users\edit.blade.php" $vEdit

# ---- 5. Update layout navbar: add Administration > Users ----
$layoutPath = Join-Path $root "resources\views\layouts\app.blade.php"
if (Test-Path $layoutPath) {
    $lay = [System.IO.File]::ReadAllText($layoutPath)
    if ($lay -notmatch "users\.index") {
        $navBlock = @'
        @can('admin')
        @if(Route::has('users.index'))
        <div class="nav-label">Administration</div>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 00-1-7.75"/></svg>
            Users
        </a>
        @endif
        @endcan
    </nav>
'@
        $lay = $lay -replace "(?s)\r?\n\s*</nav>", ("`r`n" + $navBlock)
        [System.IO.File]::WriteAllText($layoutPath, $lay, $Utf8NoBom)
        Write-Host "  updated navbar (Administration > Users)" -ForegroundColor Green
    } else {
        Write-Host "  navbar already has Users link - skipped" -ForegroundColor DarkGray
    }
} else {
    Write-Host "  WARNING: layout not found at resources\views\layouts\app.blade.php" -ForegroundColor Yellow
}

# ---- 6. Edit routes/web.php ----
$webPath = Join-Path $root "routes\web.php"
$web = [System.IO.File]::ReadAllText($webPath)

# 6a. block deactivated users on the main auth group
if ($web -notmatch "EnsureActive") {
    $web = $web -replace "Route::middleware\('auth'\)->group\(function \(\) \{", "Route::middleware(['auth', \App\Http\Middleware\EnsureActive::class])->group(function () {"
    Write-Host "  web.php: added EnsureActive to auth group" -ForegroundColor Green
}

# 6b. disable public registration (override before auth.php loads)
if ($web -notmatch "Disable public registration") {
    $override = @'
// Disable public registration - Owner/Manager create accounts in Users module
Route::match(['get', 'post'], 'register', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';
'@
    $web = $web -replace "require\s+__DIR__\s*\.\s*'/auth\.php';", $override
    Write-Host "  web.php: public registration disabled" -ForegroundColor Green
}

# 6c. append user management routes
if ($web -notmatch "users\.index") {
    $usersRoutes = @'

// ---- User Management (Owner & Manager) ----
Route::middleware(['auth', 'admin', \App\Http\Middleware\EnsureActive::class])->group(function () {
    Route::get('/users',                 [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create',          [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/users',                [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit',     [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',          [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle');
});
'@
    $web = $web + $usersRoutes
    Write-Host "  web.php: user management routes added" -ForegroundColor Green
}
[System.IO.File]::WriteAllText($webPath, $web, $Utf8NoBom)

# ---- 7. Migrate + clear ----
Write-Host ""
Write-Host "Running migration..." -ForegroundColor Yellow
php artisan migrate --force
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

Write-Host ""
Write-Host "DONE!" -ForegroundColor Cyan
Write-Host "  - New 'Users' link under Administration (Owner/Manager only)" -ForegroundColor White
Write-Host "  - /register now redirects to Sign In" -ForegroundColor White
Write-Host "  - Deactivated users can no longer log in" -ForegroundColor White
Write-Host "Refresh with Ctrl+F5." -ForegroundColor White
