# EduShopify Backend Dashboard Design System — `design.md`

> **Status:** Project-wide mandatory UI/UX specification.
>
> This file is the design source of truth for the EduShopify backend dashboards:
>
> - Admin
> - Buyer
> - Supplier
> - Shared backend pages
>
> It must be used together with `ARCHITECTURE.md` and the relevant workflow specification such as `buyer_dashboard_workflow.md`.
>
> The visual baseline comes from `edushopify_dashboard_reference.html`. When reproducing layouts, tables, forms, cards, list boxes, dropdowns, sidebars, topbars, buttons, filters, status badges, and spacing, the static reference HTML is the **primary visual reference**.
>
> `ARCHITECTURE.md` remains the source of truth for code/folder architecture. This file controls visual structure and UI behavior.
>
> **Backend rule:** Do not introduce Livewire. Use Laravel Blade, normal routes/controllers/Form Requests, standard forms, and Alpine.js or small JavaScript for UI-only interactivity.

---

## 0. Mandatory Design Priority

When an AI agent or developer builds or modifies backend UI, use this order:

```text
1. ARCHITECTURE.md
   → code structure, folders, portal/domain separation

2. design.md
   → project-wide UI/UX rules

3. edushopify_dashboard_reference.html
   → exact visual reference and component appearance

4. Relevant workflow file
   → business functionality and page requirements

5. Existing reusable Blade components/styles
   → reuse before creating duplicates
```

If the static HTML and this document differ visually, prefer the static HTML **unless this document explicitly declares a project-wide rule**, such as runtime themeability, authorization-aware menus, semantic status colors, accessibility, or responsive behavior.

Do not redesign the application with a different visual identity.

---

## 0.1 Design Goals

EduShopify is a B2B education procurement marketplace. The backend must feel:

- professional
- modern
- clean
- enterprise-oriented
- compact enough for data-heavy work
- easy to scan
- consistent across Admin, Buyer, Supplier, and Shared pages
- responsive
- permission-aware
- accessible
- reusable
- themeable from Admin settings

The reference style is:

```text
Inter
+
Gray-50 application canvas
+
White bordered surfaces
+
Indigo default accent
+
Compact enterprise tables
+
Rounded-lg / rounded-xl controls
+
Subtle transitions
+
Minimal shadows
```

Avoid creating a second design language.

---

## 0.2 Portal Consistency

Admin, Buyer, and Supplier use the **same component system**.

Differences come from:

- sidebar/menu content
- permissions
- workflow
- dashboard metrics
- optional portal accent

They should not look like separate applications.

Default accent values:

```text
Admin    → Indigo #4F46E5
Buyer    → Indigo #4F46E5
Supplier → Teal   #0D9488 where Supplier context requires its own accent
```

The actual active colors must be exposed through CSS variables rather than hard-coded into every Blade page.

---

## 0.3 Backend Technology & UI Reuse Rules

Use:

```text
Laravel Blade
Tailwind CSS
Blade Components / shared partials for repeated UI
Alpine.js for lightweight browser-side interaction
Font Awesome 6
Inter
```

Backend business logic remains in Laravel according to `ARCHITECTURE.md`.

Do not use Alpine.js as a replacement for:

- authorization
- validation that belongs on the server
- workflow/business rules
- database state
- permissions
- ownership checks

Do not introduce:

```text
Backend Livewire
Filament
Bootstrap
another competing UI framework
a second icon system without a strong reason
```

### 0.3.1 Mandatory Reuse Decision

Use the following rule throughout EduShopify:

```text
Repeated HTML / repeated visual design
        ↓
Blade Component or shared Blade partial

Simple one-page/local interaction
        ↓
Inline Alpine x-data

Repeated or complex JavaScript behavior
        ↓
Alpine.data()

Backend/business functionality
        ↓
Laravel Controller / Form Request / Action / Service / Model
```

These tools solve different problems and should not replace one another.

### 0.3.2 Blade Components — Preferred for Repeated UI

If the same visual structure appears on multiple Blade pages, create or reuse a Blade component instead of copying the markup.

Examples:

```text
Table shell
Table toolbar
Pagination
Page header
Breadcrumb
Status badge
Stat card
Filter panel
Form section card
Input
Textarea
Select
Searchable select shell
Multi-select shell
File uploader
Empty state
Flash alert
Modal
```

Example usage:

```blade
<x-backend.table>
    <x-slot:head>
        <!-- headings -->
    </x-slot:head>

    <!-- rows -->
</x-backend.table>
```

The component controls the **shared design**, while the page supplies its own business data and columns.

Benefits:

```text
One design source
Less duplicate markup
Consistent Admin / Buyer / Supplier UI
One visual fix updates all uses
Cleaner Blade pages
Easier future maintenance
```

Do not create one giant universal component with dozens of unrelated options.

Prefer several focused components.

### 0.3.3 Inline Alpine `x-data` — Use for Small Local State

Inline Alpine is preferred when the interaction is small and belongs to one local UI area.

Good examples:

```html
<div x-data="{ open: false }">
```

Use inline `x-data` for things such as:

- simple sidebar accordion state
- notification dropdown
- profile dropdown
- show/hide filter panel
- simple modal open/close
- simple tabs
- mobile sidebar open/close
- small local preview state

Do not extract tiny one-off state into `Alpine.data()` only for abstraction.

### 0.3.4 `Alpine.data()` — Use Selectively

`Alpine.data()` is **not mandatory**.

Use it when JavaScript behavior is:

- reused in several places
- large enough to make Blade difficult to read
- composed of several methods/computed values
- easier to maintain as one reusable behavior

Recommended candidates:

```text
Searchable select
Searchable multi-select
Permission selector
Category assignment selector
Reusable file uploader/preview
Complex reusable dashboard widget
```

Example architecture:

```text
Blade Component
    ↓
Reusable HTML / visual structure

Alpine.data()
    ↓
Reusable JavaScript state / behavior
```

Example:

```blade
<x-backend.multi-select
    name="category_ids"
    :options="$categories"
/>
```

Internally the component may use:

```html
<div x-data="multiSelect(...)">
```

Do **not** duplicate a large inline Alpine object across many Blade files.

### 0.3.5 Final Component + Alpine Rule

```text
Blade Component
→ "How does it look?"

Alpine.js
→ "How does this UI react in the browser?"

Alpine.data()
→ "Is this browser behavior reusable/complex?"

Laravel
→ "Is this business logic, validation, permission or persistence?"
```

For a normal reusable table, status badge, stat card, input, or form card:

```text
Blade Component only
```

is usually enough.

Alpine is added only when interaction is actually required.

---

## 0.4 Runtime Themeability — Mandatory

The sidebar, menu, and submenu colors must be changeable later from Admin settings.

Therefore these colors must **never be permanently hard-coded across individual Blade menu items**.

Theme-sensitive navigation should use centralized CSS variables.

Admin-configurable categories should support at least:

```text
Application accent
Sidebar background
Sidebar border
Sidebar text
Sidebar muted/icon text

Menu text
Menu hover background
Menu hover text
Menu active background
Menu active text
Menu active border

Submenu text
Submenu hover background
Submenu hover text
Submenu active background
Submenu active text

Optional topbar background
Optional topbar border
Sidebar badge styling where appropriate
```

The default appearance must remain exactly compatible with the static HTML reference.

---

## 0.5 Suggested Theme Setting Keys

When the theme settings feature is implemented, prefer logical keys such as:

```text
theme_primary
theme_primary_hover
theme_primary_soft

sidebar_background
sidebar_border
sidebar_text
sidebar_muted

sidebar_menu_text
sidebar_menu_hover_background
sidebar_menu_hover_text
sidebar_menu_active_background
sidebar_menu_active_text
sidebar_menu_active_border

sidebar_submenu_text
sidebar_submenu_hover_background
sidebar_submenu_hover_text
sidebar_submenu_active_background
sidebar_submenu_active_text

sidebar_badge_background
sidebar_badge_text

topbar_background
topbar_border
```

Important database rule:

- Do not create a new theme/settings table just because this file lists keys.
- Inspect the existing `settings` system first.
- Reuse existing settings storage when suitable.
- Only propose database changes if the current schema cannot support the requirement.

---

## 0.6 Runtime Theme Application

Theme values should be applied once in a shared backend theme partial, for example:

```text
resources/views/backend/layouts/partials/shared/_theme.blade.php
```

Concept:

