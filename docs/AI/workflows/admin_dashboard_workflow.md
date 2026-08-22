# EduShopify Admin Dashboard Workflow — `admin_dashboard_workflow.md`

> **Status:** Mandatory Admin backend workflow and functional specification.
>
> This document defines **what the EduShopify Platform Admin Dashboard must do** and how platform-level administration, approvals, moderation, configuration, support, billing oversight, access control, and operational oversight must behave.
>
> It must be used together with:
>
> 1. `docs/ai/ARCHITECTURE.md`
> 2. `docs/ai/design.md`
> 3. `docs/ai/workflows/buyer_dashboard_workflow.md`
> 4. `docs/ai/workflows/supplier_dashboard_workflow.md`
> 5. the current EduShopify database schema / SQL dump
> 6. `docs/ai/references/edushopify_dashboard_reference.html` when exact visual clarification is needed
>
> `ARCHITECTURE.md` controls code/folder architecture.
> `design.md` controls backend UI/UX, reusable Blade components, Alpine usage, responsive behavior, sidebar/topbar, tables, forms, theme rules, accessibility, and visual consistency.
> Buyer/Supplier workflow files define portal-specific commercial workflows.
> This file controls **platform Admin governance, approvals, moderation, settings, oversight, and platform operations**.

==========================================================
SCOPE — PLATFORM ADMIN BACKEND
==========================================================

EduShopify is an existing B2B education procurement marketplace.

This specification applies to the **Platform Admin backend dashboard**.

The Admin backend exists to govern and operate the whole platform.

The Admin Dashboard must provide appropriate control over:

- platform users
- accounts
- Buyer accounts/capabilities
- Supplier accounts/capabilities
- capability applications and history
- Supplier verification/documents
- account members and ownership oversight
- account conversion requests
- account closure/deletion workflow
- global roles and permissions
- Buyer/Supplier custom role requests
- catalog taxonomy
- categories
- attributes and values
- brands
- units
- Buyer types
- Supplier types
- document types and document requirements
- exhibitions
- listing approvals/moderation
- RFQ oversight and approval where configured
- quotations and revision history oversight
- Awards oversight
- Purchase Order oversight
- Supplier subscription plans
- Supplier subscriptions
- subscription payments
- messages/conversations where Admin participation is permitted
- contact inquiries oversight
- notifications
- reviews and review-reply moderation
- review reports
- support tickets
- system settings
- backend theme settings
- geography
- currencies
- languages
- audit/activity history
- queue/job operational visibility where existing Laravel infrastructure supports it

The existing Admin backend implementation is NOT automatically the desired structure.

It may be refactored, reorganized, replaced, or rebuilt as required to comply with:

- `ARCHITECTURE.md`
- `design.md`
- this Admin workflow
- Buyer/Supplier workflows
- the current database schema
- confirmed Phase 1 business rules

Reuse existing backend code only when it remains compatible and useful.

Do not preserve poor legacy Admin backend structure merely because it already exists.

DO NOT make unrelated changes to:

- public frontend pages
- public frontend design
- registration UI
- registration flow
- working authentication flows
- Buyer/Supplier business rules except where an explicit Admin governance action is part of those workflows

If backend integration requires touching shared authentication/account context code,
make the smallest compatible change and do not redesign registration or public frontend behavior.

==========================================================
ADMIN AUTHORITY MODEL
==========================================================

Admin control must be powerful but structured.

Do NOT interpret “Admin has full control” as:

- every Admin user can do everything
- hidden buttons are sufficient authorization
- Admin may silently rewrite transactional history
- Admin may bypass database integrity
- Admin may impersonate Buyer/Supplier actors without an explicit audited feature
- Admin may create duplicate business systems

Use two concepts:

1. **Super Admin / fully privileged platform role**
   - may have the complete platform permission set
   - controls platform-wide configuration and governance
   - still follows transactional integrity, audit, and workflow safety rules

2. **Delegated Platform Admin roles**
   - receive only the permissions required for their duties
   - examples may include:
     - Platform Admin
     - Account Approver
     - Supplier Verifier
     - Catalog Moderator
     - Content Moderator
     - Support Manager
     - Finance Admin
     - Access Control Admin
   - use actual roles present in the project; do not hard-code duplicate role systems

Admin is a **platform authorization context**, not a Buyer/Supplier capability.

Platform Admin access should be protected by the architecture's platform-admin middleware / platform permissions.

For sensitive actions, enforce:

Authenticated User
        ↓
User Active
        ↓
Platform Admin Eligibility
        ↓
Required Platform Permission
        ↓
Resource Current State
        ↓
Business Invariant / Dependency Check
        ↓
Policy / Action Authorization
        ↓
Controller
        ↓
Transaction / Audit where required

A Super Admin may possess all platform permissions, but critical actions must still preserve:

- database constraints
- history
- idempotency
- transaction safety
- actor attribution
- timestamps
- reasons/comments where required

==========================================================
MANDATORY IMPLEMENTATION RULES
==========================================================

Admin backend functionality must use the architecture defined in `ARCHITECTURE.md`.

Approved request flow:

HTTP Request
      ↓
Route
      ↓
Platform Admin Middleware / Permission
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
Do NOT use Bootstrap or another competing backend UI framework.

Use Laravel Blade for backend rendering.
Use Alpine.js/small JavaScript only for browser-side UI interaction according to `design.md`.

Do not duplicate design rules inside this workflow.

For repeated visual UI:

- use shared Blade components according to `design.md`
- use inline Alpine for small local state
- use `Alpine.data()` only for reusable/complex browser behavior

The mandatory topbar/sidebar-brand `h-20` alignment and mobile-responsive rules are controlled by `design.md`.

==========================================================
DOCUMENT PRIORITY
==========================================================

When implementing Admin backend functionality, use this order:

1. Current database schema and confirmed business rules
2. `ARCHITECTURE.md`
3. `design.md`
4. `admin_dashboard_workflow.md`
5. Buyer/Supplier workflow files for the business workflow being administered
6. `edushopify_dashboard_reference.html` for exact visual clarification
7. Existing implementation only where compatible

If a genuine conflict could affect data integrity or business behavior:

- do not silently guess
- inspect the related schema and workflow
- preserve history
- use the confirmed Phase 1 rule

Minor implementation choices should follow these documents without unnecessary interruption.

==========================================================
DATABASE IS THE SOURCE OF TRUTH
==========================================================

The latest EduShopify database schema / SQL dump is the database structure source of truth.

Reuse existing tables.

Do NOT create duplicate systems for functionality already represented by the schema.

Examples of existing systems that must be reused include:

AUTH / SYSTEM

users
otp_codes
social_accounts
sessions
settings
notifications
activity_log
jobs
job_batches
failed_jobs
cache
cache_locks
media

ACCOUNT / ACCESS

accounts
account_members
account_member_invitations
account_ownership_transfers
account_capabilities
capability_application_history
account_conversion_requests
account_type_changes
account_dashboard_preferences
permissions
roles
model_has_roles
model_has_permissions
role_has_permissions
role_requests

BUYER / SUPPLIER PROFILE

buyer_profiles
buyer_types
buyer_buyer_type
supplier_profiles
supplier_types
supplier_supplier_type
account_locations
supplier_service_areas
supplier_documents
supplier_gallery
supplier_videos
business_hours
exhibitions
exhibition_supplier

CATALOG

categories
category_suggestions
brands
units
attributes
attribute_values
category_attribute
attribute_suggestions
listings
product_details
service_details
listing_categories
listing_attribute_values
listing_variants
listing_variant_attributes
listing_tier_prices
listing_change_logs

PROCUREMENT

rfqs
rfq_items
rfq_selected_suppliers
rfq_target_filters
rfq_supplier_queue
rfq_questions
rfq_deadline_extensions
rfq_change_logs
quotations
quotation_items
quotation_revision_requests
quotation_revisions
quotation_revision_items
rfq_shortlists
awards
purchase_orders
purchase_order_items
purchase_order_status_history

BILLING

subscription_plans
subscriptions
subscription_payments

COMMUNICATION / MODERATION

conversations
conversation_accounts
conversation_admin_users
conversation_user_states
messages
contact_inquiries
reviews
review_replies
review_reports
tickets
ticket_messages
saved_items

Before proposing a migration:

1. inspect current schema
2. inspect Models
3. inspect migrations
4. inspect Services/Actions
5. inspect Buyer/Supplier workflows
6. prove current database cannot support the requirement

Only then propose a schema change.

Do not create a new table just because an Admin screen needs a different presentation.

==========================================================
CRITICAL ADMIN GOVERNANCE RULES
==========================================================

1. Admin manages the **platform**, not a fake Buyer/Supplier account context.

2. Platform Admin actions must use platform permissions.

3. Super Admin may have all platform permissions.

4. Delegated Admin users must be permission-scoped.

5. Admin must be able to review Buyer and Supplier capability applications.

6. Capability decisions must preserve application/review history.

7. Supplier document verification must use existing Supplier document records and document rules.

