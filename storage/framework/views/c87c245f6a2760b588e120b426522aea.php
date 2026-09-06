<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> — EduShopify</title>
<link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon.png')); ?>">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.tailwindcss.com"></script>


<?php echo $__env->make('backend.layouts.partials.shared._theme', ['theme' => $themeSettings ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
    html, body { font-family: 'Inter', sans-serif; }
    [x-cloak] { display: none !important; }
    body { background: var(--page-bg); }
</style>
<?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="min-h-screen text-gray-900 antialiased" x-data="{ mobileSidebar:false, notifOpen:false, profileOpen:false }">

    <div class="fixed inset-0 flex overflow-hidden" style="background:var(--page-bg)">

        <div x-show="mobileSidebar" x-transition.opacity @click="mobileSidebar=false"
             class="fixed inset-0 bg-gray-900/40 z-30 lg:hidden" x-cloak></div>

        <?php echo $__env->yieldContent('sidebar'); ?>

        <div class="flex-1 flex flex-col min-w-0 min-h-0">

            <?php echo $__env->yieldContent('topbar'); ?>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6" style="background:var(--page-bg)">
                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <footer class="bg-white border-t px-4 lg:px-6 py-3 text-center shrink-0" style="border-color:var(--topbar-border)">
                <p class="text-xs text-gray-400">&copy; <?php echo e(now()->year); ?> EduShopify. All rights reserved.</p>
            </footer>

        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global SweetAlert confirmation helper
        function confirmSwal(form, title = 'Are you sure?', text = '', icon = 'warning', confirmBtnText = 'Yes, proceed!') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: icon === 'danger' || icon === 'error' ? '#DC2626' : '#4F46E5',
                cancelButtonColor: '#6B7280',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'rounded-2xl font-sans text-xs',
                    title: 'text-base font-bold text-gray-900',
                    htmlContainer: 'text-xs text-gray-600',
                    confirmButton: 'text-xs font-semibold px-4 py-2 rounded-lg',
                    cancelButton: 'text-xs font-semibold px-4 py-2 rounded-lg',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof form === 'function') {
                        form();
                    } else if (form && form.submit) {
                        form.submit();
                    }
                }
            });
            return false;
        }

        // Global Flash Toast Notifications
        <?php if(session('success')): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: <?php echo json_encode(session('success'), 15, 512) ?>,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        <?php endif; ?>
        <?php if(session('error')): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: <?php echo json_encode(session('error'), 15, 512) ?>,
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true
            });
        <?php endif; ?>
        <?php if(session('warning')): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: <?php echo json_encode(session('warning'), 15, 512) ?>,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        <?php endif; ?>
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\edushopify\resources\views/backend/layouts/master.blade.php ENDPATH**/ ?>