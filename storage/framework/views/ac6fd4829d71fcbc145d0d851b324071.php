<?php $__env->startSection('title', 'Document Enables'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Document Enables'); ?>

<?php $__env->startSection('body'); ?>

    
    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Document Enables','subtitle' => 'Configure which compliance documents are enabled, mandatory (Required), or optional for each capability.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Document Enables','subtitle' => 'Configure which compliance documents are enabled, mandatory (Required), or optional for each capability.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <button type="button"
                    @click="$dispatch('open-modal-create-document-enable')"
                    class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i> Enable Document for Capability
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

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="fa-solid fa-layer-group text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Total Configured</p>
                <p class="text-xl font-bold text-gray-900"><?php echo e($totalCount); ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                <i class="fa-solid fa-asterisk text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Required (Mandatory)</p>
                <p class="text-xl font-bold text-rose-600"><?php echo e($requiredCount); ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Optional Documents</p>
                <p class="text-xl font-bold text-emerald-600"><?php echo e($optionalCount); ?></p>
            </div>
        </div>
    </div>

    
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
                    <input type="text"
                           name="search"
                           value="<?php echo e($search); ?>"
                           placeholder="Search document name or description..."
                           class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>

                
                <select name="capability_id" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Capabilities</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $capabilityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cap->id); ?>" <?php if((string)$capabilityId === (string)$cap->id): echo 'selected'; endif; ?>><?php echo e($cap->name); ?> Capability</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>

                
                <select name="requirement" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Requirements</option>
                    <option value="required" <?php if($requirement === 'required'): echo 'selected'; endif; ?>>Required (Mandatory)</option>
                    <option value="optional" <?php if($requirement === 'optional'): echo 'selected'; endif; ?>>Optional</option>
                </select>

                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($search) || !empty($capabilityId) || !empty($requirement)): ?>
                    <a href="<?php echo e(route('admin.catalog.document-type-enables.index')); ?>" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($enables->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-file-shield','title' => 'No document enables found','description' => 'Enable a document type for a capability to mandate or recommend compliance uploads.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-file-shield','title' => 'No document enables found','description' => 'Enable a document type for a capability to mandate or recommend compliance uploads.']); ?>
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capability / Role</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requirement Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accepted Formats</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Configured Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $enables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/80 transition-colors">
                    
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                <i class="fa-solid fa-file-shield text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900"><?php echo e($item->documentType?->name ?? '—'); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->documentType?->description): ?>
                                    <p class="text-xs text-gray-500 line-clamp-1 max-w-sm"><?php echo e($item->documentType->description); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </td>

                    
                    <td class="px-5 py-3.5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->capabilityType): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strtolower($item->capabilityType->code) === 'supplier'): ?>
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-store text-[10px]"></i> Supplier
                                </span>
                            <?php elseif(strtolower($item->capabilityType->code) === 'buyer'): ?>
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-cart-shopping text-[10px]"></i> Buyer
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                    <?php echo e($item->capabilityType->name); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-5 py-3.5">
                        <form method="POST" action="<?php echo e(route('admin.catalog.document-type-enables.toggle', $item)); ?>" class="inline-block">
                            <?php echo csrf_field(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->is_required): ?>
                                <button type="submit"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors inline-flex items-center gap-1 shadow-2xs"
                                        title="Click to toggle to Optional">
                                    <i class="fa-solid fa-asterisk text-[10px] text-rose-500"></i> Required (Mandatory)
                                    <i class="fa-solid fa-arrows-rotate text-[10px] ml-1 text-rose-400"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors inline-flex items-center gap-1 shadow-2xs"
                                        title="Click to toggle to Required">
                                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-500"></i> Optional
                                    <i class="fa-solid fa-arrows-rotate text-[10px] ml-1 text-emerald-400"></i>
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </form>
                    </td>

                    
                    <td class="px-5 py-3.5 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item->documentType?->accepted_formats) && count($item->documentType->accepted_formats) > 0): ?>
                            <div class="flex items-center gap-1 flex-wrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item->documentType->accepted_formats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fmt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-700 border border-gray-200">
                                        <?php echo e($fmt); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs">Any</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-5 py-3.5 text-xs text-gray-500 font-medium">
                        <?php echo e($item->created_at?->format('d M Y') ?? '—'); ?>

                    </td>

                    
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-document-enable-<?php echo e($item->id); ?>')"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition-colors"
                                    title="Edit Configuration">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            
                            <form method="POST"
                                  action="<?php echo e(route('admin.catalog.document-type-enables.destroy', $item)); ?>"
                                  onsubmit="return confirmSwal(this, 'Remove Document Enable?', 'Remove <?php echo e(addslashes($item->documentType?->name ?? 'this document')); ?> from <?php echo e(addslashes($item->capabilityType?->name ?? 'capability')); ?>?', 'warning', 'Yes, Remove')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                        title="Remove Enable Rule">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $enables]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($enables)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'create-document-enable','title' => 'Enable Document for Capability','width' => 'max-w-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'create-document-enable','title' => 'Enable Document for Capability','width' => 'max-w-lg']); ?>
        <form method="POST" action="<?php echo e(route('admin.catalog.document-type-enables.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="create_document_type_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Document Type <span class="text-rose-500">*</span>
                </label>
                <select name="document_type_id" id="create_document_type_id" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Select Document Type...</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $documentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($doc->id); ?>"><?php echo e($doc->name); ?> (<?php echo e(collect($doc->accepted_formats)->implode(', ') ?: 'Any format'); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <div>
                <label for="create_capability_type_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Capability / Account Role <span class="text-rose-500">*</span>
                </label>
                <select name="capability_type_id" id="create_capability_type_id" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Select Capability...</option>
                    <option value="both" class="font-bold text-indigo-700">★ Both (Buyer &amp; Supplier)</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $capabilityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cap->id); ?>"><?php echo e($cap->name); ?> (<?php echo e(strtoupper($cap->code)); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <p class="text-[11px] text-gray-500 mt-1">Select <strong>"Both (Buyer &amp; Supplier)"</strong> to enable this document type for both Buyer and Supplier capabilities simultaneously.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Requirement Type <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-rose-300 hover:bg-rose-50/40 cursor-pointer transition-colors has-checked:border-rose-500 has-checked:bg-rose-50/50">
                        <input type="radio" name="is_required" value="1" checked class="mt-0.5 text-rose-600 focus:ring-rose-500 border-gray-300">
                        <div class="ml-2.5 text-xs">
                            <span class="font-bold text-gray-900 block">Required</span>
                            <span class="text-gray-500 block text-[11px] mt-0.5">Mandatory for capability verification &amp; approval.</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/40 cursor-pointer transition-colors has-checked:border-emerald-500 has-checked:bg-emerald-50/50">
                        <input type="radio" name="is_required" value="0" class="mt-0.5 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                        <div class="ml-2.5 text-xs">
                            <span class="font-bold text-gray-900 block">Optional</span>
                            <span class="text-gray-500 block text-[11px] mt-0.5">Recommended or optional supporting document.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                    Cancel
                </button>
                <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Enable Document
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $enables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'edit-document-enable-'.$item->id,'title' => 'Edit Document Enable: ' . ($item->documentType?->name ?? 'Document'),'width' => 'max-w-lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('edit-document-enable-'.$item->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Edit Document Enable: ' . ($item->documentType?->name ?? 'Document')),'width' => 'max-w-lg']); ?>
            <form method="POST" action="<?php echo e(route('admin.catalog.document-type-enables.update', $item)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div>
                    <label for="edit_doc_<?php echo e($item->id); ?>" class="block text-xs font-semibold text-gray-700 mb-1">
                        Document Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="document_type_id" id="edit_doc_<?php echo e($item->id); ?>" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $documentTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($doc->id); ?>" <?php if($item->document_type_id === $doc->id): echo 'selected'; endif; ?>>
                                <?php echo e($doc->name); ?> (<?php echo e(collect($doc->accepted_formats)->implode(', ') ?: 'Any format'); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div>
                    <label for="edit_cap_<?php echo e($item->id); ?>" class="block text-xs font-semibold text-gray-700 mb-1">
                        Capability / Account Role <span class="text-rose-500">*</span>
                    </label>
                    <select name="capability_type_id" id="edit_cap_<?php echo e($item->id); ?>" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        <option value="both" class="font-bold text-indigo-700">★ Both (Buyer &amp; Supplier)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $capabilityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cap->id); ?>" <?php if($item->capability_type_id === $cap->id): echo 'selected'; endif; ?>>
                                <?php echo e($cap->name); ?> (<?php echo e(strtoupper($cap->code)); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">Select <strong>"Both (Buyer &amp; Supplier)"</strong> to ensure this document is enabled across both capabilities.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        Requirement Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-rose-300 hover:bg-rose-50/40 cursor-pointer transition-colors has-checked:border-rose-500 has-checked:bg-rose-50/50">
                            <input type="radio" name="is_required" value="1" <?php if($item->is_required): echo 'checked'; endif; ?> class="mt-0.5 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-gray-900 block">Required</span>
                                <span class="text-gray-500 block text-[11px] mt-0.5">Mandatory for capability verification &amp; approval.</span>
                            </div>
                        </label>

                        <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/40 cursor-pointer transition-colors has-checked:border-emerald-500 has-checked:bg-emerald-50/50">
                            <input type="radio" name="is_required" value="0" <?php if(! $item->is_required): echo 'checked'; endif; ?> class="mt-0.5 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-gray-900 block">Optional</span>
                                <span class="text-gray-500 block text-[11px] mt-0.5">Recommended or optional supporting document.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-check"></i> Update Configuration
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

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\document-type-enables\index.blade.php ENDPATH**/ ?>