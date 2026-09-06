<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="frontend scroll-smooth">
<?php echo $__env->make('frontend.layouts.partials._head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<body class="bg-white text-gray-800 antialiased">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg">
        Skip to content
    </a>

    <?php echo $__env->make('frontend.layouts.partials._header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('frontend.layouts.partials._mobile_menu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main id="main-content">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="fe-container pt-4">
                <div class="rounded-xl border px-4 py-3 text-sm flex items-center gap-2 bg-green-50 border-green-600 text-green-800">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="fe-container pt-4">
                <div class="rounded-xl border px-4 py-3 text-sm flex items-center gap-2 bg-red-50 border-red-600 text-red-800">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo e(session('error')); ?></span>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo $__env->make('frontend.layouts.partials._footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if (isset($component)) { $__componentOriginale0974fb414d1f3cb23e5bbde99a03877 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale0974fb414d1f3cb23e5bbde99a03877 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale0974fb414d1f3cb23e5bbde99a03877)): ?>
<?php $attributes = $__attributesOriginale0974fb414d1f3cb23e5bbde99a03877; ?>
<?php unset($__attributesOriginale0974fb414d1f3cb23e5bbde99a03877); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale0974fb414d1f3cb23e5bbde99a03877)): ?>
<?php $component = $__componentOriginale0974fb414d1f3cb23e5bbde99a03877; ?>
<?php unset($__componentOriginale0974fb414d1f3cb23e5bbde99a03877); ?>
<?php endif; ?>
    <?php echo $__env->make('frontend.layouts.partials._scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\layouts\master.blade.php ENDPATH**/ ?>