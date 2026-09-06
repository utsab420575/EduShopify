<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'neutral']));

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

foreach (array_filter((['variant' => 'neutral']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colors = match ($variant) {
        'verified' => 'bg-emerald-50 text-emerald-600',
        'success' => 'bg-green-50 text-green-800',
        'warning' => 'bg-amber-50 text-amber-800',
        'danger' => 'bg-red-50 text-red-800',
        'info' => 'bg-sky-50 text-sky-800',
        'brand' => 'bg-emerald-500 text-white',
        default => 'bg-gray-100 text-gray-500',
    };
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium $colors"])); ?>>
    <?php echo e($slot); ?>

</span>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\components\common\badge.blade.php ENDPATH**/ ?>