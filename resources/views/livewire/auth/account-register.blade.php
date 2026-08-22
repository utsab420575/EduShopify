<div>
    {{-- Progress Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    @if($step === 1) Step 1 of 3 — Account Type
                    @elseif($step === 2) Step 2 of 3 — Capability
                    @else Step 3 of 3 — Your Details
                    @endif
                </p>
            </div>
        </div>

        {{-- Segmented progress bar --}}
        <div class="flex gap-1 mt-3">
            @for($i = 1; $i <= $totalSteps; $i++)
                <div class="flex-1 h-1.5 rounded-full transition-all duration-300
                    {{ $i <= $step ? 'bg-indigo-500' : 'bg-gray-200' }}"></div>
            @endfor
        </div>
    </div>

    {{-- ══════════════ STEP 1: Account Type ══════════════ --}}
    @if($step === 1)
    <div>
        <p class="text-sm text-gray-500 mb-5">
            Choose the type of account you want to create.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            {{-- Individual --}}
            <button type="button" wire:click="chooseAccountType('individual')"
                class="group relative flex flex-col items-center p-6 border-2 border-gray-200 rounded-xl
                       hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200 cursor-pointer text-left w-full">
                <div class="w-14 h-14 bg-indigo-100 group-hover:bg-indigo-500 rounded-xl
                            flex items-center justify-center transition-colors mb-4">
                    <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-gray-900 text-base">Individual</h2>
                <p class="text-xs text-gray-500 text-center mt-1">Personal account for a single buyer or supplier</p>
                <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </button>

            {{-- Organization --}}
            <button type="button" wire:click="chooseAccountType('organization')"
                class="group relative flex flex-col items-center p-6 border-2 border-gray-200 rounded-xl
                       hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200 cursor-pointer text-left w-full">
                <div class="w-14 h-14 bg-indigo-100 group-hover:bg-indigo-500 rounded-xl
                            flex items-center justify-center transition-colors mb-4">
                    <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-gray-900 text-base">Organisation</h2>
                <p class="text-xs text-gray-500 text-center mt-1">School, university, company, NGO or government body</p>
                <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </button>
        </div>

        <div class="text-center text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
        </div>
    </div>

    {{-- ══════════════ STEP 2: Capability ══════════════ --}}
    @elseif($step === 2)
    <div>
        <p class="text-sm text-gray-500 mb-5">
            What will you use Edushopify for?
        </p>

        <div class="space-y-3 mb-6">
            {{-- Buyer --}}
            <button type="button" wire:click="chooseCapability('buyer')"
                class="group w-full flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl
                       hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-200 text-left">
                <div class="w-11 h-11 bg-indigo-100 group-hover:bg-indigo-500 rounded-xl
                            flex items-center justify-center transition-colors flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm">I'm a Buyer</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Source products and services, issue RFQs, compare quotes</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-indigo-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Supplier --}}
            <button type="button" wire:click="chooseCapability('supplier')"
                class="group w-full flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl
                       hover:border-teal-500 hover:bg-teal-50 transition-all duration-200 text-left">
                <div class="w-11 h-11 bg-teal-100 group-hover:bg-teal-500 rounded-xl
                            flex items-center justify-center transition-colors flex-shrink-0">
                    <svg class="w-6 h-6 text-teal-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 2.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm">I'm a Supplier</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Sell products and services, respond to RFQs, manage listings</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-teal-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Both --}}
            <button type="button" wire:click="chooseCapability('both')"
                class="group w-full flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl
                       hover:border-violet-500 hover:bg-violet-50 transition-all duration-200 text-left">
                <div class="w-11 h-11 bg-violet-100 group-hover:bg-violet-500 rounded-xl
                            flex items-center justify-center transition-colors flex-shrink-0">
                    <svg class="w-6 h-6 text-violet-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm">Both — Buy & Sell</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Access both Buyer and Supplier capabilities on one account</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-violet-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <button type="button" wire:click="prevStep"
            class="w-full text-center text-sm text-gray-400 hover:text-gray-600 transition">
            ← Back
        </button>
    </div>

    {{-- ══════════════ STEP 3: Basic Details ══════════════ --}}
    @else
    <div>
        {{-- Context badge --}}
        <div class="flex gap-2 mb-5">
            <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full border border-indigo-200 capitalize">
                {{ $account_type === 'organization' ? 'Organisation' : 'Individual' }}
            </span>
            <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full border border-indigo-200 capitalize">
                {{ $capability === 'both' ? 'Buyer + Supplier' : ucfirst($capability) }}
            </span>
        </div>

        <form wire:submit="register" novalidate>
            @csrf

            {{-- Org name (organization only) --}}
            @if($account_type === 'organization')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Organisation Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="organization_display_name" autocomplete="organization"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('organization_display_name') border-red-400 bg-red-50 @enderror"
                    placeholder="School, university or company name">
                @error('organization_display_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            @endif

            {{-- Name --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Your Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="name" autocomplete="name"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('name') border-red-400 bg-red-50 @enderror"
                    placeholder="John Smith">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" wire:model.blur="email" autocomplete="email"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('email') border-red-400 bg-red-50 @enderror"
                    placeholder="you@organization.com">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Phone Number <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input type="tel" wire:model.blur="phone" autocomplete="tel"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="+971 50 000 0000">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" wire:model.blur="password" autocomplete="new-password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('password') border-red-400 bg-red-50 @enderror"
                    placeholder="Min. 8 characters">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <input type="password" wire:model.blur="password_confirmation" autocomplete="new-password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition @error('password_confirmation') border-red-400 bg-red-50 @enderror"
                    placeholder="Repeat your password">
                @error('password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" wire:loading.attr="disabled" wire:target="register"
                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-lg">
                <span wire:loading.remove wire:target="register">Create Account →</span>
                <span wire:loading wire:target="register" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Creating account…
                </span>
            </button>
        </form>

        <div class="mt-4 text-center">
            <button type="button" wire:click="prevStep"
                class="text-sm text-gray-400 hover:text-gray-600 transition">
                ← Change capability selection
            </button>
        </div>

        <div class="mt-3 text-center text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
        </div>
    </div>
    @endif
</div>
