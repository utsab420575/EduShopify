# EduShopify Laravel Project Architecture — Master Instructions

You are working on an existing Laravel project named **EduShopify**.

This is a large B2B marketplace/procurement application with:

- Public frontend
- Authentication system
- Platform Admin dashboard
- Buyer dashboard
- Supplier dashboard
- Multi-account support
- Buyer and Supplier capabilities
- Account members
- Account-scoped roles
- Permission management
- Product/service listings
- RFQs
- Quotations
- Quotation revisions
- Awards
- Purchase Orders
- Supplier subscriptions
- Reviews
- Messaging
- Notifications
- Support tickets
- Supplier profiles and verification
- Account/team management

Your job is to develop and maintain this Laravel application while **strictly following the architecture defined below**.

---

# 1. CRITICAL ARCHITECTURE RULES

These rules are mandatory.

## Rule 1 — Backend must NOT use Livewire

Do NOT introduce Livewire into the backend.

Backend development must primarily use:

```text
Laravel Routes
    ↓
Middleware
    ↓
Controller
    ↓
Form Request
    ↓
Action / Service
    ↓
Model
    ↓
Blade View
```

Use standard Laravel Blade, Controllers, Form Requests, Policies, Services, Actions, Middleware, Events, Jobs, etc.

Do not generate backend Livewire components unless I explicitly request Livewire in a future instruction.

---

## Rule 2 — Frontend and Backend must stay separated

Never mix public frontend views/controllers with dashboard/backend code.

Use:

```text
Frontend
    → public website

Backend
    → Admin
    → Buyer
    → Supplier
    → Shared
```

Frontend pages must not be stored under backend folders.

Backend pages must not be stored under frontend folders.

---

## Rule 3 — Backend UI/HTTP code is portal-based

Organize the following by dashboard/portal:

```text
Controllers
Form Requests
Views
Routes
```

Use these backend portals:

```text
Admin
Buyer
Supplier
Shared
```
'''So instead of putting all controllers together like this:

app/Http/Controllers/
├── RfqController.php
├── ListingController.php
├── RoleController.php
├── AccountController.php
├── QuotationController.php
└── ...

you separate them by portal:

app/Http/Controllers/Backend/
├── Admin/
├── Buyer/
├── Supplier/
└── Shared/'''
---

## Rule 4 — Business/data layer is domain-based

Do NOT organize Models, Services, Actions, Policies, Events or business logic by Admin/Buyer/Supplier unless there is an exceptional reason.

Instead organize them by business domain:

```text
Account
AccessControl
Catalog
Procurement
Billing
Communication
Review
Support
System
```

For example:

DO:

```text
app/Models/Procurement/Rfq.php
app/Models/Procurement/Quotation.php
```

DO NOT:

```text
app/Models/Buyer/Rfq.php
app/Models/Admin/Rfq.php
```

An RFQ is one business entity used by several dashboards.

---

# 2. USER / ACCOUNT / CAPABILITY ARCHITECTURE

The application should conceptually follow:

```text
User
  ↓
Account Membership
  ↓
Account
  ↓
Capabilities
  ├── Buyer
  └── Supplier
  ↓
Dashboard Mode
  ↓
Role
  ↓
Permission
```

Do not assume that a user permanently belongs to only one dashboard role.

An account may have:

```text
Buyer capability
Supplier capability
or both
```

The dashboard context determines whether the user is currently operating as:

```text
Buyer
or
Supplier
```

---

# 3. ROLE MANAGEMENT ARCHITECTURE

Admin, Buyer and Supplier dashboards all contain a **Role Management** menu.

However, their scopes are different.

## Admin Role Management

Admin manages platform/system roles and platform permissions.

Possible examples:

```text
Super Admin
Platform Admin
Account Approver
Supplier Verifier
Content Moderator
Support Manager
Finance Admin
```

Admin role-management controllers:

```text
app/Http/Controllers/Backend/Admin/AccessControl/
├── RoleController.php
└── PermissionController.php
```

Views:

```text
resources/views/backend/admin/access-control/
├── roles/
└── permissions/
```

---

## Buyer Role Management

A Buyer account owner/admin can create custom account roles.

Examples:

```text
Buyer Owner
Procurement Manager
Procurement Officer
Finance Officer
Reviewer
Employee
Viewer
Custom Role
```

Do NOT create separate dashboards/controllers for each of these roles.

They all use the existing Buyer dashboard.

Access is controlled by permissions.

Controllers:

```text
app/Http/Controllers/Backend/Buyer/AccessControl/
├── RoleController.php
└── PermissionController.php
```

Views:

```text
resources/views/backend/buyer/access-control/
├── roles/
└── permissions/
```

---

## Supplier Role Management

Supplier accounts can also create custom roles.

Examples:

```text
Supplier Owner
Sales Manager
Sales Officer
Quotation Officer
Catalog Manager
Finance Officer
Support Officer
Employee
Viewer
Custom Role
```

