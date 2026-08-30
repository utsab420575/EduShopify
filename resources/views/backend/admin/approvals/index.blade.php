@extends('backend.layouts.admin')

@section('title', 'Approval Center')
@section('breadcrumb', 'Platform Governance / Approval Center')

@section('body')

<<<<<<< HEAD
    <x-backend.page-header title="Approval Center" :subtitle="$activeQueue ? $activeQueue['label'] . ' — items awaiting your decision.' : 'Review and moderate platform items awaiting administrative decision.'" />
=======
    <x-backend.page-header title="Approval Center" subtitle="Review and moderate platform items awaiting administrative decision." />
>>>>>>> 65fab0cae4ad61d182eed68d9dbf650abbef22f7

    @if(empty($queues))
        <x-backend.empty-state icon="fa-clipboard-check" title="Nothing to review" description="You don't have any pending approval queues assigned." />
    @else
<<<<<<< HEAD
        @php($tab = $activeKey)

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
                            @elseif($tab === 'custom_attribute_values')
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Attribute</th>
                                    <th class="px-4 py-3 font-semibold">Custom Value</th>
                                    <th class="px-4 py-3 font-semibold">Used By</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
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
                        <tbody class="divide-y divide-gray-100" id="approval-tbody">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50/60 transition-colors"
                                    @if($tab === 'capabilities') data-approval-row="capabilities-{{ $item->id }}" @endif
                                    @if($tab === 'listings') data-approval-row="listings-{{ $item->id }}" @endif>
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
                                                    <button type="button"
                                                            @click="window.dispatchEvent(new CustomEvent('open-ajax-modal-approval-review', { detail: { url: '{{ route('admin.catalog.listings.panel', $item) }}', rowId: 'listings-{{ $item->id }}', queueKey: 'listings', showUrl: '{{ route('admin.catalog.listings.show', $item) }}' } }))"
                                                            class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center gap-1 transition-colors">
                                                        <i class="fa-solid fa-magnifying-glass"></i> Review Listing
                                                    </button>
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
                                                <button type="button"
                                                        @click="window.dispatchEvent(new CustomEvent('open-ajax-modal-approval-review', { detail: { url: '{{ route('admin.capabilities.panel', $item) }}', rowId: 'capabilities-{{ $item->id }}', queueKey: 'capabilities', showUrl: '{{ route('admin.capabilities.show', $item) }}' } }))"
                                                        class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold inline-flex items-center gap-1">
                                                    Review &rarr;
                                                </button>
                                            </td>
                                            @break

                                        @case('documents')
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                                        @php($ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION)))
                                                        @if($ext === 'pdf')
                                                            <i class="fa-solid fa-file-pdf text-red-500 text-lg"></i>
                                                        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                                                            <i class="fa-solid fa-file-image text-blue-500 text-lg"></i>
                                                        @else
                                                            <i class="fa-solid fa-file-lines text-indigo-500 text-lg"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-900 line-clamp-1">
                                                            {{ $item->documentType?->name ?? ($item->custom_name ?: 'Compliance Document') }}
                                                        </p>
                                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-500">
                                                            <span class="font-medium text-gray-700 truncate max-w-[200px]" title="{{ $item->original_name }}">{{ $item->original_name }}</span>
                                                            @if($item->file_size_kb)
                                                                <span>&bull; {{ $item->file_size_kb }} KB</span>
                                                            @endif
                                                            @if($item->expires_at)
                                                                <span class="text-amber-600 font-medium">&bull; Exp: {{ $item->expires_at->format('d M Y') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                @if($item->supplierAccount)
                                                    <a href="{{ route('admin.suppliers.show', $item->supplierAccount) }}" class="font-bold text-gray-800 hover:text-indigo-600 transition-colors flex items-center gap-1">
                                                        {{ $item->supplierAccount->supplierProfile?->display_name ?? $item->supplierAccount->display_name }}
                                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400"></i>
                                                    </a>
                                                    <span class="text-[10px] text-gray-400 font-mono">{{ $item->supplierAccount->account_number }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-500 text-[11px]">
                                                {{ $item->created_at?->format('d M Y, h:i A') ?? '—' }}
                                                @if($item->uploadedBy)
                                                    <span class="block text-gray-400 text-[10px]">by {{ $item->uploadedBy->name }}</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    {{-- View / Inspect Modal Button --}}
                                                    <button type="button"
                                                            @click="$dispatch('open-modal-doc-preview-{{ $item->id }}')"
                                                            class="px-2.5 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center gap-1 transition-colors"
                                                            title="View & Preview Document">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </button>

                                                    {{-- Quick Approve Form --}}
                                                    <form method="POST"
                                                          action="{{ route('admin.documents.verify', $item) }}"
                                                          onsubmit="return confirmSwal(this, 'Approve Document?', 'Verify and approve {{ addslashes($item->documentType?->name ?? $item->original_name) }} for this supplier?', 'question', 'Yes, Approve')">
                                                        @csrf
                                                        <button type="submit"
                                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1 transition-colors"
                                                                title="Approve Document">
                                                            <i class="fa-solid fa-check"></i> Approve
                                                        </button>
                                                    </form>

                                                    {{-- Reject Button (Opens Modal) --}}
                                                    <button type="button"
                                                            @click="$dispatch('open-modal-reject-doc-{{ $item->id }}')"
                                                            class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold flex items-center gap-1 transition-colors"
                                                            title="Reject Document">
                                                        <i class="fa-solid fa-xmark"></i> Reject
                                                    </button>
                                                </div>
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

                                        @case('custom_attribute_values')
                                            <td class="px-5 py-3.5 font-bold text-gray-900">{{ $item->attribute_name }}</td>
                                            <td class="px-4 py-3.5">
                                                <span class="font-medium text-gray-800">{{ $item->custom_value }}</span>
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-600">
                                                {{ $item->usage_count }} {{ Str::plural('listing', $item->usage_count) }}
                                            </td>
                                            <td class="px-4 py-3.5">
                                                @if($item->status === 'ignored')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">Ignored</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('admin.catalog.custom-attribute-values.approve') }}"
                                                          onsubmit="return confirmSwal(this, 'Promote to Official Value?', 'Make &quot;{{ addslashes($item->custom_value) }}&quot; a standard selectable option for {{ addslashes($item->attribute_name) }}? This updates all {{ $item->usage_count }} listing(s) currently using it.', 'question', 'Yes, Promote')">
                                                        @csrf
                                                        <input type="hidden" name="attribute_id" value="{{ $item->attribute_id }}">
                                                        <input type="hidden" name="custom_value" value="{{ $item->custom_value }}">
                                                        <button type="submit" class="px-3 py-1 rounded bg-emerald-600 text-white font-semibold text-xs">
                                                            {{ $item->status === 'ignored' ? 'Promote' : 'Approve' }}
                                                        </button>
                                                    </form>
                                                    @if($item->status !== 'ignored')
                                                        <form method="POST" action="{{ route('admin.catalog.custom-attribute-values.ignore') }}">
                                                            @csrf
                                                            <input type="hidden" name="attribute_id" value="{{ $item->attribute_id }}">
                                                            <input type="hidden" name="custom_value" value="{{ $item->custom_value }}">
                                                            <button type="submit" class="px-3 py-1 rounded bg-gray-200 text-gray-700 font-semibold text-xs">Ignore</button>
                                                        </form>
                                                    @endif
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

        {{-- Document Modals for Verification & Preview --}}
        @if($tab === 'documents')
            @foreach($items as $item)
                @php($fileUrl = asset('storage/' . $item->file_path))
                @php($ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION)))

                {{-- Preview & Review Modal --}}
                <x-backend.modal :id="'doc-preview-'.$item->id" :title="'Review Document: ' . ($item->documentType?->name ?? $item->custom_name ?? $item->original_name)" width="max-w-4xl">
                    <div class="space-y-4">
                        {{-- Metadata Banner --}}
                        <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Supplier</span>
                                <span class="font-bold text-gray-900 truncate block">{{ $item->supplierAccount?->supplierProfile?->display_name ?? $item->supplierAccount?->display_name ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Document Type</span>
                                <span class="font-semibold text-indigo-700 block">{{ $item->documentType?->name ?? ($item->custom_name ?: 'Custom Document') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">File Details</span>
                                <span class="font-medium text-gray-800 block truncate" title="{{ $item->original_name }}">{{ $item->original_name }} ({{ $item->file_size_kb }} KB)</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-semibold">Uploaded Date</span>
                                <span class="font-medium text-gray-800 block">{{ $item->created_at?->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>

                        {{-- Document Viewer / Preview --}}
                        <div class="rounded-xl border border-gray-200 bg-gray-100 overflow-hidden flex items-center justify-center min-h-[420px] max-h-[550px]">
                            @if($ext === 'pdf')
                                <iframe src="{{ $fileUrl }}" class="w-full h-[520px] bg-white" frameborder="0"></iframe>
                            @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                <div class="p-4 flex items-center justify-center w-full h-full overflow-auto bg-gray-900/5">
                                    <img src="{{ $fileUrl }}" alt="{{ $item->original_name }}" class="max-h-[500px] rounded-lg shadow-md object-contain">
                                </div>
                            @else
                                <div class="p-10 text-center">
                                    <i class="fa-solid fa-file-arrow-down text-5xl text-indigo-400 mb-3"></i>
                                    <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $item->original_name }}</h4>
                                    <p class="text-xs text-gray-500 mb-4">Preview is not supported for {{ strtoupper($ext) }} files. Please download or view in a separate application.</p>
                                    <a href="{{ $fileUrl }}" target="_blank" download class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 inline-flex items-center gap-1.5 transition-colors">
                                        <i class="fa-solid fa-download"></i> Download File
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Footer Actions inside Preview Modal --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2">
                                <a href="{{ $fileUrl }}" target="_blank" class="text-xs font-medium text-gray-600 hover:text-indigo-600 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Open in new tab
                                </a>
                                @if($item->expires_at)
                                    <span class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md font-medium">
                                        Expires: {{ $item->expires_at->format('d M Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                {{-- Quick Approve Button --}}
                                <form method="POST"
                                      action="{{ route('admin.documents.verify', $item) }}"
                                      onsubmit="return confirmSwal(this, 'Approve Document?', 'Verify and approve this document for {{ addslashes($item->supplierAccount?->display_name ?? 'supplier') }}?', 'question', 'Yes, Approve')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                                        <i class="fa-solid fa-check"></i> Approve Document
                                    </button>
                                </form>

                                {{-- Reject Trigger --}}
                                <button type="button"
                                        @click="open = false; setTimeout(() => $dispatch('open-modal-reject-doc-{{ $item->id }}'), 150)"
                                        class="px-4 py-2 rounded-lg bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                    <i class="fa-solid fa-xmark"></i> Reject Document
                                </button>

                                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </x-backend.modal>

                {{-- Reject Modal --}}
                <x-backend.modal :id="'reject-doc-'.$item->id" :title="'Reject Document: ' . ($item->documentType?->name ?? $item->custom_name ?? $item->original_name)" width="max-w-md">
                    <form method="POST" action="{{ route('admin.documents.reject', $item) }}" class="space-y-4">
                        @csrf
                        <p class="text-xs text-gray-500">
                            Please provide a clear reason why this compliance document is being rejected. The supplier will be notified to upload a revised document.
                        </p>
                        <x-backend.textarea name="reason" label="Rejection Reason" placeholder="e.g. The uploaded trade license is expired or illegible..." required rows="3" />

                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button type="button" @click="open = false" class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-xmark"></i> Confirm Rejection
                            </button>
                        </div>
                    </form>
                </x-backend.modal>
            @endforeach
        @endif

        <x-backend.ajax-modal id="approval-review" width="max-w-3xl" />
=======
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
>>>>>>> 65fab0cae4ad61d182eed68d9dbf650abbef22f7
    @endif

@endsection

@push('scripts')
<script>
    (function () {
        function decrementBadge(el) {
            if (! el) return;
            const next = (parseInt(el.textContent, 10) || 0) - 1;
            if (next <= 0) {
                el.remove();
            } else {
                el.textContent = next;
            }
        }

        window.addEventListener('approval-item-resolved', (e) => {
            const { rowId, queueKey } = e.detail || {};
            if (! rowId) return;

            const row = document.querySelector(`[data-approval-row="${CSS.escape(rowId)}"]`);
            if (row) row.remove();

            if (queueKey) {
                decrementBadge(document.getElementById('sidebar-approval-badge-' + queueKey));
            }
            decrementBadge(document.getElementById('sidebar-approval-badge-total'));

            const tbody = document.getElementById('approval-tbody');
            if (tbody && tbody.children.length === 0) {
                const colspan = tbody.closest('table')?.querySelector('thead tr')?.children.length || 4;
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="${colspan}" class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 mb-1">Queue is empty</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">There are currently no items pending review in this queue.</p>
                </td>`;
                tbody.appendChild(tr);
            }
        });
    })();
</script>
@endpush
