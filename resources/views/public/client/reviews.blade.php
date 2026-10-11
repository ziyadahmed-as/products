@extends('layouts.public')
@section('title', 'My Reviews')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        @include('public.client._sidebar', ['active' => 'reviews'])

        <div style="flex:1;min-width:300px;">
            <h1 style="font-size:2rem;font-weight:800;color:var(--text-1);margin-bottom:1.5rem;">My Reviews</h1>

            @if($reviews->isEmpty())
                <div style="background:var(--bg-card);padding:3rem;border-radius:14px;border:1px solid var(--border);text-align:center;">
                    <svg style="width:48px;height:48px;color:var(--text-3);margin:0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <div style="color:var(--text-2);margin-bottom:1rem;">You haven't reviewed any products yet.</div>
                    <a href="{{ route('client.orders') }}" class="pub-btn pub-btn-secondary">Rate your past orders</a>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    @foreach($reviews as $review)
                    <div style="background:var(--bg-card);padding:1.5rem;border-radius:12px;border:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                            <div style="display:flex;gap:1rem;align-items:center;">
                                <div style="width:52px;height:52px;background:var(--bg-card2);border-radius:10px;overflow:hidden;flex-shrink:0;border:1px solid var(--border);">
                                    @if($review->product && $review->product->image)
                                        <img src="{{ asset('storage/' . $review->product->image) }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-3);">
                                        <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight:700;color:var(--text-1);font-size:.95rem;">{{ $review->product->name ?? 'Unknown Product' }}</div>
                                    <div style="color:var(--text-3);font-size:.75rem;margin-top:.2rem;">
                                        Reviewed {{ $review->created_at->diffForHumans() }}
                                        @if($review->order_reference)
                                        &bull; <span style="font-family:monospace;">{{ $review->order_reference }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div style="color:#fbbf24;font-size:1.1rem;letter-spacing:.1em;">
                                @for($i=1;$i<=5;$i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
                                <span style="font-size:.8rem;color:var(--text-3);margin-left:.25rem;">({{ $review->rating }}/5)</span>
                            </div>
                        </div>
                        @if($review->comment)
                        <div style="background:var(--bg-card2);padding:1rem;border-radius:8px;color:var(--text-2);font-size:.875rem;font-style:italic;border-left:3px solid rgba(8,145,178,.3);">
                            "{{ $review->comment }}"
                        </div>
                        @endif
                        @if($review->product)
                        <div style="margin-top:.75rem;">
                            <a href="{{ route('public.show', $review->product) }}" style="font-size:.78rem;color:var(--cyan);font-weight:600;">View Product →</a>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
