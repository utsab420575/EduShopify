@extends('backend.layouts.admin')

@section('title', $benefit->name)
@section('breadcrumb', 'Subscription & Billing / Benefits / '.$benefit->name)

@section('body')

    @php($inUse = $benefit->planBenefits->isNotEmpty() || $overrideCount > 0)

    <x-backend.page-header :title="$benefit->name" :subtitle="$benefit->code">
        <x-slot:actions>
            <x-backend.status-badge :status="$benefit->is_active ? 'active' : 'inactive'" />
            <form method="POST" action="{{ route($benefit->is_active ? 'admin.billing.benefits.deactivate' : 'admin.billing.benefits.activate', $benefit) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    {{ $benefit->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('admin.billing.benefits.edit', $benefit) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-regular fa-pen-to-square mr-1.5"></i>Edit</a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-backend.stat-card label="Value Type" :value="$benefit->typeLabel()" icon="fa-shapes" />
        <x-backend.stat-card label="Default" :value="$benefit->formatValue($benefit->fallbackValue())" icon="fa-rotate-left" />
        <x-backend.stat-card label="Plans" :value="$benefit->planBenefits->count()" icon="fa-tags" />
        <x-backend.stat-card label="Active Overrides" :value="$activeOverrideCount" icon="fa-user-pen" :hint="$overrideCount.' in history'" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-backend.form-card title="Details" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs text-gray-500">Code</dt><dd class="font-mono text-gray-900">{{ $benefit->code }}</dd></div>
                <div><dt class="text-xs text-gray-500">Unit</dt><dd class="text-gray-900">{{ $benefit->unit ?: '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Sort Order</dt><dd class="text-gray-900">{{ $benefit->sort_order }}</dd></div>
                <div><dt class="text-xs text-gray-500">Description</dt><dd class="text-gray-700 whitespace-pre-line">{{ $benefit->description ?: '—' }}</dd></div>
            </dl>
            @unless($inUse)
                <form method="POST" action="{{ route('admin.billing.benefits.destroy', $benefit) }}" class="mt-5 pt-4 border-t border-gray-100"
                      onsubmit="return confirmSwal(this, 'Delete this benefit?', '{{ addslashes($benefit->name) }} will be permanently removed.', 'warning', 'Yes, delete')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700"><i class="fa-regular fa-trash-can mr-1"></i>Delete benefit</button>
                </form>
            @else
                <p class="mt-5 pt-4 border-t border-gray-100 text-xs text-gray-500"><i class="fa-solid fa-lock mr-1"></i>In use — it can be deactivated but not deleted.</p>
            @endunless
        </x-backend.form-card>

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Assigned to Plans</h3>
                <a href="{{ route('admin.billing.plan-benefits.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-800">Plan Benefit Assignment →</a>
            </div>
            @if($benefit->planBenefits->isEmpty())
                <x-backend.empty-state icon="fa-tags" title="Not assigned to any plan" />
            @else
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($benefit->planBenefits->sortBy(fn ($row) => $row->plan?->sort_order) as $row)
                            <tr>
                                <td class="px-5 py-3 text-sm text-gray-900">{{ $row->plan?->name ?? 'Plan #'.$row->subscription_plan_id }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $benefit->formatValue($benefit->normalize($row->value)) }}</td>
                                <td class="px-5 py-3 text-right">
                                    @if($row->plan)
                                        <a href="{{ route('admin.billing.plan-benefits.edit', $row->plan) }}" class="text-xs font-medium text-indigo-600 hover:underline">Edit plan benefits</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection
