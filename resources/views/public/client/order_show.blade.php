@extends('layouts.public')
@section('title', 'Order ' . $order->reference)

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        @include('public.client._sidebar', ['active' => 'orders'])

        <div style="flex:1;min-width:300px;">

            <div style="margin-bottom:1.5rem;">
                <a href="{{ route('client.orders') }}" style="font-size:.85rem;color:var(--text-2);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;">
                    ← Back to My Orders
                </a>
            </div>

            <div style="display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;gap:1rem;margin-bottom:1.75rem;">
                <div>
                    <h1 style="font-size:1.75rem;font-weight:800;color:var(--text-1);margin:0 0 .25rem;font-family:monospace;">{{ $order->reference }}</h1>
                    <div style="color:var(--text-3);font-size:.82rem;">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</div>
                </div>
                @php
                    $statusColors = ['Pending'=>'#f59e0b','Fulfilled'=>'#0ea5e9','Completed'=>'#10b981','Cancelled'=>'#ef4444'];
                    $sc = $statusColors[$order->status] ?? '#94a3b8';
                @endphp
                <span style="padding:.4rem 1.2rem;border-radius:50px;font-size:.85rem;font-weight:700;background:{{ $sc }}22;color:{{ $sc }};border:1px solid {{ $sc }}44;">{{ $order->status }}</span>
            </div>

            @if(session('success'))
            <div style="background:rgba(16,185,129,.1);color:#10b981;padding:1rem;border-radius:10px;margin-bottom:1.5rem;border:1px solid rgba(16,185,129,.2);display:flex;align-items:center;gap:.5rem;">
                <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div style="background:rgba(239,68,68,.1);color:#ef4444;padding:1rem;border-radius:10px;margin-bottom:1.5rem;border:1px solid rgba(239,68,68,.2);">
                {{ session('error') }}
            </div>
            @endif

            <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">

                {{-- Products & Rating --}}
                <div style="flex:2;min-width:300px;">
                    <div style="background:var(--bg-card);border-radius:14px;border:1px solid var(--border);overflow:hidden;">
                        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);font-weight:700;color:var(--text-1);">
                            Purchased Products
                        </div>
                        <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1.5rem;">
                            @foreach($order->lines as $line)
                            @php
                                $isRated = \App\Models\ProductReview::where('product_id', $line->product_id)
                                    ->where(function($q){ $q->where('user_id', auth()->id())->orWhere('order_reference', request()->route('order')->reference ?? ''); })
                                    ->where('order_reference', $order->reference)
                                    ->exists();
                            @endphp
                            <div style="display:flex;gap:1.25rem;align-items:flex-start;padding-bottom:1.5rem;border-bottom:1px solid var(--border);{{ $loop->last ? 'border:none;padding-bottom:0;' : '' }}">
                                <div style="width:72px;height:72px;background:var(--bg-card2);border-radius:10px;overflow:hidden;flex-shrink:0;border:1px solid var(--border);">
                                    @if($line->product && $line->product->image)
                                        <img src="{{ asset('storage/' . $line->product->image) }}" style="width:100%;height:100%;object-fit:cover;">
                                    @endif
                                </div>
                                <div style="flex:1;">
                                    <div style="font-weight:700;color:var(--text-1);font-size:1rem;margin-bottom:.2rem;">{{ $line->product->name ?? 'Unknown Product' }}</div>
                                    <div style="color:var(--text-3);font-size:.82rem;margin-bottom:.875rem;">
                                        Qty: {{ $line->quantity }} &bull; Br{{ number_format($line->unit_price, 2) }} each &bull; <strong style="color:var(--text-2);">Br{{ number_format($line->total, 2) }}</strong>
                                    </div>

                                    @if($isRated)
                                    <div style="color:#10b981;font-size:.82rem;font-weight:600;display:flex;align-items:center;gap:.35rem;">
                                        <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        You've reviewed this product
                                    </div>
                                    @elseif($line->product)
                                    <div style="background:rgba(8,145,178,.05);border:1px solid rgba(8,145,178,.2);padding:1rem;border-radius:10px;">
                                        <div style="font-weight:700;font-size:.85rem;color:var(--text-1);margin-bottom:.6rem;">Rate this product:</div>
                                        <form action="{{ route('client.orders.rate', [$order, $line->product]) }}" method="POST">
                                            @csrf
                                            <div class="star-rating" style="display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:.3rem;margin-bottom:.75rem;">
                                                @for($s=5;$s>=1;$s--)
                                                <input type="radio" id="star{{ $s }}-{{ $line->id }}" name="rating" value="{{ $s }}" required>
                                                <label for="star{{ $s }}-{{ $line->id }}" title="{{ $s }} stars">★</label>
                                                @endfor
                                            </div>
                                            <textarea name="comment" rows="2" placeholder="Write a comment… (optional)" class="pub-input" style="width:100%;font-size:.82rem;padding:.5rem .75rem;margin-bottom:.625rem;"></textarea>
                                            <button type="submit" class="pub-btn pub-btn-primary" style="padding:.4rem 1.1rem;font-size:.82rem;">
                                                Submit Review
                                            </button>
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
                <div style="flex:1;min-width:240px;max-width:320px;">
                    <div style="background:var(--bg-card);border-radius:14px;border:1px solid var(--border);overflow:hidden;position:sticky;top:100px;">
                        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);font-weight:700;color:var(--text-1);">Order Summary</div>
                        <div style="padding:1.5rem;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:.75rem;color:var(--text-2);font-size:.875rem;">
                                <span>Payment</span>
                                <span style="font-weight:600;color:{{ $order->payment_status == 'Paid' ? '#10b981' : '#f59e0b' }};">{{ $order->payment_status }}</span>
                            </div>
                            @if($order->customer_phone)
                            <div style="display:flex;justify-content:space-between;margin-bottom:.75rem;color:var(--text-2);font-size:.875rem;">
                                <span>Phone</span>
                                <span style="color:var(--text-1);">{{ $order->customer_phone }}</span>
                            </div>
                            @endif
                            @if($order->customer_email)
                            <div style="display:flex;justify-content:space-between;margin-bottom:.75rem;color:var(--text-2);font-size:.875rem;">
                                <span>Email</span>
                                <span style="color:var(--text-1);font-size:.78rem;word-break:break-all;">{{ $order->customer_email }}</span>
                            </div>
                            @endif
                            @if($order->customer_address)
                            <div style="margin-bottom:.75rem;color:var(--text-2);font-size:.875rem;">
                                <div style="margin-bottom:.25rem;">Delivery Address</div>
                                <div style="color:var(--text-1);font-size:.82rem;line-height:1.5;">{{ $order->customer_address }}</div>
                            </div>
                            @endif
                            @if($order->order_notes)
                            <div style="margin-bottom:.75rem;color:var(--text-2);font-size:.875rem;">
                                <div style="margin-bottom:.25rem;">Notes</div>
                                <div style="color:var(--text-1);font-size:.82rem;font-style:italic;">{{ $order->order_notes }}</div>
                            </div>
                            @endif
                            <div style="height:1px;background:var(--border);margin:1rem 0;"></div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;color:var(--text-2);font-size:.875rem;">
                                <span>Subtotal</span><span>Br{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-weight:900;font-size:1.25rem;color:var(--text-1);">
                                <span>Total</span><span>Br{{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.star-rating { font-size: 1.6rem; color: var(--text-3); }
.star-rating input { display: none; }
.star-rating label { cursor: pointer; transition: color .1s; }
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label { color: #fbbf24; }
</style>
@endsection
