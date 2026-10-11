@extends('layouts.public')
@section('title', 'My Dashboard')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        {{-- ── Sidebar ── --}}
        @include('public.client._sidebar', ['active' => 'dashboard'])

        {{-- ── Main Content ── --}}
        <div style="flex:1;min-width:300px;">

            @if(session('success'))
            <div style="background:rgba(16,185,129,.1);color:#10b981;padding:1rem;border-radius:10px;margin-bottom:1.5rem;border:1px solid rgba(16,185,129,.2);display:flex;align-items:center;gap:.5rem;">
                <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif

            <h1 style="font-size:2rem;font-weight:800;color:var(--text-1);margin-bottom:1.75rem;">Dashboard</h1>

            {{-- Stats Cards --}}
            @php
                $totalOrders  = \App\Models\Sale::where('client_id', auth()->id())->where('type','customer_order')->count();
                $totalSpent   = \App\Models\Sale::where('client_id', auth()->id())->where('type','customer_order')->sum('total');
                $pendingOrders= \App\Models\Sale::where('client_id', auth()->id())->where('type','customer_order')->where('status','Pending')->count();
                $myReviews    = \App\Models\ProductReview::where(function($q){ $q->where('user_id', auth()->id())->orWhere('reviewer_email', auth()->user()->email); })->count();
            @endphp

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.25rem;margin-bottom:2.5rem;">
                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="font-size:.8rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Total Orders</div>
                    <div style="font-size:2.25rem;font-weight:900;color:var(--cyan);line-height:1;">{{ $totalOrders }}</div>
                </div>
                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="font-size:.8rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Total Spent</div>
                    <div style="font-size:2.25rem;font-weight:900;color:#a78bfa;line-height:1;">Br{{ number_format($totalSpent, 0) }}</div>
                </div>
                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="font-size:.8rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Pending</div>
                    <div style="font-size:2.25rem;font-weight:900;color:#f59e0b;line-height:1;">{{ $pendingOrders }}</div>
                </div>
                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="font-size:.8rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Reviews</div>
                    <div style="font-size:2.25rem;font-weight:900;color:#34d399;line-height:1;">{{ $myReviews }}</div>
                </div>
            </div>

            {{-- Recent Orders --}}
            @php
                $recentOrders = \App\Models\Sale::where('client_id', auth()->id())->where('type','customer_order')->latest()->limit(5)->get();
            @endphp

            <div style="background:var(--bg-card);border-radius:14px;border:1px solid var(--border);overflow:hidden;">
                <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-weight:700;color:var(--text-1);font-size:1rem;">Recent Orders</div>
                    <a href="{{ route('client.orders') }}" style="font-size:.8rem;color:var(--cyan);font-weight:600;">View all →</a>
                </div>

                @if($recentOrders->isEmpty())
                <div style="padding:3rem;text-align:center;">
                    <div style="color:var(--text-2);margin-bottom:1rem;">No orders yet.</div>
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary" style="padding:.6rem 1.5rem;">Start Shopping</a>
                </div>
                @else
                @foreach($recentOrders as $ord)
                <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;{{ $loop->last ? 'border:none;' : '' }}">
                    <div>
                        <div style="font-weight:700;color:var(--text-1);font-size:.9rem;font-family:monospace;">{{ $ord->reference }}</div>
                        <div style="color:var(--text-3);font-size:.78rem;margin-top:.15rem;">{{ $ord->created_at->format('d M Y') }} &bull; Br{{ number_format($ord->total, 2) }}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        @php
                            $statusColors = ['Pending'=>'#f59e0b','Fulfilled'=>'#0ea5e9','Completed'=>'#10b981','Cancelled'=>'#ef4444'];
                            $sc = $statusColors[$ord->status] ?? '#94a3b8';
                        @endphp
                        <span style="padding:.2rem .65rem;border-radius:50px;font-size:.72rem;font-weight:700;background:{{ $sc }}22;color:{{ $sc }};">{{ $ord->status }}</span>
                        <a href="{{ route('client.orders.show', $ord) }}" class="pub-btn pub-btn-secondary" style="padding:.35rem .85rem;font-size:.78rem;">View</a>
                    </div>
                </div>
                @endforeach
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
