@extends('layouts.public')
@section('title', 'About Us')

@section('content')
<div class="pub-about-hero pub-fade">
    <div class="pub-hero-label" style="margin-bottom:1rem;display:inline-block;">Our Story</div>
    <h1>{!! \App\Models\Setting::get('about_title', 'About <span class="pub-text-gradient">Albareck</span>') !!}</h1>
    <p style="font-size:1.1rem;color:var(--text-2);line-height:1.7;">
        {{ \App\Models\Setting::get('about_description', 'We are a premier provider of high-quality manufactured goods and raw materials. Our mission is to deliver excellence to every business we partner with.') }}
    </p>
</div>

<div class="pub-container" style="padding-bottom:6rem;">
    <div class="pub-grid" style="grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
        
        <div class="pub-fade pub-fade-d1">
            <h2 style="font-size:1.75rem;font-weight:800;margin-bottom:1rem;">Our Vision</h2>
            <p style="color:var(--text-2);line-height:1.7;margin-bottom:2.5rem;">
                {{ \App\Models\Setting::get('about_vision', 'To be the global standard in industrial supply and manufacturing quality, driving innovation and efficiency for our clients worldwide.') }}
            </p>
            
            <h2 style="font-size:1.75rem;font-weight:800;margin-bottom:1rem;">Our Mission</h2>
            <p style="color:var(--text-2);line-height:1.7;margin-bottom:2.5rem;">
                {{ \App\Models\Setting::get('about_mission', 'Providing robust, reliable, and sustainable products through advanced manufacturing processes and stringent quality control.') }}
            </p>

            <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:1rem;">Why Choose Us?</h2>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:.75rem;">
                <li style="display:flex;align-items:center;gap:.75rem;color:var(--text-2);">
                    <svg style="width:20px;height:20px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Uncompromising Quality Assurance
                </li>
                <li style="display:flex;align-items:center;gap:.75rem;color:var(--text-2);">
                    <svg style="width:20px;height:20px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    State-of-the-Art Manufacturing
                </li>
                <li style="display:flex;align-items:center;gap:.75rem;color:var(--text-2);">
                    <svg style="width:20px;height:20px;color:var(--cyan);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Global Supply Chain Network
                </li>
            </ul>
        </div>

        <div class="pub-fade pub-fade-d2">
            <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem;text-align:center;">Our Highly Rated Products</h2>
            <div class="pub-grid pub-grid-4">
                @foreach($topRatedProducts as $product)
                    <a href="{{ route('public.show', $product) }}" class="pub-product-card">
                        <div class="pub-product-img">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                            @else
                                <div class="pub-product-img-placeholder">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="pub-product-info">
                            <div class="pub-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="{{ $i <= $product->rating ? 'pub-star-on' : 'pub-star-off' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                                <span class="pub-stars-count">({{ $product->reviews_count ?? rand(5,50) }})</span>
                            </div>
                            <h3 class="pub-product-name">{{ $product->name }}</h3>
                            <div class="pub-product-footer">
                                <span class="pub-price">Br{{ number_format($product->selling_price, 2) }}</span>
                                <span class="pub-add-btn"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

