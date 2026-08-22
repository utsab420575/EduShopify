# EduShopify — Registration Pages UI Design Spec

> This file is a **UI design reference** for building any new registration, onboarding, or auth page in EduShopify.
> Follow the colors, typography, spacing, component patterns, and states documented here so all pages stay visually consistent.

---

## 1. Foundation

### CSS Framework
- **Tailwind CSS** (via CDN) with custom `brand` color extension
- **No additional CSS files** for auth pages — all styling is inline Tailwind classes

### Typography
- **Font:** `Inter` (Google Fonts)
  - Weights used: 300, 400, 500, 600, 700, 800
  - Import: `https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap`
  - Applied globally: `* { font-family: 'Inter', sans-serif; }`
- **Base text size:** `text-sm` (14px) for form content
- **Heading H1:** `text-2xl font-bold text-gray-900` (24px, weight 700) — main page title
- **Heading H1 (wizard):** `text-xl font-bold text-gray-900` (20px) — smaller for multi-step pages
- **Heading H2 (section):** `text-base font-semibold text-gray-800` (16px) — section titles inside wizard
- **Labels:** `text-xs font-medium text-gray-700` (12px, weight 500)
- **Sub-labels / hints:** `text-xs text-gray-400 font-normal` (10px, gray-400)
- **Step indicator text:** `text-xs text-gray-500` (12px, gray-500)
- **Step label tiny text:** `text-[10px]` (10px)
- **Helper/footer text:** `text-sm text-gray-500` (14px)

---

## 2. Color Palette

### Brand Colors (Custom Tailwind Extension)
```
brand-50  : #eef2ff
brand-100 : #e0e7ff
brand-500 : #6366f1   (Indigo)
brand-600 : #4f46e5
brand-700 : #4338ca
brand-800 : #3730a3
brand-900 : #312e81
```

### Color Usage by Role

| Element | Color Token | Hex |
| :--- | :--- | :--- |
| **Buyer accent** (primary) | `indigo-500` / `indigo-600` | `#6366f1` / `#4f46e5` |
| **Supplier accent** (primary) | `teal-500` / `teal-600` | `#14b8a6` / `#0d9488` |
| Page background | `bg-gray-50` | `#f9fafb` |
| Card background | `bg-white` | `#ffffff` |
| Card border | `border-gray-100` | `#f3f4f6` |
| Input border (default) | `border-gray-300` | `#d1d5db` |
| Input border (focus, buyer) | `ring-indigo-500` | `#6366f1` |
| Input border (focus, supplier) | `ring-teal-500` | `#14b8a6` |
| Input border (error) | `border-red-400` | `#f87171` |
| Input background (error) | `bg-red-50` | `#fef2f2` |
| Required asterisk | `text-red-500` | `#ef4444` |
| Error message text | `text-red-600` | `#dc2626` |
| Success flash bg | `bg-green-50 border-green-200` | |
| Success flash text | `text-green-700` | `#15803d` |
| Error flash bg | `bg-red-50 border-red-200` | |
| Error flash text | `text-red-700` | `#b91c1c` |
| Progress bar (buyer) | `bg-indigo-500` | `#6366f1` |
| Progress bar (supplier) | `bg-teal-500` | `#14b8a6` |
| Progress track | `bg-gray-100` | `#f3f4f6` |
| Step label (completed) | `text-teal-600 font-semibold` | |
| Step label (upcoming) | `text-gray-400` | |
| % badge bg (supplier) | `bg-teal-50 border-teal-200` text `text-teal-700` | |
| Section number badge (supplier) | `bg-teal-100 text-teal-600` | |
| Primary CTA button (buyer) | `bg-indigo-600 hover:bg-indigo-700` | |
| Primary CTA button (supplier) | `bg-teal-600 hover:bg-teal-700` | |
| Secondary/back button | `bg-gray-100 hover:bg-gray-200 text-gray-700` | |
| Link (buyer) | `text-indigo-600 hover:text-indigo-800` | |
| Link (supplier switch) | `text-teal-600 hover:text-teal-800` | |

---

## 3. Page Layout (Auth Layout Shell)

**File:** `resources/views/components/layouts/auth.blade.php`

```
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4">
  <div class="relative z-10 w-full {maxWidth}">
    <!-- Logo (centered, h-12) -->
    <!-- Optional subtitle (text-sm text-gray-500) -->
    <!-- Flash messages (success / error) -->
    <!-- Card: bg-white rounded-2xl shadow-xl p-6 sm:p-8 border border-gray-100 -->
    <!-- Footer links: Home · Sign In · Copyright -->
  </div>
</body>
```

