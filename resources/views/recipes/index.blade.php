@extends('layouts.app')
@section('title', 'Recipes & Bill of Materials')

@section('content')
<div class="space-y-6">

    <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Recipes & Bill of Materials (BOM)</h1>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1 ml-10">Configure formulas, raw material proportions, and bills of materials for production.</p>
        </div>
        <a href="{{ route('recipes.create') }}" class="btn-primary flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Recipe</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('recipes.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="form-group flex-1 min-w-[200px]">
                <label class="form-label text-xs">Search Recipe or Product</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipe or output product…" class="form-input text-xs pl-9">
                </div>
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
                <a href="{{ route('recipes.index') }}" class="btn-secondary text-xs px-3 py-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card p-0 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-200 dark:border-dark-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">BOM Recipes Directory</h3>
                <span class="badge badge-blue">{{ $recipes->total() }} recipes</span>
            </div>
            @if(request()->anyFilled(['search', 'status']))
                <span class="text-xs text-slate-500 dark:text-dark-400">Filtered results</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Output Product</th>
                        <th>BOM Ingredients</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipes as $recipe)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-850/50 transition-colors">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-200 dark:border-amber-800/60">
                                    {{ strtoupper(substr($recipe->name, 0, 1)) }}
                                </span>
                                <div>
                                    <a href="{{ route('recipes.show', $recipe) }}" class="font-semibold text-slate-900 dark:text-white hover:text-sky-600 dark:hover:text-sky-400 transition-colors text-sm">
                                        {{ $recipe->name }}
                                    </a>
                                    @if($recipe->description)
                                        <p class="text-xs text-slate-500 dark:text-dark-400 truncate max-w-sm mt-0.5">{{ $recipe->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium text-slate-800 dark:text-dark-200 text-sm">{{ $recipe->product?->name ?? '—' }}</span>
                            <p class="text-xs text-slate-400 font-mono">{{ $recipe->product?->sku }}</p>
                        </td>
                        <td>
                            <span class="badge badge-purple">{{ $recipe->materials_count }} Ingredients</span>
                        </td>
                        <td>
                            @if($recipe->is_active)
                                <span class="badge badge-green">Active</span>
                            @else
                                <span class="badge badge-red">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('recipes.show', $recipe) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/50 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('recipes.edit', $recipe) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('Delete recipe {{ $recipe->name }}?')">
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
                        <td colspan="5" class="text-center py-16 text-slate-400 dark:text-dark-500">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <p class="font-medium text-slate-600 dark:text-dark-400">No recipes found</p>
                            <p class="text-xs text-slate-400 dark:text-dark-600 mt-1">Configure formulas and raw material bills of materials for finished goods.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recipes->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/60">
            {{ $recipes->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
