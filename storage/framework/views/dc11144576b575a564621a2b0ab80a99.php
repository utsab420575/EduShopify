
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title', 'count', 'searchParam', 'pageParam', 'currentSearch', 'placeholder' => 'Search...',
    'filterParams' => [], 'hasActiveFilter' => false,
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
    'title', 'count', 'searchParam', 'pageParam', 'currentSearch', 'placeholder' => 'Search...',
    'filterParams' => [], 'hasActiveFilter' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<div x-data="{ filterOpen: <?php echo e($hasActiveFilter ? 'true' : 'false'); ?> }" class="relative">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-gray-900"><?php echo e($title); ?></h3>
            <span class="text-xs text-gray-400"><?php echo e($count); ?> total</span>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = request()->except(array_merge([$searchParam, $pageParam], $filterParams)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!is_array($value)): ?>
                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="<?php echo e($searchParam); ?>" value="<?php echo e($currentSearch); ?>" placeholder="<?php echo e($placeholder); ?>"
                       @input.debounce.500ms="$event.target.form.requestSubmit()"
                       x-init="if (new URLSearchParams(window.location.search).get('<?php echo e($searchParam); ?>')) { $el.focus(); $el.setSelectionRange($el.value.length, $el.value.length); }"
                       class="focus-accent w-full sm:w-56 pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($filters)): ?>
                <button type="button" @click="filterOpen = !filterOpen"
                        class="relative text-sm font-medium px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-sliders text-xs"></i> Filters
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasActiveFilter): ?>
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full" style="background:var(--theme-primary)"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($filters)): ?>
                <div x-show="filterOpen" @click.outside="filterOpen = false" x-transition x-cloak
                     class="absolute z-20 top-full mt-1.5 right-5 bg-white border border-gray-200 rounded-lg shadow-lg p-4 flex items-end gap-3 whitespace-nowrap">
                    <?php echo e($filters); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </form>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\backend\table-search.blade.php ENDPATH**/ ?>