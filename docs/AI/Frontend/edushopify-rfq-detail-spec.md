# Edushopify — RFQ Detail Page Design Specification
> Laravel + Tailwind CDN · Vanilla JS · Mobile-first · No Alpine, no Livewire

---

## Overview

**Route:** `GET /rfq/{id}`
**View:** `resources/views/rfq/show.blade.php`
**Layout:** `layouts/app.blade.php`
**Controller:** `RfqController@show`

This page is the full detail view of a single RFQ. It has a two-column layout on desktop (main content left, info sidebar right) and stacks to a single column on mobile. The sidebar stacks below content on mobile.

---

## Page Structure

```
[Navbar]
  │
  ├── [Breadcrumb]
  ├── [Page Header Row]        ← title + status badge + bid count + action buttons
  │
  ├── [Two-column layout]
  │     ├── LEFT (main — 2/3 width on lg)
  │     │     ├── [Description Card]
  │     │     ├── [Specifications Card]
  │     │     ├── [Attachments Card]
  │     │     ├── [Buyer Info Card]
  │     │     └── [Quote Submissions List]   ← visible only to RFQ owner
  │     │
  │     └── RIGHT (sidebar — 1/3 width on lg, sticky)
  │           ├── [RFQ Info Card]            ← key stats
  │           ├── [Submit Quote CTA Card]    ← for suppliers
  │           └── [Similar RFQs Card]
  │
[Footer]
```

---

## 1. CSS / Style Tokens

Carry over all custom classes from the RFQ list page. Add these page-specific classes:

```css
* { font-family: 'Inter', sans-serif; }

/* Nav */
.nav-link        { font-size:14px; font-weight:500; color:#374151; }
.nav-link:hover  { color:#111; }
.nav-link-active { font-size:14px; font-weight:600; color:#111827; background:#f3f4f6; padding:4px 12px; border-radius:6px; }

/* Status badges */
.badge-bidding { background:#fef9c3; color:#854d0e; border:1px solid #fef08a; }
.badge-posted  { background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; }
.badge-closed  { background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb; }
.badge-awarded { background:#e8f5ee; color:#1a7f45; border:1px solid #b6e0c6; }

/* Cards */
.detail-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; }

/* Quote form textarea */
.quote-textarea { border:1px solid #e5e7eb; border-radius:6px; padding:10px 12px; font-size:14px; width:100%; resize:none; outline:none; }
.quote-textarea:focus { box-shadow:0 0 0 2px #a7f3d0; border-color:#10b981; }

/* Form input shared */
.form-input { border:1px solid #e5e7eb; border-radius:6px; padding:8px 12px; font-size:14px; width:100%; outline:none; }
.form-input:focus { box-shadow:0 0 0 2px #a7f3d0; border-color:#10b981; }

/* File attachment row */
.attachment-row { border:1px solid #e5e7eb; border-radius:6px; padding:10px 12px; display:flex; align-items:center; gap:10px; }
.attachment-row:hover { background:#f9fafb; }

/* Bid/quote card */
.quote-card { border:1px solid #e5e7eb; border-radius:8px; background:#fff; }

/* Sidebar info row */
.info-row { display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid #f3f4f6; }
.info-row:last-child { border-bottom:none; }
```

---

## 2. Navbar

Identical to RFQ list page. The **RFQ** nav link is active (dark pill style).

