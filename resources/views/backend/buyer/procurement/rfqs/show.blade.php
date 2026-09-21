@extends('backend.layouts.buyer')

@section('title', $rfq->title)
@section('breadcrumb', 'Procurement / RFQs / ' . $rfq->rfq_number)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush

@section('body')

@php
    $vt = $rfq->getRelationValue('visibilityType');
    $daysRemaining = $statistics['days_remaining'];
    $deadlinePassed = $rfq->quotation_deadline && $rfq->quotation_deadline->isPast();

    // Days remaining pill colour
    $daysColor = 'bg-gray-100 text-gray-600';
    if ($daysRemaining !== null && !$deadlinePassed) {
        if ($daysRemaining <= 5)      $daysColor = 'bg-red-100 text-red-700';
        elseif ($daysRemaining <= 14) $daysColor = 'bg-amber-100 text-amber-800';
        else                          $daysColor = 'bg-emerald-100 text-emerald-700';
    }

    // Status chip colours (matching the page's own badge map)
    $statusColors = [
        'open'             => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
        'draft'            => ['bg' => 'bg-gray-100',   'border' => 'border-gray-200',    'text' => 'text-gray-600',    'dot' => 'bg-gray-400'],
        'pending_approval' => ['bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
        'closed'           => ['bg' => 'bg-gray-100',   'border' => 'border-gray-200',    'text' => 'text-gray-600',    'dot' => 'bg-gray-400'],
        'awarded'          => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',    'text' => 'text-blue-700',    'dot' => 'bg-blue-500'],
        'cancelled'        => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-700',     'dot' => 'bg-red-400'],
        'expired'          => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-700',     'dot' => 'bg-red-400'],
    ];
    $sc = $statusColors[$rfq->status] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-200', 'text' => 'text-gray-600', 'dot' => 'bg-gray-400'];
@endphp

    {{-- ── Hero Header ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs mb-5 overflow-hidden">
        {{-- Top colour stripe --}}
        <div class="h-1 w-full" style="background: linear-gradient(90deg, var(--theme-primary) 0%, var(--theme-primary-soft, #6366f1) 100%)"></div>

        <div class="px-5 py-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                {{-- Title + meta --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap mb-1">
                        {{-- Status chip --}}
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full border {{ $sc['bg'] }} {{ $sc['border'] }} {{ $sc['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} inline-block"></span>
                            {{ ucwords(str_replace('_', ' ', $rfq->status)) }}
                        </span>
                        {{-- RFQ number badge --}}
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $rfq->rfq_number }}
                        </span>
                        @if($rfq->quotation_deadline && !$deadlinePassed && $daysRemaining <= 5)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200">
                                <i class="fa-solid fa-clock animate-pulse text-xs"></i> Expiring Soon
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl font-bold text-gray-900 leading-snug mb-2">{{ $rfq->title }}</h1>

                    {{-- Compact info strip --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-gray-400"></i>
                            Created {{ $rfq->created_at->format('d M Y') }}
                        </span>
                        @if($rfq->published_at)
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-paper-plane text-emerald-400"></i>
                                Published {{ $rfq->published_at->format('d M Y') }}
                            </span>
                        @endif
                        @if($rfq->quotation_deadline)
                            <span class="flex items-center gap-1.5 {{ $deadlinePassed ? 'text-red-500' : '' }}">
                                <i class="fa-regular fa-clock {{ $deadlinePassed ? 'text-red-400' : 'text-gray-400' }}"></i>
                                Deadline: {{ $rfq->quotation_deadline->format('d M Y, h:i A') }}
                                @if($deadlinePassed)
                                    <span class="font-semibold text-red-500">(Passed)</span>
                                @elseif($daysRemaining !== null)
                                    <span class="font-semibold {{ $sc['text'] }}">· {{ $daysRemaining }}d left</span>
                                @endif
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-stacked text-gray-400"></i>
                            {{ $rfq->items->count() }} {{ Str::plural('item', $rfq->items->count()) }}
                        </span>
                        @if($vt)
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-eye text-gray-400"></i>
                                {{ $vt->name }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Mobile back-link (actions are in sidebar) --}}
                <div class="shrink-0 hidden sm:flex">
                    <a href="{{ route('buyer.rfqs.index') }}" class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> All RFQs
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main content area: Alpine tab controller wraps everything ──────── --}}
    <div x-data="{ tab: '{{ request('_tab', 'overview') }}' }">

        {{-- ── Tab bar (full-width) ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs mb-5 overflow-hidden">
            <div class="flex items-center gap-0.5 overflow-x-auto px-2 py-1.5">
                @foreach([
                    'overview'   => ['label' => 'Overview',      'icon' => 'fa-house'],
                    'items'      => ['label' => 'Items ('.$rfq->items->count().')', 'icon' => 'fa-boxes-stacked'],
                    'suppliers'  => ['label' => 'Suppliers',     'icon' => 'fa-building'],
                    'statistics' => ['label' => 'Statistics',    'icon' => 'fa-chart-bar'],
                    'questions'  => ['label' => 'Q&A ('.$rfq->questions->count().')', 'icon' => 'fa-circle-question'],
                    'history'    => ['label' => 'Change History','icon' => 'fa-clock-rotate-left'],
                ] as $key => $meta)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm whitespace-nowrap transition-all">
                        <i class="fa-solid {{ $meta['icon'] }} text-[11px]"></i>
                        {{ $meta['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ── 8 / 4 grid ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- ── LEFT: Tab Content (8 cols) ──────────────────────────────── --}}
            <div class="lg:col-span-8 min-w-0">

                {{-- Overview Tab --}}
                <div x-show="tab === 'overview'">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                        <div class="lg:col-span-2 space-y-5">
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

                        <div class="space-y-5">
                            <x-backend.form-card title="Supplier Targeting">
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Visibility</dt>
                                        <dd class="text-gray-900 font-medium">{{ $vt?->name ?? '—' }}</dd>
                                    </div>
                                    @php($tf = $vt?->code === 'open_matching' ? $rfq->targetFilters->first() : null)
                                    @if($tf)
                                        <div class="flex justify-between"><dt class="text-gray-500">Category Filter</dt><dd class="text-gray-900 font-medium">{{ $tf->category?->name ?? 'All Categories (Open Broadcast)' }}</dd></div>
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

                {{-- Items Tab --}}
                <div x-show="tab === 'items'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @forelse($rfq->items as $item)
                            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 transition-colors">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 text-xs font-bold {{ $item->listing_id ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-semibold text-gray-900">{{ $item->item_name }}</p>
                                            @if($item->isRequirement())
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 bg-amber-50 text-amber-900 border border-amber-300 flex items-center gap-1">
                                                    <i class="fa-solid fa-file-invoice text-amber-700 text-[9px]"></i>
                                                    Requirement (Quotation Only)
                                                </span>
                                            @elseif($item->listing_id)
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0 bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                                    <i class="fa-solid fa-store text-emerald-600 text-[9px]"></i>
                                                    Marketplace Product
                                                </span>
                                            @else
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0 bg-gray-100 text-gray-700 border border-gray-200 flex items-center gap-1">
                                                    <i class="fa-solid fa-box-open text-gray-400 text-[9px]"></i>
                                                    Custom Product
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->category?->name ?? 'No category selected' }}</p>
                                        @if($item->description)<p class="text-xs text-gray-500 mt-1.5">{{ $item->description }}</p>@endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 mb-4 py-3 border-y border-gray-100">
                                    <div class="text-center">
                                        <p class="text-sm font-bold text-gray-900">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide mt-0.5">{{ $item->unit?->symbol ?? $item->custom_unit ?? 'Qty' }}</p>
                                    </div>
                                    <div class="text-center border-x border-gray-100">
                                        <p class="text-sm font-bold text-gray-900">{{ $item->estimated_unit_price ? number_format($item->estimated_unit_price, 2) : '—' }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide mt-0.5">Est. / Unit</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-bold text-gray-900">{{ $item->attributeValues->count() + count(is_array($item->specs) ? array_filter($item->specs, fn($s) => ($s['name'] ?? '') !== '__is_requirement') : []) }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wide mt-0.5">Specs</p>
                                    </div>
                                </div>

                                @if($item->attributeValues->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                                        @foreach($item->attributeValues as $value)
                                            <div class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-gray-50">
                                                <span class="text-gray-500 truncate">{{ $value->attribute?->name }}</span>
                                                <span class="text-gray-900 font-medium text-right truncate">{{ $value->formattedValue() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(is_array($item->specs))
                                    @php($customSpecs = array_filter($item->specs, fn($s) => is_array($s) && ($s['name'] ?? '') !== '__is_requirement'))
                                    @if(!empty($customSpecs))
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 mt-2">
                                            @foreach($customSpecs as $cs)
                                                <div class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-gray-50">
                                                    <span class="text-gray-500 truncate">{{ $cs['name'] ?? '' }}</span>
                                                    <span class="text-gray-900 font-medium text-right truncate">{{ $cs['value'] ?? '' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                @if($item->relationLoaded('media') ? $item->media->isNotEmpty() : $item->getMedia('attachments')->isNotEmpty())
                                    <div class="mt-3 pt-3 border-t border-gray-100">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                            <i class="fa-solid fa-paperclip text-amber-600"></i> Reference Attachments ({{ $item->getMedia('attachments')->count() }})
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($item->getMedia('attachments') as $media)
                                                <a href="{{ $media->getUrl() }}" target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gray-50 border border-gray-200 hover:border-amber-400 text-xs text-gray-700 hover:text-amber-900 transition-colors">
                                                    <i class="fa-solid {{ str_starts_with($media->mime_type ?? '', 'image/') ? 'fa-file-image text-emerald-600' : 'fa-file-pdf text-red-600' }} text-[11px]"></i>
                                                    <span class="truncate max-w-[140px]">{{ $media->file_name }}</span>
                                                    <span class="text-[10px] text-gray-400 font-mono">({{ $media->human_readable_size }})</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-full">
                                <x-backend.empty-state icon="fa-box-open" title="No items yet" description="Items added to this RFQ will appear here." />
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Suppliers Tab --}}
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

                {{-- Statistics Tab --}}
                <div x-show="tab === 'statistics'" x-cloak class="space-y-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <p class="text-[11px] text-gray-500">Suppliers Notified</p>
                            <p class="text-xl font-bold text-gray-900">{{ $statistics['total_notified'] }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <p class="text-[11px] text-gray-500">Suppliers Viewed</p>
                            <p class="text-xl font-bold text-gray-900">{{ $statistics['viewed'] }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <p class="text-[11px] text-gray-500">Suppliers Interested</p>
                            <p class="text-xl font-bold text-gray-900">{{ $statistics['interested'] }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <p class="text-[11px] text-gray-500">Messages Received</p>
                            <p class="text-xl font-bold text-gray-900">{{ $statistics['messaged'] }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <p class="text-[11px] text-gray-500">Quotations Received</p>
                            <p class="text-xl font-bold text-gray-900">{{ $statistics['quotations_received'] }}</p>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <p class="text-[11px] text-gray-500">Days Remaining</p>
                            <p class="text-xl font-bold text-gray-900">{{ $statistics['days_remaining'] ?? '—' }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Last activity: {{ $statistics['last_activity_human'] ?? 'No activity yet' }}</p>

                    {{-- Engagement funnel --}}
                    <x-backend.form-card title="Engagement Funnel">
                        <div class="flex flex-col sm:flex-row items-stretch gap-2">
                            @foreach($engagementFunnel as $i => $stage)
                                <div class="flex-1 flex items-center gap-2">
                                    <div class="flex-1 rounded-lg border border-gray-200 p-3 text-center">
                                        <p class="text-[11px] text-gray-500">{{ $stage['label'] }}</p>
                                        <p class="text-xl font-bold text-gray-900">{{ $stage['count'] }}</p>
                                        @if($i > 0)
                                            <p class="text-[11px] font-medium" style="color:var(--theme-primary)">{{ $stage['percent'] }}%</p>
                                        @endif
                                        <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-full rounded-full" style="width: {{ $stage['percent'] }}%; background-color: var(--theme-primary)"></div>
                                        </div>
                                    </div>
                                    @if($i < count($engagementFunnel) - 1)
                                        <i class="fa-solid fa-chevron-right text-gray-300 hidden sm:block"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-backend.form-card>

                    {{-- RFQ health / recommendations --}}
                    @if(!empty($recommendations))
                        <x-backend.form-card title="RFQ Health &amp; Recommendations">
                            <div class="space-y-3">
                                @foreach($recommendations as $rec)
                                    <div class="flex items-start gap-3 p-3 rounded-lg bg-amber-50 border border-amber-200">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                            <i class="fa-solid {{ $rec['icon'] }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-900">{{ $rec['title'] }}</p>
                                            <p class="text-xs text-gray-600 mt-0.5">{{ $rec['description'] }}</p>
                                        </div>
                                        @if(isset($rec['action_modal']))
                                            <button type="button" @click="$dispatch('open-modal-{{ $rec['action_modal'] }}')"
                                                    class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border border-amber-300 text-amber-800 bg-white hover:bg-amber-100 whitespace-nowrap">
                                                {{ $rec['action_label'] }}
                                            </button>
                                        @elseif(isset($rec['action_href']))
                                            <a href="{{ $rec['action_href'] }}"
                                               class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border border-amber-300 text-amber-800 bg-white hover:bg-amber-100 whitespace-nowrap">
                                                {{ $rec['action_label'] }}
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </x-backend.form-card>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
                        {{-- Supplier activity table --}}
                        <div class="lg:col-span-3">
                            <x-backend.form-card title="Supplier Activity">
                                @if(empty($supplierEngagement))
                                    <p class="text-sm text-gray-400">No supplier activity yet — this will fill in as suppliers view, engage with, or quote on this RFQ.</p>
                                @else
                                    <div class="-mx-5 -mb-5 overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-gray-100 text-[11px] text-gray-500 uppercase tracking-wide">
                                                    <th class="px-5 py-2.5 text-left">Supplier</th>
                                                    <th class="px-5 py-2.5 text-left">Viewed</th>
                                                    <th class="px-5 py-2.5 text-left">Interested</th>
                                                    <th class="px-5 py-2.5 text-left">Messages</th>
                                                    <th class="px-5 py-2.5 text-left">Quotation</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($supplierEngagement as $row)
                                                    <tr>
                                                        <td class="px-5 py-2.5">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <img src="{{ $row['logo_url'] }}" class="w-7 h-7 rounded-full object-cover border border-gray-200 shrink-0" alt="">
                                                                @if($row['account'])
                                                                    <a href="{{ route('buyer.suppliers.show', $row['account']) }}" class="text-gray-800 font-medium hover:text-indigo-600 truncate">{{ $row['name'] }}</a>
                                                                @else
                                                                    <span class="text-gray-800 font-medium truncate">{{ $row['name'] }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-2.5">
                                                            @if($row['seen_at'])
                                                                <span class="text-emerald-600"><i class="fa-solid fa-check"></i> {{ $row['seen_at']->diffForHumans() }}</span>
                                                            @else
                                                                <span class="text-gray-400">Not yet</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-5 py-2.5">
                                                            @if($row['interested'])
                                                                <span class="text-emerald-600"><i class="fa-solid fa-check"></i></span>
                                                            @else
                                                                <span class="text-gray-300">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-5 py-2.5">
                                                            @if($row['message_count'] > 0)
                                                                <span class="text-emerald-600"><i class="fa-solid fa-comment-dots"></i> {{ $row['message_count'] }}</span>
                                                            @else
                                                                <span class="text-gray-300">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-5 py-2.5">
                                                            @if($row['quotation_status'])
                                                                <x-backend.status-badge :status="$row['quotation_status']" />
                                                            @else
                                                                <span class="text-gray-300">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </x-backend.form-card>
                        </div>

                        {{-- Recent activity timeline --}}
                        <div class="lg:col-span-2">
                            <x-backend.form-card title="Recent Activity">
                                @if(empty($activityTimeline))
                                    <p class="text-sm text-gray-400">No activity recorded yet.</p>
                                @else
                                    <ul class="-mb-1">
                                        @foreach($activityTimeline as $event)
                                            <li class="flex items-start gap-3 pb-4 relative">
                                                @if(!$loop->last)
                                                    <span class="absolute left-3.5 top-7 bottom-0 w-px bg-gray-100"></span>
                                                @endif
                                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 {{ $event['color'] }}">
                                                    <i class="fa-solid {{ $event['icon'] }} text-[11px]"></i>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-0.5">
                                                    <p class="text-xs text-gray-700"><span class="font-semibold text-gray-900">{{ $event['supplier'] }}</span> {{ $event['action'] }}</p>
                                                    <p class="text-[11px] text-gray-400">{{ \Illuminate\Support\Carbon::parse($event['at'])->diffForHumans() }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </x-backend.form-card>
                        </div>
                    </div>
                </div>

                {{-- Q&A Tab --}}
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

                {{-- Change History Tab --}}
                <div x-show="tab === 'history'" x-cloak class="space-y-5">
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

            </div>{{-- end left col --}}

            {{-- ── RIGHT: Sidebar (4 cols) ─────────────────────────────────── --}}
            <div class="lg:col-span-4 space-y-4">

                {{-- ── 1. Actions Card (top) ───────────────────────────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-400 text-sm"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Actions</h3>
                    </div>
                    <ul class="divide-y divide-gray-50 px-1 py-1">
                        @can('update', $rfq)
                            <li>
                                <a href="{{ route('buyer.rfqs.edit', $rfq) }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-700 transition-colors group">
                                    <i class="fa-solid fa-pen-to-square w-4 text-center text-indigo-400 group-hover:text-indigo-600 text-xs"></i>
                                    Edit RFQ
                                </a>
                            </li>
                        @endcan

                        @can('extendDeadline', $rfq)
                            <li>
                                <button type="button" x-on:click="$dispatch('open-modal-extend-deadline')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group text-left">
                                    <i class="fa-solid fa-clock w-4 text-center text-blue-400 group-hover:text-blue-600 text-xs"></i>
                                    Extend Deadline
                                </button>
                            </li>
                        @endcan

                        @can('publish', $rfq)
                            <li>
                                <form method="POST" action="{{ route('buyer.rfqs.publish', $rfq) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition-colors group text-left">
                                        <i class="fa-solid fa-paper-plane w-4 text-center text-indigo-500 group-hover:text-indigo-700 text-xs"></i>
                                        Publish RFQ
                                    </button>
                                </form>
                            </li>
                        @endcan

                        @can('compare', $rfq)
                            <li>
                                <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group">
                                    <i class="fa-solid fa-list-check w-4 text-center text-emerald-500 group-hover:text-emerald-600 text-xs"></i>
                                    View Responses
                                    @if($rfq->quotations_count > 0)
                                        <span class="ml-auto text-[11px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ $rfq->quotations_count }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('buyer.quotations.compare', $rfq) }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors group">
                                    <i class="fa-solid fa-scale-balanced w-4 text-center text-purple-500 group-hover:text-purple-600 text-xs"></i>
                                    Compare Quotations
                                </a>
                            </li>
                        @endcan

                        @can('cancel', $rfq)
                            <li class="border-t border-gray-100 mt-1 pt-1">
                                <button type="button" x-on:click="$dispatch('open-modal-cancel-rfq')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors group text-left">
                                    <i class="fa-solid fa-circle-xmark w-4 text-center text-red-400 group-hover:text-red-600 text-xs"></i>
                                    Cancel RFQ
                                </button>
                            </li>
                        @endcan
                    </ul>
                </div>

                {{-- ── 2. RFQ Status Card (below Actions) ──────────────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-indigo-400 text-sm"></i>
                        <h3 class="text-sm font-semibold text-gray-800">RFQ Status</h3>
                    </div>
                    <div class="px-4 py-4 space-y-3">
                        {{-- Current status --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Current Status</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full border {{ $sc['bg'] }} {{ $sc['border'] }} {{ $sc['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} inline-block"></span>
                                {{ ucwords(str_replace('_', ' ', $rfq->status)) }}
                            </span>
                        </div>
                        {{-- Visibility --}}
                        @if($vt)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Visibility</span>
                                <span class="text-xs font-medium text-gray-800">{{ $vt->name }}</span>
                            </div>
                        @endif
                        {{-- Deadline --}}
                        @if($rfq->quotation_deadline)
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-xs text-gray-500 shrink-0">Deadline</span>
                                <div class="text-right">
                                    <p class="text-xs font-medium {{ $deadlinePassed ? 'text-red-600' : 'text-gray-800' }}">
                                        {{ $rfq->quotation_deadline->format('d M Y') }}
                                    </p>
                                    <p class="text-[11px] text-gray-400">{{ $rfq->quotation_deadline->format('h:i A') }}</p>
                                </div>
                            </div>
                            {{-- Days remaining pill --}}
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Time Left</span>
                                @if($deadlinePassed)
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700">Deadline Passed</span>
                                @elseif($daysRemaining !== null)
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $daysColor }}">
                                        {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }} left
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Deadline</span>
                                <span class="text-xs text-gray-400">Not set</span>
                            </div>
                        @endif
                        {{-- Items + Quotations mini strip --}}
                        <div class="pt-2 mt-1 border-t border-gray-100 grid grid-cols-2 gap-2 text-center">
                            <div class="bg-gray-50 rounded-lg py-2">
                                <p class="text-sm font-bold text-gray-900">{{ $rfq->items->count() }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">{{ Str::plural('Item', $rfq->items->count()) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg py-2">
                                <p class="text-sm font-bold text-gray-900">{{ $rfq->quotations_count }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Quotations</p>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- ── 3. Performance Summary Card ─────────────────────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-emerald-400 text-sm"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Performance</h3>
                    </div>
                    <div class="px-4 py-4">
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            {{-- Supplier Views --}}
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-blue-700">{{ $statistics['viewed'] }}</p>
                                <p class="text-[10px] text-blue-500 mt-0.5 font-medium uppercase tracking-wide">Views</p>
                            </div>
                            {{-- Interested --}}
                            <div class="bg-indigo-50 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-indigo-700">{{ $statistics['interested'] }}</p>
                                <p class="text-[10px] text-indigo-500 mt-0.5 font-medium uppercase tracking-wide">Interested</p>
                            </div>
                            {{-- Messages --}}
                            <div class="bg-purple-50 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-purple-700">{{ $statistics['messaged'] }}</p>
                                <p class="text-[10px] text-purple-500 mt-0.5 font-medium uppercase tracking-wide">Messages</p>
                            </div>
                            {{-- Quotations --}}
                            <div class="bg-emerald-50 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-emerald-700">{{ $statistics['quotations_received'] }}</p>
                                <p class="text-[10px] text-emerald-500 mt-0.5 font-medium uppercase tracking-wide">Quotations</p>
                            </div>
                        </div>
                        {{-- View Full Statistics button — switches to Statistics tab --}}
                        <button @click="tab = 'statistics'"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition">
                            <i class="fa-solid fa-chart-bar text-gray-400"></i>
                            View Full Statistics
                        </button>
                    </div>
                </div>

            </div>{{-- end sidebar --}}

        </div>{{-- end 8/4 grid --}}

    </div>{{-- end x-data tab controller --}}

    {{-- ── Modals (unchanged) ─────────────────────────────────────────────── --}}
    @can('extendDeadline', $rfq)
        <x-backend.modal id="extend-deadline" title="Extend Deadline">
            <form method="POST" action="{{ route('buyer.rfqs.extend-deadline', $rfq) }}" class="space-y-4">
                @csrf
                <x-backend.select name="deadline_type" label="Deadline" required :options="['quotation' => 'Quotation Deadline', 'qna' => 'Q&A Deadline']" />
                <x-backend.input type="text" name="new_deadline" label="New Deadline" required autocomplete="off" placeholder="Select date &amp; time"
                                  x-init="typeof flatpickr !== 'undefined' && flatpickr($el, { enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i' })" />
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
