# EduShopify — Public Frontend Complete Implementation Specification

> **Version:** 2.0 — New Client Requirements Master Document
> **Stack:** Laravel Blade · Tailwind CSS · Vanilla JavaScript · No Alpine · No Livewire · No React/Vue

## Source Priority

| Concern | Source |
|---|---|
| UI design, layouts, colors, components, HTML | NEW: `edushopify-frontend-spec.md`, `edushopify-product-detail-spec.md`, `edushopify-supplier-detail-spec.md`, `edushopify-rfq-detail-spec.md` |
| Folder organization, naming conventions | OLD: `frontend_workflow.md`, `design_frontend.md` (architecture reference only) |
| Database fields, models | Existing schema |

**NEW CLIENT REQUIREMENT ALWAYS WINS over old design files.**

---

## Table of Contents

1. Design System
2. Technology Stack & Asset Rules
3. Folder & File Structure
4. Routes
5. Controllers
6. Public Data Eligibility Rules
7. Global Layout: app.blade.php
8. Scope Boundaries
9. Navbar
10. Mobile Menu Drawer
11. Footer
12. Reusable Partials & Components
13. Home Page
14. Categories Page
15. Suppliers Listing Page
16. Supplier Profile / Storefront Page
17. Product Detail Page
18. RFQ Pages
19. Resources / Blog Pages
20. About Page
21. Contact Page
22. Pricing Page
23. JavaScript Patterns
24. Form Security
25. SEO & Accessibility
26. Testing Checklist
27. Implementation Order
28. Quick Class Reference

---

## 1. Design System

### 1.1 Color Palette

```
Primary Green   #10b981  emerald-500   CTAs, active nav, links, badges
Primary Dark    #059669  emerald-600   Hover states
Primary Light   #6ee7b7  emerald-300   Logo accent, soft fills
Accent Amber    #f59e0b               Star ratings only
Text Primary    #111827  gray-900      Headings
Text Secondary  #374151  gray-700      Body copy
Text Muted      #6b7280  gray-500      Labels, captions
Border          #e5e7eb  gray-200      All borders, dividers
Surface         #f3f4f6  gray-100      Page background, inactive tags
White           #ffffff               Cards, navbar, footer
```

**Gradient rule:** No gradients on UI surfaces. The ONLY allowed gradient is the dark left-to-right hero overlay:
`background: linear-gradient(90deg, rgba(0,0,0,.72) 0%, rgba(0,0,0,.3) 60%, rgba(0,0,0,.05) 100%)`
All UI elements must be flat — borders and subtle shadows only.

### 1.2 Typography

```
Font:    Inter (Google Fonts — 300,400,500,600,700,800)
Base:    14px / text-sm
Body:    16px / text-base / font-normal / text-gray-700 / leading-relaxed

h1:  text-4xl lg:text-5xl / font-extrabold / text-gray-900
h2:  text-2xl lg:text-3xl / font-bold      / text-gray-900
h3:  text-xl               / font-semibold  / text-gray-900
h4:  text-base             / font-semibold  / text-gray-900

Section label: text-xs / font-semibold / uppercase / tracking-widest / text-emerald-600
Caption:       text-xs / text-gray-400
```

### 1.3 Spacing & Sizing

```
Section padding:  py-12 lg:py-16
Container:        max-w-7xl mx-auto px-4 sm:px-6
Card padding:     p-4 lg:p-5
Card gap:         gap-4
Input height:     h-10 (py-2 px-3)
Button height:    h-9 to h-10
```

### 1.4 Border Radius Rules

```
Cards:              rounded-lg   (8px)
Buttons:            rounded-md   (6px)
Inputs:             rounded-md   (6px)
Badges/pills:       rounded      (4px)  — NOT rounded-full
Tags:               rounded-md   (6px)
Images in cards:    rounded-md   (6px)
Avatars (initials): rounded      (4px)
Table rows:         no radius
Modals:             rounded-lg   (8px)
```

### 1.5 Shadow Scale

```
Card default: none (border only)
Card hover:   shadow-md
Navbar:       border-b border-gray-200
Dropdown:     shadow-lg border border-gray-100
Modal:        shadow-xl
```

### 1.6 Custom CSS Tokens

Add to `<style>` block in `app.blade.php` or a frontend CSS file:

```css
* { font-family: 'Inter', sans-serif; }

.badge-verified { background:#e8f5ee; color:#1a7f45; border:1px solid #b6e0c6; }
.badge-founding { background:#fff8e6; color:#a16207; border:1px solid #fde68a; }
.badge-ise      { background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; }
.badge-new      { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.badge-featured { background:#fef9c3; color:#854d0e; border:1px solid #fef08a; }

.tag-active   { background:#111827; color:#fff; }
.tag-inactive { background:#f3f4f6; color:#374151; }
.star         { color:#f59e0b; }

.hero-overlay {
  background: linear-gradient(90deg, rgba(0,0,0,.72) 0%, rgba(0,0,0,.3) 60%, rgba(0,0,0,.05) 100%);
}

.form-input {
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 14px;
  width: 100%;
  outline: none;
  transition: box-shadow .15s;
}
.form-input:focus {
  box-shadow: 0 0 0 2px #a7f3d0;
  border-color: #10b981;
}

#mobile-menu { transition: transform 0.3s ease; }
```

---

## 2. Technology Stack & Asset Rules

```
Language:  PHP / Laravel Blade
CSS:       Tailwind CSS (CDN script or Vite pipeline)
JS:        Vanilla JavaScript — public/js/app.js
Fonts:     Google Fonts — Inter
Icons:     Inline SVG (as shown in spec)
NO:        Alpine.js, Livewire, React, Vue, jQuery
```

Load in `<head>`: Tailwind CDN → Google Fonts → custom style block.
Load `<script src="{{ asset('js/app.js') }}">` before `</body>`.

---

## 3. Folder & File Structure

All public frontend views live under `resources/views/frontend/`. The backend dashboards already exist under `resources/views/backend/` and must not be modified.

```
resources/views/frontend/
│
├── layouts/
│   └── app.blade.php              ← Public frontend layout (navbar + footer + mobile menu)
│
├── partials/
│   ├── navbar.blade.php
│   ├── footer.blade.php
│   ├── mobile-menu.blade.php
│   ├── alerts.blade.php
│   ├── breadcrumb.blade.php
│   ├── pagination.blade.php
│   ├── supplier-card.blade.php
│   └── supplier-card-featured.blade.php
│
├── home/
│   ├── index.blade.php            ← Homepage composition file (@include sections only)
│   └── sections/
│       ├── _hero.blade.php
│       ├── _stats_bar.blade.php
│       ├── _featured_suppliers.blade.php
│       ├── _all_suppliers.blade.php
│       ├── _why_choose_edushopify.blade.php
│       └── _events.blade.php
│
├── pages/
│   ├── about.blade.php
│   ├── contact.blade.php
│   └── pricing.blade.php
│
├── categories/
│   └── index.blade.php
│
├── suppliers/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── partials/                  ← Optional: extracted sub-sections for show/index
│       ├── _overview.blade.php
│       ├── _products.blade.php
│       ├── _reviews.blade.php
│       └── _rfq.blade.php
│
├── products/
│   ├── show.blade.php
│   └── partials/                  ← Optional: extracted sub-sections
│       ├── _gallery.blade.php
│       ├── _sidebar.blade.php
│       └── _related.blade.php
│
├── rfq/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── partials/                  ← Optional: extracted sub-sections
│       ├── _info-card.blade.php
│       └── _quote-panel.blade.php
│
└── resources/
    ├── index.blade.php
    └── show.blade.php
```

> **Note on `partials/` sub-folders:** For complex pages (suppliers, products, rfq), extract large sub-sections into a `partials/` folder inside the page folder and `@include` them from the parent view. This keeps each file under a manageable size and matches the section-based pattern used for the homepage.

> **Backend & Auth:** Do not create or modify anything under `resources/views/backend/`. Auth views (login, register) are already implemented — do not rebuild them.

---

## 4. Routes

Public frontend routes only. Place in `routes/frontend.php` (or add to `routes/web.php`). Do not add or modify dashboard or auth routes.

