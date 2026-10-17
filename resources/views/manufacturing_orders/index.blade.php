@extends('layouts.app')
@section('title', 'Manufacturing Orders')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Manufacturing Orders</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">Track production batches, raw material consumption, and finished product yields.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('manufacturing.dashboard') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Staff Dashboard
            </a>
            <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-sm shadow-sm">
                <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Production Order
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
               class="px-3.5 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition flex items-center gap-2 {{ $currentStatus === $key ? 'bg-primary-600 text-white font-semibold shadow-sm' : 'bg-white dark:bg-dark-900 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-800 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-dark-800' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === $key ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400' }}">
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
                <label class="block text-xs font-medium text-gray-700 dark:text-dark-400 mb-1">Search Reference / Product</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. MO-2026 or Oil..."
                       class="form-input text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-dark-400 mb-1">From Planned Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="form-input text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-dark-400 mb-1">To Planned Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="form-input text-sm">
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
    <div class="card">
        @if($orders->isEmpty())
            <div class="py-12 text-center text-gray-500 dark:text-dark-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400 dark:text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium">No manufacturing orders matched your filter.</p>
                <a href="{{ route('manufacturing-orders.create') }}" class="btn-primary text-xs mt-3 inline-block">Create Manufacturing Order</a>
            </div>
        @else
            <div class="table-wrapper">
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
                            <tr>
                                <td>
                                    <a href="{{ route('manufacturing-orders.show', $order) }}" class="font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ $order->reference }}
                                    </a>
                                    <div class="text-[11px] text-gray-500 dark:text-dark-400">
                                        Date: {{ $order->planned_date?->format('M d, Y') ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $order->product->name }}</div>
                                    <div class="text-[11px] text-gray-500 dark:text-dark-400">
                                        {{ $order->recipe ? 'Recipe: ' . $order->recipe->name : 'Manual Material Spec' }}
                                    </div>
                                </td>
                                <td class="text-xs text-gray-600 dark:text-dark-300">
                                    {{ $order->storageLocation->name ?? 'Default' }}
                                </td>
                                <td>
                                    <div class="font-mono text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $order->planned_quantity }} <span class="text-xs text-gray-500 dark:text-dark-400 font-sans">{{ $order->unit?->code ?? $order->product->unit?->code ?? 'units' }}</span>
                                    </div>
                                    @if($order->status === 'completed')
                                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">
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
                                            'draft'            => 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-dark-700',
                                            'pending', 'pending_approval' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60',
                                            'approved'         => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60',
                                            'materials_issued' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60',
                                            'in_progress'      => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60',
                                            'completed'        => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60',
                                            'cancelled'        => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60',
                                            default            => 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded text-xs font-medium {{ $badgeClass }}">
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
                                    <div class="text-[11px] text-gray-500 dark:text-dark-400">{{ $order->materialIssues->count() }} raw materials</div>
                                </td>
                                <td class="text-xs text-gray-600 dark:text-dark-300">
                                    {{ $order->assignedUser?->name ?? 'Unassigned' }}
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('manufacturing-orders.show', $order) }}" class="btn-secondary text-xs px-2.5 py-1" title="View & Process">
                                            Manage
                                        </a>
                                        @if(in_array(strtolower($order->status), ['draft', 'pending']))
                                            <a href="{{ route('manufacturing-orders.edit', $order) }}" class="p-1 rounded text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-800" title="Edit Order">
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

            <div class="p-4 border-t border-gray-200 dark:border-dark-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
