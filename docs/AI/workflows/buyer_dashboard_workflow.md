# EduShopify Buyer Dashboard Workflow

> **Purpose:** Complete Buyer Dashboard functional specification and implementation workflow for the existing EduShopify Laravel project.
>
> This file must be used together with `ARCHITECTURE.md` and `design.md`.
>
> Backend Buyer functionality must **NOT use Livewire**. Legacy backend Livewire code, if any exists, must not be extended. Refactor/migrate only when the relevant feature is being worked on and only when it can be done safely without breaking working behavior.

You are acting as a Senior Laravel Architect,
Senior Laravel Backend Developer,
Senior Product Architect,
Database Architect,
Security Engineer,
Business Analyst,
and B2B Marketplace Engineer.

You are continuing development of an EXISTING project:

EDUSHOPIFY

Edushopify is a B2B education procurement marketplace.

Your task is to design and implement the COMPLETE BUYER DASHBOARD and all
Buyer-side functionality required by the existing Edushopify database,
business rules, `ARCHITECTURE.md`, and `design.md`.

==========================================================
MANDATORY PROJECT DOCUMENTS / SOURCES OF TRUTH
==========================================================

Before modifying or implementing any Buyer functionality:

1. Read `ARCHITECTURE.md` completely.
2. Read `design.md` completely.
3. Inspect the existing project code related to the requested module.
4. Inspect the latest database schema / SQL dump.
5. Inspect existing automated tests for the module.
6. Inspect existing routes, Controllers, Form Requests, Actions, Services,
   Policies, Models, Blade views/components, middleware, events/jobs, and
   JavaScript used by that feature.

Use these sources according to their responsibility:

- Current database schema / SQL dump
  = database structure source of truth.

- `ARCHITECTURE.md`
  = project folder structure, code organization, portal/domain separation,
    backend request flow, Controller/Request/Action/Service/Model/Blade rules,
    authorization architecture, and backend NO-Livewire rule.

- `design.md`
  = Buyer dashboard visual/UI standard, including layout, sidebar, topbar,
    tables, forms, typography, colors, responsive behavior, accessibility,
    permission-aware UI, and themeable sidebar/menu/submenu colors.

- `buyer_dashboard_workflow.md`
  = Buyer-side functional requirements, Buyer business workflow, Phase 1
    behavior, status rules, and implementation phases.

- Existing project code
  = inspect, preserve, reuse, and extend where it is still valid. Existing
    legacy implementation must not override the approved architecture,
    database rules, or Phase 1 business rules.

If documents appear to conflict:
- Do not guess.
- Prefer the current SQL schema for what fields/tables/statuses physically exist.
- Prefer explicit Phase 1 business rules in this workflow for which supported
  schema capabilities are active now.
- Follow `ARCHITECTURE.md` for where code belongs.
- Follow `design.md` for how dashboard UI is implemented.
- Preserve working behavior while refactoring incrementally.

==========================================================
IMPORTANT PROJECT RULES
==========================================================

This is NOT a greenfield project.

The project already has or may contain:

- Laravel
- Blade
- Tailwind CSS
- Current SQL schema generated from MySQL 8.4.x
- MariaDB-compatible schema conventions in the V4.3 design reference
- Spatie Laravel Permission with Teams
- Buyer capability architecture
- Service classes
- Laravel Policies
- Existing Buyer routes/components
- Existing Buyer master dashboard layout
- Existing header
- Existing sidebar
- Existing footer
- Existing Tailwind design system
- Existing automated tests
- Legacy Livewire code may exist from earlier backend development

DATABASE ENGINE SAFETY:

- The current SQL dump is the implementation source of truth.
- Do not introduce MySQL-only, MariaDB-only, or SQLite-incompatible behavior
  without checking the actual runtime environment and test suite.
- Preserve existing SQLite test compatibility where the project uses SQLite
  for automated tests.

BACKEND TECHNOLOGY RULE:

Do NOT use Livewire for Buyer backend development.

Backend request flow should follow the approved architecture:

Laravel Route
    ↓
Middleware
    ↓
Form Request
    ↓
Controller
    ↓
Action / Service when business complexity requires it
    ↓
Model / Database
    ↓
Blade View / Redirect

Simple CRUD does NOT require an Action or Service merely for architectural
ceremony. Use them only where they add real value.

If legacy backend Livewire components exist:
- inspect them only to understand current behavior;
- do NOT create new backend Livewire components;
- do NOT extend the backend Livewire architecture;
- preserve working behavior;
- migrate a touched feature carefully to the approved Laravel Controller +
  Form Request + Action/Service when needed + Model + Blade architecture.

DO NOT use Filament for the Buyer Dashboard.

DO NOT create a new dashboard shell.

Use the existing Buyer master layout and align it with `design.md`:

- existing header
- existing sidebar
- existing footer
- existing Blade components
- existing Tailwind classes where compatible
- typography defined in `design.md`
- spacing defined in `design.md`
- colors/design tokens defined in `design.md`
- existing text patterns where they remain consistent

For dashboard design consistency, the PRIMARY design source is:

`design.md`

If an older registration/onboarding design reference still exists, it may be
used only as a secondary auth/onboarding reference. Do not let it override
`design.md` for dashboard tables, forms, sidebar, topbar, cards, or other
backend UI.

Do NOT invent a new visual identity.

Sidebar menu/submenu colors must remain compatible with the Admin-configurable
theme/CSS-variable system defined in `design.md`. Do not hard-code theme colors
inside individual Buyer navigation items in a way that prevents Admin settings
from changing them.

I do NOT need you to explain what every page should visually look like.

Determine appropriate UI automatically from the existing project and
`design.md`.

The important requirement is:

EVERY required Buyer functionality must exist.

==========================================================
DATABASE IS THE SOURCE OF TRUTH
==========================================================

The latest Edushopify database schema / SQL dump is the database structure
source of truth.

Reuse the existing database tables.

DO NOT create duplicate tables.

DO NOT create alternative systems for functionality already represented
in the database.

Before creating a migration, first inspect the existing schema.

Only add or modify database structure when the current database genuinely
cannot support the required functionality.

A database status/field existing does NOT automatically mean that every
future-capable workflow is enabled in Phase 1. Follow the explicit Phase 1
rules in this workflow.

Do not physically delete important transactional history unless the existing
schema/business rule explicitly permits it.

==========================================================
AUTHORIZATION ARCHITECTURE
==========================================================

Buyer is a CAPABILITY.

Buyer is NOT a Spatie role.

An account may have:

- Buyer capability
- Supplier capability
- both

Spatie Permission Teams uses:

account_id

Before account-scoped permission checks, make sure the correct account
team context is loaded.

Every protected Buyer action must validate:

Authenticated User
        ↓
User Active
        ↓
Account Active
        ↓
Active Account Membership
        ↓
Active Buyer Capability
        ↓
Correct Spatie Account Context
        ↓
Required Permission
        ↓
Resource belongs to Buyer Account
        ↓
Current workflow/status allows action

Backend authorization must be enforced through existing Policies,
services, middleware, and permission architecture.

Never rely only on hidden buttons or frontend conditions.

Prevent IDOR/cross-account access everywhere.

==========================================================
PART 1 — FINAL BUYER DASHBOARD MENU STRUCTURE
==========================================================

Use the following logical Buyer Dashboard structure.

BUYER DASHBOARD