```php
// ─── Public Frontend Routes ───────────────────────────────────────────────
// All controllers are under App\Http\Controllers\Frontend\

Route::get('/',                [HomeController::class,     'index'])->name('home');

Route::get('/categories',      [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}',[CategoryController::class,'show'])->name('categories.show');

Route::get('/suppliers',       [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('/suppliers/{slug}',[SupplierController::class, 'show'])->name('suppliers.show');

Route::get('/products/{slug}', [ProductController::class,  'show'])->name('products.show');

Route::get('/rfq',             [RfqController::class,      'index'])->name('rfq.index');
Route::get('/rfq/create',      [RfqController::class,      'create'])->name('rfq.create');
Route::post('/rfq',            [RfqController::class,      'store'])->name('rfq.store')->middleware('throttle:10,1');
Route::get('/rfq/{id}',        [RfqController::class,      'show'])->name('rfq.show');

Route::get('/resources',       [ResourceController::class, 'index'])->name('resources.index');
Route::get('/resources/{slug}',[ResourceController::class, 'show'])->name('resources.show');

Route::get('/about',           [PageController::class,     'about'])->name('about');
Route::get('/contact',         [PageController::class,     'contact'])->name('contact');
Route::post('/contact',        [PageController::class,     'contactSend'])->name('contact.send')->middleware('throttle:5,1');
Route::get('/pricing',         [PageController::class,     'pricing'])->name('pricing');
Route::get('/terms',           [PageController::class,     'terms'])->name('terms');
Route::get('/privacy',         [PageController::class,     'privacy'])->name('privacy');

// ─── Auth & Dashboard routes are ALREADY IMPLEMENTED ─────────────────────
// DO NOT add, modify, or duplicate:
// - /login, /register, /logout (existing auth)
// - /dashboard/* (existing backend)
// - /admin (existing backend)
```

---

## 5. Controllers

All public frontend controllers live under `App\Http\Controllers\Frontend\`. Do not create or modify backend or auth controllers.

```
App\Http\Controllers\Frontend\

HomeController         → index()    — homepage: featured suppliers, all suppliers, events, stats
CategoryController     → index()    — category directory
                         show($slug) — category landing page with supplier/product list
SupplierController     → index()    — supplier directory with filters
                         show($slug) — public supplier storefront with tabs
ProductController      → show($slug) — product detail page
RfqController          → index()    — browse public RFQs
                         create()   — post RFQ form (GET)
                         store()    — post RFQ form (POST, auth-protected)
                         show($id)  — RFQ detail page
ResourceController     → index()    — blog/article listing
                         show($slug) — article detail
PageController         → about()
                         contact()
                         contactSend()
                         pricing()
                         terms()
                         privacy()

// ─── Already implemented — DO NOT recreate ──────────────────────────────
// Auth controllers (login, register, logout, password reset)
// Supplier dashboard controllers
// Buyer dashboard controllers
// Admin controllers
```

---

## 6. Public Data Eligibility Rules

### 6.1 Supplier Eligibility

Only publicly visible suppliers:

```php
Supplier::where('status', 'active')->whereNull('deleted_at')->whereNotNull('slug')
```

### 6.2 Product Eligibility

```php
Listing::where('approval_status', 'approved')
        ->where('is_active', true)
        ->whereNotNull('published_at')
        ->whereNull('deleted_at')
        ->whereHas('supplier', fn($q) => $q->where('status','active'))
```

Use a central scope `Listing::publiclyVisible()`.

### 6.3 RFQ Eligibility — CRITICAL

```php
Rfq::where('status', 'open')
    ->where('visibility_type', 'global')   // NEVER skip this
    ->whereNotNull('published_at')
    ->whereNull('deleted_at')
    ->where('deadline', '>', now())
```

### 6.4 Reviews

```php
Review::where('status', 'published')
```

### 6.5 General Rules

- Never serialize full Eloquent models to public views — select needed fields only
- Stats on homepage/about pages: derive from DB queries or approved config; no fake hardcoded production numbers
- Do not expose: private contact info, supplier documents, billing data, internal workflow statuses

---

## 7. Global Layout: app.blade.php

**File:** `resources/views/frontend/layouts/app.blade.php`

All public frontend views extend `frontend.layouts.app`. Partials use the `frontend.partials.*` namespace.

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Edushopify — Global Education Suppliers')</title>
  <meta name="description" content="@yield('meta_description', 'B2B education procurement marketplace.')">
  <link rel="canonical" href="{{ url()->current() }}" />

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <style>
    * { font-family: 'Inter', sans-serif; }
    .badge-verified { background:#e8f5ee; color:#1a7f45; border:1px solid #b6e0c6; }
    .badge-founding { background:#fff8e6; color:#a16207; border:1px solid #fde68a; }
    .badge-ise      { background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; }
    .badge-new      { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
    .badge-featured { background:#fef9c3; color:#854d0e; border:1px solid #fef08a; }
    .tag-active     { background:#111827; color:#fff; }
    .tag-inactive   { background:#f3f4f6; color:#374151; }
    .star           { color:#f59e0b; }
    .hero-overlay   { background:linear-gradient(90deg,rgba(0,0,0,.72) 0%,rgba(0,0,0,.3) 60%,rgba(0,0,0,.05) 100%); }
    .form-input     { border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; width:100%; outline:none; transition:box-shadow .15s; }
    .form-input:focus { box-shadow:0 0 0 2px #a7f3d0; border-color:#10b981; }
    #mobile-menu    { transition:transform 0.3s ease; }
    @yield('styles')
  </style>
</head>
<body class="bg-white text-gray-800 antialiased">

  @include('frontend.partials.mobile-menu')
  @include('frontend.partials.navbar')

  <main>
    @include('frontend.partials.alerts')
    @yield('content')
  </main>

  @include('frontend.partials.footer')

  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>
```

**Every public frontend page extends this layout:**

```blade
@extends('frontend.layouts.app')
@section('title', 'Page Title — Edushopify')
@section('content')
  {{-- page content --}}
@endsection
```

---

## 8. Scope Boundaries

This specification covers **public frontend only**. The following are already implemented and must not be touched:

### 8.1 Backend / Dashboard — DO NOT IMPLEMENT

The backend is complete and lives in `resources/views/backend/`.

Do not create, modify, or duplicate:
- Supplier Dashboard pages or layouts
- Buyer Dashboard pages or layouts
- Admin Dashboard pages or layouts
- Dashboard sidebar components
- Dashboard route groups
- Dashboard controllers
- Any view under `resources/views/backend/`

### 8.2 Authentication — DO NOT IMPLEMENT

Login, registration, and all auth flows are already implemented.

Do not create, modify, or duplicate:
- Login functionality or views
- Registration workflow or views
- Password reset system
- Email verification
- Auth middleware or guards
- Any auth controller logic

The navbar's `@guest` / `@auth` blocks reference existing auth routes (`route('login')`, `route('register')`, `route('logout')`) — link to them, do not rebuild them.

### 8.3 Database — DO NOT MODIFY

Do not create or alter:
- Database migrations
- Database schema
- Existing models (only add public query scopes if needed, following existing project conventions)

---

## 9. Navbar

**File:** `resources/views/frontend/partials/navbar.blade.php`
**Height:** h-14 · **Position:** sticky top-0 z-50 · **Background:** bg-white border-b border-gray-200

**Desktop layout:**
```
[☰ mobile] [Logo] [Search flex-1 max-w-md] [Categories|RFQ|Suppliers|Resources] [♡ 💬 🔔] [Sign In] [Register]
```

