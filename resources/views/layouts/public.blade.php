<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Albareck') }} — @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#060913] text-dark-100 font-sans min-h-screen flex flex-col relative overflow-x-hidden selection:bg-primary-500/30">

    <!-- Ambient Background Lighting -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-primary-600/10 blur-[120px]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] rounded-full bg-blue-900/20 blur-[120px]"></div>
    </div>

    <!-- Navigation -->
    <header class="glass-panel border-b-0 border-x-0 border-t-0 border-white/5 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    <a href="{{ route('public.home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-[0_0_15px_rgba(14,165,233,0.5)] group-hover:shadow-[0_0_25px_rgba(14,165,233,0.8)] transition-all duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white tracking-tighter font-['Outfit']">Albareck</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center gap-8 bg-white/[0.03] px-6 py-2 rounded-full border border-white/10 shadow-inner">
                    <a href="{{ route('public.home') }}" class="text-sm font-semibold tracking-wide transition-colors {{ request()->routeIs('public.home') ? 'text-primary-400' : 'text-dark-200 hover:text-white' }}">Home</a>
                    <a href="{{ route('public.shop') }}" class="text-sm font-semibold tracking-wide transition-colors {{ request()->routeIs('public.shop') ? 'text-primary-400' : 'text-dark-200 hover:text-white' }}">Shop</a>
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-5">
                    <a href="{{ route('public.cart') }}" class="relative w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-dark-200 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all shadow-lg active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-[0_0_10px_rgba(14,165,233,0.8)] border border-primary-400 border-opacity-50 min-w-[20px] text-center">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary text-xs px-4 py-2 rounded-full">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-dark-200 hover:text-white transition-colors">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow relative z-10">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 mt-8 animate-fade-in-up">
                <div class="alert-success backdrop-blur-xl border border-emerald-500/30 shadow-[0_0_20px_rgba(16,185,129,0.15)] rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="font-medium text-emerald-100">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 mt-8 animate-fade-in-up">
                <div class="alert-danger backdrop-blur-xl border border-red-500/30 shadow-[0_0_20px_rgba(239,68,68,0.15)] rounded-2xl p-4 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="font-medium text-red-100">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-black/40 backdrop-blur-3xl border-t border-white/5 mt-20 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-[0_0_15px_rgba(14,165,233,0.3)]">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-extrabold text-white tracking-tighter font-['Outfit']">Albareck</span>
                    </div>
                    <p class="text-dark-400 max-w-sm leading-relaxed">
                        High-quality professional products manufactured and sourced with excellence. Order online easily with our streamlined secure checkout.
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-widest mb-6 font-['Outfit']">Quick Links</h3>
                    <ul class="space-y-3 text-sm font-medium text-dark-400">
                        <li><a href="{{ route('public.home') }}" class="hover:text-primary-400 transition-colors">Home</a></li>
                        <li><a href="{{ route('public.shop') }}" class="hover:text-primary-400 transition-colors">Shop</a></li>
                        <li><a href="{{ route('public.cart') }}" class="hover:text-primary-400 transition-colors">Shopping Cart</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-widest mb-6 font-['Outfit']">Contact</h3>
                    <ul class="space-y-3 text-sm font-medium text-dark-400">
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            info@albareck.local
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +1 (234) 567-8900
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-16 pt-8 text-center text-sm font-medium text-dark-500">
                &copy; {{ date('Y') }} Albareck Systems. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
