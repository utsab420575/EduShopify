<?php $__env->startSection('title', 'Approval Center'); ?>
<?php $__env->startSection('breadcrumb', 'Platform Governance / Approval Center'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Approval Center','subtitle' => $activeQueue ? $activeQueue['label'] . ' — items awaiting your decision.' : 'Review and moderate platform items awaiting administrative decision.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Approval Center','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeQueue ? $activeQueue['label'] . ' — items awaiting your decision.' : 'Review and moderate platform items awaiting administrative decision.')]); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($queues)): ?>
        <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-clipboard-check','title' => 'Nothing to review','description' => 'You don\'t have any pending approval queues assigned.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-clipboard-check','title' => 'Nothing to review','description' => 'You don\'t have any pending approval queues assigned.']); ?>
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
    <?php else: ?>
        <?php ($tab = $activeKey); ?>

        
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->isEmpty()): ?>
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 mb-1">Queue is empty</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">There are currently no items pending review in this queue.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'listings'): ?>
                                <tr>
                                    <th class="px-4 py-3 font-semibold w-10 text-center">#</th>
                                    <th class="px-5 py-3 font-semibold">Listing / Product</th>
                                    <th class="px-4 py-3 font-semibold">Category</th>
                                    <th class="px-4 py-3 font-semibold">Supplier</th>
                                    <th class="px-4 py-3 font-semibold">Base Price</th>
                                    <th class="px-4 py-3 font-semibold">Submitted</th>
                                    <th class="px-5 py-3 font-semibold text-right">Moderation Actions</th>
                                </tr>
                            <?php elseif($tab === 'custom_attribute_values'): ?>
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Attribute</th>
                                    <th class="px-4 py-3 font-semibold">Custom Value</th>
                                    <th class="px-4 py-3 font-semibold">Used By</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Item &amp; Details</th>
                                    <th class="px-4 py-3 font-semibold">Submitted By</th>
                                    <th class="px-4 py-3 font-semibold">Date Submitted</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="approval-tbody">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50/60 transition-colors"
                                    <?php if($tab === 'capabilities'): ?> data-approval-row="capabilities-<?php echo e($item->id); ?>" <?php endif; ?>
                                    <?php if($tab === 'listings'): ?> data-approval-row="listings-<?php echo e($item->id); ?>" <?php endif; ?>>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($tab):
                                        case ('listings'): ?>
                                            <?php ($firstImg = $item->getMedia('gallery')->first()); ?>
                                            <td class="px-4 py-3.5 text-center text-xs text-gray-400 font-medium">
                                                <?php echo e($loop->iteration); ?>

                                            </td>
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex-shrink-0 flex items-center justify-center">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstImg): ?>
                                                            <img src="<?php echo e($firstImg->getUrl()); ?>" alt="" class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-box text-gray-300 text-base"></i>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <a href="<?php echo e(route('admin.catalog.listings.show', $item)); ?>" class="font-bold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-1">
                                                            <?php echo e($item->name); ?>

                                                        </a>
                                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                            <span class="font-mono text-[10px] text-gray-400">SKU: <?php echo e($item->sku ?? '—'); ?></span>
                                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase bg-purple-50 text-purple-700 border border-purple-100">
                                                                <?php echo e($item->listing_type); ?>

                                                            </span>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->variants->isNotEmpty()): ?>
                                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                                    <?php echo e($item->variants->count()); ?> Variants
                                                                </span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-700 font-medium">
                                                <?php echo e($item->mainCategory?->name ?? '—'); ?>

                                            </td>
                                            <td class="px-4 py-3.5">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->supplierAccount): ?>
                                                    <a href="<?php echo e(route('admin.suppliers.show', $item->supplierAccount)); ?>" class="font-bold text-gray-800 hover:text-indigo-600 transition-colors flex items-center gap-1">
                                                        <?php echo e($item->supplierAccount->supplierProfile?->display_name ?? $item->supplierAccount->display_name); ?>

                                                        <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400">—</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3.5 font-bold text-indigo-700">
                                                <?php echo e($item->base_price ? $item->currency_code . ' ' . number_format($item->base_price, 2) : 'Negotiable'); ?>

                                            </td>
                                            <td class="px-4 py-3.5 text-[11px]">
                                                <?php ($hoursWaiting = $item->created_at->diffInHours(now())); ?>
                                                <span class="font-medium text-gray-700"><?php echo e($item->created_at->format('d M Y')); ?></span>
                                                <span class="block text-gray-400 text-[10px] mt-0.5"><?php echo e($item->created_at->diffForHumans()); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hoursWaiting >= 72): ?>
                                                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                                        <i class="fa-solid fa-triangle-exclamation text-[8px]"></i> Urgent
                                                    </span>
                                                <?php elseif($hoursWaiting >= 24): ?>
                                                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                                        <i class="fa-solid fa-clock text-[8px]"></i> Overdue
                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button"
                                                            @click="window.dispatchEvent(new CustomEvent('open-ajax-modal-approval-review', { detail: { url: '<?php echo e(route('admin.catalog.listings.panel', $item)); ?>', rowId: 'listings-<?php echo e($item->id); ?>', queueKey: 'listings', showUrl: '<?php echo e(route('admin.catalog.listings.show', $item)); ?>' } }))"
                                                            class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center gap-1 transition-colors">
                                                        <i class="fa-solid fa-magnifying-glass"></i> Review Listing
                                                    </button>
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.listings.approve', $item)); ?>" onsubmit="return confirmSwal(this, 'Approve & Publish Listing?', 'Approve and publish this listing to the marketplace?', 'question', 'Yes, Approve')">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1 transition-colors" title="Quick Approve">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('capabilities'): ?>
                                            <td class="px-5 py-3.5">
                                                <p class="font-bold text-gray-900"><?php echo e($item->account?->display_name); ?></p>
                                                <p class="text-[11px] text-indigo-600 font-medium">Capability: <?php echo e($item->capabilityType?->name); ?></p>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->appliedBy?->name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->applied_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <button type="button"
                                                        @click="window.dispatchEvent(new CustomEvent('open-ajax-modal-approval-review', { detail: { url: '<?php echo e(route('admin.capabilities.panel', $item)); ?>', rowId: 'capabilities-<?php echo e($item->id); ?>', queueKey: 'capabilities', showUrl: '<?php echo e(route('admin.capabilities.show', $item)); ?>' } }))"
                                                        class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </button>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('documents'): ?>
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                                        <?php ($ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION))); ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ext === 'pdf'): ?>
                                                            <i class="fa-solid fa-file-pdf text-red-500 text-lg"></i>
                                                        <?php elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                                                            <i class="fa-solid fa-file-image text-blue-500 text-lg"></i>
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-file-lines text-indigo-500 text-lg"></i>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-900 line-clamp-1">
                                                            <?php echo e($item->documentType?->name ?? ($item->custom_name ?: 'Compliance Document')); ?>

                                                        </p>
                                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-500">
                                                            <span class="font-medium text-gray-700 truncate max-w-[200px]" title="<?php echo e($item->original_name); ?>"><?php echo e($item->original_name); ?></span>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->file_size_kb): ?>
                                                                <span>&bull; <?php echo e($item->file_size_kb); ?> KB</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->expires_at): ?>
                                                                <span class="text-amber-600 font-medium">&bull; Exp: <?php echo e($item->expires_at->format('d M Y')); ?></span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->supplierAccount): ?>
                                                    <a href="<?php echo e(route('admin.suppliers.show', $item->supplierAccount)); ?>" class="font-bold text-gray-800 hover:text-indigo-600 transition-colors flex items-center gap-1">
                                                        <?php echo e($item->supplierAccount->supplierProfile?->display_name ?? $item->supplierAccount->display_name); ?>

                                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400"></i>
                                                    </a>
                                                    <span class="text-[10px] text-gray-400 font-mono"><?php echo e($item->supplierAccount->account_number); ?></span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">—</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-500 text-[11px]">
                                                <?php echo e($item->created_at?->format('d M Y, h:i A') ?? '—'); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->uploadedBy): ?>
                                                    <span class="block text-gray-400 text-[10px]">by <?php echo e($item->uploadedBy->name); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    
                                                    <button type="button"
                                                            @click="$dispatch('open-modal-doc-preview-<?php echo e($item->id); ?>')"
                                                            class="px-2.5 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center gap-1 transition-colors"
                                                            title="View & Preview Document">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </button>

                                                    
                                                    <form method="POST"
                                                          action="<?php echo e(route('admin.documents.verify', $item)); ?>"
                                                          onsubmit="return confirmSwal(this, 'Approve Document?', 'Verify and approve <?php echo e(addslashes($item->documentType?->name ?? $item->original_name)); ?> for this supplier?', 'question', 'Yes, Approve')">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1 transition-colors"
                                                                title="Approve Document">
                                                            <i class="fa-solid fa-check"></i> Approve
                                                        </button>
                                                    </form>

                                                    
                                                    <button type="button"
                                                            @click="$dispatch('open-modal-reject-doc-<?php echo e($item->id); ?>')"
                                                            class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold flex items-center gap-1 transition-colors"
                                                            title="Reject Document">
                                                        <i class="fa-solid fa-xmark"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('categories'): ?>
                                            <td class="px-5 py-3.5 font-bold text-gray-900"><?php echo e($item->name); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->supplierAccount?->supplierProfile?->display_name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->created_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.categories.suggestions.approve', $item)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">Approve</button>
                                                    </form>
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.categories.suggestions.reject', $item)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-3 py-1 rounded bg-rose-600 text-white font-semibold text-xs">Reject</button>
                                                    </form>
                                                </div>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('attributes'): ?>
                                            <td class="px-5 py-3.5 font-bold text-gray-900"><?php echo e($item->name); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->supplierAccount?->supplierProfile?->display_name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->created_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.attributes.suggestions.approve', $item)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">Approve</button>
                                                    </form>
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.attributes.suggestions.reject', $item)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-3 py-1 rounded bg-rose-600 text-white font-semibold text-xs">Reject</button>
                                                    </form>
                                                </div>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('custom_attribute_values'): ?>
                                            <td class="px-5 py-3.5 font-bold text-gray-900"><?php echo e($item->attribute_name); ?></td>
                                            <td class="px-4 py-3.5">
                                                <span class="font-medium text-gray-800"><?php echo e($item->custom_value); ?></span>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600">
                                                <?php echo e($item->usage_count); ?> <?php echo e(Str::plural('listing', $item->usage_count)); ?>

                                            </td>
                                            <td class="px-4 py-3.5">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->status === 'ignored'): ?>
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">Ignored</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.custom-attribute-values.approve')); ?>"
                                                          onsubmit="return confirmSwal(this, 'Promote to Official Value?', 'Make &quot;<?php echo e(addslashes($item->custom_value)); ?>&quot; a standard selectable option for <?php echo e(addslashes($item->attribute_name)); ?>? This updates all <?php echo e($item->usage_count); ?> listing(s) currently using it.', 'question', 'Yes, Promote')">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="attribute_id" value="<?php echo e($item->attribute_id); ?>">
                                                        <input type="hidden" name="custom_value" value="<?php echo e($item->custom_value); ?>">
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">
                                                            <?php echo e($item->status === 'ignored' ? 'Promote' : 'Approve'); ?>

                                                        </button>
                                                    </form>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->status !== 'ignored'): ?>
                                                        <form method="POST" action="<?php echo e(route('admin.catalog.custom-attribute-values.ignore')); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="attribute_id" value="<?php echo e($item->attribute_id); ?>">
                                                            <input type="hidden" name="custom_value" value="<?php echo e($item->custom_value); ?>">
                                                            <button type="submit" class="px-3 py-1 rounded bg-gray-200 text-gray-700 font-semibold text-xs">Ignore</button>
                                                        </form>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('brands'): ?>
                                            <td class="px-5 py-3.5 font-bold text-gray-900"><?php echo e($item->name); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->supplierAccount?->supplierProfile?->display_name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->created_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.brands.approve', $item)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">Approve</button>
                                                    </form>
                                                    <form method="POST" action="<?php echo e(route('admin.catalog.brands.reject', $item)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="px-3 py-1 rounded bg-rose-600 text-white font-semibold text-xs">Reject</button>
                                                    </form>
                                                </div>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('role_requests'): ?>
                                            <td class="px-5 py-3.5">
                                                <p class="font-bold text-gray-900"><?php echo e($item->account?->display_name); ?></p>
                                                <p class="text-[11px] text-gray-500">Requested: <?php echo e($item->role_name ?? $item->requestedRole?->name); ?></p>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->requestedBy?->name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->created_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="<?php echo e(route('admin.access-control.role-requests.show', $item)); ?>" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('conversions'): ?>
                                            <td class="px-5 py-3.5 font-bold text-gray-900"><?php echo e($item->account?->display_name); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->submittedBy?->name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->submitted_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="<?php echo e(route('admin.conversions.show', $item)); ?>" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            <?php break; ?>

                                        <?php case ('reports'): ?>
                                            <td class="px-5 py-3.5 font-bold text-gray-900">Report on review #<?php echo e($item->review_id); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->reportedByAccount?->display_name ?? '—'); ?></td>
                                            <td class="px-4 py-3.5 text-gray-600"><?php echo e($item->created_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="<?php echo e(route('admin.reviews.reports.show', $item)); ?>" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            <?php break; ?>
                                    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages()): ?>
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                        <?php echo e($items->links()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'documents'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($fileUrl = asset('storage/' . $item->file_path)); ?>
                <?php ($ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION))); ?>

                
                <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'doc-preview-'.$item->id,'title' => 'Review Document: ' . ($item->documentType?->name ?? $item->custom_name ?? $item->original_name),'width' => 'max-w-4xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('doc-preview-'.$item->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Review Document: ' . ($item->documentType?->name ?? $item->custom_name ?? $item->original_name)),'width' => 'max-w-4xl']); ?>
                    <div class="space-y-4">
                        
                        <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Supplier</span>
                                <span class="font-bold text-gray-900 truncate block"><?php echo e($item->supplierAccount?->supplierProfile?->display_name ?? $item->supplierAccount?->display_name ?? '—'); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Document Type</span>
                                <span class="font-semibold text-indigo-700 block"><?php echo e($item->documentType?->name ?? ($item->custom_name ?: 'Custom Document')); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">File Details</span>
                                <span class="font-medium text-gray-800 block truncate" title="<?php echo e($item->original_name); ?>"><?php echo e($item->original_name); ?> (<?php echo e($item->file_size_kb); ?> KB)</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Uploaded Date</span>
                                <span class="font-medium text-gray-800 block"><?php echo e($item->created_at?->format('d M Y, h:i A')); ?></span>
                            </div>
                        </div>

                        
                        <div class="rounded-xl border border-gray-200 bg-gray-100 overflow-hidden flex items-center justify-center min-h-[420px] max-h-[550px]">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ext === 'pdf'): ?>
                                <iframe src="<?php echo e($fileUrl); ?>" class="w-full h-[520px] bg-white" frameborder="0"></iframe>
                            <?php elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])): ?>
                                <div class="p-4 flex items-center justify-center w-full h-full overflow-auto bg-gray-900/5">
                                    <img src="<?php echo e($fileUrl); ?>" alt="<?php echo e($item->original_name); ?>" class="max-h-[500px] rounded-lg shadow-md object-contain">
                                </div>
                            <?php else: ?>
                                <div class="p-10 text-center">
                                    <i class="fa-solid fa-file-arrow-down text-5xl text-indigo-400 mb-3"></i>
                                    <h4 class="font-bold text-gray-800 text-sm mb-1"><?php echo e($item->original_name); ?></h4>
                                    <p class="text-xs text-gray-500 mb-4">Preview is not supported for <?php echo e(strtoupper($ext)); ?> files. Please download or view in a separate application.</p>
                                    <a href="<?php echo e($fileUrl); ?>" target="_blank" download class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 inline-flex items-center gap-1.5 transition-colors">
                                        <i class="fa-solid fa-download"></i> Download File
                                    </a>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2">
                                <a href="<?php echo e($fileUrl); ?>" target="_blank" class="text-xs font-medium text-gray-600 hover:text-indigo-600 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Open in new tab
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->expires_at): ?>
                                    <span class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md font-medium">
                                        Expires: <?php echo e($item->expires_at->format('d M Y')); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                
                                <form method="POST"
                                      action="<?php echo e(route('admin.documents.verify', $item)); ?>"
                                      onsubmit="return confirmSwal(this, 'Approve Document?', 'Verify and approve this document for <?php echo e(addslashes($item->supplierAccount?->display_name ?? 'supplier')); ?>?', 'question', 'Yes, Approve')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                                        <i class="fa-solid fa-check"></i> Approve Document
                                    </button>
                                </form>

                                
                                <button type="button"
                                        @click="open = false; setTimeout(() => $dispatch('open-modal-reject-doc-<?php echo e($item->id); ?>'), 150)"
                                        class="px-4 py-2 rounded-lg bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                    <i class="fa-solid fa-xmark"></i> Reject Document
                                </button>

                                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
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

                
                <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'reject-doc-'.$item->id,'title' => 'Reject Document: ' . ($item->documentType?->name ?? $item->custom_name ?? $item->original_name),'width' => 'max-w-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('reject-doc-'.$item->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Reject Document: ' . ($item->documentType?->name ?? $item->custom_name ?? $item->original_name)),'width' => 'max-w-md']); ?>
                    <form method="POST" action="<?php echo e(route('admin.documents.reject', $item)); ?>" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <p class="text-xs text-gray-500">
                            Please provide a clear reason why this compliance document is being rejected. The supplier will be notified to upload a revised document.
                        </p>
                        <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'reason','label' => 'Rejection Reason','placeholder' => 'e.g. The uploaded trade license is expired or illegible...','required' => true,'rows' => '3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reason','label' => 'Rejection Reason','placeholder' => 'e.g. The uploaded trade license is expired or illegible...','required' => true,'rows' => '3']); ?>
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

                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button type="button" @click="open = false" class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-xmark"></i> Confirm Rejection
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal281a3e0ab32d704203c7971535b83a39 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal281a3e0ab32d704203c7971535b83a39 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.ajax-modal','data' => ['id' => 'approval-review','width' => 'max-w-3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.ajax-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'approval-review','width' => 'max-w-3xl']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal281a3e0ab32d704203c7971535b83a39)): ?>
<?php $attributes = $__attributesOriginal281a3e0ab32d704203c7971535b83a39; ?>
<?php unset($__attributesOriginal281a3e0ab32d704203c7971535b83a39); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal281a3e0ab32d704203c7971535b83a39)): ?>
<?php $component = $__componentOriginal281a3e0ab32d704203c7971535b83a39; ?>
<?php unset($__componentOriginal281a3e0ab32d704203c7971535b83a39); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (function () {
        function decrementBadge(el) {
            if (! el) return;
            const next = (parseInt(el.textContent, 10) || 0) - 1;
            if (next <= 0) {
                el.remove();
            } else {
                el.textContent = next;
            }
        }

        window.addEventListener('approval-item-resolved', (e) => {
            const { rowId, queueKey } = e.detail || {};
            if (! rowId) return;

            const row = document.querySelector(`[data-approval-row="${CSS.escape(rowId)}"]`);
            if (row) row.remove();

            if (queueKey) {
                decrementBadge(document.getElementById('sidebar-approval-badge-' + queueKey));
            }
            decrementBadge(document.getElementById('sidebar-approval-badge-total'));

            const tbody = document.getElementById('approval-tbody');
            if (tbody && tbody.children.length === 0) {
                const colspan = tbody.closest('table')?.querySelector('thead tr')?.children.length || 4;
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="${colspan}" class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 mb-1">Queue is empty</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">There are currently no items pending review in this queue.</p>
                </td>`;
                tbody.appendChild(tr);
            }
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\approvals\index.blade.php ENDPATH**/ ?>