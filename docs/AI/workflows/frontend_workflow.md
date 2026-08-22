# EduShopify Public Frontend Workflow — `frontend_workflow.md`

> **Status:** Mandatory public marketplace frontend functional specification.
>
> This document defines what the EduShopify public frontend must do, what guests and authenticated users may see, how public marketplace discovery works, how frontend actions hand off into Buyer/Supplier workflows, and how the frontend must be organized in Laravel.
>
> This file is for the **public website / marketplace**, not for the Admin, Buyer, or Supplier dashboards.
>
> Use it together with:
>
> 1. `docs/AI/ARCHITECTURE.md`
> 2. `docs/AI/design_frontend.md`
> 3. the current EduShopify database schema / SQL dump
> 4. `docs/AI/workflows/buyer_dashboard_workflow.md`
> 5. `docs/AI/workflows/supplier_dashboard_workflow.md`
> 6. `docs/AI/workflows/admin_dashboard_workflow.md`
> 7. the existing authentication and registration implementation
> 8. `docs/AI/references/fronted_reference/` for visual clarification only
>
> `ARCHITECTURE.md` controls project/folder architecture. The database controls real fields, relationships, statuses, and constraints. This file controls **public frontend functionality and workflow**. Buyer/Supplier/Admin workflow files control protected marketplace behavior after handoff. `design_frontend.md` controls the final public visual design, component appearance, typography, colors, spacing, responsive presentation, animation, and page composition styling.
>
> **Important:** this workflow intentionally does not lock the public frontend to a final visual design. Do not treat incidental examples in this file as visual source of truth after `design_frontend.md` exists.

---

# 1. PURPOSE

EduShopify is a B2B education procurement marketplace.

The public frontend must make EduShopify useful **before registration**.

The public site must allow visitors to discover real marketplace content and understand how the platform works.

```text
Public Frontend
      ↓
Marketplace Discovery
      ↓
Products / Services / Suppliers / Public RFQs
      ↓
Visitor Wants a Business Action
      ↓
Guest-safe action OR Login/Register handoff
      ↓
Buyer / Supplier Dashboard Workflow
```

The frontend must support both sides of the marketplace:

```text
BUYER DISCOVERY
Products / Services / Suppliers
        ↓
Request Quote / Contact Supplier / Post RFQ
        ↓
Buyer authentication + eligibility
        ↓
Buyer Dashboard

SUPPLIER DISCOVERY
Public RFQs / Pricing / How It Works
        ↓
Login / Register / Become a Supplier
        ↓
Supplier approval + subscription + RFQ eligibility
        ↓
Supplier Dashboard
```

---

# 2. CRITICAL SCOPE RULES

The frontend implementation must NOT unnecessarily rebuild or change:

- existing registration UI
- registration workflow
- login flow
- password-reset flow
- OTP/verification flow
- Admin dashboard
- Buyer dashboard
- Supplier dashboard
- existing Buyer/Supplier business rules

The current registration flow stays in place.

Frontend CTAs integrate with it through safe intended destinations and preserved action context.

Do not rebuild registration merely because the public frontend is new.

---

# 3. TECHNOLOGY RULES

Use the existing Laravel project.

Preferred public frontend stack:

```text
Laravel Route
      ↓
Frontend Controller
      ↓
Domain Query / Service when justified
      ↓
Existing Model / Database
      ↓
Blade
      ↓
Tailwind CSS
      ↓
Alpine.js / small JavaScript for UI-only behavior
```

## Strict prohibitions

- **Do NOT use Filament for the public frontend.**
- Do NOT create Filament Panels, Resources, Pages, Widgets, Forms, or Tables for public marketplace pages.
- Do NOT introduce backend Livewire through frontend work.
- Default public frontend implementation is normal Laravel Blade + Alpine.js.
- Do not add Bootstrap or another competing UI framework unless explicitly requested later.
- Do not rewrite the frontend as a React/Vue SPA unless explicitly requested later.

Use Vite/current project asset pipeline.

---

# 4. SOURCE-OF-TRUTH RESPONSIBILITIES

Do not treat one file as the source of truth for every concern. Use each source for its intended responsibility.

```text
Current database schema / SQL dump
→ real tables, fields, relationships, statuses, constraints

ARCHITECTURE.md
→ Laravel structure, frontend/backend separation, request lifecycle, domain placement

frontend_workflow.md
→ what the public frontend does, public visibility, guest behavior, handoff, security, routes/pages

design_frontend.md
→ how the public frontend looks and behaves visually

Buyer/Supplier/Admin workflow files
→ authenticated/protected business workflow after frontend handoff

Existing authentication/registration implementation
→ login/register/verification integration; preserve it unless explicitly changed
```

If the workflow asks for data that the current schema does not support, do not silently invent a field/table.

If `design_frontend.md` changes visual layout without changing functionality, update the design file rather than rewriting this business workflow.

Do not invent public claims the schema/business rules do not support.

Examples:

- Do not show “ISO Certified” just because it looks useful.
- Do not show “Verified for X years” unless stored business data supports it.
- Do not expose Supplier documents merely because they are verified.
- Do not invent shipping guarantees, stock guarantees, or delivery promises.

---

# 4.1 STATIC FRONTEND REFERENCE GOVERNANCE

The static files under:

```text
docs/AI/references/fronted_reference/
```

are visual reference material for the public frontend.

Current references include:

```text
home.html
catalog.html
listing.html
suppliers.html
supplier.html
rfqs.html
rfq-detail.html
```

They are demo pages, not business/data sources.

Use them for:

- visual direction
- page proportions
- component inspiration
- content hierarchy
- responsive intent
- interaction examples

Do NOT treat their hard-coded sample data as production requirements.

Examples that must be replaced by real data or removed include:

- fake marketplace totals
- fake institution counts
- hard-coded Supplier ratings/review counts
- hard-coded certification claims
- `Verified · X yrs` without real supported data
- hard-coded categories/listings/RFQs
- dead social links
- newsletter UI without a real workflow

Visual precedence is:

```text
design_frontend.md
        ↓
static HTML reference
```

Functional precedence remains:

```text
frontend_workflow.md + database + relevant protected workflows
```

Therefore a static reference must be adapted whenever it conflicts with real project functionality, privacy, eligibility, or the finalized design system.

---

# 5. FRONTEND / BACKEND SEPARATION

Keep public frontend completely separate from backend dashboards.

```text
Frontend
    → public website
    → public marketplace
    → public Supplier storefronts
    → public RFQ summaries

Backend
    → Admin
    → Buyer
    → Supplier
    → Shared backend
```

Use:

```text
routes/frontend.php
app/Http/Controllers/Frontend/
resources/views/frontend/
```

Do NOT put public pages under `resources/views/backend/`.
Do NOT put frontend controllers under `app/Http/Controllers/Backend/`.

---

# 6. FRONTEND BUSINESS PRINCIPLES

## 6.1 Discovery is public

Guests should be able to browse safe public marketplace data:

- approved/published products
- approved/published services
- eligible Supplier storefronts
- published reviews
- categories
- safe public RFQ opportunities
- Supplier subscription plans/pricing
- informational pages

## 6.2 Business execution is authenticated

Official marketplace actions require the correct identity/account/capability.

```text
Save Listing/Supplier
→ login required

Platform Message Supplier
→ authenticated account context

Official Request Quote / Create RFQ
→ Buyer workflow

Submit Quotation
→ Supplier workflow + eligibility

Manage Listing
→ Supplier Dashboard
```

## 6.3 Guest inquiry is not an RFQ

A guest may send a simple Supplier/listing inquiry using `contact_inquiries`.

That record is a lead/contact inquiry.

It is NOT:

- an RFQ
- a quotation
- an Award
- a Purchase Order

Never create an anonymous official RFQ.

## 6.4 Public data is allowlisted

Public frontend queries must never mean “show every database row”.

Use explicit public eligibility rules.

---

# 7. VISITOR TYPES

Frontend behavior must account for:

1. Guest
2. Logged-in user without active marketplace capability
3. Active Buyer-only user
4. Active Supplier-only user
5. Dual Buyer + Supplier account
6. Pending capability
7. Suspended/inactive user/account/capability
8. Platform Admin browsing public frontend