├── 1. Dashboard
│   └── Overview
│
├── 2. Marketplace
│   ├── Products
│   ├── Services
│   └── Suppliers
│
├── 3. Procurement
│   │
│   ├── RFQs
│   │   ├── All RFQs
│   │   ├── Create RFQ
│   │   └── RFQ Detail
│   │       ├── Overview
│   │       ├── Items
│   │       ├── Suppliers / Targeting
│   │       ├── Questions & Answers
│   │       ├── Quotations
│   │       ├── Deadline History
│   │       └── Change History
│   │
│   ├── Quotations
│   │   ├── Received Quotations
│   │   ├── Compare Quotations
│   │   ├── Shortlisted Quotations
│   │   └── Revision Requests / History
│   │
│   ├── Awards
│   │   ├── All Awards
│   │   ├── Pending Supplier Response
│   │   ├── Accepted / Rejected Awards
│   │   └── Re-award
│   │
│   └── Purchase Orders
│       ├── All Purchase Orders
│       └── Purchase Order Detail
│           ├── Items & Totals
│           ├── Current Status
│           ├── Status History
│           ├── Complete / Close where permitted
│           └── Cancel / Support Dispute
│
├── 4. Saved Items
│   ├── Suppliers
│   ├── Products / Listings
│   ├── RFQs
│   └── Quotations
│
├── 5. Communication
│   ├── Messages
│   ├── Notifications
│   └── Support Tickets
│
├── 6. Reviews
│   └── My Reviews
│
├── 7. Buyer Profile
│   ├── Profile Information
│   └── Locations / Address Book
│
├── 8. Organization
│   ├── Members
│   ├── Invitations
│   ├── Roles & Permissions
│   ├── Role Requests
│   └── Ownership
│
└── 9. Settings & Account
    ├── Personal & Security
    ├── Dashboard / Buyer-Supplier Mode
    ├── Convert to Organization
    └── Close Account


IMPORTANT NAVIGATION RULE:

Do NOT put every database workflow directly into the sidebar.

For example:

- RFQ Questions
- Deadline Extension
- RFQ Change History

belong inside RFQ Detail.

Similarly:

- PO status history
- authorized Phase 1 completion
- cancellation
- Support Ticket based dispute handling

belong inside Purchase Order Detail.

Keep the sidebar simple while preserving complete functionality.

==========================================================
PART 2 — DASHBOARD
==========================================================

MENU:

Dashboard
└── Overview

Purpose:

Provide the Buyer with an immediate understanding of procurement activity,
deadlines, outstanding actions, and recent events.

Implement dashboard functionality for:

- Buyer capability status
- Buyer onboarding/application status where applicable
- Open RFQs
- Draft RFQs
- RFQs pending approval if configured
- Quotations received
- New quotations
- Shortlisted quotations
- Pending quotation revision requests
- Supplier questions awaiting Buyer answer
- RFQs approaching quotation deadline
- RFQs approaching Q&A deadline
- Awards awaiting Supplier response
- Accepted awards
- Active/issued purchase orders
- Purchase orders awaiting authorized Phase 1 completion
- Completed purchase orders
- Unread messages
- Unread notifications
- Open support tickets
- Recent RFQs
- Recent quotations
- Recent awards
- Recent purchase orders
- Recent activity

Add an "Action Required" concept.

Examples:

- 3 Supplier questions waiting for answers
- 5 new quotations
- 1 award awaiting Supplier response
- 2 issued purchase orders awaiting completion/action

Add "Upcoming Deadlines":

- quotation deadlines
- Q&A deadlines
- quotation revision deadlines
- award response deadlines where relevant

Primary existing tables:

accounts
account_capabilities
capability_types
buyer_profiles
rfqs
rfq_questions
quotations
quotation_revision_requests
awards
purchase_orders
messages
conversation_user_states
notifications
tickets
account_dashboard_preferences

==========================================================
PART 3 — MARKETPLACE
==========================================================

MENU:

Marketplace
├── Products
├── Services
└── Suppliers


==========================================================
3.1 PRODUCTS
==========================================================

Implement complete Buyer product discovery.

Buyer must be able to:

- Browse published products
- Search products
- Search by product/listing name
- Search relevant descriptions
- Filter by category
- Filter by subcategory
- Filter by brand
- Filter by attributes
- Filter by Supplier
- Filter by location when relevant
- Filter by price where relevant
- Filter by MOQ where appropriate
- Filter by availability/status where supported
- Sort results
- Paginate results
- Open Product Detail
- View Supplier
- View category
- View brand
- View attributes/specifications
- View variants
- View variant attributes
- View tier pricing
- View MOQ
- View lead time
- View media/images
- View relevant commercial information
- Save product/listing
- Remove saved product
- Message Supplier
- Request quotation
- Create RFQ from product
- Pre-populate RFQ with selected listing where appropriate

Only expose listings allowed by existing publication/approval rules.

Primary tables:

listings
product_details
listing_categories
categories
brands
attributes
attribute_values
category_attribute
listing_attribute_values
listing_variants
listing_variant_attributes
listing_tier_prices
units
media
accounts
supplier_profiles
saved_items


PRODUCT FLOW:

Marketplace
     ↓
Products
     ↓
Search / Filter
     ↓
Product Detail
     ↓
     ├── Save
     ├── View Supplier
     ├── Message Supplier
     └── Request Quote
              ↓
          Create RFQ
              ↓
      Product pre-populated


==========================================================
3.2 SERVICES
==========================================================

Implement complete service discovery.

Buyer must be able to:

- Browse published services
- Search services
- Filter category
- Filter Supplier
- Filter service area/location
- Filter service type/mode where supported
- Open Service Detail
- View Supplier
- View service description
- View category
- View service mode
- View duration
- View lead time
- View service terms
- View support terms
- View media
- Save service/listing
- Message Supplier
- Request quotation
- Create RFQ from service

Primary tables:

listings
service_details
listing_categories
categories
listing_attribute_values
supplier_profiles
supplier_service_areas
media
saved_items


SERVICE FLOW:

Marketplace
     ↓
Services
     ↓
Search / Filter
     ↓
Service Detail
     ↓
Save / Message / Request Quote
     ↓
Create RFQ


==========================================================
3.3 SUPPLIERS
==========================================================

Implement full Supplier discovery functionality for Buyers.

Buyer must be able to:

- Browse Supplier directory
- Search Suppliers
- Filter by Supplier type
- Filter by country
- Filter by state/city where supported
- Filter by relevant service area
- Open Supplier profile
- View Supplier company information
- View public Supplier verification information
- View Supplier types
- View locations
- View service areas
- View business hours
- View Supplier gallery
- View Supplier videos
- View Supplier exhibitions where available
- View Supplier published products
- View Supplier published services
- View Supplier rating/reviews
- View Supplier performance metrics where public
- Save Supplier
- Remove saved Supplier
- Message Supplier
- Send RFQ
- Create an RFQ pre-targeted to the selected Supplier

Primary tables:

accounts
supplier_profiles
supplier_types
supplier_supplier_type
supplier_service_areas
account_locations
business_hours
supplier_gallery
supplier_videos
exhibitions
exhibition_supplier
listings
reviews
saved_items


SUPPLIER FLOW:

Supplier Directory
       ↓
Search / Filter
       ↓
Supplier Profile
       ↓
       ├── Products / Services
       ├── Reviews
       ├── Save
       ├── Message
       └── Send RFQ
                ↓
         Supplier pre-selected
         in RFQ creation


==========================================================
PART 4 — PROCUREMENT / RFQ MANAGEMENT
==========================================================

MENU:

Procurement
└── RFQs
    ├── All RFQs
    ├── Create RFQ
    └── RFQ Detail


==========================================================
4.1 ALL RFQs
==========================================================

Buyer must be able to:

- View all RFQs belonging to current Buyer account
- Search RFQs
- Filter RFQs by status
- Filter by date where useful
- Filter by visibility type
- Sort RFQs
- View quotation count
- View item count
- View quotation deadline
- View RFQ status
- View RFQ target type
- Open RFQ Detail
- Continue editing draft RFQ
- Create new RFQ

Statuses include existing RFQ workflow states such as:

draft
pending_approval
open
closed
award_pending
awarded
cancelled
expired
completed

Actions must depend on status.

Primary table:

rfqs


==========================================================
4.2 CREATE RFQ
==========================================================

Build complete RFQ creation functionality.

RFQ must support:

BASIC INFORMATION

- title
- description
- currency
- budget minimum
- budget maximum
- expected delivery date

DELIVERY

- country
- state
- city
- address
- coordinates where supported
- saved Buyer location/address selection

RFQ ITEMS

RFQ must support multiple items.

Each RFQ item may be:

- product
- service

Support:

- existing listing
- custom item
- item name
- category
- description
- quantity
- unit
- custom unit where supported
- estimated unit price
- specifications
- item ordering/sort

RFQ OPTIONS

- allow partial quotation
- allow alternative products where relevant
- quotation deadline
- Q&A deadline

