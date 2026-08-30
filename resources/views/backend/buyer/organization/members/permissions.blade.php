@extends('backend.layouts.buyer')

@section('title', 'Permissions for ' . $member->user->name)
@section('breadcrumb', 'Organization / Members / ' . $member->user->name . ' / Permissions')

@section('body')

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Custom Permissions: {{ $member->user->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Manage role-inherited permissions and direct permission overrides for this team member.</p>
            </div>
            <a href="{{ route('buyer.members.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 px-3.5 py-2 bg-white border border-gray-300 rounded-xl">
                &larr; Back to Members
            </a>
        </div>

        <!-- Role Summary Card -->
        <div class="bg-indigo-50/50 rounded-2xl border border-indigo-100 p-5 flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-700">Assigned Roles</div>
                <div class="flex items-center gap-2">
                    @forelse($member->user->roles as $r)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-800">
                            {{ $r->display_name ?: $r->name }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-500">No roles assigned.</span>
                    @endforelse
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500">Inherited from Roles</div>
                <div class="text-sm font-bold text-gray-900">{{ count($inheritedPermissions) }} permissions</div>
            </div>
        </div>

        <form method="POST" action="{{ route('buyer.members.permissions.update', $member) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Direct Permission Overrides</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Grant additional direct permissions specifically to this employee.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    @foreach($permissionGroups as $groupName => $permissions)
                        <div class="bg-gray-50 rounded-xl border border-gray-200/80 p-5">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                {{ $groupName ?: 'General' }}
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($permissions as $perm)
                                    @php($isInherited = in_array($perm->name, $inheritedPermissions))
                                    @php($isDirect = in_array($perm->name, $directPermissions))
                                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border {{ $isInherited ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-gray-200 hover:border-indigo-300' }} cursor-pointer transition-colors">
                                        <input type="checkbox" name="direct_permissions[]" value="{{ $perm->name }}" {{ $isDirect || $isInherited ? 'checked' : '' }} {{ $isInherited ? 'disabled' : '' }} class="w-4 h-4 mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <div class="text-xs">
                                            <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                                {{ $perm->display_name ?: $perm->name }}
                                                @if($isInherited)
                                                    <span class="text-[9px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">Inherited</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-mono">{{ $perm->name }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('buyer.members.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-semibold rounded-xl">
                    Save Direct Permissions
                </button>
            </div>
        </form>
    </div>

@endsection
