@extends('backend.layouts.admin')

@section('title', 'Users')
@section('breadcrumb', 'Users & Accounts / Users')

@section('body')

    <x-backend.page-header title="Users" subtitle="Every login on the platform." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email or phone..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending_verification' => 'Pending Verification', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deleted' => 'Deleted'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($users->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-users" title="No users found" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($users as $u)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background=eef2ff&color=4f46e5" class="w-9 h-9 rounded-full" alt="">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $u->name }}</p>
                                <p class="text-xs text-gray-400">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $u->accountMember?->account?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-xs text-gray-600">
                        @if($u->roles->isNotEmpty())
                            @foreach($u->roles as $role)
                                <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 mr-1">{{ $role->name }}</span>
                            @endforeach
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $u->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$u->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-user-{{ $u->id }}')"
                                    title="Quick View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Edit User Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-user-{{ $u->id }}')"
                                    title="Edit User"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Suspend / Reactivate Action --}}
                            @if($u->id !== auth()->id())
                                @if($u->status !== 'suspended')
                                    <button type="button"
                                            @click="$dispatch('open-modal-suspend-user-{{ $u->id }}')"
                                            title="Suspend User"
                                            class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.users.reactivate', $u) }}" onsubmit="return confirmSwal(this, 'Reactivate User?', 'Re-enable platform login for {{ addslashes($u->name) }}?', 'question', 'Yes, Reactivate')">
                                        @csrf
                                        <button type="submit" title="Reactivate User" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            <i class="fa-solid fa-rotate-right"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete User Action --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirmSwal(this, 'Delete User?', 'Are you sure you want to delete user {{ addslashes($u->name) }}?', 'warning', 'Yes, Delete')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete User" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$users" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Users --}}
    @foreach($users as $u)
        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-user-'.$u->id" :title="'User Details — '.$u->name" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background=eef2ff&color=4f46e5" class="w-12 h-12 rounded-full" alt="">
                        <div>
                            <h4 class="text-base font-bold text-gray-900">{{ $u->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $u->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $u) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open full page view in current tab">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $u->phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$u->status" /></dd></div>
                    <div><dt class="text-xs text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $u->accountMember?->account?->display_name ?? 'No account associated' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Joined</dt><dd class="font-medium text-gray-900">{{ $u->created_at->format('d M Y, h:i A') }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500 mb-1">Assigned Roles</dt>
                        <dd>
                            @if($u->roles->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($u->roles as $role)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">No platform roles assigned</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- Edit User Modal --}}
        <x-backend.modal :id="'edit-user-'.$u->id" :title="'Edit User — '.$u->name" width="max-w-lg">
            <form method="POST" action="{{ route('admin.users.update', $u) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-backend.input name="name" label="Full Name" :value="$u->name" required />
                <x-backend.input name="email" type="email" label="Email Address" :value="$u->email" required />
                <x-backend.input name="phone" label="Phone Number" :value="$u->phone" />
                <x-backend.select name="status" label="Account Status" required>
                    @foreach(['pending_verification' => 'Pending Verification', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deleted' => 'Deleted'] as $value => $label)
                        <option value="{{ $value }}" @selected($u->status === $value)>{{ $label }}</option>
                    @endforeach
                </x-backend.select>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>

        {{-- Suspend User Modal --}}
        @if($u->id !== auth()->id() && $u->status !== 'suspended')
            <x-backend.modal :id="'suspend-user-'.$u->id" :title="'Suspend User — '.$u->name">
                <form method="POST" action="{{ route('admin.users.suspend', $u) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Suspension Reason" placeholder="State why this user account is being suspended..." required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700">Confirm Suspension</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