TARGETING

Support:

1. Global RFQ
2. Selected Suppliers RFQ

For selected Suppliers:

- Supplier search
- select one Supplier
- select multiple Suppliers
- remove selected Supplier
- prevent duplicate Supplier selection

For Global RFQ support target filters:

- category
- country
- state
- city

Support:

- save draft
- edit draft
- validate draft
- preview where useful
- publish RFQ

Primary tables:

rfqs
rfq_items
rfq_selected_suppliers
rfq_target_filters
listings
categories
units
countries
states
cities
accounts
supplier_profiles
account_locations


RFQ CREATION FLOW:

Create RFQ
    ↓
Basic Information
    ↓
Add RFQ Items
    ↓
Budget / Currency
    ↓
Delivery Location
    ↓
Set Deadlines
    ↓
Choose Visibility
    ↓
    ├── GLOBAL
    │      ↓
    │ Target Filters
    │      ↓
    │ Determine Eligible Suppliers
    │
    └── SELECTED SUPPLIERS
           ↓
       Search Suppliers
           ↓
       Select Supplier(s)
    ↓
Save Draft
    ↓
Publish
    ↓
Open RFQ


==========================================================
4.3 RFQ PUBLISHING
==========================================================

Before publishing validate:

- active authenticated user
- active account
- active membership
- active Buyer capability
- required permission
- Buyer account owns RFQ
- RFQ is in publishable state
- required basic fields
- at least one valid RFQ item
- valid deadlines
- valid delivery information when required
- selected Supplier list when visibility requires it

If admin approval is configured:

draft
  ↓
pending_approval
  ↓
open

Otherwise:

draft
  ↓
open

When publishing:

- set appropriate timestamps
- generate/update Supplier eligibility queue
- create target Supplier relationships
- notify relevant Suppliers where required
- ensure publishing is idempotent
- protect against duplicate submission

Primary tables:

rfqs
rfq_items
rfq_selected_suppliers
rfq_target_filters
rfq_supplier_queue
notifications


==========================================================
4.4 RFQ DETAIL — OVERVIEW
==========================================================

Buyer must be able to view:

- RFQ number
- RFQ title
- description
- current status
- current version
- visibility
- budget
- currency
- delivery
- deadlines
- expected delivery
- created by
- created date
- published date
- item count
- quotation count
- cancellation details when applicable
- approval details when applicable

Actions must change depending on RFQ status.


==========================================================
4.5 RFQ DETAIL — ITEMS
==========================================================

Buyer must be able to:

- view all RFQ items
- view listing reference
- view item category
- view quantity
- view unit
- view specifications
- edit items when workflow allows
- add item when workflow allows
- remove item when workflow allows

Primary tables:

rfq_items
listings
categories
units


==========================================================
4.6 RFQ DETAIL — SUPPLIERS / TARGETING
==========================================================

Buyer must be able to:

- view visibility type
- view selected Suppliers
- view targeting criteria
- add/remove selected Suppliers when workflow allows
- view Supplier invitation/eligibility information
- view Supplier access/queue state where relevant

Primary tables:

rfq_selected_suppliers
rfq_target_filters
rfq_supplier_queue
accounts
supplier_profiles


==========================================================
4.7 RFQ QUESTIONS & ANSWERS
==========================================================

Implement Supplier Q&A functionality.

Supplier asks question
        ↓
Buyer receives question
        ↓
Buyer reviews question
        ↓
Buyer answers
        ↓
Supplier receives answer

Buyer must be able to:

- view all questions for owned RFQ
- see pending question count
- see asking Supplier
- see question
- see question status
- see asked date
- answer question
- see previous answer
- see answered by
- see answered date
- respect Q&A deadline
- respect RFQ status
- support public/private behavior according to existing schema/business logic

Buyer must NOT be able to alter Supplier's original question.

Primary table:

rfq_questions

Related:

rfqs
accounts
users
notifications


FLOW:

RFQ Published
      ↓
Supplier Asks Question
      ↓
rfq_questions.status = pending
      ↓
Buyer Notification
      ↓
Buyer Answers
      ↓
rfq_questions updated
      ↓
status = answered
      ↓
Supplier Notification


==========================================================
4.8 RFQ DEADLINE EXTENSION
==========================================================

Implement formal deadline extension rather than silently editing deadline.

Support:

- quotation deadline extension
- Q&A deadline extension

Buyer must:

- choose deadline type
- enter valid new deadline
- enter reason
- see old deadline
- see extension history

Store:

- old deadline
- new deadline
- type
- reason
- user who extended
- timestamps

Update the corresponding RFQ deadline.

Notify affected Suppliers.

Primary tables:

rfqs
rfq_deadline_extensions
notifications


FLOW:

Open RFQ
      ↓
Extend Deadline
      ↓
Quotation OR Q&A
      ↓
New Deadline
      ↓
Reason
      ↓
Create Extension History
      +
Update RFQ
      ↓
Notify Suppliers


==========================================================
4.9 RFQ CHANGE HISTORY / VERSIONING
==========================================================

This is critical.

Do NOT silently overwrite a published RFQ when Suppliers have already
received or quoted against it.

Implement RFQ versions.

When Buyer changes a published RFQ:

- determine changed fields
- determine minor vs major change
- increment RFQ version
- preserve previous version snapshot
- preserve new snapshot
- store changed fields
- store changing user
- store change time
- determine whether existing quotations require revision

Relevant fields already represented include:

from_version_no
to_version_no
change_level
changed_fields
old_snapshot
new_snapshot
requires_quotation_revision
changed_by_user_id
changed_at

Primary tables:

rfqs
rfq_change_logs
quotations
notifications


FLOW:

RFQ v1 Published
      ↓
Supplier submits quotation
      ↓
Buyer changes RFQ
      ↓
Detect changed fields
      ↓
Minor OR Major
      ↓
RFQ becomes v2
      ↓
Create Change Log
      ↓
Existing quote affected?
      ↓
YES
      ↓
Require Supplier revision where necessary
      ↓
Notify Supplier


Buyer must be able to view:

- version history
- version numbers
- minor/major changes
- changed fields
- change date
- changed by
- whether quotation revision was required


==========================================================
4.10 RFQ CANCELLATION
==========================================================

Buyer must be able to cancel RFQ when business state permits.

Require:

- permission
- ownership
- valid RFQ status
- cancellation reason
- confirmation

On cancellation:

- set cancelled state
- save cancellation reason
- save cancellation timestamp
- preserve all existing historical quotations
- preserve history
- notify affected Suppliers

Do not physically delete transactional records.


==========================================================
PART 5 — QUOTATIONS
==========================================================

MENU:

Procurement
└── Quotations
    ├── Received Quotations
    ├── Compare Quotations
    ├── Shortlisted Quotations
    └── Revision Requests / History


==========================================================
5.1 RECEIVED QUOTATIONS
==========================================================

Buyer must be able to:

- view all quotations received for owned RFQs
- filter by RFQ
- filter by quotation status
- search
- sort
- see Supplier
- see quotation number
- see quotation total
- see lead time
- see validity
- see current revision number
- see RFQ version quoted against
- see viewed/unviewed state if implemented
- open quotation detail

Quotation Detail must show:

- Supplier
- RFQ
- RFQ version
- quotation number
- current revision
- subtotal
- tax
- discount
- shipping charge
- grand total
- currency
- lead time
- valid until
- warranty
- support terms
- payment terms
- proposal
- quotation items
- offered listing
- offered variant
- alternative item indicator
- partial quotation information
- status

Buyer actions:

- shortlist
- remove shortlist where permitted
- request revision
- reject
- compare
- award where allowed
- message Supplier

Primary tables:

quotations
quotation_items
rfqs
rfq_items
supplier_profiles
accounts


==========================================================
5.2 COMPARE QUOTATIONS
==========================================================

Implement complete commercial comparison.

Do NOT compare only grand totals.

Comparison must account for RFQ item coverage.

Show comparable information:

- Supplier
- Supplier rating where relevant
- RFQ items requested
- items quoted
- item coverage count
- full vs partial quotation
- alternative item indicator
- individual item prices
- quantities
- subtotal
- discount
- tax
- shipping
- grand total
- currency
- lead time
- valid until
- warranty
- support
- payment terms
- proposal summary
- quotation status
- RFQ version
- quotation revision number

