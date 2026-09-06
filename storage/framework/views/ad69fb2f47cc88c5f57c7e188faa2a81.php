<?php $__env->startSection('title', 'RFQ Visibility Types'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Visibility Types'); ?>

<?php $__env->startSection('body'); ?>

<div x-data="{
    modalOpen: false,
    isEdit: false,
    formAction: '',
    form: { id: null, name: '', code: '', engine_type: 'open', max_suppliers: null, description: '', sort_order: 0, is_active: true },
    openCreate() {
        this.isEdit = false;
        this.formAction = '<?php echo e(route('admin.catalog.visibility-types.store')); ?>';
        this.form = { id: null, name: '', code: '', engine_type: 'open', max_suppliers: null, description: '', sort_order: 0, is_active: true };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.formAction = '<?php echo e(url('admin/catalog/visibility-types')); ?>/' + item.id;
        this.form = {
            id: item.id,
            name: item.name,
            code: item.code,
            engine_type: item.engine_type || 'open',
            max_suppliers: item.max_suppliers,
            description: item.description || '',
            sort_order: item.sort_order,
            is_active: Boolean(item.is_active)
        };
        this.modalOpen = true;
    }
}">

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'RFQ Visibility Types','subtitle' => 'Manage supplier targeting and marketplace visibility modes for buyer RFQs (Direct, Invited, Open Matching, Broadcast All).']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'RFQ Visibility Types','subtitle' => 'Manage supplier targeting and marketplace visibility modes for buyer RFQs (Direct, Invited, Open Matching, Broadcast All).']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.attributes.manage')): ?>
            <button type="button" @click="openCreate()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>New Visibility Type</span>
            </button>
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

    
    <div class="mb-4">
        <form method="GET" action="<?php echo e(route('admin.catalog.visibility-types.index')); ?>" class="flex flex-wrap items-center gap-3">
            <div class="relative w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search name, code..."
                       class="w-full text-xs rounded-xl border border-gray-300 pl-8 pr-3 py-2 bg-white focus-accent transition"
                       @input.debounce.500ms="$event.target.form.requestSubmit()"
                       x-init="if (new URLSearchParams(window.location.search).get('search')) { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); }">
            </div>
            <select name="status" onchange="this.form.submit()"
                    class="text-xs rounded-xl border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                <option value="">All Statuses</option>
                <option value="active"   <?php echo e($status === 'active'   ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e($status === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
            </select>
            <select name="engine_type" onchange="this.form.submit()"
                    class="text-xs rounded-xl border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                <option value="">All Engine Modes</option>
                <option value="open"    <?php echo e($engineType === 'open'    ? 'selected' : ''); ?>>Open (Marketplace / Matchmaking)</option>
                <option value="invited" <?php echo e($engineType === 'invited' ? 'selected' : ''); ?>>Invited (Restricted / Direct)</option>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $status || $engineType): ?>
                <a href="<?php echo e(route('admin.catalog.visibility-types.index')); ?>" class="text-xs text-gray-500 hover:text-gray-800">Clear</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <input type="hidden" name="sort" value="<?php echo e($sortField); ?>">
            <input type="hidden" name="direction" value="<?php echo e($sortDir); ?>">
        </form>
    </div>

    <?php
        $sortUrl = fn($col) => route('admin.catalog.visibility-types.index', array_merge(request()->except(['sort','direction','page']), [
            'sort'      => $col,
            'direction' => ($sortField === $col && $sortDir === 'asc') ? 'desc' : 'asc',
        ]));
        $sortIcon = fn($col) => $sortField === $col
            ? ($sortDir === 'asc' ? '<i class="fa-solid fa-sort-up text-indigo-600 ml-1"></i>' : '<i class="fa-solid fa-sort-down text-indigo-600 ml-1"></i>')
            : '<i class="fa-solid fa-sort text-gray-400 ml-1"></i>';
    ?>

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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($visibilityTypes->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-eye','title' => 'No visibility types found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-eye','title' => 'No visibility types found']); ?>
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="<?php echo e($sortUrl('name')); ?>" class="inline-flex items-center hover:text-gray-900 transition-colors">Name <?php echo $sortIcon('name'); ?></a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="<?php echo e($sortUrl('code')); ?>" class="inline-flex items-center hover:text-gray-900 transition-colors">Code <?php echo $sortIcon('code'); ?></a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="<?php echo e($sortUrl('engine_type')); ?>" class="inline-flex items-center hover:text-gray-900 transition-colors">Engine Mode <?php echo $sortIcon('engine_type'); ?></a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Max Suppliers</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Meaning / Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="<?php echo e($sortUrl('sort_order')); ?>" class="inline-flex items-center hover:text-gray-900 transition-colors">Order <?php echo $sortIcon('sort_order'); ?></a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQs</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $visibilityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="px-5 py-3.5 text-sm font-semibold text-gray-900"><?php echo e($item->name); ?></td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center font-mono text-xs font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100"><?php echo e($item->code); ?></span>
                </td>
                <td class="px-5 py-3.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->engine_type === 'invited'): ?>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                            <i class="fa-solid fa-lock text-[10px]"></i> Invited (Restricted)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-globe text-[10px]"></i> Open (Marketplace)
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-700 font-medium">
                    <?php echo e($item->max_suppliers ? $item->max_suppliers . ' supplier(s)' : 'Unlimited'); ?>

                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 max-w-xs truncate"><?php echo e($item->description ?: '—'); ?></td>
                <td class="px-5 py-3.5 text-xs text-gray-600 font-mono"><?php echo e($item->sort_order); ?></td>
                <td class="px-5 py-3.5 text-xs text-gray-700 font-semibold">
                    <span class="px-2 py-0.5 rounded-full <?php echo e($item->rfqs_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-gray-100 text-gray-500'); ?>">
                        <?php echo e($item->rfqs_count); ?>

                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.attributes.manage')): ?>
                    <form method="POST" action="<?php echo e(route('admin.catalog.visibility-types.toggle-active', $item)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="cursor-pointer">
                            <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $item->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->is_active ? 'active' : 'inactive')]); ?>
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
                        </button>
                    </form>
                    <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $item->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->is_active ? 'active' : 'inactive')]); ?>
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
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.attributes.manage')): ?>
                    <button type="button" @click="openEdit(<?php echo e($item->toJson()); ?>)"
                            class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold">
                        Edit
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->rfqs_count === 0): ?>
                    <button type="button"
                            onclick="confirmDelete('delete-form-<?php echo e($item->id); ?>', '<?php echo e(addslashes($item->name)); ?>')"
                            class="text-red-600 hover:text-red-900 text-xs font-semibold ml-2">
                        Delete
                    </button>
                    <form id="delete-form-<?php echo e($item->id); ?>" method="POST" action="<?php echo e(route('admin.catalog.visibility-types.destroy', $item)); ?>" class="hidden">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                    </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

             <?php $__env->slot('pagination', null, []); ?> 
                <?php echo e($visibilityTypes->links()); ?>

             <?php $__env->endSlot(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    
    <div x-show="modalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="modalOpen = false"></div>

            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">

                <form :action="formAction" method="POST">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900" id="modal-title"
                                x-text="isEdit ? 'Edit Visibility Type' : 'Create Visibility Type'"></h3>
                            <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <div class="space-y-4 text-xs">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required
                                       placeholder="e.g. Direct RFQ, Invited RFQ, Open RFQ"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Code / Identifier <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="form.code" required
                                       placeholder="e.g. direct, invited, open_matching"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent font-mono">
                                <p class="text-[11px] text-gray-400 mt-1">Unique programmatic code (a-z, 0-9, underscores, hyphens).</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-1">Engine Mode <span class="text-red-500">*</span></label>
                                    <select name="engine_type" x-model="form.engine_type" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus-accent">
                                        <option value="open">Open (Public Marketplace)</option>
                                        <option value="invited">Invited (Restricted Shortlist)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 mb-1">Max Suppliers Limit</label>
                                    <input type="number" name="max_suppliers" x-model="form.max_suppliers" min="1" max="1000"
                                           placeholder="1 for single-source, blank for unlimited"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Meaning & Buyer Description</label>
                                <textarea name="description" x-model="form.description" rows="2"
                                          placeholder="Explain what this mode does to the buyer in the RFQ creation form..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" x-model="form.sort_order" min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus-accent">
                                </div>

                                <div class="flex items-center pt-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                                               class="rounded border-gray-300" style="accent-color:var(--theme-primary)">
                                        <span class="font-semibold text-gray-700">Active / Enabled</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3.5 flex items-center justify-end gap-2 rounded-b-2xl border-t border-gray-100">
                        <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="btn-primary px-5 py-2 text-xs font-semibold rounded-lg shadow-xs transition">
                            <span x-text="isEdit ? 'Save Changes' : 'Create Visibility Type'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function confirmDelete(formId, name) {
    Swal.fire({
        title: 'Delete Visibility Type?',
        text: 'Are you sure you want to delete "' + name + '"? This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\visibility-types\index.blade.php ENDPATH**/ ?>