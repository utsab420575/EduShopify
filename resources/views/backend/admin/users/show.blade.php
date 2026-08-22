@extends('backend.layouts.admin')

@section('title', $targetUser->name)
@section('breadcrumb', 'Users & Accounts / Users / ' . $targetUser->name)

@section('body')

    <x-backend.page-header :title="$targetUser->name" :subtitle="$targetUser->email">
        <x-slot:actions>
            <x-backend.status-badge :status="$targetUser->status" />
            @can('platform.users.suspend')
                @if($targetUser->status === 'suspended')
                    <form method="POST" action="{{ route('admin.users.reactivate', $targetUser) }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reactivate</button>
                    </form>
                @elseif($targetUser->id !== auth()->id())
                    <button @click="$dispatch('open-modal-suspend-user')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Suspend</button>
                @endif
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Account Membership">
                @if($targetUser->accountMember)
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $targetUser->accountMember->account?->display_name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Membership Status</dt><dd><x-backend.status-badge :status="$targetUser->accountMember->status" /></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900">{{ $targetUser->accountMember->is_primary_owner ? 'Primary Owner' : ucfirst($targetUser->accountMember->member_type) }}</dd></div>
                        @if($targetUser->accountMember->account)
                            <div class="flex justify-between"><dt class="text-gray-500">Capabilities</dt><dd class="font-medium text-gray-900">{{ $targetUser->accountMember->account->capabilities->map(fn($c) => $c->capabilityType?->name.' ('.$c->status.')')->implode(', ') ?: '—' }}</dd></div>
                        @endif
                    </dl>
                    @if($targetUser->accountMember->account && ! $targetUser->accountMember->account->is_system_account)
                        <a href="{{ route('admin.accounts.show', $targetUser->accountMember->account) }}" class="inline-block text-sm font-medium mt-4" style="color:var(--theme-primary)">View Account &rarr;</a>
                    @endif
                @else
                    <p class="text-sm text-gray-400">No account membership.</p>
                @endif
            </x-backend.form-card>

            @if($targetUser->roles->isNotEmpty())
                <x-backend.form-card title="Platform Roles">
                    <div class="flex flex-wrap gap-2">
                        @foreach($targetUser->roles as $role)
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">{{ $role->display_name ?? $role->name }}</span>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $targetUser->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Email Verified</dt><dd class="font-medium text-gray-900">{{ $targetUser->email_verified_at?->format('d M Y') ?? 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Last Login</dt><dd class="font-medium text-gray-900">{{ $targetUser->last_login_at?->diffForHumans() ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Joined</dt><dd class="font-medium text-gray-900">{{ $targetUser->created_at->format('d M Y') }}</dd></div>
                </dl>
            </x-backend.form-card>

            @if($targetUser->socialAccounts->isNotEmpty())
                <x-backend.form-card title="Connected Accounts">
                    <ul class="space-y-2">
                        @foreach($targetUser->socialAccounts as $social)
                            <li class="text-sm text-gray-700 flex items-center gap-2"><i class="fa-brands fa-{{ strtolower($social->provider) }}"></i> {{ ucfirst($social->provider) }}</li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif
        </div>
    </div>

    @can('platform.users.suspend')
        <x-backend.modal id="suspend-user" title="Suspend User">
            <form method="POST" action="{{ route('admin.users.suspend', $targetUser) }}">
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
