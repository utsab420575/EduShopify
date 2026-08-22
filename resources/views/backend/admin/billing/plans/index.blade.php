@extends('backend.layouts.admin')

@section('title', 'Subscription Plans')
@section('breadcrumb', 'Subscription & Billing / Plans')

@section('body')

    <x-backend.page-header title="Subscription Plans" subtitle="Plans suppliers may subscribe to.">
        <x-slot:actions>
            <a href="{{ route('admin.billing.plans.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Plan</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($plans->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-tags" title="No plans found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subscribers</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($plans as $plan)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $plan->name }} @if($plan->is_featured)<i class="fa-solid fa-star text-amber-400 text-xs ml-1"></i>@endif</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($plan->billing_type) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $plan->formattedPrice() }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $plan->subscriptions_count }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$plan->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($plan->is_active)
                                <form method="POST" action="{{ route('admin.billing.plans.deactivate', $plan) }}">
                                    @csrf
                                    <button type="submit" title="Deactivate" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-toggle-on"></i></button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.billing.plans.activate', $plan) }}">
                                    @csrf
                                    <button type="submit" title="Activate" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-toggle-off"></i></button>
                                </form>
                            @endif
                            <a href="{{ route('admin.billing.plans.edit', $plan) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            @if($plan->subscriptions_count === 0)
                                <form method="POST" action="{{ route('admin.billing.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
