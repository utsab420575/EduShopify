<?php
    $isActive = fn (string $pattern) => request()->routeIs($pattern);
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
                <p class="text-[11px] text-gray-400">Buyer Dashboard</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        
        <a href="<?php echo e(route('buyer.dashboard')); ?>" class="sidebar-menu-item <?php echo e($isActive('buyer.dashboard') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('buyer.dashboard') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        
        <div x-data="{ open: <?php echo e($groupActive(['buyer.marketplace.*', 'buyer.suppliers.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['buyer.marketplace.*', 'buyer.suppliers.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['buyer.marketplace.*', 'buyer.suppliers.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-store sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Marketplace</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('buyer.marketplace.products.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.marketplace.products.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Products</a>
                <a href="<?php echo e(route('buyer.marketplace.services.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.marketplace.services.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Services</a>
                <a href="<?php echo e(route('buyer.suppliers.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.suppliers.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Suppliers</a>
            </div>
        </div>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['rfq.view', 'rfq.create', 'quotation.view_received', 'purchase_order.view_buyer', 'buyer.rfqs.index', 'buyer.quotations.index', 'buyer.purchase-orders.index'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['buyer.rfqs.*', 'buyer.quotations.*', 'buyer.awards.*', 'buyer.purchase-orders.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['buyer.rfqs.*', 'buyer.quotations.*', 'buyer.awards.*', 'buyer.purchase-orders.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['buyer.rfqs.*', 'buyer.quotations.*', 'buyer.awards.*', 'buyer.purchase-orders.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-file-signature sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Procurement</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['rfq.view', 'rfq.create', 'buyer.rfqs.index'])): ?>
                    <a href="<?php echo e(route('buyer.rfqs.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.rfqs.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">RFQs</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['quotation.view_received', 'buyer.quotations.index'])): ?>
                    <a href="<?php echo e(route('buyer.quotations.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.quotations.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Quotations</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['quotation.award', 'buyer.awards.index'])): ?>
                    <a href="<?php echo e(route('buyer.awards.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.awards.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Awards</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['purchase_order.view_buyer', 'purchase_order.update_buyer', 'buyer.purchase-orders.index'])): ?>
                    <a href="<?php echo e(route('buyer.purchase-orders.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.purchase-orders.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Purchase Orders</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <a href="<?php echo e(route('buyer.saved-items.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('buyer.saved-items.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('buyer.saved-items.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-bookmark sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Saved Items</span>
        </a>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['messages.view', 'messages.send', 'tickets.view', 'tickets.create', 'tickets.reply', 'buyer.messages.index'])): ?>
        <div x-data="{ open: <?php echo e($groupActive(['buyer.messages.*', 'buyer.notifications.*', 'buyer.tickets.*']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['buyer.messages.*', 'buyer.notifications.*', 'buyer.tickets.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['buyer.messages.*', 'buyer.notifications.*', 'buyer.tickets.*']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-comments sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Communication</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('buyer.messages.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.messages.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Messages</a>
                <a href="<?php echo e(route('buyer.notifications.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.notifications.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Notifications</a>
                <a href="<?php echo e(route('buyer.tickets.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.tickets.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Support Tickets</a>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['supplier.review', 'buyer.reviews.index'])): ?>
        <a href="<?php echo e(route('buyer.reviews.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('buyer.reviews.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('buyer.reviews.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-star sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Reviews</span>
        </a>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['buyer.profile.view', 'buyer.profile.update', 'buyer.profile.edit', 'account.view', 'account.update', 'locations.view', 'locations.manage'])): ?>
        <a href="<?php echo e(route('buyer.profile.edit')); ?>" class="sidebar-menu-item <?php echo e($isActive('buyer.profile.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('buyer.profile.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-building sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Buyer Profile</span>
        </a>
        <?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($account && $account->isOrganization()): ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['members.view', 'members.invite', 'members.update', 'members.remove', 'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign', 'ownership.transfer', 'buyer.members.index'])): ?>
            <div x-data="{ open: <?php echo e($groupActive(['buyer.members.*', 'buyer.invitations.*', 'buyer.roles.*', 'buyer.role-requests.*', 'buyer.ownership.*']) ? 'true' : 'false'); ?> }">
                <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive(['buyer.members.*', 'buyer.invitations.*', 'buyer.roles.*', 'buyer.role-requests.*', 'buyer.ownership.*']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive(['buyer.members.*', 'buyer.invitations.*', 'buyer.roles.*', 'buyer.role-requests.*', 'buyer.ownership.*']) ? '' : 'border-transparent'); ?>">
                    <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Organization</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['members.view', 'buyer.members.index'])): ?>
                        <a href="<?php echo e(route('buyer.members.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.members.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Members</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['members.invite', 'buyer.invitations.index'])): ?>
                        <a href="<?php echo e(route('buyer.invitations.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.invitations.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Invitations</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['roles.view', 'roles.create', 'roles.update', 'roles.assign', 'buyer.roles.index'])): ?>
                        <a href="<?php echo e(route('buyer.roles.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.roles.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Roles &amp; Permissions</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['roles.view', 'roles.assign', 'buyer.role-requests.index'])): ?>
                        <a href="<?php echo e(route('buyer.role-requests.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.role-requests.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Role Requests</a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['ownership.transfer', 'buyer.ownership.index'])): ?>
                        <a href="<?php echo e(route('buyer.ownership.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.ownership.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Ownership</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <div x-data="{ open: <?php echo e($isActive('buyer.settings.*') ? 'true' : 'false'); ?> }">
                <button @click="open = !open" class="sidebar-menu-item <?php echo e($isActive('buyer.settings.*') ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('buyer.settings.*') ? '' : 'border-transparent'); ?>">
                    <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">Settings &amp; Account</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="<?php echo e(route('buyer.settings.security')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.settings.security') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Personal &amp; Security</a>
                    <a href="<?php echo e(route('buyer.settings.dashboard-mode')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.settings.dashboard-mode') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Dashboard Mode</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($account && !$account->isOrganization()): ?>
                        <a href="<?php echo e(route('buyer.settings.conversion')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.settings.conversion') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Convert to Organization</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['account.close', 'buyer.settings.close-account'])): ?>
                        <a href="<?php echo e(route('buyer.settings.close-account')); ?>" class="sidebar-submenu-item <?php echo e($isActive('buyer.settings.close-account') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Close Account</a>
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
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\layouts\partials\buyer\_sidebar.blade.php ENDPATH**/ ?>