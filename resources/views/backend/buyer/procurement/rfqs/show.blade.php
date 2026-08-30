@extends('backend.layouts.buyer')

@section('title', $rfq->title)
@section('breadcrumb', 'Procurement / RFQs / ' . $rfq->rfq_number)

@section('body')

    <x-backend.page-header :title="$rfq->title" :subtitle="$rfq->rfq_number">
        <x-slot:actions>
            <x-backend.status-badge :status="$rfq->status" />

            @can('update', $rfq)
                <a href="{{ route('buyer.rfqs.edit', $rfq) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Edit</a>
            @endcan
            @can('extendDeadline', $rfq)
                <button @click="$dispatch('open-modal-extend-deadline')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Extend Deadline</button>
            @endcan
            @can('publish', $rfq)
                <form method="POST" action="{{ route('buyer.rfqs.publish', $rfq) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Publish</button>
                </form>
            @endcan
            @can('cancel', $rfq)
                <button @click="$dispatch('open-modal-cancel-rfq')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Cancel RFQ</button>
            @endcan
            @can('compare', $rfq)
                <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">View Responses</a>
                <a href="{{ route('buyer.quotations.compare', $rfq) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Compare Quotations</a>
            @endcan
        </x-slot:actions>
    </x-backend.page-header>

    <div x-data="{ tab: '{{ request('_tab', 'overview') }}' }">

        <div class="flex items-center gap-1 border-b border-gray-200 mb-6 overflow-x-auto">
            @foreach(['overview' => 'Overview', 'items' => 'Items ('.$rfq->items->count().')', 'suppliers' => 'Suppliers', 'questions' => 'Q&A ('.$rfq->questions->count().')', 'history' => 'Change History'] as $key => $label)
                <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'border-b-2 font-semibold' : 'text-gray-500'"
                        style="border-color: var(--theme-primary);" class="px-4 py-2.5 text-sm whitespace-nowrap"
                        :style="tab === '{{ $key }}' ? 'color:var(--theme-primary);border-color:var(--theme-primary)' : ''">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div x-show="tab === 'overview'">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <x-backend.form-card title="Description">
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $rfq->description ?: 'No description provided.' }}</p>
                    </x-backend.form-card>

                    <x-backend.form-card title="Delivery">
                        <p class="text-sm text-gray-600">
                            {{ collect([$rfq->deliveryCity?->name, $rfq->deliveryState?->name, $rfq->deliveryCountry?->name])->filter()->implode(', ') ?: 'Not specified' }}
                        </p>
                        @if($rfq->delivery_address)
                            <p class="text-sm text-gray-500 mt-1">{{ $rfq->delivery_address }}</p>
                        @endif
                    </x-backend.form-card>

                    @if($rfq->status === 'cancelled')
                        <x-backend.form-card title="Cancellation">
                            <p class="text-sm text-gray-600">{{ $rfq->cancellation_reason }}</p>
                            <p class="text-xs text-gray-400 mt-1">Cancelled {{ $rfq->cancelled_at?->format('d M Y, h:i A') }}</p>
                        </x-backend.form-card>
                    @endif
                </div>

                <div class="space-y-6">
                    @php($vt = $rfq->getRelationValue('visibilityType'))
                    <x-backend.form-card title="Supplier Targeting">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Visibility</dt>
                                <dd class="text-gray-900 font-medium">{{ $vt?->name ?? '—' }}</dd>
                            </div>
                            @if($vt?->code === 'open_matching' && $rfq->targetFilters->first())
                                @php($tf = $rfq->targetFilters->first())
                                <div class="flex justify-between"><dt class="text-gray-500">Category Filter</dt><dd class="text-gray-900 font-medium">{{ $tf->category?->name ?? 'Any category' }}</dd></div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Location Match</dt>
                                    <dd class="text-gray-900 font-medium">
                                        @if($tf->location_match_level === 'none' || !$tf->location_match_level)
                                            Anywhere
                                        @else
                                            {{ ucfirst($tf->location_match_level) }} — {{ collect([$tf->city?->name, $tf->state?->name, $tf->country?->name])->filter()->implode(', ') ?: 'Not set' }}
                                        @endif
                                    </dd>
                                </div>
                            @elseif(in_array($vt?->code, ['direct', 'invited']))
                                <div class="flex justify-between"><dt class="text-gray-500">Suppliers Invited</dt><dd class="text-gray-900 font-medium">{{ $rfq->invitedSupplierAccounts->count() }}</dd></div>
                            @endif
                        </dl>
                    </x-backend.form-card>

                    <x-backend.form-card title="Rules">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Partial Quotations</dt><dd class="text-gray-900 font-medium">{{ $rfq->allow_partial_quotation ? 'Allowed' : 'Not allowed' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Alternative Products</dt><dd class="text-gray-900 font-medium">{{ $rfq->allow_alternative_products ? 'Allowed' : 'Not allowed' }}</dd></div>
                        </dl>
                    </x-backend.form-card>

                    <x-backend.form-card title="Timeline">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Budget</dt><dd class="text-gray-900 font-medium">{{ $rfq->budget_min || $rfq->budget_max ? number_format((float) $rfq->budget_min, 2) . ' - ' . number_format((float) $rfq->budget_max, 2) . ' ' . $rfq->currency_code : '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Quotation Deadline</dt><dd class="text-gray-900 font-medium">{{ $rfq->quotation_deadline?->format('d M Y, h:i A') }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Q&amp;A Deadline</dt><dd class="text-gray-900 font-medium">{{ $rfq->qna_deadline?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Expected Delivery</dt><dd class="text-gray-900 font-medium">{{ $rfq->expected_delivery_date?->format('d M Y') ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Published</dt><dd class="text-gray-900 font-medium">{{ $rfq->published_at?->format('d M Y') ?? 'Not published' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Quotations Received</dt><dd class="text-gray-900 font-medium">{{ $rfq->quotations_count }}</dd></div>
                        </dl>
                    </x-backend.form-card>

                    @if($rfq->latestAward)
                        <x-backend.form-card title="Award">
                            <p class="text-sm text-gray-700">{{ $rfq->latestAward->supplierAccount?->supplierProfile?->display_name }}</p>
                            <x-backend.status-badge :status="$rfq->latestAward->status" class="mt-2" />
                            <a href="{{ route('buyer.awards.show', $rfq->latestAward) }}" class="block text-sm mt-3" style="color:var(--theme-primary)">View award &rarr;</a>
                        </x-backend.form-card>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="tab === 'items'" x-cloak class="space-y-4">
            @foreach($rfq->items as $item)
                <x-backend.form-card>
                    <div class="flex items-start justify-between gap-3 mb-1">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ $item->item_name }}</p>
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $item->listing_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ $item->listing_id ? 'Marketplace Product' : 'Custom Requirement' }}
                                </span>
                            </div>
                            @if($item->description)<p class="text-xs text-gray-500 mt-1">{{ $item->description }}</p>@endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm text-gray-900 font-medium">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit?->symbol ?? $item->custom_unit }}</p>
                            <p class="text-xs text-gray-400">{{ $item->estimated_unit_price ? 'Est. '.number_format($item->estimated_unit_price, 2).' / unit' : 'No estimate' }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mb-3">{{ $item->category?->name ?? 'No category selected' }}</p>

                    @if($item->attributeValues->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 pt-3 border-t border-gray-100">
                            @foreach($item->attributeValues as $value)
                                <span class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-full bg-gray-50 border border-gray-200 text-gray-700">
                                    <span class="font-medium text-gray-500">{{ $value->attribute?->name }}:</span> {{ $value->formattedValue() }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </x-backend.form-card>
            @endforeach
        </div>

        <div x-show="tab === 'suppliers'" x-cloak>
            <x-backend.form-card :title="$rfq->getRelationValue('visibilityType')?->name ?? ($rfq->isOpenMarketplace() ? 'Open Marketplace RFQ' : 'Selected Suppliers')">
                @if($rfq->isOpenMarketplace())
                    <p class="text-sm text-gray-500">{{ $rfq->getRelationValue('visibilityType')?->description ?: 'This RFQ is visible to all eligible matching suppliers on the marketplace.' }}</p>
                @elseif($rfq->invitedSupplierAccounts->isEmpty())
                    <p class="text-sm text-gray-400">No suppliers invited yet.</p>
                @else
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($rfq->invitedSupplierAccounts as $supplier)
                            <li class="flex items-center justify-between px-5 py-3">
                                <a href="{{ route('buyer.suppliers.show', $supplier) }}" class="text-sm text-gray-700 hover:text-gray-900">{{ $supplier->supplierProfile?->display_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>
        </div>

        <div x-show="tab === 'questions'" x-cloak class="space-y-4">
            @forelse($rfq->questions as $question)
                <x-backend.form-card>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400">Asked {{ $question->created_at->diffForHumans() }}</p>
                            <p class="text-sm text-gray-800 mt-1">{{ $question->question }}</p>
                        </div>
                        <x-backend.status-badge :status="$question->status" />
                    </div>

                    @if($question->answer)
                        <div class="mt-3 pl-4 border-l-2" style="border-color:var(--theme-primary-soft)">
                            <p class="text-sm text-gray-700">{{ $question->answer }}</p>
                            <p class="text-xs text-gray-400 mt-1">Answered {{ $question->answered_at?->diffForHumans() }}</p>
                        </div>
                    @elseif($rfq->status === 'open')
                        <form method="POST" action="{{ route('buyer.rfqs.questions.answer', [$rfq, $question]) }}" class="mt-3 flex items-start gap-2">
                            @csrf
                            <textarea name="answer" rows="2" required placeholder="Write your answer..." class="focus-accent flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                            <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg shrink-0">Answer</button>
                        </form>
                    @endif
                </x-backend.form-card>
            @empty
                <x-backend.empty-state icon="fa-circle-question" title="No questions yet" description="Supplier questions about this RFQ will appear here." />
            @endforelse
        </div>

        <div x-show="tab === 'history'" x-cloak class="space-y-6">

            <x-backend.form-card title="Version History">
                @if($rfq->changeLogs->isEmpty())
                    <p class="text-sm text-gray-400">No changes recorded since this RFQ was published.</p>
                @else
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($rfq->changeLogs as $log)
                            <li class="px-5 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        v{{ $log->from_version_no }} &rarr; v{{ $log->to_version_no }}
                                        <span class="ml-2 text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $log->change_level === 'major' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($log->change_level) }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $log->changed_at->format('d M Y, h:i A') }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Changed by {{ $log->changedBy?->name }}</p>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($log->changed_fields as $field)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ ucwords(str_replace('_', ' ', $field)) }}</span>
                                    @endforeach
                                </div>
                                @if($log->requires_quotation_revision)
                                    <p class="text-xs text-amber-600 mt-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Suppliers with a live quotation were notified that revision may be needed.</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>

            <x-backend.form-card title="Deadline Extensions">
                @if($rfq->deadlineExtensions->isEmpty())
                    <p class="text-sm text-gray-400">No deadline extensions recorded.</p>
                @else
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($rfq->deadlineExtensions as $extension)
                            <li class="px-5 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $extension->deadline_type === 'qna' ? 'Q&A Deadline' : 'Quotation Deadline' }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $extension->old_deadline->format('d M Y, h:i A') }} &rarr; {{ $extension->new_deadline->format('d M Y, h:i A') }}</p>
                                @if($extension->reason)
                                    <p class="text-xs text-gray-500 mt-1">{{ $extension->reason }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-1">By {{ $extension->extendedBy?->name }} &middot; {{ $extension->created_at->format('d M Y, h:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>

        </div>

    </div>

    @can('extendDeadline', $rfq)
        <x-backend.modal id="extend-deadline" title="Extend Deadline">
            <form method="POST" action="{{ route('buyer.rfqs.extend-deadline', $rfq) }}" class="space-y-4">
                @csrf
                <x-backend.select name="deadline_type" label="Deadline" required :options="['quotation' => 'Quotation Deadline', 'qna' => 'Q&A Deadline']" />
                <x-backend.input type="datetime-local" name="new_deadline" label="New Deadline" required />
                <x-backend.textarea name="reason" label="Reason (optional)" />
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Extend Deadline</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @can('cancel', $rfq)
        <x-backend.modal id="cancel-rfq" title="Cancel this RFQ?">
            <form method="POST" action="{{ route('buyer.rfqs.cancel', $rfq) }}">
                @csrf
                <x-backend.textarea name="reason" label="Cancellation reason" required hint="This will be visible in the RFQ history." />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Keep RFQ</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Cancel RFQ</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

@endsection
