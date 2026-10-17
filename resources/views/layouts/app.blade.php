<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Albareck IMS') }} — @yield('title', 'Dashboard')</title>
    <meta name="description" content="Albareck Integrated Stock & Manufacturing Management System">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js for real-time lightweight visual charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Theme Initializer (Anti-flash) -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        }
    </script>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-black dark:text-white font-sans antialiased selection:bg-sky-100 selection:text-sky-800" x-data="{ mobileSidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-show="mobileSidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 backdrop-blur-xs z-30 lg:hidden"
         style="display: none;"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar scrollbar-thin shadow-xs" :class="{ 'open': mobileSidebarOpen }" id="sidebar">
        <!-- Brand Logo Header -->
        <div class="sidebar-logo flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-dark-800">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group text-decoration-none">
                <span class="font-extrabold text-slate-900 dark:text-white text-xl tracking-tight font-sans">Albareck</span>
            </a>
            <button @click="mobileSidebarOpen = false" class="lg:hidden p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-dark-200 hover:bg-slate-100 dark:hover:bg-dark-800 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Role & Workspace Chip -->
        <div class="px-3 py-2 mx-3 my-2.5 rounded-xl bg-slate-50 dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-700 dark:text-dark-200 truncate">
                    {{ auth()->user()->roles->first()?->name ?? 'Staff' }}
                </span>
            </div>
            @if(auth()->user()->branches->isNotEmpty())
                <span class="text-[11px] font-medium text-slate-500 dark:text-dark-300 bg-white dark:bg-dark-800 px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700 truncate max-w-[90px]" title="{{ auth()->user()->branches->pluck('name')->join(', ') }}">
                    {{ auth()->user()->branches->first()->name }}
                </span>
            @else
                <span class="text-[11px] font-medium text-slate-600 dark:text-dark-300 bg-white dark:bg-dark-800 px-2 py-0.5 rounded border border-slate-200 dark:border-dark-700">
                    Global
                </span>
            @endif
        </div>

        <!-- Dynamic Nav Links by Authenticated Role -->
        @include('layouts._sidebar_nav')

        <!-- Sidebar User Footer -->
        <div class="p-3.5 border-t border-slate-100 dark:border-dark-800 bg-slate-50/70 dark:bg-dark-900/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-900 dark:bg-dark-700 flex items-center justify-center text-xs font-bold text-white shadow-xs flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate leading-tight">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-xs text-slate-600 dark:text-dark-400 truncate leading-normal">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-icon hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 dark:hover:bg-rose-950/50 dark:hover:border-rose-900" title="Sign Out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Topbar Navigation -->
    <header class="topbar">
        <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
            <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="lg:hidden p-2 text-slate-600 dark:text-dark-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-dark-800 rounded-lg transition" aria-label="Toggle navigation">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Global Search Bar (Dashboard 1 & 2 integration) -->
            <div class="relative w-48 sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" placeholder="Search..." class="form-input text-xs pl-8 pr-3 py-1.5 w-full bg-slate-50 dark:bg-dark-900 border-slate-200 dark:border-dark-700 rounded-lg">
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Date indicator pill -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100/80 dark:bg-dark-800 border border-slate-200/60 dark:border-dark-700 text-xs font-medium text-slate-600 dark:text-dark-300">
                <svg class="w-3.5 h-3.5 text-slate-600 dark:text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ now()->format('D, d M Y') }}</span>
            </div>

            <!-- Role Badge -->
            <span class="hidden md:inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                {{ auth()->user()->roles->first()?->name ?? 'Staff' }}
            </span>

            <!-- Quick Action Shortcut -->
            @hasanyrole('Super Admin|Manager|Seller')
            <a href="{{ route('sales.create') }}" class="btn-primary text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-lg shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="font-semibold">POS</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Production Officer')
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 rounded-lg shadow-xs">
                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Batch</span>
            </a>
            @endhasanyrole

            <!-- Theme Toggle Button -->
            <button type="button" onclick="toggleTheme()" class="btn-icon" aria-label="Toggle Theme" title="Toggle Theme">
                <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg class="w-4 h-4 block dark:hidden text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            <!-- User Circular Avatar with Initials (Dashboard 1 style) -->
            <div class="w-8 h-8 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 cursor-pointer shadow-xs" title="{{ auth()->user()->name }}">
                {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="page-content">
        <div class="page-inner animate-fade-in-up">
            @if (session('success'))
                <div class="alert alert-success mb-5">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger mb-5">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            localStorage.theme = 'dark';
        }
        window.dispatchEvent(new CustomEvent('themechanged'));
    }
    </script>
    @stack('scripts')
</body>
</html>
