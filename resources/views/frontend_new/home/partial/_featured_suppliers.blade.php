@if($featuredSuppliers->isNotEmpty())
<style>
  #featured-suppliers-grid {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
    padding-top: 6px;
    padding-bottom: 12px;
  }
  #featured-suppliers-grid::-webkit-scrollbar {
    display: none;
  }
  .featured-supplier-item {
    flex: 0 0 85%;
    width: 85%;
    min-width: 260px;
    max-width: 320px;
    scroll-snap-align: start;
    scroll-snap-stop: always;
  }
  @media (min-width: 640px) {
    .featured-supplier-item {
      flex: 0 0 calc(50% - 10px);
      width: calc(50% - 10px);
      min-width: calc(50% - 10px);
      max-width: none;
    }
  }
  @media (min-width: 1024px) {
    .featured-supplier-item {
      flex: 0 0 calc(25% - 15px);
      width: calc(25% - 15px);
      min-width: calc(25% - 15px);
      max-width: none;
    }
  }
</style>

<section class="max-w-7xl mx-auto px-4 py-10" id="featured-suppliers-section">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-bold text-lg text-gray-900">Featured Suppliers</h2>
    <div class="flex items-center gap-2.5">
      <a href="{{ route('v2.suppliers.index') }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium hover:underline cursor-pointer">See all</a>
      <div class="flex items-center gap-1.5">
        <button
          type="button"
          id="featured-suppliers-prev"
          aria-label="Previous suppliers"
          class="border border-gray-200 rounded-md w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors opacity-30 cursor-not-allowed"
          disabled
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button
          type="button"
          id="featured-suppliers-next"
          aria-label="Next suppliers"
          class="border border-gray-200 rounded-md w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>
  </div>

  {{-- Skeleton shown immediately; JS reveals #featured-suppliers-grid
       (real cards, already server-rendered) a short moment later --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" data-skeleton-target="#featured-suppliers-grid">
    <div class="skel-block" style="height:320px;"></div>
    <div class="skel-block" style="height:320px;"></div>
    <div class="skel-block" style="height:320px;"></div>
    <div class="skel-block" style="height:320px;"></div>
  </div>

  {{-- Real slider track --}}
  <div class="relative">
    <div
      class="cards-hidden"
      id="featured-suppliers-grid"
    >
      @foreach($featuredSuppliers as $supplier)
        <div class="featured-supplier-item shrink-0 product-card-fade-up" style="animation-delay: {{ $loop->index * 45 }}ms">
          @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
        </div>
      @endforeach
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const slider = document.getElementById('featured-suppliers-grid');
  const prevBtn = document.getElementById('featured-suppliers-prev');
  const nextBtn = document.getElementById('featured-suppliers-next');

  if (!slider || !prevBtn || !nextBtn) return;

  function getStep() {
    return slider.clientWidth || 320;
  }

  function setButtonState(btn, disabled) {
    btn.disabled = disabled;
    if (disabled) {
      btn.classList.add('opacity-30', 'cursor-not-allowed');
      btn.classList.remove('hover:bg-gray-100', 'hover:text-gray-900');
    } else {
      btn.classList.remove('opacity-30', 'cursor-not-allowed');
      btn.classList.add('hover:bg-gray-100', 'hover:text-gray-900');
    }
  }

  function updateButtons() {
    if (slider.classList.contains('cards-hidden') || slider.offsetWidth === 0) {
      return;
    }
    const maxScroll = Math.max(0, slider.scrollWidth - slider.clientWidth);
    if (maxScroll <= 8) {
      setButtonState(prevBtn, true);
      setButtonState(nextBtn, true);
      return;
    }

    const atStart = slider.scrollLeft <= 8;
    const atEnd = slider.scrollLeft >= maxScroll - 8;

    setButtonState(prevBtn, atStart);
    setButtonState(nextBtn, atEnd);
  }

  prevBtn.addEventListener('click', function (e) {
    e.preventDefault();
    slider.scrollBy({ left: -getStep(), behavior: 'smooth' });
  });

  nextBtn.addEventListener('click', function (e) {
    e.preventDefault();
    slider.scrollBy({ left: getStep(), behavior: 'smooth' });
  });

  slider.addEventListener('scroll', updateButtons, { passive: true });
  window.addEventListener('resize', updateButtons);

  // When frontend_new.js removes 'cards-hidden', initialize button states
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      if (m.attributeName === 'class' && !slider.classList.contains('cards-hidden')) {
        updateButtons();
      }
    });
  });
  observer.observe(slider, { attributes: true });

  // Initial check after load and after skeleton animation
  setTimeout(updateButtons, 650);
});
</script>
@endif
