<nav class="sidebar-nav">

    {{-- ── 1. DASHBOARD (Top Primary Item) ── --}}
    @if(auth()->user()->hasRole('Production Officer') && !auth()->user()->hasAnyRole(['Super Admin', 'Manager']))
        <a href="{{ route('manufacturing.dashboard') }}" class="sidebar-item {{ request()->routeIs('manufacturing.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>
    @else
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>
    @endif

    {{-- ── 2. ADMINISTRATION (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Auditor|Admin')
    @php
        $adminActive = request()->routeIs('users.*') || request()->routeIs('branches.*') || request()->routeIs('storage-locations.*') || request()->routeIs('reports.activity*');
    @endphp
    <div x-data="{ open: {{ $adminActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $adminActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Administration</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $adminActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            @hasanyrole('Super Admin|Admin')
            <a href="{{ route('users.index') }}" class="sidebar-subitem {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Users & Roles</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Admin')
            <a href="{{ route('branches.index') }}" class="sidebar-subitem {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Branches</span>
            </a>
            <a href="{{ route('storage-locations.index') }}" class="sidebar-subitem {{ request()->routeIs('storage-locations.*') ? 'active' : '' }}">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Storage Locations</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Auditor|Admin')
            <a href="{{ route('reports.activity') }}" class="sidebar-subitem {{ request()->routeIs('reports.activity*') ? 'active' : '' }}">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Audit Logs / User Audit Trail</span>
            </a>
            @endhasanyrole
        </div>
    </div>
    @endhasanyrole

    {{-- ── 3. CATALOG (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Production Officer|Admin')
    @php
        $catalogActive = request()->routeIs('products.*') || request()->routeIs('raw-materials.*') || request()->routeIs('manufactured-products.*') || request()->routeIs('resale-products.*') || request()->routeIs('categories.*') || request()->routeIs('units.*');
    @endphp
    <div x-data="{ open: {{ $catalogActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $catalogActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Catalog</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $catalogActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('products.index') }}" class="sidebar-subitem {{ (request()->routeIs('products.index') && !request('type')) ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('products.index') && !request('type')) ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Products & Materials</span>
            </a>

            <a href="{{ route('raw-materials.index') }}" class="sidebar-subitem {{ (request()->routeIs('raw-materials.*') || (request()->routeIs('products.*') && request('type') === 'raw_material')) ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('raw-materials.*') || (request()->routeIs('products.*') && request('type') === 'raw_material')) ? 'bg-amber-500' : 'bg-slate-400' }}"></span>
                <span>Raw Materials</span>
            </a>

            <a href="{{ route('manufactured-products.index') }}" class="sidebar-subitem {{ (request()->routeIs('manufactured-products.*') || (request()->routeIs('products.*') && request('type') === 'manufactured_product')) ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('manufactured-products.*') || (request()->routeIs('products.*') && request('type') === 'manufactured_product')) ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                <span>Manufactured Goods</span>
            </a>

            <a href="{{ route('resale-products.index') }}" class="sidebar-subitem {{ (request()->routeIs('resale-products.*') || (request()->routeIs('products.*') && request('type') === 'resale_product')) ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('resale-products.*') || (request()->routeIs('products.*') && request('type') === 'resale_product')) ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Resale Goods</span>
            </a>

            @hasanyrole('Super Admin|Manager|Admin')
            <a href="{{ route('categories.index') }}" class="sidebar-subitem {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('categories.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Categories</span>
            </a>
            <a href="{{ route('units.index') }}" class="sidebar-subitem {{ request()->routeIs('units.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('units.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Units of Measure</span>
            </a>
            @endhasanyrole
        </div>
    </div>
    @endhasanyrole

    {{-- ── 4. INVENTORY (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Production Officer|Seller|Auditor|Admin')
    @php
        $inventoryActive = request()->routeIs('inventory.*') || request()->routeIs('stock-receipts.*') || request()->routeIs('reports.inventory*') || request()->routeIs('reports.transfers*');
    @endphp
    <div x-data="{ open: {{ $inventoryActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $inventoryActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Inventory</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $inventoryActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('inventory.index') }}" class="sidebar-subitem {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inventory.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Stock Balances</span>
            </a>

            @hasanyrole('Super Admin|Manager|Production Officer|Auditor|Admin')
            <a href="{{ route('stock-receipts.index') }}" class="sidebar-subitem {{ request()->routeIs('stock-receipts.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('stock-receipts.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Stock Receipts</span>
            </a>
            <a href="{{ route('reports.transfers') }}" class="sidebar-subitem {{ request()->routeIs('reports.transfers*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.transfers*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Stock Transfers</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Auditor|Admin')
            <a href="{{ route('reports.inventory') }}" class="sidebar-subitem {{ request()->routeIs('reports.inventory*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.inventory*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Stock Valuation</span>
            </a>
            @endhasanyrole
        </div>
    </div>
    @endhasanyrole

    {{-- ── 5. MANUFACTURING (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Production Officer|Admin')
    @php
        $mfgActive = request()->routeIs('manufacturing.*') || request()->routeIs('manufacturing-orders.*') || request()->routeIs('recipes.*') || request()->routeIs('reports.production*');
    @endphp
    <div x-data="{ open: {{ $mfgActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $mfgActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Manufacturing</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $mfgActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            <a href="{{ route('manufacturing.dashboard') }}" class="sidebar-subitem {{ request()->routeIs('manufacturing.dashboard') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manufacturing.dashboard') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Production Floor</span>
            </a>
            <a href="{{ route('manufacturing-orders.index') }}" class="sidebar-subitem {{ request()->routeIs('manufacturing-orders.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manufacturing-orders.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Manufacturing Orders</span>
            </a>
            <a href="{{ route('recipes.index') }}" class="sidebar-subitem {{ request()->routeIs('recipes.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('recipes.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Recipes / BOM</span>
            </a>
            <a href="{{ route('reports.production') }}" class="sidebar-subitem {{ request()->routeIs('reports.production*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.production*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Material Consumption</span>
            </a>
        </div>
    </div>
    @endhasanyrole

    {{-- ── 6. SALES (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Seller|Auditor|Admin')
    @php
        $salesActive = request()->routeIs('sales.*') || request()->routeIs('sale-returns.*');
    @endphp
    <div x-data="{ open: {{ $salesActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $salesActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Sales</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $salesActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            @hasanyrole('Super Admin|Manager|Seller|Admin')
            <a href="{{ route('sales.create') }}" class="sidebar-subitem {{ request()->routeIs('sales.create') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sales.create') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Quick Sale (POS)</span>
            </a>
            @endhasanyrole

            <a href="{{ route('sales.index') }}" class="sidebar-subitem {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Sales Records</span>
            </a>

            @hasanyrole('Super Admin|Manager|Seller|Admin')
            <a href="{{ route('sale-returns.index') }}" class="sidebar-subitem {{ request()->routeIs('sale-returns.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('sale-returns.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Returns & Refunds</span>
            </a>
            @endhasanyrole
        </div>
    </div>
    @endhasanyrole

    {{-- ── 7. REPORTS & ANALYTICS (Collapsible Group) ── --}}
    @hasanyrole('Super Admin|Manager|Auditor|Seller|Production Officer|Admin')
    @php
        $reportsActive = request()->routeIs('reports.*') && !request()->routeIs('reports.activity*');
    @endphp
    <div x-data="{ open: {{ $reportsActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $reportsActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Reports & Analytics</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $reportsActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            @hasanyrole('Super Admin|Manager|Auditor|Admin')
            <a href="{{ route('reports.index') }}" class="sidebar-subitem {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.index') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Executive Overview</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Auditor|Seller|Admin')
            <a href="{{ route('reports.sales') }}" class="sidebar-subitem {{ request()->routeIs('reports.sales*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.sales*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Sales Reports</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Auditor|Admin')
            <a href="{{ route('reports.inventory') }}" class="sidebar-subitem {{ request()->routeIs('reports.inventory*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.inventory*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Inventory Reports</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Auditor|Production Officer|Admin')
            <a href="{{ route('reports.production') }}" class="sidebar-subitem {{ request()->routeIs('reports.production*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.production*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Manufacturing Reports</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Manager|Auditor|Admin')
            <a href="{{ route('reports.branches') }}" class="sidebar-subitem {{ request()->routeIs('reports.branches*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.branches*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Branch Performance</span>
            </a>
            <a href="{{ route('reports.transfers') }}" class="sidebar-subitem {{ request()->routeIs('reports.transfers*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.transfers*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Transfers Logistics</span>
            </a>
            @endhasanyrole

            @hasanyrole('Super Admin|Auditor|Admin')
            <a href="{{ route('reports.activity') }}" class="sidebar-subitem {{ request()->routeIs('reports.activity*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.activity*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>User Audit Trail</span>
            </a>
            @endhasanyrole
        </div>
    </div>
    @endhasanyrole

    {{-- ── 8. SETTINGS (Collapsible Group) ── --}}
    @php
        $settingsActive = request()->routeIs('client.profile') || request()->routeIs('settings.*');
    @endphp
    <div x-data="{ open: {{ $settingsActive ? 'true' : 'false' }} }" class="sidebar-group">
        <button type="button" @click="open = !open"
                class="sidebar-group-toggle {{ $settingsActive ? 'text-slate-900 dark:text-white font-semibold' : '' }}">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Settings</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open, 'rotate-0': !open }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" style="display: {{ $settingsActive ? 'block' : 'none' }};" class="space-y-1 pl-1 pt-1">
            @hasanyrole('Super Admin|Admin')
            <a href="{{ route('settings.index') }}" class="sidebar-subitem {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.*') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>System & Logo Settings</span>
            </a>
            @endhasanyrole

            <a href="{{ route('client.profile') }}" class="sidebar-subitem {{ request()->routeIs('client.profile') ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('client.profile') ? 'bg-cyan-500' : 'bg-slate-400' }}"></span>
                <span>Account Profile</span>
            </a>
        </div>
    </div>

</nav>
