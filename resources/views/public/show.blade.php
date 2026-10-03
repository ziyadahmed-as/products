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

{{-- ════════════ REVIEWS SECTION ════════════ --}}
<div style="margin-top:4rem; max-width:56rem; margin-left:auto; margin-right:auto;" id="reviews">

    {{-- Success message --}}
    @if(session('review_success'))
    <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); border-radius:1rem; padding:1rem 1.5rem; margin-bottom:2rem; color:#34d399; display:flex; align-items:center; gap:0.75rem;">
        <svg style="width:1.25rem;height:1.25rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('review_success') }}
    </div>
    @endif

    {{-- Section header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
        <div>
            <h2 style="font-family:'Outfit',sans-serif; font-size:1.75rem; font-weight:800; color:#fff; margin:0;">Customer Reviews</h2>
            <p style="color:#64748b; margin:0.25rem 0 0;">
                @if($product->reviews_count > 0)
                    {{ $product->reviews_count }} review{{ $product->reviews_count != 1 ? 's' : '' }} &mdash;
                    <span style="color:#fbbf24;">
                        @for($i=1;$i<=5;$i++)
                            {{ $i <= round($product->rating) ? '★' : '☆' }}
                        @endfor
                        {{ number_format($product->rating, 1) }} / 5
                    </span>
                @else
                    No reviews yet — be the first!
                @endif
            </p>
        </div>
    </div>

    {{-- Existing approved reviews --}}
    @php $approvedReviews = $product->reviews()->where('is_approved', true)->latest()->get(); @endphp
    @if($approvedReviews->count() > 0)
    <div style="display:flex; flex-direction:column; gap:1rem; margin-bottom:2.5rem;">
        @foreach($approvedReviews as $review)
        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.07); border-radius:1.25rem; padding:1.5rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:2.5rem; height:2.5rem; border-radius:50%; background:linear-gradient(135deg,#0ea5e9,#6366f1); display:flex; align-items:center; justify-content:center; font-family:'Outfit',sans-serif; font-weight:700; font-size:1rem; color:#fff; flex-shrink:0;">
                        {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600; color:#e2e8f0; font-size:0.9rem;">{{ $review->reviewer_name }}</div>
                        @if($review->order_reference)
                        <div style="font-size:0.7rem; color:#34d399; margin-top:0.1rem;">✓ Verified Purchase · {{ $review->order_reference }}</div>
                        @endif
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.25rem;">
                    <div style="color:#fbbf24; font-size:1rem; letter-spacing:0.05em;">
                        @for($i=1;$i<=5;$i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
                    </div>
                    <div style="font-size:0.7rem; color:#475569;">{{ $review->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @if($review->comment)
            <p style="margin:1rem 0 0; color:#94a3b8; font-size:0.875rem; line-height:1.6;">{{ $review->comment }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Write a Review Form ── --}}
    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(14,165,233,0.2); border-radius:1.5rem; padding:2rem; box-shadow:0 8px 32px rgba(14,165,233,0.05);">
        <h3 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:700; color:#fff; margin:0 0 0.5rem;">Write a Review</h3>
        <p style="color:#64748b; font-size:0.85rem; margin:0 0 1.5rem;">Share your experience with this product. Your order reference helps verify your purchase.</p>

        <form method="POST" action="{{ route('public.review.submit', $product) }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#e2e8f0; margin-bottom:0.4rem;">Your Name *</label>
                    <input type="text" name="reviewer_name" value="{{ old('reviewer_name') }}" required
                        style="width:100%; background:rgba(10,15,29,0.8); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:0.75rem; padding:0.75rem 1rem; font-size:0.875rem; outline:none; font-family:'Inter',sans-serif;"
                        placeholder="John Doe">
                    @error('reviewer_name')<p style="color:#f87171;font-size:0.75rem;margin-top:0.25rem;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#e2e8f0; margin-bottom:0.4rem;">Email (optional)</label>
                    <input type="email" name="reviewer_email" value="{{ old('reviewer_email') }}"
                        style="width:100%; background:rgba(10,15,29,0.8); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:0.75rem; padding:0.75rem 1rem; font-size:0.875rem; outline:none; font-family:'Inter',sans-serif;"
                        placeholder="john@email.com">
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#e2e8f0; margin-bottom:0.4rem;">Order Reference (optional)</label>
                <input type="text" name="order_reference" value="{{ old('order_reference') }}"
                    style="width:100%; background:rgba(10,15,29,0.8); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:0.75rem; padding:0.75rem 1rem; font-size:0.875rem; outline:none; font-family:'Inter',sans-serif;"
                    placeholder="e.g. ORD-A1B2C3">
                <p style="font-size:0.7rem; color:#475569; margin-top:0.25rem;">Enter your order ID from checkout confirmation to mark as verified purchase.</p>
            </div>

            {{-- Star Rating Picker --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#e2e8f0; margin-bottom:0.75rem;">Your Rating *</label>
                <div class="star-picker" style="display:flex; gap:0.5rem;">
                    @for($i=1;$i<=5;$i++)
                    <label style="cursor:pointer;">
                        <input type="radio" name="rating" value="{{ $i }}" required style="display:none;" {{ old('rating') == $i ? 'checked' : '' }}>
                        <svg class="star-icon" data-val="{{ $i }}" style="width:2rem; height:2rem; color:#334155; transition:color 0.15s; cursor:pointer;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </label>
                    @endfor
                </div>
                @error('rating')<p style="color:#f87171;font-size:0.75rem;margin-top:0.25rem;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#e2e8f0; margin-bottom:0.4rem;">Your Review</label>
                <textarea name="comment" rows="4"
                    style="width:100%; background:rgba(10,15,29,0.8); border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:0.75rem; padding:0.75rem 1rem; font-size:0.875rem; outline:none; resize:vertical; font-family:'Inter',sans-serif;"
                    placeholder="Tell others what you think about this product...">{{ old('comment') }}</textarea>
            </div>

            <button type="submit"
                style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.875rem 2rem; background:linear-gradient(135deg,#0284c7,#0ea5e9); color:#fff; border:none; border-radius:0.875rem; font-family:'Outfit',sans-serif; font-weight:700; font-size:0.9rem; cursor:pointer; transition:all 0.3s; box-shadow:0 4px 15px rgba(14,165,233,0.3);">
                <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Submit Review
            </button>
        </form>
    </div>
</div>

<script>
// Interactive star rating picker
document.querySelectorAll('.star-icon').forEach(star => {
    star.addEventListener('mouseenter', function() {
        const val = parseInt(this.dataset.val);
        document.querySelectorAll('.star-icon').forEach((s, idx) => {
            s.style.color = (idx + 1) <= val ? '#fbbf24' : '#334155';
        });
    });
    star.parentElement.addEventListener('click', function() {
        const radio = this.querySelector('input[type=radio]');
        const val = parseInt(radio.value);
        document.querySelectorAll('.star-icon').forEach((s, idx) => {
            s.style.color = (idx + 1) <= val ? '#fbbf24' : '#334155';
        });
    });
});
document.querySelector('.star-picker').addEventListener('mouseleave', function() {
    const checked = document.querySelector('input[name=rating]:checked');
    const val = checked ? parseInt(checked.value) : 0;
    document.querySelectorAll('.star-icon').forEach((s, idx) => {
        s.style.color = (idx + 1) <= val ? '#fbbf24' : '#334155';
    });
});
</script>

</div>
@endsection

