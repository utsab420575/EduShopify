@extends('backend.layouts.admin')

@section('title', 'Buyers')
@section('breadcrumb', 'Users & Accounts / Buyers')

@section('body')

    <x-backend.page-header title="Buyers" subtitle="Accounts with a Buyer capability." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search buyers..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['draft' => 'Draft', 'pending' => 'Pending', 'active' => 'Active', 'revision_required' => 'Revision Required', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($accounts->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-cart-shopping" title="No buyers found" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Buyer</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact Person</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capability Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($accounts as $account)
                @php($profile = $account->buyerProfile)
                @php($cap = $account->capabilities->first())
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $profile?->display_name ?? $account->display_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $account->account_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <p class="font-medium text-gray-800">{{ $profile?->contact_person ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $profile?->email ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($account->account_type) }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$cap?->status ?? 'draft'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-buyer-{{ $account->id }}')"
                                    title="Quick View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Edit Buyer Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-buyer-{{ $account->id }}')"
                                    title="Edit Buyer Details"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Direct Show Link --}}
                            <a href="{{ route('admin.buyers.show', $account) }}"
                               title="Full Profile"
                               class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$accounts" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Buyers --}}
    @foreach($accounts as $account)
        @php($profile = $account->buyerProfile)
        @php($cap = $account->capabilities->first())

        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-buyer-'.$account->id" :title="'Buyer Profile — '.($profile?->display_name ?? $account->display_name)" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $profile?->display_name ?? $account->display_name }}</h4>
                        <p class="text-xs text-gray-400 font-mono">Account No: {{ $account->account_number }}</p>
                    </div>
                    <a href="{{ route('admin.buyers.show', $account) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open full page view in current tab">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Contact Person</dt><dd class="font-medium text-gray-900">{{ $profile?->contact_person ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $profile?->email ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $profile?->phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Country</dt><dd class="font-medium text-gray-900">{{ $profile?->country?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Organization Name</dt><dd class="font-medium text-gray-900">{{ $profile?->organization_name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Capability Status</dt><dd><x-backend.status-badge :status="$cap?->status ?? 'draft'" /></dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $profile?->address ?? '—' }}</dd></div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- Edit Buyer Modal --}}
        <x-backend.modal :id="'edit-buyer-'.$account->id" :title="'Edit Buyer — '.($profile?->display_name ?? $account->display_name)" width="max-w-lg">
            <form method="POST" action="{{ route('admin.buyers.update', $account) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-backend.input name="display_name" label="Display Name" :value="$profile?->display_name ?? $account->display_name" required />
                <x-backend.input name="organization_name" label="Organization Name" :value="$profile?->organization_name" />
                <x-backend.input name="contact_person" label="Contact Person" :value="$profile?->contact_person" />
                <x-backend.input name="email" type="email" label="Contact Email" :value="$profile?->email" />
                <x-backend.input name="phone" label="Contact Phone" :value="$profile?->phone" />
                <x-backend.textarea name="address" label="Address" :value="$profile?->address" />
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection
