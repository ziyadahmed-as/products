@extends('layouts.app')
@section('title', 'Units of Measure')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Units of Measure</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Standardized measurement units for inventory tracking and BOM recipes.</p>
        </div>
        <a href="{{ route('units.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Unit
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search unit name or abbreviation…" class="form-input flex-1 min-w-[200px]">
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('units.index') }}" class="btn-secondary">Reset</a>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table class="data-table">
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
                <tr>
                    <td>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $unit->name }}</p>
                    </td>
                    <td>
                        <span class="badge badge-blue font-mono uppercase">{{ $unit->code }}</span>
                    </td>
                    <td>
                        <span class="badge badge-green">{{ $unit->products_count }} Products</span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('units.show', $unit) }}" class="btn-icon text-slate-500 hover:text-sky-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('units.edit', $unit) }}" class="btn-icon text-slate-500 hover:text-amber-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Delete unit {{ $unit->name }}?')">
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
                    <td colspan="4" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        No units of measure found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-dark-800">
            {{ $units->links() }}
        </div>
    </div>

</div>
@endsection