They all use the Supplier dashboard.

Controllers:

```text
app/Http/Controllers/Backend/Supplier/AccessControl/
├── RoleController.php
└── PermissionController.php
```

Views:

```text
resources/views/backend/supplier/access-control/
├── roles/
└── permissions/
```

---

# 4. PERMISSION ARCHITECTURE

Permissions should generally be centrally defined by the platform.

Buyer/Supplier account owners create roles by selecting from the permissions available for their capability.

Example permission naming:

```text
rfq.view
rfq.create
rfq.edit
rfq.publish
rfq.cancel

quotation.view
quotation.compare
quotation.shortlist
quotation.award

listing.view
listing.create
listing.edit
listing.delete
listing.publish

purchase-order.view
purchase-order.update

member.view
member.invite
member.remove

role.view
role.create
role.edit
role.delete
role.assign

subscription.view
subscription.manage
```

Do not scatter manual role-name checks through controllers.

Avoid logic like:

```php
if ($user->role === 'manager') {
    //
}
```

Prefer:

```php
$user->can('rfq.create')
```

or Laravel authorization through Policies.

---

# 5. ROOT APPLICATION STRUCTURE

Follow this architecture:

```text
app/
│
├── Actions/
│   ├── Account/
│   ├── AccessControl/
│   ├── Catalog/
│   ├── Procurement/
│   ├── Billing/
│   ├── Communication/
│   ├── Review/
│   └── Support/
│
├── Http/
│   │
│   ├── Controllers/
│   │   ├── Frontend/
│   │   │
│   │   └── Backend/
│   │       ├── Admin/
│   │       ├── Buyer/
│   │       ├── Supplier/
│   │       └── Shared/
│   │
│   ├── Middleware/
│   │
│   └── Requests/
│       ├── Frontend/
│       │
│       └── Backend/
│           ├── Admin/
│           ├── Buyer/
│           ├── Supplier/
│           └── Shared/
│
├── Models/
│   ├── User.php
│   ├── Account/
│   ├── AccessControl/
│   ├── Catalog/
│   ├── Procurement/
│   ├── Billing/
│   ├── Communication/
│   ├── Review/
│   ├── Support/
│   └── System/
│
├── Policies/
│   ├── Account/
│   ├── AccessControl/
│   ├── Catalog/
│   ├── Procurement/
│   ├── Billing/
│   ├── Communication/
│   ├── Review/
│   └── Support/
│
├── Services/
│   ├── Account/
│   ├── AccessControl/
│   ├── Procurement/
│   ├── Billing/
│   ├── Media/
│   └── Notification/
│
├── Events/
├── Listeners/
├── Jobs/
│
└── Support/
    ├── Enums/
    ├── Helpers/
    └── Traits/
```

---

# 6. BACKEND CONTROLLER STRUCTURE

Use:

```text
app/Http/Controllers/Backend/
│
├── Admin/
├── Buyer/
├── Supplier/
└── Shared/
```

Never put dozens of unrelated controllers directly inside:

```text
Backend/
```

Keep them grouped by portal and business module.

---

# 7. ADMIN CONTROLLERS

Follow approximately this structure:

```text
app/Http/Controllers/Backend/Admin/
│
├── DashboardController.php
│
├── Account/
│   ├── AccountController.php
│   ├── AccountMemberController.php
│   ├── AccountCapabilityController.php
│   ├── AccountConversionController.php
│   └── RoleRequestController.php
│
├── User/
│   └── UserController.php
│
├── AccessControl/
│   ├── RoleController.php
│   └── PermissionController.php
│
├── Catalog/
│   ├── CategoryController.php
│   ├── AttributeController.php
│   ├── AttributeValueController.php
│   ├── UnitController.php
│   ├── BrandController.php
│   └── ListingController.php
│
├── Procurement/
│   ├── RfqController.php
│   ├── QuotationController.php
│   ├── AwardController.php
│   └── PurchaseOrderController.php
│
├── Buyer/
│   └── BuyerController.php
│
├── Supplier/
│   ├── SupplierController.php
│   ├── SupplierDocumentController.php
│   └── SupplierVerificationController.php
│
├── Billing/
│   ├── SubscriptionPlanController.php
│   ├── SubscriptionController.php
│   └── PaymentController.php
│
├── Review/
│   ├── ReviewController.php
│   ├── ReviewReplyController.php
│   └── ReviewReportController.php
│
├── Communication/
│   ├── ConversationController.php
│   └── ContactInquiryController.php
│
├── Support/
│   └── TicketController.php
│
└── System/
    ├── CountryController.php
    ├── StateController.php
    ├── CityController.php
    ├── CurrencyController.php
    ├── LanguageController.php
    ├── DocumentTypeController.php
    └── SettingController.php
```

---

# 8. BUYER CONTROLLERS

Use:

