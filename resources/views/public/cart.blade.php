@extends('layouts.public')
@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-white mb-8">Shopping Cart</h1>

    @if(count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @php $total = 0; @endphp
                @foreach($cart as $id => $item)
                    @php $total += $item['price'] * $item['quantity']; @endphp
                    <div class="card p-4 flex gap-4 items-center">
                        <div class="w-20 h-20 bg-dark-800 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item['image'])
                                <img src="{{ asset('storage/'.$item['image']) }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-white truncate"><a href="{{ route('public.show', $id) }}">{{ $item['name'] }}</a></h3>
                            <p class="text-sm text-dark-400">SKU: {{ $item['sku'] }}</p>
                            <p class="text-primary-400 font-medium">${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-white mb-2">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                            <form action="{{ route('public.cart.remove', $id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="card sticky top-24">
                    <h2 class="text-xl font-bold text-white mb-6">Order Summary</h2>
                    
                    <div class="space-y-3 text-sm mb-6 border-b border-dark-800 pb-6">
                        <div class="flex justify-between">
                            <span class="text-dark-300">Subtotal</span>
                            <span class="text-white font-medium">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dark-300">Shipping</span>
                            <span class="text-dark-400">Calculated later</span>
                        </div>
                    </div>

                    <div class="flex justify-between mb-8">
                        <span class="text-lg font-bold text-white">Total</span>
                        <span class="text-2xl font-bold text-primary-400">${{ number_format($total, 2) }}</span>
                    </div>

                    <a href="{{ route('public.checkout') }}" class="btn-primary w-full justify-center text-lg py-3">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-20 bg-dark-900 border border-dark-800 rounded-2xl">
            <svg class="w-16 h-16 text-dark-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-white mb-2">Your cart is empty</h2>
            <p class="text-dark-400 mb-6">Looks like you haven't added any products to your cart yet.</p>
            <a href="{{ route('public.shop') }}" class="btn-primary px-8">Continue Shopping</a>
        </div>
    @endif
</div>
@endsection
