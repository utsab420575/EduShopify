@extends('backend.layouts.supplier')

@section('title', $rfq->title)
@section('breadcrumb', 'RFQ Opportunities / ' . $rfq->rfq_number)

@section('body')

    <x-backend.page-header title="{{ $rfq->title }}" subtitle="RFQ Number: {{ $rfq->rfq_number }}">
        <x-slot:actions>
            @if($existingQuotation)
                <a href="{{ route('supplier.quotations.show', $existingQuotation) }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-file-invoice"></i> View My Quotation ({{ $existingQuotation->quotation_number }})
                </a>
            @else
                <a href="{{ route('supplier.quotations.create', $rfq) }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i> Submit Quotation
                </a>
                @if($queueRow && $queueRow->status !== 'ignored')
                    <button x-data @click="$dispatch('open-modal-decline-opportunity')" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
                        <i class="fa-solid fa-ban"></i> Decline
                    </button>
                @endif
            @endif
        </x-slot:actions>
    </x-backend.page-header>

    @if($queueRow && $queueRow->status === 'ignored')
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6 text-xs text-gray-600">
            <i class="fa-solid fa-ban mr-1"></i> You declined this opportunity.
            @if($queueRow->decline_reason)
                <span class="text-gray-500">Reason: {{ $queueRow->decline_reason }}</span>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Left / Main RFQ Details --}}
        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="RFQ Information">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Buyer Institution</span>
                        <span class="font-bold text-gray-900">{{ $rfq->buyerAccount?->buyerProfile?->organization_name ?? $rfq->buyerAccount?->display_name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Quotation Deadline</span>
                        <span class="font-bold text-amber-700">{{ $rfq->quotation_deadline->format('d M Y, h:i A') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Delivery By</span>
                        <span class="font-semibold text-gray-800">{{ $rfq->expected_delivery_date?->format('d M Y') ?? 'Flexible' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Currency</span>
                        <span class="font-semibold text-gray-800">{{ $rfq->currency_code ?? 'USD' }}</span>
                    </div>
                </div>

                @if($rfq->description)
                    <div class="text-sm text-gray-700 whitespace-pre-line mb-4">{{ $rfq->description }}</div>
                @endif

                {{-- Terms & Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <div>
                        <span class="text-gray-400 block">Partial Quotations Allowed:</span>
                        <span class="font-semibold {{ $rfq->allow_partial_quotation ? 'text-green-700' : 'text-gray-700' }}">{{ $rfq->allow_partial_quotation ? 'Yes' : 'No' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Alternative Items Allowed:</span>
                        <span class="font-semibold {{ $rfq->allow_alternative_products ? 'text-green-700' : 'text-gray-700' }}">{{ $rfq->allow_alternative_products ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </x-backend.form-card>

            {{-- Items Requested --}}
            <x-backend.form-card title="Requested Items">
                <div class="space-y-3">
                    @foreach($rfq->items as $item)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->item_name }}</p>
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $item->listing_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                            {{ $item->listing_id ? 'Marketplace Product' : 'Custom Requirement' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->category?->name ?? 'No category' }}</p>
                                    @if($item->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <p class="text-xs font-semibold text-gray-800 shrink-0">{{ (float)$item->quantity }} {{ $item->unit?->name ?? $item->custom_unit ?? 'Units' }}</p>
                            </div>

                            @if($item->attributeValues->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-gray-100">
                                    @foreach($item->attributeValues as $value)
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-full bg-gray-50 border border-gray-200 text-gray-700">
                                            <span class="font-medium text-gray-500">{{ $value->attribute?->name }}:</span> {{ $value->formattedValue() }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-backend.form-card>

            {{-- Q&A Section --}}
            <x-backend.form-card title="Questions &amp; Answers">
                <div class="mb-4">
                    <form method="POST" action="{{ route('supplier.opportunities.questions.store', $rfq) }}" class="space-y-2">
                        @csrf
                        <label class="block text-xs font-medium text-gray-700">Ask the buyer a question about this RFQ</label>
                        <div class="flex gap-2">
                            <input type="text" name="question" required placeholder="Type your clarification question..." class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white">
                            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg shrink-0">
                                Ask Question
                            </button>
                        </div>
                    </form>
                </div>

                @if($rfq->questions->isNotEmpty())
                    <div class="space-y-3 pt-3 border-t border-gray-100">
                        @foreach($rfq->questions as $q)
                            <div class="p-3 bg-gray-50 rounded-lg text-xs">
                                <p class="font-bold text-gray-900"><i class="fa-solid fa-circle-question text-indigo-600 mr-1.5"></i>{{ $q->question }}</p>
                                @if($q->answer)
                                    <div class="mt-2 pl-4 border-l-2 border-indigo-500 text-gray-700">
                                        <p>{{ $q->answer }}</p>
                                    </div>
                                @else
                                    <p class="text-gray-400 italic mt-1 pl-4">Awaiting buyer response...</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-backend.form-card>

        </div>

        {{-- Right Column / Action Card --}}
        <div class="xl:col-span-4 space-y-6">

            <x-backend.form-card title="Take Action">
                @if($existingQuotation && $existingQuotation->status === 'draft')
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-center">
                        <i class="fa-solid fa-file-pen text-gray-500 text-2xl mb-2"></i>
                        <h4 class="text-sm font-bold text-gray-900">Draft In Progress</h4>
                        <p class="text-xs text-gray-600 mt-1 mb-3">Continue and submit your quotation before the deadline.</p>
                        <a href="{{ route('supplier.quotations.show', $existingQuotation) }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-block">
                            Continue Draft
                        </a>
                    </div>
                @elseif($existingQuotation)
                    <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-200 text-center">
                        <i class="fa-solid fa-circle-check text-indigo-600 text-2xl mb-2"></i>
                        <h4 class="text-sm font-bold text-gray-900">Quotation Submitted</h4>
                        <p class="text-xs text-gray-600 mt-1 mb-3">You quoted {{ $existingQuotation->currency_code }} {{ number_format($existingQuotation->grand_total, 2) }}</p>
                        <a href="{{ route('supplier.quotations.show', $existingQuotation) }}" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-block">
                            View Quotation
                        </a>
                    </div>
                @else
                    <div class="text-center p-4">
                        <p class="text-xs text-gray-500 mb-4">Review all specifications and submit your best price proposal before the deadline.</p>
                        <a href="{{ route('supplier.quotations.create', $rfq) }}" class="btn-primary text-sm font-bold w-full py-3 rounded-xl flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-solid fa-paper-plane"></i> Submit Quotation
                        </a>
                    </div>
                @endif
            </x-backend.form-card>

        </div>

    </div>

    @if(! $existingQuotation)
        <x-backend.modal id="decline-opportunity" title="Decline this RFQ opportunity?">
            <form method="POST" action="{{ route('supplier.opportunities.decline', $rfq) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason (optional)" hint="Helps us improve future matching — e.g. outside your category, delivery location unsupported, deadline too short." />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Decline Opportunity</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
