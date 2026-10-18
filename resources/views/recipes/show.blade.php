@extends('layouts.app')
@section('title', 'Recipe — ' . $recipe->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1>{{ $recipe->name }}</h1>
                @if($recipe->is_active)
                    <span class="badge badge-green">Active</span>
                @else
                    <span class="badge badge-red">Inactive</span>
                @endif
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Output Product: <span class="font-semibold text-slate-800 dark:text-dark-200">{{ $recipe->product?->name }}</span> ({{ $recipe->product?->sku }})</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('recipes.edit', $recipe) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Recipe
            </a>
            <a href="{{ route('recipes.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Recipes
            </a>
        </div>
    </div>

    @if($recipe->description)
        <div class="card p-5">
            <h3 class="text-xs uppercase font-semibold text-slate-400 dark:text-dark-500 mb-1">Recipe Instructions & Notes</h3>
            <p class="text-sm text-slate-700 dark:text-dark-300">{{ $recipe->description }}</p>
        </div>
    @endif

    <!-- Bill of Materials (BOM) Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900 dark:text-white">Bill of Materials (BOM Ingredients)</h3>
            <span class="badge badge-blue">{{ $recipe->materials->count() }} Components</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Component Material</th>
                    <th>SKU</th>
                    <th>Required Qty</th>
                    <th>Unit</th>
                    <th>Current In-Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recipe->materials as $mat)
                <tr>
                    <td>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $mat->rawMaterial?->name ?? 'Unknown item' }}</p>
                    </td>
                    <td class="font-mono text-xs text-slate-500 dark:text-dark-400">{{ $mat->rawMaterial?->sku ?? '—' }}</td>
                    <td class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($mat->quantity, 3) }}</td>
                    <td>
                        <span class="badge badge-blue uppercase text-xs">{{ $mat->rawMaterial?->unit?->code ?? 'unit' }}</span>
                    </td>
                    <td>
                        <span class="text-sm {{ ($mat->rawMaterial?->quantity ?? 0) > 0 ? 'text-emerald-600 font-semibold' : 'text-rose-500 font-semibold' }}">
                            {{ number_format($mat->rawMaterial?->quantity ?? 0, 2) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        No materials defined for this recipe.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
