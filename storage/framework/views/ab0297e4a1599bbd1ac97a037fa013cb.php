<?php $__env->startSection('title', 'Catalog Listings'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog / All Listings'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Catalog Listings','subtitle' => 'Manage your educational product and service listings, pricing, and availability.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Catalog Listings','subtitle' => 'Manage your educational product and service listings, pricing, and availability.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('supplier.catalog.listings.create')); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Listing
            </a>
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
                
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-900">Listings</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600"><?php echo e($listings->total()); ?> total</span>
                    <button type="button" onclick="window.print()" class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5 ml-2 transition">
                        <i class="fa-solid fa-print text-gray-500"></i> Print
                    </button>
                </div>

                
                <form method="GET" action="<?php echo e(route('supplier.catalog.listings.index')); ?>" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                    <input type="hidden" name="direction" value="<?php echo e($direction); ?>">

                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search by name, SKU, or ID..."
                               @input.debounce.300ms="$el.form.submit()"
                               class="focus-accent w-full sm:w-60 pl-9 pr-3 py-1.5 text-xs rounded-lg border border-gray-300">
                    </div>

                    <select name="type" onchange="this.form.submit()" class="focus-accent text-xs rounded-lg border border-gray-300 px-2.5 py-1.5 bg-white">
                        <option value="">All Types</option>
                        <option value="product" <?php if($type === 'product'): echo 'selected'; endif; ?>>Products</option>
                        <option value="service" <?php if($type === 'service'): echo 'selected'; endif; ?>>Services</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="focus-accent text-xs rounded-lg border border-gray-300 px-2.5 py-1.5 bg-white">
                        <option value="">All Statuses</option>
                        <option value="draft" <?php if($status === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                        <option value="pending" <?php if($status === 'pending'): echo 'selected'; endif; ?>>Pending Approval</option>
                        <option value="approved" <?php if($status === 'approved'): echo 'selected'; endif; ?>>Approved / Active</option>
                        <option value="rejected" <?php if($status === 'rejected'): echo 'selected'; endif; ?>>Rejected</option>
                    </select>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $type || $status || request('sort')): ?>
                        <a href="<?php echo e(route('supplier.catalog.listings.index')); ?>" class="text-xs text-gray-500 hover:text-gray-700 px-2 font-medium">Reset</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </form>
            </div>
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listings->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-box-open','title' => 'No listings found','description' => 'Create your first catalog listing to start selling to institutions.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-box-open','title' => 'No listings found','description' => 'Create your first catalog listing to start selling to institutions.']); ?>
                     <?php $__env->slot('actions', null, []); ?> 
                        <a href="<?php echo e(route('supplier.catalog.listings.create')); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Add Listing
                        </a>
                     <?php $__env->endSlot(); ?>
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
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-12 text-center">SL</th>
                    <?php if (isset($component)) { $__componentOriginal5fa38c9a3f111a520f583b1baa357d2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa38c9a3f111a520f583b1baa357d2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'name','label' => 'Listing','currentSort' => $sort,'currentDirection' => $direction]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'name','label' => 'Listing','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($direction)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'type','label' => 'Type','currentSort' => $sort,'currentDirection' => $direction]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'type','label' => 'Type','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($direction)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'category','label' => 'Category','currentSort' => $sort,'currentDirection' => $direction]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'category','label' => 'Category','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($direction)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'price','label' => 'Price','currentSort' => $sort,'currentDirection' => $direction]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'price','label' => 'Price','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($direction)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.sortable-th','data' => ['column' => 'status','label' => 'Status','currentSort' => $sort,'currentDirection' => $direction]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.sortable-th'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['column' => 'status','label' => 'Status','current-sort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sort),'current-direction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($direction)]); ?>
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
             <?php $__env->endSlot(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-4 py-3.5 text-xs text-gray-500 text-center font-medium">
                        <?php echo e($listings->firstItem() ? $listings->firstItem() + $loop->index : $loop->iteration); ?>

                    </td>
                    <td class="px-4 py-3.5">
                        <a href="<?php echo e(route('supplier.catalog.listings.show', $item)); ?>" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs transition-colors">
                            <?php echo e($item->name); ?>

                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="font-mono text-gray-500"><?php echo e($item->listing_number); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->sku): ?>
                                &middot; SKU: <span class="font-mono text-gray-600"><?php echo e($item->sku); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium <?php echo e($item->isProduct() ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'); ?>">
                            <?php echo e($item->listingType?->name ?? ucfirst($item->listing_type ?? 'product')); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        <?php echo e($item->mainCategory?->name ?? 'Uncategorized'); ?>

                    </td>
                    <td class="px-4 py-3.5 text-xs font-semibold text-gray-900">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->base_price): ?>
                            <?php echo e($item->currency_code); ?> <?php echo e(number_format($item->base_price, 2)); ?>

                        <?php else: ?>
                            <span class="text-gray-400 uppercase text-[10px] font-normal"><?php echo e($item->pricingType?->name ?? str_replace('_', ' ', $item->pricing_type ?? 'Quote Only')); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3.5">
                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $item->approval_status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->approval_status)]); ?>
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
                            <a href="<?php echo e(route('supplier.catalog.listings.show', $item)); ?>" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition" title="View details">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </a>
                            <a href="<?php echo e(route('supplier.catalog.listings.edit', $item)); ?>" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition" title="Edit listing">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </a>
                            <form method="POST" action="<?php echo e(route('supplier.catalog.listings.destroy', $item)); ?>" onsubmit="return confirm('Are you sure you want to delete this listing?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete listing">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $listings]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listings)]); ?>
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

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\catalog\listings\index.blade.php ENDPATH**/ ?>