<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'icon',
    'route' => null,
    'routePattern' => null,
    'accent' => 'indigo',
    'disabled' => false,
    'badge' => null,
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
    'label',
    'icon',
    'route' => null,
    'routePattern' => null,
    'accent' => 'indigo',
    'disabled' => false,
    'badge' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // A nav item is only ever a real link when its route is both given AND
    // registered — this lets sidebar entries for not-yet-built screens stay
    // "Soon" automatically instead of needing a manually-flipped disabled prop.
    $routeExists = $route && \Illuminate\Support\Facades\Route::has($route);
    $disabled = $disabled || ! $routeExists;

    $active = false;
    if (! $disabled) {
        $active = request()->routeIs($routePattern ?? $route . '*');
    }

    $classes = $disabled
        ? 'text-slate-400 cursor-not-allowed'
        : ($active
            ? "bg-{$accent}-50 text-{$accent}-700 font-semibold"
            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900');
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($disabled): ?>
    <span class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm <?php echo e($classes); ?>">
        <?php if (isset($component)) { $__componentOriginald4af31a9fbcc7c391a6cfd9546254e19 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4af31a9fbcc7c391a6cfd9546254e19 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.icon','data' => ['name' => $icon,'class' => 'w-5 h-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'w-5 h-5 shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4af31a9fbcc7c391a6cfd9546254e19)): ?>
<?php $attributes = $__attributesOriginald4af31a9fbcc7c391a6cfd9546254e19; ?>
<?php unset($__attributesOriginald4af31a9fbcc7c391a6cfd9546254e19); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4af31a9fbcc7c391a6cfd9546254e19)): ?>
<?php $component = $__componentOriginald4af31a9fbcc7c391a6cfd9546254e19; ?>
<?php unset($__componentOriginald4af31a9fbcc7c391a6cfd9546254e19); ?>
<?php endif; ?>
        <span class="flex-1"><?php echo e($label); ?></span>
        <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-300 bg-slate-100 px-1.5 py-0.5 rounded">Soon</span>
    </span>
<?php else: ?>
    <a href="<?php echo e(route($route)); ?>" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors <?php echo e($classes); ?>">
        <x-dashboard.icon :name="$icon" class="w-5 h-5 shrink-0 <?php echo e($active ? "text-{$accent}-600" : 'text-slate-400 group-hover:text-slate-600'); ?>" />
        <span class="flex-1"><?php echo e($label); ?></span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($badge): ?>
            <span class="text-[11px] font-semibold text-white bg-<?php echo e($accent); ?>-500 rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center"><?php echo e($badge); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\dashboard\nav-item.blade.php ENDPATH**/ ?>