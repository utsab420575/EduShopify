<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-6 h-14">

    <!-- Hamburger (mobile only) — not present in the static reference; added
         because the reference navbar has no mobile nav at all (nav links and
         search simply vanish below lg with nothing replacing them). -->
    <button type="button" onclick="fnOpenMobileMenu()" class="lg:hidden text-gray-500 hover:text-gray-800 shrink-0" aria-label="Open menu" aria-haspopup="true">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
    </button>

    <!-- Logo -->
    <a href="{{ route('v2.home') }}" class="flex items-center gap-1.5 shrink-0">
      <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="11" height="11" rx="2" fill="#10b981"/>
        <rect x="15" y="0" width="11" height="11" rx="2" fill="#10b981" opacity=".5"/>
        <rect x="0" y="15" width="11" height="11" rx="2" fill="#10b981" opacity=".5"/>
        <rect x="15" y="15" width="11" height="11" rx="2" fill="#10b981"/>
      </svg>
      <span class="font-bold text-gray-900 text-[17px]">Edu<span class="text-emerald-500">shopify</span></span>
      <span class="text-[9px] text-gray-400 leading-none mt-1 hidden sm:block">Connecting Education</span>
    </a>

    <!-- Search -->
    <div class="flex-1 max-w-md relative hidden sm:block">
      <input type="text" placeholder="Search suppliers, products, categories..." class="w-full border border-gray-200 rounded-lg text-sm px-3 py-2 pl-3 pr-9 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      <button class="absolute right-0 top-0 h-full px-3 bg-emerald-500 rounded-r-lg flex items-center">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
    </div>

    <!-- Nav links -->
    <nav class="hidden lg:flex items-center gap-5 ml-2">
      <a href="{{ route('v2.categories.index') }}" class="{{ request()->routeIs('v2.categories.*') ? 'nav-link-active' : 'nav-link' }}">Categories</a>
      <a href="{{ route('v2.rfqs.index') }}" class="{{ request()->routeIs('v2.rfqs.*') ? 'nav-link-active' : 'nav-link' }}">RFQ</a>
      <a href="{{ route('v2.suppliers.index') }}" class="{{ request()->routeIs('v2.suppliers.*') ? 'nav-link-active' : 'nav-link' }}">Suppliers</a>
      <a href="{{ route('v2.resources.index') }}" class="{{ request()->routeIs('v2.resources.*') ? 'nav-link-active' : 'nav-link' }}">Resources</a>
    </nav>

    <!-- Right icons -->
    <div class="ml-auto flex items-center gap-3">
      <button class="hidden sm:block text-gray-400 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </button>
      <button class="hidden sm:block text-gray-400 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </button>
      <button class="hidden sm:block text-gray-400 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </button>
      @guest
        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 ml-1">Sign In</a>
        <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg">Register</a>
      @else
        <a href="{{ route('dashboard') }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-lg">Dashboard</a>
      @endguest
    </div>
  </div>
</header>
