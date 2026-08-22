@php
    $isActive = fn (string $pattern) => request()->routeIs($pattern);
    $groupActive = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
@endphp

<aside class="w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0 bg-white"
       style="border-color:var(--sidebar-border)"
       :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">

    <div class="h-20 flex items-center px-4 border-b shrink-0" style="border-color:var(--sidebar-border)">
        <a href="{{ route('buyer.dashboard') }}" class="flex items-center min-w-0">
            <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm shrink-0">ES</div>
            <div class="ml-3 leading-tight min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">EduShopify</p>
                <p class="text-[11px] text-gray-400">Buyer Dashboard</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <a href="{{ route('buyer.dashboard') }}" class="sidebar-menu-item {{ $isActive('buyer.dashboard') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('buyer.dashboard') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        <div x-data="{ open: {{ $groupActive(['buyer.marketplace.*', 'buyer.suppliers.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['buyer.marketplace.*', 'buyer.suppliers.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['buyer.marketplace.*', 'buyer.suppliers.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-store sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Marketplace</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('buyer.marketplace.products.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.marketplace.products.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Products</a>
                <a href="{{ route('buyer.marketplace.services.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.marketplace.services.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Services</a>
                <a href="{{ route('buyer.suppliers.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.suppliers.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Suppliers</a>
            </div>
        </div>

        <div x-data="{ open: {{ $groupActive(['buyer.rfqs.*', 'buyer.quotations.*', 'buyer.awards.*', 'buyer.purchase-orders.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['buyer.rfqs.*', 'buyer.quotations.*', 'buyer.awards.*', 'buyer.purchase-orders.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['buyer.rfqs.*', 'buyer.quotations.*', 'buyer.awards.*', 'buyer.purchase-orders.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-file-signature sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Procurement</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('buyer.rfqs.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.rfqs.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">RFQs</a>
                <a href="{{ route('buyer.quotations.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.quotations.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Quotations</a>
                <a href="{{ route('buyer.awards.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.awards.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Awards</a>
                <a href="{{ route('buyer.purchase-orders.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.purchase-orders.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Purchase Orders</a>
            </div>
        </div>

        <a href="{{ route('buyer.saved-items.index') }}" class="sidebar-menu-item {{ $isActive('buyer.saved-items.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('buyer.saved-items.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-bookmark sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Saved Items</span>
        </a>

        <div x-data="{ open: {{ $groupActive(['buyer.messages.*', 'buyer.notifications.*', 'buyer.tickets.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['buyer.messages.*', 'buyer.notifications.*', 'buyer.tickets.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['buyer.messages.*', 'buyer.notifications.*', 'buyer.tickets.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-comments sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Communication</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('buyer.messages.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.messages.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Messages</a>
                <a href="{{ route('buyer.notifications.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.notifications.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Notifications</a>
                <a href="{{ route('buyer.tickets.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.tickets.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Support Tickets</a>
            </div>
        </div>

        <a href="{{ route('buyer.reviews.index') }}" class="sidebar-menu-item {{ $isActive('buyer.reviews.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('buyer.reviews.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-star sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Reviews</span>
        </a>

        <div x-data="{ open: {{ $groupActive(['buyer.profile.*', 'buyer.locations.*']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['buyer.profile.*', 'buyer.locations.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['buyer.profile.*', 'buyer.locations.*']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-building sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Buyer Profile</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('buyer.profile.edit') }}" class="sidebar-submenu-item {{ $isActive('buyer.profile.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Profile Information</a>
                <a href="{{ route('buyer.locations.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.locations.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Locations</a>
            </div>
        </div>

        @if($account->isOrganization())
            <div x-data="{ open: {{ $groupActive(['buyer.members.*', 'buyer.invitations.*', 'buyer.roles.*', 'buyer.role-requests.*', 'buyer.ownership.*']) ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-menu-item {{ $groupActive(['buyer.members.*', 'buyer.invitations.*', 'buyer.roles.*', 'buyer.role-requests.*', 'buyer.ownership.*']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive(['buyer.members.*', 'buyer.invitations.*', 'buyer.roles.*', 'buyer.role-requests.*', 'buyer.ownership.*']) ? '' : 'border-transparent' }}">
                    <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Organization</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="{{ route('buyer.members.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.members.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Members</a>
                    <a href="{{ route('buyer.invitations.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.invitations.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Invitations</a>
                    <a href="{{ route('buyer.roles.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.roles.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Roles &amp; Permissions</a>
                    <a href="{{ route('buyer.role-requests.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.role-requests.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Role Requests</a>
                    <a href="{{ route('buyer.ownership.index') }}" class="sidebar-submenu-item {{ $isActive('buyer.ownership.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Ownership</a>
                </div>
            </div>
        @endif

        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <div x-data="{ open: {{ $isActive('buyer.settings.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-menu-item {{ $isActive('buyer.settings.*') ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('buyer.settings.*') ? '' : 'border-transparent' }}">
                    <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Settings &amp; Account</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="{{ route('buyer.settings.security') }}" class="sidebar-submenu-item {{ $isActive('buyer.settings.security') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Personal &amp; Security</a>
                    <a href="{{ route('buyer.settings.dashboard-mode') }}" class="sidebar-submenu-item {{ $isActive('buyer.settings.dashboard-mode') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Dashboard Mode</a>
                    <a href="{{ route('buyer.settings.conversion') }}" class="sidebar-submenu-item {{ $isActive('buyer.settings.conversion') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Convert to Organization</a>
                    <a href="{{ route('buyer.settings.close-account') }}" class="sidebar-submenu-item {{ $isActive('buyer.settings.close-account') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Close Account</a>
                </div>
            </div>
        </div>

    </nav>

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
