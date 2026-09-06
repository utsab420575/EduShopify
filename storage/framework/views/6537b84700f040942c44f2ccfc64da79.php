<?php $__env->startSection('title', 'FAQs — EduShopify'); ?>
<?php $__env->startSection('meta_description', 'Frequently asked questions about buying, selling and using EduShopify.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $faqGroups = [
            'Buyer Registration' => [
                ['q' => 'How do I register as a Buyer?', 'a' => 'Click Join Free or Register, choose the Buyer capability, and complete your account and profile details.'],
                ['q' => 'Is registration free for Buyers?', 'a' => 'Yes, creating a Buyer account and posting RFQs is free.'],
            ],
            'Supplier Verification' => [
                ['q' => 'How does Supplier verification work?', 'a' => 'After registering as a Supplier, you complete your company profile and upload required documents for Admin review before your account is approved.'],
                ['q' => 'How long does approval take?', 'a' => 'Review times vary, but our team reviews applications as quickly as possible.'],
            ],
            'RFQs & Quotations' => [
                ['q' => 'What is an RFQ?', 'a' => 'A Request for Quotation is a structured sourcing request a Buyer posts describing what they need. Suppliers respond with quotations.'],
                ['q' => 'Can I submit a quotation without an account?', 'a' => 'No — submitting an official quotation requires an approved, eligible Supplier account.'],
            ],
            'Subscription & Payments' => [
                ['q' => 'Do Suppliers need a subscription?', 'a' => 'Yes, Suppliers select a subscription plan that determines listing limits, RFQ access and features.'],
                ['q' => 'Does EduShopify process product payments?', 'a' => 'In this phase, product and service payment happens outside the platform. EduShopify processes Supplier subscription payments only.'],
            ],
            'Privacy & Support' => [
                ['q' => 'Is my RFQ visible to everyone?', 'a' => 'Only RFQs marked as globally visible appear on the public opportunities board with a safe summary. Selected-Supplier RFQs are never shown publicly.'],
                ['q' => 'How do I get help?', 'a' => 'Use our Contact page, or reach out from your dashboard support section once logged in.'],
            ],
        ];
    ?>

    <div class="fe-container py-10 sm:py-14 max-w-3xl mx-auto">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['FAQs' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['FAQs' => null])]); ?>
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

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Frequently asked questions</h1>
        </div>

        <div class="space-y-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $faqGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide mb-3" style="color:var(--fe-primary);"><?php echo e($group); ?></h2>
                    <div class="fe-card rounded-2xl divide-y" style="border-color:var(--fe-border);">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div x-data="{ open: false }" class="px-5" style="border-color:var(--fe-border);">
                                <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-3 py-4 text-left fe-focus-ring" :aria-expanded="open.toString()">
                                    <span class="text-sm font-semibold" style="color:var(--fe-text);"><?php echo e($item['q']); ?></span>
                                    <i class="fa-solid fa-chevron-down text-xs shrink-0 transition-transform" :class="open && 'rotate-180'" style="color:var(--fe-text-subtle);"></i>
                                </button>
                                <div x-show="open" x-cloak x-transition class="pb-4 text-sm" style="color:var(--fe-text-muted);">
                                    <?php echo e($item['a']); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="text-center mt-10">
            <p class="text-sm" style="color:var(--fe-text-muted);">Still have questions?</p>
            <a href="<?php echo e(route('frontend.pages.contact')); ?>" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">Contact our team &rarr;</a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\pages\faqs.blade.php ENDPATH**/ ?>