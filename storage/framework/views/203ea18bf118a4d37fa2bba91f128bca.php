
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'column',
    'label',
    'sortParam' => 'sort',
    'directionParam' => 'direction',
    'currentSort' => request('sort'),
    'currentDirection' => request('direction', 'desc'),
    'pageParam' => 'page',
    'align' => 'left',
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
    'column',
    'label',
    'sortParam' => 'sort',
    'directionParam' => 'direction',
    'currentSort' => request('sort'),
    'currentDirection' => request('direction', 'desc'),
    'pageParam' => 'page',
    'align' => 'left',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $currentSort = $currentSort ?? request($sortParam);
    $currentDirection = $currentDirection ?? request($directionParam, 'desc');
    $isActive = $currentSort === $column;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([$sortParam => $column, $directionParam => $nextDirection, $pageParam => null]);
    $icon = $isActive ? ($currentDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
    $iconColor = $isActive ? 'text-indigo-600' : 'text-gray-400';
?>
<th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider <?php echo e($align === 'right' ? 'text-right' : ''); ?>">
    <a href="<?php echo e($url); ?>" class="inline-flex items-center gap-1 hover:text-gray-700 transition-colors <?php echo e($align === 'right' ? 'flex-row-reverse' : ''); ?> <?php echo e($isActive ? 'text-gray-700' : ''); ?>">
        <span><?php echo e($label); ?></span>
        <i class="fa-solid <?php echo e($icon); ?> <?php echo e($iconColor); ?> ml-1 text-[11px]"></i>
    </a>
</th>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\backend\sortable-th.blade.php ENDPATH**/ ?>