```blade
<style>
    :root {
        --theme-primary: {{ $theme['theme_primary'] ?? '#4f46e5' }};
        --theme-primary-hover: {{ $theme['theme_primary_hover'] ?? '#4338ca' }};
        --theme-primary-soft: {{ $theme['theme_primary_soft'] ?? '#eef2ff' }};

        --sidebar-bg: {{ $theme['sidebar_background'] ?? '#ffffff' }};
        --sidebar-border: {{ $theme['sidebar_border'] ?? '#e5e7eb' }};
        --sidebar-text: {{ $theme['sidebar_text'] ?? '#374151' }};
        --sidebar-muted: {{ $theme['sidebar_muted'] ?? '#9ca3af' }};

        --sidebar-menu-text: {{ $theme['sidebar_menu_text'] ?? '#374151' }};
        --sidebar-menu-hover-bg: {{ $theme['sidebar_menu_hover_background'] ?? '#f3f4f6' }};
        --sidebar-menu-hover-text: {{ $theme['sidebar_menu_hover_text'] ?? '#111827' }};
        --sidebar-menu-active-bg: {{ $theme['sidebar_menu_active_background'] ?? '#eef2ff' }};
        --sidebar-menu-active-text: {{ $theme['sidebar_menu_active_text'] ?? '#4f46e5' }};
        --sidebar-menu-active-border: {{ $theme['sidebar_menu_active_border'] ?? '#4f46e5' }};

        --sidebar-submenu-text: {{ $theme['sidebar_submenu_text'] ?? '#6b7280' }};
        --sidebar-submenu-hover-bg: {{ $theme['sidebar_submenu_hover_background'] ?? '#f9fafb' }};
        --sidebar-submenu-hover-text: {{ $theme['sidebar_submenu_hover_text'] ?? '#4f46e5' }};
        --sidebar-submenu-active-bg: {{ $theme['sidebar_submenu_active_background'] ?? '#eef2ff' }};
        --sidebar-submenu-active-text: {{ $theme['sidebar_submenu_active_text'] ?? '#4f46e5' }};

        --topbar-bg: {{ $theme['topbar_background'] ?? '#ffffff' }};
        --topbar-border: {{ $theme['topbar_border'] ?? '#e5e7eb' }};
    }
</style>
```

Do not duplicate theme blocks in Admin, Buyer, and Supplier page files.

---

## 0.7 Semantic Colors Are Not Theme Colors

Admin theme settings must not destroy semantic meaning.

Keep these meanings stable:

```text
Success / Active / Approved / Completed → Green
Pending / Warning / Revision Required   → Amber
Danger / Rejected / Failed / Suspended  → Red
Neutral / Draft / Inactive              → Gray
Information                              → Blue or contextual accent
```

Do not allow normal theme configuration to turn:

```text
Delete
Error
Rejected
Success
Pending
```

into arbitrary colors.

Portal accent and sidebar theme are related but **not the same thing**.

Example:

```text
Supplier accent = Teal

Sidebar background = Dark slate
Sidebar active item = Teal soft/dark combination
```

This is valid.

---

## 1. Global Tech Stack & Setup

- **CSS Framework:** Tailwind CSS
- **Interactivity / State Management:** Alpine.js (`x-data`, `x-show`, `x-transition`, `x-cloak`, `@click.outside`)
- **Icon Library:** Font Awesome 6.5.1 (`fa-solid`, `fa-regular`)
- **Typography:** Google Font **Inter** (Weights: `300`, `400`, `500`, `600`, `700`, `800`)

---

## 2. Core Theme Variables & Design Tokens

Include these CSS variables and custom utility rules in your global stylesheet or `<style>` block:

```css
:root {
    /* Brand Theme Colors */
    --theme-primary: #4f46e5;          /* Tailwind Indigo-600 */
    --theme-primary-hover: #4338ca;    /* Tailwind Indigo-700 */
    --theme-primary-soft: #eef2ff;     /* Tailwind Indigo-50 */

    /* Sidebar Variables */
    --sidebar-bg: #ffffff;
    --sidebar-border: #e5e7eb;
    --sidebar-text: #374151;
    --sidebar-muted: #9ca3af;

    --sidebar-menu-text: #374151;
    --sidebar-menu-hover-bg: #f3f4f6;
    --sidebar-menu-hover-text: #111827;
    --sidebar-menu-active-bg: #eef2ff;
    --sidebar-menu-active-text: #4f46e5;
    --sidebar-menu-active-border: #4f46e5;

    --sidebar-submenu-text: #6b7280;
    --sidebar-submenu-hover-bg: #f9fafb;
    --sidebar-submenu-hover-text: #4f46e5;
    --sidebar-submenu-active-bg: #eef2ff;
    --sidebar-submenu-active-text: #4f46e5;

    /* Topbar & Page */
    --topbar-bg: #ffffff;
    --topbar-border: #e5e7eb;
    --page-bg: #f9fafb;
    --surface-bg: #ffffff;
}

html, body {
    font-family: 'Inter', sans-serif;
}

[x-cloak] {
    display: none !important;
}

/* Custom Scrollbar for Sidebar */
.sidebar-scroll::-webkit-scrollbar { width: 6px; }
.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 9999px; }
.sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

/* Global Button & Focus Utilities */
.btn-primary {
    background: var(--theme-primary);
    color: #ffffff;
}
.btn-primary:hover {
    background: var(--theme-primary-hover);
}
.focus-accent:focus {
    outline: none;
    border-color: var(--theme-primary);
    box-shadow: 0 0 0 3px var(--theme-primary-soft);
}

/* Sidebar Menu Transitions */
.sidebar-menu-item {
    color: var(--sidebar-menu-text);
    transition: background-color .2s ease, color .2s ease, border-color .2s ease;
}
.sidebar-menu-item:hover {
    background: var(--sidebar-menu-hover-bg);
    color: var(--sidebar-menu-hover-text);
}
.sidebar-menu-item.active {
    background: var(--sidebar-menu-active-bg);
    color: var(--sidebar-menu-active-text);
    border-left-color: var(--sidebar-menu-active-border);
}
.sidebar-menu-item.active .sidebar-menu-icon {
    color: var(--sidebar-menu-active-text);
}

.sidebar-submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height .25s ease;
}
.sidebar-submenu.open {
    max-height: 1000px;
}
.sidebar-submenu-item {
    color: var(--sidebar-submenu-text);
}
.sidebar-submenu-item:hover {
    background: var(--sidebar-submenu-hover-bg);
    color: var(--sidebar-submenu-hover-text);
}
.sidebar-submenu-item.active {
    background: var(--sidebar-submenu-active-bg);
    color: var(--sidebar-submenu-active-text);
    font-weight: 600;
}
```

---

## 3. Full-Page Shell & Layout Architecture

The overall page operates as a fixed-viewport, 2-column flexbox shell where the sidebar stays anchored on the left and the main column contains the sticky topbar, independently scrolling content area, and footer.

### Mandatory Header Alignment Contract

> **The topbar height must equal the sidebar logo box height (`h-20`) so their bottom border lines align in one continuous horizontal line. Do not use padding-based height on the topbar.**

This rule applies to:

```text
Admin
Buyer
Supplier
Shared backend shell
Desktop
Tablet
Mobile
```

Required pair:

```text
Sidebar Logo / Brand Header
→ h-20 shrink-0

Topbar
→ h-20 shrink-0
```

Correct:

```html
<div class="h-20 ... border-b">
    <!-- sidebar logo -->
</div>

<header class="h-20 shrink-0 ... border-b px-4 lg:px-6">
    <!-- topbar -->
</header>
```

Do not use this as the primary topbar sizing rule:

```text
py-3.5
py-4
min-height based only on content
different heights per portal
```

The explicit `h-20` is intentional. It prevents the sidebar brand border and topbar border from becoming vertically misaligned.

On mobile, the sidebar becomes an overlay drawer, but both the sidebar brand header and topbar must still remain `h-20` for consistent portal geometry.


```
+-----------------------------------------------------------------------------+
| BODY (fixed inset-0 flex overflow-hidden bg-gray-50)                        |
| +-------------------+ +---------------------------------------------------+ |
| |                   | | TOPBAR (bg-white border-b h-20 px-4/6)           | |
| |                   | +---------------------------------------------------+ |
| |                   | | MAIN SCROLL AREA (flex-1 overflow-y-auto p-4/6)   | |
| | LEFT SIDEBAR      | |                                                   | |
| | (w-64 border-r    | |  - Page Header & Action Bar                       | |
| |  flex flex-col)   | |  - Stat Cards Grid (4 cols)                       | |
| |                   | |  - Collapsible Filter Panel                       | |
| | - Logo (h-20)     | |  - Table / Form / Cards Section                   | |
| | - Nav Menu (flex) | |                                                   | |
| | - User Card (p-3) | +---------------------------------------------------+ |
| |                   | | FOOTER (bg-white border-t py-3 text-center)       | |
| +-------------------+ +---------------------------------------------------+ |
+-----------------------------------------------------------------------------+
```

