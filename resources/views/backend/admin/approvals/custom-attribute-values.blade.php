@extends('backend.layouts.admin')

@section('title', 'Approval Center — Custom Attribute Values')
@section('breadcrumb', 'Platform Governance / Approval Center / Custom Attribute Values')

@section('body')

    <x-backend.page-header title="Approval Center" subtitle="Custom Attribute Values — review supplier-submitted &quot;Other&quot; values." />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        {{-- LEFT: Decided — ignored first, then approved (default order); any explicit sort overrides that grouping --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            <x-backend.table-search
                title="Decided" :count="$decided->total()"
                search-param="decided_search" page-param="decided_page"
                :current-search="$decidedSearch" placeholder="Search attribute or value..."
                :filter-params="['decided_status']" :has-active-filter="$decidedStatus !== ''">
                <x-slot:filters>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                        <select name="decided_status" onchange="this.form.submit()"
                                class="focus-accent w-40 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Statuses</option>
                            <option value="ignored" @selected($decidedStatus === 'ignored')>Ignored</option>
                            <option value="approved" @selected($decidedStatus === 'approved')>Approved</option>
                        </select>
                    </div>
                    @if($decidedStatus !== '')
                        <a href="{{ request()->fullUrlWithQuery(['decided_status' => null, 'decided_page' => null]) }}"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                    @endif
                </x-slot:filters>
            </x-backend.table-search>

            @if($decided->isEmpty())
                <div class="p-10 text-center">
                    <div class="w-10 h-10 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <p class="text-xs text-gray-400">{{ $decidedSearch !== '' ? 'No decisions match "'.$decidedSearch.'".' : 'No decisions recorded yet.' }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-backend.sortable-th column="attribute_name" label="Attribute" sort-param="decided_sort" direction-param="decided_direction" page-param="decided_page" :current-sort="$decidedSort" :current-direction="$decidedDirection" />
                                <x-backend.sortable-th column="custom_value" label="Custom Value" sort-param="decided_sort" direction-param="decided_direction" page-param="decided_page" :current-sort="$decidedSort" :current-direction="$decidedDirection" />
                                <x-backend.sortable-th column="status" label="Status" sort-param="decided_sort" direction-param="decided_direction" page-param="decided_page" :current-sort="$decidedSort" :current-direction="$decidedDirection" />
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Used By</th>
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php($previousStatus = null)
                            @foreach($decided as $review)
                                {{-- Group divider — only in the default (unsorted) grouped view; an explicit sort shows a flat list instead --}}
                                @if($decidedSort === '' && $review->status !== $previousStatus)
                                    <tr class="bg-gray-50/80">
                                        <td colspan="5" class="px-5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                            {{ ucfirst($review->status) }}
                                        </td>
                                    </tr>
                                    @php($previousStatus = $review->status)
                                @endif
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $review->attribute?->name ?? 'Unknown attribute' }}</td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-sm font-semibold text-gray-900">{{ $review->custom_value }}</p>
                                        @if($review->status === 'approved' && $review->resultingAttributeValue)
                                            <p class="text-[11px] text-gray-400">now official as <span class="font-medium text-gray-600">{{ $review->resultingAttributeValue->value }}</span></p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            {{ $review->status === 'ignored' ? 'bg-gray-100 text-gray-600 border border-gray-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                            {{ ucfirst($review->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $review->usage_count }} {{ Str::plural('listing', $review->usage_count) }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if($review->status === 'ignored')
                                            <button type="button" @click="$dispatch('open-modal-approve-decided-{{ $review->id }}')"
                                                    class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">
                                                Promote
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.catalog.custom-attribute-values.ignore') }}"
                                                  onsubmit="return confirmSwal(this, 'Stop Treating as Official?', 'This only changes the review record — the &quot;{{ addslashes($review->resultingAttributeValue?->value ?? $review->custom_value) }}&quot; option and every listing already using it are left exactly as they are.', 'warning', 'Yes, Mark Ignored')"
                                                  class="inline">
                                                @csrf
                                                <input type="hidden" name="attribute_id" value="{{ $review->attribute_id }}">
                                                <input type="hidden" name="custom_value" value="{{ $review->custom_value }}">
                                                <button type="submit" class="px-3 py-1 rounded bg-gray-200 text-gray-700 font-semibold text-xs">Ignore</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                    <x-backend.pagination :paginator="$decided" />
                </div>
            @endif
        </div>

        {{-- RIGHT: Pending — newly submitted, not yet decided --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            {{-- No Filters button here on purpose (design.md §0.3.10): pending items have
                 no status/category dimension, only free-text search, which the toolbar
                 above already covers. --}}
            <x-backend.table-search
                title="Pending Review" :count="$pending->total()"
                search-param="pending_search" page-param="pending_page"
                :current-search="$pendingSearch" placeholder="Search attribute or value..." />

            @if($pending->isEmpty())
                <div class="p-10 text-center">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <p class="text-xs text-gray-400">{{ $pendingSearch !== '' ? 'No pending values match "'.$pendingSearch.'".' : 'No custom values waiting for review.' }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-backend.sortable-th column="attribute_name" label="Attribute" sort-param="pending_sort" direction-param="pending_direction" page-param="pending_page" :current-sort="$pendingSort" :current-direction="$pendingDirection" />
                                <x-backend.sortable-th column="custom_value" label="Custom Value" sort-param="pending_sort" direction-param="pending_direction" page-param="pending_page" :current-sort="$pendingSort" :current-direction="$pendingDirection" />
                                <x-backend.sortable-th column="usage_count" label="Used By" sort-param="pending_sort" direction-param="pending_direction" page-param="pending_page" :current-sort="$pendingSort" :current-direction="$pendingDirection" />
                                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pending as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $item->attribute_name }}</td>
                                    <td class="px-4 py-3.5 text-sm font-semibold text-gray-900">{{ $item->custom_value }}</td>
                                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $item->usage_count }} {{ Str::plural('listing', $item->usage_count) }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="$dispatch('open-modal-approve-pending-{{ $loop->index }}')"
                                                    class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">
                                                Approve
                                            </button>
                                            <form method="POST" action="{{ route('admin.catalog.custom-attribute-values.ignore') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="attribute_id" value="{{ $item->attribute_id }}">
                                                <input type="hidden" name="custom_value" value="{{ $item->custom_value }}">
                                                <button type="submit" class="px-3 py-1 rounded bg-gray-200 text-gray-700 font-semibold text-xs">Ignore</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                    <x-backend.pagination :paginator="$pending" />
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- DUPLICATE-CHECK MODALS — one per approvable row (pending + decided/ignored) --}}
    {{-- ========================================================================= --}}
    @foreach($pending as $item)
        @php($existingForPending = $existingValuesByAttribute[$item->attribute_id] ?? collect())
        <x-backend.modal :id="'approve-pending-'.$loop->index" title="Promote to Official Value?" width="max-w-lg">
            @include('backend.admin.approvals.partials.custom-value-duplicate-check', [
                'attributeName' => $item->attribute_name,
                'attributeId'   => $item->attribute_id,
                'customValue'   => $item->custom_value,
                'usageCount'    => $item->usage_count,
                'existing'      => $existingForPending,
            ])
        </x-backend.modal>
    @endforeach

    @foreach($decided as $review)
        @if($review->status === 'ignored')
            @php($existingForDecided = $existingValuesByAttribute[$review->attribute_id] ?? collect())
            <x-backend.modal :id="'approve-decided-'.$review->id" title="Promote to Official Value?" width="max-w-lg">
                @include('backend.admin.approvals.partials.custom-value-duplicate-check', [
                    'attributeName' => $review->attribute?->name ?? 'Unknown attribute',
                    'attributeId'   => $review->attribute_id,
                    'customValue'   => $review->custom_value,
                    'usageCount'    => $review->usage_count,
                    'existing'      => $existingForDecided,
                ])
            </x-backend.modal>
        @endif
    @endforeach

@endsection
