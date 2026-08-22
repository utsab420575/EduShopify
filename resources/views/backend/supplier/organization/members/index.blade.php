@extends('backend.layouts.supplier')

@section('title', 'Team Members')
@section('breadcrumb', 'Organization / Members')

@section('body')

    <x-backend.page-header title="Team Members" subtitle="Manage employee access, invitations, and active team seats.">
        <x-slot:actions>
            <a href="{{ route('supplier.invitations.index') }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i> Invite Team Member
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3.5 font-semibold">User</th>
                    <th class="px-3 py-3.5 font-semibold">Role</th>
                    <th class="px-3 py-3.5 font-semibold">Status</th>
                    <th class="px-3 py-3.5 font-semibold">Joined</th>
                    <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @foreach($members as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3.5 flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($m->user->name) }}&background=0D9488&color=fff" class="w-8 h-8 rounded-full" alt="">
                            <div>
                                <p class="font-bold text-gray-900">{{ $m->user->name }}</p>
                                <p class="text-gray-400 text-[11px]">{{ $m->user->email }}</p>
                            </div>
                        </td>
                        <td class="px-3 py-3.5">
                            @if($m->is_primary_owner)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Primary Owner
                                </span>
                            @else
                                <span class="text-gray-700 font-medium">{{ $m->user->roles->pluck('name')->join(', ') ?: 'Member' }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3.5">
                            <x-backend.status-badge :status="$m->status" />
                        </td>
                        <td class="px-3 py-3.5 text-gray-500">
                            {{ $m->created_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if(!$m->is_primary_owner && $m->user_id !== $user->id)
                                <div class="flex items-center justify-end gap-2">
                                    @if($m->status === 'active')
                                        <form method="POST" action="{{ route('supplier.members.suspend', $m) }}">
                                            @csrf
                                            <button type="submit" class="text-amber-600 hover:underline text-[11px]">Suspend</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('supplier.members.activate', $m) }}">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:underline text-[11px]">Activate</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('supplier.members.destroy', $m) }}" onsubmit="return confirm('Remove this member?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline text-[11px]">Remove</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
