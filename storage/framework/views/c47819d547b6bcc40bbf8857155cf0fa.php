<?php $__env->startSection('title', 'Edit ' . $category->name); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Categories / Edit'); ?>

<?php $__env->startSection('body'); ?>

    
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit <?php echo e($category->name); ?></h1>
                <p class="text-sm text-gray-500 mt-1">Configure category properties, hierarchy, and assigned specification attributes.</p>
            </div>
            <a href="<?php echo e(route('admin.catalog.categories.attributes.index', $category)); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-sliders text-xs"></i> Manage Specifications (<?php echo e($category->attributes()->count()); ?>)
            </a>
        </div>

        <div class="border-b border-gray-200">
            <nav class="flex gap-6 -mb-px">
                <a href="<?php echo e(route('admin.catalog.categories.edit', $category)); ?>" class="py-3 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600">
                    General Information
                </a>
                <a href="<?php echo e(route('admin.catalog.categories.attributes.index', $category)); ?>" class="py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent flex items-center gap-2">
                    <span>Specifications &amp; Attributes</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 font-semibold"><?php echo e($category->attributes()->count()); ?></span>
                </a>
            </nav>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.catalog.categories.update', $category)); ?>" class="space-y-6">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <?php echo $__env->make('backend.admin.catalog.categories._form', ['category' => $category, 'parents' => $parents], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="<?php echo e(route('admin.catalog.categories.index')); ?>" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\categories\edit.blade.php ENDPATH**/ ?>