# EduShopify Supplier Dashboard Workflow — `supplier_dashboard_workflow.md`

> **Status:** Mandatory Supplier backend workflow and functional specification.
>
> This document defines **what the Supplier Dashboard must do** and the Supplier-side business workflow.
>
> It must be used together with:
>
> 1. `docs/ai/ARCHITECTURE.md`
> 2. `docs/ai/design.md`
> 3. the current EduShopify database schema
> 4. `docs/ai/references/edushopify_dashboard_reference.html` when exact visual clarification is needed
>
> `ARCHITECTURE.md` controls code/folder architecture.
> `design.md` controls backend UI/UX, shared Blade components, Alpine usage, responsive behavior, sidebar/topbar, tables, forms, theme rules and accessibility.
> This file controls Supplier functionality, state transitions and business behavior.

==========================================================
SCOPE — SUPPLIER BACKEND DASHBOARD
==========================================================

EduShopify is an existing B2B education procurement marketplace.

This specification applies to the **Supplier backend dashboard**.

The existing Supplier backend implementation is NOT automatically the desired structure.
It may be refactored, reorganized, replaced or rebuilt as necessary to comply with:

- `ARCHITECTURE.md`
- `design.md`
- this Supplier workflow
- the current database schema and confirmed business rules

Reuse existing backend code only when it is compatible and useful.
Do not preserve poor/legacy backend structure merely because it already exists.

DO NOT make unrelated changes to:

- public frontend pages
- public frontend design
- registration UI
- registration flow
- existing authentication flow that already works

If a backend integration requires touching shared authentication/account context code,
make the smallest compatible change and do not redesign the registration experience.

==========================================================
MANDATORY IMPLEMENTATION RULES
==========================================================

Backend Supplier functionality must use the architecture defined in `ARCHITECTURE.md`.

Approved request flow:

HTTP Request
      ↓
Route
      ↓
Middleware / Account Context
      ↓
Form Request where appropriate
      ↓
Controller
      ↓
Action / Service only when business complexity justifies it
      ↓
Model / Database
      ↓
Blade / Redirect / Response

Do NOT introduce backend Livewire.
Do NOT use Filament.
Do NOT use Bootstrap or a second competing backend UI framework.

Use Laravel Blade for backend rendering.
Use Alpine.js/small JavaScript only for browser-side UI interaction according to `design.md`.

Do not duplicate design rules inside this workflow.
For repeated visual UI, reusable Blade components and Alpine behavior follow `design.md`.
The mandatory `h-20` topbar/sidebar-brand alignment and all mobile-responsive rules are also controlled by `design.md`.

==========================================================
DOCUMENT PRIORITY
==========================================================

When implementing Supplier backend functionality, use this order:

1. Current database schema and confirmed business rules
2. `ARCHITECTURE.md`
3. `design.md`
4. `supplier_dashboard_workflow.md`
5. `edushopify_dashboard_reference.html` for exact visual clarification
6. Existing implementation only where compatible

Do not silently guess when a genuine database/business-rule conflict could affect data integrity.
Minor implementation decisions should follow the documents above without unnecessary interruption.

==========================================================
DATABASE IS THE SOURCE OF TRUTH
==========================================================

The latest Edushopify database schema is the database source of truth.

Reuse existing tables.

DO NOT create duplicate tables.

DO NOT create another Supplier profile system.

DO NOT create another subscription system.

DO NOT create another listing/catalog system.

DO NOT create another quotation system.

DO NOT create another role/permission system.

Before proposing any migration:

1. inspect the current schema
2. inspect existing models
3. inspect migrations
4. inspect services
5. prove the current database cannot support the requirement

Only then propose a database change.

==========================================================
SUPPLIER IS A CAPABILITY
==========================================================

Supplier is a CAPABILITY.

Supplier is NOT a Spatie role.

An account may have:

- Buyer capability
- Supplier capability
- both

The current capability system uses:

account_capabilities
capability_types

Do NOT use any old/dropped:

account_capabilities.capability

column.

Capability must be resolved through:

capability_type_id
→ capability_types.code

==========================================================
AUTHORIZATION ARCHITECTURE
==========================================================

Spatie Permission Teams uses:

account_id

Before account-scoped permission checks, correctly set the current
Spatie account/team context.

Every protected Supplier action must effectively validate:

Authenticated User
        ↓
User Active
        ↓
Account Active
        ↓
Active Account Membership
        ↓
Active Supplier Capability where required
        ↓
Correct Spatie Account Context
        ↓
Required Permission
        ↓
Resource belongs to Supplier Account
        ↓
Subscription permits action where relevant
        ↓
Current workflow/status permits action

Never rely only on:

- hidden buttons
- disabled buttons
- navigation conditions
- frontend/UI state
- route visibility

Backend Policies/services must enforce authorization.

Prevent IDOR and cross-account access everywhere.

==========================================================
CRITICAL SUPPLIER BUSINESS RULES
==========================================================

Follow these rules throughout the Supplier Dashboard.

1. Supplier capability requires Admin approval.

2. Supplier application supports:
   - draft
   - pending
   - active
   - rejected
   - revision_required
   - suspended

3. Supplier capability application history must be preserved.

4. Required Supplier documents must be determined dynamically through
   the existing document type/capability configuration.

5. Supplier documents may be:
   - pending
   - verified
   - rejected

6. Document expiry/current-version behavior must be respected.

7. A pending Supplier may prepare/draft appropriate information and
   listings where current business rules allow.

8. A pending/unapproved Supplier must NOT publish marketplace listings,
   access protected RFQ opportunities, or submit quotations where
   Supplier approval is required.

9. Supplier must be approved BEFORE normal subscription activation/
   procurement participation according to existing business rules.

10. Do NOT automatically assign a free subscription after Supplier approval.

11. Supplier selects an eligible subscription plan.

12. Subscription may control:
   - listing limits
   - RFQ access
   - RFQ availability timing
   - team-member functionality
   - featured/homepage placement
   - other plan features stored in the existing plan/features structure

13. Global RFQ access may be delayed according to Supplier subscription.

14. Selected/invited Suppliers may receive RFQ access immediately subject
   to eligibility and business rules.

15. Supplier must not see Buyer-private RFQ data before eligibility permits it.

16. One Supplier account submits only one current quotation record per RFQ,
   with revisions preserved separately.

17. Partial quotations may be allowed when RFQ allows them.

18. Alternative items may be offered when RFQ allows them.

19. Quotation revisions must preserve history.

20. Supplier may Accept or Reject an Award before the response deadline.

21. Supplier rejection requires a reason.

22. Award acceptance creates one Purchase Order only.

23. Phase 1 Purchase Order flow is intentionally simplified.

24. Award acceptance creates the Purchase Order exactly once with initial status `issued`.

25. Do NOT activate `confirmed`, `in_progress`, `ready_for_delivery`, `delivered` or Buyer receipt-confirmation transitions as required Phase 1 workflow. Those database statuses may remain for compatibility/future phases.

26. Product/service transaction/payment/fulfilment takes place OUTSIDE Edushopify in Phase 1.

27. An authorized project completion action may move an eligible PO to `completed` according to the confirmed Phase 1 workflow. Supplier users must not invent or bypass that authorization.

28. Only Supplier subscription payment is platform-processed in Phase 1.

==========================================================
PART 1 — FINAL SUPPLIER DASHBOARD MENU STRUCTURE
==========================================================

Use this logical Supplier Dashboard structure.

SUPPLIER DASHBOARD

├── 1. Dashboard
│   └── Overview
│
├── 2. Business Profile
│   ├── Company Information
│   ├── Supplier Types
│   ├── Documents & Verification
│   ├── Locations & Service Areas
│   ├── Business Hours
│   ├── Gallery & Videos
│   └── Exhibitions
│
├── 3. Catalog
│   ├── Products
│   ├── Services
│   ├── Add Listing
│   ├── Brands
│   └── Category / Attribute Requests
│
├── 4. RFQ Opportunities
│   ├── Available RFQs
│   ├── Invited RFQs
│   ├── RFQ Detail
│   │   ├── Overview
│   │   ├── Items
│   │   ├── Questions & Answers
│   │   ├── RFQ Changes
│   │   └── My Quotation
│   └── My RFQ Questions
│
├── 5. Quotations
│   ├── My Quotations
│   ├── Draft Quotations
│   ├── Submitted Quotations
│   └── Revision Requests / History
│
├── 6. Awards
│   ├── All Awards
│   ├── Pending Response
│   └── Accepted / Rejected Awards
│
├── 7. Purchase Orders
│   ├── All Purchase Orders
│   └── Purchase Order Detail
│       ├── Items & Totals
│       ├── Order / Completion Status
│       ├── Status History
│       └── Support / Communication
│
├── 8. Subscription & Billing
│   ├── Subscription Plans
│   ├── Current Subscription
│   └── Payment History
│
├── 9. Communication
│   ├── Messages
│   ├── Contact Inquiries
│   ├── Notifications
│   └── Support Tickets
│
├── 10. Reviews
│   ├── Reviews Received
│   ├── Review Replies
│   └── Report Reviews
│
├── 11. Organization
│   ├── Members
│   ├── Invitations
│   ├── Roles & Permissions
│   ├── Role Requests
│   └── Ownership
│
└── 12. Settings & Account
    ├── Personal & Security
    ├── Dashboard / Buyer-Supplier Mode
    ├── Convert to Organization
    └── Close Account


