@extends('backend.layouts.buyer')

@section('title', $role->display_name ?? $role->name)
@section('breadcrumb', 'Organization / Roles & Permissions / ' . ($role->display_name ?? $role->name))

@section('body')

    <x-backend.page-header :title="$role->display_name ?? $role->name" :subtitle="$role->description">
        <x-slot:actions>
            <a href="{{ route('buyer.roles.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back to Roles</a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-backend.form-card title="Permissions">
            @if($role->permissions->isEmpty())
                <p class="text-sm text-gray-400">No permissions assigned to this role.</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($role->permissions as $permission)
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">{{ $permission->display_name ?? $permission->name }}</span>
                    @endforeach
                </div>
            @endif
        </x-backend.form-card>

        <x-backend.form-card title="Assign to Members">
            <div class="space-y-3">
                @forelse($members as $member)
                    @php($assigned = $assignedUserIds->contains($member->user_id))
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-800">{{ $member->user?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $member->user?->email }}</p>
                        </div>
                        @if($assigned)
                            <form method="POST" action="{{ route('buyer.roles.unassign', $role) }}">
                                @csrf
                                <input type="hidden" name="member_id" value="{{ $member->id }}">
                                <button type="submit" class="text-xs font-medium text-red-600">Remove</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('buyer.roles.assign', $role) }}">
                                @csrf
                                <input type="hidden" name="member_id" value="{{ $member->id }}">
                                <button type="submit" class="text-xs font-medium" style="color:var(--theme-primary)">Assign</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No active members.</p>
                @endforelse
            </div>
        </x-backend.form-card>
    </div>

@endsection
