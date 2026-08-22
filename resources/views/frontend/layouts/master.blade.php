<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="frontend scroll-smooth">
@include('frontend.layouts.partials._head')
<body class="bg-[--fe-canvas] text-[--fe-text] antialiased" style="background:var(--fe-canvas);">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg">
        Skip to content
    </a>

    @include('frontend.layouts.partials._header')
    @include('frontend.layouts.partials._mobile_menu')

    <main id="main-content">
        @if (session('success'))
            <div class="fe-container pt-4">
                <div class="rounded-xl border px-4 py-3 text-sm flex items-center gap-2" style="background:var(--fe-success-soft);border-color:var(--fe-success);color:#166534;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="fe-container pt-4">
                <div class="rounded-xl border px-4 py-3 text-sm flex items-center gap-2" style="background:var(--fe-danger-soft);border-color:var(--fe-danger);color:#991b1b;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('frontend.layouts.partials._footer')
    @include('frontend.layouts.partials._scripts')
</body>
</html>
