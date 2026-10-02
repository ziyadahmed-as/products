@extends('layouts.public')
@section('title', 'Shopping Cart')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">

    <h1 style="font-size:2rem;font-weight:900;margin-bottom:.4rem;">Shopping Cart</h1>
    <p style="color:var(--text-2);margin-bottom:2.5rem;">Review your items before placing an order.</p>

    @php
        $cartItems = collect($cart)->map(fn($item, $id) => array_merge($item, ['id' => $id]));
        $total = $cartItems->sum(fn($i) => $i['price'] * $i['quantity']);
    @endphp

    @if($cartItems->isEmpty())
        <div class="pub-empty">
            <div class="pub-empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3>Your cart is empty</h3>
            <p>Add some products to get started.</p>
            <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary">Browse Products</a>
        </div>
    @else
        <div class="pub-cart-layout">

            {{-- Items --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach($cartItems as $item)
                <div class="pub-cart-item">
                    <div class="pub-cart-item-img">
                        @if(!empty($item['image']))
                            <img src="{{ asset('storage/'.$item['image']) }}" alt="{{ $item['name'] }}">
                        @else
                            <svg style="width:30px;height:30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div class="pub-cart-item-body">
                        <div>
                            <div class="pub-cart-item-name">{{ $item['name'] }}</div>
                            <div class="pub-cart-item-sku">{{ $item['sku'] }}</div>
                        </div>
                        <div class="pub-cart-item-row">
                            <div class="pub-cart-item-qty">
                                ${{ number_format($item['price'], 2) }} × <strong>{{ $item['quantity'] }}</strong>
                            </div>
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <span class="pub-cart-item-total">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                <form action="{{ route('public.cart.remove', $item['id']) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="pub-cart-remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div style="margin-top:.5rem;">
                    <a href="{{ route('public.shop') }}" style="display:inline-flex;align-items:center;gap:.5rem;font-size:.85rem;font-weight:600;color:var(--text-2);">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Continue Shopping
                    </a>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="pub-order-summary">
                <h3>Order Summary</h3>

                <div class="pub-summary-row">
                    <span>Subtotal ({{ $cartItems->sum('quantity') }} items)</span>
                    <strong>${{ number_format($total, 2) }}</strong>
                </div>
                <div class="pub-summary-row">
                    <span>Shipping</span>
                    <span class="pub-summary-free">Free</span>
                </div>

                <div class="pub-summary-divider"></div>

                <div class="pub-summary-total">
                    <span>Total</span>
                    <span class="pub-summary-amount">${{ number_format($total, 2) }}</span>
                </div>

                <a href="{{ route('public.checkout') }}" class="pub-btn pub-btn-primary pub-btn-full" style="margin-top:1.5rem;padding:1rem;font-size:1rem;gap:.75rem;">
                    <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Proceed to Checkout
                </a>

                <div class="pub-trust-note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Safe &amp; secure checkout
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