```html
<header class="sticky top-0 z-50 bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 flex items-center gap-6 h-14">

    <!-- Logo (3×3 SVG dot grid) -->
    <a href="/" class="flex items-center gap-2 shrink-0">
      <!-- SVG dot grid -->
      <div class="flex flex-col leading-tight">
        <span class="font-bold text-gray-900 text-[17px] tracking-tight">
          Edu<span class="text-emerald-500">shopify</span>
        </span>
        <span class="text-[9px] text-gray-400 hidden sm:block">Connecting Education</span>
      </div>
    </a>

    <!-- Search -->
    <div class="flex-1 max-w-md relative hidden sm:flex">
      <input type="text" placeholder="Search suppliers, products, categories..."
        class="w-full border border-gray-200 rounded-l-md text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
      <button class="px-3 bg-emerald-500 hover:bg-emerald-600 rounded-r-md">
        <!-- search icon -->
      </button>
    </div>

    <!-- Nav links -->
    <nav class="hidden lg:flex items-center gap-1 ml-2">
      <a href="/categories" class="nav-link px-3 py-1.5">Categories</a>
      <a href="/rfq"        class="nav-link-active">RFQ</a>
      <a href="/suppliers"  class="nav-link px-3 py-1.5">Suppliers</a>
      <a href="/resources"  class="nav-link px-3 py-1.5">Resources</a>
    </nav>

    <!-- Icons + auth -->
    <div class="ml-auto flex items-center gap-3">
      <!-- heart / chat / bell icons (hidden on mobile) -->
      <a href="/login"    class="text-sm font-medium text-gray-700 hidden sm:inline">Sign In</a>
      <a href="/register" class="text-sm font-semibold bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-1.5 rounded-md">Register</a>
    </div>
  </div>
</header>
```

---

## 3. Breadcrumb

Sits directly below the navbar, inside the max-width container. Full-width `bg-white border-b border-gray-100` strip.

```html
<div class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
    <nav class="flex items-center gap-2 text-sm text-gray-500">
      <a href="/"    class="hover:text-gray-900 transition-colors">Home</a>
      <span class="text-gray-300">/</span>
      <a href="/rfq" class="hover:text-gray-900 transition-colors">RFQs</a>
      <span class="text-gray-300">/</span>
      <span class="text-gray-900 font-medium truncate max-w-xs">
        50 Interactive Displays for Schools in UAE
      </span>
    </nav>
  </div>
</div>
```

---

## 4. Page Header Row

Below the breadcrumb, inside `max-w-7xl mx-auto px-4 sm:px-6 py-6`.

```
[Status badge]  [Title — H1]                                    [Bid count]
                                              [Save btn — outline]  [Share btn — outline]  [Post a Quote btn — emerald]
```

**Mobile:** title full-width, buttons stack below in a row.

```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-7 pb-5">

  <!-- Top row: badge + bid count -->
  <div class="flex items-center justify-between mb-3">
    <span class="badge-bidding text-xs font-semibold px-2.5 py-0.5 rounded">
      bidding
    </span>
    <span class="text-sm text-gray-400 font-medium">4 bids</span>
  </div>

  <!-- Title + actions -->
  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <h1 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-snug max-w-2xl">
      50 Interactive Displays for Schools in UAE
    </h1>
    <div class="flex items-center gap-2 shrink-0">
      <!-- Save button -->
      <button class="flex items-center gap-1.5 border border-gray-200 hover:border-gray-300 text-gray-600 hover:text-gray-900 text-sm font-medium px-3 py-2 rounded-md transition-colors">
        <!-- heart icon -->
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        Save
      </button>
      <!-- Share button -->
      <button class="flex items-center gap-1.5 border border-gray-200 hover:border-gray-300 text-gray-600 hover:text-gray-900 text-sm font-medium px-3 py-2 rounded-md transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
          <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
        </svg>
        Share
      </button>
      <!-- Submit Quote — primary -->
      <a href="#submit-quote"
        class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2 rounded-md transition-colors">
        Submit a Quote
      </a>
    </div>
  </div>

  <!-- Meta row: location / budget / deadline / qty / posted -->
  <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 text-sm text-gray-500">
    <span class="flex items-center gap-1.5">
      <!-- location pin icon -->
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
      </svg>
      UAE
    </span>
    <span class="flex items-center gap-1.5">
      <!-- dollar icon -->
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="12" y1="1" x2="12" y2="23"/>
        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
      </svg>
      100,000 USD
    </span>
    <span class="flex items-center gap-1.5">
      <!-- calendar icon -->
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      Deadline: Jun 15, 2026
    </span>
    <span>Qty: 50 units</span>
    <span class="text-gray-400">·</span>
    <span class="text-gray-400">Posted Jun 1, 2026</span>
  </div>
</div>
```

---

