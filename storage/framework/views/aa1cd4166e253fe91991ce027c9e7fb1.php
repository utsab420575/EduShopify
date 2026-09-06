<?php $__env->startSection('title', $listing->name.' — EduShopify'); ?>
<?php $__env->startSection('meta_description', Str::limit(strip_tags($listing->short_description ?? $listing->description ?? $listing->name), 155)); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* ── Gallery ───────────────────────────────────────────────── */
    .pdp-thumb { transition: border-color .2s, opacity .2s; }
    .pdp-thumb.active { border-color: var(--fe-primary) !important; opacity: 1; }
    .pdp-thumb:not(.active) { opacity: .55; }
    .pdp-thumb:not(.active):hover { opacity: 1; }

    /* ── Lightbox ──────────────────────────────────────────────── */
    #pdp-lightbox { display:none !important; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,.88); backdrop-filter:blur(6px);
        align-items:center; justify-content:center; }
    #pdp-lightbox.open { display:flex !important; }
    #pdp-lightbox img { max-height:88vh; max-width:90vw; object-fit:contain;
        border-radius:12px; box-shadow:0 24px 64px rgba(0,0,0,.5); }
    .lb-arrow { position:absolute; top:50%; transform:translateY(-50%);
        background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
        backdrop-filter:blur(8px); color:#fff; width:48px; height:48px;
        border-radius:50%; display:flex; align-items:center; justify-content:center;
        cursor:pointer; transition:background .2s; font-size:18px; }
    .lb-arrow:hover { background:rgba(255,255,255,.25); }
    .lb-arrow.prev { left:20px; }
    .lb-arrow.next { right:20px; }
    #pdp-lightbox .lb-close { position:absolute; top:16px; right:16px;
        background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
        backdrop-filter:blur(8px); color:#fff; width:40px; height:40px;
        border-radius:50%; display:flex; align-items:center; justify-content:center;
        cursor:pointer; font-size:16px; transition:background .2s; }
    #pdp-lightbox .lb-close:hover { background:rgba(255,255,255,.25); }
    #pdp-lightbox .lb-counter { position:absolute; bottom:18px; left:50%;
        transform:translateX(-50%); color:rgba(255,255,255,.8); font-size:13px;
        background:rgba(0,0,0,.4); px:12px; padding:4px 16px; border-radius:99px; }

    /* ── Tabs ──────────────────────────────────────────────────── */
    .pdp-tab-btn { border-bottom: 2px solid transparent; color: var(--fe-text-muted); }
    .pdp-tab-btn.active { border-bottom-color: var(--fe-primary); color: var(--fe-primary); font-weight:600; }

    /* ── Spec table ────────────────────────────────────────────── */
    .spec-row:nth-child(even) { background: var(--fe-surface-soft); }

    /* ── Sticky purchase panel ─────────────────────────────────── */
    @media (min-width: 1024px) {
        .pdp-sticky { position: sticky; top: 80px; }
        /* Ensure the parent grid row allows sticky children */
        .pdp-grid-row { align-items: start; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isProduct      = $listing->listing_type === 'product';
    $supplierProfile = $listing->supplierAccount?->supplierProfile;
    $globalTiers    = $listing->allTierPrices->whereNull('listing_variant_id');
    $hasVariants    = $isProduct && $listing->variants->isNotEmpty();
    $hasAnyTiers    = $listing->allTierPrices->isNotEmpty();

    // Build gallery — primary image first
    $gallery = $listing->getMedia('gallery');
    $primaryMediaId = $listing->primary_image_media_id;
    if ($primaryMediaId && $gallery->count() > 1) {
        $gallery = $gallery->sortByDesc(fn($m) => $m->id == $primaryMediaId)->values();
    }
    $galleryUrls = $gallery->map(fn($m) => $m->getUrl())->values()->toArray();
    $firstUrl    = $galleryUrls[0] ?? null;
    $galleryCount = count($galleryUrls);
?>


<div id="pdp-lightbox" role="dialog" aria-modal="true" aria-label="Product image lightbox" style="display:none;">
    <button class="lb-close" id="lb-close" title="Close (Esc)"><i class="fa-solid fa-xmark"></i></button>
    <button class="lb-arrow prev" id="lb-prev" title="Previous"><i class="fa-solid fa-chevron-left"></i></button>
    <img id="lb-img" src="" alt="<?php echo e($listing->name); ?>" loading="lazy">
    <button class="lb-arrow next" id="lb-next" title="Next"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="lb-counter"><span id="lb-cur">1</span> / <span id="lb-total"><?php echo e($galleryCount); ?></span></div>
</div>

<div class="fe-container py-6 sm:py-8">
    
    <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => [
        ($isProduct ? 'Products' : 'Services') => route($isProduct ? 'frontend.products.index' : 'frontend.services.index'),
        $listing->name => null,
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ($isProduct ? 'Products' : 'Services') => route($isProduct ? 'frontend.products.index' : 'frontend.services.index'),
        $listing->name => null,
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e)): ?>
<?php $attributes = $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e; ?>
<?php unset($__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e)): ?>
<?php $component = $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e; ?>
<?php unset($__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e); ?>
<?php endif; ?>

    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-4 pdp-grid-row">

        
        <div class="lg:col-span-5 xl:col-span-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstUrl): ?>
                <div class="flex gap-3" x-data>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryCount > 1): ?>
                        <div class="hidden sm:flex flex-col gap-2 w-16 shrink-0" id="thumb-strip">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $galleryUrls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    type="button"
                                    onclick="pdpSetImage(<?php echo e($idx); ?>)"
                                    id="thumb-<?php echo e($idx); ?>"
                                    class="pdp-thumb <?php echo e($idx === 0 ? 'active' : ''); ?> aspect-square rounded-xl border-2 overflow-hidden bg-white shrink-0 focus:outline-none"
                                    style="border-color:var(--fe-border);"
                                    title="Photo <?php echo e($idx + 1); ?>">
                                    <img src="<?php echo e($url); ?>" alt="<?php echo e($listing->name); ?> <?php echo e($idx + 1); ?>"
                                         class="w-full h-full object-cover">
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="flex-1 relative">
                        
                        <div class="relative rounded-2xl border overflow-hidden bg-white group cursor-zoom-in"
                             style="border-color:var(--fe-border); aspect-ratio:1/1;"
                             onclick="pdpOpenLightbox(currentPdpIdx)">
                            <img id="pdp-main-img"
                                 src="<?php echo e($firstUrl); ?>"
                                 alt="<?php echo e($listing->name); ?>"
                                 class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105">

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryCount > 1): ?>
                                <button type="button"
                                        onclick="event.stopPropagation(); pdpSetImage(currentPdpIdx - 1)"
                                        class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-sm transition-all opacity-0 group-hover:opacity-100"
                                        style="background:rgba(255,255,255,.9);box-shadow:0 2px 8px rgba(0,0,0,.12);color:var(--fe-text);"
                                        title="Previous">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button"
                                        onclick="event.stopPropagation(); pdpSetImage(currentPdpIdx + 1)"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-sm transition-all opacity-0 group-hover:opacity-100"
                                        style="background:rgba(255,255,255,.9);box-shadow:0 2px 8px rgba(0,0,0,.12);color:var(--fe-text);"
                                        title="Next">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>

                                
                                <div class="absolute bottom-2.5 right-3 pointer-events-none">
                                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-black/45 text-white backdrop-blur-sm">
                                        <span id="pdp-counter-cur">1</span> / <?php echo e($galleryCount); ?>

                                    </span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <div class="absolute top-3 right-3 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-black/40 text-white backdrop-blur-sm">
                                    <i class="fa-solid fa-magnifying-glass-plus text-[9px] mr-0.5"></i> Click to zoom
                                </span>
                            </div>
                        </div>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryCount > 1): ?>
                            <div class="sm:hidden flex gap-2 mt-2 overflow-x-auto pb-1" id="thumb-strip-mobile">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $galleryUrls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button"
                                            onclick="pdpSetImage(<?php echo e($idx); ?>)"
                                            id="thumb-m-<?php echo e($idx); ?>"
                                            class="pdp-thumb <?php echo e($idx === 0 ? 'active' : ''); ?> aspect-square rounded-xl border-2 overflow-hidden bg-white shrink-0 w-14 focus:outline-none"
                                            style="border-color:var(--fe-border);">
                                        <img src="<?php echo e($url); ?>" alt="" class="w-full h-full object-cover">
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="rounded-2xl border flex items-center justify-center"
                     style="aspect-ratio:1/1;border-color:var(--fe-border);background:var(--fe-surface-soft);">
                    <div class="text-center">
                        <i class="fa-solid <?php echo e($isProduct ? 'fa-box' : 'fa-briefcase'); ?> text-6xl mb-3"
                           style="color:var(--fe-text-subtle);"></i>
                        <p class="text-sm" style="color:var(--fe-text-subtle);">No image uploaded</p>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="lg:col-span-4 xl:col-span-5">
            
            <div class="flex items-center gap-2 mb-3 flex-wrap">
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
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->is_featured): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-star text-[10px]"></i> Featured
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <h1 class="text-2xl sm:text-[26px] font-bold tracking-tight leading-snug"
                style="font-family:var(--font-display);color:var(--fe-text);">
                <?php echo e($listing->name); ?>

            </h1>

            
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm" style="color:var(--fe-text-muted);">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->brand): ?>
                    <span>Brand: <strong style="color:var(--fe-text);"><?php echo e($listing->brand->name); ?></strong></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->sku): ?>
                    <span>SKU: <code class="text-xs font-mono"><?php echo e($listing->sku); ?></code></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->mainCategory): ?>
                    <span><?php echo e($listing->mainCategory->name); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="mt-3 flex items-center gap-1.5">
                <span class="text-xs font-semibold" style="color:var(--fe-text-muted);">Product Rating:</span>
                <?php if (isset($component)) { $__componentOriginal42be004482c6a898e71d324bf92e906c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42be004482c6a898e71d324bf92e906c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rating-summary','data' => ['rating' => $listing->product_rating,'count' => $listing->product_reviews_count ?? 0,'size' => 'full']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rating-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->product_rating),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->product_reviews_count ?? 0),'size' => 'full']); ?>
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

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->short_description): ?>
                <p class="mt-4 text-sm leading-relaxed" style="color:var(--fe-text-muted);">
                    <?php echo e($listing->short_description); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <hr class="my-5" style="border-color:var(--fe-border);">

            
            <div class="mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->pricing_type === 'fixed' && $listing->base_price): ?>
                    <div class="flex items-baseline gap-3 flex-wrap">
                        <span class="text-3xl font-bold" style="color:var(--fe-text);">
                            <?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->base_price, 2)); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->unit): ?>
                            <span class="text-sm" style="color:var(--fe-text-muted);">per <?php echo e($listing->unit->symbol); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price): ?>
                            <span class="text-lg line-through" style="color:var(--fe-text-subtle);">
                                <?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->compare_at_price, 2)); ?>

                            </span>
                            <span class="text-sm font-semibold px-2 py-0.5 rounded-lg" style="background:var(--fe-success-soft,#d1fae5);color:#065f46;">
                                Save <?php echo e(number_format((($listing->compare_at_price - $listing->base_price) / $listing->compare_at_price) * 100)); ?>%
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->sales_mode === 'rfq_only'): ?>
                        <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Price shown is indicative. Submit an RFQ for confirmed pricing.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold" style="color:var(--fe-primary);">Request for Quote</span>
                        <i class="fa-solid fa-file-invoice-dollar text-sm" style="color:var(--fe-primary);"></i>
                    </div>
                    <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Pricing is negotiated per order. Submit an RFQ to receive a formal quote.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex flex-wrap gap-2 mb-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isProduct && $listing->productDetail?->stock_status): ?>
                    <?php $ss = $listing->productDetail->stock_status; ?>
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <span class="w-2 h-2 rounded-full <?php echo e($ss === 'in_stock' ? 'bg-green-500' : ($ss === 'limited' ? 'bg-amber-500' : 'bg-red-400')); ?>"></span>
                        <?php echo e(str_replace('_', ' ', ucfirst($ss))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->min_order_quantity): ?>
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <i class="fa-solid fa-boxes-stacking text-[11px]" style="color:var(--fe-text-muted);"></i>
                        MOQ: <?php echo e(rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.')); ?><?php echo e($listing->unit ? ' '.$listing->unit->symbol : ''); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isProduct && $listing->productDetail?->lead_time_days): ?>
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <i class="fa-regular fa-clock text-[11px]" style="color:var(--fe-text-muted);"></i>
                        <?php echo e($listing->productDetail->lead_time_days); ?>-day lead time
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isProduct && $listing->serviceDetail?->service_mode): ?>
                    <div class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg"
                         style="background:var(--fe-surface-soft);color:var(--fe-text);">
                        <i class="fa-solid fa-display text-[11px]" style="color:var(--fe-text-muted);"></i>
                        <?php echo e(ucfirst($listing->serviceDetail->service_mode)); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasVariants): ?>
                <div class="mb-5">
                    <?php if (isset($component)) { $__componentOriginal6bf804ad17bc4cfe757324f45df8c2aa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6bf804ad17bc4cfe757324f45df8c2aa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.variant-selector','data' => ['variants' => $listing->variants]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.variant-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variants' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->variants)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6bf804ad17bc4cfe757324f45df8c2aa)): ?>
