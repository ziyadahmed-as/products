@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Greeting -->
    <div class="page-header">
        <div>
            <h1 class="text-gradient">Welcome, {{ auth()->user()->name ?? 'User' }} 👋</h1>
            <p class="text-dark-400 text-sm mt-1">Here's what's happening across Albareck today.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Sale
        </a>
    </div>

    <!-- Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <!-- Total Products -->
        <div class="stat-card animate-fade-in-up" style="animation-delay:.05s">
            <div class="stat-icon bg-primary-900/40">
                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">{{ $stats['total_products'] ?? 0 }}</p>
                <p class="stat-label">Total Products</p>
            </div>
        </div>

        <!-- Today Sales -->
        <div class="stat-card animate-fade-in-up" style="animation-delay:.1s">
            <div class="stat-icon bg-emerald-900/40">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value">Br{{ number_format($stats['today_sales'] ?? 0, 2) }}</p>
                <p class="stat-label">Today's Sales</p>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="stat-card animate-fade-in-up" style="animation-delay:.15s">
            <div class="stat-icon bg-yellow-900/40">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-yellow-400">{{ $stats['low_stock'] ?? 0 }}</p>
                <p class="stat-label">Low Stock Items</p>
            </div>
        </div>

        <!-- Active Orders -->
        <div class="stat-card animate-fade-in-up" style="animation-delay:.2s">
            <div class="stat-icon bg-purple-900/40">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/>
                </svg>
            </div>
            <div>
                <p class="stat-value text-purple-400">{{ $stats['active_orders'] ?? 0 }}</p>
                <p class="stat-label">Active Mfg Orders</p>
            </div>
        </div>
    </div>

    <!-- Middle Row -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Recent Sales -->
        <div class="card xl:col-span-2">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-white">Recent Sales</h2>
                <a href="{{ route('sales.index') }}" class="btn-secondary text-xs px-3 py-1.5">View All</a>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSales ?? [] as $sale)
                        <tr>
                            <td class="font-mono text-primary-400">{{ $sale->reference }}</td>
                            <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                            <td class="font-semibold">Br{{ number_format($sale->total, 2) }}</td>
                            <td>
                                @if($sale->payment_status === 'Paid')
                                    <span class="badge-green">Paid</span>
                                @elseif($sale->payment_status === 'Partial')
                                    <span class="badge-yellow">Partial</span>
                                @else
                                    <span class="badge-red">Unpaid</span>
                                @endif
                            </td>
                            <td class="text-dark-400">{{ $sale->created_at->format('d M') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-dark-500">No sales yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-white">Low Stock</h2>
                <span class="badge-red">{{ ($lowStockItems ?? collect())->count() }} Items</span>
            </div>
            <div class="space-y-3">
                @forelse ($lowStockItems ?? [] as $item)
                <div class="flex items-center justify-between p-3 rounded-xl bg-dark-800/60">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $item->product->name ?? 'N/A' }}</p>
                        <p class="text-xs text-dark-500">Min: {{ $item->product->minimum_stock_level ?? 0 }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-yellow-400">{{ $item->quantity }}</p>
                        <p class="text-xs text-dark-500">in stock</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-xl bg-emerald-900/30 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm text-dark-500">All items well stocked!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Row - Manufacturing & Quick Actions -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Open Manufacturing Orders -->
        <div class="card">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-white">Manufacturing Orders</h2>
                <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-xs px-3 py-1.5">View All</a>
            </div>
            <div class="space-y-3">
                @forelse ($openOrders ?? [] as $order)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-dark-800/60">
                    <div class="w-9 h-9 rounded-lg bg-purple-900/40 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $order->product->name ?? 'N/A' }}</p>
                        <p class="text-xs text-dark-500">Qty: {{ $order->planned_quantity }} • {{ $order->planned_date }}</p>
                    </div>
                    @if($order->status === 'Pending')
                        <span class="badge-yellow">Pending</span>
                    @elseif($order->status === 'In Progress')
                        <span class="badge-blue">In Progress</span>
                    @else
                        <span class="badge-green">{{ $order->status }}</span>
                    @endif
                </div>
                @empty
                <p class="text-sm text-dark-500 text-center py-6">No open manufacturing orders</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h2 class="text-base font-semibold text-white mb-5">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('sales.create') }}" class="card-hover group p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-emerald-900/40 flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-700/40 transition-colors">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-white">New Sale</p>
                </a>
                <a href="{{ route('stock-receipts.create') }}" class="card-hover group p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-900/40 flex items-center justify-center mx-auto mb-2 group-hover:bg-primary-700/40 transition-colors">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-white">Stock Receipt</p>
                </a>
                <a href="{{ route('products.create') }}" class="card-hover group p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-yellow-900/40 flex items-center justify-center mx-auto mb-2 group-hover:bg-yellow-700/40 transition-colors">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-white">Add Product</p>
                </a>
                <a href="{{ route('manufacturing-orders.create') }}" class="card-hover group p-4 text-center">
                    <div class="w-10 h-10 rounded-xl bg-purple-900/40 flex items-center justify-center mx-auto mb-2 group-hover:bg-purple-700/40 transition-colors">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-white">New Order</p>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

