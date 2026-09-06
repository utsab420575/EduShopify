<?php $__env->startSection('title', 'Education Resources Center | Edushopify'); ?>

<?php $__env->startSection('body_class', 'bg-gray-50'); ?>

<?php $__env->startSection('content'); ?>

    
    <?php echo $__env->make('frontend_new.resources.partials._hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.resources.partials._categories', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.resources.partials._content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.resources.partials._features', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend_new.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\resources\index.blade.php ENDPATH**/ ?>