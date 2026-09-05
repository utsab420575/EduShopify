@php
    $feUser = auth()->user();
    $feAccount = $feUser?->accountMember?->account;
    $feIsAdmin = $feUser?->isAdmin() ?? false;
    $feIsBuyer = $feAccount?->isBuyer() ?? false;
    $feIsSupplier = $feAccount?->isSupplier() ?? false;
@endphp
<header class="sticky top-0 z-40 bg-white border-b border-gray-200">
    <div class="fe-container">
        <div class="h-20 flex items-center gap-4">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm font-display bg-emerald-500">ES</span>
                <span class="hidden xs:inline text-lg font-bold tracking-tight font-display text-gray-900">EduShopify</span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-1 ml-2" aria-label="Primary">
                <div x-data="categoryMenu" @keydown.escape.window="close()" class="relative">
                    <button type="button" @click="toggle()" @click.outside="close()" class="fe-focus-ring inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-emerald-600" :class="open && 'text-emerald-600'" aria-haspopup="true" :aria-expanded="open.toString()">
                        Categories
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute left-0 top-full mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-lg p-3 z-50">
                        <ul class="grid grid-cols-1 gap-0.5" role="menu">
                            @forelse($headerCategories as $cat)
                                <li role="none">
                                    <a role="menuitem" href="{{ route('frontend.categories.show', $cat->slug) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600">
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-50 text-emerald-600">
                                            <i class="fa-solid {{ $cat->icon ?: 'fa-shapes' }} text-xs"></i>
                                        </span>
                                        {{ $cat->name }}
                                    </a>
                                </li>
                            @empty
                                <li class="px-3 py-2 text-sm text-gray-400">No categories yet.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('frontend.categories.index') }}" class="mt-2 block text-center text-sm font-semibold px-3 py-2 rounded-lg text-emerald-600">Browse all categories</a>
                    </div>
                </div>

                <a href="{{ route('frontend.catalog.index') }}" class="fe-focus-ring px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('frontend.catalog.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-emerald-600' }}">Marketplace</a>
                <a href="{{ route('frontend.suppliers.index') }}" class="fe-focus-ring px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('frontend.suppliers.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-emerald-600' }}">Suppliers</a>
                <a href="{{ route('frontend.rfqs.index') }}" class="fe-focus-ring px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('frontend.rfqs.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-emerald-600' }}">Opportunities</a>
                <a href="{{ route('frontend.pages.pricing') }}" class="fe-focus-ring px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('frontend.pages.pricing') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-emerald-600' }}">Pricing</a>
            </nav>

            {{-- Global search --}}
            <div class="hidden md:block flex-1 max-w-md ml-auto">
                @include('frontend.components.search.global-search')
            </div>

            {{-- Auth actions --}}
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <a href="{{ route('frontend.compare.index') }}" x-data="compareBadge"
                   class="group fe-focus-ring relative inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-700 hover:bg-emerald-50/80 transition-all duration-200 ease-in-out"
                   aria-label="Product comparison" title="View product comparison">
                    <i class="fa-solid fa-arrow-right-arrow-left text-xs text-gray-400 group-hover:text-emerald-600 transition-transform duration-200 group-hover:rotate-180"></i>
                    <span class="hidden xl:inline">Compare</span>
                    <span x-show="count > 0" x-cloak x-text="count"
                          class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow-xs transition-transform duration-200 group-hover:scale-110"></span>
                </a>
                @guest
                    <a href="{{ route('login') }}" class="fe-focus-ring px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600">Log in</a>
                    <a href="{{ route('frontend.handoff.post-rfq') }}" class="fe-focus-ring px-3.5 py-2 rounded-md text-sm font-semibold border border-gray-300 text-gray-900">Post an RFQ</a>
                    <a href="{{ route('register') }}" class="fe-btn-primary fe-focus-ring px-4 py-2 rounded-md text-sm font-semibold">Join Free</a>
                @else
                    <div x-data="{ open: false }" @keydown.escape.window="open=false" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open=false" class="fe-focus-ring flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50" :aria-expanded="open.toString()">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-semibold bg-emerald-500">{{ strtoupper(substr($feUser->name, 0, 1)) }}</span>
                            <span class="max-w-[120px] truncate">{{ $feUser->name }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 top-full mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50">
                            @if($feIsAdmin && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Admin Dashboard</a>
                            @endif
                            @if($feIsBuyer && \Illuminate\Support\Facades\Route::has('buyer.dashboard'))
                                <a href="{{ route('buyer.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Buyer Dashboard</a>
                            @endif
                            @if($feIsSupplier && \Illuminate\Support\Facades\Route::has('supplier.dashboard'))
                                <a href="{{ route('supplier.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Supplier Dashboard</a>
                            @endif
                            <div class="my-1 border-t border-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Log out</button>
                            </form>
                        </div>
                    </div>
                    @if(!$feIsAdmin)
                        <a href="{{ route('frontend.handoff.post-rfq') }}" class="fe-btn-primary fe-focus-ring px-4 py-2 rounded-md text-sm font-semibold">Post an RFQ</a>
                    @endif
                @endguest
            </div>

            {{-- Mobile controls --}}
            <div class="flex lg:hidden items-center gap-1 ml-auto">
                <button type="button" x-data @click="$dispatch('open-mobile-menu')" class="fe-focus-ring w-10 h-10 rounded-lg flex items-center justify-center text-gray-600" aria-label="Search marketplace">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" x-data @click="$dispatch('open-mobile-menu')" class="fe-focus-ring w-10 h-10 rounded-lg flex items-center justify-center text-gray-600" aria-label="Open menu" aria-haspopup="true">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>
