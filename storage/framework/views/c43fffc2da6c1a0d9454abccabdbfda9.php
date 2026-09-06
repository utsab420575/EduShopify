<?php $__env->startSection('title', 'Purchase Orders'); ?>
<?php $__env->startSection('breadcrumb', 'Procurement / Purchase Orders'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Purchase Orders','subtitle' => 'Orders issued after an award is accepted by the supplier.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Purchase Orders','subtitle' => 'Orders issued after an award is accepted by the supplier.']); ?>
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

    <?php if (isset($component)) { $__componentOriginalb5402a608f4ff404f07800485e35a084 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5402a608f4ff404f07800485e35a084 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.tabs','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if (isset($component)) { $__componentOriginalfda544832f91f39c42466108e87ef294 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfda544832f91f39c42466108e87ef294 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.tab','data' => ['href' => route('buyer.purchase-orders.index'),'active' => $status === '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.purchase-orders.index')),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status === '')]); ?>All <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $attributes = $__attributesOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__attributesOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $component = $__componentOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__componentOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginalfda544832f91f39c42466108e87ef294 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfda544832f91f39c42466108e87ef294 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.tab','data' => ['href' => route('buyer.purchase-orders.index', ['status' => $value]),'active' => $status === $value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.purchase-orders.index', ['status' => $value])),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status === $value)]); ?><?php echo e($label); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $attributes = $__attributesOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__attributesOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $component = $__componentOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__componentOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5402a608f4ff404f07800485e35a084)): ?>
<?php $attributes = $__attributesOriginalb5402a608f4ff404f07800485e35a084; ?>
<?php unset($__attributesOriginalb5402a608f4ff404f07800485e35a084); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5402a608f4ff404f07800485e35a084)): ?>
<?php $component = $__componentOriginalb5402a608f4ff404f07800485e35a084; ?>
<?php unset($__componentOriginalb5402a608f4ff404f07800485e35a084); ?>
<?php endif; ?>

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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-clipboard-list','title' => 'No purchase orders yet','description' => 'Purchase orders are created automatically once a supplier accepts an award.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-clipboard-list','title' => 'No purchase orders yet','description' => 'Purchase orders are created automatically once a supplier accepts an award.']); ?>
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
                    <?php if (isset($component)) { $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'po_number','label' => 'PO #']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'po_number','label' => 'PO #']); ?>
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500"><?php echo e($orders->firstItem() + $loop->index); ?></td>
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900"><?php echo e($po->po_number); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($po->rfq->title); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($po->supplierAccount?->supplierProfile?->display_name); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium"><?php echo e(number_format($po->grand_total, 2)); ?> <?php echo e($po->currency_code); ?></td>
                    <td class="px-5 py-3.5"><?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $po->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($po->status)]); ?>
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
                        <a href="<?php echo e(route('buyer.purchase-orders.show', $po)); ?>" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php $__env->slot('pagination', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $orders]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($orders)]); ?>
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.buyer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\procurement\purchase-orders\index.blade.php ENDPATH**/ ?>