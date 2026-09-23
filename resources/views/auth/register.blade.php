<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — ConSupMan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;flex-direction:column;}

        /* NAV */
        .nav{background:#fff;border-bottom:1px solid #e2e8f0;padding:14px 32px;display:flex;align-items:center;justify-content:space-between;}
        .nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
        .nav-logo-box{width:38px;height:38px;background:#0f1f3d;border-radius:9px;display:flex;align-items:center;justify-content:center;color:#f0ad1f;font-weight:900;font-size:13px;}
        .nav-logo-name{font-size:18px;font-weight:900;color:#0f1f3d;}
        .nav-logo-name span{color:#f0ad1f;}
        .nav-logo-sub{font-size:10px;color:#94a3b8;}
        .nav-link{font-size:13px;color:#0f1f3d;text-decoration:none;font-weight:600;}
        .nav-link span{color:#f0ad1f;}

        /* MAIN */
        main{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px;}
        .wrap{width:100%;max-width:580px;}

        /* Role chooser */
        .role-chooser{background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,.08);margin-bottom:20px;}
        .role-chooser h2{font-size:22px;font-weight:900;color:#0f1f3d;text-align:center;margin-bottom:6px;}
        .role-chooser p{font-size:13px;color:#64748b;text-align:center;margin-bottom:24px;}
        .role-cards{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        .role-card{border:2px solid #e2e8f0;border-radius:12px;padding:20px 16px;cursor:pointer;text-align:center;transition:all .2s;background:#fff;}
        .role-card:hover{border-color:#f0ad1f;background:#fffbeb;}
        .role-card.selected{border-color:#0f1f3d;background:#0f1f3d;color:#fff;}
        .role-card.selected .role-icon{background:rgba(255,255,255,.15);}
        .role-card.selected .role-name{color:#f0ad1f;}
        .role-card.selected .role-desc{color:rgba(255,255,255,.7);}
        .role-icon{width:52px;height:52px;border-radius:14px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;}
        .role-name{font-size:15px;font-weight:800;color:#0f1f3d;margin-bottom:4px;}
        .role-desc{font-size:11px;color:#64748b;line-height:1.4;}

        /* Form card */
        .form-card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,.08);display:none;}
        .form-card.visible{display:block;}
        .form-card h3{font-size:17px;font-weight:800;color:#0f1f3d;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;}
        .section-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;margin:20px 0 12px;}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        .form-group{display:flex;flex-direction:column;gap:5px;}
        .form-group.full{grid-column:1/-1;}
        label{font-size:13px;font-weight:700;color:#374151;}
        label .req{color:#ef4444;}
        input,select{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;color:#1e293b;background:#f8fafc;outline:none;transition:border .2s;}
        input:focus,select:focus{border-color:#f0ad1f;background:#fff;box-shadow:0 0 0 3px rgba(240,173,31,.12);}
        input.error{border-color:#ef4444;}
        .field-error{font-size:11px;color:#dc2626;margin-top:2px;}

        /* Alerts */
        .alert-box{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;}
        .alert-box ul{margin-left:16px;margin-top:4px;}

        /* Buttons */
        .btn-submit{width:100%;padding:13px;background:#f0ad1f;color:#0f1f3d;font-weight:900;font-size:15px;border:none;border-radius:10px;cursor:pointer;margin-top:20px;transition:opacity .2s;display:flex;align-items:center;justify-content:center;gap:8px;}
        .btn-submit:hover{opacity:.88;}
        .signin-row{text-align:center;font-size:13px;color:#64748b;margin-top:16px;}
        .signin-row a{color:#0f1f3d;font-weight:700;text-decoration:none;}
        .signin-row a:hover{text-decoration:underline;}

        footer{text-align:center;padding:14px;font-size:11px;color:#94a3b8;}
    </style>
</head>
<body>

<header class="nav">
    <a href="{{ url('/') }}" class="nav-logo">
        <div class="nav-logo-box">CS</div>
        <div>
            <div class="nav-logo-name">ConSup<span>Man</span></div>
            <div class="nav-logo-sub">Construction Supplier Management</div>
        </div>
    </a>
    <a href="{{ route('login') }}" class="nav-link">Already have an account? <span>Sign in</span></a>
</header>

<main>
    <div class="wrap">

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert-box">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── STEP 1: Choose Role ── --}}
        <div class="role-chooser">
            <h2>Create Your Account</h2>
            <p>What type of account do you need?</p>
            <div class="role-cards">
                <div class="role-card {{ old('role')==='admin' ? 'selected' : '' }}"
                     onclick="selectRole('admin', this)">
                    <div class="role-icon">🏗️</div>
                    <div class="role-name">Buyer</div>
                    <div class="role-desc">Order construction materials from suppliers. Manage deliveries and inventory.</div>
                </div>
                <div class="role-card {{ old('role')==='supplier' ? 'selected' : '' }}"
                     onclick="selectRole('supplier', this)">
                    <div class="role-icon">🏭</div>
                    <div class="role-name">Supplier</div>
                    <div class="role-desc">List your products, receive orders, and manage deliveries to buyers.</div>
                </div>
            </div>
        </div>

        {{-- ── BUYER FORM ── --}}
        <div class="form-card {{ old('role')==='admin' ? 'visible' : '' }}" id="buyerForm">
            <h3>🏗️ Buyer Account Details</h3>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="role" value="admin">

                <div class="section-label">Personal Information</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Your full name">
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@email.com">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Contact Number <span class="req">*</span></label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}" required placeholder="09XX XXX XXXX">
                        @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Password <span class="req">*</span></label>
                        <input type="password" name="password" required placeholder="Min. 8 characters">
                        @error('password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Confirm Password <span class="req">*</span></label>
                        <input type="password" name="password_confirmation" required placeholder="Repeat password">
                    </div>
                </div>

                <div class="section-label">Company Information</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Company Name <span class="req">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. ABC Construction Corp">
                        @error('company_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group full">
                        <label>Company Address <span class="req">*</span></label>
                        <input type="text" name="company_address" value="{{ old('company_address') }}" required placeholder="Street / Building, City, Province">
                        @error('company_address')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Company Email <span class="req">*</span></label>
                        <input type="email" name="company_email" value="{{ old('company_email') }}" required placeholder="company@email.com">
                        @error('company_email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Company Tel / Fax <span class="req">*</span></label>
                        <input type="text" name="company_tel" value="{{ old('company_tel') }}" required placeholder="(082) XXX-XXXX">
                        @error('company_tel')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Create Buyer Account →
                </button>
                <div class="signin-row">
                    Already have an account? <a href="{{ route('login') }}">Sign in here</a>
                </div>
            </form>
        </div>

        {{-- ── SUPPLIER FORM ── --}}
        <div class="form-card {{ old('role')==='supplier' ? 'visible' : '' }}" id="supplierForm">
            <h3>🏭 Supplier Account Details</h3>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="role" value="supplier">

                <div class="section-label">Account Owner</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contact person name">
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@email.com">
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="09XX XXX XXXX">
                    </div>
                    <div class="form-group">
                        <label>Password <span class="req">*</span></label>
                        <input type="password" name="password" required placeholder="Min. 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password <span class="req">*</span></label>
                        <input type="password" name="password_confirmation" required placeholder="Repeat password">
                    </div>
                </div>

                <div class="section-label">Company Information</div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Company Name <span class="req">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. CementPro Supply Co.">
                        @error('company_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group full">
                        <label>Street / Building Address <span class="req">*</span></label>
                        <input type="text" name="company_address" value="{{ old('company_address') }}" required placeholder="Street, Barangay">
                        @error('company_address')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>City / Municipality <span class="req">*</span></label>
                        <input type="text" name="company_city" value="{{ old('company_city') }}" required placeholder="e.g. Zamboanga City">
                        @error('company_city')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>ZIP Code <span class="req">*</span></label>
                        <input type="text" name="company_zip" value="{{ old('company_zip') }}" required placeholder="e.g. 7000">
                        @error('company_zip')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Company Email <span class="req">*</span></label>
                        <input type="email" name="company_email" value="{{ old('company_email') }}" required placeholder="company@email.com">
                        @error('company_email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Company Tel <span class="req">*</span></label>
                        <input type="text" name="company_tel" value="{{ old('company_tel') }}" required placeholder="(082) XXX-XXXX">
                        @error('company_tel')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Create Supplier Account →
                </button>
                <div class="signin-row">
                    Already have an account? <a href="{{ route('login') }}">Sign in here</a>
                </div>
            </form>
        </div>

    </div>
</main>

<footer>© {{ date('Y') }} ConSupMan. All rights reserved.</footer>

<script>
function selectRole(role, el) {
    // Deselect all
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');

    // Show correct form
    document.getElementById('buyerForm').classList.remove('visible');
    document.getElementById('supplierForm').classList.remove('visible');
    document.getElementById(role === 'admin' ? 'buyerForm' : 'supplierForm').classList.add('visible');

    // Scroll to form
    setTimeout(() => {
        document.querySelector('.form-card.visible')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}
</script>

</body>
</html>