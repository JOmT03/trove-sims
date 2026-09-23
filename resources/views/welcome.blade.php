<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ConSupMan - Construction Supplier Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f7f5] text-slate-900">

    <div class="min-h-screen flex flex-col">

        <!-- ══════════════════════════════════════
             TOP NAV — Sign In / Register for guests
             ══════════════════════════════════════ -->
        <header class="w-full px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-[#0f2d52] text-white flex items-center justify-center font-bold text-lg">
                    CS
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold leading-none">
                        <span class="text-[#0f2d52]">ConSup</span><span class="text-[#f0ad1f]">Man</span>
                    </h1>
                    <p class="text-sm text-slate-600">Construction Supplier Management System</p>
                </div>
            </div>

            <nav class="flex items-center gap-4">
                @auth
                    {{-- Logged-in users see the Dashboard button --}}
                    <a href="{{ route('dashboard') }}"
                       class="px-5 py-2 rounded-lg bg-[#0f2d52] text-white font-semibold hover:opacity-90 transition">
                        Dashboard
                    </a>
                @else
                    {{-- Guests see Sign In and Register --}}
                    <a href="{{ route('login') }}"
                       class="px-5 py-2 rounded-lg border border-slate-300 font-semibold hover:bg-white transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-5 py-2 rounded-lg bg-[#f0ad1f] text-slate-900 font-semibold hover:opacity-90 transition">
                        Register
                    </a>
                @endauth
            </nav>
        </header>

        <!-- ══════════════════════════════════════
             HERO — Two-column layout
             ══════════════════════════════════════ -->
        <main class="flex-1 grid lg:grid-cols-2 items-stretch">

            <!-- LEFT — Branding + Features -->
            <section class="relative px-10 lg:px-16 pt-10 pb-12 flex flex-col justify-center overflow-hidden"
                     style="background: url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=900&q=80') center/cover no-repeat; min-height: 600px;">
                <!-- Dark overlay -->
                <div class="absolute inset-0 bg-[#0f2d52]/75"></div>

                <div class="relative z-10 max-w-xl">
                    <h2 class="text-5xl lg:text-7xl font-black leading-[0.95] uppercase tracking-tight">
                        <span class="text-white">Building</span><br>
                        <span class="text-white">Projects.</span><br>
                        <span class="text-[#f0ad1f]">Building</span><br>
                        <span class="text-[#f0ad1f]">Relationships.</span>
                    </h2>

                    <div class="w-24 h-1 bg-[#f0ad1f] my-6"></div>

                    <p class="text-lg text-slate-200 leading-8 max-w-md">
                        Manage your construction suppliers, materials, and deliveries efficiently in one centralized platform.
                    </p>

                    <!-- Bottom Feature Strip -->
                    <div class="mt-10 grid grid-cols-3 gap-4">
                        <div>
                            {{-- Supplier icon --}}
                            <svg class="w-8 h-8 text-[#f0ad1f] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-[#f0ad1f] font-bold text-sm">Supplier Management</p>
                            <p class="text-xs text-slate-300 mt-1">Organize and manage all your suppliers</p>
                        </div>
                        <div>
                            {{-- Truck icon --}}
                            <svg class="w-8 h-8 text-[#f0ad1f] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 .001M13 16l2 .001M13 16H9m4 0h2m0 0l2-5h-3V6m0 5h3"/>
                            </svg>
                            <p class="text-[#f0ad1f] font-bold text-sm">Material Tracking</p>
                            <p class="text-xs text-slate-300 mt-1">Track materials and deliveries in real-time</p>
                        </div>
                        <div>
                            {{-- Hard hat icon --}}
                            <svg class="w-8 h-8 text-[#f0ad1f] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 17h18M5 17V9a7 7 0 0114 0v8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4"/>
                            </svg>
                            <p class="text-[#f0ad1f] font-bold text-sm">Project Support</p>
                            <p class="text-xs text-slate-300 mt-1">Support your projects with reliable suppliers</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RIGHT — Auth Panel -->
            <section class="flex items-center justify-center px-8 py-10 bg-white">
                <div class="w-full max-w-md">

                    {{-- Crane icon --}}
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-full border-2 border-[#f0ad1f] flex items-center justify-center">
                            <svg class="w-10 h-10 text-[#f0ad1f]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21l9-18 9 18M9 17h6"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v5M7 8h10"/>
                            </svg>
                        </div>
                    </div>

                    @guest
                        {{-- ─── REGISTER FORM (matches design image) ─── --}}
                        <h3 class="text-3xl font-extrabold text-center text-[#0f2d52] mb-1">Create Your Account</h3>
                        <p class="text-center text-slate-500 mb-6 text-sm">
                            Join our system to manage construction suppliers<br>and streamline your projects.
                        </p>

                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
                            @csrf

                            {{-- Full Name --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                           class="w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 focus:border-[#f0ad1f] focus:ring-[#f0ad1f] focus:outline-none text-slate-900 placeholder-slate-400 @error('name') border-red-500 @enderror"
                                           placeholder="Enter your full name">
                                </div>
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                           class="w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 focus:border-[#f0ad1f] focus:ring-[#f0ad1f] focus:outline-none text-slate-900 placeholder-slate-400 @error('email') border-red-500 @enderror"
                                           placeholder="Enter your email address">
                                </div>
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                    <input type="password" name="password" required id="password"
                                           class="w-full pl-10 pr-10 py-3 rounded-lg border border-slate-300 focus:border-[#f0ad1f] focus:ring-[#f0ad1f] focus:outline-none text-slate-900 placeholder-slate-400 @error('password') border-red-500 @enderror"
                                           placeholder="Enter your password">
                                    <button type="button" onclick="togglePw('password','eyeIcon1')"
                                            class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <svg id="eyeIcon1" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                    <input type="password" name="password_confirmation" required id="password_confirmation"
                                           class="w-full pl-10 pr-10 py-3 rounded-lg border border-slate-300 focus:border-[#f0ad1f] focus:ring-[#f0ad1f] focus:outline-none text-slate-900 placeholder-slate-400"
                                           placeholder="Confirm your password">
                                    <button type="button" onclick="togglePw('password_confirmation','eyeIcon2')"
                                            class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <svg id="eyeIcon2" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="w-full py-3 rounded-lg bg-[#f0ad1f] text-slate-900 font-extrabold text-base flex items-center justify-center gap-2 hover:opacity-90 transition mt-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                CREATE ACCOUNT
                            </button>

                            <p class="text-center text-slate-500 text-sm pt-2">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-[#0f2d52] font-bold hover:underline">Sign in here</a>
                            </p>
                        </form>

                    @else
                        {{-- Logged-in users see a Dashboard link --}}
                        <h3 class="text-3xl font-extrabold text-center text-[#0f2d52] mb-3">
                            Welcome back, {{ auth()->user()->name }}!
                        </h3>
                        <p class="text-center text-slate-500 mb-6">You are already signed in.</p>
                        <a href="{{ route('dashboard') }}"
                           class="block w-full text-center py-4 rounded-xl bg-[#f0ad1f] text-slate-900 font-extrabold text-lg hover:opacity-90 transition">
                            Open Dashboard
                        </a>
                    @endguest
                </div>
            </section>
        </main>

        <footer class="py-5 text-center text-sm text-slate-600">
            <div class="flex items-center justify-center gap-2 mb-1">
                <div class="w-6 h-6 rounded bg-[#0f2d52] text-white flex items-center justify-center text-xs font-bold">CS</div>
                Building better projects with reliable suppliers.
            </div>
            © {{ date('Y') }} ConSupMan. All rights reserved.
        </footer>
    </div>

    <script>
        function togglePw(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>