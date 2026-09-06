{{-- Supplier Organization + Listing Quick Stats + Moderation Timeline. See listings/_panel.blade.php for expected variables. --}}
@php
    $timelineSteps = [
        [
            'icon'  => 'fa-circle-plus',
            'label' => 'Listing Created',
            'sub'   => $listing->created_at?->format('d M Y, h:i A'),
            'done'  => true,
            'alert' => null,
        ],
        [
            'icon'  => 'fa-paper-plane',
            'label' => 'Submitted for Review',
            'sub'   => $listing->setup_completed_at?->format('d M Y, h:i A'),
            'done'  => !in_array($listing->approval_status, ['draft']),
            'alert' => null,
        ],
        [
            'icon'  => 'fa-circle-check',
            'label' => 'Approved by Admin',
            'sub'   => $listing->approved_at?->format('d M Y, h:i A'),
            'done'  => $listing->approval_status === 'approved',
            'alert' => $listing->approval_status === 'rejected' ? 'Rejected' : null,
        ],
        [
            'icon'  => 'fa-globe',
            'label' => 'Published & Live',
            'sub'   => $listing->published_at?->format('d M Y, h:i A'),
            'done'  => (bool) $listing->published_at,
            'alert' => null,
        ],
    ];
    $timelineTotal = count($timelineSteps);
@endphp

{{-- ═══ CARD 1: Supplier Organization ═══ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier Organization</h3>
    </div>
    @if($listing->supplierAccount)
        @php($profile = $listing->supplierAccount->supplierProfile)
        <div class="px-4 py-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-extrabold text-sm shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($profile?->display_name ?? $listing->supplierAccount->display_name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $profile?->display_name ?? $listing->supplierAccount->display_name }}</p>
                    <p class="text-[11px] text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-gray-400 text-[10px]"></i>
                        {{ $profile?->country?->name ?? 'International Supplier' }}
                    </p>
                </div>
            </div>

            <dl class="space-y-1.5 text-xs">
                <div class="flex justify-between items-center py-1 border-b border-gray-50">
                    <dt class="text-gray-400">Account ID</dt>
                    <dd class="font-mono font-semibold text-gray-700">#{{ $listing->supplierAccount->id }}</dd>
                </div>
                <div class="flex justify-between items-center py-1">
                    <dt class="text-gray-400">Account Status</dt>
                    <dd>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-circle text-[5px]"></i> {{ ucfirst($listing->supplierAccount->status ?? 'Active') }}
                        </span>
                    </dd>
                </div>
            </dl>

            <a href="{{ route('admin.suppliers.show', $listing->supplierAccount) }}"
               class="block w-full text-center text-xs font-semibold py-2 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors shadow-sm">
                View Supplier Profile &rarr;
            </a>
        </div>
    @else
        <p class="px-4 py-4 text-xs text-gray-400">No supplier account linked.</p>
    @endif
</div>

{{-- ═══ CARD 2: Listing Quick Stats ═══ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Listing Quick Stats</h3>
    </div>
    <dl class="divide-y divide-gray-50 text-xs">
        <div class="flex items-center justify-between px-4 py-2.5">
            <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-hashtag w-3 text-center text-gray-400"></i> Listing ID</dt>
            <dd class="font-mono font-semibold text-gray-700">{{ $listing->listing_number }}</dd>
        </div>
        <div class="flex items-center justify-between px-4 py-2.5">
            <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-tag w-3 text-center text-gray-400"></i> Type</dt>
            <dd>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 uppercase">
                    {{ $listing->listing_type }}
                </span>
            </dd>
        </div>
        @if($listing->base_price)
            <div class="flex items-center justify-between px-4 py-2.5">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-dollar-sign w-3 text-center text-gray-400"></i> Base Price</dt>
                <dd class="font-bold text-indigo-700">{{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}</dd>
            </div>
        @else
            <div class="flex items-center justify-between px-4 py-2.5">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-dollar-sign w-3 text-center text-gray-400"></i> Pricing</dt>
                <dd class="text-gray-500 italic text-[11px]">Negotiable / RFQ</dd>
            </div>
        @endif
        @if($listing->min_order_quantity)
            <div class="flex items-center justify-between px-4 py-2.5">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-boxes-stacked w-3 text-center text-gray-400"></i> Min. Order</dt>
                <dd class="font-semibold text-gray-700">{{ number_format($listing->min_order_quantity, 0) }} units</dd>
            </div>
        @endif
        @if($listing->variants->isNotEmpty())
            <div class="flex items-center justify-between px-4 py-2.5">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-cubes w-3 text-center text-gray-400"></i> Variants</dt>
                <dd class="font-semibold text-gray-700">{{ $listing->variants->count() }} variant{{ $listing->variants->count() !== 1 ? 's' : '' }}</dd>
            </div>
        @endif
        <div class="flex items-center justify-between px-4 py-2.5">
            <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-images w-3 text-center text-gray-400"></i> Photos</dt>
            <dd class="font-semibold text-gray-700">{{ $listing->getMedia('gallery')->count() }} uploaded</dd>
        </div>
        @if($listing->approvedBy)
            <div class="flex items-center justify-between px-4 py-2.5">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-user-shield w-3 text-center text-gray-400"></i> Approved By</dt>
                <dd class="font-semibold text-gray-700">{{ $listing->approvedBy->name }}</dd>
            </div>
        @endif
        @if($listing->rejection_reason && $listing->approval_status === 'rejected')
            <div class="px-4 py-3 bg-rose-50 border-t border-rose-100">
                <p class="text-[10px] font-bold text-rose-700 uppercase tracking-wider mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Rejection Reason</p>
                <p class="text-xs text-rose-700 leading-relaxed">{{ $listing->rejection_reason }}</p>
            </div>
        @endif
    </dl>
</div>

{{-- ═══ CARD 3: Moderation Timeline ═══ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Moderation Timeline</h3>
    </div>
    <div class="px-4 py-4">
        <ol class="space-y-0 relative">
            @foreach($timelineSteps as $stepIdx => $tStep)
                <li class="flex gap-3 {{ $stepIdx < $timelineTotal - 1 ? 'pb-4' : '' }} relative">
                    @if($stepIdx < $timelineTotal - 1)
                        <div class="absolute left-[13px] top-7 bottom-0 w-0.5 {{ $tStep['done'] ? 'bg-indigo-200' : 'bg-gray-200' }}"></div>
                    @endif
                    <div class="flex-shrink-0 z-10 w-7 h-7 rounded-full flex items-center justify-center mt-0.5
                        {{ $tStep['alert'] ? 'bg-rose-100' : ($tStep['done'] ? 'bg-indigo-100' : 'bg-gray-100') }}">
                        <i class="fa-solid {{ $tStep['icon'] }} text-[11px]
                            {{ $tStep['alert'] ? 'text-rose-500' : ($tStep['done'] ? 'text-indigo-600' : 'text-gray-400') }}"></i>
                    </div>
                    <div class="flex-1 min-w-0 pt-1">
                        <p class="text-xs font-semibold {{ $tStep['alert'] ? 'text-rose-600' : ($tStep['done'] ? 'text-gray-900' : 'text-gray-400') }}">
                            {{ $tStep['alert'] ?? $tStep['label'] }}
                        </p>
                        @if($tStep['sub'])
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $tStep['sub'] }}</p>
                        @elseif(!$tStep['done'])
                            <p class="text-[11px] text-gray-300 mt-0.5">Pending</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