Public discovery remains public.
Protected actions remain capability/permission/workflow aware.

---

# 8. PUBLIC ACTION ACCESS MATRIX

| Action | Guest | Buyer | Supplier | Dual Capability |
|---|---:|---:|---:|---:|
| View homepage | Yes | Yes | Yes | Yes |
| Browse categories | Yes | Yes | Yes | Yes |
| Browse products/services | Yes | Yes | Yes | Yes |
| View listing detail | Yes | Yes | Yes | Yes |
| Browse Suppliers | Yes | Yes | Yes | Yes |
| View Supplier storefront | Yes | Yes | Yes | Yes |
| View public reviews | Yes | Yes | Yes | Yes |
| View public RFQ board | Yes | Yes | Yes | Yes |
| Send guest inquiry | Yes if enabled | Yes | Yes | Yes |
| Save listing/Supplier | Login required | Yes if authorized | Yes if authorized | Yes |
| Platform message Supplier | Login required | Yes if authorized | Context dependent | Yes |
| Request official quote | Login/Register | Buyer flow | Requires Buyer capability | Buyer mode |
| Post RFQ | Login/Register | Buyer flow | Requires Buyer capability | Buyer mode |
| Submit quotation | Login/Register | Requires Supplier capability | Supplier flow | Supplier mode |
| Manage procurement | No | Buyer Dashboard | No unless Buyer capability | Buyer Dashboard |
| Manage listings | No | No unless Supplier capability | Supplier Dashboard | Supplier Dashboard |

This table is UI behavior only. Server-side authorization remains mandatory.

---

# 9. PUBLIC LISTING ELIGIBILITY

A product/service is publicly visible only when all relevant conditions are true.

At minimum:

```text
Listing exists
    ↓
deleted_at IS NULL
    ↓
approval_status = approved
    ↓
is_active = true
    ↓
published_at IS NOT NULL
    ↓
Supplier account is publicly eligible (not suspended/deleted)
    ↓
Supplier capability/public-state rule from supplier_dashboard_workflow.md passes
    ↓
Supplier remains eligible for public marketplace visibility under current subscription/business rules
```

Subscription/public visibility rules from Supplier workflow must also be respected where applicable.

Important nuance: if the confirmed Supplier workflow allows already-published listings to remain public during a temporary `revision_required` state after a legal-information change, the centralized public query must honor that rule. Do not blindly implement `capability.status = active` in every listing query if it would contradict the confirmed Supplier workflow. Suspended/rejected/ineligible Suppliers must not gain public visibility through this exception.

Do not duplicate this condition manually across many controllers.

Prefer one central query scope/service, for example conceptually:

```text
Listing::publiclyVisible()
PublicCatalogService
PublicListingQuery
```

Follow existing project conventions before choosing the exact class name.

---

# 10. PUBLIC SUPPLIER ELIGIBILITY

A Supplier storefront should require at least:

```text
Supplier account is publicly eligible
    ↓
Supplier capability/public-state rule from supplier_dashboard_workflow.md passes
    ↓
Supplier profile exists
    ↓
Public profile identity/slug valid
```

Normally this means an active Supplier capability. If the confirmed Supplier workflow explicitly preserves public presence during a `revision_required` legal-information review, honor that narrow rule. Do not show pending first-time applications, rejected Suppliers, suspended Suppliers, deleted accounts, or otherwise ineligible Suppliers as normal public storefronts.

Homepage placement/badges may additionally depend on subscription entitlement or Admin-controlled placement rules.

---

# 11. PUBLIC REVIEW RULES

Only public reviews may appear on frontend.

```text
reviews.status = published
```

Supplier replies shown publicly must also be:

```text
review_replies.status = published
```

Never expose:

- pending review content as public
- hidden/rejected reviews
- moderation reasons
- review reports
- Admin notes

Public reviewer identity must use a safe display policy. Do not automatically expose the individual employee who created the review.

---

# 12. SUPPLIER DOCUMENT PRIVACY

Supplier verification documents are NOT automatically public.

Public frontend may show:

- EduShopify verified Supplier badge when business rules support it
- high-level verification status
- explicitly public certifications only when an explicit public-safe mechanism exists

Do NOT expose by default:

- `supplier_documents.file_path`
- tax/legal identity documents
- confidential compliance files
- pending/rejected documents
- verification reviewer details

If public certification display is added later, implement a deliberate public-safe rule rather than exposing all verified files.

---

# 13. PUBLIC RFQ ELIGIBILITY — CRITICAL

The public RFQ board must show only safe public/global opportunities.

Required public eligibility:

```text
published_at IS NOT NULL
    ↓
status = open
    ↓
visibility_type = global
    ↓
deleted_at IS NULL
    ↓
public business/deadline rule still valid
```

Prefer the existing `rfq_public_summary` view for safe summary fields.

**Important:** the public frontend must still explicitly enforce:

```text
visibility_type = global
```

Do not assume the database view alone guarantees selected-Supplier privacy.

Selected-Supplier RFQs must NEVER appear on public pages.

---

# 14. PUBLIC RFQ DATA CONTRACT

Public RFQ pages may show safe summary fields such as:

- RFQ number
- title
- status
- visibility type
- currency where useful
- quotation deadline
- Q&A deadline where useful
- expected delivery date
- published date
- item count
- category summary
- item type summary
- delivery country
- delivery state
- delivery city

Do NOT expose by default:

- Buyer private contact information
- exact delivery address
- latitude/longitude
- selected Supplier list
- Supplier queue/eligibility snapshots
- private/hidden questions
- quotation commercial data
- Buyer member identities
- internal change snapshots

A public RFQ page is a safe summary, not the authenticated Supplier RFQ detail screen.

---

# 15. PUBLIC SITE MAP

```text
EduShopify
│
├── Home
│
├── Marketplace
│   ├── All Listings
│   ├── Products
│   ├── Services
│   └── Categories
│
├── Suppliers
│   ├── Supplier Directory
│   └── Supplier Storefront
│
├── RFQ Opportunities
│   ├── Public RFQ Board
│   └── Public RFQ Summary
│
├── How It Works
│   ├── For Buyers
│   └── For Suppliers
│
├── Supplier Pricing
├── About
├── Contact
├── FAQs
├── Terms
└── Privacy
```

---

# 16. ROUTE ARCHITECTURE

Use:

```text
routes/frontend.php
```

`web.php` should mainly load the route files according to `ARCHITECTURE.md`.

Recommended public routes:

```text
GET  /
GET  /catalog
GET  /products
GET  /services
GET  /categories
GET  /category/{slug}
GET  /listing/{slug}
GET  /suppliers
GET  /supplier/{slug}
GET  /opportunities
GET  /opportunities/{rfq_number}
GET  /search/suggestions
GET  /how-it-works
GET  /pricing
GET  /about
GET  /contact
POST /contact
GET  /faqs
GET  /terms
GET  /privacy
POST /inquire/{listing}
POST /inquire/supplier/{supplier}
```

Preferred route names if they do not conflict with stable existing route names:

```text
frontend.home
frontend.catalog.index
frontend.products.index
frontend.services.index
frontend.categories.index
frontend.categories.show
frontend.listings.show
frontend.suppliers.index
frontend.suppliers.show
frontend.rfqs.index
frontend.rfqs.show
frontend.search.suggestions
frontend.inquiries.listing
frontend.inquiries.supplier
frontend.pages.how-it-works
frontend.pages.pricing
frontend.pages.about
frontend.pages.contact
frontend.pages.contact.submit
frontend.pages.faqs
frontend.pages.terms
frontend.pages.privacy
```

If authentication/registration already depends on an established route name such as `home`, preserve compatibility rather than renaming blindly.

Canonical public URLs should prefer slugs/numbers where available instead of exposing raw sequential IDs.

---

# 17. CONTROLLER STRUCTURE

Controllers belong under:

```text
app/Http/Controllers/Frontend/
```

Recommended structure:

```text
app/Http/Controllers/Frontend/
├── HomeController.php
├── CatalogController.php
├── ListingController.php
├── CategoryController.php
├── SupplierDirectoryController.php
├── PublicRfqController.php
├── SearchController.php
├── InquiryController.php
└── PageController.php
```

Responsibilities:

```text
HomeController
→ homepage section data only

CatalogController
→ combined catalog + product-only + service-only listing indexes

ListingController
→ public product/service detail through the canonical Listing entity

CategoryController
→ category directory + category landing pages

SupplierDirectoryController
→ Supplier directory + public Supplier storefront

PublicRfqController
→ safe public RFQ board + safe public RFQ summary

SearchController
→ public autocomplete/suggestion endpoint when implemented

InquiryController
→ guest/public Supplier/listing/general inquiries

PageController
→ How It Works, Pricing, About, Contact view, FAQs, Terms, Privacy
```

Do not create separate Product and Service Models.

The canonical marketplace entity remains:

```text
Listing
└── listing_type = product | service
```

Product/service controllers may be split later only if controller complexity genuinely justifies it; do not duplicate domain models or business systems.

---

# 18. DOMAIN SERVICE / QUERY STRUCTURE

Keep frontend HTTP code under Frontend controllers.
Keep reusable business/query logic domain-based according to `ARCHITECTURE.md`.

Possible examples only when complexity justifies them:

```text
app/Services/Catalog/PublicCatalogService.php
app/Services/Catalog/PublicListingService.php
app/Services/Account/PublicSupplierDirectoryService.php
app/Services/Procurement/PublicRfqService.php
app/Actions/Communication/CreateContactInquiryAction.php
```

Do not create Services merely for ceremony.

Use Form Requests for public write forms, such as:

```text
StorePublicInquiryRequest
StoreContactRequest
```

---

# 19. VIEW / LAYOUT STRUCTURE

Keep public frontend views under:

```text
resources/views/frontend/
```

**Frontend view-structure decision:** this workflow intentionally uses direct page folders such as `frontend/home/`, `frontend/catalog/`, `frontend/categories/`, `frontend/suppliers/`, and `frontend/rfqs/`. If an older `ARCHITECTURE.md` example still shows an extra `frontend/pages/` wrapper, treat that older frontend-view example as needing alignment with this finalized frontend structure before implementation. This exception applies only to the public frontend view tree; do not reinterpret backend architecture from this note.

Use this target structure:

```text
resources/views/frontend/
│
├── layouts/
│   ├── master.blade.php
│   └── partials/
│       ├── _head.blade.php
│       ├── _header.blade.php
│       ├── _mobile_menu.blade.php
│       ├── _footer.blade.php
│       └── _scripts.blade.php
│
├── components/
│   ├── marketplace/
│   │   ├── listing-card.blade.php
│   │   ├── supplier-card.blade.php
│   │   ├── rfq-card.blade.php
│   │   ├── category-card.blade.php
│   │   ├── tier-pricing-table.blade.php
│   │   ├── variant-selector.blade.php
│   │   └── rating-summary.blade.php
│   ├── search/
│   │   ├── global-search.blade.php
│   │   ├── search-suggestions.blade.php
│   │   └── filter-drawer.blade.php
│   ├── navigation/
│   │   └── breadcrumbs.blade.php
│   └── common/
│       ├── empty-state.blade.php
│       ├── pagination.blade.php
│       ├── badge.blade.php
│       └── section-heading.blade.php
│
├── home/
│   ├── index.blade.php
│   └── sections/
│       ├── _hero.blade.php
│       ├── _top_categories.blade.php
│       ├── _featured_products.blade.php
│       ├── _featured_services.blade.php
│       ├── _featured_suppliers.blade.php
│       ├── _rfq_opportunities.blade.php
│       ├── _how_it_works.blade.php
│       ├── _why_edushopify.blade.php
│       ├── _buyer_supplier_cta.blade.php
│       ├── _pricing_teaser.blade.php
│       └── _marketplace_stats.blade.php      # optional; only when backed by real data
│
├── catalog/
│   ├── index.blade.php
│   ├── products.blade.php
│   ├── services.blade.php
│   └── show.blade.php
│
├── categories/
│   ├── index.blade.php
│   └── show.blade.php
│
├── suppliers/
│   ├── index.blade.php
│   └── show.blade.php
│
├── rfqs/
│   ├── index.blade.php
│   └── show.blade.php
│
└── pages/
    ├── how-it-works.blade.php
    ├── pricing.blade.php
    ├── about.blade.php
    ├── contact.blade.php
    ├── faqs.blade.php
    ├── terms.blade.php
    └── privacy.blade.php
```

## 19.1 Homepage section composition rule

`home/index.blade.php` is the homepage composition file.

It should NOT contain hundreds of lines for every homepage section.

Instead, it should extend the public master layout and include the homepage-specific section partials in business order.

Conceptually:

```blade
@extends('frontend.layouts.master')

@section('content')
    @include('frontend.home.sections._hero')
    @include('frontend.home.sections._top_categories')
    @include('frontend.home.sections._featured_products')
    @include('frontend.home.sections._featured_services')
    @include('frontend.home.sections._featured_suppliers')
    @include('frontend.home.sections._rfq_opportunities')
    @include('frontend.home.sections._how_it_works')
    @include('frontend.home.sections._why_edushopify')
    @include('frontend.home.sections._buyer_supplier_cta')
    @include('frontend.home.sections._pricing_teaser')
@endsection
```

`_marketplace_stats.blade.php` should be included only if the project decides to show real database-backed marketplace statistics.

## 19.2 Section partial vs reusable component

Use this distinction:

```text
Homepage-specific section composition
→ frontend/home/sections/*.blade.php

Reusable UI used on multiple pages
→ frontend/components/*
```

Example:

```text
_featured_products.blade.php
→ decides which homepage heading/section content is rendered
→ loops through featured products
→ uses reusable listing-card component

listing-card.blade.php
→ reusable on homepage, catalog, category, Supplier storefront, related listings
```

Do not copy full product-card markup directly into multiple homepage sections.

Do not turn each homepage section into a Controller. `HomeController` provides the section data; Blade partials compose the page.

If one homepage section later becomes complex enough to justify a dedicated data/query class, keep that class in the appropriate domain rather than creating HTTP controllers per section.

---

# 20. PUBLIC FRONTEND DESIGN SOURCE OF TRUTH

This workflow defines **functionality**, not the final public visual design.

The final UI/UX must be controlled by:

```text
docs/AI/design_frontend.md
```

`design_frontend.md` controls items such as:

- brand palette
- typography
- container widths
- spacing scale
- header appearance
- category menu/mega-menu appearance
- search appearance
- hero layout
- card appearance
- grids vs carousels
- RFQ card/list/ticker presentation
- buttons
- badges
- forms
- filter presentation
- Supplier storefront layout
- listing detail layout
- mobile/tablet/desktop visual behavior
- hover/focus/animation rules
- frontend CSS variables/tokens

This workflow may define that a section or control **must exist**, but it should not dictate whether the final design renders it as a carousel, static grid, ticker, tabs, pills, cards, or another presentation unless that choice affects business behavior/accessibility.

Do not invent a second visual system or page-specific styling that conflicts with `design_frontend.md`.

The existing backend `design.md` remains the dashboard design specification and must not be treated as the public marketplace design file.

---

# 21. GLOBAL HEADER

Desktop header should support:

- Logo
- Products
- Services
- Suppliers
- RFQ Opportunities
- How It Works
- Global Search
- Login/Register or account state
- Post an RFQ primary CTA

Optional:

- Pricing
- Category mega-menu

Do not overcrowd the header.

## Guest

```text
EduShopify | Products | Services | Suppliers | RFQs | How It Works
                           Search | Login | Register | Post an RFQ
```

## Buyer

Replace Login/Register with account menu and Buyer Dashboard.

## Supplier

Show account menu and Supplier Dashboard.

## Dual capability

Allow navigation to both Buyer and Supplier dashboards.

Changing a frontend dropdown does NOT grant capability or permission.

## Mobile

Prioritize:

```text
Menu | Logo | Search | Account
```

Use accessible Alpine drawer/accordion behavior.

---

# 22. GLOBAL FOOTER

