<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'code' => '',
    'icon' => 'fa-triangle-exclamation',
    'title' => 'Something went wrong',
    'message' => null,
    'accent' => 'gray', // gray | amber | red
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'code' => '',
    'icon' => 'fa-triangle-exclamation',
    'title' => 'Something went wrong',
    'message' => null,
    'accent' => 'gray', // gray | amber | red
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $accents = [
        'gray' => ['soft' => '#f3f4f6', 'text' => '#4b5563', 'ring' => '#e5e7eb'],
        'amber' => ['soft' => '#fffbeb', 'text' => '#b45309', 'ring' => '#fde68a'],
        'red' => ['soft' => '#fef2f2', 'text' => '#dc2626', 'ring' => '#fecaca'],
    ];
    $a = $accents[$accent] ?? $accents['gray'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($code ? $code . ' — ' : ''); ?><?php echo e($title); ?> — <?php echo e(config('app.name', 'EduShopify')); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --theme-primary: #4f46e5;
            --theme-primary-hover: #4338ca;
            --theme-primary-soft: #eef2ff;
            --page-bg: #f9fafb;
        }
        html, body { font-family: 'Inter', sans-serif; }
        body { background: var(--page-bg); }
        .btn-primary { background: var(--theme-primary); color: #ffffff; }
        .btn-primary:hover { background: var(--theme-primary-hover); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10 text-gray-900 antialiased">

    <div class="w-full max-w-md">

        <div class="flex items-center justify-center gap-2.5 mb-6">
            <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm shrink-0">ES</div>
            <span class="text-sm font-bold text-gray-900"><?php echo e(config('app.name', 'EduShopify')); ?></span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center shadow-sm">

            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5"
                 style="background: <?php echo e($a['soft']); ?>; border: 1px solid <?php echo e($a['ring']); ?>">
                <i class="fa-solid <?php echo e($icon); ?> text-xl" style="color: <?php echo e($a['text']); ?>"></i>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($code): ?>
                <p class="text-xs font-semibold uppercase tracking-wider mb-1.5" style="color: <?php echo e($a['text']); ?>">Error <?php echo e($code); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <h1 class="text-lg font-bold text-gray-900"><?php echo e($title); ?></h1>

            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                <?php echo e($message ?? 'Please try again, or head back to somewhere safe.'); ?>

            </p>

            <div class="flex items-center justify-center gap-2 mt-6">
                <a href="javascript:history.back()"
                   class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    <i class="fa-solid fa-arrow-left mr-1.5 text-xs"></i> Go Back
                </a>
                <a href="<?php echo e(url('/')); ?>"
                   class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-house"></i> Go Home
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Need help? <a href="<?php echo e(url('/')); ?>" class="font-medium hover:underline" style="color: var(--theme-primary)">Contact support</a>
        </p>
    </div>

</body>
</html>
<?php /**PATH C:\laragon\www\edushopify\resources\views\components\error-page.blade.php ENDPATH**/ ?>