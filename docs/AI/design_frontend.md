# EduShopify Public Frontend Design Specification — `design_frontend.md`

> **Status:** Mandatory public frontend visual/UI specification.
>
> This document defines **how the EduShopify public marketplace looks, feels, responds, and composes its reusable UI**.
>
> It does **not** redefine business workflow, authorization, database eligibility, procurement rules, Supplier subscription rules, or protected dashboard behavior. Those belong to `frontend_workflow.md`, the Buyer/Supplier/Admin workflow files, `ARCHITECTURE.md`, and the current database schema.
>
> Use this document together with:
>
> 1. `docs/AI/ARCHITECTURE.md`
> 2. `docs/AI/workflows/frontend_workflow.md`
> 3. `docs/AI/workflows/buyer_dashboard_workflow.md` when a public CTA hands into Buyer functionality
> 4. `docs/AI/workflows/supplier_dashboard_workflow.md` when a public CTA hands into Supplier functionality
> 5. the current EduShopify database schema / SQL dump
> 6. the existing authentication / registration implementation
> 7. `docs/AI/references/fronted_reference/` as a **visual reference library only**
>
> The reference HTML files are demos of the desired design direction. They are **not** the source of truth for literal text, counts, claims, business rules, authorization, routes, database fields, or final production markup.

---

# 1. RESPONSIBILITY & PRECEDENCE

Use each source for one concern:

```text
Database Schema(docs\AI\references\database.txt)
→ real fields / relationships / statuses / available data

ARCHITECTURE.md
→ Laravel architecture and folder responsibility

frontend_workflow.md
→ public functionality, visibility, guest behavior, protected handoff

design_frontend.md
→ public visual system, layout, component appearance, responsive presentation

Static HTML References
→ visual inspiration / proportions / interaction examples
```

If two sources conflict:

```text
Business / data conflict
→ database + workflow win

Laravel structure conflict
→ ARCHITECTURE.md + finalized frontend_workflow.md win

Visual conflict
→ this design_frontend.md wins

Static reference conflict
→ static reference is adapted, not copied blindly
```

Do not change business behavior only to make a static demo easier to reproduce.

---

# 2. STATIC REFERENCE LIBRARY

Reference directory(it is just reference no need to blindy follow, you can use own intelligence):

```text
docs/AI/references/fronted_reference/
```

Current design references:

```text
home.html
→ Homepage design direction

catalog.html
→ Marketplace catalog, filters, listing grid

listing.html
→ Product/service detail, variants, tier pricing, quote panel

suppliers.html
→ Supplier directory

supplier.html
→ Public Supplier storefront/profile

rfqs.html
→ Public RFQ opportunities board

rfq-detail.html
→ Public RFQ summary/detail
```

## 2.1 Reference usage rule

The coding AI should:

1. inspect the relevant static reference before implementing the related page;
2. preserve the overall design language, spacing, visual hierarchy, proportions, and interaction intent;
3. improve markup, accessibility, responsive behavior, and component reuse where needed;
4. replace demo content with real database-driven content;
5. remove or conditionally hide UI that has no real project functionality;
6. never reproduce fake statistics, certifications, Supplier claims, review counts, or RFQ data from the demo.

## 2.2 Demo-only examples

The following types of content in reference HTML are examples only(all data comes from database):

- `Trusted by 2,400+ institutions`
- `1,247 verified suppliers`
- `Verified · 5 yrs`
- `ISO 9001:2015`
- hard-coded country flags/cities
- hard-coded ratings/review counts
- hard-coded prices
- hard-coded category names
- hard-coded RFQ deadlines
- hard-coded plan values
- newsletter signup
- social links using `#`

Render such information only when real project data/configuration supports it.

---

# 3. DESIGN IDENTITY

The public frontend is a **professional B2B education marketplace**, not another dashboard and not a consumer retail store.

Desired characteristics:

```text
Professional
Trustworthy
Institutional
Modern
Calm
Search-focused
Marketplace-oriented
Procurement-aware
Content-first
Responsive
Accessible
```

Avoid:

```text
Overly playful consumer e-commerce design
Excessive gradients
Heavy glassmorphism
Huge shadows
Neon colors
Unnecessary animation
Dashboard-style sidebars
Dense enterprise tables on public discovery pages
Fake urgency
Fake scarcity
Fake social proof
```

---

# 4. FRONTEND VS BACKEND VISUAL IDENTITY

EduShopify intentionally has two related but distinct visual environments.

```text
PUBLIC FRONTEND
Primary identity: Emerald
Purpose: discovery + trust + conversion
Headings: Outfit selectively
Body/UI: Inter
No dashboard sidebar
More whitespace and imagery

BACKEND DASHBOARDS
Primary identity: Indigo / configured backend theme
Purpose: operations + management
Typography: Inter
Sidebar-driven
Data-dense
```

Do not import the Indigo dashboard identity into the public frontend as a general accent.

Do not import the Emerald frontend identity into backend dashboards merely for consistency.

The same logo/brand name may appear in both environments.

---

# 5. COLOR SYSTEM

Use centralized frontend design tokens in `resources/css/frontend.css` or the project's equivalent Vite-managed frontend stylesheet.

```css
:root {
    /* Brand */
    --fe-primary: #216C50;
    --fe-primary-hover: #2D8A67;
    --fe-primary-soft: #E7F3EE;
    --fe-primary-contrast: #FFFFFF;

    /* Text */
    --fe-text: #0F172A;
    --fe-text-muted: #475569;
    --fe-text-subtle: #94A3B8;
    --fe-text-inverse: #FFFFFF;

    /* Surfaces */
    --fe-canvas: #F8FAFC;
    --fe-surface: #FFFFFF;
    --fe-surface-soft: #F1F5F9;
    --fe-border: #E2E8F0;
    --fe-border-strong: #CBD5E1;

    /* Dark contrast surfaces */
    --fe-dark: #0F172A;
    --fe-dark-soft: #1E293B;

    /* Semantic */
    --fe-rating: #F59E0B;
    --fe-success: #16A34A;
    --fe-success-soft: #F0FDF4;
    --fe-warning: #D97706;
    --fe-warning-soft: #FFFBEB;
    --fe-danger: #DC2626;
    --fe-danger-soft: #FEF2F2;
    --fe-info: #0284C7;
    --fe-info-soft: #F0F9FF;
}
```

## 5.1 Color rules

Primary Emerald is used for:

- primary CTA buttons;
- active navigation;
- active filter chips;
- links that need brand emphasis;
- category icon backgrounds through soft Emerald;
- verified/featured marketplace badges where appropriate;
- focus outline/ring;
- selected tab underline/indicator;
- small decorative brand accents.

Amber is used for:

- rating stars;
- warning semantics when genuinely a warning.

Amber is **not** a second brand color.

Indigo is not a general public frontend accent.

Semantic colors must keep their meaning:

```text
Green → positive / available / success
Amber → warning / rating star
Red → error / destructive / unavailable
Blue → informational only when needed
```

