@extends('backend.layouts.admin')

@php($profile = $account->buyerProfile)

@section('title', $profile?->display_name ?? $account->display_name)
@section('breadcrumb', 'Users & Accounts / Buyers / ' . ($profile?->display_name ?? $account->display_name))

@section('body')

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

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <x-backend.stat-card label="Total RFQs" :value="$rfqCount" icon="fa-file-signature" />
        <x-backend.stat-card label="Open RFQs" :value="$openRfqCount" tone="info" icon="fa-envelope-open-text" />
        <x-backend.stat-card label="Awards" :value="$awardCount" icon="fa-trophy" />
        <x-backend.stat-card label="Purchase Orders" :value="$poCount" icon="fa-clipboard-list" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Buyer Profile">
                @if($profile)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Contact Person</dt><dd class="font-medium text-gray-900">{{ $profile->contact_person ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $profile->email ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $profile->phone ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Country</dt><dd class="font-medium text-gray-900">{{ $profile->country?->name ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $profile->address ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Buyer Types</dt><dd class="font-medium text-gray-900">{{ $account->buyerTypes->pluck('name')->implode(', ') ?: '—' }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm text-gray-400">Profile not completed.</p>
                @endif
            </x-backend.form-card>

            @if($capability && $capability->rejection_reason)
                <x-backend.form-card title="Rejection Reason"><p class="text-sm text-gray-600">{{ $capability->rejection_reason }}</p></x-backend.form-card>
            @endif
            @if($capability && $capability->revision_reason)
                <x-backend.form-card title="Revision Reason"><p class="text-sm text-gray-600">{{ $capability->revision_reason }}</p></x-backend.form-card>
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

@endsection
