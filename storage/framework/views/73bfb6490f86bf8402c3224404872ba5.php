<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-[70vh] flex items-center justify-center py-20 px-4">
        <div class="max-w-lg w-full text-center space-y-6">

            
            <div class="w-20 h-20 mx-auto bg-emerald-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-extrabold text-slate-900 font-display">You're all set! 🎉</h1>
                <p class="text-slate-500 text-base">Your subscription has been activated successfully.</p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscription): ?>
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-left space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Plan</span>
                    <span class="text-slate-900 font-bold"><?php echo e($subscription->plan?->name ?? '—'); ?></span>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-100 pt-3">
                    <span class="text-slate-500 font-medium">Status</span>
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-bold">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        Active
                    </span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscription->expires_at): ?>
                <div class="flex justify-between text-sm border-t border-slate-100 pt-3">
                    <span class="text-slate-500 font-medium">Expires</span>
                    <span class="text-slate-900 font-bold"><?php echo e($subscription->expires_at->format('d M Y')); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscription->plan?->bonus_days > 0): ?>
                <div class="flex justify-between text-sm border-t border-slate-100 pt-3">
                    <span class="text-slate-500 font-medium">Bonus Days</span>
                    <span class="text-amber-600 font-bold">+<?php echo e($subscription->plan->bonus_days); ?> days included</span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="<?php echo e(route('supplier.dashboard')); ?>"
                   class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/20 transition-all duration-200 hover:-translate-y-0.5">
                    Go to Dashboard
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\supplier\checkout\success.blade.php ENDPATH**/ ?>