```text
app/Http/Controllers/Backend/Buyer/
│
├── DashboardController.php
│
├── Procurement/
│   ├── RfqController.php
│   ├── RfqQuestionController.php
│   ├── QuotationController.php
│   ├── ShortlistController.php
│   ├── AwardController.php
│   └── PurchaseOrderController.php
│
├── Supplier/
│   ├── SupplierDirectoryController.php
│   └── SavedSupplierController.php
│
├── Review/
│   └── ReviewController.php
│
└── AccessControl/
    ├── RoleController.php
    └── PermissionController.php
```

Do not create:

```text
Buyer/ProcurementManager/
Buyer/FinanceOfficer/
Buyer/Employee/
```

Those are authorization roles, not architectural modules.

---

# 9. SUPPLIER CONTROLLERS

Use:

```text
app/Http/Controllers/Backend/Supplier/
│
├── DashboardController.php
│
├── Catalog/
│   ├── ListingController.php
│   ├── ProductController.php
│   ├── ServiceController.php
│   ├── VariantController.php
│   ├── TierPriceController.php
│   └── MediaController.php
│
├── Procurement/
│   ├── OpportunityController.php
│   ├── QuotationController.php
│   ├── QuotationRevisionController.php
│   ├── AwardController.php
│   └── PurchaseOrderController.php
│
├── Company/
│   ├── ProfileController.php
│   ├── DocumentController.php
│   ├── GalleryController.php
│   ├── VideoController.php
│   ├── ServiceAreaController.php
│   └── BusinessHourController.php
│
├── Billing/
│   ├── SubscriptionController.php
│   └── PaymentController.php
│
├── Review/
│   └── ReviewController.php
│
└── AccessControl/
    ├── RoleController.php
    └── PermissionController.php
```

---

# 10. SHARED BACKEND CONTROLLERS

Features shared between Buyer and Supplier should not be unnecessarily duplicated.

Use:

```text
app/Http/Controllers/Backend/Shared/
│
├── Account/
│   ├── ProfileController.php
│   ├── MemberController.php
│   ├── InvitationController.php
│   ├── LocationController.php
│   └── OwnershipController.php
│
├── Communication/
│   ├── ConversationController.php
│   └── MessageController.php
│
├── Notification/
│   └── NotificationController.php
│
├── Support/
│   └── TicketController.php
│
└── Settings/
    ├── ProfileController.php
    ├── SecurityController.php
    └── PreferenceController.php
```

---

# 11. BACKEND BLADE STRUCTURE

The backend view structure must be:

```text
resources/views/backend/
│
├── layouts/
├── components/
├── admin/
├── buyer/
├── supplier/
└── shared/
```

Detailed structure:

```text
resources/views/backend/
│
├── layouts/
│   ├── master.blade.php
│   ├── admin.blade.php
│   ├── buyer.blade.php
│   ├── supplier.blade.php
│   │
│   └── partials/
│       ├── shared/
│       │   ├── _head.blade.php
│       │   ├── _scripts.blade.php
│       │   ├── _footer.blade.php
│       │   ├── _flash.blade.php
│       │   ├── _breadcrumb.blade.php
│       │   └── _modal.blade.php
│       │
│       ├── admin/
│       │   ├── _sidebar.blade.php
│       │   └── _topbar.blade.php
│       │
│       ├── buyer/
│       │   ├── _sidebar.blade.php
│       │   └── _topbar.blade.php
│       │
│       └── supplier/
│           ├── _sidebar.blade.php
│           └── _topbar.blade.php
│
├── components/
│   ├── cards/
│   ├── forms/
│   ├── tables/
│   ├── modals/
│   ├── badges/
│   └── common/
│
├── admin/
├── buyer/
├── supplier/
└── shared/
```

---

# 12. BACKEND LAYOUT HIERARCHY

Use this hierarchy:

```text
master.blade.php
      │
      ├── admin.blade.php
      ├── buyer.blade.php
      └── supplier.blade.php
              │
              ↓
          Actual pages
```

The master layout contains common HTML structure.

Example responsibilities:

```text
master.blade.php
├── HTML
├── common head
├── global CSS
├── global JS
├── @stack('styles')
└── @stack('scripts')
```

Portal layouts contain their corresponding:

```text
Sidebar
Topbar
Main Content
Footer
```

---

# 13. ADMIN VIEW STRUCTURE

Use:

```text
resources/views/backend/admin/
│
├── dashboard/
│   └── index.blade.php
│
├── accounts/
│   ├── accounts/
│   ├── members/
│   ├── capabilities/
│   ├── conversions/
│   └── role-requests/
│
├── users/
│
├── access-control/
│   ├── roles/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── permissions.blade.php
│   │
│   └── permissions/
│       └── index.blade.php
│
├── catalog/
│   ├── categories/
│   ├── attributes/
│   ├── units/
│   ├── brands/
│   └── listings/
│
├── procurement/
│   ├── rfqs/
│   ├── quotations/
│   ├── awards/
│   └── purchase-orders/
│
├── buyers/
│
├── suppliers/
│   ├── profiles/
│   ├── documents/
│   └── verifications/
│
├── subscriptions/
│   ├── plans/
│   ├── subscriptions/
│   └── payments/
│
├── reviews/
│   ├── reviews/
│   ├── reports/
│   └── replies/
│
├── communication/
│   ├── conversations/
│   └── inquiries/
│
├── support/
│   └── tickets/
│
└── system/
    ├── countries/
    ├── states/
    ├── cities/
    ├── currencies/
    ├── languages/
    ├── document-types/
    └── settings/
```

---

# 14. BUYER VIEW STRUCTURE

Use:

```text
resources/views/backend/buyer/
│
├── dashboard/
│   └── index.blade.php
│
├── procurement/
│   ├── rfqs/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── show.blade.php
│   │   └── partials/
│   │
│   ├── quotations/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   └── compare.blade.php
│   │
│   ├── shortlists/
│   ├── awards/
│   └── purchase-orders/
│
├── suppliers/
│   ├── directory/
│   └── saved/
│
├── reviews/
│
└── access-control/
    ├── roles/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── show.blade.php
    │   └── permissions.blade.php
    │
    └── permissions/
        └── index.blade.php
```

---

# 15. SUPPLIER VIEW STRUCTURE

Use:

```text
resources/views/backend/supplier/
│
├── dashboard/
│   └── index.blade.php
│
├── catalog/
│   ├── listings/
│   ├── products/
│   ├── services/
│   ├── variants/
│   └── pricing/
│
├── procurement/
│   ├── opportunities/
│   ├── quotations/
│   ├── revision-requests/
│   ├── awards/
│   └── purchase-orders/
│
├── company/
│   ├── profile/
│   ├── documents/
│   ├── gallery/
│   ├── videos/
│   ├── service-areas/
│   └── business-hours/
│
├── subscription/
│   ├── plans/
│   ├── current/
│   └── payments/
│
├── reviews/
│
└── access-control/
    ├── roles/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── show.blade.php
    │   └── permissions.blade.php
    │
    └── permissions/
        └── index.blade.php
```

---

# 16. SHARED BACKEND VIEW STRUCTURE

Use:

```text
resources/views/backend/shared/
│
├── account/
│   ├── profile/
│   ├── members/
│   ├── invitations/
│   ├── locations/
│   └── ownership/
│
├── messages/
│   ├── index.blade.php
│   └── show.blade.php
│
├── notifications/
│
├── support/
│   └── tickets/
│
└── settings/
    ├── profile/
    ├── security/
    └── preferences/
```

---

# 17. MODEL STRUCTURE

Models must be domain-based.

Use:

```text
app/Models/
│
├── User.php
│
├── Account/
│   ├── Account.php
│   ├── AccountMember.php
│   ├── AccountCapability.php
│   ├── AccountLocation.php
│   ├── AccountMemberInvitation.php
│   ├── AccountOwnershipTransfer.php
│   ├── AccountConversionRequest.php
│   ├── BuyerProfile.php
│   └── SupplierProfile.php
│
├── AccessControl/
│   ├── Role.php
│   └── Permission.php
│
├── Catalog/
│   ├── Listing.php
│   ├── ProductDetail.php
│   ├── ServiceDetail.php
│   ├── ListingVariant.php
│   ├── Category.php
│   ├── Attribute.php
│   ├── AttributeValue.php
│   ├── Brand.php
│   └── Unit.php
│
├── Procurement/
│   ├── Rfq.php
│   ├── RfqItem.php
│   ├── RfqQuestion.php
│   ├── RfqShortlist.php
│   ├── Quotation.php
│   ├── QuotationItem.php
│   ├── QuotationRevision.php
│   ├── QuotationRevisionItem.php
│   ├── QuotationRevisionRequest.php
│   ├── Award.php
│   ├── PurchaseOrder.php
│   └── PurchaseOrderItem.php
│
├── Billing/
│   ├── Subscription.php
│   ├── SubscriptionPlan.php
│   └── SubscriptionPayment.php
│
├── Communication/
│   ├── Conversation.php
│   └── Message.php
│
├── Review/
│   ├── Review.php
│   ├── ReviewReply.php
│   └── ReviewReport.php
│
├── Support/
│   ├── Ticket.php
│   └── TicketMessage.php
│
└── System/
    ├── Country.php
    ├── State.php
    ├── City.php
    ├── Currency.php
    ├── Language.php
    └── DocumentType.php
```

When adding a new model, place it in the most appropriate domain.

Do not create duplicate models for different dashboards.

---

# 18. FORM REQUEST ARCHITECTURE

Do not place large validation logic directly inside controllers.

Use Form Requests.

Structure:

```text
app/Http/Requests/
├── Frontend/
│
└── Backend/
    ├── Admin/
    ├── Buyer/
    ├── Supplier/
    └── Shared/
```

