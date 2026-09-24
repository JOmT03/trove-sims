<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trove - An Exquisite Taste</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDF6EC] text-[#2E1C10]">

    <div class="min-h-screen flex flex-col">

        <header class="w-full px-8 py-5 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-[#4A2C17] text-white flex items-center justify-center font-bold text-lg">
                    TR
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold leading-none">
                        <span class="text-[#4A2C17]">TROVE</span>
                    </h1>
                    <p class="text-sm text-[#8A7460]">An Exquisite Taste</p>
                </div>
            </div>

            <nav class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="px-5 py-2 rounded-lg bg-[#4A2C17] text-white font-semibold hover:opacity-90 transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-5 py-2 rounded-lg border border-[#EDE0D0] font-semibold hover:bg-[#FDF6EC] transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-5 py-2 rounded-lg bg-[#D9782C] text-white font-semibold hover:opacity-90 transition">
                        Register
                    </a>
                @endauth
            </nav>
        </header>

        <main class="flex-1 grid lg:grid-cols-2 items-stretch">

            <!-- LEFT — Branding + Features (solid warm gradient, no photo) -->
            <section class="relative px-10 lg:px-16 pt-10 pb-12 flex flex-col justify-center overflow-hidden"
                     style="background: linear-gradient(135deg, #4A2C17 0%, #6B3E22 55%, #D9782C 100%); min-height: 600px;">

                <div class="relative z-10 max-w-xl">
                    <h2 class="text-5xl lg:text-7xl font-black leading-[0.95] uppercase tracking-tight">
                        <span class="text-white">A cake is never late,</span><br>
                        <span class="text-white">nor is it early.</span><br>
                        <span class="text-white">It arrives precisely</span><br>
                        <span class="text-white">when it is needed.</span>
                    </h2>

                    <div class="w-24 h-1 bg-[#E89552] my-6"></div>

                    <p class="text-lg text-[#FDF6EC] leading-8 max-w-md">
                        Even the smallest pastry<br>
                        can change the course of your morning.
                    </p>

                    <div class="mt-10 grid grid-cols-3 gap-4">
                        <div>
                            <svg class="w-8 h-8 text-[#E89552] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-white font-bold text-sm">Product Management</p>
                            <p class="text-xs text-[#EDE0D0] mt-1">Manage cakes, pastries, and coffee items</p>
                        </div>
                        <div>
                            <svg class="w-8 h-8 text-[#E89552] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-white font-bold text-sm">Inventory Tracking</p>
                            <p class="text-xs text-[#EDE0D0] mt-1">Monitor stock across Matina and Jacinto</p>
                        </div>
                        <div>
                            <svg class="w-8 h-8 text-[#E89552] mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <p class="text-white font-bold text-sm">Order Management</p>
                            <p class="text-xs text-[#EDE0D0] mt-1">Handle walk-in, customized, and bulk orders</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RIGHT — Auth Panel -->
            <section class="flex items-center justify-center px-8 py-10 bg-white">
                <div class="w-full max-w-md">

                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-full border-2 border-[#D9782C] flex items-center justify-center">
                            <svg class="w-10 h-10 text-[#D9782C]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8 2 6 5 6 8c0 2 1 3 1 5 0 3 2 5 5 5s5-2 5-5c0-2 1-3 1-5 0-3-2-6-6-6z"/>
                            </svg>
                        </div>
                    </div>

                    @guest
                        <h3 class="text-3xl font-extrabold text-center text-[#4A2C17] mb-1">Create Your Account</h3>
                        <p class="text-center text-[#8A7460] mb-6 text-sm">
                            Join Trove's sales and inventory system.
                        </p>

                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                           class="w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 focus:border-[#D9782C] focus:ring-[#D9782C] focus:outline-none text-slate-900 placeholder-slate-400 @error('name') border-red-500 @enderror"
                                           placeholder="Enter your full name">
                                </div>
                                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                           class="w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 focus:border-[#D9782C] focus:ring-[#D9782C] focus:outline-none text-slate-900 placeholder-slate-400 @error('email') border-red-500 @enderror"
                                           placeholder="Enter your email address">
                                </div>
                                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                    <input type="password" name="password" required id="password"
                                           class="w-full pl-10 pr-10 py-3 rounded-lg border border-slate-300 focus:border-[#D9782C] focus:ring-[#D9782C] focus:outline-none text-slate-900 placeholder-slate-400 @error('password') border-red-500 @enderror"
                                           placeholder="Enter your password">
                                    <button type="button" onclick="togglePw('password','eyeIcon1')"
                                            class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <svg id="eyeIcon1" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                    <input type="password" name="password_confirmation" required id="password_confirmation"
                                           class="w-full pl-10 pr-10 py-3 rounded-lg border border-slate-300 focus:border-[#D9782C] focus:ring-[#D9782C] focus:outline-none text-slate-900 placeholder-slate-400"
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

                            <button type="submit"
                                    class="w-full py-3 rounded-lg bg-[#D9782C] text-white font-extrabold text-base flex items-center justify-center gap-2 hover:opacity-90 transition mt-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                CREATE ACCOUNT
                            </button>

                            <p class="text-center text-slate-500 text-sm pt-2">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-[#4A2C17] font-bold hover:underline">Sign in here</a>
                            </p>
                        </form>

                    @else
                        <h3 class="text-3xl font-extrabold text-center text-[#4A2C17] mb-3">
                            Welcome back, {{ auth()->user()->name }}!
                        </h3>
                        <p class="text-center text-[#8A7460] mb-6">You are already signed in.</p>
                        <a href="{{ route('dashboard') }}"
                           class="block w-full text-center py-4 rounded-xl bg-[#D9782C] text-white font-extrabold text-lg hover:opacity-90 transition">
                            Open Dashboard
                        </a>
                    @endguest
                </div>
            </section>
        </main>

        <footer class="py-5 text-center text-sm text-[#8A7460] bg-white">
            <div class="flex items-center justify-center gap-2 mb-1">
                <div class="w-6 h-6 rounded bg-[#4A2C17] text-white flex items-center justify-center text-xs font-bold">TR</div>
                Cakes, pastries, and coffee — made with care.
            </div>
            © {{ date('Y') }} Trove. All rights reserved.
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