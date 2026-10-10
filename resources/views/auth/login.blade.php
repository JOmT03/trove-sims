<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In - Trove</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #FDF6EC;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-nav {
            width: 100%; padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }
        .logo-link { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-box {
            width: 42px; height: 42px; border-radius: 10px;
            background: #4A2C17; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; letter-spacing: -.5px;
        }
        .logo-name { font-size: 20px; font-weight: 900; line-height: 1; }
        .logo-name .brown { color: #4A2C17; }
        .logo-name .gold { color: #D9782C; }
        .logo-sub { font-size: 11px; color: #8A7460; }
        .nav-register { font-size: 13px; color: #374151; text-decoration: none; }
        .nav-register strong { color: #D9782C; }
        main {
            flex: 1; display: flex;
            align-items: center; justify-content: center;
            padding: 40px 16px;
        }
        .wrap { width: 100%; max-width: 440px; }
        .icon-ring {
            width: 80px; height: 80px; border-radius: 50%;
            border: 2.5px solid #D9782C; background: #fff;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 16px rgba(217,120,44,.25);
        }
        .icon-ring svg { width: 38px; height: 38px; stroke: #D9782C; }
        .card {
            background: #fff; border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.10);
            padding: 36px 40px;
        }
        .card-title {
            font-size: 26px; font-weight: 900; color: #4A2C17;
            text-align: center; margin-bottom: 4px;
        }
        .card-sub {
            text-align: center; color: #8A7460;
            font-size: 14px; margin-bottom: 28px;
        }
        .alert {
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; margin-bottom: 18px;
        }
        .alert-green { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-red   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .field { margin-bottom: 18px; }
        .field label {
            display: block; font-size: 13px;
            font-weight: 700; color: #374151; margin-bottom: 6px;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            stroke: #8A7460; display: flex;
        }
        .field-icon svg { width: 18px; height: 18px; }
        .field-wrap input {
            width: 100%; padding: 12px 42px;
            border: 1.5px solid #EDE0D0; border-radius: 10px;
            font-size: 14px; color: #2E1C10;
            background: #FDF6EC; outline: none;
            transition: border .2s, box-shadow .2s;
        }
        .field-wrap input:focus {
            border-color: #D9782C; background: #fff;
            box-shadow: 0 0 0 3px rgba(217,120,44,.15);
        }
        .field-wrap input.has-error { border-color: #f87171; }
        .eye-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            stroke: #8A7460; display: flex; padding: 0;
        }
        .eye-toggle svg { width: 18px; height: 18px; }
        .field-error { font-size: 12px; color: #dc2626; margin-top: 4px; }
        .meta-row {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 22px;
        }
        .remember {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; color: #4b5563; cursor: pointer;
        }
        .remember input { width: 15px; height: 15px; accent-color: #D9782C; }
        .forgot { font-size: 13px; color: #4A2C17; font-weight: 700; text-decoration: none; }
        .forgot:hover { text-decoration: underline; }
        .btn-signin {
            width: 100%; padding: 14px;
            background: #4A2C17; color: #fff; border: none;
            border-radius: 10px; font-size: 15px; font-weight: 900;
            letter-spacing: .5px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .2s;
        }
        .btn-signin:hover { opacity: .88; }
        .btn-signin svg { width: 18px; height: 18px; stroke: #fff; }
        footer { text-align: center; padding: 16px; font-size: 12px; color: #8A7460; }
    </style>
</head>
<body>

    <header class="top-nav">
        <a href="{{ url('/') }}" class="logo-link">
            <div class="logo-box">TR</div>
            <div>
                <div class="logo-name"><span class="brown">Trove</span></div>
                <div class="logo-sub">Food &amp; Cake Shop</div>
            </div>
        </a>

    </header>

    <main>
        <div class="wrap">

            <div class="icon-ring">
                <svg fill="none" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8 2 6 5 6 8c0 2 1 3 1 5 0 3 2 5 5 5s5-2 5-5c0-2 1-3 1-5 0-3-2-6-6-6z"/>
                </svg>
            </div>

            <div class="card">
                <h2 class="card-title">Welcome Back</h2>
                <p class="card-sub">Log in to your Trove account</p>

                @if(session('status'))
                    <div class="alert alert-green">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-red">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

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

                    <div class="meta-row">
                        <label class="remember">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-signin">
                        <svg fill="none" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        LOG IN
                    </button>
                </form>
            </div>

        </div>
    </main>

    <footer>&copy; {{ date('Y') }} Trove. All rights reserved.</footer>

    <script>
        function togglePw() {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>