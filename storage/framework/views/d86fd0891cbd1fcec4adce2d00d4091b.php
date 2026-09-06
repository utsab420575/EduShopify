<section class="relative h-[420px] overflow-hidden">
  <img src="<?php echo e(asset('storage/System_Files/HeroSection/hero_image.jpg')); ?>" alt="Global Education Suppliers" class="absolute inset-0 w-full h-full object-cover object-center" />
  <div class="hero-overlay absolute inset-0"></div>
  <div class="relative max-w-7xl mx-auto px-6 h-full flex flex-col justify-center hero-fade-up">
    <h1 class="text-white font-extrabold text-3xl sm:text-4xl md:text-5xl lg:text-[46px] leading-tight max-w-2xl">
      Global Suppliers for<br />Education. All in One Place.
    </h1>
    <p class="text-white/85 mt-3 text-sm sm:text-base font-normal max-w-md">Connect with verified education suppliers worldwide</p>
    <div class="flex flex-wrap items-center gap-3 mt-7">
      <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="hero-btn-find inline-flex items-center justify-center bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-lg text-sm hover:bg-emerald-600 hover:text-white focus:bg-emerald-600 focus:text-white focus:outline-none focus:ring-2 focus:ring-white/80 transition-all duration-200 shadow-sm">
        Find Suppliers
      </a>
      <a href="<?php echo e(route('v2.handoff.post-rfq')); ?>" class="inline-flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 active:bg-emerald-700 transition-all duration-200 shadow-sm">
        Post an RFQ
      </a>
    </div>
  </div>
</section>
<?php /**PATH C:\laragon\www\edushopify\resources\views/frontend_new/home/partial/_hero.blade.php ENDPATH**/ ?>