## 5. Two-Column Layout Wrapper

```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 pb-14">
  <div class="flex flex-col lg:flex-row gap-6 items-start">

    <!-- LEFT: main content -->
    <div class="w-full lg:flex-1 flex flex-col gap-5">
      <!-- Description, Specs, Attachments, Buyer Info, Quotes -->
    </div>

    <!-- RIGHT: sidebar -->
    <div class="w-full lg:w-80 xl:w-96 shrink-0 flex flex-col gap-5 lg:sticky lg:top-20">
      <!-- Info card, Submit Quote CTA, Similar RFQs -->
    </div>

  </div>
</div>
```

---

## 6. Left Column Cards

### 6.1 Description Card

```html
<div class="detail-card p-5 sm:p-6">
  <h2 class="text-base font-semibold text-gray-900 mb-3">Description</h2>
  <p class="text-sm text-gray-600 leading-relaxed">
    Ministry of Education project requiring 50 × 75-inch interactive flat panel displays
    with built-in Android, multi-touch capability, and 3-year warranty. Installation and
    training required for 50 classrooms across Abu Dhabi.
  </p>
  <p class="text-sm text-gray-600 leading-relaxed mt-3">
    Preferred suppliers must have prior government project experience and be able to
    provide local technical support in the UAE. Delivery must be completed within 60 days
    of purchase order issuance.
  </p>
</div>
```

---

### 6.2 Specifications Card

Displays a clean key-value list. Two columns on sm+, single column on mobile.

```html
<div class="detail-card p-5 sm:p-6">
  <h2 class="text-base font-semibold text-gray-900 mb-4">Specifications</h2>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0 divide-y divide-gray-100">

    <!-- Each spec row -->
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Category</span>
      <span class="text-sm font-medium text-gray-900 text-right">AV &amp; Display</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Sub-category</span>
      <span class="text-sm font-medium text-gray-900 text-right">Interactive Displays</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Screen Size</span>
      <span class="text-sm font-medium text-gray-900 text-right">75 inches</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Quantity Required</span>
      <span class="text-sm font-medium text-gray-900 text-right">50 units</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Budget Range</span>
      <span class="text-sm font-medium text-gray-900 text-right">Up to $100,000 USD</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Warranty Required</span>
      <span class="text-sm font-medium text-gray-900 text-right">3 Years On-site</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Installation</span>
      <span class="text-sm font-medium text-gray-900 text-right">Required</span>
    </div>
    <div class="flex items-start justify-between py-3">
      <span class="text-sm text-gray-500">Training</span>
      <span class="text-sm font-medium text-gray-900 text-right">Required</span>
    </div>
    <div class="flex items-start justify-between py-3 sm:col-span-2">
      <span class="text-sm text-gray-500">Delivery Location</span>
      <span class="text-sm font-medium text-gray-900 text-right">Abu Dhabi, UAE (50 sites)</span>
    </div>
    <div class="flex items-start justify-between py-3 sm:col-span-2">
      <span class="text-sm text-gray-500">Required Certifications</span>
      <span class="text-sm font-medium text-gray-900 text-right">CE, FCC, UAE ESMA approved</span>
    </div>

  </div>
</div>
```

---

### 6.3 Attachments Card

Shown only when the RFQ owner has uploaded files. Hidden if no attachments.

```html
<div class="detail-card p-5 sm:p-6">
  <h2 class="text-base font-semibold text-gray-900 mb-4">Attachments</h2>

  <div class="flex flex-col gap-2">

    <!-- Attachment row 1 -->
    <div class="attachment-row cursor-pointer group">
      <!-- file icon -->
      <div class="w-9 h-9 bg-red-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800 truncate">RFQ_Technical_Specifications.pdf</p>
        <p class="text-xs text-gray-400">PDF · 1.2 MB</p>
      </div>
      <!-- download icon -->
      <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
    </div>

    <!-- Attachment row 2 -->
    <div class="attachment-row cursor-pointer group">
      <div class="w-9 h-9 bg-blue-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800 truncate">Site_Survey_Report.docx</p>
        <p class="text-xs text-gray-400">DOCX · 540 KB</p>
      </div>
      <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
    </div>

  </div>
</div>
```

