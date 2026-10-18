@extends('layouts.app')
@section('title', $user->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>{{ $user->name }}</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">User profile &amp; access details.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn-primary">Edit</a>
            <a href="{{ route('users.index') }}" class="btn-secondary">← Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Profile Card -->
        <div class="card p-6 space-y-4">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-20 h-20 rounded-full bg-slate-900 dark:bg-dark-700 text-white font-extrabold text-2xl flex items-center justify-center">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-lg">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-dark-400">{{ $user->email }}</p>
                    @if($user->phone)
                        <p class="text-sm text-slate-500 dark:text-dark-400">{{ $user->phone }}</p>
                    @endif
                </div>
                @if($user->is_active)
                    <span class="badge badge-green">Active Account</span>
                @else
                    <span class="badge badge-red">Inactive Account</span>
                @endif
            </div>
            <hr class="border-slate-100 dark:border-dark-800">
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-dark-400">Joined</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 dark:text-dark-400">Last Updated</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <!-- Roles & Branches -->
        <div class="md:col-span-2 space-y-5">
            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Assigned Role</h3>
                @forelse($user->roles as $role)
                    <span class="badge badge-blue text-sm">{{ $role->name }}</span>
                @empty
                    <p class="text-slate-400 dark:text-dark-500 text-sm">No role assigned.</p>
                @endforelse
            </div>

            <div class="card p-5">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Branch Access</h3>
                @forelse($user->branches as $branch)
                    <div class="flex items-center gap-2 py-1.5 border-b border-slate-50 dark:border-dark-800 last:border-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-sm text-slate-700 dark:text-dark-200">{{ $branch->name }}</span>
                        <span class="text-xs text-slate-400">{{ $branch->code }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 dark:text-dark-500 text-sm">Global access (all branches).</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