8. Required Supplier documents must be configured through existing document types/document enablement rather than hard-coded in Supplier forms.

9. Admin manages global taxonomy and moderation workflows.

10. Admin may approve/reject Supplier-submitted taxonomy/listing requests according to the actual schema states.

11. Admin must not invent statuses not represented by the current schema/workflow.

12. Admin may oversee all RFQs, quotations, Awards, and POs, but ordinary Buyer/Supplier commercial actions remain owned by their respective portals.

13. A platform override must be an explicit Admin action, permission-protected, reasoned where appropriate, and audited.

14. Admin must not silently overwrite immutable commercial history.

15. Phase 1 Product/Service transaction payments remain outside EduShopify.

16. Only Supplier subscription payments are platform-processed in Phase 1.

17. Phase 1 Purchase Order workflow remains simplified as defined in Buyer/Supplier workflows.

18. Admin must not activate a full fulfilment/logistics workflow merely because legacy PO statuses exist in the database.

19. Admin controls subscription plans, subscription oversight, and subscription payment records according to the existing billing schema.

20. Admin controls global permissions and platform roles.

21. Buyer/Supplier custom roles are account-scoped and must be created only after an approved `role_requests` workflow when Admin approval is required.

22. Admin may approve/reject role requests but must prevent platform/sensitive/owner-only permission escalation.

23. Account conversion approval changes the SAME account; do not create a second marketplace account.

24. Important account, capability, role, conversion, moderation, billing, procurement-override, and support actions must be auditable.

25. Account closure must preserve transactional/legal history.

26. Account/user suspension must not physically delete commercial history.

27. Admin settings must reuse the existing `settings` table unless a specific setting cannot safely fit it.

28. Locked settings must not be casually overwritten through ordinary UI.

29. Theme settings must use the runtime CSS-variable design system from `design.md`.

30. Admin must prevent cross-account data corruption even though Admin can view platform-wide data.

==========================================================
PART 1 — FINAL ADMIN DASHBOARD MENU STRUCTURE
==========================================================

Use this logical Admin Dashboard structure.

ADMIN DASHBOARD

├── 1. Dashboard
│   └── Overview
│
├── 2. Users & Accounts
│   ├── Users
│   ├── Accounts
│   ├── Buyers
│   ├── Suppliers
│   ├── Account Members
│   ├── Capabilities
│   ├── Account Conversions
│   └── Account Closure / Deletion Queue
│
├── 3. Approval Center
│   ├── Buyer Applications
│   ├── Supplier Applications
│   ├── Supplier Documents
│   ├── Listing Approvals
│   ├── RFQ Approvals (when configured)
│   ├── Category Suggestions
│   ├── Attribute Suggestions
│   ├── Brand / Unit Requests
│   ├── Role Requests
│   ├── Account Conversion Requests
│   └── Review Reports / Moderation Queue
│
├── 4. Catalog & Taxonomy
│   ├── Categories
│   ├── Attributes
│   ├── Attribute Values
│   ├── Brands
│   ├── Units
│   ├── Buyer Types
│   ├── Supplier Types
│   ├── Document Types / Requirements
│   └── Exhibitions
│
├── 5. Procurement Oversight
│   ├── RFQs
│   ├── Quotations
│   ├── Awards
│   └── Purchase Orders
│
├── 6. Subscription & Billing
│   ├── Subscription Plans
│   ├── Supplier Subscriptions
│   └── Subscription Payments
│
├── 7. Communication
│   ├── Conversations
│   ├── Contact Inquiries
│   └── Notifications
│
├── 8. Reviews & Moderation
│   ├── Reviews
│   ├── Supplier Replies
│   └── Review Reports
│
├── 9. Support
│   └── Tickets
│
├── 10. Access Control
│   ├── Platform Roles
│   ├── Permissions
│   └── Account Role Requests
│
└── 11. System & Settings
    ├── General Settings
    ├── Appearance / Theme
    ├── Countries / States / Cities
    ├── Currencies
    ├── Languages
    ├── Document Configuration
    ├── Audit / Activity Log
    └── Queue / Failed Jobs (where supported)

IMPORTANT NAVIGATION RULE:

Do NOT put every approval/state/history action directly into the sidebar.

For example:

- capability application attempts
- document versions
- listing change history
- RFQ change history
- quotation revision history
- Award attempt/history
- PO status history
- ownership history
- account type change history
- payment metadata
- individual moderation actions

belong inside their relevant detail pages.

The Approval Center may provide centralized queues/tabs, but it should link to or reuse the canonical module detail/review screens rather than create duplicate business systems.

==========================================================
PART 2 — ADMIN DASHBOARD OVERVIEW
==========================================================

MENU:

Dashboard
└── Overview

Purpose:

Give platform administrators an immediate understanding of:

- platform growth
- account/capability approvals
- Supplier verification workload
- catalog moderation workload
- procurement activity
- subscription health
- support workload
- review moderation workload
- system alerts
- recent sensitive Admin activity

Dashboard metrics should be permission-aware.

A delegated Admin should not see sensitive finance/access-control metrics without permission.

ACCOUNT / USER METRICS

- total users
- active users
- pending-verification users
- suspended users
- total accounts
- individual accounts
- organization accounts
- pending account approvals where applicable
- suspended accounts
- deletion-pending accounts

CAPABILITY METRICS

- active Buyer capabilities
- pending Buyer applications
- Buyer revision-required
- suspended Buyers
- active Supplier capabilities
- pending Supplier applications
- Supplier revision-required
- rejected Supplier applications
- suspended Suppliers

SUPPLIER VERIFICATION

- pending Supplier documents
- rejected documents awaiting replacement
- documents nearing expiry
- Supplier applications awaiting decision

CATALOG / APPROVAL

- pending listings
- rejected listings
- active/published listings
- pending category suggestions
- pending attribute suggestions
- pending Supplier brands/units where applicable

PROCUREMENT

- open RFQs
- RFQs pending approval where configured
- quotations submitted
- pending revision requests
- Awards awaiting Supplier response
- accepted Awards
- issued POs
- completed POs
- cancelled/disputed POs

BILLING

- active subscriptions
- trialing subscriptions
- pending subscriptions
- past-due subscriptions
- expired subscriptions
- suspended subscriptions
- recent successful subscription payments
- failed subscription payments
- refunds/partial refunds where represented

COMMUNICATION / MODERATION

- open support tickets
- unassigned tickets
- high-priority tickets where schema/workflow supports priority
- new contact inquiries
- pending reviews
- flagged reviews
- pending review reports

ACTION REQUIRED

Examples:

- 8 Supplier applications require review
- 14 Supplier documents await verification
- 6 listings await approval
- 3 custom role requests await review
- 2 account conversion requests await review
- 5 review reports await moderation
- 7 unassigned support tickets
- 1 failed subscription payment requiring investigation

RECENT ACTIVITY

Show relevant recent activity such as:

- account approved/suspended
- capability approved/rejected/revision-required/suspended
- document verified/rejected
- listing approved/rejected
- taxonomy request reviewed
- role request approved/rejected
- conversion request approved/rejected
- review moderation
- subscription/payment administration
- ticket assignment/status change
- settings change
- platform role/permission change

Do not expose confidential audit details to Admin roles lacking permission.

==========================================================
PART 3 — USERS & ACCOUNTS
==========================================================

MENU:

Users & Accounts
├── Users
├── Accounts
├── Buyers
├── Suppliers
├── Account Members
├── Capabilities
├── Account Conversions
└── Account Closure / Deletion Queue

----------------------------------------------------------
3.1 USERS
----------------------------------------------------------

Use:

users
sessions
otp_codes
social_accounts
model_has_roles
model_has_permissions
activity_log

Admin must be able to:

- list users
- search by name/email/phone where appropriate
- filter by user status
- view user detail
- view verification state
- view last login where stored
- view current marketplace account/membership relationships
- view platform roles where applicable
- activate/inactivate/suspend user where permitted
- restore/reactivate from allowed states
- mark deleted only through controlled workflow where applicable
- review active sessions where authorized
- revoke sessions where the existing security implementation supports it
- inspect connected social providers without exposing secrets
- view relevant audit history

User statuses from the current schema include:

pending_verification
active
inactive
suspended
deleted

Sensitive user actions require explicit permission.

Suspension must:

- require a reason where project workflow requires it
- block protected access
- preserve account/business records
- write audit/activity history

Admin must NOT:

- expose password hashes
- expose OTP hashes
- expose session payload secrets
- manually fabricate email/phone verification without a deliberate sensitive Admin workflow
- silently delete transaction history belonging to the user's account

----------------------------------------------------------
3.2 ACCOUNTS
----------------------------------------------------------

Use:

accounts
account_members
account_capabilities
buyer_profiles
supplier_profiles
account_locations
account_dashboard_preferences
activity_log

Admin must be able to:

