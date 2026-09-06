<?php $__env->startSection('title', 'RFQ Opportunities — EduShopify Marketplace'); ?>
<?php $__env->startSection('meta_description', 'Browse open, publicly visible RFQ sourcing opportunities on EduShopify.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-6 sm:py-8">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['Opportunities' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Opportunities' => null])]); ?>
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

        <div class="flex items-center gap-2 mb-2">
            <span class="w-2 h-2 rounded-full" style="background:var(--fe-primary);"></span>
            <span class="text-xs font-semibold uppercase tracking-wide" style="color:var(--fe-primary);">Live Sourcing</span>
        </div>
        <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight mb-1" style="font-family:var(--font-display);color:var(--fe-text);">RFQ Opportunities</h1>
        <p class="text-sm mb-6" style="color:var(--fe-text-muted);"><?php echo e($opportunities->total()); ?> open <?php echo e(Str::plural('opportunity', $opportunities->total())); ?>. Suppliers must log in or register to submit a quotation.</p>

        <form method="GET" class="fe-card rounded-2xl p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Search opportunities..." class="fe-focus-ring w-full h-11 pl-11 pr-4 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            </div>
            <input type="text" name="category" value="<?php echo e($filters['category'] ?? ''); ?>" placeholder="Category" class="fe-focus-ring h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            <div class="flex gap-2">
                <select name="sort" class="fe-focus-ring flex-1 h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                    <option value="deadline" <?php if($sort === 'deadline'): echo 'selected'; endif; ?>>Closing Soonest</option>
                    <option value="newest" <?php if($sort === 'newest'): echo 'selected'; endif; ?>>Newest</option>
                </select>
                <button type="submit" class="fe-btn-primary fe-focus-ring px-4 rounded-xl text-sm font-semibold shrink-0">Filter</button>
            </div>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunities->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-file-circle-question','title' => 'No public RFQ opportunities match your search','description' => 'Try a different search term or check back soon.','actionLabel' => 'Clear filters','actionUrl' => route('frontend.rfqs.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-file-circle-question','title' => 'No public RFQ opportunities match your search','description' => 'Try a different search term or check back soon.','action-label' => 'Clear filters','action-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.rfqs.index'))]); ?>
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
        <?php else: ?>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalcc3f1b29b1d7162b4889c2fb09a44862 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcc3f1b29b1d7162b4889c2fb09a44862 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.rfq-card','data' => ['opportunity' => $opportunity]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.rfq-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['opportunity' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunity)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcc3f1b29b1d7162b4889c2fb09a44862)): ?>
<?php $attributes = $__attributesOriginalcc3f1b29b1d7162b4889c2fb09a44862; ?>
<?php unset($__attributesOriginalcc3f1b29b1d7162b4889c2fb09a44862); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcc3f1b29b1d7162b4889c2fb09a44862)): ?>
<?php $component = $__componentOriginalcc3f1b29b1d7162b4889c2fb09a44862; ?>
<?php unset($__componentOriginalcc3f1b29b1d7162b4889c2fb09a44862); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginalaa684956d2f805bd41f5b0a3a180039a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.pagination','data' => ['paginator' => $opportunities]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opportunities)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaa684956d2f805bd41f5b0a3a180039a)): ?>
<?php $attributes = $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a; ?>
<?php unset($__attributesOriginalaa684956d2f805bd41f5b0a3a180039a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaa684956d2f805bd41f5b0a3a180039a)): ?>
<?php $component = $__componentOriginalaa684956d2f805bd41f5b0a3a180039a; ?>
<?php unset($__componentOriginalaa684956d2f805bd41f5b0a3a180039a); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\rfqs\index.blade.php ENDPATH**/ ?>