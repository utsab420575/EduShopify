<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Edushopify — B2B Education Procurement Marketplace' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fbf6',
                            100: '#d3efe2',
                            200: '#a9ded5',
                            300: '#7ecab6',
                            400: '#57b799',
                            500: '#3da47e',
                            600: '#2d8a67',
                            700: '#216c50',
                            900: '#124633',
                        },
                        emerald: {
                            50: '#f0fbf6',
                            100: '#d3efe2',
                            200: '#a9ded5',
                            300: '#7ecab6',
                            400: '#57b799',
                            500: '#3da47e',
                            600: '#2d8a67',
                            700: '#216c50',
                            900: '#124633',
                        },
                        accent: {
                            500: '#6366f1',
                            600: '#4f46e5',
                        }
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 1s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .text-gradient {
            background: linear-gradient(to right, #216c50, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased font-sans overflow-x-hidden selection:bg-brand-500 selection:text-white">

    <!-- Top Nav (from homepage.html design, but with previous menu logic) -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-[1400px] mx-auto px-4 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
                <img class="h-8 w-auto" src="{{ asset('images/logo.png') }}" alt="Edushopify Logo">
            </a>

            <!-- Header Actions & Contacts -->
            <div class="hidden md:flex items-center gap-6">
                <!-- Contact info (left of login buttons) -->
                <div class="flex items-center gap-4 text-xs font-semibold text-slate-500 border-r border-slate-200 pr-6 mr-1">
                    <a href="mailto:info@edushopify.com" class="flex items-center gap-1.5 hover:text-brand-600 transition-colors">
                        <!-- Envelope icon -->
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"></path></svg>
                        info@edushopify.com
                    </a>
                    <a href="tel:+17788049199" class="flex items-center gap-1.5 hover:text-brand-600 transition-colors whitespace-nowrap">
                        <!-- Phone icon -->
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.122-4.1-6.924-6.924l1.293-.97a1.242 1.242 0 0 0 .417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"></path></svg>
                        +1 778 804 9199
                    </a>
                </div>

                <!-- Nav Links (Previous Menu) -->
                <div class="flex space-x-6 items-center">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? url('/admin') : (auth()->user()->isSupplier() ? url('/supplier') : (auth()->user()->isBuyer() ? url('/buyer') : url('/dashboard'))) }}" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors font-display">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors font-display">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors font-display">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-500/30 transform hover:-translate-y-0.5 transition-all duration-200 font-display">
                                Reserve My Supplier Profile
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button id="mobile-menu-btn" class="text-slate-600 hover:text-slate-900 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-4 px-4 space-y-3 bg-white border-b border-slate-200">
             @auth
                <a href="{{ auth()->user()->isAdmin() ? url('/admin') : (auth()->user()->isSupplier() ? url('/supplier') : (auth()->user()->isBuyer() ? url('/buyer') : url('/dashboard'))) }}" class="block px-3 py-2 text-base font-medium text-slate-700 hover:text-brand-600">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="block px-3 py-2">
                    @csrf
                    <button type="submit" class="text-base font-medium text-slate-700 hover:text-brand-600 w-full text-left">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-slate-700 hover:text-brand-600">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-brand-600 hover:text-brand-700">Reserve My Supplier Profile</a>
                @endif
            @endauth
            
            <!-- Contact info (Mobile) -->
            <div class="border-t border-slate-100 pt-3 flex flex-col gap-2.5 text-sm font-semibold text-slate-500 px-3">
                <a href="mailto:info@edushopify.com" class="flex items-center gap-2 hover:text-brand-600 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"></path></svg>
                    info@edushopify.com
                </a>
                <a href="tel:+17788049199" class="flex items-center gap-2 hover:text-brand-600 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.122-4.1-6.924-6.924l1.293-.97a1.242 1.242 0 0 0 .417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"></path></svg>
                    +1 778 804 9199
                </a>
            </div>
        </div>
    </header>

    {{ $slot }}

    <!-- Footer (from homepage.html) -->
    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-[1400px] mx-auto px-4 lg:px-8 py-14">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10">
                <div class="col-span-2 md:col-span-2">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img class="h-8 w-auto" src="{{ asset('images/logo.png') }}" alt="Edushopify Logo">
                </a>
                <p class="mt-5 text-slate-500 max-w-sm">
                    The B2B education procurement marketplace connecting institutional buyers with verified suppliers worldwide.
                </p>
                </div>

                <div>
                <h4 class="font-bold text-slate-900">Marketplace</h4>
                <ul class="mt-4 space-y-3 text-slate-500">
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Browse Suppliers</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Products</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Categories</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Request Quote</a></li>
                </ul>
                </div>

                <div>
                <h4 class="font-bold text-slate-900">Company</h4>
                <ul class="mt-4 space-y-3 text-slate-500">
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">About Us</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Contact</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Careers</a></li>
                </ul>
                </div>

                <div>
                <h4 class="font-bold text-slate-900">Support</h4>
                <ul class="mt-4 space-y-3 text-slate-500">
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Help Center</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Pricing</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-emerald-600 transition-colors">Privacy Policy</a></li>
                </ul>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col md:flex-row gap-6 items-center justify-between text-sm text-slate-500">
                <p>© {{ date('Y') }} Edushopify. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <!-- Facebook -->
                        <a href="#" class="text-slate-400 hover:text-[#1877F2] transition-colors" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <!-- Instagram -->
                        <a href="#" class="text-slate-400 hover:text-[#E4405F] transition-colors" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <!-- YouTube -->
                        <a href="#" class="text-slate-400 hover:text-[#FF0000] transition-colors" aria-label="YouTube">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M21.582 6.186a2.665 2.665 0 00-1.876-1.884C18.05 3.86 12 3.86 12 3.86s-6.05 0-7.706.442a2.665 2.665 0 00-1.876 1.884C2 7.854 2 12 2 12s0 4.146.418 5.814a2.665 2.665 0 001.876 1.884C6.05 20.14 12 20.14 12 20.14s6.05 0 7.706-.442a2.665 2.665 0 001.876-1.884C22 16.146 22 12 22 12s0-4.146-.418-5.814zM9.8 15.228V8.772L15.4 12 9.8 15.228z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <!-- LinkedIn -->
                        <a href="#" class="text-slate-400 hover:text-[#0A66C2] transition-colors" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <!-- X (Twitter) -->
                        <a href="#" class="text-slate-400 hover:text-black transition-colors" aria-label="X (Twitter)">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile Menu Toggle
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    </script>
    {{ $scripts ?? '' }}
</body>
</html>
