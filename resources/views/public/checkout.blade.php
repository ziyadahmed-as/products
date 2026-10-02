@extends('layouts.public')
@section('title', 'Checkout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-white mb-8">Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Checkout Form -->
        <div class="card">
            <h2 class="text-xl font-bold text-white mb-6 border-b border-dark-800 pb-4">Shipping Information</h2>
            
            <form action="{{ route('public.placeOrder') }}" method="POST" class="space-y-5">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="customer_name" required class="form-input" placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" required class="form-input" placeholder="+1 234 567 8900">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Delivery Address *</label>
                    <textarea name="address" required class="form-textarea h-24" placeholder="Full street address, city, state..."></textarea>
                </div>

                <div class="pt-6">
                    <button type="submit" class="btn-primary w-full justify-center text-lg py-3 shadow-lg shadow-primary-900/40">
                        Place Order
                    </button>
                    <p class="text-xs text-dark-500 text-center mt-3">You will be contacted regarding payment processing after your order is submitted.</p>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="bg-dark-900/50 border border-dark-800 rounded-2xl p-6 sticky top-24">
                <h2 class="text-xl font-bold text-white mb-6">Your Order</h2>
                
                <div class="space-y-4 mb-6">
                    @php $total = 0; @endphp
                    @foreach($cart as $item)
                        @php $total += $item['price'] * $item['quantity']; @endphp
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="bg-dark-800 text-dark-200 text-xs font-bold px-2 py-1 rounded">{{ $item['quantity'] }}x</span>
                                <span class="text-white text-sm truncate max-w-[200px]">{{ $item['name'] }}</span>
                            </div>
                            <span class="text-dark-300">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-dark-800 pt-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-dark-300">Subtotal</span>
                        <span class="text-white">${{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex justify-between mb-6">
                        <span class="text-dark-300">Shipping</span>
                        <span class="text-dark-500 text-sm">TBD</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <span class="text-lg font-bold text-white">Total</span>
                        <span class="text-3xl font-bold text-primary-400">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
