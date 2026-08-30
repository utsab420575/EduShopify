@extends('backend.layouts.buyer')

@section('title', 'Members')
@section('breadcrumb', 'Organization / Members')

@section('body')

    <x-backend.page-header title="Members" subtitle="People who belong to your organization account.">
        <x-slot:actions>
            <a href="{{ route('buyer.invitations.index') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i> Invite Member
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($members->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-users" title="No members yet" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Member</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($members as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $member->user?->name }}</p>
                        <p class="text-xs text-gray-400">{{ $member->user?->email }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $member->user?->roles->pluck('display_name')->filter()->implode(', ') ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $member->is_primary_owner ? 'Primary Owner' : ucfirst($member->member_type) }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$member->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        @unless($member->is_primary_owner)
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('buyer.members.permissions.edit', $member) }}" title="Direct Permissions" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50"><i class="fa-solid fa-key"></i></a>
                                @if($member->status === 'active')
                                    <form method="POST" action="{{ route('buyer.members.suspend', $member) }}">
                                        @csrf
                                        <button type="submit" title="Suspend" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50"><i class="fa-solid fa-ban"></i></button>
                                    </form>
                                @elseif($member->status === 'suspended')
                                    <form method="POST" action="{{ route('buyer.members.activate', $member) }}">
                                        @csrf
                                        <button type="submit" title="Activate" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('buyer.members.destroy', $member) }}" onsubmit="return confirm('Remove this member?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Remove" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        @endunless
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
