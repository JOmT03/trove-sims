<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#4A2C17">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Trove">
    <link rel="apple-touch-icon" href="/trove-192.png">
    <title>Log In - Trove</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --brown:#4A2C17; --gold:#D9782C; --cream:#FBF2E4; --ink:#2E1C10; --muted:#8A7460; --border:#EBDCCA;
            --f-display:'Bricolage Grotesque','Segoe UI',system-ui,sans-serif; --f-body:'Plus Jakarta Sans','Segoe UI',system-ui,sans-serif;
        }
        *{ box-sizing:border-box; margin:0; padding:0; }
        body{ font-family:var(--f-body); color:var(--ink); background:#fff; }
        .topbar{ display:flex; align-items:center; gap:11px; padding:14px 26px; background:#fff; border-bottom:1px solid var(--border); }
        .logo{ width:40px; height:40px; border-radius:10px; background:var(--brown); color:#fff; display:grid; place-items:center; font-family:var(--f-display); font-weight:800; font-size:14px; }
        .brand .n{ font-family:var(--f-display); font-weight:800; font-size:18px; line-height:1; letter-spacing:.5px; color:var(--brown); }
        .brand .t{ font-size:11px; color:var(--gold); font-weight:600; }
        .split{ display:grid; grid-template-columns:1.05fr 1fr; min-height:calc(100vh - 69px); }
        .hero{ background:linear-gradient(140deg,#3A2113 0%,#4A2C17 45%,#C06B26 140%); color:#fff; padding:56px 56px; display:flex; flex-direction:column; justify-content:center; }
        .hero h1{ font-family:var(--f-display); font-weight:800; font-size:46px; line-height:1.02; margin:0; letter-spacing:-.5px; }
        .hero .rule{ width:70px; height:4px; background:var(--gold); border-radius:4px; margin:26px 0 20px; }
        .hero .lede{ font-size:14.5px; color:#F3D9C4; max-width:360px; line-height:1.5; }
        .loginside{ background:var(--cream); display:flex; align-items:center; justify-content:center; padding:40px 24px; }
        .ring{ width:70px; height:70px; border-radius:50%; border:2px solid var(--gold); display:grid; place-items:center; margin:0 auto 18px; }
        .ring span{ font-family:var(--f-display); font-weight:800; font-size:28px; color:var(--gold); }
        .card{ background:#fff; border-radius:20px; box-shadow:0 12px 40px rgba(74,44,23,.12); padding:32px 30px; width:100%; max-width:380px; }
        .card h2{ font-family:var(--f-display); font-weight:800; font-size:25px; text-align:center; margin:0; color:var(--brown); }
        .card .sub{ text-align:center; color:var(--muted); font-size:13px; margin:5px 0 22px; }
        .alert{ border-radius:10px; padding:11px 14px; font-size:12.5px; margin-bottom:16px; }
        .alert-green{ background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .alert-red{ background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
        label{ display:block; font-size:12.5px; font-weight:700; color:var(--brown); margin-bottom:6px; }
        .fw{ position:relative; margin-bottom:16px; }
        .fw input{ width:100%; padding:11px 40px; border:1.5px solid var(--border); border-radius:11px; font-size:14px; background:#FDF8F1; font-family:inherit; color:var(--ink); }
        .fw input:focus{ outline:none; border-color:var(--gold); background:#fff; }
        .fw input.has-error{ border-color:#f87171; }
        .fw .i{ position:absolute; left:13px; top:50%; transform:translateY(-50%); stroke:var(--muted); width:17px; height:17px; }
        .fw .eye{ position:absolute; right:13px; top:50%; transform:translateY(-50%); stroke:var(--muted); width:18px; height:18px; cursor:pointer; background:none; border:none; padding:0; }
        .field-error{ font-size:11.5px; color:#dc2626; margin:-8px 0 12px; }
        .opts{ display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
        .opts label{ display:flex; align-items:center; gap:7px; font-size:12.5px; color:var(--muted); font-weight:500; margin:0; }
        .opts input{ accent-color:var(--gold); }
        .opts a{ font-size:12.5px; font-weight:700; color:var(--brown); text-decoration:none; }
        .btn{ width:100%; display:flex; align-items:center; justify-content:center; gap:9px; background:var(--brown); color:#fff; border:none; border-radius:12px; padding:14px; font-family:inherit; font-weight:800; font-size:14px; letter-spacing:.4px; cursor:pointer; }
        .btn:hover{ background:#3a2213; }
        .btn svg{ stroke:#fff; }
        @media (max-width:820px){ .split{ grid-template-columns:1fr; } .hero{ padding:40px 30px; } .hero h1{ font-size:34px; } }
    </style>
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
      }
    </script>
</head>
<body>
    <div class="topbar">
        <div class="logo">TR</div>
        <div class="brand"><div class="n">TROVE</div><div class="t">An Exquisite Taste</div></div>
    </div>

    <div class="split">
        <div class="hero">
            <h1>A cake is never late,<br>nor is it early.<br>It arrives precisely<br>when it is needed.</h1>
            <div class="rule"></div>
            <div class="lede">Even the smallest pastry can change the course of your morning.</div>
        </div>

        <div class="loginside">
            <div style="width:100%;max-width:380px">
                <div class="ring"><span>O</span></div>
                <div class="card">
                    <h2>Welcome Back</h2>
                    <div class="sub">Log in to your Trove account</div>

                    @if(session('status'))
                        <div class="alert alert-green">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-red">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <label for="email">Email Address</label>
                        <div class="fw">
                            <svg class="i" fill="none" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="Enter your email"
                                   class="{{ $errors->has('email') ? 'has-error' : '' }}"
                                   required autofocus autocomplete="username">
                        </div>

                        <label for="password">Password</label>
                        <div class="fw">
                            <svg class="i" fill="none" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            <input type="password" id="password" name="password"
                                   placeholder="Enter your password"
                                   class="{{ $errors->has('password') ? 'has-error' : '' }}"
                                   required autocomplete="current-password">
                            <button type="button" class="eye" onclick="togglePw()">
                                <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>

                        <div class="opts">
                            <label><input type="checkbox" name="remember"> Remember me</label>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Forgot password?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn">
                            <svg width="17" height="17" fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/></svg>
                            LOG IN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePw(){
            var pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>