**File icon color by type:**
- PDF → `bg-red-50 text-red-500`
- DOCX → `bg-blue-50 text-blue-500`
- XLSX → `bg-emerald-50 text-emerald-600`
- Image → `bg-purple-50 text-purple-500`
- ZIP → `bg-amber-50 text-amber-500`

---

### 6.4 Buyer Info Card

Public info about who posted the RFQ. No sensitive data exposed to guests.

```html
<div class="detail-card p-5 sm:p-6">
  <h2 class="text-base font-semibold text-gray-900 mb-4">About the Buyer</h2>

  <div class="flex items-center gap-3 mb-4">
    <!-- Buyer avatar (initials) -->
    <div class="w-11 h-11 rounded bg-emerald-100 text-emerald-700 font-bold text-base flex items-center justify-center shrink-0">
      M
    </div>
    <div>
      <p class="text-sm font-semibold text-gray-900">Ministry of Education</p>
      <p class="text-xs text-gray-500">Government Institution · UAE</p>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div class="bg-gray-50 rounded-md p-3 text-center">
      <p class="text-lg font-bold text-gray-900">14</p>
      <p class="text-xs text-gray-500 mt-0.5">RFQs Posted</p>
    </div>
    <div class="bg-gray-50 rounded-md p-3 text-center">
      <p class="text-lg font-bold text-gray-900">9</p>
      <p class="text-xs text-gray-500 mt-0.5">Awarded</p>
    </div>
  </div>

  <p class="text-xs text-gray-400 mt-4 flex items-center gap-1.5">
    <!-- shield check icon -->
    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      <polyline points="9 12 11 14 15 10"/>
    </svg>
    Verified institutional buyer
  </p>
</div>
```

---

### 6.5 Quote Submissions List

**Visibility:** Shown only to the RFQ owner (buyer). Hidden for guests and other suppliers. Suppliers see only their own submitted quote.

```html
<!-- @if(auth()->id() === $rfq->user_id) -->
<div class="detail-card p-5 sm:p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-base font-semibold text-gray-900">Quotes Received</h2>
    <span class="text-sm text-gray-400">4 quotes</span>
  </div>

  <div class="flex flex-col gap-3">

    <!-- Quote card 1 -->
    <div class="quote-card p-4">
      <div class="flex items-start justify-between gap-3 mb-2">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded bg-blue-100 text-blue-700 font-bold text-sm flex items-center justify-center shrink-0">V</div>
          <div>
            <p class="text-sm font-semibold text-gray-900">ViewSonic Education</p>
            <p class="text-xs text-gray-400">Submitted Jun 3, 2026</p>
          </div>
        </div>
        <div class="text-right shrink-0">
          <p class="text-sm font-bold text-gray-900">$92,500</p>
          <p class="text-xs text-gray-400">Total bid</p>
        </div>
      </div>
      <p class="text-xs text-gray-500 leading-relaxed mb-3">
        We propose ViewBoard IFP7550-5 75" panels — Android 11, 20-point multitouch, 4K UHD.
        Includes full installation, 3-yr on-site warranty, and 2-day teacher training per site.
      </p>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 text-xs text-gray-500">
          <span>Delivery: 45 days</span>
          <span>·</span>
          <span>Warranty: 3 years</span>
        </div>
        <div class="flex gap-2">
          <button class="text-xs border border-gray-200 hover:border-gray-300 text-gray-600 px-3 py-1.5 rounded-md">View Full Quote</button>
          <button class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-3 py-1.5 rounded-md">Award</button>
        </div>
      </div>
    </div>

    <!-- Quote card 2 -->
    <div class="quote-card p-4">
      <div class="flex items-start justify-between gap-3 mb-2">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded bg-red-100 text-red-700 font-bold text-sm flex items-center justify-center shrink-0">S</div>
          <div>
            <p class="text-sm font-semibold text-gray-900">SMART Technologies</p>
            <p class="text-xs text-gray-400">Submitted Jun 4, 2026</p>
          </div>
        </div>
        <div class="text-right shrink-0">
          <p class="text-sm font-bold text-gray-900">$97,000</p>
          <p class="text-xs text-gray-400">Total bid</p>
        </div>
      </div>
      <p class="text-xs text-gray-500 leading-relaxed mb-3">
        Offering SMART Board 7000R series with iQ system, 4K display, and unlimited software licenses.
        ESMA-certified and CE/FCC compliant. Installation + 3-yr warranty included.
      </p>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 text-xs text-gray-500">
          <span>Delivery: 50 days</span>
          <span>·</span>
          <span>Warranty: 3 years</span>
        </div>
        <div class="flex gap-2">
          <button class="text-xs border border-gray-200 hover:border-gray-300 text-gray-600 px-3 py-1.5 rounded-md">View Full Quote</button>
          <button class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-3 py-1.5 rounded-md">Award</button>
        </div>
      </div>
    </div>

    <!-- Quote card 3 (collapsed — "Show more" pattern) -->
    <button id="show-more-quotes" onclick="toggleMoreQuotes()"
      class="text-sm text-emerald-600 hover:text-emerald-700 font-medium text-center py-2">
      Show 2 more quotes ↓
    </button>
    <div id="extra-quotes" class="hidden flex flex-col gap-3">
      <!-- additional quote cards -->
    </div>

  </div>
</div>
<!-- @endif -->
```