Example:

Supplier A:
5 / 5 Items Quoted

Supplier B:
3 / 5 Items Quoted
Partial Quote

This distinction is mandatory.

Primary tables:

rfqs
rfq_items
quotations
quotation_items
supplier_profiles
reviews
quotation_revisions
quotation_revision_items


==========================================================
5.3 SHORTLISTED QUOTATIONS
==========================================================

Buyer must be able to:

- shortlist quotation
- remove quotation from shortlist
- see shortlisted quotations
- add internal shortlist note
- rank quotations where supported
- compare shortlisted quotations
- proceed to award

Primary table:

rfq_shortlists

Related:

quotations
rfqs
supplier_profiles


FLOW:

Received Quotes
      ↓
Compare
      ↓
Shortlist Supplier A
Shortlist Supplier B
      ↓
Final Evaluation
      ↓
Award Winner


==========================================================
5.4 QUOTATION REVISION REQUESTS
==========================================================

Buyer must be able to formally request changes.

Support:

- revision request
- requested changes text
- requested fields
- response deadline
- request status
- Supplier response
- request date
- response date

Statuses may include:

pending
accepted
revised
declined
expired
cancelled

Buyer must be able to:

- request revision
- view pending request
- cancel pending request where allowed
- see Supplier response
- see expired request
- see completed revision
- request another revision where workflow permits

Primary table:

quotation_revision_requests


==========================================================
5.5 QUOTATION REVISION HISTORY
==========================================================

Never overwrite old quotation revisions.

Buyer must be able to view:

Quotation
      ↓
Revision 1
Revision 2
Revision 3
...

Show:

- revision number
- revision date
- created by
- commercial totals
- item details
- changed item information
- terms
- Supplier response
- RFQ version associated with revision

Buyer must be able to compare historical revisions.

Primary tables:

quotation_revisions
quotation_revision_items
quotations
quotation_items
rfq_items


FLOW:

Quote v1
   ↓
Buyer Requests Revision
   ↓
Supplier Revises
   ↓
Quote v2
   ↓
Buyer Reviews / Compares
   ↓
Award / Reject / Request Revision Again


==========================================================
5.6 REJECT QUOTATION
==========================================================

Buyer must be able to reject quotation when allowed.

Store:

- status
- deciding user
- decision timestamp
- reason if required by current business rules

Notify Supplier.

Rejecting one quotation must not incorrectly reject unrelated quotations.


==========================================================
PART 6 — AWARDS
==========================================================

MENU:

Procurement
└── Awards
    ├── All Awards
    ├── Pending Supplier Response
    ├── Accepted / Rejected Awards
    └── Re-award


==========================================================
6.1 CREATE AWARD
==========================================================

Buyer must be able to:

- select eligible quotation
- verify quotation belongs to owned RFQ
- verify quotation is awardable
- confirm final award
- enter award note
- set/use response deadline
- generate award
- see attempt number

Primary table:

awards

Related:

rfqs
quotations
accounts
users
notifications


FLOW:

Buyer Reviews Quotations
      ↓
Select Winner
      ↓
Award
      ↓
status = pending_supplier_response
      ↓
Supplier Notification


==========================================================
6.2 SUPPLIER AWARD RESPONSE
==========================================================

Complete end-to-end dependency.

Supplier must later be able to:

ACCEPT

or

REJECT

On ACCEPT:

- award = accepted
- store response information
- automatically create Purchase Order
- notify Buyer

On REJECT:

- award = rejected_by_supplier
- Supplier rejection reason required
- save responded time
- notify Buyer
- allow Buyer to choose another eligible quotation

Primary table:

awards


==========================================================
6.3 RE-AWARD
==========================================================

Implement:

Award Supplier A
      ↓
Supplier A Rejects
      ↓
Buyer sees rejection reason
      ↓
Buyer chooses another quotation
      ↓
Award Supplier B
      ↓
award_attempt_no increments

Previous award remains historical.

Do not delete previous award.

Respect:

pending_supplier_response
accepted
rejected_by_supplier
cancelled
superseded

Enforce the Phase 1 rule:

Only ONE final winning Supplier per RFQ.


==========================================================
6.4 AWARD CONCURRENCY PROTECTION
==========================================================

Critical.

Two organization employees must not successfully award two different
Suppliers simultaneously.

Use:

- database transaction
- state re-check inside transaction
- row locking where appropriate
- idempotency
- existing database constraints

Never rely only on frontend button disabling.


==========================================================
PART 7 — PURCHASE ORDERS
==========================================================

MENU:

Procurement
└── Purchase Orders
    ├── All Purchase Orders
    └── Purchase Order Detail


==========================================================
7.1 AUTOMATIC PURCHASE ORDER CREATION
==========================================================

When an award is accepted:

Award Accepted
      ↓
Create Purchase Order exactly once

Populate PO from the accepted quotation.

Snapshot:

- quotation
- RFQ
- Buyer
- Supplier
- commercial totals
- currency
- item details
- quantities
- unit prices
- tax
- discount
- shipping
- other required commercial values

Create:

purchase_orders
purchase_order_items
purchase_order_status_history where required

Initial status:

issued

Purchase Order creation must be idempotent.

Duplicate Supplier accept requests must NOT create duplicate POs.


==========================================================
7.2 PURCHASE ORDER LIST
==========================================================

Buyer must be able to:

- view all owned purchase orders
- search
- filter status
- filter Supplier
- filter date
- sort
- view PO number
- Supplier
- grand total
- currency
- status
- payment status
- delivery status
- open PO Detail

Primary table:

purchase_orders


==========================================================
7.3 PURCHASE ORDER DETAIL
==========================================================

Display complete transactional information:

- PO number
- award
- RFQ
- quotation
- Buyer
- Supplier
- items
- quantities
- unit prices
- subtotal
- tax
- shipping
- discount
- grand total
- currency
- payment note
- payment status
- issue date
- current fulfilment status
- complete status history

Primary tables:

purchase_orders
purchase_order_items
purchase_order_status_history


==========================================================
7.4 PHASE 1 PURCHASE ORDER FULFILMENT / COMPLETION
==========================================================

IMPORTANT PHASE 1 RULE:

The database enum supports future-capable Purchase Order statuses including:

issued
confirmed
in_progress
ready_for_delivery
delivered
completed
cancelled
disputed

However, the approved Phase 1 business workflow is intentionally simpler:

Award Accepted
      ↓
Purchase Order Created Exactly Once
      ↓
status = issued
      ↓
Buyer and Supplier View PO / Continue Transaction Outside Platform
      ↓
Authorized Manual Completion
      ↓
status = completed

Do NOT activate or build automatic transitions through:

confirmed
in_progress
ready_for_delivery
delivered
Buyer receipt confirmation

unless the project business rules are explicitly changed in a future phase.

If legacy data already contains one of those extended statuses, display it
safely and preserve history, but do not create new Phase 1 transitions into
those statuses.

Buyer should see the current PO status and complete status history.

Completion must validate:

- authenticated active user
- active account and membership
- active Buyer capability where required
- correct Spatie account/team context
- required permission
- Buyer account ownership
- current PO status
- valid Phase 1 transition
- unresolved dependency rules where applicable

Prevent duplicate completion.

Record the status transition in purchase_order_status_history.


==========================================================
7.5 CANCEL / SUPPORT-TICKET DISPUTE
==========================================================

Support:

cancelled
disputed

Buyer should be able to:

- cancel where business rules allow
- provide cancellation reason
- mark/link a dispute state where the existing PO workflow permits it
- create/link a Support Ticket
- message Supplier

PHASE 1 DISPUTE RULE:

Buyer-Supplier disputes are handled through the existing Support Ticket system.
Do NOT create a separate disputes table, escrow dispute engine, refund engine,
or full dispute-resolution subsystem in Phase 1.

Conceptually:

Purchase Order / RFQ / Quotation / Award
      ↓
Support Ticket
      ↓
Platform Support/Admin Handling

Primary tables:

purchase_orders
purchase_order_status_history
tickets
ticket_messages
conversations
messages


