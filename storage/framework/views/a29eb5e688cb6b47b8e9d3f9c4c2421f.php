<?php $__env->startSection('title', $capability->account?->display_name); ?>
<?php $__env->startSection('breadcrumb', 'Users & Accounts / Capabilities / ' . $capability->account?->display_name); ?>

<?php $__env->startSection('body'); ?>
    <?php echo $__env->make('backend.admin.capabilities._panel', ['capability' => $capability, 'documents' => $documents], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\capabilities\show.blade.php ENDPATH**/ ?>