---

## 7. Right Sidebar Cards

### 7.1 RFQ Info Card

The key stats summary — always visible to everyone.

```html
<div class="detail-card p-5">
  <h3 class="text-sm font-semibold text-gray-900 mb-1">RFQ Details</h3>
  <p class="text-xs text-gray-400 mb-4">Reference #RFQ-2026-00471</p>

  <!-- Info rows -->
  <div class="divide-y divide-gray-100">

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <!-- tag icon -->
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
          <line x1="7" y1="7" x2="7.01" y2="7"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Category</p>
        <p class="text-sm font-medium text-gray-900">AV &amp; Display</p>
      </div>
    </div>

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Budget</p>
        <p class="text-sm font-medium text-gray-900">Up to $100,000 USD</p>
      </div>
    </div>

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Submission Deadline</p>
        <p class="text-sm font-medium text-gray-900">Jun 15, 2026</p>
        <p class="text-xs text-red-500 mt-0.5">11 days remaining</p>
      </div>
    </div>

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Delivery Country</p>
        <p class="text-sm font-medium text-gray-900">UAE</p>
      </div>
    </div>

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
          <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Quantity</p>
        <p class="text-sm font-medium text-gray-900">50 units</p>
      </div>
    </div>

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Posted</p>
        <p class="text-sm font-medium text-gray-900">Jun 1, 2026</p>
      </div>
    </div>

    <div class="info-row">
      <div class="w-8 h-8 bg-gray-50 rounded flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
      <div>
        <p class="text-xs text-gray-400">Quotes Received</p>
        <p class="text-sm font-medium text-gray-900">4 bids</p>
      </div>
    </div>

  </div>
</div>
```

---

### 7.2 Submit Quote CTA Card

**Visibility logic:**
- **Guest / not logged in:** Show a "Sign in to submit a quote" prompt
- **Logged-in supplier, not yet submitted:** Show the full form
- **Logged-in supplier, already submitted:** Show their submitted quote summary + "Edit Quote" button
- **Buyer / admin:** Hide this card entirely