Do not make every badge Emerald. Neutral metadata should stay Slate.

---

# 6. TYPOGRAPHY

## 6.1 Font pairing

Use:

```text
Outfit
→ major public/marketing headings only

Inter
→ body text
→ navigation
→ buttons
→ labels
→ forms
→ filters
→ cards
→ tables
→ breadcrumbs
→ metadata
→ pricing
→ specifications
→ all dense UI
```

Outfit is an accent typeface, not the default for every heading-sized text element.

## 6.2 Recommended typography scale

```text
Hero Display
Desktop: 48–56px / 1.08–1.15
Tablet: 40–48px
Mobile: 34–40px
Weight: 700
Font: Outfit

Page H1
Desktop: 28–32px
Mobile: 24–28px
Weight: 700
Font: Outfit

Section H2
Desktop: 24–28px
Mobile: 21–24px
Weight: 700
Font: Outfit

Section H3 / Marketing Card Heading
18–20px
Weight: 600–700
Outfit when marketing-oriented; otherwise Inter

Marketplace Card Title
14–16px
Weight: 600
Font: Inter

Body Large
16–18px
Inter

Body
14–16px
Inter

Meta / Caption
12–13px
Inter
```

## 6.3 Text rules

- Use sentence case for headings and labels.
- Avoid ALL CAPS except tiny utility labels when necessary.
- Avoid long center-aligned paragraphs.
- Center alignment is acceptable for hero/marketing sections.
- Data-heavy marketplace pages should use left alignment.
- Keep line length around 60–75 characters for long-form content.

---

# 7. ICON SYSTEM

Use **Font Awesome 6** as the single frontend icon family, consistent with the reference pages.

Do not mix:

- Heroicons;
- Lucide;
- Bootstrap Icons;
- custom random SVG icon packs;

unless the project later intentionally changes the icon system.

Rules:

- icons support meaning; they do not replace labels for important actions;
- icon-only buttons require accessible labels;
- use consistent icon sizing;
- avoid decorative icon overload.

---

# 8. PRODUCTION ASSET RULES

The static reference HTML uses CDN Tailwind and CDN Alpine for demo convenience.

Production Laravel implementation must **not copy the static `<script src="https://cdn.tailwindcss.com">` pattern**.

Use the project's normal Vite pipeline.

Preferred production responsibility:

```text
Vite entry
├── frontend CSS
├── Tailwind build
├── Alpine initialization
└── frontend JS behaviors
```

Load fonts/icons once through the public layout head, not separately on every page.

Do not initialize multiple Alpine runtimes.

---

# 9. LAYOUT SYSTEM

## 9.1 Main container

Default public container:

```text
max-w-7xl mx-auto px-4 lg:px-6
```

Use narrower containers when content benefits from readability:

```text
RFQ detail
→ max-w-4xl

Terms / Privacy / long-form information
→ max-w-4xl or max-w-5xl

Contact form
→ max-w-3xl
```

## 9.2 Page background

Discovery/index pages:

```text
background: var(--fe-canvas)
```

Marketing sections may alternate:

```text
white surface
soft canvas
soft Emerald section background
```

Avoid alternating colors on every section just for decoration.

## 9.3 Section spacing

Recommended:

```text
Mobile: py-10 to py-12
Desktop: py-14 to py-16
Hero: larger custom spacing
```

Use consistent vertical rhythm across homepage sections.

## 9.4 Radius system

```text
Small controls/chips: rounded-lg / rounded-xl
Cards: rounded-xl to rounded-2xl
Large hero/search containers: rounded-2xl
Pills: rounded-full
```

Do not mix many unrelated radius sizes on one page.

## 9.5 Shadow system

Default cards should rely on border + surface, not heavy shadows.

Recommended:

```text
Resting card
→ border + no/very subtle shadow

Floating search / dropdown
→ shadow-sm / shadow-lg as appropriate

Hover card
→ subtle elevation only
```

---

# 10. INTERACTION & MOTION

Motion should communicate state, not decorate everything.

Allowed:

- dropdown fade/scale;
- mobile drawer slide;
- card hover lift up to `translateY(-2px)`;
- button/background transitions around 150–200ms;
- tab content transition where subtle;
- small “live opportunity” pulse indicator.

Avoid:

- auto-scrolling content that users cannot pause;
- large parallax effects;
- constant bouncing CTAs;
- long entrance animations;
- animation on every card.

Respect:

```css
@media (prefers-reduced-motion: reduce) {
    /* disable non-essential animation */
}
```

The RFQ homepage section should look “live” through a small status indicator and fresh deadline data; it does not need an inaccessible continuously moving ticker.

---

# 11. FOCUS / ACCESSIBILITY VISUALS

Every interactive control needs a visible keyboard focus state.

Recommended focus style:

```text
2px / 3px soft Emerald ring
with sufficient contrast
```

Do not remove outlines without replacement.

Ensure:

- text contrast passes normal accessibility expectations;
- status does not depend on color only;
- buttons have visible hover/focus/disabled states;
- links remain distinguishable;
- form errors include message + color + icon where useful.

---

# 12. PUBLIC LAYOUT SHELL

Use the finalized frontend view structure from `frontend_workflow.md`.

```text
resources/views/frontend/
├── layouts/
│   ├── master.blade.php
│   └── partials/
│       ├── _head.blade.php
│       ├── _header.blade.php
│       ├── _mobile_menu.blade.php
│       ├── _footer.blade.php
│       └── _scripts.blade.php
```

Do not use the older demo names `app.blade.php` / `_navbar.blade.php` if the finalized frontend workflow uses `master.blade.php` / `_header.blade.php`.

The static HTML is mapped into these reusable production partials.

---

# 13. GLOBAL HEADER

Reference direction comes from all 7 static pages: white sticky header, `h-20`, Emerald brand mark, categories menu, marketplace navigation, global search, and auth actions.

## 13.1 Header shell

```text
height: h-20 (80px)
background: white
position: sticky top-0
z-index: 40 or appropriate project layer
border-bottom: 1px solid var(--fe-border)
container: max-w-7xl
```

Header should remain visually stable across all public pages.

Do not give individual pages different header heights.

## 13.2 Desktop layout

Recommended hierarchy:

```text
[Logo]
[Categories ▼]
[Marketplace]
[Suppliers]
[Opportunities]
[Pricing]
        [Global Search................]
[Login / Account]
[Post RFQ]
[Join Free / Dashboard]
```

At narrower desktop widths, lower-priority actions may collapse before search becomes unusably narrow.

## 13.3 Logo

Reference uses:

- 36×36 Emerald `ES` mark;
- EduShopify wordmark;
- Outfit for wordmark;
- wordmark hidden at very narrow mobile widths when needed.

If a production logo asset exists later, use it instead of permanently keeping the text placeholder.

## 13.4 Category dropdown / mega-menu

Use dynamic active categories.

Desktop behavior:

- button opens on click;
- closes on outside click and Escape;
- keyboard navigable;
- shows a curated/top-level category subset;
- includes “Browse all categories”.

Visual direction:

```text
white surface
border
rounded-xl
shadow-lg
comfortable rows
Emerald icon accents
```

Do not hard-code reference categories in production.

If categories become numerous, upgrade the menu to 2–3 columns or grouped sections while keeping the same visual language.

## 13.5 Navigation states

```text
Default: muted Slate
Hover: Emerald
Active: Emerald + semibold
```

Do not use a heavy filled active nav background unless later design testing requires it.

## 13.6 Global search in header

Desktop search:

- max width around 360–440px depending available space;
- pill/rounded-full shape;
- subtle Slate canvas background;
- search icon left;
- autocomplete dropdown directly below.

Placeholder:

```text
Search products, services, suppliers...
```

RFQs may also be included in search results according to workflow; placeholder does not need to list every entity.

## 13.7 Guest auth actions

Guest desktop priority:

```text
Login
Post an RFQ (outline/secondary when space allows)
Join Free (primary Emerald)
```

If space is constrained:

- keep `Join Free` visible;
- move `Post an RFQ` into the mobile/overflow navigation;
- never crush the global search to preserve all buttons.

## 13.8 Authenticated header

Replace Login/Join Free with account-aware controls.

Possible states:

```text
Buyer-only
→ Post RFQ + Buyer Dashboard + account menu

Supplier-only
→ Supplier Dashboard + account menu

Both capabilities
→ Dashboard selector / account menu
```

Exact capability behavior comes from `frontend_workflow.md`, not this design file.

## 13.9 Mobile header

Below `lg`:

```text
[Menu] [Logo] [Search icon] [Account/Join]
```

The full nav moves into `_mobile_menu.blade.php`.

Mobile drawer includes:

- Categories accordion;
- Marketplace;
- Products;
- Services;
- Suppliers;
- Opportunities;
- Pricing;
- How It Works;
- Post RFQ;
- Login/Register or dashboard/account actions.

The drawer must:

- use backdrop;
- close on backdrop click;
- close on Escape;
- prevent body scroll while open where practical;
- have a visible close button;
- be keyboard accessible.

---

# 14. GLOBAL SEARCH AUTOCOMPLETE DESIGN

Autocomplete is a visual layer over server-side public search.

Desktop dropdown:

```text
positioned below search
white surface
border
rounded-xl
shadow-lg
max-height with scroll
```

Group results by type:

```text
Products
Services
Suppliers
RFQ Opportunities (if included)
```

Each suggestion may show:

- compact thumbnail/icon;
- title;
- entity type;
- one line of metadata;
- highlighted matched phrase if safe.

Bottom action:

```text
View all results for “query”
```

UI states:

- idle;
- typing/debounced;
- loading;
- results;
- no results;
- error fallback.

Keyboard behavior:

- ArrowUp/ArrowDown;
- Enter;
- Escape;
- sensible focus/ARIA pattern.

---

# 15. GLOBAL FOOTER

Use the dark Slate footer direction from the static references.

## 15.1 Footer structure

Desktop:

```text
5-column visual grid

Columns 1–2
→ brand + short marketplace description + configured social links

Marketplace
→ Catalog / Products / Services / Suppliers / Opportunities / Pricing

For Buyers / Company
→ How It Works / Post RFQ / About / Contact / FAQs

Legal
→ Terms / Privacy
```

Mobile:

- 1–2 columns;
- comfortable spacing;
- no tiny multi-column squeeze.

## 15.2 Footer social links

Render only configured real URLs.

Do not output dead `href="#"` icons in production.

## 15.3 Newsletter

Newsletter is **not part of the default production design** because no confirmed newsletter workflow exists.

Do not implement a decorative email input that goes nowhere.

If newsletter functionality is intentionally added later, create a real workflow first and then add the component.

---

# 16. PAGE HEADER / BREADCRUMBS PATTERN

Non-home public pages typically start with:

```text
Breadcrumbs
Page title + supporting text
Optional page-level action/search/sort
```

Recommended spacing:

```text
page top: py-6 to py-8
breadcrumbs mb-4 to mb-5
heading block mb-6
```

Breadcrumbs:

- 12px/13px Inter;
- muted text;
- Emerald hover on links;
- small chevron separator;
- current page darker Slate;
- collapse long ancestry on small screens if necessary.

---

# 17. REUSABLE PUBLIC COMPONENT SYSTEM

Follow the component structure from `frontend_workflow.md`.

```text
resources/views/frontend/components/
├── marketplace/
│   ├── listing-card.blade.php
│   ├── supplier-card.blade.php
│   ├── rfq-card.blade.php
│   ├── category-card.blade.php
│   ├── tier-pricing-table.blade.php
│   ├── variant-selector.blade.php
│   └── rating-summary.blade.php
├── search/
│   ├── global-search.blade.php
│   ├── search-suggestions.blade.php
│   └── filter-drawer.blade.php
├── navigation/
│   └── breadcrumbs.blade.php
└── common/
    ├── empty-state.blade.php
    ├── pagination.blade.php
    ├── badge.blade.php
    └── section-heading.blade.php
```

Do not duplicate full card markup inside every page.

Static HTML should be refactored into this component system during implementation.

---

# 18. BUTTON SYSTEM

## 18.1 Primary button

Use for one dominant action per local decision area.

Visual:

```text
Emerald background
white text
font-semibold
rounded-lg / rounded-xl
height around 40–44px
subtle 150ms transition
```

States:

- hover;
- focus;
- disabled;
- loading where relevant.

## 18.2 Secondary / outline button

Visual:

```text
white background
Slate text
Slate border
hover → soft Slate background
```

## 18.3 Text link action

Use for low-emphasis navigation such as:

- View all;
- Browse marketplace;
- View Supplier;
- View all opportunities.

Use Emerald text with semibold weight.

## 18.4 Destructive button

Public frontend should have very few destructive actions.

If required for an authenticated inline action, use Red semantic styling, not Emerald.

---

# 19. BADGES / CHIPS

Badge classes are semantic rather than decorative.

Recommended variants:

```text
Verified
→ Emerald soft + Emerald text + check icon

Featured
→ Emerald soft or neutral Slate

RFQ Pricing / Quote Only
→ Slate soft / neutral

Product / Service type
→ neutral or soft Emerald depending density

In Stock
→ success semantic

Limited
→ warning semantic

Out of Stock
→ danger/neutral according to context

Open RFQ
→ Emerald soft

Closed/Expired
→ neutral Slate
```

Do not use “Verified” when the actual Supplier/listing does not qualify under workflow/business rules.

---

# 20. RATING COMPONENT

Use Amber stars only.

Recommended compact format:

```text
★ 4.9 (128)
```

Avoid drawing five full stars everywhere when a compact rating saves space.

Full star display is appropriate in:

- review summaries;
- Supplier profile header;
- review cards.

No rating UI when there are no published reviews unless the product decision explicitly shows `New`/`No reviews yet`.

