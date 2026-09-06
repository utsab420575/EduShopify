<?php $__env->startSection('title', 'Category Builder — Attribute Groups'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Category Builder / Attribute Groups'); ?>

<?php $__env->startSection('body'); ?>

    <?php ($active = 'attribute-groups'); ?>
    <?php echo $__env->make('backend.admin.catalog.builder._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        search: '',
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: <?php echo e(Js::from($groups->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'is_active' => (bool) $g->is_active,
            'attributes_count' => $g->attributes_count,
            'can_delete' => $g->attributes_count === 0,
        ])->values())); ?>,
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return q === '' ? this.allNodes : this.allNodes.filter(n => n.name.toLowerCase().includes(q));
        },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
        get pageItems() {
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },
        get rangeStart() { return this.filtered.length === 0 ? 0 : (this.page - 1) * this.perPage + 1; },
        get rangeEnd() { return Math.min(this.page * this.perPage, this.filtered.length); },
        goToPage(p) {
            this.page = Math.min(Math.max(1, p), this.totalPages);
            const url = new URL(window.location.href);
            if (this.page > 1) { url.searchParams.set('page', this.page); } else { url.searchParams.delete('page'); }
            window.history.replaceState(null, '', url);
        },
    }" x-init="goToPage(page)">

        
        <div class="lg:col-span-8 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900">All Attribute Groups</h2>
                <span class="text-xs text-gray-400"><?php echo e($groups->count()); ?> total</span>
            </div>

            <div class="relative mb-4">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search attribute groups..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <div class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg hover:bg-gray-50 text-sm"
                         :class="{ 'opacity-50': !node.is_active }">
                        <button type="button" @click="$dispatch('open-modal-view-group-' + node.id)" class="flex items-center gap-2 min-w-0 text-left flex-1">
                            <i class="fa-solid fa-layer-group text-indigo-400"></i>
                            <span class="truncate font-medium text-gray-800" x-text="node.name"></span>
                        </button>
                        <span class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="$dispatch('open-group-attributes-' + node.id)"
                                    x-show="node.attributes_count > 0"
                                    class="text-[10px] font-medium text-gray-600 bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 px-1.5 py-0.5 rounded-full transition"
                                    title="View attributes in this group"
                                    x-text="node.attributes_count + (node.attributes_count === 1 ? ' attribute' : ' attributes')"></button>
                            <span x-show="node.attributes_count === 0" class="text-[10px] font-medium text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">0 attributes</span>
                            <span x-show="!node.is_active" class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>

                            <button type="button" @click="$dispatch('open-modal-view-group-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="View">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-edit-group-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>
                            <button type="button" x-show="node.can_delete" @click="$dispatch('delete-group-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                            <button type="button" x-show="!node.can_delete" disabled
                                    title="Cannot delete — has assigned attributes, reassign or remove them first"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-200 cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </span>
                    </div>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No attribute groups created yet. Use "Add Group" to create the first one.</span>
                    <span x-show="allNodes.length > 0">No attribute groups match "<span x-text="search"></span>".</span>
                </p>
            </div>

            <?php echo $__env->make('backend.admin.catalog.builder._pagination', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-layer-group text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Add an Attribute Group</h3>
                <p class="text-xs text-gray-500 mb-4">A section heading used to organize related attributes (e.g. "Technical Specification").</p>
                <button type="button" @click="$dispatch('open-create-group')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-lg w-full">
                    <i class="fa-solid fa-plus text-xs mr-1.5"></i> Add Group
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-file-csv text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Import Attribute Groups</h3>
                <p class="text-xs text-gray-500 mb-4">Upload a CSV of group names to create several at once — no form filling needed.</p>
                <button type="button" @click="$dispatch('open-import-groups')" class="text-sm font-semibold px-4 py-2.5 rounded-lg w-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition">
                    <i class="fa-solid fa-upload text-xs mr-1.5"></i> Import CSV
                </button>
                <a href="<?php echo e(route('admin.catalog.attribute-groups.import.template')); ?>" class="block mt-2 text-xs text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-download mr-1"></i> Download template
                </a>
            </div>
        </div>
    </div>

    
    <div x-data="{ open: false }" @open-create-group.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add Attribute Group</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="<?php echo e(route('admin.catalog.attribute-groups.store')); ?>" class="space-y-4 pt-4"
                  @submit="$el.redirect_to.value = '<?php echo e(route('admin.catalog.builder.attribute-groups')); ?>' + window.location.search">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.attribute-groups')); ?>">
                <?php echo $__env->make('backend.admin.catalog.attribute-groups._form', ['group' => new \App\Models\AttributeGroup()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Create Group</button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-data="{ open: <?php echo e(session('open_group_import') ? 'true' : 'false'); ?> }" @open-import-groups.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Import Attribute Groups</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div class="pt-4 space-y-4">
                <p class="text-xs text-gray-500">
                    Upload a CSV with <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name</code> and
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">description</code> columns — one attribute
                    group per row, both required. Everything else is set automatically: new groups start active, are
                    appended after existing ones, and are stamped with who imported them.
                    Groups that already exist (matched by name) are reused, never duplicated, so it's safe to re-upload the same file.
                </p>
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-[11px] font-mono text-gray-600 leading-5">
                    name,description<br>
                    Technical Specification,Core specifications suppliers must fill in.<br>
                    Physical Dimensions,Size and weight of the product.
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form method="POST" action="<?php echo e(route('admin.catalog.attribute-groups.import.preview')); ?>" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CSV File</label>
                        <input type="file" name="file" accept=".csv,text/csv" required
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg">
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="<?php echo e(route('admin.catalog.attribute-groups.import.template')); ?>" class="text-xs font-medium text-indigo-600 hover:underline">
                            <i class="fa-solid fa-download mr-1"></i> Download template
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Preview Import</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('group_import_preview')): ?>
        <?php ($groupImportPreview = session('group_import_preview')); ?>
        <div x-data="{ open: <?php echo e(session('open_group_import_preview') ? 'true' : 'false'); ?> }"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 max-h-[88vh] flex flex-col"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 shrink-0">
                    <h3 class="text-base font-bold text-gray-900">Review Import</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="py-4 flex items-center gap-4 shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-plus"></i> <?php echo e($groupImportPreview['created_count']); ?> new
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                        <i class="fa-solid fa-check"></i> <?php echo e($groupImportPreview['existing_count']); ?> already exist (will be reused)
                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupImportPreview['error_count'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($groupImportPreview['error_count']); ?> row error<?php echo e($groupImportPreview['error_count'] === 1 ? '' : 's'); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupImportPreview['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($row['error'])): ?>
                                <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Line <?php echo e($row['line']); ?>: <?php echo e($row['name']); ?> — <?php echo e($row['error']); ?></p>
                            <?php else: ?>
                                <p class="text-xs flex flex-wrap items-center gap-2">
                                    <span class="<?php echo e($row['status'] === 'existing' ? 'text-gray-500' : 'text-emerald-700 font-semibold'); ?>"><?php echo e($row['name']); ?></span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full <?php echo e($row['status'] === 'would_create' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-500'); ?>">
                                        <?php echo e($row['status'] === 'would_create' ? 'Creates new' : 'Already exists'); ?>

                                    </span>
                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 mt-2 border-t border-gray-100 shrink-0">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="<?php echo e(route('admin.catalog.attribute-groups.import.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-check mr-1"></i> Confirm Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group->attributes_count === 0): ?>
            <form x-data @delete-group-<?php echo e($group->id); ?>.window="$el.requestSubmit()"
                  method="POST" action="<?php echo e(route('admin.catalog.attribute-groups.destroy', $group)); ?>"
                  onsubmit="return confirmSwal(this, 'Delete Attribute Group?', 'Are you sure you want to delete &quot;<?php echo e(addslashes($group->name)); ?>&quot;? This cannot be undone.', 'warning', 'Yes, Delete')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'view-group-'.$group->id,'title' => $group->name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('view-group-'.$group->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group->name)]); ?>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($group->is_active): ?>
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Active
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Inactive
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-xs font-mono text-gray-400"><?php echo e($group->slug); ?></span>
                </div>

                <p class="text-sm text-gray-600"><?php echo e($group->description ?: 'No description provided.'); ?></p>

                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Attributes</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo e($group->attributes_count); ?></p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Sort Order</p>
                        <p class="text-lg font-bold text-gray-900"><?php echo e($group->sort_order); ?></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                <a href="<?php echo e(route('admin.catalog.attribute-groups.edit', $group)); ?>" target="_self" class="text-xs font-medium text-indigo-600 hover:underline">
                    See in page &rarr;
                </a>
                <button type="button" @click="open = false; $dispatch('open-edit-group-<?php echo e($group->id); ?>')" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                    Edit
                </button>
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
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div x-data="{ open: false }" @open-edit-group-<?php echo e($group->id); ?>.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit "<?php echo e($group->name); ?>"</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form method="POST" action="<?php echo e(route('admin.catalog.attribute-groups.update', $group)); ?>" class="space-y-4 pt-4"
                      @submit="$el.redirect_to.value = '<?php echo e(route('admin.catalog.builder.attribute-groups')); ?>' + window.location.search">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.attribute-groups')); ?>">
                    <?php echo $__env->make('backend.admin.catalog.attribute-groups._form', ['group' => $group], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div x-data="{ open: <?php echo e(session('open_group_attributes_id') == $group->id ? 'true' : 'false'); ?> }"
             @open-group-attributes-<?php echo e($group->id); ?>.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Attributes in "<?php echo e($group->name); ?>"</h3>
                        <p class="text-xs text-gray-500 mt-0.5"><?php echo e($group->attributes->count()); ?> attribute<?php echo e($group->attributes->count() === 1 ? '' : 's'); ?> — toggle active/inactive or edit directly.</p>
                    </div>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="pt-3 space-y-1.5 max-h-[60vh] overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group->attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 <?php echo e($attr->is_active ? '' : 'opacity-60'); ?>">
                            <div class="min-w-0 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-indigo-400 text-xs shrink-0"></i>
                                <span class="truncate text-sm font-medium text-gray-800"><?php echo e($attr->name); ?></span>
                                <span class="shrink-0 text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded"><?php echo e(str_replace('_', ' ', $attr->input_type)); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($attr->is_active)): ?>
                                    <span class="shrink-0 text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <form method="POST" action="<?php echo e(route('admin.catalog.attributes.toggle-active', $attr)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.attribute-groups')); ?>">
                                    <input type="hidden" name="reopen_group_id" value="<?php echo e($group->id); ?>">
                                    <button type="submit" class="w-7 h-7 rounded-lg inline-flex items-center justify-center hover:bg-gray-100" title="<?php echo e($attr->is_active ? 'Deactivate' : 'Activate'); ?>">
                                        <i class="fa-solid <?php echo e($attr->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400'); ?> text-base"></i>
                                    </button>
                                </form>
                                <a href="<?php echo e(route('admin.catalog.attributes.edit', $attr)); ?>" target="_self"
                                   class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit attribute">
                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-400 text-center py-8">No attributes in this group yet.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\builder\attribute-groups.blade.php ENDPATH**/ ?>