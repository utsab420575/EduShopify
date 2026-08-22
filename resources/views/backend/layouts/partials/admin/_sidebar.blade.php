@php
    $isActive = fn (string $pattern) => request()->routeIs($pattern);
    $groupActive = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
    $u = auth()->user();

    $groups = [
        'users-accounts' => ['admin.users.*', 'admin.accounts.*', 'admin.buyers.*', 'admin.suppliers.*', 'admin.account-members.*', 'admin.capabilities.*', 'admin.conversions.*', 'admin.closures.*'],
        'catalog' => ['admin.catalog.*'],
        'procurement' => ['admin.procurement.*'],
        'billing' => ['admin.billing.*'],
        'communication' => ['admin.communication.*'],
        'moderation' => ['admin.reviews.*'],
        'access-control' => ['admin.access-control.*'],
        'system' => ['admin.system.*'],
    ];
@endphp

<aside class="w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0 bg-white"
       style="border-color:var(--sidebar-border)"
       :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">

    <div class="h-20 flex items-center px-4 border-b shrink-0" style="border-color:var(--sidebar-border)">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center min-w-0">
            <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm shrink-0">ES</div>
            <div class="ml-3 leading-tight min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">EduShopify</p>
                <p class="text-[11px] text-gray-400">Admin Panel</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <a href="{{ route('admin.dashboard') }}" class="sidebar-menu-item {{ $isActive('admin.dashboard') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('admin.dashboard') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        @can('platform.accounts.view')
        <div x-data="{ open: {{ $groupActive($groups['users-accounts']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['users-accounts']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['users-accounts']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Users &amp; Accounts</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                @can('platform.users.view')
                <a href="{{ route('admin.users.index') }}" class="sidebar-submenu-item {{ $isActive('admin.users.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Users</a>
                @endcan
                <a href="{{ route('admin.accounts.index') }}" class="sidebar-submenu-item {{ $isActive('admin.accounts.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Accounts</a>
                <a href="{{ route('admin.buyers.index') }}" class="sidebar-submenu-item {{ $isActive('admin.buyers.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Buyers</a>
                <a href="{{ route('admin.suppliers.index') }}" class="sidebar-submenu-item {{ $isActive('admin.suppliers.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Suppliers</a>
                <a href="{{ route('admin.account-members.index') }}" class="sidebar-submenu-item {{ $isActive('admin.account-members.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Account Members</a>
                @can('platform.capabilities.review')
                <a href="{{ route('admin.capabilities.index') }}" class="sidebar-submenu-item {{ $isActive('admin.capabilities.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Capabilities</a>
                @endcan
                @can('platform.conversions.review')
                <a href="{{ route('admin.conversions.index') }}" class="sidebar-submenu-item {{ $isActive('admin.conversions.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Account Conversions</a>
                @endcan
                <a href="{{ route('admin.closures.index') }}" class="sidebar-submenu-item {{ $isActive('admin.closures.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Closure / Deletion Queue</a>
            </div>
        </div>
        @endcan

        @if($u && $u->hasAnyPermission(['platform.capabilities.review', 'platform.conversions.review', 'platform.supplier_documents.verify', 'platform.listings.moderate', 'platform.categories.manage', 'platform.access_control.manage', 'platform.reviews.moderate']))
        <a href="{{ route('admin.approvals.index') }}" class="sidebar-menu-item {{ $isActive('admin.approvals.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('admin.approvals.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-clipboard-check sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Approval Center</span>
            @if(($approvalQueueTotal ?? 0) > 0)
                <span class="text-[10px] font-semibold text-white rounded-full px-1.5 py-0.5" style="background:#ef4444">{{ $approvalQueueTotal }}</span>
            @endif
        </a>
        @endif

        @if($u && $u->hasAnyPermission(['platform.categories.manage', 'platform.attributes.manage', 'platform.brands.manage', 'platform.listings.moderate']))
        <div x-data="{ open: {{ $groupActive($groups['catalog']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['catalog']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['catalog']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-layer-group sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Catalog &amp; Taxonomy</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                @can('platform.categories.manage')
                <a href="{{ route('admin.catalog.categories.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.categories.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Categories</a>
                <a href="{{ route('admin.catalog.buyer-types.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.buyer-types.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Buyer Types</a>
                <a href="{{ route('admin.catalog.supplier-types.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.supplier-types.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Supplier Types</a>
                <a href="{{ route('admin.catalog.document-types.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.document-types.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Document Types</a>
                <a href="{{ route('admin.catalog.exhibitions.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.exhibitions.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Exhibitions</a>
                @endcan
                @can('platform.attributes.manage')
                <a href="{{ route('admin.catalog.attributes.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.attributes.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Attributes</a>
                <a href="{{ route('admin.catalog.units.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.units.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Units</a>
                @endcan
                @can('platform.brands.manage')
                <a href="{{ route('admin.catalog.brands.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.brands.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Brands</a>
                @endcan
                @can('platform.listings.moderate')
                <a href="{{ route('admin.catalog.listings.index') }}" class="sidebar-submenu-item {{ $isActive('admin.catalog.listings.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Listings</a>
                @endcan
            </div>
        </div>
        @endif

        @can('platform.rfqs.moderate')
        <div x-data="{ open: {{ $groupActive($groups['procurement']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['procurement']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['procurement']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-file-signature sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Procurement Oversight</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('admin.procurement.rfqs.index') }}" class="sidebar-submenu-item {{ $isActive('admin.procurement.rfqs.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">RFQs</a>
                <a href="{{ route('admin.procurement.quotations.index') }}" class="sidebar-submenu-item {{ $isActive('admin.procurement.quotations.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Quotations</a>
                <a href="{{ route('admin.procurement.awards.index') }}" class="sidebar-submenu-item {{ $isActive('admin.procurement.awards.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Awards</a>
                <a href="{{ route('admin.procurement.purchase-orders.index') }}" class="sidebar-submenu-item {{ $isActive('admin.procurement.purchase-orders.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Purchase Orders</a>
            </div>
        </div>
        @endcan

        @can('platform.subscriptions.manage')
        <div x-data="{ open: {{ $groupActive($groups['billing']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['billing']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['billing']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-credit-card sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Subscription &amp; Billing</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('admin.billing.plans.index') }}" class="sidebar-submenu-item {{ $isActive('admin.billing.plans.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Subscription Plans</a>
                <a href="{{ route('admin.billing.subscriptions.index') }}" class="sidebar-submenu-item {{ $isActive('admin.billing.subscriptions.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Supplier Subscriptions</a>
                <a href="{{ route('admin.billing.payments.index') }}" class="sidebar-submenu-item {{ $isActive('admin.billing.payments.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Subscription Payments</a>
            </div>
        </div>
        @endcan

        @can('platform.communication.manage')
        <div x-data="{ open: {{ $groupActive($groups['communication']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['communication']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['communication']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-comments sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Communication</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('admin.communication.conversations.index') }}" class="sidebar-submenu-item {{ $isActive('admin.communication.conversations.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Conversations</a>
                <a href="{{ route('admin.communication.inquiries.index') }}" class="sidebar-submenu-item {{ $isActive('admin.communication.inquiries.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Contact Inquiries</a>
            </div>
        </div>
        @endcan

        <a href="{{ route('admin.notifications.index') }}" class="sidebar-menu-item {{ $isActive('admin.notifications.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('admin.notifications.*') ? '' : 'border-transparent' }}">
            <i class="fa-regular fa-bell sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Notifications</span>
        </a>

        @can('platform.reviews.moderate')
        <div x-data="{ open: {{ $groupActive($groups['moderation']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['moderation']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['moderation']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-star sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Reviews &amp; Moderation</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('admin.reviews.index') }}" class="sidebar-submenu-item {{ $isActive('admin.reviews.index') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Reviews</a>
                <a href="{{ route('admin.reviews.replies.index') }}" class="sidebar-submenu-item {{ $isActive('admin.reviews.replies.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Supplier Replies</a>
                <a href="{{ route('admin.reviews.reports.index') }}" class="sidebar-submenu-item {{ $isActive('admin.reviews.reports.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Review Reports</a>
            </div>
        </div>
        @endcan

        @can('platform.tickets.manage')
        <a href="{{ route('admin.tickets.index') }}" class="sidebar-menu-item {{ $isActive('admin.tickets.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('admin.tickets.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-life-ring sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Support</span>
        </a>
        @endcan

        @can('platform.access_control.manage')
        <div x-data="{ open: {{ $groupActive($groups['access-control']) ? 'true' : 'false' }} }">
            <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['access-control']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['access-control']) ? '' : 'border-transparent' }}">
                <i class="fa-solid fa-shield-halved sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Access Control</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="{{ route('admin.access-control.roles.index') }}" class="sidebar-submenu-item {{ $isActive('admin.access-control.roles.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Platform Roles</a>
                <a href="{{ route('admin.access-control.permissions.index') }}" class="sidebar-submenu-item {{ $isActive('admin.access-control.permissions.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Permissions</a>
                <a href="{{ route('admin.access-control.role-requests.index') }}" class="sidebar-submenu-item {{ $isActive('admin.access-control.role-requests.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Account Role Requests</a>
            </div>
        </div>
        @endcan

        @can('platform.settings.manage')
        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <div x-data="{ open: {{ $groupActive($groups['system']) ? 'true' : 'false' }} }">
                <button @click="open = !open" class="sidebar-menu-item {{ $groupActive($groups['system']) ? 'active' : '' }} w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $groupActive($groups['system']) ? '' : 'border-transparent' }}">
                    <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">System &amp; Settings</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="{{ route('admin.system.settings.index') }}" class="sidebar-submenu-item {{ $isActive('admin.system.settings.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">General Settings</a>
                    <a href="{{ route('admin.system.theme.edit') }}" class="sidebar-submenu-item {{ $isActive('admin.system.theme.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Appearance / Theme</a>
                    <a href="{{ route('admin.system.geography.index') }}" class="sidebar-submenu-item {{ $isActive('admin.system.geography.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Geography</a>
                    <a href="{{ route('admin.system.currencies.index') }}" class="sidebar-submenu-item {{ $isActive('admin.system.currencies.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Currencies</a>
                    <a href="{{ route('admin.system.languages.index') }}" class="sidebar-submenu-item {{ $isActive('admin.system.languages.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Languages</a>
                    <a href="{{ route('admin.system.jobs.index') }}" class="sidebar-submenu-item {{ $isActive('admin.system.jobs.*') ? 'active' : '' }} block px-3 py-2 text-sm rounded-md">Failed Jobs</a>
                </div>
            </div>
        </div>
        @endcan

        @can('platform.activity_logs.view')
        <a href="{{ route('admin.system.audit.index') }}" class="sidebar-menu-item {{ $isActive('admin.system.audit.*') ? 'active' : '' }} flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 {{ $isActive('admin.system.audit.*') ? '' : 'border-transparent' }}">
            <i class="fa-solid fa-clock-rotate-left sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Audit Log</span>
        </a>
        @endcan

    </nav>

    <div class="p-3 border-t shrink-0" style="border-color:var(--sidebar-border)">
        <div class="flex items-center px-2 py-2 rounded-lg" style="background:var(--theme-primary-soft)">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" class="w-8 h-8 rounded-full" alt="">
            <div class="ml-2 leading-tight min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-[10px] text-gray-500 truncate">{{ $user->roles->first()?->display_name ?? 'Admin' }}</p>
            </div>
        </div>
    </div>
</aside>
