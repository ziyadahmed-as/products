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
                <div style="display:flex;flex-direction:column;gap:1.5rem;">
                    @foreach($orders as $order)
                    @php
                        $statusColors = ['Pending'=>'#f59e0b','Fulfilled'=>'#0ea5e9','Completed'=>'#10b981','Cancelled'=>'#ef4444'];
                        $sc = $statusColors[$order->status] ?? '#94a3b8';
                    @endphp
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.03);">

                        {{-- Order Header --}}
                        <div style="background:var(--bg-card2);padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                            <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Order Reference</div>
                                    <div style="font-family:monospace;font-weight:800;color:var(--text-1);font-size:1rem;">{{ $order->reference }}</div>
                                </div>
                                <div style="width:1px;height:24px;background:var(--border);"></div>
                                <div>
                                    <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Order Date</div>
                                    <div style="font-size:.85rem;color:var(--text-2);font-weight:600;">{{ $order->created_at->format('d M Y · h:i A') }}</div>
                                </div>
                                <div style="width:1px;height:24px;background:var(--border);"></div>
                                <div>
                                    <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Order Total</div>
                                    <div style="font-size:.95rem;font-weight:900;color:var(--text-1);">Br{{ number_format($order->total, 2) }}</div>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                                <span style="padding:.28rem .85rem;border-radius:50px;font-size:.75rem;font-weight:700;background:{{ $sc }}22;color:{{ $sc }};border:1px solid {{ $sc }}44;">
                                    ● {{ $order->status }}
                                </span>
                                <span style="padding:.28rem .85rem;border-radius:50px;font-size:.75rem;font-weight:700;background:{{ $order->payment_status === 'Paid' ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $order->payment_status === 'Paid' ? '#10b981' : '#f59e0b' }};border:1px solid {{ $order->payment_status === 'Paid' ? 'rgba(16,185,129,.3)' : 'rgba(245,158,11,.3)' }};">
                                    {{ $order->payment_status }}
                                </span>
                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.38rem 1rem;font-size:.8rem;gap:.35rem;">
                                    Order Details →
                                </a>
                            </div>
                        </div>

                        {{-- Product items breakdown --}}
                        <div style="padding:1.25rem 1.5rem;">
                            <div style="display:flex;flex-direction:column;gap:1rem;">
                                @foreach($order->lines as $line)
                                @php
                                    $prod = $line->product;
                                    $existingReview = $prod ? \App\Models\ProductReview::where('product_id', $prod->id)
                                        ->where(function($q){ $q->where('user_id', auth()->id())->orWhere('reviewer_email', auth()->user()->email); })
                                        ->where('order_reference', $order->reference)
                                        ->first() : null;
                                @endphp
                                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border);{{ $loop->last ? 'border-bottom:none;padding-bottom:0;' : '' }}">
                                    <div style="display:flex;align-items:center;gap:1rem;flex:1;min-width:240px;">
                                        <div style="width:56px;height:56px;border-radius:10px;background:var(--bg-card2);border:1px solid var(--border);overflow:hidden;flex-shrink:0;">
                                            @if($prod && $prod->image)
                                                <img src="{{ asset('storage/' . $prod->image) }}" alt="{{ $prod->name }}" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-3);">
                                                    <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-weight:700;color:var(--text-1);font-size:.92rem;">
                                                @if($prod)
                                                    <a href="{{ route('public.show', $prod) }}" style="color:inherit;text-decoration:none;">{{ $prod->name }}</a>
                                                @else
                                                    {{ $line->product_name ?? 'Product' }}
                                                @endif
                                            </div>
                                            <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;font-size:.78rem;color:var(--text-3);margin-top:.2rem;">
                                                @if($prod && $prod->sku)
                                                    <span style="font-family:monospace;background:rgba(255,255,255,.05);padding:.1rem .35rem;border-radius:4px;">SKU: {{ $prod->sku }}</span>
                                                @endif
                                                <span>Qty: <strong style="color:var(--text-1);">{{ $line->quantity }}</strong></span>
                                                <span>Unit Price: <strong>Br{{ number_format($line->unit_price, 2) }}</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:1.5rem;">
                                        <div style="text-align:right;">
                                            <div style="font-size:.7rem;color:var(--text-3);text-transform:uppercase;">Line Total</div>
                                            <div style="font-weight:800;color:var(--text-1);font-size:.95rem;">Br{{ number_format($line->total, 2) }}</div>
                                        </div>

                                        <div>
                                            @if($existingReview)
                                                <a href="{{ route('client.orders.show', $order) }}" style="display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .75rem;border-radius:6px;background:rgba(16,185,129,.1);color:#10b981;font-size:.75rem;font-weight:600;text-decoration:none;border:1px solid rgba(16,185,129,.25);">
                                                    ★ {{ $existingReview->rating }}/5 Rated
                                                </a>
                                            @elseif($prod)
                                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.35rem .8rem;font-size:.75rem;">
                                                    Rate Item
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
