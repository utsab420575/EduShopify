@extends('backend.layouts.admin')

@section('title', 'Account Members')
@section('breadcrumb', 'Users & Accounts / Account Members')

@section('body')

    <x-backend.page-header title="Account Members" subtitle="Platform-wide membership oversight across all organization and team accounts." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search member or account..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['invited' => 'Invited', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'removed' => 'Removed'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($members as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->user?->name ?? 'User') }}&background=eef2ff&color=4f46e5" class="w-8 h-8 rounded-full" alt="">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $member->user?->name }}</p>
                                <p class="text-xs text-gray-400">{{ $member->user?->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($member->account)
                            <a href="{{ route('admin.accounts.show', $member->account) }}" class="hover:underline font-medium text-gray-900" style="color:var(--theme-primary)">{{ $member->account->display_name }}</a>
                            <span class="block text-xs text-gray-400 font-mono">{{ $member->account->account_number }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($member->is_primary_owner)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">Primary Owner</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst($member->member_type) }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$member->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-member-{{ $member->id }}')"
                                    title="Quick View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Edit Member Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-member-{{ $member->id }}')"
                                    title="Edit Membership"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Remove Member Action --}}
                            @if(! $member->is_primary_owner && $member->status !== 'removed')
                                <form method="POST" action="{{ route('admin.account-members.destroy', $member) }}" onsubmit="return confirmSwal(this, 'Remove Member?', 'Remove {{ addslashes($member->user?->name) }} from {{ addslashes($member->account?->display_name) }}?', 'warning', 'Yes, Remove')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Remove Member" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
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
            <x-backend.pagination :paginator="$members" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Account Members --}}
    @foreach($members as $member)
        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-member-'.$member->id" :title="'Member — '.($member->user?->name ?? 'User')" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $member->user?->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $member->user?->email }}</p>
                    </div>
                    @if($member->account)
                        <a href="{{ route('admin.accounts.show', $member->account) }}"
                           class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                           title="Open full account view in current tab">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                        </a>
                    @endif
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $member->account?->display_name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Membership Type</dt><dd class="font-medium text-gray-900">{{ $member->is_primary_owner ? 'Primary Owner' : ucfirst($member->member_type) }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$member->status" /></dd></div>
                    <div><dt class="text-xs text-gray-500">Joined Date</dt><dd class="font-medium text-gray-900">{{ $member->joined_at?->format('d M Y, h:i A') ?? ($member->created_at?->format('d M Y') ?? '—') }}</dd></div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- Edit Member Modal --}}
        <x-backend.modal :id="'edit-member-'.$member->id" :title="'Edit Membership — '.($member->user?->name ?? 'User')" width="max-w-md">
            <form method="POST" action="{{ route('admin.account-members.update', $member) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-backend.select name="member_type" label="Membership Type" required>
                    <option value="member" @selected($member->member_type === 'member')>Member</option>
                    <option value="owner" @selected($member->member_type === 'owner')>Owner</option>
                </x-backend.select>
                <x-backend.select name="status" label="Membership Status" required>
                    @foreach(['invited' => 'Invited', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'removed' => 'Removed'] as $value => $label)
                        <option value="{{ $value }}" @selected($member->status === $value)>{{ $label }}</option>
                    @endforeach
                </x-backend.select>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection
