@extends('backend.layouts.admin')

@section('title', $account->display_name)
@section('breadcrumb', 'Users & Accounts / Accounts / ' . $account->display_name)

@section('body')

    <x-backend.page-header :title="$account->display_name" :subtitle="$account->account_number">
        <x-slot:actions>
            <x-backend.status-badge :status="$account->status" />
            @can('platform.accounts.approve')
                @if($account->status === 'pending_approval')
                    <form method="POST" action="{{ route('admin.accounts.approve', $account) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Approve</button>
                    </form>
                @endif
            @endcan
            @can('platform.accounts.suspend')
                @if($account->status === 'suspended')
                    <form method="POST" action="{{ route('admin.accounts.reactivate', $account) }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reactivate</button>
                    </form>
                @elseif($account->status === 'active')
                    <button @click="$dispatch('open-modal-suspend-account')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Suspend</button>
                @endif
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <x-backend.form-card title="Capabilities">
                @if($account->capabilities->isEmpty())
                    <p class="text-sm text-gray-400">No capability applications.</p>
                @else
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($account->capabilities as $capability)
                            <li class="flex items-center justify-between px-5 py-3">
                                <span class="text-sm text-gray-700">{{ $capability->capabilityType?->name }}</span>
                                <x-backend.status-badge :status="$capability->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>

            <x-backend.form-card title="Members">
                <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                    @foreach($account->members as $member)
                        <li class="flex items-center justify-between px-5 py-3">
                            <div>
                                <p class="text-sm text-gray-800">{{ $member->user?->name }}</p>
                                <p class="text-xs text-gray-400">{{ $member->is_primary_owner ? 'Primary Owner' : ucfirst($member->member_type) }}</p>
                            </div>
                            <x-backend.status-badge :status="$member->status" />
                        </li>
                    @endforeach
                </ul>
            </x-backend.form-card>

            @if($account->locations->isNotEmpty())
                <x-backend.form-card title="Locations">
                    <ul class="space-y-2">
                        @foreach($account->locations as $location)
                            <li class="text-sm text-gray-700">{{ $location->address_line_1 }} — <span class="text-gray-400">{{ ucwords(str_replace('_',' ',$location->location_type)) }}</span></li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

            @if($account->status === 'suspended' && $account->suspension_reason)
                <x-backend.form-card title="Suspension Reason">
                    <p class="text-sm text-gray-600">{{ $account->suspension_reason }}</p>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900">{{ ucfirst($account->account_type) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Primary Owner</dt><dd class="font-medium text-gray-900">{{ $account->primaryOwner?->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $account->created_at->format('d M Y') }}</dd></div>
                </dl>
            </x-backend.form-card>

            @if($account->buyerProfile)
                <x-backend.form-card title="Buyer Profile">
                    <p class="text-sm text-gray-700">{{ $account->buyerProfile->display_name }}</p>
                    <a href="{{ route('admin.buyers.show', $account) }}" class="inline-block text-sm font-medium mt-2" style="color:var(--theme-primary)">View Buyer Detail &rarr;</a>
                </x-backend.form-card>
            @endif

            @if($account->supplierProfile)
                <x-backend.form-card title="Supplier Profile">
                    <p class="text-sm text-gray-700">{{ $account->supplierProfile->display_name }}</p>
                    <a href="{{ route('admin.suppliers.show', $account) }}" class="inline-block text-sm font-medium mt-2" style="color:var(--theme-primary)">View Supplier Detail &rarr;</a>
                </x-backend.form-card>
            @endif
        </div>
    </div>

    @can('platform.accounts.suspend')
        <x-backend.modal id="suspend-account" title="Suspend Account">
            <form method="POST" action="{{ route('admin.accounts.suspend', $account) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Suspend</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

@endsection