### Full-Page HTML Scaffold:

```html
<body class="bg-gray-50 min-h-screen text-gray-900" 
      x-data="{ mobileSidebar:false, notifOpen:false, profileOpen:false, filterOpen:false, view:'table' }">

    <div class="fixed inset-0 flex overflow-hidden">

        <!-- MOBILE BACKDROP -->
        <div x-show="mobileSidebar" x-transition.opacity @click="mobileSidebar=false"
             class="fixed inset-0 bg-gray-900/40 z-30 lg:hidden" x-cloak></div>

        <!-- 1. LEFT SIDEBAR -->
        <aside class="backend-sidebar w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0"
               :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">
            <!-- Sidebar Content -->
        </aside>

        <!-- 2. MAIN COLUMN -->
        <div class="flex-1 flex flex-col min-w-0 min-h-0">

            <!-- TOPBAR -->
            <header class="h-20 shrink-0 bg-white border-b flex items-center justify-between px-4 lg:px-6" style="border-color:var(--topbar-border)">
                <!-- Topbar Content -->
            </header>

            <!-- MAIN SCROLLABLE CANVAS -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 lg:p-6">
                <!-- Dynamic Page Content -->
            </main>

            <!-- FOOTER -->
            <footer class="bg-white border-t px-4 lg:px-6 py-3 text-center" style="border-color:var(--topbar-border)">
                <p class="text-xs text-gray-400">© 2026 EduShopify. All rights reserved. &nbsp;·&nbsp;
                    <a href="#" class="hover:text-gray-600">Privacy Policy</a> &nbsp;·&nbsp;
                    <a href="#" class="hover:text-gray-600">Terms of Service</a> &nbsp;·&nbsp;
                    <a href="#" class="hover:text-gray-600">Support</a>
                </p>
            </footer>

        </div>
    </div>
</body>
```

---

## 4. Left Sidebar Layout & Menu Structure

The left sidebar (`w-64`) is divided into three sections:
1. **Brand Header (`h-20`)**: Logo badge with application title and subtitle.
2. **Scrollable Navigation Menu (`flex-1 sidebar-scroll`)**: Single menu items, collapsible accordions with badge counts, and dividers.
3. **Footer User Pill (`p-3 border-t`)**: Profile picture, user full name, and role badge.

### Sidebar HTML Implementation:

```html
<aside class="backend-sidebar w-64 flex flex-col border-r fixed lg:static inset-y-0 left-0 z-40 transform transition-transform duration-200 lg:translate-x-0 bg-white"
       style="border-color:var(--sidebar-border)"
       :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full'">

    <!-- Logo / Brand -->
    <div class="h-20 flex items-center px-4 border-b shrink-0" style="border-color:var(--sidebar-border)">
        <div class="w-9 h-9 rounded-lg btn-primary flex items-center justify-center font-bold text-sm">ES</div>
        <div class="ml-3 leading-tight">
            <p class="text-sm font-bold text-gray-900">EduShopify</p>
            <p class="text-[11px] text-gray-400">Admin Panel</p>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="sidebar-scroll flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <!-- Standalone Active Menu Item -->
        <a href="#" class="sidebar-menu-item active flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4">
            <i class="fa-solid fa-gauge sidebar-menu-icon w-5 text-center"></i>
            <span class="ml-3 flex-1 text-sm font-medium">Dashboard</span>
        </a>

        <!-- Collapsible Accordion Menu -->
        <div x-data="{ open: true }">
            <button @click="open = !open" class="sidebar-menu-item w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 border-transparent">
                <i class="fa-solid fa-users sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">User Management</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="#" class="sidebar-submenu-item block px-3 py-2 text-sm rounded-md">All Users</a>
                <a href="#" class="sidebar-submenu-item block px-3 py-2 text-sm rounded-md">Buyers</a>
                <a href="#" class="sidebar-submenu-item block px-3 py-2 text-sm rounded-md">Suppliers</a>
                <a href="#" class="sidebar-submenu-item block px-3 py-2 text-sm rounded-md">Admin Staff</a>
            </div>
        </div>

        <!-- Menu Item with Badge Counter -->
        <div x-data="{ open: true }">
            <button @click="open = !open" class="sidebar-menu-item active w-full flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4">
                <i class="fa-solid fa-circle-check sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium text-left">Verifications</span>
                <span class="text-[10px] font-semibold text-white rounded-full px-1.5 py-0.5 mr-1" style="background:#ef4444">3</span>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            <div class="sidebar-submenu ml-8" :class="open && 'open'">
                <a href="#" class="sidebar-submenu-item active flex items-center justify-between px-3 py-2 text-sm rounded-md">
                    <span>Pending Suppliers</span>
                    <span class="text-[10px] font-semibold text-white rounded-full px-1.5" style="background:#ef4444">3</span>
                </a>
                <a href="#" class="sidebar-submenu-item block px-3 py-2 text-sm rounded-md">Pending Buyers</a>
                <a href="#" class="sidebar-submenu-item block px-3 py-2 text-sm rounded-md">Document Review</a>
            </div>
        </div>

        <!-- Section Divider -->
        <div class="pt-3 mt-3 border-t" style="border-color:var(--sidebar-border)">
            <a href="#" class="sidebar-menu-item flex items-center px-3 py-2.5 rounded-lg mb-1 border-l-4 border-transparent">
                <i class="fa-solid fa-gear sidebar-menu-icon w-5 text-center"></i>
                <span class="ml-3 flex-1 text-sm font-medium">Settings</span>
            </a>
        </div>

    </nav>

    <!-- Bottom User Profile Card -->
    <div class="p-3 border-t shrink-0" style="border-color:var(--sidebar-border)">
        <div class="flex items-center px-2 py-2 rounded-lg" style="background:var(--theme-primary-soft)">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" class="w-8 h-8 rounded-full">
            <div class="ml-2 leading-tight min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate">Admin User</p>
                <p class="text-[10px] text-gray-500">Super Admin</p>
            </div>
        </div>
    </div>
</aside>
```

---


## 5. Topbar Header Component

### Mandatory Height Rule

The topbar is **always `h-20`**.

```text
Sidebar brand box = h-20
Topbar            = h-20
```

Their bottom borders must visually meet at the same vertical position on desktop.

Do not calculate the topbar height using vertical padding.

Use:

```html
<header class="h-20 shrink-0 bg-white border-b flex items-center justify-between px-4 lg:px-6">
```

not:

```html
<header class="bg-white border-b py-3.5 ...">
```

The same `h-20` topbar rule remains active on mobile.




The Topbar spans the top of the content pane. It houses:
- **Mobile Menu Toggle:** Hamburger icon (`lg:hidden`)
- **Breadcrumb Navigation:** Dynamic navigation links
- **Global Search:** Pill search field with magnifying glass icon
- **Notification Dropdown:** Bell button with badge counter and interactive flyout menu
- **User Profile Menu:** Avatar button with dropdown containing profile links and logout

```html
<header class="h-20 shrink-0 bg-white border-b flex items-center justify-between px-4 lg:px-6" style="border-color:var(--topbar-border)">
    <!-- Left: Mobile Toggle & Breadcrumbs -->
    <div class="flex items-center gap-3 min-w-0">
        <button @click="mobileSidebar = true" class="lg:hidden text-gray-500 p-2 -ml-2">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="hidden sm:block min-w-0">
            <p class="text-xs text-gray-400 truncate">
                <a href="#" class="hover:text-gray-600">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[8px] mx-1.5 text-gray-300"></i>
                <span class="text-gray-600">Current Section</span>
            </p>
        </div>
    </div>

    <!-- Right: Search, Notifications & Profile Dropdown -->
    <div class="flex items-center gap-2">
        <!-- Quick Search -->
        <div class="hidden md:block relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" placeholder="Quick search..."
                class="focus-accent w-56 pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50">
        </div>

        <!-- Notification Bell & Flyout -->
        <div class="relative">
            <button @click="notifOpen = !notifOpen; profileOpen=false" class="relative p-2.5 rounded-xl text-gray-500 hover:bg-gray-100">
                <i class="fa-regular fa-bell"></i>
                <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-semibold rounded-full w-4 h-4 flex items-center justify-center">4</span>
            </button>
            <div x-show="notifOpen" @click.outside="notifOpen=false" x-transition x-cloak
                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl border border-gray-200 shadow-lg z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">Notifications</p>
                    <span class="text-xs font-medium cursor-pointer" style="color:var(--theme-primary)">Mark all read</span>
                </div>
                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                    <a href="#" class="flex gap-3 px-4 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:var(--theme-primary-soft)">
                            <i class="fa-solid fa-file-invoice text-xs" style="color:var(--theme-primary)"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800">New application submitted</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">5 minutes ago</p>
                        </div>
                    </a>
                </div>
                <a href="#" class="block text-center text-xs font-medium py-2.5 border-t border-gray-100" style="color:var(--theme-primary)">View all notifications</a>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative">
            <button @click="profileOpen = !profileOpen; notifOpen=false" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-xl hover:bg-gray-100">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" class="w-8 h-8 rounded-full">
                <span class="hidden sm:block text-left leading-tight">
                    <span class="block text-xs font-semibold text-gray-900">Admin User</span>
                    <span class="block text-[10px] text-gray-400">Super Admin</span>
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden sm:block"></i>
            </button>
            <div x-show="profileOpen" @click.outside="profileOpen=false" x-transition x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-200 shadow-lg z-50 py-1">
                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fa-regular fa-user w-4 text-gray-400"></i>My Profile</a>
                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fa-solid fa-gear w-4 text-gray-400"></i>Settings</a>
                <div class="border-t border-gray-100 my-1"></div>
                <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fa-solid fa-arrow-right-from-bracket w-4"></i>Logout</a>
            </div>
        </div>
    </div>
</header>
```

