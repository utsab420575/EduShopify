
<div>
    <h1 class="text-lg font-bold text-gray-900"><?php echo e($listing->name); ?></h1>
    <p class="text-sm text-gray-500 mt-1"><?php echo e($listing->mainCategory?->name); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->brand): ?> &middot; <?php echo e($listing->brand->name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->short_description): ?>
    <p class="text-sm text-gray-600 mt-4"><?php echo e($listing->short_description); ?></p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->description): ?>
    <div class="mt-4 pt-4 border-t border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</h3>
        <p class="text-sm text-gray-600 whitespace-pre-line"><?php echo e($listing->description); ?></p>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->tierPrices->isNotEmpty()): ?>
    <div class="mt-4 pt-4 border-t border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tier Pricing</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-xs text-gray-500 uppercase"><th class="text-left py-2">Quantity</th><th class="text-right py-2">Unit Price</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listing->tierPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-2"><?php echo e(rtrim(rtrim((string) $tier->min_quantity, '0'), '.')); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tier->max_quantity): ?> - <?php echo e(rtrim(rtrim((string) $tier->max_quantity, '0'), '.')); ?><?php else: ?>+<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td>
                        <td class="py-2 text-right font-medium"><?php echo e(number_format($tier->unit_price, 2)); ?> <?php echo e($tier->currency_code); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->short_description === null && $listing->description === null && $listing->tierPrices->isEmpty()): ?>
    <p class="text-sm text-gray-400">No additional overview details provided for this product.</p>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\marketplace\products\partials\_overview.blade.php ENDPATH**/ ?>