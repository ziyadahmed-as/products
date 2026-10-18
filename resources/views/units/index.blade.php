@extends('layouts.app')
@section('title', 'Units of Measure')

@section('content')
<div class="space-y-6">

    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Units of Measure</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">Standardized measurement units for inventory tracking and BOM recipes.</p>
        </div>
        <a href="{{ route('units.create') }}" class="btn-primary flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Unit</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('units.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Unit</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search unit name or abbreviation…" class="form-input text-xs pl-9">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-xs px-4 py-2">Filter</button>
                <a href="{{ route('units.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Units Directory</h3>
                <span class="badge badge-blue">{{ $units->total() }} units</span>
            </div>
            @if(request()->filled('search'))
                <span class="text-xs text-slate-500 dark:text-dark-400">Filtered results</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Unit Name</th>
                        <th>Code / Abbreviation</th>
                        <th>Associated Products</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs border border-emerald-200 dark:border-emerald-800/60">
                                    {{ strtoupper(substr($unit->name, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $unit->name }}</p>
                                    @if($unit->description)
                                        <p class="text-[11px] text-slate-400 dark:text-dark-400 truncate max-w-xs">{{ $unit->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-purple font-mono uppercase font-bold">{{ $unit->code }}</span>
                        </td>
                        <td>
                            <span class="badge badge-green">{{ $unit->products_count }} Products</span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('units.show', $unit) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/50 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('units.edit', $unit) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Delete unit {{ $unit->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-16 text-slate-400 dark:text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 dark:text-dark-400">No units of measure found</p>
                            <p class="text-xs text-slate-400 dark:text-dark-600 mt-1">Configure units of measure (e.g. kg, liter, pieces) for accurate inventory tracking.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/60">
            {{ $units->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
