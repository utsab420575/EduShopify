<?php $__env->startSection('title', 'Dashboard Mode Preference'); ?>
<?php $__env->startSection('breadcrumb', 'Settings / Dashboard Mode'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Default Dashboard Mode','subtitle' => 'Choose your default landing dashboard if your account holds both Buyer and Supplier capabilities.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Default Dashboard Mode','subtitle' => 'Choose your default landing dashboard if your account holds both Buyer and Supplier capabilities.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccefb989a1afce853acb3cdbc40307e)): ?>
<?php $attributes = $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e; ?>
<?php unset($__attributesOriginal6ccefb989a1afce853acb3cdbc40307e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccefb989a1afce853acb3cdbc40307e)): ?>
<?php $component = $__componentOriginal6ccefb989a1afce853acb3cdbc40307e; ?>
<?php unset($__componentOriginal6ccefb989a1afce853acb3cdbc40307e); ?>
<?php endif; ?>

    <div class="max-w-xl">
        <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Landing Portal Preference']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Landing Portal Preference']); ?>
            <form method="POST" action="<?php echo e(route('supplier.settings.dashboard-mode.update')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Dashboard on Login</label>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer hover:bg-gray-50 <?php echo e($current === 'supplier' ? 'border-indigo-500 bg-indigo-50/30' : 'border-gray-200'); ?>">
                            <input type="radio" name="default_mode" value="supplier" <?php if($current === 'supplier'): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Supplier Dashboard (Sell &amp; Quote)</p>
                                <p class="text-xs text-gray-400">View RFQ opportunities, quotations, awards, and listings.</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer hover:bg-gray-50 <?php echo e($current === 'buyer' ? 'border-indigo-500 bg-indigo-50/30' : 'border-gray-200'); ?>">
                            <input type="radio" name="default_mode" value="buyer" <?php if($current === 'buyer'): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Buyer Dashboard (Procure &amp; Order)</p>
                                <p class="text-xs text-gray-400">Create RFQs, compare quotations, and issue purchase orders.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-check"></i> Save Preference
                    </button>
                </div>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $attributes = $__attributesOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $component = $__componentOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__componentOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\settings\dashboard-mode.blade.php ENDPATH**/ ?>