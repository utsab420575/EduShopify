<?php
    $feUser = auth()->user();
    $feAccount = $feUser?->accountMember?->account;
    $feIsAdmin = $feUser?->isAdmin() ?? false;
    $feIsBuyer = $feAccount?->isBuyer() ?? false;
    $feIsSupplier = $feAccount?->isSupplier() ?? false;
?>
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
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-gray-900/40" @click="close(); document.body.style.overflow = ''"></div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="absolute inset-x-0 top-0 bg-white shadow-xl max-h-screen overflow-y-auto">
        <div class="fe-container py-4">
            <div class="flex items-center justify-between mb-4">
                <span class="text-lg font-bold font-display">Menu</span>
                <button type="button" @click="close(); document.body.style.overflow = ''" class="fe-focus-ring w-10 h-10 rounded-lg flex items-center justify-center text-gray-500" aria-label="Close menu">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="mb-5">
                <?php echo $__env->make('frontend.components.search.global-search', ['size' => 'mobile'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <nav class="space-y-1 mb-5" aria-label="Mobile primary">
                <a href="<?php echo e(route('frontend.catalog.index')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Marketplace</a>
                <a href="<?php echo e(route('frontend.products.index')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Products</a>
                <a href="<?php echo e(route('frontend.services.index')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Services</a>
                <a href="<?php echo e(route('frontend.suppliers.index')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Suppliers</a>
                <a href="<?php echo e(route('frontend.compare.index')); ?>" x-data="compareBadge" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <span><i class="fa-solid fa-arrow-right-arrow-left mr-1.5"></i> Compare</span>
                    <span x-show="count > 0" x-cloak x-text="count" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600"></span>
                </a>
                <a href="<?php echo e(route('frontend.rfqs.index')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Opportunities</a>
                <a href="<?php echo e(route('frontend.pages.pricing')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Pricing</a>
                <a href="<?php echo e(route('frontend.pages.how-it-works')); ?>" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">How It Works</a>

                <div x-data="{ open: false }" class="pt-1">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Categories
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-cloak class="pl-3 space-y-0.5 mt-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $headerCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('frontend.categories.show', $cat->slug)); ?>" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"><?php echo e($cat->name); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <a href="<?php echo e(route('frontend.categories.index')); ?>" class="block px-3 py-2 rounded-lg text-sm font-semibold text-emerald-600">Browse all categories</a>
                    </div>
                </div>
            </nav>

            <div class="border-t border-gray-200 pt-4 space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('frontend.handoff.post-rfq')); ?>" class="block text-center px-4 py-2.5 rounded-md text-sm font-semibold border border-gray-300">Post an RFQ</a>
                    <a href="<?php echo e(route('register')); ?>" class="fe-btn-primary block text-center px-4 py-2.5 rounded-md text-sm font-semibold">Join Free</a>
                    <a href="<?php echo e(route('login')); ?>" class="block text-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600">Log in</a>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feIsAdmin && \Illuminate\Support\Facades\Route::has('admin.dashboard')): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="block text-center px-4 py-2.5 rounded-md text-sm font-semibold border border-gray-300">Admin Dashboard</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feIsBuyer && \Illuminate\Support\Facades\Route::has('buyer.dashboard')): ?>
                        <a href="<?php echo e(route('buyer.dashboard')); ?>" class="block text-center px-4 py-2.5 rounded-md text-sm font-semibold border border-gray-300">Buyer Dashboard</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feIsSupplier && \Illuminate\Support\Facades\Route::has('supplier.dashboard')): ?>
                        <a href="<?php echo e(route('supplier.dashboard')); ?>" class="block text-center px-4 py-2.5 rounded-md text-sm font-semibold border border-gray-300">Supplier Dashboard</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full text-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600">Log out</button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\layouts\partials\_mobile_menu.blade.php ENDPATH**/ ?>