IMPORTANT:

Do NOT convert every workflow into a sidebar item.

For example:

- Listing variants
- Listing pricing
- Listing attributes
- Listing change history

belong inside Listing Detail/Edit.

Similarly:

- quotation items
- quotation revisions
- Supplier award response
- PO status history

belong inside their relevant transaction detail pages.

==========================================================
PART 2 — DASHBOARD
==========================================================

MENU:

Dashboard
└── Overview

Purpose:

Provide the Supplier with immediate understanding of:

- account/capability status
- profile/verification status
- subscription status
- catalog activity
- RFQ opportunities
- quotation activity
- Awards requiring response
- Purchase Orders requiring action
- communication
- reviews
- support

Dashboard must support:

SUPPLIER ACCOUNT / CAPABILITY

- Supplier capability status
- pending/rejected/revision-required/suspended alerts
- Supplier profile completion
- missing required information
- required document status
- rejected documents
- expiring documents

SUBSCRIPTION

- current plan
- subscription status
- current period
- trial period where applicable
- expiration date
- renewal state
- past-due warning
- expired/cancelled/suspended warning
- plan limits/features summary

CATALOG

- total listings
- draft listings
- pending approval listings
- approved/published listings
- rejected listings
- products count
- services count

RFQ

- available RFQs
- newly available RFQs
- directly invited RFQs
- RFQs approaching quotation deadline
- RFQs where Supplier has asked questions
- Buyer answers received

QUOTATIONS

- draft quotations
- submitted quotations
- under-review quotations
- revision requests
- shortlisted quotations
- rejected quotations
- awarded quotations

AWARDS

- Awards awaiting Supplier response
- award response deadline
- accepted awards
- rejected awards

PURCHASE ORDERS

- newly issued POs
- issued/open POs requiring follow-up
- completed POs
- cancelled POs
- disputed/support-linked POs where applicable

COMMUNICATION

- unread messages
- new contact inquiries
- unread notifications
- open support tickets

REVIEWS

- rating
- review count
- new reviews
- reviews awaiting Supplier reply where appropriate

Add:

ACTION REQUIRED

Examples:

- Complete Supplier verification
- Upload 2 missing documents
- Document expires in 10 days
- Subscription expires soon
- 4 quotations due today
- 2 Buyers requested quotation revisions
- 1 Award requires response
- 3 issued POs require follow-up / authorized completion coordination

Add:

UPCOMING DEADLINES

Including:

- RFQ quotation deadlines
- RFQ Q&A deadlines
- quotation revision response deadlines
- Award response deadlines
- subscription expiry
- document expiry

Primary tables:

accounts
account_capabilities
capability_application_history
supplier_profiles
supplier_documents
document_types
document_type_enables
subscriptions
subscription_plans
listings
rfq_supplier_queue
rfq_selected_suppliers
rfq_questions
quotations
quotation_revision_requests
awards
purchase_orders
messages
contact_inquiries
notifications
tickets
reviews


==========================================================
PART 3 — BUSINESS PROFILE
==========================================================

MENU:

Business Profile
├── Company Information
├── Supplier Types
├── Documents & Verification
├── Locations & Service Areas
├── Business Hours
├── Gallery & Videos
└── Exhibitions


==========================================================
3.1 COMPANY INFORMATION
==========================================================

Implement complete Supplier profile management.

Supplier must be able to manage permitted fields including:

- display name
- legal name
- legal entity type
- company type
- contact person
- contact email
- contact phone
- WhatsApp
- support email
- country
- state
- city
- address
- website
- founded year
- employees
- logo
- banner
- profile photo
- company description
- social links

System-managed profile metrics such as:

- rating
- reviews count
- quotation response rate
- average response minutes

must NOT be freely editable by Supplier.

Primary table:

supplier_profiles

Related:

accounts
countries
states
cities


==========================================================
3.2 SUPPLIER TYPES
==========================================================

Use:

supplier_types
supplier_supplier_type

Supplier may have multiple Supplier types.

Support:

- view available Supplier types
- select allowed Supplier type(s)
- identify primary Supplier type
- change Supplier type according to approval rules
- prevent invalid/duplicate relationships

Examples may include Supplier business categories defined by existing
database records.

Do NOT hard-code type names when database records already exist.

Primary tables:

supplier_types
supplier_supplier_type


==========================================================
3.3 DOCUMENTS & VERIFICATION
==========================================================

This is critical.

Use:

document_types
document_type_enables
supplier_documents
account_capabilities
capability_application_history

Determine required Supplier documents dynamically.

Do NOT hard-code document requirements when:

document_type_enables

already defines which document types apply to the Supplier capability.

Supplier must be able to:

- view required documents
- view optional documents
- upload document
- replace current document
- see file metadata
- see upload date
- see verification status
- see rejection reason
- see expiry date
- upload replacement for expired/rejected document
- preserve old/non-current document history where business rules require
- identify current version
- resubmit Supplier capability application after revision-required state
  when permitted

Document statuses:

pending
verified
rejected

Support:

is_current
expires_at

Supplier capability application must support:

draft
pending
active
rejected
revision_required
suspended

Application history must preserve:

- attempt number
- submitted snapshot
- status
- Admin response
- review comment
- reviewed date

FLOW:

Supplier Capability Application
        ↓
Complete Profile
        ↓
Upload Required Documents
        ↓
Validate Required Data
        ↓
Submit
        ↓
Admin Review
        ↓
        ├── Approved → Supplier Active
        ├── Revision Required
        │       ↓
        │ Fix Profile/Documents
        │       ↓
        │ Resubmit
        │
        └── Rejected
                ↓
          Show Reason / Resubmit
          only if business rules permit

Do not allow Supplier verification to bypass Admin approval.


==========================================================
3.4 LOCATIONS & SERVICE AREAS
==========================================================

Distinguish:

PHYSICAL ACCOUNT LOCATIONS

from:

SUPPLIER SERVICE AREAS

Use:

account_locations

for physical locations such as:

- Head Office
- Registered Office
- Branch
- Warehouse
- Showroom

Use:

supplier_service_areas

for where the Supplier delivers/provides service.

Supplier service areas support:

country
state
city
radius

Supplier must be able to:

- add service area
- edit service area
- deactivate service area
- designate primary service area
- select country/state/city
- configure radius area where supported
- prevent invalid combinations
- use service areas for RFQ eligibility

Primary tables:

account_locations
supplier_service_areas
countries
states
cities


==========================================================
3.5 BUSINESS HOURS
==========================================================

Use:

business_hours

Supplier must be able to manage:

- day of week
- open/closed
- opening time
- closing time
- timezone
- location-specific hours where supported

Allow business hours by:

Supplier account
and optionally:
account location

Validate:

- valid time ranges
- no impossible duplicate conflicting hours


==========================================================
3.6 GALLERY & VIDEOS
==========================================================

Use:

supplier_gallery
supplier_videos
media

Supplier must be able to:

GALLERY

- add image
- caption
- alt text
- reorder
- activate/deactivate
- remove where permitted

VIDEOS

- add YouTube/Vimeo/other supported video
- title
- caption
- ordering
- activate/deactivate

Do not allow one Supplier to modify another Supplier's media.


==========================================================
3.7 EXHIBITIONS
==========================================================

Use:

exhibitions
exhibition_supplier

Supplier should be able to view/manage Supplier association with relevant
exhibitions according to existing platform rules.

Do not automatically allow Supplier to create global exhibition records
unless existing permissions/business rules allow it.

Where association is Supplier-manageable support:

- view available exhibition
- request/add participation
- view participation status/data

Reuse existing relationships.


==========================================================
PART 4 — CATALOG
==========================================================

MENU:

Catalog
├── Products
├── Services
├── Add Listing
├── Brands
└── Category / Attribute Requests


==========================================================
4.1 LISTING CORE RULES
==========================================================

Use:

listings

A listing belongs to:

supplier_account_id

Listing type:

product
service

Listing approval states:

draft
pending
approved
rejected

Supplier must only manipulate listings belonging to their own account.

Support:

- listing number
- listing name
- slug
- SKU
- main category
- brand
- short description
- full description
- pricing type
- sales mode
- base price
- compare-at price
- currency
- MOQ
- unit
- extra specifications
- active state
- approval state
- rejection reason
- publish state

Pricing types include current database-supported types such as:

fixed
quote_only
rfq_enabled

Sales modes include existing database-supported values.

Follow Phase 1 platform rules.

Do not accidentally introduce direct marketplace checkout if it is not
part of the current platform phase.


==========================================================
4.2 PRODUCTS
==========================================================

Supplier must be able to:

- list own products
- search own products
- filter status
- filter category
- filter brand
- filter active/inactive
- create product
- edit draft/rejected product
- view approved product
- duplicate listing only if current business rules allow
- deactivate listing
- submit listing for approval
- see rejection reason
- revise rejected listing
- resubmit
- view public listing status

Product Detail/Edit must support:

- core listing information
- categories
- attributes
- variants
- pricing
- tier pricing
- product-specific details
- media
- change history

