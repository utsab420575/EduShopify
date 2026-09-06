<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-4 lg:gap-7 py-3 min-h-[64px]">

    <!-- Hamburger (mobile only) -->
    <button type="button" onclick="fnOpenMobileMenu()" class="lg:hidden text-gray-500 hover:text-gray-800 p-1.5 rounded-lg hover:bg-gray-100 shrink-0 transition" aria-label="Open menu" aria-haspopup="true">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
    </button>

    <!-- Logo -->
    <a href="{{ route('v2.home') }}" class="flex items-center shrink-0">
      <img src="{{ asset('storage/System_Files/Logo/edushopifyLogo.png') }}" alt="Edushopify" class="h-14 w-auto object-contain" />
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
      <a href="{{ route('v2.categories.index') }}" class="{{ request()->routeIs('v2.categories.*') ? 'nav-link-active' : 'nav-link' }}">Categories</a>
      <a href="{{ route('v2.rfqs.index') }}" class="{{ request()->routeIs('v2.rfqs.*') ? 'nav-link-active' : 'nav-link' }}">RFQ</a>
      <a href="{{ route('v2.suppliers.index') }}" class="{{ request()->routeIs('v2.suppliers.*') ? 'nav-link-active' : 'nav-link' }}">Suppliers</a>
      <a href="{{ route('v2.blogs.index') }}" class="{{ request()->routeIs('v2.blogs.*') ? 'nav-link-active' : 'nav-link' }}">Blog</a>
      <a href="{{ route('v2.resources.index') }}" class="{{ request()->routeIs('v2.resources.*') ? 'nav-link-active' : 'nav-link' }}">Resources</a>
    </nav>

    <!-- Right icons & actions -->
    <div class="ml-auto flex items-center gap-2 sm:gap-3 lg:gap-4">
      <div class="flex items-center gap-1 sm:gap-1.5">
        <a href="{{ route('v2.compare.index') }}"
           class="group relative inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-emerald-700 hover:bg-emerald-50/80 transition-all duration-200 ease-in-out"
           aria-label="Product comparison" title="View product comparison">
          <i class="fa-solid fa-arrow-right-arrow-left text-xs text-gray-400 group-hover:text-emerald-600 transition-transform duration-200 group-hover:rotate-180"></i>
          <span class="hidden xl:inline">Compare</span>
          <span data-compare-badge class="hidden inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow-xs transition-transform duration-200 group-hover:scale-110">0</span>
        </a>
      </div>

      <div class="flex items-center gap-2 sm:gap-3 pl-1 sm:pl-2">
        @guest
          <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 px-2 py-1.5 transition">Sign In</a>
          <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg transition shadow-sm">Register</a>
        @else
          <a href="{{ route('dashboard') }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg transition shadow-sm">Dashboard</a>
        @endguest
      </div>
    </div>
  </div>
</header>