---

## 6. Page Headers & Action Bar

Standard header format placed at the top of the `<main>` area:

```html
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pending Supplier Verification</h1>
        <p class="text-sm text-gray-500 mt-0.5">Review supplier applications and required documents</p>
    </div>

    <!-- Actions Area (Buttons, Filters, Badges) -->
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold px-3 py-1.5 rounded-full">
            <i class="fa-regular fa-clock"></i> 3 Pending
        </span>
        <button @click="filterOpen = !filterOpen" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
            <i class="fa-solid fa-sliders"></i> Filters
        </button>
        <button class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Supplier
        </button>
    </div>
</div>
```

---

## 7. Metric Cards (Dashboard Grid)

4-Column Stat Cards Grid:

```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Stat Card 1 -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Suppliers</p>
        <p class="text-2xl font-bold text-gray-900 mt-2">61</p>
        <p class="text-xs text-gray-400 mt-1">All registered accounts</p>
    </div>
    <!-- Stat Card 2 (Success Accent) -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Verified</p>
        <p class="text-2xl font-bold text-green-700 mt-2">54</p>
        <p class="text-xs text-gray-400 mt-1">Active capability</p>
    </div>
    <!-- Stat Card 3 (Warning Accent) -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending Review</p>
        <p class="text-2xl font-bold text-amber-700 mt-2">3</p>
        <p class="text-xs text-gray-400 mt-1">Awaiting admin decision</p>
    </div>
    <!-- Stat Card 4 (Danger Accent) -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</p>
        <p class="text-2xl font-bold text-red-600 mt-2">4</p>
        <p class="text-xs text-gray-400 mt-1">Requires resubmission</p>
    </div>
</div>
```

---

## 8. Collapsible Filter Bar

```html
<div x-show="filterOpen" x-transition x-cloak class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Filter Applications</h2>
        <button @click="filterOpen=false" class="text-gray-400 hover:text-gray-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <form class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700 block mb-1.5">Status</label>
            <select class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                <option>All Statuses</option>
                <option selected>Pending</option>
                <option>Verified</option>
                <option>Rejected</option>
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700 block mb-1.5">Date Range</label>
            <input type="date" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2">
        </div>
        <!-- Action Buttons (Spanning full width) -->
        <div class="lg:col-span-4 flex items-center justify-end gap-2 pt-1">
            <button type="button" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Reset</button>
            <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Apply Filters</button>
        </div>
    </form>
</div>
```

---

## 9. Data Table Pattern with Toolbar & Pagination

```html
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    <!-- Top Table Toolbar (Export Buttons & Search) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <button class="text-xs font-medium px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
                <i class="fa-solid fa-file-excel text-green-600"></i> Excel
            </button>
            <button class="text-xs font-medium px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
                <i class="fa-solid fa-file-pdf text-red-600"></i> PDF
            </button>
            <button class="text-xs font-medium px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
                <i class="fa-solid fa-print text-gray-500"></i> Print
            </button>
        </div>
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" placeholder="Search records..."
                class="focus-accent w-full sm:w-64 pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
        </div>
    </div>

    <!-- Table Canvas -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name / Organization</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Documents</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500">1</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Amina+Karimova&background=eef2ff&color=4f46e5" class="w-9 h-9 rounded-full">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Amina Karimova</p>
                                <p class="text-xs text-gray-400">Textbook Distributors LLC</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">amina@textbookdist.com</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600">
                            <i class="fa-regular fa-file-lines text-gray-400"></i> 4/4 uploaded
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle text-[6px]"></i> Pending
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1.5">
                            <button title="View" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></button>
                            <button title="Approve" class="w-8 h-8 rounded-lg flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                            <button title="Reject" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-t border-gray-100">
        <p class="text-xs text-gray-500">Showing <span class="font-medium text-gray-700">1</span> to <span class="font-medium text-gray-700">10</span> of <span class="font-medium text-gray-700">61</span> entries</p>
        <div class="flex items-center gap-1">
            <button class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed">Previous</button>
            <button class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white" style="background:var(--theme-primary)">1</button>
            <button class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">2</button>
            <button class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
```

---

## 10. Form Layout & Advanced Input Components

Forms follow an **8 / 4 Column Split (`grid grid-cols-1 xl:grid-cols-12 gap-6`)**:
- **Main Left (8 cols):** Primary identity, details, multi-select tags, address fields, documents.
- **Sidebar Right (4 cols):** Status selector, manual plan picker, logo uploader.

### Form Elements:

#### A. Standard Input & Textarea:
```html
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">
        Company Display Name <span class="text-red-500">*</span>
    </label>
    <input type="text" placeholder="e.g. Textbook Distributors LLC"
        class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder:text-gray-400 transition">
</div>
```

#### B. Radio Card Selectors (e.g. Supplier / Account Types):
```html
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <!-- Active Selected Card -->
    <label class="flex flex-col items-center text-center gap-2 rounded-lg border-2 px-3 py-4 cursor-pointer transition"
           style="border-color:var(--theme-primary); background:var(--theme-primary-soft)">
        <i class="fa-solid fa-industry text-lg" style="color:var(--theme-primary)"></i>
        <span class="text-xs font-semibold" style="color:var(--theme-primary)">Manufacturer</span>
        <input type="radio" name="supplier_type" class="hidden" checked>
    </label>

    <!-- Inactive Card -->
    <label class="flex flex-col items-center text-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 py-4 cursor-pointer hover:border-gray-300 transition">
        <i class="fa-solid fa-truck-fast text-lg text-gray-400"></i>
        <span class="text-xs font-medium text-gray-600">Distributor</span>
        <input type="radio" name="supplier_type" class="hidden">
    </label>
</div>
```

#### C. Searchable Combobox / Custom Dropdown (Alpine.js):
```html
<div x-data="{
        open: false,
        query: '',
        selected: 'Science Lab Equipment',
        items: ['Science Lab Equipment', 'Textbooks', 'Classroom Furniture'],
        get filtered() {
            if (!this.query.trim()) return this.items;
            return this.items.filter(i => i.toLowerCase().includes(this.query.toLowerCase()));
        },
        select(item) { this.selected = item; this.open = false; this.query = ''; }
     }"
     @click.outside="open=false"
     class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
    <button type="button" @click="open = !open"
        class="focus-accent w-full flex items-center justify-between px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-left bg-white">
        <span class="text-gray-900" x-text="selected"></span>
        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform" :class="open && 'rotate-180'"></i>
    </button>
    <div x-show="open" x-transition x-cloak
         class="absolute z-20 mt-1.5 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden py-1">
        <div class="p-2 border-b border-gray-100">
            <input type="text" x-model="query" placeholder="Search..."
                class="focus-accent w-full px-2.5 py-1.5 text-sm border border-gray-200 rounded-md">
        </div>
        <div class="max-h-52 overflow-y-auto">
            <template x-for="item in filtered" :key="item">
                <button type="button" @click="select(item)"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-left hover:bg-gray-50"
                    :class="selected === item && 'bg-[var(--theme-primary-soft)]'">
                    <span :class="selected === item ? 'font-medium' : 'text-gray-700'"
                          :style="selected === item && 'color:var(--theme-primary)'"
                          x-text="item"></span>
                    <i class="fa-solid fa-check text-xs" style="color:var(--theme-primary)" x-show="selected === item"></i>
                </button>
            </template>
        </div>
    </div>
</div>
```

