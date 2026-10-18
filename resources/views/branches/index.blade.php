@extends('layouts.app')
@section('title', 'Branches')

@section('content')
<div class="space-y-5">

    <div class="page-header">
        <div>
            <h1>Branches</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Manage store branches, warehouses and distribution centres.</p>
        </div>
        <a href="{{ route('branches.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Branch
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or code…" class="form-input flex-1 min-w-[200px]">
        <select name="type" class="form-input w-52">
            <option value="">All Types</option>
            <option value="headquarters" {{ request('type') === 'headquarters' ? 'selected' : '' }}>Headquarters</option>
            <option value="branch" {{ request('type') === 'branch' ? 'selected' : '' }}>Branch</option>
            <option value="warehouse" {{ request('type') === 'warehouse' ? 'selected' : '' }}>Warehouse</option>
            <option value="distribution_center" {{ request('type') === 'distribution_center' ? 'selected' : '' }}>Distribution Centre</option>
        </select>
        <select name="status" class="form-input w-36">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('branches.index') }}" class="btn-secondary">Reset</a>
    </form>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Type</th>
                    <th>Parent</th>
                    <th>Users</th>
                    <th>Locations</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $branch->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-dark-400">{{ $branch->code }}</p>
                    </td>
                    <td>
                        <span class="badge badge-blue capitalize">{{ str_replace('_', ' ', $branch->type) }}</span>
                    </td>
                    <td class="text-sm text-slate-600 dark:text-dark-300">{{ $branch->parent?->name ?? '—' }}</td>
                    <td class="text-sm text-slate-600 dark:text-dark-300">{{ $branch->users_count }}</td>
                    <td class="text-sm text-slate-600 dark:text-dark-300">{{ $branch->storage_locations_count }}</td>
                    <td>
                        @if($branch->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('branches.show', $branch) }}" class="btn-icon text-slate-500 hover:text-sky-600" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('branches.edit', $branch) }}" class="btn-icon text-slate-500 hover:text-amber-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Delete branch {{ $branch->name }}?')">
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
                    <td colspan="7" class="text-center py-12 text-slate-400 dark:text-dark-500">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        No branches found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-dark-800">
            {{ $branches->links() }}
        </div>
    </div>

</div>
@endsection
