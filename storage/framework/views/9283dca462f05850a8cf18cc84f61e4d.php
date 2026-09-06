<?php ($existingCategories = $existingCategories ?? []); ?>

<?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Category Details']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Category Details']); ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"
         x-data="{
            currentId: <?php echo e(Js::from($category->id ?? null)); ?>,
            q: <?php echo e(Js::from(old('name', $category->name ?? ''))); ?>,
            parentId: <?php echo e(Js::from(old('parent_id', $category->parent_id ?? ''))); ?>,
            categories: <?php echo e(Js::from(collect($existingCategories)->map(fn($c) => [
                'id' => is_array($c) ? $c['id'] : $c->id,
                'name' => is_array($c) ? $c['name'] : $c->name,
                'parent_id' => is_array($c) ? ($c['parent_id'] ?? null) : $c->parent_id,
                'parent_name' => is_array($c) ? ($c['parent_name'] ?? null) : ($c->parent?->name ?? null),
            ])->values())); ?>,
            focused: false,
            get matches() {
                const v = this.q.trim().toLowerCase();
                if (v.length < 2) return [];
                return this.categories
                    .filter(c => c.id !== this.currentId && c.name.toLowerCase().includes(v))
                    .slice(0, 8);
            },
            get isDuplicate() {
                const name = this.q.trim().toLowerCase();
                if (!name) return false;
                const pId = this.parentId ? Number(this.parentId) : null;
                return this.categories.some(c =>
                    c.id !== this.currentId &&
                    c.name.toLowerCase() === name &&
                    (c.parent_id ? Number(c.parent_id) : null) === pId
                );
            },

            // ── Searchable, path-aware parent picker ──────────────────────
            // Walks parent_id upward through the same categories list to
            // build a full 'A greater-than B greater-than C' breadcrumb, so
            // it works whether or not the caller happened to also pass a
            // precomputed 'depth' (the Category Builder does; the
            // standalone create/edit pages don't) — self-sufficient either way.
            pathLabel(id) {
                if (!id) return 'Root category';
                const parts = [];
                let node = this.categories.find(c => c.id === Number(id));
                let guard = 0;
                while (node && guard < 20) {
                    parts.unshift(node.name);
                    node = node.parent_id ? this.categories.find(c => c.id === Number(node.parent_id)) : null;
                    guard++;
                }
                return parts.join(' > ') || 'Root category';
            },
            parentQuery: '',
            parentFocused: false,
            get parentSelectedLabel() { return this.pathLabel(this.parentId); },
            get parentMatches() {
                const v = this.parentQuery.trim().toLowerCase();
                const pool = this.categories.filter(c => c.id !== this.currentId);
                const filtered = v === '' ? pool : pool.filter(c => c.name.toLowerCase().includes(v));
                return filtered
                    .map(c => ({ id: c.id, label: this.pathLabel(c.id) }))
                    .sort((a, b) => a.label.localeCompare(b.label))
                    .slice(0, 30);
            },
            selectParent(id) {
                this.parentId = id;
                this.parentQuery = '';
                this.parentFocused = false;
            }
        }" x-effect="$dispatch('category-name-check', isDuplicate)"
         @open-create-category.window="if (!currentId) { parentId = ($event.detail && $event.detail.parentId) ? $event.detail.parentId : ''; parentQuery = ''; }">

        
        <div>
            <div class="relative">
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'name','label' => 'Name','required' => true,'value' => $category->name,'xModel' => 'q','autocomplete' => 'off','@focus' => 'focused = true','@blur' => 'setTimeout(() => focused = false, 200)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => 'Name','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category->name),'x-model' => 'q','autocomplete' => 'off','@focus' => 'focused = true','@blur' => 'setTimeout(() => focused = false, 200)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>

                <div x-show="focused && matches.length > 0" x-cloak
                     class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                    <div class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                        Existing categories matching "<span x-text="q"></span>"
                    </div>
                    <div class="max-h-44 overflow-y-auto divide-y divide-gray-50">
                        <template x-for="item in matches" :key="item.id">
                            <div class="px-3 py-2 text-xs flex items-center justify-between gap-2"
                                 :class="(item.name.toLowerCase() === q.trim().toLowerCase() && (item.parent_id ? Number(item.parent_id) : null) === (parentId ? Number(parentId) : null)) ? 'bg-red-50 text-red-700 font-semibold' : 'text-gray-700'">
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <i class="fa-solid fa-folder text-gray-300 text-[10px] shrink-0"></i>
                                    <span class="truncate" x-text="item.name"></span>
                                    <span class="text-[10px] text-gray-400 font-normal shrink-0" x-text="item.parent_name ? '(under ' + item.parent_name + ')' : '(Root)'"></span>
                                </span>
                                <template x-if="item.name.toLowerCase() === q.trim().toLowerCase() && (item.parent_id ? Number(item.parent_id) : null) === (parentId ? Number(parentId) : null)">
                                    <span class="text-[9px] font-bold uppercase text-red-600 shrink-0">Duplicate under same parent</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <template x-if="isDuplicate">
                <p class="mt-1 text-xs text-red-600 font-medium">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    A category named "<span x-text="q"></span>" already exists under the selected parent category.
                </p>
            </template>
        </div>

        
        <div class="relative">
            <label for="parent_id_search" class="block text-sm font-medium text-gray-700 mb-1.5">Parent Category</label>
            <input type="hidden" name="parent_id" x-model="parentId">

            <div class="relative">
                <i class="fa-solid fa-sitemap absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="parent_id_search" autocomplete="off"
                       class="focus-accent w-full text-sm rounded-lg border border-gray-300 pl-8 pr-8 py-2.5 bg-white"
                       :placeholder="parentSelectedLabel"
                       x-model="parentQuery"
                       @focus="parentFocused = true; parentQuery = ''"
                       @blur="setTimeout(() => parentFocused = false, 200)">
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
            </div>

            <p class="mt-1 text-xs text-gray-500" x-show="!parentFocused">
                Currently: <span class="font-medium text-gray-700" x-text="parentSelectedLabel"></span>
            </p>

            <div x-show="parentFocused" x-cloak
                 class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                <button type="button" @mousedown.prevent="selectParent('')"
                        class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-indigo-50 flex items-center gap-1.5"
                        :class="!parentId ? 'text-indigo-700 bg-indigo-50' : 'text-gray-700'">
                    <i class="fa-solid fa-house text-[10px] text-gray-400"></i> Root category (no parent)
                </button>
                <div class="max-h-52 overflow-y-auto divide-y divide-gray-50 border-t border-gray-100">
                    <template x-for="item in parentMatches" :key="item.id">
                        <button type="button" @mousedown.prevent="selectParent(item.id)"
                                class="w-full text-left px-3 py-2 text-xs hover:bg-indigo-50 flex items-center gap-1.5 truncate"
                                :class="Number(parentId) === item.id ? 'text-indigo-700 bg-indigo-50 font-semibold' : 'text-gray-700'">
                            <i class="fa-solid fa-folder text-[10px] text-gray-300 shrink-0"></i>
                            <span class="truncate" x-text="item.label"></span>
                        </button>
                    </template>
                    <p x-show="parentMatches.length === 0" class="px-3 py-3 text-xs text-gray-400 text-center">No categories match.</p>
                </div>
            </div>
        </div>

        <?php if (isset($component)) { $__componentOriginalbed0546cb676c4dfa33e9654d0b1c543 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.select','data' => ['name' => 'type','label' => 'Type','required' => true,'selected' => $category->type ?: 'both','options' => ['product' => 'Product', 'service' => 'Service', 'both' => 'Both']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'type','label' => 'Type','required' => true,'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category->type ?: 'both'),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['product' => 'Product', 'service' => 'Service', 'both' => 'Both'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543)): ?>
<?php $attributes = $__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543; ?>
<?php unset($__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbed0546cb676c4dfa33e9654d0b1c543)): ?>
<?php $component = $__componentOriginalbed0546cb676c4dfa33e9654d0b1c543; ?>
<?php unset($__componentOriginalbed0546cb676c4dfa33e9654d0b1c543); ?>
<?php endif; ?>
        <div class="flex items-center gap-2 pt-7">
            <input type="checkbox" name="is_active" id="is_active" value="1" <?php if(old('is_active', $category->exists ? $category->is_active : true)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>
    </div>
    <div class="mt-4">
        <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'description','label' => 'Description','value' => $category->description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'description','label' => 'Description','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($category->description)]); ?>
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
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $attributes = $__attributesOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $component = $__componentOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__componentOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\categories\_form.blade.php ENDPATH**/ ?>