<?php $__env->startSection('title', 'Received Quotations'); ?>
<?php $__env->startSection('breadcrumb', 'Procurement / Quotations'); ?>

<?php
    $compareRfqId = $rfq ?: null;
?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Received Quotations','subtitle' => 'Quotations submitted by suppliers against your RFQs.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Received Quotations','subtitle' => 'Quotations submitted by suppliers against your RFQs.']); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($compareRfqId): ?>
             <?php $__env->slot('actions', null, []); ?> 
                <a href="<?php echo e(route('buyer.quotations.compare', $compareRfqId)); ?>" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" x-data="compareTray(<?php echo e($compareRfqId); ?>, <?php echo e($maxCompareItems); ?>)">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Compare Quotations <span x-show="count > 0" x-cloak x-text="'(' + count + ')'"></span>
                </a>
             <?php $__env->endSlot(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($compareRfqId): ?>
        <p class="text-xs text-gray-500 -mt-4 mb-6">Check "Add to Compare" on 2–5 quotations below, then use "Compare Quotations" to view them side by side. Selections only apply to this RFQ.</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal72603426510669aaf343a2492f1d2357 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72603426510669aaf343a2492f1d2357 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('toolbar', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal9f270ab1af9c8c53a0344ef50975b8f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f270ab1af9c8c53a0344ef50975b8f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.table-search','data' => ['title' => 'Quotations','count' => $quotations->total(),'searchParam' => 'search','pageParam' => 'page','currentSearch' => $search,'placeholder' => 'Search quote # or supplier...','filterParams' => ['rfq', 'status'],'hasActiveFilter' => $rfq || $status !== '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.table-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quotations','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotations->total()),'search-param' => 'search','page-param' => 'page','current-search' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($search),'placeholder' => 'Search quote # or supplier...','filter-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['rfq', 'status']),'has-active-filter' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rfq || $status !== '')]); ?>
                 <?php $__env->slot('filters', null, []); ?> 
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">RFQ</label>
                        <select name="rfq" onchange="this.form.submit()" class="focus-accent w-52 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All RFQs</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rfqOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($option->id); ?>" <?php if($rfq === $option->id): echo 'selected'; endif; ?>><?php echo e($option->title); ?> (<?php echo e($option->rfq_number); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                        <select name="status" onchange="this.form.submit()" class="focus-accent w-40 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Statuses</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if($status === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rfq || $status !== ''): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['rfq' => null, 'status' => null, 'page' => null])); ?>"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f270ab1af9c8c53a0344ef50975b8f3)): ?>
<?php $attributes = $__attributesOriginal9f270ab1af9c8c53a0344ef50975b8f3; ?>
<?php unset($__attributesOriginal9f270ab1af9c8c53a0344ef50975b8f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f270ab1af9c8c53a0344ef50975b8f3)): ?>
<?php $component = $__componentOriginal9f270ab1af9c8c53a0344ef50975b8f3; ?>
<?php unset($__componentOriginal9f270ab1af9c8c53a0344ef50975b8f3); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotations->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-inbox','title' => 'No quotations found','description' => 'There are no quotations matching the selected filters.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-inbox','title' => 'No quotations found','description' => 'There are no quotations matching the selected filters.']); ?>
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
             <?php $__env->endSlot(); ?>
        <?php else: ?>
             <?php $__env->slot('head', null, []); ?> 
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($compareRfqId): ?>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Compare</th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <?php if (isset($component)) { $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'quotation_number','label' => 'Quote #']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'quotation_number','label' => 'Quote #']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c)): ?>
<?php $attributes = $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c; ?>
<?php unset($__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5fa38c9a3f111a520f583b1baa357d2c)): ?>
<?php $component = $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c; ?>
<?php unset($__componentOriginal5fa38c9a3f111a520f583b1baa357d2c); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'grand_total','label' => 'Total','align' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'grand_total','label' => 'Total','align' => 'right']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c)): ?>
<?php $attributes = $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c; ?>
<?php unset($__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5fa38c9a3f111a520f583b1baa357d2c)): ?>
<?php $component = $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c; ?>
<?php unset($__componentOriginal5fa38c9a3f111a520f583b1baa357d2c); ?>
<?php endif; ?>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500"><?php echo e($quotations->firstItem() + $loop->index); ?></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($compareRfqId): ?>
                        <td class="px-5 py-3.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $quotation->rfq_id === $compareRfqId && in_array($quotation->status, $compareEligibleStatuses, true)): ?>
                                <label class="inline-flex items-center gap-2 cursor-pointer" x-data="compareCheckbox(<?php echo e($compareRfqId); ?>, <?php echo e($quotation->id); ?>)" data-max-items="<?php echo e($maxCompareItems); ?>">
                                    <input type="checkbox" x-model="checked" @change="toggle" class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                                    <span class="text-xs text-gray-500">Add to Compare</span>
                                </label>
                            <?php else: ?>
                                <span class="text-xs text-gray-300">&mdash;</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($quotation->supplierAccount?->supplierProfile?->display_name); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($quotation->submitted_at?->format('d M Y')); ?></p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($quotation->rfq->title); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($quotation->quotation_number); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium"><?php echo e(number_format($quotation->grand_total, 2)); ?> <?php echo e($quotation->currency_code); ?></td>
                    <td class="px-5 py-3.5"><?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $quotation->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $attributes = $__attributesOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__attributesOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $component = $__componentOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__componentOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="<?php echo e(route('buyer.quotations.show', $quotation)); ?>" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php $__env->slot('pagination', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $quotations]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotations)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a8b7815bf55366b2036b8aef8f795af)): ?>
<?php $attributes = $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af; ?>
<?php unset($__attributesOriginal3a8b7815bf55366b2036b8aef8f795af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a8b7815bf55366b2036b8aef8f795af)): ?>
<?php $component = $__componentOriginal3a8b7815bf55366b2036b8aef8f795af; ?>
<?php unset($__componentOriginal3a8b7815bf55366b2036b8aef8f795af); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72603426510669aaf343a2492f1d2357)): ?>
<?php $attributes = $__attributesOriginal72603426510669aaf343a2492f1d2357; ?>
<?php unset($__attributesOriginal72603426510669aaf343a2492f1d2357); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72603426510669aaf343a2492f1d2357)): ?>
<?php $component = $__componentOriginal72603426510669aaf343a2492f1d2357; ?>
<?php unset($__componentOriginal72603426510669aaf343a2492f1d2357); ?>
<?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($compareRfqId): ?>
        <div
            x-data="compareTray(<?php echo e($compareRfqId); ?>, <?php echo e($maxCompareItems); ?>)"
            x-show="count > 0"
            x-cloak
            class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 shadow-[0_-4px_12px_rgba(0,0,0,0.06)]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
                <p class="text-sm text-gray-600"><span class="font-semibold text-gray-900" x-text="count"></span> quotation<span x-show="count !== 1">s</span> selected for comparison (max <?php echo e($maxCompareItems); ?>).</p>
                <a href="<?php echo e(route('buyer.quotations.compare', $compareRfqId)); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                    <i class="fa-solid fa-scale-balanced mr-1"></i> Compare Quotations
                </a>
            </div>
        </div>
        <div class="h-16"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('backend.buyer.procurement.quotations.partials._compare-store', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.buyer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\procurement\quotations\index.blade.php ENDPATH**/ ?>