Recommended columns:

```text
Marketplace
- Products
- Services
- Suppliers
- RFQ Opportunities

For Buyers
- How It Works
- Post an RFQ
- Find Suppliers

For Suppliers
- Become a Supplier
- Pricing
- RFQ Opportunities

Company
- About
- Contact
- FAQs

Legal
- Terms
- Privacy
```

Do not invent social URLs or newsletter functionality if no real backend workflow exists.

---

# 23. HOMEPAGE WORKFLOW

The homepage must be composed from section partials under:

```text
resources/views/frontend/home/sections/
```

Functional section order:

```text
Global Header (layout partial)
  ↓
Hero + Marketplace Search
  ↓
Top Educational Categories
  ↓
Featured Products
  ↓
Featured Services
  ↓
Featured / Verified Suppliers
  ↓
Open Public RFQ Opportunities
  ↓
How EduShopify Works
  ↓
Why EduShopify
  ↓
Buyer / Supplier CTA
  ↓
Supplier Pricing Teaser
  ↓
Optional Real Marketplace Statistics
  ↓
Global Footer (layout partial)
```

Recommended partial mapping:

```text
Hero                         → _hero.blade.php
Top Categories               → _top_categories.blade.php
Featured Products            → _featured_products.blade.php
Featured Services            → _featured_services.blade.php
Featured Suppliers           → _featured_suppliers.blade.php
Public RFQ Opportunities     → _rfq_opportunities.blade.php
How It Works                 → _how_it_works.blade.php
Why EduShopify               → _why_edushopify.blade.php
Buyer/Supplier CTA           → _buyer_supplier_cta.blade.php
Pricing Teaser               → _pricing_teaser.blade.php
Marketplace Statistics       → _marketplace_stats.blade.php (optional)
```

Use real database-driven data wherever practical.

Do not hard-code fake marketplace totals, Supplier names, product data, RFQs, ratings, or plan values in the completed feature.

The final visual arrangement of these sections is governed by `design_frontend.md`.

---

# 24. HERO

Hero must immediately explain EduShopify as a B2B education procurement marketplace.

Primary controls:

- marketplace search
- Products / Services / Suppliers scope switch if desired
- Post an RFQ
- Become a Supplier

Example:

```text
Search “laboratory microscope”
       ↓
/catalog?q=laboratory+microscope
```

Quick category pills/cards should come from active categories or Admin-curated settings/data, not permanent hard-coded examples.

---

# 25. HOMEPAGE CATEGORIES

Use `categories`.

Show active public categories.

Card may contain:

- category name
- supported icon/image
- optional public listing count

Counts must count only publicly eligible listings.

Possible seed examples may include laboratory equipment, IT/STEM, furniture, stationery, software, training, etc., but the UI must render actual database categories.

---

# 26. FEATURED PRODUCTS / SERVICES

Featured listing must still pass all public eligibility rules.

`is_featured = true` is not enough by itself.

## Product card may show

- image
- product name
- Supplier
- verified badge when eligible
- category
- brand
- base price OR RFQ/quote-only label
- MOQ
- lead time
- stock status
- rating summary
- View Details
- Request Quote

## Service card may show

- image
- service name
- Supplier
- category
- service mode
- duration
- lead time
- service-area summary
- Request Quote

---

# 27. FEATURED / VERIFIED SUPPLIERS

Use existing Supplier/account/capability/profile/subscription data.

A homepage featured Supplier may depend on subscription entitlement such as homepage placement.

Do not label every active Supplier “featured”.

Supplier card may show safe public data:

- logo
- display name
- verified badge when eligible
- Supplier type(s)
- country/city
- category/service summary
- rating/review count
- response metric if public
- View Supplier

Do not expose private contact person details by default.

---

# 28. HOMEPAGE RFQ OPPORTUNITIES

Show a limited set of public global opportunities.

Card may show:

- RFQ number
- title
- category summary
- item types
- item count
- delivery country/state/city
- quotation deadline
- expected delivery date

Never show selected-Supplier RFQs.

---

# 29. HOW IT WORKS — HOMEPAGE SUMMARY

## Buyer

```text
Discover
→ Create RFQ
→ Receive Quotations
→ Compare / Shortlist / Revision
→ Award Supplier
→ Purchase Order
```

## Supplier

```text
Register
→ Verification
→ Subscription
→ Publish Listings
→ Receive Eligible RFQ Opportunities
→ Submit Quotations
→ Win Business
```

Do not promise in-platform product payment because Phase 1 product/service payment occurs outside EduShopify.

---

# 30. MARKETPLACE CATALOG

Routes:

```text
/catalog
/products
/services
```

Support:

- search
- listing type
- category
- brand
- Supplier
- location/service area
- public price where meaningful
- MOQ
- product stock status
- service mode
- category attributes where configured
- sort
- pagination

Do not apply fake numeric prices to quote-only listings.

---

# 31. FILTER RULES

## Category

Use `categories`, `listing_categories`, and main category relationship.

Support hierarchy.

## Listing type

Use `listings.listing_type`.

## Brand

Use `brands` through public eligible listings.

## Supplier

Only publicly eligible Suppliers.

## Location

Use Supplier public location/service-area semantics. Do not mislabel Supplier location as product origin.

## Price

Use actual public base/variant/tier prices only.

## MOQ

Use listing/variant MOQ.

## Stock

Use Product/Variant stock statuses.

## Service mode

Use:

```text
onsite
remote
hybrid
```

## Attributes

Expose relevant category attributes only.

---

# 32. SORTING / PAGINATION

Recommended sort options where meaningful:

- Relevance
- Newest
- Price low-high
- Price high-low
- Rating
- Featured

Use allowlisted sort keys.
Never inject raw request sort column into SQL.

Preserve filters in pagination/back navigation.

---

# 33. LISTING CARD CONTRACT

Recommended safe B2B fields:

```text
Image
Listing Type Badge
Name
Supplier
Verified Badge if eligible
Category
Brand if relevant
Public Price / RFQ Pricing
MOQ
Location/service summary
Rating summary
View Details
Request Quote
```

Handle missing optional data without breaking layout.

---

# 34. LISTING DETAIL PAGE

Canonical route:

```text
/listing/{slug}
```

If listing is not public, return safe 404/unavailable behavior.
Do not reveal that a private listing exists but is pending/rejected.

Recommended structure:

```text
Breadcrumbs

Media Gallery
+
Commercial / Service Summary
+
Supplier Summary Card

Description
Specifications
Variants
Tier Pricing
Warranty / Support / Service Terms
Related Listings
Supplier Information
Review Summary
```

---

# 35. PRODUCT DETAIL

Use actual fields from `listings`, `product_details`, variants, attributes, and tier prices.

Possible public fields:

- name
- listing number/SKU if intended public
- brand
- category
- pricing type
- base price
- compare-at price
- currency
- MOQ
- unit
- description
- extra specs
- product type
- stock status
- weight
- lead time
- warranty
- support terms

## Variants

Show only active/non-deleted variants associated with a public listing.

May show:

- variant name
- SKU if public
- price
- stock status
- MOQ
- lead time
- attributes

## Tier pricing

Use `listing_tier_prices`.

Display actual quantity breaks only.

---

# 36. PRICE ESTIMATE / QUANTITY SELECTOR

A quantity selector/calculator may show a **display estimate** for fixed/tier pricing.

It must not create:

- official quotation
- checkout order
- payment
- Purchase Order

```text
Quantity selected
    ↓
Match public tier
    ↓
Display estimated unit price/subtotal
    ↓
Request Quotation remains official CTA
```

For `quote_only`, show Request Quote instead of fake pricing.

Do not imply final taxes, shipping, or payment terms from the estimator.

---

# 37. SERVICE DETAIL

Use `service_details`.

May show:

- service mode
- duration value/unit
- lead time
- service terms
- support terms
- service area
- category
- description
- pricing mode

Primary CTAs:

```text
Request Quotation
Contact Supplier
```

Do not show product stock UI on service pages.

---

# 38. MEDIA

Use existing media/listing/Supplier media systems.

Public pages may show public-intended:

- listing images
- Supplier gallery
- Supplier videos

