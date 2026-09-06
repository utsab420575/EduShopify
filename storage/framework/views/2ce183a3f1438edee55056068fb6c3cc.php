
<style>
  /* ── Tab Buttons Styling ── */
  .category-tab-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 1rem; /* px-4 py-1.5 */
    font-size: 0.75rem; /* text-xs */
    font-weight: 600;
    line-height: 1rem;
    border-radius: 9999px;
    border-width: 1px;
    border-style: solid;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* Active Tab: Dark charcoal pill with white text */
  .category-tab-btn.active {
    background-color: #0f172a; /* slate-900 */
    color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }

  /* Inactive Tab: Clean white pill with gray border */
  .category-tab-btn:not(.active) {
    background-color: #ffffff;
    color: #4b5563; /* gray-600 */
    border-color: #e5e7eb; /* gray-200 */
  }

  /* Inactive Tab Hover: Accent emerald border + text */
  .category-tab-btn:not(.active):hover {
    border-color: #10b981; /* emerald-500 */
    color: #059669; /* emerald-600 */
    background-color: #f0fdf4;
  }

  /* ── Product Card Fade-Up Reveal Animation ── */
  @keyframes productCardFadeUp {
    0% {
      opacity: 0;
      transform: translateY(18px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .product-card-fade-up {
    animation: productCardFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    will-change: transform, opacity;
  }
</style>

<main id="supplier-content" data-active-category="<?php echo e($activeCategory); ?>" class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

  
  <div class="flex items-center gap-2 flex-wrap mb-6" id="supplier-tabs-row">
    <a
      href="<?php echo e(route('v2.suppliers.index', array_filter(['search' => $search]))); ?>"
      data-category="all"
      class="category-tab-btn <?php echo e($activeCategory === 'all' ? 'active' : ''); ?>"
    >
      All Categories
    </a>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cat->supplier_count > 0): ?>
        <a
          href="<?php echo e(route('v2.suppliers.index', array_filter(['category' => $cat->slug, 'search' => $search]))); ?>"
          data-category="<?php echo e($cat->slug); ?>"
          class="category-tab-btn <?php echo e($activeCategory === $cat->slug ? 'active' : ''); ?>"
        >
          <?php echo e($cat->name); ?>

        </a>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  <p class="text-sm text-gray-500 mb-5">
    <span class="font-semibold text-gray-900"><?php echo e(number_format($totalSuppliers)); ?></span>
    <?php echo e(Str::plural('supplier', $totalSuppliers)); ?> found
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
      for "<span class="text-emerald-600 font-medium"><?php echo e($search); ?></span>"
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeCategory && $activeCategory !== 'all'): ?>
      in <span class="text-emerald-600 font-medium"><?php echo e($categories->firstWhere('slug', $activeCategory)?->name); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </p>

  
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suppliers->isNotEmpty()): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" id="supplier-cards-grid">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="product-card-fade-up" style="animation-delay: <?php echo e($loop->index * 55); ?>ms">
          <?php echo $__env->make('frontend_new.components.supplier-card', ['supplier' => $supplier], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suppliers->hasPages()): ?>
      <div class="mt-10 flex justify-center">
        <?php echo e($suppliers->links('pagination::simple-tailwind')); ?>

      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php else: ?>
    <div class="col-span-full flex flex-col items-center justify-center py-24 text-center product-card-fade-up">
      <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <path d="M3 9l4.5-4.5 4.5 4.5 4.5-4.5 4.5 4.5"/>
      </svg>
      <p class="text-gray-400 font-medium">No suppliers found</p>
      <p class="text-sm text-gray-300 mt-1">Try a different category or search term</p>
      <a href="<?php echo e(route('v2.suppliers.index')); ?>" id="btn-clear-all-filters" class="mt-4 text-sm text-emerald-600 font-medium hover:underline">Clear filters →</a>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</main>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\suppliers\partials\_content.blade.php ENDPATH**/ ?>