@extends('layouts.app')

@section('title', 'My Profile & Security Settings')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ old('_tab', $activeTab ?? 'profile') }}',
    showCurrent: false,
    showNew: false,
    showConfirm: false,
    newPass: '',
    confirmPass: '',
    get passScore() {
        if (!this.newPass) return 0;
        let score = 0;
        if (this.newPass.length >= 8) score += 25;
        if (this.newPass.length >= 12) score += 25;
        if (/[A-Z]/.test(this.newPass) && /[a-z]/.test(this.newPass)) score += 25;
        if (/[0-9]/.test(this.newPass) || /[^A-Za-z0-9]/.test(this.newPass)) score += 25;
        return score;
    },
    get scoreLabel() {
        if (this.passScore === 0) return 'Enter a password';
        if (this.passScore <= 25) return 'Weak (too short)';
        if (this.passScore <= 50) return 'Fair (needs variety)';
        if (this.passScore <= 75) return 'Good';
        return 'Strong & Secure';
    },
    get scoreColor() {
        if (this.passScore <= 25) return 'bg-rose-500';
        if (this.passScore <= 50) return 'bg-amber-500';
        if (this.passScore <= 75) return 'bg-sky-500';
        return 'bg-emerald-500';
    }
}">

    {{-- Executive Profile Hero Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-cyan-950 text-white shadow-xl border border-slate-800">
        {{-- Background Geometric Patterns --}}
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-cyan-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-20 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                {{-- Avatar & Identity --}}
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-sky-400 p-1 shadow-lg ring-4 ring-white/10 flex-shrink-0">
                            <div class="w-full h-full rounded-xl bg-slate-900/90 backdrop-blur-xs flex items-center justify-center text-white font-extrabold text-2xl sm:text-3xl tracking-tight">
                                {{ strtoupper(substr($user->name ?? 'SA', 0, 2)) }}
                            </div>
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-slate-900 shadow-sm flex items-center justify-center" title="Account Active">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight truncate">{{ $user->name }}</h1>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Verified Account
                            </span>
                        </div>
                        <p class="text-slate-300 text-sm mt-0.5 flex items-center gap-2 truncate">
                            <span>{{ $user->email }}</span>
                            @if($user->phone)
                                <span class="text-slate-500">•</span>
                                <span class="text-slate-300">{{ $user->phone }}</span>
                            @endif
                        </p>
                        <div class="mt-3 flex items-center gap-2 flex-wrap text-xs">
                            @foreach($user->roles as $role)
                                <span class="px-3 py-1 rounded-lg bg-white/10 backdrop-blur-sm text-cyan-200 font-semibold border border-white/10">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                            @if($user->branches->isNotEmpty())
                                <span class="px-3 py-1 rounded-lg bg-white/5 backdrop-blur-sm text-slate-300 font-medium border border-white/10 flex items-center gap-1.5" title="{{ $user->branches->pluck('name')->join(', ') }}">
                                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    {{ $user->branches->pluck('name')->join(', ') }}
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-lg bg-white/5 backdrop-blur-sm text-slate-300 font-medium border border-white/10">
                                    Global Scope
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Quick Tab Trigger Buttons inside Hero --}}
                <div class="flex items-center gap-2 self-start md:self-center bg-black/30 backdrop-blur-md p-1.5 rounded-2xl border border-white/10">
                    <button type="button"
                            @click="activeTab = 'profile'"
                            :class="activeTab === 'profile' ? 'bg-cyan-500 text-white shadow-md font-bold' : 'text-slate-300 hover:text-white font-medium'"
                            class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Profile Information</span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'password'"
                            :class="activeTab === 'password' ? 'bg-indigo-600 text-white shadow-md font-bold' : 'text-slate-300 hover:text-white font-medium'"
                            class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Change Password</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-3 text-emerald-800 dark:text-emerald-200 text-sm shadow-xs animate-fade-in">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <span class="font-bold">Success:</span>
                <span class="ml-1">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 flex flex-col gap-2 text-rose-800 dark:text-rose-200 text-sm shadow-xs animate-fade-in">
            <div class="flex items-center gap-2 font-bold">
                <div class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <span>Please review the form errors:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-9 text-rose-700 dark:text-rose-300">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Left Sidebar Column: Overview, Security Health & Scope --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Navigation Cards Switcher --}}
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-2 shadow-xs space-y-1.5">
                <button type="button"
                        @click="activeTab = 'profile'"
                        class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-xs transition duration-150 text-left group"
                        :class="activeTab === 'profile'
                            ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 font-bold border border-cyan-200/80 dark:border-cyan-800 shadow-2xs'
                            : 'text-slate-600 dark:text-dark-300 hover:bg-slate-50 dark:hover:bg-dark-800 hover:text-slate-900 dark:hover:text-white border border-transparent font-medium'">
                    <div class="p-2 rounded-xl transition"
                         :class="activeTab === 'profile' ? 'bg-cyan-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-dark-800 text-slate-500 group-hover:text-cyan-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="block truncate leading-tight">Profile Information</span>
                        <span class="block text-[11px] font-normal text-slate-500 dark:text-dark-400 truncate">Identity, email & contact number</span>
                    </div>
                    <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400 transition" :class="activeTab === 'profile' ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-1'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <button type="button"
                        @click="activeTab = 'password'"
                        class="w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-xs transition duration-150 text-left group"
                        :class="activeTab === 'password'
                            ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 font-bold border border-indigo-200/80 dark:border-indigo-800 shadow-2xs'
                            : 'text-slate-600 dark:text-dark-300 hover:bg-slate-50 dark:hover:bg-dark-800 hover:text-slate-900 dark:hover:text-white border border-transparent font-medium'">
                    <div class="p-2 rounded-xl transition"
                         :class="activeTab === 'password' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-dark-800 text-slate-500 group-hover:text-indigo-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="block truncate leading-tight">Security Settings</span>
                        <span class="block text-[11px] font-normal text-slate-500 dark:text-dark-400 truncate">Change Password & credentials</span>
                    </div>
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 transition" :class="activeTab === 'password' ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-1'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Account Security Status Box --}}
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-dark-800">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Security Health</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                        Protected
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <span class="font-semibold text-slate-800 dark:text-dark-100 block">Password Authentication</span>
                            <span class="text-[11px] text-slate-500 dark:text-dark-400">Encrypted with Bcrypt hash</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <span class="font-semibold text-slate-800 dark:text-dark-100 block">Role-Based Access Control</span>
                            <span class="text-[11px] text-slate-500 dark:text-dark-400">{{ $user->roles->first()?->name ?? 'Staff' }} authorization active</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <span class="font-semibold text-slate-800 dark:text-dark-100 block">System Audit Trail</span>
                            <span class="text-[11px] text-slate-500 dark:text-dark-400">All inventory and auth actions logged</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Metadata Info --}}
            <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200/80 dark:border-dark-800 p-5 shadow-xs space-y-3 text-xs">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-dark-800">Account Details</h3>

                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-dark-300">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        System User ID
                    </span>
                    <span class="font-bold text-slate-800 dark:text-white font-mono bg-slate-100 dark:bg-dark-800 px-2 py-0.5 rounded">#{{ $user->id }}</span>
                </div>

                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-dark-300">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Registered On
                    </span>
                    <span class="font-semibold text-slate-800 dark:text-white">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between py-1 text-slate-600 dark:text-dark-300">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Last Modified
                    </span>
                    <span class="font-semibold text-slate-800 dark:text-white">{{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Recently' }}</span>
                </div>
            </div>

        </div>

        {{-- Right Main Content Area --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- ═════════════════════════════════════════════════════════════ --}}
            {{-- TAB 1: Profile Information                                    --}}
            {{-- ═════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'profile'"
                 x-transition:enter="transition duration-150 ease-out"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white dark:bg-dark-900 rounded-3xl border border-slate-200/80 dark:border-dark-800 overflow-hidden shadow-xs">

                {{-- Card Header --}}
                <div class="p-6 border-b border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 border border-cyan-200/60 dark:border-cyan-800/60">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Profile Information</h2>
                            <p class="text-xs text-slate-500 dark:text-dark-400">Keep your personal contact details and identifier up to date</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-cyan-50 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 self-start sm:self-auto">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-pulse"></span>
                        Active Account
                    </span>
                </div>

                {{-- Form Body --}}
                <form action="{{ route('profile.update') }}" method="POST" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="profile">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Full Name --}}
                        <div class="space-y-1.5">
                            <label for="name" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                                <span>Full Name <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] font-normal text-slate-400">Displayed across IMS</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       required
                                       class="form-input w-full text-xs pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-800/80 focus:bg-white dark:focus:bg-dark-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition"
                                       placeholder="Enter your full name">
                            </div>
                        </div>

                        {{-- Email Address --}}
                        <div class="space-y-1.5">
                            <label for="email" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                                <span>Email Address <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] font-normal text-emerald-600 dark:text-emerald-400">Primary login email</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       required
                                       class="form-input w-full text-xs pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-800/80 focus:bg-white dark:focus:bg-dark-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition"
                                       placeholder="you@company.com">
                            </div>
                        </div>

                        {{-- Phone Number --}}
                        <div class="space-y-1.5">
                            <label for="phone" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                                <span>Phone Number</span>
                                <span class="text-[11px] font-normal text-slate-400">Ethiopian mobile (+251...)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </span>
                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="+251912345678"
                                       pattern="^\+251[0-9]{9}$"
                                       title="Must start with +251 followed by 9 digits (total 12 digits, e.g. +251912345678)"
                                       class="form-input w-full text-xs pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-800/80 focus:bg-white dark:focus:bg-dark-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Format: +251 followed by 9 digits (e.g. +251911223344)</p>
                        </div>

                        {{-- Role (System Controlled) --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                                <span>Security Role</span>
                                <span class="text-[11px] font-normal text-slate-400">Managed by Administrator</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       value="{{ $user->roles->pluck('name')->join(', ') ?: 'Staff' }}"
                                       readonly
                                       disabled
                                       class="form-input w-full text-xs pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-100/80 dark:bg-dark-800/40 text-slate-600 dark:text-dark-400 cursor-not-allowed">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Contact a Super Admin to request role privilege modifications.</p>
                        </div>

                    </div>

                    {{-- Workspace / Branch Info Panel --}}
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-dark-800/60 border border-slate-200/80 dark:border-dark-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-white dark:bg-dark-900 text-slate-600 dark:text-dark-300 border border-slate-200/80 dark:border-dark-700 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-white block">Assigned Work Branch</span>
                                <span class="text-[11px] text-slate-500 dark:text-dark-400">
                                    {{ $user->branches->pluck('name')->join(', ') ?: 'All Branches (Global Administrator)' }}
                                </span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-dark-900 text-slate-700 dark:text-dark-200 border border-slate-200 dark:border-dark-700 self-start sm:self-auto shadow-2xs">
                            {{ $user->branches->count() }} {{ \Illuminate\Support\Str::plural('Branch', $user->branches->count()) }} Assigned
                        </span>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-5 border-t border-slate-100 dark:border-dark-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400 hidden sm:inline">Changes take effect immediately across all sessions</span>
                        <button type="submit"
                                class="btn-primary text-xs py-2.5 px-6 flex items-center gap-2 rounded-xl shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-bold">Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ═════════════════════════════════════════════════════════════ --}}
            {{-- TAB 2: Security Settings / Change Password                    --}}
            {{-- ═════════════════════════════════════════════════════════════ --}}
            <div x-show="activeTab === 'password'"
                 x-transition:enter="transition duration-150 ease-out"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-white dark:bg-dark-900 rounded-3xl border border-slate-200/80 dark:border-dark-800 overflow-hidden shadow-xs"
                 style="display: none;">

                {{-- Card Header --}}
                <div class="p-6 border-b border-slate-100 dark:border-dark-800 bg-slate-50/50 dark:bg-dark-900/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Security Settings</h2>
                            <p class="text-xs text-slate-500 dark:text-dark-400">Change your password and manage account security credentials</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 self-start sm:self-auto">
                        End-to-End Encrypted
                    </span>
                </div>

                {{-- Security Recommendation Banner --}}
                <div class="p-6 sm:p-8 pb-0">
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-50/80 via-slate-50 to-cyan-50/60 dark:from-indigo-950/30 dark:via-dark-800/40 dark:to-cyan-950/30 border border-indigo-100 dark:border-indigo-900/50 flex items-start gap-3.5">
                        <div class="p-2 rounded-xl bg-indigo-600 text-white shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="text-xs space-y-1">
                            <span class="font-bold text-slate-900 dark:text-white block">Password Security Standards</span>
                            <p class="text-slate-600 dark:text-dark-300">
                                Use at least 8 characters with a blend of letters, numbers, and symbols. Never share your credentials or reuse passwords from external platforms.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Form Body --}}
                <form action="{{ route('profile.password.update') }}" method="POST" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="password">

                    {{-- Current Password --}}
                    <div class="space-y-1.5">
                        <label for="current_password" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                            <span>Current Password <span class="text-rose-500">*</span></span>
                            <span class="text-[11px] font-normal text-slate-400">Required to verify identity</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input :type="showCurrent ? 'text' : 'password'"
                                   id="current_password"
                                   name="current_password"
                                   required
                                   autocomplete="current-password"
                                   class="form-input w-full text-xs pl-10 pr-10 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-800/80 focus:bg-white dark:focus:bg-dark-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                   placeholder="Enter your current password">
                            <button type="button"
                                    @click="showCurrent = !showCurrent"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-dark-200 transition focus:outline-none"
                                    aria-label="Toggle password visibility">
                                <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- New Password & Confirm Password Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- New Password --}}
                        <div class="space-y-1.5">
                            <label for="password" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                                <span>New Password <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] font-normal text-slate-400">Min 8 characters</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </span>
                                <input :type="showNew ? 'text' : 'password'"
                                       id="password"
                                       name="password"
                                       x-model="newPass"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="form-input w-full text-xs pl-10 pr-10 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-800/80 focus:bg-white dark:focus:bg-dark-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                       placeholder="Enter at least 8 characters">
                                <button type="button"
                                        @click="showNew = !showNew"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-dark-200 transition focus:outline-none"
                                        aria-label="Toggle password visibility">
                                    <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Live Strength Progress Bar --}}
                            <div class="space-y-1 pt-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500 font-medium">Strength Meter</span>
                                    <span class="font-bold" :class="scoreColor.replace('bg-', 'text-')" x-text="scoreLabel"></span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-dark-800 overflow-hidden">
                                    <div class="h-full transition-all duration-300"
                                         :class="scoreColor"
                                         :style="`width: ${passScore}%`"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Confirm New Password --}}
                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-dark-200">
                                <span>Confirm New Password <span class="text-rose-500">*</span></span>
                                <span class="text-[11px] font-normal text-slate-400">Must match above</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </span>
                                <input :type="showConfirm ? 'text' : 'password'"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       x-model="confirmPass"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="form-input w-full text-xs pl-10 pr-10 py-2.5 rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50/60 dark:bg-dark-800/80 focus:bg-white dark:focus:bg-dark-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                       placeholder="Re-type your new password">
                                <button type="button"
                                       @click="showConfirm = !showConfirm"
                                       class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-dark-200 transition focus:outline-none"
                                       aria-label="Toggle password visibility">
                                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Live Matching Indicator --}}
                            <div class="h-5 pt-1">
                                <template x-if="confirmPass.length > 0">
                                    <span class="text-[11px] font-semibold flex items-center gap-1.5"
                                          :class="newPass === confirmPass ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
                                        <span x-text="newPass === confirmPass ? '✓ Passwords match' : '✕ Passwords do not match'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-5 border-t border-slate-100 dark:border-dark-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400 hidden sm:inline">You will remain signed in after password update</span>
                        <button type="submit"
                                class="btn-secondary text-xs py-2.5 px-6 flex items-center gap-2 rounded-xl shadow-md hover:shadow-lg transition bg-indigo-600 hover:bg-indigo-700 text-white border-transparent">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="font-bold">Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
