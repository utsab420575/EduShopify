@extends('backend.layouts.admin')

@section('title', 'Subscriptions')
@section('breadcrumb', 'Subscription & Billing / Subscriptions')

@section('body')

    <x-backend.page-header title="Subscriptions" subtitle="Supplier subscription lifecycle." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'trialing' => 'Trialing', 'active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="plan" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected($planId === $plan->id)>{{ $plan->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($subscriptions->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-receipt" title="No subscriptions found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Period Ends</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Auto Renew</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($subscriptions as $subscription)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $subscription->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $subscription->plan?->name ?? $subscription->plan_name_snapshot }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $subscription->current_period_end?->format('d M Y') ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $subscription->auto_renew ? 'Yes' : 'No' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$subscription->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('admin.billing.subscriptions.show', $subscription) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$subscriptions" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