---

# 21. CARD SYSTEM

Base marketplace card:

```text
white background
1px Slate border
rounded-2xl
no heavy shadow
subtle hover elevation on pointer devices
```

Card hover must not be the only indication of clickability.

Avoid wrapping an entire card in one `<a>` if the card also contains independent buttons/links; use valid accessible markup.

---

# 22. CATEGORY CARD

Reference direction:

- icon in 48×48 soft Emerald rounded-xl box;
- centered content;
- category name;
- optionally public listing count.

Grid:

```text
mobile: 2 columns
small: 3 columns
desktop: 6 columns
```

Cards use `rounded-2xl` and consistent height.

Icons/categories are dynamic from project data/configuration.

---

# 23. LISTING CARD

The same component should adapt to Product and Service listings.

## 23.1 Card structure

```text
Media
  ├── type/verified/pricing badge when appropriate
  └── optional save button for authenticated user

Content
  ├── title
  ├── category/brand or service mode
  ├── pricing summary
  ├── MOQ / lead time / service metadata when useful
  └── Supplier row + rating

Actions
  ├── View Details
  └── Request Quote where appropriate
```

## 23.2 Product card

May show:

- image;
- name;
- brand;
- SKU only if useful/public;
- price / Request Quote;
- unit;
- MOQ;
- stock status or lead time where helpful;
- Supplier;
- rating.

Do not overload cards with every product specification.

## 23.3 Service card

May show:

- service image/icon;
- name;
- service mode;
- duration if useful;
- lead time;
- Supplier;
- pricing mode;
- rating.

Do not show product-only stock UI on service cards.

## 23.4 Pricing display

```text
fixed
→ formatted amount + currency + unit where available

quote_only
→ “Request Quote” / “Quote Only”

rfq_enabled
→ fixed price when stored OR “RFQ Pricing” depending workflow/data
```

Never display `$0.00` merely because a quote-only record lacks a price.

---

# 24. SUPPLIER CARD

Card hierarchy:

```text
Logo
Business name + Verified badge if eligible
Location
Supplier type(s)
Short public description
Category/service summary when useful
Rating + review count
View Profile
```

Optional response metric may be shown only when public and meaningful.

Do not display:

- private contact person;
- legal documents;
- subscription payment details;
- internal capability status;
- “verified years” unless a real verified-since business field/rule exists;
- unsupported certification claims.

Grid:

```text
mobile: 1 column
md: 2 columns
lg/xl: 3 columns
```

---

# 25. RFQ OPPORTUNITY CARD / ROW

RFQ discovery is better as a structured list/card row than a retail product tile.

Recommended content:

```text
RFQ number
Category summary
Title
Delivery location summary
Item count
Quotation deadline
Expected delivery date where useful
View Summary
```

Use a small “Open”/live indicator.

Do not show exact private Buyer details.

Responsive:

- desktop row with metadata/actions aligned right;
- mobile stacked card.

---

# 26. FILTER UI SYSTEM

## 26.1 Desktop

Catalog filter sidebar:

```text
width: approximately 260–300px
white surface
border
rounded-xl / rounded-2xl
sticky when practical
```

Filter groups:

- clear headings;
- enough spacing;
- collapsible only if list becomes long;
- active selections visible.

## 26.2 Mobile

Use `Filters` button to open a full-height/bottom-sheet/side drawer according to final implementation.

Recommended:

- fixed overlay;
- clear title;
- Reset;
- Apply Filters;
- close button;
- scrollable filter body;
- safe-area padding.

## 26.3 Active filter chips

Above results, optionally show removable chips for active filters.

Example:

```text
Lab Equipment ×
Verified ×
Under $500 ×
```

## 26.4 Dynamic attribute filters

Category-specific filters should visually join the same filter system rather than looking like a separate widget.

Possible controls:

- checkbox list;
- radio list;
- select;
- number min/max;
- boolean toggle;
- color swatches only when the attribute is truly a color.

Control type follows real attribute data/workflow.

---

# 27. FORM CONTROLS

Use consistent public form styling:

```text
height: 42–46px for single-line controls
rounded-xl
1px border
white or soft Slate background
Inter 14px
Emerald focus ring
```

Textarea:

- minimum 120px;
- resize vertical;
- same border/focus system.

Labels:

- 13–14px;
- medium/semibold;
- clear required marker when required.

Errors:

- Red message below field;
- input border/ring changes;
- no error conveyed through color alone.

---

# 28. PAGINATION

Use compact server-driven pagination.

Desktop:

```text
Previous
1 2 3 … 12
Next
```

Mobile may simplify to:

```text
Previous
Page X of Y
Next
```

Active page uses Emerald fill or soft Emerald + strong text.

---

# 29. EMPTY STATES

Empty states must look intentional rather than broken.

Structure:

```text
simple icon/illustration
short title
one sentence
one useful next action
```

Examples:

```text
No listings match these filters
→ Clear filters

No public RFQ opportunities right now
→ Browse marketplace

No published reviews yet
→ no CTA required
```

Avoid large decorative empty-state illustrations unless the project later defines them.

---

# 30. LOADING STATES

Traditional Blade navigation may use normal page loads.

Use loading states only for asynchronous pieces such as:

- search autocomplete;
- dynamic filter option loading if implemented;
- async save/contact interactions if later used.

Use compact spinner or 2–4 skeleton rows/cards.

Do not make every Blade page show artificial skeletons.

---

# 31. HOME VIEW COMPOSITION

The homepage must remain modular.

```text
resources/views/frontend/home/
├── index.blade.php
└── sections/
    ├── _hero.blade.php
    ├── _top_categories.blade.php
    ├── _featured_products.blade.php
    ├── _featured_services.blade.php
    ├── _featured_suppliers.blade.php
    ├── _rfq_opportunities.blade.php
    ├── _how_it_works.blade.php
    ├── _why_edushopify.blade.php
    ├── _buyer_supplier_cta.blade.php
    ├── _pricing_teaser.blade.php
    └── _marketplace_stats.blade.php
```

`_marketplace_stats.blade.php` is optional and rendered only when real statistics are available/approved.

`index.blade.php` is a composition file, not a 500-line monolith.

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
    {{-- Include marketplace stats only when real/approved --}}
