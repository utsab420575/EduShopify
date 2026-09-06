<?php $__env->startSection('title', 'My Quotations'); ?>
<?php $__env->startSection('breadcrumb', 'Quotations / All Quotations'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'My Quotations','subtitle' => 'Track quotation submissions, buyer reviews, shortlist status, and revision requests.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Quotations','subtitle' => 'Track quotation submissions, buyer reviews, shortlist status, and revision requests.']); ?>
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

    
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?php echo e(route('supplier.quotations.index')); ?>" class="text-xs font-semibold px-3 py-2 rounded-lg <?php echo e(!$status ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'); ?>">
                All
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('supplier.quotations.index', ['status' => $key])); ?>" class="text-xs font-semibold px-3 py-2 rounded-lg <?php echo e($status === $key ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'); ?>">
                    <?php echo e($label); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotations->isEmpty()): ?>
            <div class="p-8 text-center">
                <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-file-invoice','title' => 'No quotations found','description' => 'Browse available RFQ opportunities and submit your proposals.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-file-invoice','title' => 'No quotations found','description' => 'Browse available RFQ opportunities and submit your proposals.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $attributes = $__attributesOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $component = $__componentOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__componentOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Quotation / RFQ</th>
                            <th class="px-3 py-3.5 font-semibold">Buyer</th>
                            <th class="px-3 py-3.5 font-semibold">Total Amount</th>
                            <th class="px-3 py-3.5 font-semibold">Revision</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="<?php echo e(route('supplier.quotations.show', $quote)); ?>" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs">
                                        <?php echo e($quote->rfq?->title ?? 'RFQ #' . $quote->rfq_id); ?>

                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo e($quote->quotation_number); ?> &middot; <?php echo e($quote->submitted_at?->format('d M Y') ?? $quote->created_at->format('d M Y')); ?></p>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-600">
                                    <?php echo e($quote->rfq?->buyerAccount?->buyerProfile?->organization_name ?? $quote->rfq?->buyerAccount?->display_name); ?>

                                </td>
                                <td class="px-3 py-3.5 font-bold text-indigo-700 text-xs">
                                    <?php echo e($quote->currency_code); ?> <?php echo e(number_format($quote->grand_total, 2)); ?>

                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-500">
                                    <?php echo e($quote->current_revision_no > 0 ? 'Rev #'.$quote->current_revision_no : 'Draft'); ?>

                                </td>
                                <td class="px-3 py-3.5">
                                    <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $quote->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quote->status)]); ?>
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
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="<?php echo e(route('supplier.quotations.show', $quote)); ?>" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg inline-block">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotations->hasPages()): ?>
                <div class="p-4 border-t border-gray-100">
                    <?php echo e($quotations->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\procurement\quotations\index.blade.php ENDPATH**/ ?>