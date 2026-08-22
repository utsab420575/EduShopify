# EduShopify AI Development Instructions

Before making any code changes, read the following project specifications.

## Mandatory Documents

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