Use:

listings
product_details
listing_categories
listing_attribute_values
listing_variants
listing_variant_attributes
listing_tier_prices
media
listing_change_logs


PRODUCT DETAILS

Support:

- simple product
- variable product
- stock status
- stock quantity
- weight
- weight unit
- lead time
- warranty
- support terms


==========================================================
4.3 SERVICES
==========================================================

Supplier must be able to:

- list own services
- search
- filter approval/status
- create service
- edit draft/rejected service
- submit for approval
- view rejection reason
- revise and resubmit
- activate/deactivate approved service

Service listing must use:

listings
service_details
listing_categories
listing_attribute_values
media
listing_change_logs

Support all fields represented in the current service_details schema.

Do not force product-only fields into service listings.


==========================================================
4.4 ADD LISTING
==========================================================

FLOW:

Add Listing
     ↓
Choose:
Product OR Service
     ↓
Basic Information
     ↓
Category
     ↓
Brand
     ↓
Attributes
     ↓
Product/Service Specific Data
     ↓
Variants if applicable
     ↓
Pricing / MOQ / Unit
     ↓
Tier Pricing where applicable
     ↓
Media
     ↓
Save Draft
     ↓
Submit for Approval
     ↓
pending
     ↓
Admin Review
     ↓
     ├── approved
     │      ↓
     │ Publish / Active
     │
     └── rejected
            ↓
       Rejection Reason
            ↓
       Revise / Resubmit


Supplier capability/subscription restrictions must be applied.

A Supplier that is allowed to DRAFT but not publish must still be blocked
from public publication until requirements are satisfied.


==========================================================
4.5 CATEGORIES & ATTRIBUTES
==========================================================

Use existing platform taxonomies:

categories
attributes
attribute_values
category_attribute

Supplier should use approved global taxonomy when available.

When required category/attribute does not exist, use the existing
suggestion workflow rather than allowing arbitrary uncontrolled taxonomy.

Use:

category_suggestions
attribute_suggestions

Supplier may:

- submit category suggestion
- submit attribute suggestion
- explain reason
- see pending status
- withdraw when allowed
- see approved/rejected result
- see Admin review comment
- use resulting category/attribute after approval

Statuses include:

pending
approved
rejected
withdrawn


==========================================================
4.6 BRANDS
==========================================================

Use:

brands

Brands may be:

global
supplier-owned

Supplier should be able to:

- select approved global brand
- view own Supplier brands
- propose/create Supplier brand where existing business rules allow
- edit permitted Supplier-owned brand before approval
- see approval status
- see rejection reason
- use brand on listings after valid approval/state

Do not allow Supplier to edit global brands.

Respect:

approval_status
reviewed_by_user_id
reviewed_at
is_active


==========================================================
4.7 LISTING ATTRIBUTES
==========================================================

Use:

attributes
attribute_values
category_attribute
listing_attribute_values

Load attributes based on listing category.

Respect:

- required
- filterable
- variant
- sort order

Supplier must provide required values before submission where appropriate.


==========================================================
4.8 PRODUCT VARIANTS
==========================================================

Use:

listing_variants
listing_variant_attributes

For variable products support:

- variant SKU
- variant attribute combinations
- variant price/price adjustment according to existing schema
- stock data according to schema
- active/inactive state
- appropriate media/reference if existing model supports it

Prevent duplicate/invalid variant combinations.


==========================================================
4.9 TIER PRICING
==========================================================

Use:

listing_tier_prices

Supplier must be able to create quantity-based pricing according to
current schema.

Validate:

- quantity ranges
- price
- no invalid overlaps where prohibited
- listing ownership

==========================================================
4.10 LISTING CHANGE HISTORY
==========================================================

Use:

listing_change_logs

Preserve significant listing change history according to current business
rules.

Do not destroy historical audit information.

Especially important for changes to:

- price
- product/service definition
- availability
- major specifications
- approval-sensitive fields


==========================================================
PART 5 — RFQ OPPORTUNITIES
==========================================================

MENU:

RFQ Opportunities
├── Available RFQs
├── Invited RFQs
├── RFQ Detail
└── My RFQ Questions


==========================================================
5.1 RFQ ACCESS / ELIGIBILITY
==========================================================

RFQ access is NOT simply:

SELECT * FROM rfqs WHERE status = open

Use the existing eligibility architecture.

Relevant:

rfq_public_summary
rfq_supplier_queue
rfq_selected_suppliers
rfq_target_filters
subscriptions
subscription_plans
supplier_profiles
supplier_service_areas

Supplier RFQ access must consider:

- Supplier capability active
- account active
- membership active
- permission
- subscription active/trialing where required
- subscription plan
- RFQ visibility
- selected Supplier invitation
- Supplier targeting eligibility
- RFQ queue/release timing
- RFQ status
- quotation deadline

Global RFQs may have subscription-based availability delay.

Selected Suppliers may receive immediate access subject to eligibility.

Do not bypass:

rfq_supplier_queue

where it is the designed eligibility/release source.


==========================================================
5.2 AVAILABLE RFQs
==========================================================

Supplier must be able to:

- view eligible available RFQs
- search RFQ
- filter category
- filter product/service
- filter delivery location
- filter deadline
- filter quotation state
- sort by newest/deadline/relevance where supported
- open RFQ Detail

Before full eligibility, only public summary information should be exposed
where applicable.

Do not leak Buyer-private RFQ data to an ineligible Supplier.


==========================================================
5.3 INVITED RFQs
==========================================================

Use:

rfq_selected_suppliers

Show RFQs where the current Supplier account was explicitly selected/
invited.

Supplier should see:

- invitation
- RFQ
- deadline
- status
- whether quotation already exists
- whether Supplier needs action


==========================================================
5.4 RFQ DETAIL
==========================================================

Eligible Supplier must be able to view:

- RFQ number
- title
- description
- current RFQ version
- items
- specifications
- quantities
- units
- delivery destination
- budget only where business rules expose it
- expected delivery
- quotation deadline
- Q&A deadline
- partial quotation rule
- alternative product rule
- RFQ change information relevant to Supplier
- Buyer information permitted by platform rules
- own quotation status

Primary:

rfqs
rfq_items
rfq_change_logs
rfq_deadline_extensions


==========================================================
5.5 SUPPLIER RFQ QUESTIONS
==========================================================

Use:

rfq_questions

Supplier must be able to:

- ask question about eligible RFQ
- respect Q&A deadline
- see own questions
- see Buyer answers
- see public questions/answers where business rules allow
- see question status
- receive notification when Buyer answers

Question statuses:

pending
answered
hidden

Supplier must NOT:

- answer their own question as Buyer
- modify Buyer answer
- access private questions from another Supplier unless business rules
  intentionally make them public


FLOW:

Supplier Opens RFQ
      ↓
Needs Clarification
      ↓
Ask Question
      ↓
rfq_questions
      ↓
Buyer Notification
      ↓
Buyer Answers
      ↓
Supplier Notification
      ↓
Supplier Reviews Answer


==========================================================
5.6 RFQ CHANGES
==========================================================

When Buyer changes a published RFQ:

Supplier must be able to see relevant change information.

Use:

rfq_change_logs

Show:

- previous RFQ version
- new version
- minor/major
- changed fields where appropriate
- change date
- whether quotation revision is required

If Supplier already submitted quotation against an older RFQ version:

clearly identify:

Quotation submitted against RFQ vX

Current RFQ version = vY

If revision is required:

Supplier should receive appropriate action/notification.


==========================================================
PART 6 — QUOTATIONS
==========================================================

MENU:

Quotations
├── My Quotations
├── Draft Quotations
├── Submitted Quotations
└── Revision Requests / History


==========================================================
6.1 CREATE QUOTATION
==========================================================

Supplier creates quotation from an eligible RFQ.

Use:

quotations
quotation_items

Quotation must preserve:

rfq_version_no

at time of submission.

Supplier quotation supports:

- RFQ
- Supplier account
- quotation number
- current revision number
- subtotal
- tax
- discount
- shipping
- grand total
- currency
- lead time
- valid until
- warranty
- support terms
- payment terms
- proposal

Quotation items support:

- RFQ item reference
- offered listing
- offered variant
- alternative item flag
- item name
- description
- quantity
- unit
- custom unit
- unit price
- tax
- discount
- line total

Respect RFQ:

allow_partial_quotation

and:

allow_alternative_products


==========================================================
6.2 QUOTATION FLOW
==========================================================

Eligible RFQ
      ↓
Create Quotation
      ↓
Draft
      ↓
Add Quotation Items
      ↓
Price / Tax / Discount
      ↓
Shipping
      ↓
Lead Time
      ↓
Validity
      ↓
Warranty / Support
      ↓
Payment Terms
      ↓
Proposal
      ↓
Validate RFQ Rules
      ↓
Submit Before Deadline
      ↓
Buyer Receives Quotation


==========================================================
6.3 QUOTATION VALIDATION
==========================================================

Before submission verify:

- active User
- active Account
- active Supplier membership
- Supplier capability active
- subscription eligibility
- required permission
- RFQ eligibility
- RFQ open/quoteable
- quotation deadline not passed
- no conflicting submitted quotation record
- RFQ version is current/known
- required RFQ item coverage
- partial quotation allowed if not all items quoted
- alternatives permitted if alternative item used
- commercial totals valid
- currency rules satisfied


