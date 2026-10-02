@extends('layouts.public')
@section('title', $product->name)

@section('content')
<div class="pub-container" style="padding-top:2.5rem;padding-bottom:5rem;">

    {{-- Breadcrumb --}}
    <nav class="pub-breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        <a href="{{ route('public.shop') }}">Shop</a>
        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        <span>{{ Str::limit($product->name, 40) }}</span>
    </nav>

    {{-- Main product section --}}
    <div class="pub-product-detail">

        {{-- Image --}}
        <div class="pub-detail-img">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
            @else
                <div style="text-align:center;color:#1e293b;">
                    <svg style="width:80px;height:80px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p style="font-size:.8rem;margin-top:.75rem;color:var(--text-3);">No image available</p>
                </div>
            @endif
            @if($product->is_featured)
                <div style="position:absolute;top:1rem;left:1rem;">
                    <span class="pub-badge pub-badge-featured" style="font-size:.75rem;padding:.35rem .9rem;">★ Featured</span>
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div class="pub-detail-info">

            @if($product->category)
                <div class="pub-detail-category">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    {{ $product->category->name }}
                </div>
            @endif

            <h1 class="pub-detail-title">{{ $product->name }}</h1>

            <div class="pub-detail-meta">
                <span>
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    SKU: <strong style="color:#fff;font-family:monospace;">{{ $product->sku }}</strong>
                </span>
                @if($product->unit)
                <span>Unit: <strong style="color:#fff;">{{ $product->unit->name }}</strong></span>
                @endif
                @if($product->package_size)
                <span>Pack: <strong style="color:#fff;">{{ $product->package_size }}</strong></span>
                @endif
            </div>

            <div class="pub-detail-rating">
                @for($i=1;$i<=5;$i++)
                    <svg class="pub-star-{{ $i<=floor($product->rating)?'on':'off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                @endfor
                <span class="pub-rating-score">{{ $product->rating }}</span>
                <span class="pub-rating-count">({{ $product->reviews_count }} reviews)</span>
            </div>

            <div class="pub-detail-price">${{ number_format($product->selling_price, 2) }}</div>

            @if($product->specification)
            <div class="pub-detail-spec">
                <h4>Specifications</h4>
                {{ $product->specification }}
            </div>
            @endif

            <form action="{{ route('public.cart.add', $product) }}" method="POST" class="pub-add-form">
                @csrf
                <div>
                    <label class="pub-filter-label" style="margin-bottom:.4rem;display:block;">Qty</label>
                    <input type="number" name="quantity" value="1" min="1" max="999" class="pub-qty">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;">
                    <button type="submit" class="pub-btn pub-btn-primary pub-btn-full" style="padding:1rem;font-size:1rem;gap:.75rem;">
                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Add to Cart
                    </button>
                </div>
            </form>

            <div class="pub-trust">
                <div class="pub-trust-item">
                    <svg style="color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Quality Guaranteed</span>
                </div>
                <div class="pub-trust-item">
                    <svg style="color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Secure Checkout</span>
                </div>
                <div class="pub-trust-item">
                    <svg style="color:#f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>24/7 Support</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if($relatedProducts->count() > 0)
    <div style="margin-top:4rem;">
        <div class="pub-section-header">
            <h2>You May Also Like</h2>
            @if($product->category)
            <a href="{{ route('public.shop', ['category_id' => $product->category_id]) }}" class="pub-section-link">More in {{ $product->category->name }} &rarr;</a>
            @endif
        </div>
        <div class="pub-grid pub-grid-4">
            @foreach($relatedProducts as $rel)
            <div class="pub-product-card">
                <a href="{{ route('public.show', $rel) }}" class="pub-product-img">
                    @if($rel->image)<img src="{{ asset('storage/'.$rel->image) }}" alt="{{ $rel->name }}">
                    @else<div class="pub-product-img-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    @endif
                </a>
                <div class="pub-product-info">
                    <a href="{{ route('public.show', $rel) }}" class="pub-product-name">{{ $rel->name }}</a>
                    <div class="pub-stars">@for($i=1;$i<=5;$i++)<svg class="pub-star-{{ $i<=floor($rel->rating)?'on':'off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                    <div class="pub-product-footer">
                        <span class="pub-price">${{ number_format($rel->selling_price, 2) }}</span>
                        <form action="{{ route('public.cart.add', $rel) }}" method="POST">@csrf<button type="submit" class="pub-add-btn"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></button></form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
