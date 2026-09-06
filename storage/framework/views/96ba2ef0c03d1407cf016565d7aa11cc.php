<?php
    $isActive    = fn (string $pattern) => request()->routeIs($pattern);
    $groupActive = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
    $account = $account ?? auth()->user()?->accountMember?->account;
    $user = $user ?? auth()->user();
?>

<aside class="w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0 bg-white"
       style="border-color:var(--sidebar-border)"
       :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">

    
    <div class="h-20 flex items-center px-4 border-b shrink-0" style="border-color:var(--sidebar-border)">
        <a href="<?php echo e(route('home')); ?>" class="flex items-center min-w-0">
            <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm shrink-0">ES</div>
            <div class="ml-3 leading-tight min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">EduShopify</p>
                <p class="text-[11px] text-gray-400">Supplier Dashboard</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        
        <a href="<?php echo e(route('supplier.dashboard')); ?>" class="sidebar-menu-item <?php echo e($isActive('supplier.dashboard') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('supplier.dashboard') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['supplier.profile.view', 'supplier.profile.update', 'supplier.documents.manage', 'supplier.service_areas.manage', 'supplier.company.profile', 'account.view', 'account.update'])): ?>
        <a href="<?php echo e(route('supplier.company.profile')); ?>" class="sidebar-menu-item <?php echo e($groupActive(['supplier.company.*']) ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.company.*']) ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-building sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Business Profile</span>
        </a>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['listing.view', 'listing.create', 'listing.update', 'supplier.catalog.listings.index', 'supplier.catalog.listings.create'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['supplier.catalog.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['supplier.catalog.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.catalog.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-box-open sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Catalog</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['listing.view', 'supplier.catalog.listings.index'])): ?>
                    <a href="<?php echo e(route('supplier.catalog.listings.index')); ?>" class="sidebar-submenu-item <?php echo e(($isActive('supplier.catalog.listings.*') && !$isActive('supplier.catalog.listings.create')) ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">All Listings</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['listing.create', 'supplier.catalog.listings.create'])): ?>
                    <a href="<?php echo e(route('supplier.catalog.listings.create')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.catalog.listings.create') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Add Listing</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['listing.view', 'listing.create', 'supplier.catalog.suggestions.index'])): ?>
                    <a href="<?php echo e(route('supplier.catalog.suggestions.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.catalog.suggestions.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Suggestions</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['rfq_opportunity.view', 'opportunity.view', 'supplier.opportunities.index'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['supplier.opportunities.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['supplier.opportunities.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.opportunities.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-magnifying-glass-chart sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">RFQ Opportunities</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('supplier.opportunities.index')); ?>" class="sidebar-submenu-item <?php echo e(($isActive('supplier.opportunities.index') && request('filter') !== 'invited') || $isActive('supplier.opportunities.show') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Available RFQs</a>
                <a href="<?php echo e(route('supplier.opportunities.index', ['filter' => 'invited'])); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.opportunities.index') && request('filter') === 'invited' ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Invited RFQs</a>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['quotation.view_own', 'quotation.create', 'quotation.submit', 'quotation.revise', 'supplier.quotations.index'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['supplier.quotations.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['supplier.quotations.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.quotations.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-file-invoice sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Quotations</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('supplier.quotations.index')); ?>" class="sidebar-submenu-item <?php echo e(($isActive('supplier.quotations.index') && !request('status')) || $isActive('supplier.quotations.show') || $isActive('supplier.quotations.create') || $isActive('supplier.quotations.revision') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">My Quotations</a>
                <a href="<?php echo e(route('supplier.quotations.index', ['status' => 'draft'])); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.quotations.index') && request('status') === 'draft' ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Draft Quotations</a>
                <a href="<?php echo e(route('supplier.quotations.index', ['status' => 'revision_requested'])); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.quotations.index') && request('status') === 'revision_requested' ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Revision Requests</a>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['award.view', 'award.accept', 'award.reject', 'supplier.awards.index'])): ?>
        <a href="<?php echo e(route('supplier.awards.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('supplier.awards.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('supplier.awards.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-trophy sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Awards</span>
        </a>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['purchase_order.view_supplier', 'purchase_order.update_supplier', 'supplier.purchase-orders.index'])): ?>
        <a href="<?php echo e(route('supplier.purchase-orders.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('supplier.purchase-orders.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('supplier.purchase-orders.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-clipboard-list sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Purchase Orders</span>
        </a>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['subscription.view', 'subscription.select', 'subscription.cancel', 'billing.view', 'billing.manage', 'supplier.subscription.current'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['supplier.subscription.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['supplier.subscription.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.subscription.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-credit-card sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Subscription &amp; Billing</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('supplier.subscription.current')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.subscription.current') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Current Plan</a>
                <a href="<?php echo e(route('supplier.subscription.plans')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.subscription.plans') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Available Plans</a>
                <a href="<?php echo e(route('supplier.subscription.payments')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.subscription.payments') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Payment History</a>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['messages.view', 'messages.send', 'tickets.view', 'tickets.create', 'tickets.reply', 'supplier.messages.index'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['supplier.messages.*', 'supplier.notifications.*', 'supplier.tickets.*', 'supplier.contact-inquiries.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['supplier.messages.*', 'supplier.notifications.*', 'supplier.tickets.*', 'supplier.contact-inquiries.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.messages.*', 'supplier.notifications.*', 'supplier.tickets.*', 'supplier.contact-inquiries.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-comments sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Communication</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('supplier.messages.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.messages.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Messages</a>
                <a href="<?php echo e(route('supplier.notifications.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.notifications.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Notifications</a>
                <a href="<?php echo e(route('supplier.tickets.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.tickets.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Support Tickets</a>
                <a href="<?php echo e(route('supplier.contact-inquiries.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.contact-inquiries.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Contact Inquiries</a>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['review.reply', 'supplier.reviews.index'])): ?>
        <a href="<?php echo e(route('supplier.reviews.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('supplier.reviews.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('supplier.reviews.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-star sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Reviews</span>
        </a>
        <?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($account && $account->isOrganization()): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['members.view', 'members.invite', 'members.update', 'members.remove', 'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign', 'ownership.transfer', 'supplier.members.index'])): ?>
            <div x-data="{ open: <?php echo e($groupActive(['supplier.members.*', 'supplier.invitations.*', 'supplier.roles.*', 'supplier.role-requests.*', 'supplier.ownership.*']) ? 'true' : 'false'); ?> }">
                <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['supplier.members.*', 'supplier.invitations.*', 'supplier.roles.*', 'supplier.role-requests.*', 'supplier.ownership.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['supplier.members.*', 'supplier.invitations.*', 'supplier.roles.*', 'supplier.role-requests.*', 'supplier.ownership.*']) ? '' : 'border-transparent'); ?>">
                    <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Organization</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['members.view', 'supplier.members.index'])): ?>
                        <a href="<?php echo e(route('supplier.members.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.members.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Members</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['members.invite', 'supplier.invitations.index'])): ?>
                        <a href="<?php echo e(route('supplier.invitations.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.invitations.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Invitations</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['roles.view', 'roles.create', 'roles.update', 'roles.assign', 'supplier.roles.index'])): ?>
                        <a href="<?php echo e(route('supplier.roles.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.roles.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Roles &amp; Permissions</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['roles.view', 'roles.assign', 'supplier.role-requests.index'])): ?>
                        <a href="<?php echo e(route('supplier.role-requests.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.role-requests.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Role Requests</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['ownership.transfer', 'supplier.ownership.index'])): ?>
                        <a href="<?php echo e(route('supplier.ownership.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.ownership.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Ownership</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <div x-data="{ open: <?php echo e($isActive('supplier.settings.*') ? 'true' : 'false'); ?> }">
                <button @click="open = !open" class="sidebar-menu-item <?php echo e($isActive('supplier.settings.*') ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('supplier.settings.*') ? '' : 'border-transparent'); ?>">
                    <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Settings &amp; Account</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="<?php echo e(route('supplier.settings.security')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.settings.security') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Personal &amp; Security</a>
                    <a href="<?php echo e(route('supplier.settings.dashboard-mode')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.settings.dashboard-mode') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Dashboard Mode</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($account && !$account->isOrganization()): ?>
                        <a href="<?php echo e(route('supplier.settings.conversion')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.settings.conversion') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Convert to Organization</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['account.close', 'supplier.settings.close-account'])): ?>
                        <a href="<?php echo e(route('supplier.settings.close-account')); ?>" class="sidebar-submenu-item <?php echo e($isActive('supplier.settings.close-account') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Close Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </nav>

    
    <div class="p-3 border-t shrink-0" style="border-color:var(--sidebar-border)">
        <div class="flex items-center px-2 py-2 rounded-lg" style="background:var(--theme-primary-soft)">
            <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($user?->name ?? 'User')); ?>&background=4f46e5&color=fff" class="w-8 h-8 rounded-full" alt="">
            <div class="ml-2 leading-tight min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate"><?php echo e($user?->name); ?></p>
                <p class="text-[10px] text-gray-500 truncate"><?php echo e($account?->display_name); ?></p>
            </div>
        </div>
    </div>
</aside>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\layouts\partials\supplier\_sidebar.blade.php ENDPATH**/ ?>