IMPORTANT PAYMENT RULE:

Product/service transaction payments happen OUTSIDE Edushopify in Phase 1.

Do NOT build:

- Buyer Stripe checkout
- marketplace wallet
- escrow
- Supplier payout
- marketplace refund engine

PO payment status is tracking information only.


==========================================================
PART 8 — SAVED ITEMS
==========================================================

MENU:

Saved Items
├── Suppliers
├── Products / Listings
├── RFQs
└── Quotations

Use the existing:

saved_items

Supported types:

listing
supplier
rfq
quotation

Support:

- save item
- unsave item
- view saved items
- filter saved items
- add notes
- personal visibility
- account/shared visibility

For organization Buyer:

PERSONAL
- only user sees item

ACCOUNT
- permitted members of same account may see item

SECURITY RULE:

Saving an item does NOT grant permanent authorization to that resource.

Whenever a saved Supplier, listing, RFQ, or quotation is opened, re-check the
current user's account context, capability, permission, visibility rules, and
resource access. A `saved_items` row must never bypass normal authorization.


SAVED ITEM FLOW:

Supplier / Listing / RFQ / Quote
       ↓
Save
       ↓
Personal OR Account
       ↓
saved_items
       ↓
Saved Items
       ↓
Open / Remove / Notes


==========================================================
PART 9 — COMMUNICATION
==========================================================

MENU:

Communication
├── Messages
├── Notifications
└── Support Tickets


==========================================================
9.1 MESSAGES
==========================================================

Implement/retain complete Buyer messaging.

Support conversation contexts such as:

- RFQ
- quotation
- listing
- purchase order
- general
- support

Buyer must be able to:

- start conversation with Supplier
- find existing appropriate thread
- prevent unnecessary duplicate conversation
- view conversation list
- open conversation
- send text
- receive text
- maintain read state
- view unread state
- use polling using existing project approach
- send files if existing message architecture supports them
- send images if existing architecture supports them
- show system messages where supported
- archive where supported
- mute where supported
- respect closed conversation state

Primary tables:

conversations
conversation_accounts
conversation_user_states
messages
media where used

Maintain existing polling architecture unless project requirements change.

Do NOT introduce WebSocket infrastructure just for this task.


==========================================================
9.2 NOTIFICATIONS
==========================================================

Implement full Buyer notification center.

Use existing:

notifications

Do NOT create a parallel notifications table.

Provide:

- notification bell count
- all notifications
- unread notifications
- mark read
- mark all read
- click notification → open related business resource

Notify Buyer for important events including:

BUYER CAPABILITY

- submitted
- approved
- rejected
- revision required
- suspended

RFQ

- approved/rejected if RFQ admin approval applies
- Supplier question
- important deadline events
- relevant RFQ workflow events

QUOTATION

- new quotation
- quotation revision
- Supplier response to revision request
- quotation withdrawal if supported

AWARD

- Supplier accepted
- Supplier rejected
- response deadline event where appropriate

PURCHASE ORDER

- PO created
- Supplier confirms
- status changed
- ready for delivery
- delivered

MESSAGES

- new conversation/message where appropriate

SUPPORT

- Admin reply
- ticket status change

REVIEWS

- moderation result

ORGANIZATION

- invitation
- membership changes
- role/ownership events where appropriate

Prevent duplicate notifications when jobs or actions retry.


==========================================================
9.3 SUPPORT TICKETS
==========================================================

Buyer must be able to:

- create ticket
- list tickets
- filter ticket status
- view ticket detail
- reply
- reopen according to current rules
- see Admin replies
- see status
- see priority
- link ticket to relevant business resource
- attach files if supported

Related resources may include:

- RFQ
- quotation
- award
- purchase order
- general account issue

Primary tables:

tickets
ticket_messages
accounts
users
media where relevant

Never expose Admin-only/internal notes to Buyer.


==========================================================
PART 10 — REVIEWS
==========================================================

MENU:

Reviews
└── My Reviews

Buyer must be able to:

- submit review when eligible
- review Supplier after quotation experience
- review Supplier after completed purchase experience
- rate Supplier
- add title
- add comment
- view own reviews
- see moderation status
- see published/hidden/rejected state
- see Supplier response when allowed
- report relevant published review where platform rules allow

Review contexts include:

quotation_experience
purchase_experience

Before review submission validate:

- Buyer account participated in relevant transaction
- Supplier belongs to that transaction
- review context is valid
- duplicate review does not exist
- rating range valid
- transaction/RFQ/PO state permits review

New review should follow moderation workflow.

Do not auto-publish if current system requires moderation.

Primary tables:

reviews
review_replies
review_reports
rfqs
quotations
purchase_orders
accounts
supplier_profiles


FLOW:

Valid Buyer Experience
      ↓
Write Review
      ↓
pending
      ↓
Admin Moderation
      ↓
Published / Hidden / Rejected
      ↓
Supplier Reply where permitted


==========================================================
PART 11 — BUYER PROFILE
==========================================================

MENU:

Buyer Profile
├── Profile Information
└── Locations / Address Book


==========================================================
11.1 PROFILE INFORMATION
==========================================================

Support complete Buyer profile information.

Buyer may manage permitted fields such as:

- display name
- organization name
- contact person
- position
- email
- phone
- website
- country
- state
- city
- address
- logo
- tax ID
- bio
- procurement information
- additional data where supported

Support Buyer types.

Database supports:

buyer_profiles
buyer_types
buyer_buyer_type

Respect primary Buyer type where applicable.

Primary tables:

buyer_profiles
buyer_types
buyer_buyer_type
accounts
countries
states
cities


==========================================================
11.2 LOCATIONS / ADDRESS BOOK
==========================================================

Implement reusable account locations.

Buyer should be able to:

- list locations
- add location
- edit location
- deactivate/remove according to rules
- set primary
- categorize/use relevant location types
- reuse location in RFQ creation

Possible business locations:

- Head Office
- Registered Office
- Branch
- Warehouse
- Billing Address
- Delivery Address

Primary tables:

account_locations
accounts
countries
states
cities

FLOW:

Buyer Adds Location
      ↓
account_locations
      ↓
Set Primary
      ↓
Create RFQ
      ↓
Select Saved Location


==========================================================
PART 12 — ORGANIZATION
==========================================================

Organization menu applies only where account type and permissions allow it.

MENU:

Organization
├── Members
├── Invitations
├── Roles & Permissions
├── Role Requests
└── Ownership


==========================================================
12.1 MEMBERS
==========================================================

Use existing:

account_members

Do not create a second membership system.

Organization Buyer must be able to:

- view members
- view owners
- view primary owner
- view active members
- view suspended/inactive members where allowed
- activate/deactivate according to permissions
- suspend member
- remove member
- see joining information
- see member status
- see assigned role(s)

Respect statuses:

invited
active
inactive
suspended
removed

Business rule:

One user belongs to only one marketplace account.

Never allow membership operations to violate this.


==========================================================
12.2 INVITATIONS
==========================================================

Use:

account_member_invitations

Organization Buyer with proper permissions must be able to:

- invite employee/member
- enter email
- optional name
- optional phone
- select appropriate invitation mode
- generate secure invitation token
- send invitation
- list pending invitations
- resend invitation
- cancel invitation
- handle expiry
- accept invitation
- create/activate account membership
- assign allowed role after acceptance

Support existing invitation modes:

owner_prefilled
employee_self_complete

Statuses:

pending
accepted
expired
cancelled

FLOW:

Authorized Owner/Admin
      ↓
Invite Employee
      ↓
Secure Invitation
      ↓
Employee Opens Invitation
      ↓
Verify User Eligibility
      ↓
Accept
      ↓
account_members
      ↓
Role Assignment


==========================================================
12.3 ROLES & PERMISSIONS
==========================================================

Use the existing Spatie Permission Teams architecture.

DO NOT build another RBAC system.

Buyer is NOT a role.

Roles control what individual organization members may do.

Organization management should support:

- list available global/account roles allowed for the current account
- view role permissions
- assign an allowed approved role to a member
- remove role assignment from a member
- view capability scope
- enforce owner-only permissions
- enforce sensitive permissions
- enforce assignable/active permission flags
- prevent Platform-scoped permission escalation
- view account-specific roles created from approved requests

IMPORTANT V4.3 CUSTOM ROLE RULE:

The `role_requests` table is the authoritative workflow for organization
requests for new account-specific custom roles.

Buyer/organization users must NOT directly create a new account-specific Spatie
role and immediately grant it to themselves.

When the organization needs a new custom role or a customized role that
requires new permission composition:

1. authorized organization user creates a Role Request;
2. requested permissions are validated against capability scope,
   assignable/active flags, sensitive restrictions, owner-only restrictions,
   and Platform permission boundaries;
3. Admin reviews the request;
4. only an APPROVED request may result in creation of the account-specific
   Spatie role with `roles.account_id = current account id`;
5. permissions are synchronized through `role_has_permissions`;
6. after the role exists, authorized organization users may assign it according
   to normal role-assignment rules.

Global standard roles remain maintained by Platform Admin.

If an existing approved account-specific role already exists, authorized users
may assign/remove it without creating another request.

Do not let organization users edit/delete global roles.

Never allow organization users to grant themselves platform/admin permissions.

Examples of organization Buyer roles may include existing roles such as:

- primary_owner
- co_owner
- account_admin
- buyer_manager
- procurement_manager
- finance_manager
- viewer

But use actual existing roles from the database/project.

Do NOT hard-code new role names if equivalent roles already exist.

Primary tables:

roles
permissions
role_has_permissions
model_has_roles
model_has_permissions
account_members
accounts
role_requests


==========================================================
12.4 ROLE REQUESTS
==========================================================

Use:

role_requests

Organization users with the required permission may:

- create role request
- enter role name
- enter display name
- select valid capability scope
- request allowed permissions
- enter description
- view request status
- cancel pending request when allowed
- see Admin review comment

Statuses:

pending
approved
rejected
cancelled

Rules:

- pending request does NOT create a live account role;
- rejected request does NOT create a live account role;
- cancelled request does NOT create a live account role;
- approved request may create the account-specific role exactly once;
- role creation from approval must be idempotent;
- role/account/capability scope must match the requesting account;
- never copy Platform-scoped permissions into an account role;
- owner-only/sensitive restrictions must still be enforced;
- audit important role-request and role-creation actions.

FLOW:

Organization Needs Custom Role
      ↓
Role Request
      ↓
Requested Permissions
      ↓
Validation
      ↓
Admin Review
      ↓
      ├── Rejected / Cancelled
      │       ↓
      │   No Live Role Created
      │
      └── Approved
              ↓
      Create Account-Specific Spatie Role Exactly Once
              ↓
      Sync Approved Permissions
              ↓
      Role Becomes Assignable to Authorized Members


==========================================================
12.5 OWNERSHIP
==========================================================

Use:

account_ownership_transfers

Support:

- primary owner
- co-owners where current business rules allow
- initiate ownership transfer
- select eligible target member
- transfer reason
- pending transfer
- target acceptance/rejection
- cancellation
- transfer completion
- ownership history

Statuses:

pending
accepted
rejected
cancelled
completed

Rules:

- account must never have zero owners
- primary owner cannot simply leave without valid transfer
- last active owner cannot remove themselves
- ownership transition must be transactional
- audit important changes

Primary tables:

accounts
account_members
account_ownership_transfers
users
activity_log


==========================================================
PART 13 — SETTINGS & ACCOUNT
==========================================================

MENU:

Settings & Account
├── Personal & Security
├── Dashboard / Buyer-Supplier Mode
├── Convert to Organization
└── Close Account


==========================================================
13.1 PERSONAL & SECURITY
==========================================================

Separate user identity from Buyer business profile.

Support appropriate account-user settings:

- personal name/profile information
- email
- phone
- password
- email verification
- phone verification where supported
- language/locale
- currency preference
- active sessions where supported
- security/session management
- OTP/security features where supported
- connected social accounts where applicable

Primary tables may include:

users
otp_codes
sessions
social_accounts
languages
currencies
settings

Reuse existing authentication/profile features where already available.


==========================================================
13.2 DASHBOARD / BUYER-SUPPLIER MODE
==========================================================

Use:

account_dashboard_preferences

If an account has BOTH:

Buyer capability = active
Supplier capability = active

allow switching:

BUYER
↔
SUPPLIER

Support default mode preference.

Important:

Changing dashboard mode does NOT grant permission.

Authorization always remains based on:

capability
+
membership
+
role/permission
+
resource ownership


==========================================================
13.3 CONVERT INDIVIDUAL TO ORGANIZATION
==========================================================

Use:

account_conversion_requests
account_type_changes

Do NOT create another account.

Conversion applies to the SAME account.

Support:

- start conversion
- enter proposed organization information
- upload documents where required
- save draft
- edit draft
- submit
- view pending state
- view revision-required response
- edit/resubmit
- view rejection
- cancel where permitted
- view approval

Statuses:

draft
pending
approved
rejected
revision_required
cancelled

Before approval:

- existing Individual account remains active
- proposed organization data remains private
- public identity does not change

After approval:

- same account changes to organization
- history recorded in account_type_changes

No organization → individual rollback unless the business rules are later
explicitly changed.

FLOW:

Individual Account
      ↓
Conversion Request
      ↓
Draft
      ↓
Submit
      ↓
Admin Review
      ↓
Revision / Reject / Approve
      ↓
Same Account → Organization


==========================================================
13.4 CLOSE ACCOUNT
==========================================================

Implement controlled account closure.

Do not physically delete important transactional history.

Before closure evaluate outstanding dependencies such as:

- open RFQs
- pending Awards
- active Purchase Orders
- open disputes/tickets
- other unresolved transactional obligations

Support:

- request account closure
- reason
- confirmation
- pending deletion/closure status
- final closure according to project rules

Preserve:

rfqs
quotations
awards
purchase_orders
reviews
tickets
messages/history
audit records

Primary tables:

accounts
activity_log
rfqs
awards
purchase_orders
tickets


==========================================================
PART 14 — COMPLETE BUYER BUSINESS WORKFLOW
==========================================================

The completed Buyer journey must support:

Buyer Registration
      ↓
Create Account
      ↓
Buyer Capability Application
      ↓
Buyer Profile
      ↓
Admin Approval
      ↓
Buyer Dashboard
      ↓
Browse Marketplace
      ↓
Products / Services / Suppliers
      ↓
Save / Message / Request Quote
      ↓
Create RFQ
      ↓
Add Items
      ↓
Global OR Selected Suppliers
      ↓
Publish RFQ
      ↓
Supplier Questions
      ↓
Buyer Answers
      ↓
Suppliers Submit Quotations
      ↓
Buyer Reviews Quotes
      ↓
Compare
      ↓
Shortlist
      ↓
Request Revision / Negotiate
      ↓
Supplier Revises
      ↓
Buyer Reviews Revision
      ↓
Select Winner
      ↓
Create Award
      ↓
Supplier Responds
      ↓
      ├── Reject
      │      ↓
      │ Buyer Re-awards
      │
      └── Accept
             ↓
       Purchase Order
             ↓
       Supplier Confirms
             ↓
       In Progress
             ↓
       Ready For Delivery
             ↓
       Delivered
             ↓
       Buyer Confirms Receipt
             ↓
       Completed
             ↓
       Buyer Review


At appropriate stages:

Buyer ↔ Supplier Messages

and:

Buyer → Support / Dispute


==========================================================
PART 15 — STATUS-AWARE BUSINESS LOGIC
==========================================================

Never expose actions merely because a page exists.

Actions must depend on current status.


RFQ

DRAFT

Allow appropriate:
- edit
- add/remove items
- change Supplier targeting
- change delivery
- change deadlines
- publish

PENDING_APPROVAL

Allow appropriate:
- view
- limited edit/cancel depending business rules

OPEN

Allow:
- view
- answer Supplier questions
- receive quotations
- formal deadline extension
- edit only through version-aware workflow
- cancel where permitted

CLOSED

Primarily:
- view
- process eligible quotations depending workflow

AWARD_PENDING

- view
- manage award state as permitted

AWARDED

