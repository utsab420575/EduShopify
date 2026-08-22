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
            @endif
        </x-slot:actions>
    </x-backend.page-header>

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
                        <span class="font-semibold text-gray-800">{{ $rfq->delivery_deadline?->format('d M Y') ?? 'Flexible' }}</span>
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
                <div class="overflow-x-auto -mx-5 -mb-5">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-y border-gray-100 text-xs text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Item / Description</th>
                                <th class="px-3 py-3">Quantity</th>
                                <th class="px-3 py-3">Target Specs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rfq->items as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-gray-900">{{ $item->item_name }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-gray-500">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs font-semibold text-gray-800">
                                        {{ (float)$item->quantity }} {{ $item->unit?->name ?? $item->custom_unit ?? 'Units' }}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-500">
                                        {{ $item->target_specs ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                                @if($q->answers && $q->answers->isNotEmpty())
                                    <div class="mt-2 pl-4 border-l-2 border-indigo-500 text-gray-700">
                                        @foreach($q->answers as $ans)
                                            <p>{{ $ans->answer }}</p>
                                        @endforeach
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
                @if($existingQuotation)
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

@endsection
