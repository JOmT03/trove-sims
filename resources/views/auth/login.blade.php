<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — ConSupMan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* NAV */
        .top-nav {
            width: 100%; padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }
        .logo-link { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-box {
            width: 42px; height: 42px; border-radius: 10px;
            background: #0f2d52; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; letter-spacing: -.5px;
        }
        .logo-name { font-size: 20px; font-weight: 900; line-height: 1; }
        .logo-name .blue { color: #0f2d52; }
        .logo-name .gold { color: #f0ad1f; }
        .logo-sub { font-size: 11px; color: #6b7280; }
        .nav-register { font-size: 13px; color: #374151; text-decoration: none; }
        .nav-register strong { color: #f0ad1f; }

        /* MAIN */
        main {
            flex: 1; display: flex;
            align-items: center; justify-content: center;
            padding: 40px 16px;
        }
        .wrap { width: 100%; max-width: 440px; }

        /* Crane icon */
        .icon-ring {
            width: 80px; height: 80px; border-radius: 50%;
            border: 2.5px solid #f0ad1f; background: #fff;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 16px rgba(240,173,31,.25);
        }
        .icon-ring svg { width: 38px; height: 38px; stroke: #f0ad1f; }

        /* Card */
        .card {
            background: #fff; border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.10);
            padding: 36px 40px;
        }
        .card-title {
            font-size: 26px; font-weight: 900; color: #0f2d52;
            text-align: center; margin-bottom: 4px;
        }
        .card-sub {
            text-align: center; color: #6b7280;
            font-size: 14px; margin-bottom: 28px;
        }

        /* Alerts */
        .alert {
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; margin-bottom: 18px;
        }
        .alert-green { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-red   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Form fields */
        .field { margin-bottom: 18px; }
        .field label {
            display: block; font-size: 13px;
            font-weight: 700; color: #374151; margin-bottom: 6px;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            stroke: #9ca3af; display: flex;
        }
        .field-icon svg { width: 18px; height: 18px; }
        .field-wrap input {
            width: 100%; padding: 12px 42px;
            border: 1.5px solid #d1d5db; border-radius: 10px;
            font-size: 14px; color: #111827;
            background: #f9fafb; outline: none;
            transition: border .2s, box-shadow .2s;
        }
        .field-wrap input:focus {
            border-color: #f0ad1f; background: #fff;
            box-shadow: 0 0 0 3px rgba(240,173,31,.15);
        }
        .field-wrap input.has-error { border-color: #f87171; }
        .eye-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            stroke: #9ca3af; display: flex; padding: 0;
        }
        .eye-toggle svg { width: 18px; height: 18px; }
        .field-error { font-size: 12px; color: #dc2626; margin-top: 4px; }

        /* Remember + forgot row */
        .meta-row {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 22px;
        }
        .remember {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; color: #4b5563; cursor: pointer;
        }
        .remember input { width: 15px; height: 15px; accent-color: #f0ad1f; }
        .forgot { font-size: 13px; color: #0f2d52; font-weight: 700; text-decoration: none; }
        .forgot:hover { text-decoration: underline; }

        /* Buttons */
        .btn-signin {
            width: 100%; padding: 14px;
            background: #0f2d52; color: #fff; border: none;
            border-radius: 10px; font-size: 15px; font-weight: 900;
            letter-spacing: .5px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .2s;
        }
        .btn-signin:hover { opacity: .88; }
        .btn-signin svg { width: 18px; height: 18px; stroke: #fff; }

        .separator {
            text-align: center; color: #9ca3af;
            font-size: 13px; margin: 16px 0;
        }

        .btn-register {
            width: 100%; padding: 13px;
            background: #fff; color: #0f2d52;
            border: 2px solid #0f2d52; border-radius: 10px;
            font-size: 14px; font-weight: 800; cursor: pointer;
            text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .2s, color .2s;
        }
        .btn-register:hover { background: #0f2d52; color: #fff; }
        .btn-register svg { width: 18px; height: 18px; }

        /* Feature cards */
        .features {
            display: grid; grid-template-columns: repeat(3,1fr);
            gap: 12px; margin-top: 20px;
        }
        .feat {
            background: #fff; border-radius: 14px;
            padding: 16px 10px; text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            border-top: 3px solid #f0ad1f;
        }
        .feat svg { width: 26px; height: 26px; stroke: #f0ad1f; margin: 0 auto 8px; display: block; }
        .feat p { font-size: 11px; font-weight: 700; color: #0f2d52; line-height: 1.4; }

        footer { text-align: center; padding: 16px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>

    <!-- TOP NAV -->
    <header class="top-nav">
        <a href="{{ url('/') }}" class="logo-link">
            <div class="logo-box">CS</div>
            <div>
                <div class="logo-name"><span class="blue">ConSup</span><span class="gold">Man</span></div>
                <div class="logo-sub">Construction Supplier Management</div>
            </div>
        </a>
        <a href="{{ route('register') }}" class="nav-register">
            No account? <strong>Register here</strong>
        </a>
    </header>

    <main>
        <div class="wrap">

            <!-- Crane Icon -->
            <div class="icon-ring">
                <svg fill="none" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21l9-18 9 18M9 17h6"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v5M7 8h10"/>
                </svg>
            </div>

            <!-- Card -->
            <div class="card">
                <h2 class="card-title">Welcome Back</h2>
                <p class="card-sub">Sign in to your ConSupMan account</p>

                @if(session('status'))
                    <div class="alert alert-green">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-red">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="field">
                        <label for="email">Email Address</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email"
                                   class="{{ $errors->has('email') ? 'has-error' : '' }}"
                                   required autofocus autocomplete="username">
                        </div>
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <!-- Password -->
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password"
                                   placeholder="Enter your password"
                                   class="{{ $errors->has('password') ? 'has-error' : '' }}"
                                   required autocomplete="current-password">
                            <button type="button" class="eye-toggle" onclick="togglePw()">
                                <svg id="eyeIcon" fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="meta-row">
                        <label class="remember">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                        @endif
                    </div>

                    <!-- Sign In Button -->
                    <button type="submit" class="btn-signin">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        SIGN IN
                    </button>

                    <div class="separator">— or —</div>

                    <!-- Register Link -->
                    <a href="{{ route('register') }}" class="btn-register">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24" style="stroke:currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Create New Account
                    </a>
                </form>
            </div>

            <!-- Feature Strip -->
            <div class="features">
                <div class="feat">
                    <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>Supplier Management</p>
                </div>
                <div class="feat">
                    <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p>Inventory Tracking</p>
                </div>
                <div class="feat">
                    <svg fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M13 8h4l3 5v3h-7V8z"/>
                    </svg>
                    <p>Delivery Tracking</p>
                </div>
            </div>

        </div>
    </main>

    <footer>© {{ date('Y') }} ConSupMan. All rights reserved.</footer>

    <script>
        function togglePw() {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>