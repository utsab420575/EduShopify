<x-layouts.auth title="Application Under Review">

    @php
        $account    = auth()->user()?->account;
        $capability = $account?->capabilities->firstWhere('capability', 'supplier');
    @endphp

    <div class="text-center">

        @if($capability?->status === 'rejected')
            {{-- Rejected --}}
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-red-700 mb-2">Application Not Approved</h1>
            <p class="text-gray-500 text-sm mb-4">Unfortunately your application was not approved at this time.</p>

            @if($capability->rejection_reason)
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-left">
                    <p class="text-xs font-semibold text-red-700 mb-1">Reason from reviewer:</p>
                    <p class="text-sm text-red-800">{{ $capability->rejection_reason }}</p>
                </div>
            @endif

            <p class="text-xs text-gray-400">If you believe this is an error, please contact <a href="mailto:support@edushopify.test" class="text-indigo-600 hover:underline">support@edushopify.test</a></p>

        @elseif($capability?->status === 'revision_required')
            {{-- Revision Requested --}}
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-amber-700 mb-2">Revision Required</h1>
            <p class="text-gray-500 text-sm mb-4">Please update your application based on the feedback below.</p>

            @if($capability->revision_reason)
                <div class="mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-left">
                    <p class="text-xs font-semibold text-amber-700 mb-1">Reviewer notes:</p>
                    <p class="text-sm text-amber-800">{{ $capability->revision_reason }}</p>
                </div>
            @endif

            <a href="{{ route('supplier.application') }}"
                class="block w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition text-sm">
                Update My Application
            </a>

        @else
            {{-- Pending --}}
            <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-teal-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-2">Application Under Review</h1>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
                Your supplier application has been received and is being reviewed by our team.
                We typically respond within <strong>1–3 business days</strong>.
            </p>

            <div class="grid grid-cols-3 gap-3 mb-6">
                @foreach([
                    ['icon' => '📋', 'label' => 'Application', 'status' => 'Submitted'],
                    ['icon' => '🔍', 'label' => 'Review',      'status' => 'In Progress'],
                    ['icon' => '✅', 'label' => 'Approval',    'status' => 'Pending'],
                ] as $s)
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                        <div class="text-2xl mb-1">{{ $s['icon'] }}</div>
                        <p class="text-xs font-semibold text-gray-700">{{ $s['label'] }}</p>
                        <p class="text-[11px] text-gray-400">{{ $s['status'] }}</p>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-400">You'll receive an email at <strong>{{ auth()->user()?->email }}</strong> once your application is reviewed.</p>
        @endif

        <div class="mt-6 pt-5 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition">Sign out</button>
            </form>
        </div>
    </div>

</x-layouts.auth>
