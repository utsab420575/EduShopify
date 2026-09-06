<?php $__env->startSection('title', $opportunity->title.' ('.$opportunity->rfq_number.') — EduShopify'); ?>
<?php $__env->startSection('meta_description', 'Public RFQ opportunity summary on EduShopify.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $location = collect([$opportunity->delivery_city, $opportunity->delivery_state, $opportunity->delivery_country])->filter()->implode(', ');
    ?>

    <div class="fe-container py-6 sm:py-8 max-w-4xl mx-auto">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => [
            'Opportunities' => route('frontend.rfqs.index'),
            $opportunity->rfq_number => null,
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            'Opportunities' => route('frontend.rfqs.index'),
            $opportunity->rfq_number => null,
        ])]); ?>
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

        <div class="fe-card rounded-2xl p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                <div>
                    <p class="text-xs font-mono mb-1" style="color:var(--fe-text-subtle);"><?php echo e($opportunity->rfq_number); ?></p>
                    <h1 class="text-xl sm:text-2xl font-bold" style="font-family:var(--font-display);color:var(--fe-text);"><?php echo e($opportunity->title); ?></h1>
                </div>
                <?php if (isset($component)) { $__componentOriginal3edc6e413075cef70a8c09841aea3483 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3edc6e413075cef70a8c09841aea3483 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.badge','data' => ['variant' => 'verified']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified']); ?><span class="w-1.5 h-1.5 rounded-full" style="background:var(--fe-primary);"></span> Open <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $attributes = $__attributesOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__attributesOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3edc6e413075cef70a8c09841aea3483)): ?>
<?php $component = $__componentOriginal3edc6e413075cef70a8c09841aea3483; ?>
<?php unset($__componentOriginal3edc6e413075cef70a8c09841aea3483); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-5 border-y" style="border-color:var(--fe-border);">
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Category</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);"><?php echo e($opportunity->category_summary ?: '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Delivery Location</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);"><?php echo e($location ?: '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Items</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);"><?php echo e($opportunity->item_count); ?></p>
                </div>
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Quotation Deadline</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);"><?php echo e($opportunity->quotation_deadline?->format('M j, Y') ?? '—'); ?></p>
                </div>
            </div>

            <div class="py-5 space-y-3 text-sm" style="color:var(--fe-text-muted);">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->item_types): ?>
                    <p><strong style="color:var(--fe-text);">Item types:</strong> <?php echo e(str_replace(',', ', ', $opportunity->item_types)); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunity->expected_delivery_date): ?>
                    <p><strong style="color:var(--fe-text);">Expected delivery:</strong> <?php echo e($opportunity->expected_delivery_date->format('M j, Y')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p><strong style="color:var(--fe-text);">Published:</strong> <?php echo e($opportunity->published_at?->format('M j, Y') ?? '—'); ?></p>
                <p><strong style="color:var(--fe-text);">Quotations received so far:</strong> <?php echo e($opportunity->quotations_count); ?></p>
            </div>

            <div class="rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between" style="background:var(--fe-surface-soft);">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-lock mt-0.5" style="color:var(--fe-text-subtle);"></i>
                    <p class="text-sm" style="color:var(--fe-text-muted);">
                        Full item specifications, buyer details and quotation submission are only available to eligible, authenticated suppliers.
                    </p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('frontend.handoff.submit-quotation', $opportunity->rfq_number)); ?>" class="fe-btn-primary fe-focus-ring shrink-0 px-5 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap">
                        View Full Opportunity
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('frontend.handoff.submit-quotation', $opportunity->rfq_number)); ?>" class="fe-btn-primary fe-focus-ring shrink-0 px-5 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap">
                        Login / Register to Continue
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="<?php echo e(route('frontend.rfqs.index')); ?>" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">&larr; Back to all opportunities</a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\rfqs\show.blade.php ENDPATH**/ ?>