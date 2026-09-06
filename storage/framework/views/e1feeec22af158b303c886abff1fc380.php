<?php $__env->startSection('title', 'Supplier Pricing — EduShopify'); ?>
<?php $__env->startSection('meta_description', 'Compare EduShopify subscription plans for suppliers.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="fe-container py-10 sm:py-14">
        <?php if (isset($component)) { $__componentOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3e0e8c17135e5e5e1d7f2557cd4e022e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.navigation.breadcrumbs','data' => ['items' => ['Pricing' => null]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::navigation.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Pricing' => null])]); ?>
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

        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Plans for every supplier</h1>
            <p class="mt-3 text-base" style="color:var(--fe-text-muted);">Transparent pricing to help you reach institutional buyers on EduShopify.</p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plans->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginaldd0cd4b649efa8747071108c282d437e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd0cd4b649efa8747071108c282d437e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'frontend.components.common.empty-state','data' => ['icon' => 'fa-tags','title' => 'No plans available right now','description' => 'Check back soon.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend::common.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-tags','title' => 'No plans available right now','description' => 'Check back soon.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $attributes = $__attributesOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__attributesOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd0cd4b649efa8747071108c282d437e)): ?>
<?php $component = $__componentOriginaldd0cd4b649efa8747071108c282d437e; ?>
<?php unset($__componentOriginaldd0cd4b649efa8747071108c282d437e); ?>
<?php endif; ?>
        <?php else: ?>
            <?php
                $planGridClass = match (min($plans->count(), 4)) {
                    1 => 'lg:grid-cols-1',
                    2 => 'lg:grid-cols-2',
                    3 => 'lg:grid-cols-3',
                    default => 'lg:grid-cols-4',
                };
            ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 <?php echo e($planGridClass); ?> gap-5 max-w-6xl mx-auto mb-14">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="fe-card rounded-2xl p-6 flex flex-col <?php echo e($plan->is_featured ? 'ring-2' : ''); ?>" <?php if($plan->is_featured): ?> style="--tw-ring-color:var(--fe-primary);" <?php endif; ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->is_featured): ?>
                            <span class="self-start mb-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" style="background:var(--fe-primary);color:#fff;">Recommended</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--fe-primary);"><?php echo e(ucfirst($plan->billing_type)); ?></p>
                        <h3 class="text-lg font-bold mb-1" style="color:var(--fe-text);font-family:var(--font-display);"><?php echo e($plan->name); ?></h3>
                        <p class="text-3xl font-bold mb-1" style="color:var(--fe-text);"><?php echo e($plan->formattedPrice()); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->trial_days > 0): ?>
                            <p class="text-xs mb-4" style="color:var(--fe-text-muted);"><?php echo e($plan->trial_days); ?>-day free trial</p>
                        <?php else: ?>
                            <p class="text-xs mb-4">&nbsp;</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <ul class="space-y-2 text-sm mb-6 flex-1" style="color:var(--fe-text-muted);">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_active_listings): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i><?php echo e($plan->max_active_listings); ?> active listings</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_products): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i><?php echo e($plan->max_products); ?> products</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_services): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i><?php echo e($plan->max_services); ?> services</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_team_members): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i><?php echo e($plan->max_team_members); ?> team members</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->max_monthly_quotations): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i><?php echo e($plan->max_monthly_quotations); ?> quotations/month</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->has_rfq_notifications): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>RFQ notifications</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->has_analytics): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Analytics dashboard</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->has_verified_badge): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Verified Supplier badge</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->has_homepage_placement): ?><li><i class="fa-solid fa-check text-xs mr-1.5" style="color:var(--fe-primary);"></i>Homepage placement eligibility</li><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((auth()->user()->accountMember?->account)?->isSupplier()): ?>
                                <a href="<?php echo e(route('supplier.pricing')); ?>" class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Manage Subscription</a>
                            <?php else: ?>
                                <a href="<?php echo e(route('register')); ?>" class="fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Become a Supplier</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo e(route('register')); ?>" class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Become a Supplier</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="text-center">
            <a href="<?php echo e(route('frontend.pages.faqs')); ?>" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">Have questions? See our FAQs &rarr;</a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\pages\pricing.blade.php ENDPATH**/ ?>