@extends('backend.layouts.admin')

@php($profile = $account->supplierProfile)

@section('title', $profile?->display_name ?? $account->display_name)
@section('breadcrumb', 'Users & Accounts / Suppliers / ' . ($profile?->display_name ?? $account->display_name))

@section('body')

    <div x-data="{ tab: 'profile' }">

        <x-backend.page-header :title="$profile?->display_name ?? $account->display_name" :subtitle="$account->account_number">
            <x-slot:actions>
                @if($capability)
                    <x-backend.status-badge :status="$capability->status" />
                @endif
                <a href="{{ route('admin.accounts.show', $account) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">View Account</a>
                @if($capability)
                    <a href="{{ route('admin.capabilities.show', $capability) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Review Application</a>
                @endif
            </x-slot:actions>
        </x-backend.page-header>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
            <x-backend.stat-card label="Listings" :value="$listingCount" icon="fa-box" />
            <x-backend.stat-card label="Published" :value="$publishedListingCount" tone="success" icon="fa-circle-check" />
            <x-backend.stat-card label="Quotations" :value="$quotationCount" icon="fa-file-invoice" />
            <x-backend.stat-card label="Awards" :value="$awardCount" icon="fa-trophy" />
            <x-backend.stat-card label="Reviews" :value="$reviewCount" icon="fa-star" />
        </div>

        <div class="flex items-center gap-1 border-b border-gray-200 mb-6 overflow-x-auto">
            @foreach(['profile' => 'Profile', 'documents' => 'Documents ('.$documents->count().')', 'subscription' => 'Subscription'] as $key => $label)
                <button @click="tab = '{{ $key }}'" class="px-4 py-2.5 text-sm whitespace-nowrap"
                        :style="tab === '{{ $key }}' ? 'color:var(--theme-primary);border-color:var(--theme-primary)' : ''"
                        :class="tab === '{{ $key }}' ? 'border-b-2 font-semibold' : 'text-gray-500'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div x-show="tab === 'profile'">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <x-backend.form-card title="Company Profile">
                        @if($profile)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div><dt class="text-gray-500">Legal Name</dt><dd class="font-medium text-gray-900">{{ $profile->legal_name ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Contact Person</dt><dd class="font-medium text-gray-900">{{ $profile->contact_person ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Contact Email</dt><dd class="font-medium text-gray-900">{{ $profile->contact_email ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Contact Phone</dt><dd class="font-medium text-gray-900">{{ $profile->contact_phone ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Country</dt><dd class="font-medium text-gray-900">{{ $profile->country?->name ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Rating</dt><dd class="font-medium text-gray-900">{{ number_format($profile->rating, 1) }} ({{ $profile->reviews_count }})</dd></div>
                                <div class="sm:col-span-2"><dt class="text-gray-500">Supplier Types</dt><dd class="font-medium text-gray-900">{{ $account->supplierTypes->pluck('name')->implode(', ') ?: '—' }}</dd></div>
                            </dl>
                        @else
                            <p class="text-sm text-gray-400">Profile not completed.</p>
                        @endif
                    </x-backend.form-card>

                    @if($capability && $capability->rejection_reason)
                        <x-backend.form-card title="Rejection Reason"><p class="text-sm text-gray-600">{{ $capability->rejection_reason }}</p></x-backend.form-card>
                    @endif
                </div>
                <div class="space-y-6">
                    <x-backend.form-card title="Application History">
                        @if(!$capability || $capability->applicationHistory->isEmpty())
                            <p class="text-sm text-gray-400">No history recorded.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach($capability->applicationHistory as $entry)
                                    <li class="text-sm">
                                        <div class="flex justify-between"><span class="text-gray-700">Attempt #{{ $entry->attempt_no }}</span><x-backend.status-badge :status="$entry->status" /></div>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $entry->created_at->format('d M Y') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-backend.form-card>
                </div>
            </div>
        </div>

        <div x-show="tab === 'documents'" x-cloak>
            <x-backend.table>
                @if($documents->isEmpty())
                    <x-slot:empty><x-backend.empty-state icon="fa-file-lines" title="No documents uploaded" /></x-slot:empty>
                @else
                    <x-slot:head>
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Uploaded</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expires</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </x-slot:head>
                    @foreach($documents as $document)
                        <tr>
                            <td class="px-5 py-3.5 text-sm text-gray-900">{{ $document->documentType?->name ?? $document->custom_name }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $document->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ $document->expires_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-3.5"><x-backend.status-badge :status="$document->status" /></td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                                    @can('platform.supplier_documents.verify')
                                        @if($document->status === 'pending')
                                            <form method="POST" action="{{ route('admin.suppliers.documents.verify', [$account, $document]) }}">
                                                @csrf
                                                <button type="submit" title="Verify" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                            </form>
                                            <button type="button" title="Reject" @click="$dispatch('open-modal-reject-doc-{{ $document->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </x-backend.table>

            @can('platform.supplier_documents.verify')
                @foreach($documents as $document)
                    @if($document->status === 'pending')
                        <x-backend.modal :id="'reject-doc-'.$document->id" title="Reject Document">
                            <form method="POST" action="{{ route('admin.suppliers.documents.reject', [$account, $document]) }}">
                                @csrf
                                <x-backend.textarea name="reason" label="Rejection reason" required />
                                <div class="flex justify-end gap-2 mt-4">
                                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                                </div>
                            </form>
                        </x-backend.modal>
                    @endif
                @endforeach
            @endcan
        </div>

        <div x-show="tab === 'subscription'" x-cloak>
            <x-backend.form-card title="Current Subscription">
                @if($account->activeSubscription)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Plan</dt><dd class="font-medium text-gray-900">{{ $account->activeSubscription->plan?->name }}</dd></div>
                        <div><dt class="text-gray-500">Status</dt><dd><x-backend.status-badge :status="$account->activeSubscription->status" /></dd></div>
                        <div><dt class="text-gray-500">Current Period Ends</dt><dd class="font-medium text-gray-900">{{ $account->activeSubscription->current_period_end?->format('d M Y') ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Auto Renew</dt><dd class="font-medium text-gray-900">{{ $account->activeSubscription->auto_renew ? 'Yes' : 'No' }}</dd></div>
                    </dl>
                    <a href="{{ route('admin.billing.subscriptions.show', $account->activeSubscription) }}" class="inline-block text-sm font-medium mt-4" style="color:var(--theme-primary)">View Subscription &rarr;</a>
                @else
                    <p class="text-sm text-gray-400">No active subscription.</p>
                @endif
            </x-backend.form-card>
        </div>

    </div>

@endsection