==========================================================
6.4 MY QUOTATIONS
==========================================================

Supplier must be able to:

- view all own quotations
- search
- filter by RFQ
- filter by status
- filter by date
- sort
- view Buyer/RFQ
- see RFQ version
- see quotation revision
- see grand total
- see status
- see submitted date
- see Buyer-viewed status where useful
- open quotation detail

Quotation statuses include:

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


==========================================================
6.5 DRAFT QUOTATIONS
==========================================================

Supplier may:

- create draft
- edit draft
- add/remove quotation items
- recalculate totals
- delete draft where appropriate
- submit when valid

Drafts must remain private from Buyer until submitted.


==========================================================
6.6 SUBMITTED QUOTATIONS
==========================================================

Supplier may:

- view submitted quotation
- see Buyer viewed state where available
- see status
- see shortlist state
- see rejection comment
- see Award state
- withdraw quotation where current business rules permit

Withdrawal must:

- validate quotation status
- validate deadline/workflow
- preserve history
- store withdrawal timestamp
- notify Buyer where appropriate

Do not physically delete submitted commercial records.


==========================================================
6.7 QUOTATION REVISION REQUEST
==========================================================

Use:

quotation_revision_requests

Buyer may request changes.

Supplier must be able to:

- see requested changes
- see requested fields
- see response deadline
- accept revision request
- decline revision request
- provide response
- submit revised quotation
- see request status

Statuses:

pending
accepted
revised
declined
expired
cancelled


FLOW:

Buyer Requests Revision
      ↓
Supplier Notification
      ↓
Supplier Opens Request
      ↓
      ├── Decline
      │      ↓
      │ Store Supplier Response
      │
      └── Accept
             ↓
       Create New Revision
             ↓
       Edit Commercial Terms
             ↓
       Submit Revision
             ↓
       Buyer Notification


==========================================================
6.8 QUOTATION REVISION HISTORY
==========================================================

Use:

quotation_revisions
quotation_revision_items

Never overwrite historical revisions.

Every submitted revision must preserve:

- revision number
- totals
- terms
- items
- offered products/variants
- RFQ version
- creator account/user
- timestamps

Supplier must be able to view:

Revision 1
Revision 2
Revision 3
...

and identify:

CURRENT REVISION

Historical revisions should become immutable once submitted.


==========================================================
PART 7 — AWARDS
==========================================================

MENU:

Awards
├── All Awards
├── Pending Response
└── Accepted / Rejected Awards


==========================================================
7.1 AWARD RECEIPT
==========================================================

When Buyer awards Supplier's quotation:

Supplier receives:

- Award
- Award number
- RFQ
- quotation
- award note
- response deadline
- current status

Use:

awards
notifications


==========================================================
7.2 ACCEPT AWARD
==========================================================

Supplier may accept only when:

- Award belongs to Supplier account
- status = pending_supplier_response
- response deadline valid
- Supplier capability/account valid
- user has required permission
- Award has not already been responded to

On Accept:

- status = accepted
- responded_at
- accepted_at
- Supplier response note if provided
- notify Buyer
- create Purchase Order EXACTLY ONCE

Use database transaction.

Revalidate Award inside transaction.

Use locking/idempotency.

Do not create duplicate Purchase Orders if:

- Supplier double clicks
- duplicate HTTP request
- network request repeats
- queued action retries


==========================================================
7.3 REJECT AWARD
==========================================================

Supplier may reject Award.

Rejection requires:

supplier_rejection_reason

Optionally use Supplier response note as supported.

On rejection:

- status = rejected_by_supplier
- responded_at
- rejected_at
- store rejection reason
- notify Buyer

Buyer may then re-award another quotation.

Supplier cannot alter Award after final rejection unless explicit Admin
workflow allows it.


==========================================================
PART 8 — PURCHASE ORDERS — PHASE 1
==========================================================

MENU:

Purchase Orders
├── All Purchase Orders
└── Purchase Order Detail
    ├── Items & Totals
    ├── Order / Completion Status
    ├── Status History
    └── Support / Communication


==========================================================
8.1 PURCHASE ORDER CREATION DEPENDENCY
==========================================================

Supplier does NOT manually create the Purchase Order.

The Phase 1 dependency is:

Buyer creates Award
      ↓
Supplier accepts valid Award
      ↓
Award = accepted
      ↓
Create Purchase Order EXACTLY ONCE
      ↓
PO status = issued

Use existing:

awards
purchase_orders
purchase_order_items
purchase_order_status_history where appropriate

Purchase Order creation must remain transactional and idempotent.

Duplicate Supplier Award acceptance must NOT create duplicate POs.


==========================================================
8.2 PURCHASE ORDER ACCESS
==========================================================

Supplier must only see Purchase Orders where:

purchase_orders.supplier_account_id

matches the current Supplier account.

Also enforce:

- authenticated active user
- active account
- active membership
- Supplier capability as required
- correct Spatie account/team context
- required permission
- resource ownership
- current workflow/status

Cross-account PO access must be blocked even when a URL/route ID is modified.


==========================================================
8.3 PURCHASE ORDER LIST
==========================================================

Supplier must be able to:

- view all owned Purchase Orders
- search
- filter by status
- filter by Buyer where supported
- filter by date
- sort
- paginate
- view PO number
- Buyer
- grand total
- currency
- current status
- payment tracking status
- open PO Detail

Use:

purchase_orders

Do not load another Supplier account's PO records.


==========================================================
8.4 PURCHASE ORDER DETAIL
==========================================================

Supplier must see permitted transactional/snapshot information including:

- PO number
- RFQ
- quotation
- Award
- Buyer
- items
- quantities
- unit prices
- subtotal
- tax
- discount
- shipping
- grand total
- currency
- payment method/note where stored
- payment status
- issued date
- current PO status
- available status/history records

Use:

purchase_orders
purchase_order_items
purchase_order_status_history

Commercial snapshot/history values must not be silently recalculated from later-edited source records.


==========================================================
8.5 PHASE 1 STATUS FLOW — MANDATORY
==========================================================

For current Phase 1 implementation use:

Award Accepted
      ↓
Purchase Order Created Exactly Once
      ↓
issued
      ↓
Product / Service Transaction & Fulfilment Occur Outside Platform
      ↓
Authorized Completion Action
      ↓
completed

The database may contain additional status values such as:

confirmed
in_progress
ready_for_delivery
delivered

These values are retained for compatibility/future phases/legacy data.

DO NOT build or activate a new Phase 1 Supplier workflow that requires:

issued
  ↓
confirmed
  ↓
in_progress
  ↓
ready_for_delivery
  ↓
delivered
  ↓
Buyer receipt confirmation

Supplier Dashboard may display legacy/current stored values safely when such records already exist,
but it must not expose new transition buttons into those extended states for Phase 1.

Do not require Buyer receipt confirmation as a Phase 1 completion dependency.


==========================================================
8.6 SUPPLIER ACTIONS ON PHASE 1 PURCHASE ORDER
==========================================================

Supplier may, subject to permission and current state:

- view the issued/completed/cancelled/disputed PO
- view items and commercial snapshot
- view payment tracking information
- message Buyer in the proper business context
- open/reply to linked Support Ticket where appropriate
- view status/history
- provide supporting information/documents where the current architecture permits

Supplier must NOT:

- arbitrarily mark PO `confirmed`
- arbitrarily mark PO `in_progress`
- arbitrarily mark PO `ready_for_delivery`
- arbitrarily mark PO `delivered`
- bypass the authorized completion rule
- create a second PO

If future phases activate extended fulfilment states, that must be introduced through a separate confirmed business-rule update rather than inferred from the existing enum values.


==========================================================
8.7 AUTHORIZED COMPLETION
==========================================================

An eligible PO may be moved to `completed` only through the authorized Phase 1 completion workflow defined by the project.

Supplier-side UI must not assume that every Supplier member may complete a PO.

Always validate:

- active user/account/membership
- permission
- current account/team context
- resource ownership/participation
- current PO status
- allowed completion actor/rule
- duplicate submission/idempotency

Where completion changes multiple records/history, use a database transaction and re-check the state inside the transaction.

Create/update PO history/audit information where the current schema/workflow requires it.


==========================================================
8.8 CANCELLED / DISPUTED / SUPPORT
==========================================================

Respect:

cancelled
disputed

Supplier should be able to:

- see cancellation reason where stored
- see dispute/support state
- communicate with Buyer
- open/reply to Support Ticket
- provide information required for issue resolution

Do not create a second dispute system.

Use existing:

tickets
ticket_messages
messages
conversations

where appropriate.

Product/service transaction disputes are handled through Support Tickets in Phase 1.


==========================================================
8.9 PAYMENT TRACKING
==========================================================

Product/service transaction payment is OFF-PLATFORM in Phase 1.

Supplier may see, where stored/authorized:

payment_method_note
payment_status

Payment status is tracking information only.

Do NOT build for Phase 1:

- product/service marketplace checkout
- escrow
- Supplier marketplace wallet
- commission ledger
- product Stripe checkout
- marketplace Supplier payout engine
- marketplace refund engine