### Layout Widths
| Page | `maxWidth` class |
| :--- | :--- |
| Login, Register Type Selector | `max-w-lg` (default, 512px) |
| Buyer Register (Step 1) | `max-w-lg` |
| Buyer Profile Complete (Step 3) | `max-w-3xl` (768px) |
| Supplier Register (Step 1) | `max-w-lg` |
| Supplier Application Wizard | `max-w-3xl` (768px) |

### Logo
- Image: `public/images/logo.png`
- Size: `h-12 w-auto object-contain`
- Centered above card: `text-center mb-8`

### Card
```
bg-white rounded-2xl shadow-xl p-6 sm:p-8 border border-gray-100
```
- `rounded-2xl` — 16px corner radius
- `shadow-xl` — prominent elevation
- Padding: `p-6` mobile, `p-8` (sm+)

### Footer (below card)
```
text-center mt-6 text-gray-500 text-sm space-x-4
```
Links: `Home · Sign In · © 2024 Edushopify`
Each link: `hover:text-gray-900 transition`

---

## 4. Form Elements

### Text / Email / Password / Number Input

**Default state:**
```html
class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm
       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
       transition"
```

**Compact (inside grid, buyer profile & supplier wizard):**
```html
class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
       focus:ring-2 focus:ring-{accent}-500 focus:border-transparent"
```
- `rounded-xl` (12px) on standalone full-width inputs
- `rounded-lg` (8px) on grid/compact inputs
- Padding: `px-4 py-2.5` full, `px-3 py-2.5` compact

**Error state (append to class):**
```
@error('field') border-red-400 bg-red-50 @enderror
```

**Error message below input:**
```html
@error('field')
  <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
```

### Textarea
```html
class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
       focus:ring-2 focus:ring-{accent}-500 focus:border-transparent"
rows="2"   <!-- or rows="3" for longer content -->
```

### Select / Dropdown
```html
class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
       focus:ring-2 focus:ring-{accent}-500 focus:border-transparent bg-white"
```
- Always include `bg-white` to prevent browser default gray background
- Disabled state: add `disabled` attribute; text shows `"Select country first"`

### Label

**Standard:**
```html
<label class="block text-sm font-medium text-gray-700 mb-1.5">
  Field Name <span class="text-red-500">*</span>
</label>
```

**Compact (inside grid):**
```html
<label class="block text-xs font-medium text-gray-700 mb-1.5">
  Field Name <span class="text-red-500">*</span>
</label>
```

**Optional field label:**
```html
Field Name <span class="text-gray-400 font-normal">(optional)</span>
```

**Label with size hint (for file uploads):**
```html
<label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
  <span>Photo</span>
  <span class="text-[10px] text-gray-400 font-normal">(Rec: 500×500 px)</span>
</label>
```

### Field Spacing
- Between full-width fields: `mb-4`
- Last field before submit button: `mb-6`
- Grid gap: `gap-3` (compact) or `gap-4` (standard)

---

## 5. Buttons

### Primary CTA Button (Buyer)
```html
<button type="submit"
  class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
         text-white font-semibold py-3 rounded-xl
         transition-all duration-200
         flex items-center justify-center gap-2
         text-sm shadow-lg">
  Button Text
</button>
```

### Primary CTA Button (Supplier)
Same as above but replace `indigo` with `teal`:
```html
class="w-full bg-teal-600 hover:bg-teal-700 disabled:opacity-60
       text-white font-semibold py-3 rounded-xl
       transition-all duration-200 text-sm shadow-lg"
```

### Loading State (Livewire)
Always add loading spinner inside submit buttons:
```html
<button wire:loading.attr="disabled" wire:target="actionName" ...>
  <span wire:loading.remove wire:target="actionName">Button Text</span>
  <span wire:loading wire:target="actionName" class="flex items-center gap-2">
    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    Loading text…
  </span>
</button>
```

### Secondary / Back Button (Wizard)
```html
<button type="button" wire:click="prevStep"
  class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700
         font-medium rounded-xl text-sm transition">
  Back
</button>
```

### Next Step Button (Wizard)
```html
<button type="button" wire:click="nextStep"
  class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white
         font-semibold rounded-xl text-sm shadow transition">
  Next →
</button>
```

### Wizard Navigation Row
```html
<div class="flex justify-between items-center mt-8 pt-5 border-t border-gray-100">
  <!-- Back button (hidden on step 1) -->
  <!-- Next / Submit button -->
</div>
```

---

## 6. Progress Bar

### Buyer (Single Bar, 3 steps)
```html
<div class="mt-3 h-1.5 bg-gray-100 rounded-full">
  <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 33%"></div>
  <!--  Step 1 = 33%, Step 2 = 66%, Step 3 = 100% -->
</div>
```

