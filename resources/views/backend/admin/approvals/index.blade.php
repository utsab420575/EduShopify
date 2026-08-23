@extends('backend.layouts.admin')

@section('title', 'Approval Center')
@section('breadcrumb', 'Platform Governance / Approval Center')

@section('body')

    <x-backend.page-header title="Approval Center" subtitle="Review and moderate platform items awaiting administrative decision." />

    @if(empty($queues))
        <x-backend.empty-state icon="fa-clipboard-check" title="Nothing to review" description="You don't have any pending approval queues assigned." />
    @else
        {{-- Queue Tabs Bar --}}
        <div class="flex items-center gap-2 border-b border-gray-200 mb-6 overflow-x-auto pb-1">
            @foreach($queues as $key => $queue)
                <a href="{{ route('admin.approvals.index', ['tab' => $key]) }}"
                   class="px-4 py-2.5 text-xs font-semibold whitespace-nowrap flex items-center gap-2 rounded-t-lg transition-all"
                   style="{{ $tab === $key ? 'color:var(--theme-primary);border-bottom: 2px solid var(--theme-primary);background:rgba(99,102,241,0.04);' : 'color:#64748B;' }}">
                    <i class="fa-solid {{ $queue['icon'] ?? 'fa-clipboard-list' }} {{ $tab === $key ? 'text-indigo-600' : 'text-gray-400' }}"></i>
                    {{ $queue['label'] }}
                    @if($queue['count'] > 0)
                        <span class="text-[11px] font-bold rounded-full px-2 py-0.5 shadow-xs"
                              style="{{ $tab === $key ? 'background:var(--theme-primary,#4F46E5);color:#fff;' : 'background:#FEF3C7;color:#92400E;' }}">
                            {{ $queue['count'] }}
                        </span>
                    @else
                        <span class="text-[10px] text-gray-400 font-medium">(0)</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Queue Content Table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            @if($items->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 mb-1">Queue is empty</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">There are currently no items pending review in this queue.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                            @if($tab === 'listings')
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Listing / Product</th>
                                    <th class="px-4 py-3 font-semibold">Category</th>
                                    <th class="px-4 py-3 font-semibold">Supplier</th>
                                    <th class="px-4 py-3 font-semibold">Base Price</th>
                                    <th class="px-4 py-3 font-semibold">Submitted</th>
                                    <th class="px-5 py-3 font-semibold text-right">Moderation Actions</th>
                                </tr>
                            @else
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Item &amp; Details</th>
                                    <th class="px-4 py-3 font-semibold">Submitted By</th>
                                    <th class="px-4 py-3 font-semibold">Date Submitted</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    @switch($tab)
                                        @case('listings')
                                            @php($firstImg = $item->getMedia('gallery')->first())
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-11 h-11 rounded-lg border border-gray-200 overflow-hidden bg-gray-50 flex-shrink-0 flex items-center justify-center">
                                                        @if($firstImg)
                                                            <img src="{{ $firstImg->getUrl() }}" alt="" class="w-full h-full object-cover">
                                                        @else
                                                            <i class="fa-solid fa-box text-gray-300 text-base"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('admin.catalog.listings.show', $item) }}" class="font-bold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-1">
                                                            {{ $item->name }}
                                                        </a>
                                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                            <span class="font-mono text-[10px] text-gray-400">SKU: {{ $item->sku ?? '—' }}</span>
                                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase bg-purple-50 text-purple-700 border border-purple-100">
                                                                {{ $item->listing_type }}
                                                            </span>
                                                            @if($item->variants->isNotEmpty())
                                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                                    {{ $item->variants->count() }} Variants
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-700 font-medium">
                                                {{ $item->mainCategory?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3.5">
                                                @if($item->supplierAccount)
                                                    <a href="{{ route('admin.suppliers.show', $item->supplierAccount) }}" class="font-bold text-gray-800 hover:text-indigo-600 transition-colors flex items-center gap-1">
                                                        {{ $item->supplierAccount->supplierProfile?->display_name ?? $item->supplierAccount->display_name }}
                                                        <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3.5 font-bold text-indigo-700">
                                                {{ $item->base_price ? $item->currency_code . ' ' . number_format($item->base_price, 2) : 'Negotiable' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-500 text-[11px]">
                                                {{ $item->created_at->format('d M Y') }}
                                                <span class="block text-gray-400 text-[10px]">{{ $item->created_at->diffForHumans() }}</span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('admin.catalog.listings.show', $item) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center gap-1 transition-colors">
                                                        <i class="fa-solid fa-magnifying-glass"></i> Review Listing
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.catalog.listings.approve', $item) }}" onsubmit="return confirmSwal(this, 'Approve & Publish Listing?', 'Approve and publish this listing to the marketplace?', 'question', 'Yes, Approve')">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1 transition-colors" title="Quick Approve">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            @break

                                        @case('capabilities')
                                            <td class="px-5 py-3.5">
                                                <p class="font-bold text-gray-900">{{ $item->account?->display_name }}</p>
                                                <p class="text-[11px] text-indigo-600 font-medium">Capability: {{ $item->capabilityType?->name }}</p>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->appliedBy?->name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->applied_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ route('admin.capabilities.show', $item) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            @break

                                        @case('documents')
                                            <td class="px-5 py-3.5">
                                                <p class="font-bold text-gray-900">{{ $item->supplierAccount?->supplierProfile?->display_name ?? $item->supplierAccount?->display_name }}</p>
                                                <p class="text-[11px] text-gray-500">Doc: {{ $item->documentType?->name ?? $item->custom_name }}</p>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600">—</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ route('admin.suppliers.show', $item->supplierAccount) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            @break

                                        @case('categories')
                                            <td class="px-5 py-3.5 font-bold text-gray-900">{{ $item->name }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('admin.catalog.categories.suggestions.approve', $item) }}">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.catalog.categories.suggestions.reject', $item) }}">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded bg-rose-600 text-white font-semibold text-xs">Reject</button>
                                                    </form>
                                                </div>
                                            </td>
                                            @break

                                        @case('attributes')
                                            <td class="px-5 py-3.5 font-bold text-gray-900">{{ $item->name }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('admin.catalog.attributes.suggestions.approve', $item) }}">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.catalog.attributes.suggestions.reject', $item) }}">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded bg-rose-600 text-white font-semibold text-xs">Reject</button>
                                                    </form>
                                                </div>
                                            </td>
                                            @break

                                        @case('brands')
                                            <td class="px-5 py-3.5 font-bold text-gray-900">{{ $item->name }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('admin.catalog.brands.approve', $item) }}">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.catalog.brands.reject', $item) }}">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 rounded bg-rose-600 text-white font-semibold text-xs">Reject</button>
                                                    </form>
                                                </div>
                                            </td>
                                            @break

                                        @case('role_requests')
                                            <td class="px-5 py-3.5">
                                                <p class="font-bold text-gray-900">{{ $item->account?->display_name }}</p>
                                                <p class="text-[11px] text-gray-500">Requested: {{ $item->role_name ?? $item->requestedRole?->name }}</p>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->requestedBy?->name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ route('admin.access-control.role-requests.show', $item) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            @break

                                        @case('conversions')
                                            <td class="px-5 py-3.5 font-bold text-gray-900">{{ $item->account?->display_name }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->submittedBy?->name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->submitted_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ route('admin.conversions.show', $item) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            @break

                                        @case('reports')
                                            <td class="px-5 py-3.5 font-bold text-gray-900">Report on review #{{ $item->review_id }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->reportedByAccount?->display_name ?? '—' }}</td>
                                            <td class="px-4 py-3.5 text-gray-600">{{ $item->created_at?->format('d M Y') ?? '—' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ route('admin.reviews.reports.show', $item) }}" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </a>
                                            </td>
                                            @break
                                    @endswitch
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $items->links() }}
                    </div>
                @endif
            @endif
        </div>
    @endif

@endsection
