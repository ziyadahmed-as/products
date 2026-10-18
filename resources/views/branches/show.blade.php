@extends('layouts.app')
@section('title', 'Branch Details — ' . $branch->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="flex items-center gap-3">
                <h1>{{ $branch->name }}</h1>
                @if($branch->is_active)
                    <span class="badge badge-green">Active</span>
                @else
                    <span class="badge badge-red">Inactive</span>
                @endif
                <span class="badge badge-blue capitalize">{{ str_replace('_', ' ', $branch->type) }}</span>
            </div>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Branch Code: <span class="font-mono font-semibold">{{ $branch->code }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('branches.edit', $branch) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Branch
            </a>
            <a href="{{ route('branches.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Branches
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Parent Branch</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $branch->parent?->name ?? 'None (Top Level)' }}</p>
            @if($branch->parent)
                <p class="text-xs text-slate-400 mt-1">Code: {{ $branch->parent->code }}</p>
            @endif
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Assigned Staff</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $branch->users->count() }} Users</p>
            <p class="text-xs text-slate-400 mt-1">Active team members</p>
        </div>
        <div class="stat-card">
            <p class="text-xs uppercase font-semibold text-slate-500 dark:text-dark-400">Storage Locations</p>
            <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $branch->storageLocations->count() }} Zones</p>
            <p class="text-xs text-slate-400 mt-1">Designated inventory areas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Storage Locations -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-dark-800">
                <h3 class="font-semibold text-slate-900 dark:text-white">Storage Locations</h3>
                <span class="badge badge-blue">{{ $branch->storageLocations->count() }}</span>
            </div>
            @if($branch->storageLocations->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">No storage locations assigned to this branch yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($branch->storageLocations as $loc)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-dark-800/60 border border-slate-100 dark:border-dark-800">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $loc->name }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ $loc->code }}</p>
                            </div>
                            @if($loc->is_active)
                                <span class="badge badge-green text-xs">Active</span>
                            @else
                                <span class="badge badge-red text-xs">Inactive</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Assigned Users -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-dark-800">
                <h3 class="font-semibold text-slate-900 dark:text-white">Assigned Personnel</h3>
                <span class="badge badge-blue">{{ $branch->users->count() }}</span>
            </div>
            @if($branch->users->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">No users currently assigned to this branch.</p>
            @else
                <div class="space-y-2">
                    @foreach($branch->users as $u)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-dark-800/60 border border-slate-100 dark:border-dark-800">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400">{{ $u->email }}</p>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-blue text-xs">{{ $u->roles->pluck('name')->implode(', ') ?: 'Staff' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Sub-Branches / Child Branches -->
    @if($branch->children->isNotEmpty())
    <div class="card p-5">
        <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Sub-Branches & Facilities</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($branch->children as $child)
                <a href="{{ route('branches.show', $child) }}" class="p-3 rounded-lg border border-slate-100 dark:border-dark-800 hover:border-cyan-500 transition-colors block">
                    <p class="font-medium text-slate-900 dark:text-white text-sm">{{ $child->name }}</p>
                    <p class="text-xs text-slate-400">{{ $child->code }} &bull; {{ ucfirst(str_replace('_', ' ', $child->type)) }}</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
