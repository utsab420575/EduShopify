@extends('backend.layouts.admin')

@section('title', 'Accounts')
@section('breadcrumb', 'Users & Accounts / Accounts')

@section('body')

    <x-backend.page-header title="Accounts" subtitle="Every marketplace account on the platform." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name or account number..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="type" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    <option value="individual" @selected($type === 'individual')>Individual</option>
                    <option value="organization" @selected($type === 'organization')>Organization</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deletion_pending' => 'Deletion Pending', 'deleted' => 'Deleted'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($accounts->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-building" title="No accounts found" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capabilities</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($accounts as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $account->display_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $account->account_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $account->primaryOwner?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($account->account_type) }}</td>
                    <td class="px-5 py-3.5 text-xs text-gray-600">
                        @if($account->capabilities->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($account->capabilities as $cap)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700">{{ $cap->capabilityType?->name ?? $cap->capabilityType?->code }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$account->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-account-{{ $account->id }}')"
                                    title="Quick View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Edit Account Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-account-{{ $account->id }}')"
                                    title="Edit Account"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Approve Action if pending --}}
                            @if(in_array($account->status, ['draft', 'pending_approval']))
                                <form method="POST" action="{{ route('admin.accounts.approve', $account) }}" onsubmit="return confirmSwal(this, 'Approve Account?', 'Approve and activate account {{ addslashes($account->display_name) }}?', 'question', 'Yes, Approve')">
                                    @csrf
                                    <button type="submit" title="Approve Account" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- Suspend / Reactivate Action --}}
                            @if($account->status === 'active')
                                <button type="button"
                                        @click="$dispatch('open-modal-suspend-account-{{ $account->id }}')"
                                        title="Suspend Account"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            @elseif($account->status === 'suspended')
                                <form method="POST" action="{{ route('admin.accounts.reactivate', $account) }}" onsubmit="return confirmSwal(this, 'Reactivate Account?', 'Re-enable platform operations for {{ addslashes($account->display_name) }}?', 'question', 'Yes, Reactivate')">
                                    @csrf
                                    <button type="submit" title="Reactivate Account" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-rotate-right"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- Delete Account Action --}}
                            @if($account->status !== 'deleted')
                                <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" onsubmit="return confirmSwal(this, 'Delete Account?', 'Are you sure you want to delete account {{ addslashes($account->display_name) }}?', 'warning', 'Yes, Delete')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Account" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
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
            <x-backend.pagination :paginator="$accounts" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Accounts --}}
    @foreach($accounts as $account)
        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-account-'.$account->id" :title="'Account — '.$account->display_name" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $account->display_name }}</h4>
                        <p class="text-xs text-gray-400 font-mono">Account No: {{ $account->account_number }}</p>
                    </div>
                    <a href="{{ route('admin.accounts.show', $account) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open full page view in current tab">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Account Type</dt><dd class="font-medium text-gray-900">{{ ucfirst($account->account_type) }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$account->status" /></dd></div>
                    <div><dt class="text-xs text-gray-500">Primary Owner</dt><dd class="font-medium text-gray-900">{{ $account->primaryOwner?->name ?? '—' }} ({{ $account->primaryOwner?->email ?? '—' }})</dd></div>
                    <div><dt class="text-xs text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $account->created_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500 mb-1">Active Capabilities</dt>
                        <dd>
                            @if($account->capabilities->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($account->capabilities as $cap)
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center gap-1">
                                            <i class="fa-solid fa-award text-[10px]"></i> {{ $cap->capabilityType?->name }} ({{ $cap->status }})
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">No capabilities registered</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- Edit Account Modal --}}
        <x-backend.modal :id="'edit-account-'.$account->id" :title="'Edit Account — '.$account->display_name" width="max-w-lg">
            <form method="POST" action="{{ route('admin.accounts.update', $account) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-backend.input name="display_name" label="Display Name" :value="$account->display_name" required />
                <x-backend.select name="account_type" label="Account Type" required>
                    <option value="individual" @selected($account->account_type === 'individual')>Individual</option>
                    <option value="organization" @selected($account->account_type === 'organization')>Organization</option>
                </x-backend.select>
                <x-backend.select name="status" label="Account Status" required>
                    @foreach(['draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'deletion_pending' => 'Deletion Pending', 'deleted' => 'Deleted'] as $value => $label)
                        <option value="{{ $value }}" @selected($account->status === $value)>{{ $label }}</option>
                    @endforeach
                </x-backend.select>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>

        {{-- Suspend Account Modal --}}
        @if($account->status === 'active')
            <x-backend.modal :id="'suspend-account-'.$account->id" :title="'Suspend Account — '.$account->display_name">
                <form method="POST" action="{{ route('admin.accounts.suspend', $account) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Suspension Reason" placeholder="State why this account is being suspended..." required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700">Confirm Suspension</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
