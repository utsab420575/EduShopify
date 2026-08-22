<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Edushopify</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fbf6', 100: '#d3efe2', 200: '#a9ded5', 300: '#7ecab6',
                            400: '#57b799', 500: '#3da47e', 600: '#2d8a67', 700: '#216c50', 900: '#124633',
                        },
                    },
                }
            }
        }
    </script>

    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased font-sans" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen lg:pl-64">

        <x-dashboard.sidebar :role="$role" />

        <div class="lg:flex lg:flex-col lg:min-h-screen">
            <x-dashboard.topbar :role="$role" :title="$title ?? null" />

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    {{ $slot }}
                </div>
            </main>

            <x-dashboard.footer />
        </div>
    </div>

    @livewireScripts
    {{ $scripts ?? '' }}
</body>
</html>
