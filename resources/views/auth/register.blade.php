<x-layouts.auth title="Create Account" subtitle="Join the B2B education procurement marketplace">

    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Create your account</h1>
        <p class="text-gray-500 mt-1 text-sm">Choose how you'll use Edushopify</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Buyer Card -->
        <a href="{{ route('register') }}"
           class="group relative flex flex-col items-center p-6 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200 cursor-pointer">
            <div class="w-14 h-14 bg-indigo-100 group-hover:bg-indigo-500 rounded-xl flex items-center justify-center transition-colors mb-4">
                <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h2 class="font-semibold text-gray-900 text-base">I'm a Buyer</h2>
            <p class="text-xs text-gray-500 text-center mt-1">Schools, universities, government orgs, NGOs</p>
            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Supplier Card -->
        <a href="{{ route('register') }}"
           class="group relative flex flex-col items-center p-6 border-2 border-gray-200 rounded-xl hover:border-teal-500 hover:bg-teal-50 transition-all duration-200 cursor-pointer">
            <div class="w-14 h-14 bg-teal-100 group-hover:bg-teal-500 rounded-xl flex items-center justify-center transition-colors mb-4">
                <svg class="w-7 h-7 text-teal-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="font-semibold text-gray-900 text-base">I'm a Supplier</h2>
            <p class="text-xs text-gray-500 text-center mt-1">Manufacturers, distributors, service providers</p>
            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="w-5 h-5 bg-teal-500 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    <div class="mt-6 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
    </div>

</x-layouts.auth>
