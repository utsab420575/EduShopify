# EduShopify Dashboard Design System & Architecture Specification

This document serves as the complete technical and visual specification for the **EduShopify Dashboard**. It documents the exact page shell architecture, sidebar, topbar, content layout, and all component design patterns (forms, tables, typography, buttons, modals, dropdowns, and status badges) found in [`edushopify_dashboard_reference.html`](file:///c:/laragon/www/edushopify/static_design/edushopify_dashboard_reference.html).

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

```
+-----------------------------------------------------------------------------+
| BODY (fixed inset-0 flex overflow-hidden bg-gray-50)                        |
| +-------------------+ +---------------------------------------------------+ |
| |                   | | TOPBAR (bg-white border-b h-auto py-3.5 px-4/6)   | |
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
            <header class="bg-white border-b flex items-center justify-between px-4 lg:px-6 py-3.5" style="border-color:var(--topbar-border)">
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

The Topbar spans the top of the content pane. It houses:
- **Mobile Menu Toggle:** Hamburger icon (`lg:hidden`)
- **Breadcrumb Navigation:** Dynamic navigation links
- **Global Search:** Pill search field with magnifying glass icon
- **Notification Dropdown:** Bell button with badge counter and interactive flyout menu
- **User Profile Menu:** Avatar button with dropdown containing profile links and logout

```html
<header class="bg-white border-b flex items-center justify-between px-4 lg:px-6 py-3.5" style="border-color:var(--topbar-border)">
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
| **Topbar** | `bg-white border-b px-4 lg:px-6 py-3.5 flex justify-between` |
| **Main Area** | `flex-1 overflow-y-auto bg-gray-50 p-4 lg:p-6` |
| **Panels & Cards** | `bg-white rounded-xl border border-gray-200 p-5` |
| **Primary Buttons** | `.btn-primary text-sm font-medium px-4 py-2 rounded-lg` |
| **Outline Buttons** | `border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm px-4 py-2` |
| **Input Fields** | `.focus-accent px-3 py-2.5 border border-gray-300 rounded-lg text-sm` |
| **Table Headings** | `bg-gray-50 px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider` |
| **Table Rows** | `hover:bg-gray-50 px-5 py-3.5 text-sm text-gray-600 divide-y divide-gray-100` |
