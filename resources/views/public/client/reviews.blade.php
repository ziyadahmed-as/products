@extends('layouts.public')
@section('title', 'My Reviews')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex; gap:2rem; flex-wrap:wrap;">
        
        {{-- Sidebar --}}
        <div style="width:250px; flex-shrink:0;">
            <div style="background:var(--bg-card); border-radius:12px; border:1px solid var(--border); overflow:hidden;">
                <div style="padding:1.5rem; border-bottom:1px solid var(--border);">
                    <div style="font-weight:700; color:var(--text-1);">{{ auth()->user()->name }}</div>
                    <div style="font-size:0.85rem; color:var(--text-2);">{{ auth()->user()->email }}</div>
                </div>
                <div style="display:flex; flex-direction:column; padding:0.5rem 0;">
                    <a href="{{ route('client.dashboard') }}" style="padding:0.75rem 1.5rem; color:var(--text-2);">Dashboard</a>
                    <a href="{{ route('client.orders') }}" style="padding:0.75rem 1.5rem; color:var(--text-2);">My Orders</a>
                    <a href="{{ route('client.reviews') }}" style="padding:0.75rem 1.5rem; color:var(--text-1); font-weight:600; border-left:3px solid var(--cyan); background:rgba(8,145,178,0.05);">My Reviews</a>
                    <form action="{{ route('client.logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" style="width:100%; text-align:left; padding:0.75rem 1.5rem; color:var(--text-2); background:none; border:none; cursor:pointer;">Log Out</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div style="flex:1; min-width:300px;">
            <h1 style="font-size:2rem; font-weight:800; color:var(--text-1); margin-bottom:1.5rem;">My Reviews</h1>
            
            @if($reviews->isEmpty())
                <div style="background:var(--bg-card); padding:3rem; border-radius:12px; border:1px solid var(--border); text-align:center;">
                    <div style="color:var(--text-2); margin-bottom:1rem;">You haven't written any reviews yet.</div>
                    <a href="{{ route('client.orders') }}" class="pub-btn pub-btn-secondary">Rate your past orders</a>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:1rem;">
                    @foreach($reviews as $review)
                        <div style="background:var(--bg-card); padding:1.5rem; border-radius:12px; border:1px solid var(--border); display:flex; flex-direction:column; gap:1rem;">
                            
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div style="display:flex; gap:1rem; align-items:center;">
                                    <div style="width:50px; height:50px; background:var(--bg-card2); border-radius:8px; overflow:hidden;">
                                        @if($review->product && $review->product->image)
                                            <img src="{{ asset('storage/' . $review->product->image) }}" style="width:100%; height:100%; object-fit:cover;">
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight:700; color:var(--text-1);">{{ $review->product->name ?? 'Unknown Product' }}</div>
                                        <div style="color:var(--text-2); font-size:0.8rem;">Reviewed on {{ $review->created_at->format('d M Y') }} &bull; Order: {{ $review->order_reference }}</div>
                                    </div>
                                </div>
                                <div style="color:#fbbf24; font-size:1.1rem; letter-spacing:0.1em;">
                                    @for($i=1;$i<=5;$i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
                                </div>
                            </div>
                            
                            @if($review->comment)
                                <div style="background:var(--bg-card2); padding:1rem; border-radius:8px; color:var(--text-2); font-size:0.9rem; font-style:italic;">
                                    "{{ $review->comment }}"
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