#### D. Document Upload Box (Dashed Border Dropzones):
```html
<div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft)] transition">
    <i class="fa-solid fa-cloud-arrow-up text-xl text-gray-400 mb-2"></i>
    <p class="text-sm font-medium text-gray-700">Trade License <span class="text-red-500">*</span></p>
    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG — up to 5MB</p>
</div>
```

---

## 11. Summary Checklist for Designing Pages

| Element | Class / Rule Pattern |
|---|---|
| **Page Root** | `body.bg-gray-50` with `fixed inset-0 flex overflow-hidden` wrapper |
| **Left Sidebar** | `w-64 flex flex-col border-r fixed lg:static bg-white z-40` |
| **Topbar** | `h-20 shrink-0 bg-white border-b px-4 lg:px-6 flex items-center justify-between` — must equal sidebar logo `h-20` |
| **Main Area** | `flex-1 overflow-y-auto bg-gray-50 p-4 lg:p-6` |
| **Panels & Cards** | `bg-white rounded-xl border border-gray-200 p-5` |
| **Primary Buttons** | `.btn-primary text-sm font-medium px-4 py-2 rounded-lg` |
| **Outline Buttons** | `border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm px-4 py-2` |
| **Input Fields** | `.focus-accent px-3 py-2.5 border border-gray-300 rounded-lg text-sm` |
| **Table Headings** | `bg-gray-50 px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider` |
| **Table Rows** | `hover:bg-gray-50 px-5 py-3.5 text-sm text-gray-600 divide-y divide-gray-100` |

---

# 12. Additional Exact Form Patterns from the Static Reference

The shorter design summary above documents the primary form controls, but the static reference contains additional patterns that must also be treated as part of the design system.

## 12.1 Form Section Card

Complex forms must be broken into visual sections.

```html
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-gray-900">Basic Information</h3>
        <p class="text-xs text-gray-500 mt-1">
            Main identity details for this record.
        </p>
    </div>

    <!-- fields -->
</div>
```

Use:

```text
bg-white
rounded-xl
border border-gray-200
p-5
```

Do not build one giant unsectioned form.

---

## 12.2 Validation Error State

The static reference uses explicit field-level validation.

```html
<input
    type="email"
    class="w-full px-3 py-2.5 border border-red-400 bg-red-50 rounded-lg
           text-sm text-gray-900 focus:outline-none focus:ring-2
           focus:ring-red-400 transition"
>

<p class="mt-1 text-xs text-red-600">
    Please enter a valid email address.
</p>
```

Rules:

- show the error beside the field
- use red border
- use subtle red background
- use readable error text
- do not depend only on a generic page-level validation alert

Laravel validation should preserve old input where appropriate.

---

## 12.3 Helper Text

```html
<p class="mt-1.5 text-xs text-gray-400">
    Helpful explanation.
</p>
```

Use helper text only when it helps the user make a decision.

---

## 12.4 Standard Simple Dropdown

Short fixed-option selects may use a normal `<select>`:

```html
<select class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
    ...
</select>
```

Do not turn every small fixed select into a custom Alpine component.

---

## 12.5 Custom Single-Select List Box

Use the static reference pattern for richer selections:

```text
Trigger
↓
Popover
↓
Optional search
↓
Scrollable options
↓
Selected item uses soft accent
↓
Check icon
```

Required visual rules:

```text
Trigger:
px-3 py-2.5
border-gray-300
rounded-lg
text-sm
bg-white

Popover:
mt-1.5
bg-white
border-gray-200
rounded-lg
shadow-lg
z-20

Options:
px-3 py-2
text-sm
hover:bg-gray-50

Selected:
theme-primary-soft background
theme-primary text
font-medium
check icon
```

---

## 12.6 Searchable Multi-Select List Box

Use this pattern for:

- service areas
- categories
- permissions where appropriate
- target filters
- multi-value reference data

Structure:

```text
Closed Trigger
    ↓
Searchable Popover
    ↓
Checkbox List
    ↓
Selection Count + Done
    ↓
Selected Chips
```

Selected option:

```text
background: var(--theme-primary-soft)
```

Checkbox accent:

```text
var(--theme-primary)
```

Selected chips:

```html
<span
    class="inline-flex items-center gap-1.5 text-xs font-medium
           pl-2.5 pr-1.5 py-1 rounded-full"
    style="background:var(--theme-primary-soft);
           color:var(--theme-primary)"
>
    Selected Item
</span>
```

When the search has no matches, show a proper empty result inside the list.

---

## 12.7 Searchable Assignment List

For multi-selection screens where users need to see more options at once, use:

```text
Search Input
Selected Chips
Scrollable Bordered List
Checkbox Rows
```

Recommended list box:

```text
border border-gray-200
rounded-lg
max-h-56
overflow-y-auto
divide-y divide-gray-100
```

Row:

```text
px-3 py-2.5
text-sm
hover:bg-gray-50
```

This pattern is useful for:

- category assignment
- permission assignment
- service areas
- member-role assignment when multiple selection is valid

---

## 12.8 Status Selector with Semantic Dot

For status selectors that benefit from visual state indication:

```text
Draft             → Gray dot
Pending Review    → Amber dot
Active            → Green dot
Revision Required → Amber dot
Rejected          → Red dot
```

Color indicates status but must always be accompanied by text.

Never show a color dot alone.

---

## 12.9 Side Column Configuration Cards

The reference form uses an 8/4 desktop layout:

```text
Main Column 8/12
├── primary details
├── business information
├── categories
├── documents
└── long content

Side Column 4/12
├── status
├── configuration
├── plan/meta
└── logo/image
```

On smaller screens everything stacks into one column.

Use the 8/4 pattern for medium-to-complex create/edit pages.

Do not force it on tiny simple forms.

---

## 12.10 Logo / Image Manager

Pattern:

```text
Current preview
Upload New
Remove
Helper text
```

Recommended preview:

```text
w-16 h-16
rounded-xl
object-cover or object-contain depending asset
```

Use `object-contain` for organization/company logos if cropping would damage the logo.

Use `object-cover` for human avatars/photos.

---

## 12.11 Bottom Form Action Bar

Complex forms should end with a clear action bar:

```html
<div class="xl:col-span-12 flex items-center justify-end gap-2
            bg-white rounded-xl border border-gray-200 p-4">

    <button type="button"
        class="text-sm font-medium px-4 py-2 rounded-lg
               border border-gray-300 text-gray-700 hover:bg-gray-50">
        Cancel
    </button>

    <button type="submit"
        class="btn-primary text-sm font-medium px-5 py-2
               rounded-lg flex items-center gap-2">
        <i class="fa-solid fa-check"></i>
        Save
    </button>
</div>
```

Use one visually dominant submit action.

---

# 13. Button and Action System

## 13.1 Primary

```text
.btn-primary
text-sm
font-medium
px-4 or px-5
py-2
rounded-lg
```

Primary color comes from:

```text
--theme-primary
```

## 13.2 Outline / Secondary

```text
border border-gray-300
text-gray-700
hover:bg-gray-50
rounded-lg
text-sm
px-4
py-2
```

## 13.3 Row Icon Actions

Reference table style:

```text
w-8 h-8
rounded-lg
flex items-center justify-center
```

Examples:

```text
View    → gray
Approve → green
Reject  → red
```

Use `title` and/or accessible labels for icon-only buttons.

If a row has too many actions, use:

```text
[Primary Action] [...]
```

with an overflow dropdown rather than displaying a long strip of icons.

---

# 14. Status Badge Standard

Base:

```text
inline-flex
items-center
gap-1.5
text-xs
font-semibold
px-2.5
py-1
rounded-full
border
```

Recommended mappings:

```text
Active / Approved / Verified / Completed
→ bg-green-50 text-green-700/800 border-green-200

Pending / Waiting / Revision Required
→ bg-amber-50 text-amber-800 border-amber-200

Rejected / Suspended / Failed / Cancelled
→ bg-red-50 text-red-700 border-red-200

Draft / Inactive / Historical
→ bg-gray-100 text-gray-600 border-gray-200

Information
→ bg-blue-50 text-blue-700 border-blue-200
```

Use the same mapping everywhere.

---

# 15. Table Behavior Rules

The table appearance in Section 9 is the exact baseline.

Additionally:

- tables must be inside `overflow-x-auto`
- do not squeeze wide enterprise tables on mobile
- actions generally remain the final column
- numeric comparison columns may be right-aligned
- use eager-loaded data server-side to avoid N+1 problems
- preserve search/filter/sort query strings during pagination
- show visible active sort state when sortable
- avoid sort icons on unsortable columns
- if bulk actions are required, show the bulk toolbar only after selection
- empty tables must show an empty state instead of blank rows

