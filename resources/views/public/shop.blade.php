@extends('layouts.public')
@section('title', 'Shop')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Our Products</h1>
            <p class="text-dark-400 mt-1">Browse our full catalog.</p>
        </div>

        <!-- Search & Filter -->
        <form method="GET" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="form-input w-full md:w-64">
            <select name="category_id" class="form-select w-full md:w-48">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="card-hover p-4 group">
                <a href="{{ route('public.show', $product) }}" class="block">
                    <div class="aspect-square rounded-xl bg-dark-800 mb-4 overflow-hidden relative">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-dark-500">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-1 truncate">{{ $product->name }}</h3>
                    <p class="text-sm text-dark-400 font-mono mb-3">{{ $product->sku }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-primary-400">${{ number_format($product->selling_price, 2) }}</span>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-span-full text-center py-20 text-dark-500">
                <p class="text-lg font-medium">No products found matching your criteria.</p>
                <a href="{{ route('public.shop') }}" class="text-primary-400 mt-2 inline-block">Clear filters</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