<?php $attributes = $__attributesOriginal6bf804ad17bc4cfe757324f45df8c2aa; ?>
<?php unset($__attributesOriginal6bf804ad17bc4cfe757324f45df8c2aa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6bf804ad17bc4cfe757324f45df8c2aa)): ?>
<?php $component = $__componentOriginal6bf804ad17bc4cfe757324f45df8c2aa; ?>
<?php unset($__componentOriginal6bf804ad17bc4cfe757324f45df8c2aa); ?>
<?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasAnyTiers): ?>
                <div class="mb-5">
                    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">
                        <i class="fa-solid fa-tags text-xs mr-1" style="color:var(--fe-primary);"></i>
                        Volume Discount Tiers
                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($globalTiers->isNotEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginal8662a1601596facc7e95072a6bf8620e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8662a1601596facc7e95072a6bf8620e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.tier-pricing-table','data' => ['tiers' => $globalTiers,'currency' => $listing->currency_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.tier-pricing-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tiers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($globalTiers),'currency' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->currency_code)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8662a1601596facc7e95072a6bf8620e)): ?>
<?php $attributes = $__attributesOriginal8662a1601596facc7e95072a6bf8620e; ?>
<?php unset($__attributesOriginal8662a1601596facc7e95072a6bf8620e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8662a1601596facc7e95072a6bf8620e)): ?>
<?php $component = $__componentOriginal8662a1601596facc7e95072a6bf8620e; ?>
<?php unset($__componentOriginal8662a1601596facc7e95072a6bf8620e); ?>
<?php endif; ?>
                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal8662a1601596facc7e95072a6bf8620e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8662a1601596facc7e95072a6bf8620e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.tier-pricing-table','data' => ['tiers' => $listing->allTierPrices,'currency' => $listing->currency_code]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.tier-pricing-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tiers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->allTierPrices),'currency' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->currency_code)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8662a1601596facc7e95072a6bf8620e)): ?>
