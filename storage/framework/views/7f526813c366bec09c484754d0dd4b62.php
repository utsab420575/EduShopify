<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allSuppliers->isNotEmpty()): ?>
    <section class="py-12 lg:py-16 bg-gray-50">
        <div class="fe-container">
            <?php if (isset($component)) { $__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.section-heading','data' => ['title' => 'All Suppliers','action' => route('frontend.suppliers.index'),'actionLabel' => 'View all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.section-heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'All Suppliers','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('frontend.suppliers.index')),'actionLabel' => 'View all']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a)): ?>
<?php $attributes = $__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a; ?>
<?php unset($__attributesOriginal2d696b07533e1d202d77c3ea5e0ca69a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a)): ?>
<?php $component = $__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a; ?>
<?php unset($__componentOriginal2d696b07533e1d202d77c3ea5e0ca69a); ?>
<?php endif; ?>

            <div x-data="{ active: 'all' }" class="flex flex-wrap gap-2 mb-6">
                <button type="button" @click="active = 'all'"
                        class="tab-btn text-xs font-semibold px-3 py-1.5 rounded-md"
                        :class="active === 'all' ? 'tag-active' : 'tag-inactive'">
                    All Categories
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" @click="active = '<?php echo e($tab->slug); ?>'"
                            class="tab-btn text-xs font-semibold px-3 py-1.5 rounded-md"
                            :class="active === '<?php echo e($tab->slug); ?>' ? 'tag-active' : 'tag-inactive'">
                        <?php echo e($tab->name); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 w-full mt-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allSuppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="supplier-item" x-show="active === 'all' || active === '<?php echo e($supplier->home_category?->slug); ?>'">
                            <?php if (isset($component)) { $__componentOriginal73f0dab196648a7aae3177cd3a1cf306 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73f0dab196648a7aae3177cd3a1cf306 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.marketplace.supplier-card','data' => ['supplier' => $supplier]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::marketplace.supplier-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['supplier' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier)]); ?>
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
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\home\sections\_all_suppliers.blade.php ENDPATH**/ ?>