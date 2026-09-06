<?php $__env->startSection('title', 'Payment'); ?>
<?php $__env->startSection('breadcrumb', 'Subscription & Billing / Payments / Detail'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => number_format($payment->amount, 2) . ' ' . $payment->currency_code,'subtitle' => 'Subscription payment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($payment->amount, 2) . ' ' . $payment->currency_code),'subtitle' => 'Subscription payment']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $payment->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($payment->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $attributes = $__attributesOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__attributesOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $component = $__componentOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__componentOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
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

    <div class="flex flex-wrap items-center gap-2 mb-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->status === 'pending'): ?>
            <form method="POST" action="<?php echo e(route('admin.billing.payments.mark-paid', $payment)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Mark as Paid</button>
            </form>
        <?php elseif($payment->status === 'paid'): ?>
            <button @click="$dispatch('open-modal-refund')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Refund</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Payment Details']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payment Details']); ?>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Provider</dt><dd class="font-medium text-gray-900"><?php echo e(ucfirst($payment->provider ?? '—')); ?></dd></div>
                    <div><dt class="text-gray-500">Provider Payment ID</dt><dd class="font-medium text-gray-900"><?php echo e($payment->provider_payment_id ?? '—'); ?></dd></div>
                    <div><dt class="text-gray-500">Paid At</dt><dd class="font-medium text-gray-900"><?php echo e($payment->paid_at?->format('d M Y H:i') ?? '—'); ?></dd></div>
                    <div><dt class="text-gray-500">Refunded At</dt><dd class="font-medium text-gray-900"><?php echo e($payment->refunded_at?->format('d M Y H:i') ?? '—'); ?></dd></div>
                </dl>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->failure_reason): ?>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Failure Reason</p>
                        <p class="text-sm text-gray-600"><?php echo e($payment->failure_reason); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $attributes = $__attributesOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $component = $__componentOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__componentOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
        </div>

        <div class="space-y-6">
            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Supplier']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Supplier']); ?>
                <p class="text-sm font-medium text-gray-900"><?php echo e($payment->supplierAccount?->supplierProfile?->display_name); ?></p>
                <a href="<?php echo e(route('admin.suppliers.show', $payment->supplierAccount)); ?>" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Supplier &rarr;</a>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $attributes = $__attributesOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $component = $__componentOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__componentOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->subscription): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Subscription']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Subscription']); ?>
                    <p class="text-sm font-medium text-gray-900"><?php echo e($payment->subscription->plan?->name); ?></p>
                    <a href="<?php echo e(route('admin.billing.subscriptions.show', $payment->subscription)); ?>" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Subscription &rarr;</a>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $attributes = $__attributesOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $component = $__componentOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__componentOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->status === 'paid'): ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'refund','title' => 'Refund Payment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'refund','title' => 'Refund Payment']); ?>
            <form method="POST" action="<?php echo e(route('admin.billing.payments.refund', $payment)); ?>">
                <?php echo csrf_field(); ?>
                <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'reason','label' => 'Reason','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reason','label' => 'Reason','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e)): ?>
<?php $attributes = $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e; ?>
<?php unset($__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e)): ?>
<?php $component = $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e; ?>
<?php unset($__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e); ?>
<?php endif; ?>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Refund</button>
                </div>
            </form>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5845bcee7aa8bdff54827061cf154d18)): ?>
<?php $attributes = $__attributesOriginal5845bcee7aa8bdff54827061cf154d18; ?>
<?php unset($__attributesOriginal5845bcee7aa8bdff54827061cf154d18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5845bcee7aa8bdff54827061cf154d18)): ?>
<?php $component = $__componentOriginal5845bcee7aa8bdff54827061cf154d18; ?>
<?php unset($__componentOriginal5845bcee7aa8bdff54827061cf154d18); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\billing\payments\show.blade.php ENDPATH**/ ?>