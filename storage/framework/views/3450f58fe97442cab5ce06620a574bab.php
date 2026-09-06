<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allSuppliers->isNotEmpty()): ?>
<style>
  /* ── Category Tab Pills (matching suppliers directory) ── */
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

  /* Inactive Tab Hover */
  .category-tab-btn:not(.active):hover {
    background-color: #f8fafc; /* slate-50 */
    color: #0f172a; /* slate-900 */
    border-color: #cbd5e1; /* slate-300 */
  }

  /* ── Product Card Fade-Up Reveal Animation ── */
  @keyframes productCardFadeUp {
    0% {
      opacity: 0;
      transform: translateY(16px);
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

<section id="all-suppliers-section" class="max-w-7xl mx-auto px-4 pb-12">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg text-gray-900">All Suppliers</h2>
    <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="text-emerald-600 text-sm font-medium hover:underline">View all →</a>
  </div>

  
  <div class="flex items-center gap-2 flex-wrap mb-5" id="all-suppliers-tabs-row">
    <button
      type="button"
      class="category-tab-btn <?php echo e(($activeCategory ?? 'all') === 'all' ? 'active' : ''); ?>"
      data-cat="all"
    >
      All Categories
    </button>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allSuppliersTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <button
        type="button"
        class="category-tab-btn <?php echo e(($activeCategory ?? 'all') === $tab->slug ? 'active' : ''); ?>"
        data-cat="<?php echo e($tab->slug); ?>"
      >
        <?php echo e($tab->name); ?>

      </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="filter-btn ml-auto hover:border-gray-400 transition-colors">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4"/></svg>
      Filter
    </a>
  </div>

  
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4" data-skeleton-target="#all-suppliers-grid">
    <div class="skel-block" style="height:190px;"></div>
    <div class="skel-block" style="height:190px;"></div>
    <div class="skel-block" style="height:190px;"></div>
    <div class="skel-block" style="height:190px;"></div>
  </div>

  
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 cards-hidden" id="all-suppliers-grid">
    <?php $initVisibleIndex = 0; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allSuppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $profileUrl = \Illuminate\Support\Facades\Route::has('v2.suppliers.show') ? route('v2.suppliers.show', $supplier->slug) : '#';
        $supplierCategories = $supplier->matched_categories?->all() ?? [];
        $categoriesString = implode(' ', $supplierCategories);
        $initMatch = (($activeCategory ?? 'all') === 'all') || in_array($activeCategory, $supplierCategories);
        $animDelay = $initMatch ? ($initVisibleIndex++ * 55) : 0;
      ?>
      <div
        class="supplier-item"
        data-categories="<?php echo e($categoriesString); ?>"
        style="<?php echo e($initMatch ? '' : 'display: none;'); ?>"
      >
        <a href="<?php echo e($profileUrl); ?>" class="group block h-full bg-white rounded-2xl border border-gray-200 hover:border-emerald-200 hover:shadow-md transition-all duration-200 p-4 flex flex-col justify-between product-card-fade-up" style="animation-delay: <?php echo e($animDelay); ?>ms">
          <div>
            
            <div class="flex items-start justify-between gap-2.5 mb-3">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-105 transition-transform">
                  <?php echo e(strtoupper(substr($supplier->display_name, 0, 1))); ?>

                </div>
                <div class="min-w-0">
                  <h3 class="font-bold text-sm text-gray-900 group-hover:text-emerald-700 transition-colors leading-tight truncate"><?php echo e($supplier->display_name); ?></h3>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->account?->supplierTypes?->isNotEmpty()): ?>
                    <p class="text-[11px] text-gray-500 truncate mt-0.5"><?php echo e($supplier->account->supplierTypes->pluck('name')->implode(' · ')); ?></p>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
              </div>
              <?php echo $__env->make('frontend_new.components.supplier-save-btn', ['supplier' => $supplier, 'class' => 'shrink-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            
            <div class="flex gap-1.5 mb-3 flex-wrap">
              <span class="badge-verified text-[10px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">✓ Verified</span>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->demo_founding): ?>
                <span class="badge-founding text-[10px] font-medium px-2 py-0.5 rounded-full">Founding</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->demo_ise): ?>
                <span class="badge-ise text-[10px] font-medium px-2 py-0.5 rounded-full">ISE Exhibitor</span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>

          <div>
            
            <div class="flex items-center justify-between text-[11px] text-gray-500 mb-2.5 pt-2.5 border-t border-gray-100">
              <span class="flex items-center gap-1 text-amber-600 font-medium"><span class="star">★</span> <?php echo e(number_format((float) $supplier->rating, 1)); ?> <span class="text-gray-400 font-normal">(<?php echo e($supplier->reviews_count ?? 0); ?>)</span></span>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($supplier->country): ?>
                <span class="flex items-center gap-1 text-gray-500"><?php echo e($supplier->country->flag_emoji); ?> <?php echo e($supplier->country->name); ?></span>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex items-center justify-between text-[11px]">
              <span class="text-gray-400">🛍 <?php echo e($supplier->product_count); ?>+ Products</span>
              <span class="text-emerald-600 font-semibold group-hover:underline">View Profile →</span>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>

  
  <div id="all-suppliers-empty" class="<?php echo e($initVisibleIndex === 0 ? '' : 'hidden'); ?> col-span-full py-16 text-center product-card-fade-up">
    <p class="text-gray-400 font-medium">No suppliers in this category</p>
    <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="mt-2 inline-block text-xs text-emerald-600 font-semibold hover:underline">Browse all suppliers in directory &rarr;</a>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const section = document.getElementById('all-suppliers-section');
  if (!section) return;

  const tabs = section.querySelectorAll('.category-tab-btn');
  const items = section.querySelectorAll('.supplier-item');
  const emptyState = document.getElementById('all-suppliers-empty');

  tabs.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const cat = this.dataset.cat || 'all';

      // Update active tab visual state (matching suppliers directory active/inactive)
      tabs.forEach(function (b) {
        b.classList.remove('active');
      });
      this.classList.add('active');

      // Staggered reveal animation on filtered items matching category
      let visibleIndex = 0;
      items.forEach(function (item) {
        const categories = (item.dataset.categories || '').split(/\s+/).filter(Boolean);
        const isMatch = (cat === 'all' || categories.includes(cat));

        if (isMatch) {
          item.style.display = '';
          const card = item.querySelector('a') || item;
          card.classList.remove('product-card-fade-up');
          void card.offsetWidth; // Force CSS animation reflow
          card.style.animationDelay = (visibleIndex * 55) + 'ms';
          card.classList.add('product-card-fade-up');
          visibleIndex++;
        } else {
          item.style.display = 'none';
        }
      });

      if (emptyState) {
        emptyState.classList.toggle('hidden', visibleIndex > 0);
      }

      // Sync URL parameter without page reload
      const newUrl = new URL(window.location.href);
      if (cat === 'all') {
        newUrl.searchParams.delete('category');
      } else {
        newUrl.searchParams.set('category', cat);
      }
      window.history.replaceState({ category: cat }, '', newUrl.toString());
    }, true);
  });
});
</script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views/frontend_new/home/partial/_all_suppliers.blade.php ENDPATH**/ ?>