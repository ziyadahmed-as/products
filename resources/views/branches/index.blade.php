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

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label mb-1">Search</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Branch name or code…" class="form-input pl-9">
                </div>
            </div>
            <div class="min-w-[180px]">
                <label class="form-label mb-1">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="headquarters" {{ request('type') === 'headquarters' ? 'selected' : '' }}>Headquarters</option>
                    <option value="branch" {{ request('type') === 'branch' ? 'selected' : '' }}>Branch</option>
                    <option value="warehouse" {{ request('type') === 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                    <option value="distribution_center" {{ request('type') === 'distribution_center' ? 'selected' : '' }}>Distribution Centre</option>
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('branches.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">All Branches</h2>
                <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">{{ $branches->total() }} branch{{ $branches->total() !== 1 ? 'es' : '' }} found</p>
            </div>
        </div>
        <div class="overflow-x-auto">
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
                    @php
                        $typeColors = ['headquarters'=>'badge-purple','branch'=>'badge-blue','warehouse'=>'badge-yellow','distribution_center'=>'badge-gray'];
                        $typeBadge = $typeColors[$branch->type] ?? 'badge-gray';
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4.5 h-4.5 text-slate-500 dark:text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm leading-tight">{{ $branch->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-dark-400 font-mono">{{ $branch->code }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $typeBadge }} capitalize">{{ str_replace('_', ' ', $branch->type) }}</span>
                        </td>
                        <td class="text-sm text-slate-600 dark:text-dark-300">{{ $branch->parent?->name ?? '—' }}</td>
                        <td>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-dark-200">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $branch->users_count }}
                            </span>
                        </td>
                        <td>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-dark-200">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $branch->storage_locations_count }}
                            </span>
                        </td>
                        <td>
                            @if($branch->is_active)
                                <span class="badge badge-green flex items-center gap-1 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                                </span>
                            @else
                                <span class="badge badge-red flex items-center gap-1 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('branches.show', $branch) }}" class="btn-icon hover:text-sky-600 hover:border-sky-200 hover:bg-sky-50 dark:hover:bg-sky-950/40 dark:hover:text-sky-400" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('branches.edit', $branch) }}" class="btn-icon hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 dark:hover:text-amber-400" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Delete {{ addslashes($branch->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700 dark:text-dark-300">No branches found</p>
                                    <p class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">Add your first branch to start managing inventory.</p>
                                </div>
                                <a href="{{ route('branches.create') }}" class="btn-primary text-sm mt-1">Add Branch</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($branches->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/40">
            {{ $branches->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
