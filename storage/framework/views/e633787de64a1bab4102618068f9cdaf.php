
<section class="bg-gray-900 py-12 px-4">
  <div class="max-w-7xl mx-auto">
    <p class="text-emerald-400 text-xs font-semibold uppercase tracking-widest mb-2">Insights &amp; Knowledge</p>
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight mb-2">Education Insights &amp; Blog</h1>
    <p class="text-gray-400 text-base mb-8">Articles, procurement frameworks, and classroom innovation from verified suppliers and institutions</p>

    
    <div class="relative max-w-xl" id="blog-search-wrap">
      <div class="flex items-center bg-white rounded-xl shadow-sm overflow-hidden border border-transparent focus-within:border-emerald-500 transition-colors">
        <span class="pl-4 text-gray-400 flex-shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </span>
        <input
          id="blog-search-input"
          type="text"
          name="search"
          placeholder="Search articles, procurement guides, topics..."
          autocomplete="off"
          value="<?php echo e($search); ?>"
          class="flex-1 px-4 py-3.5 text-sm text-gray-800 placeholder-gray-400 bg-transparent focus:outline-none"
        />
        <button
          id="blog-search-clear"
          type="button"
          class="pr-4 text-gray-400 hover:text-gray-600 transition-colors <?php echo e($search ? '' : 'hidden'); ?>"
          aria-label="Clear search"
          title="Clear search"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 6 6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</section>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\blogs\partials\_hero.blade.php ENDPATH**/ ?>