<?php $__env->startSection('title', 'Approval Center — Custom Attribute Values'); ?>
<?php $__env->startSection('breadcrumb', 'Platform Governance / Approval Center / Custom Attribute Values'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Approval Center','subtitle' => 'Custom Attribute Values — review supplier-submitted &quot;Other&quot; values.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Approval Center','subtitle' => 'Custom Attribute Values — review supplier-submitted &quot;Other&quot; values.']); ?>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            <?php if (isset($component)) { $__componentOriginal9f270ab1af9c8c53a0344ef50975b8f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f270ab1af9c8c53a0344ef50975b8f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.table-search','data' => ['title' => 'Decided','count' => $decided->total(),'searchParam' => 'decided_search','pageParam' => 'decided_page','currentSearch' => $decidedSearch,'placeholder' => 'Search attribute or value...','filterParams' => ['decided_status'],'hasActiveFilter' => $decidedStatus !== '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.table-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Decided','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decided->total()),'search-param' => 'decided_search','page-param' => 'decided_page','current-search' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedSearch),'placeholder' => 'Search attribute or value...','filter-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['decided_status']),'has-active-filter' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedStatus !== '')]); ?>
                 <?php $__env->slot('filters', null, []); ?> 
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                        <select name="decided_status" onchange="this.form.submit()"
                                class="focus-accent w-40 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Statuses</option>
                            <option value="ignored" <?php if($decidedStatus === 'ignored'): echo 'selected'; endif; ?>>Ignored</option>
                            <option value="approved" <?php if($decidedStatus === 'approved'): echo 'selected'; endif; ?>>Approved</option>
                        </select>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($decidedStatus !== ''): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['decided_status' => null, 'decided_page' => null])); ?>"
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($decided->isEmpty()): ?>
                <div class="p-10 text-center">
                    <div class="w-10 h-10 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <p class="text-xs text-gray-400"><?php echo e($decidedSearch !== '' ? 'No decisions match "'.$decidedSearch.'".' : 'No decisions recorded yet.'); ?></p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php if (isset($component)) { $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'attribute_name','label' => 'Attribute','sortParam' => 'decided_sort','directionParam' => 'decided_direction','pageParam' => 'decided_page','currentSort' => $decidedSort,'currentDirection' => $decidedDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'attribute_name','label' => 'Attribute','sort-param' => 'decided_sort','direction-param' => 'decided_direction','page-param' => 'decided_page','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedSort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedDirection)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'custom_value','label' => 'Custom Value','sortParam' => 'decided_sort','directionParam' => 'decided_direction','pageParam' => 'decided_page','currentSort' => $decidedSort,'currentDirection' => $decidedDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'custom_value','label' => 'Custom Value','sort-param' => 'decided_sort','direction-param' => 'decided_direction','page-param' => 'decided_page','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedSort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedDirection)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'status','label' => 'Status','sortParam' => 'decided_sort','directionParam' => 'decided_direction','pageParam' => 'decided_page','currentSort' => $decidedSort,'currentDirection' => $decidedDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'status','label' => 'Status','sort-param' => 'decided_sort','direction-param' => 'decided_direction','page-param' => 'decided_page','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedSort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decidedDirection)]); ?>
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
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Used By</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php ($previousStatus = null); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $decided; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($decidedSort === '' && $review->status !== $previousStatus): ?>
                                    <tr class="bg-gray-50/80">
                                        <td colspan="5" class="px-5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                            <?php echo e(ucfirst($review->status)); ?>

                                        </td>
                                    </tr>
                                    <?php ($previousStatus = $review->status); ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800"><?php echo e($review->attribute?->name ?? 'Unknown attribute'); ?></td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($review->custom_value); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->status === 'approved' && $review->resultingAttributeValue): ?>
                                            <p class="text-[11px] text-gray-400">now official as <span class="font-medium text-gray-600"><?php echo e($review->resultingAttributeValue->value); ?></span></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            <?php echo e($review->status === 'ignored' ? 'bg-gray-100 text-gray-600 border border-gray-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'); ?>">
                                            <?php echo e(ucfirst($review->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-sm text-gray-600"><?php echo e($review->usage_count); ?> <?php echo e(Str::plural('listing', $review->usage_count)); ?></td>
                                    <td class="px-5 py-3.5 text-right">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->status === 'ignored'): ?>
                                            <button type="button" @click="$dispatch('open-modal-approve-decided-<?php echo e($review->id); ?>')"
                                                    class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">
                                                Promote
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="<?php echo e(route('admin.catalog.custom-attribute-values.ignore')); ?>"
                                                  onsubmit="return confirmSwal(this, 'Stop Treating as Official?', 'This only changes the review record — the &quot;<?php echo e(addslashes($review->resultingAttributeValue?->value ?? $review->custom_value)); ?>&quot; option and every listing already using it are left exactly as they are.', 'warning', 'Yes, Mark Ignored')"
                                                  class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="attribute_id" value="<?php echo e($review->attribute_id); ?>">
                                                <input type="hidden" name="custom_value" value="<?php echo e($review->custom_value); ?>">
                                                <button type="submit" class="px-3 py-1 rounded bg-gray-200 text-gray-700 font-semibold text-xs">Ignore</button>
                                            </form>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                    <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $decided]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($decided)]); ?>
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
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            
            <?php if (isset($component)) { $__componentOriginal9f270ab1af9c8c53a0344ef50975b8f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f270ab1af9c8c53a0344ef50975b8f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.table-search','data' => ['title' => 'Pending Review','count' => $pending->total(),'searchParam' => 'pending_search','pageParam' => 'pending_page','currentSearch' => $pendingSearch,'placeholder' => 'Search attribute or value...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.table-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pending Review','count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pending->total()),'search-param' => 'pending_search','page-param' => 'pending_page','current-search' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingSearch),'placeholder' => 'Search attribute or value...']); ?>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pending->isEmpty()): ?>
                <div class="p-10 text-center">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <p class="text-xs text-gray-400"><?php echo e($pendingSearch !== '' ? 'No pending values match "'.$pendingSearch.'".' : 'No custom values waiting for review.'); ?></p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php if (isset($component)) { $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'attribute_name','label' => 'Attribute','sortParam' => 'pending_sort','directionParam' => 'pending_direction','pageParam' => 'pending_page','currentSort' => $pendingSort,'currentDirection' => $pendingDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'attribute_name','label' => 'Attribute','sort-param' => 'pending_sort','direction-param' => 'pending_direction','page-param' => 'pending_page','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingSort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingDirection)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'custom_value','label' => 'Custom Value','sortParam' => 'pending_sort','directionParam' => 'pending_direction','pageParam' => 'pending_page','currentSort' => $pendingSort,'currentDirection' => $pendingDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'custom_value','label' => 'Custom Value','sort-param' => 'pending_sort','direction-param' => 'pending_direction','page-param' => 'pending_page','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingSort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingDirection)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'usage_count','label' => 'Used By','sortParam' => 'pending_sort','directionParam' => 'pending_direction','pageParam' => 'pending_page','currentSort' => $pendingSort,'currentDirection' => $pendingDirection]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'usage_count','label' => 'Used By','sort-param' => 'pending_sort','direction-param' => 'pending_direction','page-param' => 'pending_page','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingSort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingDirection)]); ?>
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
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800"><?php echo e($item->attribute_name); ?></td>
                                    <td class="px-4 py-3.5 text-sm font-semibold text-gray-900"><?php echo e($item->custom_value); ?></td>
                                    <td class="px-4 py-3.5 text-sm text-gray-600"><?php echo e($item->usage_count); ?> <?php echo e(Str::plural('listing', $item->usage_count)); ?></td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="$dispatch('open-modal-approve-pending-<?php echo e($loop->index); ?>')"
                                                    class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">
                                                Approve
                                            </button>
                                            <form method="POST" action="<?php echo e(route('admin.catalog.custom-attribute-values.ignore')); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="attribute_id" value="<?php echo e($item->attribute_id); ?>">
                                                <input type="hidden" name="custom_value" value="<?php echo e($item->custom_value); ?>">
                                                <button type="submit" class="px-3 py-1 rounded bg-gray-200 text-gray-700 font-semibold text-xs">Ignore</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                    <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $pending]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pending)]); ?>
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
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($existingForPending = $existingValuesByAttribute[$item->attribute_id] ?? collect()); ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'approve-pending-'.$loop->index,'title' => 'Promote to Official Value?','width' => 'max-w-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('approve-pending-'.$loop->index),'title' => 'Promote to Official Value?','width' => 'max-w-lg']); ?>
            <?php echo $__env->make('backend.admin.approvals.partials.custom-value-duplicate-check', [
                'attributeName' => $item->attribute_name,
                'attributeId'   => $item->attribute_id,
                'customValue'   => $item->custom_value,
                'usageCount'    => $item->usage_count,
                'existing'      => $existingForPending,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $decided; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->status === 'ignored'): ?>
            <?php ($existingForDecided = $existingValuesByAttribute[$review->attribute_id] ?? collect()); ?>
            <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'approve-decided-'.$review->id,'title' => 'Promote to Official Value?','width' => 'max-w-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('approve-decided-'.$review->id),'title' => 'Promote to Official Value?','width' => 'max-w-lg']); ?>
                <?php echo $__env->make('backend.admin.approvals.partials.custom-value-duplicate-check', [
                    'attributeName' => $review->attribute?->name ?? 'Unknown attribute',
                    'attributeId'   => $review->attribute_id,
                    'customValue'   => $review->custom_value,
                    'usageCount'    => $review->usage_count,
                    'existing'      => $existingForDecided,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\approvals\custom-attribute-values.blade.php ENDPATH**/ ?>