- list all marketplace accounts
- search by account number/display name/owner
- filter individual/organization
- filter account status
- view primary owner
- view members
- view Buyer/Supplier capabilities
- view Buyer profile
- view Supplier profile
- view locations
- view subscription summary for Supplier accounts
- view procurement summary
- view audit history
- approve account where account-level approval applies
- activate/inactivate/suspend/reactivate according to rules
- inspect deletion/closure state
- manage exceptional account corrections with explicit permission

Account statuses from current schema:

draft
pending_approval
active
inactive
suspended
deletion_pending
deleted

Admin account suspension must:

- store actor
- store reason
- preserve historical records
- prevent account-scoped protected actions
- not physically remove procurement/billing history

Do not change account type directly when an Individual→Organization conversion request/history is required.

----------------------------------------------------------
3.3 ACCOUNT MEMBERS OVERSIGHT
----------------------------------------------------------

Use:

account_members
account_member_invitations
roles
model_has_roles
activity_log

Admin may:

- inspect members of any marketplace account
- inspect owner/member status
- inspect primary owner
- inspect assigned account-scoped roles
- investigate membership disputes/support cases
- perform exceptional corrective action only with an explicit platform permission

Normal team management belongs to Buyer/Supplier organization users.

Admin should not routinely manage an account's employees on their behalf.

Emergency/corrective Admin action must preserve:

- at least one valid owner
- primary owner invariants
- account membership integrity
- role/account team context
- audit history

----------------------------------------------------------
3.4 BUYER MANAGEMENT
----------------------------------------------------------

Admin Buyer management should provide a platform view of:

- Buyer account
- account status
- Buyer capability status
- Buyer profile
- Buyer type(s)
- capability application history
- relevant account locations
- procurement activity summary
- support/moderation issues

Admin may:

- review Buyer capability application
- approve
- request revision
- reject
- suspend active Buyer capability
- reactivate when permitted
- view rejection/revision/suspension reasons
- inspect attempt history

Do not use Buyer as a hard-coded Spatie role.

Buyer capability is represented by account capability architecture.

----------------------------------------------------------
3.5 SUPPLIER MANAGEMENT
----------------------------------------------------------

Admin Supplier management should provide:

- account status
- Supplier capability status
- Supplier profile
- Supplier types
- verification/document status
- service areas
- listings summary
- RFQ/quotation participation summary
- Award/PO summary
- subscription summary
- payment history summary
- review summary
- support history

Admin may:

- review Supplier capability application
- verify/reject Supplier documents
- approve/revision/reject Supplier application
- suspend/reactivate Supplier capability where permitted
- inspect listing compliance
- inspect subscription entitlement
- view Supplier public/private management data according to Admin permission

Supplier capability approval does NOT automatically activate a free subscription.

Supplier must select an eligible subscription according to Supplier workflow.

==========================================================
PART 4 — CAPABILITY APPLICATIONS & VERIFICATION
==========================================================

Use:

account_capabilities
capability_types
capability_application_history
supplier_documents
document_types
document_type_enables
buyer_profiles
supplier_profiles
activity_log
notifications

----------------------------------------------------------
4.1 CAPABILITY REVIEW
----------------------------------------------------------

Capability statuses:

draft
pending
active
rejected
revision_required
suspended

Admin should normally review `pending` applications.

For each application show:

- account
- capability type
- current status
- attempt number/application attempts
- submitted by
- applied date
- profile completeness
- required documents/compliance where applicable
- previous attempts
- previous rejection/revision reasons
- review history

Admin decision actions:

APPROVE

pending
   ↓
validate current data/documents
   ↓
active
   ↓
set reviewed_by / reviewed_at
   ↓
set activated_at where applicable
   ↓
append/preserve history
   ↓
notify account

REVISION REQUIRED

pending
   ↓
revision_required
   ↓
store revision reason
   ↓
append/preserve history
   ↓
notify account

REJECT

pending
   ↓
rejected
   ↓
store rejection reason
   ↓
append/preserve history
   ↓
notify account

SUSPEND ACTIVE CAPABILITY

active
   ↓
suspended
   ↓
store reason / actor / time
   ↓
preserve historical access records
   ↓
notify account

REACTIVATE

suspended
   ↓
validate current eligibility
   ↓
active
   ↓
audit
   ↓
notify account

Do not approve a Supplier application while required verification conditions fail.

Do not bypass dynamic document requirements.

----------------------------------------------------------
4.2 SUPPLIER DOCUMENT VERIFICATION
----------------------------------------------------------

Use:

supplier_documents
document_types
document_type_enables

Supplier document statuses:

pending
verified
rejected

Admin must be able to:

- list pending documents
- filter Supplier/document type/status/expiry
- open document metadata/file securely
- inspect document type requirement
- verify document
- reject document with reason
- see uploader and upload date
- see expiry date
- see current/non-current version
- see previous document versions where preserved
- detect expired/expiring verified documents

VERIFY:

pending
   ↓
validate document
   ↓
verified
   ↓
verified_by_user_id
verified_at
   ↓
notify Supplier

REJECT:

pending
   ↓
rejected
   ↓
rejection_reason
   ↓
notify Supplier

Admin must not destroy prior versions when version/history behavior requires retention.

==========================================================
PART 5 — APPROVAL CENTER
==========================================================

Purpose:

Provide a consolidated, permission-aware work queue for Admin approvals.

The Approval Center is NOT a second business-data system.

It should aggregate/query existing records and route Admin to canonical review screens.

Queues may include:

- Buyer capability applications
- Supplier capability applications
- Supplier documents
- listings
- RFQs pending approval when configured
- category suggestions
- attribute suggestions
- Supplier-created brands requiring review
- Supplier-custom units requiring review
- role requests
- account conversion requests
- review reports
- review/reply moderation queues

Each queue should support:

- status filter
- submitted date
- account/Supplier/Buyer filter
- assigned reviewer where represented
- search
- sort
- pagination
- bulk selection only for actions that are safe to perform in bulk

Do NOT bulk-approve sensitive applications without validating each record's current eligibility.

==========================================================
PART 6 — CATALOG & TAXONOMY GOVERNANCE
==========================================================

MENU:

Catalog & Taxonomy
├── Categories
├── Attributes
├── Attribute Values
├── Brands
├── Units
├── Buyer Types
├── Supplier Types
├── Document Types / Requirements
└── Exhibitions

----------------------------------------------------------
6.1 CATEGORIES
----------------------------------------------------------

Use:

categories
category_suggestions

Admin manages the global category hierarchy.

Support:

- list/search/filter categories
- create global category
- edit category
- parent/child hierarchy
- active/inactive state
- ordering where schema supports it
- review Supplier category suggestions
- approve suggestion by creating/linking resulting category where appropriate
- reject suggestion with review comment/reason according to schema/workflow
- prevent invalid parent loops
- prevent destructive removal when dependent listings/RFQs require preservation

Supplier-submitted category suggestion must not automatically become a global category before Admin approval.

----------------------------------------------------------
6.2 ATTRIBUTES & VALUES
----------------------------------------------------------

Use:

attributes
attribute_values
category_attribute
attribute_suggestions

Admin may:

- create/edit global attributes
- activate/deactivate where represented
- configure display/sort behavior according to schema
- create/edit attribute values
- map attributes to categories
- mark category relationship requirements/variant/filter behavior according to existing columns
- review Supplier attribute suggestions
- approve/reject suggestions
- link resulting attribute where applicable

Do not create uncontrolled Supplier-specific taxonomy when the global suggestion workflow exists.

----------------------------------------------------------
6.3 BRANDS
----------------------------------------------------------

Use:

brands

Admin must distinguish:

- global brands
- Supplier-owned/proposed brands

Admin may:

- create/manage global brands
- review Supplier brands
- approve/reject where current approval fields support it
- activate/deactivate
- inspect reviewed_by/reviewed_at

Do not allow Supplier ownership of a global brand record to be confused with platform ownership.

----------------------------------------------------------
6.4 UNITS
----------------------------------------------------------

Use:

units

Unit scope includes:

- global
- supplier_custom

Admin may:

- manage global units
- review Supplier custom units
- approve/reject
- activate/deactivate
- inspect unit type/symbol

Do not create duplicate unit systems.

----------------------------------------------------------
6.5 BUYER TYPES / SUPPLIER TYPES
----------------------------------------------------------

Use:

buyer_types
supplier_types
buyer_buyer_type
supplier_supplier_type

Admin manages the available global types.

Support:

- create
- edit
- activate/deactivate
- sort/order where supported
- prevent deletion that would corrupt existing relationships

Do not hard-code type names in Buyer/Supplier portal logic when database records exist.

----------------------------------------------------------
6.6 DOCUMENT TYPES / REQUIREMENTS
----------------------------------------------------------

Use:

document_types
document_type_enables
capability_types

Admin must be able to configure which document types apply to which capabilities.

Support according to existing schema:

- document type list
- create/edit document type
- active/inactive
- requirement/enablement mapping to Buyer/Supplier capability where represented
- metadata/expiry rules where represented

Supplier verification forms must resolve requirements dynamically from this configuration.

Do not hard-code Supplier-required documents in portal code.

----------------------------------------------------------
6.7 EXHIBITIONS
----------------------------------------------------------

