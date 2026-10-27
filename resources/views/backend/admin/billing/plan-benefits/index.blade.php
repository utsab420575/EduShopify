@extends('backend.layouts.admin')

@section('title', 'Plan Benefit Assignment')
@section('breadcrumb', 'Subscription & Billing / Plan Benefits')

@section('body')

    <x-backend.page-header title="Plan Benefit Assignment" subtitle="Assign benefits to each subscription plan and set their values.">
        <x-slot:actions>
            <a href="{{ route('admin.billing.benefits.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"><i class="fa-solid fa-gift mr-1.5"></i>Manage Benefits</a>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
        <x-backend.table-search
            title="Plans" :count="$plans->total()"
            search-param="search" page-param="page"
            :current-search="$search" placeholder="Search plan name..." />

        @if($plans->isEmpty())
            <x-backend.empty-state icon="fa-tags" title="{{ $search !== '' ? 'No plans match your search' : 'No subscription plans yet' }}" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-14">SL</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Benefits Assigned</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Subscribers</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($plans as $plan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5 text-sm text-gray-500">{{ $plans->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $plan->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($plan->billing_type) }} · {{ $plan->formattedPrice() }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">
                                    {{ $plan->plan_benefits_count }} <span class="text-gray-400">/ {{ $benefitCount }} active</span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $plan->subscriptions_count }}</td>
                                <td class="px-5 py-3.5"><x-backend.status-badge :status="$plan->is_active ? 'active' : 'inactive'" /></td>
                                <td class="px-5 py-3.5 text-right">
                                    <button type="button" @click="$dispatch('open-modal-plan-benefits-{{ $plan->id }}')"
                                            class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-sliders"></i> Manage
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                <x-backend.pagination :paginator="$plans" />
            </div>
        @endif
    </div>

    {{-- One assignment modal per plan on this page --}}
    @foreach($plans as $plan)
        @php
            $assigned = $plan->planBenefits->keyBy('subscription_benefit_id');
            $planBenefits = $benefits->filter(fn ($b) => $b->is_active || $assigned->has($b->id))->values();
        @endphp
        <x-backend.modal :id="'plan-benefits-'.$plan->id" :title="'Benefits — '.$plan->name" width="max-w-4xl">
            @if($planBenefits->isEmpty())
                <x-backend.empty-state icon="fa-gift" title="No active benefits" description="Create benefits first, then assign them to plans.">
                    <x-slot:actions>
                        <a href="{{ route('admin.billing.benefits.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">New Benefit</a>
                    </x-slot:actions>
                </x-backend.empty-state>
            @else
                @include('backend.admin.billing.plan-benefits.partials._assign-form', [
                    'plan' => $plan,
                    'benefits' => $planBenefits,
                    'assigned' => $assigned,
                    'redirect' => request()->fullUrl(),
                    'cancelModal' => 'plan-benefits-'.$plan->id,
                ])
            @endif
        </x-backend.modal>
    @endforeach

    {{-- Reopen the plan whose save failed validation --}}
    @if($errors->any() && str_starts_with((string) old('_form'), 'plan-'))
        <script>
            document.addEventListener('alpine:initialized', () => {
                window.dispatchEvent(new CustomEvent('open-modal-plan-benefits-' + @js(substr((string) old('_form'), 5))));
            });
        </script>
    @endif

@endsection
