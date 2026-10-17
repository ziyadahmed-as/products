@extends('layouts.public')
@section('title', 'Sign In')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;max-width:420px;margin:0 auto;">
    <div style="background:var(--bg-card);padding:2.5rem;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.15);border:1px solid var(--border);">

        {{-- Header --}}
        <div style="text-align:center;margin-bottom:2rem;">
            <div style="display:inline-flex;padding:6px 14px;background:#ffffff;border-radius:16px;border:1px solid rgba(0,0,0,.08);box-shadow:0 4px 12px rgba(0,0,0,.06);margin-bottom:1rem;">
                <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="{{ \App\Models\Setting::getSiteName() }}" style="height:48px;width:auto;max-width:160px;object-fit:contain;">
            </div>
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-1);margin:0 0 .25rem;">Welcome Back</h1>
            <p style="color:var(--text-2);font-size:.875rem;margin:0;">Sign in to your {{ \App\Models\Setting::getSiteName() }} account</p>
        </div>

        @if(session('status'))
        <div style="background:rgba(16,185,129,.1);color:#34d399;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(16,185,129,.2);">
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(239,68,68,.2);">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login.submit', [], false) }}" method="POST">
            @csrf

            <div style="margin-bottom:1rem;">
                <label class="pub-form-label">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="pub-input" style="width:100%;" placeholder="you@example.com" autocomplete="email">
            </div>

            <div style="margin-bottom:.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
                    <label class="pub-form-label" style="margin:0;">Password</label>
                    <a href="{{ route('client.password.request', [], false) }}" style="font-size:.78rem;color:var(--cyan);font-weight:600;">Forgot password?</a>
                </div>
                <input type="password" id="password" name="password" required class="pub-input" style="width:100%;" placeholder="••••••••" autocomplete="current-password">
            </div>

            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.5rem;margin-top:.75rem;">
                <input type="checkbox" id="remember" name="remember" style="width:15px;height:15px;accent-color:var(--cyan);">
                <label for="remember" style="font-size:.85rem;color:var(--text-2);cursor:pointer;">Remember me</label>
            </div>

            <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:.85rem;font-size:.95rem;">
                Sign In
            </button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:.875rem;color:var(--text-2);">
            Don't have an account?
            <a href="{{ route('client.register', [], false) }}" style="color:var(--cyan);font-weight:700;">Create one →</a>
        </div>
    </div>
</div>
@endsection