Supplier subscription billing is separate and remains platform-managed according to Part 9.


==========================================================
PART 9 — SUBSCRIPTION & BILLING
==========================================================

MENU:

Subscription & Billing
├── Subscription Plans
├── Current Subscription
└── Payment History


==========================================================
9.1 SUBSCRIPTION PRECONDITIONS
==========================================================

Supplier subscription is a first-class business requirement.

Follow:

Supplier Application
      ↓
Admin Approval
      ↓
Supplier Capability Active
      ↓
Choose Subscription
      ↓
Payment/Activation
      ↓
Supplier Marketplace Access

Do NOT automatically activate a free plan merely because Supplier is approved.

Supplier must explicitly select an eligible plan according to business rules.


==========================================================
9.2 SUBSCRIPTION PLANS
==========================================================

Use:

subscription_plans

Supplier must be able to:

- view active plans
- compare plan name
- pricing
- billing interval where represented
- listing limits/features
- RFQ-related features
- team-member feature where applicable
- featured/homepage placement features
- other existing plan features
- identify featured plan
- identify free plan if actually available
- choose eligible plan

Use actual database fields/features.

Do not hard-code plans into frontend.


==========================================================
9.3 CURRENT SUBSCRIPTION
==========================================================

Use:

subscriptions

Show:

- plan
- provider
- plan snapshot
- price snapshot
- features snapshot
- status
- starts date
- trial end
- current period
- expiration
- auto-renew
- cancellation status/reason

Statuses:

pending
trialing
active
past_due
expired
cancelled
suspended

Use snapshot fields for historical accuracy.

Do not recalculate old subscription commercial terms from a currently
edited plan.


==========================================================
9.4 SUBSCRIPTION STATUS RULES
==========================================================

TRIALING / ACTIVE

Allow plan-entitled Supplier functionality.

PENDING

Do not grant paid subscription benefits prematurely.

PAST_DUE

Restrict according to platform business rules.

EXPIRED

Restrict subscription-dependent features.

CANCELLED

Respect cancellation effective dates/current period according to rules.

SUSPENDED

Restrict Supplier subscription-dependent functionality.

Supplier capability and subscription are DIFFERENT.

Example:

Supplier capability = active

does NOT automatically mean:

Subscription = active


==========================================================
9.5 PAYMENT HISTORY
==========================================================

Use:

subscription_payments

Supplier must be able to see:

- payment
- subscription
- amount
- currency
- provider
- payment status
- provider invoice reference where stored
- payment date
- failure reason where appropriate
- refund status where applicable

Statuses include:

pending
paid
failed
refunded
partially_refunded
cancelled

Do not invent a second payment history table.


==========================================================
PART 10 — COMMUNICATION
==========================================================

MENU:

Communication
├── Messages
├── Contact Inquiries
├── Notifications
└── Support Tickets


==========================================================
10.1 MESSAGES
==========================================================

Use existing messaging infrastructure:

conversations
conversation_accounts
conversation_user_states
messages

Supplier must be able to:

- receive Buyer conversation
- start/reply to Buyer conversation where permitted
- view conversation list
- open conversation
- send text
- receive text
- maintain read/unread state
- handle context-based conversations
- use existing polling architecture
- use attachments/images/system messages when current architecture supports
- archive/mute according to supported functionality

Conversation contexts may include:

- listing
- RFQ
- quotation
- Purchase Order
- general
- support

Do not create duplicate Supplier/Buyer threads unnecessarily.

Maintain strict participant authorization.


==========================================================
10.2 CONTACT INQUIRIES
==========================================================

Use:

contact_inquiries

This represents public/marketplace inquiries associated with Supplier
or listing.

Supplier should be able to:

- list inquiries belonging to Supplier/listing
- identify new inquiries
- open inquiry
- mark read
- record/reply according to current implementation
- mark replied
- close
- identify spam where business rules allow
- see relevant listing

Statuses:

new
read
replied
spam
closed

Do not expose inquiries for other Suppliers.


==========================================================
10.3 NOTIFICATIONS
==========================================================

Use:

notifications

Do NOT create another notification system.

Supplier notification center should support:

- notification bell
- unread count
- all notifications
- unread filter
- mark read
- mark all read
- deep-link to relevant resource

Notify Supplier for relevant events.


SUPPLIER CAPABILITY

- submitted
- approved
- rejected
- revision required
- suspended


DOCUMENTS

- document rejected
- verification complete
- document approaching expiry where implemented


SUBSCRIPTION

- activated
- payment success
- payment failed
- past due
- upcoming expiry
- expired
- cancelled
- suspended


LISTINGS

- listing submitted
- approved
- rejected
- category/attribute suggestion result
- brand approval result


RFQ

- selected/invited RFQ
- global RFQ becomes available
- Buyer answers Supplier question
- RFQ changed
- quotation deadline approaching
- RFQ cancelled


QUOTATION

- Buyer viewed where notification is useful
- shortlisted
- revision requested
- rejected
- Awarded


AWARD

- Award received
- response deadline approaching


PURCHASE ORDER

- PO created
- Buyer status/confirmation events
- dispute/cancellation events


MESSAGES

- new message


REVIEWS

- new published review
- reply moderation result if applicable


SUPPORT

- ticket reply
- ticket status change


ORGANIZATION

- invitation/member/role/ownership events


Prevent duplicate notifications on retries.


==========================================================
10.4 SUPPORT TICKETS
==========================================================

Use:

tickets
ticket_messages

Supplier must be able to:

- create ticket
- list own/account tickets
- open ticket
- reply
- upload attachment if supported
- view priority
- view status
- reopen according to rules
- link ticket to business resource

Relevant Supplier ticket subjects may involve:

- account/capability
- verification/document
- subscription/payment
- listing
- RFQ
- quotation
- Award
- PO/dispute
- review
- general issue

Never expose Admin-only internal notes.


==========================================================
PART 11 — REVIEWS
==========================================================

MENU:

Reviews
├── Reviews Received
├── Review Replies
└── Report Reviews


==========================================================
11.1 REVIEWS RECEIVED
==========================================================

Use:

reviews

Supplier must be able to see reviews where:

supplier_account_id = current Supplier account

Support review contexts:

quotation_experience
purchase_experience

Supplier may view:

- rating
- title
- comment
- review context
- relevant RFQ/quotation/PO where permitted
- status
- publication date
- Buyer identity according to privacy rules

Only appropriately published/visible reviews should affect public profile
according to moderation rules.


==========================================================
11.2 REVIEW REPLY
==========================================================

Use:

review_replies

Supplier may:

- reply to eligible review
- edit reply only if current rules allow
- see reply status
- see moderation result if reply is moderated

Only ONE Supplier reply per review/Supplier relationship should exist
where database uniqueness enforces it.

Do not allow Supplier to reply to another Supplier's review.


==========================================================
11.3 REPORT REVIEW
==========================================================

Use:

review_reports

Supplier may report a review according to platform policy.

Require:

- reason
- optional details

Supplier can see report status where appropriate.

Admin handles moderation/review.

Do not allow duplicate report abuse where database/business rules prevent it.


==========================================================
PART 12 — ORGANIZATION
==========================================================

MENU:

Organization
├── Members
├── Invitations
├── Roles & Permissions
├── Role Requests
└── Ownership

This area is available where account type/plan/permissions allow it.


==========================================================
12.1 MEMBERS
==========================================================

Use:

account_members

Organization Supplier must be able to:

- view members
- view owners
- view primary owner
- see status
- see assigned roles
- activate
- deactivate
- suspend
- remove

according to permission/business rules.

Statuses:

invited
active
inactive
suspended
removed

One user must not belong to multiple marketplace accounts contrary to
existing account architecture.


==========================================================
12.2 SUBSCRIPTION TEAM LIMIT
==========================================================

If the selected subscription plan limits or enables team members:

enforce that plan entitlement when inviting/activating team members.

Do not rely only on UI.

Service/backend logic must validate subscription feature/limit.

Do not delete existing members automatically if plan changes unless
explicit business rules define that behavior.


==========================================================
12.3 INVITATIONS
==========================================================

Use:

account_member_invitations

Support:

- invite member
- invitation mode
- pending invitations
- resend
- cancel
- expiry
- acceptance
- membership creation
- role assignment

Existing modes include:

owner_prefilled
employee_self_complete

Before acceptance verify that user is eligible to join this account.


==========================================================
12.4 ROLES & PERMISSIONS
==========================================================

Use existing Spatie Permission Teams.

Do NOT build a second RBAC system.

Supplier is a capability, not a role.

Typical Supplier-side responsibilities may include:

- Supplier Manager
- Product Manager
- Quotation Manager
- Finance Manager
- Viewer

but use actual existing project roles where available.

Do not hard-code duplicates.

Support:

- view roles
- assign role
- remove role
- assign/use approved account-specific custom roles where allowed
- sync allowed permissions only within the approved/assignable permission catalogue
- enforce capability_scope
- enforce is_sensitive
- enforce is_owner_only
- enforce is_assignable
- enforce is_active

Possible functional separation:

Product Manager
→ listings/catalog

Quotation Manager
→ RFQs/quotations

Supplier Manager
→ broader Supplier operations

