# Edushopify — Master Concept Document

> **B2B Education Procurement Marketplace**
> Alibaba × ThomasNet × Education Procurement × RFQ Marketplace
>
> Stack: **Laravel 12 + Filament 3** · MySQL · Reverb · Stripe · Twilio · Intervention Image · Spatie suite
> Version: `concept v1.0` · Status: `Definition / Pre-build`

---

## 1. Vision & Positioning

Edushopify is a **B2B procurement marketplace for the education sector**. It connects institutional **buyers** (schools, universities, governments, NGOs, resellers) with **suppliers** of education products and services (interactive displays, robotics, STEM, furniture, LMS/ERP software, etc.).

The platform is **not** primarily a storefront. The **core business process is the RFQ → Bidding → Award lifecycle**. Supplier profiles, product listings, and the public marketplace are *discovery surfaces* that feed buyers into the RFQ engine and motivate suppliers to subscribe.

| Reference model | What Edushopify borrows from it |
|---|---|
| **Alibaba** | Supplier profiles, product catalog, "Get Quote", verified-supplier trust signals |
| **ThomasNet** | Categorized industrial/education supplier directory, lead generation |
| **Government procurement portals** | Structured RFQ with BOQ, technical specs, deadlines, awarding |
| **RFQ Marketplaces** | Bidding, quote comparison, supplier matching, tiered lead delivery |

**Primary revenue engine:** Supplier subscriptions (tiered) + featured placement. RFQ lead-delivery timing is the key upgrade lever.

---

## 2. Technology Stack

### 2.1 Core
- **Laravel 12** (PHP 8.3+) — application framework
- **Filament 3** — Admin panel + Supplier/Buyer dashboards (Panel Builder, multiple panels)
- **MySQL 8** — primary datastore
- **Laravel Reverb** — WebSocket server for real-time chat & notifications
- **Redis** — queues, cache, Reverb scaling, presence

### 2.2 Key Packages
| Concern | Package |
|---|---|
| Roles & Permissions | `spatie/laravel-permission` |
| Translations (DB columns) | `spatie/laravel-translatable` |
| Media / images | `intervention/image` (+ optional `spatie/laravel-medialibrary`) |
| Slugs | `spatie/laravel-sluggable` |
| SEO | `artesaos/seotools` or custom `seo_meta` table |
| Settings | `spatie/laravel-settings` |
| Activity log | `spatie/laravel-activitylog` |
| Payments | `stripe/stripe-php` (+ `laravel/cashier` for subscriptions) |
| OTP SMS | `twilio/sdk` |
| Social login | `laravel/socialite` (Google + Facebook) |
| Filament translatable forms | `filament/spatie-laravel-translatable-plugin` |
| Money / currency | `akaunting/laravel-money` or `moneyphp/money` |

### 2.3 Filament Panels
Three separate Filament panels, each with its own auth guard/scope:

1. **`/admin`** — Super admin & staff (full control)
2. **`/supplier`** — Supplier dashboard (RFQ center, products, subscription, quotes)
3. **`/buyer`** — Buyer dashboard (RFQs, quotes, comparison, saved items, messaging)

The **public marketplace** (guest + product/supplier browsing) is a standard Blade/Livewire frontend, not a Filament panel.

### 2.4 Styling / Layout
- **Public website (frontend):** **Tailwind CSS via CDN** (`<script src="https://cdn.tailwindcss.com">`) — no build step / no Vite pipeline for prototypes. Theme color & font (from settings, §16) injected as CSS variables and wired into the Tailwind config inline.
- **Dashboards (Admin / Supplier / Buyer):** use **Filament's own CSS/theme** (the default Filament design system + its Tailwind theme). No separate styling — dashboards inherit Filament look-and-feel; theme color can be aligned via the Filament panel `colors()` config.

---

## 3. User Types & Roles

Four primary actors, enforced via Spatie roles.

| Role | Auth | Approval | Subscription |
|---|---|---|---|
| **Guest Visitor** | None | — | — |
| **Buyer** | Yes | Auto-active (optional admin approval) | Free |
| **Supplier** | Yes | Admin approval required | Required to operate |
| **Admin** | Yes | — | — |

### 3.1 Guest Visitor
**Can:** browse suppliers, browse products, search the marketplace, view supplier profiles, view categories, view events/news/blog/resources, submit contact inquiries.
**Cannot:** post RFQs, bid, save suppliers/products, access any dashboard.

### 3.2 Buyer
Institutions seeking procurement. The **organization/buyer type** is a single selection from a managed `buyer_types` table (seeded, translatable, admin-editable):

