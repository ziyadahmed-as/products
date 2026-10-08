@extends('layouts.public')
@section('title', 'My Orders')

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
                    <a href="{{ route('client.orders') }}" style="padding:0.75rem 1.5rem; color:var(--text-1); font-weight:600; border-left:3px solid var(--cyan); background:rgba(8,145,178,0.05);">My Orders</a>
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
            <h1 style="font-size:2rem; font-weight:800; color:var(--text-1); margin-bottom:1.5rem;">My Orders</h1>
            
            @if($orders->isEmpty())
                <div style="background:var(--bg-card); padding:3rem; border-radius:12px; border:1px solid var(--border); text-align:center;">
                    <div style="color:var(--text-2); margin-bottom:1rem;">You have not placed any orders yet.</div>
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary">Browse Shop</a>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:1rem;">
                    @foreach($orders as $order)
                        <div style="background:var(--bg-card); padding:1.5rem; border-radius:12px; border:1px solid var(--border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                            <div>
                                <div style="font-weight:700; color:var(--text-1); font-size:1.1rem; margin-bottom:0.25rem;">{{ $order->reference }}</div>
                                <div style="color:var(--text-2); font-size:0.85rem;">Date: {{ $order->created_at->format('d M Y') }} &bull; Total: Br{{ number_format($order->total, 2) }}</div>
                            </div>
                            <div style="display:flex; gap:1rem; align-items:center;">
                                <span style="padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem; background:var(--bg-card2); color:var(--text-1);">{{ $order->status }}</span>
                                <span style="padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem; background:{{ $order->payment_status == 'Paid' ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)' }}; color:{{ $order->payment_status == 'Paid' ? '#10b981' : '#f59e0b' }};">{{ $order->payment_status }}</span>
                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:0.5rem 1rem;">View Order</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