<?php $attributes = $__attributesOriginal8662a1601596facc7e95072a6bf8620e; ?>
<?php unset($__attributesOriginal8662a1601596facc7e95072a6bf8620e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8662a1601596facc7e95072a6bf8620e)): ?>
<?php $component = $__componentOriginal8662a1601596facc7e95072a6bf8620e; ?>
<?php unset($__componentOriginal8662a1601596facc7e95072a6bf8620e); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="flex flex-col gap-2 lg:hidden mt-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('buyer.rfqs.create', ['listing' => $listing->id])); ?>"
                       class="fe-btn-primary fe-focus-ring block text-center px-4 py-3 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('frontend.handoff.request-quote-listing', $listing->slug)); ?>"
                       class="fe-btn-primary fe-focus-ring block text-center px-4 py-3 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="button" @click="$dispatch('open-inquiry-listing')"
                        class="fe-focus-ring block w-full text-center px-4 py-3 rounded-xl text-sm font-semibold border"
                        style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                    <i class="fa-regular fa-comment-dots mr-1.5"></i> Contact Supplier
                </button>
                <?php if (isset($component)) { $__componentOriginale5c68880a5ee546c83c58a25fda1def9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5c68880a5ee546c83c58a25fda1def9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.compare-button','data' => ['listing' => $listing,'style' => 'text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.compare-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing),'style' => 'text']); ?>
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

        </div>

        
        <div class="hidden lg:block lg:col-span-3">
            <div class="pdp-sticky space-y-4">
                
                <div class="fe-card rounded-2xl p-5 space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->pricing_type === 'fixed' && $listing->base_price): ?>
                        <div>
                            <p class="text-2xl font-bold" style="color:var(--fe-text);">
                                <?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->base_price, 2)); ?>

                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->unit): ?>
                                <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);">per <?php echo e($listing->unit->symbol); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price): ?>
                                <p class="text-sm line-through mt-0.5" style="color:var(--fe-text-subtle);">
                                    <?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->compare_at_price, 2)); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div>
                            <p class="text-lg font-bold" style="color:var(--fe-primary);">Price on Request</p>
                            <p class="text-xs mt-0.5" style="color:var(--fe-text-muted);">Submit an RFQ for pricing</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="space-y-2 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isProduct && $listing->productDetail?->stock_status): ?>
                            <div class="flex justify-between">
                                <span style="color:var(--fe-text-muted);">Availability</span>
                                <span class="font-medium" style="color:var(--fe-text);">
                                    <?php echo e(str_replace('_', ' ', ucfirst($listing->productDetail->stock_status))); ?>

                                </span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->min_order_quantity): ?>
                            <div class="flex justify-between">
                                <span style="color:var(--fe-text-muted);">Min. Order</span>
                                <span class="font-medium" style="color:var(--fe-text);">
                                    <?php echo e(rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.')); ?>

                                    <?php echo e($listing->unit?->symbol); ?>

                                </span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isProduct && $listing->productDetail?->lead_time_days): ?>
                            <div class="flex justify-between">
                                <span style="color:var(--fe-text-muted);">Lead Time</span>
                                <span class="font-medium" style="color:var(--fe-text);"><?php echo e($listing->productDetail->lead_time_days); ?> days</span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <hr style="border-color:var(--fe-border);">

                    
                    <div class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('buyer.rfqs.create', ['listing' => $listing->id])); ?>"
                               class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-xl text-sm font-semibold">
                                <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('frontend.handoff.request-quote-listing', $listing->slug)); ?>"
                               class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-xl text-sm font-semibold">
                                <i class="fa-solid fa-file-invoice mr-1.5"></i> Request Quotation
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <button type="button" @click="$dispatch('open-inquiry-listing')"
                                class="group fe-focus-ring block w-full text-center px-4 py-2.5 rounded-xl text-sm font-semibold border transition-all duration-200 ease-in-out cursor-pointer shadow-xs bg-white text-slate-700 border-slate-300 hover:bg-slate-50 hover:border-slate-400 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 active:bg-slate-100">
                            <i class="fa-regular fa-comment-dots mr-1.5 transition-transform duration-200 group-hover:scale-110"></i> Contact Supplier
                        </button>

                        <div x-data="{
                            isSaved: <?php echo e($isSaved ? 'true' : 'false'); ?>,
                            loading: false,
                            async saveListing() {
                                <?php if(auth()->guard()->guest()): ?>
                                    window.location.href = '<?php echo e(route('frontend.handoff.save-listing', $listing->slug)); ?>';
                                    return;
                                <?php endif; ?>

                                if (this.loading) return;
                                this.loading = true;

                                try {
                                    const res = await fetch('<?php echo e(route('buyer.saved-items.toggle')); ?>', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                                        },
                                        body: JSON.stringify({
                                            type: 'listing',
                                            id: <?php echo e($listing->id); ?>,
                                            action: 'save'
                                        })
                                    });

                                    const data = await res.json();
                                    if (res.ok) {
                                        this.isSaved = true;
                                        const toastDetail = {
                                            message: data.message || (this.isSaved ? 'Already in saved list' : 'Product is saved'),
                                            type: 'success',
                                            actionUrl: '<?php echo e(route('buyer.saved-items.index', ['type' => 'listing'])); ?>',
                                            actionLabel: 'saved items'
                                        };
                                        window.dispatchEvent(new CustomEvent('toast', { detail: toastDetail }));
                                    } else {
                                        const errDetail = {
                                            message: data.message || 'Could not save listing.',
                                            type: 'danger'
                                        };
                                        window.dispatchEvent(new CustomEvent('toast', { detail: errDetail }));
                                    }
                                } catch (e) {
                                    console.error(e);
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: {
                                            message: 'An error occurred while saving.',
                                            type: 'danger'
                                        }
                                    }));
                                } finally {
                                    this.loading = false;
                                }
                            }
                        }"
                        <?php if(request('save_intent')): ?>
                            x-init="$nextTick(() => saveListing())"
                        <?php endif; ?>
                        class="w-full">
                            <button type="button"
                                    id="fe-save-listing-btn"
                                    @click="saveListing()"
                                    :disabled="loading"
                                    class="group fe-focus-ring flex items-center justify-center w-full px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 ease-in-out cursor-pointer shadow-xs focus:outline-none focus:ring-2 active:scale-[0.99]"
                                    :class="isSaved
                                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100/70 hover:border-emerald-400 hover:text-emerald-800 focus:ring-emerald-500/40 focus:border-emerald-500'
                                        : 'bg-white text-slate-700 border border-slate-200 hover:bg-emerald-50/70 hover:text-emerald-700 hover:border-emerald-300 focus:ring-emerald-500/40 focus:border-emerald-400'">
                                <i :class="isSaved ? 'fa-solid fa-bookmark text-emerald-600' : 'fa-regular fa-bookmark text-slate-400 group-hover:text-emerald-600'"
                                   class="mr-2 transition-transform duration-200 group-hover:scale-110"
                                   :class="loading ? 'animate-pulse' : ''"></i>
                                <span x-text="isSaved ? 'Saved' : 'Save Listing'"></span>
                            </button>
                        </div>
                        <?php if (isset($component)) { $__componentOriginale5c68880a5ee546c83c58a25fda1def9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5c68880a5ee546c83c58a25fda1def9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.compare-button','data' => ['listing' => $listing,'style' => 'text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.compare-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing),'style' => 'text']); ?>
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

                    
                    <div class="grid grid-cols-3 gap-2 pt-2 text-center text-[10px]" style="color:var(--fe-text-muted);">
                        <div><i class="fa-solid fa-shield-halved block text-lg mb-0.5" style="color:var(--fe-primary-soft,#6366f1);"></i>Secure</div>
                        <div><i class="fa-solid fa-headset block text-lg mb-0.5" style="color:var(--fe-primary-soft,#6366f1);"></i>Support</div>
                        <div><i class="fa-solid fa-certificate block text-lg mb-0.5" style="color:var(--fe-primary-soft,#6366f1);"></i>Verified</div>
                    </div>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplierProfile): ?>
                    <div class="fe-card rounded-2xl p-5">
                        <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:var(--fe-text-muted);">Sold by</p>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-11 h-11 rounded-xl flex items-center justify-center text-base font-bold shrink-0"
                                  style="background:var(--fe-primary-soft);color:var(--fe-primary);font-family:var(--font-display);">
                                <?php echo e(strtoupper(substr($supplierProfile->display_name, 0, 1))); ?>

                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate" style="color:var(--fe-text);"><?php echo e($supplierProfile->display_name); ?></p>
                                <p class="text-[10px] font-semibold uppercase tracking-wide" style="color:var(--fe-text-subtle);">Supplier Rating</p>
                                <?php if (isset($component)) { $__componentOriginal42be004482c6a898e71d324bf92e906c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42be004482c6a898e71d324bf92e906c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rating-summary','data' => ['rating' => $supplierProfile->rating,'count' => $supplierProfile->reviews_count ?? 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rating-summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['rating' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplierProfile->rating),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplierProfile->reviews_count ?? 0)]); ?>
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
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplierProfile->city || $supplierProfile->country): ?>
                            <p class="text-xs mb-3" style="color:var(--fe-text-muted);">
                                <i class="fa-solid fa-location-dot text-[10px] mr-1"></i>
                                <?php echo e(collect([$supplierProfile->city?->name, $supplierProfile->country?->name])->filter()->implode(', ')); ?>

                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <a href="<?php echo e(route('frontend.suppliers.show', $supplierProfile->slug)); ?>"
                           class="fe-focus-ring block text-center px-4 py-2 rounded-xl text-sm font-semibold border"
                           style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                            View Storefront
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-10">

        
        <div class="lg:col-span-8" id="pdp-tabs" x-data="{ tab: 'desc' }">
            <div class="flex items-center gap-0 border-b overflow-x-auto" style="border-color:var(--fe-border);">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ($isProduct
                    ? ['desc' => 'Description', 'specs' => 'Specifications', 'terms' => 'Warranty & Support']
                    : ['desc' => 'Description', 'specs' => 'Service Details', 'terms' => 'Terms & Coverage']
                ); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button @click="tab = '<?php echo e($key); ?>'"
                            :class="tab === '<?php echo e($key); ?>' ? 'active' : ''"
                            class="pdp-tab-btn px-4 py-2.5 text-sm whitespace-nowrap transition-colors">
                        <?php echo e($label); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="py-5 text-sm leading-relaxed" style="color:var(--fe-text-muted);">

                
                <div x-show="tab === 'desc'">
                    <?php echo nl2br(e($listing->description ?? 'No description provided.')); ?>

                </div>

                
                <div x-show="tab === 'specs'" x-cloak>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty()): ?>
                        
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupedSpecifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <table class="w-full text-sm border-collapse" style="border:1px solid var(--fe-border);border-radius:8px;overflow:hidden;">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="px-4 py-2.5 text-left text-sm font-bold"
                                                style="background:var(--fe-surface-soft);color:var(--fe-primary);border-bottom:1px solid var(--fe-border);">
                                                <?php echo e($group['group_name']); ?>

                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-b" style="border-color:var(--fe-border);">
                                                <td class="px-4 py-2.5 w-2/5" style="color:var(--fe-text-muted);font-size:13px;">
                                                    <?php echo e($item->attribute?->name); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->attribute?->unit): ?>
                                                        <span class="opacity-60 text-xs">
                                                            (<?php echo e($item->attribute->unit->symbol ?? $item->attribute->unit->name); ?>)
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <td class="px-4 py-2.5" style="color:var(--fe-text);font-size:13px;font-weight:500;">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->attribute?->input_type === 'color' && $item->attributeValue?->color_hex): ?>
                                                        <span class="inline-flex items-center gap-2">
                                                            <span class="w-3.5 h-3.5 rounded-full border border-gray-300 inline-block shadow-xs"
                                                                  style="background-color:<?php echo e($item->attributeValue->color_hex); ?>;"></span>
                                                            <?php echo e($item->formattedValue()); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <?php echo e($item->formattedValue()); ?>

                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php elseif($listing->attributeValues->isNotEmpty()): ?>
                        <table class="w-full text-sm border-collapse" style="border:1px solid var(--fe-border);border-radius:8px;overflow:hidden;">
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listing->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="border-b" style="border-color:var(--fe-border);">
                                        <td class="px-4 py-2.5 w-2/5" style="color:var(--fe-text-muted);font-size:13px;"><?php echo e($value->attribute?->name); ?></td>
                                        <td class="px-4 py-2.5" style="color:var(--fe-text);font-size:13px;font-weight:500;"><?php echo e($value->formattedValue()); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:var(--fe-text-muted);">No additional specifications listed for this <?php echo e($isProduct ? 'product' : 'service'); ?>.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div x-show="tab === 'terms'" x-cloak>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isProduct): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->productDetail?->warranty_terms): ?>
                            <p class="mb-3"><strong style="color:var(--fe-text);">Warranty:</strong> <?php echo e($listing->productDetail->warranty_terms); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->productDetail?->support_terms): ?>
                            <p class="mb-3"><strong style="color:var(--fe-text);">Support:</strong> <?php echo e($listing->productDetail->support_terms); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$listing->productDetail?->warranty_terms && !$listing->productDetail?->support_terms): ?>
                            <p>No warranty or support terms specified.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->serviceDetail?->service_terms): ?>
                            <p class="mb-3"><strong style="color:var(--fe-text);">Service Terms:</strong> <?php echo e($listing->serviceDetail->service_terms); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->serviceDetail?->support_terms): ?>
                            <p class="mb-3"><strong style="color:var(--fe-text);">Support:</strong> <?php echo e($listing->serviceDetail->support_terms); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$listing->serviceDetail?->service_terms && !$listing->serviceDetail?->support_terms): ?>
                            <p>No service terms or coverage details specified.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($related->isNotEmpty()): ?>
            <div class="lg:col-span-4">
                <div class="fe-card rounded-2xl p-5">
                    <p class="text-sm font-bold mb-4" style="color:var(--fe-text);">Similar Product</p>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($itemImg = $item->getMedia('gallery')->first()); ?>
                            <a href="<?php echo e(route('frontend.listings.show', $item->slug)); ?>"
                               class="flex items-start gap-3 group <?php echo e(!$loop->first ? 'pt-4 border-t' : ''); ?>"
                               <?php if(!$loop->first): ?> style="border-color:var(--fe-border);" <?php endif; ?>>
                                <div class="w-16 h-16 rounded-lg border overflow-hidden bg-white shrink-0 flex items-center justify-center"
                                     style="border-color:var(--fe-border);">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemImg): ?>
                                        <img src="<?php echo e($itemImg->getUrl()); ?>" alt="<?php echo e($item->name); ?>" class="w-full h-full object-contain p-1">
                                    <?php else: ?>
                                        <i class="fa-solid fa-box text-xl" style="color:var(--fe-text-subtle);"></i>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium leading-snug line-clamp-2 transition-colors group-hover:opacity-80"
                                       style="color:var(--fe-text);">
                                        <?php echo e($item->name); ?>

                                    </p>
                                    <p class="text-sm font-bold mt-1" style="color:var(--fe-primary);">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->pricing_type === 'fixed' && $item->base_price): ?>
                                            <?php echo e($item->currency_code); ?> <?php echo e(number_format($item->base_price, 2)); ?>

                                        <?php else: ?>
                                            Request Quote
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>

