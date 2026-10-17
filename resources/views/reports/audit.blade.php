@extends('layouts.app')
@section('title', 'User Activity & Audit Reports')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">User Activity & Audit Reports</h1>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">
                Real-time security auditing, administrative oversight, and role-based transaction tracking.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Print</span>
            </button>
            <a href="{{ route('reports.activity.export', request()->query()) }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Total Activities</span>
                <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/80 border border-primary-200 dark:border-primary-800/50 flex items-center justify-center text-primary-600 dark:text-primary-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($totalActivities) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Matching current filters</div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Successful Events</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-3">{{ number_format($successfulCount) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Verified standard operations</div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Failed / Security Alerts</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800/50 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-3">{{ number_format($failedCount) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Failed logins or violations</div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-dark-400">Warnings / Anomalies</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/80 border border-amber-200 dark:border-amber-800/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-3">{{ number_format($warningCount) }}</div>
            <div class="text-xs text-gray-500 dark:text-dark-400 mt-1">Noticeable state flags</div>
        </div>
    </div>

    <!-- Multi-Dimensional Filters -->
    <div class="card p-5 print:hidden">
        <form method="GET" action="{{ route('reports.activity') }}" class="space-y-4">
            
            {{-- Quick Date Range Presets --}}
            <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-gray-100 dark:border-dark-800 text-sm">
                <span class="text-gray-500 dark:text-dark-400 text-xs font-semibold uppercase tracking-wider mr-2">Quick Presets:</span>
                @php $curPeriod = request('period'); @endphp
                <a href="{{ route('reports.activity', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'today'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curPeriod === 'today' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   Today
                </a>
                <a href="{{ route('reports.activity', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_week'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curPeriod === 'this_week' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Week
                </a>
                <a href="{{ route('reports.activity', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_month'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curPeriod === 'this_month' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Month
                </a>
                <a href="{{ route('reports.activity', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => 'this_year'])) }}"
                   class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $curPeriod === 'this_year' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }}">
                   This Year
                </a>
                @if($curPeriod || request('from') || request('to'))
                <a href="{{ route('reports.activity', request()->except(['period', 'from', 'to', 'page'])) }}"
                   class="text-xs text-rose-500 hover:underline ml-2">
                   Clear Date
                </a>
                @endif
            </div>

            {{-- Filter Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {{-- Custom Date From --}}
                <div class="form-group">
                    <label class="form-label text-xs">From Date</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-input text-xs">
                </div>

                {{-- Custom Date To --}}
                <div class="form-group">
                    <label class="form-label text-xs">To Date</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-input text-xs">
                </div>

                {{-- User --}}
                <div class="form-group">
                    <label class="form-label text-xs">User</label>
                    <select name="user_id" class="form-input text-xs">
                        <option value="">All Users</option>
                        @foreach($filterUsers as $u)
                            <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Role --}}
                <div class="form-group">
                    <label class="form-label text-xs">Role</label>
                    <select name="role" class="form-input text-xs">
                        <option value="">All Roles</option>
                        @foreach($filterRoles as $r)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
                                {{ $r }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch --}}
                <div class="form-group">
                    <label class="form-label text-xs">Branch</label>
                    <select name="branch_id" class="form-input text-xs">
                        <option value="">All Branches</option>
                        @foreach($filterBranches as $bId => $bName)
                            <option value="{{ $bId }}" {{ (string)request('branch_id') === (string)$bId ? 'selected' : '' }}>
                                {{ $bName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Module --}}
                <div class="form-group">
                    <label class="form-label text-xs">Module</label>
                    <select name="module" class="form-input text-xs">
                        <option value="">All Modules</option>
                        @foreach($filterModules as $mKey => $mLabel)
                            <option value="{{ $mKey }}" {{ request('module') === $mKey ? 'selected' : '' }}>
                                {{ $mLabel }} ({{ $mKey }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Type --}}
                <div class="form-group">
                    <label class="form-label text-xs">Action Type</label>
                    <select name="action_type" class="form-input text-xs">
                        <option value="">All Actions</option>
                        @foreach($filterActionTypes as $aKey => $aLabel)
                            <option value="{{ $aKey }}" {{ request('action_type') === $aKey ? 'selected' : '' }}>
                                {{ $aLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Outcome / Status --}}
                <div class="form-group">
                    <label class="form-label text-xs">Status / Outcome</label>
                    <select name="outcome" class="form-input text-xs">
                        <option value="">All Outcomes</option>
                        <option value="success" {{ request('outcome') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('outcome') === 'failed' ? 'selected' : '' }}>Failed / Error</option>
                        <option value="warning" {{ request('outcome') === 'warning' ? 'selected' : '' }}>Warning</option>
                    </select>
                </div>
            </div>

            {{-- Text Search & Submit Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-dark-800">
                <div class="form-group flex-1 w-full">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by username, description, or transaction reference..."
                           class="form-input text-xs">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="btn-primary text-xs px-4 py-2">
                        Apply Filters
                    </button>
                    <a href="{{ route('reports.activity') }}" class="btn-secondary text-xs px-4 py-2">
                        Reset All
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Activity Records Table -->
    <div class="card p-0 overflow-hidden shadow-sm">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-dark-400 bg-gray-50/75 dark:bg-dark-950/60 border-b border-gray-200 dark:border-dark-800">
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">User & Role</th>
                        <th class="py-3 px-4">Module</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Reference</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Outcome</th>
                        <th class="py-3 px-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-800 text-sm">
                    @forelse($activities as $act)
                    @php
                        $props = is_array($act->properties) ? $act->properties : (json_decode($act->properties, true) ?? []);
                        $outcome = $props['outcome'] ?? 'success';
                        $role = $props['role'] ?? ($act->causer?->roles->first()?->name ?? 'User');
                        $branch = $props['branch_name'] ?? '—';
                        $reference = $props['reference'] ?? '—';
                        $actionName = $act->event ?? ($props['action_type'] ?? $act->description);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-800/40 transition">
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-300 font-mono whitespace-nowrap">
                            {{ $act->created_at ? $act->created_at->format('d M Y, H:i:s') : '—' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $act->causer->name ?? ($props['username'] ?? 'System') }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-dark-300 border border-gray-200 dark:border-dark-700">
                                    {{ $role }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800/60">
                                {{ $act->log_name ?? 'System' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if(in_array($actionName, ['login', 'create', 'complete', 'approve', 'register']))
                                <span class="badge-green text-xs">{{ ucfirst(str_replace('_', ' ', $actionName)) }}</span>
                            @elseif(in_array($actionName, ['failed_login', 'delete', 'cancel']))
                                <span class="badge-red text-xs">{{ ucfirst(str_replace('_', ' ', $actionName)) }}</span>
                            @elseif(in_array($actionName, ['logout', 'update', 'payment_status', 'status_update']))
                                <span class="badge-yellow text-xs">{{ ucfirst(str_replace('_', ' ', $actionName)) }}</span>
                            @else
                                <span class="badge-gray text-xs">{{ ucfirst(str_replace('_', ' ', $actionName)) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-gray-700 dark:text-dark-200 text-xs max-w-sm">
                            <div class="line-clamp-2" title="{{ $act->description }}">{{ $act->description }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs font-mono text-primary-600 dark:text-primary-400 whitespace-nowrap">
                            {{ $reference }}
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-dark-300 whitespace-nowrap">
                            {{ $branch }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($outcome === 'failed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed
                                </span>
                            @elseif($outcome === 'warning')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Warning
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Success
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <button onclick="toggleDetails({{ $act->id }})" class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-500 font-medium">
                                View Data
                            </button>
                        </td>
                    </tr>
                    {{-- Expandable Details Row --}}
                    <tr id="details-row-{{ $act->id }}" class="hidden bg-gray-50/75 dark:bg-dark-950/70 border-b border-gray-200 dark:border-dark-800">
                        <td colspan="9" class="p-4">
                            <div class="p-3 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-800 rounded-xl space-y-2 text-xs font-mono">
                                <div class="flex items-center justify-between text-gray-500 dark:text-dark-400 border-b border-gray-100 dark:border-dark-800 pb-2">
                                    <span>Subject: {{ $act->subject_type ? class_basename($act->subject_type) . ' #' . $act->subject_id : 'None' }}</span>
                                    <span>IP: {{ $props['ip'] ?? 'N/A' }} | Agent: {{ Str::limit($props['user_agent'] ?? 'N/A', 60) }}</span>
                                </div>
                                <div class="text-gray-700 dark:text-dark-300 overflow-x-auto whitespace-pre-wrap">
                                    <strong>Payload & Changes:</strong>
                                    {{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-16 text-gray-500 dark:text-dark-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-dark-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="font-medium text-gray-600 dark:text-dark-400">No activity records match the selected criteria.</span>
                                <span class="text-xs text-gray-400 dark:text-dark-600 mt-1">Try adjusting the date range or clearing specific filters.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-800 bg-gray-50/50 dark:bg-dark-900/60">
            {{ $activities->links() }}
        </div>
        @endif
    </div>

</div>

<script>
function toggleDetails(id) {
    const el = document.getElementById('details-row-' + id);
    if (el) {
        el.classList.toggle('hidden');
    }
}
</script>
@endsection
