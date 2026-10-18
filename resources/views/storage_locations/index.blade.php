@extends('layouts.app')
@section('title', 'Storage Locations')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Storage Locations & Zones</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Manage warehouse aisles, bins, shelves, and storage zones assigned to branches.</p>
        </div>
        <a href="{{ route('storage-locations.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Storage Location
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search location name or code…" class="form-input flex-1 min-w-[200px]">
        <select name="branch_id" class="form-input w-52">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-input w-36">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('storage-locations.index') }}" class="btn-secondary">Reset</a>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Code</th>
                    <th>Assigned Branch</th>
                    <th>Inventory Records</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $loc)
                <tr>
                    <td>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $loc->name }}</p>
                    </td>
                    <td>
                        <span class="font-mono text-xs font-semibold text-slate-600 dark:text-dark-300">{{ $loc->code }}</span>
                    </td>
                    <td>
                        <span class="badge badge-blue">{{ $loc->branch?->name ?? 'Unassigned' }}</span>
                    </td>
                    <td>
                        <span class="text-sm text-slate-600 dark:text-dark-300">{{ $loc->inventory_balances_count }} Stock Balances</span>
                    </td>
                    <td>
                        @if($loc->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('storage-locations.edit', $loc) }}" class="btn-icon text-slate-500 hover:text-amber-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('storage-locations.destroy', $loc) }}" onsubmit="return confirm('Delete storage location {{ $loc->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon text-slate-500 hover:text-rose-600" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        No storage locations found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-dark-800">
            {{ $locations->links() }}
        </div>
    </div>

</div>
@endsection