- Schools
- Universities
- Colleges
- Government organizations
- NGOs
- Training centers
- Consultants
- System integrators
- Resellers
- Distributors

### 3.3 Supplier
The most important revenue user. Goes through application → approval → subscription before full marketplace access.

**Supplier Types (multi-select, max 5):** Suppliers classify themselves by **supplier type** — the kind of business they are. A supplier may pick **up to 5** from a managed `supplier_types` table (seeded, translatable, admin-editable):

- Manufacturer
- Distributor
- Wholesaler
- Reseller
- Importer
- Exporter
- System Integrator
- Service Provider
- Consultant
- Publisher
- Training Provider
- OEM

**Exhibitions (separate, optional):** A distinct, **optional** field captures which education trade shows the supplier exhibits at — e.g. **BETT, GESS, ISE**, etc. This is stored separately from supplier types (its own managed `exhibitions` table + pivot) and is **not required**.

> Supplier types stored via a `supplier_supplier_type` pivot with a **max-5 validation rule**. Exhibitions stored via a separate `exhibition_supplier` pivot (optional, no cap unless desired). For now these **replace** category/subcategory + product/service selection during onboarding (those are deferred — see §4.2).

### 3.4 Admin
Controls the entire ecosystem: users, supplier approval, subscriptions, RFQ moderation, product moderation, content, settings.

---

## 4. Registration & Onboarding

### 4.1 Buyer Registration
| Step | Fields |
|---|---|
| **1. Basic** | user_type=Buyer, name, email, phone, password |
| **2. Verify** | email verification (link) + optional phone OTP |
| **3. Complete profile** | photo, organization name, **organization type** (select from `buyer_types`), position, country, city, address, website, bio, *(optional)* LinkedIn, organization logo, tax ID, procurement department info |

**Result status:** `Active` (no admin approval required unless toggled in settings).

### 4.2 Supplier Registration
| Step | Section | Fields |
|---|---|---|
| **1. Basic** | Registration | user_type=Supplier, name, email, phone, password |
| **2. Verify** | Email + (optional) OTP | — |
| **3. Application** | Company Info | company name, company type, country, city, address, website, founded year, employees |
| | Branding | logo, banner, profile photo, gallery, videos (YouTube) |
| | **Supplier Types** | **select up to 5** from `supplier_types` (Manufacturer, Distributor, Reseller, OEM, etc.) |
| | **Exhibitions** *(optional)* | exhibitions the supplier attends — BETT / GESS / ISE, etc. (separate field, not required) |
| | Contact | contact person, phone, email, WhatsApp, support email |
| | Social | LinkedIn, Facebook, Instagram, YouTube |
| | Verification / Certified Docs | submitted **during the application step (after email verification)** — trade license, company registration, tax certificate, VAT certificate, ISO & other **certified documents**, authorization letters |
| | Business Hours | per-day open/close (see §12) |

> **Note:** category/subcategory selection is **not** part of supplier onboarding — supplier classification is handled by **Supplier Types (max 5)**. However, after approval **suppliers can add Products and Services** from their dashboard (each **product requires admin approval** before going live — see §7.3). The category tree supports product classification.

**Submit → Status = `Pending Approval`.**

### 4.3 Admin Review
The admin reviews the application + certified documents and takes one of these actions:

1. **Approve + assign Free plan** → supplier gets the Free plan (30-day limited trial) and can enter the dashboard.
2. **Approve with NO plan** → supplier is approved but has no active plan (redirected to pricing — §4.4).
3. **Reject (with reason)** → application rejected; a **specific reason** is recorded and sent to the supplier.
4. **Request Revision (with reason)** → status returns to a revisable state so the supplier can **update the application again**, guided by the **specific reason/notes** the admin provides (e.g. "trade license expired, re-upload"). The supplier edits and re-submits → back to `Pending Approval`.