@endsection
```

Homepage sections may use reusable cards from `frontend/components`.

---

# 32. HOMEPAGE — OVERALL COMPOSITION

Final homepage design order:

```text
Global Header
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
Open RFQ Opportunities
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
Global Footer
```

Do not add a newsletter section by default.

---

# 33. HOMEPAGE HERO

Reference inspiration: `home.html`.

## 33.1 Surface

Use the soft Emerald-to-canvas gradient direction:

```text
#F0F9F5 → #F8FAFC
```

Keep gradient subtle.

## 33.2 Hero content

Centered desktop layout.

Recommended max widths:

```text
headline: max-w-3xl
body: max-w-xl / max-w-2xl
search: max-w-2xl / max-w-3xl
```

## 33.3 Hero trust badge

The reference displays a numeric institutional count.

Production rule:

- show a numeric trust badge only when backed by real approved data;
- otherwise use a non-numeric label such as `B2B Education Procurement Marketplace`;
- never hard-code demo statistics.

## 33.4 Hero search card

Visual:

- white surface;
- border;
- rounded-2xl;
- shadow-sm;
- search icon;
- large input;
- primary button.

Desktop:

```text
[input........................] [Search Marketplace]
```

Mobile:

```text
[input........................]
[Search Marketplace           ]
```

## 33.5 Hero CTAs

Below/near search, ensure clear entry paths:

```text
Post an RFQ
Become a Supplier
```

Use one as primary and one as secondary depending final content hierarchy.

Do not add five competing hero buttons.

## 33.6 Popular category pills

Render a small curated/dynamic subset.

Visual:

- white/soft surface;
- thin border;
- rounded-full;
- 12–13px text;
- Emerald hover/focus.

---

# 34. HOMEPAGE TOP CATEGORIES

Reference uses six cards at desktop.

Recommended layout:

```text
2 columns mobile
3 columns small/tablet
6 columns desktop
```

Section header:

```text
Shop by Category
Explore the categories institutions source most.
View all categories →
```

Final copy can change; structure remains.

Use real category names/icons/counts.

---

# 35. HOMEPAGE FEATURED PRODUCTS

Use a dedicated Product section rather than the demo’s combined “Featured Listings” section because the workflow distinguishes Products and Services.

Desktop:

```text
4-column grid
```

Tablet:

```text
2-column grid
```

Mobile:

```text
1 column or 2 compact columns where cards remain readable
```

Prefer a normal responsive grid over an automatic carousel.

If horizontal swipe is later used on small screens, it must remain manually controllable and accessible.

Section CTA:

```text
Browse products →
```

---

# 36. HOMEPAGE FEATURED SERVICES

Visually related to Product cards but clearly service-oriented.

Recommended metadata emphasis:

- service mode;
- lead time;
- Supplier;
- location/service area when useful;
- pricing mode.

Section CTA:

```text
Browse services →
```

Avoid product-only inventory labels.

---

# 37. HOMEPAGE FEATURED SUPPLIERS

Reference direction: four clean Supplier cards.

Use real Supplier public profile data and eligibility from workflow.

Recommended card information:

- logo;
- Supplier name;
- verified badge if genuinely eligible;
- location;
- Supplier type;
- rating/review count;
- short description or category summary;
- View Supplier.

Do not display reference-only `Verified · 5 yrs` unless a real verified-since value exists.

Do not show certification names merely because a document is verified.

---

# 38. HOMEPAGE RFQ OPPORTUNITIES

Reference uses a “Live Sourcing Opportunities” list.

Keep the strong B2B procurement feeling, but avoid an automatic ticker.

Recommended design:

```text
Section heading + small live/open indicator
3–5 latest/closing public RFQ rows
View all opportunities →
```

Each row:

- RFQ number;
- category summary;
- title;
- delivery location;
- item count;
- deadline/countdown;
- View Summary.

On mobile, stack metadata and button beneath title.

Never display private selected-Supplier opportunities.

---

# 39. HOMEPAGE HOW IT WORKS

This section is not fully represented in the static homepage but is required by frontend workflow.

Visual direction:

```text
Centered heading
Buyer / Supplier segmented tabs OR two-column tracks
3–6 concise steps
simple numbered/icon markers
CTA at end
```

Desktop option:

```text
Buyer Journey | Supplier Journey
```

Mobile:

- tabs or stacked sections;
- avoid horizontal complexity.

Use only actual workflow steps.

---

# 40. HOMEPAGE WHY EDUSHOPIFY

Purpose: explain platform value without fake claims.

Use 3–4 feature cards, for example when accurate:

- Structured RFQ procurement;
- Supplier discovery;
- Competitive quotation workflow;
- Account-based Buyer/Supplier collaboration.

Visual:

- icon;
- short heading;
- 1–2 sentence explanation;
- no statistics unless real.

---

# 41. HOMEPAGE BUYER / SUPPLIER CTA

Preserve the static reference’s two-way marketplace concept.

Desktop:

```text
[Buyer panel] [Supplier panel]
```

Buyer panel:

- Emerald brand background;
- white text;
- `Post an RFQ` / `Register as Buyer` depending state.

Supplier panel:

- Dark Slate background;
- white text;
- `Become a Supplier` / `Browse Opportunities` depending state.

Mobile:

- stack vertically;
- equal emphasis;
- avoid placing critical text over complex imagery.

---

# 42. HOMEPAGE PRICING TEASER

Show Supplier plans only when active public plans exist.

Recommended design:

- concise heading;
- 2–3 plan summary cards or one pricing CTA panel;
- plan name;
- price/billing type;
- 3–5 key features;
- View Pricing.

Do not show every internal plan field on homepage.

Do not expose Stripe IDs or internal metadata.

---

# 43. HOMEPAGE MARKETPLACE STATISTICS

Optional.

If included, derive real values from public-safe data.

Possible metrics:

- eligible public Suppliers;
- active categories;
- published public listings;
- countries represented/served if derivation is clearly defined.

Do not hard-code reference values.

Do not show `institutions served` unless the project has a valid definition and count.

Visual:

- simple 2×2 mobile / 4-column desktop strip;
- large number;
- small label;
- minimal iconography.

---

# 44. CATALOG PAGE — `/catalog`

Reference: `catalog.html`.

## 44.1 Desktop layout

```text
Breadcrumbs
Title / result count                         Sort

