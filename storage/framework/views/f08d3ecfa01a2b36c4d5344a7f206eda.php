<?php $__env->startSection('title', 'Category Builder — Categories'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Category Builder / Categories'); ?>

<?php $__env->startSection('body'); ?>

    <?php ($active = 'categories'); ?>
    <?php echo $__env->make('backend.admin.catalog.builder._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{
        search: '',
        page: Number(new URLSearchParams(window.location.search).get('page')) || 1,
        perPage: 10,
        allNodes: <?php echo e(Js::from(collect($tree)->map(fn($n) => [
            'id' => $n['id'],
            'parent_id' => $n['parent_id'],
            'name' => $n['name'],
            'depth' => $n['depth'],
            'is_active' => $n['is_active'],
            'can_delete' => $n['can_delete'],
            'delete_reason' => $n['can_delete'] ? null : ($n['children_count'] > 0
                ? $n['children_count'].' subcategor'.($n['children_count'] === 1 ? 'y' : 'ies').' must be removed or reassigned first'
                : 'in use by listings'),
        ])->values())); ?>,
        // A name-only filter used to just drop everything else, so
        // searching for a leaf like Laptop hid that it lives under
        // Electronics greater-than Computer and has its own children
        // like MiniLaptop nested under it. Instead: find direct name
        // matches, then pull in every ancestor (so the breadcrumb is
        // visible) and every descendant (so nested children are visible
        // too) — tagging which rows are the actual match vs. shown only
        // for context.
        get filtered() {
            const q = this.search.trim().toLowerCase();
            if (q === '') return this.allNodes;

            const byId = {};
            this.allNodes.forEach(n => { byId[n.id] = n; });

            const matchedIds = new Set(this.allNodes.filter(n => n.name.toLowerCase().includes(q)).map(n => n.id));
            const includeIds = new Set(matchedIds);

            // Ancestors of every match.
            matchedIds.forEach(id => {
                let node = byId[id];
                while (node && node.parent_id) {
                    includeIds.add(node.parent_id);
                    node = byId[node.parent_id];
                }
            });

            // Descendants of every match (any node whose ancestor chain passes through a match).
            this.allNodes.forEach(n => {
                let node = n;
                while (node) {
                    if (matchedIds.has(node.id)) { includeIds.add(n.id); break; }
                    node = node.parent_id ? byId[node.parent_id] : null;
                }
            });

            return this.allNodes
                .filter(n => includeIds.has(n.id))
                .map(n => ({ ...n, isMatch: matchedIds.has(n.id) }));
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
                <h2 class="text-sm font-bold text-gray-900">All Categories</h2>
                <span class="text-xs text-gray-400"><?php echo e(count($tree)); ?> total</span>
            </div>

            <div class="relative mb-4">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" x-model="search" @input="goToPage(1)" placeholder="Search categories..."
                       class="w-full text-sm rounded-lg border border-gray-300 pl-9 pr-3 py-2 bg-white">
            </div>

            <p x-show="search.trim() !== '' && filtered.length > 0" class="text-[11px] text-gray-400 mb-2 -mt-2">
                <i class="fa-solid fa-circle-info mr-1"></i> Showing matches along with their parent and child categories for context — highlighted rows are the actual match.
            </p>

            <div class="space-y-0.5 h-[420px] lg:h-[480px] overflow-y-auto">
                <template x-for="node in pageItems" :key="node.id">
                    <div class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-sm"
                         :class="{ 'opacity-50': !node.is_active, 'bg-indigo-50/60 ring-1 ring-indigo-100': search.trim() !== '' && node.isMatch }"
                         :style="'padding-left:' + (12 + node.depth * 20) + 'px'">

                        <button type="button" @click="$dispatch('open-edit-category-' + node.id)"
                                class="flex items-center gap-2 min-w-0 text-left flex-1">
                            <i class="fa-solid text-[10px]" :class="node.depth > 0 ? 'fa-turn-up fa-rotate-90 text-gray-300' : 'fa-folder text-indigo-400 text-sm'"></i>
                            <span class="truncate font-medium"
                                  :class="search.trim() !== '' && !node.isMatch ? 'text-gray-400' : 'text-gray-800'"
                                  x-text="node.name"></span>
                            <span x-show="search.trim() !== '' && node.isMatch" class="shrink-0 text-[9px] font-bold uppercase tracking-wide text-indigo-500 bg-indigo-100 px-1.5 py-0.5 rounded">Match</span>
                        </button>

                        <span class="flex items-center gap-1.5 shrink-0">
                            <span x-show="!node.is_active" class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">Inactive</span>

                            <button type="button" @click="$dispatch('open-create-category', { parentId: node.id, parentName: node.name })"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-indigo-50 hover:text-indigo-600" title="Add child category">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>

                            <button type="button" @click="$dispatch('open-edit-category-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>

                            <button type="button" x-show="node.can_delete" @click="$dispatch('delete-category-' + node.id)"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600" title="Delete">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                            <button type="button" x-show="!node.can_delete" disabled :title="'Cannot delete — ' + node.delete_reason"
                                    class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-200 cursor-not-allowed">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </span>
                    </div>
                </template>

                <p x-show="filtered.length === 0" class="text-sm text-gray-400 text-center py-10">
                    <span x-show="allNodes.length === 0">No categories created yet. Use "Add Category" to create the first one.</span>
                    <span x-show="allNodes.length > 0">No categories match "<span x-text="search"></span>".</span>
                </p>
            </div>

            <?php echo $__env->make('backend.admin.catalog.builder._pagination', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-folder-plus text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Add a Category</h3>
                <p class="text-xs text-gray-500 mb-4">Create a top-level category, or pick a parent to nest it as a subcategory.</p>
                <button type="button" @click="$dispatch('open-create-category')" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-lg w-full">
                    <i class="fa-solid fa-plus text-xs mr-1.5"></i> Add Category
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-file-csv text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">Import Categories</h3>
                <p class="text-xs text-gray-500 mb-4">Upload a CSV of full category paths to build out a whole tree in one go — no parent picking needed.</p>
                <button type="button" @click="$dispatch('open-import-categories')" class="text-sm font-semibold px-4 py-2.5 rounded-lg w-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition">
                    <i class="fa-solid fa-upload text-xs mr-1.5"></i> Import CSV
                </button>
                <a href="<?php echo e(route('admin.catalog.categories.import.template')); ?>" class="block mt-2 text-xs text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-download mr-1"></i> Download template
                </a>
            </div>
        </div>
    </div>

    
    <div x-data="{ open: false }" @open-create-category.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Add Category</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="<?php echo e(route('admin.catalog.categories.store')); ?>" class="space-y-4 pt-4"
                  x-data="{ dupBlocked: false }" @category-name-check="dupBlocked = $event.detail"
                  @submit="$el.redirect_to.value = '<?php echo e(route('admin.catalog.builder.categories')); ?>' + window.location.search">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.categories')); ?>">
                <?php echo $__env->make('backend.admin.catalog.categories._form', [
                    'category' => new \App\Models\Category(),
                    'parents' => $parents,
                    'existingCategories' => $tree,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="dupBlocked" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">Create Category</button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-data="{ open: <?php echo e(session('open_import') ? 'true' : 'false'); ?> }" @open-import-categories.window="open = true"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100"
             x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Import Categories</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <div class="pt-4 space-y-4">
                <p class="text-xs text-gray-500">
                    Upload a CSV with a <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">path</code> column —
                    one full category path per row, segments separated by <code class="bg-gray-100 px-1 py-0.5 rounded text-[11px]">&gt;</code>.
                    Existing segments are reused, never duplicated, so it's safe to re-upload the same file.
                </p>
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 text-[11px] font-mono text-gray-600 leading-5">
                    path<br>
                    Electronics<br>
                    Electronics &gt; Computer<br>
                    Electronics &gt; Computer &gt; Laptop
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

                <form method="POST" action="<?php echo e(route('admin.catalog.categories.import.preview')); ?>" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CSV File</label>
                        <input type="file" name="file" accept=".csv,text/csv" required
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-lg">
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="<?php echo e(route('admin.catalog.categories.import.template')); ?>" class="text-xs font-medium text-indigo-600 hover:underline">
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('import_preview')): ?>
        <?php ($importPreview = session('import_preview')); ?>
        <div x-data="{ open: <?php echo e(session('open_import_preview') ? 'true' : 'false'); ?> }"
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
                        <i class="fa-solid fa-plus"></i> <?php echo e($importPreview['created_count']); ?> new
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                        <i class="fa-solid fa-check"></i> <?php echo e($importPreview['existing_count']); ?> already exist (will be reused)
                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importPreview['error_count'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-red-50 text-red-700 border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($importPreview['error_count']); ?> row error<?php echo e($importPreview['error_count'] === 1 ? '' : 's'); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $importPreview['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($row['error'])): ?>
                                <p class="text-xs text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Line <?php echo e($row['line']); ?>: <?php echo e($row['path']); ?> — <?php echo e($row['error']); ?></p>
                            <?php else: ?>
                                <p class="text-xs flex flex-wrap items-center gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $row['segments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $seg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i > 0): ?>
                                            <i class="fa-solid fa-chevron-right text-[8px] text-gray-300"></i>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <span class="<?php echo e($seg['status'] === 'existing' ? 'text-gray-500' : 'text-emerald-700 font-semibold'); ?>"><?php echo e($seg['name']); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="ml-2 text-[10px] font-semibold px-1.5 py-0.5 rounded-full <?php echo e(collect($row['segments'])->contains('status', 'would_create') ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-500'); ?>">
                                        <?php echo e(collect($row['segments'])->contains('status', 'would_create') ? 'Creates new' : 'All exist'); ?>

                                    </span>
                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 mt-2 border-t border-gray-100 shrink-0">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <form method="POST" action="<?php echo e(route('admin.catalog.categories.import.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-check mr-1"></i> Confirm Import
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($node['can_delete']): ?>
            <form x-data @delete-category-<?php echo e($node['id']); ?>.window="$el.requestSubmit()"
                  method="POST" action="<?php echo e(route('admin.catalog.categories.destroy', $node['id'])); ?>"
                  onsubmit="return confirmSwal(this, 'Delete Category?', 'Are you sure you want to delete &quot;<?php echo e(addslashes($node['name'])); ?>&quot;? This cannot be undone.', 'warning', 'Yes, Delete')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tree; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div x-data="{ open: false }" @open-edit-category-<?php echo e($node['id']); ?>.window="open = true"
             x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-gray-100 overflow-y-auto max-h-[88vh]"
                 x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Edit "<?php echo e($node['name']); ?>"</h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form method="POST" action="<?php echo e(route('admin.catalog.categories.update', $node['id'])); ?>" class="space-y-4 pt-4"
                      x-data="{ dupBlocked: false }" @category-name-check="dupBlocked = $event.detail"
                      @submit="$el.redirect_to.value = '<?php echo e(route('admin.catalog.builder.categories')); ?>' + window.location.search">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.catalog.builder.categories')); ?>">
                    <?php echo $__env->make('backend.admin.catalog.categories._form', [
                        'category' => $categoryModels->get($node['id']),
                        'parents'  => $parents->where('id', '!=', $node['id'])->values(),
                        'existingCategories' => collect($tree)->reject(fn($n) => $n['id'] === $node['id'])->values(),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                        <a href="<?php echo e(route('admin.catalog.categories.attributes.index', $node['id'])); ?>" class="text-xs font-medium text-indigo-600 hover:underline">
                            Manage attributes for this category &rarr;
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="dupBlocked" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\builder\categories.blade.php ENDPATH**/ ?>