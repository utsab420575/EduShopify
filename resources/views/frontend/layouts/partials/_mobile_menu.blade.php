@php
    $feUser = auth()->user();
    $feAccount = $feUser?->accountMember?->account;
    $feIsAdmin = $feUser?->isAdmin() ?? false;
    $feIsBuyer = $feAccount?->isBuyer() ?? false;
    $feIsSupplier = $feAccount?->isSupplier() ?? false;
@endphp
<div
    x-data="mobileMenu"
    @open-mobile-menu.window="open = true; document.body.style.overflow = 'hidden'"
    x-show="open"
    x-cloak
    class="lg:hidden fixed inset-0 z-50"
    role="dialog"
    aria-modal="true"
    aria-label="Site menu"
    @keydown.escape.window="close(); document.body.style.overflow = ''"
>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-slate-900/40" @click="close(); document.body.style.overflow = ''"></div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="absolute inset-x-0 top-0 bg-white shadow-xl max-h-screen overflow-y-auto">
        <div class="fe-container py-4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-lg font-bold" style="font-family:var(--font-display);">Menu</span>
                <button type="button" @click="close(); document.body.style.overflow = ''" class="fe-focus-ring w-10 h-10 rounded-lg flex items-center justify-center text-slate-500" aria-label="Close menu">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mb-5">
                @include('frontend.components.search.global-search', ['size' => 'mobile'])
            </div>

            <nav class="space-y-1 mb-5" aria-label="Mobile primary">
                <a href="{{ route('frontend.catalog.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Marketplace</a>
                <a href="{{ route('frontend.products.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Products</a>
                <a href="{{ route('frontend.services.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Services</a>
                <a href="{{ route('frontend.suppliers.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Suppliers</a>
                <a href="{{ route('frontend.compare.index') }}" x-data="compareBadge" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <span><i class="fa-solid fa-arrow-right-arrow-left mr-1.5"></i> Compare</span>
                    <span x-show="count > 0" x-cloak x-text="count" class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:var(--fe-primary-soft);color:var(--fe-primary);"></span>
                </a>
                <a href="{{ route('frontend.rfqs.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Opportunities</a>
                <a href="{{ route('frontend.pages.pricing') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Pricing</a>
                <a href="{{ route('frontend.pages.how-it-works') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">How It Works</a>

                <div x-data="{ open: false }" class="pt-1">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Categories
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-cloak class="pl-3 space-y-0.5 mt-1">
                        @foreach($headerCategories as $cat)
                            <a href="{{ route('frontend.categories.show', $cat->slug) }}" class="block px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50">{{ $cat->name }}</a>
                        @endforeach
                        <a href="{{ route('frontend.categories.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold" style="color:var(--fe-primary);">Browse all categories</a>
                    </div>
                </div>
            </nav>

            <div class="border-t pt-4 space-y-2" style="border-color:var(--fe-border);">
                @guest
                    <a href="{{ route('frontend.handoff.post-rfq') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);">Post an RFQ</a>
                    <a href="{{ route('register') }}" class="fe-btn-primary block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Join Free</a>
                    <a href="{{ route('login') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600">Log in</a>
                @else
                    @if($feIsAdmin && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);">Admin Dashboard</a>
                    @endif
                    @if($feIsBuyer && \Illuminate\Support\Facades\Route::has('buyer.dashboard'))
                        <a href="{{ route('buyer.dashboard') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);">Buyer Dashboard</a>
                    @endif
                    @if($feIsSupplier && \Illuminate\Support\Facades\Route::has('supplier.dashboard'))
                        <a href="{{ route('supplier.dashboard') }}" class="block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);">Supplier Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600">Log out</button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</div>
