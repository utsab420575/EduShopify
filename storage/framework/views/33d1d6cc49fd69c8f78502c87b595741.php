


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->short_description): ?>
    <div class="mb-5 p-4 rounded-lg bg-indigo-50/60 border border-indigo-100">
        <p class="text-sm font-semibold text-gray-800 leading-relaxed"><?php echo e($listing->short_description); ?></p>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->description): ?>
    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line"><?php echo e($listing->description); ?></div>
<?php else: ?>
    <p class="text-sm text-gray-400 italic">No description added yet.
        <a href="<?php echo e(route('supplier.catalog.listings.edit', $listing)); ?>" class="text-indigo-600 font-semibold hover:underline not-italic">Add one in Edit Listing →</a>
    </p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php
    $globalTiers = $listing->allTierPrices->whereNull('listing_variant_id')->values();
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($globalTiers->isNotEmpty()): ?>
    <div class="mt-6 pt-5 border-t border-gray-100">
        <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-layer-group text-indigo-500 text-xs"></i>
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Quantity Break / Tier Pricing</h4>
        </div>
        <p class="text-xs text-gray-400 mb-3">Volume pricing for the base product. To modify, use Edit Listing.</p>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                        <th class="px-4 py-2.5 font-semibold">Unit Price</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Discount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $globalTiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $discount = $listing->base_price && $tp->unit_price
                                ? round((1 - ($tp->unit_price / $listing->base_price)) * 100, 0)
                                : null;
                        ?>
                        <tr class="<?php echo e($loop->index % 2 === 0 ? 'bg-white' : 'bg-gray-50/40'); ?>">
                            <td class="px-4 py-2.5 font-medium text-gray-800">
                                <?php echo e(number_format($tp->min_quantity, 0)); ?> &ndash; <?php echo e($tp->max_quantity ? number_format($tp->max_quantity, 0) : '∞'); ?> units
                            </td>
                            <td class="px-4 py-2.5 font-bold text-indigo-700">
                                <?php echo e($tp->currency_code); ?> <?php echo e(number_format($tp->unit_price, 2)); ?>

                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discount !== null && $discount > 0): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                        −<?php echo e($discount); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300">—</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\catalog\listings\partials\preview-overview.blade.php ENDPATH**/ ?>