<?php if (isset($component)) { $__componentOriginalcef9809f78c7b2d29e8e1a19df173cda = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcef9809f78c7b2d29e8e1a19df173cda = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.error-page','data' => ['code' => '419','icon' => 'fa-clock-rotate-left','accent' => 'amber','title' => 'Session Expired','message' => 'Your session has expired, likely due to inactivity. Please refresh the page and try again.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('error-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['code' => '419','icon' => 'fa-clock-rotate-left','accent' => 'amber','title' => 'Session Expired','message' => 'Your session has expired, likely due to inactivity. Please refresh the page and try again.']); ?>
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
<?php /**PATH C:\laragon\www\edushopify\resources\views\errors\419.blade.php ENDPATH**/ ?>