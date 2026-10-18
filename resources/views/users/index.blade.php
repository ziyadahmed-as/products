@extends('layouts.app')
@section('title', 'Users & Roles')

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Users &amp; Roles</h1>
            <p class="text-sm text-slate-500 dark:text-dark-400 mt-1">Manage system accounts, assign roles, and control branch access.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </a>
    </div>

    {{-- ── Summary KPI Cards ── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-sky-50 dark:bg-sky-900/30 border border-sky-100 dark:border-sky-800/50 flex-shrink-0">
                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="stat-value dark:!text-white">{{ $summary['total'] }}</p>
                <p class="stat-label">Total Users</p>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800/50 flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="stat-value dark:!text-white">{{ $summary['active'] }}</p>
                <p class="stat-label">Active</p>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-rose-50 dark:bg-rose-900/30 border border-rose-100 dark:border-rose-800/50 flex-shrink-0">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <p class="stat-value dark:!text-white">{{ $summary['inactive'] }}</p>
                <p class="stat-label">Inactive</p>
            </div>
        </div>
        <div class="stat-card flex items-center gap-4">
            <div class="stat-icon bg-purple-50 dark:bg-purple-900/30 border border-purple-100 dark:border-purple-800/50 flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
            <div>
                <p class="stat-value dark:!text-white">{{ $summary['admins'] }}</p>
                <p class="stat-label">Admins</p>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label mb-1">Search</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…" class="form-input pl-9">
                </div>
            </div>
            <div class="min-w-[160px]">
                <label class="form-label mb-1">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
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
                <a href="{{ route('users.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    {{-- ── User Table ── --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-dark-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">All Users</h2>
                <p class="text-xs text-slate-500 dark:text-dark-400 mt-0.5">{{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }} found</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Branches</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $roleColors = ['Super Admin'=>'bg-purple-600','Manager'=>'bg-sky-600','Seller'=>'bg-emerald-600','Production Officer'=>'bg-amber-600','Auditor'=>'bg-rose-600','Client'=>'bg-slate-600'];
                        $roleClass = $roleColors[$user->roles->first()?->name ?? ''] ?? 'bg-slate-700';
                        $badgeColors = ['Super Admin'=>'badge-purple','Manager'=>'badge-blue','Seller'=>'badge-green','Production Officer'=>'badge-yellow','Auditor'=>'badge-red','Client'=>'badge-gray'];
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full {{ $roleClass }} text-white font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-xs">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white text-sm leading-tight">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-dark-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="badge {{ $badgeColors[$role->name] ?? 'badge-gray' }}">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400 dark:text-dark-500 italic">No role</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            @if($user->branches->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->branches->take(2) as $branch)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 dark:text-dark-300 bg-slate-100 dark:bg-dark-800 px-2 py-0.5 rounded-md border border-slate-200 dark:border-dark-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 flex-shrink-0"></span>
                                            {{ $branch->name }}
                                        </span>
                                    @endforeach
                                    @if($user->branches->count() > 2)
                                        <span class="text-xs text-slate-400 dark:text-dark-500 self-center">+{{ $user->branches->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 dark:text-dark-500 italic">Global Access</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-green flex items-center gap-1 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                                </span>
                            @else
                                <span class="badge badge-red flex items-center gap-1 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-sm text-slate-500 dark:text-dark-400">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('users.show', $user) }}" class="btn-icon hover:text-sky-600 hover:border-sky-200 hover:bg-sky-50 dark:hover:bg-sky-950/40 dark:hover:text-sky-400" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="btn-icon hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 dark:hover:text-amber-400" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-800 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-slate-400 dark:text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-700 dark:text-dark-300">No users found</p>
                                    <p class="text-xs text-slate-400 dark:text-dark-500 mt-0.5">Try adjusting your filters or add a new user.</p>
                                </div>
                                <a href="{{ route('users.create') }}" class="btn-primary text-sm mt-1">Add First User</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/40">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

