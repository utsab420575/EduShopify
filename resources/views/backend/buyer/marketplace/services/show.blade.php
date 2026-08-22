@extends('backend.layouts.buyer')

@section('title', $listing->name)
@section('breadcrumb', 'Marketplace / Services / ' . $listing->name)

@section('body')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h1 class="text-xl font-bold text-gray-900">{{ $listing->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $listing->mainCategory?->name }}</p>
                @if($listing->short_description)
                    <p class="text-sm text-gray-600 mt-3">{{ $listing->short_description }}</p>
                @endif
            </div>

            @if($listing->description)
                <x-backend.form-card title="Description">
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $listing->description }}</p>
                </x-backend.form-card>
            @endif

            @if($listing->serviceDetail)
                <x-backend.form-card title="Service Details">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Mode</dt><dd class="text-gray-900 font-medium">{{ ucfirst($listing->serviceDetail->service_mode ?? '—') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Duration</dt><dd class="text-gray-900 font-medium">{{ $listing->serviceDetail->duration_value ? rtrim(rtrim((string) $listing->serviceDetail->duration_value,'0'),'.') . ' ' . $listing->serviceDetail->duration_unit : '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Lead Time</dt><dd class="text-gray-900 font-medium">{{ $listing->serviceDetail->lead_time_days ? $listing->serviceDetail->lead_time_days . ' days' : '—' }}</dd></div>
                    </dl>
                    @if($listing->serviceDetail->service_terms)
                        <p class="text-sm text-gray-600 mt-4">{{ $listing->serviceDetail->service_terms }}</p>
                    @endif
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card>
                @if($listing->base_price)
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($listing->base_price, 2) }} {{ $listing->currency_code }}</p>
                @endif
                <div class="flex flex-col gap-2 mt-4">
                    <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg text-center">Request Quotation</a>
                    <form method="POST" action="{{ route('buyer.suppliers.message', $listing->supplierAccount) }}">
                        @csrf
                        <button type="submit" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Message Supplier</button>
                    </form>
                    <form method="POST" action="{{ route('buyer.saved-items.toggle') }}">
                        @csrf
                        <input type="hidden" name="type" value="listing">
                        <input type="hidden" name="id" value="{{ $listing->id }}">
                        <button type="submit" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-bookmark {{ $isSaved ? 'text-red-500' : 'text-gray-300' }}"></i> {{ $isSaved ? 'Saved' : 'Save' }}
                        </button>
                    </form>
                </div>
            </x-backend.form-card>

            <x-backend.form-card title="Supplier">
                <a href="{{ route('buyer.suppliers.show', $listing->supplierAccount) }}" class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($listing->supplierAccount?->supplierProfile?->display_name ?? 'S') }}&background=eef2ff&color=4f46e5" class="w-10 h-10 rounded-lg" alt="">
                    <span class="text-sm font-medium text-gray-900">{{ $listing->supplierAccount?->supplierProfile?->display_name }}</span>
                </a>
            </x-backend.form-card>
        </div>
    </div>

@endsection