Example:

```text
app/Http/Requests/Backend/Buyer/Procurement/
├── StoreRfqRequest.php
├── UpdateRfqRequest.php
└── AwardQuotationRequest.php
```

Supplier:

```text
app/Http/Requests/Backend/Supplier/Catalog/
├── StoreListingRequest.php
└── UpdateListingRequest.php
```

Access control:

```text
app/Http/Requests/Backend/Buyer/AccessControl/
├── StoreRoleRequest.php
└── UpdateRoleRequest.php
```

---

# 19. ACTION CLASSES

Complicated business operations must not produce huge controllers.

Use Action classes.

Recommended structure:

```text
app/Actions/
│
├── Account/
│   ├── CreateAccount.php
│   ├── InviteAccountMember.php
│   ├── RemoveAccountMember.php
│   └── TransferOwnership.php
│
├── AccessControl/
│   ├── CreateAccountRole.php
│   ├── UpdateAccountRole.php
│   ├── DeleteAccountRole.php
│   ├── AssignRoleToMember.php
│   └── SyncRolePermissions.php
│
├── Catalog/
│   ├── CreateListing.php
│   ├── UpdateListing.php
│   └── PublishListing.php
│
└── Procurement/
    ├── CreateRfq.php
    ├── UpdateRfq.php
    ├── PublishRfq.php
    ├── CloseRfq.php
    ├── SubmitQuotation.php
    ├── RequestQuotationRevision.php
    ├── ShortlistQuotation.php
    ├── AwardQuotation.php
    ├── AcceptAward.php
    └── CreatePurchaseOrder.php
```

Controllers should coordinate HTTP requests.

Actions should perform business operations.

---

# 20. SERVICE CLASSES

Services should provide reusable business/infrastructure functionality.

Use:

```text
app/Services/
│
├── Account/
│   └── AccountContextService.php
│
├── AccessControl/
│   └── PermissionService.php
│
├── Procurement/
│   ├── RfqMatchingService.php
│   └── QuotationComparisonService.php
│
├── Billing/
│   └── SubscriptionService.php
│
├── Media/
│   └── MediaService.php
│
└── Notification/
    └── NotificationService.php
```

General distinction:

```text
Action
    = execute one business use case

Service
    = reusable domain/infrastructure functionality
```

Do not create unnecessary Service classes for simple CRUD operations.

---

# 21. POLICY ARCHITECTURE

Authorization must primarily use:

```text
Middleware
Policies
Permissions
```

Use:

```text
app/Policies/
│
├── Account/
│   ├── AccountPolicy.php
│   └── AccountMemberPolicy.php
│
├── AccessControl/
│   └── RolePolicy.php
│
├── Catalog/
│   └── ListingPolicy.php
│
├── Procurement/
│   ├── RfqPolicy.php
│   ├── QuotationPolicy.php
│   ├── AwardPolicy.php
│   └── PurchaseOrderPolicy.php
│
├── Billing/
│   └── SubscriptionPolicy.php
│
└── Review/
    └── ReviewPolicy.php
```

Prefer:

```php
$this->authorize('update', $rfq);
```

instead of manually checking roles repeatedly.

Policies must consider:

```text
Current user
Current account
Account membership
Current capability
Resource ownership
Role
Permission
```

---

# 22. MIDDLEWARE ARCHITECTURE

Use middleware for request-context/access checks.

Recommended middleware:

```text
app/Http/Middleware/
├── SetCurrentAccount.php
├── EnsureAccountMember.php
├── EnsureActiveAccount.php
├── EnsureApprovedAccount.php
├── EnsureCapability.php
├── EnsurePlatformAdmin.php
├── SetDashboardMode.php
└── EnsurePermission.php
```

Expected backend request flow:

```text
Authenticated User
       ↓
Current Account
       ↓
Account Membership
       ↓
Account Status
       ↓
Capability
       ↓
Dashboard Mode
       ↓
Role
       ↓
Permission
       ↓
Policy
       ↓
Controller
```

---

# 23. ROUTE STRUCTURE

Use:

```text
routes/
├── web.php
├── auth.php
├── frontend.php
│
└── backend/
    ├── admin.php
    ├── buyer.php
    ├── supplier.php
    └── shared.php
```

Keep `web.php` small.

It should mainly load the route files.

Conceptually:

```php
require __DIR__.'/frontend.php';
require __DIR__.'/auth.php';

require __DIR__.'/backend/admin.php';
require __DIR__.'/backend/buyer.php';
require __DIR__.'/backend/supplier.php';
require __DIR__.'/backend/shared.php';
```

---

# 24. ROUTE NAMING

Admin:

```text
admin.*
```

Examples:

```text
admin.dashboard
admin.accounts.index
admin.catalog.categories.index
admin.procurement.rfqs.index
admin.access-control.roles.index
```

Buyer:

```text
buyer.*
```

Examples:

```text
buyer.dashboard
buyer.rfqs.index
buyer.rfqs.create
buyer.quotations.index
buyer.purchase-orders.index
buyer.roles.index
```

Supplier:

```text
supplier.*
```

Examples:

```text
supplier.dashboard
supplier.listings.index
supplier.opportunities.index
supplier.quotations.index
supplier.purchase-orders.index
supplier.roles.index
```

Shared:

```text
account.*
messages.*
notifications.*
support.*
```

Use predictable names consistently.

---

# 25. URL STRUCTURE

Use:

```text
/admin/*
/buyer/*
/supplier/*
```

Examples:

```text
/admin/dashboard
/admin/users
/admin/catalog/categories

/buyer/dashboard
/buyer/rfqs
/buyer/quotations
/buyer/roles

/supplier/dashboard
/supplier/listings
/supplier/opportunities
/supplier/roles
```

Shared URLs may use:

```text
/account/*
/messages/*
/notifications/*
/support/*
```

---

# 26. FRONTEND STRUCTURE

Keep frontend completely separate:

```text
resources/views/frontend/
│
├── layouts/
│   ├── master.blade.php
│   │
│   └── partials/
│       ├── _head.blade.php
│       ├── _header.blade.php
│       ├── _footer.blade.php
│       └── _scripts.blade.php
│
├── components/
│
└── pages/
    ├── home/
    ├── categories/
    ├── listings/
    ├── suppliers/
    ├── rfqs/
    ├── contact/
    └── about/
```

Frontend controllers belong in:

```text
app/Http/Controllers/Frontend/
```

---

# 27. ADMIN SIDEBAR ARCHITECTURE

The Admin sidebar should conceptually contain:

```text
Dashboard

Account Management
├── Accounts
├── Capabilities
├── Conversion Requests
└── Role Requests

User Management
└── Users

Role Management
├── Roles
└── Permissions

Catalog Management
├── Categories
├── Attributes
├── Units
├── Brands
└── Listings

Procurement
├── RFQs
├── Quotations
├── Awards
└── Purchase Orders

Buyer Management

Supplier Management

Subscription Management

Reviews

Communication

Support
└── Tickets

System
├── Countries
├── States
├── Cities
├── Currencies
├── Languages
├── Document Types
└── Settings
```

Sidebar links must be permission-aware where appropriate.

---

# 28. BUYER SIDEBAR ARCHITECTURE

Use approximately:

```text
Dashboard

Procurement
├── My RFQs
├── Create RFQ
├── Quotations
├── Shortlisted Quotations
├── Awards
└── Purchase Orders

Suppliers
├── Supplier Directory
└── Saved Suppliers

Reviews

Team Management
├── Members
└── Invitations

Role Management
├── Roles
└── Permissions

Messages

Notifications

Support

Account Settings
├── Profile
├── Locations
├── Security
└── Preferences
```

Menu visibility should respect permissions.

For example:

```blade
@can('rfq.create')
    ...
@endcan
```

or equivalent account-aware permission checks.

---

# 29. SUPPLIER SIDEBAR ARCHITECTURE

Use approximately:

```text
Dashboard

Catalog
├── Listings
├── Products
├── Services
└── Pricing

Procurement
├── RFQ Opportunities
├── Quotations
├── Revision Requests
├── Awards
└── Purchase Orders

Company
├── Profile
├── Documents
├── Gallery
├── Videos
├── Service Areas
└── Business Hours

Subscription
├── Current Plan
├── Plans
└── Payments

Reviews

Team Management
├── Members
└── Invitations

Role Management
├── Roles
└── Permissions

Messages

Notifications

Support

Account Settings
```

---

# 30. BLADE PAGE CONVENTION

Resource pages should generally follow:

```text
module/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
└── partials/
```

Example:

```text
resources/views/backend/buyer/procurement/rfqs/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
└── partials/
    ├── _form.blade.php
    ├── _items.blade.php
    └── _status.blade.php
```

Do not duplicate large forms between create/edit pages.

Extract reusable fragments into local `partials/`.

---

# 31. CONTROLLER NAMING RULE

Use singular resource names:

```text
RfqController
QuotationController
PurchaseOrderController
RoleController
CategoryController
ListingController
```

Do NOT use inconsistent names such as:

```text
RfqsController
ManageRFQsController
BuyerRfqManagementController
```

unless there is a very specific architectural reason.

---

# 32. CONTROLLER RESPONSIBILITY

Controllers must remain thin.

Good controller responsibilities:

```text
Receive request
Authorize
Call action/service
Load required data
Return view
Redirect response
```

Avoid putting:

```text
Large database transactions
Complex workflow logic
Large validation arrays
Permission engines
Notification orchestration
Long transformation logic
```

directly inside controllers.

Move those to the proper Actions / Services / Policies / Requests.

---

# 33. ELOQUENT RULES

Use proper Eloquent relationships.

Examples:

