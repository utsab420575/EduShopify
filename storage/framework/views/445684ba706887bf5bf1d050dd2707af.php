<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="compare-max-items" content="<?php echo e((int) config('comparison.max_items', 5)); ?>">

    <title><?php echo $__env->yieldContent('title', 'EduShopify — B2B Education Procurement Marketplace'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'EduShopify is the B2B marketplace connecting educational institutions with verified suppliers of products and services, powered by structured RFQ procurement.'); ?>">
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    <meta property="og:site_name" content="EduShopify">
    <meta property="og:title" content="<?php echo $__env->yieldContent('title', 'EduShopify — B2B Education Procurement Marketplace'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('meta_description', 'EduShopify is the B2B marketplace connecting educational institutions with verified suppliers of products and services.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">

    <link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/frontend.css', 'resources/js/frontend.js']); ?>

    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\layouts\partials\_head.blade.php ENDPATH**/ ?>