```html
<!-- ── Guest state ── -->
<div class="detail-card p-5" id="quote-cta-guest">
  <p class="text-sm font-semibold text-gray-900 mb-1">Interested in this RFQ?</p>
  <p class="text-xs text-gray-500 mb-4 leading-relaxed">
    Sign in as a verified supplier to submit a competitive quote.
  </p>
  <a href="/login"
    class="block text-center bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-md mb-2">
    Sign In to Submit Quote
  </a>
  <a href="/register"
    class="block text-center border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-md">
    Register as Supplier
  </a>
</div>

<!-- ── Supplier: Submit form (id="submit-quote") ── -->
<div class="detail-card p-5" id="submit-quote">
  <h3 class="text-sm font-semibold text-gray-900 mb-4">Submit Your Quote</h3>

  <form method="POST" action="{{ route('rfq.quote.store', $rfq->id) }}" enctype="multipart/form-data">
    @csrf

    <!-- Unit Price -->
    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-700 mb-1">
        Unit Price (USD) <span class="text-red-500">*</span>
      </label>
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
        <input type="number" name="unit_price" placeholder="0.00"
          class="form-input pl-7"
          required />
      </div>
    </div>

    <!-- Total Price -->
    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-700 mb-1">
        Total Quote Amount (USD) <span class="text-red-500">*</span>
      </label>
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
        <input type="number" name="total_price" placeholder="0.00"
          class="form-input pl-7"
          required />
      </div>
    </div>

    <!-- Delivery Days -->
    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-700 mb-1">
        Estimated Delivery (days) <span class="text-red-500">*</span>
      </label>
      <input type="number" name="delivery_days" placeholder="e.g. 45"
        class="form-input" required />
    </div>

    <!-- Validity -->
    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-700 mb-1">Quote Valid Until</label>
      <input type="date" name="valid_until" class="form-input" />
    </div>

    <!-- Proposal / Message -->
    <div class="mb-3">
      <label class="block text-xs font-medium text-gray-700 mb-1">
        Proposal / Notes <span class="text-red-500">*</span>
      </label>
      <textarea name="proposal" rows="5" placeholder="Describe your product offer, warranty terms, and any relevant experience..."
        class="quote-textarea" required></textarea>
    </div>

    <!-- Attachment -->
    <div class="mb-4">
      <label class="block text-xs font-medium text-gray-700 mb-1">Attach Quote PDF (optional)</label>
      <input type="file" name="quote_file" accept=".pdf,.docx,.xlsx"
        class="w-full text-xs text-gray-500
          file:mr-3 file:py-1.5 file:px-3
          file:rounded file:border-0
          file:text-xs file:font-medium
          file:bg-gray-100 file:text-gray-700
          hover:file:bg-gray-200 cursor-pointer" />
      <p class="text-[11px] text-gray-400 mt-1">PDF, DOCX or XLSX · max 5MB</p>
    </div>

    <!-- Terms checkbox -->
    <label class="flex items-start gap-2 mb-4 cursor-pointer">
      <input type="checkbox" name="agree_terms" required
        class="mt-0.5 w-4 h-4 rounded accent-emerald-500 shrink-0" />
      <span class="text-xs text-gray-500 leading-relaxed">
        I confirm this quote is accurate and I am authorised to submit on behalf of my company.
      </span>
    </label>

    <button type="submit"
      class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-md transition-colors">
      Submit Quote
    </button>

    <p class="text-[11px] text-gray-400 text-center mt-3">
      The buyer will receive your quote and may contact you directly.
    </p>
  </form>
</div>

<!-- ── Supplier: Already submitted ── -->
<div class="detail-card p-5" id="quote-submitted">
  <div class="flex items-center gap-2 mb-3">
    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
      <path d="M20 6 9 17l-5-5"/>
    </svg>
    <p class="text-sm font-semibold text-emerald-700">Quote Submitted</p>
  </div>
  <div class="bg-gray-50 rounded-md p-3 text-sm mb-3 divide-y divide-gray-100">
    <div class="flex justify-between py-2"><span class="text-gray-500">Total Amount</span><span class="font-semibold text-gray-900">$92,500</span></div>
    <div class="flex justify-between py-2"><span class="text-gray-500">Delivery</span><span class="font-medium text-gray-700">45 days</span></div>
    <div class="flex justify-between py-2"><span class="text-gray-500">Submitted</span><span class="font-medium text-gray-700">Jun 3, 2026</span></div>
  </div>
  <button class="w-full border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-md">
    Edit My Quote
  </button>
</div>
```

---

### 7.3 Similar RFQs Card

Shows 3 related RFQs based on category. Each is a compact clickable row.