Use:

exhibitions
exhibition_supplier

Admin manages global exhibition records and Supplier associations according to platform rules.

Support:

- create/edit exhibition
- activate/deactivate where represented
- inspect Supplier participation
- approve/manage participation where business rules require Admin decision

==========================================================
PART 7 — LISTING APPROVAL & CATALOG MODERATION
==========================================================

Use:

listings
product_details
service_details
listing_categories
listing_attribute_values
listing_variants
listing_variant_attributes
listing_tier_prices
listing_change_logs
brands
units
media
notifications
activity_log

Listing approval statuses:

draft
pending
approved
rejected

Admin normally reviews `pending` listings.

Listing review screen should show:

- Supplier account
- capability/subscription status where relevant
- listing type
- listing number
- name
- category/categories
- brand
- attributes
- product/service details
- variants
- tier pricing
- media
- price/MOQ/unit
- prior rejection reason
- significant listing change history
- public/publish state

APPROVE:

pending
   ↓
validate required listing data
   ↓
approved
   ↓
set approved_by_user_id / approved_at
   ↓
allow Supplier publish/active behavior according to Supplier workflow
   ↓
notify Supplier

REJECT:

pending
   ↓
rejected
   ↓
store rejection_reason
   ↓
notify Supplier

Admin may also:

- deactivate/hide an approved listing when policy violations require it
- feature/unfeature listing where `is_featured` is supported and permission permits
- inspect listing history

Admin must not casually edit Supplier commercial content on their behalf.

If a corrective Admin edit is required:

- use explicit override permission
- identify actor
- preserve history where applicable
- avoid overwriting historical quotations/orders that already snapshot commercial values

==========================================================
PART 8 — RFQ APPROVAL & PROCUREMENT OVERSIGHT
==========================================================

MENU:

Procurement Oversight
├── RFQs
├── Quotations
├── Awards
└── Purchase Orders

Admin has platform-wide visibility according to permissions.

Admin oversight does NOT mean ordinary Admin users should make Buyer commercial decisions.

Buyer creates/manages RFQ, evaluates quotations, and creates Awards according to Buyer workflow.
Supplier responds according to Supplier workflow.

Admin functions are:

- approval where configured
- moderation
- support investigation
- fraud/abuse investigation
- status/integrity oversight
- exceptional controlled platform intervention

----------------------------------------------------------
8.1 RFQ OVERSIGHT
----------------------------------------------------------

Use:

rfqs
rfq_items
rfq_selected_suppliers
rfq_target_filters
rfq_supplier_queue
rfq_questions
rfq_deadline_extensions
rfq_change_logs

Admin may:

- list all RFQs
- search/filter Buyer/status/date/visibility
- open full RFQ detail
- inspect items
- inspect targeting
- inspect Supplier queue/eligibility
- inspect Q&A
- inspect deadline history
- inspect change/version history
- inspect quotations summary
- inspect Award/PO relationship

If RFQ approval is configured and status is `pending_approval`:

Admin may approve to the valid next workflow status.

Do NOT invent a new `rejected` RFQ status if the schema does not contain it.

If an RFQ cannot be approved:

- use the supported return/cancel/edit workflow defined by current business rules
- store appropriate reason/history if supported
- do not fabricate a status

Admin exceptional RFQ actions such as forced cancellation/hiding require:

- platform permission
- reason
- current-state validation
- transaction where related records change
- audit
- notifications where appropriate

----------------------------------------------------------
8.2 QUOTATION OVERSIGHT
----------------------------------------------------------

Use:

quotations
quotation_items
quotation_revision_requests
quotation_revisions
quotation_revision_items
rfq_shortlists

Admin may:

- list/search/filter quotations platform-wide
- inspect original quotation
- inspect current revision
- inspect complete revision history
- inspect Buyer shortlist/revision status
- investigate disputes/support issues

Admin must not overwrite submitted quotation/revision history.

Commercial snapshots are immutable historical evidence once submitted.

If a correction is unavoidable, use an explicit supported correction/override workflow rather than editing historical rows invisibly.

----------------------------------------------------------
8.3 AWARD OVERSIGHT
----------------------------------------------------------

Use:

awards

Admin may:

- list all Awards
- inspect Buyer/Supplier/RFQ/quotation relationship
- inspect attempt number
- inspect response deadline
- inspect Supplier response
- inspect rejection reason
- inspect accepted/rejected/cancelled/superseded history
- investigate duplicate/concurrency issues

Buyer owns winner selection/re-award in normal workflow.
Supplier owns accept/reject response in normal workflow.

Admin must preserve one-winner and concurrency rules.

A forced platform cancellation/correction must be permission-protected and audited.

----------------------------------------------------------
8.4 PURCHASE ORDER OVERSIGHT — PHASE 1
----------------------------------------------------------

Use:

purchase_orders
purchase_order_items
purchase_order_status_history
awards
quotations
rfqs

Admin may:

- list/search/filter all POs
- inspect Buyer/Supplier
- inspect Award/RFQ/quotation relationship
- inspect item snapshots
- inspect totals/currency
- inspect payment tracking note/status
- inspect PO status history
- investigate support/dispute cases
- perform authorized exceptional completion/cancellation/correction if the confirmed workflow permits it

PHASE 1 RULE:

Award accepted
      ↓
PO created exactly once
      ↓
issued
      ↓
Product/service transaction/payment/fulfilment occurs outside EduShopify
      ↓
Authorized project completion
      ↓
completed

Do NOT activate a required logistics workflow around:

confirmed
in_progress
ready_for_delivery
delivered
Buyer receipt confirmation

Those statuses may remain in the schema for legacy/future compatibility.

Admin should display legacy/historical values safely if records already use them, but new Phase 1 actions must follow the confirmed simplified flow.

Payment tracking is NOT a marketplace checkout system.

Do NOT build:

- escrow
- Buyer wallet
- Supplier payout wallet
- product Stripe checkout
- marketplace commission ledger

for Phase 1 unless a later business rule explicitly changes scope.

==========================================================
PART 9 — SUBSCRIPTION & BILLING ADMINISTRATION
==========================================================

MENU:

Subscription & Billing
├── Subscription Plans
├── Supplier Subscriptions
└── Subscription Payments

Use:

subscription_plans
subscriptions
subscription_payments
rfq_supplier_queue
activity_log

Only Supplier subscription payment is platform-processed in Phase 1.

----------------------------------------------------------
9.1 SUBSCRIPTION PLANS
----------------------------------------------------------

Admin controls plan configuration using actual schema fields.

Examples represented by current schema include:

- name
- slug
- billing type
- price
- currency
- Stripe product/price references
- trial days
- bonus days
- active listing limits
- product limits
- service limits
- team-member limit
- monthly quotation limit
- RFQ delay minutes
- RFQ notification entitlement
- analytics entitlement
- other feature fields represented by schema

Admin must be able to:

- list plans
- create plan
- edit plan
- activate/deactivate
- feature/unfeature where represented
- configure commercial/feature limits
- configure Stripe identifiers where applicable
- inspect current subscriber count

Historical subscription snapshots must remain authoritative for existing subscriptions.

Editing a plan must not retroactively rewrite old subscription commercial snapshots.

----------------------------------------------------------
9.2 SUPPLIER SUBSCRIPTIONS
----------------------------------------------------------

Admin must be able to:

- list subscriptions
- search/filter Supplier/plan/status/date
- view plan snapshot
- view price/features snapshot
- view start/trial/current period/expiry
- view auto-renew/cancellation state
- inspect payment history
- inspect RFQ entitlement consequences
- activate/suspend/cancel/extend/correct only according to permission and provider/business rules

Subscription statuses include current schema values such as:

pending
trialing
active
past_due
expired
cancelled
suspended

Do not confuse:

Supplier capability = active

with:

Subscription = active

They are separate systems.

----------------------------------------------------------
9.3 SUBSCRIPTION PAYMENTS
----------------------------------------------------------

Use:

subscription_payments

Payment providers represented include:

- stripe
- manual
- free

Payment statuses include:

pending
paid
failed
refunded
partially_refunded
cancelled

Admin may:

- list/search/filter payments
- inspect Supplier/subscription
- inspect amount/currency
- inspect provider IDs/invoice refs
- inspect failure reason
- inspect metadata where safe
- record/manage manual payment only through a valid manual-payment workflow
- synchronize/provider-update Stripe state where integration supports it
- record refund/partial-refund state only when provider/business operation is valid

Do NOT mark a Stripe payment `paid` merely to make UI look correct if provider confirmation is required.

Billing corrections are sensitive and must be audited.

==========================================================
PART 10 — COMMUNICATION & CONTACT INQUIRIES
==========================================================

MENU:

Communication
├── Conversations
├── Contact Inquiries
└── Notifications

----------------------------------------------------------
10.1 CONVERSATIONS
----------------------------------------------------------

Use:

conversations
conversation_accounts
conversation_admin_users
conversation_user_states
messages

Normal Buyer↔Supplier conversations remain participant-scoped.

