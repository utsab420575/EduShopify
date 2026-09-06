<?php if (isset($component)) { $__componentOriginalcef9809f78c7b2d29e8e1a19df173cda = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcef9809f78c7b2d29e8e1a19df173cda = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.error-page','data' => ['code' => '403','icon' => 'fa-lock','accent' => 'red','title' => 'Access Denied','message' => $exception->getMessage() ?: 'You don\'t have permission to view this page. If you believe this is a mistake, please contact support or an administrator.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('error-page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['code' => '403','icon' => 'fa-lock','accent' => 'red','title' => 'Access Denied','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($exception->getMessage() ?: 'You don\'t have permission to view this page. If you believe this is a mistake, please contact support or an administrator.')]); ?>
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
<?php /**PATH C:\laragon\www\edushopify\resources\views\errors\403.blade.php ENDPATH**/ ?>