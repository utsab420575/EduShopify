<?php ($feSocialLinks = collect(config('services.social_links', []))); ?>
<footer class="mt-16 bg-gray-900 text-gray-300">
    <div class="fe-container py-14">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10">
            <div class="col-span-2 md:col-span-2">
                <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-2">
                    <span class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm font-display bg-emerald-500">ES</span>
                    <span class="text-lg font-bold text-white font-display">EduShopify</span>
                </a>
                <p class="mt-4 text-sm leading-relaxed max-w-sm text-gray-400">
                    The B2B education procurement marketplace connecting institutional buyers with verified suppliers through structured RFQ sourcing.
                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feSocialLinks->isNotEmpty()): ?>
                    <div class="flex items-center gap-3 mt-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $feSocialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($url); ?>" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg flex items-center justify-center border border-white/10 text-slate-300 hover:text-white hover:border-white/30" aria-label="<?php echo e($label); ?>">
                                <i class="fa-brands fa-<?php echo e(strtolower($label)); ?>"></i>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white">Marketplace</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="<?php echo e(route('frontend.catalog.index')); ?>" class="hover:text-white transition-colors">Catalog</a></li>
                    <li><a href="<?php echo e(route('frontend.products.index')); ?>" class="hover:text-white transition-colors">Products</a></li>
                    <li><a href="<?php echo e(route('frontend.services.index')); ?>" class="hover:text-white transition-colors">Services</a></li>
                    <li><a href="<?php echo e(route('frontend.suppliers.index')); ?>" class="hover:text-white transition-colors">Suppliers</a></li>
                    <li><a href="<?php echo e(route('frontend.rfqs.index')); ?>" class="hover:text-white transition-colors">RFQ Opportunities</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white">For Buyers</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="<?php echo e(route('frontend.pages.how-it-works')); ?>" class="hover:text-white transition-colors">How It Works</a></li>
                    <li><a href="<?php echo e(route('frontend.handoff.post-rfq')); ?>" class="hover:text-white transition-colors">Post an RFQ</a></li>
                    <li><a href="<?php echo e(route('frontend.suppliers.index')); ?>" class="hover:text-white transition-colors">Find Suppliers</a></li>
                </ul>
                <h4 class="text-sm font-semibold text-white mt-6">For Suppliers</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="<?php echo e(route('register')); ?>" class="hover:text-white transition-colors">Become a Supplier</a></li>
                    <li><a href="<?php echo e(route('frontend.pages.pricing')); ?>" class="hover:text-white transition-colors">Pricing</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white">Company</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="<?php echo e(route('frontend.pages.about')); ?>" class="hover:text-white transition-colors">About</a></li>
                    <li><a href="<?php echo e(route('frontend.pages.contact')); ?>" class="hover:text-white transition-colors">Contact</a></li>
                    <li><a href="<?php echo e(route('frontend.pages.faqs')); ?>" class="hover:text-white transition-colors">FAQs</a></li>
                </ul>
                <h4 class="text-sm font-semibold text-white mt-6">Legal</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="<?php echo e(route('frontend.pages.terms')); ?>" class="hover:text-white transition-colors">Terms</a></li>
                    <li><a href="<?php echo e(route('frontend.pages.privacy')); ?>" class="hover:text-white transition-colors">Privacy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row gap-3 items-center justify-between text-xs text-gray-500">
            <p>&copy; <?php echo e(date('Y')); ?> EduShopify. All rights reserved.</p>
        </div>
    </div>
</footer>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\layouts\partials\_footer.blade.php ENDPATH**/ ?>