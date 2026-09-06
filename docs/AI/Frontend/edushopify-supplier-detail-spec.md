# Edushopify — Supplier Detail Page Design Specification
> Laravel + Tailwind CDN · Vanilla JS · Mobile-first · No Alpine, no Livewire

---

## Table of Contents
1. [Page Overview](#1-page-overview)
2. [CSS / Style Tokens](#2-css--style-tokens)
3. [Navbar](#3-navbar)
4. [Hero Banner + Stats Bar](#4-hero-banner--stats-bar)
5. [Tab Bar](#5-tab-bar)
6. [Two-Column Layout](#6-two-column-layout)
7. [Tab: About Us](#7-tab-about-us)
8. [Tab: Products](#8-tab-products)
9. [Tab: Services](#9-tab-services)
10. [Tab: Videos](#10-tab-videos)
11. [Tab: Certifications](#11-tab-certifications)
12. [Tab: Reviews](#12-tab-reviews)
13. [Tab: Contact](#13-tab-contact)
14. [Right Sidebar](#14-right-sidebar)
15. [You May Also Like](#15-you-may-also-like)
16. [Footer](#16-footer)
17. [JavaScript](#17-javascript)
18. [Laravel Blade Structure](#18-laravel-blade-structure)
19. [Mobile Behaviour](#19-mobile-behaviour)
20. [Quick Class Reference](#20-quick-class-reference)

---

## 1. Page Overview

**Route:** `GET /suppliers/{slug}`
**View:** `resources/views/suppliers/show.blade.php`
**Layout:** `layouts/app.blade.php`
**Controller:** `SupplierController@show`

**Page flow (top → bottom):**
```
[Navbar — sticky]
[Hero Banner — 280px tall image with overlay identity + CTA buttons]
[Stats Bar — directly attached below hero, inside same rounded card]
[Tab Bar — 7 tabs: About Us · Products · Services · Videos · Certifications · Reviews · Contact]
[Two-column: LEFT (tab content) + RIGHT (sticky sidebar)]
[You May Also Like — full-width, below two-column, visible on every tab]
[Footer]
```

**Column sizing:**
- Left: `lg:flex-1 lg:max-w-[calc(100%-335px)]` — takes remaining space, capped
- Right: `lg:w-[310px] xl:w-[330px]` — fixed wider sidebar
- Gap: `gap-5`

**Body background:** `bg-gray-50`

---

## 2. CSS / Style Tokens

```css
* { font-family: 'Inter', sans-serif; }

/* ── Navbar ── */
.nav-link       { font-size:14px; font-weight:500; color:#374151; padding:4px 10px; }
.nav-link:hover { color:#111; }

/* ── Stars ── */
.star-filled { color:#f59e0b; }
.star-empty  { color:#d1d5db; }

/* ── Badges ── */
.badge-verified { background:#e8f5ee; color:#1a7f45; border:1px solid #b6e0c6;
                  border-radius:99px; font-size:11px; font-weight:600; padding:3px 10px;
                  display:inline-flex; align-items:center; gap:4px; }
.badge-bett     { background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe;
                  border-radius:99px; font-size:11px; font-weight:600; padding:3px 10px; }
.badge-founding { background:#fff8e6; color:#a16207; border:1px solid #fde68a;
                  border-radius:99px; font-size:10px; font-weight:600; padding:2px 8px; }
.badge-ise      { background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe;
                  border-radius:99px; font-size:10px; font-weight:600; padding:2px 8px; }

/* ── Hero + Stats ── */
.hero-wrap        { border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;
                    box-shadow:0 1px 8px rgba(0,0,0,.06); margin-bottom:16px; }
.hero-banner      { position:relative; height:280px; }
.hero-banner img  { width:100%; height:100%; object-fit:cover; object-position:center top; }
.hero-save-btn    { position:absolute; top:14px; right:14px; width:34px; height:34px;
                    background:rgba(255,255,255,.92); border-radius:50%;
                    display:flex; align-items:center; justify-content:center;
                    cursor:pointer; border:1px solid rgba(0,0,0,.08);
                    box-shadow:0 1px 4px rgba(0,0,0,.15); }
.profile-bar      { position:absolute; bottom:0; left:0; right:0; padding:18px 24px;
                    display:flex; align-items:flex-end; justify-content:space-between; gap:12px;
                    background:linear-gradient(to top,rgba(0,0,0,.65) 0%,rgba(0,0,0,.2) 55%,rgba(0,0,0,0) 100%); }
.supplier-avatar  { width:60px; height:60px; border-radius:10px; background:#fff;
                    display:flex; align-items:center; justify-content:center;
                    font-size:26px; font-weight:800; color:#374151;
                    flex-shrink:0; box-shadow:0 2px 10px rgba(0,0,0,.2); }

/* ── Stats bar ── */
.stats-bar                        { background:#fff; border-top:1px solid #e5e7eb; }
.stat-col                         { text-align:center; padding:14px 8px; }
.stat-col:not(:last-child)        { border-right:1px solid #f3f4f6; }
.stat-col .val                    { font-size:20px; font-weight:800; color:#111827; line-height:1; }
.stat-col .lbl                    { font-size:11px; color:#9ca3af; margin-top:4px; }

/* ── Tab bar ── */
.tab-bar                { background:#fff; border:1px solid #e5e7eb; border-radius:10px;
                          display:flex; overflow-x:auto; scrollbar-width:none; }
.tab-bar::-webkit-scrollbar { display:none; }
.tab-btn                { flex:none; padding:14px 22px; font-size:14px; font-weight:500;
                          color:#6b7280; cursor:pointer; white-space:nowrap;
                          border-bottom:2px solid transparent; transition:color .15s,border-color .15s; }
.tab-btn:hover          { color:#111827; }
.tab-btn.active         { color:#10b981; border-bottom-color:#10b981; font-weight:600; }

/* ── Tab panels — JS controls via inline style ── */
.tab-panel { display:none; }

/* ── Section card (shared by all tab panels) ── */
.sec-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px 22px; }

/* ── Featured product card (About tab) ── */
.fp-card       { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;
                 background:#fff; cursor:pointer; transition:box-shadow .15s; }
.fp-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.08); }

/* ── Products tab card ── */
.pt-card       { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
                 background:#fff; transition:box-shadow .15s; }
.pt-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.08); }

/* ── Service icon wrap ── */
.svc-icon { width:38px; height:38px; border-radius:8px; background:#f0fdf4;
            display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* ── Service tag pill ── */
.svc-tag { border:1px solid #d1d5db; border-radius:4px; font-size:11px;
           font-weight:500; color:#374151; padding:2px 8px; display:inline-block; }

/* ── Certification pill (About tab + Industry Partnerships) ── */
.cert-pill { border:1px solid #d1d5db; border-radius:6px; padding:7px 16px;
             font-size:13px; font-weight:500; color:#374151; background:#fff; }

/* ── Certification card (Certifications tab) ── */
.cert-card  { border:1px solid #e5e7eb; border-radius:10px; padding:16px;
              background:#fff; display:flex; gap:12px; align-items:flex-start; }
.cert-icon  { width:36px; height:36px; border-radius:8px; background:#f0fdf4;
              display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* ── Video card ── */
.vid-card       { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;
                  background:#fff; transition:box-shadow .15s; }
.vid-card:hover { box-shadow:0 3px 12px rgba(0,0,0,.08); }

/* ── Review card ── */
.rev-card { border:1px solid #e5e7eb; border-radius:10px; padding:16px; background:#fff; }
.rev-av   { width:36px; height:36px; border-radius:8px; display:flex; align-items:center;
            justify-content:center; font-size:14px; font-weight:700; flex-shrink:0; }

/* ── Contact tile ── */
.contact-tile  { border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px;
                 display:flex; align-items:center; gap:12px; background:#fff; }
.contact-icon  { width:36px; height:36px; border-radius:8px; background:#f0fdf4;
                 display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* ── Social icon (Contact tab) ── */
.soc-icon       { width:34px; height:34px; border:1px solid #e5e7eb; border-radius:8px;
                  display:flex; align-items:center; justify-content:center;
                  cursor:pointer; background:#fff; transition:border-color .15s,color .15s; }
.soc-icon:hover { border-color:#10b981; color:#10b981; }

/* ── Sidebar card ── */
.side-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:16px; }

/* ── Share button (sidebar) ── */
.share-btn       { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:8px;
                   display:flex; align-items:center; justify-content:center;
                   color:#6b7280; cursor:pointer; background:#f9fafb;
                   transition:border-color .15s,color .15s; }
.share-btn:hover { border-color:#10b981; color:#10b981; background:#f0fdf4; }

/* ── You May Also Like supplier card ── */
.supp-card       { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
                   background:#fff; transition:box-shadow .2s; }
.supp-card:hover { box-shadow:0 4px 18px rgba(0,0,0,.09); }
.supp-av         { width:34px; height:34px; border-radius:8px; display:flex;
                   align-items:center; justify-content:center; font-size:15px;
                   font-weight:700; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,.1); }

/* ── Business hours row ── */
.h-row        { display:flex; justify-content:space-between; font-size:13px; padding:4px 0; }
.h-row .closed { color:#ef4444; font-weight:500; }
```

---

## 3. Navbar

Identical to all other pages. **Suppliers** nav link is slightly bold/active.

```html
<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-4 h-14">
    <!-- Logo: 3×3 SVG dot grid + Edushopify wordmark -->
    <!-- Search bar (hidden mobile) -->
    <!-- Nav: Categories · RFQ · Suppliers (active) · Resources -->
    <!-- Right: heart · chat · bell icons + Sign In + Register btn -->
  </div>
</header>
```

---

## 4. Hero Banner + Stats Bar

Both are wrapped in `.hero-wrap` — a single rounded bordered box. Stats attach to the bottom of the hero with only a top border (no separate card).

### 4.1 Hero Banner

```html
<div class="hero-wrap">
  <div class="hero-banner">
    <img src="{{ $supplier->banner_image }}" alt="{{ $supplier->name }}" />

    <!-- Heart/save button — top-right -->
    <button class="hero-save-btn">
      <!-- heart icon, w-4 h-4 text-gray-500 -->
    </button>

    <!-- Identity overlay — bottom of banner -->
    <div class="profile-bar">

      <!-- LEFT: Avatar + name + badges + meta -->
      <div class="flex items-end gap-3">
        <div class="supplier-avatar">
          {{ strtoupper(substr($supplier->name, 0, 1)) }}
        </div>
        <div>
          <h1 class="text-white font-bold text-2xl leading-tight mb-1.5">
            {{ $supplier->name }}
          </h1>
          <!-- Badges row -->
          <div class="flex flex-wrap items-center gap-2 mb-2">
            @if($supplier->is_verified)
              <span class="badge-verified">
                <!-- check icon w-3 h-3 -->
                Verified Supplier
              </span>
            @endif
            @foreach($supplier->exhibit_badges as $badge)
              <span class="badge-bett">{{ $badge }}</span>
            @endforeach
          </div>
          <!-- Meta row: location · since · products · rating -->
          <div class="flex flex-wrap items-center gap-4 text-[12px] text-white/85">
            <span class="flex items-center gap-1.5"><!-- pin icon --> {{ $supplier->country }}</span>
            <span class="flex items-center gap-1.5"><!-- clock icon --> Since {{ $supplier->founded_year }}</span>
            <span class="flex items-center gap-1.5"><!-- briefcase icon --> {{ $supplier->products_count }} Products</span>
            <span class="flex items-center gap-1.5">
              <span class="star-filled">★</span>
              {{ $supplier->avg_rating }}
              <span class="text-white/70">({{ $supplier->reviews_count }} Reviews)</span>
            </span>
          </div>
        </div>
      </div>

      <!-- RIGHT: CTA buttons (hidden on mobile) -->
      <div class="hidden sm:flex flex-col gap-2 shrink-0">
        <button class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-5 py-2.5 rounded-md flex items-center gap-2">
          <!-- document icon --> Request Quotation
        </button>
        <button class="bg-white/90 hover:bg-white text-gray-800 text-sm font-medium px-5 py-2.5 rounded-md flex items-center gap-2 border border-white/40">
          <!-- chat icon --> Contact Supplier
        </button>
      </div>

    </div>
  </div><!-- /hero-banner -->

  <!-- Stats bar — directly attached, border-top only -->
  <div class="stats-bar">
    <div class="grid grid-cols-5 divide-x divide-gray-100">
      <div class="stat-col"><p class="val">{{ $supplier->response_rate }}%</p><p class="lbl">Response Rate</p></div>
      <div class="stat-col"><p class="val">{{ $supplier->avg_response_time }}</p><p class="lbl">Avg Response Time</p></div>
      <div class="stat-col"><p class="val">{{ $supplier->rfqs_completed }}+</p><p class="lbl">RFQs Completed</p></div>
      <div class="stat-col"><p class="val">{{ $supplier->countries_served }}+</p><p class="lbl">Countries Served</p></div>
      <div class="stat-col"><p class="val">{{ $supplier->years_in_business }}+</p><p class="lbl">Years in Business</p></div>
    </div>
  </div>

</div><!-- /hero-wrap -->
```

**Key design notes:**
- `profile-bar` gradient: `linear-gradient(to top, rgba(0,0,0,.65) 0%, rgba(0,0,0,.2) 55%, transparent 100%)`
- Avatar: white square `60×60 border-radius:10px` with large initial letter
- Stats: 5 equal columns, `divide-x divide-gray-100`, no outer border (already from `hero-wrap`)

---

## 5. Tab Bar

```html
<div class="tab-bar mb-5">
  <button class="tab-btn active" onclick="switchTab(this,'about')">About Us</button>
  <button class="tab-btn" onclick="switchTab(this,'products')">Products</button>
  <button class="tab-btn" onclick="switchTab(this,'services')">Services</button>
  <button class="tab-btn" onclick="switchTab(this,'videos')">Videos</button>
  <button class="tab-btn" onclick="switchTab(this,'certifications')">Certifications</button>
  <button class="tab-btn" onclick="switchTab(this,'reviews')">Reviews</button>
  <button class="tab-btn" onclick="switchTab(this,'contact')">Contact</button>
</div>
```

**Tab button sizing:** `padding:14px 22px` · `font-size:14px`
**Active:** `color:#10b981` · `border-bottom:2px solid #10b981` · `font-weight:600`
**Mobile:** horizontal scroll, no scrollbar visible

---

## 6. Two-Column Layout

```html
<div class="flex flex-col lg:flex-row gap-5 items-start">

  <!-- LEFT: Tab content -->
  <div class="w-full lg:flex-1 lg:max-w-[calc(100%-335px)] min-w-0 flex flex-col gap-4">
    <!-- Tab panels injected here -->
  </div>

  <!-- RIGHT: Sticky sidebar -->
  <div class="w-full lg:w-[310px] xl:w-[330px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">
    <!-- Sidebar cards injected here -->
  </div>

</div>
```

---

## 7. Tab: About Us

**Shows:** About Us card · Featured Products · Our Services (compact) · Certifications & Partners

JS sets `display:flex; flex-direction:column; gap:16px` on this panel.

```html
<div id="tab-about" class="tab-panel"
     style="display:flex;flex-direction:column;gap:16px;">

  <!-- ── About Us Card ── -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-3">About Us</h2>
    <div class="flex gap-4">
      <!-- Text (flex-1) -->
      <div class="flex-1">
        <p class="text-sm text-gray-600 leading-relaxed mb-2">
          <span class="text-emerald-600 font-medium">{{ $supplier->name }}</span>
          {{ $supplier->about_short }}
        </p>
        <a href="#" class="text-sm text-emerald-600 font-medium hover:underline">Read More</a>
      </div>
      <!-- Company intro video thumbnail (w-36 h-24) -->
      <div class="shrink-0 w-36 h-24 relative rounded-lg overflow-hidden cursor-pointer group">
        <img src="{{ $supplier->intro_video_thumbnail }}" class="w-full h-full object-cover opacity-80" />
        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
          <!-- Play button circle -->
          <div class="w-9 h-9 bg-white/90 rounded-full flex items-center justify-center group-hover:bg-white shadow">
            <!-- play triangle icon -->
          </div>
        </div>
        <span class="absolute bottom-1.5 left-0 right-0 text-white text-[10px] font-medium text-center bg-black/40 py-0.5">
          Company Introduction Video
        </span>
      </div>
    </div>
  </div>

  <!-- ── Featured Products ── -->
  <div class="sec-card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-[15px] font-bold text-gray-900">Featured Products</h2>
      <a href="#" class="text-sm text-emerald-600 font-medium hover:underline">View all products →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      @foreach($supplier->featuredProducts as $product)
      <div class="fp-card">
        <img src="{{ $product->thumbnail }}" class="w-full h-24 object-cover" />
        <div class="p-2.5">
          <p class="text-sm font-semibold text-gray-900 leading-snug mb-0.5">{{ $product->name }}</p>
          <p class="text-[11px] text-emerald-600 font-medium mb-1.5">{{ $product->category }}</p>
          <a href="{{ route('products.show', $product->slug) }}"
             class="text-[11px] text-emerald-600 font-medium hover:underline">
            View Details →
          </a>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <!-- ── Our Services (compact 4-col) ── -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-4">Our Services</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      @foreach($supplier->services->take(4) as $service)
      <div class="border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow">
        <div class="svc-icon mb-2"><!-- icon --></div>
        <p class="text-sm font-semibold text-gray-900 mb-1">{{ $service->name }}</p>
        <p class="text-[11px] text-gray-500 leading-snug">{{ $service->short_desc }}</p>
      </div>
      @endforeach
    </div>
  </div>

  <!-- ── Certifications & Partners (pill tags) ── -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-4">Certifications &amp; Partners</h2>
    <div class="flex flex-wrap gap-2">
      @foreach($supplier->cert_pills as $cert)
        <span class="cert-pill">{{ $cert }}</span>
      @endforeach
    </div>
  </div>

</div>
```

---

## 8. Tab: Products

**Shows:** `Products (120+)` heading + View in Marketplace link + **3-column × 2-row** product grid.

```html
<div id="tab-products" class="tab-panel">
  <div class="sec-card">

    <div class="flex items-center justify-between mb-5">
      <h2 class="text-[15px] font-bold text-gray-900">
        Products ({{ $supplier->products_count }}+)
      </h2>
      <a href="{{ route('products.index', ['supplier' => $supplier->slug]) }}"
         class="text-sm text-emerald-600 font-medium hover:underline">
        View in Marketplace →
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      @foreach($supplier->products->take(6) as $product)
      <div class="pt-card">

        <!-- Product image -->
        <img src="{{ $product->thumbnail }}" class="w-full h-40 object-cover" />

        <div class="p-3.5">
          <!-- Category label -->
          <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wide mb-1">
            {{ strtoupper($product->category) }}
          </p>
          <!-- Product name — first/active card name in emerald, rest in gray-900 -->
          <p class="text-[15px] font-bold {{ $loop->first ? 'text-gray-900' : 'text-gray-900' }} mb-2">
            {{ $product->name }}
          </p>
          <!-- Stars -->
          <div class="flex items-center gap-1 mb-1.5 text-sm">
            @for($i = 1; $i <= 5; $i++)
              <span class="{{ $i <= round($product->avg_rating) ? 'star-filled' : 'star-empty' }}">★</span>
            @endfor
            <span class="text-xs text-gray-400">({{ $product->reviews_count }})</span>
          </div>
          <!-- Verified supplier -->
          <div class="flex items-center gap-1.5 text-xs text-emerald-600 mb-3">
            <!-- check icon w-3 h-3 -->
            {{ $supplier->name }}
          </div>
          <!-- Footer: price request + view link -->
          <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-400 italic">Request price</span>
            <a href="{{ route('products.show', $product->slug) }}"
               class="text-sm text-emerald-600 font-semibold hover:underline flex items-center gap-1">
              View <!-- chevron-right icon -->
            </a>
          </div>
        </div>

      </div>
      @endforeach
    </div>

  </div>
</div>
```

---

## 9. Tab: Services

**Shows:** "Our Services" heading + subtitle + **2-column grid of 6 full service cards** with icon, title, description (with emerald highlights), and tag pills.

```html
<div id="tab-services" class="tab-panel">
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-1">Our Services</h2>
    <p class="text-sm text-gray-500 mb-5">
      We offer comprehensive support to ensure your institution gets the most from our solutions.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      @foreach($supplier->services as $service)
      <div class="border border-gray-200 rounded-lg p-4">
        <div class="flex items-start gap-3 mb-3">
          <div class="svc-icon shrink-0">
            <!-- icon: w-4 h-4 text-emerald-600 -->
          </div>
          <div>
            <p class="text-sm font-bold text-gray-900 mb-1">{{ $service->name }}</p>
            <p class="text-xs text-gray-500 leading-relaxed">{!! $service->description_html !!}</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-1.5">
          @foreach($service->tags as $tag)
            <span class="svc-tag">{{ $tag }}</span>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
```

**Service description HTML:** wrap key phrases in `<span class="text-emerald-600">` for the coloured text highlights seen in the screenshot.

---

## 10. Tab: Videos

**Shows:** "Videos" heading + subtitle + **2×2 grid of real YouTube iframes** with 16:9 ratio and an emerald caption below.

```html
<div id="tab-videos" class="tab-panel">
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-1">Videos</h2>
    <p class="text-sm text-gray-500 mb-5">
      Watch our product demos, company overview, and classroom showcase videos.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      @foreach($supplier->videos as $video)
      <div class="vid-card">
        <!-- 16:9 YouTube embed -->
        <div class="relative" style="padding-bottom:56.25%; height:0; overflow:hidden;">
          <iframe src="https://www.youtube.com/embed/{{ $video->youtube_id }}"
            class="absolute top-0 left-0 w-full h-full"
            frameborder="0" allowfullscreen
            title="{{ $video->title }}">
          </iframe>
        </div>
        <div class="p-3">
          <p class="text-sm font-semibold text-emerald-600">{{ $video->title }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
```

**Video title:** `text-sm font-semibold text-emerald-600` (not gray)
**Aspect ratio:** 16:9 via `padding-bottom:56.25%` on a zero-height relative container

---

## 11. Tab: Certifications

**Shows:** "Certifications & Accreditations" + emerald subtitle + 2-col certification cards, THEN a separate "Industry Partnerships" section with pill tags.

JS sets `display:flex; flex-direction:column; gap:16px` on this panel.

```html
<div id="tab-certifications" class="tab-panel"
     style="display:none;flex-direction:column;gap:16px;">

  <!-- Certifications & Accreditations -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-1">
      Certifications &amp; Accreditations
    </h2>
    <p class="text-sm text-emerald-600 mb-5">
      Our products and operations are certified by leading global bodies in education and quality management.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      @foreach($supplier->certifications as $cert)
      <div class="cert-card">
        <div class="cert-icon">
          <!-- check icon: w-4 h-4 text-emerald-600, stroke-width 2.5 -->
        </div>
        <div>
          <p class="text-sm font-bold text-gray-900 mb-0.5">{{ $cert->name }}</p>
          <p class="text-[11px] text-emerald-600 mb-1.5">
            {{ $cert->issuer }} · Since {{ $cert->since }}
          </p>
          <p class="text-xs text-gray-500 leading-relaxed">{{ $cert->description }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <!-- Industry Partnerships -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-4">Industry Partnerships</h2>
    <div class="flex flex-wrap gap-2">
      @foreach($supplier->industry_partnerships as $partner)
        <span class="cert-pill">{{ $partner }}</span>
      @endforeach
    </div>
  </div>

</div>
```

---

## 12. Tab: Reviews

**Shows:** Rating summary box (big score + 4 bars) + "Customer Reviews" heading + individual review cards (with optional supplier reply).

```html
<div id="tab-reviews" class="tab-panel">
  <div class="sec-card">

    <!-- Rating summary box -->
    <div class="flex flex-col sm:flex-row items-center gap-5 p-5 border border-gray-200 rounded-lg mb-6 bg-white">

      <!-- Big score -->
      <div class="text-center shrink-0">
        <p class="text-5xl font-extrabold text-gray-900 leading-none">
          {{ $supplier->avg_rating }}
        </p>
        <div class="flex gap-0.5 justify-center mt-2 text-xl">
          @for($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= round($supplier->avg_rating) ? 'star-filled' : 'star-empty' }}">★</span>
          @endfor
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ $supplier->reviews_count }} Reviews</p>
      </div>

      <!-- Rating bars (Quality / Delivery / Communication / Support) -->
      <div class="flex-1 w-full flex flex-col gap-2.5">
        @foreach([
          ['Quality',       $supplier->avg_quality,       '4.9'],
          ['Delivery',      $supplier->avg_delivery,      '4.7'],
          ['Communication', $supplier->avg_communication, '4.8'],
          ['Support',       $supplier->avg_support,       '4.9'],
        ] as [$label, $score, $display])
        <div class="flex items-center gap-3">
          <span class="text-xs text-gray-500 w-24 shrink-0">{{ $label }}</span>
          <div style="flex:1;background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;">
            <div style="background:#f59e0b;height:100%;width:{{ ($score/5)*100 }}%"></div>
          </div>
          <span class="text-xs font-semibold text-gray-700 w-6 text-right">{{ $display }}</span>
        </div>
        @endforeach
      </div>

    </div>

    <h2 class="text-[15px] font-bold text-gray-900 mb-4">Customer Reviews</h2>

    @foreach($supplier->reviews as $review)
    <div class="rev-card mb-3 @if($loop->last) mb-0 @endif">

      <!-- Reviewer header -->
      <div class="flex items-start justify-between mb-2">
        <div class="flex items-center gap-2.5">
          <div class="rev-av {{ $review->avatar_bg }} {{ $review->avatar_text }}">
            {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ $review->reviewer_name }}</p>
            <p class="text-xs text-gray-400">{{ $review->institution }}</p>
          </div>
        </div>
        <span class="text-xs text-gray-400">{{ $review->created_at->format('F Y') }}</span>
      </div>

      <!-- Stars -->
      <div class="flex gap-0.5 mb-2 text-sm">
        @for($i = 1; $i <= 5; $i++)
          <span class="{{ $i <= $review->rating ? 'star-filled' : 'star-empty' }}">★</span>
        @endfor
      </div>

      <!-- Review body — key phrases wrapped in text-emerald-600 -->
      <p class="text-sm text-gray-700 leading-relaxed mb-2">{!! $review->body_html !!}</p>

      <!-- Optional supplier reply -->
      @if($review->supplier_reply)
      <div class="bg-gray-50 rounded-md p-2.5 border-l-2 border-emerald-400">
        <p class="text-xs text-emerald-600 font-semibold mb-0.5">Supplier Reply:</p>
        <p class="text-xs text-gray-500">{!! $review->supplier_reply_html !!}</p>
      </div>
      @endif

    </div>
    @endforeach

  </div>
</div>
```

**Reviewer avatar color pairs:**
| Bg | Text | Initial examples |
|---|---|---|
| `bg-emerald-100` | `text-emerald-700` | S, E, G |
| `bg-amber-100` | `text-amber-700` | A, B, F |
| `bg-purple-100` | `text-purple-700` | P, M, Q |
| `bg-blue-100` | `text-blue-700` | J, D, V |
| `bg-red-100` | `text-red-700` | R, K, X |

**Supplier reply box:** `bg-gray-50 rounded-md p-2.5 border-l-2 border-emerald-400`

---

## 13. Tab: Contact

**Shows:** "Send a Message" form card + "Contact Details" card (4 info tiles + 3 social icon buttons).

JS sets `display:flex; flex-direction:column; gap:16px` on this panel.

```html
<div id="tab-contact" class="tab-panel"
     style="display:none;flex-direction:column;gap:16px;">

  <!-- Send a Message -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-1">Send a Message</h2>
    <p class="text-sm text-emerald-600 mb-5">
      Fill in the form and our team will get back to you within 24 hours.
    </p>

    <form method="POST" action="{{ route('supplier.message.send', $supplier->slug) }}">
      @csrf

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1.5">Full Name</label>
          <input type="text" name="name" placeholder="John Smith"
            class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1.5">Email Address</label>
          <input type="email" name="email" placeholder="john@school.edu"
            class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1.5">Organization</label>
          <input type="text" name="organization" placeholder="Springfield School District"
            class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1.5">Subject</label>
          <input type="text" name="subject" placeholder="Product Inquiry — Robotics Kit"
            class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400" />
        </div>
      </div>

      <div class="mb-5">
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Message</label>
        <textarea name="message" rows="4"
          placeholder="Hello, I am interested in your STEM robotics kits..."
          class="w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 resize-none">
        </textarea>
      </div>

      <button type="submit"
        class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-3 rounded-md flex items-center justify-center gap-2 transition-colors">
        <!-- chat icon --> Send Message
      </button>
    </form>
  </div>

  <!-- Contact Details -->
  <div class="sec-card">
    <h2 class="text-[15px] font-bold text-gray-900 mb-4">Contact Details</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">

      <!-- Phone tile -->
      <div class="contact-tile">
        <div class="contact-icon"><!-- phone icon: w-4 h-4 text-emerald-600 --></div>
        <div>
          <p class="text-[10px] text-gray-400 mb-0.5">Phone</p>
          <p class="text-sm text-gray-800 font-medium">{{ $supplier->phone }}</p>
        </div>
      </div>

      <!-- Email tile -->
      <div class="contact-tile">
        <div class="contact-icon"><!-- email icon --></div>
        <div>
          <p class="text-[10px] text-gray-400 mb-0.5">Email</p>
          <p class="text-sm text-gray-800 font-medium">{{ $supplier->email }}</p>
        </div>
      </div>

      <!-- Website tile -->
      <div class="contact-tile">
        <div class="contact-icon"><!-- globe icon --></div>
        <div>
          <p class="text-[10px] text-gray-400 mb-0.5">Website</p>
          <p class="text-sm text-gray-800 font-medium">{{ $supplier->website }}</p>
        </div>
      </div>

      <!-- Head Office tile -->
      <div class="contact-tile">
        <div class="contact-icon"><!-- pin icon --></div>
        <div>
          <p class="text-[10px] text-gray-400 mb-0.5">Head Office</p>
          <p class="text-sm text-gray-800 font-medium">{{ $supplier->city }}, {{ $supplier->country }}</p>
        </div>
      </div>

    </div>

    <!-- Social icon row -->
    <div class="flex items-center gap-2">
      <!-- LinkedIn: color #0a66c2 border-blue-200 -->
      <button class="soc-icon" style="color:#0a66c2;border-color:#bfdbfe;">
        <!-- LinkedIn SVG -->
      </button>
      <!-- Facebook: color #1877f2 border-blue-200 -->
      <button class="soc-icon" style="color:#1877f2;border-color:#bfdbfe;">
        <!-- Facebook SVG -->
      </button>
      <!-- YouTube: color #ff0000 border-red-200 -->
      <button class="soc-icon" style="color:#ff0000;border-color:#fecaca;">
        <!-- YouTube SVG -->
      </button>
    </div>

  </div>

</div>
```

**Contact tile label:** `text-[10px] text-gray-400`
**Contact tile value:** `text-sm text-gray-800 font-medium`

---

## 14. Right Sidebar

Sticky (`lg:sticky lg:top-20`), width `lg:w-[310px] xl:w-[330px]`, gap between cards `gap-4`.

All 5 cards use `.side-card` (`border border-gray-200 rounded-lg bg-white p-4`).

### 14.1 CTA Card

```html
<div class="side-card">
  <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold
                 py-2.5 rounded-md flex items-center justify-center gap-2 mb-2.5 transition-colors">
    <!-- document icon --> Request Quotation
  </button>
  <button class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 text-sm
                 font-medium py-2.5 rounded-md flex items-center justify-center gap-2
                 hover:bg-gray-50 transition-colors">
    <!-- chat icon --> Contact Supplier
  </button>
</div>
```

### 14.2 Contact Information Card

```html
<div class="side-card">
  <p class="text-sm font-semibold text-gray-900 mb-3">Contact Information</p>
  <div class="flex flex-col gap-2.5">
    <div class="flex items-center gap-2 text-sm text-gray-600">
      <!-- email icon w-4 h-4 text-gray-400 -->
      {{ $supplier->email }}
    </div>
    <div class="flex items-center gap-2 text-sm text-emerald-600">
      <!-- globe icon w-4 h-4 text-gray-400 -->
      <a href="#" class="hover:underline">{{ $supplier->website }}</a>
    </div>
  </div>
</div>
```

### 14.3 Business Hours Card

```html
<div class="side-card">
  <p class="text-sm font-semibold text-gray-900 mb-3">Business Hours</p>
  <div class="h-row"><span class="text-gray-700">Mon – Fri</span><span class="text-gray-500">9:00 AM – 6:00 PM</span></div>
  <div class="h-row"><span class="text-gray-700">Saturday</span><span class="text-gray-500">10:00 AM – 2:00 PM</span></div>
  <div class="h-row"><span class="text-gray-700">Sunday</span><span class="closed">Closed</span></div>
</div>
```

**Closed:** `color:#ef4444; font-weight:500`

### 14.4 Head Office Card

```html
<div class="side-card">
  <p class="text-sm font-semibold text-gray-900 mb-2">Head Office</p>
  <p class="text-sm text-gray-600 mb-1">{{ $supplier->city }}, {{ $supplier->country }}</p>
  <a href="#" class="text-sm text-emerald-600 hover:underline font-medium">View on Map</a>
</div>
```

### 14.5 Share Profile Card

```html
<div class="side-card">
  <p class="text-sm font-semibold text-gray-900 mb-3">Share Profile</p>
  <div class="flex items-center gap-2">
    <button class="share-btn" title="Facebook"><!-- Facebook icon --></button>
    <button class="share-btn" title="LinkedIn"><!-- LinkedIn icon --></button>
    <button class="share-btn" title="Email"><!-- Email icon --></button>
    <button class="share-btn" title="Copy link"><!-- Share icon --></button>
  </div>
</div>
```

---

## 15. You May Also Like

Full-width section below the two-column layout. Visible on **every tab** — it's outside and below the tab panels.

```html
<div class="mt-10">
  <div class="flex items-center justify-between mb-5">
    <h2 class="text-[17px] font-bold text-gray-900">You May Also Like</h2>
    <a href="{{ route('suppliers.index') }}" class="text-sm text-emerald-600 font-medium hover:underline">See all →</a>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($similarSuppliers as $supp)
    <div class="supp-card">

      <!-- Image + heart + supplier name overlay -->
      <div class="relative">
        <img src="{{ $supp->banner_thumb }}" class="w-full h-36 object-cover" />
        <button class="absolute top-2 right-2 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center shadow-sm hover:bg-white">
          <!-- heart icon w-3.5 h-3.5 text-gray-400 -->
        </button>
        <!-- Avatar + name bottom-left overlay on image -->
        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
          <div class="supp-av bg-white {{ $supp->avatar_text }} border border-gray-100">
            {{ strtoupper(substr($supp->name, 0, 1)) }}
          </div>
          <span class="text-white text-xs font-semibold drop-shadow">{{ $supp->name }}</span>
        </div>
      </div>

      <div class="p-3">
        <!-- Badges -->
        <div class="flex flex-wrap gap-1 mb-2">
          @if($supp->is_verified)
            <span class="badge-verified text-[10px] px-1.5 py-0.5">✓ Verified</span>
          @endif
          @if($supp->is_founding)
            <span class="badge-founding text-[10px] px-1.5 py-0.5">Founding Supplier</span>
          @endif
          @if($supp->is_ise)
            <span class="badge-ise text-[10px] px-1.5 py-0.5">ISE Exhibitor</span>
          @endif
        </div>
        <!-- Type -->
        <p class="text-xs text-gray-500 mb-2">{{ $supp->type }}</p>
        <!-- Rating + Country -->
        <div class="flex items-center justify-between text-xs mb-1">
          <span class="text-gray-600">
            <span class="star-filled">★</span> {{ $supp->avg_rating }}
            <span class="text-gray-400">({{ $supp->reviews_count }})</span>
          </span>
          <span class="text-gray-500 flex items-center gap-1">
            <!-- pin icon w-3 h-3 text-gray-400 --> {{ $supp->country }}
          </span>
        </div>
        <!-- Products + View Profile -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-gray-400">🛍 {{ $supp->products_count }}+ Products</span>
          <a href="{{ route('suppliers.show', $supp->slug) }}"
             class="text-emerald-600 font-semibold hover:underline">
            View Profile →
          </a>
        </div>
      </div>

    </div>
    @endforeach
  </div>
</div>
```

---

## 16. Footer

Identical to all other pages — 4-column grid (brand + Marketplace + Company + Support) + bottom bar with Terms/Privacy. See shared `partials/footer.blade.php`.

---

## 17. JavaScript

```javascript
/**
 * Tab switching
 * Pass `this` from onclick — never `event` (currentTarget becomes null after dispatch).
 * Use inline style, not CSS class, to control display —
 * prevents Tailwind's `flex` class from overriding `display:none`.
 */
function switchTab(btn, name) {
  // Deactivate all buttons
  document.querySelectorAll('.tab-btn').forEach(function(b) {
    b.classList.remove('active');
  });

  // Hide all panels via inline style (beats any CSS class)
  document.querySelectorAll('.tab-panel').forEach(function(p) {
    p.style.display = 'none';
  });

  // Activate clicked button
  btn.classList.add('active');

  // Show target panel with correct display type
  var panel = document.getElementById('tab-' + name);
  if (panel) {
    // Multi-card flex panels
    if (name === 'about' || name === 'certifications' || name === 'contact') {
      panel.style.display       = 'flex';
      panel.style.flexDirection = 'column';
      panel.style.gap           = '16px';
    } else {
      // Single-card block panels
      panel.style.display = 'block';
    }
  }
}

// On page load: hide all panels, then show the default (About Us)
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.tab-panel').forEach(function(p) {
    p.style.display = 'none';
  });
  var defaultPanel = document.getElementById('tab-about');
  if (defaultPanel) {
    defaultPanel.style.display       = 'flex';
    defaultPanel.style.flexDirection = 'column';
    defaultPanel.style.gap           = '16px';
  }
});
```

**Critical rules:**
- Always pass `this` from `onclick`, never `event`
- Always use `panel.style.display` (inline style), never `.classList.add('active')`
- About, Certifications, Contact → `flex + column + gap:16px` (they have multiple child cards)
- Products, Services, Videos, Reviews → `block` (single `.sec-card` child)

---

## 18. Laravel Blade Structure

**View:** `resources/views/suppliers/show.blade.php`

```blade
@extends('layouts.app')

@section('title', $supplier->name . ' — Edushopify Supplier Profile')

@section('content')

  <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Hero + Stats --}}
    @include('suppliers.partials.hero', compact('supplier'))

    {{-- Tab Bar --}}
    @include('suppliers.partials.tabs')

    {{-- Two-column --}}
    <div class="flex flex-col lg:flex-row gap-5 items-start">

      {{-- LEFT: Tab content --}}
      <div class="w-full lg:flex-1 lg:max-w-[calc(100%-335px)] min-w-0 flex flex-col gap-4">
        @include('suppliers.tabs.about',          compact('supplier'))
        @include('suppliers.tabs.products',       compact('supplier'))
        @include('suppliers.tabs.services',       compact('supplier'))
        @include('suppliers.tabs.videos',         compact('supplier'))
        @include('suppliers.tabs.certifications', compact('supplier'))
        @include('suppliers.tabs.reviews',        compact('supplier'))
        @include('suppliers.tabs.contact',        compact('supplier'))
      </div>

      {{-- RIGHT: Sidebar --}}
      <div class="w-full lg:w-[310px] xl:w-[330px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">
        @include('suppliers.partials.sidebar', compact('supplier'))
      </div>

    </div>

    {{-- You May Also Like --}}
    @include('suppliers.partials.similar', compact('similarSuppliers'))

  </main>

@endsection

@push('scripts')
<script>
  // Tab switching JS (see Section 17)
</script>
@endpush
```

**Controller:**
```php
// SupplierController.php
public function show(Supplier $supplier)
{
    $supplier->load([
        'featuredProducts',
        'products',
        'services',
        'videos',
        'certifications',
        'reviews.reviewer',
        'businessHours',
    ]);

    $similarSuppliers = Supplier::where('category_id', $supplier->category_id)
        ->where('id', '!=', $supplier->id)
        ->with('featuredProducts')
        ->take(4)
        ->get();

    return view('suppliers.show', compact('supplier', 'similarSuppliers'));
}
```

**Key model attributes:**

| Attribute | Type | Use |
|---|---|---|
| `$supplier->name` | string | Name, avatar initial |
| `$supplier->slug` | string | URL |
| `$supplier->banner_image` | string | Hero image URL |
| `$supplier->banner_thumb` | string | You May Also Like card image |
| `$supplier->is_verified` | bool | Shows Verified badge |
| `$supplier->exhibit_badges` | array | ["BETT Exhibitor", …] |
| `$supplier->country` | string | Flag + name |
| `$supplier->founded_year` | int | "Since 1980" |
| `$supplier->products_count` | int | "120 Products" |
| `$supplier->avg_rating` | float | 4.9 |
| `$supplier->reviews_count` | int | 58 |
| `$supplier->response_rate` | int | 88 (%) |
| `$supplier->avg_response_time` | string | "4 hrs" |
| `$supplier->rfqs_completed` | int | 57 |
| `$supplier->countries_served` | int | 35 |
| `$supplier->years_in_business` | int | 46 |
| `$supplier->about_short` | string | Short bio paragraph |
| `$supplier->intro_video_thumbnail` | string | Video thumb URL |
| `$supplier->email` | string | Contact email |
| `$supplier->phone` | string | Contact phone |
| `$supplier->website` | string | URL without https |
| `$supplier->city` | string | Head office city |
| `$supplier->cert_pills` | array | Simple pill labels |
| `$supplier->industry_partnerships` | array | Partnership labels |
| `$supplier->avg_quality` | float | Rating bar |
| `$supplier->avg_delivery` | float | Rating bar |
| `$supplier->avg_communication` | float | Rating bar |
| `$supplier->avg_support` | float | Rating bar |

---

## 19. Mobile Behaviour

| Element | Mobile | Desktop (lg+) |
|---|---|---|
| Two-column layout | Single column stacked | Side-by-side flex row |
| Sidebar | Below tab content | Sticky right column `310px` |
| Hero banner | `height:280px` | `height:280px` |
| CTA buttons on banner | Hidden | Visible (flex-col, right) |
| Stats bar | `grid-cols-5` (compact) | `grid-cols-5` with dividers |
| Tab bar | Horizontal scroll, no scrollbar | Full width flex |
| About tab grids | 2-col (Featured Products, Services) | 4-col |
| Products tab grid | 1-col | 3-col |
| Services tab grid | 1-col | 2-col |
| Videos tab grid | 1-col | 2-col |
| Certifications grid | 1-col | 2-col |
| Contact form | 1-col fields | 2-col fields |
| Contact details | 1-col tiles | 2-col tiles |
| You May Also Like | 2-col | 4-col |

---

## 20. Quick Class Reference

| Element | Classes / Custom class |
|---|---|
| Page background | `bg-gray-50` |
| Main container | `max-w-7xl mx-auto px-4 sm:px-6 py-6` |
| Hero wrapper | `.hero-wrap` |
| Hero banner | `.hero-banner` — `height:280px` |
| Profile overlay | `.profile-bar` — gradient, flex, align-end |
| Supplier avatar | `.supplier-avatar` — `60×60 rounded-[10px] bg-white` |
| Heart button | `.hero-save-btn` — `34×34 rounded-full white` |
| Stats bar | `.stats-bar` — `border-top only` |
| Stat column | `.stat-col` — `.val` (20px 800) + `.lbl` (11px gray-400) |
| Tab bar | `.tab-bar` — overflow-x:auto no scrollbar |
| Tab button | `.tab-btn` — `padding:14px 22px font-size:14px` |
| Tab active | `.tab-btn.active` — `color:#10b981 border-bottom:#10b981` |
| Tab panel | `.tab-panel` — `display:none` (JS overrides inline) |
| Section card | `.sec-card` — `border rounded-lg bg-white p-5` |
| Featured prod card | `.fp-card` — `border rounded-lg overflow-hidden` |
| Products tab card | `.pt-card` — `border rounded-lg overflow-hidden` |
| Service icon | `.svc-icon` — `38×38 rounded-lg bg-emerald-50` |
| Service tag | `.svc-tag` — `border rounded text-xs` |
| Cert pill | `.cert-pill` — `border rounded-md px-4 py-1.5 text-sm` |
| Cert card | `.cert-card` — `border rounded-lg flex gap-3` |
| Cert icon | `.cert-icon` — `36×36 rounded-lg bg-emerald-50` |
| Video card | `.vid-card` — `border rounded-lg overflow-hidden` |
| Video ratio | `padding-bottom:56.25% height:0 overflow:hidden` |
| Review card | `.rev-card` — `border rounded-lg p-4` |
| Review avatar | `.rev-av` — `36×36 rounded-lg font-700` |
| Supplier reply | `bg-gray-50 rounded-md p-2.5 border-l-2 border-emerald-400` |
| Contact tile | `.contact-tile` — `border rounded-lg flex items-center gap-3` |
| Contact icon | `.contact-icon` — `36×36 rounded-lg bg-emerald-50` |
| Social icon | `.soc-icon` — `34×34 rounded-lg border` |
| Sidebar card | `.side-card` — `border rounded-lg bg-white p-4` |
| Share button | `.share-btn` — `32×32 rounded-lg border bg-gray-50` |
| Closed text | `.h-row .closed` — `color:#ef4444 font-weight:500` |
| Supp card | `.supp-card` — `border rounded-lg overflow-hidden` |
| Supp avatar | `.supp-av` — `34×34 rounded-lg font-700` |
| Left column | `w-full lg:flex-1 lg:max-w-[calc(100%-335px)] min-w-0` |
| Right sidebar | `w-full lg:w-[310px] xl:w-[330px] shrink-0 lg:sticky lg:top-20` |
| Primary button | `bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-md` |
| Secondary button | `border border-gray-200 hover:border-gray-300 text-gray-700 font-medium py-2.5 rounded-md hover:bg-gray-50` |
| Verified badge | `.badge-verified` — `bg-emerald-50 text-emerald-700 border-emerald-200 rounded-full` |
| BETT badge | `.badge-bett` — `bg-violet-50 text-violet-700 border-violet-200 rounded-full` |
| Founding badge | `.badge-founding` — `bg-amber-50 text-amber-700 border-amber-200 rounded-full` |
| ISE badge | `.badge-ise` — `bg-indigo-50 text-indigo-700 border-indigo-200 rounded-full` |
