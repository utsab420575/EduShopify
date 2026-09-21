{{-- Self-contained: rendered both inline (normal page load) and standalone
     (AJAX live-filter/sort/pagination responses) — see docs/AI/design.md
     §17.1 for the pattern this follows (mirrors the supplier opportunities
     table). Defines its own badge map rather than relying on the parent
     view's inline PHP, same reasoning as the supplier table partial. --}}
@php
    $visibilityBadgeMeta = [
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
                    <th class="px-4 py-3">RFQ</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Quotes</th>
                    <th class="px-4 py-3">Visibility</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $sortToggle, 'page' => null]) }}"
                           data-ajax-link class="inline-flex items-center gap-1 hover:text-indigo-600">
                            Deadline <i class="fa-solid {{ $sortIcon }} text-[10px]"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rfqs as $rfq)
                    @php
                        $visibilityCode = $rfq->getRelationValue('visibilityType')?->code;
                    @endphp
                    <tr class="hover:bg-gray-50/60 align-top">
                        <td class="px-4 py-3 max-w-[280px]">
                            <a href="{{ route('buyer.rfqs.show', $rfq) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 line-clamp-2">
                                {{ $rfq->title }}
                            </a>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $rfq->rfq_number }} &middot; {{ $rfq->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $rfq->items_count }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $rfq->quotations_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($visibilityCode && isset($visibilityBadgeMeta[$visibilityCode]))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $visibilityBadgeMeta[$visibilityCode]['class'] }}">
                                    <i class="fa-solid {{ $visibilityBadgeMeta[$visibilityCode]['icon'] }} mr-1"></i>{{ $visibilityBadgeMeta[$visibilityCode]['label'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap"><x-backend.status-badge :status="$rfq->status" /></td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                            @if($rfq->quotation_deadline)
                                <i class="fa-regular fa-clock mr-1 text-gray-400"></i>{{ $rfq->quotation_deadline->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                // Keep the actions dynamic per RFQ status — same
                                // list drives both the desktop pill row and the
                                // mobile "More" dropdown, so they can never show
                                // different actions for the same RFQ.
                                $actions = [
                                    ['label' => 'View', 'icon' => 'fa-regular fa-eye', 'href' => route('buyer.rfqs.show', $rfq)],
                                ];

                                switch ($rfq->status) {
                                    case 'draft':
                                        $actions[] = ['label' => 'Edit', 'icon' => 'fa-solid fa-pen', 'href' => route('buyer.rfqs.edit', $rfq)];
                                        $actions[] = [
                                            'label' => 'Delete Draft', 'icon' => 'fa-solid fa-trash-can', 'danger' => true,
                                            'href' => route('buyer.rfqs.destroy', $rfq), 'method' => 'DELETE',
                                            'confirm' => ['title' => 'Delete this draft?', 'text' => 'This cannot be undone.', 'icon' => 'warning', 'confirmText' => 'Yes, delete it'],
                                        ];
                                        break;

                                    case 'open':
                                        $actions[] = ['label' => 'Statistics', 'icon' => 'fa-solid fa-chart-column', 'stats' => true];
                                        $actions[] = ['label' => 'Edit', 'icon' => 'fa-solid fa-pen', 'href' => route('buyer.rfqs.edit', $rfq)];
                                        $actions[] = ['label' => 'Extend Deadline', 'icon' => 'fa-regular fa-clock', 'href' => route('buyer.rfqs.show', $rfq)];
                                        $actions[] = ['label' => 'View Responses', 'icon' => 'fa-solid fa-inbox', 'href' => route('buyer.quotations.index', ['rfq' => $rfq->id])];
                                        $actions[] = ['label' => 'Compare Quotations', 'icon' => 'fa-solid fa-scale-balanced', 'href' => route('buyer.quotations.compare', $rfq)];
                                        $actions[] = ['label' => 'Cancel RFQ', 'icon' => 'fa-solid fa-ban', 'danger' => true, 'href' => route('buyer.rfqs.show', $rfq)];
                                        break;

                                    case 'pending_approval':
                                        $actions[] = ['label' => 'Edit', 'icon' => 'fa-solid fa-pen', 'href' => route('buyer.rfqs.edit', $rfq)];
                                        $actions[] = ['label' => 'Cancel RFQ', 'icon' => 'fa-solid fa-ban', 'danger' => true, 'href' => route('buyer.rfqs.show', $rfq)];
                                        break;

                                    case 'award_pending':
                                        $actions[] = ['label' => 'Statistics', 'icon' => 'fa-solid fa-chart-column', 'stats' => true];
                                        $actions[] = ['label' => 'Compare Quotations', 'icon' => 'fa-solid fa-scale-balanced', 'href' => route('buyer.quotations.compare', $rfq)];
                                        $actions[] = ['label' => 'Award Supplier', 'icon' => 'fa-solid fa-trophy', 'href' => route('buyer.quotations.index', ['rfq' => $rfq->id])];
                                        $actions[] = ['label' => 'Cancel RFQ', 'icon' => 'fa-solid fa-ban', 'danger' => true, 'href' => route('buyer.rfqs.show', $rfq)];
                                        break;

                                    case 'awarded':
                                        if ($rfq->latestAward) {
                                            $actions[] = ['label' => 'View Award Details', 'icon' => 'fa-solid fa-trophy', 'href' => route('buyer.awards.show', $rfq->latestAward)];
                                        }
                                        $actions[] = ['label' => 'Download RFQ', 'icon' => 'fa-solid fa-download', 'href' => route('buyer.rfqs.show', $rfq)];
                                        break;

                                    case 'closed':
                                    case 'expired':
                                    case 'cancelled':
                                    case 'completed':
                                        $actions[] = ['label' => 'Statistics', 'icon' => 'fa-solid fa-chart-column', 'stats' => true];
                                        $actions[] = ['label' => 'Duplicate RFQ', 'icon' => 'fa-solid fa-copy', 'href' => route('buyer.rfqs.duplicate', $rfq), 'method' => 'POST'];
                                        $actions[] = ['label' => 'Download RFQ', 'icon' => 'fa-solid fa-download', 'href' => route('buyer.rfqs.show', $rfq)];
                                        break;
                                }
                            @endphp

                            {{-- Desktop / tablet: icon + text pill buttons --}}
                            <div class="hidden md:flex items-center flex-wrap justify-end gap-1.5">
                                @foreach($actions as $action)
                                    @include('backend.buyer.procurement.rfqs.partials._action-button', ['action' => $action, 'rfq' => $rfq, 'variant' => 'pill'])
                                @endforeach
                            </div>

                            {{-- Mobile: collapse into a "More" dropdown --}}
                            <div class="md:hidden relative flex justify-end" x-data="{ menuOpen: false }" @click.away="menuOpen = false">
                                <button type="button" @click="menuOpen = !menuOpen" title="More actions"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div x-show="menuOpen" x-cloak @click="menuOpen = false"
                                     x-transition
                                     class="absolute right-0 top-full mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-20 py-1">
                                    @foreach($actions as $action)
                                        @include('backend.buyer.procurement.rfqs.partials._action-button', ['action' => $action, 'rfq' => $rfq, 'variant' => 'menu'])
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10">
                            <x-backend.empty-state icon="fa-file-signature" title="No RFQs found" description="There are no RFQs matching the selected filters.">
                                <x-slot:actions>
                                    @can('create', \App\Models\Rfq::class)
                                        <a href="{{ route('buyer.rfqs.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Create RFQ</a>
                                    @endcan
                                </x-slot:actions>
                            </x-backend.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rfqs->total() > 0)
        <div class="p-4 border-t border-gray-100" data-ajax-link>
            <x-backend.pagination :paginator="$rfqs" />
        </div>
    @endif
</div>
