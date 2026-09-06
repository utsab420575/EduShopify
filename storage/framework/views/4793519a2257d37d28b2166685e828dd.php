
<div class="space-y-3">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listing->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex items-center justify-between gap-4 p-3 rounded-lg border border-gray-100">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($variant->name); ?></p>
                <div class="flex items-center gap-2 mt-0.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variant->variantAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $va): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="text-[11px] text-gray-500"><?php echo e($va->attribute?->name); ?>: <span class="font-medium text-gray-700"><?php echo e($va->attributeValue?->value ?? $va->custom_value); ?></span></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant->tierPrices->isNotEmpty()): ?>
                    <p class="text-[11px] text-indigo-600 mt-1"><i class="fa-solid fa-layer-group"></i> Volume pricing available</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-semibold text-gray-900"><?php echo e(number_format($variant->price, 2)); ?> <?php echo e($variant->currency_code ?? $listing->currency_code); ?></p>
                <p class="text-[11px] mt-0.5 <?php echo e($variant->stock_status === 'out_of_stock' ? 'text-red-500' : 'text-emerald-600'); ?>">
                    <i class="fa-solid <?php echo e($variant->stock_status === 'out_of_stock' ? 'fa-circle-xmark' : 'fa-circle-check'); ?>"></i>
                    <?php echo e(['in_stock' => 'In Stock', 'limited' => 'Limited Stock', 'on_request' => 'Made to Order', 'out_of_stock' => 'Out of Stock'][$variant->stock_status] ?? 'Available'); ?>

                </p>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\marketplace\products\partials\_variants.blade.php ENDPATH**/ ?>