<div class="space-y-6" x-data="{ searchQuery: '', stockFilter: 'all' }">

    <!-- Top Greeting & Seller Command Center Bar (Matches Admin Dashboard) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-dark-900 border border-slate-200/80 dark:border-dark-800 rounded-2xl p-5 sm:p-6 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Sales Command Center
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    Seller / POS Register
                </span>
                @if(auth()->user()->branches->isNotEmpty())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-dark-700 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ auth()->user()->branches->pluck('name')->join(', ') }}
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Real-time point-of-sale terminal, sellable inventory lookup, and personal sales performance.
            </p>
        </div>

        <!-- Primary Quick Actions for Seller -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('sales.create') }}" class="btn-primary text-sm shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>New Quick Sale (POS)</span>
                <kbd class="text-[10px] bg-sky-800/20 px-1.5 py-0.5 rounded border border-white/20 font-mono text-white">F2</kbd>
            </a>
            <a href="{{ route('sales.index') }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>My Sales Records</span>
            </a>
            <a href="{{ route('sale-returns.index') }}" class="btn-secondary text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                <span>Returns</span>
            </a>
        </div>
    </div>

    <!-- Core KPI Stat Cards Row for Seller (Matches Admin Dashboard Styling) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. Today's Sales Revenue — Emerald Accent -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wide">Today's Sales</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Br {{ number_format($sellerData['today_sales'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                    {{ $sellerData['today_count'] ?? 0 }} orders
                </span>
                <span class="text-slate-500 dark:text-slate-400">completed today</span>
            </div>
        </div>

        <!-- 2. This Month's Sales Volume — Sky Accent -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-sky-500 to-blue-500"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wide">This Month's Sales</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Br {{ number_format($sellerData['month_sales'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-sky-700 bg-sky-50 dark:bg-sky-950/70 dark:text-sky-400 px-2 py-0.5 rounded-full">
                    {{ $sellerData['month_count'] ?? 0 }} orders
                </span>
                <span class="text-slate-500 dark:text-slate-400">this month</span>
            </div>
        </div>

        <!-- 3. Payments Collected — Amber Accent -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wide">Collected Payments</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Br {{ number_format($sellerData['total_paid'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs">
                @if(($sellerData['total_unpaid'] ?? 0) > 0)
                    <span class="inline-flex items-center gap-1 font-semibold text-rose-700 bg-rose-50 dark:bg-rose-950/70 dark:text-rose-400 px-2 py-0.5 rounded-full">
                        Br {{ number_format($sellerData['total_unpaid'], 2) }} credit
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/70 dark:text-emerald-400 px-2 py-0.5 rounded-full">
                        All settled ✔
                    </span>
                @endif
            </div>
        </div>

        <!-- 4. Average Order Value (AOV) — Purple Accent -->
        <div class="relative overflow-hidden bg-gradient-to-br from-white to-slate-50/70 dark:from-dark-900 dark:to-dark-800/90 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs hover:-translate-y-1 hover:shadow-lg transition-all duration-300 ease-out group">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wide">Avg. Order Value</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Br {{ number_format($sellerData['avg_order_value'] ?? 0, 2) }}</div>
            <div class="flex items-center gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 font-medium text-purple-700 bg-purple-50 dark:bg-purple-950/70 dark:text-purple-300 px-2 py-0.5 rounded-full">
                    Lifetime: {{ $sellerData['total_count'] ?? 0 }} orders
                </span>
            </div>
        </div>

    </div>

    <!-- Sellable Inventory & Recent Orders Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Sellable Catalog Lookup & Recent Sales -->
        <div class="xl:col-span-2 space-y-6">

            <!-- Fast Sellable Products Stock Lookup (Counter POS Tool) -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-dark-800 gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Sellable Products & Stock Lookup
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Quick price check and current shelf inventory for counter sales</p>
                    </div>
                    <!-- Live Search Input -->
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Search product or SKU..."
                                   class="form-input text-xs pl-8 pr-3 py-1.5 w-48 sm:w-60 bg-slate-50 dark:bg-dark-800 border-slate-200 dark:border-dark-700 text-slate-900 dark:text-white rounded-lg">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <a href="{{ route('products.index') }}" class="btn-secondary py-1.5 px-3 text-xs">
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
                         class="p-3.5 rounded-xl border border-slate-200/80 dark:border-dark-700 hover:border-sky-300 dark:hover:border-sky-500 hover:shadow-xs transition flex flex-col justify-between bg-slate-50/50 dark:bg-dark-800/60 group">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">{{ $prod->category->name ?? 'General' }}</span>
                                @if($isOut)
                                    <span class="badge badge-red text-[10px] py-0 px-1.5">Out of Stock</span>
                                @elseif($isLow)
                                    <span class="badge badge-yellow text-[10px] py-0 px-1.5">Low Stock</span>
                                @else
                                    <span class="badge badge-green text-[10px] py-0 px-1.5">In Stock</span>
                                @endif
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition line-clamp-1">{{ $prod->name }}</h4>
                            <p class="font-mono text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">SKU: {{ $prod->sku }}</p>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-slate-200/80 dark:border-dark-700 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 block leading-none">Price:</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Br {{ number_format($prod->selling_price, 2) }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">/ {{ $prod->unit->code ?? 'unit' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] font-bold font-mono {{ $isOut ? 'text-rose-600 dark:text-rose-400' : ($isLow ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                    {{ number_format($stockQty, 1) }}
                                </span>
                                <a href="{{ route('sales.create') }}" class="mt-1 block px-2 py-0.5 rounded bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 hover:bg-sky-600 hover:text-white text-[10px] font-semibold transition border border-sky-200 dark:border-sky-800">
                                    Sell Now →
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                        No sellable products found in inventory.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Seller's Recent Transactions Table (Matches Admin Dashboard) -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 shadow-xs p-0 overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            My Recent Sales Transactions
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Live register logs, invoices, and customer receipt records</p>
                    </div>
                    <a href="{{ route('sales.index') }}" class="btn-secondary py-1.5 px-3 text-xs">
                        View All ({{ $sellerData['total_count'] ?? 0 }})
                    </a>
                </div>

                <div class="table-wrapper border-0 rounded-none shadow-none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date / Time</th>
                                <th>Items</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Method</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellerData['recent_sales'] ?? [] as $sale)
                            <tr>
                                <td class="font-mono text-xs font-bold text-sky-600 dark:text-sky-400">
                                    <a href="{{ route('sales.show', $sale) }}" class="hover:underline">
                                        {{ $sale->reference }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900 dark:text-white text-xs">{{ $sale->customer_name ?: 'Walk-in Customer' }}</div>
                                    @if($sale->customer_phone)
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $sale->customer_phone }}</div>
                                    @endif
                                </td>
                                <td class="text-xs text-slate-600 dark:text-slate-300">
                                    <div>{{ $sale->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $sale->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="text-xs text-slate-600 dark:text-slate-300">
                                    {{ $sale->lines->count() }} {{ \Illuminate\Support\Str::plural('item', $sale->lines->count()) }}
                                </td>
                                <td class="text-right font-mono font-bold text-xs text-slate-900 dark:text-white">
                                    Br {{ number_format($sale->total, 2) }}
                                </td>
                                <td class="text-center">
                                    @if(strtolower($sale->payment_status) === 'paid')
                                        <span class="badge badge-green text-[10px]">Paid</span>
                                    @elseif(strtolower($sale->payment_status) === 'partial')
                                        <span class="badge badge-yellow text-[10px]">Partial</span>
                                    @else
                                        <span class="badge badge-red text-[10px]">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-center text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-dark-800 text-[10px] font-medium text-slate-700 dark:text-slate-300">
                                        {{ $sale->payments->first()->method ?? 'Cash' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('invoice.show', $sale) }}" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 hover:underline">
                                        Invoice
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-500 dark:text-slate-400">
                                    No sales transactions recorded yet today.
                                    <a href="{{ route('sales.create') }}" class="text-sky-600 dark:text-sky-400 text-xs hover:underline block mt-1">Make your first sale</a>
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
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-dark-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Payment Breakdown</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Settlement channels for your sales</p>
                    </div>
                </div>

                <div class="space-y-3.5">
                    @forelse($sellerData['payments_breakdown'] ?? [] as $pay)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-semibold text-slate-800 dark:text-slate-200 capitalize flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ strtolower($pay->method) === 'telebirr' ? 'bg-sky-500' : (strtolower($pay->method) === 'cash' ? 'bg-emerald-500' : 'bg-purple-500') }}"></span>
                                {{ $pay->method ?: 'Cash' }}
                            </span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">Br {{ number_format($pay->total_amount, 2) }}</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-dark-800 overflow-hidden">
                            <div class="h-full rounded-full {{ strtolower($pay->method) === 'telebirr' ? 'bg-sky-500' : (strtolower($pay->method) === 'cash' ? 'bg-emerald-500' : 'bg-purple-500') }}"
                                 style="width: {{ ($sellerData['total_paid'] ?? 0) > 0 ? min(100, round(($pay->total_amount / $sellerData['total_paid']) * 100)) : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 block">{{ $pay->count }} {{ \Illuminate\Support\Str::plural('receipt', $pay->count) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">No payment transactions processed yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Bestselling Products by this Seller -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-dark-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Top Selling Items</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Your highest volume products</p>
                    </div>
                </div>

                <div class="space-y-2.5">
                    @forelse($sellerData['top_products'] ?? [] as $index => $item)
                    <div class="p-2.5 rounded-xl border border-slate-100 dark:border-dark-800 bg-slate-50/60 dark:bg-dark-800/60 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-5 h-5 rounded-md bg-white dark:bg-dark-700 border border-slate-200 dark:border-dark-600 text-slate-600 dark:text-slate-200 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                #{{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $item->product->name ?? 'Product' }}</h5>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ number_format($item->total_qty, 1) }} {{ $item->product->unit->code ?? 'units' }} sold</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">Br {{ number_format($item->total_revenue, 2) }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">No sales lines recorded yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Register Shortcuts & Helpers -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <span class="p-1 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">POS Keyboard Shortcuts</h3>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 py-1 border-b border-slate-100 dark:border-dark-800">
                        <span>New Quick Sale</span>
                        <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-dark-800 font-mono text-[10px] text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-dark-700 shadow-2xs">F2</kbd>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 py-1 border-b border-slate-100 dark:border-dark-800">
                        <span>Check Products Stock</span>
                        <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-dark-800 font-mono text-[10px] text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-dark-700 shadow-2xs">Alt + P</kbd>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 py-1">
                        <span>View Sales Ledger</span>
                        <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-dark-800 font-mono text-[10px] text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-dark-700 shadow-2xs">Alt + S</kbd>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
