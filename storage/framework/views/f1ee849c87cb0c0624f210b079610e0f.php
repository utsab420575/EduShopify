<style>
  /* ── RFQ Card Skeleton Loading State (Shimmer Effect) ── */
  .skeleton-box {
    position: relative;
    overflow: hidden;
    background-color: #f1f5f9; /* slate-100 */
    border-radius: 0.375rem;
  }
  .skeleton-box::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    transform: translateX(-100%);
    background-image: linear-gradient(
      90deg,
      rgba(255, 255, 255, 0) 0,
      rgba(255, 255, 255, 0.6) 40%,
      rgba(255, 255, 255, 0.9) 60%,
      rgba(255, 255, 255, 0) 100%
    );
    animation: rfqSkeletonShimmer 1.5s infinite ease-in-out;
    content: '';
  }
  @keyframes rfqSkeletonShimmer {
    100% {
      transform: translateX(100%);
    }
  }

  /* ── RFQ Card Fade-Up Reveal with Staggered Entrance Animation ── */
  @keyframes rfqCardFadeUp {
    0% {
      opacity: 0;
      transform: translateY(20px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .rfq-card-fade-up {
    animation: rfqCardFadeUp 0.38s cubic-bezier(0.16, 1, 0.3, 1) both;
    will-change: transform, opacity;
  }
</style>


<?php echo $__env->make('frontend_new.rfqs.partials._skeleton', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="flex flex-col gap-4" id="rfq-list" style="display: none;">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rfq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php echo $__env->make('frontend_new.rfqs.partials._card', ['rfq' => $rfq], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="text-center py-20 rfq-card-fade-up">
      <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </div>
      <p class="font-semibold text-gray-900 mb-1">No open RFQs right now</p>
      <p class="text-sm text-gray-500">Check back soon for new procurement opportunities.</p>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<div id="empty-state" class="hidden text-center py-20 rfq-card-fade-up">
  <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
  </div>
  <p class="font-semibold text-gray-900 mb-1">No RFQs found</p>
  <p class="text-sm text-gray-500">Try a different search term.</p>
</div>


<div id="rfq-pagination" style="display: none;">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunities->hasPages()): ?>
    <div class="flex items-center justify-between gap-4 mt-8">
      <div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunities->onFirstPage()): ?>
          <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Previous</span>
        <?php else: ?>
          <a href="<?php echo e($opportunities->previousPageUrl()); ?>" class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50 transition-colors">Previous</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
      <p class="text-sm text-gray-500">Page <?php echo e($opportunities->currentPage()); ?> of <?php echo e($opportunities->lastPage()); ?></p>
      <div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opportunities->hasMorePages()): ?>
          <a href="<?php echo e($opportunities->nextPageUrl()); ?>" class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50 transition-colors">Next</a>
        <?php else: ?>
          <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Next</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
  const skeletonList = document.getElementById('rfq-skeleton-list');
  const realList     = document.getElementById('rfq-list');
  const pagination   = document.getElementById('rfq-pagination');
  const searchInput  = document.getElementById('rfq-search');
  const emptyState   = document.getElementById('empty-state');

  // ── Orchestrate: Page Refresh → Skeleton Loading Animation → Staggered Fade-Up Reveal ──
  function revealCards() {
    if (!skeletonList || !realList) return;

    // Show skeleton briefly (350ms) for smooth perception
    setTimeout(function () {
      skeletonList.style.transition = 'opacity 0.2s ease-out';
      skeletonList.style.opacity = '0';

      setTimeout(function () {
        skeletonList.style.display = 'none';
        realList.style.display = 'flex';
        if (pagination) pagination.style.display = '';

        // Trigger staggered animation on each card
        const cards = realList.querySelectorAll('.rfq-card');
        cards.forEach(function (card, index) {
          card.style.animationDelay = (index * 60) + 'ms';
          card.classList.add('rfq-card-fade-up');
        });
      }, 150);
    }, 350);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', revealCards);
  } else {
    revealCards();
  }

  // ── Live Search with Staggered Entrance on Matching Results ──
  if (searchInput) {
    let searchDebounce = null;
    searchInput.addEventListener('input', function () {
      const q = this.value.toLowerCase().trim();
      const cards = realList ? realList.querySelectorAll('.rfq-card') : [];

      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(function () {
        let matchIndex = 0;

        cards.forEach(function (card) {
          const match = card.textContent.toLowerCase().includes(q);
          if (match) {
            card.style.display = '';
            // Reset and trigger staggered entrance for matching results
            card.classList.remove('rfq-card-fade-up');
            void card.offsetWidth;
            card.style.animationDelay = (matchIndex * 50) + 'ms';
            card.classList.add('rfq-card-fade-up');
            matchIndex++;
          } else {
            card.style.display = 'none';
          }
        });

        if (emptyState) {
          emptyState.classList.toggle('hidden', matchIndex > 0);
        }
      }, 150);
    });
  }
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\rfqs\partials\_content.blade.php ENDPATH**/ ?>