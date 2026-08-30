@extends('backend.layouts.admin')

@section('title', 'User Role Assignment')
@section('breadcrumb', 'Access Control / User Role Assignment')

@section('body')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Role Assignment</h1>
            <p class="text-sm text-gray-500 mt-1">Manage role allocations and permissions across all registered system and company users.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email or phone..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="role" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" @selected($roleId === $r->id)>{{ $r->display_name ?? $r->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($users->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-users" title="No users found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-6 py-4 w-16 text-xs font-semibold uppercase tracking-wider text-gray-500">SI</th>
                    <th class="px-6 py-4 w-20 text-xs font-semibold uppercase tracking-wider text-gray-500">Image</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Email</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Phone</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Action</th>
                </tr>
            </x-slot:head>
            @foreach($users as $index => $u)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3.5 text-xs text-gray-500 font-semibold">
                        {{ $users->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-3.5">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background=6366F1&color=fff" class="w-9 h-9 rounded-full object-cover shadow-sm" alt="">
                    </td>
                    <td class="px-6 py-3.5 text-sm font-bold text-gray-900">
                        {{ $u->name }}
                        @if($u->accountMember?->account)
                            <div class="text-[11px] font-normal text-gray-400">{{ $u->accountMember->account->display_name }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-3.5 text-xs text-gray-600 font-mono">
                        {{ $u->email }}
                    </td>
                    <td class="px-6 py-3.5 text-xs text-gray-600">
                        {{ $u->phone ?: '—' }}
                    </td>
                    <td class="px-6 py-3.5">
                        <div class="flex flex-wrap gap-1">
                            @forelse($u->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $role->display_name ?? $role->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">No role</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-3.5 text-right">
                        <button type="button" onclick="openRoleModal({{ $u->id }}, '{{ addslashes($u->name) }}', {{ json_encode($u->roles->pluck('id')) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </button>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$users" />
        </x-slot:pagination>
    </x-backend.table>

    <!-- Role Assignment Modal -->
    <div id="role-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 shadow-xl border border-gray-100 space-y-5 animate-scaleIn">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-gray-900">Assign Roles: <span id="modal-user-name" class="text-indigo-600"></span></h3>
                <button type="button" onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form id="modal-role-form" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                    <p class="text-xs text-gray-500 mb-2">Check the roles to grant this user:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($roles as $r)
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer text-xs">
                                <input type="checkbox" name="role_ids[]" value="{{ $r->id }}" class="modal-role-cb w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <div>
                                    <div class="font-bold text-gray-800">{{ $r->display_name ?? $r->name }}</div>
                                    <div class="text-[10px] text-gray-400 capitalize">{{ $r->capability_scope }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-4">
                    <button type="button" onclick="closeRoleModal()" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-2 text-xs font-semibold rounded-xl">Save Role</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRoleModal(userId, userName, userRoleIds) {
            document.getElementById('modal-user-name').innerText = userName;
            document.getElementById('modal-role-form').action = `/admin/access-control/user-roles/${userId}`;

            const roleSet = new Set(userRoleIds);
            document.querySelectorAll('.modal-role-cb').forEach(cb => {
                cb.checked = roleSet.has(parseInt(cb.value));
            });

            document.getElementById('role-modal').classList.remove('hidden');
        }

        function closeRoleModal() {
            document.getElementById('role-modal').classList.add('hidden');
        }
    </script>

@endsection
