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

            {{-- Welcome Banner --}}
            <div style="background:linear-gradient(135deg,rgba(8,145,178,.15),rgba(99,102,241,.1));border:1px solid rgba(8,145,178,.3);border-radius:16px;padding:1.75rem 2rem;margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1.25rem;">
                <div>
                    <span style="display:inline-block;padding:.25rem .75rem;background:rgba(8,145,178,.2);border:1px solid rgba(8,145,178,.4);border-radius:50px;font-size:.72rem;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;">Buyer Portal</span>
                    <h1 style="font-size:1.75rem;font-weight:900;color:var(--text-1);margin:0 0 .35rem;">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p style="color:var(--text-2);font-size:.9rem;margin:0;">Track your orders, view purchased items, and submit verified product reviews.</p>
                </div>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary" style="padding:.65rem 1.4rem;font-size:.85rem;">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Browse Catalog
                    </a>
                    <a href="{{ route('client.orders') }}" class="pub-btn pub-btn-secondary" style="padding:.65rem 1.4rem;font-size:.85rem;">
                        View All Orders
                    </a>
                </div>
            </div>

            {{-- Key Metrics --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:2.25rem;">
                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);position:relative;overflow:hidden;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.78rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Total Orders</div>
                            <div style="font-size:2rem;font-weight:900;color:var(--cyan);line-height:1;">{{ $totalOrders }}</div>
                        </div>
                        <div style="width:42px;height:42px;border-radius:10px;background:rgba(8,145,178,.1);border:1px solid rgba(8,145,178,.25);display:flex;align-items:center;justify-content:center;color:var(--cyan);">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                </div>

                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.78rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Total Purchases</div>
                            <div style="font-size:2rem;font-weight:900;color:#a78bfa;line-height:1;">Br{{ number_format($totalSpent, 0) }}</div>
                        </div>
                        <div style="width:42px;height:42px;border-radius:10px;background:rgba(167,139,250,.1);border:1px solid rgba(167,139,250,.25);display:flex;align-items:center;justify-content:center;color:#a78bfa;">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.78rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">In Progress / Pending</div>
                            <div style="font-size:2rem;font-weight:900;color:#f59e0b;line-height:1;">{{ $pendingOrders }}</div>
                        </div>
                        <div style="width:42px;height:42px;border-radius:10px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);display:flex;align-items:center;justify-content:center;color:#f59e0b;">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <div style="background:var(--bg-card);padding:1.5rem;border-radius:14px;border:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.78rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Reviews Submitted</div>
                            <div style="font-size:2rem;font-weight:900;color:#34d399;line-height:1;">{{ $myReviews }}</div>
                        </div>
                        <div style="width:42px;height:42px;border-radius:10px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);display:flex;align-items:center;justify-content:center;color:#34d399;">
                            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Orders with Full Product Details --}}
            <div style="margin-bottom:2.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
                    <div>
                        <h2 style="font-size:1.35rem;font-weight:800;color:var(--text-1);margin:0 0 .25rem;">Recent Orders & Purchased Products</h2>
                        <p style="color:var(--text-3);font-size:.85rem;margin:0;">Inspect purchased products, quantities, prices, delivery details, and rate items.</p>
                    </div>
                    <a href="{{ route('client.orders') }}" style="font-size:.85rem;color:var(--cyan);font-weight:700;display:inline-flex;align-items:center;gap:.3rem;text-decoration:none;">
                        View All ({{ $totalOrders }}) →
                    </a>
                </div>

                @if($recentOrders->isEmpty())
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:3.5rem 2rem;text-align:center;">
                    <div style="width:54px;height:54px;border-radius:50%;background:rgba(8,145,178,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;color:var(--cyan);">
                        <svg style="width:28px;height:28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div style="font-weight:800;font-size:1.1rem;color:var(--text-1);margin-bottom:.5rem;">No orders found yet</div>
                    <p style="color:var(--text-2);font-size:.9rem;max-width:400px;margin:0 auto 1.5rem;">Browse our catalog of products and start your shopping journey.</p>
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary" style="padding:.75rem 1.75rem;">Explore Products</a>
                </div>
                @else
                <div style="display:flex;flex-direction:column;gap:1.5rem;">
                    @foreach($recentOrders as $order)
                    @php
                        $statusColors = ['Pending'=>'#f59e0b','Fulfilled'=>'#0ea5e9','Completed'=>'#10b981','Cancelled'=>'#ef4444'];
                        $sc = $statusColors[$order->status] ?? '#94a3b8';
                    @endphp
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.03);">

                        {{-- Order Header --}}
                        <div style="background:var(--bg-card2);padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                            <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Order Reference</div>
                                    <div style="font-family:monospace;font-weight:800;color:var(--text-1);font-size:1rem;">{{ $order->reference }}</div>
                                </div>
                                <div style="width:1px;height:24px;background:var(--border);"></div>
                                <div>
                                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Placed On</div>
                                    <div style="font-size:.875rem;color:var(--text-2);font-weight:600;">{{ $order->created_at->format('M d, Y · h:i A') }}</div>
                                </div>
                                <div style="width:1px;height:24px;background:var(--border);"></div>
                                <div>
                                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Total Amount</div>
                                    <div style="font-size:.95rem;font-weight:900;color:var(--text-1);">Br{{ number_format($order->total, 2) }}</div>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                                <span style="padding:.3rem .85rem;border-radius:50px;font-size:.75rem;font-weight:700;background:{{ $sc }}22;color:{{ $sc }};border:1px solid {{ $sc }}44;">
                                    ● {{ $order->status }}
                                </span>
                                <span style="padding:.3rem .85rem;border-radius:50px;font-size:.75rem;font-weight:700;background:{{ $order->payment_status === 'Paid' ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $order->payment_status === 'Paid' ? '#10b981' : '#f59e0b' }};border:1px solid {{ $order->payment_status === 'Paid' ? 'rgba(16,185,129,.3)' : 'rgba(245,158,11,.3)' }};">
                                    {{ $order->payment_status }}
                                </span>
                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.4rem 1rem;font-size:.8rem;gap:.4rem;">
                                    View Full Details →
                                </a>
                            </div>
                        </div>

                        {{-- Purchased Products List --}}
                        <div style="padding:1.25rem 1.5rem;">
                            <div style="font-size:.78rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.85rem;">
                                Products in this Order ({{ $order->lines->count() }})
                            </div>

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
                                    <div style="display:flex;align-items:center;gap:1rem;min-width:240px;flex:1;">
                                        <div style="width:60px;height:60px;border-radius:10px;background:var(--bg-card2);border:1px solid var(--border);overflow:hidden;flex-shrink:0;">
                                            @if($prod && $prod->image)
                                                <img src="{{ asset('storage/' . $prod->image) }}" alt="{{ $prod->name }}" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-3);">
                                                    <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-weight:700;color:var(--text-1);font-size:.95rem;">
                                                @if($prod)
                                                    <a href="{{ route('public.show', $prod) }}" style="color:inherit;text-decoration:none;">{{ $prod->name }}</a>
                                                @else
                                                    {{ $line->product_name ?? 'Product Unavailable' }}
                                                @endif
                                            </div>
                                            <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;font-size:.8rem;color:var(--text-3);margin-top:.2rem;">
                                                @if($prod && $prod->sku)
                                                    <span style="font-family:monospace;background:rgba(255,255,255,.05);padding:.1rem .4rem;border-radius:4px;">SKU: {{ $prod->sku }}</span>
                                                @endif
                                                @if($prod && $prod->category)
                                                    <span>Category: <strong style="color:var(--text-2);">{{ $prod->category->name }}</strong></span>
                                                @endif
                                                <span>Qty: <strong style="color:var(--text-1);">{{ $line->quantity }}</strong></span>
                                                <span>Unit: <strong>Br{{ number_format($line->unit_price, 2) }}</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:1.5rem;">
                                        <div style="text-align:right;">
                                            <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;">Subtotal</div>
                                            <div style="font-weight:800;color:var(--text-1);font-size:1rem;">Br{{ number_format($line->total, 2) }}</div>
                                        </div>

                                        <div>
                                            @if($existingReview)
                                                <a href="{{ route('client.orders.show', $order) }}" style="display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .75rem;border-radius:6px;background:rgba(16,185,129,.1);color:#10b981;font-size:.78rem;font-weight:600;text-decoration:none;border:1px solid rgba(16,185,129,.25);">
                                                    ★ {{ $existingReview->rating }}/5 Rated
                                                </a>
                                            @elseif($prod)
                                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.35rem .85rem;font-size:.78rem;gap:.3rem;">
                                                    ☆ Rate Product
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Order Footer (Delivery & Address information) --}}
                        <div style="background:var(--bg-card2);padding:.85rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;font-size:.8rem;color:var(--text-2);">
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <svg style="width:15px;height:15px;color:var(--cyan);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Delivery to: <strong style="color:var(--text-1);">{{ $order->customer_address ?? 'Not specified' }}</strong></span>
                            </div>
                            @if($order->customer_phone)
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <svg style="width:15px;height:15px;color:var(--text-3);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $order->customer_phone }}</span>
                            </div>
                            @endif
                        </div>

                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
