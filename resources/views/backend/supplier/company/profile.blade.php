@extends('backend.layouts.supplier')

@section('title', 'Business Profile')
@section('breadcrumb', 'Business Profile')

@section('body')

@php
    // Every accordion section below computes its initial open/closed state
    // through this one helper: a validation failure or a just-completed
    // save always force-opens its own section (via the `section` query
    // param every Company\* controller/Form Request redirects with), and
    // otherwise the section falls back to whatever the visitor last left it
    // as (persisted client-side — design.md §43.3 requires accordion state
    // to be instant/zero-server-latency, so this never touches the server).
    $spAccOpen = function (string $key, bool $defaultOpen) use ($openSection) {
        if ($openSection === $key) {
            return 'true';
        }

        return $defaultOpen ? "localStorage.getItem('sp-acc-{$key}') !== '0'" : "localStorage.getItem('sp-acc-{$key}') === '1'";
    };
@endphp

{{-- Flash notification --}}
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-5 right-5 z-[200] flex items-center gap-3 bg-white border border-green-200 text-green-800 text-sm font-medium rounded-xl shadow-lg px-5 py-3">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Page Header --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Business Profile</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your company profile, branding, locations, and documents.</p>
    </div>
    @if($profile?->isComplete())
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-green-50 text-green-700 border border-green-200">
            <i class="fa-solid fa-circle-check"></i> Profile Complete
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
            <i class="fa-solid fa-clock"></i> Draft
        </span>
    @endif
</div>

{{-- Profile Summary Card --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-4">
        <div class="relative flex-shrink-0">
            <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=e0e7ff&color=4f46e5&size=80' }}"
                 class="w-16 h-16 rounded-2xl object-cover border border-gray-200 shadow-sm" alt="Logo">
            @if($profile?->profile_photo)
                <img src="{{ asset('storage/'.$profile->profile_photo) }}"
                     class="w-7 h-7 rounded-full border-2 border-white absolute -bottom-1 -right-1 object-cover shadow" alt="">
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-lg font-bold text-gray-900 truncate">{{ $profile?->display_name ?? 'Your Business Name' }}</p>
            <p class="text-sm text-gray-500 truncate">{{ $profile?->contact_email ?: $profile?->support_email }}</p>
            @if($profile?->country_id)
                <p class="text-xs text-gray-400 mt-0.5">
                    <i class="fa-solid fa-location-dot mr-1 text-indigo-400"></i>
                    {{ collect([$profile->city?->name, $profile->country?->name])->filter()->implode(', ') }}
                </p>
            @endif
        </div>

        <div class="hidden lg:flex items-center gap-5 flex-shrink-0 border-l border-gray-100 pl-5">
            <div class="text-center">
                <p class="text-xl font-bold text-amber-500"><i class="fa-solid fa-star text-sm"></i> {{ number_format($profile?->rating ?? 0, 1) }}</p>
                <p class="text-xs text-gray-400">Rating</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-pink-600">{{ $existingGallery->count() }}</p>
                <p class="text-xs text-gray-400">Gallery</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-emerald-600">{{ $serviceAreas->count() }}</p>
                <p class="text-xs text-gray-400">Locations</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-rose-600">{{ $documents->where('is_current', true)->count() }}</p>
                <p class="text-xs text-gray-400">Documents</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════ ACCORDION ═══════════════════════════════════ --}}
<div class="space-y-3">
    @include('backend.supplier.company.partials._company')
    @include('backend.supplier.company.partials._contact')
    @include('backend.supplier.company.partials._media')
    @include('backend.supplier.company.partials._gallery')
    @include('backend.supplier.company.partials._locations')
    @include('backend.supplier.company.partials._hours')
    @include('backend.supplier.company.partials._exhibitions')
    @include('backend.supplier.company.partials._documents')
    @include('backend.supplier.company.partials._services')
    @include('backend.supplier.company.partials._achievements')
</div>

@if($openSection)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('sp-section-{{ $openSection }}');
            if (el) {
                setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 50);
            }
        });
    </script>
@endif

@endsection

{{--
    Override the shared layout's content section: it normally includes
    backend.layouts.partials.shared._flash, which dumps every validation
    error from $errors->all() into one global "Please fix the following"
    box. With 10 independent accordion sections on this one page (several
    sharing field names, e.g. "title" on both Video and Service), that
    global box can't say which section actually failed. Each section shows
    its own errors instead (see partials._section-errors), so only the
    session flashes are kept here — not the global error dump. This must
    come AFTER @section('body') above: @yield('body') below only sees
    content already captured on the section stack, and 'body' has to have
    been defined first.
--}}
@section('content')
    @if(session('success'))
        <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6" role="alert">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6" role="alert">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif
    @if(session('warning'))
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6" role="alert">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <p class="text-sm">{{ session('warning') }}</p>
        </div>
    @endif
    @yield('body')
@endsection
