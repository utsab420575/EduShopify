@extends('backend.layouts.buyer')

@php($profile = $supplierAccount->supplierProfile)

@section('title', $profile?->display_name)
@section('breadcrumb', 'Marketplace / Suppliers / ' . $profile?->display_name)

@section('body')

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        @if($profile?->banner)
            <div class="h-32 bg-gray-100"><img src="{{ asset('storage/'.$profile->banner) }}" class="w-full h-full object-cover" alt=""></div>
        @endif
        <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5' }}" class="w-16 h-16 rounded-xl object-contain bg-white border border-gray-100" alt="">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold text-gray-900">{{ $profile?->display_name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                    @if($profile?->rating)
                        <span class="text-amber-500"><i class="fa-solid fa-star"></i> {{ number_format($profile->rating, 1) }} ({{ $profile->reviews_count }} reviews)</span>
                    @endif
                    <span>{{ collect([$profile?->city?->name, $profile?->country?->name])->filter()->implode(', ') }}</span>
                </div>
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($supplierAccount->supplierTypes as $t)
                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $t->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('buyer.suppliers.save', $supplierAccount) }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-heart {{ $isSaved ? 'text-red-500' : 'text-gray-300' }}"></i> {{ $isSaved ? 'Saved' : 'Save' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('buyer.suppliers.message', $supplierAccount) }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Message</button>
                </form>
                <a href="{{ route('buyer.rfqs.create', ['supplier' => $supplierAccount->id]) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send RFQ</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if($profile?->description)
                <x-backend.form-card title="About">
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $profile->description }}</p>
                </x-backend.form-card>
            @endif

            <x-backend.form-card title="Listings">
                @if($listings->isEmpty())
                    <p class="text-sm text-gray-400">No published listings yet.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($listings as $listing)
                            <a href="{{ $listing->isProduct() ? route('buyer.marketplace.products.show', $listing) : route('buyer.marketplace.services.show', $listing) }}" class="border border-gray-200 rounded-lg p-3 hover:border-gray-300">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $listing->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($listing->listing_type) }}</p>
                                @if($listing->base_price)
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ number_format($listing->base_price, 2) }} {{ $listing->currency_code }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-backend.form-card>

            <x-backend.form-card title="Reviews">
                @if($reviews->isEmpty())
                    <p class="text-sm text-gray-400">No reviews yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star text-xs {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                @if($review->title)<p class="text-sm font-semibold text-gray-800 mt-1">{{ $review->title }}</p>@endif
                                <p class="text-sm text-gray-600 mt-1">{{ $review->comment }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $review->createdBy?->name }} &middot; {{ $review->published_at?->format('d M Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-backend.form-card>
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Company Information">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="text-gray-900 font-medium text-right">{{ $profile?->company_type ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Founded</dt><dd class="text-gray-900 font-medium">{{ $profile?->founded_year ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Website</dt><dd class="text-gray-900 font-medium truncate max-w-[140px]">{{ $profile?->website ?? '—' }}</dd></div>
                </dl>
            </x-backend.form-card>
        </div>
    </div>

@endsection
