<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['action' => null]));

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

foreach (array_filter((['action' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ open: false }" class="lg:hidden">
    <button type="button" @click="open = true" class="fe-focus-ring w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border text-sm font-semibold" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
        <i class="fa-solid fa-sliders text-xs"></i> Filters
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Filters" @keydown.escape.window="open = false">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-slate-900/40" @click="open = false"></div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="absolute inset-x-0 bottom-0 bg-white rounded-t-2xl max-h-[85vh] flex flex-col">
            <form method="GET" action="<?php echo e($action ?? url()->current()); ?>" class="flex flex-col max-h-[85vh]">
                <div class="flex items-center justify-between px-5 py-4 border-b shrink-0" style="border-color:var(--fe-border);">
                    <span class="text-base font-semibold" style="color:var(--fe-text);">Filters</span>
                    <button type="button" @click="open = false" class="fe-focus-ring w-9 h-9 rounded-lg flex items-center justify-center text-slate-500" aria-label="Close filters">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="px-5 py-4 overflow-y-auto flex-1">
                    <?php echo e($slot); ?>

                </div>
                <div class="px-5 py-4 border-t shrink-0 flex items-center gap-3" style="border-color:var(--fe-border);">
                    <a href="<?php echo e($action ?? url()->current()); ?>" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Reset</a>
                    <button type="submit" class="fe-btn-primary flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\search\filter-drawer.blade.php ENDPATH**/ ?>