```html
<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-6 h-14">

    <!-- Hamburger (mobile only) -->
    <button id="menu-open" onclick="openMenu()" class="lg:hidden text-gray-600 hover:text-gray-900 mr-1">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 12h18M3 6h18M3 18h18"/>
      </svg>
    </button>

    <!-- Logo: 3x3 dot SVG + wordmark -->
    <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
      <svg width="30" height="30" viewBox="0 0 36 36" fill="none">
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
      <span class="font-bold text-gray-900 text-[17px]">Edu<span class="text-emerald-500">shopify</span></span>
      <span class="text-[9px] text-gray-400 hidden sm:block">Connecting Education</span>
    </a>

    <!-- Search (hidden mobile) -->
    <div class="flex-1 max-w-md relative hidden sm:flex">
      <input type="text" placeholder="Search suppliers, products, categories..."
             class="w-full border border-gray-200 rounded-l-md text-sm px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      <button class="px-3 bg-emerald-500 hover:bg-emerald-600 rounded-r-md">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
      </button>
    </div>

    <!-- Desktop nav -->
    <nav class="hidden lg:flex items-center gap-5 ml-2">
      <a href="{{ route('categories.index') }}"
         class="text-sm font-medium {{ request()->routeIs('categories.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
        Categories</a>
      <a href="{{ route('rfq.index') }}"
         class="text-sm font-medium {{ request()->routeIs('rfq.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
        RFQ</a>
      <a href="{{ route('suppliers.index') }}"
         class="text-sm font-medium {{ request()->routeIs('suppliers.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
        Suppliers</a>
      <a href="{{ route('resources.index') }}"
         class="text-sm font-medium {{ request()->routeIs('resources.*') ? 'text-emerald-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
        Resources</a>
    </nav>

    <!-- Right icons + auth -->
    <div class="ml-auto flex items-center gap-3">
      <div class="hidden sm:flex items-center gap-2 text-gray-500">
        <button class="hover:text-gray-800"><!-- heart icon --></button>
        <button class="hover:text-gray-800"><!-- chat icon --></button>
        <button class="relative hover:text-gray-800">
          <!-- bell icon -->
          <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-emerald-500 rounded-full"></span>
        </button>
      </div>
      @guest
        <a href="{{ route('login') }}" class="hidden sm:inline text-sm font-medium text-gray-700 hover:text-gray-900">Sign In</a>
        <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-md">Register</a>
      @else
        <div class="relative" id="user-menu">
          <button onclick="toggleUserMenu()"
                  class="w-8 h-8 rounded bg-emerald-100 text-emerald-700 font-semibold text-sm flex items-center justify-center">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </button>
          <div id="user-dropdown" class="hidden absolute right-0 top-10 w-44 bg-white border border-gray-100 rounded-lg shadow-lg py-1 z-50">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Dashboard</a>
            <hr class="my-1 border-gray-100">
            <form method="POST" action="{{ route('logout') }}">@csrf
              <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Sign Out</button>
            </form>
          </div>
        </div>
      @endguest
    </div>
  </div>
</header>
```

---

## 10. Mobile Menu Drawer

**File:** `resources/views/frontend/partials/mobile-menu.blade.php`
**Behavior:** Slides from LEFT · Overlay dims page · Close via × or overlay click or Escape key

```html
<!-- Overlay -->
<div id="menu-overlay" class="fixed inset-0 bg-black/40 z-40 hidden" onclick="closeMenu()"></div>

<!-- Drawer -->
<div id="mobile-menu"
     class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-xl transform -translate-x-full flex flex-col">

  <!-- Header -->
  <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
      <!-- smaller 3x3 dot SVG -->
      <span class="font-bold text-gray-900">Edu<span class="text-emerald-500">shopify</span></span>
    </a>
    <button onclick="closeMenu()" class="text-gray-500 hover:text-gray-900">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M6 18 18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <!-- Search -->
  <div class="px-4 py-3 border-b border-gray-100">
    <input type="text" placeholder="Search suppliers..."
           class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm
                  focus:outline-none focus:ring-2 focus:ring-emerald-400" />
  </div>

  <!-- Nav items -->
  <nav class="px-4 py-4 flex flex-col gap-1 flex-1 overflow-y-auto">
    @foreach([
      ['route' => 'categories.index', 'label' => 'Categories'],
      ['route' => 'rfq.index',        'label' => 'RFQ'],
      ['route' => 'suppliers.index',  'label' => 'Suppliers'],
      ['route' => 'resources.index',  'label' => 'Resources'],
      ['route' => 'pricing',          'label' => 'Pricing'],
      ['route' => 'about',            'label' => 'About'],
      ['route' => 'contact',          'label' => 'Contact'],
    ] as $nav)
    <a href="{{ route($nav['route']) }}"
       class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
      {{ $nav['label'] }}
    </a>
    @endforeach
  </nav>

  <!-- Auth -->
  <div class="px-4 pb-6 pt-3 border-t border-gray-100 space-y-2">
    @guest
      <a href="{{ route('login') }}" class="block text-center py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900">Sign In</a>
      <a href="{{ route('register') }}" class="block text-center py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-md">Register</a>
    @else
      <a href="{{ route('dashboard') }}" class="block text-center py-2.5 bg-emerald-500 text-white text-sm font-semibold rounded-md">Dashboard</a>
    @endguest
  </div>
</div>
```

---

## 11. Footer

**File:** `resources/views/frontend/partials/footer.blade.php`
**Background:** `bg-white border-t border-gray-200`
**Layout:** 4-column grid + bottom bar

```html
<footer class="bg-white border-t border-gray-200 pt-14 pb-8 px-4">
  <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

    <!-- Brand -->
    <div>
      <!-- Logo same as navbar -->
      <p class="text-sm text-gray-500 leading-relaxed mt-4 max-w-[260px]">
        The B2B education procurement marketplace connecting institutional buyers with verified suppliers worldwide.
      </p>
    </div>

    <!-- Marketplace -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Marketplace</p>
      <ul class="space-y-4 text-sm text-gray-500">
        <li><a href="{{ route('suppliers.index') }}"  class="hover:text-gray-900">Browse Suppliers</a></li>
        <li><a href="{{ route('categories.index') }}" class="hover:text-gray-900">Categories</a></li>
        <li><a href="{{ route('rfq.create') }}"       class="hover:text-gray-900">Request Quote</a></li>
        <li><a href="{{ route('rfq.index') }}"        class="hover:text-gray-900">Browse RFQs</a></li>
      </ul>
    </div>

    <!-- Company -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Company</p>
      <ul class="space-y-4 text-sm text-gray-500">
        <li><a href="{{ route('about') }}"           class="hover:text-gray-900">About Us</a></li>
        <li><a href="{{ route('resources.index') }}" class="hover:text-gray-900">Blog</a></li>
        <li><a href="{{ route('contact') }}"         class="hover:text-gray-900">Contact</a></li>
        <li><a href="/careers"                       class="hover:text-gray-900">Careers</a></li>
      </ul>
    </div>

    <!-- Support -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Support</p>
      <ul class="space-y-4 text-sm text-gray-500">
        <li><a href="/help"                  class="hover:text-gray-900">Help Center</a></li>
        <li><a href="{{ route('pricing') }}" class="hover:text-gray-900">Pricing</a></li>
        <li><a href="/terms"                 class="hover:text-gray-900">Terms of Service</a></li>
        <li><a href="/privacy"               class="hover:text-gray-900">Privacy Policy</a></li>
      </ul>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="max-w-7xl mx-auto border-t border-gray-200 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
    <p class="text-sm text-gray-400">© {{ date('Y') }} Edushopify. All rights reserved.</p>
    <div class="flex gap-6 text-sm text-gray-400">
      <a href="/terms"   class="hover:text-gray-700">Terms</a>
      <a href="/privacy" class="hover:text-gray-700">Privacy</a>
    </div>
  </div>
</footer>
```

---

## 12. Reusable Partials & Components

### 12.1 Alerts — `frontend/partials/alerts.blade.php`

```html
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 text-sm mb-4 flex items-center gap-2" id="flash-msg">
  <!-- check icon -->
  {{ session('success') }}
  <button onclick="document.getElementById('flash-msg').remove()" class="ml-auto text-emerald-600 hover:text-emerald-800">✕</button>
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-md px-4 py-3 text-sm mb-4 flex items-center gap-2">
  <!-- error icon --> {{ session('error') }}
</div>
@endif
```

### 12.2 Breadcrumb — `frontend/partials/breadcrumb.blade.php`

```html
<nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
  <a href="{{ route('home') }}" class="hover:text-gray-900">Home</a>
  @foreach($crumbs as $label => $url)
    <span class="text-gray-300">/</span>
    @if($loop->last)
      <span class="text-gray-900 font-medium">{{ $label }}</span>
    @else
      <a href="{{ $url }}" class="hover:text-gray-900">{{ $label }}</a>
    @endif
  @endforeach
</nav>
```

### 12.3 Custom Pagination — `frontend/partials/pagination.blade.php`

```html
@if($paginator->hasPages())
<div class="flex items-center justify-center gap-1 mt-6">
  @if($paginator->onFirstPage())
    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-md cursor-not-allowed">← Prev</span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">← Prev</a>
  @endif

  @foreach($elements as $element)
    @if(is_string($element)) <span class="px-3 py-1.5 text-sm text-gray-400">…</span> @endif
    @if(is_array($element))
      @foreach($element as $page => $url)
        @if($page == $paginator->currentPage())
          <span class="px-3 py-1.5 text-sm bg-emerald-500 text-white rounded-md font-medium">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">{{ $page }}</a>
        @endif
      @endforeach
    @endif
  @endforeach

  @if($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">Next →</a>
  @else
    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-md cursor-not-allowed">Next →</span>
  @endif
</div>
@endif
```

### 12.4 Supplier Card (Compact) — `frontend/partials/supplier-card.blade.php`