Finance Manager
→ subscription/payment/PO financial tracking

Viewer
→ read-only

Missing permission = deny.


==========================================================
12.5 ROLE REQUESTS
==========================================================

Use:

role_requests


Custom Supplier organization roles require the approved role-request workflow.

A Supplier organization must NOT directly create a live account-specific Spatie role from the Supplier portal.

Required flow:

Organization Needs Custom Role
      ↓
Create role_requests
      ↓
pending
      ↓
Admin Review
      ↓
      ├── rejected / cancelled → no live role
      └── approved
              ↓
         Create/sync approved account-scoped role exactly once

Only permissions approved for the account/capability scope may be attached.
Platform/Admin, owner-only, sensitive, inactive or non-assignable permissions must not be escalated through forged requests.

Support where Admin review is required:

- request custom role
- requested permission set
- description
- capability scope
- pending state
- cancellation
- approval
- rejection
- Admin review comment


==========================================================
12.6 OWNERSHIP
==========================================================

Use:

account_ownership_transfers

Support:

- primary owner
- co-owner where applicable
- ownership transfer
- target owner
- accept
- reject
- cancel
- complete
- reason
- history

Rules:

- Supplier organization must never end with zero owners
- primary owner cannot simply leave without valid transfer
- last owner cannot remove themselves
- use transactions
- preserve audit trail


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

Separate:

USER PERSONAL INFORMATION

from:

SUPPLIER BUSINESS PROFILE

Support existing account-user features:

- name
- email
- phone
- password
- email verification
- phone verification if supported
- language
- currency
- sessions
- OTP/security
- connected social accounts

Use existing:

users
otp_codes
sessions
social_accounts
languages
currencies
settings


==========================================================
13.2 BUYER / SUPPLIER MODE
==========================================================

Use:

account_dashboard_preferences

If the same account has:

Buyer capability = active
AND
Supplier capability = active

provide:

BUYER ↔ SUPPLIER

dashboard mode switching.

Support:

default_mode

Changing dashboard mode does NOT merge permissions.

Example:

Supplier mode

does not grant:

supplier.listing.create

if current user lacks that permission.

Authorization continues to depend on:

capability
membership
role
permission
resource ownership


==========================================================
13.3 INDIVIDUAL → ORGANIZATION CONVERSION
==========================================================

Use:

account_conversion_requests
account_type_changes

Do NOT create another account.

Flow:

Individual Supplier
      ↓
Convert to Organization
      ↓
Proposed Organization Information
      ↓
Documents
      ↓
Draft
      ↓
Submit
      ↓
Admin Review
      ↓
      ├── Revision Required
      ├── Rejected
      └── Approved
             ↓
      SAME account becomes organization


Important Supplier rule:

If an already-approved Supplier converts from Individual to Organization,
respect the business rule requiring Supplier capability review/reapproval
where applicable.

Do not silently retain public Supplier approval if legal identity changes
require re-verification.


==========================================================
13.4 CLOSE ACCOUNT
==========================================================

Implement controlled account closure.

Before closure inspect dependencies:

- active subscription
- published listings
- submitted quotations
- pending Awards
- active Purchase Orders
- disputes
- open Support Tickets
- organization ownership dependencies

Do not physically delete transactional history.

Preserve:

- RFQs relevant to Supplier
- quotations
- Award history
- Purchase Orders
- reviews
- subscription/payment history
- messages
- tickets
- audit records


==========================================================
PART 14 — COMPLETE SUPPLIER BUSINESS WORKFLOW
==========================================================

The completed Supplier journey must support:

Supplier Registration
      ↓
Create Account
      ↓
Apply for Supplier Capability
      ↓
Complete Supplier Profile
      ↓
Select Supplier Types
      ↓
Upload Required Documents
      ↓
Submit Supplier Application
      ↓
Admin Review
      ↓
      ├── Revision Required
      │      ↓
      │ Correct Information
      │      ↓
      │ Resubmit
      │
      ├── Rejected
      │
      └── Approved
             ↓
      Supplier Capability Active
             ↓
      Select Subscription Plan
             ↓
      Subscription Active
             ↓
      Create Products / Services
             ↓
      Submit Listings
             ↓
      Admin Approval
             ↓
      Listings Published
             ↓
      Receive RFQ Opportunities
             ↓
      View Eligible RFQ
             ↓
      Ask Buyer Question if Required
             ↓
      Buyer Answers
             ↓
      Create Quotation
             ↓
      Submit Quotation
             ↓
      Buyer Reviews
             ↓
      Buyer May:
          ├── Shortlist
          ├── Request Revision
          ├── Reject
          └── Award
             ↓
      If Revision Requested
             ↓
      Supplier Revises Quote
             ↓
      Buyer Reviews Again
             ↓
      Award Received
             ↓
      Supplier Accepts / Rejects
             ↓
             ├── REJECT
             │      ↓
             │ Store Reason
             │      ↓
             │ Buyer May Re-award
             │
             └── ACCEPT
                    ↓
               Purchase Order Created Exactly Once
                    ↓
               PO status = issued
                    ↓
               Product / Service Transaction & Fulfilment Outside Platform (Phase 1)
                    ↓
               Authorized Completion
                    ↓
               PO status = completed
                    ↓
               Buyer May Review Supplier
                    ↓
               Supplier May Reply to Review


Throughout:

Supplier ↔ Buyer Messages

Supplier ↔ Support

Supplier Subscription controls entitled access/features.


==========================================================
PART 15 — STATUS-AWARE SUPPLIER FUNCTIONALITY
==========================================================

Never expose an action merely because its button/page exists.


SUPPLIER CAPABILITY

DRAFT
- complete Supplier profile/application
- upload documents
- prepare application

PENDING
- view application
- view status
- limited changes according to business rules

REVISION_REQUIRED
- see reason
- correct permitted data/documents
- resubmit

REJECTED
- see rejection reason
- resubmit only if rules permit

ACTIVE
- proceed to subscription/catalog/RFQ functionality subject to permissions

SUSPENDED
- block protected Supplier actions
- preserve historical records


SUPPLIER DOCUMENT

PENDING
- awaiting Admin verification

VERIFIED
- usable until expiry/current-version rules change

REJECTED
- Supplier sees reason
- uploads corrected/replacement document


SUBSCRIPTION

PENDING
- waiting for activation/payment

TRIALING
- allow entitled functionality

ACTIVE
- allow entitled functionality

PAST_DUE
- restrict according to business rules

EXPIRED
- restrict subscription-dependent features

CANCELLED
- respect effective cancellation

SUSPENDED
- block subscription-dependent functionality


LISTING

DRAFT
- edit
- delete if safe
- submit

PENDING
- view
- limited modification

APPROVED
- publish/activate according to workflow

REJECTED
- see reason
- edit
- resubmit

INACTIVE
- hidden from marketplace while preserved


RFQ

OPEN + ELIGIBLE
- view
- ask question
- quote

OPEN + INELIGIBLE
- only permitted public summary
- no protected actions

CLOSED/CANCELLED/EXPIRED
- no new quotation
- historical access only as permitted


QUOTATION

DRAFT
- edit
- submit

SUBMITTED
- view
- withdraw only if permitted

UNDER_REVIEW
- view

REVISION_REQUESTED
- respond
- revise or decline

REVISED
- view current revision/history

SHORTLISTED
- view status

REJECTED
- see rejection information

AWARDED
- proceed to Award

WITHDRAWN/EXPIRED
- historical/read-only


AWARD

PENDING_SUPPLIER_RESPONSE
- accept
- reject

ACCEPTED
- proceed to PO

REJECTED_BY_SUPPLIER
- historical

CANCELLED
- historical

SUPERSEDED
- historical


PURCHASE ORDER — PHASE 1

ISSUED
- Supplier may view the PO
- Supplier may communicate/support as permitted
- no required Supplier fulfilment-state transition is activated in Phase 1
- waits for the authorized project completion workflow

COMPLETED
- historical / Review stage
- read-only except permitted support/communication actions

CANCELLED
- historical / support as applicable

DISPUTED
- support-ticket workflow

LEGACY / FUTURE STATUS VALUES

confirmed
in_progress
ready_for_delivery
delivered

- may be displayed safely for existing historical/legacy data
- must not become new Supplier transition actions in Phase 1
- do not make Buyer receipt confirmation a required Phase 1 dependency


==========================================================
PART 16 — SECURITY REQUIREMENTS
==========================================================

For EVERY Supplier feature enforce:

- authenticated User
- User active
- Account active
- active Membership
- Supplier capability where required
- correct Spatie team account
- permission
- Supplier resource ownership
- subscription entitlement where required
- valid workflow/status

Prevent:

- viewing another Supplier profile's private management data
- editing another Supplier listing
- viewing another Supplier quotation
- answering another Supplier's Award
- updating another Supplier PO
- seeing another Supplier contact inquiries
- seeing another Supplier private RFQ question
- viewing another Supplier subscription/payment
- replying to another Supplier review
- modifying another organization member/role

Cross-account ID manipulation must always return forbidden/not found
according to project convention.


==========================================================
PART 17 — TRANSACTION & CONCURRENCY REQUIREMENTS
==========================================================

Use transactions for multi-record workflows including:

- Supplier capability submission/resubmission
- subscription activation/change
- listing submission if multiple related records change
- quotation submission
- quotation revision
- Award response
- PO creation
- authorized PO completion/status change when Phase 1 requires it
- invitation acceptance
- ownership transfer
- organization conversion
- account closure state changes

For concurrency-sensitive operations:

re-check state INSIDE transaction.

Use:

lockForUpdate()

or equivalent where appropriate.

Critical idempotent operations:

- Supplier application submission
- listing submission
- quotation submission
- quotation revision
- Award Accept
- Purchase Order creation
- authorized PO completion/status change

==========================================================
PART 18 — HISTORY / AUDIT
==========================================================

Preserve history.

Use existing history structures including:

capability_application_history
listing_change_logs
quotation_revisions
quotation_revision_items
quotation_revision_requests
purchase_order_status_history
subscription_payments
account_type_changes
account_ownership_transfers
activity_log

Do not overwrite historical commercial/legal records.

==========================================================
PART 19 — PHASE-BY-PHASE DEVELOPMENT PLAN
==========================================================

Before each phase:

1. read `ARCHITECTURE.md` and the relevant sections of `design.md`
2. inspect existing Supplier routes
3. inspect Supplier Controllers and Form Requests
4. inspect Actions/Services where related business workflows exist
5. inspect Models and relationships
6. inspect Policies and permissions
7. inspect middleware/account context
8. inspect Supplier/Shared Blade views and reusable backend components
9. inspect related JavaScript/Alpine behavior only where UI interaction requires it
10. inspect the actual database schema/migrations
11. inspect existing automated tests
12. determine what backend code is compatible, incomplete, conflicting or missing
13. reuse compatible code
14. refactor/replace/rebuild incompatible Supplier backend code as necessary
15. preserve the public frontend and registration flow


==========================================================
PHASE 1 — SUPPLIER DASHBOARD FOUNDATION
==========================================================

Complete:

- Supplier master navigation
- Supplier capability-aware sidebar
- subscription-aware sidebar
- organization-only menu visibility
- Buyer/Supplier mode visibility
- dashboard metrics
- Action Required
- deadlines
- counters

Build/refactor the Supplier backend foundation according to `ARCHITECTURE.md` and `design.md`.

The old Supplier dashboard shell is not a source of truth. Reuse compatible shared layout/components when they already match the current specifications; otherwise refactor/rebuild the backend shell as required.

Do not modify the public frontend or registration design/flow.


==========================================================
PHASE 2 — SUPPLIER PROFILE & VERIFICATION
==========================================================

Complete:

- Company Information
- Supplier Types
- required document detection
- document upload/replacement
- document verification status
- application submission
- revision-required workflow
- rejection/resubmission
- capability history
- locations
- service areas
- business hours
- gallery
- videos
- exhibitions


==========================================================
PHASE 3 — SUBSCRIPTION & BILLING
==========================================================

Complete:

- plans
- plan comparison functionality
- explicit plan selection
- checkout/manual/free provider handling according to current system
- activation
- current subscription
- status rules
- plan snapshot use
- payment history
- failed payment handling
- expiration/cancellation
- subscription entitlement checks

Do this before opening unrestricted Supplier RFQ/catalog participation.


==========================================================
PHASE 4 — CATALOG
==========================================================

Complete:

- Products
- Services
- Add Listing
- Listing edit/detail
- categories
- attributes
- variants
- tier pricing
- product details
- service details
- Supplier brands
- category suggestions
- attribute suggestions
- listing approval
- rejection/resubmission
- listing change history


==========================================================
PHASE 5 — RFQ OPPORTUNITIES
==========================================================

Complete:

- RFQ public summary
- Supplier eligibility
- subscription delay
- Supplier queue
- Available RFQs
- Invited RFQs
- RFQ detail
- RFQ items
- Supplier questions
- Buyer answers
- RFQ version changes
- deadline handling


==========================================================
PHASE 6 — QUOTATIONS
==========================================================

Complete:

- quotation drafts
- item-based quotation
- partial quotation
- alternatives
- totals
- terms
- submission
- deadline enforcement
- RFQ version preservation
- My Quotations
- withdrawal where allowed
- Buyer decision status


==========================================================
PHASE 7 — QUOTATION REVISIONS
==========================================================

Complete:

- revision request receipt
- accept/decline revision request
- Supplier response
- immutable quotation revision
- revision items
- resubmit
- revision history
- current revision
- RFQ version relationship


==========================================================
PHASE 8 — AWARDS
==========================================================

Complete:

- Award list
- Award detail
- pending response
- response deadline
- Supplier Accept
- Supplier Reject
- mandatory rejection reason
- Buyer notification
- concurrency protection
- one-time PO creation


==========================================================
PHASE 9 — PURCHASE ORDERS — PHASE 1
==========================================================

Complete:

- PO list
- PO detail
- automatic PO dependency from accepted Award
- one-time/idempotent PO creation verification
- initial `issued` state
- items/commercial snapshot
- status/history display
- issued/completed/cancelled/disputed visibility
- no activation of confirmed/in_progress/ready_for_delivery/delivered transitions
- no Buyer receipt-confirmation dependency
- authorized completion integration according to current project rule
- Support linkage
- Buyer/Supplier messaging context
- payment tracking display only
- no product/service checkout, wallet, escrow, payout or refund engine


==========================================================
PHASE 10 — COMMUNICATION
==========================================================

Complete:

- Messages
- contact inquiries
- Notifications
- notification deep-links
- Support Tickets
- attachments where existing system allows
- transaction-context communication


==========================================================
PHASE 11 — REVIEWS
==========================================================

Complete:

- Reviews Received
- Review Detail
- Supplier Reply
- report review
- moderation states
- rating/review count integration


==========================================================
PHASE 12 — ORGANIZATION TEAM
==========================================================

Complete:

- Members
- Subscription team entitlement/limit
- Invitations
- invitation acceptance
- suspend/remove members
- member roles


==========================================================
PHASE 13 — ROLES & PERMISSIONS
==========================================================

Complete:

- Supplier-side role management
- Product Manager permissions
- Quotation Manager permissions
- Supplier Manager permissions
- Finance permissions
- Viewer permissions
- approved account-specific custom roles only after approved Role Requests
- Role Requests lifecycle
- prevent direct live custom-role creation before Admin approval
- owner/sensitive restrictions


==========================================================
PHASE 14 — OWNERSHIP
==========================================================

Complete:

- Owner list
- primary owner
- transfer
- target acceptance/rejection
- completion
- owner safety
- audit


==========================================================
PHASE 15 — SETTINGS & DUAL CAPABILITY
==========================================================

Complete:

- Personal & Security
- language/currency
- sessions
- Buyer/Supplier mode
- default dashboard preference


==========================================================
PHASE 16 — INDIVIDUAL → ORGANIZATION
==========================================================

Complete:

- conversion draft
- proposed organization
- documents
- submit
- revision
- resubmit
- approval
- same-account conversion
- Supplier reapproval rules
- type-change history


==========================================================
PHASE 17 — ACCOUNT CLOSURE
==========================================================

Complete:

- closure request
- active subscription handling
- active listing handling
- unresolved quotation/Award/PO validation
- disputes
- deletion_pending state
- preserve history


==========================================================
PHASE 18 — FINAL SECURITY & EDGE-CASE HARDENING
==========================================================

Test at minimum:

SUPPLIER ACCOUNT

- Supplier capability pending
- rejected
- revision required
- suspended
- account suspended
- organization member suspended
- removed user

DOCUMENTS

- missing required document
- rejected document
- expired document
- replacement/current document

SUBSCRIPTION

- no subscription
- pending subscription
- active subscription
- trialing
- past due
- expired
- cancelled
- suspended
- plan feature restriction
- team member restriction
- RFQ delay restriction

LISTINGS

- pending Supplier drafts listing
- pending Supplier cannot publish
- listing approval/rejection
- unauthorized listing edit
- invalid variant
- invalid tier price
- category/attribute suggestion security

RFQ

- ineligible Supplier cannot access full RFQ
- selected Supplier gets appropriate access
- global Supplier delay respected
- quotation deadline expired
- Q&A deadline expired
- Buyer changes RFQ version
- Supplier sees revision requirement

QUOTATION

- one quotation per Supplier/RFQ
- draft private from Buyer
- partial quote only when allowed
- alternative only when allowed
- RFQ version preserved
- submission after deadline rejected
- revision history preserved
- revision deadline
- withdrawal rules

AWARD

- wrong Supplier cannot respond
- response after deadline rejected
- double Accept
- Accept and Reject race
- PO created once
- rejected Award stores reason

PO — PHASE 1

- wrong Supplier cannot access
- accepted Award creates exactly one PO
- initial PO status is `issued`
- duplicate Award acceptance cannot duplicate PO
- Supplier cannot activate confirmed/in_progress/ready_for_delivery/delivered transitions
- unauthorized completion is rejected
- duplicate completion/status action is idempotent
- legacy extended statuses display safely without creating new Phase 1 transitions
- cancelled/disputed/support rules
- no marketplace product/service payment engine

ORGANIZATION

- user from another account cannot join
- plan team-member limit
- unauthorized role assignment
- platform permission escalation
- last owner protection

