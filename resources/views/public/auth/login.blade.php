@extends('layouts.public')
@section('title', 'Client Login')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;max-width:400px;margin:0 auto;">
    <div style="background:var(--bg-card); padding:2.5rem; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.1); border:1px solid var(--border);">
        <h1 style="font-size:1.75rem;font-weight:800;margin-bottom:1.5rem;color:var(--text-1);text-align:center;">Welcome Back</h1>
        
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.1); color:#ef4444; padding:1rem; border-radius:8px; margin-bottom:1.5rem; font-size:0.875rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('client.login.submit') }}" method="POST">
            @csrf
            <div style="margin-bottom:1.5rem;">
                <label style="display:block;margin-bottom:0.5rem;color:var(--text-1);font-weight:600;font-size:0.9rem;">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="pub-input" style="width:100%;">
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="display:block;margin-bottom:0.5rem;color:var(--text-1);font-weight:600;font-size:0.9rem;">Password</label>
                <input type="password" name="password" required class="pub-input" style="width:100%;">
            </div>
            <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:0.75rem;">Log In</button>
        </form>

        <div style="margin-top:1.5rem; text-align:center; font-size:0.9rem; color:var(--text-2);">
            Don't have an account? <a href="{{ route('client.register') }}" style="color:var(--cyan);font-weight:600;">Register here</a>
        </div>
    </div>
</div>
@endsection
