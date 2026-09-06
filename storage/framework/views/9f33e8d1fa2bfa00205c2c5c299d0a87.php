<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tiers', 'currency' => null, 'highlightQuantity' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['tiers', 'currency' => null, 'highlightQuantity' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tiers->isNotEmpty()): ?>
    <div class="overflow-x-auto rounded-xl border" style="border-color:var(--fe-border);">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="background:var(--fe-surface-soft);">
                    <th class="px-4 py-2.5 font-semibold" style="color:var(--fe-text-muted);">Quantity</th>
                    <th class="px-4 py-2.5 font-semibold text-right" style="color:var(--fe-text-muted);">Unit Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isHighlighted = $highlightQuantity && $highlightQuantity >= $tier->min_quantity && ($tier->max_quantity === null || $highlightQuantity <= $tier->max_quantity);
                    ?>
                    <tr class="border-t" style="border-color:var(--fe-border); <?php echo e($isHighlighted ? 'background:var(--fe-primary-soft);' : ''); ?>">
                        <td class="px-4 py-2.5" style="color:var(--fe-text);">
                            <?php echo e(rtrim(rtrim(number_format($tier->min_quantity, 2), '0'), '.')); ?><?php echo e($tier->max_quantity ? ' – '.rtrim(rtrim(number_format($tier->max_quantity, 2), '0'), '.') : '+'); ?>

                        </td>
                        <td class="px-4 py-2.5 text-right font-semibold" style="color:var(--fe-text);">
                            <?php echo e($currency ?? $tier->currency_code); ?> <?php echo e(number_format($tier->unit_price, 2)); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\tier-pricing-table.blade.php ENDPATH**/ ?>