Admin participation should use the existing `conversation_admin_users` relationship when Admin joining/assignment is part of the business/support workflow.

Admin may:

- view Admin-authorized conversations
- join/leave where permission/business context allows
- review conversations for support/moderation only when authorized
- send Admin/system communication where appropriate
- preserve read state

Do not grant every Admin unrestricted message surveillance unless the platform permission model explicitly permits it.

Admin message access is sensitive.

----------------------------------------------------------
10.2 CONTACT INQUIRIES
----------------------------------------------------------

Use:

contact_inquiries

Admin may:

- list platform contact inquiries
- search/filter Supplier/listing/status
- inspect handler
- inspect linked listing
- assign/handle where platform workflow allows
- mark status according to existing schema
- investigate spam/abuse

Supplier should still receive/manage inquiries belonging to the Supplier according to Supplier workflow.

----------------------------------------------------------
10.3 ADMIN NOTIFICATIONS
----------------------------------------------------------

Use existing Laravel notifications table/system.

Admin notification examples:

- new capability application
- Supplier document submitted/replaced
- listing submitted
- category/attribute suggestion
- role request submitted
- account conversion submitted
- review reported
- support ticket created/escalated
- subscription payment failed
- account closure request
- system/queue issue where integrated

Prevent duplicate notifications on retries.

==========================================================
PART 11 — REVIEWS & MODERATION
==========================================================

MENU:

Reviews & Moderation
├── Reviews
├── Supplier Replies
└── Review Reports

Use:

reviews
review_replies
review_reports
activity_log
notifications

----------------------------------------------------------
11.1 REVIEW MODERATION
----------------------------------------------------------

Review statuses:

pending
published
hidden
flagged
rejected

Admin may:

- list/search/filter reviews
- inspect Buyer/Supplier/context
- inspect RFQ/quotation/PO context where allowed
- publish/approve where moderation workflow requires it
- hide review
- flag review
- reject review
- restore/re-publish where valid
- store moderation reason
- preserve moderated_by and timestamps

Do not physically delete review evidence merely to moderate visibility.

----------------------------------------------------------
11.2 SUPPLIER REPLY MODERATION
----------------------------------------------------------

Use:

review_replies

Admin may:

- list replies
- inspect review/Supplier relationship
- hide/publish according to policy
- store moderation reason

Do not allow a reply from another Supplier to be reassigned to a review.

----------------------------------------------------------
11.3 REVIEW REPORTS
----------------------------------------------------------

Review report statuses:

pending
reviewed
dismissed
action_taken

Admin must be able to:

- list pending reports
- inspect report reason/details
- inspect review
- inspect reporting account/user
- mark reviewed
- dismiss
- record action taken
- store review action
- set reviewed_by/reviewed_at

A report does not automatically mean the underlying review must be hidden.

Moderation decision must be explicit.

==========================================================
PART 12 — SUPPORT TICKETS
==========================================================

MENU:

Support
└── Tickets

Use:

tickets
ticket_messages
activity_log
notifications

Admin support must be able to:

- list all authorized tickets
- search/filter account/status/category/priority where represented
- view linked account
- view linked business resource where supported
- assign ticket to Admin user
- reassign
- reply
- view attachments where supported
- change status according to current ticket workflow
- reopen/close according to rules
- inspect threaded history

Use:

assigned_admin_user_id

for assignment where current schema supports it.

Never expose private Admin-only operational information to Buyer/Supplier unless explicitly intended.

If internal-note functionality is not represented by the current schema, do not fake it using customer-visible ticket messages.

Support/dispute handling must reuse the existing ticket system.

Do NOT create a second dispute engine.

==========================================================
PART 13 — ACCESS CONTROL
==========================================================

MENU:

Access Control
├── Platform Roles
├── Permissions
└── Account Role Requests

Use:

roles
permissions
role_has_permissions
model_has_roles
model_has_permissions
role_requests
activity_log

----------------------------------------------------------
13.1 PLATFORM ROLES
----------------------------------------------------------

Admin role management controls platform/system roles.

Platform roles normally have:

account_id = NULL

and appropriate:

capability_scope = platform/common as designed

Admin may:

- list platform roles
- create non-reserved platform role
- edit role display/description
- activate/deactivate
- assign platform permissions
- assign role to eligible Admin users
- remove role subject to safety rules
- inspect role users

System/reserved roles must be protected from unsafe deletion or destructive edits.

Examples may include:

Super Admin
Platform Admin
Account Approver
Supplier Verifier
Catalog Moderator
Content Moderator
Support Manager
Finance Admin

Use actual project role records.

Do not hard-code business authorization to role names.

Use permissions.

----------------------------------------------------------
13.2 PERMISSION CATALOG
----------------------------------------------------------

Permissions include metadata represented in the current schema:

- name
- display_name
- group_name
- capability_scope
- description
- is_sensitive
- is_owner_only
- is_assignable
- is_active
- sort_order

Admin may manage the permission catalog with strict safeguards.

Do not casually rename a permission `name` that application code depends on.

Permission `name` is a technical contract.

For a rename:

- search code usage
- update authorization references safely
- update tests
- clear Spatie permission cache
- avoid breaking existing roles

Admin UI should make sensitive/non-assignable/owner-only/platform permissions clearly distinguishable.

----------------------------------------------------------
13.3 ACCOUNT ROLE REQUESTS
----------------------------------------------------------

Use:

role_requests

Role request statuses:

pending
approved
rejected
cancelled

Admin must be able to:

- list pending requests
- inspect requesting account/user
- inspect requested role name/display name
- inspect capability scope
- inspect requested permissions
- validate every requested permission
- approve
- reject with review comment

APPROVAL RULE:

Pending Role Request
       ↓
Admin validates account + capability scope
       ↓
Validate requested permissions
       ↓
Reject any platform/admin/non-assignable/inactive/forbidden permission
       ↓
Transaction
       ↓
Create account-scoped Spatie role EXACTLY ONCE
       ↓
Sync only approved allowed permissions
       ↓
Mark role_request approved
       ↓
reviewed_by / review_comment / reviewed_at
       ↓
clear permission cache
       ↓
notify account

No live custom account role should exist from the request before Admin approval.

Approval must be idempotent.

Concurrent approval must not create duplicate account roles.

REJECTION:

pending
   ↓
rejected
   ↓
review comment
   ↓
no live role created
   ↓
notify account

Admin must not approve permission escalation into:

- platform permission
- Admin-only permission
- inactive permission
- non-assignable permission
- inappropriate owner-only permission
- capability-incompatible permission

==========================================================
PART 14 — ACCOUNT CONVERSION ADMINISTRATION
==========================================================

Use:

account_conversion_requests
account_type_changes
accounts
supplier_profiles
account_capabilities
supplier_documents
activity_log
notifications

The conversion is:

INDIVIDUAL
   ↓
SAME ACCOUNT
   ↓
ORGANIZATION

Do NOT create another account.

Admin must be able to:

- list pending/revision/rejected/approved requests
- inspect current account
- inspect proposed organization data
- inspect submitted documents/data
- inspect submission actor/time
- inspect prior review comments
- request revision
- reject
- approve

Statuses should follow existing conversion schema/workflow:

draft
pending
approved
rejected
revision_required
cancelled

REVISION REQUIRED:

pending
   ↓
revision_required
   ↓
review comment/reason
   ↓
notify requester

REJECT:

pending
   ↓
rejected
   ↓
review reason
   ↓
notify requester

APPROVE:

pending
   ↓
transaction
   ↓
validate account still individual / eligible
   ↓
update SAME account to organization
   ↓
record converted_from_individual_at where applicable
   ↓
create account_type_changes history
   ↓
mark request approved
   ↓
set reviewed_by / reviewed_at
   ↓
notify account

Supplier-specific rule:

If an already-approved Supplier changes legal identity through conversion,
Admin must enforce Supplier re-verification/reapproval where confirmed business rules require it.

Do not silently preserve Supplier public approval if legal-identity change requires review.

No organization→individual rollback unless a later business rule explicitly enables it.

==========================================================
PART 15 — ACCOUNT CLOSURE / DELETION ADMINISTRATION
==========================================================

Use:

accounts
rfqs
quotations
awards
purchase_orders
subscriptions
subscription_payments
tickets
reviews
messages
activity_log

Account status includes:

deletion_pending
deleted

Before final closure, Admin must evaluate unresolved dependencies such as:

- open RFQs
- submitted quotations that require resolution
- pending Awards
- issued/active POs
- open support/dispute tickets
- active Supplier subscription
- payment/refund obligations
- ownership issues
- other business/legal obligations

Admin may:

- view closure request/reason
- review dependency report
- reject/hold closure when dependencies exist according to business rules
- finalize closure when eligible

Final closure must:

- prevent new account actions
- preserve transactional/commercial history
- preserve audit history
- preserve billing history
- preserve support/review/message history as required
- store Admin actor/time/reason

Do not physically delete critical transaction history.

==========================================================
PART 16 — SYSTEM SETTINGS
==========================================================

