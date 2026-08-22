@extends('backend.layouts.supplier')

@section('title', 'Role: ' . $role->name)
@section('breadcrumb', 'Access Control / Roles / ' . $role->name)

@section('body')

    <x-backend.page-header title="Role: {{ $role->name }}" subtitle="Manage members assigned to this role and inspect permissions." />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-6 space-y-6">
            <x-backend.form-card title="Assign to Members">
                <div class="space-y-3">
                    @forelse($members as $m)
                        @php($assigned = $assignedUserIds->contains($m->user_id))
                        <div class="flex items-center justify-between text-xs py-1">
                            <span class="font-bold text-gray-900">{{ $m->user?->name }} ({{ $m->user?->email }})</span>
                            @if($assigned)
                                <form method="POST" action="{{ route('supplier.roles.unassign', $role) }}">
                                    @csrf
                                    <input type="hidden" name="member_id" value="{{ $m->id }}">
                                    <button type="submit" class="text-red-500 hover:underline text-[11px]">Unassign</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('supplier.roles.assign', $role) }}">
                                    @csrf
                                    <input type="hidden" name="member_id" value="{{ $m->id }}">
                                    <button type="submit" class="text-[11px] font-medium" style="color:var(--theme-primary)">Assign</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No active members.</p>
                    @endforelse
                </div>
            </x-backend.form-card>
        </div>

        <div class="xl:col-span-6 space-y-6">
            <x-backend.form-card title="Included Permissions">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    @foreach($role->permissions as $perm)
                        <div class="p-2 bg-gray-50 rounded border border-gray-100 font-mono text-[11px] text-gray-700">
                            {{ $perm->name }}
                        </div>
                    @endforeach
                </div>
            </x-backend.form-card>
        </div>

    </div>

@endsection
