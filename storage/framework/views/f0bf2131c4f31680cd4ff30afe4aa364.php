<?php $__env->startSection('title', 'Achievements'); ?>
<?php $__env->startSection('breadcrumb', 'Achievements / Achievement List'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Achievements','subtitle' => 'Master catalogue of badges accounts can request and be awarded.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Achievements','subtitle' => 'Master catalogue of badges accounts can request and be awarded.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <button type="button"
                    @click="$dispatch('open-modal-create-achievement')"
                    class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i> New Achievement
            </button>
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
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search achievements..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" <?php if($status === 'active'): echo 'selected'; endif; ?>>Active</option>
                    <option value="inactive" <?php if($status === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $status): ?>
                    <a href="<?php echo e(route('admin.achievements.index')); ?>" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($achievements->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-trophy','title' => 'No achievements found','description' => 'Create an achievement badge accounts can request.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-trophy','title' => 'No achievements found','description' => 'Create an achievement badge accounts can request.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $attributes = $__attributesOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $component = $__componentOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__componentOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
        <?php else: ?>
             <?php $__env->slot('head', null, []); ?> 
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Achievement</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requirement</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Claims</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                                <i class="fa-solid <?php echo e($achievement->badge_icon ?: 'fa-trophy'); ?> text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900"><?php echo e($achievement->name); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($achievement->description): ?>
                                    <p class="text-xs text-gray-500 line-clamp-1 max-w-sm"><?php echo e($achievement->description); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($achievement->type ?: '—'); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 font-mono"><?php echo e($achievement->requirement_value ?? '—'); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($achievement->account_achievements_count); ?></td>
                    <td class="px-5 py-3.5">
                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $achievement->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement->is_active ? 'active' : 'inactive')]); ?>
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
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-achievement-<?php echo e($achievement->id); ?>')"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition-colors"
                                    title="Edit Achievement">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            <form method="POST"
                                  action="<?php echo e(route('admin.achievements.destroy', $achievement)); ?>"
                                  onsubmit="return confirmSwal(this, 'Delete Achievement?', 'Are you sure you want to delete <?php echo e(addslashes($achievement->name)); ?>?', 'warning', 'Yes, Delete')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                        title="Delete Achievement">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

         <?php $__env->slot('pagination', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $achievements]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievements)]); ?>
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

    
    <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'create-achievement','title' => 'New Achievement','width' => 'max-w-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'create-achievement','title' => 'New Achievement','width' => 'max-w-xl']); ?>
        <form method="POST" action="<?php echo e(route('admin.achievements.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'name','label' => 'Achievement Name','placeholder' => 'e.g. Top Rated Supplier','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => 'Achievement Name','placeholder' => 'e.g. Top Rated Supplier','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'description','label' => 'Description','placeholder' => 'What does an account need to do to earn this?','rows' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'description','label' => 'Description','placeholder' => 'What does an account need to do to earn this?','rows' => '2']); ?>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'badge_icon','label' => 'Badge Icon','placeholder' => 'fa-trophy','hint' => 'Font Awesome solid icon class']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'badge_icon','label' => 'Badge Icon','placeholder' => 'fa-trophy','hint' => 'Font Awesome solid icon class']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'type','label' => 'Type','placeholder' => 'e.g. sales, tenure, quality']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'type','label' => 'Type','placeholder' => 'e.g. sales, tenure, quality']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'requirement_value','label' => 'Requirement Value','type' => 'number','placeholder' => 'e.g. 100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'requirement_value','label' => 'Requirement Value','type' => 'number','placeholder' => 'e.g. 100']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                    <label for="create_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Active / Requestable</label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">Cancel</button>
                <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Create Achievement
                </button>
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'edit-achievement-'.$achievement->id,'title' => 'Edit Achievement: '.$achievement->name,'width' => 'max-w-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('edit-achievement-'.$achievement->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Edit Achievement: '.$achievement->name),'width' => 'max-w-xl']); ?>
            <form method="POST" action="<?php echo e(route('admin.achievements.update', $achievement)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'name','label' => 'Achievement Name','value' => $achievement->name,'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => 'Achievement Name','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement->name),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'description','label' => 'Description','value' => $achievement->description,'rows' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'description','label' => 'Description','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement->description),'rows' => '2']); ?>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'badge_icon','label' => 'Badge Icon','value' => $achievement->badge_icon,'hint' => 'Font Awesome solid icon class']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'badge_icon','label' => 'Badge Icon','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement->badge_icon),'hint' => 'Font Awesome solid icon class']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'type','label' => 'Type','value' => $achievement->type]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'type','label' => 'Type','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement->type)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                    <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'requirement_value','label' => 'Requirement Value','type' => 'number','value' => $achievement->requirement_value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'requirement_value','label' => 'Requirement Value','type' => 'number','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($achievement->requirement_value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" name="is_active" id="edit_is_active_<?php echo e($achievement->id); ?>" value="1" <?php if($achievement->is_active): echo 'checked'; endif; ?> class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                        <label for="edit_is_active_<?php echo e($achievement->id); ?>" class="text-sm font-medium text-gray-700 cursor-pointer">Active / Requestable</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-check"></i> Update Achievement
                    </button>
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
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\achievements\index.blade.php ENDPATH**/ ?>