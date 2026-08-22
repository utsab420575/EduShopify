@extends('backend.layouts.supplier')

@section('title', 'Exhibitions')
@section('breadcrumb', 'Business Profile / Exhibitions')

@section('body')

    <x-backend.page-header title="Exhibitions" subtitle="Manage your participation in trade exhibitions and educational events." />

    {{-- Currently Participating --}}
    @if($participating->isNotEmpty())
        <h3 class="text-sm font-bold text-gray-800 mb-3">Your Current Exhibitions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @foreach($participating as $exhibition)
                @php $pivot = $exhibition->supplierAccounts->first()?->pivot; @endphp
                <div class="bg-white rounded-xl border border-indigo-200 p-5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">{{ $exhibition->getTranslation('name', app()->getLocale()) }}</h4>
                            @if($exhibition->starts_at)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $exhibition->starts_at->format('d M Y') }}
                                    @if($exhibition->ends_at) — {{ $exhibition->ends_at->format('d M Y') }} @endif
                                </p>
                            @endif
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full px-2 py-0.5">
                            <i class="fa-solid fa-circle-check"></i> Joined
                        </span>
                    </div>
                    @if($pivot?->booth_number)
                        <p class="text-xs text-gray-600"><span class="font-semibold">Booth:</span> {{ $pivot->booth_number }}</p>
                    @endif
                    @if($pivot?->participation_year)
                        <p class="text-xs text-gray-600"><span class="font-semibold">Year:</span> {{ $pivot->participation_year }}</p>
                    @endif
                    <form method="POST" action="{{ route('supplier.company.exhibitions.leave', $exhibition) }}"
                          onsubmit="return confirm('Withdraw from this exhibition?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">
                            <i class="fa-solid fa-right-from-bracket mr-1"></i> Withdraw
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Available Exhibitions --}}
    <h3 class="text-sm font-bold text-gray-800 mb-3">Available Exhibitions</h3>

    @if($available->isEmpty())
        <x-backend.empty-state icon="fa-calendar-star" title="No new exhibitions available" description="Check back later for upcoming educational trade events." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($available as $exhibition)
                <div x-data="{ open: false }" class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">{{ $exhibition->getTranslation('name', app()->getLocale()) }}</h4>
                        @if($exhibition->starts_at)
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $exhibition->starts_at->format('d M Y') }}
                                @if($exhibition->ends_at) — {{ $exhibition->ends_at->format('d M Y') }} @endif
                            </p>
                        @endif
                        @if($exhibition->description)
                            <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ $exhibition->description }}</p>
                        @endif
                        @if($exhibition->website)
                            <a href="{{ $exhibition->website }}" target="_blank" rel="noopener" class="text-xs text-indigo-600 hover:underline mt-1 block">
                                <i class="fa-solid fa-link mr-1"></i>{{ $exhibition->website }}
                            </a>
                        @endif
                    </div>
                    <button @click="open = !open" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg w-full">
                        <i class="fa-solid fa-plus mr-1"></i> Join Exhibition
                    </button>
                    <form x-show="open" method="POST" action="{{ route('supplier.company.exhibitions.join', $exhibition) }}"
                          class="border-t border-gray-100 pt-3 space-y-2" x-cloak>
                        @csrf
                        <x-backend.input name="booth_number" label="Booth Number (optional)" placeholder="e.g. A-12" />
                        <x-backend.input name="participation_year" label="Participation Year" type="number"
                                         :value="date('Y')" min="2000" max="2100" />
                        <button type="submit" class="w-full text-xs font-bold px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                            Confirm Join
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

@endsection
