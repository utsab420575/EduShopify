<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-4 lg:gap-7 py-3 min-h-[64px]">

    <!-- Hamburger (mobile only) -->
    <button type="button" onclick="fnOpenMobileMenu()" class="lg:hidden text-gray-500 hover:text-gray-800 p-1.5 rounded-lg hover:bg-gray-100 shrink-0 transition" aria-label="Open menu" aria-haspopup="true">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
    </button>

    <!-- Logo -->
    <a href="<?php echo e(route('v2.home')); ?>" class="flex items-center shrink-0">
      <img src="<?php echo e(asset('storage/System_Files/Logo/edushopifyLogo.png')); ?>" alt="Edushopify" class="h-14 w-auto object-contain" />
    </a>

    <!-- Search -->
    <div class="flex-1 max-w-md relative hidden sm:block">
      <input type="text" placeholder="Search suppliers, products, categories..." class="w-full border border-gray-200 bg-gray-50/60 rounded-lg text-sm px-3.5 py-2 pr-10 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition" />
      <button class="absolute right-1 top-1 bottom-1 px-2.5 bg-emerald-500 hover:bg-emerald-600 transition text-white rounded-md flex items-center justify-center" aria-label="Search">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
    </div>

    <!-- Nav links -->
    <nav class="hidden lg:flex items-center gap-5 ml-1">
      <a href="<?php echo e(route('v2.categories.index')); ?>" class="<?php echo e(request()->routeIs('v2.categories.*') ? 'nav-link-active' : 'nav-link'); ?>">Categories</a>
      <a href="<?php echo e(route('v2.rfqs.index')); ?>" class="<?php echo e(request()->routeIs('v2.rfqs.*') ? 'nav-link-active' : 'nav-link'); ?>">RFQ</a>
      <a href="<?php echo e(route('v2.suppliers.index')); ?>" class="<?php echo e(request()->routeIs('v2.suppliers.*') ? 'nav-link-active' : 'nav-link'); ?>">Suppliers</a>
      <a href="<?php echo e(route('v2.blogs.index')); ?>" class="<?php echo e(request()->routeIs('v2.blogs.*') ? 'nav-link-active' : 'nav-link'); ?>">Blog</a>
      <a href="<?php echo e(route('v2.resources.index')); ?>" class="<?php echo e(request()->routeIs('v2.resources.*') ? 'nav-link-active' : 'nav-link'); ?>">Resources</a>
    </nav>

    <!-- Right icons & actions -->
    <div class="ml-auto flex items-center gap-2 sm:gap-3 lg:gap-4">
      <div class="flex items-center gap-1 sm:gap-1.5">
        <button type="button" class="hidden sm:flex items-center justify-center p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition" aria-label="Favorites">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
        <button type="button" class="hidden sm:flex items-center justify-center p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition" aria-label="Messages">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </button>
        <button type="button" class="hidden sm:flex items-center justify-center p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition" aria-label="Notifications">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </button>
      </div>

      <div class="flex items-center gap-2 sm:gap-3 pl-1 sm:pl-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
          <a href="<?php echo e(route('login', ['redirect' => url()->current()])); ?>" class="text-sm font-medium text-gray-700 hover:text-gray-900 px-2 py-1.5 transition">Sign In</a>
          <a href="<?php echo e(route('register', ['redirect' => url()->current()])); ?>" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg transition shadow-sm">Register</a>
        <?php else: ?>
          <a href="<?php echo e(route('dashboard')); ?>" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg transition shadow-sm">Dashboard</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
  </div>
</header>
<?php /**PATH C:\laragon\www\edushopify\resources\views/frontend_new/partials/navbar.blade.php ENDPATH**/ ?>