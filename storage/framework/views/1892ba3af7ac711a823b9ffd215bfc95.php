<?php $__env->startSection('title', 'About EduShopify'); ?>
<?php $__env->startSection('meta_description', 'EduShopify is a B2B marketplace connecting educational institutions with verified suppliers through structured RFQ procurement.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-10 sm:py-14">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['About' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['About' => null])]); ?>
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

        <div class="max-w-3xl mx-auto text-center mb-14">
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Procurement, built for education</h1>
            <p class="mt-4 text-base sm:text-lg" style="color:var(--fe-text-muted);">
                EduShopify is a B2B marketplace that connects educational institutions with verified suppliers of products and services, through a structured, transparent RFQ procurement process.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-4xl mx-auto mb-14">
            <div class="fe-card rounded-2xl p-6">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
                <h3 class="text-base font-semibold mb-1.5" style="color:var(--fe-text);">For institutional buyers</h3>
                <p class="text-sm" style="color:var(--fe-text-muted);">Post structured RFQs, receive comparable quotations, and manage sourcing as a team with roles and permissions.</p>
            </div>
            <div class="fe-card rounded-2xl p-6">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                    <i class="fa-solid fa-store"></i>
                </span>
                <h3 class="text-base font-semibold mb-1.5" style="color:var(--fe-text);">For suppliers</h3>
                <p class="text-sm" style="color:var(--fe-text-muted);">Publish your catalog, get matched with eligible RFQ opportunities, and grow your reach into the education sector.</p>
            </div>
        </div>

        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-xl font-bold mb-3" style="font-family:var(--font-display);color:var(--fe-text);">Our procurement principles</h2>
            <p class="text-sm sm:text-base" style="color:var(--fe-text-muted);">
                Every RFQ, quotation and award on EduShopify follows a structured workflow — built for transparency, fair comparison, and accountable procurement decisions.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?php echo e(route('frontend.handoff.post-rfq')); ?>" class="fe-btn-primary fe-focus-ring px-5 py-2.5 rounded-lg text-sm font-semibold">Post an RFQ</a>
                <a href="<?php echo e(route('frontend.pages.contact')); ?>" class="fe-focus-ring px-5 py-2.5 rounded-lg text-sm font-semibold border bg-white" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Contact Us</a>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\pages\about.blade.php ENDPATH**/ ?>