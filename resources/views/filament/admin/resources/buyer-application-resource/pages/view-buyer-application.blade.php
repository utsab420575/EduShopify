<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Status Badge --}}
        <div class="flex items-center gap-3">
            @php
                $statusColor = match($cap->status) {
                    'pending'           => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fde68a'],
                    'active'            => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
                    'revision_required' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#bfdbfe'],
                    'rejected'          => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fecaca'],
                    default             => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#e5e7eb'],
                };
            @endphp
            <span style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};border:1px solid {{ $statusColor['border'] }};padding:0.375rem 0.875rem;border-radius:9999px;font-size:0.8rem;font-weight:600;">
                {{ ucwords(str_replace('_', ' ', $cap->status)) }}
            </span>
            <span class="text-sm text-gray-500">Attempt {{ $cap->application_attempts }} of 3</span>
            @if($cap->applied_at)
                <span class="text-sm text-gray-400">· Submitted {{ $cap->applied_at->format('d M Y H:i') }}</span>
            @endif
        </div>

        {{-- Account Info --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700">Account Information</h3>
            </div>
            <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Account Number</p>
                    <p class="font-medium text-gray-800">{{ $account->account_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Account Type</p>
                    <p class="font-medium text-gray-800 capitalize">{{ $account->account_type }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Display Name</p>
                    <p class="font-medium text-gray-800">{{ $account->display_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Owner Name</p>
                    <p class="font-medium text-gray-800">{{ $account->primaryOwner?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Owner Email</p>
                    <p class="font-medium text-gray-800">{{ $account->primaryOwner?->email ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Buyer Profile --}}
        @if($account->buyerProfile)
        @php $profile = $account->buyerProfile; @endphp
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700">Buyer Profile</h3>
            </div>
            <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Buyer Type</p>
                    <p class="font-medium text-gray-800">{{ $profile->buyerType?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Organisation Name</p>
                    <p class="font-medium text-gray-800">{{ $profile->organization_name ?? $profile->display_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Contact Person</p>
                    <p class="font-medium text-gray-800">{{ $profile->contact_person ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Position</p>
                    <p class="font-medium text-gray-800">{{ $profile->position ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Email</p>
                    <p class="font-medium text-gray-800">{{ $profile->email ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Phone</p>
                    <p class="font-medium text-gray-800">{{ $profile->phone ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Country</p>
                    <p class="font-medium text-gray-800">{{ $profile->country?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">City</p>
                    <p class="font-medium text-gray-800">{{ $profile->city?->name ?? '—' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 mb-0.5">Address</p>
                    <p class="font-medium text-gray-800">{{ $profile->address ?: '—' }}</p>
                </div>
                @if($profile->website)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Website</p>
                    <a href="{{ $profile->website }}" target="_blank" class="text-indigo-600 hover:underline font-medium">{{ $profile->website }}</a>
                </div>
                @endif
                @if($profile->tax_id)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Tax / VAT ID</p>
                    <p class="font-medium text-gray-800">{{ $profile->tax_id }}</p>
                </div>
                @endif
                @if($profile->procurement_info)
                <div class="col-span-3">
                    <p class="text-xs text-gray-400 mb-0.5">Procurement Info</p>
                    <p class="font-medium text-gray-800 whitespace-pre-line">{{ $profile->procurement_info }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Review reason (if any) --}}
        @if($cap->revision_reason || $cap->rejection_reason)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-amber-800 mb-2">Review Notes</h3>
            <p class="text-sm text-amber-700 whitespace-pre-line">{{ $cap->revision_reason ?? $cap->rejection_reason }}</p>
        </div>
        @endif

        {{-- Application History --}}
        @if($history->count())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700">Application History</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($history as $attempt)
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-700">Attempt #{{ $attempt->attempt_no }}</span>
                        <span class="text-xs text-gray-400">{{ $attempt->created_at?->format('d M Y H:i') }}</span>
                    </div>
                    @if($attempt->review_comment)
                    <p class="text-xs text-gray-500"><span class="font-medium">Review comment:</span> {{ $attempt->review_comment }}</p>
                    @endif
                    @if($attempt->submitted_snapshot)
                    <details class="mt-2">
                        <summary class="text-xs text-indigo-600 cursor-pointer">View submitted snapshot</summary>
                        <pre class="mt-2 p-3 bg-gray-50 rounded-lg text-xs text-gray-600 overflow-auto max-h-60">{{ json_encode($attempt->submitted_snapshot, JSON_PRETTY_PRINT) }}</pre>
                    </details>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
