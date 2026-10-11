@extends('layouts.public')
@section('title', 'My Orders')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        @include('public.client._sidebar', ['active' => 'orders'])

        <div style="flex:1;min-width:300px;">
            <h1 style="font-size:2rem;font-weight:800;color:var(--text-1);margin-bottom:1.5rem;">My Orders</h1>

            @if($orders->isEmpty())
                <div style="background:var(--bg-card);padding:3rem;border-radius:14px;border:1px solid var(--border);text-align:center;">
                    <svg style="width:48px;height:48px;color:var(--text-3);margin:0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <div style="color:var(--text-2);margin-bottom:1rem;">You haven't placed any orders yet.</div>
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary">Browse Shop</a>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:.875rem;">
                    @foreach($orders as $order)
                    @php
                        $statusColors = ['Pending'=>'#f59e0b','Fulfilled'=>'#0ea5e9','Completed'=>'#10b981','Cancelled'=>'#ef4444'];
                        $sc = $statusColors[$order->status] ?? '#94a3b8';
                    @endphp
                    <div style="background:var(--bg-card);padding:1.5rem;border-radius:12px;border:1px solid var(--border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;">
                        <div>
                            <div style="font-weight:700;color:var(--text-1);font-size:1rem;font-family:monospace;margin-bottom:.3rem;">{{ $order->reference }}</div>
                            <div style="color:var(--text-3);font-size:.82rem;">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                                &bull; <strong style="color:var(--text-2);">Br{{ number_format($order->total, 2) }}</strong>
                                &bull; {{ $order->lines->count() ?? '?' }} item(s)
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                            <span style="padding:.25rem .75rem;border-radius:50px;font-size:.72rem;font-weight:700;background:{{ $sc }}22;color:{{ $sc }};">{{ $order->status }}</span>
                            <span style="padding:.25rem .75rem;border-radius:50px;font-size:.72rem;font-weight:700;background:{{ $order->payment_status == 'Paid' ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $order->payment_status == 'Paid' ? '#10b981' : '#f59e0b' }};">{{ $order->payment_status }}</span>
                            <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.4rem 1rem;font-size:.82rem;">View Order →</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
