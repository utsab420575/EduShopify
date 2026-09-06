<?php $__env->startSection('title', 'Compare Products — EduShopify'); ?>
<?php $__env->startSection('meta_description', 'Compare marketplace listings side by side — pricing, MOQ, supplier and structured specifications, sourced fresh from EduShopify.'); ?>

<?php $__env->startPush('head'); ?>
    
    <meta name="robots" content="noindex, follow">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* border-collapse breaks position:sticky on table cells in several
       browsers (a well-known interaction) — border-separate with zero
       spacing keeps the same visual grid while keeping the sticky first
       column reliable at any number of compared columns. */
    .comparison-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .comparison-table th.sticky, .comparison-table td.sticky {
        position: sticky;
        left: 0;
    }
    /* Sticky header row (the product cards) — stays visible as you scroll
       down through a long spec list, offset below the site's own sticky
       top nav (h-20 = 5rem, resources/views/frontend/layouts/partials/_header.blade.php). */
    .comparison-table thead th {
        position: sticky;
        top: 5rem;
        z-index: 15;
    }
    /* The top-left corner cell is sticky on both axes at once — it must sit
       above every other sticky cell (header row AND left column) so it never
       gets visually covered when both scroll directions are active together. */
    .comparison-table thead th.sticky {
        z-index: 25;
    }
    @media print {
        header, #main-content > .fe-container > nav, .comparison-hide-print,
        .comparison-controls, footer, [aria-label="Site menu"] {
            display: none !important;
        }
        #comparison-print-area {
            border: none !important;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="fe-container py-6 sm:py-8" x-data="comparePage()">

    <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['Compare Products' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Compare Products' => null])]); ?>
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

    <template x-if="loading">
        <div class="py-20 text-center text-sm" style="color:var(--fe-text-muted);">
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading comparison…
        </div>
    </template>

    <template x-if="!loading && count === 0">
        <div>
            <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-arrow-right-arrow-left','title' => 'No products selected for comparison.','description' => 'Browse products and click &quot;Add to Compare&quot; to start comparing.','actionLabel' => 'Browse Products','actionUrl' => route('frontend.catalog.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-arrow-right-arrow-left','title' => 'No products selected for comparison.','description' => 'Browse products and click &quot;Add to Compare&quot; to start comparing.','actionLabel' => 'Browse Products','actionUrl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.catalog.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $attributes = $__attributesOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__attributesOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $component = $__componentOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__componentOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
        </div>
    </template>

    <template x-if="!loading && count > 0">
        <div>
            <div class="flex items-center justify-between flex-wrap gap-3 mb-5 mt-4">
                <h1 class="text-xl font-bold" style="font-family:var(--font-display);color:var(--fe-text);">
                    Compare Products
                    <span class="text-sm font-normal" style="color:var(--fe-text-muted);" x-text="'(' + count + ' of ' + maxItems + ')'"></span>
                </h1>
                <div class="comparison-controls comparison-hide-print flex items-center gap-3 flex-wrap">
                    <label class="inline-flex items-center gap-1.5 text-xs font-medium cursor-pointer" style="color:var(--fe-text);">
                        <input type="checkbox" x-model="showDiffsOnly" class="rounded" style="accent-color:var(--fe-primary);">
                        Show Differences Only
                    </label>
                    <button type="button" @click="window.print()" class="fe-focus-ring text-xs font-semibold px-3 py-1.5 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                        <i class="fa-solid fa-print mr-1"></i> Print
                    </button>
                    <button type="button" @click="if (confirm('Clear all products from comparison?')) clearAll()" class="fe-focus-ring text-xs font-semibold px-3 py-1.5 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-danger);">
                        <i class="fa-solid fa-trash mr-1"></i> Clear All
                    </button>
                </div>
            </div>

            
            <div class="comparison-hide-print mb-5 flex items-center justify-between gap-3 flex-wrap rounded-xl px-4 py-3" style="background:var(--fe-primary-soft);border:1px solid var(--fe-border);">
                <p class="text-xs font-medium" style="color:var(--fe-text-muted);">
                    <i class="fa-solid fa-file-invoice mr-1" style="color:var(--fe-primary);"></i>
                    Request a quote for all <span x-text="count"></span> compared product<span x-show="count !== 1">s</span> at once.
                </p>
                <a :href="<?php echo e(auth()->check() ? "'/buyer/rfqs/create?listings=' + listings.map(l => l.listing_id).join(',')" : "'/handoff/compare-rfq?listings=' + listings.map(l => l.slug).join(',')"); ?>"
                   class="fe-btn-primary fe-focus-ring text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i> Send RFQ to All Suppliers
                </a>
            </div>

            <div class="rounded-2xl border overflow-hidden" style="border-color:var(--fe-border);" id="comparison-print-area">
                <div class="overflow-x-auto">
                    <table class="w-full comparison-table" style="min-width:720px;">
                        <thead>
                            <tr>
                                <th class="sticky z-20 text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="background:var(--fe-surface-soft);color:var(--fe-text-muted);width:160px;min-width:160px;">
                                    Product
                                </th>
                                <template x-for="(item, idx) in listings" :key="item.listing_id + ':' + (item.variant_id ?? 0)">
                                    <?php echo $__env->make('frontend.components.marketplace.comparison-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </template>
                                <th class="comparison-hide-print px-4 py-4 align-top" x-show="canAddMore" x-cloak style="width:180px;min-width:180px;background:var(--fe-surface-soft);border-left:1px solid var(--fe-border);">
                                    <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                        <button type="button" @click="open = !open" class="fe-focus-ring w-full flex flex-col items-center justify-center gap-1.5 text-xs font-semibold py-6 rounded-xl border-2 border-dashed" style="border-color:var(--fe-border-strong);color:var(--fe-text-muted);">
                                            <i class="fa-solid fa-plus text-base"></i> Add Product
                                        </button>
                                        <div x-show="open" x-cloak class="absolute z-30 mt-2 left-0 w-64 bg-white border rounded-xl shadow-lg p-3" style="border-color:var(--fe-border);">
                                            <input type="text" x-model="addMoreQuery" @input.debounce.300ms="searchAddMore()" placeholder="Search products…" class="fe-focus-ring w-full h-10 px-3 rounded-lg border text-sm mb-2" style="border-color:var(--fe-border);">
                                            <template x-if="addMoreResults.length > 0">
                                                <div class="max-h-56 overflow-y-auto space-y-1">
                                                    <template x-for="r in addMoreResults" :key="r.id">
                                                        <button type="button" @click="addMore(r.id); open = false" class="w-full text-left px-2 py-2 rounded-lg hover:bg-slate-50 text-xs flex items-center gap-2">
                                                            <img :src="r.thumb_url" x-show="r.thumb_url" class="w-8 h-8 rounded object-cover shrink-0" alt="">
                                                            <span class="min-w-0">
                                                                <span class="block font-medium truncate" x-text="r.name" style="color:var(--fe-text);"></span>
                                                                <span class="block text-[10px]" style="color:var(--fe-text-muted);" x-text="r.category"></span>
                                                            </span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                            <p x-show="addMoreQuery.length >= 2 && addMoreResults.length === 0" class="text-xs py-2 text-center" style="color:var(--fe-text-muted);">No matching products.</p>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        
                        <tbody>
                            <tr>
                                <td class="sticky z-10 px-4 py-2 text-[11px] font-bold uppercase tracking-wide" style="background:var(--fe-surface-soft);color:var(--fe-primary);border-top:1px solid var(--fe-border);">Reviews</td>
                                <td :colspan="listings.length" class="px-4 py-2 text-[11px] font-bold uppercase tracking-wide" style="background:var(--fe-surface-soft);border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);"></td>
                            </tr>
                            <tr>
                                <td class="sticky left-0 z-10 px-4 py-3 text-xs font-medium align-top" style="background:var(--fe-surface);color:var(--fe-text-muted);border-top:1px solid var(--fe-border);">Product Rating</td>
                                <template x-for="item in listings" :key="'product-rating-' + item.listing_id + ':' + (item.variant_id ?? 0)">
                                    <td class="px-4 py-3 text-xs align-top" style="border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="flex items-center gap-0.5">
                                                <template x-for="star in [1,2,3,4,5]" :key="star">
                                                    <i class="fa-solid fa-star text-[10px]" :style="star <= Math.round(item.product_rating) ? 'color:#f59e0b;' : 'color:#e2e8f0;'"></i>
                                                </template>
                                            </span>
                                            <span class="font-semibold" style="color:var(--fe-text);" x-text="item.product_rating.toFixed(1)"></span>
                                            <span style="color:var(--fe-text-muted);" x-text="'(' + item.product_reviews_count + ')'"></span>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            <tr>
                                <td class="sticky left-0 z-10 px-4 py-3 text-xs font-medium align-top" style="background:var(--fe-surface);color:var(--fe-text-muted);border-top:1px solid var(--fe-border);">Supplier Rating</td>
                                <template x-for="item in listings" :key="'supplier-rating-' + item.listing_id + ':' + (item.variant_id ?? 0)">
                                    <td class="px-4 py-3 text-xs align-top" style="border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="flex items-center gap-0.5">
                                                <template x-for="star in [1,2,3,4,5]" :key="star">
                                                    <i class="fa-solid fa-star text-[10px]" :style="star <= Math.round(item.supplier_rating ?? 0) ? 'color:#f59e0b;' : 'color:#e2e8f0;'"></i>
                                                </template>
                                            </span>
                                            <span class="font-semibold" style="color:var(--fe-text);" x-text="(item.supplier_rating ?? 0).toFixed(1)"></span>
                                            <span style="color:var(--fe-text-muted);" x-text="'(' + item.supplier_reviews_count + ')'"></span>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </tbody>

                        
                        <tbody>
                            <template x-for="row in matrix.key_specs" :key="'key-' + row.attribute_id">
                                <?php echo $__env->make('frontend.components.marketplace.comparison-row', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </template>
                        </tbody>

                        
                        <template x-if="showAdditional">
                            <template x-for="group in matrix.additional_groups" :key="group.group_name">
                                <tbody>
                                    <tr>
                                        <td class="sticky z-10 px-4 py-2 text-[11px] font-bold uppercase tracking-wide" style="background:var(--fe-surface-soft);color:var(--fe-primary);border-top:1px solid var(--fe-border);" x-text="group.group_name"></td>
                                        <td :colspan="listings.length" class="px-4 py-2 text-[11px] font-bold uppercase tracking-wide" style="background:var(--fe-surface-soft);border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);"></td>
                                    </tr>
                                    <template x-for="row in group.rows" :key="group.group_name + '-' + row.attribute_id">
                                        <?php echo $__env->make('frontend.components.marketplace.comparison-row', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </template>
                                </tbody>
                            </template>
                        </template>
                    </table>
                </div>
            </div>

            <div class="comparison-hide-print text-center mt-5">
                <button type="button" @click="showAdditional = !showAdditional" class="fe-focus-ring text-sm font-semibold px-5 py-2.5 rounded-xl border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                    <span x-show="!showAdditional"><i class="fa-solid fa-chevron-down mr-1.5"></i> Show Additional Information</span>
                    <span x-show="showAdditional" x-cloak><i class="fa-solid fa-chevron-up mr-1.5"></i> Show Basic Information</span>
                </button>
            </div>
        </div>
    </template>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\compare\index.blade.php ENDPATH**/ ?>