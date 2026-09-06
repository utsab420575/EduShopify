<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['opportunity']));

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

foreach (array_filter((['opportunity']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $location = collect([$opportunity->delivery_city, $opportunity->delivery_state, $opportunity->delivery_country])->filter()->implode(', ');
    $closesInDays = $opportunity->quotation_deadline ? now()->diffInDays($opportunity->quotation_deadline, false) : null;
?>

<div class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
            <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => ['variant' => 'verified']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified']); ?><span class="w-1.5 h-1.5 rounded-full" style="background:var(--fe-primary);"></span> Open <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
            <span class="text-xs font-mono" style="color:var(--fe-text-subtle);"><?php echo e($opportunity->rfq_number); ?></span>
        </div>
        <a href="<?php echo e(route('frontend.rfqs.show', $opportunity->rfq_number)); ?>" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-text);"><?php echo e($opportunity->title); ?></a>
        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs" style="color:var(--fe-text-muted);">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->category_summary): ?>
                <span><i class="fa-solid fa-tag text-[10px] mr-1"></i><?php echo e(Str::limit($opportunity->category_summary, 40)); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($location): ?>
                <span><i class="fa-solid fa-location-dot text-[10px] mr-1"></i><?php echo e($location); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->item_count): ?>
                <span><i class="fa-solid fa-list text-[10px] mr-1"></i><?php echo e($opportunity->item_count); ?> <?php echo e(Str::plural('item', $opportunity->item_count)); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="flex items-center justify-between sm:flex-col sm:items-end gap-2 shrink-0">
        <div class="text-right">
            <p class="text-xs" style="color:var(--fe-text-muted);">Quotation deadline</p>
            <p class="text-sm font-semibold" style="color:var(--fe-text);">
                <?php echo e($opportunity->quotation_deadline?->format('M j, Y') ?? '—'); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($closesInDays !== null && $closesInDays >= 0): ?>
                    <span class="block text-[11px] font-normal" style="color:var(--fe-warning);">Closes in <?php echo e($closesInDays); ?> <?php echo e(Str::plural('day', $closesInDays)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>
        <a href="<?php echo e(route('frontend.rfqs.show', $opportunity->rfq_number)); ?>" class="fe-focus-ring shrink-0 text-xs font-semibold px-3.5 py-2 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
            View Summary
        </a>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\rfq-card.blade.php ENDPATH**/ ?>