```html
<div class="detail-card p-5">
  <h3 class="text-sm font-semibold text-gray-900 mb-4">Similar RFQs</h3>

  <div class="flex flex-col gap-0 divide-y divide-gray-100">

    <!-- Similar RFQ row -->
    <a href="/rfq/2" class="flex items-start justify-between gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-md transition-colors group">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800 group-hover:text-emerald-600 leading-snug line-clamp-2">
          Digital Projectors for 20 Classrooms — Kuwait
        </p>
        <p class="text-xs text-gray-400 mt-1">$40,000 · Deadline Jun 25</p>
      </div>
      <span class="badge-posted text-[10px] font-semibold px-2 py-0.5 rounded shrink-0 mt-0.5">posted</span>
    </a>

    <a href="/rfq/3" class="flex items-start justify-between gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-md transition-colors group">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800 group-hover:text-emerald-600 leading-snug line-clamp-2">
          Audio-Visual Systems for Dubai Conference Rooms
        </p>
        <p class="text-xs text-gray-400 mt-1">$75,000 · Deadline Jul 15</p>
      </div>
      <span class="badge-posted text-[10px] font-semibold px-2 py-0.5 rounded shrink-0 mt-0.5">posted</span>
    </a>

    <a href="/rfq/4" class="flex items-start justify-between gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-md transition-colors group">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800 group-hover:text-emerald-600 leading-snug line-clamp-2">
          Smart Boards for 100 Primary Schools — Egypt
        </p>
        <p class="text-xs text-gray-400 mt-1">$200,000 · Deadline Jul 1</p>
      </div>
      <span class="badge-bidding text-[10px] font-semibold px-2 py-0.5 rounded shrink-0 mt-0.5">bidding</span>
    </a>

  </div>

  <a href="/rfq" class="block text-center text-sm text-emerald-600 hover:text-emerald-700 font-medium mt-4">
    Browse all RFQs →
  </a>
</div>
```

---

## 8. Status Badge Reference

| Status | Class | Colors |
|---|---|---|
| `posted` | `.badge-posted` | `bg-dbeafe / text-1d4ed8 / border-bfdbfe` |
| `bidding` | `.badge-bidding` | `bg-fef9c3 / text-854d0e / border-fef08a` |
| `awarded` | `.badge-awarded` | `bg-e8f5ee / text-1a7f45 / border-b6e0c6` |
| `closed` | `.badge-closed` | `bg-f3f4f6 / text-6b7280 / border-e5e7eb` |

---

## 9. Mobile Behaviour

| Element | Mobile | Desktop |
|---|---|---|
| Layout | Single column, stacked | 2/3 + 1/3 flex row |
| Sidebar | Below main content | Sticky, right column |
| Page header actions | Stacked below title | Inline right of title |
| Specs grid | 1 col | 2 col |
| Meta row | Wraps to 2 rows | Single row |
| Quote form | Full width | Full width within sidebar card |
| Attachments | Full width rows | Full width rows |
| Similar RFQs | Full width rows | Full width rows |

---

## 10. JavaScript

### 10.1 Show/hide more quotes
```javascript
function toggleMoreQuotes() {
  const extra = document.getElementById('extra-quotes');
  const btn   = document.getElementById('show-more-quotes');
  const open  = !extra.classList.contains('hidden');
  extra.classList.toggle('hidden', open);
  btn.textContent = open ? 'Show 2 more quotes ↓' : 'Show less ↑';
}
```

### 10.2 Auto-calculate total price from unit × qty
```javascript
const unitInput  = document.querySelector('[name="unit_price"]');
const totalInput = document.querySelector('[name="total_price"]');
const qty = {{ $rfq->quantity ?? 1 }};

unitInput?.addEventListener('input', function () {
  const total = (parseFloat(this.value) || 0) * qty;
  totalInput.value = total > 0 ? total.toFixed(2) : '';
});
```

