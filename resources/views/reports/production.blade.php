@extends('layouts.app')
@section('title', 'Production Report')

@section('content')
<div class="space-y-5">
    <div class="page-header">
        <div>
            <h1>Production Report</h1>
            <p class="text-dark-400 text-sm mt-1">Completed Orders: {{ $summary['completed'] }} | Total Quantity Produced: {{ $summary['total_produced'] }}</p>
        </div>
        <button onclick="window.print()" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4 print:hidden">
        <form method="GET" class="flex gap-3 items-end">
            <div class="form-group min-w-[150px]">
                <label class="form-label">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input">
            </div>
            <div class="form-group min-w-[150px]">
                <label class="form-label">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </form>
    </div>

    <div class="card p-0">
        <div class="table-wrapper rounded-2xl">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Product</th>
                        <th>Planned Qty</th>
                        <th>Actual Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="text-dark-300">{{ $order->created_at->format('Y-m-d') }}</td>
                        <td class="font-mono text-primary-400">{{ $order->reference }}</td>
                        <td class="font-medium text-white">{{ $order->product->name ?? '—' }}</td>
                        <td class="text-dark-300">{{ $order->planned_quantity }}</td>
                        <td class="font-bold text-white">{{ $order->actual_quantity ?? '—' }}</td>
                        <td>
                            @if($order->status === 'Completed')
                                <span class="badge-green">Completed</span>
                            @elseif($order->status === 'Pending')
                                <span class="badge-yellow">Pending</span>
                            @else
                                <span class="badge-blue">{{ $order->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-dark-500">No production orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