```html
<div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
  <div class="flex items-start justify-between mb-2">
    <div class="flex items-center gap-2.5">
      <span class="w-9 h-9 rounded bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center shrink-0">
        {{ strtoupper(substr($supplier->name, 0, 1)) }}
      </span>
      <div>
        <p class="font-semibold text-sm text-gray-900 leading-tight">{{ $supplier->name }}</p>
        <p class="text-xs text-gray-500">{{ $supplier->type }}</p>
      </div>
    </div>
    <button class="text-gray-300 hover:text-red-400 transition-colors"><!-- heart --></button>
  </div>
  <div class="flex flex-wrap gap-1 mb-2">
    @if($supplier->is_verified) <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded inline-flex items-center gap-1">✓ Verified</span> @endif
    @if($supplier->is_founding) <span class="badge-founding text-[10px] font-medium px-1.5 py-0.5 rounded">Founding</span> @endif
  </div>
  <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
    <span><span class="star">★</span> {{ $supplier->rating }} <span class="text-gray-400">({{ $supplier->review_count }})</span></span>
    <span>{{ $supplier->country }}</span>
  </div>
  <div class="flex items-center justify-between text-xs">
    <span class="text-gray-400">🛍 {{ $supplier->product_count }}+ Products</span>
    <a href="{{ route('suppliers.show', $supplier->slug) }}" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
  </div>
</div>
```

### 12.5 Empty State

```html
<div class="text-center py-16">
  <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4"><!-- icon --></div>
  <p class="font-semibold text-gray-900 mb-1">No results found</p>
  <p class="text-sm text-gray-500 mb-5">Try adjusting your filters or search terms.</p>
  <button onclick="clearFilters()" class="text-sm text-emerald-600 hover:underline">Clear all filters</button>
</div>
```

### 12.6 Table with Export (Reusable Pattern)

```html
<!-- Export controls row -->
<div class="flex items-center justify-between mb-4">
  <div class="flex items-center gap-2">
    <input type="text" placeholder="Search..." id="table-search"
           class="border border-gray-200 rounded-md px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
  </div>
  <div class="flex items-center gap-2">
    <button onclick="exportTableCSV('main-table','export')" class="border border-gray-200 text-sm px-3 py-1.5 rounded-md hover:bg-gray-50">Export CSV</button>
    <button onclick="exportTablePDF('main-table','Export')" class="border border-gray-200 text-sm px-3 py-1.5 rounded-md hover:bg-gray-50">Export PDF</button>
    <button onclick="window.print()"                        class="border border-gray-200 text-sm px-3 py-1.5 rounded-md hover:bg-gray-50">Print</button>
  </div>
</div>

<!-- Table -->
<div class="overflow-x-auto border border-gray-200 rounded-lg">
  <table class="w-full text-sm" id="main-table">
    <thead class="bg-gray-50 border-b border-gray-200">
      <tr>
        <th class="w-8 px-4 py-3"><input type="checkbox" id="check-all" class="w-4 h-4 rounded accent-emerald-500" /></th>
        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide cursor-pointer hover:text-gray-900" onclick="sortTable(this,1)">
          Name <span class="sort-indicator">↕</span>
        </th>
        <!-- more headers -->
        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @foreach($rows as $row)
      <tr class="hover:bg-gray-50">
        <td class="px-4 py-3"><input type="checkbox" class="row-check w-4 h-4 rounded accent-emerald-500" value="{{ $row->id }}" /></td>
        <!-- data cells -->
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <a href="#" class="text-sm text-gray-500 hover:text-emerald-600">View</a>
            <span class="text-gray-200">|</span>
            <a href="#" class="text-sm text-gray-500 hover:text-emerald-600">Edit</a>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<!-- Bulk action + pagination row -->
<div class="flex items-center justify-between mt-4">
  <div class="flex items-center gap-3">
    <span class="text-sm text-gray-500" id="selected-count"></span>
    <button id="bulk-delete" class="hidden text-sm text-red-500 hover:text-red-700 border border-red-200 px-3 py-1 rounded-md">Delete Selected</button>
  </div>
  {{ $rows->links('frontend.partials.pagination') }}
</div>
```


---

## 13. Home Page

