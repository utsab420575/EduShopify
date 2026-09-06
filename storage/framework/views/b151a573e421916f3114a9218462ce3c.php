<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'groups' => [],
    'selected' => [],
    'name' => 'permissions',
    'roleScope' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'groups' => [],
    'selected' => [],
    'name' => 'permissions',
    'roleScope' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $selected = collect($selected)->map(fn ($v) => (string) $v);
    $groups = collect($groups);
    $totalCount = $groups->sum(fn ($perms) => $perms->count());

    // A group is "relevant" to the role's own scope if any permission in it
    // shares that scope, is 'common', or is 'both' — those groups start
    // expanded; everything else starts collapsed (not hidden) so a platform
    // role isn't opened straight into a wall of buyer/supplier checkboxes,
    // while a genuinely cross-scope permission is still one click away.
    $isGroupRelevant = function ($perms) use ($roleScope) {
        if (! $roleScope || $roleScope === 'both') {
            return true;
        }
        $groupScope = $perms->first()?->capability_scope;

        return in_array($groupScope, [$roleScope, 'common', 'both'], true);
    };
?>

<div
    <?php echo e($attributes); ?>

    x-data="{
        search: '',
        selectedCount: <?php echo e($selected->count()); ?>,
        totalCount: <?php echo e($totalCount); ?>,
        updateCount() {
            this.selectedCount = this.$el.querySelectorAll('.perm-matrix-checkbox:checked').length;
        },
        setAll(checked) {
            this.$el.querySelectorAll('.perm-matrix-checkbox').forEach(cb => cb.checked = checked);
            this.updateCount();
        },
        setGroup(groupEl, checked) {
            groupEl.querySelectorAll('.perm-matrix-checkbox').forEach(cb => cb.checked = checked);
            this.updateCount();
        },
        groupMatchesSearch(haystack) {
            return this.search === '' || haystack.toLowerCase().includes(this.search.toLowerCase());
        },
    }"
    x-init="updateCount()"
>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 border-b border-gray-100 pb-4 mb-4">
        <div class="relative flex-1 min-w-[200px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" x-model="search" placeholder="Search permissions by name…"
                class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="text-xs font-semibold text-gray-500" x-text="selectedCount + ' of ' + totalCount + ' selected'"></span>
            <button type="button" @click="setAll(true)" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Select All</button>
            <span class="text-gray-300">|</span>
            <button type="button" @click="setAll(false)" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Clear All</button>
        </div>
    </div>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupPermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $groupHaystack = $groupPermissions->map(fn ($p) => $p->name.' '.($p->display_name ?? ''))->implode(' ');
                $relevant = $isGroupRelevant($groupPermissions);
                $groupSelectedCount = $groupPermissions->filter(fn ($p) => $selected->contains($p->name))->count();
            ?>
            <div
                x-data="{ expanded: <?php echo e($relevant ? 'true' : 'false'); ?> }"
                x-show="groupMatchesSearch(<?php echo \Illuminate\Support\Js::from($groupHaystack)->toHtml() ?>)"
                class="perm-matrix-group rounded-xl border border-gray-200/80 bg-gray-50/70 overflow-hidden"
            >
                <button type="button" @click="expanded = !expanded"
                    class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left hover:bg-gray-100/60 transition-colors">
                    <span class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 transition-transform shrink-0" :class="expanded && 'rotate-90'"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-800 truncate"><?php echo e($groupName ?: 'General'); ?></span>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-600 shrink-0"><?php echo e($groupSelectedCount); ?>/<?php echo e($groupPermissions->count()); ?></span>
                    </span>
                    <label class="inline-flex items-center gap-1.5 text-[11px] text-gray-500 hover:text-gray-800 cursor-pointer shrink-0" @click.stop>
                        <input type="checkbox" @change="setGroup($el.closest('.perm-matrix-group'), $event.target.checked)" class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        Select all in group
                    </label>
                </button>

                <div x-show="expanded" class="px-4 pb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($checked = $selected->contains($permission->name)); ?>
                            <label
                                x-show="groupMatchesSearch(<?php echo \Illuminate\Support\Js::from($permission->name.' '.($permission->display_name ?? ''))->toHtml() ?>)"
                                class="flex items-start gap-2 text-xs text-gray-700 bg-white border border-gray-200 rounded-lg p-2.5 hover:border-indigo-300 cursor-pointer transition-colors"
                            >
                                <input type="checkbox" name="<?php echo e($name); ?>[]" value="<?php echo e($permission->name); ?>" <?php if($checked): echo 'checked'; endif; ?>
                                    @change="updateCount()"
                                    class="perm-matrix-checkbox w-4 h-4 mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 flex items-center gap-1.5 flex-wrap">
                                        <?php echo e($permission->display_name ?? $permission->name); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($permission->is_sensitive): ?>
                                            <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">Sensitive</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-mono truncate"><?php echo e($permission->name); ?></div>
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\backend\permission-matrix.blade.php ENDPATH**/ ?>