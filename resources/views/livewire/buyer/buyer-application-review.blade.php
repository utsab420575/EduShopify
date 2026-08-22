<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Review Your Application</h1>
        <p class="text-gray-500 text-sm mt-1">Step 3 of 3 — Review and submit for approval</p>
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full">
            <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Read-only summary --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 divide-y divide-gray-200 mb-6">

        {{-- Account --}}
        <div class="px-5 py-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Account</p>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                    <span class="text-xs text-gray-500">Type</span>
                    <p class="font-medium text-gray-800 capitalize">{{ $account->account_type === 'organization' ? 'Organisation' : 'Individual' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-500">Account No.</span>
                    <p class="font-medium text-gray-800">{{ $account->account_number }}</p>
                </div>
            </div>
        </div>

        {{-- Profile --}}
        @if($profile)
        <div class="px-5 py-4">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Buyer Profile</p>
            <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <span class="text-xs text-gray-500">Display Name</span>
                    <p class="font-medium text-gray-800">{{ $profile->display_name ?: '—' }}</p>
                </div>
                @if($profile->organization_name)
                <div>
                    <span class="text-xs text-gray-500">Organisation</span>
                    <p class="font-medium text-gray-800">{{ $profile->organization_name }}</p>
                </div>
                @endif
                <div class="col-span-2">
                    <span class="text-xs text-gray-500">Buyer Type(s)</span>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @forelse($account->buyerTypes as $bt)
                            <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-md border border-indigo-200">
                                {{ $bt->name }}
                            </span>
                        @empty
                            <span class="font-medium text-gray-800">{{ $profile->buyerType?->name ?? '—' }}</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <span class="text-xs text-gray-500">Contact Person</span>
                    <p class="font-medium text-gray-800">{{ $profile->contact_person ?: '—' }}</p>
                </div>
                @if($profile->position)
                <div>
                    <span class="text-xs text-gray-500">Position</span>
                    <p class="font-medium text-gray-800">{{ $profile->position }}</p>
                </div>
                @endif
                <div>
                    <span class="text-xs text-gray-500">Email</span>
                    <p class="font-medium text-gray-800">{{ $profile->email ?: '—' }}</p>
                </div>
                @if($profile->phone)
                <div>
                    <span class="text-xs text-gray-500">Phone</span>
                    <p class="font-medium text-gray-800">{{ $profile->phone }}</p>
                </div>
                @endif
                <div>
                    <span class="text-xs text-gray-500">Country</span>
                    <p class="font-medium text-gray-800">{{ $profile->country?->name ?? '—' }}</p>
                </div>
                @if($profile->city?->name)
                <div>
                    <span class="text-xs text-gray-500">City</span>
                    <p class="font-medium text-gray-800">{{ $profile->city->name }}</p>
                </div>
                @endif
                <div class="col-span-2">
                    <span class="text-xs text-gray-500">Address</span>
                    <p class="font-medium text-gray-800">{{ $profile->address ?: '—' }}</p>
                </div>
                @if($profile->website)
                <div>
                    <span class="text-xs text-gray-500">Website</span>
                    <p class="font-medium text-gray-800">{{ $profile->website }}</p>
                </div>
                @endif
                @if($profile->tax_id)
                <div>
                    <span class="text-xs text-gray-500">Tax / VAT ID</span>
                    <p class="font-medium text-gray-800">{{ $profile->tax_id }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
        <a href="{{ route('buyer.onboarding.profile') }}"
            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 rounded-xl transition-all duration-200 text-sm">
            ← Edit Profile
        </a>
        <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
            class="flex-grow bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-lg">
            <span wire:loading.remove wire:target="submit">Submit for Approval →</span>
            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Submitting…
            </span>
        </button>
    </div>

    <p class="text-center text-xs text-gray-400 mt-4">
        By submitting, you confirm that the information provided is accurate and complete.
    </p>
</div>
