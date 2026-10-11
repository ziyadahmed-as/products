@extends('layouts.public')
@section('title', 'Create Account')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;max-width:460px;margin:0 auto;">
    <div style="background:var(--bg-card);padding:2.5rem;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.15);border:1px solid var(--border);">

        {{-- Header --}}
        <div style="text-align:center;margin-bottom:2rem;">
            <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,rgba(8,145,178,.15),rgba(99,102,241,.1));border:1px solid rgba(8,145,178,.3);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                <svg style="width:26px;height:26px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-1);margin:0 0 .25rem;">Create Account</h1>
            <p style="color:var(--text-2);font-size:.875rem;margin:0;">Track orders, rate products, and more</p>
        </div>

        @if($errors->any())
        <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;border:1px solid rgba(239,68,68,.2);">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('client.register.submit') }}" method="POST">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.875rem;margin-bottom:1rem;">
                <div>
                    <label class="pub-form-label">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="pub-input" style="width:100%;" placeholder="John Doe">
                </div>
                <div>
                    <label class="pub-form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="pub-input" style="width:100%;" placeholder="+1 555 000">
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="pub-form-label">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="pub-input" style="width:100%;" placeholder="you@example.com">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.875rem;margin-bottom:1.5rem;">
                <div>
                    <label class="pub-form-label">Password *</label>
                    <input type="password" name="password" required class="pub-input" style="width:100%;" placeholder="Min. 8 chars">
                </div>
                <div>
                    <label class="pub-form-label">Confirm *</label>
                    <input type="password" name="password_confirmation" required class="pub-input" style="width:100%;" placeholder="Repeat">
                </div>
            </div>

            <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:.85rem;font-size:.95rem;">
                Create Account
            </button>
        </form>

        <div style="margin-top:1.5rem;text-align:center;font-size:.875rem;color:var(--text-2);">
            Already have an account?
            <a href="{{ route('client.login') }}" style="color:var(--cyan);font-weight:700;">Log in →</a>
        </div>
    </div>
</div>
@endsection