- view winner
- proceed through Award/PO workflow

CANCELLED / EXPIRED / COMPLETED

- primarily historical/read-only behavior


QUOTATION

Handle statuses including existing values such as:

draft
submitted
under_review
revision_requested
revised
withdrawn
shortlisted
rejected
awarded
expired

Buyer actions must depend on each status.


AWARD

Handle:

pending_supplier_response
accepted
rejected_by_supplier
cancelled
superseded


PURCHASE ORDER

The database supports:

issued
confirmed
in_progress
ready_for_delivery
delivered
completed
cancelled
disputed

PHASE 1 ACTIVE WORKFLOW:

issued
completed
cancelled
disputed

Do not generate Phase 1 transitions to confirmed, in_progress,
ready_for_delivery, or delivered unless business rules are explicitly changed.

If legacy records contain an extended status, display and authorize them safely
without deleting or rewriting history.


REVIEW

Handle moderation states according to schema.


TICKET

Handle:

open
pending
answered
resolved
closed


BUYER CAPABILITY

Handle:

draft
pending
active
rejected
revision_required
suspended


ORGANIZATION MEMBERSHIP

Handle:

invited
active
inactive
suspended
removed


INVITATION

Handle:

pending
accepted
expired
cancelled


OWNERSHIP TRANSFER

Handle:

pending
accepted
rejected
cancelled
completed


ORGANIZATION CONVERSION

Handle:

draft
pending
approved
rejected
revision_required
cancelled


==========================================================
PART 16 — CROSS-CUTTING SECURITY REQUIREMENTS
==========================================================

For EVERY Buyer feature:

- validate authenticated user
- validate user status
- validate account
- validate account status
- validate account membership
- validate Buyer capability where required
- set correct Spatie team/account context
- validate permission
- validate resource ownership
- validate resource status
- validate workflow transition

Prevent:

- cross-account RFQ access
- cross-account quotation access
- cross-account Award access
- cross-account PO access
- cross-account message access
- cross-account ticket access
- cross-account saved item access
- cross-account member access
- cross-account role assignment

Do not expose another Buyer account's data by modifying URL parameters.


==========================================================
PART 17 — TRANSACTION / CONCURRENCY REQUIREMENTS
==========================================================

Use transactions for workflows involving multiple related changes.

Especially:

- RFQ publish
- RFQ version update
- deadline extension
- quotation decisions where multiple records change
- Award creation
- Supplier Award response
- Re-award
- Purchase Order creation
- PO state changes
- invitation acceptance
- ownership transfer
- organization conversion
- account closure transitions

For concurrency-sensitive operations:

re-check current state INSIDE the transaction.

Use lockForUpdate() or appropriate locking where required.

Make important operations idempotent.

Especially:

- RFQ publish
- Award
- Award accept
- PO creation
- authorized PO completion

==========================================================
PART 18 — ACTIVITY / HISTORY
==========================================================

Preserve history instead of destroying transactional records.

Use dedicated history tables where available:

rfq_change_logs
rfq_deadline_extensions
quotation_revisions
quotation_revision_items
purchase_order_status_history
account_type_changes
account_ownership_transfers
activity_log

Do not overwrite historical commercial information where a snapshot/history
structure already exists.


==========================================================
PART 19 — PHASE-BY-PHASE DEVELOPMENT PLAN
==========================================================

Before each phase:

1. read `ARCHITECTURE.md`
2. read `design.md`
3. inspect existing routes
4. inspect Controllers
5. inspect Form Requests
6. inspect Actions and Services
7. inspect Policies
8. inspect Models
9. inspect Blade views/components
10. inspect Middleware and relevant JavaScript
11. inspect database tables/schema
12. inspect existing tests
13. identify and preserve existing working functionality
14. implement only missing/required functionality
15. do not create or extend backend Livewire components


==========================================================
PHASE 1 — DASHBOARD FOUNDATION & NAVIGATION
==========================================================

Complete:

- final navigation structure
- permission-aware links
- capability-aware links
- organization-only links
- Buyer/Supplier mode-aware links
- Dashboard action-required data
- deadline summaries
- notification counters

Do not rebuild the master layout.


==========================================================
PHASE 2 — COMPLETE MARKETPLACE
==========================================================

Implement/complete:

- Product Marketplace
- Product Detail
- Service Marketplace
- Service Detail
- Supplier Directory
- Supplier Profile
- Save product/service
- Message Supplier
- Request Quote
- RFQ creation from listing


==========================================================
PHASE 3 — COMPLETE RFQ LIFECYCLE
==========================================================

Implement/complete:

- RFQ list
- creation
- drafts
- edit
- item management
- targeting
- publish
- Supplier queue
- RFQ Q&A
- deadline extension
- published RFQ versioning
- change history
- cancellation
- status-aware actions


==========================================================
PHASE 4 — COMPLETE QUOTATION LIFECYCLE
==========================================================

Implement/complete:

- Received Quotations
- Quotation Detail
- Full comparison
- Partial quote comparison
- Alternative item comparison
- Shortlisting
- Revision requests
- Revision response
- Revision history
- Revision comparison
- Reject
- prepare award action


==========================================================
PHASE 5 — COMPLETE AWARD LIFECYCLE
==========================================================

Implement/complete:

- Create Award
- Award Detail
- Pending response
- Supplier Accept
- Supplier Reject
- rejection reason
- response deadline
- Re-award
- attempt history
- concurrency protection
- one-winner enforcement


==========================================================
PHASE 6 — COMPLETE PHASE 1 PURCHASE ORDER LIFECYCLE
==========================================================

Implement/complete:

- automatic PO creation exactly once after accepted Award
- PO list
- PO detail
- item snapshots
- status history
- issued state
- authorized manual completion
- duplicate completion prevention
- completed state
- cancelled state where allowed
- disputed state only as supported by current PO workflow
- Support Ticket linkage for disputes
- outside-platform payment/fulfilment information
- preservation/display of extended legacy statuses if encountered

Do NOT implement new Phase 1 transitions for:

- confirmed
- in_progress
- ready_for_delivery
- delivered
- Buyer receipt confirmation

unless the business rules are explicitly changed.


==========================================================
PHASE 7 — SAVED ITEMS
==========================================================

Complete:

- Saved Suppliers
- Saved Products/Listings
- Saved RFQs
- Saved Quotations
- personal/account visibility
- notes


==========================================================
PHASE 8 — COMMUNICATION
==========================================================

Complete:

- Messages
- read state
- context-based conversations
- file/image/system message support where project permits
- Notifications
- notification deep links
- Support Tickets
- related transaction linking


==========================================================
PHASE 9 — REVIEWS
==========================================================

Complete:

- eligible review submission
- quotation experience review
- purchase experience review
- duplicate prevention
- My Reviews
- moderation state display
- Supplier reply display
- reporting where permitted


==========================================================
PHASE 10 — BUYER PROFILE & LOCATIONS
==========================================================

Complete:

- Buyer Profile
- Buyer Types
- contact/business information
- reusable Account Locations
- primary address
- delivery address reuse in RFQ


==========================================================
PHASE 11 — ORGANIZATION MEMBERS & INVITATIONS
==========================================================

Complete:

- Member list
- Member status
- Invitations
- invitation acceptance
- resend
- cancel
- expiry
- activation
- suspension
- removal


==========================================================
PHASE 12 — ROLES & PERMISSIONS
==========================================================

Complete:

- organization role management
- assign roles
- remove roles
- approved account-specific roles created from Role Requests
- permission synchronization
- owner-only restrictions
- sensitive permissions
- capability scope
- Role Requests


==========================================================
PHASE 13 — OWNERSHIP
==========================================================

Complete:

- owner list
- primary owner
- transfer ownership
- acceptance/rejection
- cancellation
- completion
- owner safety rules
- audit trail


==========================================================
PHASE 14 — SETTINGS / ACCOUNT
==========================================================

Complete:

- personal/security settings
- email/phone/password
- language/currency
- security/session controls
- Buyer/Supplier dashboard preference


==========================================================
PHASE 15 — INDIVIDUAL → ORGANIZATION
==========================================================

Complete:

- conversion draft
- proposed organization data
- documents
- submission
- review status
- revision
- resubmission
- approval
- same-account conversion
- type-change history


