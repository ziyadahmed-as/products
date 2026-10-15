<div class="space-y-6" x-data="{ searchQuery: '', stockFilter: 'all' }">

    <!-- Top Greeting & Seller Command Center Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Sales Command Center
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                    Seller / POS Register
                </span>
                @if(auth()->user()->branches->isNotEmpty())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-dark-900 text-dark-300 border border-dark-700 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        {{ auth()->user()->branches->pluck('name')->join(', ') }}
                    </span>
                @endif
            </div>
            <p class="text-dark-400 text-sm mt-1">
                Real-time point-of-sale terminal, sellable inventory lookup, and personal sales performance.
            </p>
        </div>

        <!-- Primary Quick Actions for Seller -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('sales.create') }}" class="btn-primary flex items-center gap-2 text-sm shadow-lg shadow-emerald-600/25 !bg-gradient-to-r !from-emerald-500 !to-teal-600 hover:!from-emerald-600 hover:!to-teal-700 !border-emerald-400/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-bold">New Quick Sale (POS)</span>
                <span class="text-[10px] bg-emerald-950/60 px-1.5 py-0.5 rounded border border-emerald-400/40 font-mono text-emerald-200">F2</span>
            </a>
            <a href="{{ route('sales.index') }}" class="btn-secondary flex items-center gap-2 text-sm !bg-dark-900 border-dark-700 hover:border-cyan-500/40">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>My Sales Records</span>
            </a>
            <a href="{{ route('sale-returns.index') }}" class="btn-secondary flex items-center gap-2 text-sm !bg-dark-900 border-dark-700 hover:border-amber-500/40">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span>Returns</span>
            </a>
        </div>
    </div>

    <!-- Seller Sales Reports & Export Toolbar -->
    <div class="card bg-gradient-to-r from-dark-900 via-dark-900/95 to-dark-900 border border-dark-800 rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-dark-800/80">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide uppercase">Sales Analytics & Export Center</h3>
                    <p class="text-xs text-dark-400">Review your scoped transaction history, download receipts, and export personal sales ledger</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.sales') }}" class="btn-secondary text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Full Sales Report
                </a>
                <a href="{{ route('reports.sales.export') }}" class="btn-primary !bg-dark-900 border border-dark-700 hover:border-emerald-500/50 text-xs flex items-center gap-1.5 !text-emerald-300">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Sales CSV
                </a>
            </div>
        </div>

        <!-- Shortcuts Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-3">
            <div class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 flex items-center justify-between">
                <span class="text-xs text-dark-400">Cash Register:</span>
                <span class="text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Online & Ready
                </span>
            </div>
            <div class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 flex items-center justify-between">
                <span class="text-xs text-dark-400">Shift Started:</span>
                <span class="text-xs font-medium text-dark-200">{{ now()->format('M d, Y') }}</span>
            </div>
            <div class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 flex items-center justify-between">
                <span class="text-xs text-dark-400">Orders Today:</span>
                <span class="text-xs font-bold text-white">{{ $sellerData['today_count'] ?? 0 }} Completed</span>
            </div>
            <div class="p-2.5 rounded-xl bg-dark-950/70 border border-dark-800 flex items-center justify-between">
                <span class="text-xs text-dark-400">Total Career Sales:</span>
                <span class="text-xs font-bold text-white">{{ $sellerData['total_count'] ?? 0 }} Orders</span>
            </div>
        </div>
    </div>

    <!-- Core KPI Stat Cards Row (Standardized with Admin Dashboard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <!-- Today's Sales Revenue -->
        <div class="stat-card">
            <div class="stat-icon bg-emerald-900/40 border border-emerald-700/30">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-emerald-400">Br{{ number_format($sellerData['today_sales'] ?? 0, 2) }}</p>
                <p class="stat-label">Today's Sales</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    {{ $sellerData['today_count'] ?? 0 }} completed {{ \Illuminate\Support\Str::plural('transaction', $sellerData['today_count'] ?? 0) }}
                </p>
            </div>
        </div>

        <!-- This Month's Sales Volume -->
        <div class="stat-card">
            <div class="stat-icon bg-cyan-900/40 border border-cyan-700/30">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-cyan-400">Br{{ number_format($sellerData['month_sales'] ?? 0, 2) }}</p>
                <p class="stat-label">This Month's Volume</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    {{ $sellerData['month_count'] ?? 0 }} transactions this month
                </p>
            </div>
        </div>

        <!-- Payments Collected vs Pending -->
        <div class="stat-card">
            <div class="stat-icon bg-amber-900/40 border border-amber-700/30">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-amber-400">Br{{ number_format($sellerData['total_paid'] ?? 0, 2) }}</p>
                <p class="stat-label">Collected Payments</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    @if(($sellerData['total_unpaid'] ?? 0) > 0)
                        <span class="text-rose-400 font-semibold">Br{{ number_format($sellerData['total_unpaid'], 2) }} credit/unpaid</span>
                    @else
                        <span class="text-emerald-400 font-semibold">All accounts settled</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="stat-card">
            <div class="stat-icon bg-purple-900/40 border border-purple-700/30">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-purple-400">Br{{ number_format($sellerData['avg_order_value'] ?? 0, 2) }}</p>
                <p class="stat-label">Avg. Order Value</p>
                <p class="text-[11px] text-dark-400 mt-1">
                    Total Revenue: Br{{ number_format($sellerData['total_revenue'] ?? 0, 2) }}
                </p>
            </div>
        </div>

    </div>

    <!-- ── SELLABLE INVENTORY & RECENT ORDERS MAIN GRID ── -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Sellable Catalog Lookup & Recent Sales -->
        <div class="xl:col-span-2 space-y-6">

            <!-- Live Sellable Products Fast Lookup (Counter POS Tool) -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-dark-800 gap-3">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            Sellable Products & Live Stock Lookup
                        </h2>
                        <p class="text-xs text-dark-400 mt-0.5">Quick price check and stock availability for counter sales</p>
                    </div>
                    <!-- Live Search Input -->
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Search product or SKU..."
                                   class="bg-dark-950 border border-dark-700 text-xs text-white rounded-xl pl-8 pr-3 py-1.5 focus:border-emerald-500 focus:outline-none w-48 sm:w-60">
                            <svg class="w-3.5 h-3.5 text-dark-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <a href="{{ route('products.index') }}" class="btn-secondary !px-2.5 !py-1 text-xs">
                            All Stock
                        </a>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($sellerData['sellable_catalog'] ?? [] as $prod)
                    @php
                        $stockQty = (float) ($prod->quantity ?? 0);
                        $isLow = $prod->minimum_stock_level > 0 && $stockQty <= $prod->minimum_stock_level;
                        $isOut = $stockQty <= 0;
                    @endphp
                    <div x-show="searchQuery === '' || '{{ strtolower($prod->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($prod->sku) }}'.includes(searchQuery.toLowerCase())"
                         class="p-3.5 rounded-xl bg-dark-950/70 border border-dark-800 hover:border-emerald-500/40 transition flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-dark-400 truncate">{{ $prod->category->name ?? 'General' }}</span>
                                @if($isOut)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">Out of Stock</span>
                                @elseif($isLow)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">Low Stock</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">In Stock</span>
                                @endif
                            </div>
                            <h4 class="text-sm font-bold text-white group-hover:text-emerald-300 transition line-clamp-1">{{ $prod->name }}</h4>
                            <p class="font-mono text-[11px] text-dark-400 mt-0.5">SKU: {{ $prod->sku }}</p>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-dark-800/80 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-dark-400 block leading-none">Price:</span>
                                <span class="text-sm font-extrabold text-white">Br{{ number_format($prod->selling_price, 2) }}</span>
                                <span class="text-[10px] text-dark-400">/ {{ $prod->unit->code ?? 'unit' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] font-bold font-mono {{ $isOut ? 'text-rose-400' : ($isLow ? 'text-amber-400' : 'text-emerald-400') }}">
                                    {{ number_format($stockQty, 1) }}
                                </span>
                                <a href="{{ route('sales.create') }}" class="mt-1 block px-2 py-0.5 rounded bg-primary-600/20 text-primary-300 hover:bg-primary-600 hover:text-white text-[10px] font-semibold transition border border-primary-500/30">
                                    Sell Now →
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-8 text-center text-dark-500 text-sm">
                        No sellable merchandise available in inventory.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Seller's Recent Transactions & Receipts Table -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-dark-800">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                            My Recent Sales Transactions
                        </h2>
                        <p class="text-xs text-dark-400 mt-0.5">Live register logs, invoices, and customer receipt records</p>
                    </div>
                    <a href="{{ route('sales.index') }}" class="btn-secondary !px-2.5 !py-1 text-xs">
                        View All ({{ $sellerData['total_count'] ?? 0 }})
                    </a>
                </div>

                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date / Time</th>
                                <th>Items</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Payment Status</th>
                                <th class="text-center">Method</th>
                                <th class="text-right">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellerData['recent_sales'] ?? [] as $sale)
                            <tr>
                                <td class="font-mono text-xs font-bold text-white">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-primary-400 hover:underline">
                                        {{ $sale->reference }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-medium text-white text-xs">{{ $sale->customer_name ?: 'Walk-in Customer' }}</div>
                                    @if($sale->customer_phone)
                                        <div class="text-[10px] text-dark-400 font-mono">{{ $sale->customer_phone }}</div>
                                    @endif
                                </td>
                                <td class="text-xs text-dark-300">
                                    <div>{{ $sale->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-dark-500">{{ $sale->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="text-xs text-dark-300">
                                    {{ $sale->lines->count() }} {{ \Illuminate\Support\Str::plural('item', $sale->lines->count()) }}
                                </td>
                                <td class="text-right font-mono font-bold text-sm text-emerald-400">
                                    Br{{ number_format($sale->total, 2) }}
                                </td>
                                <td class="text-center">
                                    @if(strtolower($sale->payment_status) === 'paid')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Paid</span>
                                    @elseif(strtolower($sale->payment_status) === 'partial')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30">Partial</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-500/15 text-rose-400 border border-rose-500/30">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-center text-xs text-dark-300">
                                    <span class="px-2 py-0.5 rounded bg-dark-950 border border-dark-800 text-[10px] font-medium text-dark-300">
                                        {{ $sale->payments->first()->method ?? 'Cash' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('sales.show', $sale) }}" class="p-1.5 rounded-lg bg-dark-950 hover:bg-primary-600/20 text-dark-400 hover:text-primary-300 transition inline-flex items-center justify-center border border-dark-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-dark-500">
                                    No sales transactions recorded yet.
                                    <a href="{{ route('sales.create') }}" class="text-primary-400 text-xs hover:underline block mt-1">Make your first sale</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Payment Methods, Bestsellers & Quick Shortcuts -->
        <div class="space-y-6">

            <!-- Payment Methods Breakdown Widget -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-dark-800">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Payment Breakdown</h3>
                        <p class="text-[11px] text-dark-400 mt-0.5">Settlement channels for your sales</p>
                    </div>
                </div>

                <div class="space-y-3.5">
                    @forelse($sellerData['payments_breakdown'] ?? [] as $pay)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-semibold text-white capitalize flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ strtolower($pay->method) === 'telebirr' ? 'bg-cyan-400' : (strtolower($pay->method) === 'cash' ? 'bg-emerald-400' : 'bg-purple-400') }}"></span>
                                {{ $pay->method ?: 'Cash' }}
                            </span>
                            <span class="font-mono font-bold text-dark-200">Br{{ number_format($pay->total_amount, 2) }}</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-dark-950 overflow-hidden">
                            <div class="h-full rounded-full {{ strtolower($pay->method) === 'telebirr' ? 'bg-cyan-400' : (strtolower($pay->method) === 'cash' ? 'bg-emerald-400' : 'bg-purple-400') }}"
                                 style="width: {{ ($sellerData['total_paid'] ?? 0) > 0 ? min(100, round(($pay->total_amount / $sellerData['total_paid']) * 100)) : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] text-dark-500 mt-0.5 block">{{ $pay->count }} {{ \Illuminate\Support\Str::plural('receipt', $pay->count) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-dark-500 py-4 text-center">No payment transactions processed yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Bestselling Products by this Seller -->
            <div class="card bg-dark-900/90 border border-dark-800 rounded-2xl p-5">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-dark-800">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Top Selling Items</h3>
                        <p class="text-[11px] text-dark-400 mt-0.5">Your highest volume products</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($sellerData['top_products'] ?? [] as $index => $item)
                    <div class="p-2.5 rounded-xl bg-dark-950/60 border border-dark-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-5 h-5 rounded-md bg-dark-800 text-dark-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                #{{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <h5 class="text-xs font-bold text-white truncate">{{ $item->product->name ?? 'Product' }}</h5>
                                <span class="text-[10px] text-dark-400">{{ number_format($item->total_qty, 1) }} {{ $item->product->unit->code ?? 'units' }} sold</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-mono text-xs font-bold text-emerald-400">Br{{ number_format($item->total_revenue, 2) }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-dark-500 py-4 text-center">No sales lines recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Register Shortcuts & Helpers -->
            <div class="card bg-gradient-to-br from-dark-900 to-dark-950 border border-dark-800 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-1 rounded bg-primary-600/20 text-primary-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">POS Fast Actions</h3>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between text-dark-300 py-1 border-b border-dark-800/60">
                        <span>New Quick Sale</span>
                        <kbd class="px-1.5 py-0.5 rounded bg-dark-800 font-mono text-[10px] text-white border border-dark-700">F2</kbd>
                    </div>
                    <div class="flex items-center justify-between text-dark-300 py-1 border-b border-dark-800/60">
                        <span>Check Products Stock</span>
                        <kbd class="px-1.5 py-0.5 rounded bg-dark-800 font-mono text-[10px] text-white border border-dark-700">Alt + P</kbd>
                    </div>
                    <div class="flex items-center justify-between text-dark-300 py-1">
                        <span>View Sales Ledger</span>
                        <kbd class="px-1.5 py-0.5 rounded bg-dark-800 font-mono text-[10px] text-white border border-dark-700">Alt + S</kbd>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
