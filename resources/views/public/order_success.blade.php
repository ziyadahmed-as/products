@extends('layouts.public')
@section('title', 'Order Placed Successfully')

@section('content')
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;max-width:700px;margin:0 auto;">

    {{-- Success Header --}}
    <div style="text-align:center;margin-bottom:2.5rem;">
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(52,211,153,.15));border:2px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
            <svg style="width:40px;height:40px;color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 style="font-size:2rem;font-weight:900;color:var(--text-1);margin-bottom:.5rem;">Order Placed!</h1>
        <p style="color:var(--text-2);font-size:1rem;">Thank you for your purchase. Our team will contact you shortly to confirm payment and delivery.</p>
        @if($order)
        <div style="display:inline-flex;align-items:center;gap:.6rem;background:rgba(8,145,178,.1);border:1px solid rgba(8,145,178,.3);border-radius:50px;padding:.4rem 1.2rem;margin-top:1rem;">
            <svg style="width:14px;height:14px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span style="font-family:monospace;font-weight:700;color:var(--cyan);font-size:.9rem;">{{ $order->reference }}</span>
        </div>
        @endif
    </div>

    @auth
        {{-- Already logged in — go to dashboard --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:2rem;text-align:center;">
            <p style="color:var(--text-2);margin-bottom:1.5rem;">Your order is linked to your account. View it anytime in your dashboard.</p>
            <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('client.orders') }}" class="pub-btn pub-btn-primary" style="padding:.75rem 1.75rem;">
                    View My Orders →
                </a>
                <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-secondary" style="padding:.75rem 1.75rem;">
                    Continue Shopping
                </a>
            </div>
        </div>
    @else
        {{-- Guest — prompt to create account or log in --}}
        <div style="background:var(--bg-card);border:1px solid rgba(8,145,178,.35);border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(8,145,178,.06);">

            {{-- Banner --}}
            <div style="background:linear-gradient(135deg,rgba(8,145,178,.12),rgba(99,102,241,.08));padding:1.5rem 2rem;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:44px;height:44px;border-radius:50%;background:rgba(8,145,178,.15);border:1px solid rgba(8,145,178,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg style="width:22px;height:22px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight:800;color:var(--text-1);font-size:1.1rem;">Save Your Order — Create a Free Account</div>
                        <div style="color:var(--text-2);font-size:.85rem;margin-top:.15rem;">Track your order, rate products, and manage future purchases easily.</div>
                    </div>
                </div>
            </div>

            <div style="padding:2rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;color:var(--text-2);font-size:.85rem;">
                        <svg style="width:16px;height:16px;color:#34d399;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Track your order status
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem;color:var(--text-2);font-size:.85rem;">
                        <svg style="width:16px;height:16px;color:#34d399;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Rate & review products
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem;color:var(--text-2);font-size:.85rem;">
                        <svg style="width:16px;height:16px;color:#34d399;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        View full order history
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem;color:var(--text-2);font-size:.85rem;">
                        <svg style="width:16px;height:16px;color:#34d399;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Faster future checkouts
                    </div>
                </div>

                {{-- Register Form --}}
                @if(session('last_order_email'))
                <form action="{{ route('client.register.submit') }}" method="POST">
                    @csrf

                    @if($errors->any())
                    <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:.85rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div>
                            <label class="pub-form-label">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', session('last_order_name')) }}" required class="pub-input" style="width:100%;" placeholder="John Doe">
                        </div>
                        <div>
                            <label class="pub-form-label">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="pub-input" style="width:100%;" placeholder="+1 555 000-0000">
                        </div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label class="pub-form-label">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', session('last_order_email')) }}" required class="pub-input" style="width:100%;" readonly
                               style="width:100%;opacity:.85;cursor:not-allowed;">
                        <div style="font-size:.75rem;color:var(--text-3);margin-top:.3rem;">Using the email from your order</div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                        <div>
                            <label class="pub-form-label">Password *</label>
                            <input type="password" name="password" required class="pub-input" style="width:100%;" placeholder="Min. 8 characters">
                        </div>
                        <div>
                            <label class="pub-form-label">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required class="pub-input" style="width:100%;" placeholder="Repeat password">
                        </div>
                    </div>

                    <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:.9rem;font-size:.95rem;gap:.6rem;">
                        <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Create My Account & Save Order
                    </button>
                </form>

                <div style="text-align:center;margin-top:1.25rem;font-size:.85rem;color:var(--text-2);">
                    Already have an account?
                    <a href="{{ route('login') }}" style="color:var(--cyan);font-weight:600;">Log in instead →</a>
                </div>
                @endif

                <div style="text-align:center;margin-top:1rem;">
                    <a href="{{ route('public.home') }}" style="font-size:.8rem;color:var(--text-3);">Skip for now — continue without an account</a>
                </div>
            </div>
        </div>
    @endauth

</div>
@endsection