### 10.3 Deadline countdown
```javascript
(function () {
  const deadline = new Date('{{ $rfq->deadline }}');
  const now      = new Date();
  const diff     = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24));
  const el       = document.getElementById('deadline-countdown');
  if (!el) return;
  if (diff > 0) {
    el.textContent = diff + ' days remaining';
    el.className   = 'text-xs text-red-500 mt-0.5';
  } else if (diff === 0) {
    el.textContent = 'Closes today';
    el.className   = 'text-xs text-red-600 font-semibold mt-0.5';
  } else {
    el.textContent = 'Deadline passed';
    el.className   = 'text-xs text-gray-400 mt-0.5';
  }
})();
```

### 10.4 Share button (Web Share API with clipboard fallback)
```javascript
document.querySelector('[data-action="share"]')?.addEventListener('click', async function () {
  const data = {
    title: document.title,
    text:  'Check out this RFQ on Edushopify',
    url:   window.location.href,
  };
  try {
    if (navigator.share) {
      await navigator.share(data);
    } else {
      await navigator.clipboard.writeText(window.location.href);
      showToast('Link copied to clipboard');
    }
  } catch (e) { /* cancelled */ }
});

function showToast(msg) {
  const t = document.createElement('div');
  t.textContent = msg;
  t.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-sm px-4 py-2 rounded-md shadow-lg z-50 transition-opacity';
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
}
```

---

## 11. Laravel Blade Controller Notes

```php
// RfqController.php
public function show(Rfq $rfq)
{
    $rfq->load(['user', 'category', 'quotes.supplier', 'attachments']);

    $similar = Rfq::where('category_id', $rfq->category_id)
        ->where('id', '!=', $rfq->id)
        ->where('status', '!=', 'closed')
        ->latest()
        ->take(3)
        ->get();

    $userQuote = null;
    if (auth()->check() && auth()->user()->isSupplier()) {
        $userQuote = $rfq->quotes()->where('supplier_id', auth()->id())->first();
    }

    return view('rfq.show', compact('rfq', 'similar', 'userQuote'));
}
```

**Key variables passed to view:**
| Variable | Type | Use |
|---|---|---|
| `$rfq` | `Rfq` model | Main RFQ object with relations |
| `$similar` | Collection | 3 related RFQs for sidebar |
| `$userQuote` | `Quote\|null` | Supplier's own quote, if submitted |

**Blade conditionals:**
```blade
{{-- Show quote form only to suppliers who haven't quoted yet --}}
@if(auth()->check() && auth()->user()->isSupplier() && !$userQuote)
  {{-- submit form --}}
@elseif($userQuote)
  {{-- already submitted card --}}
@else
  {{-- guest CTA --}}
@endif

{{-- Show all quotes only to RFQ owner --}}
@if(auth()->id() === $rfq->user_id)
  {{-- quotes received list --}}
@endif
```

---

## 12. Quick Class Reference

| Element | Tailwind classes |
|---|---|
| Page wrapper | `max-w-7xl mx-auto px-4 sm:px-6` |
| Section card | `detail-card p-5 sm:p-6` (custom class) |
| Card heading | `text-base font-semibold text-gray-900 mb-4` |
| Spec label | `text-sm text-gray-500` |
| Spec value | `text-sm font-medium text-gray-900` |
| Meta icon | `w-4 h-4 text-gray-400` |
| Meta text | `text-sm text-gray-500` |
| Primary button | `bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-md` |
| Outline button | `border border-gray-200 hover:border-gray-300 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-md` |
| Danger/small btn | `text-xs border border-gray-200 text-gray-600 px-3 py-1.5 rounded-md hover:border-gray-300` |
| Form input | `form-input` (custom class) |
| Countdown red | `text-xs text-red-500 mt-0.5` |
| Info row | `info-row` (custom class — flex, border-b, py-2.5 gap-3) |
| Buyer stat box | `bg-gray-50 rounded-md p-3 text-center` |
| Attachment row | `attachment-row` (custom class — flex, border, rounded, p-3) |
| Quote card | `quote-card p-4` (custom class — border, rounded-lg) |
| Similar RFQ row | `py-3 hover:bg-gray-50 -mx-2 px-2 rounded-md transition-colors group` |
| Sidebar sticky | `lg:sticky lg:top-20` |
