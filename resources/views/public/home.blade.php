@extends('layouts.public')
@section('title', 'Home')

@section('content')



{{-- ─── Featured Products ──────────────────────────── --}}
@if($featuredProducts->count() > 0)
<div class="pub-container" style="padding-top:3rem;padding-bottom:4rem;">
    <div class="pub-section-header">
        <h2>Featured Selection <small>Hand-picked top tier products</small></h2>
        <a href="{{ route('public.shop') }}" class="pub-section-link">View All &rarr;</a>
    </div>

    <div class="pub-featured-grid">
        {{-- First 2: large cards --}}
        @foreach($featuredProducts->take(2) as $product)
        <div class="pub-featured-large">
            <a href="{{ route('public.show', $product) }}" class="pub-product-img" style="aspect-ratio:16/9;">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                @else
                    <div class="pub-product-img-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                @endif
                <div class="pub-product-badges">
                    <span class="pub-badge pub-badge-featured">★ Featured</span>
                </div>
                @if($product->category)
                    <div class="pub-product-cat"><span class="pub-badge pub-badge-cyan">{{ $product->category->name }}</span></div>
                @endif
            </a>
            <div class="pub-product-info">
                <a href="{{ route('public.show', $product) }}" class="pub-product-name">{{ $product->name }}</a>
                <div class="pub-stars">
                    @for($i=1;$i<=5;$i++)<svg class="pub-star-{{ $i<=floor($product->rating)?'on':'off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                    <span class="pub-stars-count">({{ $product->reviews_count }})</span>
                </div>
                <div class="pub-product-footer">
                    <span class="pub-price">Br{{ number_format($product->selling_price, 2) }}</span>
                    <form action="{{ route('public.cart.add', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="pub-add-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Remaining: small cards row --}}
        @if($featuredProducts->count() > 2)
        <div class="pub-featured-smalls">
            @foreach($featuredProducts->skip(2) as $product)
            <div class="pub-featured-small">
                <a href="{{ route('public.show', $product) }}" class="pub-product-img" style="aspect-ratio:4/3;">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                    @else
                        <div class="pub-product-img-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    @endif
                </a>
                <div class="pub-product-info">
                    <a href="{{ route('public.show', $product) }}" class="pub-product-name">{{ $product->name }}</a>
                    <div class="pub-product-footer">
                        <span class="pub-price">Br{{ number_format($product->selling_price, 2) }}</span>
                        <form action="{{ route('public.cart.add', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="pub-add-btn">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif

{{-- ─── Latest Arrivals ────────────────────────────── --}}
<div class="pub-stripe">
    <div class="pub-container">
        <div class="pub-section-header">
            <h2>Latest Arrivals <small>Fresh out of production</small></h2>
            <a href="{{ route('public.shop', ['sort'=>'newest']) }}" class="pub-section-link">View All &rarr;</a>
        </div>
        <div class="pub-grid pub-grid-4">
            @forelse($latestProducts as $product)
            <div class="pub-product-card">
                <a href="{{ route('public.show', $product) }}" class="pub-product-img">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                    @else
                        <div class="pub-product-img-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    @endif
                    <div class="pub-product-badges"><span class="pub-badge pub-badge-new">New</span></div>
                    @if($product->category)<div class="pub-product-cat"><span class="pub-badge pub-badge-cyan">{{ $product->category->name }}</span></div>@endif
                </a>
                <div class="pub-product-info">
                    <a href="{{ route('public.show', $product) }}" class="pub-product-name">{{ $product->name }}</a>
                    <div class="pub-stars">
                        @for($i=1;$i<=5;$i++)<svg class="pub-star-{{ $i<=floor($product->rating)?'on':'off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                    </div>
                    <div class="pub-product-footer">
                        <span class="pub-price">Br{{ number_format($product->selling_price, 2) }}</span>
                        <form action="{{ route('public.cart.add', $product) }}" method="POST">
                            @csrf<button type="submit" class="pub-add-btn"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:3rem;color:#475569;">No products available yet.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ─── Top Rated ───────────────────────────────────── --}}
@if($topRatedProducts->count() > 0)
<div class="pub-container" style="padding-top:4rem;padding-bottom:5rem;">
    <div class="pub-section-header">
        <h2>Highest Rated <small>Loved by our clients</small></h2>
    </div>
    <div class="pub-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));">
        @foreach($topRatedProducts as $product)
        <a href="{{ route('public.show', $product) }}" class="pub-toprated-item">
            <div class="pub-toprated-img">
                @if($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                @else
                    <svg style="width:28px;height:28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @endif
            </div>
            <div class="pub-toprated-info">
                <div class="pub-stars" style="margin-bottom:.3rem;">
                    @for($i=1;$i<=5;$i++)<svg class="pub-star-{{ $i<=floor($product->rating)?'on':'off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                    <span class="pub-stars-count">({{ $product->reviews_count }})</span>
                </div>
                <div class="pub-toprated-name">{{ $product->name }}</div>
                <div class="pub-toprated-price">Br{{ number_format($product->selling_price, 2) }}</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection

