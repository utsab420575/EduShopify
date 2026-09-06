<?php $__env->startSection('title', 'Terms of Service — EduShopify'); ?>
<?php $__env->startSection('meta_description', 'EduShopify Terms of Service.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-10 sm:py-14 max-w-4xl mx-auto">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['Terms' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Terms' => null])]); ?>
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

        <h1 class="text-3xl font-bold tracking-tight mb-2" style="font-family:var(--font-display);color:var(--fe-text);">Terms of Service</h1>
        <p class="text-sm mb-8" style="color:var(--fe-text-muted);">Last updated: <?php echo e(now()->format('F j, Y')); ?></p>

        <div class="prose-sm max-w-none space-y-6 text-sm leading-relaxed" style="color:var(--fe-text-muted);">
            <p>These Terms of Service govern access to and use of the EduShopify marketplace. By creating an account or using the platform, you agree to these terms.</p>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">1. The Marketplace</h2>
                <p>EduShopify is a B2B education procurement marketplace connecting institutional Buyers with verified Suppliers through a structured Request for Quotation (RFQ) process. EduShopify facilitates discovery, sourcing and communication between Buyers and Suppliers.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">2. Accounts</h2>
                <p>You must provide accurate information when registering. Buyer and Supplier capabilities are subject to eligibility, approval and ongoing compliance with these terms.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">3. RFQs, Quotations and Purchase Orders</h2>
                <p>An RFQ posted by a Buyer is a structured sourcing request. A guest inquiry submitted through the public marketplace is not an RFQ, quotation, Award or Purchase Order. Official procurement actions require an authenticated, eligible account.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">4. Fulfilment & Payment</h2>
                <p>In this phase of the platform, payment and fulfilment for products and services occur outside EduShopify between the Buyer and Supplier. EduShopify processes Supplier subscription payments only.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">5. Conduct</h2>
                <p>Users must not misuse the platform, submit false information, or attempt to access data or accounts they are not authorized to access.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">6. Changes</h2>
                <p>We may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>
            </div>

            <p>For questions about these terms, please <a href="<?php echo e(route('frontend.pages.contact')); ?>" class="font-semibold" style="color:var(--fe-primary);">contact us</a>.</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\pages\terms.blade.php ENDPATH**/ ?>