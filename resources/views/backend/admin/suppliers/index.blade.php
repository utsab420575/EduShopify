@extends('backend.layouts.admin')

@section('title', 'Suppliers')
@section('breadcrumb', 'Users & Accounts / Suppliers')

@section('body')

    <x-backend.page-header title="Suppliers" subtitle="Accounts with a Supplier capability." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search suppliers..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
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
                <x-backend.empty-state icon="fa-store" title="No suppliers found" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Legal Entity</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subscription</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Documents</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capability Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($accounts as $account)
                @php($profile = $account->supplierProfile)
                @php($cap = $account->capabilities->first())
                @php($docs = $account->supplierDocuments)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $profile?->display_name ?? $account->display_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $account->account_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <p class="text-gray-900 font-medium">{{ $profile?->legal_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $profile?->contact_email ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                            {{ $account->activeSubscription?->plan?->name ?? 'Free Tier' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-600">
                        @php($verifiedDocs = $docs->where('status', 'verified')->count())
                        @php($totalDocs = $docs->count())
                        @if($totalDocs > 0)
                            <span class="inline-flex items-center gap-1 {{ $verifiedDocs === $totalDocs ? 'text-emerald-700 font-medium' : 'text-amber-700 font-medium' }}">
                                <i class="fa-solid {{ $verifiedDocs === $totalDocs ? 'fa-circle-check text-emerald-500' : 'fa-clock text-amber-500' }}"></i>
                                {{ $verifiedDocs }}/{{ $totalDocs }} verified
                            </span>
                        @else
                            <span class="text-gray-400">No docs</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$cap?->status ?? 'draft'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-supplier-{{ $account->id }}')"
                                    title="Quick View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Edit Supplier Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-supplier-{{ $account->id }}')"
                                    title="Edit Supplier Details"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Direct Show Link --}}
                            <a href="{{ route('admin.suppliers.show', $account) }}"
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

    {{-- Modals for Suppliers --}}
    @foreach($accounts as $account)
        @php($profile = $account->supplierProfile)
        @php($cap = $account->capabilities->first())
        @php($docs = $account->supplierDocuments)

        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-supplier-'.$account->id" :title="'Supplier Profile — '.($profile?->display_name ?? $account->display_name)" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $profile?->display_name ?? $account->display_name }}</h4>
                        <p class="text-xs text-gray-400 font-mono">Account No: {{ $account->account_number }}</p>
                    </div>
                    <a href="{{ route('admin.suppliers.show', $account) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open full page view in current tab">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Legal Business Name</dt><dd class="font-medium text-gray-900">{{ $profile?->legal_name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Contact Email</dt><dd class="font-medium text-gray-900">{{ $profile?->contact_email ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Contact Phone</dt><dd class="font-medium text-gray-900">{{ $profile?->contact_phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Country</dt><dd class="font-medium text-gray-900">{{ $profile?->country?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Subscription Plan</dt><dd class="font-medium text-gray-900">{{ $account->activeSubscription?->plan?->name ?? 'None' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Capability Status</dt><dd><x-backend.status-badge :status="$cap?->status ?? 'draft'" /></dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $profile?->address ?? '—' }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500 mb-1">Current Documents ({{ $docs->count() }})</dt>
                        <dd>
                            @if($docs->isNotEmpty())
                                <div class="space-y-1.5">
                                    @foreach($docs as $d)
                                        <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-gray-50 border border-gray-100">
                                            <span class="font-medium text-gray-800">{{ $d->documentType?->name ?? $d->custom_name }}</span>
                                            <x-backend.status-badge :status="$d->status" />
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">No documents uploaded</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- Edit Supplier Modal --}}
        <x-backend.modal :id="'edit-supplier-'.$account->id" :title="'Edit Supplier — '.($profile?->display_name ?? $account->display_name)" width="max-w-lg">
            <form method="POST" action="{{ route('admin.suppliers.update', $account) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-backend.input name="display_name" label="Display Name" :value="$profile?->display_name ?? $account->display_name" required />
                <x-backend.input name="legal_name" label="Legal Business Name" :value="$profile?->legal_name" />
                <x-backend.input name="contact_email" type="email" label="Contact Email" :value="$profile?->contact_email" />
                <x-backend.input name="contact_phone" label="Contact Phone" :value="$profile?->contact_phone" />
                <x-backend.textarea name="address" label="Address" :value="$profile?->address" />
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection
