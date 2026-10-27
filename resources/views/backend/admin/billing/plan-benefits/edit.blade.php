@extends('backend.layouts.admin')

@section('title', 'Plan Benefits — '.$plan->name)
@section('breadcrumb', 'Subscription & Billing / Plan Benefits / '.$plan->name)

@section('body')

    <x-backend.page-header :title="'Plan Benefits — '.$plan->name" :subtitle="ucfirst($plan->billing_type).' · '.$plan->formattedPrice()">
        <x-slot:actions>
            <a href="{{ route('admin.billing.plan-benefits.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"><i class="fa-solid fa-arrow-left mr-1.5"></i>All Plans</a>
        </x-slot:actions>
    </x-backend.page-header>

    @if($benefits->isEmpty())
        <x-backend.form-card>
            <x-backend.empty-state icon="fa-gift" title="No active benefits" description="Create benefits first, then assign them to plans.">
                <x-slot:actions>
                    <a href="{{ route('admin.billing.benefits.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">New Benefit</a>
                </x-slot:actions>
            </x-backend.empty-state>
        </x-backend.form-card>
    @else
        <x-backend.form-card>
            @include('backend.admin.billing.plan-benefits.partials._assign-form', [
                'plan' => $plan,
                'benefits' => $benefits,
                'assigned' => $assigned,
                'cancelUrl' => route('admin.billing.plan-benefits.index'),
            ])
        </x-backend.form-card>
    @endif

@endsection
