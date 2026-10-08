@extends('layouts.public')
@section('title', 'Order ' . $order->reference)

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    
    <div style="margin-bottom:2rem;">
        <a href="{{ route('client.orders') }}" style="color:var(--text-2); font-size:0.9rem; text-decoration:none;">&larr; Back to My Orders</a>
    </div>

    <h1 style="font-size:2rem; font-weight:800; color:var(--text-1); margin-bottom:0.5rem;">Order: {{ $order->reference }}</h1>
    <div style="color:var(--text-2); margin-bottom:2rem;">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</div>

    @if(session('success'))
        <div style="background:rgba(16,185,129,0.1); color:#10b981; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div style="background:rgba(239,68,68,0.1); color:#ef4444; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:flex; gap:2rem; flex-wrap:wrap;">
        
        {{-- Items List --}}
        <div style="flex:2; min-width:300px;">
            <div style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border); overflow:hidden;">
                <div style="padding:1.5rem; border-bottom:1px solid var(--border); font-weight:700; color:var(--text-1); font-size:1.1rem;">Purchased Products</div>
                
                <div style="padding:1.5rem; display:flex; flex-direction:column; gap:1.5rem;">
                    @foreach($order->lines as $line)
                        @php
                            $isRated = \App\Models\ProductReview::where('product_id', $line->product_id)
                                ->where('order_reference', $order->reference)
                                ->exists();
                        @endphp
                        
                        <div style="display:flex; gap:1.5rem; align-items:flex-start; padding-bottom:1.5rem; border-bottom:1px solid var(--border); {{ $loop->last ? 'border:none; padding-bottom:0;' : '' }}">
                            <div style="width:80px; height:80px; background:var(--bg-card2); border-radius:8px; overflow:hidden; flex-shrink:0;">
                                @if($line->product && $line->product->image)
                                    <img src="{{ asset('storage/' . $line->product->image) }}" style="width:100%; height:100%; object-fit:cover;">
                                @endif
                            </div>
                            
                            <div style="flex:1;">
                                <div style="font-weight:700; color:var(--text-1); font-size:1.1rem; margin-bottom:0.25rem;">{{ $line->product->name ?? 'Unknown Product' }}</div>
                                <div style="color:var(--text-2); font-size:0.9rem; margin-bottom:1rem;">Qty: {{ $line->quantity }} &bull; Br{{ number_format($line->unit_price, 2) }} each &bull; Total: Br{{ number_format($line->total, 2) }}</div>
                                
                                @if($isRated)
                                    <div style="color:#10b981; font-size:0.85rem; font-weight:600; display:flex; align-items:center; gap:0.25rem;">
                                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        You have rated this product
                                    </div>
                                @elseif($line->product)
                                    {{-- Rating Form --}}
                                    <div style="background:var(--bg-card2); padding:1rem; border-radius:8px; margin-top:0.5rem;">
                                        <div style="font-weight:600; font-size:0.9rem; color:var(--text-1); margin-bottom:0.5rem;">Rate this product:</div>
                                        <form action="{{ route('client.orders.rate', [$order, $line->product]) }}" method="POST">
                                            @csrf
                                            <div style="display:flex; gap:0.5rem; margin-bottom:0.75rem; flex-direction:row-reverse; justify-content:flex-end;" class="star-rating">
                                                <input type="radio" id="star5-{{$line->id}}" name="rating" value="5" required /><label for="star5-{{$line->id}}" title="5 stars">★</label>
                                                <input type="radio" id="star4-{{$line->id}}" name="rating" value="4" /><label for="star4-{{$line->id}}" title="4 stars">★</label>
                                                <input type="radio" id="star3-{{$line->id}}" name="rating" value="3" /><label for="star3-{{$line->id}}" title="3 stars">★</label>
                                                <input type="radio" id="star2-{{$line->id}}" name="rating" value="2" /><label for="star2-{{$line->id}}" title="2 stars">★</label>
                                                <input type="radio" id="star1-{{$line->id}}" name="rating" value="1" /><label for="star1-{{$line->id}}" title="1 star">★</label>
                                            </div>
                                            <textarea name="comment" rows="2" placeholder="Write a comment... (optional)" class="pub-input" style="width:100%; font-size:0.85rem; padding:0.5rem; margin-bottom:0.75rem;"></textarea>
                                            <button type="submit" class="pub-btn pub-btn-primary" style="padding:0.4rem 1rem; font-size:0.85rem;">Submit Review</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Order Summary --}}
        <div style="flex:1; min-width:250px; max-width:350px;">
            <div style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border); overflow:hidden; position:sticky; top:100px;">
                <div style="padding:1.5rem; border-bottom:1px solid var(--border); font-weight:700; color:var(--text-1); font-size:1.1rem;">Order Summary</div>
                <div style="padding:1.5rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; color:var(--text-2);">
                        <span>Status</span>
                        <span style="font-weight:600; color:var(--text-1);">{{ $order->status }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; color:var(--text-2);">
                        <span>Payment</span>
                        <span style="font-weight:600; color:{{ $order->payment_status == 'Paid' ? '#10b981' : '#f59e0b' }};">{{ $order->payment_status }}</span>
                    </div>
                    
                    <div style="height:1px; background:var(--border); margin-bottom:1.5rem;"></div>
                    
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.75rem; color:var(--text-2);">
                        <span>Subtotal</span>
                        <span>Br{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem; color:var(--text-2);">
                        <span>Tax / Discount</span>
                        <span>-</span>
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.25rem; color:var(--text-1);">
                        <span>Total</span>
                        <span>Br{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.star-rating {
  font-size: 1.5rem;
  color: var(--text-3);
}
.star-rating input {
  display: none;
}
.star-rating label {
  cursor: pointer;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
  color: #fbbf24;
}
</style>
@endsection