</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplierProfile): ?>
    <?php if (isset($component)) { $__componentOriginal2f2136dcb302dac670a7b401bfecb83a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2f2136dcb302dac670a7b401bfecb83a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.inquiry-modal','data' => ['triggerId' => 'listing','action' => route('frontend.inquiries.listing', $listing->slug),'context' => $supplierProfile->display_name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.inquiry-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['trigger-id' => 'listing','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.inquiries.listing', $listing->slug)),'context' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplierProfile->display_name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2f2136dcb302dac670a7b401bfecb83a)): ?>
<?php $attributes = $__attributesOriginal2f2136dcb302dac670a7b401bfecb83a; ?>
<?php unset($__attributesOriginal2f2136dcb302dac670a7b401bfecb83a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2f2136dcb302dac670a7b401bfecb83a)): ?>
<?php $component = $__componentOriginal2f2136dcb302dac670a7b401bfecb83a; ?>
<?php unset($__componentOriginal2f2136dcb302dac670a7b401bfecb83a); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('contact') && $supplierProfile): ?>
    <?php $__env->startPush('scripts'); ?>
        <script>window.dispatchEvent(new CustomEvent('open-inquiry-listing'));</script>
    <?php $__env->stopPush(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<?php $__env->startPush('scripts'); ?>
<script>
(function() {
    const IMAGES = <?php echo json_encode($galleryUrls, 15, 512) ?>;
    let currentPdpIdx = 0;

    window.currentPdpIdx = currentPdpIdx;

    window.pdpSetImage = function(idx) {
        if (IMAGES.length === 0) return;
        // Wrap around
        if (idx < 0) idx = IMAGES.length - 1;
        if (idx >= IMAGES.length) idx = 0;

        currentPdpIdx = idx;
        window.currentPdpIdx = idx;

        // Update main image
        const mainImg = document.getElementById('pdp-main-img');
        if (mainImg) {
            mainImg.style.opacity = '0.6';
            mainImg.src = IMAGES[idx];
            mainImg.onload = () => { mainImg.style.opacity = '1'; };
        }

        // Update counter
        const cur = document.getElementById('pdp-counter-cur');
        if (cur) cur.textContent = idx + 1;

        // Update thumbnail active states — desktop
        document.querySelectorAll('[id^="thumb-"]').forEach((el) => {
            const elIdx = parseInt(el.id.replace('thumb-m-', '').replace('thumb-', ''));
            if (!isNaN(elIdx)) {
                el.classList.toggle('active', elIdx === idx);
            }
        });
    };

    // ── Lightbox ───────────────────────────────────────────
    const lightbox  = document.getElementById('pdp-lightbox');
    const lbImg     = document.getElementById('lb-img');
    const lbCur     = document.getElementById('lb-cur');
    const lbTotal   = document.getElementById('lb-total');

    function updateLightbox(idx) {
        currentPdpIdx = idx;
        window.currentPdpIdx = idx;
        if (lbImg) lbImg.src = IMAGES[idx];
        if (lbCur) lbCur.textContent = idx + 1;
    }

    window.pdpOpenLightbox = function(idx) {
        if (IMAGES.length === 0) return;
        updateLightbox(idx);
        lightbox?.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    function closeLightbox() {
        lightbox?.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('lb-close')?.addEventListener('click', closeLightbox);
    document.getElementById('lb-prev')?.addEventListener('click', () => {
        let i = currentPdpIdx - 1;
        if (i < 0) i = IMAGES.length - 1;
        updateLightbox(i);
    });
    document.getElementById('lb-next')?.addEventListener('click', () => {
        let i = currentPdpIdx + 1;
        if (i >= IMAGES.length) i = 0;
        updateLightbox(i);
    });

    lightbox?.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (!lightbox?.classList.contains('open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') {
            let i = currentPdpIdx - 1;
            if (i < 0) i = IMAGES.length - 1;
            updateLightbox(i);
        }
        if (e.key === 'ArrowRight') {
            let i = currentPdpIdx + 1;
            if (i >= IMAGES.length) i = 0;
            updateLightbox(i);
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\catalog\show.blade.php ENDPATH**/ ?>