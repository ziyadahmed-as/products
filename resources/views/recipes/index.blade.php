@extends('layouts.app')
@section('title', 'Recipes & Bill of Materials')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Recipes & Bill of Materials (BOM)</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Configure formulas, raw material proportions, and bills of materials for production.</p>
        </div>
        <a href="{{ route('recipes.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Recipe
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipe or output product…" class="form-input flex-1 min-w-[200px]">
        <select name="status" class="form-input w-36">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('recipes.index') }}" class="btn-secondary">Reset</a>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table class="data-table">
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
                <tr>
                    <td>
                        <a href="{{ route('recipes.show', $recipe) }}" class="font-semibold text-slate-900 dark:text-white hover:text-cyan-600 transition-colors text-sm">
                            {{ $recipe->name }}
                        </a>
                        @if($recipe->description)
                            <p class="text-xs text-slate-500 dark:text-dark-400 truncate max-w-sm mt-0.5">{{ $recipe->description }}</p>
                        @endif
                    </td>
                    <td>
                        <span class="font-medium text-slate-800 dark:text-dark-200 text-sm">{{ $recipe->product?->name ?? '—' }}</span>
                        <p class="text-xs text-slate-400 font-mono">{{ $recipe->product?->sku }}</p>
                    </td>
                    <td>
                        <span class="badge badge-blue">{{ $recipe->materials_count }} Ingredients</span>
                    </td>
                    <td>
                        @if($recipe->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('recipes.show', $recipe) }}" class="btn-icon text-slate-500 hover:text-sky-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('recipes.edit', $recipe) }}" class="btn-icon text-slate-500 hover:text-amber-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('Delete recipe {{ $recipe->name }}?')">
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
                    <td colspan="5" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        No recipes found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-dark-800">
            {{ $recipes->links() }}
        </div>
    </div>

</div>
@endsection
