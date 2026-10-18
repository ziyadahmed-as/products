@extends('layouts.public')
@section('title', 'Customer Portal & Dashboard')

@section('content')
<div class="pub-container" style="padding-top:2.5rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        {{-- ── Standardized Sidebar ── --}}
        @include('public.client._sidebar', ['active' => 'dashboard'])

        {{-- ── Main Content Area ── --}}
        <div style="flex:1;min-width:320px;">

            @if(session('success'))
            <div style="background:rgba(16,185,129,.12);color:#10b981;padding:1rem 1.25rem;border-radius:14px;margin-bottom:1.5rem;border:1px solid rgba(16,185,129,.25);display:flex;align-items:center;gap:.75rem;font-size:.9rem;font-weight:600;">
                <svg style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            {{-- Hero Greeting & Launch Bar (Standardized matching Admin) --}}
            <div style="background:linear-gradient(135deg, rgba(8,145,178,0.12), rgba(99,102,241,0.08));border:1px solid rgba(8,145,178,0.25);border-radius:20px;padding:2rem;margin-bottom:1.75rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1.5rem;box-shadow:0 10px 30px rgba(0,0,0,0.04);">
                <div>
                    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;">
                        <span style="display:inline-block;padding:.2rem .75rem;background:rgba(8,145,178,.15);border:1px solid rgba(8,145,178,.35);border-radius:999px;font-size:.72rem;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.05em;">
                            Customer Command Center
                        </span>
                    </div>
                    <h1 style="font-size:1.85rem;font-weight:900;color:var(--text-1);margin:0 0 .35rem;font-family:'Outfit',sans-serif;letter-spacing:-0.02em;">
                        Welcome back, {{ auth()->user()->name }}
                    </h1>
                    <p style="color:var(--text-2);font-size:.92rem;margin:0;max-width:560px;line-height:1.5;">
                        Track your shipments, inspect item invoices, access purchase spending reports, and review verified goods.
                    </p>
                </div>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary" style="padding:.7rem 1.4rem;font-size:.875rem;display:inline-flex;align-items:center;gap:.5rem;border-radius:12px;">
                        <svg style="width:17px;height:17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Browse Catalog</span>
                    </a>
                    <a href="{{ route('client.orders') }}" class="pub-btn pub-btn-secondary" style="padding:.7rem 1.4rem;font-size:.875rem;display:inline-flex;align-items:center;gap:.5rem;border-radius:12px;">
                        <svg style="width:17px;height:17px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>All Orders</span>
                    </a>
                </div>
            </div>

            {{-- Customer Reports & Quick Links Toolbar (Standardized) --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:1.25rem 1.5rem;margin-bottom:1.75rem;box-shadow:0 6px 20px rgba(0,0,0,0.03);">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                    <div style="display:flex;align-items:center;gap:.85rem;">
                        <div style="width:38px;height:38px;border-radius:10px;background:rgba(8,145,178,.1);color:var(--cyan);display:flex;align-items:center;justify-content:center;border:1px solid rgba(8,145,178,.2);">
                            <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:700;color:var(--text-1);font-family:'Outfit',sans-serif;text-transform:uppercase;letter-spacing:0.04em;">Customer Financial Reports</div>
                            <div style="font-size:.78rem;color:var(--text-3);">Analyze monthly purchases, payments, and category-wise spending</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:.65rem;flex-wrap:wrap;">
                        <a href="{{ route('client.reports') }}" class="pub-btn pub-btn-secondary" style="padding:.45rem 1rem;font-size:.8rem;gap:.4rem;">
                            <span>Spending Analytics Suite</span>
                        </a>
                        <a href="{{ route('client.reviews') }}" class="pub-btn pub-btn-secondary" style="padding:.45rem 1rem;font-size:.8rem;gap:.4rem;">
                            <span>My Product Ratings</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 4 Standardized Metric Cards Row (Matches Admin Dashboard Accent Style) --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1.25rem;margin-bottom:2rem;">
                <!-- Total Orders (Cyan Accent) -->
                <div style="background:var(--bg-card);padding:1.4rem;border-radius:18px;border:1px solid var(--border);border-bottom:3px solid var(--cyan);position:relative;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Total Orders</div>
                            <div style="font-size:2rem;font-weight:900;color:var(--text-1);line-height:1;font-family:'Outfit',sans-serif;">{{ $totalOrders }}</div>
                            <div style="margin-top:.6rem;">
                                <span style="display:inline-flex;align-items:center;padding:.18rem .6rem;background:rgba(8,145,178,.1);border-radius:999px;font-size:.72rem;font-weight:600;color:var(--cyan);">
                                    Lifetime purchases
                                </span>
                            </div>
                        </div>
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(8,145,178,.1);border:1px solid rgba(8,145,178,.25);display:flex;align-items:center;justify-content:center;color:var(--cyan);flex-shrink:0;">
                            <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Purchases (Emerald Accent) -->
                <div style="background:var(--bg-card);padding:1.4rem;border-radius:18px;border:1px solid var(--border);border-bottom:3px solid #10b981;position:relative;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Total Purchases</div>
                            <div style="font-size:1.85rem;font-weight:900;color:var(--text-1);line-height:1;font-family:'Outfit',sans-serif;">Br{{ number_format($totalSpent, 0) }}</div>
                            <div style="margin-top:.6rem;">
                                <span style="display:inline-flex;align-items:center;padding:.18rem .6rem;background:rgba(16,185,129,.1);border-radius:999px;font-size:.72rem;font-weight:600;color:#10b981;">
                                    Gross payment total
                                </span>
                            </div>
                        </div>
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);display:flex;align-items:center;justify-content:center;color:#10b981;flex-shrink:0;">
                            <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- In Progress / Pending (Amber Accent) -->
                <div style="background:var(--bg-card);padding:1.4rem;border-radius:18px;border:1px solid var(--border);border-bottom:3px solid #f59e0b;position:relative;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">In Progress / Pending</div>
                            <div style="font-size:2rem;font-weight:900;color:{{ $pendingOrders > 0 ? '#f59e0b' : 'var(--text-1)' }};line-height:1;font-family:'Outfit',sans-serif;">{{ $pendingOrders }}</div>
                            <div style="margin-top:.6rem;">
                                <span style="display:inline-flex;align-items:center;padding:.18rem .6rem;background:rgba(245,158,11,.1);border-radius:999px;font-size:.72rem;font-weight:600;color:#f59e0b;">
                                    {{ $pendingOrders > 0 ? 'Awaiting delivery or prep' : 'All orders processed ✔' }}
                                </span>
                            </div>
                        </div>
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);display:flex;align-items:center;justify-content:center;color:#f59e0b;flex-shrink:0;">
                            <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Reviews Submitted (Purple Accent) -->
                <div style="background:var(--bg-card);padding:1.4rem;border-radius:18px;border:1px solid var(--border);border-bottom:3px solid #8b5cf6;position:relative;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.03);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Reviews & Ratings</div>
                            <div style="font-size:2rem;font-weight:900;color:var(--text-1);line-height:1;font-family:'Outfit',sans-serif;">{{ $myReviews }}</div>
                            <div style="margin-top:.6rem;">
                                <span style="display:inline-flex;align-items:center;padding:.18rem .6rem;background:rgba(139,92,246,.1);border-radius:999px;font-size:.72rem;font-weight:600;color:#8b5cf6;">
                                    Verified customer reviews
                                </span>
                            </div>
                        </div>
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.25);display:flex;align-items:center;justify-content:center;color:#8b5cf6;flex-shrink:0;">
                            <svg style="width:22px;height:22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Orders & Purchases List --}}
            <div style="margin-bottom:2.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
                    <div>
                        <h2 style="font-size:1.35rem;font-weight:800;color:var(--text-1);margin:0 0 .25rem;font-family:'Outfit',sans-serif;">Recent Orders & Purchased Products</h2>
                        <p style="color:var(--text-3);font-size:.85rem;margin:0;">Inspect item specifications, quantities, prices, delivery destination, and rate items.</p>
                    </div>
                    <a href="{{ route('client.orders') }}" style="font-size:.85rem;color:var(--cyan);font-weight:700;display:inline-flex;align-items:center;gap:.3rem;text-decoration:none;">
                        View All ({{ $totalOrders }}) →
                    </a>
                </div>

                @if($recentOrders->isEmpty())
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:3.5rem 2rem;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                    <div style="width:58px;height:58px;border-radius:50%;background:rgba(8,145,178,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;color:var(--cyan);">
                        <svg style="width:30px;height:30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div style="font-weight:800;font-size:1.15rem;color:var(--text-1);margin-bottom:.5rem;font-family:'Outfit',sans-serif;">No orders found yet</div>
                    <p style="color:var(--text-2);font-size:.9rem;max-width:420px;margin:0 auto 1.5rem;line-height:1.5;">Browse our catalog of manufactured products and initiate your first direct order.</p>
                    <a href="{{ route('public.shop') }}" class="pub-btn pub-btn-primary" style="padding:.75rem 1.8rem;border-radius:12px;">Explore Shop Catalog</a>
                </div>
                @else
                <div style="display:flex;flex-direction:column;gap:1.5rem;">
                    @foreach($recentOrders as $order)
                    @php
                        $statusColors = [
                            'Pending'   => ['bg' => 'rgba(245,158,11,0.12)', 'text' => '#f59e0b', 'border' => 'rgba(245,158,11,0.3)'],
                            'Fulfilled' => ['bg' => 'rgba(14,165,233,0.12)', 'text' => '#0ea5e9', 'border' => 'rgba(14,165,233,0.3)'],
                            'Completed' => ['bg' => 'rgba(16,185,129,0.12)', 'text' => '#10b981', 'border' => 'rgba(16,185,129,0.3)'],
                            'Cancelled' => ['bg' => 'rgba(239,68,68,0.12)',  'text' => '#ef4444', 'border' => 'rgba(239,68,68,0.3)'],
                        ];
                        $stStyle = $statusColors[$order->status] ?? ['bg' => 'rgba(148,163,184,0.12)', 'text' => '#94a3b8', 'border' => 'rgba(148,163,184,0.3)'];
                    @endphp
                    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:18px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.03);">

                        {{-- Order Header --}}
                        <div style="background:var(--bg-card2);padding:1.15rem 1.6rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                            <div style="display:flex;align-items:center;gap:1.4rem;flex-wrap:wrap;">
                                <div>
                                    <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Reference</div>
                                    <div style="font-family:monospace;font-weight:800;color:var(--cyan);font-size:1.05rem;">{{ $order->reference }}</div>
                                </div>
                                <div style="width:1px;height:24px;background:var(--border);"></div>
                                <div>
                                    <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Placed Date</div>
                                    <div style="font-size:.875rem;color:var(--text-2);font-weight:600;">{{ $order->created_at->format('M d, Y · h:i A') }}</div>
                                </div>
                                <div style="width:1px;height:24px;background:var(--border);"></div>
                                <div>
                                    <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Order Total</div>
                                    <div style="font-size:1rem;font-weight:900;color:var(--text-1);font-family:'Outfit',sans-serif;">Br{{ number_format($order->total, 2) }}</div>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                                <span style="padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:700;background:{{ $stStyle['bg'] }};color:{{ $stStyle['text'] }};border:1px solid {{ $stStyle['border'] }};">
                                    ● {{ $order->status }}
                                </span>
                                <span style="padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:700;background:{{ $order->payment_status === 'Paid' ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $order->payment_status === 'Paid' ? '#10b981' : '#f59e0b' }};border:1px solid {{ $order->payment_status === 'Paid' ? 'rgba(16,185,129,.3)' : 'rgba(245,158,11,.3)' }};">
                                    {{ $order->payment_status }}
                                </span>
                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.4rem 1.1rem;font-size:.8rem;gap:.4rem;border-radius:10px;">
                                    View Full Details →
                                </a>
                            </div>
                        </div>

                        {{-- Purchased Products List --}}
                        <div style="padding:1.4rem 1.6rem;">
                            <div style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem;">
                                Order Items ({{ $order->lines->count() }})
                            </div>

                            <div style="display:flex;flex-direction:column;gap:1.1rem;">
                                @foreach($order->lines as $line)
                                @php
                                    $prod = $line->product;
                                    $existingReview = $prod ? \App\Models\ProductReview::where('product_id', $prod->id)
                                        ->where(function($q){ $q->where('user_id', auth()->id())->orWhere('reviewer_email', auth()->user()->email); })
                                        ->where('order_reference', $order->reference)
                                        ->first() : null;
                                @endphp
                                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--border);{{ $loop->last ? 'border-bottom:none;padding-bottom:0;' : '' }}">
                                    <div style="display:flex;align-items:center;gap:1.1rem;min-width:240px;flex:1;">
                                        <div style="width:62px;height:62px;border-radius:12px;background:var(--bg-card2);border:1px solid var(--border);overflow:hidden;flex-shrink:0;">
                                            @if($prod && $prod->image)
                                                <img src="{{ asset('storage/' . $prod->image) }}" alt="{{ $prod->name }}" style="width:100%;height:100%;object-fit:cover;">
                                            @else
                                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text-3);">
                                                    <svg style="width:26px;height:26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-weight:700;color:var(--text-1);font-size:.95rem;font-family:'Outfit',sans-serif;">
                                                @if($prod)
                                                    <a href="{{ route('public.show', $prod) }}" style="color:inherit;text-decoration:none;">{{ $prod->name }}</a>
                                                @else
                                                    {{ $line->product_name ?? 'Product Unavailable' }}
                                                @endif
                                            </div>
                                            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;font-size:.8rem;color:var(--text-3);margin-top:.25rem;">
                                                @if($prod && $prod->sku)
                                                    <span style="font-family:monospace;background:rgba(255,255,255,.05);padding:.15rem .45rem;border-radius:4px;border:1px solid var(--border);">SKU: {{ $prod->sku }}</span>
                                                @endif
                                                @if($prod && $prod->category)
                                                    <span>Category: <strong style="color:var(--text-2);">{{ $prod->category->name }}</strong></span>
                                                @endif
                                                <span>Qty: <strong style="color:var(--text-1);">{{ $line->quantity }}</strong></span>
                                                <span>Price: <strong style="color:var(--text-1);">Br{{ number_format($line->unit_price, 2) }}</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:1.5rem;">
                                        <div style="text-align:right;">
                                            <div style="font-size:.72rem;color:var(--text-3);text-transform:uppercase;font-weight:700;">Subtotal</div>
                                            <div style="font-weight:900;color:var(--text-1);font-size:1.05rem;font-family:'Outfit',sans-serif;">Br{{ number_format($line->total, 2) }}</div>
                                        </div>

                                        <div>
                                            @if($existingReview)
                                                <a href="{{ route('client.orders.show', $order) }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .85rem;border-radius:8px;background:rgba(16,185,129,.12);color:#10b981;font-size:.78rem;font-weight:700;text-decoration:none;border:1px solid rgba(16,185,129,.3);">
                                                    ★ {{ $existingReview->rating }}/5 Rated
                                                </a>
                                            @elseif($prod)
                                                <a href="{{ route('client.orders.show', $order) }}" class="pub-btn pub-btn-secondary" style="padding:.4rem .9rem;font-size:.78rem;gap:.35rem;border-radius:8px;">
                                                    ☆ Rate Item
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Order Footer (Delivery & Address information) --}}
                        <div style="background:var(--bg-card2);padding:.95rem 1.6rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.85rem;font-size:.82rem;color:var(--text-2);">
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                <svg style="width:16px;height:16px;color:var(--cyan);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Delivery destination: <strong style="color:var(--text-1);">{{ $order->customer_address ?? 'Standard Delivery' }}</strong></span>
                            </div>
                            @if($order->customer_phone)
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <svg style="width:15px;height:15px;color:var(--text-3);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
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