[Filter Sidebar ~25%] [Result Grid ~75%]
```

Use a 12-column layout conceptually:

```text
Filter: 3 columns
Results: 9 columns
```

Result grid inside right area:

```text
2 columns medium
3 columns wide desktop
```

## 44.2 Header controls

Include:

- page title;
- dynamic public result count;
- sort dropdown;
- optional active filter chips.

Avoid hard-coded result counts.

## 44.3 Filter sidebar

Visual groups:

- Category;
- Listing Type;
- Price;
- MOQ;
- dynamic category attributes;
- Supplier/brand where relevant;
- country/location;
- verification;
- stock/service mode depending type.

Filters are database/workflow-driven.

## 44.4 Mobile catalog

Hide persistent sidebar.

Top controls:

```text
[Filters] [Sort]
```

Filter opens drawer.

Results become 1 or 2 columns depending card readability.

## 44.5 Products / Services pages

`/products` and `/services` use the same design shell as `/catalog`, pre-scoped to listing type.

Do not create a visually unrelated page for each.

---

# 45. CATEGORY DIRECTORY — `/categories`

No static file exists; derive from the category card language.

Recommended layout:

- breadcrumbs;
- page title + explanation;
- searchable top-level category grid;
- category cards;
- optional child-category preview;
- optional public listing counts.

Large category trees may use grouped sections rather than one giant flat grid.

---

# 46. CATEGORY LANDING — `/category/{slug}`

Use a lightweight category hero/header followed by the catalog system.

Recommended:

```text
Breadcrumb ancestry
Category title
Description if available
Child category chips/cards
Catalog results + filters
```

Do not build an unrelated marketing layout for every category.

---

# 47. LISTING DETAIL — `/listing/{slug}`

Reference: `listing.html`.

The same route supports Product and Service listing types.

## 47.1 Desktop composition

Use the reference 12-column structure:

```text
Media Gallery      4/12
Commercial Details 5/12
Quote/Supplier     3/12
```

On very wide displays, keep the whole layout inside `max-w-7xl`.

## 47.2 Mobile composition

Order:

```text
Breadcrumb
Media Gallery
Title / Supplier / Core metadata
Price / Variant / Specs
Primary CTA block
Description/specification sections
Supplier summary
Related listings
```

Do not preserve desktop three-column density on mobile.

## 47.3 Media gallery

Primary media:

- square or near-square aspect;
- white/soft Slate background;
- rounded-xl/2xl;
- object-contain for technical product imagery unless cropping is intended.

Thumbnail strip:

- 4–5 visible thumbnails;
- selected border Emerald;
- video thumbnail shows play icon;
- keyboard accessible.

## 47.4 Product heading block

Show as available:

- verification indicator;
- listing title;
- model/SKU;
- brand;
- primary category;
- rating if relevant.

Avoid putting `Verified` above the title if it actually describes Supplier verification but can be mistaken for product certification. Prefer `Verified Supplier` wording.

## 47.5 Variant selector

Variable products only.

Visual:

- label;
- selectable chips/pills/cards;
- selected state with Emerald border + soft background;
- unavailable options visually disabled.

Use accessible radio/option semantics.

Variant selection may update:

- price;
- SKU;
- stock;
- MOQ;
- lead time;
- image where supported.

This is UI state only; business truth still comes from server data.

## 47.6 Tier pricing table

Compact table:

```text
Quantity | Unit Price
```

Highlight the tier matching the currently entered quantity with subtle Emerald soft background.

Do not highlight a tier before quantity is known unless the first/default tier is intentionally selected.

## 47.7 Key metrics

Use compact grid/cards for:

- stock status;
- lead time;
- MOQ;
- warranty;
- service mode/duration for service listing.

Only show applicable fields.

## 47.8 Information tabs

Product suggested tabs:

```text
Description
Specifications
Shipping & Coverage
```

Service suggested tabs:

```text
Description
Service Details
Coverage / Terms
```

Use local Alpine state for tabs.

On small screens, tabs can horizontally scroll or become accordion sections if more readable.

## 47.9 Quote calculator / action panel

Desktop right column may be sticky below header:

```text
lg:sticky
roughly top-24/top-28
```

Panel contains as relevant:

- public price range;
- quantity;
- estimated tier price;
- estimated subtotal label;
- Request Quotation;
- Contact Supplier;
- Save listing where authenticated UI permits.

The estimate must be visually labeled as an estimate when it is not an official quotation.

For quote-only listings, replace calculator emphasis with Request Quote.

## 47.10 Supplier mini-card

Show:

- logo;
- display name;
- Verified Supplier badge when eligible;
- rating/review count;
- response metric if public;
- location;
- View Full Storefront.

Do not show unsupported `Verified years` text.

---

# 48. SUPPLIER DIRECTORY — `/suppliers`

Reference: `suppliers.html`.

## 48.1 Header

```text
Breadcrumb
Supplier Directory
Dynamic count / explanatory text
Search Suppliers input
```

Do not hard-code “1,247 verified suppliers”.

## 48.2 Filter controls

Desktop top/side filter pattern may include:

- Supplier type;
- category served;
- country;
- state/city where useful;
- service area;
- verified-only;
- rating/sort if meaningful.

Use same control styling as Catalog.

## 48.3 Grid

```text
mobile: 1
md: 2
lg: 3
```

Cards should have aligned heights where practical.

## 48.4 Supplier card content

Follow Section 24.

Use real public Supplier profile data only.

---

# 49. SUPPLIER STOREFRONT — `/supplier/{slug}`

Reference: `supplier.html`.

## 49.1 Banner/header

Preserve the strong storefront identity:

```text
h-36 to h-40 banner
Emerald gradient when no Supplier banner image
Supplier logo overlapping lower edge
```

If Supplier has an approved/public banner image, it may replace gradient while maintaining readable overlays.

## 49.2 Profile header content

Show as public/available:

- display name;
- Verified Supplier badge;
- location;
- Supplier type(s);
- founded year;
- rating/review count.

Primary actions:

```text
Request Quote
Contact Supplier
Save Supplier (authenticated behavior)
```

CTA availability follows workflow.

## 49.3 Desktop main layout

```text
Left info rail: 3/12
Main storefront: 9/12
```

Left rail may be sticky when content height permits.

## 49.4 Left profile rail

Sections may include:

- Company Overview;
- founded year;
- employee count;
- public response metrics;
- service areas;
- business hours;
- website/public links;
- high-level verification indicators.

## 49.5 Certification / verification warning

Do **not** automatically expose verified `supplier_documents` or render fake certifications.

If a future public-safe certification feature exists, display it in a dedicated explicit section.

Until then, use only high-level `Verified Supplier` trust state permitted by workflow.

## 49.6 Main storefront tabs

Recommended:

```text
Catalog
About
Reviews
Gallery
```

Within Catalog:

```text
All | Products | Services
```

Optional separate Videos/Exhibitions sections may appear inside Gallery/About according to real data.

## 49.7 Catalog tab

Use reusable listing cards.

Do not duplicate card markup.

## 49.8 About tab

Use readable long-form layout for Supplier description/company information.

Avoid displaying private legal/contact fields.

## 49.9 Reviews tab

Review card:

- safe Buyer/public reviewer identity;
- rating;
- date;
- title/comment;
- published Supplier reply where present.

Only public/published content.

---

# 50. PUBLIC RFQ BOARD — `/opportunities`

Reference: `rfqs.html`.

## 50.1 Header

Use:

- small live/open indicator;
- `Sourcing Opportunities` / `Live Sourcing Opportunities` title;
- short Supplier guidance;
- Login/Register/Become Supplier handoff when relevant.

Do not imply every guest can quote immediately.

## 50.2 Filter bar

Recommended:

- keyword;
- category;
- country/state/city;
- item type;
- deadline;
- sort (`Closing Soonest`, `Newest`).

Mobile filters use drawer or stacked compact controls.

## 50.3 Opportunity list

Prefer structured vertical rows/cards.

Avoid consumer-product card imagery.

Use Section 25 RFQ card system.

## 50.4 Deadline display

Show an absolute date and optionally a relative label:

```text
Aug 23, 2026
Closes in 4 days
```

Avoid only relative time because it is harder to verify.

Expired records should not look open.

---

# 51. PUBLIC RFQ DETAIL — `/opportunities/{rfq_number}`

Reference: `rfq-detail.html`.

## 51.1 Container

Use `max-w-4xl` to keep procurement summary readable.

## 51.2 Main card

White surface, border, rounded-2xl, comfortable padding.

Header:

- RFQ number;
- title;
- Open status badge.

Key data grid:

```text
Category
Delivery Location
Items
Quotation Deadline
```

Use real safe public fields only.

## 51.3 Summary

Use normal readable paragraphs.

Requested categories/items may be displayed as compact pills/summary if public workflow permits.

## 51.4 Private-detail notice

Keep the reference lock-panel concept.

Visual:

```text
soft Slate background
lock icon
short privacy explanation
primary CTA
```

CTA copy depends on visitor state:

```text
Guest
→ Login / Register to Continue