Requirements:

- lazy-load non-critical images
- alt text
- aspect-ratio placeholders
- fallback image
- no layout break when media missing

Do not expose private media collections merely because media records exist.

---

# 39. RELATED LISTINGS

May use:

- category
- Supplier
- brand
- attributes

Rules:

- only public eligible listings
- exclude current listing
- limit results
- avoid expensive random ordering on large datasets

---

# 40. CATEGORY DIRECTORY

Route:

```text
/categories
```

Show:

- active category tree
- child categories
- public listing counts where practical
- featured/top categories where configured

Counts must exclude draft/rejected/private listings.

---

# 41. CATEGORY PAGE

Route:

```text
/category/{slug}
```

Show:

- category title
- description/image if supported
- child categories
- filtered listings
- relevant attribute filters
- product/service counts
- top Suppliers if useful
- breadcrumbs

---

# 42. SUPPLIER DIRECTORY

Route:

```text
/suppliers
```

Support:

- search Supplier name
- Supplier type
- country
- state/city
- category
- service area
- rating
- verified-only filter if verification semantics are clearly defined
- sort
- pagination

Do not add “ISO Certified” as a generic filter unless structured public certification data exists.

---

# 43. SUPPLIER CARD

Recommended fields:

- logo
- display name
- verified badge
- primary Supplier type
- other type summary
- location
- category/service summary
- rating/reviews
- response rate/time if public
- View Supplier
- Contact Supplier

Do not expose subscription billing, private member lists, or private documents.

---

# 44. PUBLIC SUPPLIER STOREFRONT

Canonical route:

```text
/supplier/{slug}
```

Recommended structure:

```text
Banner / Header
      ↓
Logo + Display Name + Verified Badge
      ↓
Location + Supplier Types + Rating
      ↓
Contact Supplier | Request Quote
      ↓
Overview
Products
Services
Reviews
Gallery / Videos
Service Areas / Business Hours
```

Safe public data may include when intended:

- display name
- description
- company type
- location
- website
- founded year
- employee count
- logo/banner
- public social links
- Supplier types
- service areas
- business hours
- gallery/videos
- exhibitions
- rating/review count
- response rate/time
- published listings
- published reviews/replies

Prefer a public-field whitelist.
Do not simply serialize the entire Supplier profile model.

---

# 45. VERIFIED SUPPLIER BADGE

Do not infer verification from `supplier_profiles` alone.

Verification/eligibility must follow account/capability/document/subscription business rules.

Conceptually:

```text
Account active
+
Supplier capability active
+
required verification conditions
+
verified-badge entitlement/rule where applicable
=
Verified Supplier display
```

Do not show a badge purely because the UI designer wants one.

---

# 46. SUPPLIER REVIEWS

Public Supplier profile may show:

- rating aggregate
- review count
- published reviews
- review context label where useful
- published Supplier reply

Do not expose hidden/rejected reviews or private Buyer employee identity.

---

# 47. PUBLIC RFQ BOARD

Canonical route:

```text
/opportunities
```

Support:

- keyword search
- category
- item type
- country/state/city
- deadline range
- newest/deadline sort
- pagination

Only public/global/open/published RFQs.

The public RFQ board is discovery only.
Supplier Dashboard remains authoritative for whether a specific Supplier may submit a quotation.

---

# 48. PUBLIC RFQ CARD

Recommended fields:

- RFQ number
- title
- category summary
- item types
- item count
- delivery location summary
- quotation deadline
- expected delivery date
- published date

CTA:

```text
View Opportunity
```

Supplier CTA:

```text
Login / Register to Quote
```

Never show full Buyer account details, exact private address, selected Suppliers, or quotation details.

---

# 49. PUBLIC RFQ DETAIL

Canonical route:

```text
/opportunities/{rfq_number}
```

Use whitelisted safe data.

May show:

- RFQ number
- title
- approved-safe description
- category/item-type summary
- safe item summary if intentionally allowed
- delivery country/state/city
- quotation deadline
- expected delivery date
- partial/alternative flags if intentionally public
- public answered Q&A only if intentionally enabled

If public Q&A is displayed, only show:

```text
is_public = true
status = answered
```

Do not expose hidden/private questions.

---

# 50. SUPPLIER RFQ HANDOFF

Public Submit Quotation CTA flow:

```text
Guest clicks Submit Quotation
        ↓
Login / Register
        ↓
Supplier capability/onboarding if needed
        ↓
Supplier Dashboard
        ↓
Check account active
Check membership active
Check Supplier capability active
Check subscription eligible
Check targeting / Supplier queue eligibility
Check available_at
Check RFQ status/deadline
        ↓
Open authenticated opportunity
        ↓
Quotation workflow
```

A public page never grants quotation eligibility.

---

# 51. OFFICIAL REQUEST QUOTE HANDOFF

Request Quote may appear on:

- listing card
- listing detail
- Supplier storefront

Guest:

```text
Request Quote
    ↓
Login / Register
    ↓
Preserve safe intent
    ↓
Buyer eligibility
    ↓
Buyer Dashboard Create RFQ
    ↓
Pre-populate listing/Supplier context where appropriate
```

Active Buyer:

```text
Request Quote
    ↓
Buyer Dashboard RFQ creation with context
```

Do not create a parallel anonymous quotation-request system.

---

# 52. POST AN RFQ CTA

Global CTA:

```text
Post an RFQ
```

Guest:

```text
Post an RFQ
→ Login/Register
→ Buyer capability/eligibility
→ Buyer Dashboard Create RFQ
```

Supplier-only account requires Buyer capability/mode before Buyer RFQ workflow.
Clicking the CTA does not grant Buyer capability.

---

# 53. AUTH INTENT PRESERVATION

Protected frontend actions should preserve safe context.

Examples:

```text
request_quote + listing
request_quote + supplier
submit_quotation + rfq_number
post_rfq
save_listing
save_supplier
message_supplier
```

Use safe mechanisms such as:

- Laravel intended URL
- server-side session intent
- signed/validated parameters

After authentication:

- re-resolve resource
- re-check public/current state
- re-authorize

Never store permission decisions in the intent itself.

Prevent open redirects.

---

# 54. GUEST CONTACT / INQUIRY FLOW

Use existing `contact_inquiries`.

Guest input may include:

- name
- email
- phone
- organization
- subject
- message

Server may capture:

- Supplier target
- listing target
- source URL
- IP address
- user agent
- safe metadata

Use the actual `contact_inquiries` ownership fields correctly:

```text
General /contact inquiry
→ supplier_account_id = NULL
→ listing_id = NULL

Supplier storefront inquiry
→ supplier_account_id = public Supplier account id
→ listing_id = NULL

Listing detail inquiry
→ supplier_account_id = listing.supplier_account_id
→ listing_id = listing.id
```

Do not invent/use a `supplier_id` column if the schema uses `supplier_account_id`.

Flow:

```text
Listing / Supplier Page
        ↓
Contact Supplier
        ↓
Inquiry Form
        ↓
Validate
        ↓
Rate Limit / Anti-Spam
        ↓
Resolve Supplier/listing server-side
        ↓
Create contact_inquiries
        ↓
status = new
        ↓
Notify Supplier where workflow supports it
        ↓
Success
```

Do not expose Supplier private email merely to avoid implementing the inquiry flow.

---

# 55. PUBLIC FORM SECURITY

Use:

- CSRF
- Form Request validation
- rate limiting
- length limits
- email validation
- safe text rendering
- anti-spam baseline

Potential anti-spam controls:

- throttle
- honeypot
- CAPTCHA only if abuse later requires it

Do not automatically add a third-party CAPTCHA without approval/configuration.

Resolve Supplier ownership server-side.
Do not trust hidden `supplier_account_id` values blindly.

---

# 56. GLOBAL SEARCH

Search public data only:

- listings
- Suppliers
- categories
- public RFQs

Do NOT search private:

- users
- members
- messages
- quotations
- Awards
- POs
- Supplier documents
- support tickets

Optional result tabs:

```text
All | Products | Services | Suppliers | RFQs
```

Server is source of truth.
Alpine may control suggestion UI.

