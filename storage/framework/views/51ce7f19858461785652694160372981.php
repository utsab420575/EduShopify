<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Dashboard'); ?> — Edushopify</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fbf6', 100: '#d3efe2', 200: '#a9ded5', 300: '#7ecab6',
                            400: '#57b799', 500: '#3da47e', 600: '#2d8a67', 700: '#216c50', 900: '#124633',
                        },
                    },
                }
            }
        }
    </script>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased font-sans" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen lg:pl-64">

        <?php if (isset($component)) { $__componentOriginal060abe2a9b4511e378911474e77b046d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal060abe2a9b4511e378911474e77b046d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.sidebar','data' => ['role' => $role]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['role' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($role)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal060abe2a9b4511e378911474e77b046d)): ?>
<?php $attributes = $__attributesOriginal060abe2a9b4511e378911474e77b046d; ?>
<?php unset($__attributesOriginal060abe2a9b4511e378911474e77b046d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal060abe2a9b4511e378911474e77b046d)): ?>
<?php $component = $__componentOriginal060abe2a9b4511e378911474e77b046d; ?>
<?php unset($__componentOriginal060abe2a9b4511e378911474e77b046d); ?>
<?php endif; ?>

        <div class="lg:flex lg:flex-col lg:min-h-screen">
            <?php if (isset($component)) { $__componentOriginal1185a77f86785c5182eccccf9103cfa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1185a77f86785c5182eccccf9103cfa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.topbar','data' => ['role' => $role,'title' => $title ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['role' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($role),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1185a77f86785c5182eccccf9103cfa0)): ?>
<?php $attributes = $__attributesOriginal1185a77f86785c5182eccccf9103cfa0; ?>
<?php unset($__attributesOriginal1185a77f86785c5182eccccf9103cfa0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1185a77f86785c5182eccccf9103cfa0)): ?>
<?php $component = $__componentOriginal1185a77f86785c5182eccccf9103cfa0; ?>
<?php unset($__componentOriginal1185a77f86785c5182eccccf9103cfa0); ?>
<?php endif; ?>

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    <?php echo e($slot); ?>

                </div>
            </main>

            <?php if (isset($component)) { $__componentOriginal6131d733ccfb7ef2e4ea10b2ead2ef15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6131d733ccfb7ef2e4ea10b2ead2ef15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6131d733ccfb7ef2e4ea10b2ead2ef15)): ?>
<?php $attributes = $__attributesOriginal6131d733ccfb7ef2e4ea10b2ead2ef15; ?>
<?php unset($__attributesOriginal6131d733ccfb7ef2e4ea10b2ead2ef15); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6131d733ccfb7ef2e4ea10b2ead2ef15)): ?>
<?php $component = $__componentOriginal6131d733ccfb7ef2e4ea10b2ead2ef15; ?>
<?php unset($__componentOriginal6131d733ccfb7ef2e4ea10b2ead2ef15); ?>
<?php endif; ?>
        </div>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo e($scripts ?? ''); ?>

</body>
</html>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\layouts\dashboard.blade.php ENDPATH**/ ?>