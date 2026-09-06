<?php $__env->startSection('title', 'Business Profile'); ?>
<?php $__env->startSection('breadcrumb', 'Business Profile'); ?>

<?php $__env->startSection('body'); ?>

<?php
    // Every accordion section below computes its initial open/closed state
    // through this one helper: a validation failure or a just-completed
    // save always force-opens its own section (via the `section` query
    // param every Company\* controller/Form Request redirects with), and
    // otherwise the section falls back to whatever the visitor last left it
    // as (persisted client-side — design.md §43.3 requires accordion state
    // to be instant/zero-server-latency, so this never touches the server).
    $spAccOpen = function (string $key, bool $defaultOpen) use ($openSection) {
        if ($openSection === $key) {
            return 'true';
        }

        return $defaultOpen ? "localStorage.getItem('sp-acc-{$key}') !== '0'" : "localStorage.getItem('sp-acc-{$key}') === '1'";
    };
?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-5 right-5 z-[200] flex items-center gap-3 bg-white border border-green-200 text-green-800 text-sm font-medium rounded-xl shadow-lg px-5 py-3">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Business Profile</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your company profile, branding, locations, and documents.</p>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->isComplete()): ?>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-green-50 text-green-700 border border-green-200">
            <i class="fa-solid fa-circle-check"></i> Profile Complete
        </span>
    <?php else: ?>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
            <i class="fa-solid fa-clock"></i> Draft
        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-4">
        <div class="relative flex-shrink-0">
            <img src="<?php echo e($profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=e0e7ff&color=4f46e5&size=80'); ?>"
                 class="w-16 h-16 rounded-2xl object-cover border border-gray-200 shadow-sm" alt="Logo">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->profile_photo): ?>
                <img src="<?php echo e(asset('storage/'.$profile->profile_photo)); ?>"
                     class="w-7 h-7 rounded-full border-2 border-white absolute -bottom-1 -right-1 object-cover shadow" alt="">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-lg font-bold text-gray-900 truncate"><?php echo e($profile?->display_name ?? 'Your Business Name'); ?></p>
            <p class="text-sm text-gray-500 truncate"><?php echo e($profile?->contact_email ?: $profile?->support_email); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile?->country_id): ?>
                <p class="text-xs text-gray-400 mt-0.5">
                    <i class="fa-solid fa-location-dot mr-1 text-indigo-400"></i>
                    <?php echo e(collect([$profile->city?->name, $profile->country?->name])->filter()->implode(', ')); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="hidden lg:flex items-center gap-5 flex-shrink-0 border-l border-gray-100 pl-5">
            <div class="text-center">
                <p class="text-xl font-bold text-amber-500"><i class="fa-solid fa-star text-sm"></i> <?php echo e(number_format($profile?->rating ?? 0, 1)); ?></p>
                <p class="text-xs text-gray-400">Rating</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-pink-600"><?php echo e($existingGallery->count()); ?></p>
                <p class="text-xs text-gray-400">Gallery</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-emerald-600"><?php echo e($serviceAreas->count()); ?></p>
                <p class="text-xs text-gray-400">Locations</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-rose-600"><?php echo e($documents->where('is_current', true)->count()); ?></p>
                <p class="text-xs text-gray-400">Documents</p>
            </div>
        </div>
    </div>
</div>


<div class="space-y-3">
    <?php echo $__env->make('backend.supplier.company.partials._company', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._contact', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._media', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._gallery', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._locations', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._hours', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._exhibitions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._documents', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._services', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('backend.supplier.company.partials._achievements', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($openSection): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('sp-section-<?php echo e($openSection); ?>');
            if (el) {
                setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 50);
            }
        });
    </script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6" role="alert">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <p class="text-sm"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6" role="alert">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <p class="text-sm"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('warning')): ?>
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6" role="alert">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <p class="text-sm"><?php echo e(session('warning')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo $__env->yieldContent('body'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\company\profile.blade.php ENDPATH**/ ?>