---

# 57. AUTOCOMPLETE

If implemented:

- debounce
- minimum character threshold
- result limit
- group by type
- public-safe title/image only
- keyboard navigation
- escaped output
- View All Results

Do not preload the entire marketplace database into JavaScript.

---

# 58. SUPPLIER PRICING PAGE

Route:

```text
/pricing
```

Use active public `subscription_plans`.

May show:

- name
- billing type
- price/currency
- trial days
- listing limit
- product/service limit
- team member limit
- monthly quotation limit
- RFQ delay
- RFQ notification feature
- analytics feature
- verified badge feature
- homepage placement feature
- safe features JSON content

Do not expose Stripe IDs or internal metadata.

CTA:

```text
Become a Supplier
```

or, for an approved Supplier:

```text
Choose Plan / Manage Subscription
```

which hands off to Supplier Dashboard.

---

# 59. HOW IT WORKS

Route:

```text
/how-it-works
```

The content must match real Phase 1 workflow.

## Buyer

```text
Register
→ Buyer eligibility/approval
→ Discover marketplace
→ Create RFQ
→ Receive quotations
→ Compare / shortlist / revision
→ Award Supplier
→ Supplier accepts
→ PO created
→ Phase 1 transaction/fulfilment outside platform
→ Authorized completion
→ Review when eligible
```

## Supplier

```text
Register
→ Supplier application
→ Profile/documents
→ Admin approval
→ Select subscription
→ Create listing
→ Listing approval
→ Publish
→ Access eligible RFQs
→ Submit quotation
→ Revision if requested
→ Award accept/reject
→ PO if accepted
```

Do not advertise escrow or in-platform product payment in Phase 1.

---

# 60. ABOUT / CONTACT / FAQ / LEGAL

## About

Explain marketplace mission and education procurement value.
Do not invent company history, team, offices, awards, or metrics.

## Contact

General contact may reuse `contact_inquiries` with null Supplier/listing when appropriate.
Distinguish general inquiry from Supplier/listing inquiry using existing fields/metadata.

## FAQs

Initial categories may include:

- Buyer registration
- Supplier verification
- RFQs
- quotations
- subscription
- payments
- privacy
- support

Do not create a new FAQ database table unless content management later requires it.

## Terms / Privacy

Provide the pages/layout, but legal text must be supplied/approved by the project owner/legal source.

---

# 61. AUTHENTICATED FRONTEND STATE

Logged-in users may still browse the public site.
Do not force all authenticated users directly to dashboard when they visit `/`.

## Buyer-only

Show Buyer Dashboard and Buyer CTAs.

## Supplier-only

Show Supplier Dashboard and opportunity CTAs.

## Dual capability

Show both dashboard destinations.

## Pending capability

Lead to onboarding/status flow rather than pretending capability is active.

---

# 62. SAVE ACTIONS

Use existing `saved_items` system.

Frontend may offer:

- Save Listing
- Save Supplier

Guest:

```text
Save
→ Login/Register
→ Re-authorize target
→ Save
```

Do not build a separate local-browser favorite system unless requested later.

Saved status never overrides current resource authorization/public eligibility.

---

# 63. PLATFORM MESSAGING

“Message Supplier” should use existing conversation/message system for authenticated authorized users.

Guest options:

- login/register → platform messaging
- guest inquiry

Do not create a second message system.

Reuse/create appropriate conversation context without unnecessary duplicate threads.

---

# 64. PUBLIC TRUST SIGNALS

Safe signals when supported:

- EduShopify verified Supplier badge
- published ratings/reviews
- Supplier types
- public location
- response rate/time
- active listings
- service areas
- founded year
- employee count
- gallery/videos
- exhibitions

Conditional; do not assume:

- ISO Certified
- government approved
- authorized distributor
- guaranteed delivery
- verified revenue
- verified staff size

Only show claims backed by explicit data and business rules.

---

# 65. SEO

Public indexable pages may include:

- homepage
- catalog
- products/services
- categories
- public listing pages
- Supplier storefronts
- public RFQs if product strategy permits
- informational pages

Each public detail page should have:

- title
- meta description
- canonical URL
- heading hierarchy
- Open Graph basics where useful

Do not index dashboards/private resources.
Avoid infinite low-value filter URL indexing.

---

# 66. CANONICAL PUBLIC IDENTIFIERS

Use stable existing public identifiers:

```text
Supplier → supplier_profiles.slug
Listing  → listings.slug
RFQ      → rfqs.rfq_number
Category → category slug when schema provides it
```

Do not expose raw IDs as canonical public URLs when a slug/number exists.

---

# 67. RESPONSIVE FUNCTIONAL REQUIREMENTS

This section defines minimum usability requirements across screen sizes. Exact breakpoint styling, spacing, component dimensions, stacking choices, and visual behavior belong in `design_frontend.md`.

Mobile-first.

## Header

Desktop:
- full nav
- search
- account CTAs

Mobile:
- menu
- logo
- search
- account

## Catalog

Desktop:

```text
Filter Sidebar + Result Grid
```

Mobile:

```text
Filters button + Sort button
Result cards
Filter drawer
```

## Listing detail

Desktop:

```text
Media + Commercial Info + Supplier Card
```

Mobile:

```text
Media
Commercial Info
Primary CTAs
Supplier Card
Details
```

## Supplier storefront

Stack banner/header safely.
Tabs may horizontally scroll or become section navigation.

## RFQ board

Use responsive cards/list, not a desktop-only table.

No full-page horizontal overflow.

---

# 68. ACCESSIBILITY

At minimum:

- semantic headings
- labels on forms
- keyboard-accessible navigation
- visible focus states
- accessible mobile menu
- image alt text
- sufficient contrast
- buttons/links use correct elements
- icon-only buttons have accessible labels
- modal/drawer focus behavior
- status is not color-only

Autocomplete must support keyboard interaction if implemented.

---

# 69. PERFORMANCE

Use:

- server-side pagination
- eager loading
- indexed filters
- select only needed columns where practical
- image lazy loading
- responsive image sizing where available
- caching for taxonomy/settings/home sections
- strict limits for homepage sections

Avoid:

- client-side loading of all listings
- N+1 Supplier/category/review queries
- huge unrestricted category recursion
- expensive random ordering
- full-model JSON exposure to JavaScript

---

# 70. CACHING

Good cache candidates:

- active category tree
- active pricing plans
- public frontend settings/theme tokens
- homepage featured sections
- expensive public aggregates

Do not accidentally cache personalized authenticated state as public HTML.

Invalidate when public eligibility changes, e.g.:

- listing approval/publish state
- Supplier suspension/reactivation
- category changes
- review publish/hide
- plan changes

---

# 71. CURRENCY / LOCALIZATION

Default currency behavior:

```text
Display stored amount + stored currency
```

Do not silently convert currencies unless a real currency-conversion feature is defined.

Frontend should be translation-friendly using Laravel translation files where localization is in scope.
A language database row alone does not mean translation content exists.

---

# 72. SECURITY & PRIVACY

Protect against:

- IDOR into private resources
- unpublished listing enumeration
- selected-Supplier RFQ leakage
- XSS
- SQL/filter injection
- mass assignment
- inquiry spam
- unsafe file/media exposure
- open redirects
- capability assumptions from UI state

Use escaped Blade output by default.
Only render trusted/sanitized rich HTML deliberately.

---

# 73. PRIVATE/UNAVAILABLE RESOURCE BEHAVIOR

For public requests to:

- draft listing
- pending listing
- rejected listing
- unpublished listing
- suspended Supplier
- selected-Supplier RFQ
- private/expired/non-public RFQ

return safe 404/unavailable behavior.

Do not reveal internal moderation status to anonymous visitors.

---

# 74. RATE LIMITING

Rate-limit where appropriate:

- inquiry submission
- contact submission
- autocomplete/search endpoints if needed
- guest actions that send email/notification

Use reasonable limits.
Do not aggressively throttle normal browsing.

---

# 75. PUBLIC QUERY CONTRACTS

Prefer reusable public query scopes/services.

Conceptually:

