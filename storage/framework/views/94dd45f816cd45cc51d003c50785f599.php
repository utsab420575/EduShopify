<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['listing']));

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

foreach (array_filter((['listing']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $supplierProfile = $listing->supplierAccount?->supplierProfile;
    $isProduct       = $listing->listing_type === 'product';
    $productDetail   = $isProduct ? $listing->productDetail : null;
    $serviceDetail   = ! $isProduct ? $listing->serviceDetail : null;

    // Resolve thumbnail: prefer the explicitly-set primary image media, else
    // fall back to the first item from the already-loaded media collection,
    // else call getFirstMediaUrl() as a last resort (avoids N+1 when the
    // collection is already eager-loaded via ->with('media')).
    $thumbUrl = null;
    if ($listing->primaryImage) {
        $thumbUrl = $listing->primaryImage->getUrl();
    } elseif ($listing->relationLoaded('media') && $listing->media->isNotEmpty()) {
        $first    = $listing->media->where('collection_name', 'gallery')->first()
                    ?? $listing->media->first();
        $thumbUrl = $first?->getUrl();
    } else {
        $thumbUrl = $listing->getFirstMediaUrl('gallery') ?: null;
    }
?>

<div class="fe-card fe-card-hover rounded-2xl overflow-hidden flex flex-col h-full group">
    
    <a href="<?php echo e(route('frontend.listings.show', $listing->slug)); ?>"
       class="block relative overflow-hidden"
       style="background:var(--fe-surface-soft);">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($thumbUrl): ?>
            <div class="relative aspect-[4/3] overflow-hidden">
                <img src="<?php echo e($thumbUrl); ?>"
                     alt="<?php echo e($listing->name); ?>"
                     loading="lazy"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
            </div>
        <?php else: ?>
            <div class="aspect-[4/3] flex items-center justify-center" style="background:var(--fe-surface-soft);">
                <i class="fa-solid <?php echo e($isProduct ? 'fa-box' : 'fa-briefcase'); ?> text-4xl" style="color:var(--fe-text-subtle);"></i>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5 pointer-events-none">
            <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><?php echo e($isProduct ? 'Product' : 'Service'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplierProfile): ?>
                <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => ['variant' => 'verified']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified']); ?>
                    <i class="fa-solid fa-circle-check text-[10px]"></i> Verified
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->is_featured): ?>
            <div class="absolute top-2.5 right-2.5 pointer-events-none">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-900">
                    <i class="fa-solid fa-star text-[9px]"></i> Featured
                </span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="absolute bottom-2.5 right-2.5" style="box-shadow:0 1px 4px rgba(15,23,42,.15);border-radius:9999px;">
            <?php if (isset($component)) { $__componentOriginale5c68880a5ee546c83c58a25fda1def9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5c68880a5ee546c83c58a25fda1def9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.compare-button','data' => ['listing' => $listing]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.compare-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5c68880a5ee546c83c58a25fda1def9)): ?>
<?php $attributes = $__attributesOriginale5c68880a5ee546c83c58a25fda1def9; ?>
<?php unset($__attributesOriginale5c68880a5ee546c83c58a25fda1def9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5c68880a5ee546c83c58a25fda1def9)): ?>
<?php $component = $__componentOriginale5c68880a5ee546c83c58a25fda1def9; ?>
<?php unset($__componentOriginale5c68880a5ee546c83c58a25fda1def9); ?>
<?php endif; ?>
        </div>
    </a>

    
    <div class="p-4 flex flex-col flex-1">
        <p class="text-xs mb-1 truncate" style="color:var(--fe-text-muted);">
            <?php echo e($listing->mainCategory?->name ?? ($isProduct ? ($listing->brand?->name) : ucfirst($serviceDetail?->service_mode ?? ''))); ?>

        </p>

        <a href="<?php echo e(route('frontend.listings.show', $listing->slug)); ?>"
           class="fe-focus-ring text-sm font-semibold fe-line-clamp-2 mb-2 hover:underline leading-snug"
           style="color:var(--fe-text);">
            <?php echo e($listing->name); ?>

        </a>

        
        <div class="mb-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->pricing_type === 'fixed' && $listing->base_price): ?>
                <div class="flex items-baseline gap-1.5 flex-wrap">
                    <span class="text-base font-bold" style="color:var(--fe-text);">
                        <?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->base_price, 2)); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->unit): ?>
                        <span class="text-xs font-normal" style="color:var(--fe-text-muted);">/ <?php echo e($listing->unit->symbol); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price): ?>
                        <span class="text-xs line-through" style="color:var(--fe-text-subtle);">
                            <?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->compare_at_price, 2)); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-sm font-semibold" style="color:var(--fe-primary);">Request Quote</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs mb-3" style="color:var(--fe-text-muted);">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isProduct): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->min_order_quantity): ?>
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-boxes-stacking text-[10px]"></i>
                        MOQ <?php echo e(rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.')); ?>

                        <?php echo e($listing->unit?->symbol); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($productDetail?->stock_status): ?>
                    <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => ['variant' => $productDetail->stock_status === 'in_stock' ? 'success' : ($productDetail->stock_status === 'limited' ? 'warning' : 'neutral')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productDetail->stock_status === 'in_stock' ? 'success' : ($productDetail->stock_status === 'limited' ? 'warning' : 'neutral'))]); ?>
                        <?php echo e(str_replace('_', ' ', ucfirst($productDetail->stock_status))); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($serviceDetail?->lead_time_days): ?>
                    <span><i class="fa-regular fa-clock text-[10px] mr-0.5"></i> <?php echo e($serviceDetail->lead_time_days); ?>d lead</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($serviceDetail?->service_mode): ?>
                    <span><?php echo e(ucfirst($serviceDetail->service_mode)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="mt-auto pt-3 border-t flex items-center justify-between gap-2" style="border-color:var(--fe-border);">
            <div class="min-w-0">
                <p class="text-xs font-medium truncate" style="color:var(--fe-text);">
                    <?php echo e($supplierProfile?->display_name ?? 'Supplier'); ?>

                </p>
                <?php if (isset($component)) { $__componentOriginal42be004482c6a898e71d324bf92e906c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42be004482c6a898e71d324bf92e906c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rating-summary','data' => ['rating' => $supplierProfile?->rating,'count' => $supplierProfile?->reviews_count ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rating-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplierProfile?->rating),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplierProfile?->reviews_count ?? 0)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $attributes = $__attributesOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__attributesOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42be004482c6a898e71d324bf92e906c)): ?>
<?php $component = $__componentOriginal42be004482c6a898e71d324bf92e906c; ?>
<?php unset($__componentOriginal42be004482c6a898e71d324bf92e906c); ?>
<?php endif; ?>
            </div>
            <a href="<?php echo e(route('frontend.listings.show', $listing->slug)); ?>"
               class="fe-focus-ring shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80"
               style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                View
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\marketplace\listing-card.blade.php ENDPATH**/ ?>