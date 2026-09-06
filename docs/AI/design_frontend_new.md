# EduShopify Frontend V2 (`frontend_new`) — Design & Coding Reference — `design_frontend_new.md`

> **Status:** Descriptive reference for the ACTUAL, already-implemented `frontend_new` ("v2") public frontend.
>
> This document records what the code really does today — real colors, real file paths, real conventions — so a new page can be added consistently with what already exists. It is **not** an aspirational spec. Where the shipped code diverges from the spec documents in `docs/AI/Frontend/`, this file says so explicitly and documents reality.
>
> **Do not confuse this with `docs/AI/design_frontend.md`.** That file describes the older, separate `resources/views/frontend/` system (routes/frontend.php, `App\Http\Controllers\Frontend\`). `frontend_new` is a from-scratch, independent rebuild — different CSS approach, different fonts, different component style, different JS strategy. Never port a `design_frontend.md` rule into `frontend_new` just because it "sounds standard" — check this file, or the code, instead.

---

## 1. What `frontend_new` is, and where things live

Fresh, independent rebuild of the public site, reproducing the mockups in `docs/AI/Frontend/*.html`, developed side-by-side with the legacy frontend under a `/v2` URL prefix so both can run at once.

```text
routes/frontend_new.php                         → all routes, prefix('v2')->name('v2.')
app/Http/Controllers/FrontendNew/                → BlogController, CategoryController,
                                                     HandoffController, HomeController,
                                                     ProductController, ResourceController,
                                                     RfqController, SupplierController
resources/views/frontend_new/                    → all Blade views (see tree below)
resources/css/frontend_new.css                   → all CSS (Tailwind v4 + hand-written classes)
resources/js/frontend_new.js                     → all JS (vanilla, single file)
```

Never mix this with the legacy equivalents (`resources/views/frontend/`, `routes/frontend.php`, `App\Http\Controllers\Frontend\`, `resources/css/frontend.css`, `resources/js/frontend.js`). Controllers here carry doc-comments explaining *why* they're independent — keep that convention when adding new ones.

### 1.1 View directory tree

```text
resources/views/frontend_new/
├── layouts/
│   └── app.blade.php                  ← the one shared shell, @yield('content')
├── partials/
│   ├── navbar.blade.php
│   ├── footer.blade.php
│   └── mobile-menu.blade.php
├── components/
│   └── supplier-card.blade.php        ← NOT a real Blade component, see §4
├── home/
│   ├── index.blade.php
│   └── partial/                        ← singular "partial" — inconsistent with every
│       ├── _hero.blade.php               other page folder below, which uses "partials".
│       ├── _featured_suppliers.blade.php This is a known typo baked into the codebase,
│       ├── _all_suppliers.blade.php      not a convention to imitate for new page types.
│       ├── _why_choose.blade.php
│       └── _events.blade.php
├── categories/
│   ├── index.blade.php
│   └── partials/
│       ├── _hero.blade.php
│       └── _content.blade.php
├── suppliers/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── _hero.blade.php
│       └── _content.blade.php
├── products/
│   └── show.blade.php                  ← single file, no partials/ subfolder yet
├── blogs/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── _hero.blade.php
│       └── _content.blade.php
├── rfqs/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── partials/
│       ├── _hero.blade.php
│       ├── _content.blade.php
│       ├── _card.blade.php
│       └── _skeleton.blade.php
└── resources/
    ├── index.blade.php
    └── partials/
        ├── _hero.blade.php
        ├── _categories.blade.php
        ├── _content.blade.php
        └── _features.blade.php
```

**Convention for a new page type:** `resources/views/frontend_new/{page}/index.blade.php` (or `show.blade.php`) + `resources/views/frontend_new/{page}/partials/_hero.blade.php` + `_content.blade.php`. Spell the folder **`partials`** (plural) — `home/partial` is a pre-existing typo, not something to copy.

`_hero` = the page banner/header block. `_content` = the actual results/listing body — and, per §5, is often also the exact fragment re-rendered for AJAX responses, so keep it self-contained (it must render correctly both inside the full page and stand-alone).

### 1.2 The shared layout

`resources/views/frontend_new/layouts/app.blade.php` in full:

```blade
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Edushopify – Global Suppliers for Education')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/frontend_new.css', 'resources/js/frontend_new.js'])
    @stack('head')
</head>
<body class="@yield('body_class', 'bg-white') text-gray-800 antialiased">
    @include('frontend_new.partials.mobile-menu')
    @include('frontend_new.partials.navbar')
    @yield('content')
    @include('frontend_new.partials.footer')
    @stack('scripts')
</body>
</html>
```

Every page:

```blade
@extends('frontend_new.layouts.app')

@section('title', 'Descriptive, SEO-flavored title | Edushopify')
@section('body_class', 'bg-gray-50')   {{-- optional; homepage omits it and gets the bg-white default --}}

@section('content')
    @include('frontend_new.{page}.partials._hero', [...])
    @include('frontend_new.{page}.partials._content', [...])
@endsection
```

`@stack('head')` / `@stack('scripts')` are available for page-specific `<meta>` or inline `<script>` — use `@push('scripts')...@endpush` in a page rather than dumping ad-hoc `<script>` tags outside the stack.

**Dead code to be aware of, not to copy:** the Font Awesome and Bootstrap Icons CDN links in the head are loaded on every page but **unused** — zero `fa-solid`, `fas fa-`, or `bi bi-` classes appear anywhere in `frontend_new` views (verified by grep). Every icon actually shipped is inline SVG (§6). Don't reach for an FA/Bootstrap icon class thinking it's wired up — it visually won't be styled as intended since the actual icon system is inline SVG; use inline SVG for any new icon instead.

---

## 2. Tech stack (confirmed, not aspirational)

```text
Templating   Laravel Blade
CSS          Tailwind CSS v4 (via @tailwindcss/vite), plus a large block of
             hand-written classes with hardcoded hex colors in one file
JS           Vanilla JavaScript only — explicitly no Alpine.js, no Livewire,
             no React/Vue (see resources/js/frontend_new.js's own header comment)
Icons        Inline SVG only
Fonts        Google Fonts "Inter" (weights 300–800) — that's the only font loaded
Build        Vite (@vite(['resources/css/frontend_new.css', 'resources/js/frontend_new.js']))
```

This matches the tech-stack line in `docs/AI/Frontend/edushopify-public-frontend-complete-spec.md` ("Laravel Blade · Tailwind CSS · Vanilla JavaScript · No Alpine · No Livewire · No React/Vue") — that part of the spec **was** followed. Do not introduce Alpine directives (`x-data`, `x-show`) or `wire:*` attributes anywhere under `resources/views/frontend_new/` — there are currently zero, and mixing in a second reactive framework here would be a real regression, not a small stylistic slip.

---

## 3. Design tokens (as actually written in `resources/css/frontend_new.css`)

**There are no CSS custom properties (`:root { --x: ... }`) anywhere in this file** — that's a real, confirmed difference from the legacy `design_frontend.md`'s CSS-variable-token approach. Colors are flat hardcoded hex values and Tailwind utility classes, mixed with a block of hand-written component classes. The file's own header says these classes were "copied verbatim from the static HTML references in `docs/AI/Frontend/*.html`, grouped per page as each phase lands" — so it's organized as sequential per-page sections, each with a comment banner naming its source mockup file, e.g. `/* Product detail — docs/AI/Frontend/edushopify-product-detail.html */`.

### 3.1 Colors actually in use

```text
Primary green (CTAs, active states, badges)   #10b981   (Tailwind emerald-500)
Hover/dark green                              #059669   (emerald-600)
Light green accent (logo mark SVG)            #6ee7b7   (emerald-300)
Star rating amber                             #f59e0b

Badge — Verified                              bg #e8f5ee  text #1a7f45  border #b6e0c6
Badge — Founding Member                       bg #fff8e6  text #a16207  border #fde68a
Badge — ISE Exhibitor                         bg #eef2ff  text #4338ca  border #c7d2fe
Badge — BETT                                  bg #f5f3ff  text #6d28d9  border #ddd6fe
Badge — Manufacturer                          bg #f3f4f6  text #374151
Badge — RFQ Bidding                           bg #fef9c3  text #854d0e  border #fef08a
Badge — RFQ Posted                            bg #dbeafe  text #1d4ed8  border #bfdbfe
Badge — RFQ Closed                            bg #f3f4f6  text #6b7280  border #e5e7eb
Badge — RFQ Awarded                           bg #e8f5ee  text #1a7f45  border #b6e0c6

Neutral/gray scale (borders, muted text)      #e5e7eb, #f3f4f6, #9ca3af, #374151, #111827
```

The brand color happens to be Emerald (same family the legacy frontend also uses), but that's coincidence, not inheritance — both were independently driven off the same `docs/AI/Frontend/*.html` mockups. Use `#10b981`/`#059669` (or Tailwind's `emerald-500`/`emerald-600` utilities, which resolve to the same values) for any new primary CTA/active state; don't invent a new accent color.

### 3.2 Typography

Only **Inter** is loaded, applied globally:

```css
* { font-family: 'Inter', sans-serif; }
```

There is no second "display" typeface (no Outfit, unlike the legacy frontend). Headings and body copy both use Inter; differentiate by weight/size utility classes, not by font-family swapping.

### 3.3 Border radius — check actual markup, not just the spec text

The spec documents (`edushopify-frontend-spec.md` / `edushopify-public-frontend-complete-spec.md`, §1.4) state badges/pills use a flat `rounded` (4px) and explicitly *not* `rounded-full`. **The shipped markup does not consistently follow that rule** — badge spans in `home/partial/_all_suppliers.blade.php` combine the custom `badge-verified`/`badge-founding`/`badge-ise` classes (which only set background/text/border color, no radius) with Tailwind's `rounded-full` utility applied directly in the blade markup, e.g.:

```blade
<span class="badge-verified text-[10px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">✓ Verified</span>
```

...and the supplier-profile-page-scoped badge overrides in the CSS itself hardcode `border-radius: 99px` (effectively a pill). So **in practice, badges are pill-shaped**, not 4px-flat. General card/panel radius elsewhere is small and flat (8–12px, e.g. `.supplier-card { border-radius: 12px }`, `.spec-table { border-radius: 8px }`), and avatars/icon circles use `border-radius: 50%`. When building a new badge, follow what's actually rendered (pill, via `rounded-full`) rather than the spec text; when building a new card/panel, use the flat 8–12px family.

### 3.4 Component CSS classes worth knowing before writing new ones

```text
.nav-link / .nav-link-active   navbar item styling + active state (background #f3f4f6 when active)
.supplier-card                 border + 12px radius + hover shadow transition
.event-card / .event-badge     homepage events section
.filter-btn                    small bordered filter chip
.spec-table                    bordered table wrapper, 8px radius
.rating-bar-bg / .rating-bar-fill   star/rating progress bar (amber fill)
.reviewer-avatar                6px-radius initials avatar
.product-card                  10px-radius bordered card
.sidebar-card                  10px-radius bordered panel (product/supplier detail sidebars)
.thumb                         gallery thumbnail, 6px radius, opacity/border transition on select
.protect-icon / .fast-response  small circular icon badge / soft-green info panel
.badge-bidding / .badge-posted / .badge-closed / .badge-awarded   RFQ status pills
```

Check this list (and the relevant per-page comment banner in `frontend_new.css`) before writing new component CSS from scratch — a similar class may already exist.

---

## 4. Components: `@include` partials only — no `<x-...>` components

`app/Providers/AppServiceProvider.php` registers an anonymous Blade component namespace for the **legacy** frontend only:

```php
// Public frontend components live under resources/views/frontend/components/
// (frontend_workflow.md Part 19), not the default components/ root, so they
// need an explicit anonymous-component namespace: <x-frontend::marketplace.listing-card>.
Blade::anonymousComponentNamespace('frontend.components', 'frontend');
```

**There is no equivalent for `frontend_new`.** The one file under `resources/views/frontend_new/components/supplier-card.blade.php` is explicitly *not* a real Blade component — its own top comment says so, and it's invoked as a plain include:

```blade
@include('frontend_new.components.supplier-card', ['supplier' => $supplier])
```

used from `suppliers/show.blade.php`, `home/partial/_featured_suppliers.blade.php`, and `suppliers/partials/_content.blade.php`.

**Convention: build shareable UI as `@include`d partials with an explicit data array, not as `<x-...>` tag components.** If you introduce a new reusable piece (e.g. a product card), put it at `resources/views/frontend_new/components/{name}.blade.php` and include it the same way — don't register a new component namespace unless you deliberately decide to change this convention project-wide.

---

## 5. Controllers → views → data shape

Controllers pass **raw Eloquent collections/models straight into the view** — no DTOs, no array transformation for normal page renders. Where a page needs computed values that aren't real columns, controllers bolt them on as dynamic properties on the model instance right before passing it to the view, e.g. `$supplier->matched_categories = ...`, `$supplier->demo_founding = ...`, `$listing->priceMin` is instead passed as a **separate named view variable**, not a model property — both patterns exist, so check the specific controller method you're extending rather than assuming one style universally.

```text
HomeController::index        → frontend_new.home.index
                                Category / SupplierProfile (via PublicSupplierQuery) / BlogPost
                                collections passed directly, with ad-hoc properties added in-place
                                (->matched_categories, ->demo_founding, ->demo_ise, ->product_count)

CategoryController::index    → frontend_new.categories.index (or JSON for ajax()/live-search)
                                Category collection + ->listing_count; paginated Listing via
                                PublicListingQuery

SupplierController::index    → frontend_new.suppliers.index full page, OR
                                frontend_new.suppliers.partials._content directly when
                                $request->ajax() — same $data array powers both
                                (PublicSupplierQuery::base()->paginate()->through(...))

SupplierController::show     → frontend_new.suppliers.show
                                one SupplierProfile (many eager-loaded relations) +
                                ~15 separately named computed scalars/collections
                                ($rfqsCompleted, $countriesServed, $avgResponseHours,
                                $yearsInBusiness, $services, $certifications, $achievements, ...)

ProductController::show      → frontend_new.products.show
                                one Listing (eager-loaded) + $priceMin/$priceMax/$dealsCount/
                                $yearsActive + $relatedProducts collection

BlogController::index        → frontend_new.blogs.index, or hand-mapped JSON array for ajax()
                                (title, excerpt, cover_image via $post->coverImageUrl(), url via
                                route('v2.blogs.show', ...))
BlogController::show         → frontend_new.blogs.show — BlogPost + nested comment/reply eager
                                loads + $hasLikedPost / $likedCommentIds against current account
BlogController::togglePostLike / storeComment / toggleCommentLike
                              → no view — JSON API-style responses only

ResourceController::index    → frontend_new.resources.index
                                NOT Eloquent — plain arrays from config('frontend_new_demo.articles')
                                / .events. This page is fully static/config-backed.

RfqController::index         → frontend_new.rfqs.index
                                paginated RfqPublicSummary (a query-object/view model, not a raw
                                table model) via ->globalVisibility()->stillOpen()
RfqController::show          → frontend_new.rfqs.show — one RfqPublicSummary + $similar collection

HandoffController             → no views — pure redirects bridging public v2 CTAs into the
                                authenticated buyer/supplier flows
```

**AJAX / live-search pattern is not uniform** — pick one deliberately per page, matching whichever sibling page is closest:

- `CategoryController` / `BlogController` index: detect `$request->ajax()`/`expectsJson()` → hand-map to a plain JSON array.
- `SupplierController::index`: detect `$request->ajax()` → return the **same** `_content` partial re-rendered server-side with the same `$data`, instead of JSON. This means `_content.blade.php` must be a fully self-contained, valid fragment on its own.

**Pagination markup is also not uniform**: `blogs`/`categories` `_content` partials use Laravel's default `{{ $collection->links() }}`; `rfqs/partials/_content.blade.php` hand-rolls custom Previous/Next markup via `$opportunities->previousPageUrl()`/`nextPageUrl()`. Match whichever style the page family you're extending already uses.

---

## 6. Icons: inline SVG, always

Every icon in shipped `frontend_new` markup — nav icons, stars, badge glyphs, card icons — is a hand-written inline `<svg>...</svg>`. There are 80+ inline SVGs across the current views and zero icon-font class usages. New icons should be inline SVG too, sized/colored via Tailwind utility classes on the `<svg>`/`<path>` (e.g. `class="w-5 h-5 text-emerald-500"`), matching the existing pattern rather than pulling in the (currently dead-weight) Font Awesome/Bootstrap Icons CDN links.

---

## 7. JavaScript conventions

Single file, `resources/js/frontend_new.js`, vanilla only. Its own header:

```js
/*
 * Edushopify Frontend V2 — vanilla JS only (per spec: no Alpine, no Livewire).
 * Grows per phase as each static page's own <script> block is ported over.
 * Independent of resources/js/frontend.js (legacy).
 *
 * Functions invoked from inline onclick="" attributes in Blade views are
 * assigned to `window` explicitly. Without that, Vite/Rollup treats them as
 * unused module-scope declarations (it can't see references inside HTML
 * attribute strings) and tree-shakes them out of the production bundle —
 * silently breaking every onclick that calls them.
 */
```

Concrete conventions to follow for new behavior:

- **Naming:** functions meant to be called from `onclick=""` are named `fnXxx` (e.g. `fnOpenMobileMenu`, `fnCloseMobileMenu`) and explicitly assigned to `window` (`window.fnOpenMobileMenu = fnOpenMobileMenu;` — check the bottom of the file for the exact export block before adding a new one).
- **DOM access:** plain `document.getElementById` / `querySelectorAll`, no jQuery, no framework.
- **Page-scoping without a bundler-per-page:** since one JS bundle loads on every page, page-specific behavior guards itself, e.g. `if (document.querySelector('.supplier-item')) { ... }`, so it safely no-ops on pages that don't have that element.
- **Skeleton loading:** a generic `data-skeleton-target` attribute convention drives a reveal-on-load IIFE — reuse it for new async-feeling sections rather than writing a bespoke skeleton mechanism (see `rfqs/partials/_skeleton.blade.php` for the markup half).
- Add new page behavior as a new guarded block in this same file, per the header's own stated pattern ("grows per phase as each static page's own `<script>` block is ported over") — don't create a second JS entry point without a real reason.

---

## 8. Routing & linking conventions

- All routes are named under the `v2.` prefix (`Route::prefix('v2')->name('v2.')` in `routes/frontend_new.php`): `v2.home`, `v2.categories.index`, `v2.suppliers.index`/`.show`, `v2.products.show`, `v2.blogs.index`/`.show`/`.like`/`.comment`/`.comment.like`, `v2.resources.index`, `v2.rfqs.index`/`.show`, `v2.handoff.submit-quotation`/`.post-rfq`.
- **Every internal link uses a named route** — `route('v2.xxx', ...)`. There are zero hardcoded `/v2/...` path strings in any blade file; keep it that way.
- Model route-binding by slug: `{supplier:slug}`, `{listing:slug}`, `{blogPost:slug}`.
- Active-nav-state check: `request()->routeIs('v2.categories.*') ? 'nav-link-active' : 'nav-link'` — wildcard `routeIs()` per nav item, toggling the two CSS classes from §3.4, not raw Tailwind background utilities.
- `asset()` for static files under `public/` (logo: `asset('storage/System_Files/Logo/edushopifyLogo.png')`; favicon: `asset('images/favicon.png')`). For a model-owned uploaded file (e.g. a supplier banner), use `Storage::url($model->path)` directly instead, matching `components/supplier-card.blade.php`.

---

## 9. The `docs/AI/Frontend/` reference set — what's actually there, and a naming trap

```text
edushopify-frontend.html                     homepage mockup (CDN Tailwind, inline <style>)
edushopify-frontend-spec.md                  design system + broad page-by-page spec
                                              (includes backend/dashboard pages frontend_new
                                              does not touch — only the public-page sections apply)
edushopify-public-frontend-complete-spec.md  "Version 2.0 — New Client Requirements Master
                                              Document" — the authoritative v2 content/UX spec.
                                              States its own priority rule: "NEW CLIENT
                                              REQUIREMENT ALWAYS WINS over old design files."
edushopify-supplier-detail.html              supplier profile mockup
edushopify-supplier-detail-spec.md
edushopify-product-detail.html               product detail mockup
edushopify-product-detail-spec.md
edushopify-rfq-detail.html                   ⚠️ actually the RFQ LIST/BROWSE mockup, not detail
                                              — frontend_new.css itself flags this filename as
                                              misleading (comment near its RFQ section)
edushopify-rfq-detail-spec.md                the true RFQ *detail* page was built from this
                                              spec alone; no static HTML mockup exists for it
```

`edushopify-public-frontend-complete-spec.md`'s own prescribed folder/namespace/route conventions (`resources/views/frontend/`, `App\Http\Controllers\Frontend\`, `routes/frontend.php`, Tailwind "CDN script or Vite — either", icons "inline SVG only") **do not fully match what was actually built** — the real implementation lives under `frontend_new`/`v2` (a deliberate parallel-build decision, per `routes/frontend_new.php`'s own header comment), uses the Vite pipeline exclusively (never CDN Tailwind in production views), and, despite the icon rule being followed for real content, still loads the two now-unused icon-font CDN links noted in §1.2. The spec also documents some reusable partials (`alerts.blade.php`, `breadcrumb.blade.php`, `pagination.blade.php` under a `partials/` convention) that don't exist yet — treat the spec's content/UX/color/copy direction as authoritative, but verify folder paths and technical plumbing against this document and the real code instead of the spec text.

### 9.1 Page → mockup map

```text
Home            → edushopify-frontend.html          (Navbar → Hero → Featured Suppliers →
                                                       All Suppliers → Why Choose → Events → Footer)
Supplier show   → edushopify-supplier-detail.html  + edushopify-supplier-detail-spec.md
Product show    → edushopify-product-detail.html   + edushopify-product-detail-spec.md
RFQ index       → edushopify-rfq-detail.html (misnamed — it's the list page) + edushopify-rfq-detail-spec.md
RFQ show        → edushopify-rfq-detail-spec.md only (no static mockup)
Categories / Blogs / Resources
                → no dedicated static mockup; derive from edushopify-frontend-spec.md's
                  page-by-page sections plus the design tokens in §3 of this document
```

---

## 10. Checklist for adding a new `frontend_new` page

```text
1. Check the relevant docs/AI/Frontend/*-spec.md section for required content/sections/copy.
2. Check the matching *.html mockup, if one exists, for layout/proportions (§9.1) —
   remember edushopify-rfq-detail.html is the list page, not detail.
3. Grep resources/css/frontend_new.css for an existing component class (§3.4) before
   writing new CSS; add new classes at the end of the file under a comment banner
   naming the source mockup, matching the existing per-page-section style.
4. Create resources/views/frontend_new/{page}/index.blade.php (or show.blade.php)
   + partials/_hero.blade.php + partials/_content.blade.php (spell it "partials", not
   "partial" — see §1.1).
5. @extends('frontend_new.layouts.app'), set @section('title', ...) and
   @section('body_class', 'bg-gray-50') if the page isn't the homepage.
6. Add the route in routes/frontend_new.php inside the existing prefix('v2')->name('v2.')
   group, model-bound by slug where applicable.
7. Add the controller under app/Http/Controllers/FrontendNew/, passing Eloquent
   collections/models directly (§5) — only hand-map to arrays for a genuine JSON
   endpoint (AJAX search, like/comment actions).
8. Use only route('v2.xxx', ...) for links — never a hardcoded /v2/... path (§8).
9. Use inline SVG for any icon (§6) — do not add Font Awesome/Bootstrap Icons classes.
10. Add any new interactive behavior to resources/js/frontend_new.js as a guarded,
    page-scoped block (§7) — do not introduce Alpine/Livewire.
11. Add the page to the nav/footer/mobile-menu partials if it should be globally linked.
```
