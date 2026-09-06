<?php $__env->startSection('title', 'Dashboard Mode'); ?>
<?php $__env->startSection('breadcrumb', 'Settings / Dashboard Mode'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Dashboard Mode','subtitle' => 'Choose which dashboard opens by default when you sign in.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard Mode','subtitle' => 'Choose which dashboard opens by default when you sign in.']); ?>
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

    <form method="POST" action="<?php echo e(route('buyer.settings.dashboard-mode.update')); ?>" class="max-w-xl">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex flex-col items-center text-center gap-2 rounded-lg border-2 px-3 py-4 cursor-pointer transition"
                       style="<?php echo e($current === 'buyer' ? 'border-color:var(--theme-primary); background:var(--theme-primary-soft)' : 'border-color:#e5e7eb'); ?>">
                    <i class="fa-solid fa-cart-shopping text-lg" style="color:var(--theme-primary)"></i>
                    <span class="text-xs font-semibold" style="color:var(--theme-primary)">Buyer</span>
                    <input type="radio" name="default_mode" value="buyer" class="hidden" <?php if($current === 'buyer'): echo 'checked'; endif; ?>>
                </label>
                <label class="flex flex-col items-center text-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 py-4 cursor-pointer hover:border-gray-300 transition">
                    <i class="fa-solid fa-store text-lg text-gray-400"></i>
                    <span class="text-xs font-medium text-gray-600">Supplier</span>
                    <input type="radio" name="default_mode" value="supplier" class="hidden" <?php if($current === 'supplier'): echo 'checked'; endif; ?>>
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-4">Changing your default dashboard does not change what you're authorized to do — that's always based on your active capabilities and permissions.</p>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save</button>
            </div>
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
    </form>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.buyer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\settings\dashboard-mode.blade.php ENDPATH**/ ?>