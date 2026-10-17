@extends('layouts.app')

@section('title', 'System & Logo Settings')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-dark-800">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 rounded-xl border border-cyan-100 dark:border-cyan-900/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">System & Branding Settings</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Manage application logo, company branding, and system preferences</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Authorized as {{ auth()->user()->roles->pluck('name')->implode(', ') }}
            </span>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-3 text-emerald-800 dark:text-emerald-200 text-sm">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 flex flex-col gap-1 text-rose-800 dark:text-rose-200 text-sm">
            <div class="flex items-center gap-2 font-semibold">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 pl-6">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Settings Form --}}
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Official Logo Management --}}
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200 dark:border-dark-800 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-dark-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Official Application Logo
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Upload or replace the brand logo displayed on the dashboard sidebar, topbar, client portal, and print invoices.
                    </p>
                </div>
            </div>

            <div class="p-5 sm:p-6 space-y-6">
                {{-- Logo Previews in Light & Dark Mode --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Preview on Light Background --}}
                    <div class="rounded-xl p-4 bg-slate-50 dark:bg-dark-800/60 border border-slate-200 dark:border-dark-700/80 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wider">Preview on Light Background</span>
                        <div class="w-full max-w-xs h-28 bg-white border border-slate-200 rounded-lg flex items-center justify-center p-3 shadow-xs">
                            <img id="logoPreviewLight" src="{{ $settings['logo_url'] }}" alt="Logo Light Preview" class="max-h-20 max-w-full object-contain" />
                        </div>
                        <span class="text-[11px] text-slate-400 mt-2">Sidebar & Light Mode appearance</span>
                    </div>

                    {{-- Preview on Dark Background --}}
                    <div class="rounded-xl p-4 bg-slate-900 border border-slate-800 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wider">Preview on Dark Background</span>
                        <div class="w-full max-w-xs h-28 bg-dark-900 border border-dark-700 rounded-lg flex items-center justify-center p-3 shadow-xs">
                            <img id="logoPreviewDark" src="{{ $settings['logo_url'] }}" alt="Logo Dark Preview" class="max-h-20 max-w-full object-contain bg-white/90 p-1 rounded" />
                        </div>
                        <span class="text-[11px] text-slate-400 mt-2">Dark Theme appearance</span>
                    </div>
                </div>

                {{-- Upload Input --}}
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Choose New Logo Image
                    </label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input type="file" id="logoInput" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                               class="block w-full text-sm text-slate-500 dark:text-slate-400
                                      file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                                      file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700
                                      dark:file:bg-cyan-950/60 dark:file:text-cyan-400
                                      hover:file:bg-cyan-100 dark:hover:file:bg-cyan-900/60
                                      cursor-pointer border border-slate-300 dark:border-dark-700 rounded-xl bg-white dark:bg-dark-800 p-1.5 focus:outline-hidden" />
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Recommended: High-resolution PNG or JPG image with clear branding. Max file size: 4MB.
                    </p>
                </div>
            </div>
        </div>

        {{-- Section 2: General Company & System Settings --}}
        <div class="bg-white dark:bg-dark-900 rounded-2xl border border-slate-200 dark:border-dark-800 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-dark-800">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    General Brand & Company Info
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Configure the organization details used in page titles, invoices, and reports.
                </p>
            </div>

            <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Site / Company Name --}}
                <div class="space-y-1.5">
                    <label for="site_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Company / Brand Name
                    </label>
                    <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400 focus:outline-hidden transition"
                           placeholder="Albareck" />
                </div>

                {{-- Tagline --}}
                <div class="space-y-1.5">
                    <label for="site_tagline" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Tagline / Subtitle
                    </label>
                    <input type="text" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400 focus:outline-hidden transition"
                           placeholder="Integrated Stock & Manufacturing Management System" />
                </div>

                {{-- Contact Email --}}
                <div class="space-y-1.5">
                    <label for="contact_email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Official Contact Email
                    </label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400 focus:outline-hidden transition"
                           placeholder="info@albareck.com" />
                </div>

                {{-- Contact Phone --}}
                <div class="space-y-1.5">
                    <label for="contact_phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Official Phone Number
                    </label>
                    <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400 focus:outline-hidden transition"
                           placeholder="+251 900 000 000" />
                </div>

                {{-- Address (Full width) --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label for="contact_address" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Headquarters / Physical Address
                    </label>
                    <input type="text" id="contact_address" name="contact_address" value="{{ old('contact_address', $settings['contact_address']) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-dark-700 bg-white dark:bg-dark-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-cyan-500 dark:focus:ring-cyan-400 focus:outline-hidden transition"
                           placeholder="Addis Ababa, Ethiopia" />
                </div>
            </div>

            {{-- Footer Action Bar --}}
            <div class="p-5 sm:p-6 bg-slate-50 dark:bg-dark-800/40 border-t border-slate-100 dark:border-dark-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Changes take effect across all dashboard layouts immediately upon saving.
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-semibold text-sm text-white bg-cyan-600 hover:bg-cyan-700 dark:bg-cyan-500 dark:hover:bg-cyan-600 shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Reset to Default Logo Form --}}
    <div class="p-5 rounded-2xl bg-slate-100 dark:bg-dark-900/60 border border-slate-200 dark:border-dark-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Reset to Default Logo</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Restore the factory Albareck oval brand logo if needed.</p>
        </div>
        <form action="{{ route('settings.reset-logo') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset the logo back to default?');">
            @csrf
            <button type="submit"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-dark-800 border border-slate-300 dark:border-dark-700 hover:bg-slate-50 dark:hover:bg-dark-700 transition cursor-pointer">
                Reset Logo to Default
            </button>
        </form>
    </div>

</div>

{{-- Interactive Logo Preview Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logoInput = document.getElementById('logoInput');
        const previewLight = document.getElementById('logoPreviewLight');
        const previewDark = document.getElementById('logoPreviewDark');

        if (logoInput) {
            logoInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        if (previewLight) previewLight.src = evt.target.result;
                        if (previewDark) previewDark.src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection
