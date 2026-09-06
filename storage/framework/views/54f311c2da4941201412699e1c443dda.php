<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rating' => null, 'count' => 0, 'size' => 'compact']));

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

foreach (array_filter((['rating' => null, 'count' => 0, 'size' => 'compact']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rating > 0 && $count > 0): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($size === 'compact'): ?>
        <span class="inline-flex items-center gap-1 text-sm">
            <i class="fa-solid fa-star text-xs" style="color:var(--fe-rating);"></i>
            <span class="font-semibold" style="color:var(--fe-text);"><?php echo e(number_format($rating, 1)); ?></span>
            <span style="color:var(--fe-text-muted);">(<?php echo e($count); ?>)</span>
        </span>
    <?php else: ?>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-0.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star text-sm" style="color:<?php echo e($i <= round($rating) ? 'var(--fe-rating)' : '#E2E8F0'); ?>;"></i>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <span class="text-sm font-semibold" style="color:var(--fe-text);"><?php echo e(number_format($rating, 1)); ?></span>
            <span class="text-sm" style="color:var(--fe-text-muted);">(<?php echo e($count); ?> <?php echo e(Str::plural('review', $count)); ?>)</span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php else: ?>
    <span class="text-xs" style="color:var(--fe-text-subtle);">No reviews yet</span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\rating-summary.blade.php ENDPATH**/ ?>