<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — EduShopify</title>
<link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js" defer></script>

@include('backend.layouts.partials.shared._theme', ['theme' => $themeSettings ?? []])
<style>
    html, body { font-family: 'Inter', sans-serif; }
    [x-cloak] { display: none !important; }
    body { background: var(--page-bg); }
</style>
@stack('styles')
</head>
<body class="min-h-screen text-gray-900 antialiased" x-data="{ mobileSidebar:false, notifOpen:false, profileOpen:false }">

    <div class="fixed inset-0 flex overflow-hidden" style="background:var(--page-bg)">

        <div x-show="mobileSidebar" x-transition.opacity @click="mobileSidebar=false"
             class="fixed inset-0 bg-gray-900/40 z-30 lg:hidden" x-cloak></div>

        @yield('sidebar')

        <div class="flex-1 flex flex-col min-w-0 min-h-0">

            @yield('topbar')

            <main class="flex-1 overflow-y-auto p-4 lg:p-6" style="background:var(--page-bg)">
                @yield('content')
            </main>

            <footer class="bg-white border-t px-4 lg:px-6 py-3 text-center shrink-0" style="border-color:var(--topbar-border)">
                <p class="text-xs text-gray-400">&copy; {{ now()->year }} EduShopify. All rights reserved.</p>
            </footer>

        </div>
    </div>

    @stack('scripts')
</body>
</html>