Recommended data ordering:

```text
Identifier
Primary info
Secondary info
Status
Dates / metrics
Actions
```

---

# 16. Table Empty State

Example:

```text
[icon]

No RFQs found

There are no RFQs matching the selected filters.

[Clear Filters]
```

For first-time data:

```text
No RFQs yet

Create your first RFQ to start receiving supplier quotations.

[Create RFQ]
```

Use the relevant action only when the current user has permission.

---

# 17. Filters and Search

Filters should normally appear above the table.

Use:

```text
white card
rounded-xl
border-gray-200
p-5
mb-6
```

For server-rendered pages:

- keep current search when paginating
- keep current filters when paginating
- keep current sort when paginating
- reset only when user explicitly selects Reset/Clear

Use specific placeholders when possible:

```text
Search RFQ number or title...
Search suppliers...
Search purchase orders...
```

instead of always using generic `Search...`.

---

# 18. Pagination

Use the static reference pattern:

```text
Left:
Showing 1 to 10 of 61 entries

Right:
Previous 1 2 Next
```

Current page:

```text
theme-primary background
white text
```

Other pages:

```text
white
gray border
gray text
gray hover
```

Disabled controls:

```text
gray text
cursor-not-allowed
```

---

# 19. Cards and Panels

Default surface:

```text
bg-white
rounded-xl
border border-gray-200
```

Default content card:

```text
p-5
```

Dashboard stat cards may use:

```text
p-4
```

Most cards should rely on borders rather than strong shadows.

Use stronger shadows only for:

- dropdowns
- floating menus
- modals
- popovers

Do not use large marketing-style shadows for normal backend content.

---

# 20. Topbar Rules

The static topbar is the baseline.

It contains:

```text
Left:
Mobile hamburger
Breadcrumb / current context

Right:
Quick search where useful
Dashboard-mode switcher where applicable
Notifications
Profile dropdown
```

## 20.1 Mandatory Topbar Height and Border Alignment

> **The topbar height must equal the sidebar logo box height (`h-20`) so their border lines align in one continuous line — do not use padding-based height on the topbar.**

Mandatory:

```text
Sidebar logo/brand header
→ h-20 shrink-0

Topbar
→ h-20 shrink-0
```

Correct topbar base:

```text
h-20
shrink-0
bg-white
border-b
px-4 lg:px-6
flex
items-center
justify-between
```

Do not use:

```text
py-3.5
py-4
content-driven height
different topbar heights between Admin / Buyer / Supplier
```

as the main height mechanism.

The reason is structural, not cosmetic: the sidebar brand area's bottom border and the topbar's bottom border must sit on exactly the same horizontal line.

This rule applies on **desktop, tablet, and mobile**.

## 20.2 Notification Dropdown

Recommended:

```text
w-80
max-w-[calc(100vw-2rem)] on narrow screens
rounded-xl
border-gray-200
shadow-lg
z-50
```

On small mobile screens, a dropdown must never extend beyond the viewport.

## 20.3 Profile Dropdown

Recommended:

```text
w-48
max-w-[calc(100vw-2rem)]
rounded-xl
border-gray-200
shadow-lg
z-50
```

Logout is always visually destructive:

```text
text-red-600
hover:bg-red-50
```

## 20.4 Buyer / Supplier Mode Switcher

If the account has both Buyer and Supplier capabilities, a dashboard-mode switcher may be added without changing the topbar geometry.

It must:

- remain vertically centered inside `h-20`
- collapse gracefully on mobile
- show only modes the account may access
- never imply that changing UI mode grants authorization

---

# 21. Sidebar Rules

The reference sidebar is the baseline.

## 21.1 Sidebar Brand Header Alignment

The sidebar brand/logo block must use:

```text
h-20
shrink-0
border-b
```

This height is paired directly with the topbar `h-20`.

Do not change the sidebar logo/header height independently from the topbar.

If one changes in a future redesign, both must change together as one layout token.

The reference sidebar is:


```text
w-64
border-r
fixed on mobile
static on lg+
flex flex-col
z-40
```

Sections:

```text
Brand header
Scrollable navigation
Bottom user card
```

## Active Route

Use Laravel route context to determine active state.

Example:

```text
buyer.rfqs.index
buyer.rfqs.create
buyer.rfqs.show
buyer.rfqs.edit
```

should keep:

```text
Procurement
└── RFQs
```

active/open.

Do not depend only on raw URL matching when named routes are available.

## Parent Open State

If any submenu child is active:

- parent remains expanded
- active state is visually clear
- active child uses submenu active colors

## Permission-Aware Menu

If user has no access to any child menu:

```text
hide the parent menu
```

Do not show an empty accordion.

UI visibility is not security; backend authorization must still exist.

---

# 22. Mobile & Responsive Rules

The dashboard must be mobile-first and remain usable from small phones through large desktop screens.

The responsive implementation must preserve the same visual identity and must **not** create a second mobile design system.

## 22.1 Global Responsive Contract

Always preserve:

```text
Topbar height        → h-20
Sidebar brand height → h-20
```

at every breakpoint.

Even when the sidebar becomes a mobile overlay drawer, its brand/header block remains the same height as the topbar.

Do not use a shorter mobile header.

## 22.2 Below `lg` — Mobile / Tablet

### Sidebar

Use:

```text
fixed
inset-y-0
left-0
w-64
z-40
transform
transition-transform
duration-200
```

Closed:

```text
-translate-x-full
```

Open:

```text
translate-x-0
```

Required behavior:

- hamburger opens the sidebar
- backdrop appears behind the sidebar
- clicking backdrop closes it
- ESC should close it where practical
- selecting a navigation destination may close it
- body/content must not gain unwanted horizontal overflow
- sidebar navigation remains independently scrollable
- bottom user area remains reachable
- sidebar brand header remains `h-20 shrink-0`

Backdrop baseline:

```text
fixed inset-0
bg-gray-900/40
z-30
lg:hidden
```

### Topbar

Mobile topbar remains:

```text
h-20
shrink-0
px-4
```

Behavior:

```text
hamburger visible
breadcrumb may hide on very small screens
quick/global search may hide below md
profile name/role may hide
notification bell remains usable
profile/avatar remains usable
mode switcher may become icon/compact control
```

Do not reduce the topbar height to make the mobile layout "smaller."

### Topbar Overflow Safety

The right side must use compact responsive behavior.

On narrow widths:

- hide nonessential text before hiding essential controls
- keep hamburger, notifications, and profile/avatar available
- do not allow topbar children to push page width beyond viewport
- use `min-w-0` and truncation where needed
- dropdowns use viewport-safe widths such as `max-w-[calc(100vw-2rem)]`

### Main Content

Use:

```text
full width
min-w-0
p-4
```

Page header actions:

```text
flex-col
sm:flex-row
flex-wrap where necessary
gap-2 / gap-3
```

Do not let buttons overflow horizontally.

### Metric Cards

Recommended progression:

```text
base → 1 column
sm   → 2 columns
lg   → up to 4 columns
```

### Tables

Wide tables:

```text
overflow-x-auto
```

Rules:

- never squeeze many columns into unreadable widths
- keep row text readable
- preserve action access
- avoid full-page horizontal scrolling
- table toolbar should stack when necessary
- pagination may stack vertically on very small screens

If a table needs a mobile card representation for usability, that may be implemented as a deliberate component variant, but do not invent it separately on every page.

### Forms

Below large desktop:

```text
single column by default
```

Two-column field grids may activate at `sm` or `md` when fields remain readable.

The complex 8/4 form layout collapses to one column.

Long bottom action bars should:

```text
flex-wrap
or
stack on very narrow screens
```

Primary Save action must remain obvious.

### Filters

Filter grids should progress naturally:

```text
base → 1 column
sm   → 2 columns
lg   → 4 columns where appropriate
```

Filter action buttons may stack/wrap.

### Dropdowns / List Boxes / Modals

On mobile:

- dropdown/popover width must stay inside viewport
- searchable list boxes must have controlled max-height
- modals should use horizontal margin/padding
- avoid fixed desktop-only widths
- prevent clipping under `overflow-hidden` parents
- keep usable touch targets

## 22.3 `lg` and Above — Desktop

Sidebar:

```text
static
w-64
translate-x-0
```

Sidebar brand:

```text
h-20
```

Topbar:

```text
h-20
px-6
```

Because both use `h-20`, the border line remains aligned.

Main:

```text
p-6
```

Metric cards:

```text
up to 4 columns
```

Complex forms:

```text
8/4 split
```

Tables:

```text
full enterprise table layout
```

## 22.4 Responsive Verification

Every backend page must be checked at minimum for:

```text
Small phone
Large phone
Tablet
Laptop
Desktop
```

Verify:

- no horizontal page overflow
- sidebar drawer works
- backdrop works
- topbar remains `h-20`
- sidebar brand remains `h-20`
- topbar controls do not collide
- dropdowns stay inside viewport
- tables remain usable
- form inputs do not overflow
- action buttons wrap/stack correctly
- modal content remains reachable
- footer does not cover content

---

# 23. Modals and Confirmation Dialogs

Use modals for:

- confirmation
- quick status changes
- small forms
- previews

Do not put very large workflows inside small modals.

Recommended modal:

```text
backdrop
centered dialog
bg-white
rounded-xl
border or shadow
header
body
footer
```

Typical widths:

```text
Small  → max-w-md
Normal → max-w-lg
Large  → max-w-2xl
```

Destructive confirmation should clearly show:

```text
Danger meaning
Short consequence
Cancel
Red confirm action
```

SweetAlert2 may be used if it is already part of the project and is styled to match the design.

---

# 24. Flash Alerts

Success:

```text
bg-green-50
border-green-200
text-green-700
rounded-xl
```

Error:

```text
bg-red-50
border-red-200
text-red-700
rounded-xl
```

Warning:

```text
bg-amber-50
border-amber-200
text-amber-800
rounded-xl
```

Information:

```text
bg-blue-50
border-blue-200
text-blue-700
rounded-xl
```

Use the shared backend flash partial rather than copying alert markup everywhere.

---

# 25. Dropdown and Popover Behavior

All interactive dropdowns should:

- close on outside click
- use `x-cloak` when Alpine controls initial visibility
- use subtle transition
- remain above surrounding cards/tables
- not be clipped by an unnecessary `overflow-hidden` parent
- use `z-20` for local list boxes and higher z-index for topbar overlays
- support keyboard/focus behavior where practical

Do not use very strong shadows.

---

# 26. Loading and Submission State

For form submissions where duplicate clicks are possible:

```text
Disable submit
Show spinner if useful
Change button text to Saving...
Prevent repeat submit
Restore state if request fails
```

For ordinary Laravel post/redirect forms, implementation may remain simple.

Do not use Livewire loading directives.

---

# 27. Permission-Aware UI

Buttons and menu links should respect permissions.

Example:

```blade
@can('rfq.create')
    <a href="...">Create RFQ</a>
@endcan
```

But remember:

```text
hidden button != authorization
```

Routes/controllers/Policies/middleware still enforce the action.

Do not expose actions merely because the page exists.

Workflow status must also determine whether an action is visible.

---

# 28. Accessibility

Required:

- real `<label>` elements for fields
- visible focus state
- keyboard-accessible buttons
- meaningful text for primary actions
- `aria-label` for icon-only controls when needed
- sufficient contrast
- status represented by text, not color alone
- meaningful image alt text where applicable

Do not remove focus outlines without providing the project `focus-accent` replacement.

---

# 29. Typography Rules

Use only **Inter** for new backend pages unless the project is intentionally rebranded.

Common hierarchy:

```text
Page Title
text-2xl font-bold text-gray-900

Page Subtitle
text-sm text-gray-500

Card / Section Title
text-sm font-semibold text-gray-900

Large Filter Section Title when needed
text-lg font-semibold text-gray-900

Form Label
text-sm font-medium text-gray-700

Table Header
text-xs font-semibold text-gray-500 uppercase tracking-wider

Table Body
text-sm text-gray-600

Primary Table Value
text-sm font-medium text-gray-900

Helper Text
text-xs text-gray-400

Micro Metadata
text-[10px] text-gray-400
```

---

# 30. Date, Number, and Currency Display

Use consistent formatting.

Recommended date:

```text
18 Aug 2026
```

Timestamp:

```text
18 Aug 2026, 10:45 AM
```

Relative secondary text:

```text
5 minutes ago
```

Numbers:

```text
1,250
```

Currency:

```text
৳ 25,000.00
$ 1,250.00
```

Use the record's actual currency context.

Do not mix multiple display formats on the same dashboard without a reason.

---

# 31. Avatars, Logos, and Thumbnails

Human avatar:

```text
rounded-full
object-cover
```

Company logo:

```text
rounded-lg or rounded-xl
object-contain
bg-white
```

Table thumbnail:

```text
w-9 h-9 or w-10 h-10
stable dimensions
```

Do not crop company logos into circles by default.

---

# 32. Reusable Blade Components

Reusable Blade components are a **core design rule** for EduShopify.

If the same visual pattern appears in several Blade pages, do not copy the entire Tailwind/HTML block repeatedly.

Create or reuse a focused component.

## 32.1 What Should Become a Component

Strong candidates:

```text
Page header
Breadcrumbs
Table shell
Table toolbar
Table empty state
Pagination
Status badge
Flash alert
Stat card
Filter panel
Form section card
Input
Textarea
Select
Searchable select
Multi-select
File upload
Modal
Confirmation dialog shell
Theme style partial
```

Example:

```blade
<x-backend.table>
    <x-slot:head>
        <tr>
            <th>RFQ</th>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </x-slot:head>

    @foreach ($rfqs as $rfq)
        <!-- page-specific rows/data -->
    @endforeach
</x-backend.table>
```

The table component should own shared visual behavior such as:

```text
white surface
rounded-xl
gray border
responsive overflow wrapper
header styling
toolbar container
pagination area
empty-state slot where applicable
```

The individual page controls:

```text
columns
business data
routes
permissions
row actions
workflow-specific content
```

Do not make the component understand every business entity.

## 32.2 Component Location

Follow `ARCHITECTURE.md`.

When using standard Laravel anonymous `<x-backend.*>` components, the conventional location is:

```text
resources/views/components/backend/
```

If the existing project already registers another backend Blade component namespace/location, keep that existing convention.

Do not maintain duplicate component systems for the same purpose.

Layout partials such as sidebar/topbar/theme partials may remain under the backend layout structure defined in `ARCHITECTURE.md`.

## 32.3 Component Granularity

Good:

```text
<x-backend.table>
<x-backend.status-badge>
<x-backend.form-card>
<x-backend.input>
```

Avoid:

```text
<x-everything
    type=""
    module=""
    mode=""
    table=""
    form=""
    modal=""
    variant=""
    ...
/>
```

Components should be focused and composable.

## 32.4 Component + Alpine Combination

A Blade component may contain Alpine behavior when interaction is part of that reusable component.

Example:

```text
<x-backend.searchable-select>
        +
Alpine.data('searchableSelect', ...)
```

Use this only when the behavior is reusable or complex.

For a purely visual component:

```text
Table
Badge
Stat card
Form card
Input
```

do not add Alpine unnecessarily.

## 32.5 Reuse Priority

Before creating a new component:

```text
1. Search existing components.
2. Reuse if suitable.
3. Extend carefully if the pattern is truly shared.
4. Create a new focused component only when needed.
```

One visual fix to a shared component should improve every page that uses it.

---

# 33. CSS Organization

The static HTML contains inline CSS for demonstration. In the Laravel application, centralize reusable styles.

Preferred concept:

```text
resources/css/
├── app.css
└── backend/
    ├── base.css
    ├── layout.css
    ├── sidebar.css
    ├── forms.css
    ├── tables.css
    └── components.css
```

If the existing Vite/Tailwind pipeline already has a clean structure, adapt to it rather than restructuring blindly.

Runtime theme variables may be injected by a shared Blade theme partial.

---

# 34. JavaScript & Alpine Organization

Do not allow dashboard Blade files to become huge JavaScript containers.

## 34.1 Inline Alpine

Keep small local state inline:

```html
<div x-data="{ open: false }">
```

Good for:

```text
simple dropdown
simple accordion
filter show/hide
small modal
mobile sidebar open/close
simple tabs
```

## 34.2 Reusable `Alpine.data()`

Use `Alpine.data()` when the same state/method logic is reused or becomes complex.

Recommended organization:

```text
resources/js/backend/
├── sidebar.js
├── dropdowns.js
├── forms.js
├── tables.js
├── alerts.js
└── components/
    ├── searchable-select.js
    ├── multi-select.js
    ├── permission-selector.js
    └── file-upload.js
```

Example principle:

```text
Small + local
→ inline x-data

Large or repeated
→ Alpine.data()
```

Do not create `Alpine.data()` for every tiny toggle.

Do not copy a 20–50 line `x-data="{ ... }"` object into multiple Blade pages.