**Route:** `GET /`
**View:** `resources/views/frontend/home/index.blade.php`
**Controller:** `HomeController@index` (under `App\Http\Controllers\Frontend\`)
**Extends:** `frontend.layouts.app`

`home/index.blade.php` is a **composition file only** — it contains no inline section HTML. It `@include`s each section partial from `frontend/home/sections/`:

```blade
@extends('frontend.layouts.app')
@section('title', 'Edushopify — Global Education Suppliers')

@section('content')
    @include('frontend.home.sections._hero')
    @include('frontend.home.sections._stats_bar')
    @include('frontend.home.sections._featured_suppliers')
    @include('frontend.home.sections._all_suppliers')
    @include('frontend.home.sections._why_choose_edushopify')
    @include('frontend.home.sections._events')
@endsection
```

### Homepage Sections (top → bottom)

1. Hero → `_hero.blade.php`
2. Stats Bar → `_stats_bar.blade.php`
3. Featured Suppliers → `_featured_suppliers.blade.php`
4. All Suppliers (with filter tabs) → `_all_suppliers.blade.php`
5. Why Choose Edushopify → `_why_choose_edushopify.blade.php`
6. Events → `_events.blade.php`

### 13.1 Hero

```html
<section class="relative h-[420px] overflow-hidden">
  <img src="{{ $heroImage ?? asset('images/hero.jpg') }}" class="absolute inset-0 w-full h-full object-cover" alt="EduShopify" />
  <div class="hero-overlay absolute inset-0"></div>
  <div class="relative max-w-7xl mx-auto px-6 h-full flex flex-col justify-center">
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight max-w-xl">
      Global Suppliers for<br>Education. All in One Place.
    </h1>
    <p class="text-white/80 mt-3 text-base max-w-sm">Connect with verified education suppliers worldwide</p>
    <div class="flex gap-3 mt-7 flex-wrap">
      <a href="{{ route('suppliers.index') }}" class="bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-md text-sm hover:bg-gray-100">Find Suppliers</a>
      <a href="{{ route('rfq.create') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-md text-sm">Post an RFQ</a>
    </div>
  </div>
</section>
```

### 13.2 Stats Bar

```html
<div class="bg-emerald-500 py-5">
  <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-white">
    <div><p class="text-2xl font-bold">{{ $stats->suppliers_count ?? '8,000+' }}</p><p class="text-sm opacity-80 mt-0.5">Suppliers</p></div>
    <div><p class="text-2xl font-bold">{{ $stats->countries_count ?? '50+' }}</p><p class="text-sm opacity-80 mt-0.5">Countries</p></div>
    <div><p class="text-2xl font-bold">{{ $stats->products_count ?? '12,000+' }}</p><p class="text-sm opacity-80 mt-0.5">Products</p></div>
    <div><p class="text-2xl font-bold">{{ $stats->buyers_count ?? '2,400+' }}</p><p class="text-sm opacity-80 mt-0.5">Verified Buyers</p></div>
  </div>
</div>
```

Note: In production, derive these values from DB queries (`$stats` object from controller). The fallback strings are spec examples.

### 13.3 Featured Suppliers (image cards, 4-col)

```
Section header: "Featured Suppliers" + "See all →" + chevron nav buttons
Grid: 4 cols desktop / 2 mobile
Card type: supplier-card-featured (image top + badge overlay + profile info)
Data: $featuredSuppliers from DB (eligible + featured flag)
```

Featured card structure:
```html
<div class="relative rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition-shadow group">
  <img src="{{ $supplier->banner_url }}" alt="{{ $supplier->name }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" />
  <div class="absolute bottom-3 left-3">
    @if($supplier->is_verified)
      <span class="badge-verified text-[10px] font-semibold px-1.5 py-0.5 rounded inline-flex items-center gap-1">✓ Verified</span>
    @endif
  </div>
  <button class="absolute top-3 right-3 w-7 h-7 bg-white/90 rounded flex items-center justify-center hover:bg-white shadow-sm"><!-- heart --></button>
  <div class="p-3">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="w-7 h-7 rounded bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0">
        {{ strtoupper(substr($supplier->name, 0, 1)) }}
      </span>
      <div>
        <p class="text-sm font-semibold text-gray-900">{{ $supplier->name }}</p>
        <p class="text-xs text-gray-500">{{ $supplier->type }}</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-1 mb-2">
      @if($supplier->is_verified) <span class="badge-verified text-[10px] px-1.5 py-0.5 rounded">Verified</span> @endif
      @if($supplier->is_founding) <span class="badge-founding text-[10px] px-1.5 py-0.5 rounded">Founding</span> @endif
    </div>
    <div class="flex items-center justify-between text-xs text-gray-500">
      <span><span class="star">★</span> {{ $supplier->rating }} ({{ $supplier->review_count }})</span>
      <span>{{ $supplier->country }}</span>
    </div>
    <div class="flex items-center justify-between text-xs mt-1">
      <span class="text-gray-400">{{ $supplier->product_count }}+ Products</span>
      <a href="{{ route('suppliers.show', $supplier->slug) }}" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
    </div>
  </div>
</div>
```

### 13.4 All Suppliers (with category filter tabs)

```
Header: "All Suppliers" + "View all →"
Filter tabs (horizontal scroll mobile): All Categories · STEM & Robotics · AV & Display · Furniture · Lab Equipment · Software & LMS + [Filter] button
Grid: 2 rows × 4 cols = 8 compact supplier-card
Data: $allSuppliers (DB, filtered by active category tab)
```

Filter tabs use `.tab-btn` + `tag-active`/`tag-inactive` CSS classes + JS filter (Section 23.4).

### 13.5 Why Choose Edushopify

```
bg-gray-50, text-center heading
Label: "OUR ADVANTAGE" (emerald uppercase)
H2: "Why Choose Edushopify"
4 pillars (2×2 mobile / 4-col desktop):
  Verified Suppliers | Global Reach | RFQ Opportunities | Secure & Reliable
Each: w-12 h-12 bg-emerald-50 rounded-md icon circle + bold title + short description
```

### 13.6 Events

```
Label: "DON'T MISS OUT" (emerald uppercase)
H2: "Education STEM & Robotics Events" + "View all →"
4 event cards (2 mobile):
  h-56 image with bg-black/50 overlay
  Category badge (top-left, emerald bg)
  Title (white, bottom)
  Date + Location (white/70)
Data: $events from DB
```

---

## 14. Categories Page

**Route:** `GET /categories`
**View:** `resources/views/frontend/categories/index.blade.php`

```
Page header (white bg, border-b):
  Breadcrumb → H1 "Browse by Category" → Subtext

Category grid: 2 cols mobile / 3 sm / 4 desktop
Each card: icon (bg-emerald-50 rounded-md w-12 h-12) + Name + X Suppliers + Browse →
Hover: bg-gray-50 border-gray-300
```

Categories (from DB): STEM & Robotics · AV & Display · Furniture & Seating · Lab Equipment · Software & LMS · Publishing & Content · Sports & PE · Safety & Security · Art & Craft Supplies · Special Needs · School Administration · EdTech Hardware

---

## 15. Suppliers Listing Page

**Route:** `GET /suppliers`
**View:** `resources/views/frontend/suppliers/index.blade.php`

```
Layout (lg+): sidebar (w-64, sticky top-20) LEFT + results RIGHT

Sidebar filters:
  - Category (checkbox list with count)
  - Country (select)
  - Verified Only (checkbox)
  - [Apply Filters] emerald btn
  - [Clear All] text link

Results:
  - Header: "N suppliers found" + Sort select (Featured | Highest Rated | Newest)
  - Grid: 1 mobile / 2 sm / 3 lg
  - frontend/partials/supplier-card component
  - Pagination
  - Empty state if no results
```

---

## 16. Supplier Profile / Storefront Page

**Route:** `GET /suppliers/{slug}`
**View:** `resources/views/frontend/suppliers/show.blade.php`
**Partials:** Large tab panels can be extracted to `frontend/suppliers/partials/_overview.blade.php`, `_products.blade.php`, `_reviews.blade.php`, `_rfq.blade.php` and `@include`d from `show.blade.php`.

### Structure

```
1. Hero banner (h-48, rounded-lg): image + verified badge overlay bottom-left
2. Profile header (-mt-10):
   Avatar 80×80 (rounded-md, border-4 white, shadow-md, bg-emerald-100)
   Name / type / country / badges / stats (X Products · X Reviews · Since YYYY)
   CTAs: [Send Inquiry — emerald] [Save Supplier — outline] [Download PDF]
3. Sticky tab bar (top-14, z-30): Overview | Products | Reviews | RFQ Requests
4. Tab content panels (JS-switched via inline style)
```

### Tab content

**Overview:** About text + categories badge list + certifications grid + key contacts table

**Products:** Category sub-tabs + product grid (image, name, MOQ, price range, Request Quote)

**Reviews:** Average rating (large score + stars + count) + review list (avatar initial + name + date + rating + comment) + Write a Review form (collapsible)

**RFQ Requests:** Inline RFQ form (same fields as rfq/create)

### Tab switching JS (CRITICAL)

```javascript
// Use inline style — not CSS class — to control display
// Pass this from onclick, never event
function switchSupplierTab(btn, name) {
  document.querySelectorAll('.supplier-tab-btn').forEach(b => {
    b.classList.remove('border-emerald-500','text-emerald-600');
    b.classList.add('border-transparent','text-gray-500');
  });
  btn.classList.add('border-emerald-500','text-emerald-600');
  btn.classList.remove('border-transparent','text-gray-500');
  ['overview','products','reviews','rfq'].forEach(t => {
    const p = document.getElementById('supplier-tab-' + t);
    if (p) p.style.display = 'none';
  });
  const target = document.getElementById('supplier-tab-' + name);
  if (target) target.style.display = 'block';
}
```

---

## 17. Product Detail Page

**Route:** `GET /products/{slug}`
**View:** `resources/views/frontend/products/show.blade.php`
**Reference:** `edushopify-product-detail-spec.md`
**Partials:** Large sections can be extracted to `frontend/products/partials/_gallery.blade.php`, `_sidebar.blade.php`, `_related.blade.php`.

### Desktop two-column layout

```
LEFT (lg:flex-1):
  Image gallery (main + 4 thumbnails with JS switcher)
  Description tab content
  Spec table (bordered, 160px label col, alternating bg rows, highlight rows)
  Buyer protection (4 icon grid)
  Customer reviews (4.8 score + rating bars + 3 review cards)

RIGHT sidebar (lg:w-[340px] xl:w-[360px], sticky top-20):
  Product title
  SKU pill badge (text-xs bg-gray-100 border rounded px-2 py-0.5)
  Star rating row
  Price range box (bg-emerald-50 border-emerald-100 rounded-lg p-4)
  3 trust bullets: shield (secure payment) · truck (fast delivery) · padlock (buyer protection)
  [Request Quotation] button (emerald, full width)
  [Contact Supplier] button (outline, full width)
  [Save] and [Share] as two separate bordered icon+text buttons
  Supplied By card (avatar initial + 3 stats: products, reviews, since)
  Why Edushopify checklist (emerald check marks)
  Fast Response box (bg-emerald-50 border-emerald-100 rounded-lg)

BELOW (full width):
  You May Also Like — 4-col product card grid
```

### Gallery JS

```javascript
function switchGalleryImage(src, thumb) {
  document.getElementById('main-gallery-image').src = src;
  document.querySelectorAll('.thumb-btn').forEach(t => {
    t.classList.remove('border-emerald-500'); t.classList.add('border-gray-200');
  });
  thumb.classList.add('border-emerald-500'); thumb.classList.remove('border-gray-200');
}
```

### Save toggle + Share JS

```javascript
async function toggleSave(listingId, btn) {
  const res = await fetch('/api/save/toggle', {
    method: 'POST',
    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    body: JSON.stringify({ listing_id: listingId })
  });
  if (res.ok) { const d = await res.json(); btn.innerHTML = d.saved ? '♥ Saved' : '♡ Save'; }
}
async function shareProduct(url, title) {
  if (navigator.share) { await navigator.share({ title, url }); }
  else { await navigator.clipboard.writeText(url); showToast('Link copied!'); }
}
function showToast(msg) {
  const el = document.createElement('div');
  el.className = 'fixed bottom-4 right-4 bg-gray-900 text-white text-sm px-4 py-2.5 rounded-lg shadow-lg z-50';
  el.textContent = msg; document.body.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}
```

---

## 18. RFQ Pages

### 18.1 Browse RFQs

**Route:** `GET /rfq` · **View:** `resources/views/frontend/rfq/index.blade.php`

```
Header: H2 "Open RFQs" + [+ Post an RFQ] button (right, emerald)
Search input (live JS filter)
Table (overflow-x-auto):
  Columns: Title | Category | Budget | Deadline | Responses | Status | Action
  Status badges: Open (emerald) | Closed (gray) | In Review (amber)
  Rows: clickable to rfq.show
Pagination
```

### 18.2 Post RFQ Form

**Route:** `GET/POST /rfq/create` · **View:** `resources/views/frontend/rfq/create.blade.php`

```
3-step indicator: [1 Requirements] ── [2 Details] ── [3 Submit]
  Active step: w-7 h-7 rounded bg-emerald-500 text-white
  Inactive step: w-7 h-7 rounded bg-gray-100 text-gray-400

Form fields (2-col grid on lg):
  RFQ Title * (full width)
  Category * (select)
  Quantity (number)
  Budget USD (text)
  Deadline (date)
  Description * (textarea rows-5, full width)
  Destination Country (select)
  Attach File (file input with emerald styling)

Footer: [Cancel] text link + [Submit RFQ] emerald button
```

### 18.3 RFQ Detail Page

**Route:** `GET /rfq/{id}` · **View:** `resources/views/frontend/rfq/show.blade.php`
**Reference:** `edushopify-rfq-detail-spec.md`
**Partials:** Info card and quote panel can be extracted to `frontend/rfq/partials/_info-card.blade.php` and `_quote-panel.blade.php`.

```
Page header: Title + Status badge + [Save] [Share] buttons + [Submit Quote] CTA

Two-column (lg+):
  LEFT (flex-1):
    Description card
    Specifications card (2-col key/value grid)
    Attachments card (file type icons)
    Buyer Info card
    Quote Submissions list (collapsed, "Show X more" JS)

  RIGHT (lg:w-[340px], sticky top-20):
    RFQ Info card (Category, Budget, Deadline+countdown, Country, Qty, Posted — 7 icon rows)
    Submit Quote card (3 states):
      Guest     → "Login / Register to Quote" + "Become a Supplier" links
      Supplier  → quote form: unit price input + lead time + notes + auto-calc total
      Submitted → submitted summary + edit link
    Similar RFQs (3 compact rows)
```

Deadline countdown JS:

```javascript
(function(){
  const deadline = new Date('{{ $rfq->deadline->toISOString() }}');
  const diff = Math.ceil((deadline - Date.now()) / 86400000);
  const el = document.getElementById('deadline-countdown');
  if (!el) return;
  if (diff > 0) el.textContent = diff + ' days left';
  else if (diff === 0) el.textContent = 'Closes today';
  else el.textContent = 'Closed';
  if (diff <= 3 && diff >= 0) el.classList.add('text-red-500');
})();
```

Auto-calculate total JS:

```javascript
function calcTotal() {
  const price = parseFloat(document.getElementById('unit-price').value) || 0;
  const qty   = parseFloat(document.getElementById('rfq-qty').textContent) || 1;
  document.getElementById('quote-total').textContent =
    (price * qty).toLocaleString('en-US', { style:'currency', currency:'USD' });
}
document.getElementById('unit-price')?.addEventListener('input', calcTotal);
```

Show more quotes JS:

```javascript
function showMoreQuotes() {
  document.querySelectorAll('.quote-row.hidden').forEach(r => r.classList.remove('hidden'));
  document.getElementById('show-more-quotes')?.remove();
}
```

---

## 19. Resources / Blog Pages

### 19.1 Blog Index

**Route:** `GET /resources` · **View:** `resources/views/frontend/resources/index.blade.php`

```
Page header: H2 "Resources & Insights"
Filter tabs: All | Guides | News | Events | Case Studies

Featured article (full-width card):
  Image left (lg:w-1/2) + content right
  Category badge + H3 + excerpt + author/date + "Read More →"

Article grid (3 cols lg, 2 sm, 1 mobile):
  Image (h-44 rounded-md) + category badge + H4 + 2-line excerpt + author avatar + date + "Read More →"

Sidebar (lg, sticky):
  Popular tags
  Upcoming events list
```

### 19.2 Blog Post Detail

**Route:** `GET /resources/{slug}` · **View:** `resources/views/frontend/resources/show.blade.php`

```
Breadcrumb: Home > Resources > {title}
Two-column (lg):
  LEFT (2/3): badge + H1 + author row + hero image + article body (prose) + tags + share buttons + author bio card
  RIGHT (1/3, sticky): related articles (3 items) + "Are you a supplier? Register →" CTA
```

---

## 20. About Page

**Route:** `GET /about` · **View:** `resources/views/frontend/pages/about.blade.php`

```
Section 1 — Hero (no image, text only):
  bg-gray-50, py-20, text-center
  Label: "ABOUT EDUSHOPIFY" (emerald uppercase)
  H1: "Connecting the World's Education Buyers with Trusted Suppliers"
  Mission statement paragraph

Section 2 — Mission & Vision (2-col):
  LEFT: Mission — icon + heading + paragraph
  RIGHT: Vision — icon + heading + paragraph
  border-l border-gray-200 on lg

Section 3 — Stats Row:
  4 stats (same pattern as homepage stats bar)
  bg-white, border-y border-gray-100, text-gray-900

Section 4 — Team Grid:
  H2: "Our Team"
  3 cols lg / 2 sm
  Each: 64×64 avatar (bg-emerald-100 text-emerald-700 rounded) + Name + Role + LinkedIn icon
  border border-gray-200 rounded-lg p-5 text-center

Section 5 — Values (3 cards):
  bg-gray-50
  3-col grid: icon in bg-emerald-50 rounded-md + Title + Description
  bg-white rounded-lg p-5 (no border, no shadow)

Section 6 — CTA Banner:
  bg-gray-900 text-white text-center py-16
  H2: "Ready to Grow Your Education Business?"
  [Register as Supplier — emerald] [Browse Suppliers — white outline]
```

---

## 21. Contact Page

**Route:** `GET /contact`, `POST /contact`
**View:** `resources/views/frontend/pages/contact.blade.php`

```
Layout: 2-col lg (info left, form right)
Left: H2 "Get in Touch" + 3 info blocks (Email, Phone, Location) + office hours + social icons
Right: form (First Name, Last Name, Email, Role select, Subject, Message, Send Message btn)
```

Form uses `.form-input` CSS class and `.form-input:focus` styles.
On success: show `session('success')` flash via alerts partial.
Server: `throttle:5,1` rate limit, validate with `StoreContactRequest`.

---

## 22. Pricing Page

**Route:** `GET /pricing` · **View:** `resources/views/frontend/pages/pricing.blade.php`
**Data:** `$plans` from DB + `$faqs` from config or DB

### 22.1 Header

```
text-center, py-16
Label: "PRICING"
H1: "Simple, Transparent Pricing"
Monthly/Annually toggle (checkbox input, JS updates prices — see JS Section 23.7)
```

### 22.2 Pricing Cards (3 tiers)

```
Grid: 3 cols md / 1 mobile

Starter:      border border-gray-200 rounded-lg p-6
              $0 / Free forever
              5 feature list items (✓ emerald / ✗ gray-300)
              [Get Started Free] outline btn

Professional: border-2 border-emerald-500 rounded-lg p-6 relative
              "Most Popular" badge (-top-3, bg-emerald-500, -translate-x-1/2)
              $49/mo (id="pro-price" for JS toggle)
              5 feature items
              [Start Free Trial] emerald btn

Enterprise:   border border-gray-200 rounded-lg p-6
              $149/mo (id="ent-price")
              5 feature items
              [Contact Sales] outline btn
```

### 22.3 Feature Comparison Table

```
overflow-x-auto max-w-5xl mx-auto mt-16
Columns: Feature | Starter | Professional (emerald header) | Enterprise
Rows from plan data (Product Listings, RFQ Responses, Analytics, Featured Placement, Account Manager)
```

### 22.4 FAQ Accordion

```
max-w-2xl mx-auto mt-16
Each item: border-b border-gray-200
  Button: question + chevron icon (.faq-icon)
  Body: .faq-body hidden pb-4 text-sm text-gray-600
JS: toggleFaq(btn) — see Section 23.6
```

---

## 23. JavaScript Patterns

All JS is vanilla. Place in `public/js/app.js`. No Alpine, no Livewire, no jQuery.

### 23.1 Mobile Menu

```javascript
function openMenu() {
  document.getElementById('mobile-menu').classList.remove('-translate-x-full');
  document.getElementById('menu-overlay').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function closeMenu() {
  document.getElementById('mobile-menu').classList.add('-translate-x-full');
  document.getElementById('menu-overlay').classList.add('hidden');
  document.body.style.overflow = '';
}
document.getElementById('menu-open')?.addEventListener('click', openMenu);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
```

### 23.2 User Dropdown

```javascript
function toggleUserMenu() {
  document.getElementById('user-dropdown').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
  const menu = document.getElementById('user-menu');
  if (menu && !menu.contains(e.target)) document.getElementById('user-dropdown')?.classList.add('hidden');
});
```

### 23.3 Sidebar Toggle (Dashboard)

```javascript
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
}
```

### 23.4 Category / Filter Tabs

```javascript
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('tag-active'); b.classList.add('tag-inactive'); });
    this.classList.remove('tag-inactive'); this.classList.add('tag-active');
    const cat = this.dataset.cat;
    document.querySelectorAll('.supplier-item').forEach(item => {
      item.style.display = (cat === 'all' || item.dataset.cat === cat) ? '' : 'none';
    });
  });
});
```

### 23.5 Supplier Storefront Tabs (CRITICAL — inline style, not CSS class)

```javascript
function switchSupplierTab(btn, name) {
  document.querySelectorAll('.supplier-tab-btn').forEach(b => {
    b.classList.remove('border-emerald-500', 'text-emerald-600');
    b.classList.add('border-transparent', 'text-gray-500');
  });
  btn.classList.add('border-emerald-500', 'text-emerald-600');
  btn.classList.remove('border-transparent', 'text-gray-500');
  ['overview','products','reviews','rfq'].forEach(t => {
    const p = document.getElementById('supplier-tab-' + t);
    if (p) p.style.display = 'none';
  });
  const target = document.getElementById('supplier-tab-' + name);
  if (target) target.style.display = 'block';
}
// Init — show overview on load
document.addEventListener('DOMContentLoaded', function() {
  ['products','reviews','rfq'].forEach(t => {
    const p = document.getElementById('supplier-tab-' + t);
    if (p) p.style.display = 'none';
  });
});
```

### 23.6 FAQ Accordion

```javascript
function toggleFaq(btn) {
  const body = btn.nextElementSibling;
  const icon = btn.querySelector('.faq-icon');
  const isOpen = !body.classList.contains('hidden');
  document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
  document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = '');
  if (!isOpen) { body.classList.remove('hidden'); icon.style.transform = 'rotate(180deg)'; }
}
```

### 23.7 Pricing Toggle (Monthly / Annually)

```javascript
const prices = { pro: { monthly: '$49', annually: '$39' }, enterprise: { monthly: '$149', annually: '$119' } };
document.getElementById('billing-toggle')?.addEventListener('change', function() {
  const mode = this.checked ? 'annually' : 'monthly';
  document.getElementById('pro-price').textContent = prices.pro[mode];
  document.getElementById('ent-price').textContent = prices.enterprise[mode];
  document.getElementById('billing-label').textContent = mode === 'annually' ? 'Billed annually' : 'Billed monthly';
});
```

### 23.8 Table Search (Client-Side)

```javascript
document.getElementById('table-search')?.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#main-table tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
```

### 23.9 Table Sort

```javascript
function sortTable(th, colIndex) {
  const tbody = th.closest('table').querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));
  const asc = th.dataset.sort !== 'asc';
  th.dataset.sort = asc ? 'asc' : 'desc';
  th.closest('table').querySelectorAll('.sort-indicator').forEach(s => s.textContent = '↕');
  th.querySelector('.sort-indicator').textContent = asc ? '↑' : '↓';
  rows.sort((a, b) => {
    const av = a.cells[colIndex]?.textContent.trim() ?? '';
    const bv = b.cells[colIndex]?.textContent.trim() ?? '';
    return asc ? av.localeCompare(bv, undefined, {numeric:true}) : bv.localeCompare(av, undefined, {numeric:true});
  });
  rows.forEach(r => tbody.appendChild(r));
}
```

### 23.10 Checkbox Select All + Bulk Action Bar

```javascript
document.getElementById('check-all')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
  updateBulkBar();
});
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBar));
function updateBulkBar() {
  const n = document.querySelectorAll('.row-check:checked').length;
  const countEl = document.getElementById('selected-count');
  const btn = document.getElementById('bulk-delete');
  if (countEl) countEl.textContent = n > 0 ? n + ' selected' : '';
  if (btn) btn.classList.toggle('hidden', n === 0);
}
```

### 23.11 Export CSV

```javascript
function exportTableCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  const csv = Array.from(table.querySelectorAll('tr')).map(row =>
    Array.from(row.querySelectorAll('th, td'))
      .filter((_, i) => i !== 0)
      .map(cell => '"' + cell.textContent.trim().replace(/"/g, '""') + '"')
      .join(',')
  ).join('\n');
  const link = document.createElement('a');
  link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
  link.download = filename + '_' + new Date().toISOString().slice(0,10) + '.csv';
  link.click();
}
```

### 23.12 Export PDF (print-based, no external library)

```javascript
function exportTablePDF(tableId, title) {
  const table = document.getElementById(tableId).cloneNode(true);
  table.querySelectorAll('tr').forEach(row => row.cells[0]?.remove());
  const win = window.open('', '_blank');
  win.document.write(`<html><head><title>${title}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>*{font-family:Inter,sans-serif;font-size:13px;}h2{font-size:18px;margin-bottom:12px;color:#111;}
    table{border-collapse:collapse;width:100%;}
    th{background:#f3f4f6;color:#374151;font-weight:600;text-align:left;padding:8px 12px;font-size:11px;text-transform:uppercase;border-bottom:1px solid #e5e7eb;}
    td{padding:8px 12px;border-bottom:1px solid #f3f4f6;color:#374151;}
    p{color:#6b7280;font-size:12px;margin-bottom:16px;}</style></head>
    <body><h2>${title}</h2><p>Exported on ${new Date().toLocaleDateString()}</p>${table.outerHTML}</body></html>`);
  win.document.close();
  setTimeout(() => { win.print(); win.close(); }, 300);
}
```

### 23.13 Password Show/Hide

```javascript
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = document.getElementById(this.dataset.target);
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? '👁' : '🙈';
  });
});
```

### 23.14 Flash Message Auto-Dismiss (4 seconds)

```javascript
setTimeout(() => {
  const flash = document.getElementById('flash-msg');
  if (flash) { flash.style.opacity = '0'; setTimeout(() => flash.remove(), 300); }
}, 4000);
```

### 23.15 RFQ Table Live Search

```javascript
function filterRfqTable(q) {
  const lower = q.toLowerCase();
  document.querySelectorAll('#rfq-tbody .rfq-row').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(lower) ? '' : 'none';
  });
}
```

### 23.16 Register Account Type Toggle (Buyer / Supplier)

```javascript
function switchAccountType(type) {
  document.querySelectorAll('.account-type-btn').forEach(btn => {
    const isActive = btn.dataset.type === type;
    btn.classList.toggle('bg-emerald-500', isActive);
    btn.classList.toggle('text-white', isActive);
    btn.classList.toggle('bg-white', !isActive);
    btn.classList.toggle('text-gray-700', !isActive);
  });
  const companyField = document.getElementById('company-field');
  if (companyField) companyField.style.display = type === 'supplier' ? 'block' : 'none';
}
```

---

## 24. Form Security

Every POST form must include `@csrf`.

```php
// Rate limits
Route::post('/contact', ...)->middleware('throttle:5,1');
Route::post('/rfq',     ...)->middleware(['auth', 'throttle:10,1']);