MENU:

System & Settings
├── General Settings
├── Appearance / Theme
├── Countries / States / Cities
├── Currencies
├── Languages
├── Document Configuration
├── Audit / Activity Log
└── Queue / Failed Jobs where supported

Use existing:

settings
countries
states
cities
currencies
languages
document_types
document_type_enables
activity_log
jobs
job_batches
failed_jobs

----------------------------------------------------------
16.1 SETTINGS STORAGE
----------------------------------------------------------

The current settings table supports:

- group_name
- name
- locked
- payload JSON

Reuse it for platform configuration where appropriate.

Do not create separate one-row setting tables for every new dashboard preference unless the current settings architecture genuinely cannot support the requirement.

Admin settings UI should group settings logically.

Potential groups may include, where represented/needed:

- general
- marketplace
- procurement
- supplier
- subscription
- notification
- security
- theme
- support

Do not assume a key exists before inspecting current data/seeders.

`locked = true` settings require special protection.

Ordinary Admin roles should not modify locked settings unless an explicit sensitive permission allows it.

----------------------------------------------------------
16.2 APPEARANCE / THEME
----------------------------------------------------------

Theme settings must follow `design.md`.

Admin should be able to configure theme-sensitive backend navigation values such as:

- application accent
- sidebar background
- sidebar border
- sidebar text
- sidebar muted/icon text
- menu text
- menu hover background/text
- menu active background/text/border
- submenu text
- submenu hover background/text
- submenu active background/text
- optional topbar background/border

Store values through the existing settings architecture.

Runtime dashboards must resolve them through centralized CSS variables.

Changing theme settings must update:

- Admin
- Buyer
- Supplier

without requiring individual Blade menu color rewrites.

Semantic status colors remain fixed according to `design.md`.

Theme changes must not turn success/error/warning meanings into arbitrary brand colors.

After settings update:

- invalidate relevant settings/theme cache if used
- do not clear unrelated application data unnecessarily

----------------------------------------------------------
16.3 GEOGRAPHY
----------------------------------------------------------

Use:

countries
states
cities

Admin may:

- list/search
- create/edit if platform supports manual geography maintenance
- activate/deactivate
- manage hierarchy
- ordering where represented

Prevent invalid state/city relationships.

Avoid deleting geography referenced by active business records; prefer deactivation where appropriate.

----------------------------------------------------------
16.4 CURRENCIES
----------------------------------------------------------

Use:

currencies

Admin may:

- create/edit currencies where appropriate
- set symbol/decimal places
- activate/deactivate
- set default currency safely
- maintain exchange-rate field/time where the current business feature uses it

Only one default currency should exist if that is the intended platform invariant.

Changing currency metadata must not rewrite historical quotation/PO/subscription currency snapshots.

----------------------------------------------------------
16.5 LANGUAGES
----------------------------------------------------------

Use:

languages

Admin may:

- manage available language/locale records
- activate/deactivate
- set ordering/default where supported

Do not claim translation content exists merely because a language record exists.

----------------------------------------------------------
16.6 QUEUE / FAILED JOBS
----------------------------------------------------------

Where the existing Laravel queue infrastructure uses:

jobs
job_batches
failed_jobs

Admin/System operations may provide permission-protected operational visibility.

Potential actions:

- view failed jobs
- inspect failure metadata safely
- retry where the application already supports safe retry
- delete obsolete failed-job record where operationally appropriate

Do not expose secrets contained in payload/exception data to unauthorized Admin roles.

Do not build a second queue system.

==========================================================
PART 17 — AUDIT / ACTIVITY HISTORY
==========================================================

Use:

activity_log

Important Admin actions must be auditable.

At minimum consider logging:

ACCOUNT

- approve
- suspend
- reactivate
- closure finalization
- sensitive owner/member correction

CAPABILITY

- approve
- revision required
- reject
- suspend
- reactivate

SUPPLIER DOCUMENT

- verify
- reject

CATALOG

- listing approve/reject/deactivate/feature
- category/attribute suggestion approve/reject
- brand/unit review
- taxonomy sensitive edit

ACCESS CONTROL

- platform role create/update/delete/deactivate
- permission metadata change
- role permission sync
- Admin assignment
- role request approve/reject

ACCOUNT CONVERSION

- revision
- rejection
- approval

BILLING

- plan create/update/deactivate
- manual subscription correction
- manual payment/refund correction

MODERATION

- review publish/hide/reject
- review reply hide/publish
- review report decision

SUPPORT

- assignment
- status changes
- sensitive ticket action

SYSTEM SETTINGS

- setting update
- locked-setting update
- theme update

PROCUREMENT OVERRIDES

- forced RFQ cancellation/approval correction
- Award cancellation/correction
- PO exceptional state change

Audit data should include where available/appropriate:

- acting Admin user
- action
- target type/id
- account context
- previous/current state
- reason/comment
- timestamp

Do not put credentials or secrets into activity logs.

==========================================================
PART 18 — ADMIN SECURITY REQUIREMENTS
==========================================================

For EVERY Admin feature enforce:

- authenticated User
- active User
- platform Admin eligibility
- required platform permission
- valid target resource
- valid resource status
- business invariant
- sensitive-action controls where required

Prevent:

- non-Admin access to `/admin/*`
- delegated Admin privilege escalation
- account-role permission escalation to platform permission
- unsafe deletion of system roles
- unsafe modification of locked settings
- unauthorized billing changes
- unauthorized viewing of sensitive conversations
- unauthorized ticket assignment/reply
- cross-record relationship corruption
- capability approval with stale state
- duplicate role-request approval
- duplicate conversion approval
- duplicate subscription/payment effects
- destructive historical edits

Do not rely only on hidden navigation.

Direct URL access must still be authorized.

Sensitive actions should require confirmation in UI.

Where the project supports sensitive-action OTP/re-authentication, apply it to especially critical controls as appropriate.

==========================================================
PART 19 — TRANSACTION & CONCURRENCY REQUIREMENTS
==========================================================

Use database transactions for multi-record Admin workflows.

Especially:

- capability approval/revision/rejection when history/notifications change
- Supplier document verification where related capability state changes
- listing approval if multiple records/state are updated
- category/attribute suggestion approval that creates resulting taxonomy
- role-request approval + role creation + permission sync
- account conversion approval
- account closure/finalization
- subscription activation/change when related records change
- manual payment/refund processing
- procurement exceptional override
- ownership corrective action

For concurrency-sensitive operations:

- re-check current state INSIDE the transaction
- use `lockForUpdate()` or equivalent where appropriate
- use database uniqueness constraints
- make operation idempotent

Critical idempotent Admin actions include:

- capability approval
- document verification
- listing approval
- role-request approval
- conversion approval
- closure finalization
- manual payment processing
- sensitive procurement override

Example role request protection:

Two Admins click Approve
       ↓
Transaction + lock role_request
       ↓
Re-check status = pending
       ↓
Create account role once
       ↓
Sync permissions once
       ↓
Mark request approved
       ↓
Second request sees approved and performs no duplicate side effect

==========================================================
PART 20 — STATUS-AWARE ADMIN FUNCTIONALITY
==========================================================

Never show/execute Admin actions merely because an Admin detail page exists.

Actions depend on current state and permission.

USER

pending_verification
- view
- allowed verification/support actions
- suspend/inactivate only if permitted

active
- view
- suspend/inactivate according to permission

inactive
- view
- reactivate where allowed

suspended
- view reason
- reactivate where allowed

ACCOUNT

draft
- inspect
- limited administrative correction

pending_approval
- review/approve where account approval is applicable

active
- normal oversight
- suspend/inactivate with permission

inactive
- view/reactivate where allowed

suspended
- view reason
- reactivate where allowed

deletion_pending
- dependency review
- finalize/hold according to workflow

deleted
- historical/read-only except narrowly defined restoration process if one exists

CAPABILITY

draft
- inspect; not ordinary approval target until submitted

pending
- approve
- revision required
- reject

active
- suspend

revision_required
- inspect; wait for account resubmission unless Admin correction workflow applies

rejected
- historical; reconsider only through allowed resubmission/review workflow

suspended
- inspect/reactivate if eligible

SUPPLIER DOCUMENT

pending
- verify
- reject

verified
- inspect
- handle expiry/current-version changes according to rules

rejected
- inspect; wait for replacement unless correction is valid

LISTING

draft
- inspect only when Admin oversight requires

pending
- approve
- reject

approved
- inspect
- deactivate/hide/feature where permitted

rejected
- inspect
- wait for Supplier revision/resubmission

ROLE REQUEST

pending
- approve
- reject

approved
- historical/read-only

rejected
- historical/read-only

cancelled
- historical/read-only

ACCOUNT CONVERSION

draft
- normally user-managed draft

pending
- revision required
- approve
- reject

revision_required
- wait for requester correction/resubmission

approved
- historical/read-only

rejected
- historical/read-only

cancelled
- historical/read-only

REVIEW REPORT

pending
- review
- dismiss
- action taken

reviewed/dismissed/action_taken
- historical/read-only except controlled correction

