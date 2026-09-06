<?php $__env->startSection('title', 'EduShopify — B2B Education Procurement Marketplace'); ?>
<?php $__env->startSection('meta_description', 'Discover verified suppliers, products and services for educational institutions. Post an RFQ or browse the marketplace on EduShopify.'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('frontend.home.sections._hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('frontend.home.sections._stats_bar', ['stats' => $stats], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('frontend.home.sections._featured_suppliers', ['featuredSuppliers' => $featuredSuppliers], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('frontend.home.sections._all_suppliers', ['allSuppliers' => $allSuppliers, 'tabs' => $allSuppliersTabs], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('frontend.home.sections._why_choose_edushopify', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('frontend.home.sections._events', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend.layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\home\index.blade.php ENDPATH**/ ?>