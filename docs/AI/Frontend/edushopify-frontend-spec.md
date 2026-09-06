# Edushopify — Frontend Design Specification
> Laravel + Tailwind CDN · Vanilla JS · Mobile-first · No Alpine, no Livewire

---

## Table of Contents
1. [Design System](#1-design-system)
2. [Shared Layout: Navbar + Footer](#2-shared-layout)
3. [Home Page](#3-home-page)
4. [Categories Page](#4-categories-page)
5. [Suppliers Listing Page](#5-suppliers-listing-page)
6. [Supplier Profile Page](#6-supplier-profile-page)
7. [RFQ Pages (Post + Browse + Detail)](#7-rfq-pages)
8. [Resources / Blog Page](#8-resources--blog-page)
9. [Blog Post Detail Page](#9-blog-post-detail-page)
10. [About Page](#10-about-page)
11. [Contact Page](#11-contact-page)
12. [Pricing Page](#12-pricing-page)
17. [Mobile Menu](#17-mobile-menu)
18. [Reusable Components](#18-reusable-components)
19. [JavaScript Patterns](#19-javascript-patterns)
20. [Laravel Blade Structure](#20-laravel-blade-structure)

---

## 1. Design System

### 1.1 Color Palette
```
Primary Green    #10b981  (emerald-500)  — CTAs, links, active states, badges
Primary Dark     #059669  (emerald-600)  — Hover states
Primary Light    #6ee7b7  (emerald-300)  — Logo accent, soft fills
Accent Amber     #f59e0b                 — Star ratings
Text Primary     #111827  (gray-900)     — Headings
Text Secondary   #374151  (gray-700)     — Body copy
Text Muted       #6b7280  (gray-500)     — Labels, captions
Border           #e5e7eb  (gray-200)     — All borders, dividers
Surface          #f3f4f6  (gray-100)     — Page background, inactive tags
White            #ffffff                 — Cards, navbar, footer
```

**Rule:** No gradients on UI surfaces. The only gradient allowed is the dark overlay on hero/event image backgrounds (rgba black). Keep all UI elements flat with borders and subtle shadows.

### 1.2 Typography
```
Font Family:   Inter (Google Fonts — already loaded)
Base size:     14px / text-sm
Body:          16px / text-base / font-normal / text-gray-700 / leading-relaxed
Headings:
  h1:  text-4xl lg:text-5xl / font-extrabold / text-gray-900
  h2:  text-2xl lg:text-3xl / font-bold      / text-gray-900
  h3:  text-xl               / font-semibold  / text-gray-900
  h4:  text-base             / font-semibold  / text-gray-900
Labels/caps: text-xs / font-semibold / uppercase / tracking-widest / text-emerald-600
Caption:     text-xs / text-gray-400
```

### 1.3 Spacing & Sizing
```
Section vertical padding:   py-12 lg:py-16
Container:                  max-w-7xl mx-auto px-4 sm:px-6
Card padding:               p-4 lg:p-5
Card gap (grid):            gap-4
Input height:               h-10 (py-2 px-3)
Button height:              h-9 to h-10
```

### 1.4 Border Radius Rules
```
Cards:          rounded-lg   (8px)
Buttons:        rounded-md   (6px)
Inputs:         rounded-md   (6px)
Badges/pills:   rounded      (4px)  — NOT rounded-full
Tags:           rounded-md   (6px)
Images in cards: rounded-md  (6px)
Avatars (initials): rounded  (4px)
Table rows:     no radius
Modals:         rounded-lg   (8px)
```

### 1.5 Shadow Scale
```
Card default:  none (border only)
Card hover:    shadow-md  (0 4px 20px rgba(0,0,0,.08))
Navbar:        border-b border-gray-200
Dropdown:      shadow-lg border border-gray-100
Modal:         shadow-xl
```

### 1.6 Badge / Tag Classes (custom CSS in app.css or <style>)
```css
.badge-verified  { background:#e8f5ee; color:#1a7f45; border:1px solid #b6e0c6; }
.badge-founding  { background:#fff8e6; color:#a16207; border:1px solid #fde68a; }
.badge-ise       { background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; }
.badge-new       { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.badge-featured  { background:#fef9c3; color:#854d0e; border:1px solid #fef08a; }
.tag-active      { background:#111827; color:#fff; }
.tag-inactive    { background:#f3f4f6; color:#374151; }
.star            { color:#f59e0b; }
```

---

## 2. Shared Layout

### 2.1 Navbar
**File:** `resources/views/partials/navbar.blade.php`

**Desktop (lg+):**
```
[Logo]  [Search bar — flex-1 max-w-md]  [Categories] [RFQ] [Suppliers] [Resources]  [♡] [💬] [🔔]  [Sign In]  [Register btn]
```

**Structure:**
```html
<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-6 h-14">

    <!-- Hamburger (mobile only) -->
    <button id="menu-open" class="lg:hidden text-gray-600 hover:text-gray-900 mr-1">
      <!-- 3-line icon -->
    </button>

    <!-- Logo -->
    <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
      <!-- 3x3 SVG dot grid -->
      <span class="font-bold text-gray-900 text-[17px]">
        Edu<span class="text-emerald-500">shopify</span>
      </span>
      <span class="text-[9px] text-gray-400 hidden sm:block">Connecting Education</span>
    </a>

    <!-- Search (hidden on mobile, shown sm+) -->
    <div class="flex-1 max-w-md relative hidden sm:flex">
      <input type="text" placeholder="Search suppliers, products, categories..."
        class="w-full border border-gray-200 rounded-md text-sm px-3 py-2 pr-9 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      <button class="absolute right-0 top-0 h-full px-3 bg-emerald-500 rounded-r-md">
        <!-- search icon -->
      </button>
    </div>

    <!-- Nav (desktop) -->
    <nav class="hidden lg:flex items-center gap-5 ml-2">
      <a href="{{ route('categories.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Categories</a>
      <a href="{{ route('rfq.index') }}"        class="text-sm font-medium text-gray-600 hover:text-gray-900">RFQ</a>
      <a href="{{ route('suppliers.index') }}"  class="text-sm font-medium text-gray-600 hover:text-gray-900">Suppliers</a>
      <a href="{{ route('resources.index') }}"  class="text-sm font-medium text-gray-600 hover:text-gray-900">Resources</a>
    </nav>

    <!-- Right icons -->
    <div class="ml-auto flex items-center gap-3">
      <!-- Wishlist, Chat, Notification icons — hidden on mobile -->
      <div class="hidden sm:flex items-center gap-2">
        <button class="text-gray-500 hover:text-gray-800"><!-- heart --></button>
        <button class="text-gray-500 hover:text-gray-800"><!-- chat --></button>
        <button class="text-gray-500 hover:text-gray-800 relative">
          <!-- bell + notification dot -->
          <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-emerald-500 rounded-full"></span>
        </button>
      </div>
      @guest
        <a href="{{ route('login') }}"    class="hidden sm:inline text-sm font-medium text-gray-700">Sign In</a>
        <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-md">Register</a>
      @else
        <!-- Avatar dropdown -->
        <div class="relative" id="user-menu">
          <button onclick="toggleUserMenu()" class="w-8 h-8 rounded bg-emerald-100 text-emerald-700 font-semibold text-sm flex items-center justify-center">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </button>
          <div id="user-dropdown" class="hidden absolute right-0 top-10 w-44 bg-white border border-gray-100 rounded-lg shadow-lg py-1 z-50">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Dashboard</a>
            <a href="{{ route('profile') }}"   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
            <hr class="my-1 border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Sign Out</button>
            </form>
          </div>
        </div>
      @endguest
    </div>
  </div>
</header>
```

**Active link:** Add `text-emerald-600 font-semibold` to the current route link using `{{ request()->routeIs('route.name') ? 'text-emerald-600 font-semibold' : 'text-gray-600' }}`.

---

### 2.2 Footer
**File:** `resources/views/partials/footer.blade.php`

**Layout:** 4-column grid (brand + 3 link columns), then a bottom bar.

```html
<footer class="bg-white border-t border-gray-200 pt-14 pb-8 px-4">
  <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

    <!-- Brand -->
    <div>
      <!-- 3x3 dot SVG + wordmark + tagline -->
      <p class="text-sm text-gray-500 leading-relaxed mt-4 max-w-[260px]">
        The B2B education procurement marketplace connecting institutional buyers with verified suppliers worldwide.
      </p>
    </div>

    <!-- Marketplace links -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Marketplace</p>
      <ul class="space-y-4 text-sm text-gray-500">
        <li><a href="{{ route('suppliers.index') }}"  class="hover:text-gray-900">Browse Suppliers</a></li>
        <li><a href="{{ route('products.index') }}"   class="hover:text-gray-900">Products</a></li>
        <li><a href="{{ route('categories.index') }}" class="hover:text-gray-900">Categories</a></li>
        <li><a href="{{ route('rfq.create') }}"       class="hover:text-gray-900">Request Quote</a></li>
      </ul>
    </div>

    <!-- Company links -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Company</p>
      <ul class="space-y-4 text-sm text-gray-500">
        <li><a href="{{ route('about') }}"   class="hover:text-gray-900">About Us</a></li>
        <li><a href="{{ route('blog') }}"    class="hover:text-gray-900">Blog</a></li>
        <li><a href="{{ route('contact') }}" class="hover:text-gray-900">Contact</a></li>
        <li><a href="/careers"               class="hover:text-gray-900">Careers</a></li>
      </ul>
    </div>

    <!-- Support links -->
    <div>
      <p class="font-semibold text-[15px] text-gray-900 mb-5">Support</p>
      <ul class="space-y-4 text-sm text-gray-500">
        <li><a href="/help"                    class="hover:text-gray-900">Help Center</a></li>
        <li><a href="{{ route('pricing') }}"   class="hover:text-gray-900">Pricing</a></li>
        <li><a href="/terms"                   class="hover:text-gray-900">Terms of Service</a></li>
        <li><a href="/privacy"                 class="hover:text-gray-900">Privacy Policy</a></li>
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

## 3. Home Page

**Route:** `GET /`
**View:** `resources/views/home.blade.php`
**Controller:** `HomeController@index`

### Sections (top → bottom)

#### 3.1 Hero
```
Full-width image background (h-[420px])
Dark overlay left-to-right (rgba 0,0,0 .7 → .05) via inline style or hero-overlay class
Left-aligned content block:
  - Eyebrow: none
  - H1: "Global Suppliers for Education. All in One Place."
  - Subtext: "Connect with verified education suppliers worldwide"
  - Two buttons: [Find Suppliers — white bg] [Post an RFQ — emerald bg]
```

```html
<section class="relative h-[420px] overflow-hidden">
  <img src="..." class="absolute inset-0 w-full h-full object-cover" />
  <div class="hero-overlay absolute inset-0"></div>
  <div class="relative max-w-7xl mx-auto px-6 h-full flex flex-col justify-center">
    <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight max-w-xl">
      Global Suppliers for<br>Education. All in One Place.
    </h1>
    <p class="text-white/80 mt-3 text-base max-w-sm">Connect with verified education suppliers worldwide</p>
    <div class="flex gap-3 mt-7 flex-wrap">
      <a href="{{ route('suppliers.index') }}"
        class="bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-md text-sm hover:bg-gray-100">
        Find Suppliers
      </a>
      <a href="{{ route('rfq.create') }}"
        class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-md text-sm">
        Post an RFQ
      </a>
    </div>
  </div>
</section>
```

#### 3.2 Stats Bar (NEW — between hero and featured suppliers)
```
4 stats in a row: [8,000+ Suppliers] [50+ Countries] [12,000+ Products] [2,400+ Verified Buyers]
Background: bg-emerald-500
Text: white
```
```html
<div class="bg-emerald-500 py-5">
  <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-white">
    <div><p class="text-2xl font-bold">8,000+</p><p class="text-sm opacity-80 mt-0.5">Suppliers</p></div>
    <div><p class="text-2xl font-bold">50+</p><p class="text-sm opacity-80 mt-0.5">Countries</p></div>
    <div><p class="text-2xl font-bold">12,000+</p><p class="text-sm opacity-80 mt-0.5">Products</p></div>
    <div><p class="text-2xl font-bold">2,400+</p><p class="text-sm opacity-80 mt-0.5">Verified Buyers</p></div>
  </div>
</div>
```

#### 3.3 Featured Suppliers
- Section heading row: "Featured Suppliers" (left) + "See all →" link + chevron nav (right)
- 4-column card grid (2 on mobile)
- Each card: image top, badge overlay, heart button, supplier name with initial avatar, badges, type, country flag, rating, product count, "View Profile →"

#### 3.4 All Suppliers (with filter tabs)
- Heading row: "All Suppliers" + "View all →"
- Filter tabs (horizontal scroll on mobile): All Categories · STEM & Robotics · AV & Display · Furniture · Lab Equipment · Software & LMS + Filter button
- 2 rows × 4 columns of compact list cards (no image, initial avatar, badges, rating, country, product count)

#### 3.5 Why Choose Edushopify
- Background: `bg-gray-50`
- Small label: "OUR ADVANTAGE" in emerald
- H2: "Why Choose Edushopify"
- Subtext below heading
- 4 icon pillars in a row (2×2 on mobile):
  - Verified Suppliers / Global Reach / RFQ Opportunities / Secure & Reliable
  - Each: icon in a `bg-emerald-50` circle + bold label + short description

#### 3.6 Events
- Small label: "DON'T MISS OUT"
- H2: "Education STEM & Robotics Events"
- "View all →" right-aligned
- 4 event cards (2 on mobile): image with dark overlay, colored category badge, title, date, location

---

## 4. Categories Page

**Route:** `GET /categories`
**View:** `resources/views/categories/index.blade.php`

### Layout
```
Page header (white bg, border-b):
  - Breadcrumb: Home > Categories
  - H1: "Browse by Category"
  - Subtext

Main grid (3 or 4 columns, 2 on mobile):
  Each category card:
  ┌─────────────────────────┐
  │  [icon — large, emerald]│
  │  Category Name          │
  │  248 Suppliers          │
  │  → Browse               │
  └─────────────────────────┘
  Border: border border-gray-200 rounded-lg
  Hover: bg-gray-50 border-gray-300
  Icon bg: w-12 h-12 bg-emerald-50 rounded-md flex items-center justify-center
```

**Categories list:**
- STEM & Robotics
- AV & Display
- Furniture & Seating
- Lab Equipment
- Software & LMS
- Publishing & Content
- Sports & PE
- Safety & Security
- Art & Craft Supplies
- Special Needs
- School Administration
- EdTech Hardware

---

## 5. Suppliers Listing Page

**Route:** `GET /suppliers`
**View:** `resources/views/suppliers/index.blade.php`

### Layout
```
Two-column layout on lg+:
  LEFT (sidebar — w-64 shrink-0):   Filters
  RIGHT (main — flex-1):            Results grid + pagination
```

### Sidebar Filters
```html
<aside class="w-full lg:w-64 shrink-0">
  <div class="border border-gray-200 rounded-lg p-4 sticky top-20">

    <!-- Filter group: Category -->
    <div class="mb-5">
      <p class="text-sm font-semibold text-gray-900 mb-3">Category</p>
      @foreach($categories as $cat)
      <label class="flex items-center gap-2 py-1 cursor-pointer">
        <input type="checkbox" name="category[]" value="{{ $cat->id }}"
          class="w-4 h-4 rounded accent-emerald-500">
        <span class="text-sm text-gray-600">{{ $cat->name }}</span>
        <span class="ml-auto text-xs text-gray-400">{{ $cat->suppliers_count }}</span>
      </label>
      @endforeach
    </div>

    <hr class="border-gray-100 mb-5">

    <!-- Country -->
    <div class="mb-5">
      <p class="text-sm font-semibold text-gray-900 mb-3">Country</p>
      <select class="w-full border border-gray-200 rounded-md text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none">
        <option>All Countries</option>
        <!-- options -->
      </select>
    </div>

    <hr class="border-gray-100 mb-5">

    <!-- Badges -->
    <div class="mb-5">
      <p class="text-sm font-semibold text-gray-900 mb-3">Supplier Type</p>
      <label class="flex items-center gap-2 py-1 cursor-pointer">
        <input type="checkbox" class="w-4 h-4 rounded accent-emerald-500">
        <span class="text-sm text-gray-600">Verified Only</span>
      </label>
      <label class="flex items-center gap-2 py-1 cursor-pointer">
        <input type="checkbox" class="w-4 h-4 rounded accent-emerald-500">
        <span class="text-sm text-gray-600">Founding Suppliers</span>
      </label>
      <label class="flex items-center gap-2 py-1 cursor-pointer">
        <input type="checkbox" class="w-4 h-4 rounded accent-emerald-500">
        <span class="text-sm text-gray-600">ISE Exhibitors</span>
      </label>
    </div>

    <button onclick="applyFilters()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2 rounded-md">
      Apply Filters
    </button>
    <button onclick="clearFilters()" class="w-full text-sm text-gray-500 hover:text-gray-800 py-2 mt-1">
      Clear All
    </button>
  </div>
</aside>
```

### Results Area
```html
<div class="flex-1">
  <!-- Sort + count bar -->
  <div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">Showing <strong>248</strong> suppliers</p>
    <select class="border border-gray-200 rounded-md text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-400">
      <option>Sort: Relevance</option>
      <option>Sort: Rating</option>
      <option>Sort: Newest</option>
      <option>Sort: Most Products</option>
    </select>
  </div>

  <!-- Grid: 3 cols on lg, 2 on sm, 1 on mobile -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($suppliers as $supplier)
      @include('partials.supplier-card', compact('supplier'))
    @endforeach
  </div>

  <!-- Pagination -->
  <div class="mt-8">{{ $suppliers->links('partials.pagination') }}</div>
</div>
```

---

## 6. Supplier Profile Page

**Route:** `GET /suppliers/{slug}`
**View:** `resources/views/suppliers/show.blade.php`

### Layout
```
HERO BANNER:
  background image (h-48 object-cover rounded-lg overflow-hidden)
  Verified badge overlay bottom-left

PROFILE HEADER (below banner, -mt-10 position):
  [Avatar — 80×80 rounded-md border-4 border-white shadow-md bg-emerald-100]
  Name / Type / Country / Badges
  Stats: [X Products] [X Reviews] [Since YYYY]
  CTA buttons: [Send Inquiry — emerald] [Save Supplier — outline] [Download Profile PDF]

TABS (sticky below header):
  Overview | Products | Reviews | RFQ Requests

TAB CONTENT PANELS:
  Overview:
    - About text
    - Categories served (badge list)
    - Certifications grid
    - Key contacts table

  Products:
    - Filter by category (tabs)
    - Product grid (image, name, MOQ, price range, "Request Quote" btn)

  Reviews:
    - Average rating display (big star + score + count)
    - Review list (avatar, name, date, rating, comment)
    - "Write a Review" form (collapsible)

  RFQ:
    - Inline RFQ form (see RFQ section)
```

---

## 7. RFQ Pages

### 7.1 Browse RFQs
**Route:** `GET /rfq`
**View:** `resources/views/rfq/index.blade.php`

```
Page header: "Open RFQs" + "Post an RFQ" button

Filter bar: Category | Budget Range | Deadline | Status
Table:
  Columns: Title | Category | Budget | Deadline | Responses | Status | Action
  Rows: clickable → detail page
  Status badges: Open (green), Closed (gray), In Review (amber)
```

### 7.2 Post RFQ Form
**Route:** `GET/POST /rfq/create`
**View:** `resources/views/rfq/create.blade.php`

```html
<form method="POST" action="{{ route('rfq.store') }}" enctype="multipart/form-data">
  @csrf

  <!-- Step indicator: 1 of 3 -->
  <div class="flex items-center gap-0 mb-8">
    <div class="flex items-center gap-2">
      <span class="w-7 h-7 rounded bg-emerald-500 text-white text-sm font-bold flex items-center justify-center">1</span>
      <span class="text-sm font-medium text-gray-900">Requirements</span>
    </div>
    <div class="flex-1 h-px bg-gray-200 mx-3"></div>
    <div class="flex items-center gap-2">
      <span class="w-7 h-7 rounded bg-gray-100 text-gray-400 text-sm font-bold flex items-center justify-center">2</span>
      <span class="text-sm text-gray-400">Details</span>
    </div>
    <div class="flex-1 h-px bg-gray-200 mx-3"></div>
    <div class="flex items-center gap-2">
      <span class="w-7 h-7 rounded bg-gray-100 text-gray-400 text-sm font-bold flex items-center justify-center">3</span>
      <span class="text-sm text-gray-400">Submit</span>
    </div>
  </div>

  <!-- Form fields -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <div class="lg:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-1">RFQ Title <span class="text-red-500">*</span></label>
      <input type="text" name="title" placeholder="e.g. Interactive Whiteboards for 50 Classrooms"
        class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
      <select name="category_id" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        <option value="">Select category</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
      <input type="number" name="quantity" placeholder="e.g. 50"
        class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Budget (USD)</label>
      <input type="text" name="budget" placeholder="e.g. 5,000 – 10,000"
        class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
      <input type="date" name="deadline"
        class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
    </div>

    <div class="lg:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
      <textarea name="description" rows="5" placeholder="Describe your requirements in detail..."
        class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 resize-none"></textarea>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Destination Country</label>
      <select name="country" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        <option>Select country</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Attach File (optional)</label>
      <input type="file" name="attachment"
        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
    </div>
  </div>

  <div class="flex items-center justify-between mt-8 pt-5 border-t border-gray-100">
    <a href="{{ route('rfq.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Cancel</a>
    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-2.5 rounded-md text-sm">
      Submit RFQ
    </button>
  </div>
</form>
```

### 7.3 RFQ Detail Page
```
Title + Status badge (top)
Two-column:
  LEFT (2/3): Description, specs, attachments
  RIGHT (1/3): Info card (Category, Budget, Deadline, Country, Posted by)
               "Submit Quote" button (for suppliers)
               Quote submissions list (for buyer — collapsed by default)
```

---

## 8. Resources / Blog Page

**Route:** `GET /resources`
**View:** `resources/views/resources/index.blade.php`

### Layout
```
Page header: H2 "Resources & Insights" + filter tabs [All | Guides | News | Events | Case Studies]

Featured article (full-width card):
  - Large image left (lg:w-1/2) + content right
  - Category badge + H3 + excerpt + author/date + "Read More →"

Article grid (3 cols on lg, 2 on sm, 1 on mobile):
  Each card:
  - Image top (h-44 object-cover rounded-md)
  - Category badge (inline)
  - H4 title
  - Excerpt (2-line clamp: overflow-hidden max-h-[2.8rem])
  - Author avatar (initial) + name + date
  - "Read More →" emerald link

Sidebar (lg — sticky):
  - Newsletter signup (input + button)
  - Popular tags
  - Upcoming events (small list)
```

---

## 9. Blog Post Detail Page

**Route:** `GET /resources/{slug}`
**View:** `resources/views/resources/show.blade.php`

```
Breadcrumb: Home > Resources > Article Title

Two-column layout (lg):
  LEFT (article — 2/3):
    - Category badge + H1 + author row (avatar + name + date + read time)
    - Hero image (full-width, rounded-md)
    - Article body (prose text-gray-700 leading-relaxed)
    - Tags row
    - Share buttons row
    - Author bio card (border rounded-lg p-4)

  RIGHT (sidebar — 1/3 sticky):
    - Related articles (3 items — image + title)
    - Newsletter box
    - CTA: "Are you a supplier? Register →"
```

---

## 10. About Page

**Route:** `GET /about`
**View:** `resources/views/about.blade.php`

### Sections

#### 10.1 Hero (no image — text-based)
```
bg-gray-50 py-20
Label: "ABOUT EDUSHOPIFY"
H1: "Connecting the World's Education Buyers with Trusted Suppliers"
Subtext: mission statement paragraph
```

#### 10.2 Mission & Vision (2-col)
```
LEFT: Mission — icon + heading + paragraph
RIGHT: Vision — icon + heading + paragraph
Separator: border-l border-gray-200 on lg
```

#### 10.3 Stats Row
```
4 stats: Suppliers, Countries, Products, Buyers — same pattern as home stats bar
bg: white, border-y border-gray-100, text-gray-900
```

#### 10.4 Team Grid
```
H2: "Our Team"
Grid: 3 cols on lg, 2 on sm
Each card:
  - Avatar initials (large, 64×64, bg-emerald-100 text-emerald-700 rounded)
  - Name (font-semibold)
  - Role (text-sm text-gray-500)
  - LinkedIn icon link
border border-gray-200 rounded-lg p-5 text-center
```

#### 10.5 Values (3 cards)
```
bg-gray-50
Cards in a 3-col grid, each:
  - Icon in emerald-50 bg rounded-md
  - Title
  - Description
  - No border, no shadow — bg-white rounded-lg p-5
```

#### 10.6 CTA Banner
```
bg-gray-900 text-white text-center py-16
H2: "Ready to Grow Your Education Business?"
2 buttons: [Register as Supplier — emerald] [Browse Suppliers — white outline]
```

---

## 11. Contact Page

**Route:** `GET/POST /contact`
**View:** `resources/views/contact.blade.php`

### Layout (2-col on lg)

#### Left: Info
```
H2: "Get in Touch"
Subtext

3 info blocks (each: icon + label + value):
  - Email:    hello@edushopify.com
  - Phone:    +1 (888) 000-0000
  - Location: Global — HQ in London, UK

Office hours block:
  Mon–Fri: 9:00 AM – 6:00 PM GMT

Social links row (icons only)
```

#### Right: Form
```html
<form method="POST" action="{{ route('contact.send') }}">
  @csrf

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
      <input type="text" name="first_name" class="form-input w-full" />
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
      <input type="text" name="last_name" class="form-input w-full" />
    </div>
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
    <input type="email" name="email" class="form-input w-full" />
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">I am a...</label>
    <select name="role" class="form-input w-full">
      <option>Buyer / Institution</option>
      <option>Supplier / Manufacturer</option>
      <option>Press / Media</option>
      <option>Other</option>
    </select>
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
    <input type="text" name="subject" class="form-input w-full" />
  </div>

  <div class="mb-5">
    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
    <textarea name="message" rows="5" class="form-input w-full resize-none"></textarea>
  </div>

  <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-md text-sm">
    Send Message
  </button>
</form>
```

**Form input shared style (add to CSS):**
```css
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
```

---

## 12. Pricing Page

**Route:** `GET /pricing`
**View:** `resources/views/pricing.blade.php`

### Layout

#### 12.1 Header
```
Text-center, py-16
Label: "PRICING"
H1: "Simple, Transparent Pricing"
Toggle: [Billed Monthly] ●———● [Billed Annually — save 20%]  (JS toggle)
```

#### 12.2 Pricing Table (3 tiers)
```
Grid: 3 cols on lg, 1 on mobile (stacked)
```

```html
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">

  <!-- Starter -->
  <div class="border border-gray-200 rounded-lg p-6">
    <p class="text-sm font-semibold text-gray-500 mb-1">Starter</p>
    <p class="text-3xl font-extrabold text-gray-900 mb-1">$0</p>
    <p class="text-sm text-gray-400 mb-6">Free forever</p>
    <ul class="space-y-3 mb-8 text-sm text-gray-600">
      <li class="flex items-center gap-2">
        <span class="text-emerald-500">✓</span> Up to 10 product listings
      </li>
      <li class="flex items-center gap-2">
        <span class="text-emerald-500">✓</span> Basic profile page
      </li>
      <li class="flex items-center gap-2">
        <span class="text-emerald-500">✓</span> 5 RFQ responses/month
      </li>
      <li class="flex items-center gap-2">
        <span class="text-gray-300">✗</span> <span class="text-gray-400">Analytics dashboard</span>
      </li>
      <li class="flex items-center gap-2">
        <span class="text-gray-300">✗</span> <span class="text-gray-400">Featured placement</span>
      </li>
    </ul>
    <a href="{{ route('register') }}" class="block text-center border border-gray-200 hover:border-emerald-400 text-gray-900 font-semibold py-2.5 rounded-md text-sm">
      Get Started Free
    </a>
  </div>

  <!-- Professional — highlighted -->
  <div class="border-2 border-emerald-500 rounded-lg p-6 relative">
    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-emerald-500 text-white text-xs font-semibold px-3 py-1 rounded">
      Most Popular
    </span>
    <p class="text-sm font-semibold text-emerald-600 mb-1">Professional</p>
    <div class="flex items-end gap-1 mb-1">
      <p class="text-3xl font-extrabold text-gray-900" id="pro-price">$49</p>
      <p class="text-sm text-gray-400 mb-1">/ month</p>
    </div>
    <p class="text-sm text-gray-400 mb-6">Billed monthly</p>
    <ul class="space-y-3 mb-8 text-sm text-gray-600">
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Unlimited listings</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Enhanced profile + media</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Unlimited RFQ responses</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Analytics dashboard</li>
      <li class="flex items-center gap-2"><span class="text-gray-300">✗</span> <span class="text-gray-400">Featured placement</span></li>
    </ul>
    <a href="{{ route('register') }}" class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-md text-sm">
      Start Free Trial
    </a>
  </div>

  <!-- Enterprise -->
  <div class="border border-gray-200 rounded-lg p-6">
    <p class="text-sm font-semibold text-gray-500 mb-1">Enterprise</p>
    <p class="text-3xl font-extrabold text-gray-900 mb-1">$149</p>
    <p class="text-sm text-gray-400 mb-6">Per month, billed annually</p>
    <ul class="space-y-3 mb-8 text-sm text-gray-600">
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Everything in Pro</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Featured homepage placement</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Dedicated account manager</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Priority RFQ matching</li>
      <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Custom integrations</li>
    </ul>
    <a href="{{ route('contact') }}" class="block text-center border border-gray-200 hover:border-emerald-400 text-gray-900 font-semibold py-2.5 rounded-md text-sm">
      Contact Sales
    </a>
  </div>
</div>
```

#### 12.3 Feature Comparison Table
```html
<div class="max-w-5xl mx-auto mt-16 overflow-x-auto">
  <table class="w-full text-sm border-collapse">
    <thead>
      <tr class="border-b border-gray-200">
        <th class="text-left py-3 px-4 font-semibold text-gray-900">Feature</th>
        <th class="text-center py-3 px-4 font-semibold text-gray-900">Starter</th>
        <th class="text-center py-3 px-4 font-semibold text-emerald-600">Professional</th>
        <th class="text-center py-3 px-4 font-semibold text-gray-900">Enterprise</th>
      </tr>
    </thead>
    <tbody>
      @foreach([
        ['Product Listings', '10', 'Unlimited', 'Unlimited'],
        ['RFQ Responses/mo', '5', 'Unlimited', 'Unlimited'],
        ['Analytics', '—', '✓', '✓'],
        ['Featured Placement', '—', '—', '✓'],
        ['Account Manager', '—', '—', '✓'],
      ] as [$feature, $s, $p, $e])
      <tr class="border-b border-gray-100 hover:bg-gray-50">
        <td class="py-3 px-4 text-gray-700">{{ $feature }}</td>
        <td class="py-3 px-4 text-center text-gray-500">{{ $s }}</td>
        <td class="py-3 px-4 text-center text-emerald-600 font-medium">{{ $p }}</td>
        <td class="py-3 px-4 text-center text-gray-700">{{ $e }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
```

#### 12.4 FAQ Accordion (below table)
```html
<div class="max-w-2xl mx-auto mt-16" id="faq">
  @foreach($faqs as $faq)
  <div class="border-b border-gray-200">
    <button onclick="toggleFaq(this)" class="w-full text-left py-4 flex items-center justify-between text-sm font-semibold text-gray-900">
      {{ $faq['q'] }}
      <svg class="w-4 h-4 text-gray-400 faq-icon transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="m6 9 6 6 6-6"/>
      </svg>
    </button>
    <div class="faq-body hidden pb-4 text-sm text-gray-600 leading-relaxed">
      {{ $faq['a'] }}
    </div>
  </div>
  @endforeach
</div>
```

---

## 13. Auth Pages

### 13.1 Sign In
**Route:** `GET/POST /login`
**View:** `resources/views/auth/login.blade.php`

```
Two-column layout on lg:
  LEFT (hidden on mobile): brand panel — bg-gray-900 text-white, logo, tagline, 3 trust points
  RIGHT: form panel — centered, max-w-sm mx-auto py-16

Form:
  - H2: "Welcome back"
  - Subtext: "Sign in to your account"
  - Email field
  - Password field + show/hide toggle (JS)
  - "Forgot password?" link (right-aligned)
  - [Sign In] button (full width, emerald)
  - Divider: "— OR —"
  - Google sign in button (border, white bg, Google icon)
  - "Don't have an account? Register" link
```

### 13.2 Register
**Route:** `GET/POST /register`

```
Two-column same as login

Form:
  - H2: "Create your account"
  - Account type toggle: [Buyer] [Supplier]  (JS — shows/hides extra fields)
  - Full Name
  - Email
  - Organization / Company Name  (shown when Supplier)
  - Country select
  - Password + confirm password
  - Terms checkbox
  - [Create Account] button
  - "Already have an account? Sign In" link
```

---

## 14. Supplier Dashboard

**Route:** `GET /dashboard/supplier`
**View:** `resources/views/dashboard/supplier.blade.php`
**Middleware:** `auth, role:supplier`

### Layout
```
Sidebar (fixed on lg, drawer on mobile):
  Logo + nav items with icons:
    - Overview
    - My Profile
    - Products
    - RFQ Inbox
    - Analytics
    - Subscription
    - Settings

Main content area: flex-1, overflow-y-auto, bg-gray-50
Top bar: "Good morning, [Name]" + date + notification bell
```

### 14.1 Overview Tab
```
Stats cards row (4 cards):
┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
│ Profile Views    │ │ RFQ Received     │ │ Active Listings  │ │ Reviews          │
│ 1,248            │ │ 34               │ │ 67               │ │ 4.8 ★ (128)     │
│ ↑ 12% this week │ │ ↑ 8 new          │ │                  │ │                  │
└──────────────────┘ └──────────────────┘ └──────────────────┘ └──────────────────┘

Each card: bg-white border border-gray-200 rounded-lg p-5
Number: text-2xl font-bold text-gray-900
Label: text-sm text-gray-500
Trend: text-xs text-emerald-600 (positive) / text-red-500 (negative)

Below stats:
  LEFT (2/3): Recent RFQ Inbox (table — Title, Buyer, Date, Status, Action)
  RIGHT (1/3): Profile completion widget (progress bar) + Quick actions list
```

### 14.2 Products Tab
```
Top bar: "My Products (67)" + [+ Add Product] button

Table with export:
┌──────┬─────────────────┬──────────────┬────────┬────────┬─────────┐
│ img  │ Product Name    │ Category     │ MOQ    │ Status │ Actions │
├──────┼─────────────────┼──────────────┼────────┼────────┼─────────┤
│  ○   │ Interactive...  │ AV & Display │ 10 pcs │ Active │ Edit Del│
└──────┴─────────────────┴──────────────┴────────┴────────┴─────────┘

Export buttons: [Export CSV] [Export PDF]  (see JS patterns below)
Bulk action: checkbox column + "Delete Selected" button
Pagination: simple prev/next
```

### 14.3 RFQ Inbox Tab
```
Filter tabs: All | New | Responded | Closed

Table:
  RFQ Title | Buyer | Category | Budget | Deadline | Status | [View] btn
  Clicking row → slide-in panel from right with full RFQ detail + response form
```

### 14.4 Analytics Tab
```
Date range picker (start/end date inputs)

Row 1: Line chart placeholder (use a simple SVG sparkline or canvas)
  - Profile views over 30 days

Row 2 (2-col):
  - Top product views (bar list: name + view count bar)
  - Traffic sources (simple table: source, visits, %)
```

### 14.5 Subscription Tab
```
Current plan card (bordered, shows plan name, renewal date, usage)
Plan comparison table (same 3-tier as pricing page)
[Upgrade] / [Manage Billing] buttons
Invoice table (Date | Plan | Amount | Status | Download PDF)
```

---

## 15. Buyer Dashboard

**Route:** `GET /dashboard/buyer`
**View:** `resources/views/dashboard/buyer.blade.php`

### Sidebar nav items
- Overview
- My RFQs
- Saved Suppliers
- Messages
- Account Settings

### 15.1 My RFQs Tab
```
[+ Post New RFQ] button top-right

Table:
  Title | Category | Posted | Deadline | Responses | Status | Actions
  Status: Open (emerald badge) | Closed (gray) | Draft (amber)
  Actions: View | Edit | Close | Delete

Export: [Export CSV]
```

### 15.2 Saved Suppliers Tab
```
Grid (3 cols lg, 2 sm, 1 mobile) — same compact supplier card
[Remove from saved] icon button on each card
```

---

## 16. Admin Dashboard

**Route:** `GET /admin`
**View:** `resources/views/admin/dashboard.blade.php`
**Middleware:** `auth, role:admin`

### Sidebar nav items
- Overview
- Suppliers
- Buyers
- RFQs
- Products
- Reviews
- Subscriptions
- Reports
- Settings

### 16.1 Overview
```
Stats row (6 cards, wrap to 3 on mobile):
  Total Suppliers | Total Buyers | Active RFQs | Revenue (MTD) | Pending Approvals | Flagged Reviews

Below:
  LEFT: Recent signups table (Name | Type | Country | Date | Status | [Approve/Reject])
  RIGHT: Platform health (API uptime, storage used, email queue — simple stat list)
```

### 16.2 Suppliers Table (full page)
```
Search input + Filter (Verified | Unverified | Suspended) + Country select

Table with bulk actions:
┌──┬──────────────────┬──────────────┬─────────┬─────────────┬────────┬──────────┐
│☐ │ Supplier Name    │ Category     │ Country │ Plan        │ Status │ Actions  │
├──┼──────────────────┼──────────────┼─────────┼─────────────┼────────┼──────────┤
│☐ │ LEGO Education   │ STEM         │ Denmark │ Enterprise  │ Active │ Edit View│
└──┴──────────────────┴──────────────┴─────────┴─────────────┴────────┴──────────┘

Export row above table: [Export CSV] [Export PDF] [Print]
Pagination: showing 1-20 of 248
```

### 16.3 Table with Export Pattern (reusable)
```html
<!-- Export controls -->
<div class="flex items-center justify-between mb-4">
  <div class="flex items-center gap-2">
    <input type="text" placeholder="Search..." id="table-search"
      class="border border-gray-200 rounded-md px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
    <!-- filter selects -->
  </div>
  <div class="flex items-center gap-2">
    <button onclick="exportTableCSV('main-table', 'suppliers')"
      class="border border-gray-200 text-sm px-3 py-1.5 rounded-md hover:bg-gray-50 flex items-center gap-1.5">
      <!-- download icon --> Export CSV
    </button>
    <button onclick="exportTablePDF('main-table', 'Suppliers')"
      class="border border-gray-200 text-sm px-3 py-1.5 rounded-md hover:bg-gray-50 flex items-center gap-1.5">
      <!-- pdf icon --> Export PDF
    </button>
    <button onclick="window.print()"
      class="border border-gray-200 text-sm px-3 py-1.5 rounded-md hover:bg-gray-50 flex items-center gap-1.5">
      <!-- print icon --> Print
    </button>
  </div>
</div>

<!-- Table -->
<div class="overflow-x-auto border border-gray-200 rounded-lg">
  <table class="w-full text-sm" id="main-table">
    <thead class="bg-gray-50 border-b border-gray-200">
      <tr>
        <th class="w-8 px-4 py-3">
          <input type="checkbox" id="check-all" class="w-4 h-4 rounded accent-emerald-500" />
        </th>
        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide cursor-pointer hover:text-gray-900" onclick="sortTable(this, 1)">
          Name <span class="sort-indicator">↕</span>
        </th>
        <!-- more th -->
        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @foreach($rows as $row)
      <tr class="hover:bg-gray-50">
        <td class="px-4 py-3">
          <input type="checkbox" class="row-check w-4 h-4 rounded accent-emerald-500" value="{{ $row->id }}" />
        </td>
        <!-- cells -->
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

<!-- Pagination + bulk action bar -->
<div class="flex items-center justify-between mt-4">
  <div class="flex items-center gap-3">
    <span class="text-sm text-gray-500" id="selected-count"></span>
    <button id="bulk-delete" class="hidden text-sm text-red-500 hover:text-red-700 border border-red-200 px-3 py-1 rounded-md">
      Delete Selected
    </button>
  </div>
  <div class="flex items-center gap-1">
    <!-- prev/next buttons -->
    {{ $rows->links('partials.pagination') }}
  </div>
</div>
```

---

## 17. Mobile Menu

**Behavior:** Slide in from the LEFT on hamburger click. Overlay darkens the page. Close with × button or overlay click.

```html
<!-- Overlay -->
<div id="menu-overlay" class="fixed inset-0 bg-black/40 z-40 hidden" onclick="closeMenu()"></div>

<!-- Drawer -->
<div id="mobile-menu"
  class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-xl transform -translate-x-full transition-transform duration-300">

  <!-- Header -->
  <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
    <!-- Logo -->
    <a href="/" class="flex items-center gap-2">
      <!-- SVG dot grid (smaller) -->
      <span class="font-bold text-gray-900">Edu<span class="text-emerald-500">shopify</span></span>
    </a>
    <button onclick="closeMenu()" class="text-gray-500 hover:text-gray-900">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M6 18 18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <!-- Search (mobile) -->
  <div class="px-4 py-3 border-b border-gray-100">
    <input type="text" placeholder="Search suppliers..."
      class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
  </div>

  <!-- Nav items -->
  <nav class="px-4 py-4 flex flex-col gap-1">
    <a href="/categories" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
      <!-- icon --> Categories
    </a>
    <a href="/rfq"        class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">RFQ</a>
    <a href="/suppliers"  class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Suppliers</a>
    <a href="/resources"  class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Resources</a>
    <a href="/pricing"    class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Pricing</a>
    <a href="/about"      class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">About</a>
    <a href="/contact"    class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Contact</a>
  </nav>

  <!-- Auth buttons -->
  <div class="px-4 pt-2 border-t border-gray-100 mt-auto">
    <a href="/login"    class="block text-center py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900">Sign In</a>
    <a href="/register" class="block text-center py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-md">Register</a>
  </div>
</div>
```

---

## 18. Reusable Components

### 18.1 Supplier Card (compact — list view)
**File:** `resources/views/partials/supplier-card.blade.php`
```html
<div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
  <div class="flex items-start justify-between mb-2">
    <div class="flex items-center gap-2.5">
      <span class="w-9 h-9 rounded bg-{{ $supplier->color }}-100 text-{{ $supplier->color }}-700 font-bold text-sm flex items-center justify-center shrink-0">
        {{ strtoupper($supplier->name[0]) }}
      </span>
      <div>
        <p class="font-semibold text-sm text-gray-900 leading-tight">{{ $supplier->name }}</p>
        <p class="text-xs text-gray-500">{{ $supplier->type }}</p>
      </div>
    </div>
    <button class="text-gray-300 hover:text-red-400 transition-colors"><!-- heart --></button>
  </div>

  <!-- Badges -->
  <div class="flex flex-wrap gap-1 mb-2">
    @if($supplier->is_verified)
      <span class="badge-verified text-[10px] font-medium px-1.5 py-0.5 rounded inline-flex items-center gap-1">✓ Verified</span>
    @endif
    @if($supplier->is_founding)
      <span class="badge-founding text-[10px] font-medium px-1.5 py-0.5 rounded">Founding</span>
    @endif
  </div>

  <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
    <span><span class="star">★</span> {{ $supplier->rating }} <span class="text-gray-400">({{ $supplier->review_count }})</span></span>
    <span>{{ $supplier->country_flag }} {{ $supplier->country }}</span>
  </div>
  <div class="flex items-center justify-between text-xs">
    <span class="text-gray-400">🛍 {{ $supplier->product_count }}+ Products</span>
    <a href="{{ route('suppliers.show', $supplier->slug) }}" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
  </div>
</div>
```

### 18.2 Alert / Flash Messages
**File:** `resources/views/partials/alerts.blade.php`
```html
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 text-sm mb-4 flex items-center gap-2" id="flash-msg">
  <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
  {{ session('success') }}
  <button onclick="document.getElementById('flash-msg').remove()" class="ml-auto text-emerald-600 hover:text-emerald-800">✕</button>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-md px-4 py-3 text-sm mb-4 flex items-center gap-2">
  <!-- error icon -->
  {{ session('error') }}
</div>
@endif
```

### 18.3 Breadcrumb
```html
<nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
  <a href="/" class="hover:text-gray-900">Home</a>
  <span class="text-gray-300">/</span>
  @foreach($crumbs as $label => $url)
    @if($loop->last)
      <span class="text-gray-900 font-medium">{{ $label }}</span>
    @else
      <a href="{{ $url }}" class="hover:text-gray-900">{{ $label }}</a>
      <span class="text-gray-300">/</span>
    @endif
  @endforeach
</nav>
```

### 18.4 Empty State
```html
<div class="text-center py-16">
  <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
    <!-- icon -->
  </div>
  <p class="font-semibold text-gray-900 mb-1">No suppliers found</p>
  <p class="text-sm text-gray-500 mb-5">Try adjusting your filters or search terms.</p>
  <button onclick="clearFilters()" class="text-sm text-emerald-600 hover:underline">Clear all filters</button>
</div>
```

### 18.5 Custom Pagination
**File:** `resources/views/partials/pagination.blade.php`
```html
@if($paginator->hasPages())
<div class="flex items-center justify-center gap-1 mt-6">
  @if($paginator->onFirstPage())
    <span class="px-3 py-1.5 text-sm text-gray-300 border border-gray-200 rounded-md cursor-not-allowed">← Prev</span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50">← Prev</a>
  @endif

  @foreach($elements as $element)
    @if(is_string($element))
      <span class="px-3 py-1.5 text-sm text-gray-400">...</span>
    @endif
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

---

## 19. JavaScript Patterns

All JS is vanilla — no Alpine, no Livewire. Place in `public/js/app.js` and include in layout.

### 19.1 Mobile Menu
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
document.getElementById('menu-open').addEventListener('click', openMenu);
```

### 19.2 User Dropdown
```javascript
function toggleUserMenu() {
  document.getElementById('user-dropdown').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
  const menu = document.getElementById('user-menu');
  if (menu && !menu.contains(e.target)) {
    document.getElementById('user-dropdown').classList.add('hidden');
  }
});
```

### 19.3 Category / Filter Tabs
```javascript
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    // Remove active from all
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('tag-active');
      b.classList.add('tag-inactive');
    });
    // Set active
    this.classList.remove('tag-inactive');
    this.classList.add('tag-active');
    // Filter items
    const cat = this.dataset.cat;
    document.querySelectorAll('.supplier-item').forEach(item => {
      if (cat === 'all' || item.dataset.cat === cat) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  });
});
```

### 19.4 FAQ Accordion
```javascript
function toggleFaq(btn) {
  const body = btn.nextElementSibling;
  const icon = btn.querySelector('.faq-icon');
  const isOpen = !body.classList.contains('hidden');
  // Close all
  document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
  document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = '');
  // Open clicked (unless was already open)
  if (!isOpen) {
    body.classList.remove('hidden');
    icon.style.transform = 'rotate(180deg)';
  }
}
```

### 19.5 Pricing Toggle (Monthly / Annually)
```javascript
const prices = { pro: { monthly: '$49', annually: '$39' }, enterprise: { monthly: '$149', annually: '$119' } };
document.getElementById('billing-toggle').addEventListener('change', function() {
  const mode = this.checked ? 'annually' : 'monthly';
  document.getElementById('pro-price').textContent = prices.pro[mode];
  document.getElementById('ent-price').textContent = prices.enterprise[mode];
  document.getElementById('billing-label').textContent = mode === 'annually' ? 'Billed annually' : 'Billed monthly';
});
```

### 19.6 Table Search (client-side)
```javascript
document.getElementById('table-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#main-table tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
```

### 19.7 Table Sort
```javascript
function sortTable(th, colIndex) {
  const table = th.closest('table');
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));
  const asc = th.dataset.sort !== 'asc';
  th.dataset.sort = asc ? 'asc' : 'desc';
  // Update indicators
  table.querySelectorAll('.sort-indicator').forEach(s => s.textContent = '↕');
  th.querySelector('.sort-indicator').textContent = asc ? '↑' : '↓';
  rows.sort((a, b) => {
    const aVal = a.cells[colIndex]?.textContent.trim() ?? '';
    const bVal = b.cells[colIndex]?.textContent.trim() ?? '';
    return asc ? aVal.localeCompare(bVal, undefined, {numeric: true}) : bVal.localeCompare(aVal, undefined, {numeric: true});
  });
  rows.forEach(r => tbody.appendChild(r));
}
```

### 19.8 Checkbox Select All
```javascript
document.getElementById('check-all')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
  updateBulkBar();
});
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBar));
function updateBulkBar() {
  const checked = document.querySelectorAll('.row-check:checked').length;
  const countEl = document.getElementById('selected-count');
  const bulkBtn = document.getElementById('bulk-delete');
  if (countEl) countEl.textContent = checked > 0 ? `${checked} selected` : '';
  if (bulkBtn) bulkBtn.classList.toggle('hidden', checked === 0);
}
```

### 19.9 Export CSV
```javascript
function exportTableCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  const rows = Array.from(table.querySelectorAll('tr'));
  const csv = rows.map(row => {
    return Array.from(row.querySelectorAll('th, td'))
      .filter((_, i) => i !== 0)               // skip checkbox column
      .map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`)
      .join(',');
  }).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `${filename}_${new Date().toISOString().slice(0,10)}.csv`;
  link.click();
}
```

### 19.10 Export PDF (print-based, no external lib)
```javascript
function exportTablePDF(tableId, title) {
  const table = document.getElementById(tableId).cloneNode(true);
  // Remove checkbox column
  table.querySelectorAll('tr').forEach(row => row.cells[0]?.remove());
  const win = window.open('', '_blank');
  win.document.write(`
    <html><head>
    <title>${title}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
      * { font-family: Inter, sans-serif; font-size: 13px; }
      h2 { font-size: 18px; margin-bottom: 12px; color: #111; }
      table { border-collapse: collapse; width: 100%; }
      th { background: #f3f4f6; color: #374151; font-weight: 600; text-align: left; padding: 8px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb; }
      td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }
      tr:last-child td { border: none; }
      p { color: #6b7280; font-size: 12px; margin-bottom: 16px; }
    </style></head>
    <body>
      <h2>${title}</h2>
      <p>Exported on ${new Date().toLocaleDateString()}</p>
      ${table.outerHTML}
    </body></html>
  `);
  win.document.close();
  setTimeout(() => { win.print(); win.close(); }, 300);
}
```

### 19.11 Password Show/Hide
```javascript
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = document.getElementById(this.dataset.target);
    input.type = input.type === 'password' ? 'text' : 'password';
    this.innerHTML = input.type === 'password' ? eyeIcon : eyeOffIcon;
  });
});
```

### 19.12 Flash Message Auto-dismiss
```javascript
setTimeout(() => {
  const flash = document.getElementById('flash-msg');
  if (flash) flash.style.opacity = '0', setTimeout(() => flash.remove(), 300);
}, 4000);
```

---

## 20. Laravel Blade Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← public layout (navbar + footer)
│   └── dashboard.blade.php    ← sidebar dashboard layout
├── partials/
│   ├── navbar.blade.php
│   ├── footer.blade.php
│   ├── mobile-menu.blade.php
│   ├── alerts.blade.php
│   ├── breadcrumb.blade.php
│   ├── pagination.blade.php
│   ├── supplier-card.blade.php
│   └── supplier-card-featured.blade.php
├── home.blade.php
├── about.blade.php
├── contact.blade.php
├── pricing.blade.php
├── categories/
│   └── index.blade.php
├── suppliers/
│   ├── index.blade.php
│   └── show.blade.php
├── rfq/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
├── resources/
│   ├── index.blade.php
│   └── show.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
└── dashboard/
    ├── supplier/
    │   ├── overview.blade.php
    │   ├── products.blade.php
    │   ├── rfq.blade.php
    │   ├── analytics.blade.php
    │   └── subscription.blade.php
    ├── buyer/
    │   ├── overview.blade.php
    │   ├── rfqs.blade.php
    │   └── saved.blade.php
    └── admin/
        ├── overview.blade.php
        ├── suppliers.blade.php
        ├── buyers.blade.php
        ├── rfqs.blade.php
        ├── products.blade.php
        ├── reviews.blade.php
        └── subscriptions.blade.php
```

### layouts/app.blade.php skeleton
```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Edushopify — Global Education Suppliers')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    /* Design tokens */
    * { font-family: 'Inter', sans-serif; }
    .badge-verified  { background:#e8f5ee; color:#1a7f45; border:1px solid #b6e0c6; }
    .badge-founding  { background:#fff8e6; color:#a16207; border:1px solid #fde68a; }
    .badge-ise       { background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; }
    .tag-active      { background:#111827; color:#fff; }
    .tag-inactive    { background:#f3f4f6; color:#374151; }
    .star            { color:#f59e0b; }
    .hero-overlay    { background: linear-gradient(90deg,rgba(0,0,0,.72) 0%,rgba(0,0,0,.3) 60%,rgba(0,0,0,.05) 100%); }
    .form-input      { border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; width:100%; outline:none; }
    .form-input:focus { box-shadow:0 0 0 2px #a7f3d0; border-color:#10b981; }
    #mobile-menu     { transition: transform 0.3s ease; }
    @yield('styles')
  </style>
</head>
<body class="bg-white text-gray-800 antialiased">

  @include('partials.mobile-menu')
  @include('partials.navbar')

  <main>
    @include('partials.alerts')
    @yield('content')
  </main>

  @include('partials.footer')

  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>
```

### layouts/dashboard.blade.php skeleton
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- same head as app.blade.php -->
  <title>@yield('title') — Edushopify Dashboard</title>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <aside id="sidebar" class="w-56 shrink-0 bg-white border-r border-gray-200 flex flex-col
       fixed lg:static inset-y-0 left-0 z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">

    <!-- Logo -->
    <div class="px-4 py-4 border-b border-gray-100">
      <a href="/" class="flex items-center gap-2"><!-- logo --></a>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-0.5">
      @foreach($navItems as $item)
      <a href="{{ $item['url'] }}"
        class="flex items-center gap-3 px-3 py-2 text-sm rounded-md
          {{ request()->routeIs($item['route']) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
        {!! $item['icon'] !!}
        {{ $item['label'] }}
      </a>
      @endforeach
    </nav>

    <!-- User -->
    <div class="px-4 py-4 border-t border-gray-100">
      <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
      <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
    </div>
  </aside>

  <!-- Main -->
  <div class="flex-1 flex flex-col overflow-hidden">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shrink-0">
      <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-gray-800"><!-- hamburger --></button>
      <p class="text-sm font-medium text-gray-700">@yield('page-title')</p>
      <!-- notification + user icons -->
    </header>

    <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
      @include('partials.alerts')
      @yield('content')
    </main>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@yield('scripts')
</body>
</html>
```

---

## Quick Reference: Key Classes

| Purpose | Classes |
|---|---|
| Primary button | `bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-md text-sm` |
| Secondary button | `border border-gray-200 hover:border-gray-300 text-gray-700 font-medium px-5 py-2.5 rounded-md text-sm hover:bg-gray-50` |
| Danger button | `border border-red-200 text-red-600 hover:bg-red-50 font-medium px-4 py-2 rounded-md text-sm` |
| Form input | `border border-gray-200 rounded-md px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-emerald-400` |
| Section heading | `text-2xl font-bold text-gray-900` |
| Section label | `text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-2` |
| Card | `border border-gray-200 rounded-lg bg-white p-4 hover:shadow-md transition-shadow` |
| Table | `w-full text-sm border-collapse` |
| Table head | `bg-gray-50 border-b border-gray-200` |
| Table th | `text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide` |
| Table td | `px-4 py-3 text-gray-700` |
| Table row hover | `hover:bg-gray-50` |
| Status badge — active | `bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-medium px-2 py-0.5 rounded` |
| Status badge — inactive | `bg-gray-100 text-gray-500 text-xs font-medium px-2 py-0.5 rounded` |
| Status badge — pending | `bg-amber-50 text-amber-700 border border-amber-200 text-xs font-medium px-2 py-0.5 rounded` |
| Divider | `border-t border-gray-100 my-5` |
| Empty state container | `text-center py-16` |
| Page container | `max-w-7xl mx-auto px-4 sm:px-6 py-10` |
