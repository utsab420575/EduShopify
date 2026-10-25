{{--
    Shown at the top of every Supplier workspace page (Business Profile,
    Catalog › Listings) while an admin is managing a supplier — makes it
    unmistakable whose data is being edited, and links between the two areas.
--}}
@php($managed = app(\App\Support\SupplierWorkspace::class)->managedAccount())
@if($managed)
    @php($managedName = $managed->supplierProfile?->display_name ?: $managed->display_name ?:'Supplier #'.$managed->id)
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-amber-900 truncate">Managing supplier: {{ $managedName }}</p>
                <p class="text-xs text-amber-800">You are editing this supplier's data as an admin. Changes are saved to their account immediately.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('admin.supplier-manage.company.profile') }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border {{ request()->routeIs('admin.supplier-manage.company.*') ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white border-amber-300 text-amber-800 hover:bg-amber-100' }}">
                <i class="fa-solid fa-building"></i> Business Profile
            </a>
            <a href="{{ route('admin.supplier-manage.catalog.listings.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border {{ request()->routeIs('admin.supplier-manage.catalog.*') ? 'bg-amber-600 border-amber-600 text-white' : 'bg-white border-amber-300 text-amber-800 hover:bg-amber-100' }}">
                <i class="fa-solid fa-boxes-stacked"></i> Products
            </a>
            <a href="{{ route('admin.suppliers.show', $managed) }}"
               class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border bg-white border-amber-300 text-amber-800 hover:bg-amber-100">
                <i class="fa-solid fa-arrow-left"></i> Back to supplier
            </a>
        </div>
    </div>
@endif
