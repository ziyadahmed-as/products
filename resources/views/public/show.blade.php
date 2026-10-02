@extends('layouts.public')
@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-dark-400 mb-8" aria-label="Breadcrumb">
        <a href="{{ route('public.shop') }}" class="hover:text-white">Shop</a>
        <span class="mx-2">/</span>
        <span class="text-white">{{ $product->name }}</span>
    </nav>

    <div class="bg-dark-900 border border-dark-800 rounded-2xl p-6 md:p-10 shadow-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Image -->
            <div class="aspect-square rounded-2xl bg-dark-800 overflow-hidden">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-dark-500">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div class="flex flex-col justify-center">
                <div class="mb-2"><span class="badge-blue">{{ $product->category->name ?? 'Misc' }}</span></div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">{{ $product->name }}</h1>
                <p class="text-dark-400 font-mono mb-6">SKU: {{ $product->sku }}</p>
                
                <div class="text-3xl font-bold text-primary-400 mb-8">
                    ${{ number_format($product->selling_price, 2) }}
                </div>

                <div class="prose prose-invert prose-dark mb-8">
                    <p>{{ $product->description ?? 'No description available for this product.' }}</p>
                </div>

                <!-- Add to Cart Form -->
                <form action="{{ route('public.cart.add', $product) }}" method="POST" class="flex gap-4">
                    @csrf
                    <div class="w-24">
                        <input type="number" name="quantity" value="1" min="1" class="form-input text-center font-bold">
                    </div>
                    <button type="submit" class="btn-primary flex-1 justify-center text-lg shadow-lg shadow-primary-900/40">
                        Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="mt-20">
        <h2 class="text-2xl font-bold text-white mb-6">You may also like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $rel)
                <div class="card-hover p-4 group">
                    <a href="{{ route('public.show', $rel) }}" class="block">
                        <div class="aspect-square rounded-xl bg-dark-800 mb-4 overflow-hidden relative">
                            @if($rel->image)
                                <img src="{{ asset('storage/'.$rel->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @endif
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-1 truncate">{{ $rel->name }}</h3>
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-primary-400">${{ number_format($rel->selling_price, 2) }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
