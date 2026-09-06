<?php
    $tabs = [
        'categories'        => ['label' => 'Category', 'icon' => 'fa-sitemap', 'route' => 'admin.catalog.builder.categories'],
        'attribute-groups'  => ['label' => 'Attribute Group', 'icon' => 'fa-layer-group', 'route' => 'admin.catalog.builder.attribute-groups'],
        'attributes'        => ['label' => 'Attribute', 'icon' => 'fa-sliders', 'route' => 'admin.catalog.builder.attributes'],
        'assign'            => ['label' => 'Assign to Category', 'icon' => 'fa-link', 'route' => 'admin.catalog.builder.assign'],
    ];
    $tabKeys = array_keys($tabs);
?>

<div class="mb-6">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Category Builder</h1>
        <p class="text-sm text-gray-500 mt-1">A guided workspace for setting up categories, attribute groups, attributes, and category assignments. Steps can be used in any order.</p>
    </div>

    <div class="border-b border-gray-200">
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($tab['route'])); ?>"
                   class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 <?php echo e($active === $key ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'); ?>">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold <?php echo e($active === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'); ?>">
                        <?php echo e(array_search($key, $tabKeys) + 1); ?>

                    </span>
                    <i class="fa-solid <?php echo e($tab['icon']); ?> text-xs"></i>
                    <?php echo e($tab['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\builder\_tabs.blade.php ENDPATH**/ ?>