Eligible Supplier
→ View Full Opportunity / Submit Quotation

Ineligible Supplier
→ Complete Supplier Requirements
```

Behavior comes from workflow.

Do not visually reveal masked Buyer contact fields underneath a blur effect. Private fields should not be rendered into public HTML at all.

---

# 52. HOW IT WORKS PAGE — `/how-it-works`

No dedicated static reference; use the homepage design language.

Recommended structure:

```text
Page intro
Buyer / Supplier selector
Buyer process timeline
Supplier process timeline
FAQ teaser
CTA
```

Timeline style:

- numbered circle/icon;
- short heading;
- concise explanation;
- subtle connecting line on desktop;
- vertical stack on mobile.

Do not illustrate an e-commerce checkout flow that does not exist in Phase 1.

---

# 53. SUPPLIER PRICING — `/pricing`

Use a professional SaaS/B2B pricing comparison design.

## 53.1 Header

- title;
- concise Supplier-focused explanation;
- monthly/yearly toggle only if real plan structure supports meaningful comparison.

## 53.2 Plan cards

Desktop:

```text
2–4 cards depending active plan count
```

Plan card:

- plan name;
- billing type;
- price/currency;
- trial if real;
- key limits/features;
- CTA;
- featured/recommended marker only when actual plan data/design says so.

Do not display raw `features` JSON.

## 53.3 Comparison table

For many plan features, add a responsive comparison table below cards.

Mobile may use stacked rows/cards rather than horizontal overflow where possible.

---

# 54. ABOUT PAGE — `/about`

Use a calm marketing layout:

- intro hero;
- marketplace mission;
- Buyer/Supplier value;
- procurement principles;
- optional real marketplace statistics;
- CTA.

Do not fabricate founding story/team metrics.

---

# 55. CONTACT PAGE — `/contact`

Recommended two-column desktop layout:

```text
Contact context / help information: 4/12
Form: 8/12
```

Mobile stacks.

Form uses shared input system.

Do not publish private staff email/phone unless configured as public contact information.

Success state should replace/confirm form clearly without losing context.

---

# 56. FAQ PAGE — `/faqs`

Use searchable/grouped FAQ accordions only if enough questions exist.

Accordion visual:

- white surface;
- bottom/border separation;
- question 15–16px semibold;
- chevron;
- comfortable answer spacing;
- only one or multiple open according to simple Alpine implementation.

Do not use giant cards for every one-line question.

---

# 57. TERMS / PRIVACY

Use readable legal layout:

```text
max-w-4xl
white or canvas background
clear H1
last-updated line when real
sticky TOC only if document is long
comfortable paragraph spacing
```

Legal content typography uses Inter rather than Outfit for dense body copy.

---

# 58. SEARCH RESULTS

If global search resolves into `/catalog?q=...`, use Catalog design.

If a dedicated unified search page is later created, use:

- query heading;
- type tabs;
- grouped result sections;
- same cards used elsewhere;
- filters where meaningful.

Do not invent a visually separate search ecosystem.

---

# 59. AUTH / REGISTRATION HANDOFF VISUAL RULE

Public frontend does not redesign the existing registration UI in this task.

When a guest clicks a protected CTA:

- the public page may show a small explanatory modal/notice if useful;
- otherwise redirect directly to existing login/register;
- preserve intended action according to workflow.

Do not build a second registration form inside a public modal.

---

# 60. INQUIRY / CONTACT MODAL

Where `Contact Supplier` opens an inline modal:

- max width around 520–600px;
- title + Supplier/listing context;
- name/email/phone/organization/subject/message according to workflow;
- Cancel + Send Inquiry;
- clear statement that this is an inquiry, not an official RFQ;
- accessible focus trap/close behavior.

On small screens, modal may become near-full-screen with safe padding.

If implementation uses a dedicated page instead, use same form styling.

---

# 61. SAVE ACTION VISUALS

Save buttons may appear as:

- heart/bookmark icon in listing card;
- `Save Supplier` outline button on storefront.

States:

```text
unsaved
saved
loading
```

Guest click may hand to Login/Register.

Do not hide the button merely because guest is not authenticated if the product strategy uses it as a conversion path.

---

# 62. RESPONSIVE BREAKPOINT EXPECTATIONS

Use Tailwind project breakpoints unless configured otherwise.

Test at minimum:

```text
320px
375px
390px
640px
768px
1024px
1280px
1440px+
```

## Mobile

- no horizontal page overflow;
- one primary content column;
- cards stack;
- filters become drawer;
- header becomes compact;
- tap targets minimum comfortable size;
- tables adapt/scroll only where unavoidable.

## Tablet

- 2-column cards;
- header search remains usable;
- filter/sidebar may still be drawer until `lg`.

## Desktop

- max-w-7xl;
- sidebar grids activate;
- header full nav;
- listing detail 3-column composition.

---

# 63. MOBILE CARD RULES

Do not simply shrink desktop cards.

Mobile card adaptations:

- reduce padding slightly;
- keep title readable;
- hide low-value metadata before truncating primary information;
- CTA buttons may become full-width when needed;
- use 1-column for complex Supplier/RFQ cards;
- use 2-column only for compact category/listing cards when readability remains good.

---

# 64. TABLE RESPONSIVENESS

Tier pricing / pricing comparisons may use horizontal scroll when columns cannot reasonably stack.

Use:

```text
overflow-x-auto
```

with clear visual containment.

Do not allow table overflow to cause whole-page horizontal scroll.

---

# 65. ALPINE.JS DESIGN BOUNDARY

Alpine is for browser-side UI reaction only.

Inline `x-data` is appropriate for:

- category dropdown;
- mobile menu;
- simple tabs;
- FAQ accordion;
- modal open/close;
- local quantity stepper.

Use reusable `Alpine.data()` when behavior is repeated/complex:

- global search autocomplete;
- variant selector;
- advanced filter drawer/state;
- reusable gallery.

Do not place authorization, database eligibility, or procurement status logic in Alpine.

---

# 66. FRONTEND JAVASCRIPT ORGANIZATION

Suggested only when complexity requires it:

```text
resources/js/frontend/
├── global-search.js
├── gallery.js
├── filters.js
├── variants.js
└── modals.js
```

Register through the normal Vite/frontend entry.

Do not create a JavaScript file for every one-line toggle.

---

# 67. FRONTEND CSS ORGANIZATION

Suggested:

```text
resources/css/frontend.css
```

Responsibilities:

- frontend CSS variables;
- font defaults;
- reusable frontend utilities not cleanly expressed in Tailwind;
- motion preferences;
- any globally repeated frontend design behavior.

Do not paste the same `<style>` block from every static HTML reference into every Blade page.

---

# 68. IMAGE / MEDIA DESIGN

Public media should:

- preserve aspect ratio;
- use lazy loading below fold;
- use meaningful alt text;
- show a neutral fallback when missing;
- avoid layout shift with aspect-ratio containers.

Product imagery generally uses object-contain for technical items unless Supplier uploads are designed to crop.

Supplier banners may use object-cover.

Do not show broken image icons.

---

# 69. VERIFIED / PUBLIC TRUST PRESENTATION

Trust must be evidence-based.

Allowed visual trust signals when workflow/data permits:

- `Verified Supplier` badge;
- public rating/review count;
- Supplier types;
- public location;
- response metrics;
- active public listings;
- service areas;
- founded year;
- public gallery/video.

Do not use:

- `Certified` generically;
- ISO badge without explicit data;
- government approval badge without explicit data;
- `Verified 5 years` without real verified-since data.

---

# 70. STATUS DESIGN

Status badges should be compact and text-based.

Examples:

```text
Open
Active
In Stock
Limited
Out of Stock
RFQ Pricing
Quote Only
Remote
Onsite
Hybrid
```

Keep status text visible; color alone is insufficient.

Do not expose internal workflow statuses such as `pending approval` on public resource pages that should instead 404/unavailable according to workflow.

---

# 71. ERROR PAGES

Public 404/500 pages use the same Emerald identity.

404:

- short title;
- helpful explanation;
- Browse Marketplace;
- Go Home;
- optional search.

500:

- neutral error message;
- retry/home action;
- no technical details.

Do not make error pages look like backend errors.

---

# 72. SEO VISUAL SUPPORT

Design must support semantic content structure:

- one clear H1;
- logical H2/H3 hierarchy;
- visible breadcrumbs where useful;
- text descriptions rather than image-only content;
- meaningful link labels;
- image alt text.

Do not hide important SEO text solely in hover interactions.

---

# 73. DARK MODE

Do not implement public frontend dark mode unless explicitly requested later.

The public reference system is a light marketplace identity with a dark footer/contrast panels.

Do not inherit backend appearance mode automatically into the public website.

---

# 74. DESIGN QUALITY RULES

Every frontend page should pass these visual checks:

- one clear primary action per local section;
- consistent container width;
- consistent header/footer;
- consistent typography;
- consistent button system;
- consistent badge semantics;
- no fake data;
- no dead controls;
- no duplicate card markup;
- no uncontrolled horizontal overflow;
- no inaccessible auto-scrolling ticker;
- no unnecessary animation;
- no huge blank areas;
- no crowded information density;
- no dashboard sidebar styling;
- no Filament UI;
- no backend Livewire dependency.

---

# 75. STATIC REFERENCE IMPROVEMENTS REQUIRED IN PRODUCTION

Do not copy these demo limitations unchanged:

1. Replace hard-coded sample categories/listings/Suppliers/RFQs with database-driven data.
2. Replace hard-coded counts with real counts or remove them.
3. Remove newsletter UI unless real workflow is added.
4. Remove dead `#` social links unless configured.
5. Do not show generic certification claims.
6. Do not show `Verified · X yrs` without real data.
7. Do not render private Buyer fields and visually blur them; omit them server-side.
8. Convert repeated HTML header/footer/cards into shared Blade partials/components.
9. Replace CDN Tailwind/Alpine with the project's Vite production build.
10. Add complete mobile drawer interaction; demo hamburger buttons alone are not sufficient.
11. Add keyboard/focus/ARIA behavior to dropdowns/tabs/modals.
12. Split the homepage into section includes.
13. Split featured Products and Services according to workflow.
14. Use real CTA behavior based on guest/Buyer/Supplier state.
15. Add missing workflow-required pages using the same design system.