// Form Requests for validation
class StoreRfqRequest extends FormRequest {
    public function rules() {
        return [
            'title'       => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:5000',
            'deadline'    => 'nullable|date|after:today',
            'quantity'    => 'nullable|integer|min:1',
            'budget'      => 'nullable|string|max:100',
            'attachment'  => 'nullable|file|max:10240',
        ];
    }
}

class StoreContactRequest extends FormRequest {
    public function rules() {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:255',
            'message'    => 'required|string|max:3000',
        ];
    }
}
```

Additional rules:
- All Blade output uses `{{ }}` (escaped) — never `{!! !!}` unless content is explicitly sanitized
- Sort keys on server must be from an allowlist (never inject raw request values into SQL)
- Never trust hidden form fields for supplier/listing ownership — always re-resolve server-side
- Prevent open redirects — only redirect to named routes after auth

---

## 25. SEO & Accessibility

### 29.1 Per-Page SEO

```blade
{{-- In each view --}}
@section('title', $supplier->name . ' — Edushopify Supplier')
@section('meta_description', Str::limit($supplier->about, 160))
```

```html
{{-- In app.blade.php head (already shown in Section 7) --}}
<meta name="description" content="@yield('meta_description', 'B2B education procurement marketplace.')">
<link rel="canonical" href="{{ url()->current() }}" />
<meta property="og:title" content="@yield('title', 'Edushopify')" />
```

### 29.2 Accessibility Requirements

- One clear `<h1>` per page
- All `<input>` elements have associated `<label for="">` or `aria-label`
- All icon-only buttons have `title` or `aria-label`
- Focus visible: `focus:ring-2 focus:ring-emerald-400` on all inputs and buttons
- Mobile drawer: Escape key closes, body scroll lock, visible close button
- Tables: `<thead>` with `<th scope="col">`
- Status badges: always include text label (not color-only)
- Images: meaningful `alt` text; decorative `alt=""`
- Tab order logical — no hidden or trapped focus

---

## 26. Testing Checklist

### Public Pages
- [ ] Homepage loads with real DB data (suppliers, events, stats)
- [ ] Stats bar values come from DB queries in production (not hardcoded)
- [ ] Featured suppliers: only active/eligible suppliers shown
- [ ] Category filter tabs on homepage correctly filter supplier grid
- [ ] Categories page shows active categories from DB
- [ ] Supplier directory filters work (category, country, verified)
- [ ] Supplier profile tabs switch correctly (uses inline style, not class)
- [ ] Product gallery image switcher works
- [ ] Product save toggle and share work
- [ ] RFQ table live search works
- [ ] RFQ detail deadline countdown works
- [ ] Blog filter tabs work

### Data Eligibility
- [ ] Only active suppliers appear publicly
- [ ] Only approved/active/published products appear publicly
- [ ] Only open + global RFQs appear on RFQ browse
- [ ] Only published reviews shown on supplier profiles

### Dashboards
- [ ] Pricing toggle switches monthly/annually prices correctly
- [ ] FAQ accordion opens/closes correctly
- [ ] RFQ quote form calculates total correctly
- [ ] Export CSV produces correct file (no checkbox column in output)
- [ ] Export PDF opens print dialog correctly
- [ ] Table sort works on all sortable columns

### Security
- [ ] CSRF token on all POST forms
- [ ] Rate limit blocks repeated contact form submissions
- [ ] Password show/hide works on auth pages
- [ ] Mobile menu slides from LEFT, closes on overlay click and Escape

### Responsive
- [ ] Homepage: 2-col cards mobile → 4-col desktop
- [ ] Supplier sidebar hidden on mobile (button-triggered)
- [ ] Navbar: hamburger mobile → full nav desktop
- [ ] Pricing cards: stacked mobile → 3-col desktop
- [ ] Tables: horizontally scrollable on mobile
- [ ] No horizontal page overflow at 320px, 375px, 390px
- [ ] Test at: 320 · 375 · 390 · 640 · 768 · 1024 · 1280 · 1440px+

### Regression
- [ ] Existing auth flows (login/register/logout) still work — not modified
- [ ] Existing backend dashboards (supplier/buyer/admin) still work — not modified
- [ ] No new routes conflict with existing auth or backend routes
- [ ] `resources/views/backend/` directory unchanged

---

## 27. Implementation Order

Build and test each phase before proceeding:

```
Phase 1 — Foundation
  frontend/layouts/app.blade.php
  frontend/partials: navbar, footer, mobile-menu, alerts, breadcrumb, pagination
  public/js/app.js (all JS patterns from Section 23)
  CSS design tokens (in layout head style block)

