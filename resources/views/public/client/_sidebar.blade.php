@php
    $sidebarLinks = [
        ['route' => 'client.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
        ['route' => 'client.orders',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'My Orders'],
        ['route' => 'client.reports',   'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Spending Reports'],
        ['route' => 'client.reviews',   'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'label' => 'My Reviews'],
        ['route' => 'client.profile',   'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => 'Profile'],
    ];
@endphp

<aside style="width:260px;flex-shrink:0;">
    <div style="background:var(--bg-card);border-radius:18px;border:1px solid var(--border);overflow:hidden;position:sticky;top:100px;box-shadow:0 10px 30px rgba(0,0,0,0.06);">

        {{-- User info header --}}
        <div style="padding:1.5rem;border-bottom:1px solid var(--border);background:linear-gradient(180deg, var(--bg-card2), var(--bg-card));">
            <div style="display:flex;align-items:center;gap:.9rem;margin-bottom:.85rem;">
                <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--cyan),#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:1.2rem;box-shadow:0 4px 14px var(--cyan-glow);border:2px solid rgba(255,255,255,0.15);flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="min-width:0;flex:1;">
                    <div style="font-weight:800;color:var(--text-1);font-size:.95rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:'Outfit',sans-serif;">{{ auth()->user()->name }}</div>
                    <span style="display:inline-block;padding:.15rem .55rem;background:rgba(8,145,178,.12);border:1px solid rgba(8,145,178,.3);border-radius:999px;font-size:.68rem;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.05em;margin-top:.2rem;">
                        Verified Customer
                    </span>
                </div>
            </div>
            <div style="font-size:.78rem;color:var(--text-3);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->email }}</div>
            @if(auth()->user()->phone)
            <div style="font-size:.78rem;color:var(--text-3);margin-top:.2rem;display:flex;align-items:center;gap:.35rem;">
                <svg style="width:13px;height:13px;color:var(--text-3);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span>{{ auth()->user()->phone }}</span>
            </div>
            @endif
        </div>

        {{-- Nav links --}}
        <nav style="display:flex;flex-direction:column;padding:.6rem 0;" aria-label="Buyer Navigation">
            @foreach($sidebarLinks as $link)
            @php $isActive = isset($active) && $active === \Illuminate\Support\Str::after($link['route'], 'client.'); @endphp
            <a href="{{ route($link['route']) }}"
               style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1.4rem;font-size:.875rem;color:{{ $isActive ? 'var(--cyan)' : 'var(--text-2)' }};font-weight:{{ $isActive ? '700' : '500' }};border-left:3px solid {{ $isActive ? 'var(--cyan)' : 'transparent' }};background:{{ $isActive ? 'rgba(8,145,178,.08)' : 'transparent' }};transition:all .18s;text-decoration:none;">
                <svg style="width:18px;height:18px;flex-shrink:0;color:{{ $isActive ? 'var(--cyan)' : 'inherit' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
                <span>{{ $link['label'] }}</span>
            </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div style="padding:.5rem 0;border-top:1px solid var(--border);">
            <form action="{{ route('client.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit"
                    style="width:100%;display:flex;align-items:center;gap:.75rem;padding:.75rem 1.4rem;font-size:.875rem;color:var(--text-2);background:none;border:none;cursor:pointer;text-align:left;font-weight:500;transition:all .18s;"
                    onmouseover="this.style.color='#ef4444';this.style.background='rgba(239,68,68,0.06)'"
                    onmouseout="this.style.color='var(--text-2)';this.style.background='transparent'">
                    <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</aside>