SUBSCRIPTION PAYMENT

pending
- provider/manual workflow action where applicable

paid
- historical; refund only through supported flow

failed
- inspect/retry through provider/business flow where applicable

refunded/partially_refunded
- historical/current financial state

cancelled
- historical

==========================================================
PART 21 — ADMIN REPORTING & EXPORT
==========================================================

Admin screens may provide operational reports using existing data.

Examples:

- user/account growth
- Buyer/Supplier capability status counts
- Supplier verification backlog
- listing approval backlog
- RFQ/quotation/Award/PO operational counts
- Supplier subscription status distribution
- subscription payment totals/statuses
- support-ticket workload
- review moderation workload

Do not create duplicate reporting tables unless necessary for performance and explicitly designed.

Use direct aggregate queries/read models/services where practical.

Exports should:

- respect Admin permissions
- respect current filters
- avoid exposing secrets
- be server-side for large datasets
- use consistent column formatting

==========================================================
PART 22 — ADMIN NOTIFICATION / APPROVAL EVENT MATRIX
==========================================================

Admin should receive relevant notification/work-queue signals for:

ACCOUNTS

- account awaiting approval where applicable
- closure requested

BUYER

- Buyer capability submitted/resubmitted

SUPPLIER

- Supplier capability submitted/resubmitted
- Supplier document uploaded/replaced
- document nearing expiry where operationally useful

CATALOG

- listing submitted
- category suggestion submitted
- attribute suggestion submitted
- brand/unit request submitted

ACCESS CONTROL

- custom role request submitted

ACCOUNT CONVERSION

- conversion submitted/resubmitted

BILLING

- subscription payment failed
- subscription past due/expired where Admin action is needed

REVIEWS

- review flagged/reported

SUPPORT

- new ticket
- escalated/high-priority ticket where supported

Notifications are supplementary.

The canonical record state remains the source of truth.

==========================================================
PART 23 — COMPLETE ADMIN BUSINESS WORKFLOW
==========================================================

The Admin Dashboard must support the whole platform lifecycle.

ACCOUNT / CAPABILITY FLOW

User Registration
      ↓
Account Creation
      ↓
Buyer / Supplier Capability Application
      ↓
Admin Approval Queue
      ↓
Admin Reviews Profile / Required Data
      ↓
For Supplier: Verify Required Documents
      ↓
      ├── Revision Required
      │      ↓
      │ Account Corrects / Resubmits
      │
      ├── Rejected
      │
      └── Approved / Active

SUPPLIER MARKETPLACE FLOW

Supplier Capability Active
      ↓
Supplier Selects Subscription
      ↓
Subscription Activated
      ↓
Supplier Creates Product / Service
      ↓
Listing Submitted
      ↓
Admin Listing Review
      ↓
      ├── Reject → Supplier Revises → Resubmits
      └── Approve → Publish/Active according to workflow

TAXONOMY REQUEST FLOW

Supplier needs missing Category / Attribute / Brand / Unit
      ↓
Submit Request/Suggestion
      ↓
Admin Review
      ↓
      ├── Reject
      └── Approve / Create or Link Global Result
              ↓
Supplier can use approved result

PROCUREMENT OVERSIGHT FLOW

Buyer Creates RFQ
      ↓
Admin Approval if configured
      ↓
RFQ Open
      ↓
Supplier Eligibility / Queue
      ↓
Supplier Quotation
      ↓
Buyer Compare / Revision / Shortlist
      ↓
Buyer Award
      ↓
Supplier Accept / Reject
      ↓
Accept → PO Created Exactly Once
      ↓
Phase 1 Off-platform transaction/fulfilment
      ↓
Authorized Completion
      ↓
Review

Admin may inspect every stage and intervene only through explicit governed Admin actions.

ROLE REQUEST FLOW

Buyer/Supplier Account Requests Custom Role
      ↓
role_requests = pending
      ↓
Admin Review
      ↓
Validate Permission Scope
      ↓
      ├── Reject → no live role
      └── Approve
              ↓
       Create Account Role Exactly Once
              ↓
       Sync Allowed Permissions
              ↓
       Account Can Assign Role

CONVERSION FLOW

Individual Account
      ↓
Conversion Request
      ↓
Admin Review
      ↓
      ├── Revision Required
      ├── Reject
      └── Approve
              ↓
       SAME Account → Organization
              ↓
       account_type_changes History

SUPPORT / MODERATION FLOW

Buyer/Supplier Issue or Review Report
      ↓
Ticket / Review Report
      ↓
Admin Assignment / Review
      ↓
Investigation
      ↓
Resolution / Action
      ↓
Audit + Notification

==========================================================
PART 24 — PHASE-BY-PHASE ADMIN DEVELOPMENT PLAN
==========================================================

Before each phase:

1. inspect existing Admin routes
2. inspect Admin Controllers
3. inspect Form Requests
4. inspect Blade views/components
5. inspect Models
6. inspect Services/Actions
7. inspect Policies
8. inspect middleware
9. inspect current database schema
10. inspect relevant Buyer/Supplier workflow
11. inspect existing tests
12. identify legacy code that conflicts with current architecture
13. reuse only compatible functionality
14. rebuild/refactor backend portion where necessary
15. implement missing functionality
16. test authorization and workflow

----------------------------------------------------------
PHASE 1 — ADMIN FOUNDATION & DASHBOARD
----------------------------------------------------------

Complete:

- Admin route/middleware foundation
- platform Admin authorization
- Admin layout/sidebar/topbar using `design.md`
- permission-aware navigation
- dashboard metrics
- Action Required queues
- approval counters
- support/moderation counters
- recent activity

Do not change public frontend/registration.

----------------------------------------------------------
PHASE 2 — USERS & ACCOUNTS
----------------------------------------------------------

Complete:

- User list/detail/status management
- Account list/detail/status management
- Buyer/Supplier account views
- member/owner oversight
- account suspension/reactivation
- deletion-pending visibility

----------------------------------------------------------
PHASE 3 — BUYER / SUPPLIER CAPABILITY APPROVAL
----------------------------------------------------------

Complete:

- capability queues
- application detail
- attempt/history
- approve
- revision required
- reject
- suspend/reactivate
- notifications
- audit

----------------------------------------------------------
PHASE 4 — SUPPLIER DOCUMENT VERIFICATION
----------------------------------------------------------

Complete:

- document requirements
- pending verification queue
- secure preview/download
- verify
- reject/reason
- expiry/current-version handling
- application linkage

----------------------------------------------------------
PHASE 5 — APPROVAL CENTER
----------------------------------------------------------

Complete centralized queues for:

- capabilities
- documents
- listings
- RFQs where configured
- taxonomy suggestions
- role requests
- conversions
- review reports

Reuse canonical detail/review screens.

----------------------------------------------------------
PHASE 6 — CATALOG & TAXONOMY
----------------------------------------------------------

Complete:

- categories
- attributes
- values
- category-attribute mapping
- brands
- units
- Buyer types
- Supplier types
- document types/enablement
- exhibitions
- suggestion review

----------------------------------------------------------
PHASE 7 — LISTING MODERATION
----------------------------------------------------------

Complete:

- pending list
- detail/review
- approve
- reject
- deactivate/hide
- feature where supported
- change-history inspection

----------------------------------------------------------
PHASE 8 — PROCUREMENT OVERSIGHT
----------------------------------------------------------

Complete:

- RFQ oversight/approval where configured
- quotation/revision oversight
- Award oversight
- PO oversight
- Phase 1 status rules
- controlled Admin overrides

----------------------------------------------------------
PHASE 9 — SUBSCRIPTION PLANS
----------------------------------------------------------

Complete:

- plan CRUD
- feature/limit configuration
- active/featured state
- Stripe identifiers
- safe plan editing

----------------------------------------------------------
PHASE 10 — SUBSCRIPTIONS & PAYMENTS
----------------------------------------------------------

Complete:

- Supplier subscription list/detail
- status oversight
- plan snapshots
- period/expiry
- payment history
- failed payments
- manual/provider administration
- refund status where applicable

----------------------------------------------------------
PHASE 11 — REVIEWS & MODERATION
----------------------------------------------------------

Complete:

- review moderation
- reply moderation
- report queue
- action/dismiss decisions
- notifications/audit

----------------------------------------------------------
PHASE 12 — SUPPORT & COMMUNICATION
----------------------------------------------------------

Complete:

- tickets
- assignment
- replies
- statuses
- contact inquiries
- Admin-authorized conversations
- notification center

----------------------------------------------------------
PHASE 13 — ACCESS CONTROL
----------------------------------------------------------

Complete:

- platform roles
- permission catalog
- Admin role assignments
- protected system roles
- role requests
- approval→account-role creation
- privilege-escalation protection

----------------------------------------------------------
PHASE 14 — ACCOUNT CONVERSION
----------------------------------------------------------

Complete:

- conversion queue
- detail
- revision
- reject
- approve
- same-account conversion
- type-change history
- Supplier re-verification handling