---

# 76. FINAL FRONTEND VIEW TARGET

Design-related view structure must align with `frontend_workflow.md`:

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
│       └── _marketplace_stats.blade.php
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

This design document does not redefine Controllers/routes; use `frontend_workflow.md` and `ARCHITECTURE.md` for those.

---

# 77. COMPONENT OWNERSHIP RULE

Use this test:

```text
Does this markup define a whole homepage section?
→ `frontend/home/sections/`

Is this UI repeated across multiple pages?
→ `frontend/components/`

Is this global shell UI?
→ `frontend/layouts/partials/`

Is this a complete public page?
→ direct public page folder (`catalog/`, `suppliers/`, `rfqs/`, `pages/`)
```

Example:

```text
_featured_products.blade.php
→ owns homepage section heading/spacing/grid
→ loops through data
→ renders listing-card component

listing-card.blade.php
→ owns reusable listing card visual
→ does not decide which listings are featured
```

---

# 78. DESIGN IMPLEMENTATION ORDER

When implementing or rebuilding a public page:

```text
1. Read frontend_workflow.md for functionality
2. Read this design_frontend.md
3. Open the matching static HTML reference when one exists
4. Inspect current Blade/components/assets
5. Reuse shared frontend shell/components
6. Implement page composition
7. Replace demo values with database data
8. Add responsive states
9. Add accessibility states
10. Verify protected CTA behavior against workflow
11. Run tests
```

Do not start by copying an entire static HTML file into a Blade view.

---

# 79. PAGE-TO-REFERENCE MAP

```text
Homepage
→ home.html

Catalog / Products / Services
→ catalog.html

Listing Detail
→ listing.html

Supplier Directory
→ suppliers.html

Supplier Storefront
→ supplier.html

RFQ Opportunities
→ rfqs.html

RFQ Public Detail
→ rfq-detail.html

Categories / How It Works / Pricing / About / Contact / FAQs / Legal
→ no exact demo file; derive from this design system and nearest relevant reference
```

---

# 80. FINAL DESIGN CONTRACT

The finished EduShopify public frontend must look like one coherent marketplace.

```text
Emerald public brand
        +
Outfit major headings
        +
Inter UI/body
        +
Slate neutral system
        +
clean bordered marketplace cards
        +
strong search/discovery hierarchy
        +
professional Supplier trust presentation
        +
structured public RFQ discovery
        +
responsive mobile-first behavior
        +
reusable Blade composition
```

The final result should preserve the strongest characteristics of the static references while correcting their demo-only limitations.

The static HTML references are **visual starting points**.

`design_frontend.md` is the final visual source of truth.

`frontend_workflow.md` is the functional source of truth.

The current database schema is the data source of truth.

`ARCHITECTURE.md` is the Laravel structural source of truth.

Do not mix these responsibilities.
