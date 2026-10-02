@extends('layouts.public')
@section('title', 'Home')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-primary-900/20 to-dark-950"></div>
        <div class="absolute top-0 right-0 -mr-48 -mt-48 w-96 h-96 rounded-full bg-primary-600/10 blur-3xl animate-pulse-glow"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-20 pb-24 text-center">
        <h1 class="text-5xl md:text-7xl font-extrabold text-white tracking-tight mb-6">
            Excellence in <br/> <span class="text-gradient">Every Product</span>
        </h1>
        <p class="mt-4 max-w-2xl text-lg text-dark-300 mx-auto mb-10">
            Discover our premium range of professional products. Manufactured with care, delivered with speed.
        </p>
        <a href="{{ route('public.shop') }}" class="btn-primary text-lg px-8 py-4 rounded-full">
            Shop Now
        </a>
    </div>
</div>

<!-- Featured Products -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-bold text-white">Featured Products</h2>
        <a href="{{ route('public.shop') }}" class="text-primary-400 hover:text-primary-300 font-medium">View All &rarr;</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($featuredProducts as $product)
            <div class="card-hover p-4 group">
                <a href="{{ route('public.show', $product) }}" class="block">
                    <div class="aspect-square rounded-xl bg-dark-800 mb-4 overflow-hidden relative">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-dark-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="absolute top-2 right-2">
                            <span class="badge-blue text-[10px]">{{ $product->category->name ?? 'Misc' }}</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-1 truncate">{{ $product->name }}</h3>
                    <p class="text-sm text-dark-400 font-mono mb-3">{{ $product->sku }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-primary-400">${{ number_format($product->selling_price, 2) }}</span>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-span-4 text-center py-12 text-dark-500">No products featured at the moment.</div>
        @endforelse
    </div>
</div>
@endsection
