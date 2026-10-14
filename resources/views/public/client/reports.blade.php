@extends('layouts.public')
@section('title', 'My Spending & Order Reports')

@section('content')
<div class="pub-container" style="padding-top:3rem;padding-bottom:5rem;">
    <div style="display:flex;gap:2rem;flex-wrap:wrap;align-items:flex-start;">

        @include('public.client._sidebar', ['active' => 'reports'])

        <div style="flex:1;min-width:300px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
                <div>
                    <h1 style="font-size:2rem;font-weight:800;color:var(--text-1);margin:0;">My Spending & Order Reports</h1>
                    <p style="color:var(--text-3);font-size:.875rem;margin-top:.25rem;">
                        View your full purchase history, payment status, and export statement records.
                    </p>
                </div>
                <div style="display:flex;gap:.75rem;align-items:center;">
                    <button onclick="window.print()" class="pub-btn pub-btn-secondary" style="padding:.5rem 1rem;font-size:.85rem;cursor:pointer;">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print
                    </button>
                    <a href="{{ route('client.reports.export', request()->query()) }}" class="pub-btn pub-btn-primary" style="padding:.5rem 1rem;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:.5rem;">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- Spending KPI Summary Cards -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:1rem;margin-bottom:1.75rem;">
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;">
                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;font-weight:700;">Total Orders</div>
                    <div style="font-size:1.75rem;font-weight:800;color:var(--text-1);margin-top:.4rem;">{{ $totalOrdersCount }}</div>
                </div>

                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;">
                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;font-weight:700;">Total Spent</div>
                    <div style="font-size:1.75rem;font-weight:800;color:var(--text-1);margin-top:.4rem;">Br{{ number_format($totalSpent, 2) }}</div>
                </div>

                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;">
                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;font-weight:700;">Paid Amount</div>
                    <div style="font-size:1.75rem;font-weight:800;color:#10b981;margin-top:.4rem;">Br{{ number_format($totalPaid, 2) }}</div>
                </div>

                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;">
                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;font-weight:700;">Outstanding Balance</div>
                    <div style="font-size:1.75rem;font-weight:800;color:{{ $totalOutstanding > 0 ? '#ef4444' : '#10b981' }};margin-top:.4rem;">Br{{ number_format($totalOutstanding, 2) }}</div>
                </div>

                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;">
                    <div style="font-size:.75rem;color:var(--text-3);text-transform:uppercase;font-weight:700;">Completed Orders</div>
                    <div style="font-size:1.75rem;font-weight:800;color:#0ea5e9;margin-top:.4rem;">{{ $completedCount }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;margin-bottom:1.75rem;">
                <form method="GET" action="{{ route('client.reports') }}">
                    {{-- Presets --}}
                    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;padding-bottom:.85rem;border-bottom:1px solid var(--border);margin-bottom:1rem;">
                        <span style="font-size:.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;">Period:</span>
                        @php $curP = request('period'); @endphp
                        <a href="{{ route('client.reports', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'today'])) }}"
                           style="padding:.25rem .75rem;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $curP === 'today' ? 'background:var(--cyan);color:#fff;' : 'background:var(--bg-card2);color:var(--text-2);' }}">
                           Today
                        </a>
                        <a href="{{ route('client.reports', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_week'])) }}"
                           style="padding:.25rem .75rem;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $curP === 'this_week' ? 'background:var(--cyan);color:#fff;' : 'background:var(--bg-card2);color:var(--text-2);' }}">
                           This Week
                        </a>
                        <a href="{{ route('client.reports', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_month'])) }}"
                           style="padding:.25rem .75rem;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $curP === 'this_month' ? 'background:var(--cyan);color:#fff;' : 'background:var(--bg-card2);color:var(--text-2);' }}">
                           This Month
                        </a>
                        <a href="{{ route('client.reports', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_year'])) }}"
                           style="padding:.25rem .75rem;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;{{ $curP === 'this_year' ? 'background:var(--cyan);color:#fff;' : 'background:var(--bg-card2);color:var(--text-2);' }}">
                           This Year
                        </a>
                        @if($curP || request('from') || request('to'))
                        <a href="{{ route('client.reports', request()->except(['period', 'from', 'to', 'page'])) }}" style="font-size:.78rem;color:#ef4444;text-decoration:underline;margin-left:.5rem;">
                            Clear Date
                        </a>
                        @endif
                    </div>

                    {{-- Form Fields --}}
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:1rem;">
                        <div>
                            <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-3);margin-bottom:.35rem;">From Date</label>
                            <input type="date" name="from" value="{{ request('from') }}" style="width:100%;padding:.45rem .65rem;border:1px solid var(--border);border-radius:8px;background:var(--bg-card2);color:var(--text-1);font-size:.85rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-3);margin-bottom:.35rem;">To Date</label>
                            <input type="date" name="to" value="{{ request('to') }}" style="width:100%;padding:.45rem .65rem;border:1px solid var(--border);border-radius:8px;background:var(--bg-card2);color:var(--text-1);font-size:.85rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-3);margin-bottom:.35rem;">Payment Status</label>
                            <select name="payment_status" style="width:100%;padding:.45rem .65rem;border:1px solid var(--border);border-radius:8px;background:var(--bg-card2);color:var(--text-1);font-size:.85rem;">
                                <option value="">All</option>
                                <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Partial" {{ request('payment_status') === 'Partial' ? 'selected' : '' }}>Partial</option>
                                <option value="Unpaid" {{ request('payment_status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:.75rem;font-weight:600;color:var(--text-3);margin-bottom:.35rem;">Order Status</label>
                            <select name="status" style="width:100%;padding:.45rem .65rem;border:1px solid var(--border);border-radius:8px;background:var(--bg-card2);color:var(--text-1);font-size:.85rem;">
                                <option value="">All</option>
                                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Fulfilled" {{ request('status') === 'Fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                                <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
                        <button type="submit" class="pub-btn pub-btn-primary" style="padding:.45rem 1.25rem;font-size:.85rem;cursor:pointer;">Filter</button>
                        <a href="{{ route('client.reports') }}" class="pub-btn pub-btn-secondary" style="padding:.45rem 1rem;font-size:.85rem;text-decoration:none;">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                    <thead>
                        <tr style="background:var(--bg-card2);border-bottom:1px solid var(--border);text-align:left;font-size:.75rem;text-transform:uppercase;color:var(--text-3);">
                            <th style="padding:.85rem 1rem;">Reference</th>
                            <th style="padding:.85rem 1rem;">Date</th>
                            <th style="padding:.85rem 1rem;">Items</th>
                            <th style="padding:.85rem 1rem;">Total</th>
                            <th style="padding:.85rem 1rem;">Paid</th>
                            <th style="padding:.85rem 1rem;">Balance</th>
                            <th style="padding:.85rem 1rem;">Payment</th>
                            <th style="padding:.85rem 1rem;">Status</th>
                            <th style="padding:.85rem 1rem;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $ord)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:.85rem 1rem;font-family:monospace;font-weight:700;color:var(--text-1);">{{ $ord->reference }}</td>
                            <td style="padding:.85rem 1rem;color:var(--text-3);font-size:.8rem;">{{ $ord->created_at->format('d M Y') }}</td>
                            <td style="padding:.85rem 1rem;color:var(--text-2);max-width:220px;font-size:.8rem;">
                                {{ $ord->lines->map(fn ($l) => ($l->product?->name ?? 'Item') . ' (' . number_format($l->quantity, 1) . ')')->implode(', ') }}
                            </td>
                            <td style="padding:.85rem 1rem;font-weight:700;color:var(--text-1);">Br{{ number_format($ord->total, 2) }}</td>
                            <td style="padding:.85rem 1rem;color:#10b981;font-weight:600;">Br{{ number_format($ord->paid_amount, 2) }}</td>
                            <td style="padding:.85rem 1rem;color:{{ ($ord->total - $ord->paid_amount) > 0 ? '#ef4444' : '#10b981' }};font-weight:600;">
                                Br{{ number_format($ord->total - $ord->paid_amount, 2) }}
                            </td>
                            <td style="padding:.85rem 1rem;">
                                <span style="display:inline-block;padding:.2rem .6rem;border-radius:50px;font-size:.72rem;font-weight:700;background:{{ $ord->payment_status === 'Paid' ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $ord->payment_status === 'Paid' ? '#10b981' : '#f59e0b' }};">
                                    {{ $ord->payment_status }}
                                </span>
                            </td>
                            <td style="padding:.85rem 1rem;">
                                <span style="display:inline-block;padding:.2rem .6rem;border-radius:50px;font-size:.72rem;font-weight:700;background:rgba(14,165,233,.12);color:#0ea5e9;">
                                    {{ $ord->status }}
                                </span>
                            </td>
                            <td style="padding:.85rem 1rem;text-align:right;white-space:nowrap;">
                                <a href="{{ route('client.orders.show', $ord) }}" style="color:var(--cyan);text-decoration:none;font-weight:600;font-size:.8rem;margin-right:.5rem;">
                                    View
                                </a>
                                <a href="{{ route('invoice.show', $ord) }}" target="_blank" style="color:var(--text-2);text-decoration:none;font-size:.8rem;">
                                    Invoice
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:3rem;color:var(--text-3);">
                                No orders found matching the selected period and criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($orders->hasPages())
                <div style="padding:1rem;border-top:1px solid var(--border);">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
