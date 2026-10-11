@extends('layouts.public')
@section('title', 'Forgot Password')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;max-width:420px;margin:0 auto;">
    <div style="background:var(--bg-card);padding:2.5rem;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.15);border:1px solid var(--border);">

        <div style="text-align:center;margin-bottom:2rem;">
            <div style="width:56px;height:56px;border-radius:16px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                <svg style="width:26px;height:26px;color:#f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 style="font-size:1.6rem;font-weight:900;color:var(--text-1);margin:0 0 .25rem;">Reset Password</h1>
            <p style="color:var(--text-2);font-size:.875rem;margin:0;">Enter your email and we'll send you a reset link</p>
        </div>

        @if(session('status'))
        <div style="background:rgba(16,185,129,.1);color:#34d399;padding:1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(16,185,129,.2);display:flex;align-items:center;gap:.5rem;">
            <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(239,68,68,.2);">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('client.password.email') }}" method="POST">
            @csrf
            <div style="margin-bottom:1.5rem;">
                <label class="pub-form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="pub-input" style="width:100%;" placeholder="you@example.com" autocomplete="email">
            </div>
            <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:.85rem;font-size:.95rem;">
                Send Reset Link
            </button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:.875rem;color:var(--text-2);">
            <a href="{{ route('client.login') }}" style="color:var(--cyan);font-weight:600;">← Back to Sign In</a>
        </div>
    </div>
</div>
@endsection
