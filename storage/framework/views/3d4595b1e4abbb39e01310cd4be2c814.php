
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <div class="xl:col-span-8 space-y-6">
        <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-specifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-variants', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="xl:col-span-4 space-y-6">
        <?php echo $__env->make('backend.supplier.catalog.listings.partials.preview-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\catalog\listings\partials\listing-preview.blade.php ENDPATH**/ ?>