<?php $__env->startSection('title', 'Currencies'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Currencies'); ?>

<?php $__env->startSection('body'); ?>

<div x-data="{
    modalOpen: false,
    isEdit: false,
    formAction: '',
    formMethod: 'POST',
    form: {
        id: null,
        code: '',
        name: '',
        symbol: '',
        exchange_rate: '1.00000000',
        decimal_places: 2,
        is_active: true
    },
    openCreate() {
        this.isEdit = false;
        this.formAction = '<?php echo e(route('admin.catalog.currencies.store')); ?>';
        this.formMethod = 'POST';
        this.form = {
            id: null,
            code: '',
            name: '',
            symbol: '',
            exchange_rate: '1.00000000',
            decimal_places: 2,
            is_active: true
        };
        this.modalOpen = true;
    },
    openEdit(item) {
        this.isEdit = true;
        this.formAction = '<?php echo e(url('admin/catalog/currencies')); ?>/' + item.id;
        this.formMethod = 'PUT';
        this.form = {
            id: item.id,
            code: item.code,
            name: item.name,
            symbol: item.symbol,
            exchange_rate: item.exchange_rate,
            decimal_places: item.decimal_places,
            is_active: Boolean(item.is_active)
        };
        this.modalOpen = true;
    }
}">

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Currencies','subtitle' => 'Manage standard platform currencies, symbols, exchange rates, and base currency defaults.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Currencies','subtitle' => 'Manage standard platform currencies, symbols, exchange rates, and base currency defaults.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <button type="button" @click="openCreate()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>
                <span>New Currency</span>
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

    <div class="mb-4">
        <form method="GET" action="<?php echo e(route('admin.catalog.currencies.index')); ?>" class="flex items-center gap-3">
            <div class="relative w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search code, name, symbol..."
                       class="w-full text-xs rounded-xl border border-gray-300 pl-8 pr-3 py-2 bg-white focus-accent transition">
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('search')): ?>
                <a href="<?php echo e(route('admin.catalog.currencies.index')); ?>" class="text-xs text-gray-500 hover:text-gray-800">Clear</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </form>
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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currencies->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-coins','title' => 'No currencies found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-coins','title' => 'No currencies found']); ?>
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Currency Code &amp; Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Symbol</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Exchange Rate</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center font-mono font-bold text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-800"><?php echo e($currency->code); ?></span>
                            <span class="font-medium text-gray-900"><?php echo e($currency->name); ?></span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm font-bold text-gray-900"><?php echo e($currency->symbol); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 font-mono"><?php echo e($currency->exchange_rate); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currency->is_default): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-check text-[10px]"></i> Base Default
                            </span>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('admin.catalog.currencies.default', $currency)); ?>"
                                  onsubmit="return confirmSwal(this, 'Set <?php echo e($currency->code); ?> as Base Currency?', 'This will make <?php echo e(addslashes($currency->name)); ?> the primary platform currency.', 'question', 'Yes, Make Default')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-xs font-semibold hover:underline text-indigo-600">Make default</button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $currency->is_active ? 'active' : 'inactive']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currency->is_active ? 'active' : 'inactive')]); ?>
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
                                    @click="openEdit(<?php echo e(json_encode([
                                        'id' => $currency->id,
                                        'code' => $currency->code,
                                        'name' => $currency->name,
                                        'symbol' => $currency->symbol,
                                        'exchange_rate' => $currency->exchange_rate,
                                        'decimal_places' => $currency->decimal_places,
                                        'is_active' => (bool)$currency->is_active,
                                    ])); ?>)"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-gray-100 transition"
                                    title="Edit Currency">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$currency->is_default): ?>
                                
                                <form method="POST" action="<?php echo e(route('admin.catalog.currencies.destroy', $currency)); ?>"
                                      onsubmit="return confirmSwal(this, 'Delete Currency <?php echo e($currency->code); ?>?', 'Are you sure you want to delete <?php echo e(addslashes($currency->name)); ?> (<?php echo e($currency->code); ?>)? This action cannot be undone.', 'warning', 'Yes, Delete')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Delete Currency">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    
    <div x-show="modalOpen"
         x-cloak
         @keydown.escape.window="modalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">

        
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"
             @click="modalOpen = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                <form :action="formAction" method="POST">
                    <?php echo csrf_field(); ?>
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="isEdit ? ('Edit Currency (' + form.code + ')') : 'Add New Currency'"></h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="isEdit ? 'Update currency symbol, exchange rate or active status' : 'Define an ISO 3-letter currency code and exchange rate'"></p>
                        </div>
                        <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Currency Code (ISO) <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="form.code" required maxlength="3" placeholder="e.g. USD, BDT"
                                       :readonly="isEdit"
                                       :class="isEdit ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-white text-gray-900'"
                                       class="w-full text-sm font-mono uppercase rounded-lg border border-gray-300 px-3 py-2 focus-accent transition">
                                <p class="text-[11px] text-gray-400 mt-1" x-show="!isEdit">3-letter standard ISO code.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Currency Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required placeholder="e.g. US Dollar, Euro"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Symbol <span class="text-red-500">*</span></label>
                                <input type="text" name="symbol" x-model="form.symbol" required placeholder="e.g. $, ৳, €"
                                       class="w-full text-sm font-semibold rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Exchange Rate <span class="text-red-500">*</span></label>
                                <input type="number" step="0.00000001" min="0" name="exchange_rate" x-model="form.exchange_rate" required placeholder="1.00000000"
                                       class="w-full text-sm font-mono rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Decimals <span class="text-red-500">*</span></label>
                                <input type="number" min="0" max="4" name="decimal_places" x-model="form.decimal_places" required
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900 focus-accent transition">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded text-indigo-600 focus:ring-indigo-500" style="accent-color:var(--theme-primary)">
                                <span class="text-xs font-semibold text-gray-800">Active across platform and catalog</span>
                            </label>
                        </div>
                    </div>

                    
                    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 rounded-b-2xl">
                        <button type="button" @click="modalOpen = false" class="px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-5 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="isEdit ? 'Save Changes' : 'Create Currency'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\currencies\index.blade.php ENDPATH**/ ?>