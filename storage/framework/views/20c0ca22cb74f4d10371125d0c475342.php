<?php $__env->startSection('title', $listing->name); ?>
<?php $__env->startSection('breadcrumb', 'Marketplace / Products / ' . $listing->name); ?>

<?php $__env->startSection('body'); ?>

    
    <?php
        $gallery = $listing->getMedia('gallery');
        $primaryId = $listing->primary_image_media_id;
        $heroFirst = $gallery->sortByDesc(fn ($m) => $m->id === $primaryId)->values();
        $firstMedia = $heroFirst->first();
        $hasVariants = $listing->isProduct() && $listing->variants->isNotEmpty();
    ?>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6" x-data="{ tab: 'overview', heroUrl: '<?php echo e($firstMedia?->getUrl()); ?>' }">

        <div class="xl:col-span-8 space-y-5">
            <?php echo $__env->make('backend.buyer.marketplace.products.partials._hero', ['heroFirst' => $heroFirst, 'firstMedia' => $firstMedia], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <nav class="flex gap-0 border-b border-gray-100 px-1 overflow-x-auto">
                    <button type="button" @click="tab = 'overview'"
                            class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                            :class="tab === 'overview' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                        <i class="fa-regular fa-file-lines mr-1.5 text-xs"></i>Overview
                    </button>
                    <button type="button" @click="tab = 'specifications'"
                            class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                            :class="tab === 'specifications' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                        <i class="fa-solid fa-sliders mr-1.5 text-xs"></i>Specifications
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->attributeValues->isNotEmpty()): ?>
                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600"><?php echo e($listing->attributeValues->count()); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasVariants): ?>
                        <button type="button" @click="tab = 'variants'"
                                class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                                :class="tab === 'variants' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                            <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>Variants
                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600"><?php echo e($listing->variants->count()); ?></span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </nav>

                <div class="p-5">
                    <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <?php echo $__env->make('backend.buyer.marketplace.products.partials._overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    <div x-show="tab === 'specifications'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <?php echo $__env->make('backend.buyer.marketplace.products.partials._specifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasVariants): ?>
                        <div x-show="tab === 'variants'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <?php echo $__env->make('backend.buyer.marketplace.products.partials._variants', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="xl:col-span-4 space-y-4">
            <?php echo $__env->make('backend.buyer.marketplace.products.partials._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.buyer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\marketplace\products\show.blade.php ENDPATH**/ ?>