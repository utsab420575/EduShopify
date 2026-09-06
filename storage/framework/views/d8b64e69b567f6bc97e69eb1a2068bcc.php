<?php if (isset($component)) { $__componentOriginalcef9809f78c7b2d29e8e1a19df173cda = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcef9809f78c7b2d29e8e1a19df173cda = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.error-page','data' => ['code' => '503','icon' => 'fa-screwdriver-wrench','accent' => 'amber','title' => 'Down for Maintenance','message' => 'EduShopify is currently undergoing scheduled maintenance. We\'ll be back shortly — thank you for your patience.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('error-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['code' => '503','icon' => 'fa-screwdriver-wrench','accent' => 'amber','title' => 'Down for Maintenance','message' => 'EduShopify is currently undergoing scheduled maintenance. We\'ll be back shortly — thank you for your patience.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcef9809f78c7b2d29e8e1a19df173cda)): ?>
<?php $attributes = $__attributesOriginalcef9809f78c7b2d29e8e1a19df173cda; ?>
<?php unset($__attributesOriginalcef9809f78c7b2d29e8e1a19df173cda); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcef9809f78c7b2d29e8e1a19df173cda)): ?>
<?php $component = $__componentOriginalcef9809f78c7b2d29e8e1a19df173cda; ?>
<?php unset($__componentOriginalcef9809f78c7b2d29e8e1a19df173cda); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\errors\503.blade.php ENDPATH**/ ?>