```text
PublicListingQuery
- publiclyVisible
- products
- services
- search
- category
- supplier

PublicSupplierQuery
- publiclyVisible
- search
- type
- location

PublicRfqQuery
- global
- open
- published
- safeSummary
```

Blade should receive public-safe data.
Do not rely on Blade `@if` as business authorization.

---

# 76. HOMEPAGE DATA SERVICE

Homepage may use a focused data collector:

```text
HomepageData
├── topCategories
├── featuredProducts
├── featuredServices
├── featuredSuppliers
├── openRfqOpportunities
└── featuredPlans
```

Each section must have strict result limits.

---

# 77. COMPONENT REUSE RULE

```text
Repeated visual markup
→ Blade component

Simple local UI interaction
→ inline Alpine x-data

Reusable/complex UI interaction
→ Alpine.data()

Business logic/authorization/data mutation
→ Laravel backend
```

Examples:

```text
Listing Card → Blade
Supplier Card → Blade
RFQ Card → Blade
Mobile Menu → Blade + Alpine
Filter Drawer → Blade + Alpine UI + server-side filters
Autocomplete → Alpine UI + server endpoint
Inquiry Form → normal Laravel form + Form Request
```

---

# 78. EMPTY / ERROR STATES

Every discovery page needs a clear empty state.

Examples:

```text
No products match your filters.
Clear filters / Browse categories
```

```text
No Suppliers found for this location/category.
Clear filters
```

```text
No public RFQ opportunities match your search.
```

Handle:

- missing media
- stale slug
- invalid RFQ number
- expired content
- validation errors
- rate limit
- server errors

Production must not expose stack traces, SQL, file paths, or secrets.

---

# 79. FRONTEND CONFIGURATION / CONTENT

Use existing `settings` architecture for Admin-configurable frontend content only when needed.

Possible future settings:

- hero title/subtitle
- featured category IDs
- homepage section visibility
- support contact details
- social links
- frontend design tokens

Do not build a full CMS unless the project later requires it.
Static Blade/config content is acceptable for stable informational pages.

---

# 80. FRONTEND PLAN ACCURACY RULE

The proposed frontend concept contains useful ideas, but implementation must remain database-accurate.

## Supported/appropriate when data exists

- verified Supplier badge
- rating/reviews
- response rate/time
- tier pricing
- MOQ
- stock status
- service mode
- lead time
- founded year
- employee count
- service areas
- business hours
- gallery/video

## Conditional / do not assume

- ISO Certified filter/badge
- verified years
- public compliance downloads
- founding Supplier badge
- delivery guarantee
- exact shipping promise
- real-time inventory guarantee

---

# 81. FILTER QUERY PARAMETER CONTRACT

Recommended GET parameters:

```text
q
category
brand
supplier
country
state
city
listing_type
pricing_type
min_price
max_price
min_moq
stock_status
service_mode
verified
sort
page
```

Validate/normalize parameters.
Use allowlists for sort/filter options.

---

# 82. BUYER FRONTEND JOURNEY

```text
Homepage
  ↓
Search / Category / Supplier
  ↓
Listing / Supplier Detail
  ↓
Request Quote
  ↓
Login/Register if needed
  ↓
Buyer eligibility
  ↓
Buyer Dashboard RFQ creation
```

Buyer-focused frontend CTAs:

- Find Products
- Find Services
- Find Suppliers
- Request Quote
- Post an RFQ

Private quotation comparison remains in Buyer Dashboard.

---

# 83. SUPPLIER FRONTEND JOURNEY

```text
Homepage / RFQ Board / Pricing
      ↓
Become a Supplier / Submit Quotation CTA
      ↓
Login/Register
      ↓
Supplier application if needed
      ↓
Admin approval
      ↓
Subscription eligibility
      ↓
Supplier Dashboard
      ↓
RFQ eligibility
      ↓
Quotation workflow
```

Do not imply registration alone allows immediate quotation submission.

---

# 84. FRONTEND → DASHBOARD HANDOFF MATRIX

| Frontend CTA | Destination |
|---|---|
| Post an RFQ | Buyer RFQ create workflow |
| Request Quote from listing | Buyer RFQ create with listing context |
| Request Quote from Supplier | Buyer RFQ create with Supplier context |
| Save Listing | existing saved-items workflow |
| Save Supplier | existing saved-items workflow |
| Message Supplier | authenticated conversation workflow |
| Submit Quotation | Supplier RFQ/quotation workflow |
| Become a Supplier | existing registration/Supplier onboarding |
| Choose Supplier Plan | Supplier subscription workflow |
| Manage Listing | Supplier Dashboard |

Do not duplicate full dashboard business forms into public frontend just to avoid redirecting.

---

# 85. DEVELOPMENT PROCESS

Before each phase:

1. inspect `ARCHITECTURE.md`
2. inspect current database schema
3. inspect existing frontend/auth code
4. inspect related Buyer/Supplier workflow
5. inspect reusable project code
6. define public-data rule
7. implement
8. test
9. continue

Do not stop after only auditing.

---

# 86. PHASE 1 — FRONTEND FOUNDATION

Implement:

- `routes/frontend.php` integration
- frontend controller structure
- `frontend/layouts/master.blade.php`
- head/header/mobile menu/footer/scripts
- frontend CSS variables/tokens
- guest/authenticated header state
- global container/spacing system
- public error/404 integration

Do not change registration design.

---

# 87. PHASE 2 — PUBLIC ELIGIBILITY FOUNDATION

Create/reuse centralized rules for:

- public listings
- public Suppliers
- public reviews
- public RFQs
- active categories
- active pricing plans

Write privacy tests first.

Critical tests:

- pending/rejected listing hidden
- unpublished listing hidden
- suspended Supplier hidden
- selected-Supplier RFQ hidden
- hidden/rejected review hidden

---

# 88. PHASE 3 — HOMEPAGE

Complete:

- hero/search
- categories
- featured products
- featured services
- featured Suppliers
- public RFQs
- How It Works
- trust section
- Buyer/Supplier CTAs
- pricing teaser

Use real database data.

---

# 89. PHASE 4 — CATEGORIES / CATALOG

Complete:

- categories index/detail
- catalog
- products
- services
- search
- filters
- sort
- pagination
- responsive filter drawer
- listing cards
- empty states

---

# 90. PHASE 5 — LISTING DETAIL

Complete:

- product/service detail
- media gallery
- variants
- attributes/specs
- tier pricing
- optional display estimate
- Supplier card
- Request Quote
- Contact Supplier
- Save handoff
- related listings

---

# 91. PHASE 6 — SUPPLIER DIRECTORY / STOREFRONT

Complete:

- directory
- search/filters
- Supplier cards
- public storefront
- overview
- products/services
- reviews/replies
- gallery/videos
- service areas/hours
- public response metrics
- contact/request quote

---

# 92. PHASE 7 — PUBLIC RFQ BOARD

Complete:

- opportunities index
- global-only public query
- search/filter/sort/pagination
- RFQ card
- safe RFQ detail
- Supplier login/register/quote handoff

Explicitly test selected-Supplier privacy.

---

# 93. PHASE 8 — INQUIRIES / CONTACT

Complete:

- listing/Supplier inquiry
- general contact
- Form Requests
- rate limiting
- anti-spam baseline
- success/error states
- notifications where current workflow supports them

---

# 94. PHASE 9 — AUTH INTENT / HANDOFF

Complete safe handoff for:

- Request Quote
- Post RFQ
- Submit Quotation
- Save Listing
- Save Supplier
- Message Supplier
- Choose Plan

Test guest → auth → intended workflow.
Always re-authorize after authentication.

---

# 95. PHASE 10 — INFORMATIONAL PAGES

Complete:

- How It Works
- Pricing
- About
- Contact
- FAQs
- Terms
- Privacy

Ensure Phase 1 statements remain accurate.

---

# 96. PHASE 11 — SEO / ACCESSIBILITY / PERFORMANCE

Complete:

- titles/meta descriptions
- canonical URLs
- breadcrumbs
- alt text
- focus/keyboard support
- mobile behavior
- query optimization/N+1 review
- cache strategy
- image lazy loading

---

# 97. PHASE 12 — SECURITY / REGRESSION

