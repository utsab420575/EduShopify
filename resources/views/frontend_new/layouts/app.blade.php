<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Edushopify – Global Suppliers for Education')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/frontend_new.css', 'resources/js/frontend_new.js'])
    @stack('head')
</head>
<body class="@yield('body_class', 'bg-white') text-gray-800 antialiased">

    @include('frontend_new.partials.mobile-menu')
    @include('frontend_new.partials.navbar')

    @yield('content')

    @include('frontend_new.partials.footer')

    @stack('scripts')
</body>
</html>