----------------------------------------------------------
PHASE 15 — ACCOUNT CLOSURE
----------------------------------------------------------

Complete:

- closure queue
- dependency analysis
- hold/reject/finalize
- preserved history
- audit

----------------------------------------------------------
PHASE 16 — SYSTEM SETTINGS & THEME
----------------------------------------------------------

Complete:

- settings groups
- locked-setting protection
- theme settings
- CSS-variable propagation
- settings cache invalidation
- geography
- currencies
- languages
- document configuration

----------------------------------------------------------
PHASE 17 — AUDIT & OPERATIONS
----------------------------------------------------------

Complete:

- activity-log browsing
- filtering/search
- sensitive-event inspection
- failed-job visibility where supported
- safe retry controls where appropriate

----------------------------------------------------------
PHASE 18 — SECURITY & BUSINESS HARDENING
----------------------------------------------------------

Test at minimum:

- non-Admin tries `/admin/*`
- Admin missing specific permission
- suspended Admin user
- platform role removed
- stale capability approval
- two Admins approve same capability
- two Admins approve same role request
- two Admins approve same conversion
- permission escalation attempt
- account-scoped role requests platform permission
- non-assignable permission request
- owner-only permission request by invalid account context
- locked setting change without permission
- listing approve/reject stale state
- document verify/reject stale state
- manual payment duplicate request
- refund duplicate request
- account deletion with unresolved PO
- account deletion with active subscription
- Supplier suspension while quotations/PO history exists
- forced procurement override without permission
- Admin conversation access without authorization
- hidden review restoration permission
- support ticket assignment unauthorized

----------------------------------------------------------
PHASE 19 — TESTING & COMPLETION
----------------------------------------------------------

Every Admin module must have appropriate automated tests.

Test:

- routes
- middleware
- Form Requests
- Policies
- Actions/Services
- transactions
- idempotency
- audit behavior
- notifications
- UI permission visibility
- responsive design
- query/filter/pagination
- frontend/registration regressions

==========================================================
PART 25 — ADMIN AUTOMATED TEST REQUIREMENTS
==========================================================

At minimum create/maintain tests for:

ADMIN ACCESS

- normal user denied Admin routes
- Buyer/Supplier member denied Admin routes
- platform Admin allowed
- Admin without permission denied action
- permission removal takes effect

USER / ACCOUNT

- user suspend/reactivate
- account suspend/reactivate
- history preserved
- account closure dependencies

CAPABILITY

- approve pending Buyer
- approve pending Supplier
- reject
- revision required
- suspend
- reactivate
- stale/double approval idempotency

DOCUMENTS

- verify pending document
- reject with reason
- other Supplier document cannot be modified accidentally by wrong target reference
- current-version/expiry behavior

LISTINGS

- approve pending
- reject
- duplicate/stale approval blocked
- approved historical commercial records preserved

TAXONOMY

- category suggestion approve/reject
- attribute suggestion approve/reject
- brand/unit review
- hierarchy validation

ROLE REQUEST

- approve valid request
- exactly one account role created
- permissions synced
- invalid platform permission rejected
- inactive/non-assignable permission rejected
- rejected request creates no role
- concurrent approval safe

CONVERSION

- revision
- reject
- approve same account
- history created
- duplicate approval safe

BILLING

- plan create/update validation
- subscription status authorization
- payment correction authorization
- duplicate manual payment protection where applicable
- historical snapshot preserved

REVIEWS

- publish/hide/reject
- report dismiss/action_taken
- moderation actor/reason stored

SUPPORT

- assign ticket
- reply
- unauthorized Admin role blocked
- account/user-facing thread preserved

SETTINGS

- normal setting update
- locked setting protected
- theme setting applies through CSS variables
- cache invalidation works

PROCUREMENT OVERSIGHT

- Admin can inspect platform records with permission
- ordinary Admin cannot act as Buyer/Supplier
- exceptional override requires permission
- Phase 1 PO workflow not expanded accidentally

REGRESSION

- Buyer dashboard workflow remains working
- Supplier dashboard workflow remains working
- public frontend remains working
- registration flow remains unchanged
- no backend Livewire introduced

==========================================================
PART 26 — ADMIN UI / DESIGN REQUIREMENTS
==========================================================

All Admin pages must follow `design.md`.

Do not duplicate UI rules here.

Required principles:

- same EduShopify backend component system as Buyer/Supplier
- Admin default accent uses configured Admin theme/default Indigo
- reusable Blade components for repeated tables/forms/cards/status badges
- Alpine only for UI interaction
- no backend Livewire
- responsive mobile behavior
- permission-aware menus/actions
- accessible controls
- exact `h-20` topbar and sidebar-brand height alignment
- theme-sensitive menu colors from CSS variables
- fixed semantic status colors

Admin data-heavy pages should use:

- searchable/filterable tables
- server-side pagination for large datasets
- clear status badges
- compact actions
- bulk actions only when safe
- detail pages for sensitive reviews/approvals
- reason/comment modals for reject/suspend/override actions

==========================================================
PART 27 — ADMIN OVERRIDE SAFETY CONTRACT
==========================================================

Admin has broad platform control, but Admin override actions must be explicit.

Use this pattern:

Admin Chooses Override
       ↓
Permission Check
       ↓
Show Current State + Consequences
       ↓
Require Reason / Confirmation where appropriate
       ↓
Transaction
       ↓
Re-check Current State
       ↓
Apply Minimum Necessary Change
       ↓
Preserve Historical Records
       ↓
Activity Log
       ↓
Notify Affected Account where appropriate

Never implement a generic “Edit Everything in Database” Admin form.

Use domain-specific Admin actions.

Examples:

GOOD

- Suspend Supplier Capability
- Reject Listing
- Approve Role Request
- Cancel RFQ for Policy Violation
- Correct PO State through explicit override
- Suspend Subscription
- Hide Review

BAD

- Arbitrary table-row editor
- Raw JSON database editor exposed to Admin
- Directly rewrite quotation history
- Directly rewrite Award winner without workflow
- Delete POs to fix state
- Change account ownership by editing IDs without transfer/correction rules

==========================================================
PART 28 — FINAL ADMIN DEFINITION OF COMPLETE
==========================================================

An Admin module is not complete merely because a page renders.

It is complete only when:

route works
+
platform Admin middleware works
+
permission works
+
Form Request validation works
+
Controller/Action/Service structure follows ARCHITECTURE.md
+
database integration uses existing schema
+
state transition is valid
+
transaction/idempotency works where required
+
audit/history is preserved
+
notifications work where required
+
Blade UI follows design.md
+
mobile responsiveness works
+
empty/loading/error states work
+
filters/search/pagination work
+
unauthorized buttons are hidden
+
direct unauthorized routes are blocked
+
automated tests pass
+
Buyer/Supplier workflows remain compatible
+
frontend and registration remain unchanged

==========================================================
FINAL ADMIN OBJECTIVE
==========================================================

The final EduShopify Admin Dashboard must operate as the platform's complete governance and operations center:

PLATFORM ADMIN
        │
        ├── Users & Accounts
        │     ├── Users
        │     ├── Accounts
        │     ├── Buyers
        │     ├── Suppliers
        │     ├── Members
        │     ├── Capabilities
        │     ├── Conversions
        │     └── Closures
        │
        ├── Approvals
        │     ├── Buyer / Supplier Applications
        │     ├── Supplier Documents
        │     ├── Listings
        │     ├── RFQs where configured
        │     ├── Taxonomy Requests
        │     ├── Role Requests
        │     ├── Conversion Requests
        │     └── Review Reports
        │
        ├── Catalog Governance
        │     ├── Categories
        │     ├── Attributes
        │     ├── Brands
        │     ├── Units
        │     ├── Types
        │     ├── Document Rules
        │     └── Exhibitions
        │
        ├── Procurement Oversight
        │     ├── RFQs
        │     ├── Quotations
        │     ├── Awards
        │     └── Purchase Orders
        │
        ├── Subscription & Billing
        │     ├── Plans
        │     ├── Subscriptions
        │     └── Payments
        │
        ├── Communication & Support
        │     ├── Conversations
        │     ├── Inquiries
        │     ├── Notifications
        │     └── Tickets
        │
        ├── Moderation
        │     ├── Reviews
        │     ├── Replies
        │     └── Reports
        │
        ├── Access Control
        │     ├── Platform Roles
        │     ├── Permissions
        │     └── Account Role Requests
        │
        └── System
              ├── Settings
              ├── Theme
              ├── Geography
              ├── Currencies
              ├── Languages
              ├── Audit
              └── Operational Jobs

The Admin Dashboard must provide **full platform governance without destroying workflow integrity**.

Build it as one coherent platform administration system, not a collection of disconnected CRUD pages.

Before implementing any Admin phase, read:

1. `ARCHITECTURE.md`
2. `design.md`
3. this `admin_dashboard_workflow.md`
4. the Buyer/Supplier workflow related to the feature
5. the current database schema
6. the existing code/tests

Then implement/refactor the Admin backend phase-by-phase.