Stored on the supplier record: `review_status`, `review_reason` (the admin's specific message), `reviewed_by`, `reviewed_at`. The supplier sees the reason in their dashboard and via notification/email.

### 4.4 Approved Supplier Gate
```
Supplier Status = Approved
        ↓
 ┌─────────────────────────────┬──────────────────────────────┐
 │  Admin assigned Free plan    │  Admin assigned NO plan       │
 │            ↓                 │            ↓                  │
 │  Dashboard access (limited,  │  On EVERY login → ALWAYS      │
 │  30-day Free trial)          │  redirected to /pricing       │
 └─────────────────────────────┴──────────────────────────────┘
```

**Rule:** if a supplier has **no active plan**, after login they are **always redirected to the pricing page** and cannot use the dashboard until they pick (and pay for, where applicable) a plan. Enforced by middleware (`EnsureSupplierHasPlan`) on all supplier-panel routes except `/pricing` and checkout.

---

## 5. Subscription System

Suppliers operate only while holding an **active plan**. There are **four plans**: **Free · Basic · Professional · Featured**.

### 5.0 Plan periods
- **Free** plan = a **time-limited trial** with a configurable duration — **default 30 days** (`trial_days`). When the Free trial expires, the supplier loses dashboard access and is redirected to `/pricing` (same rule as "no plan").
- **Basic / Professional / Featured** are **paid** and billed per period:

| Period | Duration |
|---|---|
| Monthly | 30 days |
| Quarterly | 90 days |
| Yearly | 365 days |

### 5.1 Plans

| Feature | **Free** | **Basic** | **Professional** | **Featured** |
|---|---|---|---|---|
| Duration | 30-day trial | paid period | paid period | paid period |
| Profile | ✅ | ✅ | ✅ | ✅ |
| Products *(future)* | very limited | 10 | Unlimited | Unlimited |
| RFQ access | Limited | Limited | Unlimited | Unlimited |
| RFQ notifications | — | — | ✅ | ✅ |
| Analytics | — | — | ✅ | ✅ |
| Priority placement | — | — | — | ✅ |
| Top search ranking | — | — | — | ✅ |
| Featured badge | — | — | — | ✅ |
| Homepage exposure | — | — | — | ✅ |

### 5.2 RFQ Delivery Logic (the upgrade lever)

| Plan | RFQ delivered after |
|---|---|
| **Free** | **24 hours** |
| **Basic** | **24 hours** |
| **Professional** | **2 hours** |
| **Featured** | **Immediately** |

This delay differential is the primary psychological driver for upgrades — featured suppliers reach buyers first while leads are warm. (Delivery delay is stored per plan as `rfq_delivery_minutes`: 1440 / 1440 / 120 / 0.)

### 5.3 Billing
- **Stripe** via Laravel Cashier for the paid plans (Basic/Pro/Featured) across monthly/quarterly/yearly.
- The **Free** plan requires no payment — it's assigned by admin on approval or self-selected on the pricing page, and simply sets a 30-day expiry.
- Invoices, renewals, proration, dunning handled by Cashier + webhooks.
- Admin can manually grant/extend/cancel any plan (including assigning Free or revoking to "no plan").

---

## 6. The RFQ Engine (Core Process)

### 6.1 RFQ Fields
title, description, category, subcategory, quantity, budget, currency, country, delivery location, visibility (public / invited), attachments (**PDF, BOQ, technical specs, images**), plus the **timeline deadlines** below (§6.1a).

**Example RFQ:**
> Need: 50 Interactive Displays · Budget: $100,000 · Country: UAE · Category: Smart Classroom · Deadline: 30 days

### 6.1a RFQ Timeline / Phases
Each RFQ runs through a defined timeline with its own dates, so buyers and suppliers always know the current stage:

| # | Phase | Meaning |
|---|---|---|
| 1 | **Posted** | RFQ published; matching + tier-delayed delivery begins (`posted_at`) |
| 2 | **Questionnaire Deadline** | Last date suppliers may ask clarification questions / Q&A (`questionnaire_deadline`) |
| 3 | **Quotation Deadline** | Last date suppliers may submit/edit quotations (`quotation_deadline`) |
| 4 | **Buyer Evaluation** | Buyer reviews, shortlists, compares quotes (`evaluation_start` → `evaluation_end`) |
| 5 | **Award** | Buyer selects winner (`awarded_at`) |
| 6 | **Negotiation** | Final terms negotiated with the awarded supplier before project starts (`negotiation_start`) |

Status reflects the active phase: `draft → posted → questionnaire → bidding → evaluation → awarded → negotiation → in_progress → delivered → completed` (plus `closed`/`cancelled`). The system auto-advances phases on deadline (scheduled job) and blocks actions outside the valid window (e.g. no quotes after `quotation_deadline`).

### 6.2 Buyer RFQ Actions
Create · Edit · Publish · Close · Cancel · Award · Duplicate · **set/extend timeline deadlines** · **shortlist suppliers**.

### 6.3 Quotations (Supplier → Buyer)
Supplier submits: price, lead time, proposal, warranty, support terms, attachments — only within the open **bidding window** (before `quotation_deadline`).

### 6.4 Shortlist & Quote Comparison (Buyer)
- **Shortlist:** during the **Buyer Evaluation** phase the buyer can flag promising quotes/suppliers into a shortlist (`rfq_shortlists`), then focus comparison and messaging on those.
- **Compare:** side-by-side comparison table across price, delivery/lead time, warranty, support, **supplier rating**, **response rate**, **response time**. Buyer can compare the full list or just the shortlist.

### 6.5 Award
Buyer selects winner → RFQ status `Awarded`, winning quote marked `Winner`, losing quotes marked `Lost`, then **Negotiation** before the awarded RFQ becomes a **Project** that progresses: `Awarded` → `Negotiation` → `In Progress` → `Delivered`/`Completed`.

### 6.6 Supplier Rating & Reviews
After a project is **delivered** (buyer received the product), the **buyer can rate the awarded supplier**.

- **Eligibility:** only the buyer of an **awarded + delivered** RFQ may rate, and only the **winning supplier** of that RFQ. One review per awarded project (editable within a window, e.g. 30 days).
- **Rating:** 1–5 stars, with an optional written review and optional sub-scores (product quality, delivery, communication, support).
- **Effect:** updates the supplier's aggregate `rating` and review count; shown on the supplier profile, in the directory, and in the **quote comparison** table (§6.4).
- **Moderation:** admin can hide/flag abusive reviews; supplier may post one public reply per review.

### 6.7 End-to-End Workflow
```
Buyer Creates RFQ
      ↓
System Finds Matching Suppliers
      ↓
Featured Suppliers      → notified IMMEDIATELY
Professional Suppliers  → notified after 2 HOURS
Free / Basic Suppliers  → notified after 24 HOURS
      ↓
Suppliers Submit Bids (Quotations)
      ↓
Buyer Receives Quotations
      ↓
Buyer Compares & Shortlists
      ↓
Messaging Begins (Reverb chat)
      ↓
Buyer Awards Project        → Supplier Marked As Winner
      ↓
Negotiation (final terms)
      ↓
Project Delivered / Completed
      ↓
Buyer Rates Supplier (1–5 ★ + review)
      ↓
Supplier Aggregate Rating Updated
```

**Implementation note:** matching + delayed delivery handled by a queued job (`DispatchRfqToSuppliers`) that schedules tier-based `notify` jobs with `delay()` (immediate / +2h / +24h). Each fans out notifications + chat eligibility.

### 6.8 Supplier Response Metrics
Two trust metrics are computed per supplier and shown on the profile, directory, and quote comparison:

- **Response Rate** = quotations submitted ÷ RFQs delivered to the supplier (within the bidding window), as a percentage. "Responded" means a quote was submitted (not merely viewed).
- **Response Time** = average elapsed time from **RFQ delivered to supplier** (`rfq_supplier_matches.notified_at`) → **first quotation submitted** (`quotations.created_at`), expressed in hours.

Both are recalculated by a scheduled/queued listener whenever a quote is submitted or an RFQ delivery window closes, and cached on `supplier_profiles` (`response_rate`, `response_time`). They feed §6.4 comparison and supplier ranking.

---

## 7. Marketplace (Discovery)

> **Discovery surfaces:** the primary discovery is the **Supplier directory + Supplier Types**. In addition, **suppliers can list Products and Services** (each product needs admin approval), browsable via the category tree below.

### 7.0 Supplier Directory
Guests/buyers browse and filter the **supplier directory** by: supplier type (multi), exhibition (optional), country, city, keyword. Each supplier has a public slug profile (§7.4).

### 7.1 Category Tree (example)
```
Technology
├── Interactive Displays
├── Robotics
└── STEM Kits
Furniture
├── Classroom Furniture
└── Laboratory Furniture
Software
├── LMS
└── ERP
```
Categories are **nested** (self-referencing `parent_id`), each with slug, icon, translated name.

### 7.2 Search & Filters
category, subcategory, country, brand, supplier, price range, featured supplier. Implemented with Laravel Scout (optional Meilisearch) or query filters.

### 7.3 Products (supplier-listed, admin-approved)
**Fields:** title, category, brand, model, description, images (Intervention), brochure (PDF), video (YouTube embed), specifications.
**Supplier product actions:** Add · Edit · Delete · Duplicate.

**Approval workflow — every product requires admin approval before it goes public:**
```
Supplier adds/edits product → status = Pending Approval
        ↓
Admin reviews
   ├─ Approve  → status = Approved (live, publicly visible)
   ├─ Reject   → status = Rejected (with specific reason, supplier notified)
   └─ Hide     → status = Hidden (taken down post-approval)
```
Editing an approved product can optionally re-trigger approval (configurable). Product fields: `status[pending|approved|rejected|hidden]`, `review_reason`, `reviewed_by`, `reviewed_at`.
**Public page actions:** Get Quote · Contact Supplier · Save Product · Share · *(future)* Buy Online. Only **Approved** products are visible publicly.

### 7.3a Services (supplier-listed)
Suppliers can also list **Services** (e.g. consulting, installation, training, maintenance).
**Fields:** title, category/type, description, images, optional pricing note, optional YouTube video. Same **admin approval** flow as products (`status`, `review_reason`).

### 7.4 Supplier Profile (public page, slug URL)
Company overview (logo, banner, description, **supplier types**, **exhibitions** if any, business hours, **aggregate rating + review count**, **response rate & time**), **approved products & services**, gallery & YouTube videos, documents (catalogs, certificates, brochures), **buyer reviews** (with supplier replies), contact + social links. Actions: Get Quote · Contact Supplier · Visit Website.

---

## 8. Dashboards

### 8.1 Buyer Dashboard
Overview · Active RFQs (with timeline/phase) · Draft RFQs · Received Quotes · **Shortlist & Compare Quotes** · Awarded Projects (**rate delivered suppliers**) · Saved Suppliers · Saved Products · Messaging.

### 8.2 Supplier Dashboard
Overview (New RFQs, Matched RFQs, Submitted Quotes, Awarded Projects, Revenue Opportunities) · RFQ Center (Available / Matched / My Bids / Won / Lost) · Submit Quotation · **My Reviews (view + reply)** · **Products & Services management (with approval status)** · Subscription · Analytics (Pro+).

### 8.3 Admin Dashboard
**KPIs:** Total Suppliers, Total Buyers, Total RFQs, Total Quotations, Revenue, Active Subscriptions, **Avg. Supplier Rating**.
**Modules:** User Management (Buyers/Suppliers/Admins), Supplier Approval (Approve/Reject/**Request Revision — all with reason**/Suspend), Subscription Management (Plans/Payments/Renewals/Invoices), RFQ Management (View/Moderate/Close/Flag), **Reviews Moderation (hide/flag)**, **Product & Service Approval (Approve/Reject with reason/Hide)**, Content Management (Resources/News/Events/Blog/Guides), Settings.

---

## 9. Internationalization (i18n) — Multilanguage, RTL/LTR, Translations

### 9.1 Languages
Configurable set, e.g. **English (LTR)**, **Arabic (RTL)**, **Uzbek (LTR)** — extendable. A `languages` table drives the switcher; each row has `code`, `name`, `native_name`, `direction (ltr|rtl)`, `flag`, `is_active`, `is_default`.

### 9.2 Static strings
Standard Laravel translation files (`lang/en/*.php`, `lang/ar/*.php`, …) for UI labels, buttons, emails.

### 9.3 Translatable DB columns
Use **`spatie/laravel-translatable`** — translatable fields stored as JSON, e.g.:

```php
// Category model
public $translatable = ['name', 'description', 'meta_title', 'meta_description'];
// stored as: {"en":"Robotics","ar":"الروبوتات","uz":"Robototexnika"}
```

**Translatable columns by model (necessary ones):**
| Model | Translatable columns |
|---|---|
| Buyer Type | name |
| Supplier Type | name |
| Exhibition | name |
| Category / Subcategory | name, description, meta_title, meta_description |
| Product | title, description, specifications, meta_title, meta_description |
| Service | title, description, meta_title, meta_description |
| Supplier profile | description/bio, meta_* |
| RFQ | title, description (buyer's own; optional) |
| Blog / News / Event | title, excerpt, body, meta_* |
| Pages / Resources | title, body |
| Plan | name, features |

### 9.4 Per-language form tabs
Every Filament form for translatable models uses the **Spatie Translatable plugin** so each form renders a **language tab** (EN / AR / UZ …). Editors fill content per language in a single screen. Frontend public forms mirror this with tabbed inputs.

### 9.5 RTL/LTR rendering
- Direction comes from the active language (`direction` column).
- Layout sets `<html dir="{{ $dir }}" lang="{{ $locale }}">`.
- Tailwind logical properties / RTL plugin or duplicated RTL stylesheet.
- Filament panels switch direction per locale.

### 9.6 Locale detection
Middleware resolves locale by: user preference → session → `Accept-Language` → default. Switcher persists choice to session + user record.

---

## 10. Geography & Currency

### 10.1 Country / State / City
Three normalized tables, seeded from a standard dataset (e.g. countrystatecity):

```
countries (id, name, iso2, iso3, phone_code, currency_code, flag)
states    (id, country_id, name, code)
cities    (id, state_id, country_id, name)
```
Used in: registration, RFQ delivery location, supplier address, buyer org address, filters.

### 10.2 Currency table + switcher
```
currencies (id, code, name, symbol, exchange_rate, is_default, is_active, decimal_places)
```
- A **currency switcher** in the header converts displayed prices using `exchange_rate` (base = default currency).
- Stored monetary values keep their original currency; display converts on the fly.
- `akaunting/laravel-money` formats per locale (symbol placement, separators).
- RFQ budgets and quote prices store both amount + currency_code.

---

## 11. Media: Images & Video

### 11.1 Images — Intervention Image
- All user-uploaded images (avatars, logos, banners, product images, gallery) processed via **`intervention/image`**.
- On upload: validate → resize/crop variants (thumb, medium, full) → optimize → store on disk (`public`/`s3`).
- Optionally wrap with `spatie/laravel-medialibrary` for conversions & collections.

### 11.2 Video — YouTube embed
- No video hosting. Suppliers/products store a **YouTube URL/ID**.
- Stored as `youtube_id`; rendered via responsive `<iframe>` embed.
- Validate URL → extract ID → store ID only.

---

## 12. Supplier Business Hours
```
business_hours (id, supplier_id, day_of_week 0-6, is_open, open_time, close_time)
```
- Per-day open/close, multiple rows per supplier (or a single JSON column).
- "Open now / Closed" computed against supplier timezone.
- Shown on supplier profile + product page.

---

## 13. Communication & Real-Time (Laravel Reverb)

### 13.1 Chat / Messaging
- **Buyer ↔ Supplier** chat scoped to a context: RFQ, Product, or Project.
- Powered by **Laravel Reverb** (WebSockets) + Echo on the frontend.
- Tables: `conversations` (participants, context type/id), `messages` (sender, body, attachments, read_at).
- Presence channels for online status & typing indicators.
- Unread badges in both dashboards.

### 13.2 Notifications
- Laravel Notifications: **database + broadcast (Reverb) + mail** channels.
- Triggers: new matching RFQ (tier-timed), new quote received, quote awarded, message received, subscription expiring, supplier approved/rejected.
- In-app notification bell (real-time via Reverb), with read/unread state.

---

## 14. Authentication & Social Login
- Email/password with email verification.
- **Phone OTP** via Twilio (toggleable — see §16).
- **Social login** via `laravel/socialite`: **Google** and **Facebook**.
- Account linking: social accounts attach to existing email where matched.

---

## 15. OTP via Twilio
- **Twilio SDK** sends SMS OTP for phone verification & optional login/2FA.
- Flow: generate 6-digit code → store hashed with expiry → SMS via Twilio → verify → mark phone verified.
- Rate-limited; resend cooldown.
- **Global toggle** in settings: when OTP is disabled, phone verification is skipped.

---

## 16. Settings (Admin-managed)
Backed by `spatie/laravel-settings`. Editable in Admin panel:

| Group | Settings |
|---|---|
| **OTP** | enable/disable OTP, provider keys, code length, expiry |
| **Contact** | address, phone, email, support email, WhatsApp, map coords |
| **Theme** | primary/theme color, secondary color, **theme font**, logo, favicon |
| **General** | site name, default language, default currency, timezone |
| **Social** | LinkedIn, Facebook, Instagram, YouTube links |
| **Approval** | require buyer approval (on/off), require supplier approval (on/off) |
| **SEO** | default meta title/description, OG image, analytics IDs |
| **Payments** | Stripe keys, plan pricing |

Theme color/font are injected as CSS variables so the frontend re-skins without code changes.

---

## 17. Support / Helpdesk System
- **Ticket system**: users (buyer/supplier) open tickets → admin/staff respond.
- Tables: `tickets` (subject, category, priority, status, user_id), `ticket_replies` (ticket_id, sender, body, attachments).
- Statuses: Open · In Progress · Awaiting Reply · Resolved · Closed.
- Managed in Admin panel; users see their tickets in their dashboard.

---

## 18. SEO
- **Slugged URLs** everywhere relevant (see §19) via `spatie/laravel-sluggable`.
- Per-record **meta** (title, description, OG image, canonical) — translatable.
- `seo_meta` table or `seotools` integration.
- Auto **sitemap.xml**, `robots.txt`, JSON-LD structured data (Organization, Product, BreadcrumbList).
- Clean human-readable URLs; 301 redirects on slug change.

---

## 19. Slug-based URLs
Slugs used where SEO/shareability matters:
```
/suppliers/{supplier-slug}
/products/{product-slug}
/categories/{category-slug}
/blog/{post-slug}
/news/{news-slug}
/events/{event-slug}
/resources/{resource-slug}
/rfqs/{rfq-slug}        (where public)
```
Translatable slugs optional per locale (e.g. `/ar/products/...`).

---

## 20. Content: Events · News · Blog · Resources
- **Blog**, **News**, **Events**, **Resources/Guides** modules — all translatable, slugged, SEO-ready, with featured image (Intervention) + optional YouTube video.
- Events add: start/end date, location, registration link.
- Managed in Admin content module; surfaced to guests on the public site.

---

## 21. Database Schema (high-level)

```
# Identity & access
users (id, name, email, phone, password, type[buyer|supplier|admin],
       avatar, status, locale, currency_code, email_verified_at, phone_verified_at)
roles / permissions / model_has_roles            (Spatie)
social_accounts (id, user_id, provider, provider_id)
otp_codes (id, user_id, code_hash, expires_at, purpose)

# Geography & currency
countries / states / cities
currencies
languages

# Taxonomy (managed, translatable)
buyer_types (id, name[json], slug, is_active, sort)
supplier_types (id, name[json], slug, is_active, sort)   # Manufacturer/Distributor/OEM etc.
exhibitions (id, name[json], slug, logo, is_active, sort) # BETT/GESS/ISE etc. (optional)

# Buyer
buyer_profiles (user_id, organization_name, buyer_type_id, position,
                country_id, city_id, address, website, bio, linkedin,
                org_logo, tax_id, procurement_info)

# Supplier
supplier_profiles (user_id, slug, company_name, company_type, country_id,
                   city_id, address, website, founded_year, employees,
                   logo, banner, profile_photo, description[json/translatable],
                   contact_person, contact_phone, contact_email, whatsapp,
                   support_email, socials[json], status, plan_status,
                   review_status[pending|approved|rejected|revision],
                   review_reason, reviewed_by, reviewed_at,
                   rating, reviews_count, response_rate, response_time)
supplier_supplier_type (supplier_id, supplier_type_id)   # pivot, MAX 5 enforced
exhibition_supplier (supplier_id, exhibition_id)         # pivot, optional
supplier_documents (supplier_id, type, file_path, verified)
supplier_gallery (supplier_id, image_path)
supplier_videos (supplier_id, youtube_id, title)
business_hours (supplier_id, day_of_week, is_open, open_time, close_time)

# Catalog (supplier-listed, admin-approved)
categories (id, parent_id, slug, name[json], description[json], icon, meta[json])
products (id, supplier_id, category_id, slug, title[json],
          brand, model, description[json], specifications[json],
          brochure_path, youtube_id, meta[json],
          status[pending|approved|rejected|hidden], review_reason,
          reviewed_by, reviewed_at)
product_images (product_id, path, variant)
services (id, supplier_id, category_id, slug, title[json], description[json],
          price_note, youtube_id, meta[json],
          status[pending|approved|rejected|hidden], review_reason,
          reviewed_by, reviewed_at)
service_images (service_id, path, variant)

# RFQ engine
rfqs (id, buyer_id, slug, title[json], description[json], category_id,
      subcategory_id, quantity, budget, currency_code, country_id,
      delivery_location, visibility,
      posted_at, questionnaire_deadline, quotation_deadline,
      evaluation_start, evaluation_end, awarded_at, negotiation_start,
      delivered_at,
      status[draft|posted|questionnaire|bidding|evaluation|awarded|
             negotiation|in_progress|delivered|completed|closed|cancelled],
      awarded_supplier_id)
rfq_attachments (rfq_id, type[pdf|boq|spec|image], path)
rfq_questions (rfq_id, supplier_id, question, answer, answered_at)   # Q&A phase
rfq_supplier_matches (rfq_id, supplier_id, deliver_at, notified_at)  # tier timing
rfq_shortlists (rfq_id, supplier_id, quotation_id, note)             # buyer shortlist
quotations (id, rfq_id, supplier_id, price, currency_code, lead_time,
            proposal, warranty, support_terms,
            status[submitted|shortlisted|winner|lost], created_at)
quotation_attachments (quotation_id, path)

# Ratings & reviews
supplier_reviews (id, supplier_id, buyer_id, rfq_id, rating[1-5],
                  review, score_quality, score_delivery, score_communication,
                  score_support, supplier_reply, status[published|hidden|flagged],
                  created_at)
  # UNIQUE(rfq_id) — one review per awarded project; eligible only when
  # rfq.status = delivered/completed AND buyer owns the rfq AND supplier won it

# Engagement
saved_suppliers (buyer_id, supplier_id)
saved_products (buyer_id, product_id)
conversations (id, context_type, context_id)
conversation_participants (conversation_id, user_id)
messages (id, conversation_id, sender_id, body, attachments, read_at)
notifications                                   (Laravel default)

# Subscriptions & billing
plans (id, name[json], slug, is_free, trial_days, products_limit,
       rfq_delivery_minutes, features[json], sort, is_active)
plan_prices (plan_id, period[monthly|quarterly|yearly], amount, currency_code)
subscriptions / subscription_items              (Cashier)
invoices (handled via Stripe + Cashier)

# Content & support
blog_posts / news / events / resources (slug, title[json], body[json], meta[json], image, youtube_id)
tickets / ticket_replies
settings                                        (Spatie settings)
seo_meta (model_type, model_id, title[json], description[json], og_image, canonical)
activity_log                                    (Spatie)
```

---

## 22. Filament Resource Map

| Panel | Resources / Pages |
|---|---|
| **Admin** | Users, Suppliers (approval workflow: Approve / Reject / **Request Revision — with reason** / assign Free / no plan), Buyers, **Buyer Types**, **Supplier Types**, **Exhibitions**, Categories (tree), **Products (approval)**, **Services (approval)**, RFQs (timeline phases), Quotations, **Supplier Reviews (moderation)**, Plans & Prices (Free/Basic/Pro/Featured), Subscriptions, Invoices, Languages, Currencies, Countries/States/Cities, Blog, News, Events, Resources, Tickets, Settings, SEO, Activity Log, Dashboard KPIs |
| **Supplier** | Profile (translatable tabs) + **Supplier Types (max 5)** + **Exhibitions (optional)**, Gallery/Videos, Business Hours, Certified Documents, **Products (with approval status)**, **Services (with approval status)**, RFQ Center, Quotations, **My Reviews (reply)**, **Response Rate/Time stats**, Subscription/Pricing, Analytics, Messages, Tickets |
| **Buyer** | Profile (+ **Buyer Type** select), RFQs CRUD (**timeline deadlines**), Received Quotes, **Shortlist & Compare**, Awarded Projects (**rate supplier**), Saved Suppliers, Saved Products, Messages, Tickets |

All translatable resources use the Spatie Translatable plugin → **per-language tabs** in every form.

---

## 23. V1 MVP Scope

Launch lean. Defer analytics depth, online purchasing, advanced matching.

**Buyer:** Registration · Profile (Buyer Type) · RFQ Creation (**timeline deadlines**) · **Shortlist & Compare Quotes** · Messaging · **Rate awarded suppliers**
**Supplier:** Registration (+ **certified documents**) · Approval Workflow (Approve / Reject / Request Revision **with reason**; admin assigns Free / no plan) · Plan selection (Free/Basic/Pro/Featured) · Profile (Supplier Types max 5) · **Products & Services (admin-approved)** · RFQ Bidding · **Response rate/time**
**Marketplace:** Supplier Directory · Supplier Types filter · Supplier Profiles · **Approved Products & Services** · Search
**Admin:** Supplier Approval (with reasons) + plan assignment · User Management · Buyer/Supplier Types · **Product/Service Approval** · RFQ Management (timeline) · Subscription Management

**Cross-cutting in MVP:** i18n (EN/AR/UZ) + RTL/LTR, currency switcher, Stripe subscriptions, Intervention images, YouTube embeds, Reverb chat + notifications, Twilio OTP (toggle), Google/Facebook login, settings, SEO + slugs, business hours, support tickets, blog/news/events.

### Phase 2+
Advanced supplier analytics, Buy-Online (true e-commerce), AI RFQ-to-supplier matching, supplier ratings/reviews engine, mobile app, invoicing/escrow, multi-currency settlement.

---

## 24. Build Order (suggested)
1. Foundations: auth, roles, languages, countries/states/cities, currencies, settings, theme, **public layout (Tailwind CDN) + Filament panels**.
2. Taxonomy: **buyer_types, supplier_types & exhibitions** (managed, translatable, seeded).
3. Profiles: buyer + supplier registration (+ **certified documents**), supplier approval (Approve / Reject / Request Revision **with reason**; assign Free / no plan), `EnsureSupplierHasPlan` middleware + pricing redirect, translatable forms, media, business hours.
4. Supplier directory: browse/filter by supplier type, public slug profiles, SEO.
5. Catalog: categories tree, **supplier Products & Services with admin approval**, product/service discovery.
6. RFQ engine: RFQ CRUD with **timeline phases & deadlines** (questionnaire/quotation/evaluation/award/negotiation), matching job, tier-delayed delivery, quotations, **shortlist & compare**, award, **response rate/time**, project delivery states, **buyer→supplier ratings & reviews**.
7. Subscriptions: plans (Free 30-day trial, Basic/Pro/Featured), Stripe/Cashier, gating, RFQ delivery timing tied to plan.
8. Real-time: Reverb chat + notifications.
9. Support, content (blog/news/events), Twilio OTP, social login.
10. Polish: SEO, sitemap, structured data, hardening.

---

*End of Master Concept Document — Edushopify v1.0*