## 34.3 Alpine Initialization

If `Alpine.data()` is used, register reusable behaviors through the project's normal JS/Vite entry point.

Keep initialization centralized.

Do not load separate Alpine copies per page.

## 34.4 Server Data

When passing Laravel data to Alpine:

- serialize safely
- do not expose sensitive fields unnecessarily
- do not treat client-side data as authorization
- always validate final submitted values on the server

---

# 35. DataTables / Third-Party Table Policy

If DataTables is used:

- it must visually match Section 9
- style search
- style page size
- style export buttons
- style pagination
- use responsive behavior
- do not accept raw plugin defaults

Primary plugin accent should derive from:

```text
--theme-primary
```

Semantic row actions retain semantic colors.

---

# 36. Admin Dashboard Theme Settings UI

Recommended location:

```text
System
└── Settings
    └── Dashboard Theme
```

Recommended groups:

```text
General Accent
Sidebar
Main Menu
Submenu
Topbar
Preview
```

Each configurable color should use:

```text
Color picker
+
Hex input
```

Actions:

```text
Preview
Save Changes
Reset Defaults
```

A preview should demonstrate:

```text
default menu
hover menu
active menu
submenu
active submenu
badge
```

Theme changes should be centrally applied.

If theme values are cached, saving must invalidate the relevant cache so changes appear without a server restart.

Always keep code/config defaults as fallback.

---

# 37. Theme Safety

When implementing Admin theme customization:

- validate color format
- prevent malformed CSS
- prefer hex values
- keep readable contrast
- provide reset defaults
- keep active navigation visible
- do not allow theme settings to change semantic success/danger meaning
- do not save every keystroke unless deliberately implementing live autosave
- preview before saving when practical

---

# 38. Design Rules for Admin / Buyer / Supplier

## Admin

Same reference design.

Typical characteristics:

```text
data-heavy
management tables
approval/rejection controls
filters
system settings
```

## Buyer

Same shell and component design.

Typical content:

```text
RFQs
Quotations
Awards
Purchase Orders
Marketplace
Saved Items
Messages
Organization
Roles
```

## Supplier

Same shell and component design.

Typical content:

```text
Listings
RFQ Opportunities
Quotations
Awards
Purchase Orders
Company Profile
Subscription
Team
Roles
```

Do not create a different form/table design per portal.

---

# 39. Shared Backend Pages

Shared modules such as:

```text
Account
Members
Invitations
Messages
Notifications
Support
Settings
```

must reuse the same visual components.

Shared views may inherit the active portal accent while keeping the same structural design.

---

# 40. Design Completion Checklist

Before considering a backend page complete:

- [ ] Uses the correct portal layout from `ARCHITECTURE.md`
- [ ] Sidebar brand/logo header uses `h-20 shrink-0`
- [ ] Topbar uses `h-20 shrink-0` and does not use padding-based height
- [ ] Sidebar brand border and topbar border align on desktop
- [ ] Mobile topbar also remains `h-20`
- [ ] Visually matches `edushopify_dashboard_reference.html`
- [ ] Uses Inter
- [ ] Uses gray-50 page background
- [ ] Uses white bordered surfaces
- [ ] Uses `rounded-xl` cards and `rounded-lg` controls
- [ ] Uses the correct page-header pattern
- [ ] Uses the standard table pattern if it is a list page
- [ ] Uses the standard filter pattern when filters are needed
- [ ] Uses the standard 8/4 form layout for complex forms
- [ ] Uses the exact input/label/helper/error conventions
- [ ] Uses the correct searchable single/multi list-box patterns
- [ ] Uses the correct bottom action bar for long forms
- [ ] Uses semantic status colors
- [ ] Uses theme variables instead of hard-coded navigation theme values
- [ ] Does not introduce backend Livewire
- [ ] Works on mobile
- [ ] Wide tables scroll correctly
- [ ] Dropdowns are not clipped
- [ ] Modal/dropdown z-index is correct
- [ ] Permission-sensitive controls are hidden when unavailable
- [ ] Backend authorization still exists
- [ ] Active menu and submenu state is correct
- [ ] Parent menu opens when child route is active
- [ ] Empty state exists
- [ ] Validation state exists
- [ ] Destructive action requires confirmation where appropriate
- [ ] Duplicate form submission is prevented where needed
- [ ] Existing shared UI components were reused
- [ ] Repeated visual markup was extracted/reused through a focused Blade component where appropriate
- [ ] Simple local Alpine state remains inline where appropriate
- [ ] `Alpine.data()` is used only for reusable/complex behavior where it adds value
- [ ] Large reusable Alpine state is not duplicated across Blade pages
- [ ] No new visual pattern was invented without a real requirement

---

# 41. AI Agent Workflow for Every UI Task

Before implementing or redesigning a backend page:

```text
STEP 1
Read ARCHITECTURE.md.

STEP 2
Read design.md.

STEP 3
Read the relevant workflow specification.

STEP 4
Inspect edushopify_dashboard_reference.html when exact visual guidance is needed.

STEP 5
Inspect the existing target layout, Blade view, components, CSS, and JS.

STEP 6
Identify portal:
Admin / Buyer / Supplier / Shared.

STEP 7
Identify page type:
Dashboard / List / Table / Form / Detail / Settings / Workflow.

STEP 8
Reuse existing shared Blade components first.

STEP 9
If visual markup is repeated, use/create a focused Blade component.
If interaction is small/local, use inline x-data.
If interaction is reusable/complex, use Alpine.data().

STEP 10
Verify the global shell:
Sidebar brand = h-20.
Topbar = h-20.
Do not use padding-based topbar height.

STEP 11
Apply the exact design tokens and component patterns from this file.

STEP 12
Use CSS variables for theme-sensitive navigation styles.

STEP 13
Apply semantic status/action colors.

STEP 14
Apply responsive behavior.

STEP 15
Apply permission-aware visibility.

STEP 16
Verify active route/menu/submenu state.

STEP 17
Verify empty/error/loading/submission states.

STEP 18
Do not introduce a competing visual pattern.
```

---

# 42. Prohibited Design Patterns

Do not:

- use Montserrat for new backend pages
- introduce Bootstrap
- introduce backend Livewire
- introduce Filament
- use a padding-based topbar height instead of the mandatory `h-20`
- use different topbar/sidebar-brand heights between portals
- shorten the topbar height only on mobile
- use different fonts in different portals
- create random color schemes per module
- hard-code sidebar hover/active colors in individual page views
- use theme-primary for delete/reject actions
- use red for normal primary actions
- use huge shadows on normal cards
- use excessive gradients
- make every surface heavily rounded
- create unreadable mobile tables
- create giant unsectioned forms
- duplicate the same form markup/styles across many modules when a reusable component is appropriate
- duplicate the same table shell/design across many Blade pages when a reusable component is appropriate
- create `Alpine.data()` for trivial one-off toggles without a reuse/complexity reason
- duplicate large Alpine `x-data` objects across multiple Blade files
- show unauthorized actions
- depend on color alone for status
- create a second dashboard design system
- allow Admin theme settings to destroy semantic status meanings

---

# 43. Final Visual Contract

EduShopify backend should consistently look like:

```text
FONT
Inter

APPLICATION CANVAS
Gray-50

SURFACES
White

BORDERS
Gray-200

PRIMARY TEXT
Gray-900

SECONDARY TEXT
Gray-500 / Gray-600

DEFAULT ACCENT
Indigo #4F46E5

SUPPLIER CONTEXT ACCENT
Teal #0D9488 where applicable

SIDEBAR
256px
Theme-variable driven
Scrollable navigation
Mobile drawer

TOPBAR
h-20 on desktop and mobile
Same height as sidebar brand/logo header
No padding-based height
White
Subtle border aligned with sidebar brand border
Breadcrumbs
Search where appropriate
Notifications
Profile menu

CARDS
White
Rounded-xl
Border-gray-200
Minimal shadow

INPUTS
Rounded-lg
Gray-300 border
2.5 vertical padding
Accent focus

TABLES
Compact
Gray-50 header
px-5 / py-3 or py-3.5
Subtle row hover
Horizontal mobile scrolling

PRIMARY BUTTON
Theme accent

SEMANTIC ACTIONS
Green / Amber / Red / Gray according to meaning

ANIMATION
Subtle
Approximately 150–300ms

REUSE
Repeated visual UI → Blade Component
Small local interaction → inline Alpine x-data
Reusable/complex interaction → Alpine.data()

OVERALL CHARACTER
Professional enterprise B2B marketplace
```

Use this document as the **single project-wide UI/UX standard for the EduShopify backend**.
