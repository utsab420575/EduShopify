@props(['role'])

@php
    $accent = match ($role) {
        'supplier' => 'teal',
        'admin'    => 'violet',
        default    => 'indigo',
    };

    $roleLabel = match ($role) {
        'supplier' => 'Supplier',
        'admin'    => 'Admin',
        default    => 'Buyer',
    };
@endphp

<!-- Mobile overlay -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" x-transition.opacity></div>

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0"
>
    <!-- Logo + role badge -->
    <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-100 shrink-0">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Edushopify" class="h-7 w-auto">
        </a>
        <span class="text-[10px] font-bold uppercase tracking-wider text-{{ $accent }}-700 bg-{{ $accent }}-50 border border-{{ $accent }}-200 px-1.5 py-0.5 rounded">
            {{ $roleLabel }}
        </span>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
        @if($role === 'supplier')
            <x-dashboard.nav-item label="Dashboard" icon="home" route="supplier.dashboard" :accent="$accent" />

            <x-dashboard.nav-group label="RFQ Center">
                <x-dashboard.nav-item label="RFQ Opportunities" icon="bell" route="supplier.opportunities.index" routePattern="supplier.opportunities.*" :accent="$accent" />
                <x-dashboard.nav-item label="My Quotations" icon="inbox-arrow-down" route="supplier.quotations.index" routePattern="supplier.quotations.*" :accent="$accent" />
                <x-dashboard.nav-item label="Awarded Projects" icon="trophy" route="supplier.awards.index" routePattern="supplier.awards.*" :accent="$accent" />
                <x-dashboard.nav-item label="Purchase Orders" icon="clipboard-document-list" route="supplier.purchase-orders.index" routePattern="supplier.purchase-orders.*" :accent="$accent" />
            </x-dashboard.nav-group>

            <x-dashboard.nav-group label="Catalog">
                <x-dashboard.nav-item label="My Listings" icon="document-text" route="supplier.listings.index" routePattern="supplier.listings.*" :accent="$accent" />
            </x-dashboard.nav-group>

            <x-dashboard.nav-group label="Account">
                <x-dashboard.nav-item label="My Reviews" icon="heart" route="supplier.reviews.index" :accent="$accent" />
                <x-dashboard.nav-item label="Messages" icon="chat-bubble-left-right" route="supplier.messages.index" routePattern="supplier.messages.*" :accent="$accent" />
                <x-dashboard.nav-item label="Subscription" icon="clock" route="supplier.billing" :accent="$accent" />
                <x-dashboard.nav-item label="My Profile" icon="user-circle" route="supplier.profile" :accent="$accent" />
                <x-dashboard.nav-item label="Documents" icon="document-text" route="supplier.documents" :accent="$accent" />
                <x-dashboard.nav-item label="Support Tickets" icon="lifebuoy" route="supplier.tickets.index" routePattern="supplier.tickets.*" :accent="$accent" />
            </x-dashboard.nav-group>
        @endif
    </nav>
</aside>
