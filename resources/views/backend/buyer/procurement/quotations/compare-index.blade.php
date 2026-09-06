@extends('backend.layouts.buyer')

@section('title', 'Compare Quotations')
@section('breadcrumb', 'Procurement / Compare')

@section('body')

    <x-backend.page-header title="Compare Quotations" subtitle="Comparisons run per RFQ so item-level details line up across suppliers." />

    <div x-data="compareOverviewPage('{{ route('buyer.quotations.compare', ['rfq' => '__RFQ_ID__']) }}')" x-cloak>

        {{-- Active comparisons — picked up from localStorage, client-side only --}}
        <template x-if="activeSets.length > 0">
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Continue a Comparison</h2>
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    <template x-for="set in activeSets" :key="set.rfqId">
                        <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold text-gray-900" x-text="set.count"></span>
                                quotation<span x-show="set.count !== 1">s</span> selected for RFQ #<span x-text="set.rfqId"></span>
                            </p>
                            <div class="flex items-center gap-2 shrink-0">
                                <a :href="compareUrlTemplate.replace('__RFQ_ID__', set.rfqId)" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">
                                    <i class="fa-solid fa-scale-balanced mr-1"></i> View Comparison
                                </a>
                                <button type="button" title="Remove this comparison" @click="if (confirm('Remove all ' + set.count + ' selected quotation(s) for RFQ #' + set.rfqId + ' from comparison?')) clearSet(set.rfqId)"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="activeSets.length === 0">
            <div class="mb-6">
                <x-backend.empty-state icon="fa-scale-balanced" title="No comparison in progress" description="Pick an RFQ below, then check &quot;Add to Compare&quot; on 2–5 of its quotations to build a comparison." />
            </div>
        </template>

    </div>

    {{-- RFQs with enough quotations to compare --}}
    <h2 class="text-sm font-semibold text-gray-900 mb-3">Start a New Comparison</h2>
    <x-backend.table>
        @if($rfqs->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-inbox" title="Nothing to compare yet" description="An RFQ needs at least 2 quotations before they can be compared side by side." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Quotations Received</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($rfqs as $rfq)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $rfq->title }}</p>
                        <p class="text-xs text-gray-400">{{ $rfq->rfq_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 text-right">{{ $rfq->eligible_quotations_count }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Select Quotations
                        </a>
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

    @include('backend.buyer.procurement.quotations.partials._compare-store')

@endsection