### Supplier (Segmented Bar, 9 steps)
```html
<div class="flex gap-1 mt-3">
  @for($i = 1; $i <= $totalSteps; $i++)
    <div class="flex-1 h-1.5 rounded-full transition-all duration-300
         {{ $i <= $step ? 'bg-teal-500' : 'bg-gray-200' }}"></div>
  @endfor
</div>
<!-- Step labels below: text-[10px], teal-600 for completed, gray-400 for upcoming -->
```

### % Complete Badge (Supplier)
```html
<span class="text-xs font-medium text-teal-700 bg-teal-50 px-3 py-1
             rounded-full border border-teal-200">
  {{ round(($step / $totalSteps) * 100) }}% complete
</span>
```

---

## 7. Section Header (Wizard)

Each wizard section starts with a numbered heading:
```html
<h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
  <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full
               flex items-center justify-center text-xs font-bold">
    {N}
  </span>
  Section Title
</h2>
```

---

## 8. File Upload / Image Picker

```html
<label class="relative w-28 h-28 overflow-hidden flex flex-col items-center justify-center
              border-2 border-dashed border-gray-300 rounded-xl cursor-pointer
              hover:border-{accent}-400 hover:bg-{accent}-50
              transition group bg-white">
  <!-- If preview available: -->
  <img src="{{ $previewUrl }}" class="absolute inset-0 w-full h-full object-cover">
  <!-- If no preview: -->
  <svg class="w-6 h-6 text-gray-400 group-hover:text-{accent}-500 transition-colors" ...>
    <!-- upload icon -->
  </svg>
  <input type="file" wire:model="field" class="hidden" accept="image/*">
</label>
```

- Square drop zone: `w-28 h-28`
- Wider banner drop zone: `w-full h-36` with `aspect-[3/1]`
- Border: `border-2 border-dashed border-gray-300`
- Hover: `hover:border-{accent}-400 hover:bg-{accent}-50`
- Corner radius: `rounded-xl`
- Hidden native input inside label

---

## 9. Flash Messages (Layout Level)

These appear above the card, inside the layout shell:

**Success:**
```html
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl
            text-green-700 text-sm flex items-center gap-2">
  <!-- checkmark SVG icon -->
  {{ session('success') }}
</div>
```

**Error:**
```html
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl
            text-red-700 text-sm flex items-center gap-2">
  <!-- exclamation SVG icon -->
  {{ session('error') }}
</div>
```

**Inline validation error (form level):**
```html
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
  {{ $errors->first() }}
</div>
```

---

## 10. Status/State Blocks (Pending Page)

Used on the Supplier Pending status page and can be reused for other status screens.

**Pending / Info (Teal):**
```html
<div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-5">
  <!-- icon in text-teal-600 -->
</div>
<h1 class="text-xl font-bold text-gray-900 mb-2">Title</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-6">Description</p>
```

**Warning / Revision (Amber):**
```html
<div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5">
  <!-- icon in text-amber-500 -->
</div>
<h1 class="text-xl font-bold text-amber-700 mb-2">Title</h1>
<div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-left">
  <p class="text-xs font-semibold text-amber-700 mb-1">Reviewer notes:</p>
  <p class="text-sm text-amber-800">{{ $note }}</p>
</div>
```

**Error / Rejected (Red):**
```html
<div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
  <!-- icon in text-red-500 -->
</div>
<h1 class="text-xl font-bold text-red-700 mb-2">Title</h1>
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-left">
  <p class="text-xs font-semibold text-red-700 mb-1">Reason:</p>
  <p class="text-sm text-red-800">{{ $reason }}</p>
</div>
```

---

## 11. Type Selector Cards (Register Page)

Used for the Buyer vs Supplier selector. Can be reused for any "choose your type" screen.

```html
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <a href="{{ route('register.buyer') }}"
     class="group relative flex flex-col items-center p-6
            border-2 border-gray-200 rounded-xl
            hover:border-indigo-500 hover:bg-indigo-50
            transition-all duration-200 cursor-pointer">
    <!-- Icon container -->
    <div class="w-14 h-14 bg-indigo-100 group-hover:bg-indigo-500 rounded-xl
                flex items-center justify-center transition-colors mb-4">
      <!-- SVG icon: text-indigo-600 group-hover:text-white -->
    </div>
    <h2 class="font-semibold text-gray-900 text-base">Card Title</h2>
    <p class="text-xs text-gray-500 text-center mt-1">Subtitle text</p>
    <!-- Arrow badge top-right, visible on hover -->
    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
      <div class="w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
        <!-- Arrow right SVG, w-3 h-3 text-white -->
      </div>
    </div>
  </a>
  <!-- Supplier card: same structure but teal instead of indigo -->
</div>
```

---

## 12. Checkbox Cards (Supplier Types / Exhibitions)

Multi-select checkbox items styled as interactive cards:
```html
<button type="button" wire:click="toggleSupplierType({{ $type->id }})"
  class="w-full text-left px-3 py-2.5 rounded-lg border text-sm transition-all
         {{ in_array($type->id, $supplier_type_ids)
            ? 'border-teal-500 bg-teal-50 text-teal-700 font-medium'
            : 'border-gray-200 hover:border-teal-300 text-gray-700' }}">
  {{ $type->name }}
</button>
```

Selected state: `border-teal-500 bg-teal-50 text-teal-700 font-medium`
Unselected state: `border-gray-200 hover:border-teal-300 text-gray-700`

---

## 13. Grid Layouts

| Use Case | Classes |
| :--- | :--- |
| Two equal columns | `grid grid-cols-2 gap-3` |
| Two equal columns (wider gap) | `grid grid-cols-2 gap-4` |
| Full-width inside grid | `col-span-2` |
| Three equal columns | `grid grid-cols-3 gap-3` |
| Responsive single → double | `grid grid-cols-1 sm:grid-cols-2 gap-4` |
| Stacked form fields | `space-y-4` |

---

## 14. Inline "Already have an account?" Links

Always placed after the submit button:
```html
<div class="mt-5 text-center text-sm text-gray-500">
  Already have an account?
  <a href="{{ route('login') }}"
     class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
</div>

<div class="mt-3 text-center text-sm text-gray-400">
  Registering as a supplier instead?
  <a href="{{ route('register.supplier') }}"
     class="text-teal-600 hover:text-teal-800 font-medium transition">Switch</a>
</div>
```

---

## 15. Transitions & Animations

| Element | Classes |
| :--- | :--- |
| All interactive elements | `transition` or `transition-all duration-200` |
| Button hover | `transition-all duration-200` |
| Card hover (type selector) | `transition-all duration-200` |
| Progress bar fill | `transition-all duration-300` |
| Loading spinner | `animate-spin` |
| Hover opacity reveal (arrow badge) | `opacity-0 group-hover:opacity-100 transition-opacity` |

---

## 16. Accent Color Rules Summary

| User type | Primary | Focus ring | Button | Progress | Link |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Buyer** | indigo-600 | ring-indigo-500 | bg-indigo-600 hover:bg-indigo-700 | bg-indigo-500 | text-indigo-600 |
| **Supplier** | teal-600 | ring-teal-500 | bg-teal-600 hover:bg-teal-700 | bg-teal-500 | text-teal-600 |

When building a new page:
- If it is a **buyer** page → use **indigo** everywhere
- If it is a **supplier** page → use **teal** everywhere
- If it is a **shared/neutral** page → use **indigo** as default

---

## 17. Quick Copy — New Auth Page Template

Use this as a starting point for any new auth page:

```blade
<x-layouts.auth title="Page Title">

  {{-- Page heading + step indicator --}}
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Page Heading</h1>
    <p class="text-gray-500 text-sm mt-1">Optional subtitle or step info</p>
    {{-- Progress bar (buyer) --}}
    <div class="mt-3 h-1.5 bg-gray-100 rounded-full">
      <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 50%"></div>
    </div>
  </div>

  {{-- Inline errors --}}
  @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- Form --}}
  <form wire:submit="save" novalidate>
    @csrf

    {{-- Single field --}}
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1.5">
        Field Label <span class="text-red-500">*</span>
      </label>
      <input type="text" wire:model.blur="field_name"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm
               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
               transition @error('field_name') border-red-400 bg-red-50 @enderror"
        placeholder="Placeholder text">
      @error('field_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Two column grid --}}
    <div class="grid grid-cols-2 gap-3 mb-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Field A</label>
        <input type="text" wire:model.blur="field_a"
          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Field B</label>
        <input type="text" wire:model.blur="field_b"
          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
      </div>
    </div>

    {{-- Submit button --}}
    <button type="submit" wire:loading.attr="disabled" wire:target="save"
      class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60
             text-white font-semibold py-3 rounded-xl
             transition-all duration-200 flex items-center justify-center gap-2
             text-sm shadow-lg">
      <span wire:loading.remove wire:target="save">Submit</span>
      <span wire:loading wire:target="save" class="flex items-center gap-2">
        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        Saving…
      </span>
    </button>
  </form>

  {{-- Bottom links --}}
  <div class="mt-5 text-center text-sm text-gray-500">
    Already have an account?
    <a href="{{ route('login') }}"
       class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
  </div>

</x-layouts.auth>
```