Phase 2 — Homepage
  HomeController@index
  frontend/home/index.blade.php (composition file — @includes only)
  frontend/home/sections/:
    _hero.blade.php
    _stats_bar.blade.php
    _featured_suppliers.blade.php
    _all_suppliers.blade.php
    _why_choose_edushopify.blade.php
    _events.blade.php
  frontend/partials/supplier-card.blade.php
  frontend/partials/supplier-card-featured.blade.php

Phase 3 — Categories
  CategoryController (index, show)
  frontend/categories/index.blade.php

Phase 4 — Suppliers
  SupplierController (index, show)
  frontend/suppliers/index.blade.php (filter sidebar + results grid)
  frontend/suppliers/show.blade.php (profile with 4 tabs)
  frontend/suppliers/partials/ (optional — overview, products, reviews, rfq)

Phase 5 — Products
  ProductController (show)
  frontend/products/show.blade.php
  frontend/products/partials/ (optional — gallery, sidebar, related)
  Gallery JS + spec table + sidebar quote panel + You May Also Like

Phase 6 — RFQ Pages
  RfqController (index, create, store, show)
  frontend/rfq/index.blade.php (table + live search)
  frontend/rfq/create.blade.php (3-step form)
  frontend/rfq/show.blade.php (detail + quote panel)
  frontend/rfq/partials/ (optional — info-card, quote-panel)

