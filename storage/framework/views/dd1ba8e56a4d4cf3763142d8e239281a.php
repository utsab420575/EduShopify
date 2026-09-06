
<section class="relative bg-gray-950 text-white py-14 px-4 overflow-hidden">
  
  <img
    src="<?php echo e(asset('storage/System_Files/Resources/background.jpg')); ?>"
    alt="Education Resources Background"
    class="absolute inset-0 w-full h-full object-cover object-center z-0"
  />

  
  <div class="absolute inset-0 z-0" style="background: linear-gradient(180deg, rgba(8, 12, 22, 0.65) 0%, rgba(10, 16, 28, 0.70) 100%);"></div>

  <div class="relative z-10 max-w-7xl mx-auto">
    
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-4">
      <a href="<?php echo e(route('v2.home')); ?>" class="hover:text-emerald-400 transition-colors">Home</a>
      <span>&rsaquo;</span>
      <span class="text-gray-200">Resources</span>
    </nav>

    
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-3">
      Education <span class="text-emerald-400">Resources</span> Center
    </h1>
    <p class="text-gray-300 text-sm sm:text-base max-w-3xl leading-relaxed mb-8">
      Expert guides, market insights, templates, case studies and the latest news to help you make smarter education procurement decisions.
    </p>

    
    <form action="#" method="GET" onsubmit="event.preventDefault();" class="max-w-2xl mb-4">
      <div class="flex items-center bg-white rounded-xl shadow-lg p-1.5 border border-white/20 focus-within:ring-2 focus-within:ring-emerald-400 transition-all">
        <span class="pl-3.5 text-gray-400 shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </span>
        <input
          type="text"
          id="resources-search-input"
          placeholder="Search guides, insights, templates, products, RFQs, exhibitions..."
          class="flex-1 px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 bg-transparent focus:outline-none"
        />
        <button
          type="button"
          class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors shadow-sm shrink-0"
        >
          Search
        </button>
      </div>
    </form>

    
    <div class="flex items-center gap-2 flex-wrap text-xs text-gray-400 mb-10">
      <span class="text-gray-300 font-medium">Popular Searches:</span>
      <a href="#featured-guides" class="bg-white/10 hover:bg-white/20 border border-white/15 text-gray-200 px-3 py-1 rounded-full backdrop-blur-sm transition-all">STEM Lab Guide</a>
      <a href="#featured-guides" class="bg-white/10 hover:bg-white/20 border border-white/15 text-gray-200 px-3 py-1 rounded-full backdrop-blur-sm transition-all">Interactive Display</a>
      <a href="#rfq-templates" class="bg-white/10 hover:bg-white/20 border border-white/15 text-gray-200 px-3 py-1 rounded-full backdrop-blur-sm transition-all">RFQ Template</a>
      <a href="#featured-guides" class="bg-white/10 hover:bg-white/20 border border-white/15 text-gray-200 px-3 py-1 rounded-full backdrop-blur-sm transition-all">AI in Education</a>
      <a href="#featured-guides" class="bg-white/10 hover:bg-white/20 border border-white/15 text-gray-200 px-3 py-1 rounded-full backdrop-blur-sm transition-all">School Furniture</a>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 pt-6">
      
      <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
          </svg>
        </div>
        <div>
          <p class="text-xl font-extrabold text-white leading-tight">1,250+</p>
          <p class="text-xs text-gray-400">Guides &amp; Articles</p>
        </div>
      </div>

      
      <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
          </svg>
        </div>
        <div>
          <p class="text-xl font-extrabold text-white leading-tight">350+</p>
          <p class="text-xs text-gray-400">Templates &amp; Checklists</p>
        </div>
      </div>

      
      <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <div>
          <p class="text-xl font-extrabold text-white leading-tight">180+</p>
          <p class="text-xs text-gray-400">Case Studies</p>
        </div>
      </div>

      
      <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <div>
          <p class="text-xl font-extrabold text-white leading-tight">25+</p>
          <p class="text-xs text-gray-400">Exhibitions Covered</p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\resources\partials\_hero.blade.php ENDPATH**/ ?>