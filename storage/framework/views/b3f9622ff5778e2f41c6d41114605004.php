
<?php ($profile = $listing->supplierAccount?->supplierProfile); ?>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->base_price): ?>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-gray-900 tracking-tight"><?php echo e(number_format($listing->base_price, 2)); ?></span>
                <span class="text-sm font-semibold text-gray-500"><?php echo e($listing->currency_code); ?></span>
            </div>
        <?php else: ?>
            <span class="text-xl font-extrabold text-gray-700">Request a Quote</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->min_order_quantity): ?>
            <p class="text-xs text-gray-500 mt-1">MOQ: <?php echo e(rtrim(rtrim((string) $listing->min_order_quantity, '0'), '.')); ?> <?php echo e($listing->unit?->symbol); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <dl class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <dt class="text-gray-500">Product Rating</dt>
                <dd class="flex items-center gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star text-[10px] <?php echo e($i <= round($listing->product_rating) ? 'text-amber-400' : 'text-gray-200'); ?>"></i>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-gray-600 ml-1"><?php echo e(number_format($listing->product_rating, 1)); ?> (<?php echo e($listing->product_reviews_count); ?>)</span>
                </dd>
            </div>
            <div class="flex items-center justify-between text-xs">
                <dt class="text-gray-500">Supplier Rating</dt>
                <dd class="flex items-center gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star text-[10px] <?php echo e($i <= round($profile?->rating ?? 0) ? 'text-amber-400' : 'text-gray-200'); ?>"></i>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-gray-600 ml-1"><?php echo e(number_format($profile?->rating ?? 0, 1)); ?> (<?php echo e($profile?->reviews_count ?? 0); ?>)</span>
                </dd>
            </div>
        </dl>

        <div class="flex flex-col gap-2 mt-4">
            <a href="<?php echo e(route('buyer.rfqs.create', ['listing' => $listing->id])); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg text-center">Request Quotation</a>
            <form method="POST" action="<?php echo e(route('buyer.suppliers.message', $listing->supplierAccount)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Message Supplier</button>
            </form>
            <form method="POST" action="<?php echo e(route('buyer.saved-items.toggle')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="type" value="listing">
                <input type="hidden" name="id" value="<?php echo e($listing->id); ?>">
                <button type="submit" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-bookmark <?php echo e($isSaved ? 'text-red-500' : 'text-gray-300'); ?>"></i> <?php echo e($isSaved ? 'Saved' : 'Save'); ?>

                </button>
            </form>
        </div>
    </div>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Quick Info</h3>
    </div>
    <dl class="divide-y divide-gray-50 text-xs">
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-folder w-3.5 text-gray-400 text-center"></i> Category</dt>
            <dd class="font-semibold text-gray-800 text-right max-w-[55%] truncate"><?php echo e($listing->mainCategory?->name ?? '—'); ?></dd>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->brand): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-regular fa-registered w-3.5 text-gray-400 text-center"></i> Brand</dt>
                <dd class="font-semibold text-gray-800"><?php echo e($listing->brand->name); ?></dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->unit): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-ruler w-3.5 text-gray-400 text-center"></i> Unit</dt>
                <dd class="font-semibold text-gray-800"><?php echo e($listing->unit->symbol ?? $listing->unit->name); ?></dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->min_order_quantity): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-boxes-stacked w-3.5 text-gray-400 text-center"></i> Min. Order</dt>
                <dd class="font-semibold text-gray-800"><?php echo e(number_format($listing->min_order_quantity, 0)); ?> units</dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </dl>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</h3>
    </div>
    <div class="px-5 py-4">
        <a href="<?php echo e(route('buyer.suppliers.show', $listing->supplierAccount)); ?>" class="flex items-center gap-3">
            <img src="<?php echo e($profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5'); ?>" class="w-10 h-10 rounded-lg object-contain bg-white border border-gray-100" alt="">
            <span class="text-sm font-medium text-gray-900"><?php echo e($profile?->display_name); ?></span>
        </a>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\marketplace\products\partials\_sidebar.blade.php ENDPATH**/ ?>