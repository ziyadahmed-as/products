@extends('layouts.app')
@section('title', 'Storage Locations')

@section('content')
<div class="space-y-6">

    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Storage Locations & Zones</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">Manage warehouse aisles, bins, shelves, and storage zones assigned to branches.</p>
        </div>
        <a href="{{ route('storage-locations.create') }}" class="btn-primary flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Storage Location</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('storage-locations.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Location</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search location name or code…" class="form-input text-xs pl-9">
                </div>
            </div>
            <div class="form-group min-w-[180px]">
                <label class="form-label text-xs">Branch</label>
                <select name="branch_id" class="form-select text-xs">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group min-w-[140px]">
                <label class="form-label text-xs">Status</label>
                <select name="status" class="form-select text-xs">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2">Filter</button>
                <a href="{{ route('storage-locations.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Locations Directory</h3>
                <span class="badge badge-blue">{{ $locations->total() }} zones</span>
            </div>
            @if(request()->anyFilled(['search', 'branch_id', 'status']))
                <span class="text-xs text-slate-500 dark:text-dark-400">Filtered results</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="table">
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
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs border border-sky-200 dark:border-sky-800/60">
                                    {{ strtoupper(substr($loc->name, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $loc->name }}</p>
                                    @if($loc->description)
                                        <p class="text-[11px] text-slate-400 dark:text-dark-400 truncate max-w-xs">{{ $loc->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-dark-200 border border-slate-200 dark:border-dark-700">{{ $loc->code }}</span>
                        </td>
                        <td>
                            <span class="badge badge-purple">{{ $loc->branch?->name ?? 'Unassigned' }}</span>
                        </td>
                        <td>
                            <span class="text-xs font-mono font-medium text-slate-700 dark:text-dark-200">{{ $loc->inventory_balances_count }} Stock Balances</span>
                        </td>
                        <td>
                            @if($loc->is_active)
                                <span class="badge badge-green">Active</span>
                            @else
                                <span class="badge badge-red">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('storage-locations.edit', $loc) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors" title="Edit Location">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('storage-locations.destroy', $loc) }}" onsubmit="return confirm('Delete storage location {{ $loc->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors" title="Delete Location">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16 text-slate-400 dark:text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 dark:text-dark-400">No storage locations found</p>
                            <p class="text-xs text-slate-400 dark:text-dark-600 mt-1">Configure storage zones, aisles, and warehouse bins for your branches.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/60">
            {{ $locations->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
