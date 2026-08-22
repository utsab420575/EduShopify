@extends('backend.layouts.buyer')

@section('title', $listing->name)
@section('breadcrumb', 'Marketplace / Products / ' . $listing->name)

@section('body')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center mb-4">
                    <i class="fa-solid fa-box text-5xl text-gray-300"></i>
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ $listing->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $listing->mainCategory?->name }} @if($listing->brand) &middot; {{ $listing->brand->name }} @endif</p>
                @if($listing->short_description)
                    <p class="text-sm text-gray-600 mt-3">{{ $listing->short_description }}</p>
                @endif
            </div>

            @if($listing->description)
                <x-backend.form-card title="Description">
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $listing->description }}</p>
                </x-backend.form-card>
            @endif

            @if($listing->attributeValues->isNotEmpty())
                <x-backend.form-card title="Specifications">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @foreach($listing->attributeValues as $value)
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">{{ $value->attribute?->name }}</dt>
                                <dd class="text-gray-900 font-medium">{{ $value->resolvedValue() }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-backend.form-card>
            @endif

            @if($listing->tierPrices->isNotEmpty())
                <x-backend.form-card title="Tier Pricing">
                    <table class="w-full text-sm">
                        <thead><tr class="text-xs text-gray-500 uppercase"><th class="text-left py-2">Quantity</th><th class="text-right py-2">Unit Price</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($listing->tierPrices as $tier)
                                <tr>
                                    <td class="py-2">{{ rtrim(rtrim((string) $tier->min_quantity,'0'),'.') }}@if($tier->max_quantity) - {{ rtrim(rtrim((string) $tier->max_quantity,'0'),'.') }}@else+@endif</td>
                                    <td class="py-2 text-right font-medium">{{ number_format($tier->unit_price, 2) }} {{ $tier->currency_code }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card>
                @if($listing->base_price)
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($listing->base_price, 2) }} {{ $listing->currency_code }}</p>
                @endif
                @if($listing->min_order_quantity)
                    <p class="text-xs text-gray-500 mt-1">MOQ: {{ rtrim(rtrim((string) $listing->min_order_quantity, '0'), '.') }} {{ $listing->unit?->symbol }}</p>
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