==========================================================
PHASE 16 — ACCOUNT CLOSURE
==========================================================

Complete:

- closure request
- dependency validation
- unresolved transaction checks
- deletion_pending/closure state
- preserve transactional history


==========================================================
PHASE 17 — FINAL SECURITY / BUSINESS LOGIC HARDENING
==========================================================

Test at minimum:

- suspended Buyer capability
- suspended Buyer account
- suspended member
- removed employee
- permission removed
- cross-account resource access
- late RFQ Q&A
- RFQ expiry
- published RFQ changed after quote
- RFQ version increments
- old quote against old RFQ version
- major RFQ change requiring revision
- quotation revision expiry
- partial quotation
- alternative quotation
- Award rejection
- Re-award
- simultaneous Award attempts
- one winner only
- duplicate Award acceptance
- duplicate PO prevention
- invalid PO transitions
- duplicate PO completion
- duplicate reviews
- invalid review Supplier
- invitation to user already belonging to account
- last owner protection
- platform permission escalation attempt


==========================================================
PHASE 18 — TESTING & COMPLETION
==========================================================

Every module must have appropriate automated tests.

A feature is only COMPLETE when:

- route exists
- page/Blade view exists
- functionality works
- validation exists
- required business logic exists (Action/Service only where needed)
- backend authorization exists
- ownership enforcement exists
- status rules exist
- transaction safety exists where needed
- notification exists where required
- empty states work
- invalid states are blocked
- cross-account access is tested
- existing tests continue passing


==========================================================
PART 20 — IMPORTANT DATABASE REUSE RULE
==========================================================

Reuse the existing tables wherever relevant, including:

ACCOUNT / IDENTITY

users
accounts
account_capabilities
capability_types
account_members
buyer_profiles
buyer_types
buyer_buyer_type
account_locations

MARKETPLACE

categories
brands
attributes
attribute_values
category_attribute
listings
product_details
service_details
listing_categories
listing_attribute_values
listing_variants
listing_variant_attributes
listing_tier_prices
units
media
supplier_profiles
supplier_types
supplier_supplier_type
supplier_service_areas

RFQ

rfqs
rfq_items
rfq_selected_suppliers
rfq_target_filters
rfq_supplier_queue
rfq_questions
rfq_deadline_extensions
rfq_change_logs

QUOTATION

quotations
quotation_items
rfq_shortlists
quotation_revision_requests
quotation_revisions
quotation_revision_items

AWARD

awards

PURCHASE ORDER

purchase_orders
purchase_order_items
purchase_order_status_history

SAVED

saved_items

MESSAGING

conversations
conversation_accounts
conversation_user_states
messages

NOTIFICATIONS

notifications

REVIEWS

reviews
review_replies
review_reports

SUPPORT

tickets
ticket_messages

ORGANIZATION

account_members
account_member_invitations
account_ownership_transfers
roles
permissions
role_has_permissions
model_has_roles
model_has_permissions
role_requests

ACCOUNT CONVERSION

account_conversion_requests
account_type_changes

SETTINGS

account_dashboard_preferences
settings
sessions
otp_codes
social_accounts

AUDIT

activity_log


Do not create new equivalents of these tables.


==========================================================
PART 21 — IMPORTANT: EXISTING FUNCTIONALITY
DO NOT REDEVELOP UNNECESSARILY
==========================================================

The following Buyer functionality is REPORTED as existing in the project.

Do not assume the list is still perfectly accurate after project changes.

For each item:
- inspect the actual code first;
- verify routes, Controllers/components, Services, Policies, Models, Blade views,
  database usage, and tests;
- if it exists and works, preserve and extend it;
- if it is missing, implement it according to `ARCHITECTURE.md`;
- if it uses legacy backend Livewire, do not extend that architecture; migrate
  the touched feature carefully when safe and within scope.

Do NOT rebuild working functionality from scratch:

- Buyer Dashboard master layout
- Header
- Sidebar
- Footer
- Buyer dashboard route
- Buyer capability-status banners
- Buyer onboarding profile
- Buyer onboarding review
- RFQ list
- RFQ search/filter
- RFQ create
- RFQ edit
- RFQ detail
- RFQ multi-item form
- RFQ Supplier targeting
- RFQ budget/delivery/deadline inputs
- RFQ publish
- RFQ cancel
- Received Quotations list
- Quotation detail
- Shortlist quotation
- Request quotation revision
- Reject quotation
- Basic quotation comparison
- Buyer Award creation
- Award list
- Award detail
- Buyer Purchase Order list
- Buyer Purchase Order detail
- PO item/total/history display
- Legacy Buyer confirm-receipt functionality may exist; inspect it, but do not extend it as a Phase 1 requirement
- Supplier directory
- Supplier search
- Supplier filtering
- Supplier profile
- Supplier listings
- Supplier reviews display
- Save Supplier
- Saved Suppliers page
- Save Product/Listing
- Saved Products page
- Message Supplier
- Buyer conversations
- Message read-state tracking
- 5-second message polling
- Buyer review submission
- Support Tickets
- Ticket threaded replies
- Ticket reopen behavior
- Buyer Profile editing
- Existing service classes
- Existing Policies
- Legacy backend Livewire Buyer code may exist; inspect behavior only and do not extend it
- Existing automated Buyer tests

The following pre-existing bug fixes must also be preserved:

1. Buyer/Supplier capability checks use capability_type_id and
   capabilityType->code.

   Do NOT query a removed account_capabilities.capability column.

2. SQLite tests must not be broken by MySQL-only functional-index migrations.

3. RFQ quotation comparison authorization belongs on the policy associated
   with the Rfq model when authorization is performed against an Rfq instance.

Do not undo those fixes.


==========================================================
FINAL INSTRUCTION
==========================================================

Do NOT start by rewriting the current Buyer Dashboard.

First inspect the existing project.

For every phase:

1. Read `ARCHITECTURE.md`.
2. Read `design.md`.
3. Inspect and identify what already exists.
4. Reuse valid existing routes, Controllers, Form Requests, Blade views,
   Actions, Services, Policies, Models, Middleware, and tests.
5. Identify only what is missing or conflicting.
6. Implement or extend only the required functionality.
7. Use the existing master dashboard layout.
8. Follow `design.md` for Tailwind/UI/color/text/layout conventions.
9. Do not use Livewire for backend Buyer development.
10. Do not use Filament.
11. Do not create duplicate database systems.
12. Preserve current working functionality.
13. Follow Phase 1 business rules even where the database supports future
    statuses/features.
14. Add/update tests for new or changed functionality.
15. Verify permissions, account isolation, status transitions, transaction
    safety, notifications, and idempotency where applicable.

The final objective is a COMPLETE Buyer Dashboard supporting the full
Edushopify Buyer lifecycle:

Marketplace
    ↓
RFQ
    ↓
Supplier Q&A
    ↓
Quotations
    ↓
Comparison / Shortlist / Revision
    ↓
Award
    ↓
Supplier Response
    ↓
Purchase Order Issued
    ↓
Transaction / Fulfilment Outside Platform
    ↓
Authorized Manual Completion
    ↓
Review

plus:

Saved Items
Messages
Notifications
Support
Buyer Profile
Locations
Organization Members
Invitations
Roles & Permissions
Role Requests
Ownership
Account Settings
Buyer/Supplier Mode
Individual → Organization Conversion
Account Closure

Build this as one coherent Buyer business system, not as disconnected pages.

FINAL BACKEND IMPLEMENTATION RULE:

Do not create backend Livewire components.

Use the portal-based HTTP/UI structure from `ARCHITECTURE.md`:

routes/backend/buyer.php
        ↓
Backend/Buyer Middleware Context
        ↓
Form Request
        ↓
app/Http/Controllers/Backend/Buyer/...
        ↓
Action / Service when needed
        ↓
Domain Models
        ↓
resources/views/backend/buyer/... or backend/shared/...

Shared Buyer/Supplier functionality must use the approved `Backend/Shared`
structure where `ARCHITECTURE.md` defines it.

All Buyer pages must follow `design.md`, including the centrally themeable
sidebar/menu/submenu color system.