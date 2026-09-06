<!-- Not present in the static reference (see navbar.blade.php comment) — a
     minimal drawer so the site is usable on mobile. -->

<div id="fn-mobile-overlay" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden" onclick="fnCloseMobileMenu()"></div>

<div id="fn-mobile-menu" class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-xl transform -translate-x-full transition-transform duration-200 lg:hidden flex flex-col">
  <div class="flex items-center justify-between px-4 h-14 border-b border-gray-200">
    <a href="<?php echo e(route('v2.home')); ?>" class="flex items-center">
      <img src="<?php echo e(asset('storage/System_Files/Logo/edushopifyLogo.png')); ?>" alt="Edushopify" class="h-8 w-auto object-contain" />
    </a>
    <button type="button" onclick="fnCloseMobileMenu()" class="text-gray-500 hover:text-gray-800" aria-label="Close menu">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12"/></svg>
    </button>
  </div>

  <div class="px-4 py-3 border-b border-gray-100">
    <div class="relative">
      <input type="text" placeholder="Search suppliers, products, categories..." class="w-full border border-gray-200 rounded-lg text-sm px-3 py-2 pr-9 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      <button class="absolute right-0 top-0 h-full px-3 bg-emerald-500 rounded-r-lg flex items-center">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
    </div>
  </div>

  <nav class="flex flex-col px-2 py-3 gap-1 flex-1 overflow-y-auto">
    <a href="<?php echo e(route('v2.categories.index')); ?>" class="px-3 py-2.5 rounded-md text-sm font-medium <?php echo e(request()->routeIs('v2.categories.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50'); ?>">Categories</a>
    <a href="<?php echo e(route('v2.rfqs.index')); ?>" class="px-3 py-2.5 rounded-md text-sm font-medium <?php echo e(request()->routeIs('v2.rfqs.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50'); ?>">RFQ</a>
    <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="px-3 py-2.5 rounded-md text-sm font-medium <?php echo e(request()->routeIs('v2.suppliers.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50'); ?>">Suppliers</a>
    <a href="<?php echo e(route('v2.blogs.index')); ?>" class="px-3 py-2.5 rounded-md text-sm font-medium <?php echo e(request()->routeIs('v2.blogs.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50'); ?>">Blog</a>
    <a href="<?php echo e(route('v2.resources.index')); ?>" class="px-3 py-2.5 rounded-md text-sm font-medium <?php echo e(request()->routeIs('v2.resources.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50'); ?>">Resources</a>
  </nav>

  <div class="px-4 py-4 border-t border-gray-100 space-y-2">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
      <a href="<?php echo e(route('login')); ?>" class="block text-center py-2.5 text-sm font-medium text-gray-700">Sign In</a>
      <a href="<?php echo e(route('register')); ?>" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-lg">Register</a>
    <?php else: ?>
      <a href="<?php echo e(route('dashboard')); ?>" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-lg">Dashboard</a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend_new\partials\mobile-menu.blade.php ENDPATH**/ ?>