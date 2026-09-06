<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="compare-max-items" content="{{ (int) config('comparison.max_items', 5) }}">
    <title>@yield('title', 'Edushopify – Global Suppliers for Education')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/frontend_new.css', 'resources/js/frontend_new.js'])
    @stack('head')
</head>
<body class="@yield('body_class', 'bg-white') text-gray-800 antialiased" data-authed="{{ auth()->check() ? '1' : '0' }}">

    @include('frontend_new.partials.mobile-menu')
    @include('frontend_new.partials.navbar')

    @yield('content')

    @include('frontend_new.partials.footer')

    {{-- Toast container for comparisons / notifications --}}
    <div id="fn-toast-container" class="fixed z-[200] top-5 right-5 left-5 sm:left-auto flex flex-col gap-2.5 items-end pointer-events-none"></div>

    @stack('scripts')
</body>
</html>
