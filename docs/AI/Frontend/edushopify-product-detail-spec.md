# Edushopify — Product Detail Page Design Specification
> Laravel + Tailwind CDN · Vanilla JS · Mobile-first · No Alpine, no Livewire

---

## Table of Contents
1. [Page Overview](#1-page-overview)
2. [CSS / Style Tokens](#2-css--style-tokens)
3. [Navbar](#3-navbar)
4. [Breadcrumb](#4-breadcrumb)
5. [Two-Column Layout Wrapper](#5-two-column-layout-wrapper)
6. [Left Column — Gallery](#6-left-column--gallery)
7. [Left Column — Product Description](#7-left-column--product-description)
8. [Left Column — Specifications Table](#8-left-column--specifications-table)
9. [Left Column — Buyer Protection](#9-left-column--buyer-protection)
10. [Left Column — Customer Reviews](#10-left-column--customer-reviews)
11. [Right Sidebar — Product Info Card](#11-right-sidebar--product-info-card)
12. [Right Sidebar — Supplied By Card](#12-right-sidebar--supplied-by-card)
13. [Right Sidebar — Why Edushopify Card](#13-right-sidebar--why-edushopify-card)
14. [Right Sidebar — Fast Response Card](#14-right-sidebar--fast-response-card)
15. [You May Also Like](#15-you-may-also-like)
16. [Footer](#16-footer)
17. [JavaScript](#17-javascript)
18. [Laravel Blade Structure](#18-laravel-blade-structure)
19. [Mobile Behaviour](#19-mobile-behaviour)
20. [Quick Class Reference](#20-quick-class-reference)

---

## 1. Page Overview

**Route:** `GET /marketplace/{slug}`
**View:** `resources/views/products/show.blade.php`
**Layout:** `layouts/app.blade.php`
**Controller:** `ProductController@show`

**Page layout:**
```
[Navbar — sticky]
[Breadcrumb strip]
[Two-column content]
  LEFT  (flex-1, min-w-0)     → Gallery · Description · Specs · Buyer Protection · Reviews
  RIGHT (lg:w-[340px] xl:w-[360px], sticky lg:top-20) → Product Info · Supplied By · Why Edushopify · Fast Response
[You May Also Like — full width below two-col]
[Footer]
```

**Body background:** `bg-gray-50`

---

## 2. CSS / Style Tokens

Add all of these to your `<style>` block or `app.css`. They are used across the whole page.

```css
* { font-family: 'Inter', sans-serif; }

/* ── Navbar ── */
.nav-link       { font-size:14px; font-weight:500; color:#374151; padding:4px 10px; }
.nav-link:hover { color:#111; }

/* ── Stars ── */
.star-filled { color:#f59e0b; }
.star-empty  { color:#e5e7eb; }

/* ── Spec table ── */
.spec-table { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
.spec-row   { display:grid; grid-template-columns:160px 1fr; border-bottom:1px solid #e5e7eb; font-size:14px; }
.spec-row:last-child { border-bottom:none; }
/* Alternating row backgrounds */
.spec-row:nth-child(odd)  .spec-cell-label,
.spec-row:nth-child(odd)  .spec-cell-value { background:#f9fafb; }
.spec-row:nth-child(even) .spec-cell-label,
.spec-row:nth-child(even) .spec-cell-value { background:#fff; }
/* Cell styles */
.spec-cell-label { padding:11px 14px; color:#374151; font-weight:500; border-right:1px solid #e5e7eb; }
.spec-cell-value { padding:11px 14px; color:#374151; }
/* Highlighted row (e.g. Frame) */
.spec-row.highlight .spec-cell-label { color:#10b981; }
.spec-row.highlight .spec-cell-value { color:#3b82f6; }

/* ── Rating bars ── */
.rating-bar-bg   { background:#e5e7eb; border-radius:99px; height:6px; flex:1; overflow:hidden; }
.rating-bar-fill { background:#f59e0b; height:100%; border-radius:99px; }

/* ── Reviewer avatar ── */
.reviewer-avatar { width:32px; height:32px; border-radius:6px; display:flex; align-items:center;
                   justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; }

/* ── "You May Also Like" product card ── */
.product-card { border:1px solid #e5e7eb; border-radius:10px; background:#fff;
                overflow:hidden; transition:box-shadow .2s; }
.product-card:hover { box-shadow:0 4px 18px rgba(0,0,0,.08); }

/* ── Sidebar card wrapper ── */
.sidebar-card { border:1px solid #e5e7eb; border-radius:10px; background:#fff; padding:18px; }

/* ── Gallery thumbnail ── */
.thumb { border:2px solid transparent; border-radius:6px; overflow:hidden;
         cursor:pointer; opacity:.7; transition:opacity .15s, border-color .15s; }
.thumb.active, .thumb:hover { border-color:#10b981; opacity:1; }

/* ── Buyer protection icon circle ── */
.protect-icon { width:36px; height:36px; border-radius:50%; background:#f0fdf4;
                display:flex; align-items:center; justify-content:center; margin:0 auto 6px; }

/* ── Fast Response box ── */
.fast-response { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:14px; }

/* ── Supplier stat box ── */
.stat-box     { text-align:center; }
.stat-box .val { font-size:16px; font-weight:700; color:#111827; }
.stat-box .lbl { font-size:11px; color:#9ca3af; margin-top:1px; }

/* ── Why Edushopify row ── */
.why-row     { display:flex; align-items:center; gap:7px; font-size:13px; color:#374151; margin-bottom:6px; }
.why-row svg { color:#10b981; flex-shrink:0; }

/* ── In Stock badge ── */
.in-stock { background:#ecfdf5; color:#059669; font-size:10px; font-weight:700;
            padding:2px 7px; border-radius:4px; }
```

---

## 3. Navbar

Identical to all other pages. No active state on nav links for this page (product page is not a top-level section).

```html
<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-4 h-14">

    <!-- Logo -->
    <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
      <!-- 3×3 SVG dot grid (see shared partials) -->
      <div class="flex flex-col leading-tight">
        <span class="font-bold text-gray-900 text-[17px] tracking-tight">
          Edu<span class="text-emerald-500">shopify</span>
        </span>
        <span class="text-[9px] text-gray-400 hidden sm:block">Connecting Education</span>
      </div>
    </a>

    <!-- Search bar (hidden on mobile) -->
    <div class="flex-1 max-w-md relative hidden sm:flex">
      <input type="text" placeholder="Search suppliers, products, categories..."
        class="w-full border border-gray-200 rounded-l-md text-sm px-3 py-2
               focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      <button class="px-3 bg-emerald-500 hover:bg-emerald-600 rounded-r-md flex items-center">
        <!-- search icon -->
      </button>
    </div>

    <!-- Nav links (desktop) -->
    <nav class="hidden lg:flex items-center gap-1 ml-1">
      <a href="{{ route('categories.index') }}" class="nav-link">Categories</a>
      <a href="{{ route('rfq.index') }}"        class="nav-link">RFQ</a>
      <a href="{{ route('suppliers.index') }}"  class="nav-link">Suppliers</a>
      <a href="{{ route('resources.index') }}"  class="nav-link">Resources</a>
    </nav>

    <!-- Right: icons + auth -->
    <div class="ml-auto flex items-center gap-3">
      <!-- heart / chat / bell — hidden on mobile -->
      <div class="hidden sm:flex items-center gap-2 text-gray-400">
        <button class="hover:text-gray-700"><!-- heart icon --></button>
        <button class="hover:text-gray-700"><!-- chat icon --></button>
        <button class="hover:text-gray-700"><!-- bell icon --></button>
      </div>
      @guest
        <a href="{{ route('login') }}"    class="text-sm font-medium text-gray-700 hidden sm:inline">Sign In</a>
        <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-md">Register</a>
      @else
        <!-- avatar dropdown (same as other pages) -->
      @endguest
    </div>
  </div>
</header>
```

---

## 4. Breadcrumb

Full-width white strip with a subtle bottom border. One level of context only.

```html
<div class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('products.index') }}" class="hover:text-gray-900 transition-colors">
      ← Marketplace
    </a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700 truncate">{{ $product->name }}</span>
  </div>
</div>
```

---

## 5. Two-Column Layout Wrapper

```html
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-7">
  <div class="flex flex-col lg:flex-row gap-7 items-start">

    <!-- LEFT: main content (flex-1 shrinks alongside wider sidebar) -->
    <div class="w-full lg:flex-1 flex flex-col gap-5 min-w-0">
      <!-- Gallery, Description, Specs, Buyer Protection, Reviews -->
    </div>

    <!-- RIGHT: sticky sidebar -->
    <div class="w-full lg:w-[340px] xl:w-[360px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">
      <!-- Product Info, Supplied By, Why Edushopify, Fast Response -->
    </div>

  </div>

  <!-- You May Also Like — full width below -->
  <div class="mt-10">
    <!-- ... -->
  </div>
</main>
```

**Key sizing:**
- Left column: `lg:flex-1 min-w-0` — takes remaining space
- Right sidebar: `lg:w-[340px] xl:w-[360px]` — fixed wider width
- Gap between columns: `gap-7`
- Gap between left cards: `gap-5`
- Gap between sidebar cards: `gap-4`

---

## 6. Left Column — Gallery

Card: `bg-white border border-gray-200 rounded-lg overflow-hidden`

### 6.1 Main Image

```html
<div class="relative">
  <img id="main-img"
    src="{{ $product->images->first()->url }}"
    alt="{{ $product->name }}"
    class="w-full h-[340px] sm:h-[400px] object-cover" />
</div>
```

### 6.2 Thumbnail Strip

Sits below main image with `border-t border-gray-100`. Each thumb is `w-16 h-14`.

```html
<div class="flex gap-2 p-3 border-t border-gray-100">
  @foreach($product->images as $img)
  <div class="thumb {{ $loop->first ? 'active' : '' }} w-16 h-14"
       onclick="switchImg(this, '{{ $img->url }}')">
    <img src="{{ $img->thumb_url }}" class="w-full h-full object-cover" />
  </div>
  @endforeach
</div>
```

**Thumb states:**
- Default: `border-2 border-transparent opacity-70`
- Active / hover: `border-2 border-emerald-500 opacity-100`

---

## 7. Left Column — Product Description

Card: `bg-white border border-gray-200 rounded-lg p-5 sm:p-6`

```html
<div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">

  <!-- Heading with document icon -->
  <div class="flex items-center gap-2 mb-3">
    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
    </svg>
    <h2 class="text-[15px] font-semibold text-gray-900">Product Description</h2>
  </div>

  <!-- Body text -->
  <div class="text-sm text-gray-600 leading-relaxed space-y-3">
    {!! nl2br(e($product->description)) !!}
  </div>
</div>
```

**Typography:** `text-sm text-gray-600 leading-relaxed`
**Paragraph spacing:** `space-y-3` or `mt-3` on each `<p>`

---

## 8. Left Column — Specifications Table

Card: `bg-white border border-gray-200 rounded-lg p-5 sm:p-6`

The table itself has its own rounded border — `spec-table` class wraps the rows.

```html
<div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">
  <h2 class="text-[15px] font-semibold text-gray-900 mb-4">Specifications</h2>

  <div class="spec-table">
    @foreach($product->specifications as $spec)
    <div class="spec-row {{ $spec->highlight ? 'highlight' : '' }}">
      <span class="spec-cell-label">{{ $spec->label }}</span>
      <span class="spec-cell-value">{{ $spec->value }}</span>
    </div>
    @endforeach
  </div>
</div>
```

**Table anatomy:**
```
┌─────────────────────────────────────────────┐  ← spec-table border + rounded-lg
│ spec-cell-label (160px) │ spec-cell-value   │  ← border-right separates columns
├─────────────────────────────────────────────┤  ← border-bottom between rows
│ Size                    │ 60×60 cm          │  ← odd row: bg-gray-50
├─────────────────────────────────────────────┤
│ Frame (highlight)       │ Steel (blue text) │  ← even row: bg-white, label=emerald, value=blue
├─────────────────────────────────────────────┤
│ Top                     │ MDF scratch-res.  │  ← odd row: bg-gray-50
...
```

**Cell padding:** `padding:11px 14px`
**Label column width:** `160px` (fixed)
**Value column:** `1fr` (takes remaining)
**Highlight row:** label `color:#10b981` · value `color:#3b82f6`
**Last row:** no `border-bottom`

**Blade data structure:**
```php
// Product model
$product->specifications → Collection of objects with:
  ->label     string   "Size", "Frame", "Top" …
  ->value     string   "60×60 cm", "Steel" …
  ->highlight bool     true for rows to show in color
```

---

## 9. Left Column — Buyer Protection

Card: `bg-white border border-gray-200 rounded-lg p-5 sm:p-6`

4-column icon grid (2×2 on mobile). Each item: circle icon + bold label + small description.

```html
<div class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6">
  <h2 class="text-[15px] font-semibold text-gray-900 mb-5">Buyer Protection</h2>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">

    <!-- Verified Supplier -->
    <div>
      <div class="protect-icon">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          <polyline points="9 12 11 14 15 10"/>
        </svg>
      </div>
      <p class="text-xs font-semibold text-gray-800 mb-0.5">Verified Supplier</p>
      <p class="text-[11px] text-gray-400 leading-snug">Identity &amp; credentials confirmed</p>
    </div>

    <!-- On-Time Delivery -->
    <div>
      <div class="protect-icon">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="1" y="3" width="15" height="13" rx="1.5"/>
          <path d="m16 8 5 3v5h-5V8z"/>
          <circle cx="5.5" cy="18.5" r="2.5"/>
          <circle cx="18.5" cy="18.5" r="2.5"/>
        </svg>
      </div>
      <p class="text-xs font-semibold text-gray-800 mb-0.5">On-Time Delivery</p>
      <p class="text-[11px] text-gray-400 leading-snug">96%+ on-time shipment rate</p>
    </div>

    <!-- Quality Assured -->
    <div>
      <div class="protect-icon">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
      </div>
      <p class="text-xs font-semibold text-gray-800 mb-0.5">Quality Assured</p>
      <p class="text-[11px] text-gray-400 leading-snug">Products meet platform standards</p>
    </div>

    <!-- After-Sales Support -->
    <div>
      <div class="protect-icon">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
      </div>
      <p class="text-xs font-semibold text-gray-800 mb-0.5">After-Sales Support</p>
      <p class="text-[11px] text-gray-400 leading-snug">Dedicated support post-purchase</p>
    </div>

  </div>
</div>
```

**Protect icon circle:** `width:36px height:36px border-radius:50% background:#f0fdf4`
Icon is `w-5 h-5 text-emerald-500`

---

## 10. Left Column — Customer Reviews

Card: `bg-white border border-gray-200 rounded-lg p-5 sm:p-6`

### 10.1 Rating Summary (top section)

Two-part layout: big score (left) + bar breakdown (right).

```html
<div class="flex flex-col sm:flex-row gap-6 mb-6">

  <!-- Big score -->
  <div class="shrink-0 text-center sm:text-left">
    <p class="text-5xl font-bold text-gray-900 leading-none">{{ $product->avg_rating }}</p>
    <div class="flex items-center justify-center sm:justify-start gap-0.5 mt-2">
      @for($i = 1; $i <= 5; $i++)
        <span class="{{ $i <= round($product->avg_rating) ? 'star-filled' : 'star-empty' }} text-lg">★</span>
      @endfor
    </div>
    <p class="text-xs text-gray-400 mt-1">{{ $product->reviews_count }} Reviews</p>
  </div>

  <!-- Bar breakdown -->
  <div class="flex-1 flex flex-col gap-2">
    @foreach([['Quality', $product->avg_quality, '4.9'], ['Value', $product->avg_value, '4.7'],
              ['Delivery', $product->avg_delivery, '4.8'], ['Support', $product->avg_support, '4.9']] as [$label, $pct, $score])
    <div class="flex items-center gap-3">
      <span class="text-xs text-gray-500 w-14 shrink-0">{{ $label }}</span>
      <div class="rating-bar-bg">
        <div class="rating-bar-fill" style="width:{{ ($pct / 5) * 100 }}%"></div>
      </div>
      <span class="text-xs font-medium text-gray-700 w-6 text-right">{{ $score }}</span>
    </div>
    @endforeach
  </div>

</div>

<div class="border-t border-gray-100"></div>
```

### 10.2 Individual Review Cards

Each review is separated by `border-b border-gray-100`. Last review has no border.

```html
@foreach($product->reviews as $review)
<div class="py-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">

  <!-- Reviewer header row -->
  <div class="flex items-start justify-between mb-2">
    <div class="flex items-center gap-2.5">
      <!-- Avatar (initials) — color based on reviewer name -->
      <div class="reviewer-avatar {{ $review->avatar_bg }} {{ $review->avatar_text }}">
        {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
      </div>
      <div>
        <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $review->reviewer_name }}</p>
        <p class="text-xs text-gray-400">{{ $review->institution }}</p>
      </div>
    </div>
    <span class="text-xs text-gray-400">{{ $review->created_at->format('M Y') }}</span>
  </div>

  <!-- Star rating -->
  <div class="flex items-center gap-0.5 mb-2">
    @for($i = 1; $i <= 5; $i++)
      <span class="{{ $i <= $review->rating ? 'star-filled' : 'star-empty' }} text-sm">★</span>
    @endfor
  </div>

  <!-- Review text -->
  <p class="text-sm text-gray-600 leading-relaxed">{{ $review->body }}</p>

</div>
@endforeach
```

**Reviewer avatar color pairs (rotate through these):**
| Bg class | Text class | Used for |
|---|---|---|
| `bg-emerald-100` | `text-emerald-700` | S, A, E … |
| `bg-amber-100` | `text-amber-700` | A, B, F … |
| `bg-purple-100` | `text-purple-700` | P, M, Q … |
| `bg-blue-100` | `text-blue-700` | D, J, V … |
| `bg-red-100` | `text-red-700` | R, K, X … |

---

## 11. Right Sidebar — Product Info Card

Class: `sidebar-card` (`border border-gray-200 rounded-lg bg-white p-[18px]`)

This is the most important sidebar card. Everything in it stacks vertically.

### 11.1 Title + Byline

```html
<h1 class="text-[17px] font-bold text-gray-900 leading-snug mb-1">
  {{ $product->name }}
</h1>
<div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
  <span>
    by <a href="{{ route('suppliers.show', $product->supplier->slug) }}"
          class="text-emerald-600 hover:underline font-semibold">
      {{ $product->supplier->name }}
    </a>
  </span>
  <!-- SKU pill badge -->
  <span class="bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded text-[11px] tracking-wide">
    {{ $product->sku }}
  </span>
</div>
```

### 11.2 Star Rating Row

```html
<div class="flex items-center gap-2 mb-4">
  <div class="flex items-center gap-0.5">
    @for($i = 1; $i <= 5; $i++)
      <span class="{{ $i <= round($product->avg_rating) ? 'star-filled' : 'star-empty' }} text-base">★</span>
    @endfor
  </div>
  <span class="text-sm font-semibold text-gray-800">{{ $product->avg_rating }}</span>
  <span class="text-sm text-gray-400">· {{ $product->reviews_count }} reviews</span>
</div>
```

### 11.3 Divider

```html
<div class="border-t border-gray-100 mb-4"></div>
```

### 11.4 Price Range Box

Green-tinted box. **Not** a plain text block — it has a background.

```html
<div class="bg-emerald-50 border border-emerald-100 rounded-lg px-4 py-3 mb-4">
  <p class="text-xs text-emerald-700 font-medium mb-1">Price Range</p>
  <p class="text-[24px] font-extrabold text-emerald-600 leading-none mb-0.5">
    ${{ $product->price_min }} – ${{ $product->price_max }}
    <span class="text-[15px] font-semibold text-emerald-600">each</span>
  </p>
  <p class="text-xs text-gray-400 mt-1">USD · Contact for bulk pricing</p>
</div>
```

**Colors:**
- Box bg: `bg-emerald-50` / border: `border-emerald-100`
- Label "Price Range": `text-xs text-emerald-700 font-medium`
- Price numbers: `text-[24px] font-extrabold text-emerald-600`
- "each" unit: `text-[15px] font-semibold text-emerald-600`
- Sub-label: `text-xs text-gray-400`

### 11.5 Trust Bullet Icons

Three rows. Each has a **specific icon** — not generic checkmarks.

```html
<div class="flex flex-col gap-2.5 mb-5">

  <!-- Verified Supplier — shield with checkmark -->
  <div class="flex items-center gap-2 text-sm text-gray-600">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      <polyline points="9 12 11 14 15 10"/>
    </svg>
    Verified Supplier
  </div>

  <!-- Fast Worldwide Delivery — truck icon -->
  <div class="flex items-center gap-2 text-sm text-gray-600">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="1" y="3" width="15" height="13" rx="1.5"/>
      <path d="m16 8 5 3v5h-5V8z"/>
      <circle cx="5.5" cy="18.5" r="2.5"/>
      <circle cx="18.5" cy="18.5" r="2.5"/>
    </svg>
    Fast Worldwide Delivery
  </div>

  <!-- Secure Purchase Terms — padlock icon -->
  <div class="flex items-center gap-2 text-sm text-gray-600">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <rect x="3" y="11" width="18" height="11" rx="2"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    Secure Purchase Terms
  </div>

</div>
```

### 11.6 CTA Buttons

```html
<!-- Primary: Request Quotation -->
<button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold
               py-2.5 rounded-md text-sm flex items-center justify-center gap-2 mb-2 transition-colors">
  <!-- document icon -->
  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
    <polyline points="14 2 14 8 20 8"/>
    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
  </svg>
  Request Quotation
</button>

<!-- Secondary: Contact Supplier -->
<button class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 font-medium
               py-2.5 rounded-md text-sm flex items-center justify-center gap-2 mb-4
               hover:bg-gray-50 transition-colors">
  <!-- chat icon -->
  <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
  </svg>
  Contact Supplier
</button>
```

### 11.7 Save / Share Buttons

Two **individual bordered buttons** side by side — not separated by a divider line.

```html
<div class="flex items-center gap-2">

  <!-- Save -->
  <button class="flex-1 flex items-center justify-center gap-1.5 text-sm text-gray-500
                 hover:text-gray-800 py-2 border border-gray-200 hover:border-gray-300
                 rounded-md hover:bg-gray-50 transition-colors">
    <!-- heart icon -->
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
    </svg>
    Save
  </button>

  <!-- Share -->
  <button class="flex-1 flex items-center justify-center gap-1.5 text-sm text-gray-500
                 hover:text-gray-800 py-2 border border-gray-200 hover:border-gray-300
                 rounded-md hover:bg-gray-50 transition-colors">
    <!-- share icon -->
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
      <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
      <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
    </svg>
    Share
  </button>

</div>
```

**Key point:** Both are separate `border border-gray-200 rounded-md` buttons with `flex-1`. There is no shared top border line — they sit inside the same `sidebar-card` padding.

---

## 12. Right Sidebar — Supplied By Card

Class: `sidebar-card`

```html
<div class="sidebar-card">

  <!-- Section label -->
  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Supplied By</p>

  <!-- Supplier row -->
  <div class="flex items-center gap-3 mb-4">
    <div class="w-10 h-10 rounded bg-blue-100 text-blue-700 font-bold text-base
                flex items-center justify-center shrink-0">
      {{ strtoupper(substr($product->supplier->name, 0, 1)) }}
    </div>
    <div>
      <p class="text-sm font-semibold text-gray-900">{{ $product->supplier->name }}</p>
      <div class="flex items-center gap-1 text-xs text-emerald-600">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M20 6 9 17l-5-5"/>
        </svg>
        Verified Supplier
      </div>
    </div>
  </div>

  <!-- 3 stats -->
  <div class="grid grid-cols-3 gap-2 py-3 mb-4 border-y border-gray-100">
    <div class="stat-box">
      <p class="val">{{ $product->supplier->response_rate }}%</p>
      <p class="lbl">Response</p>
    </div>
    <div class="stat-box">
      <p class="val">{{ $product->supplier->deals_count }}+</p>
      <p class="lbl">Deals</p>
    </div>
    <div class="stat-box">
      <p class="val">{{ $product->supplier->years_active }}+</p>
      <p class="lbl">Years</p>
    </div>
  </div>

  <a href="{{ route('suppliers.show', $product->supplier->slug) }}"
     class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
    Browse Suppliers →
  </a>

</div>
```

**Stat box styles:**
- `.val` → `font-size:16px font-weight:700 color:#111827`
- `.lbl` → `font-size:11px color:#9ca3af margin-top:1px`

---

## 13. Right Sidebar — Why Edushopify Card

Class: `sidebar-card`

```html
<div class="sidebar-card">
  <p class="text-sm font-semibold text-gray-900 mb-3">Why Edushopify?</p>

  <div class="why-row">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
    Verified &amp; trusted suppliers
  </div>
  <div class="why-row">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
    Secure quotation process
  </div>
  <div class="why-row">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
    Dedicated buyer support
  </div>
  <div class="why-row">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
    Compare multiple quotes
  </div>
  <div class="why-row" style="margin-bottom:0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
    Safe payment terms
  </div>
</div>
```

**Row style:** `display:flex align-items:center gap:7px font-size:13px color:#374151 margin-bottom:6px`
**Icon:** `w-4 h-4 color:#10b981 flex-shrink:0`

---

## 14. Right Sidebar — Fast Response Card

**Not** a `sidebar-card` — uses `fast-response` class directly.

```html
<div class="fast-response">
  <div class="flex items-start gap-2.5">

    <!-- Lightning bolt icon -->
    <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor"
         stroke-width="2.5" viewBox="0 0 24 24">
      <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
    </svg>

    <div>
      <p class="text-sm font-semibold text-emerald-700 mb-0.5">Fast Response</p>
      <p class="text-xs text-gray-600 leading-relaxed">
        Suppliers on Edushopify respond to quote requests within
        <strong>24 hours</strong> on average.
      </p>
    </div>

  </div>
</div>
```

**Box styles:** `background:#f0fdf4 border:1px solid #bbf7d0 border-radius:8px padding:14px`

---

## 15. You May Also Like

Full-width section below the two-column layout, inside `<main>`.

```html
<div class="mt-10">
  <div class="flex items-center justify-between mb-5">
    <h2 class="text-[17px] font-bold text-gray-900">You May Also Like</h2>
    <a href="{{ route('products.index') }}"
       class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
      View all products →
    </a>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($relatedProducts as $related)
    <div class="product-card">

      <!-- Image + In Stock badge -->
      <div class="relative">
        <img src="{{ $related->thumbnail }}" alt="{{ $related->name }}"
             class="w-full h-36 object-cover" />
        @if($related->in_stock)
          <span class="in-stock absolute top-2 left-2">In Stock</span>
        @endif
      </div>

      <!-- Card body -->
      <div class="p-3">
        <!-- Brand name — all caps, tiny, muted -->
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">
          {{ strtoupper($related->brand) }}
        </p>

        <!-- Product name -->
        <p class="text-sm font-semibold text-gray-900 leading-snug mb-2">
          {{ $related->name }}
        </p>

        <!-- Stars -->
        <div class="flex items-center gap-1 mb-2">
          @for($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= round($related->avg_rating) ? 'star-filled' : 'star-empty' }} text-xs">★</span>
          @endfor
          <span class="text-xs text-gray-400">({{ $related->reviews_count }})</span>
        </div>

        <!-- Price + View link -->
        <div class="flex items-center justify-between">
          <p class="text-sm font-bold text-emerald-600">
            ${{ $related->price_min }} – ${{ $related->price_max }}
            @if($related->price_unit)
              <span class="text-xs font-normal text-gray-500">{{ $related->price_unit }}</span>
            @endif
          </p>
          <a href="{{ route('products.show', $related->slug) }}"
             class="text-xs text-emerald-600 font-medium hover:underline shrink-0">
            View →
          </a>
        </div>
      </div>

    </div>
    @endforeach
  </div>
</div>
```

**In Stock badge:** `background:#ecfdf5 color:#059669 font-size:10px font-weight:700 padding:2px 7px border-radius:4px`
**Brand label:** `text-[10px] font-bold text-gray-400 uppercase tracking-wide`
**Price:** `text-sm font-bold text-emerald-600`
**Grid:** 2 cols mobile → 4 cols on md+

---

## 16. Footer

Identical to all other pages — 4-column grid (brand + Marketplace + Company + Support) + bottom bar. See shared `partials/footer.blade.php`.

---

## 17. JavaScript

All vanilla JS. Place in `public/js/app.js` or a `@push('scripts')` stack.

### 17.1 Gallery Image Switcher

```javascript
function switchImg(thumb, src) {
  // Update main image
  document.getElementById('main-img').src = src;
  // Update active thumbnail
  document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}
```

### 17.2 Save Button Toggle (wishlist)

```javascript
document.querySelector('[data-action="save"]')?.addEventListener('click', function () {
  const saved = this.dataset.saved === 'true';
  this.dataset.saved = !saved;

  const icon = this.querySelector('svg path');
  if (!saved) {
    // Filled heart
    icon.setAttribute('fill', '#10b981');
    icon.setAttribute('stroke', '#10b981');
    this.querySelector('span').textContent = 'Saved';
  } else {
    icon.setAttribute('fill', 'none');
    icon.setAttribute('stroke', 'currentColor');
    this.querySelector('span').textContent = 'Save';
  }

  // Fire AJAX to toggle wishlist (no page reload)
  fetch('/api/wishlist/toggle', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    body: JSON.stringify({ product_id: this.dataset.productId })
  });
});
```

### 17.3 Share Button (Web Share API + clipboard fallback)

```javascript
document.querySelector('[data-action="share"]')?.addEventListener('click', async function () {
  const data = { title: document.title, url: window.location.href };
  try {
    if (navigator.share) {
      await navigator.share(data);
    } else {
      await navigator.clipboard.writeText(window.location.href);
      showToast('Link copied!');
    }
  } catch {}
});

function showToast(msg) {
  const t = Object.assign(document.createElement('div'), {
    textContent: msg,
    className: 'fixed bottom-5 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-md shadow-lg z-50'
  });
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
}
```

### 17.4 Request Quotation Button (open modal or redirect)

```javascript
document.querySelector('[data-action="request-quote"]')?.addEventListener('click', function () {
  // Option A: redirect to RFQ create page pre-filled with product
  window.location.href = `/rfq/create?product_id={{ $product->id }}&supplier_id={{ $product->supplier->id }}`;

  // Option B: open a modal (if you have a modal component)
  // document.getElementById('quote-modal').classList.remove('hidden');
});
```

---

## 18. Laravel Blade Structure

**View file:** `resources/views/products/show.blade.php`

```blade
@extends('layouts.app')

@section('title', $product->name . ' — Edushopify')

@section('content')

  @include('partials.breadcrumb', ['crumbs' => ['Marketplace' => route('products.index'), $product->name => null]])

  <main class="max-w-7xl mx-auto px-4 sm:px-6 py-7">
    <div class="flex flex-col lg:flex-row gap-7 items-start">

      {{-- LEFT --}}
      <div class="w-full lg:flex-1 flex flex-col gap-5 min-w-0">
        @include('products.partials.gallery',          compact('product'))
        @include('products.partials.description',      compact('product'))
        @include('products.partials.specifications',   compact('product'))
        @include('products.partials.buyer-protection')
        @include('products.partials.reviews',          compact('product'))
      </div>

      {{-- RIGHT --}}
      <div class="w-full lg:w-[340px] xl:w-[360px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20">
        @include('products.partials.sidebar-info',        compact('product'))
        @include('products.partials.sidebar-supplier',    compact('product'))
        @include('products.partials.sidebar-why')
        @include('products.partials.sidebar-fast-response')
      </div>

    </div>

    {{-- YOU MAY ALSO LIKE --}}
    @include('products.partials.related', compact('relatedProducts'))

  </main>

@endsection
```

**Controller:**

```php
// ProductController.php
public function show(Product $product)
{
    $product->load([
        'images',
        'specifications',
        'supplier',
        'reviews.reviewer',
        'category',
    ]);

    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->with('supplier')
        ->take(4)
        ->get();

    return view('products.show', compact('product', 'relatedProducts'));
}
```

**Key model attributes used in the view:**

| Attribute | Type | Description |
|---|---|---|
| `$product->name` | string | Product title |
| `$product->slug` | string | URL slug |
| `$product->sku` | string | SKU displayed as pill badge |
| `$product->description` | text | Long-form description |
| `$product->price_min` | decimal | Low end of price range |
| `$product->price_max` | decimal | High end of price range |
| `$product->price_unit` | string | "per unit", "each", null |
| `$product->avg_rating` | float | e.g. 4.8 |
| `$product->avg_quality` | float | Sub-rating: quality |
| `$product->avg_value` | float | Sub-rating: value |
| `$product->avg_delivery` | float | Sub-rating: delivery |
| `$product->avg_support` | float | Sub-rating: support |
| `$product->reviews_count` | int | Total review count |
| `$product->in_stock` | bool | Shows "In Stock" badge |
| `$product->images` | Collection | `url`, `thumb_url` per image |
| `$product->specifications` | Collection | `label`, `value`, `highlight` |
| `$product->supplier` | Supplier | `name`, `slug`, `response_rate`, `deals_count`, `years_active` |
| `$product->reviews` | Collection | `reviewer_name`, `institution`, `rating`, `body`, `created_at` |

---

## 19. Mobile Behaviour

| Element | Mobile | Desktop (lg+) |
|---|---|---|
| Two-col layout | Single column, stacked | Side by side flex row |
| Sidebar position | Below left column content | Sticky right column |
| Sidebar width | Full width | Fixed `340px / 360px` |
| Gallery main image | `h-[340px]` | `h-[400px]` |
| Thumbnail strip | Horizontal scroll if needed | Same |
| Spec table label col | 160px (fixed) | 160px (fixed) |
| Buyer Protection grid | 2×2 | 1×4 |
| Rating summary | Stacked (score above bars) | Side by side |
| Related products grid | 2 cols | 4 cols |
| Nav links | Hidden (hamburger → drawer) | Visible |
| Search bar | Hidden | Visible |
| Icon buttons | Hidden | Visible |
| Save/Share buttons | Full-width side by side | Full-width side by side |

---

## 20. Quick Class Reference

| Element | Classes |
|---|---|
| Page background | `bg-gray-50` |
| Main container | `max-w-7xl mx-auto px-4 sm:px-6 py-7` |
| Left column | `w-full lg:flex-1 flex flex-col gap-5 min-w-0` |
| Right sidebar | `w-full lg:w-[340px] xl:w-[360px] shrink-0 flex flex-col gap-4 lg:sticky lg:top-20` |
| Content card | `bg-white border border-gray-200 rounded-lg p-5 sm:p-6` |
| Sidebar card | `sidebar-card` → `border border-gray-200 rounded-lg bg-white p-[18px]` |
| Card heading | `text-[15px] font-semibold text-gray-900 mb-4` |
| Section heading (sidebar label) | `text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3` |
| Price box | `bg-emerald-50 border border-emerald-100 rounded-lg px-4 py-3` |
| Price number | `text-[24px] font-extrabold text-emerald-600` |
| "each" unit text | `text-[15px] font-semibold text-emerald-600` |
| Price sublabel | `text-xs text-gray-400 mt-1` |
| Primary button | `w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-md text-sm` |
| Secondary button | `w-full border border-gray-200 hover:border-gray-300 text-gray-700 font-medium py-2.5 rounded-md text-sm hover:bg-gray-50` |
| Save/Share button | `flex-1 border border-gray-200 hover:border-gray-300 rounded-md py-2 text-sm text-gray-500 hover:bg-gray-50` |
| SKU pill | `bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded text-[11px] tracking-wide` |
| In Stock badge | `in-stock` → `bg-emerald-50 text-emerald-600 text-[10px] font-bold px-[7px] py-[2px] rounded` |
| Product card | `product-card` → `border border-gray-200 rounded-lg bg-white overflow-hidden` |
| Brand label (card) | `text-[10px] font-bold text-gray-400 uppercase tracking-wide` |
| Product name (card) | `text-sm font-semibold text-gray-900 leading-snug` |
| Review avatar | `reviewer-avatar` → `w-8 h-8 rounded flex items-center justify-center font-bold text-sm` |
| Spec table | `spec-table` → `border border-gray-200 rounded-lg overflow-hidden` |
| Spec label cell | `spec-cell-label` → `p-[11px_14px] font-medium text-gray-700 border-r border-gray-200` |
| Spec value cell | `spec-cell-value` → `p-[11px_14px] text-gray-700` |
| Highlight row label | `color:#10b981` |
| Highlight row value | `color:#3b82f6` |
| Fast response box | `fast-response` → `bg-emerald-50 border border-emerald-200 rounded-lg p-[14px]` |
| Protect icon circle | `protect-icon` → `w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-1.5` |
| Why Edushopify row | `why-row` → `flex items-center gap-[7px] text-[13px] text-gray-700 mb-[6px]` |
| Rating bar track | `rating-bar-bg` → `flex-1 h-[6px] bg-gray-200 rounded-full overflow-hidden` |
| Rating bar fill | `rating-bar-fill` → `h-full bg-amber-400 rounded-full` |
| Divider | `border-t border-gray-100` |
| Breadcrumb back link | `text-sm text-gray-500 hover:text-gray-900` |
| Thumbnail active | `border-2 border-emerald-500 opacity-100` |
| Thumbnail default | `border-2 border-transparent opacity-70` |
