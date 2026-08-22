@php
    $isActive    = fn (string $pattern) => request()->routeIs($pattern);
    $groupActive = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
@endphp

<aside class="w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0 bg-white"
       style="border-color:var(--sidebar-border)"
       :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">

    {{-- Brand / Logo --}}
    <div class="h-20 flex items-center px-4 border-b shrink-0" style="border-color:var(--sidebar-border)">
        <a href="{{ route('supplier.dashboard') }}" class="flex items-center min-w-0">
            <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm shrink-0">ES</div>
            <div class="ml-3 leading-tight min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">EduShopify</p>
                <p class="text-[11px] text-gray-400">Supplier Dashboard</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        {{-- Dashboard --}}
        <a href="{{ route('supplier.dashboard') }}" class="sidebar-menu-item {{ $isActive('supplier.dashboard') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('supplier.dashboard') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        {{-- Business Profile --}}
        <div x-data="{ open: {{ $groupActive(['supplier.company.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.company.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.company.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-building sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Business Profile</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('supplier.company.profile') }}" class="sidebar-submenu-item {{ $isActive('supplier.company.profile') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Company Information</a>
                <a href="{{ route('supplier.company.documents') }}" class="sidebar-submenu-item {{ $isActive('supplier.company.documents*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Documents &amp; Verification</a>
                <a href="{{ route('supplier.company.service-areas') }}" class="sidebar-submenu-item {{ $isActive('supplier.company.service-areas*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Locations &amp; Service Areas</a>
                <a href="{{ route('supplier.company.business-hours') }}" class="sidebar-submenu-item {{ $isActive('supplier.company.business-hours') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Business Hours</a>
                <a href="{{ route('supplier.company.gallery') }}" class="sidebar-submenu-item {{ $isActive('supplier.company.gallery*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Gallery &amp; Videos</a>
                <a href="{{ route('supplier.company.exhibitions') }}" class="sidebar-submenu-item {{ $isActive('supplier.company.exhibitions*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Exhibitions</a>
            </div>
        </div>

        {{-- Catalog --}}
        <div x-data="{ open: {{ $groupActive(['supplier.catalog.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.catalog.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.catalog.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-box-open sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Catalog</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('supplier.catalog.listings.index') }}" class="sidebar-submenu-item {{ ($isActive('supplier.catalog.listings.*') && !$isActive('supplier.catalog.listings.create')) ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">All Listings</a>
                <a href="{{ route('supplier.catalog.listings.create') }}" class="sidebar-submenu-item {{ $isActive('supplier.catalog.listings.create') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Add Listing</a>
                <a href="{{ route('supplier.catalog.suggestions.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.catalog.suggestions.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Suggestions</a>
            </div>
        </div>

        {{-- RFQ Opportunities --}}
        <div x-data="{ open: {{ $groupActive(['supplier.opportunities.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.opportunities.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.opportunities.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-magnifying-glass-chart sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">RFQ Opportunities</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('supplier.opportunities.index') }}" class="sidebar-submenu-item {{ ($isActive('supplier.opportunities.index') && request('filter') !== 'invited') || $isActive('supplier.opportunities.show') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Available RFQs</a>
                <a href="{{ route('supplier.opportunities.index', ['filter' => 'invited']) }}" class="sidebar-submenu-item {{ $isActive('supplier.opportunities.index') && request('filter') === 'invited' ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Invited RFQs</a>
            </div>
        </div>

        {{-- Quotations --}}
        <div x-data="{ open: {{ $groupActive(['supplier.quotations.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.quotations.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.quotations.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-file-invoice sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Quotations</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('supplier.quotations.index') }}" class="sidebar-submenu-item {{ ($isActive('supplier.quotations.index') && !request('status')) || $isActive('supplier.quotations.show') || $isActive('supplier.quotations.create') || $isActive('supplier.quotations.revision') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">My Quotations</a>
                <a href="{{ route('supplier.quotations.index', ['status' => 'draft']) }}" class="sidebar-submenu-item {{ $isActive('supplier.quotations.index') && request('status') === 'draft' ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Draft Quotations</a>
                <a href="{{ route('supplier.quotations.index', ['status' => 'revision_requested']) }}" class="sidebar-submenu-item {{ $isActive('supplier.quotations.index') && request('status') === 'revision_requested' ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Revision Requests</a>
            </div>
        </div>

        {{-- Awards --}}
        <a href="{{ route('supplier.awards.index') }}" class="sidebar-menu-item {{ $isActive('supplier.awards.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('supplier.awards.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-trophy sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Awards</span>
        </a>

        {{-- Purchase Orders --}}
        <a href="{{ route('supplier.purchase-orders.index') }}" class="sidebar-menu-item {{ $isActive('supplier.purchase-orders.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('supplier.purchase-orders.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-clipboard-list sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Purchase Orders</span>
        </a>

        {{-- Subscription & Billing --}}
        <div x-data="{ open: {{ $groupActive(['supplier.subscription.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.subscription.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.subscription.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-credit-card sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Subscription &amp; Billing</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('supplier.subscription.current') }}" class="sidebar-submenu-item {{ $isActive('supplier.subscription.current') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Current Plan</a>
                <a href="{{ route('supplier.subscription.plans') }}" class="sidebar-submenu-item {{ $isActive('supplier.subscription.plans') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Available Plans</a>
                <a href="{{ route('supplier.subscription.payments') }}" class="sidebar-submenu-item {{ $isActive('supplier.subscription.payments') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Payment History</a>
            </div>
        </div>

        {{-- Communication --}}
        <div x-data="{ open: {{ $groupActive(['supplier.messages.*', 'supplier.notifications.*', 'supplier.tickets.*', 'supplier.contact-inquiries.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.messages.*', 'supplier.notifications.*', 'supplier.tickets.*', 'supplier.contact-inquiries.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.messages.*', 'supplier.notifications.*', 'supplier.tickets.*', 'supplier.contact-inquiries.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-comments sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Communication</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('supplier.messages.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.messages.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Messages</a>
                <a href="{{ route('supplier.notifications.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.notifications.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Notifications</a>
                <a href="{{ route('supplier.tickets.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.tickets.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Support Tickets</a>
                <a href="{{ route('supplier.contact-inquiries.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.contact-inquiries.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Contact Inquiries</a>
            </div>
        </div>

        {{-- Reviews --}}
        <a href="{{ route('supplier.reviews.index') }}" class="sidebar-menu-item {{ $isActive('supplier.reviews.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('supplier.reviews.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-star sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Reviews</span>
        </a>

        {{-- Organization (only for organization accounts) --}}
        @if($account->isOrganization())
            <div x-data="{ open: {{ $groupActive(['supplier.members.*', 'supplier.invitations.*', 'supplier.roles.*', 'supplier.role-requests.*', 'supplier.ownership.*']) ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['supplier.members.*', 'supplier.invitations.*', 'supplier.roles.*', 'supplier.role-requests.*', 'supplier.ownership.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['supplier.members.*', 'supplier.invitations.*', 'supplier.roles.*', 'supplier.role-requests.*', 'supplier.ownership.*']) ? '' : 'border-transparent' }}">
                    <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Organization</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="{{ route('supplier.members.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.members.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Members</a>
                    <a href="{{ route('supplier.invitations.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.invitations.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Invitations</a>
                    <a href="{{ route('supplier.roles.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.roles.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Roles &amp; Permissions</a>
                    <a href="{{ route('supplier.role-requests.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.role-requests.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Role Requests</a>
                    <a href="{{ route('supplier.ownership.index') }}" class="sidebar-submenu-item {{ $isActive('supplier.ownership.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Ownership</a>
                </div>
            </div>
        @endif

        {{-- Settings & Account --}}
        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <div x-data="{ open: {{ $isActive('supplier.settings.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-menu-item {{ $isActive('supplier.settings.*') ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('supplier.settings.*') ? '' : 'border-transparent' }}">
                    <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Settings &amp; Account</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="{{ route('supplier.settings.security') }}" class="sidebar-submenu-item {{ $isActive('supplier.settings.security') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Personal &amp; Security</a>
                    <a href="{{ route('supplier.settings.dashboard-mode') }}" class="sidebar-submenu-item {{ $isActive('supplier.settings.dashboard-mode') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Dashboard Mode</a>
                    <a href="{{ route('supplier.settings.conversion') }}" class="sidebar-submenu-item {{ $isActive('supplier.settings.conversion') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Convert to Organization</a>
                    <a href="{{ route('supplier.settings.close-account') }}" class="sidebar-submenu-item {{ $isActive('supplier.settings.close-account') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Close Account</a>
                </div>
            </div>
        </div>

    </nav>

    {{-- User avatar footer --}}
    <div class="p-3 border-t shrink-0" style="border-color:var(--sidebar-border)">
        <div class="flex items-center px-2 py-2 rounded-lg" style="background:var(--theme-primary-soft)">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" class="w-8 h-8 rounded-full" alt="">
            <div class="ml-2 leading-tight min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-[10px] text-gray-500 truncate">{{ $account->display_name }}</p>
            </div>
        </div>
    </div>
</aside>
