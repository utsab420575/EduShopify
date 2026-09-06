<?php
    $isActive = fn (string $pattern) => request()->routeIs($pattern);
    $groupActive = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
    $u = auth()->user();

    $groups = [
        'users-accounts' => ['admin.users.*', 'admin.accounts.*', 'admin.buyers.*', 'admin.suppliers.*', 'admin.account-members.*', 'admin.capabilities.*', 'admin.conversions.*', 'admin.closures.*'],
        'catalog' => ['admin.catalog.*'],
        'achievements' => ['admin.achievements.*', 'admin.achievement-requests.*', 'admin.certification-requests.*'],
        'blog' => ['admin.blog.*'],
        'procurement' => ['admin.procurement.*'],
        'billing' => ['admin.billing.*'],
        'communication' => ['admin.communication.*'],
        'moderation' => ['admin.reviews.*'],
        'access-control' => ['admin.access-control.*'],
        'system' => ['admin.system.*'],
    ];
?>

<aside class="w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0 bg-white"
       style="border-color:var(--sidebar-border)"
       :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">

    <div class="h-20 flex items-center px-4 border-b shrink-0" style="border-color:var(--sidebar-border)">
        <a href="<?php echo e(route('home')); ?>" class="flex items-center min-w-0">
            <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm shrink-0">ES</div>
            <div class="ml-3 leading-tight min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">EduShopify</p>
                <p class="text-[11px] text-gray-400">Admin Panel</p>
            </div>
        </a>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-menu-item <?php echo e($isActive('admin.dashboard') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('admin.dashboard') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.accounts.view')): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['users-accounts']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['users-accounts']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['users-accounts']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Users &amp; Accounts</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.users.view')): ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.users.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Users</a>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.accounts.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.accounts.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Accounts</a>
                <a href="<?php echo e(route('admin.buyers.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.buyers.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Buyers</a>
                <a href="<?php echo e(route('admin.suppliers.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.suppliers.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Suppliers</a>
                <a href="<?php echo e(route('admin.account-members.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.account-members.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Account Members</a>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.capabilities.review')): ?>
                <a href="<?php echo e(route('admin.capabilities.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.capabilities.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Capabilities</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.conversions.review')): ?>
                <a href="<?php echo e(route('admin.conversions.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.conversions.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Account Conversions</a>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.closures.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.closures.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Closure / Deletion Queue</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($u && ! empty($approvalQueues ?? [])): ?>
        <div x-data="{ open: <?php echo e($isActive('admin.approvals.*') ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($isActive('admin.approvals.*') ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('admin.approvals.*') ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-clipboard-check sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Approval Center</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($approvalQueueTotal ?? 0) > 0): ?>
                    <span id="sidebar-approval-badge-total" class="text-[10px] font-semibold text-white rounded-full px-1.5 py-0.5 mr-1" style="background:#ef4444"><?php echo e($approvalQueueTotal); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $approvalQueues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('admin.approvals.show', $queue['key'])); ?>" class="sidebar-submenu-item <?php echo e(request()->route('queue') === $queue['key'] ? 'active' : ''); ?> flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-md">
                        <span class="truncate"><?php echo e($queue['label']); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($queue['count'] > 0): ?>
                            <span id="sidebar-approval-badge-<?php echo e($queue['key']); ?>" class="text-[10px] font-bold rounded-full px-1.5 py-0.5 shrink-0" style="background:#FEF3C7;color:#92400E;"><?php echo e($queue['count']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['platform.categories.manage', 'platform.attributes.manage', 'platform.brands.manage', 'platform.listings.moderate'])): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['catalog']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['catalog']) ? 'active' : 'border-transparent'); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4">
                <i class="fa-solid fa-layer-group sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Catalog &amp; Taxonomy</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.categories.manage')): ?>
                <a href="<?php echo e(route('admin.catalog.categories.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.categories.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Categories</a>
                <a href="<?php echo e(route('admin.catalog.builder.categories')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.builder.*') ? 'active' : ''); ?> flex items-center gap-2 px-3 py-2 text-sm rounded-md">
                    <i class="fa-solid fa-folder-plus text-[11px]"></i> Category Builder
                </a>
                <a href="<?php echo e(route('admin.catalog.buyer-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.buyer-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Buyer Types</a>
                <a href="<?php echo e(route('admin.catalog.supplier-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.supplier-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Supplier Types</a>
                <a href="<?php echo e(route('admin.catalog.document-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.document-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Document Types</a>
                <a href="<?php echo e(route('admin.catalog.document-type-enables.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.document-type-enables.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Document Enables</a>
                <a href="<?php echo e(route('admin.catalog.exhibitions.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.exhibitions.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Exhibitions</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.attributes.manage')): ?>
                <a href="<?php echo e(route('admin.catalog.attribute-groups.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.attribute-groups.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Attribute Groups</a>
                <a href="<?php echo e(route('admin.catalog.attributes.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.attributes.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Attributes</a>
                <a href="<?php echo e(route('admin.catalog.units.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.units.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Pricing Units</a>
                <a href="<?php echo e(route('admin.catalog.currencies.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.currencies.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Currencies</a>
                <a href="<?php echo e(route('admin.catalog.input-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.input-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Input Types</a>
                <a href="<?php echo e(route('admin.catalog.pricing-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.pricing-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Pricing Types</a>
                <a href="<?php echo e(route('admin.catalog.sales-modes.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.sales-modes.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Sales Modes</a>
                <a href="<?php echo e(route('admin.catalog.listing-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.listing-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Listing Types</a>
                <a href="<?php echo e(route('admin.catalog.visibility-types.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.visibility-types.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Visibility Types</a>
                <a href="<?php echo e(route('admin.catalog.icon-libraries.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.icon-libraries.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Icon Libraries</a>
                <a href="<?php echo e(route('admin.catalog.icons.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.icons.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Icons</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.brands.manage')): ?>
                <a href="<?php echo e(route('admin.catalog.brands.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.brands.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Brands</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.listings.moderate')): ?>
                <a href="<?php echo e(route('admin.catalog.listings.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.catalog.listings.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Listings</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['platform.achievements.manage', 'platform.achievements.review', 'platform.certifications.review'])): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['achievements']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['achievements']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['achievements']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-trophy sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Achievements</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($achievementsPendingCount ?? 0) > 0): ?>
                    <span class="text-[10px] font-semibold text-white rounded-full px-1.5 py-0.5 mr-1" style="background:#ef4444"><?php echo e($achievementsPendingCount); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.achievements.manage')): ?>
                <a href="<?php echo e(route('admin.achievements.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.achievements.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Achievement List</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.achievements.review')): ?>
                <a href="<?php echo e(route('admin.achievement-requests.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.achievement-requests.*') ? 'active' : ''); ?> flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-md">
                    <span>Achievement Requests</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($achievementRequestsPending ?? 0) > 0): ?>
                        <span class="text-[10px] font-bold rounded-full px-1.5 py-0.5 shrink-0" style="background:#FEF3C7;color:#92400E;"><?php echo e($achievementRequestsPending); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.certifications.review')): ?>
                <a href="<?php echo e(route('admin.certification-requests.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.certification-requests.*') ? 'active' : ''); ?> flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-md">
                    <span>Certification Requests</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($certificationRequestsPending ?? 0) > 0): ?>
                        <span class="text-[10px] font-bold rounded-full px-1.5 py-0.5 shrink-0" style="background:#FEF3C7;color:#92400E;"><?php echo e($certificationRequestsPending); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['platform.blog.manage', 'platform.blog.review'])): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['blog']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['blog']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['blog']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-blog sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Blog</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($blogPendingCount ?? 0) > 0): ?>
                    <span class="text-[10px] font-semibold text-white rounded-full px-1.5 py-0.5 mr-1" style="background:#ef4444"><?php echo e($blogPendingCount); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.blog.manage')): ?>
                <a href="<?php echo e(route('admin.blog.posts.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.blog.posts.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Blog Create</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.blog.review')): ?>
                <a href="<?php echo e(route('admin.blog.approval.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.blog.approval.*') ? 'active' : ''); ?> flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-md">
                    <span>Blog Approval</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($blogPendingCount ?? 0) > 0): ?>
                        <span class="text-[10px] font-bold rounded-full px-1.5 py-0.5 shrink-0" style="background:#FEF3C7;color:#92400E;"><?php echo e($blogPendingCount); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.rfqs.moderate')): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['procurement']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['procurement']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['procurement']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-file-signature sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Procurement Oversight</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('admin.procurement.rfqs.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.procurement.rfqs.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">RFQs</a>
                <a href="<?php echo e(route('admin.procurement.quotations.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.procurement.quotations.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Quotations</a>
                <a href="<?php echo e(route('admin.procurement.awards.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.procurement.awards.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Awards</a>
                <a href="<?php echo e(route('admin.procurement.purchase-orders.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.procurement.purchase-orders.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Purchase Orders</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.subscriptions.manage')): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['billing']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['billing']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['billing']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-credit-card sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Subscription &amp; Billing</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('admin.billing.plans.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.billing.plans.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Subscription Plans</a>
                <a href="<?php echo e(route('admin.billing.subscriptions.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.billing.subscriptions.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Supplier Subscriptions</a>
                <a href="<?php echo e(route('admin.billing.payments.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.billing.payments.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Subscription Payments</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.communication.manage')): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['communication']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['communication']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['communication']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-comments sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Communication</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('admin.communication.conversations.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.communication.conversations.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Conversations</a>
                <a href="<?php echo e(route('admin.communication.inquiries.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.communication.inquiries.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Contact Inquiries</a>
            </div>
        </div>
        <?php endif; ?>

        <a href="<?php echo e(route('admin.notifications.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('admin.notifications.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('admin.notifications.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-regular fa-bell sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Notifications</span>
        </a>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.reviews.moderate')): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['moderation']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['moderation']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['moderation']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-star sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Reviews &amp; Moderation</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('admin.reviews.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.reviews.index') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Reviews</a>
                <a href="<?php echo e(route('admin.reviews.replies.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.reviews.replies.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Supplier Replies</a>
                <a href="<?php echo e(route('admin.reviews.reports.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.reviews.reports.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Review Reports</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.tickets.manage')): ?>
        <a href="<?php echo e(route('admin.tickets.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('admin.tickets.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('admin.tickets.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-life-ring sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Support</span>
        </a>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.access_control.manage')): ?>
        <div x-data="{ open: <?php echo e($groupActive($groups['access-control']) ? 'true' : 'false'); ?> }">
            <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['access-control']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['access-control']) ? '' : 'border-transparent'); ?>">
                <i class="fa-solid fa-shield-halved sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Roles &amp; Permission</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="<?php echo e(route('admin.access-control.permissions.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.permissions.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">All Permission</a>
                <a href="<?php echo e(route('admin.access-control.roles.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.roles.index') || $isActive('admin.access-control.roles.create') || $isActive('admin.access-control.roles.edit') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">All Roles</a>
                <a href="<?php echo e(route('admin.access-control.roles-in-permission.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.roles-in-permission.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">All Roles in Permission</a>
                <a href="<?php echo e(route('admin.access-control.route-permissions.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.route-permissions.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Route Discovery</a>
                <a href="<?php echo e(route('admin.access-control.user-roles.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.user-roles.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">User Role Assignment</a>
                <a href="<?php echo e(route('admin.access-control.audit-logs.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.audit-logs.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Permission Audit Logs</a>
                <a href="<?php echo e(route('admin.access-control.role-requests.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.access-control.role-requests.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Account Role Requests</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.settings.manage')): ?>
        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <div x-data="{ open: <?php echo e($groupActive($groups['system']) ? 'true' : 'false'); ?> }">
                <button @click="open = !open" class="sidebar-menu-item <?php echo e($groupActive($groups['system']) ? 'active' : ''); ?> w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($groupActive($groups['system']) ? '' : 'border-transparent'); ?>">
                    <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                    <span class="ml-3 flex-1 text-sm font-medium text-left">System &amp; Settings</span>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                </button>
                <div class="sidebar-submenu ml-8" :class="open && 'open'">
                    <a href="<?php echo e(route('admin.system.settings.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.system.settings.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">General Settings</a>
                    <a href="<?php echo e(route('admin.system.theme.edit')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.system.theme.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Appearance / Theme</a>
                    <a href="<?php echo e(route('admin.system.geography.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.system.geography.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Geography</a>
                    <a href="<?php echo e(route('admin.system.languages.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.system.languages.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Languages</a>
                    <a href="<?php echo e(route('admin.system.jobs.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.system.jobs.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">Failed Jobs</a>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.system.deploy')): ?>
                    <a href="<?php echo e(route('admin.system.deploy.index')); ?>" class="sidebar-submenu-item <?php echo e($isActive('admin.system.deploy.*') ? 'active' : ''); ?> block px-3 py-2 text-sm rounded-md">GitHub Deploy</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.activity_logs.view')): ?>
        <a href="<?php echo e(route('admin.system.audit.index')); ?>" class="sidebar-menu-item <?php echo e($isActive('admin.system.audit.*') ? 'active' : ''); ?> flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 <?php echo e($isActive('admin.system.audit.*') ? '' : 'border-transparent'); ?>">
            <i class="fa-solid fa-clock-rotate-left sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Audit Log</span>
        </a>
        <?php endif; ?>

    </nav>

    <div class="p-3 border-t shrink-0" style="border-color:var(--sidebar-border)">
        <div class="flex items-center px-2 py-2 rounded-lg" style="background:var(--theme-primary-soft)">
            <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($user->name)); ?>&background=4f46e5&color=fff" class="w-8 h-8 rounded-full" alt="">
            <div class="ml-2 leading-tight min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate"><?php echo e($user->name); ?></p>
                <p class="text-[10px] text-gray-500 truncate"><?php echo e($user->roles->first()?->display_name ?? 'Admin'); ?></p>
            </div>
        </div>
    </div>
</aside>
<?php /**PATH C:\laragon\www\edushopify\resources\views/backend/layouts/partials/admin/_sidebar.blade.php ENDPATH**/ ?>