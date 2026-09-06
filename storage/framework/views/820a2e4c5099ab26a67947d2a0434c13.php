<?php $__env->startSection('title', 'Edushopify – Global Suppliers for Education'); ?>

<?php $__env->startSection('content'); ?>

    
    <?php echo $__env->make('frontend_new.home.partial._hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.home.partial._featured_suppliers', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.home.partial._all_suppliers', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.home.partial._why_choose', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.home.partial._events', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend_new.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\home\index.blade.php ENDPATH**/ ?>