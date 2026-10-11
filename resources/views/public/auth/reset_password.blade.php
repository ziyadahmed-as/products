@extends('layouts.public')
@section('title', 'Set New Password')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;max-width:420px;margin:0 auto;">
    <div style="background:var(--bg-card);padding:2.5rem;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.15);border:1px solid var(--border);">

        <div style="text-align:center;margin-bottom:2rem;">
            <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,rgba(8,145,178,.15),rgba(99,102,241,.1));border:1px solid rgba(8,145,178,.3);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                <svg style="width:26px;height:26px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 style="font-size:1.6rem;font-weight:900;color:var(--text-1);margin:0 0 .25rem;">Set New Password</h1>
            <p style="color:var(--text-2);font-size:.875rem;margin:0;">Choose a strong password for your account</p>
        </div>

        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(239,68,68,.2);">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('client.password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div style="margin-bottom:1rem;">
                <label class="pub-form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email', request('email')) }}" required class="pub-input" style="width:100%;" placeholder="you@example.com" autocomplete="email">
            </div>

            <div style="margin-bottom:1rem;">
                <label class="pub-form-label">New Password</label>
                <input type="password" name="password" required class="pub-input" style="width:100%;" placeholder="Min. 8 characters" autocomplete="new-password">
            </div>

            <div style="margin-bottom:1.5rem;">
                <label class="pub-form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" required class="pub-input" style="width:100%;" placeholder="Repeat password" autocomplete="new-password">
            </div>

            <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:.85rem;font-size:.95rem;">
                Reset Password
            </button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:.875rem;color:var(--text-2);">
            <a href="{{ route('client.login') }}" style="color:var(--cyan);font-weight:600;">← Back to Sign In</a>
        </div>
    </div>
</div>
@endsection
