<footer class="bg-white pt-14 pb-8 px-4 border-t border-gray-200">
  <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">

    <!-- Brand column -->
    <div class="md:col-span-1">
      <div class="flex items-center gap-2.5 mb-4">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="0"  y="0"  width="9" height="9" rx="2" fill="#10b981"/>
          <rect x="13" y="0"  width="9" height="9" rx="2" fill="#10b981"/>
          <rect x="26" y="0"  width="9" height="9" rx="2" fill="#6ee7b7"/>
          <rect x="0"  y="13" width="9" height="9" rx="2" fill="#10b981"/>
          <rect x="13" y="13" width="9" height="9" rx="2" fill="#6ee7b7"/>
          <rect x="26" y="13" width="9" height="9" rx="2" fill="#6ee7b7"/>
          <rect x="0"  y="26" width="9" height="9" rx="2" fill="#6ee7b7"/>
          <rect x="13" y="26" width="9" height="9" rx="2" fill="#10b981"/>
          <rect x="26" y="26" width="9" height="9" rx="2" fill="#10b981"/>
        </svg>
        <div class="flex flex-col leading-tight">
          <span class="font-bold text-gray-900 text-[17px] tracking-tight">Edu<span class="text-emerald-500">shopify</span></span>
          <span class="text-[11px] text-gray-400 font-normal">Connecting Education</span>
        </div>
      </div>
      <p class="text-sm text-gray-500 leading-relaxed max-w-[260px]">The B2B education procurement marketplace connecting institutional buyers with verified suppliers worldwide.</p>
    </div>

    <!-- Marketplace -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Marketplace</p>
      <ul class="space-y-4">
        <li><a href="<?php echo e(route('v2.suppliers.index')); ?>" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Browse Suppliers</a></li>
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Products</a></li>
        <li><a href="<?php echo e(route('v2.categories.index')); ?>" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Categories</a></li>
        <li><a href="<?php echo e(route('v2.rfqs.index')); ?>" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Request Quote</a></li>
      </ul>
    </div>

    <!-- Company -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Company</p>
      <ul class="space-y-4">
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">About Us</a></li>
        <li><a href="<?php echo e(route('v2.resources.index')); ?>" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Blog</a></li>
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Contact</a></li>
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Careers</a></li>
      </ul>
    </div>

    <!-- Support -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Support</p>
      <ul class="space-y-4">
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Help Center</a></li>
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Pricing</a></li>
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Terms of Service</a></li>
        <li><a href="#" class="text-sm text-gray-500 hover:text-gray-900 transition-colors">Privacy Policy</a></li>
      </ul>
    </div>
  </div>

  <div class="max-w-7xl mx-auto border-t border-gray-200 pt-6 flex items-center justify-between">
    <p class="text-sm text-gray-400">© <?php echo e(date('Y')); ?> Edushopify. All rights reserved.</p>
    <div class="flex gap-6 text-sm text-gray-400">
      <a href="#" class="hover:text-gray-700 transition-colors">Terms</a>
      <a href="#" class="hover:text-gray-700 transition-colors">Privacy</a>
    </div>
  </div>
</footer>
<?php /**PATH C:\laragon\www\edushopify\resources\views/frontend_new/partials/footer.blade.php ENDPATH**/ ?>