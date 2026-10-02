@extends('layouts.public')
@section('title', 'Checkout')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">

    <h1 style="font-size:2rem;font-weight:900;margin-bottom:.4rem;">Checkout</h1>
    <p style="color:var(--text-2);margin-bottom:2.5rem;">Fill in your details and we will contact you to confirm.</p>

    @php
        $cartItems = collect($cart)->map(fn($item, $id) => array_merge($item, ['id' => $id]));
        $total = $cartItems->sum(fn($i) => $i['price'] * $i['quantity']);
    @endphp

    <div class="pub-checkout-layout">

        {{-- Form --}}
        <div class="pub-checkout-form">
            <div class="pub-form-section-title">
                <div class="pub-step-dot">1</div>
                Your Information
            </div>

            @if($errors->any())
            <div class="pub-error-box">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <ul style="list-style:disc;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('public.order.place') }}" method="POST">
                @csrf

                <div class="pub-form-grid">
                    <div class="pub-form-group">
                        <label for="customer_name" class="pub-form-label">Full Name <span class="req">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" class="pub-input" placeholder="John Doe" value="{{ old('customer_name') }}">
                    </div>
                    <div class="pub-form-group">
                        <label for="phone" class="pub-form-label">Phone Number <span class="req">*</span></label>
                        <input type="tel" id="phone" name="phone" class="pub-input" placeholder="+1 (555) 000-0000" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="pub-form-group" style="margin-bottom:1rem;">
                    <label for="email" class="pub-form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="pub-input" placeholder="you@example.com" value="{{ old('email') }}">
                </div>

                <div class="pub-form-group" style="margin-bottom:1rem;">
                    <label for="address" class="pub-form-label">Delivery Address <span class="req">*</span></label>
                    <textarea id="address" name="address" class="pub-input" rows="3" placeholder="Street address, city, ZIP code…" style="resize:vertical;">{{ old('address') }}</textarea>
                </div>

                <div class="pub-form-group" style="margin-bottom:1.5rem;">
                    <label for="notes" class="pub-form-label">Order Notes <span style="color:var(--text-3);font-weight:400;">(optional)</span></label>
                    <textarea id="notes" name="notes" class="pub-input" rows="2" placeholder="Special delivery instructions…" style="resize:vertical;">{{ old('notes') }}</textarea>
                </div>

                <div class="pub-form-note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Our team will contact you after order placement to confirm payment and delivery details.
                </div>

                <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:1.1rem;font-size:1rem;gap:.75rem;">
                    <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Place Order — ${{ number_format($total, 2) }}
                </button>
            </form>
        </div>

        {{-- Summary sidebar --}}
        <div class="pub-order-summary">
            <div class="pub-form-section-title">
                <div class="pub-step-dot" style="background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.3);color:#34d399;">2</div>
                Order Summary
            </div>

            <div class="pub-checkout-items">
                @foreach($cartItems as $item)
                <div class="pub-checkout-item">
                    <div class="pub-checkout-item-img">
                        @if(!empty($item['image']))<img src="{{ asset('storage/'.$item['image']) }}">
                        @else<svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="pub-checkout-item-name">{{ $item['name'] }}</div>
                        <div class="pub-checkout-item-qty">Qty: {{ $item['quantity'] }}</div>
                    </div>
                    <div class="pub-checkout-item-price">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                </div>
                @endforeach
            </div>

            <div class="pub-summary-divider"></div>

            <div class="pub-summary-row"><span>Items ({{ $cartItems->sum('quantity') }})</span><strong>${{ number_format($total, 2) }}</strong></div>
            <div class="pub-summary-row"><span>Shipping</span><span class="pub-summary-free">Free</span></div>

            <div class="pub-summary-divider"></div>

            <div class="pub-summary-total">
                <span>Total</span>
                <span class="pub-summary-amount">${{ number_format($total, 2) }}</span>
            </div>

            <div style="margin-top:1.5rem;">
                <a href="{{ route('public.cart') }}" style="display:block;text-align:center;font-size:.8rem;color:var(--text-3);">← Edit Cart</a>
            </div>
        </div>
    </div>
</div>
@endsection
