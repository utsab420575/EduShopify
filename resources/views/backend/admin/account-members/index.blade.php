@extends('backend.layouts.admin')

@section('title', 'Account Members')
@section('breadcrumb', 'Users & Accounts / Account Members')

@section('body')

    <x-backend.page-header title="Account Members" subtitle="Platform-wide membership oversight — read-only. Normal team management belongs to the account owner." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search member or account..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($members->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-users" title="No members found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Member</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </x-slot:head>
            @foreach($members as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $member->user?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $member->user?->email }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($member->account)
                            <a href="{{ route('admin.accounts.show', $member->account) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $member->account->display_name }}</a>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $member->is_primary_owner ? 'Primary Owner' : ucfirst($member->member_type) }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$member->status" /></td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$members" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
