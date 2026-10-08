@extends('layouts.public')
@section('title', 'Client Dashboard')

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
                    <a href="{{ route('client.dashboard') }}" style="padding:0.75rem 1.5rem; color:var(--text-1); font-weight:600; border-left:3px solid var(--cyan); background:rgba(8,145,178,0.05);">Dashboard</a>
                    <a href="{{ route('client.orders') }}" style="padding:0.75rem 1.5rem; color:var(--text-2);">My Orders</a>
                    <a href="{{ route('client.reviews') }}" style="padding:0.75rem 1.5rem; color:var(--text-2);">My Reviews</a>
                    <form action="{{ route('client.logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" style="width:100%; text-align:left; padding:0.75rem 1.5rem; color:var(--text-2); background:none; border:none; cursor:pointer;">Log Out</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div style="flex:1; min-width:300px;">
            <h1 style="font-size:2rem; font-weight:800; color:var(--text-1); margin-bottom:1.5rem;">Dashboard</h1>
            
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div style="background:var(--bg-card); padding:2rem; border-radius:12px; border:1px solid var(--border);">
                    <div style="font-size:0.9rem; color:var(--text-2); margin-bottom:0.5rem;">Total Orders</div>
                    <div style="font-size:2rem; font-weight:800; color:var(--cyan);">{{ \App\Models\Sale::where('client_id', auth()->id())->count() }}</div>
                </div>
                <div style="background:var(--bg-card); padding:2rem; border-radius:12px; border:1px solid var(--border);">
                    <div style="font-size:0.9rem; color:var(--text-2); margin-bottom:0.5rem;">My Reviews</div>
                    <div style="font-size:2rem; font-weight:800; color:var(--cyan);">{{ \App\Models\ProductReview::where('reviewer_email', auth()->user()->email)->count() }}</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