CROSS-ACCOUNT

- listings
- RFQs
- quotations
- Awards
- POs
- conversations
- inquiries
- reviews
- tickets
- subscriptions
- members

must all be tested for isolation.


==========================================================
PHASE 19 — AUTOMATED TESTING / COMPLETION
==========================================================

A Supplier feature is NOT complete merely because the page renders.

Definition of Done:

1. Route/Controller/Form Request/Policy/View structure follows `ARCHITECTURE.md` as applicable
2. Functionality works
3. Validation exists
4. Service/business rule exists
5. Policy authorization exists
6. Supplier account ownership enforced
7. Supplier capability checked
8. Subscription entitlement checked where applicable
9. Status transition validated
10. Transaction used where needed
11. Idempotency exists where needed
12. Notification exists where required
13. History/audit preserved
14. Empty states handled
15. Error states handled
16. Cross-account isolation tested
17. `design.md` is followed
18. mobile/responsive behavior is verified
19. reusable Blade components are used where appropriate
20. no backend Livewire is introduced
21. New automated tests pass
22. Existing tests still pass
23. public frontend and registration flow remain unaffected


==========================================================
PART 20 — DATABASE TABLES TO REUSE
==========================================================

Reuse existing Edushopify tables.

ACCOUNT / CAPABILITY

users
accounts
account_members
account_capabilities
capability_types
capability_application_history
account_locations


SUPPLIER PROFILE

supplier_profiles
supplier_types
supplier_supplier_type
supplier_documents
document_types
document_type_enables
supplier_service_areas
business_hours
supplier_gallery
supplier_videos
exhibitions
exhibition_supplier
media


SUBSCRIPTION

subscription_plans
subscriptions
subscription_payments


CATALOG

listings
product_details
service_details
listing_categories
listing_attribute_values
listing_variants
listing_variant_attributes
listing_tier_prices
listing_change_logs

categories
category_attribute
category_suggestions

attributes
attribute_values
attribute_suggestions

brands
units
media


RFQ

rfqs
rfq_items
rfq_public_summary
rfq_selected_suppliers
rfq_supplier_queue
rfq_target_filters
rfq_questions
rfq_deadline_extensions
rfq_change_logs


QUOTATIONS

quotations
quotation_items
quotation_revision_requests
quotation_revisions
quotation_revision_items
rfq_shortlists


AWARDS

awards


PURCHASE ORDERS

purchase_orders
purchase_order_items
purchase_order_status_history


COMMUNICATION

conversations
conversation_accounts
conversation_user_states
messages
contact_inquiries
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


SETTINGS / SECURITY

account_dashboard_preferences
settings
sessions
otp_codes
social_accounts
languages
currencies


AUDIT

activity_log


Do NOT create new equivalents for these tables.


==========================================================
PART 21 — BACKEND REBUILD SCOPE & REUSE RULES
==========================================================

The Supplier backend dashboard may be substantially refactored/rebuilt to meet the current specifications.

The OLD Supplier backend structure, old dashboard shell, old controllers/components or old UI conventions are NOT sources of truth.

Use:

- current database schema / confirmed business rules
- `ARCHITECTURE.md`
- `design.md`
- this workflow

as the desired target.

==========================================================
21.1 REUSE DOMAIN FOUNDATIONS — DO NOT CREATE DUPLICATES
==========================================================

Reuse existing domain/data foundations where they are valid, including:

- authentication system
- users/accounts architecture
- account membership architecture
- Buyer/Supplier capability architecture
- capability_types / account_capabilities
- Supplier-related database schema
- subscription database architecture
- listing/catalog database architecture
- RFQ database architecture
- quotation/revision database architecture
- Award database architecture
- Purchase Order database architecture
- messaging database architecture
- notifications
- reviews
- support tickets
- Spatie Permission Teams architecture
- organization membership architecture
- ownership-transfer architecture
- account-conversion architecture
- activity/audit history structures

Do NOT create duplicate systems merely because the Supplier backend UI is being rebuilt.

==========================================================
21.2 BACKEND CODE MAY BE REBUILT/REFACTORED
==========================================================

Supplier backend code may be reorganized/rebuilt as necessary, including:

- `routes/backend/supplier.php`
- Supplier Controllers
- Supplier Form Requests
- Supplier Policies
- Actions / Services when business complexity justifies them
- Supplier/Shared Blade views
- Supplier sidebar/topbar integration
- reusable backend Blade components
- backend JavaScript/Alpine UI behavior
- tests

Follow `ARCHITECTURE.md` for exact placement and portal/domain separation.

Do not create employee-role controller/view hierarchies such as ProductManager/QuotationManager directories.
Roles/permissions authorize actions inside the Supplier portal.

==========================================================
21.3 DESIGN SOURCE OF TRUTH
==========================================================

Do not preserve an old Supplier visual implementation merely because it exists.

Use `design.md` as the project-wide mandatory backend UI/UX source of truth.
Use `edushopify_dashboard_reference.html` for exact visual clarification.

Supplier must use the same shared backend component system as Admin/Buyer while preserving Supplier-specific workflow/accent rules from `design.md`.

Do not duplicate the design system inside this workflow.

==========================================================
21.4 PROTECT FRONTEND & REGISTRATION
==========================================================

The Supplier backend rebuild must NOT redesign or unnecessarily modify:

- public frontend
- public listing/profile pages
- registration pages
- registration UI
- registration workflow

Existing registration/authentication functionality should be reused as the prerequisite that leads into the Supplier backend.

==========================================================
21.5 CONNECT TO EXISTING BUYER PROCUREMENT WORKFLOW
==========================================================

Supplier functionality must connect to the same shared business records used by Buyer.

Do NOT create a second Award system.
Supplier responds to the existing Buyer-created Award.

Do NOT create a second Purchase Order system.
Supplier sees/participates in the Purchase Order created from the accepted Award according to the Phase 1 PO rules in this document.

Do NOT create a second RFQ system.
Supplier discovers/responds to Buyer RFQs through the existing RFQ/eligibility architecture.

Do NOT create another quotation revision system.
Use existing:

quotation_revision_requests
quotation_revisions
quotation_revision_items


==========================================================
PRESERVE KNOWN BUG FIXES
==========================================================

Do not reintroduce previously fixed project defects.

1. Capability checks must use:

capability_type_id
→ capabilityType->code

Do NOT use removed:

account_capabilities.capability


2. Do not break SQLite automated testing with unguarded MySQL-only migration
syntax.


3. Keep authorization Policy resolution aligned with the model being
authorized.


==========================================================
FINAL INSTRUCTION
==========================================================

Before implementation, read:

1. `docs/ai/ARCHITECTURE.md`
2. `docs/ai/design.md`
3. this `supplier_dashboard_workflow.md`
4. the current database schema
5. `docs/ai/references/edushopify_dashboard_reference.html` when visual clarification is needed

Then audit the actual Supplier backend:

1. Supplier routes
2. Supplier Controllers
3. Form Requests
4. Actions/Services
5. Models/relationships
6. Policies/permissions
7. middleware/account context
8. Supplier/Shared Blade views
9. reusable backend Blade components
10. JS/Alpine behavior where relevant
11. current subscription integration
12. existing automated tests

Classify existing Supplier backend code internally as:

- compatible/reusable
- incomplete
- architecture-conflicting
- design-conflicting
- workflow-conflicting
- missing

Then implement the phase plan.

For every phase:

- use the existing database/domain system
- reuse compatible backend code
- refactor/rebuild incompatible Supplier backend code
- follow `ARCHITECTURE.md`
- follow `design.md`
- follow this workflow
- do not introduce backend Livewire
- do not use Filament
- do not create duplicate systems
- preserve Buyer-side shared business records/workflows
- preserve public frontend and registration flow
- add/update tests

Do not stop after only producing an audit when implementation is requested.
Proceed phase-by-phase unless a genuine unresolved database/business-rule conflict could cause incorrect or destructive behavior.

The final Supplier backend must support this coherent business journey:

Existing Supplier Registration / Account Creation
        ↓
Supplier Capability Application & Verification
        ↓
Admin Approval
        ↓
Subscription Selection / Activation
        ↓
Business Profile & Catalog
        ↓
RFQ Opportunities
        ↓
Supplier Q&A
        ↓
Quotation
        ↓
Quotation Revision
        ↓
Award Response
        ↓
        ├── Reject → Buyer may Re-award
        └── Accept
               ↓
        Purchase Order Created Exactly Once
               ↓
        PO status = issued
               ↓
        Product / Service Transaction & Fulfilment Outside Platform (Phase 1)
               ↓
        Authorized Completion
               ↓
        PO status = completed
               ↓
        Buyer Review / Supplier Reply

plus:

Business Profile
Documents & Verification
Locations
Service Areas
Business Hours
Gallery
Videos
Exhibitions
Products
Services
Brands
Category/Attribute Requests
Subscription & Billing
Messages
Contact Inquiries
Notifications
Support
Reviews
Organization Members
Invitations
Roles & Permissions
Role Requests
Ownership
Buyer/Supplier Mode
Individual → Organization Conversion
Account Closure

Build this as ONE coherent Supplier business system rather than a collection of disconnected pages.
