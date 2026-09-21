{{-- Self-contained: rendered both inline (normal page load) and standalone
     (AJAX live-filter/sort/pagination responses), so it defines its own
     badge/label maps rather than relying on the parent view's inline PHP block. --}}
@php
    $statusBadgeMeta = [
        'active' => ['label' => 'Active', 'class' => 'bg-emerald-100 text-emerald-800'],
        'expiring_soon' => ['label' => 'Expiring Soon', 'class' => 'bg-amber-100 text-amber-800'],
        'expired' => ['label' => 'Expired', 'class' => 'bg-gray-200 text-gray-600'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-100 text-red-700'],
        'closed' => ['label' => 'Closed', 'class' => 'bg-gray-200 text-gray-600'],
    ];
    $activityBadgeMeta = [
        'new' => ['label' => 'New', 'class' => 'bg-indigo-100 text-indigo-800'],
        'viewed' => ['label' => 'Viewed', 'class' => 'bg-gray-100 text-gray-600'],
        'interested' => ['label' => 'Interested', 'class' => 'bg-blue-100 text-blue-800'],
        'not_interested' => ['label' => 'Not Interested', 'class' => 'bg-red-100 text-red-700'],
        'messaged' => ['label' => 'Messaged', 'class' => 'bg-purple-100 text-purple-800'],
        'preparing_quote' => ['label' => 'Preparing Quote', 'class' => 'bg-amber-100 text-amber-800'],
        'quoted' => ['label' => 'Quoted', 'class' => 'bg-emerald-100 text-emerald-800'],
    ];
    $sourceBadgeMeta = [
        'direct' => ['label' => 'Direct', 'icon' => 'fa-star', 'class' => 'bg-amber-100 text-amber-800'],
        'invited' => ['label' => 'Invited', 'icon' => 'fa-envelope-open', 'class' => 'bg-blue-100 text-blue-800'],
        'open_matching' => ['label' => 'Open Matching', 'icon' => 'fa-diagram-project', 'class' => 'bg-emerald-100 text-emerald-800'],
        'broadcast_all' => ['label' => 'Broadcast', 'icon' => 'fa-tower-broadcast', 'class' => 'bg-gray-200 text-gray-700'],
    ];

    $sortToggle = $sort === 'deadline_asc' ? 'deadline_desc' : 'deadline_asc';
    $sortIcon = match ($sort) {
        'deadline_asc' => 'fa-arrow-up-short-wide',
        'deadline_desc' => 'fa-arrow-down-wide-short',
        default => 'fa-sort',
    };
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] font-bold text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3">RFQ ID</th>
                    <th class="px-4 py-3">Requirement</th>
                    <th class="px-4 py-3">Buyer</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $sortToggle, 'page' => null]) }}"
                           data-ajax-link class="inline-flex items-center gap-1 hover:text-indigo-600">
                            Deadline <i class="fa-solid {{ $sortIcon }} text-[10px]"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3">Activity</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($opportunities as $queueEntry)
                    @php
                        $rfq = $queueEntry->rfq;
                    @endphp
                    @continue(!$rfq)
                    @php
                        $visibilityCode = $rfq->getRelationValue('visibilityType')?->code;
                        $statusBucket = $rfq->lifecycleBucket();
                        $stage = $stages[$rfq->id] ?? 'new';
                        $quotation = $quotationsByRfq->get($rfq->id);
                    @endphp
                    <tr class="hover:bg-gray-50/60 align-top">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('supplier.opportunities.show', $rfq) }}" class="text-xs font-mono font-bold text-indigo-600 hover:underline">
                                    {{ $rfq->rfq_number }}
                                </a>
                                @if($statusBucket === 'expiring_soon')
                                    <i class="fa-solid fa-clock animate-pulse text-yellow-500" title="Expiring soon"></i>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 max-w-[240px]">
                            <a href="{{ route('supplier.opportunities.show', $rfq) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 line-clamp-2">
                                {{ $rfq->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                            {{ $rfq->buyerAccount?->buyerProfile?->organization_name ?? $rfq->buyerAccount?->display_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($visibilityCode && isset($sourceBadgeMeta[$visibilityCode]))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $sourceBadgeMeta[$visibilityCode]['class'] }}">
                                    <i class="fa-solid {{ $sourceBadgeMeta[$visibilityCode]['icon'] }} mr-1"></i>{{ $sourceBadgeMeta[$visibilityCode]['label'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $statusBadgeMeta[$statusBucket]['class'] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusBadgeMeta[$statusBucket]['label'] ?? ucfirst($statusBucket) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                            @if($rfq->quotation_deadline)
                                <i class="fa-regular fa-clock mr-1 text-gray-400"></i>{{ $rfq->quotation_deadline->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $activityBadgeMeta[$stage]['class'] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $activityBadgeMeta[$stage]['label'] ?? ucfirst($stage) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center flex-wrap gap-1.5">
                                <a href="{{ route('supplier.opportunities.show', $rfq) }}"
                                   class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 flex items-center gap-1" title="View RFQ">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>

                                @if($quotation && $quotation->status !== 'draft')
                                    <a href="{{ route('supplier.quotations.show', $quotation) }}"
                                       class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 flex items-center gap-1.5" title="View Submitted Quote">
                                        <i class="fa-solid fa-sack-dollar text-emerald-600"></i> Quoted
                                    </a>
                                @elseif($quotation)
                                    <a href="{{ route('supplier.quotations.show', $quotation) }}"
                                       class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 flex items-center gap-1.5" title="Continue Draft Quotation">
                                        <i class="fa-solid fa-sack-dollar text-amber-500"></i> Continue Draft
                                    </a>
                                    <form method="POST" action="{{ route('supplier.messages.start') }}">
                                        @csrf
                                        <input type="hidden" name="recipient_account_id" value="{{ $rfq->buyer_account_id }}">
                                        <input type="hidden" name="context_type" value="rfq">
                                        <input type="hidden" name="context_id" value="{{ $rfq->id }}">
                                        <button type="submit" class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 flex items-center gap-1">
                                            <i class="fa-solid fa-comment-dots"></i> {{ $stage === 'messaged' ? 'Continue Chat' : 'Message' }}
                                        </button>
                                    </form>
                                @elseif($stage === 'not_interested')
                                    <span class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 flex items-center gap-1">
                                        <i class="fa-solid fa-ban"></i> Not Interested
                                    </span>
                                @else
                                    @if(in_array($stage, ['interested', 'messaged', 'preparing_quote'], true))
                                        <span class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 flex items-center gap-1">
                                            <i class="fa-solid fa-hand-pointer"></i> Interested
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('supplier.opportunities.interested', $rfq) }}">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50 flex items-center gap-1">
                                                <i class="fa-light fa-regular fa-hand"></i> Interested
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('supplier.opportunities.decline', $rfq) }}" onsubmit="return confirm('Mark this RFQ as Not Interested?');">
                                            @csrf
                                            <button type="submit" class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 flex items-center gap-1">
                                                <i class="fa-solid fa-xmark"></i> Not Interested
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('supplier.messages.start') }}">
                                        @csrf
                                        <input type="hidden" name="recipient_account_id" value="{{ $rfq->buyer_account_id }}">
                                        <input type="hidden" name="context_type" value="rfq">
                                        <input type="hidden" name="context_id" value="{{ $rfq->id }}">
                                        <button type="submit" class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 flex items-center gap-1">
                                            <i class="fa-solid fa-comment-dots"></i> {{ $stage === 'messaged' ? 'Continue Chat' : 'Message' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('supplier.quotations.create', $rfq) }}"
                                       class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg btn-primary flex items-center gap-1">
                                        <i class="fa-solid fa-sack-dollar"></i> Submit Quote
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10">
                            <x-backend.empty-state icon="fa-magnifying-glass-chart" title="No RFQ opportunities right now" description="Check back soon or ensure your service areas and catalog match buyer requirements." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($opportunities->total() > 0)
        <div class="p-4 border-t border-gray-100" data-ajax-link>
            <x-backend.pagination :paginator="$opportunities" />
        </div>
    @endif
</div>
