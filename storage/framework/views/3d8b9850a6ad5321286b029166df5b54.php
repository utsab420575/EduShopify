<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['role']));

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

foreach (array_filter((['role']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $accent = match ($role) {
        'supplier' => 'teal',
        'admin'    => 'violet',
        default    => 'indigo',
    };

    $roleLabel = match ($role) {
        'supplier' => 'Supplier',
        'admin'    => 'Admin',
        default    => 'Buyer',
    };
?>

<!-- Mobile overlay -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" x-transition.opacity></div>

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0"
>
    <!-- Logo + role badge -->
    <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-100 shrink-0">
        <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Edushopify" class="h-7 w-auto">
        </a>
        <span class="text-[10px] font-bold uppercase tracking-wider text-<?php echo e($accent); ?>-700 bg-<?php echo e($accent); ?>-50 border border-<?php echo e($accent); ?>-200 px-1.5 py-0.5 rounded">
            <?php echo e($roleLabel); ?>

        </span>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($role === 'supplier'): ?>
            <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Dashboard','icon' => 'home','route' => 'supplier.dashboard','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Dashboard','icon' => 'home','route' => 'supplier.dashboard','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal019060ae85909a069b284b3cd4e82fc8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal019060ae85909a069b284b3cd4e82fc8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-group','data' => ['label' => 'RFQ Center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'RFQ Center']); ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'RFQ Opportunities','icon' => 'bell','route' => 'supplier.opportunities.index','routePattern' => 'supplier.opportunities.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'RFQ Opportunities','icon' => 'bell','route' => 'supplier.opportunities.index','routePattern' => 'supplier.opportunities.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'My Quotations','icon' => 'inbox-arrow-down','route' => 'supplier.quotations.index','routePattern' => 'supplier.quotations.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'My Quotations','icon' => 'inbox-arrow-down','route' => 'supplier.quotations.index','routePattern' => 'supplier.quotations.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Awarded Projects','icon' => 'trophy','route' => 'supplier.awards.index','routePattern' => 'supplier.awards.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Awarded Projects','icon' => 'trophy','route' => 'supplier.awards.index','routePattern' => 'supplier.awards.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Purchase Orders','icon' => 'clipboard-document-list','route' => 'supplier.purchase-orders.index','routePattern' => 'supplier.purchase-orders.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Purchase Orders','icon' => 'clipboard-document-list','route' => 'supplier.purchase-orders.index','routePattern' => 'supplier.purchase-orders.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal019060ae85909a069b284b3cd4e82fc8)): ?>
<?php $attributes = $__attributesOriginal019060ae85909a069b284b3cd4e82fc8; ?>
<?php unset($__attributesOriginal019060ae85909a069b284b3cd4e82fc8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal019060ae85909a069b284b3cd4e82fc8)): ?>
<?php $component = $__componentOriginal019060ae85909a069b284b3cd4e82fc8; ?>
<?php unset($__componentOriginal019060ae85909a069b284b3cd4e82fc8); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal019060ae85909a069b284b3cd4e82fc8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal019060ae85909a069b284b3cd4e82fc8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-group','data' => ['label' => 'Catalog']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Catalog']); ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'My Listings','icon' => 'document-text','route' => 'supplier.listings.index','routePattern' => 'supplier.listings.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'My Listings','icon' => 'document-text','route' => 'supplier.listings.index','routePattern' => 'supplier.listings.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal019060ae85909a069b284b3cd4e82fc8)): ?>
<?php $attributes = $__attributesOriginal019060ae85909a069b284b3cd4e82fc8; ?>
<?php unset($__attributesOriginal019060ae85909a069b284b3cd4e82fc8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal019060ae85909a069b284b3cd4e82fc8)): ?>
<?php $component = $__componentOriginal019060ae85909a069b284b3cd4e82fc8; ?>
<?php unset($__componentOriginal019060ae85909a069b284b3cd4e82fc8); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal019060ae85909a069b284b3cd4e82fc8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal019060ae85909a069b284b3cd4e82fc8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-group','data' => ['label' => 'Account']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Account']); ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'My Reviews','icon' => 'heart','route' => 'supplier.reviews.index','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'My Reviews','icon' => 'heart','route' => 'supplier.reviews.index','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Messages','icon' => 'chat-bubble-left-right','route' => 'supplier.messages.index','routePattern' => 'supplier.messages.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Messages','icon' => 'chat-bubble-left-right','route' => 'supplier.messages.index','routePattern' => 'supplier.messages.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Subscription','icon' => 'clock','route' => 'supplier.billing','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Subscription','icon' => 'clock','route' => 'supplier.billing','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'My Profile','icon' => 'user-circle','route' => 'supplier.profile','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'My Profile','icon' => 'user-circle','route' => 'supplier.profile','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Documents','icon' => 'document-text','route' => 'supplier.documents','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Documents','icon' => 'document-text','route' => 'supplier.documents','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginala80ab4329e9c1153705eaa60f773d3a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.nav-item','data' => ['label' => 'Support Tickets','icon' => 'lifebuoy','route' => 'supplier.tickets.index','routePattern' => 'supplier.tickets.*','accent' => $accent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.nav-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Support Tickets','icon' => 'lifebuoy','route' => 'supplier.tickets.index','routePattern' => 'supplier.tickets.*','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $attributes = $__attributesOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__attributesOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1)): ?>
<?php $component = $__componentOriginala80ab4329e9c1153705eaa60f773d3a1; ?>
<?php unset($__componentOriginala80ab4329e9c1153705eaa60f773d3a1); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal019060ae85909a069b284b3cd4e82fc8)): ?>
<?php $attributes = $__attributesOriginal019060ae85909a069b284b3cd4e82fc8; ?>
<?php unset($__attributesOriginal019060ae85909a069b284b3cd4e82fc8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal019060ae85909a069b284b3cd4e82fc8)): ?>
<?php $component = $__componentOriginal019060ae85909a069b284b3cd4e82fc8; ?>
<?php unset($__componentOriginal019060ae85909a069b284b3cd4e82fc8); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </nav>
</aside>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\dashboard\sidebar.blade.php ENDPATH**/ ?>