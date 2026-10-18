@extends('layouts.app')
@section('title', 'Manufacturing Orders')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Manufacturing Orders</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">Track production batches, raw material consumption, and finished product yields.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('manufacturing.dashboard') }}" class="btn-secondary text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Staff Dashboard</span>
            </a>
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-sm shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Production Order</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Status Tabs / Filters -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        @php
            $currentStatus = request('status', '');
            $tabs = [
                ''                 => ['label' => 'All Orders', 'count' => $statusCounts['all']],
                'draft'            => ['label' => 'Draft', 'count' => $statusCounts['draft']],
                'pending_approval' => ['label' => 'Pending Approval', 'count' => $statusCounts['pending_approval']],
                'approved'         => ['label' => 'Approved', 'count' => $statusCounts['approved']],
                'materials_issued' => ['label' => 'Materials Issued', 'count' => $statusCounts['materials_issued']],
                'in_progress'      => ['label' => 'In Production', 'count' => $statusCounts['in_progress']],
                'completed'        => ['label' => 'Completed', 'count' => $statusCounts['completed']],
                'cancelled'        => ['label' => 'Cancelled', 'count' => $statusCounts['cancelled']],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            <a href="{{ route('manufacturing-orders.index', array_merge(request()->query(), ['status' => $key, 'page' => 1])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition flex items-center gap-2 {{ $currentStatus === $key ? 'bg-sky-600 text-white font-semibold shadow-xs' : 'bg-white dark:bg-dark-900 text-slate-700 dark:text-dark-300 hover:bg-slate-100 dark:hover:bg-dark-800 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-dark-800' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === $key ? 'bg-white/20 text-white font-bold' : 'bg-slate-100 dark:bg-dark-800 text-slate-600 dark:text-dark-400' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Search & Filter Bar -->
    <div class="card p-4">
        <form method="GET" action="{{ route('manufacturing-orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-dark-400 mb-1">Search Reference / Product</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. MO-2026 or Oil..."
                           class="form-input text-xs pl-9">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-dark-400 mb-1">From Planned Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="form-input text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-dark-400 mb-1">To Planned Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="form-input text-xs">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2 w-full justify-center">
                    Filter Orders
                </button>
                <a href="{{ route('manufacturing-orders.index') }}" class="btn-secondary text-xs px-3 py-2" title="Reset Filters">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Orders Ledger</h3>
                <span class="badge badge-blue">{{ $orders->total() }} production orders</span>
            </div>
            @if(request()->anyFilled(['search', 'start_date', 'end_date', 'status']))
                <span class="text-xs text-slate-500 dark:text-dark-400">Filtered results</span>
            @endif
        </div>

        @if($orders->isEmpty())
            <div class="py-16 text-center text-slate-500 dark:text-dark-400">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <p class="font-medium text-slate-600 dark:text-dark-400">No manufacturing orders matched your filter.</p>
                <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-xs mt-3 inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Manufacturing Order</span>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order Reference</th>
                            <th>Product & BOM</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Raw Materials</th>
                            <th>Assigned Staff</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                                <td>
                                    <a href="{{ route('manufacturing-orders.show', $order) }}" class="font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        {{ $order->reference }}
                                    </a>
                                    <div class="text-[11px] font-mono text-slate-500 dark:text-dark-400 mt-0.5">
                                        Date: {{ $order->planned_date?->format('M d, Y') ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium text-slate-900 dark:text-white text-sm">{{ $order->product->name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-dark-400">
                                        {{ $order->recipe ? 'Recipe: ' . $order->recipe->name : 'Manual Material Spec' }}
                                    </div>
                                </td>
                                <td class="text-xs text-slate-600 dark:text-dark-300">
                                    <span class="badge badge-purple">{{ $order->storageLocation->name ?? 'Default' }}</span>
                                </td>
                                <td>
                                    <div class="font-mono text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $order->planned_quantity }} <span class="text-xs text-slate-500 dark:text-dark-400 font-sans">{{ $order->unit?->code ?? $order->product->unit?->code ?? 'units' }}</span>
                                    </div>
                                    @if($order->status === 'completed')
                                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">
                                            Yield: {{ $order->good_quantity }} accepted
                                            @if($order->rejected_quantity > 0)
                                                <span class="text-rose-600 dark:text-rose-400">({{ $order->rejected_quantity }} rejected)</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $st = strtolower($order->status);
                                        $badgeClass = match($st) {
                                            'draft'            => 'badge badge-gray',
                                            'pending', 'pending_approval' => 'badge badge-yellow',
                                            'approved'         => 'badge badge-blue',
                                            'materials_issued' => 'badge badge-indigo',
                                            'in_progress'      => 'badge badge-purple',
                                            'completed'        => 'badge badge-green',
                                            'cancelled'        => 'badge badge-red',
                                            default            => 'badge badge-gray',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">
                                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($order->is_materials_issued)
                                        <span class="inline-flex items-center text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Issued & Deducted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-xs text-amber-600 dark:text-amber-400 font-medium">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Pending Deduction
                                        </span>
                                    @endif
                                    <div class="text-[11px] text-slate-500 dark:text-dark-400 mt-0.5">{{ $order->materialIssues->count() }} raw materials</div>
                                </td>
                                <td class="text-xs text-slate-600 dark:text-dark-300">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-dark-700 text-slate-700 dark:text-dark-200 text-[10px] font-bold flex items-center justify-center">
                                            {{ strtoupper(substr($order->assignedUser?->name ?? 'U', 0, 1)) }}
                                        </span>
                                        {{ $order->assignedUser?->name ?? 'Unassigned' }}
                                    </span>
                                </td>
                                <td class="text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="btn-secondary text-xs px-2.5 py-1" title="View & Process">
                                            Manage
                                        </a>
                                        @if(in_array(strtolower($order->status), ['draft', 'pending']))
                                            <a href="{{ route('manufacturing-orders.edit', $order) }}" class="p-1 rounded text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors" title="Edit Order">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/60">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
