 FG # EduShopify AI Development Instructions

Before making any code changes, read the following project specifications.




## New Frontend Development Rule

We are creating a completely new public frontend design.

Do NOT modify or redesign the existing frontend:

- `resources/views/frontend/`
- `routes/frontend.php`
- `app/Http/Controllers/Frontend/`

Create the new frontend in separate folders:

- Views: `resources/views/frontend_new/`
- Routes: `routes/frontend_new.php`
- Controllers: `app/Http/Controllers/FrontendNew/`

The new frontend must be developed from scratch using only these references:

1. Frontend architecture:
   `docs/AI/Frontend/edushopify-public-frontend-complete-spec.md`

2. Page-specific frontend specifications:
- `docs/AI/Frontend/edushopify-frontend-spec.md` (Homepage)
- `docs/AI/Frontend/edushopify-product-detail-spec.md` (Product Detail)
- `docs/AI/Frontend/edushopify-supplier-detail-spec.md` (Supplier Profile)
- `docs/AI/Frontend/edushopify-rfq-detail-spec.md` (RFQ)

3. Static HTML design references:
   `docs/AI/Frontend/` (HTML files)

Static HTML represents the target UI design. Convert the static design into Blade templates and connect it with existing backend data/models.

Existing frontend code should only be used to understand backend functionality and data flow, not as a design reference.



## Mandatory Documents for backend/dashboard

1. `docs/ai/ARCHITECTURE.md`
2. `docs/ai/design.md`

Then read the workflow document related to the feature being developed.

### Buyer

`docs/ai/workflows/buyer_dashboard_workflow.md`

### Supplier

`docs/ai/workflows/supplier_dashboard_workflow.md`

### Admin

`docs/ai/workflows/admin_dashboard_workflow.md`

## Priority

If instructions appear to conflict, use this order:

1. Existing database schema and confirmed business rules
2. `docs/ai/ARCHITECTURE.md`
3. `docs/ai/design.md`
4. Relevant workflow specification
5. Existing implementation

Do not introduce backend Livewire.

Before implementing a feature:

- inspect existing code
- inspect related database tables
- reuse existing functionality
- follow the architecture
- follow the design system
- follow the relevant workflow
- preserve working functionality