```text
Account
├── members
├── capabilities
├── locations
├── buyerProfile
├── supplierProfile
└── roles

Rfq
├── buyerAccount
├── items
├── quotations
├── questions
├── shortlists
└── awards

Quotation
├── rfq
├── supplierAccount
├── items
├── revisions
└── award

PurchaseOrder
├── award
├── rfq
├── quotation
├── buyerAccount
├── supplierAccount
└── items
```

Avoid unnecessary raw queries when Eloquent relationships are appropriate.

Avoid N+1 queries.

Use eager loading when necessary.

---

# 34. DATABASE SAFETY

The existing database schema is the source of truth.

Before generating migrations or changing database structure:

1. Inspect the current migration/schema.
2. Inspect existing model relationships.
3. Verify whether the required table/column already exists.
4. Do not create duplicate columns or duplicate tables.
5. Do not rename existing schema elements unless explicitly requested.
6. Do not delete existing database structures without explicit approval.

When working with the imported existing database, adapt Laravel code to the schema rather than assuming a generic marketplace schema.

---

# 35. EXISTING PROJECT SAFETY RULE

This is an existing Laravel project.

Do NOT blindly regenerate or replace the whole application.

Before modifying a feature:

```text
1. Inspect existing relevant files
2. Understand current implementation
3. Identify affected modules
4. Preserve working behavior
5. Refactor incrementally
6. Follow this architecture for new/refactored code
```

Do NOT delete working controllers/views/routes simply because their current location differs from the target architecture.

If restructuring existing code:

```text
Move incrementally
Update namespaces
Update imports
Update routes
Update view paths
Update tests
Verify functionality
```

---

# 36. SHARED VS DUPLICATED LOGIC

Before creating similar code in Buyer and Supplier modules, determine whether the functionality is truly portal-specific.

For example:

```text
Account profile
Team members
Invitations
Locations
Messages
Notifications
Support tickets
Security
```

should generally use:

```text
Backend/Shared
```

Buyer-specific:

```text
Create RFQ
Compare quotations
Shortlist quotation
Award quotation
```

Supplier-specific:

```text
Listings
RFQ Opportunities
Submit quotations
Company documents
Supplier subscriptions
```

Admin-specific:

```text
Approvals
Moderation
Platform management
Master data
Permission definitions
Supplier verification
```

---

# 37. NO ROLE-BASED DIRECTORY EXPLOSION

Never create architecture such as:

```text
Backend/
├── BuyerOwner/
├── BuyerManager/
├── BuyerEmployee/
├── SupplierOwner/
├── SalesManager/
├── SalesOfficer/
└── CatalogManager/
```

This is prohibited unless explicitly requested later.

Correct architecture:

```text
Backend/
├── Admin/
├── Buyer/
├── Supplier/
└── Shared/
```

Roles only determine permissions inside these portals.

---

# 38. ACCOUNT-SCOPED AUTHORIZATION

Whenever operating on Buyer/Supplier resources, always consider account boundaries.

A user must never access another account's private records merely by changing an ID in the URL.

For resources such as:

```text
RFQ
Quotation
Purchase Order
Listing
Subscription
Team Member
Role
Conversation
Ticket
```

authorization should verify the current account.

Never rely only on:

```php
Model::findOrFail($id);
```

followed by no ownership/authorization check.

Use Policies, account-scoped queries, route binding or proper authorization.

---

# 39. ROLE SECURITY RULES

Account-created roles must stay within the current account.

Buyer roles must not accidentally gain Supplier-only permissions.

Supplier roles must not accidentally gain Buyer-only permissions.

Platform permissions must not be assignable to normal Buyer/Supplier custom roles unless explicitly permitted.

Respect concepts like:

```text
capability_scope
is_sensitive
is_owner_only
is_assignable
is_active
```

when building role management.

Owner-only permissions must not be assignable to ordinary members.

Sensitive permissions should receive extra validation/authorization.

---

# 40. DASHBOARD MODE

If an account has both Buyer and Supplier capabilities, support switching between:

```text
Buyer Dashboard
Supplier Dashboard
```

The mode affects:

```text
Sidebar
Routes
Dashboard
Permission scope
Available functionality
```

Do not duplicate the user's account.

Do not treat Buyer/Supplier as completely separate users if the same account supports both capabilities.

---

# 41. FILE / CLASS PLACEMENT CHECKLIST

Before creating any new file, decide:

## Is it HTTP/UI?

Then ask:

```text
Admin?
Buyer?
Supplier?
Shared?
Frontend?
```

Examples:

```text
Controller
Form Request
Blade View
Route
```

Place according to portal.

## Is it domain/business logic?

Then ask:

```text
Account?
AccessControl?
Catalog?
Procurement?
Billing?
Communication?
Review?
Support?
System?
```

Examples:

```text
Model
Action
Service
Policy
Event
Job
```

Place according to domain.

---

# 42. EXAMPLE FEATURE PLACEMENT

If implementing Buyer RFQ creation:

```text
Controller:
app/Http/Controllers/Backend/Buyer/Procurement/RfqController.php

Request:
app/Http/Requests/Backend/Buyer/Procurement/StoreRfqRequest.php

Action:
app/Actions/Procurement/CreateRfq.php

Models:
app/Models/Procurement/Rfq.php
app/Models/Procurement/RfqItem.php

Policy:
app/Policies/Procurement/RfqPolicy.php

Views:
resources/views/backend/buyer/procurement/rfqs/

Routes:
routes/backend/buyer.php
```

---

If implementing Supplier Listing Management:

```text
Controller:
app/Http/Controllers/Backend/Supplier/Catalog/ListingController.php

Requests:
app/Http/Requests/Backend/Supplier/Catalog/

Actions:
app/Actions/Catalog/

Models:
app/Models/Catalog/

Policy:
app/Policies/Catalog/ListingPolicy.php

Views:
resources/views/backend/supplier/catalog/listings/

Routes:
routes/backend/supplier.php
```

---

If implementing Buyer Role Management:

```text
Controller:
app/Http/Controllers/Backend/Buyer/AccessControl/RoleController.php

Requests:
app/Http/Requests/Backend/Buyer/AccessControl/

Action:
app/Actions/AccessControl/CreateAccountRole.php
app/Actions/AccessControl/SyncRolePermissions.php

Models:
app/Models/AccessControl/Role.php
app/Models/AccessControl/Permission.php

Policy:
app/Policies/AccessControl/RolePolicy.php

Views:
resources/views/backend/buyer/access-control/roles/

Routes:
routes/backend/buyer.php
```

---

# 43. DEVELOPMENT WORKFLOW FOR EVERY TASK

Whenever I ask you to implement a feature, follow this process:

```text
STEP 1
Inspect the existing project files related to the feature.

STEP 2
Inspect related database tables/models/migrations.

STEP 3
Determine whether the feature belongs to:
Frontend
Admin
Buyer
Supplier
Shared

STEP 4
Determine the business domain:
Account
AccessControl
Catalog
Procurement
Billing
Communication
Review
Support
System

STEP 5
Reuse existing Models/Services/Actions where appropriate.

STEP 6
Create/update Controller.

STEP 7
Create/update Form Request.

STEP 8
Create/update Policy/permission checks.

STEP 9
Create Action/Service only where business complexity justifies it.

STEP 10
Create/update Blade views in the correct portal/module.

STEP 11
Add/update routes in the correct route file.

STEP 12
Update sidebar navigation only if appropriate.

STEP 13
Ensure account isolation and permissions.

STEP 14
Verify namespaces, imports, route names and view paths.

STEP 15
Test existing and new behavior.
```

---

# 44. WHEN RESPONDING TO DEVELOPMENT REQUESTS

Before making major architectural changes, explain:

```text
Files being created
Files being modified
Reason for placement
Relevant route
Relevant permission
Relevant database models
```

Do not randomly introduce new folder patterns.

If an existing pattern conflicts with this architecture, point it out and migrate toward this architecture carefully.

---

# 45. FINAL ARCHITECTURAL PRINCIPLE

Always remember this rule:

```text
Controllers
Requests
Views
Routes
        ↓
ORGANIZE BY PORTAL

Admin
Buyer
Supplier
Shared
Frontend
```

But:

```text
Models
Actions
Services
Policies
Events
Jobs
        ↓
ORGANIZE BY BUSINESS DOMAIN

Account
AccessControl
Catalog
Procurement
Billing
Communication
Review
Support
System
```

Authorization follows:

```text
User
 ↓
Current Account
 ↓
Account Membership
 ↓
Capability
 ↓
Dashboard Mode
 ↓
Role
 ↓
Permission
 ↓
Policy
 ↓
Business Operation
```

This architecture is the standard for this project.

Do not deviate from it without explaining why and receiving explicit approval.

---

# 46. MOST IMPORTANT RESTRICTIONS

Never:

- introduce backend Livewire without explicit instruction
- mix frontend and backend views
- create separate dashboards for custom roles
- duplicate the same model for Buyer/Admin/Supplier
- place complex business logic inside controllers
- place large validation logic inside controllers
- bypass Policies/permissions for sensitive resources
- allow cross-account data access
- allow Buyer custom roles to receive inappropriate Supplier/platform permissions
- allow Supplier custom roles to receive inappropriate Buyer/platform permissions
- replace existing working project code blindly
- create duplicate tables/columns without checking the database
- create random new folder conventions
- make large architectural changes without first examining the existing code

Always preserve:

```text
Maintainability
Separation of concerns
Account isolation
Authorization security
Reusable business logic
Consistent namespaces
Consistent route names
Consistent view paths
Clean folder organization
Thin controllers
Domain-oriented models
```

Use this architecture as the **project-wide development standard for EduShopify**.
