<?php $__env->startSection('title', $category->name.' — EduShopify Marketplace'); ?>
<?php $__env->startSection('meta_description', Str::limit(strip_tags($category->description ?? ('Browse '.$category->name.' listings on EduShopify.')), 155)); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-6 sm:py-8">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => [
            'Categories' => route('frontend.categories.index'),
            ...($category->parent ? [$category->parent->name => route('frontend.categories.show', $category->parent->slug)] : []),
            $category->name => null,
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            'Categories' => route('frontend.categories.index'),
            ...($category->parent ? [$category->parent->name => route('frontend.categories.show', $category->parent->slug)] : []),
            $category->name => null,
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

        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);"><?php echo e($category->name); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->description): ?>
                <p class="text-sm mt-1 max-w-2xl" style="color:var(--fe-text-muted);"><?php echo e($category->description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->children->isNotEmpty()): ?>
            <div class="flex flex-wrap gap-2 mb-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('frontend.categories.show', $child->slug)); ?>" class="fe-focus-ring px-3.5 py-1.5 rounded-full text-xs font-medium bg-white border hover:border-[--fe-primary] hover:text-[--fe-primary]" style="border-color:var(--fe-border);color:var(--fe-text-muted);">
                    <?php echo e($child->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listings->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-box-open','title' => 'No listings in this category yet','description' => 'Check back soon, or browse the full marketplace.','actionLabel' => 'Browse marketplace','actionUrl' => route('frontend.catalog.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-box-open','title' => 'No listings in this category yet','description' => 'Check back soon, or browse the full marketplace.','action-label' => 'Browse marketplace','action-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.catalog.index'))]); ?>
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
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\categories\show.blade.php ENDPATH**/ ?>