@extends('backend.layouts.admin')

@section('title', 'Manage Benefits')
@section('breadcrumb', 'Subscription & Billing / Benefits')

@section('body')

    <x-backend.page-header title="Manage Benefits" subtitle="Facilities subscription plans and supplier overrides can grant.">
        <x-slot:actions>
            <a href="{{ route('admin.billing.benefits.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Benefit</a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
        <x-backend.table-search
            title="Benefits" :count="$benefits->total()"
            search-param="search" page-param="page"
            :current-search="$search" placeholder="Search name or code..."
            :filter-params="['type', 'status']" :has-active-filter="$type !== '' || $status !== ''">
            <x-slot:filters>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Value Type</label>
                    <select name="type" onchange="this.form.submit()" class="focus-accent w-40 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        <option value="">All Types</option>
                        @foreach(\App\Models\SubscriptionBenefit::VALUE_TYPES as $key => $label)
                            <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" onchange="this.form.submit()" class="focus-accent w-36 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        <option value="">All Statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                @if($type !== '' || $status !== '')
                    <a href="{{ request()->fullUrlWithQuery(['type' => null, 'status' => null, 'page' => null]) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                @endif
            </x-slot:filters>
        </x-backend.table-search>

        @if($benefits->isEmpty())
            <x-backend.empty-state icon="fa-gift" title="No benefits found"
                :description="$search !== '' || $type !== '' || $status !== '' ? 'Try a different search or filter.' : 'Create the first benefit plans can grant.'" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Benefit</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Used By</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($benefits as $benefit)
                            @php($inUse = $benefit->plan_benefits_count > 0 || $benefit->overrides_count > 0)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('admin.billing.benefits.show', $benefit) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $benefit->name }}</a>
                                    <p class="text-xs font-mono text-gray-400">{{ $benefit->code }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $benefit->typeLabel() }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $benefit->formatValue($benefit->fallbackValue()) }}</td>
                                <td class="px-5 py-3.5 text-xs text-gray-500">
                                    {{ $benefit->plan_benefits_count }} {{ Str::plural('plan', $benefit->plan_benefits_count) }}
                                    · {{ $benefit->overrides_count }} {{ Str::plural('override', $benefit->overrides_count) }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $benefit->sort_order }}</td>
                                <td class="px-5 py-3.5"><x-backend.status-badge :status="$benefit->is_active ? 'active' : 'inactive'" /></td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.billing.benefits.show', $benefit) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                                        <button type="button" title="Edit" @click="$dispatch('edit-benefit', @js($editData[$benefit->id]))"
                                                class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50"><i class="fa-regular fa-pen-to-square"></i></button>
                                        <form method="POST" action="{{ route($benefit->is_active ? 'admin.billing.benefits.deactivate' : 'admin.billing.benefits.activate', $benefit) }}">
                                            @csrf
                                            <button type="submit" title="{{ $benefit->is_active ? 'Deactivate' : 'Activate' }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100">
                                                <i class="fa-solid {{ $benefit->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        @unless($inUse)
                                            <form method="POST" action="{{ route('admin.billing.benefits.destroy', $benefit) }}"
                                                  onsubmit="return confirmSwal(this, 'Delete this benefit?', '{{ addslashes($benefit->name) }} will be permanently removed.', 'warning', 'Yes, delete')">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                <x-backend.pagination :paginator="$benefits" />
            </div>
        @endif
    </div>

    @include('backend.admin.billing.benefits.partials._edit-modal')

@endsection
