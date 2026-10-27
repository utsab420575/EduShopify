@extends('backend.layouts.admin')

@section('title', 'Supplier Benefit Overrides')
@section('breadcrumb', 'Subscription & Billing / Benefit Overrides')

@section('body')

    <x-backend.page-header title="Supplier Benefit Overrides" subtitle="Find a supplier to view its benefits and override them." />

    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
        <x-backend.table-search
            title="Suppliers" :count="$suppliers->total()"
            search-param="search" page-param="page"
            :current-search="$search" placeholder="Name, account no. or ID..."
            :filter-params="['filter']" :has-active-filter="$filter !== ''">
            <x-slot:filters>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Show</label>
                    <select name="filter" onchange="this.form.submit()" class="focus-accent w-48 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        <option value="">All suppliers</option>
                        <option value="with_overrides" @selected($filter === 'with_overrides')>With active overrides</option>
                    </select>
                </div>
            </x-slot:filters>
        </x-backend.table-search>

        @if($suppliers->isEmpty())
            <x-backend.empty-state icon="fa-store" title="No suppliers found" :description="$search !== '' ? 'Try another name, account number or ID.' : null" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-14">SL</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subscription</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Overrides</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($suppliers as $account)
                            @php($avatar = $account->supplierProfile?->profile_photo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5 text-sm text-gray-500">{{ $suppliers->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 text-xs font-bold text-gray-500">
                                            @if($avatar)
                                                <img src="{{ asset('storage/'.$avatar) }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($account->supplierProfile?->display_name ?? $account->display_name ?? 'S', 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $account->supplierProfile?->display_name ?: $account->display_name }}</p>
                                            <x-backend.status-badge :status="$account->status" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-gray-500">
                                    <span class="font-mono">{{ $account->account_number }}</span><br>ID {{ $account->id }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-700">{{ $account->activeSubscription?->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-sm">
                                    @if($account->active_overrides_count > 0)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-violet-50 text-violet-700">{{ $account->active_overrides_count }} active</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.billing.benefit-overrides.show', $account) }}" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-user-pen"></i> Manage Benefits
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                <x-backend.pagination :paginator="$suppliers" />
            </div>
        @endif
    </div>

@endsection
