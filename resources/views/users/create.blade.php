@extends('layouts.app')
@section('title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="page-header">
        <div>
            <h1>Create User</h1>
            <p class="text-slate-500 dark:text-dark-400 text-sm mt-1">Add a new system user and assign their role.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form method="POST" action="{{ route('users.store') }}" class="card p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="form-label">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-rose-400 @enderror" required>
                @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-rose-400 @enderror" required>
                @error('email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Role <span class="text-rose-500">*</span></label>
                <select name="role" class="form-input @error('role') border-rose-400 @enderror" required>
                    <option value="">Select role…</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" class="form-input @error('password') border-rose-400 @enderror" required>
                @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Confirm Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
        </div>

        <!-- Branch Access -->
        @if($branches->count() > 0)
        <div>
            <label class="form-label">Branch Access</label>
            <p class="text-xs text-slate-500 dark:text-dark-400 mb-2">Select which branches this user can access. Leave empty for global access (Super Admin).</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($branches as $branch)
                <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-dark-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-dark-800">
                    <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                           {{ in_array($branch->id, old('branches', [])) ? 'checked' : '' }}
                           class="rounded text-cyan-600">
                    <span class="text-sm text-slate-700 dark:text-dark-200">{{ $branch->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Status -->
        <div class="flex items-center gap-3 pt-2">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cyan-300 dark:peer-focus:ring-cyan-800 rounded-full peer dark:bg-dark-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
            </label>
            <span class="text-sm font-medium text-slate-700 dark:text-dark-200">Account Active</span>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-dark-800">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Create User
            </button>
            <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

</div>
@endsection
