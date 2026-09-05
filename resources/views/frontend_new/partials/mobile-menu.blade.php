<!-- Not present in the static reference (see navbar.blade.php comment) — a
     minimal drawer so the site is usable on mobile. -->

<div id="fn-mobile-overlay" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden" onclick="fnCloseMobileMenu()"></div>

<div id="fn-mobile-menu" class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-xl transform -translate-x-full transition-transform duration-200 lg:hidden flex flex-col">
  <div class="flex items-center justify-between px-4 h-14 border-b border-gray-200">
    <a href="{{ route('v2.home') }}" class="flex items-center gap-1.5">
      <svg width="22" height="22" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" y="0" width="11" height="11" rx="2" fill="#10b981"/>
        <rect x="15" y="0" width="11" height="11" rx="2" fill="#10b981" opacity=".5"/>
        <rect x="0" y="15" width="11" height="11" rx="2" fill="#10b981" opacity=".5"/>
        <rect x="15" y="15" width="11" height="11" rx="2" fill="#10b981"/>
      </svg>
      <span class="font-bold text-gray-900 text-[17px]">Edu<span class="text-emerald-500">shopify</span></span>
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
    <a href="#" class="px-3 py-2.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Categories</a>
    <a href="{{ Route::has('v2.rfqs.index') ? route('v2.rfqs.index') : '#' }}" class="px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('v2.rfqs.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50' }}">RFQ</a>
    <a href="#" class="px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('v2.suppliers.*') ? 'text-gray-900 bg-gray-100' : 'text-gray-700 hover:bg-gray-50' }}">Suppliers</a>
    <a href="#" class="px-3 py-2.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Resources</a>
  </nav>

  <div class="px-4 py-4 border-t border-gray-100 space-y-2">
    @guest
      <a href="{{ route('login') }}" class="block text-center py-2.5 text-sm font-medium text-gray-700">Sign In</a>
      <a href="{{ route('register') }}" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-lg">Register</a>
    @else
      <a href="{{ route('dashboard') }}" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-lg">Dashboard</a>
    @endguest
  </div>
</div>
