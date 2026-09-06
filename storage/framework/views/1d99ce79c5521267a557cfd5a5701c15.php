<?php $__env->startSection('title', $listing->name . ' — Listing Review'); ?>
<?php $__env->startSection('breadcrumb', 'Catalog & Taxonomy / Listings / ' . $listing->name); ?>

<?php $__env->startSection('body'); ?>
    <?php echo $__env->make('backend.admin.catalog.listings._panel', [
        'listing' => $listing,
        'groupedSpecifications' => $groupedSpecifications,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\listings\show.blade.php ENDPATH**/ ?>