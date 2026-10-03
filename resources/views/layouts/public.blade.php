<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Albareck — Premium professional products. Shop our full catalog of high-quality manufactured goods.">
    <title>Albareck Store — @yield('title', 'Welcome')</title>

    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $pubCss   = $manifest['resources/css/public.css']['file'] ?? null;
    @endphp
    @if($pubCss)
        <link rel="stylesheet" href="{{ asset('build/' . $pubCss) }}">
    @endif
    
    <!-- Theme Toggle Script -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body>

<!-- ── Navigation ─────────────────────────────────── -->
<nav class="pub-nav">
    <div class="pub-nav-inner">

        <!-- Logo -->
        <a href="{{ route('public.home') }}" class="pub-logo">
            <div class="pub-logo-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="pub-logo-text">Al<span>bareck</span></span>
        </a>

        <!-- Nav links -->
        <ul class="pub-nav-links">
            <li><a href="{{ route('public.home') }}"    class="{{ request()->routeIs('public.home')    ? 'active' : '' }}">Home</a></li>
            <li><a href="{{ route('public.about') }}"   class="{{ request()->routeIs('public.about')   ? 'active' : '' }}">About</a></li>
            <li><a href="{{ route('public.shop') }}"    class="{{ request()->routeIs('public.shop')    ? 'active' : '' }}">Shop</a></li>
            <li><a href="{{ route('public.contact') }}" class="{{ request()->routeIs('public.contact') ? 'active' : '' }}">Contact</a></li>
        </ul>

        <!-- Actions -->
        <div class="pub-nav-actions">
            <!-- Theme Toggle -->
            <button type="button" class="pub-theme-btn" onclick="toggleTheme()" aria-label="Toggle Dark Mode">
                <svg class="pub-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg class="pub-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>

            <a href="{{ route('public.cart') }}" class="pub-cart-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @php $cartCount = count(session('cart', [])); @endphp
                @if($cartCount > 0)
                    <span class="pub-cart-badge">{{ $cartCount }}</span>
                @endif
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="pub-btn pub-btn-primary" style="padding:.5rem 1.1rem;font-size:.8rem;">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="pub-login-btn">Login</a>
            @endauth
        </div>
    </div>
</nav>

<!-- ── Flash messages ──────────────────────────────── -->
@if(session('success'))
    <div class="pub-flash">
        <div class="pub-flash-success">
            <div class="pub-flash-icon">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif
@if(session('error'))
    <div class="pub-flash">
        <div class="pub-flash-error">
            <div class="pub-flash-icon">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

<!-- ── Page Content ────────────────────────────────── -->
<main>
    @yield('content')
</main>

<!-- ── Footer ─────────────────────────────────────── -->
<footer class="pub-footer">
    <div class="pub-footer-inner">
        <div class="pub-footer-grid">
            <div class="pub-footer-about">
                <a href="{{ route('public.home') }}" class="pub-logo" style="margin-bottom:1rem;">
                    <div class="pub-logo-icon" style="width:32px;height:32px;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="pub-logo-text" style="font-size:1.1rem;">Al<span>bareck</span></span>
                </a>
                <p>Premium professional products manufactured and sourced with excellence. Order online with our streamlined, secure checkout.</p>
            </div>

            <div>
                <div class="pub-footer-heading">Quick Links</div>
                <ul class="pub-footer-links">
                    <li><a href="{{ route('public.home') }}">Home</a></li>
                    <li><a href="{{ route('public.about') }}">About Us</a></li>
                    <li><a href="{{ route('public.shop') }}">Shop</a></li>
                    <li><a href="{{ route('public.cart') }}">Shopping Cart</a></li>
                </ul>
            </div>

            <div>
                <div class="pub-footer-heading">Contact</div>
                <ul class="pub-footer-contact">
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ \App\Models\Setting::get('contact_email', 'info@albareck.local') }}
                    </li>
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ \App\Models\Setting::get('contact_phone', '+1 (234) 567-8900') }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="pub-footer-divider">
            &copy; {{ date('Y') }} Albareck Systems. All rights reserved.
        </div>
    </div>
</footer>

<script>
function toggleTheme() {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    } else {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    }
}
</script>

</body>
</html>

