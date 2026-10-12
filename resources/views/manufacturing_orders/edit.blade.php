@extends('layouts.app')
@section('title', 'Edit Manufacturing Order #' . $order->reference)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manufacturing-orders.show', $order) }}" class="text-dark-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-white tracking-tight">Edit Manufacturing Order <span class="font-mono text-primary-400">#{{ $order->reference }}</span></h1>
            </div>
            <p class="text-dark-400 text-sm mt-1">Update planned quantities, scheduling, or assigned staff for this draft order.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-800/50 text-rose-300">
            <p class="font-semibold text-sm mb-1">Please correct the following errors:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('manufacturing-orders.update', $order) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card space-y-4">
            <h2 class="text-base font-semibold text-white border-b border-dark-800 pb-3">Order Quantities & Location</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-400 mb-1">Product to Manufacture</label>
                    <p class="text-sm font-bold text-white">{{ $order->product->name }} (SKU: {{ $order->product->sku }})</p>
                    <span class="text-xs text-dark-500">Output product cannot be changed after creation.</span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Planned Production Quantity <span class="text-rose-400">*</span>
                    </label>
                    <input type="number" step="any" min="0.01" name="planned_quantity" required
                           value="{{ old('planned_quantity', $order->planned_quantity) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-dark-300 mb-1.5">
                    Storage Location / Warehouse <span class="text-rose-400">*</span>
                </label>
                <select name="storage_location_id" required class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                    @foreach($storageLocations as $loc)
                        <option value="{{ $loc->id }}" {{ old('storage_location_id', $order->storage_location_id) == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ $loc->branch->name ?? 'Main' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card space-y-4">
            <h2 class="text-base font-semibold text-white border-b border-dark-800 pb-3">Scheduling & Staff</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Planned Date <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="planned_date" required
                           value="{{ old('planned_date', $order->planned_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Start Date
                    </label>
                    <input type="date" name="start_date"
                           value="{{ old('start_date', $order->start_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Expected Completion Date
                    </label>
                    <input type="date" name="expected_completion_date"
                           value="{{ old('expected_completion_date', $order->expected_completion_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Assigned Production Staff
                    </label>
                    <select name="assigned_user_id" class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">
                        <option value="">-- Unassigned --</option>
                        @foreach($staffMembers as $staff)
                            <option value="{{ $staff->id }}" {{ old('assigned_user_id', $order->assigned_user_id) == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->roles->first()->name ?? 'Staff' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark-300 mb-1.5">
                        Additional Costs
                    </label>
                    <input type="number" step="any" min="0" name="additional_costs"
                           value="{{ old('additional_costs', $order->additional_costs) }}"
                           class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-dark-300 mb-1.5">
                    Production Notes & Instructions
                </label>
                <textarea name="production_notes" rows="3"
                          class="w-full px-3 py-2 rounded-lg bg-dark-900 border border-dark-800 text-white text-sm focus:border-primary-500 focus:outline-none">{{ old('production_notes', $order->production_notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('manufacturing-orders.show', $order) }}" class="btn-secondary text-sm">
                Cancel
            </a>
            <button type="submit" class="btn-primary text-sm px-6 py-2.5">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
