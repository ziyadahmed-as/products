@extends('layouts.public')
@section('title', 'Shop All Products')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">

    <h1 style="font-size:2.25rem;font-weight:900;margin-bottom:0.4rem;">
        Our <span class="pub-text-gradient">Products</span>
    </h1>
    <p style="color:var(--text-2);margin-bottom:2.5rem;">Browse our full catalog of high-quality manufactured &amp; resale goods.</p>

    <div class="pub-shop-layout">

        {{-- Sidebar --}}
        <aside class="pub-sidebar">
            <div class="pub-sidebar-title">Filter &amp; Sort</div>

            <form action="{{ route('public.shop') }}" method="GET" id="filter-form">

                <div class="pub-filter-group">
                    <label class="pub-filter-label">Search</label>
                    <input type="text" name="search" class="pub-input" placeholder="Product name…" value="{{ request('search') }}">
                </div>

                <div class="pub-filter-group">
                    <label class="pub-filter-label">Category</label>
                    <select name="category_id" class="pub-select" onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pub-filter-group">
                    <label class="pub-filter-label">Sort By</label>
                    <select name="sort" class="pub-select" onchange="document.getElementById('filter-form').submit()">
                        <option value="newest"     {{ request('sort','newest')=='newest'     ? 'selected':'' }}>Newest First</option>
                        <option value="price_asc"  {{ request('sort')=='price_asc'  ? 'selected':'' }}>Price: Low → High</option>
                        <option value="price_desc" {{ request('sort')=='price_desc' ? 'selected':'' }}>Price: High → Low</option>
                        <option value="rating"     {{ request('sort')=='rating'     ? 'selected':'' }}>Highest Rated</option>
                    </select>
                </div>

                <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="margin-top:.5rem;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Apply Filters
                </button>

                @if(request()->hasAny(['search','category_id','sort']))
                    <a href="{{ route('public.shop') }}" class="pub-clear-link">✕ Clear all filters</a>
                @endif
            </form>

            <div class="pub-sidebar-count">
                <div class="pub-sidebar-count-num">{{ $products->total() }}</div>
                <div class="pub-sidebar-count-label">Products found</div>
            </div>
        </aside>

        {{-- Product Grid --}}
        <div>
            @if($products->isEmpty())
                <div class="pub-empty">
                    <div class="pub-empty-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter.</p>
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-secondary">Clear Filters</a>
                </div>
            @else
                <div class="pub-grid pub-grid-3">
                    @foreach($products as $product)
                    <div class="pub-product-card">
                        <a href="{{ route('public.show', $product) }}" class="pub-product-img">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                            @else
                                <div class="pub-product-img-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                            @endif
                            <div class="pub-product-badges">
                                @if($product->is_featured)<span class="pub-badge pub-badge-featured">★ Featured</span>@endif
                                @if($product->created_at->diffInDays() < 14)<span class="pub-badge pub-badge-new">New</span>@endif
                            </div>
                            @if($product->category)<div class="pub-product-cat"><span class="pub-badge pub-badge-cyan">{{ $product->category->name }}</span></div>@endif
                        </a>
                        <div class="pub-product-info">
                            <a href="{{ route('public.show', $product) }}" class="pub-product-name">{{ $product->name }}</a>
                            <div class="pub-stars">
                                @for($i=1;$i<=5;$i++)<svg class="pub-star-{{ $i<=floor($product->rating)?'on':'off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                                <span class="pub-stars-count">({{ $product->reviews_count }})</span>
                            </div>
                            <div class="pub-product-footer">
                                <span class="pub-price">${{ number_format($product->selling_price, 2) }}</span>
                                <form action="{{ route('public.cart.add', $product) }}" method="POST">
                                    @csrf<button type="submit" class="pub-add-btn"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pub-pagination">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
