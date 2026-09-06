<?php $__env->startSection('title', 'Sales Modes'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Sales Modes'); ?>

<?php $__env->startSection('body'); ?>

<div x-data="{
    modalOpen: false,
    isEdit: false,
    formAction: '',
    form: { id: null, name: '', code: '', description: '', sort_order: 0, is_active: true },
    openCreate() {
        this.isEdit = false;
        this.formAction = '<?php echo e(route('admin.catalog.sales-modes.store')); ?>';
        this.form = { id: null, name: '', code: '', description: '', sort_order: 0, is_active: true };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.formAction = '<?php echo e(url('admin/catalog/sales-modes')); ?>/' + item.id;
        this.form = { id: item.id, name: item.name, code: item.code, description: item.description || '', sort_order: item.sort_order, is_active: Boolean(item.is_active) };
        this.modalOpen = true;
    }
}">

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Sales Modes','subtitle' => 'Define how buyers can transact on listings — RFQ only, direct purchase, or both.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sales Modes','subtitle' => 'Define how buyers can transact on listings — RFQ only, direct purchase, or both.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.attributes.manage')): ?>
            <button type="button" @click="openCreate()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>New Sales Mode</span>
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
        <form method="GET" action="<?php echo e(route('admin.catalog.sales-modes.index')); ?>" class="flex flex-wrap items-center gap-3">
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $status): ?>
                <a href="<?php echo e(route('admin.catalog.sales-modes.index')); ?>" class="text-xs text-gray-500 hover:text-gray-800">Clear</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
            <input type="hidden" name="direction" value="<?php echo e($direction); ?>">
        </form>
    </div>

    <?php
        $sortUrl = fn($col) => route('admin.catalog.sales-modes.index', array_merge(request()->except(['sort','direction','page']), [
            'sort'      => $col,
            'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
        ]));
        $sortIcon = fn($col) => $sort === $col
            ? ($direction === 'asc' ? '<i class="fa-solid fa-sort-up text-indigo-600 ml-1"></i>' : '<i class="fa-solid fa-sort-down text-indigo-600 ml-1"></i>')
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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($salesModes->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-handshake','title' => 'No sales modes found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-handshake','title' => 'No sales modes found']); ?>
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <a href="<?php echo e($sortUrl('sort_order')); ?>" class="inline-flex items-center hover:text-gray-900 transition-colors">Order <?php echo $sortIcon('sort_order'); ?></a>
                    </th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Listings</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $salesModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="px-5 py-3.5 text-sm font-semibold text-gray-900"><?php echo e($item->name); ?></td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center font-mono text-xs font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-100"><?php echo e($item->code); ?></span>
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 max-w-xs truncate"><?php echo e($item->description ?: '—'); ?></td>
                <td class="px-5 py-3.5 text-sm text-gray-600 font-mono"><?php echo e($item->sort_order); ?></td>
                <td class="px-5 py-3.5 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                        <i class="fa-solid fa-layer-group text-[10px]"></i> <?php echo e(number_format($item->listings_count)); ?>

                    </span>
                </td>
                <td class="px-5 py-3.5">
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
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.attributes.manage')): ?>
                        <form method="POST" action="<?php echo e(route('admin.catalog.sales-modes.toggle-active', $item)); ?>"
                              onsubmit="return confirmSwal(this, '<?php echo e($item->is_active ? 'Deactivate' : 'Activate'); ?> Sales Mode?', '<?php echo e(addslashes($item->name)); ?> will be <?php echo e($item->is_active ? 'hidden from suppliers' : 'made available'); ?>.', 'question', 'Yes, <?php echo e($item->is_active ? 'Deactivate' : 'Activate'); ?>')">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center transition <?php echo e($item->is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50'); ?>"
                                    title="<?php echo e($item->is_active ? 'Deactivate' : 'Activate'); ?>">
                                <i class="fa-solid <?php echo e($item->is_active ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"></i>
                            </button>
                        </form>

                        <button type="button"
                                @click="openEdit(<?php echo e(json_encode(['id' => $item->id, 'name' => $item->name, 'code' => $item->code, 'description' => $item->description, 'sort_order' => $item->sort_order, 'is_active' => (bool)$item->is_active])); ?>)"
                                class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-gray-100 transition"
                                title="Edit">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>

                        <form method="POST" action="<?php echo e(route('admin.catalog.sales-modes.destroy', $item)); ?>"
                              onsubmit="return confirmSwal(this, 'Delete Sales Mode?', 'Delete <?php echo e(addslashes($item->name)); ?>? This cannot be undone.', 'warning', 'Yes, Delete')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Delete">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

             <?php $__env->slot('pagination', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $salesModes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesModes)]); ?>
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

    
    <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="modalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                <form :action="formAction" method="POST">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="isEdit ? 'Edit Sales Mode' : 'New Sales Mode'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5">Configure how buyers can transact on listings using this mode.</p>
                        </div>
                        <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="form.name" required placeholder="e.g. Direct Purchase"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                            <input type="text" name="code" x-model="form.code" required placeholder="e.g. direct_purchase" maxlength="50"
                                   :readonly="isEdit"
                                   :class="isEdit ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white'"
                                   class="w-full text-sm font-mono rounded-lg border border-gray-300 px-3 py-2 focus-accent transition">
                            <p class="text-[11px] text-gray-400 mt-1">Unique slug used by business logic — cannot be changed after creation.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                            <textarea name="description" x-model="form.description" rows="2" placeholder="Short description..."
                                      class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus-accent transition resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" x-model.number="form.sort_order" min="0"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus-accent transition">
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded" style="accent-color:var(--theme-primary)">
                                    <span class="text-xs font-semibold text-gray-800">Active</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 rounded-b-2xl">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-5 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="isEdit ? 'Save Changes' : 'Create Sales Mode'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\sales-modes\index.blade.php ENDPATH**/ ?>