<?php $__env->startSection('title', 'Supplier Directory — EduShopify Marketplace'); ?>
<?php $__env->startSection('meta_description', 'Browse verified education suppliers on EduShopify.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-6 sm:py-8">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['Suppliers' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Suppliers' => null])]); ?>
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

        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Supplier Directory</h1>
            <p class="text-sm mt-1" style="color:var(--fe-text-muted);"><?php echo e($suppliers->total()); ?> verified <?php echo e(Str::plural('supplier', $suppliers->total())); ?> ready to quote.</p>
        </div>

        <form method="GET" class="fe-card rounded-2xl p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Search suppliers..." class="fe-focus-ring w-full h-11 pl-11 pr-4 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            </div>
            <select name="type" class="fe-focus-ring h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                <option value="">All Types</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supplierTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->slug); ?>" <?php if(($filters['type'] ?? '') === $type->slug): echo 'selected'; endif; ?>><?php echo e($type->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <div class="flex gap-2">
                <select name="sort" class="fe-focus-ring flex-1 h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                    <option value="rating" <?php if($sort === 'rating'): echo 'selected'; endif; ?>>Top Rated</option>
                    <option value="newest" <?php if($sort === 'newest'): echo 'selected'; endif; ?>>Newest</option>
                </select>
                <button type="submit" class="fe-btn-primary fe-focus-ring px-4 rounded-xl text-sm font-semibold shrink-0">Filter</button>
            </div>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suppliers->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-store-slash','title' => 'No suppliers found','description' => 'Try a different search term or filter.','actionLabel' => 'Clear filters','actionUrl' => route('frontend.suppliers.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-store-slash','title' => 'No suppliers found','description' => 'Try a different search term or filter.','action-label' => 'Clear filters','action-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.suppliers.index'))]); ?>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal73f0dab196648a7aae3177cd3a1cf306 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73f0dab196648a7aae3177cd3a1cf306 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.supplier-card','data' => ['supplier' => $supplier,'isSaved' => in_array($supplier->account_id, $savedSupplierIds ?? [])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.supplier-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['supplier' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier),'is-saved' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(in_array($supplier->account_id, $savedSupplierIds ?? []))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73f0dab196648a7aae3177cd3a1cf306)): ?>
<?php $attributes = $__attributesOriginal73f0dab196648a7aae3177cd3a1cf306; ?>
<?php unset($__attributesOriginal73f0dab196648a7aae3177cd3a1cf306); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73f0dab196648a7aae3177cd3a1cf306)): ?>
<?php $component = $__componentOriginal73f0dab196648a7aae3177cd3a1cf306; ?>
<?php unset($__componentOriginal73f0dab196648a7aae3177cd3a1cf306); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginalaa684956d2f805bd41f5b0a3a180039a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaa684956d2f805bd41f5b0a3a180039a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.pagination','data' => ['paginator' => $suppliers]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($suppliers)]); ?>
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

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\suppliers\index.blade.php ENDPATH**/ ?>