Phase 7 — Resources / Blog
  ResourceController (index, show)
  frontend/resources/index.blade.php
  frontend/resources/show.blade.php

Phase 8 — Static Pages
  PageController (about, contact, contactSend, pricing, terms, privacy)
  frontend/pages/about.blade.php
  frontend/pages/contact.blade.php
  frontend/pages/pricing.blade.php

Phase 9 — Hardening
  SEO metadata (@section('title') + meta description) on all pages
  Accessibility audit (keyboard, focus, ARIA, alt text)
  Responsive testing at all breakpoints (320 · 375 · 390 · 640 · 768 · 1024 · 1280 · 1440px+)
  Security pass (CSRF, rate limits, eligibility queries, sort allowlisting)
  Regression: verify existing auth, backend dashboards, and routes are untouched
```

---

## 28. Quick Class Reference

| Element | Classes |
|---|---|
| Primary button | `bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-md text-sm` |
| Secondary button | `border border-gray-200 hover:border-gray-300 text-gray-700 font-medium px-5 py-2.5 rounded-md text-sm hover:bg-gray-50` |
| Danger button | `border border-red-200 text-red-600 hover:bg-red-50 font-medium px-4 py-2 rounded-md text-sm` |
| Form input (Tailwind) | `border border-gray-200 rounded-md px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-emerald-400` |
| Form input (custom CSS) | `.form-input` |
| Section heading H2 | `text-2xl font-bold text-gray-900` |
| Section label | `text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-2` |
| Card | `border border-gray-200 rounded-lg bg-white p-4 hover:shadow-md transition-shadow` |
| Page container | `max-w-7xl mx-auto px-4 sm:px-6 py-10` |
| Table wrapper | `overflow-x-auto border border-gray-200 rounded-lg` |
| Table | `w-full text-sm` |
| Table head | `bg-gray-50 border-b border-gray-200` |
| Table th | `text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide` |
| Table td | `px-4 py-3 text-gray-700` |
| Table row | `hover:bg-gray-50` |
| Badge — verified | `.badge-verified rounded text-[10px] font-medium px-1.5 py-0.5` |
| Badge — founding | `.badge-founding rounded text-[10px] font-medium px-1.5 py-0.5` |
| Status — open | `bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-medium px-2 py-0.5 rounded` |
| Status — closed | `bg-gray-100 text-gray-500 text-xs font-medium px-2 py-0.5 rounded` |
| Status — pending | `bg-amber-50 text-amber-700 border border-amber-200 text-xs font-medium px-2 py-0.5 rounded` |
| Tag — active | `.tag-active text-xs font-semibold px-3 py-1.5 rounded-md` |
| Tag — inactive | `.tag-inactive text-xs font-semibold px-3 py-1.5 rounded-md` |
| Stars | `.star` → color: #f59e0b |
| Empty state | `text-center py-16` |
| Breadcrumb | `flex items-center gap-2 text-sm text-gray-500 mb-6` |
| Hero section | `relative h-[420px] overflow-hidden` |
| Hero overlay | `.hero-overlay` (left-to-right dark gradient — ONLY allowed gradient) |
| Stats bar | `bg-emerald-500 py-5` |
| Alt section background | `bg-gray-50` |
| Divider | `border-t border-gray-100 my-5` |
| Step indicator — active | `w-7 h-7 rounded bg-emerald-500 text-white text-sm font-bold flex items-center justify-center` |
| Step indicator — inactive | `w-7 h-7 rounded bg-gray-100 text-gray-400 text-sm font-bold flex items-center justify-center` |
| Tab btn — active | `border-b-2 border-emerald-500 text-emerald-600` |
| Tab btn — inactive | `border-b-2 border-transparent text-gray-500 hover:text-gray-900` |