Test:

- public listing eligibility
- Supplier eligibility
- global-only RFQ privacy
- inquiry spam/rate limits
- XSS
- filter/sort injection
- invalid slugs
- expired content
- open redirect protection
- registration unchanged
- Buyer dashboard unchanged
- Supplier dashboard unchanged
- Admin dashboard unchanged
- no Filament introduced
- no backend Livewire introduced

---

# 98. AUTOMATED TEST REQUIREMENTS

## Homepage

- guest homepage loads
- only public featured listings
- only eligible Suppliers
- only global public RFQs

## Listings

- approved + active + published listing visible
- draft/pending/rejected/unpublished/deleted listing not public
- suspended Supplier listing not public
- inactive Supplier capability listing not public

## Catalog

- product/service type filters
- category filter
- brand filter
- location filter
- price/MOQ behavior
- service mode
- stock status
- pagination preserves filters

## Supplier

- eligible Supplier visible
- pending/suspended Supplier hidden
- only published reviews
- private documents absent
- only public listings

## RFQ

- global open published RFQ visible
- selected-Supplier RFQ never public
- draft/closed/expired/cancelled behavior correct
- exact private address absent
- selected Supplier data absent

## Inquiry

- valid inquiry stored
- invalid email/message rejected
- private listing inquiry rejected
- rate limiting
- Supplier resolved server-side
- source metadata captured safely

## Auth handoff

- guest Request Quote → auth
- Buyer → RFQ context
- Supplier-only user does not gain Buyer capability
- guest Submit Quotation → auth
- eligible Supplier → Supplier flow
- ineligible Supplier blocked

## Security

- XSS escaped
- unsafe sort/filter rejected
- private files unavailable
- open redirect prevented

## Regression

- registration/login works
- Buyer dashboard tests pass
- Supplier dashboard tests pass
- Admin tests pass

---

# 99. MANUAL RESPONSIVE TEST MATRIX

Test at minimum:

```text
320px
375px
390px
768px
1024px
1280px
1440px+
```

Test:

- header/mobile drawer
- global search/autocomplete
- hero
- category cards
- listing cards
- filters
- listing gallery
- tier pricing
- Supplier storefront
- RFQ cards/detail
- inquiry forms
- footer

No uncontrolled horizontal page scrolling.

---

# 100. PERFORMANCE TEST MATRIX

With realistic data, test:

- thousands of listings
- large category tree
- many Suppliers
- many public RFQs
- reviews
- variants/tier prices

Check:

- query counts
- N+1
- response times
- pagination
- indexed searches/filters
- homepage result limits
- image payload

---

# 101. DO NOT BUILD IN PHASE 1

Unless explicitly requested later, do NOT add:

- shopping cart
- direct public product checkout
- product payment gateway
- escrow
- Buyer wallet
- Supplier payout wallet
- marketplace commission ledger
- anonymous RFQ submission
- anonymous quotation submission
- public quotation details
- public Buyer directory
- public Buyer member list
- automatic public Supplier document downloads
- generic CMS
- Filament frontend
- backend Livewire
- React/Vue SPA rewrite

EduShopify Phase 1 is procurement/RFQ-oriented, not a generic retail store.

---

# 102. PUBLIC SECURITY CONTRACT

Before rendering any public business resource:

```text
Resolve canonical record
        ↓
Check public eligibility
        ↓
Check related account/capability state
        ↓
Apply public field whitelist
        ↓
Render Blade
```

Before protected action:

```text
Frontend CTA
    ↓
Authenticated?
    ├── No → Login/Register with safe intent
    └── Yes
          ↓
      Account/Capability/Permission Check
          ↓
      Resource Re-check
          ↓
      Dashboard Workflow
```

Frontend visibility is never authorization.

---

# 103. COMPLETE PUBLIC BUYER JOURNEY

```text
Guest lands on EduShopify
        ↓
Search / Browse Category
        ↓
Product / Service / Supplier
        ↓
View Public Detail
        ↓
        ├── Guest Inquiry
        │      ↓
        │   contact_inquiries
        │
        └── Request Official Quote
               ↓
           Login / Register
               ↓
           Buyer Eligibility
               ↓
           Buyer Dashboard
               ↓
           RFQ Creation with Context
               ↓
           Buyer RFQ Workflow
```

---

# 104. COMPLETE PUBLIC SUPPLIER JOURNEY

```text
Guest lands on EduShopify
        ↓
RFQ Board / Pricing / How It Works
        ↓
Browse Safe Public Opportunity
        ↓
Submit Quotation CTA
        ↓
Login / Register
        ↓
Supplier Application if needed
        ↓
Admin Approval
        ↓
Subscription Eligibility
        ↓
Supplier Dashboard
        ↓
Authenticated RFQ Eligibility
        ↓
Quotation Workflow
```

---

# 105. DOCUMENTATION LOCATION

Recommended project documentation:

```text
docs/
└── ai/
    ├── ARCHITECTURE.md
    ├── design.md                       # backend dashboards
    ├── design_frontend.md              # public frontend visual/UI source of truth
    │
    ├── workflows/
    │   ├── frontend_workflow.md
    │   ├── buyer_dashboard_workflow.md
    │   ├── supplier_dashboard_workflow.md
    │   └── admin_dashboard_workflow.md
    │
    └── references/
        └── edushopify_dashboard_reference.html
```

A separate public frontend visual reference may be added later under `docs/AI/references/` if one is created.

---

# 106. AI IMPLEMENTATION PROCESS

When an AI coding agent implements frontend:

```text
Read ARCHITECTURE.md
       ↓
Read frontend_workflow.md
       ↓
Read design_frontend.md
       ↓
Read Buyer/Supplier workflow sections relevant to CTAs
       ↓
Inspect current database schema
       ↓
Inspect existing registration/auth implementation
       ↓
Inspect current frontend files
       ↓
Audit current state
       ↓
Build public eligibility/query foundation
       ↓
Implement phase-by-phase
       ↓
Test after each phase
```

Do not stop after the audit.

Do not leave fake static marketplace cards as final implementation when database-backed data is required.

---

# 107. DEFINITION OF COMPLETE

A public frontend feature is complete only when:

```text
Route exists
+
Controller/query logic exists
+
Public eligibility enforced
+
Existing database reused
+
Blade page/component exists
+
Responsive behavior works
+
Empty/error states work
+
Search/filter/pagination works where relevant
+
Protected CTA handoff works
+
Guest privacy preserved
+
Accessibility basics work
+
SEO metadata exists where appropriate
+
Automated tests pass
+
Registration unchanged
+
Buyer/Supplier/Admin dashboards remain working
```

A page that only renders hard-coded marketplace cards is not complete.

---

# 108. FINAL FRONTEND OBJECTIVE

The completed frontend must be the public discovery and conversion layer for EduShopify's B2B education procurement marketplace.

```text
                      EDUSHOPIFY PUBLIC MARKETPLACE
                                  │
             ┌────────────────────┼────────────────────┐
             │                    │                    │
             ↓                    ↓                    ↓
        DISCOVERY             SOURCING              TRUST
             │                    │                    │
      Products/Services      Public RFQs          Suppliers
      Categories/Search      Post RFQ CTA         Reviews
             │               Quote Handoff        Verification
             └────────────────────┼────────────────────┘
                                  ↓
                           BUSINESS ACTION
                                  │
                    ┌─────────────┴─────────────┐
                    ↓                           ↓
              Guest Inquiry              Protected Workflow
                                                ↓
                                      Login/Register if needed
                                                ↓
                                      Buyer/Supplier Dashboard
```

The frontend must be:

- database-driven
- public-data-safe
- marketplace-focused
- B2B-oriented
- education-procurement oriented
- responsive
- accessible
- SEO-friendly
- performant
- reusable
- secure
- compatible with existing registration
- compatible with Buyer Dashboard
- compatible with Supplier Dashboard
- compatible with Admin Dashboard

Do not build the public frontend as another dashboard.
Do not build it as a generic retail shopping cart.
Build it as the public marketplace layer that helps visitors discover, trust, and enter EduShopify's authenticated RFQ procurement workflows.
