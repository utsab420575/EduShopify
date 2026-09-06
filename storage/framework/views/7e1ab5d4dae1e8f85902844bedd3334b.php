<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['category']));

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

foreach (array_filter((['category']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a href="<?php echo e(route('frontend.categories.show', $category->slug)); ?>" class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col items-center text-center gap-3">
    <span class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
        <i class="fa-solid <?php echo e($category->icon ?: 'fa-shapes'); ?> text-lg"></i>
    </span>
    <div>
        <p class="text-sm font-semibold" style="color:var(--fe-text);"><?php echo e($category->name); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($category->public_listing_count)): ?>
            <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);"><?php echo e($category->public_listing_count); ?> <?php echo e(Str::plural('listing', $category->public_listing_count)); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</a>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\category-card.blade.php ENDPATH**/ ?>