<?php $__env->startSection('title', $title.' — EduShopify Marketplace'); ?>
<?php $__env->startSection('meta_description', $subtitle); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-6 sm:py-8">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => [$title => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([$title => null])]); ?>
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

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);"><?php echo e($title); ?></h1>
                <p class="text-sm mt-1" style="color:var(--fe-text-muted);"><?php echo e($listings->total()); ?> <?php echo e(Str::plural('result', $listings->total())); ?> &middot; <?php echo e($subtitle); ?></p>
            </div>

            <form method="GET" class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($value)): ?>
                        <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <label for="fe-sort" class="sr-only">Sort by</label>
                <select id="fe-sort" name="sort" onchange="this.form.submit()" class="fe-focus-ring h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                    <option value="relevance" <?php if($sort === 'relevance'): echo 'selected'; endif; ?>>Relevance</option>
                    <option value="newest" <?php if($sort === 'newest'): echo 'selected'; endif; ?>>Newest</option>
                    <option value="price_low" <?php if($sort === 'price_low'): echo 'selected'; endif; ?>>Price: Low to High</option>
                    <option value="price_high" <?php if($sort === 'price_high'): echo 'selected'; endif; ?>>Price: High to Low</option>
                    <option value="featured" <?php if($sort === 'featured'): echo 'selected'; endif; ?>>Featured</option>
                </select>
            </form>
        </div>

        <div class="mb-4">
            <?php if (isset($component)) { $__componentOriginalb6ed1d8693439e519f5494386e1204c0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6ed1d8693439e519f5494386e1204c0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.search.filter-drawer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::search.filter-drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                <div class="mb-4">
                    <label for="fe-catalog-q-mobile" class="sr-only">Search</label>
                    <input type="search" id="fe-catalog-q-mobile" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Search in <?php echo e(strtolower($title)); ?>..." class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                </div>
                <?php echo $__env->make('frontend.catalog._filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6ed1d8693439e519f5494386e1204c0)): ?>
<?php $attributes = $__attributesOriginalb6ed1d8693439e519f5494386e1204c0; ?>
<?php unset($__attributesOriginalb6ed1d8693439e519f5494386e1204c0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6ed1d8693439e519f5494386e1204c0)): ?>
<?php $component = $__componentOriginalb6ed1d8693439e519f5494386e1204c0; ?>
<?php unset($__componentOriginalb6ed1d8693439e519f5494386e1204c0); ?>
<?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <aside class="hidden lg:block lg:col-span-3">
                <div class="fe-card rounded-2xl p-5 sticky top-24">
                    <p class="text-sm font-semibold mb-4" style="color:var(--fe-text);">Filters</p>
                    <form method="GET">
                        <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                        <div class="mb-4">
                            <label for="fe-catalog-q" class="sr-only">Search</label>
                            <input type="search" id="fe-catalog-q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Search in <?php echo e(strtolower($title)); ?>..." class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                        </div>
                        <?php echo $__env->make('frontend.catalog._filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <button type="submit" class="fe-btn-primary fe-focus-ring w-full mt-6 px-4 py-2.5 rounded-lg text-sm font-semibold">Apply Filters</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty()): ?>
                            <a href="<?php echo e(url()->current()); ?>" class="block text-center mt-2 text-sm font-medium" style="color:var(--fe-text-muted);">Clear all</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </form>
                </div>
            </aside>

            <div class="lg:col-span-9">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listings->isEmpty()): ?>
                    <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-box-open','title' => 'No listings match these filters','description' => 'Try adjusting or clearing your filters.','actionLabel' => 'Clear filters','actionUrl' => url()->current()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-box-open','title' => 'No listings match these filters','description' => 'Try adjusting or clearing your filters.','action-label' => 'Clear filters','action-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(url()->current())]); ?>
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
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginal678b3726af759b898b6e4915a3a0d29a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal678b3726af759b898b6e4915a3a0d29a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.listing-card','data' => ['listing' => $listing]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.listing-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['listing' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal678b3726af759b898b6e4915a3a0d29a)): ?>
<?php $attributes = $__attributesOriginal678b3726af759b898b6e4915a3a0d29a; ?>
<?php unset($__attributesOriginal678b3726af759b898b6e4915a3a0d29a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal678b3726af759b898b6e4915a3a0d29a)): ?>
<?php $component = $__componentOriginal678b3726af759b898b6e4915a3a0d29a; ?>
<?php unset($__componentOriginal678b3726af759b898b6e4915a3a0d29a); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if (isset($component)) { $__componentOriginalaa684956d2f805bd41f5b0a3a180039a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.pagination','data' => ['paginator' => $listings]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listings)]); ?>
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
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\catalog\index.blade.php ENDPATH**/ ?>