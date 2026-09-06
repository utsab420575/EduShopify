<?php $__env->startSection('title', 'Category Builder — Attributes'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Category Builder / Attributes'); ?>

<?php $__env->startSection('body'); ?>

    <?php ($active = 'attributes'); ?>
    <?php echo $__env->make('backend.admin.catalog.builder._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        search: '',
        needsValuesOnly: false,
        hasValuesOnly: false,
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: <?php echo e(Js::from($attributes->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'group_name' => $a->attributeGroup?->name ?? 'Unassigned',
            'input_type' => str_replace('_', ' ', $a->input_type),
            'has_options' => in_array($a->input_type, ['select', 'multi_select', 'color'], true),
            'values_count' => $a->values_count,
            'is_active' => (bool) $a->is_active,
            'can_delete' => $a->listing_attribute_values_count === 0,
        ])->values())); ?>,
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return this.allNodes
                .filter(n => q === '' || (n.name + ' ' + n.group_name).toLowerCase().includes(q))
                .filter(n => {
                    if (!this.needsValuesOnly && !this.hasValuesOnly) return true;
                    const needsValues = n.has_options && n.values_count === 0;
                    const hasValues = n.has_options && n.values_count > 0;
                    return (this.needsValuesOnly && needsValues) || (this.hasValuesOnly && hasValues);
                });
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
                <h2 class="text-sm font-bold text-gray-900">All Attributes</h2>
                <span class="text-xs text-gray-400"><?php echo e($attributes->count()); ?> total</span>
            </div>

            <div class="relative mb-3">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search attributes or groups..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mb-4">
                <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                    <input type="checkbox" x-model="needsValuesOnly" @change="goToPage(1)" style="accent-color:var(--theme-primary)">
                    Only show attributes that need predefined values
                </label>
                <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                    <input type="checkbox" x-model="hasValuesOnly" @change="goToPage(1)" style="accent-color:var(--theme-primary)">
                    Only show attributes that already have predefined values
                </label>
            </div>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <div class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg hover:bg-gray-50 text-sm"
                         :class="{ 'opacity-50': !node.is_active }">
                        <button type="button" @click="$dispatch('open-modal-view-attribute-' + node.id)" class="flex items-center gap-2 min-w-0 text-left flex-1">
                            <i class="fa-solid fa-sliders text-indigo-400"></i>
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-gray-800" x-text="node.name"></span>
                                <span class="block text-[11px] text-gray-400" x-text="node.group_name"></span>
                            </span>
                        </button>
                        <span class="flex items-center gap-1.5 shrink-0">
                            <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded" x-text="node.input_type"></span>
                            <span x-show="node.has_options && node.values_count === 0" class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded-full">Needs values</span>
                            <span x-show="node.has_options && node.values_count > 0" class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-full">Has values</span>
                            <span x-show="!node.is_active" class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>

                            <button type="button" @click="$dispatch('open-modal-view-attribute-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="View">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                            <button type="button" @click="$dispatch('open-edit-attribute-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>
                            <button type="button" x-show="node.can_delete" @click="$dispatch('delete-attribute-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                            <button type="button" x-show="!node.can_delete" disabled
                                    title="Cannot delete — this attribute is in use by listings"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-200 cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </span>
                    </div>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No attributes created yet. Use "Add Attribute" to create the first one.</span>
                    <span x-show="allNodes.length > 0 && needsValuesOnly">No attributes need predefined values<span x-show="search.trim() !== ''"> matching "<span x-text="search"></span>"</span>.</span>
                    <span x-show="allNodes.length > 0 && hasValuesOnly">No attributes have predefined values yet<span x-show="search.trim() !== ''"> matching "<span x-text="search"></span>"</span>.</span>
                    <span x-show="allNodes.length > 0 && !needsValuesOnly && !hasValuesOnly">No attributes match "<span x-text="search"></span>".</span>
                </p>
            </div>

            <?php echo $__env->make('backend.admin.catalog.builder._pagination', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-sliders text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Add an Attribute</h3>
                <p class="text-xs text-gray-500 mb-4">A reusable specification field (e.g. Voltage, Color) available to every category.</p>
                <button type="button" @click="$dispatch('open-create-attribute')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-lg w-full">
                    <i class="fa-solid fa-plus text-xs mr-1.5"></i> Add Attribute
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-file-csv text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Import Attributes</h3>
                <p class="text-xs text-gray-500 mb-4">Upload a CSV to create many specification fields at once — no form filling needed.</p>
                <button type="button" @click="$dispatch('open-import-attributes')" class="text-sm font-semibold px-4 py-2.5 rounded-lg w-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition">
                    <i class="fa-solid fa-upload text-xs mr-1.5"></i> Import CSV
                </button>
                <a href="<?php echo e(route('admin.catalog.attributes.import.template')); ?>" class="block mt-2 text-xs text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-download mr-1"></i> Download template
                </a>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-list-ul text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Import Attribute Values</h3>
                <p class="text-xs text-gray-500 mb-4">Bulk-add dropdown/checkbox options for existing attributes — one option per row, referenced by attribute name.</p>
                <button type="button" @click="$dispatch('open-import-attribute-values')" class="text-sm font-semibold px-4 py-2.5 rounded-lg w-full border border-amber-200 text-amber-700 hover:bg-amber-50 transition">
                    <i class="fa-solid fa-upload text-xs mr-1.5"></i> Import CSV
                </button>
                <a href="<?php echo e(route('admin.catalog.attributes.values-import.template')); ?>" class="block mt-2 text-xs text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-download mr-1"></i> Download template
                </a>
            </div>
        </div>
    </div>

    
    <div x-data="{ open: false }" @open-create-attribute.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add Attribute</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="<?php echo e(route('admin.catalog.attributes.store')); ?>" class="space-y-4 pt-4"
                  @submit="$el.redirect_to.value = '<?php echo e(route('admin.catalog.builder.attributes')); ?>' + window.location.search">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.attributes')); ?>">
                <?php echo $__env->make('backend.admin.catalog.attributes._form', [
                    'attribute' => new \App\Models\Attribute(),
                    'attributeGroups' => $attributeGroups,
                    'units' => $units,
                    'inputTypes' => $inputTypes,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Create Attribute</button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-data="{ open: <?php echo e(session('open_attribute_import') ? 'true' : 'false'); ?> }" @open-import-attributes.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Import Attributes</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div class="pt-4 space-y-4">
                <p class="text-xs text-gray-500">
                    Upload a CSV with <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">name</code> and
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">input_type</code> columns — one attribute per row, both required.
                    Optional columns: <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">attribute_group</code> (matched by name — must already exist),
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">is_required</code>, <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">is_filterable</code>,
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">is_variant</code> (1 or 0). Valid input_type values: text, textarea, number,
                    select, multi_select, boolean, date, color. Attributes that already exist (matched by name) are reused, never duplicated.
                </p>
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-[11px] font-mono text-gray-600 leading-5 overflow-x-auto">
                    name,input_type,attribute_group,is_required,is_filterable,is_variant<br>
                    Voltage,number,Technical Specification,0,1,0<br>
                    Color,select,Physical Dimensions,0,1,1
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

                <form method="POST" action="<?php echo e(route('admin.catalog.attributes.import.preview')); ?>" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CSV File</label>
                        <input type="file" name="file" accept=".csv,text/csv" required
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg">
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="<?php echo e(route('admin.catalog.attributes.import.template')); ?>" class="text-xs font-medium text-indigo-600 hover:underline">
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('attribute_import_preview')): ?>
        <?php ($attributeImportPreview = session('attribute_import_preview')); ?>
        <div x-data="{ open: <?php echo e(session('open_attribute_import_preview') ? 'true' : 'false'); ?> }"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 max-h-[88vh] flex flex-col"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 shrink-0">
                    <h3 class="text-base font-bold text-gray-900">Review Attribute Import</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="py-4 flex items-center gap-4 shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-plus"></i> <?php echo e($attributeImportPreview['created_count']); ?> new
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                        <i class="fa-solid fa-check"></i> <?php echo e($attributeImportPreview['existing_count']); ?> already exist (will be reused)
                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attributeImportPreview['error_count'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($attributeImportPreview['error_count']); ?> row error<?php echo e($attributeImportPreview['error_count'] === 1 ? '' : 's'); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attributeImportPreview['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($row['error'])): ?>
                                <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Line <?php echo e($row['line']); ?>: <?php echo e($row['name']); ?> — <?php echo e($row['error']); ?></p>
                            <?php else: ?>
                                <p class="text-xs flex flex-wrap items-center gap-2">
                                    <span class="<?php echo e($row['status'] === 'existing' ? 'text-gray-500' : 'text-emerald-700 font-semibold'); ?>"><?php echo e($row['name']); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($row['input_type'])): ?>
                                        <span class="text-[10px] text-gray-400 uppercase font-semibold"><?php echo e(str_replace('_', ' ', $row['input_type'])); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <form method="POST" action="<?php echo e(route('admin.catalog.attributes.import.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-check mr-1"></i> Confirm Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-data="{ open: <?php echo e(session('open_attribute_value_import') ? 'true' : 'false'); ?> }" @open-import-attribute-values.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Import Attribute Values</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div class="pt-4 space-y-4">
                <p class="text-xs text-gray-500">
                    Upload a CSV with <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">attribute_name</code> and
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">value</code> columns — one option per row (e.g. attribute
                    Color: Red, Black). The attribute must already exist (import attributes first) and be a select, multi_select or
                    color field. The exact color swatch for a color attribute is set later, not here — this just defines the option
                    names. Values that already exist for that attribute are reused, never duplicated.
                </p>
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-[11px] font-mono text-gray-600 leading-5 overflow-x-auto">
                    attribute_name,value<br>
                    Color,Red<br>
                    Color,Black
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

                <form method="POST" action="<?php echo e(route('admin.catalog.attributes.values-import.preview')); ?>" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CSV File</label>
                        <input type="file" name="file" accept=".csv,text/csv" required
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 border border-gray-200 rounded-lg">
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="<?php echo e(route('admin.catalog.attributes.values-import.template')); ?>" class="text-xs font-medium text-indigo-600 hover:underline">
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('attribute_value_import_preview')): ?>
        <?php ($attributeValueImportPreview = session('attribute_value_import_preview')); ?>
        <div x-data="{ open: <?php echo e(session('open_attribute_value_import_preview') ? 'true' : 'false'); ?> }"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 max-h-[88vh] flex flex-col"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 shrink-0">
                    <h3 class="text-base font-bold text-gray-900">Review Attribute Value Import</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <div class="py-4 flex items-center gap-4 shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-plus"></i> <?php echo e($attributeValueImportPreview['created_count']); ?> new
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                        <i class="fa-solid fa-check"></i> <?php echo e($attributeValueImportPreview['existing_count']); ?> already exist (will be reused)
                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attributeValueImportPreview['error_count'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($attributeValueImportPreview['error_count']); ?> row error<?php echo e($attributeValueImportPreview['error_count'] === 1 ? '' : 's'); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attributeValueImportPreview['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($row['error'])): ?>
                                <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Line <?php echo e($row['line']); ?>: <?php echo e($row['attribute_name']); ?> — <?php echo e($row['value']); ?> — <?php echo e($row['error']); ?></p>
                            <?php else: ?>
                                <p class="text-xs flex flex-wrap items-center gap-2">
                                    <span class="text-gray-400"><?php echo e($row['attribute_name']); ?></span>
                                    <i class="fa-solid fa-chevron-right text-[8px] text-gray-300"></i>
                                    <span class="<?php echo e($row['status'] === 'existing' ? 'text-gray-500' : 'text-emerald-700 font-semibold'); ?>"><?php echo e($row['value']); ?></span>
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
                    <form method="POST" action="<?php echo e(route('admin.catalog.attributes.values-import.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-check mr-1"></i> Confirm Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attr->listing_attribute_values_count === 0): ?>
            <form x-data @delete-attribute-<?php echo e($attr->id); ?>.window="$el.requestSubmit()"
                  method="POST" action="<?php echo e(route('admin.catalog.attributes.destroy', $attr)); ?>"
                  onsubmit="return confirmSwal(this, 'Delete Attribute?', 'Are you sure you want to delete &quot;<?php echo e(addslashes($attr->name)); ?>&quot;? This cannot be undone.', 'warning', 'Yes, Delete')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'view-attribute-'.$attr->id,'title' => $attr->name,'width' => 'max-w-xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('view-attribute-'.$attr->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attr->name),'width' => 'max-w-xl']); ?>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attr->is_active): ?>
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Active
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Inactive
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100 font-semibold uppercase text-[10px]">
                        <?php echo e(str_replace('_', ' ', $attr->input_type)); ?>

                    </span>
                    <span class="text-xs font-mono text-gray-400"><?php echo e($attr->slug); ?></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Group</p>
                        <p class="text-sm font-semibold text-gray-900"><?php echo e($attr->attributeGroup?->name ?? 'Unassigned'); ?></p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase">Unit</p>
                        <p class="text-sm font-semibold text-gray-900"><?php echo e($attr->unit ? "{$attr->unit->name} ({$attr->unit->symbol})" : '—'); ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <span class="text-xs <?php echo e($attr->is_required ? 'text-red-700 font-semibold' : 'text-gray-400'); ?>">
                        <i class="fa-solid <?php echo e($attr->is_required ? 'fa-check' : 'fa-xmark'); ?> mr-1"></i>Required
                    </span>
                    <span class="text-xs <?php echo e($attr->is_filterable ? 'text-emerald-700 font-semibold' : 'text-gray-400'); ?>">
                        <i class="fa-solid <?php echo e($attr->is_filterable ? 'fa-check' : 'fa-xmark'); ?> mr-1"></i>Filterable
                    </span>
                    <span class="text-xs <?php echo e($attr->is_variant ? 'text-purple-700 font-semibold' : 'text-gray-400'); ?>">
                        <i class="fa-solid <?php echo e($attr->is_variant ? 'fa-check' : 'fa-xmark'); ?> mr-1"></i>Variant
                    </span>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($attr->input_type, ['select', 'multi_select', 'color'])): ?>
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase mb-2">Predefined Values (<?php echo e($attr->values->count()); ?>)</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attr->values->isEmpty()): ?>
                            <p class="text-xs text-gray-400">No values defined yet.</p>
                        <?php else: ?>
                            <div class="flex flex-wrap gap-1.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attr->values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium pl-2.5 pr-2.5 py-1 rounded-full"
                                          style="background:var(--theme-primary-soft); color:var(--theme-primary)">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attr->input_type === 'color' && $val->color_hex): ?>
                                            <span class="w-2.5 h-2.5 rounded-full border border-white" style="background: <?php echo e($val->color_hex); ?>"></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php echo e($val->value); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                <a href="<?php echo e(route('admin.catalog.attributes.edit', $attr)); ?>" target="_self" class="text-xs font-medium text-indigo-600 hover:underline">
                    See in page &rarr;
                </a>
                <button type="button" @click="open = false; $dispatch('open-edit-attribute-<?php echo e($attr->id); ?>')" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div x-data="{ open: false }" @open-edit-attribute-<?php echo e($attr->id); ?>.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit "<?php echo e($attr->name); ?>"</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form method="POST" action="<?php echo e(route('admin.catalog.attributes.update', $attr)); ?>" class="space-y-4 pt-4"
                      @submit="$el.redirect_to.value = '<?php echo e(route('admin.catalog.builder.attributes')); ?>' + window.location.search">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.attributes')); ?>">
                    <?php echo $__env->make('backend.admin.catalog.attributes._form', [
                        'attribute' => $attr,
                        'attributeGroups' => $attributeGroups,
                        'units' => $units,
                        'inputTypes' => $inputTypes,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\builder\attributes.blade.php ENDPATH**/ ?>