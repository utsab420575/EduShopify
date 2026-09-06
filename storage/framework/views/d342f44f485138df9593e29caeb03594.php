<?php $__env->startSection('title', 'Open RFQs – Edushopify'); ?>
<?php $__env->startSection('body_class', 'bg-gray-50'); ?>

<?php $__env->startSection('content'); ?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

    
    <?php echo $__env->make('frontend_new.rfqs.partials._hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('frontend_new.rfqs.partials._content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('frontend_new.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\rfqs\index.blade.php ENDPATH**/ ?>