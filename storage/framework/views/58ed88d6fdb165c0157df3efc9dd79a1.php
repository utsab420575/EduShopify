<?php $__env->startSection('title', 'Subscription Plans'); ?>
<?php $__env->startSection('breadcrumb', 'Subscription & Billing / Available Plans'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Subscription Plans','subtitle' => 'Select the right plan to scale your educational supply business on EduShopify.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Subscription Plans','subtitle' => 'Select the right plan to scale your educational supply business on EduShopify.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccefb989a1afce853acb3cdbc40307e)): ?>
<?php $attributes = $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e; ?>
<?php unset($__attributesOriginal6ccefb989a1afce853acb3cdbc40307e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccefb989a1afce853acb3cdbc40307e)): ?>
<?php $component = $__componentOriginal6ccefb989a1afce853acb3cdbc40307e; ?>
<?php unset($__componentOriginal6ccefb989a1afce853acb3cdbc40307e); ?>
<?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isCurrent = ($subscription ?? null) && $subscription->subscription_plan_id === $plan->id; ?>
            <div class="bg-white rounded-2xl border <?php echo e($isCurrent ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'); ?> p-6 flex flex-col justify-between relative shadow-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCurrent): ?>
                    <span class="absolute -top-3 right-6 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow">
                        Current Plan
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div>
                    <h3 class="text-lg font-bold text-gray-900"><?php echo e($plan->name); ?></h3>
                    <div class="mt-4 mb-6">
                        <span class="text-3xl font-extrabold text-gray-900">
                            <?php echo e($plan->is_free ? 'Free' : ($plan->currency_code . ' ' . number_format($plan->price, 0))); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$plan->is_free): ?>
                            <span class="text-xs text-gray-500">/ <?php echo e($plan->billing_type); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <ul class="space-y-3 text-xs text-gray-600 border-t border-gray-100 pt-4">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span><?php echo e($plan->max_active_listings ? $plan->max_active_listings . ' active listings' : 'Unlimited listings'); ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span><?php echo e($plan->max_monthly_quotations ? $plan->max_monthly_quotations . ' quotations/month' : 'Unlimited quotations'); ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span><?php echo e($plan->rfq_delay_minutes ? $plan->rfq_delay_minutes . ' min RFQ delay' : 'Instant RFQ access'); ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-indigo-600"></i>
                            <span><?php echo e($plan->max_team_members ? $plan->max_team_members . ' team member seats' : '1 team seat'); ?></span>
                        </li>
                    </ul>
                </div>

                <div class="mt-8 pt-4 border-t border-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCurrent): ?>
                        <button disabled class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-bold cursor-not-allowed">
                            Active Plan
                        </button>
                    <?php else: ?>
                        <a href="<?php echo e(route('supplier.subscribe', $plan->slug)); ?>" class="btn-primary w-full py-2.5 rounded-xl text-xs font-bold text-center block shadow-sm">
                            <?php echo e($plan->is_free ? 'Choose Free Plan' : 'Select Plan'); ?>

                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\subscription\plans.blade.php ENDPATH**/ ?>