{{--
    Buy-box + Quick Info + Supplier cards. Expects $listing, $isSaved from
    the parent controller/view.
--}}
@php($profile = $listing->supplierAccount?->supplierProfile)

{{-- ═══ Buy Box ═══ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4">
        @if($listing->base_price)
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($listing->base_price, 2) }}</span>
                <span class="text-sm font-semibold text-gray-500">{{ $listing->currency_code }}</span>
            </div>
        @else
            <span class="text-xl font-extrabold text-gray-700">Request a Quote</span>
        @endif
        @if($listing->min_order_quantity)
            <p class="text-xs text-gray-500 mt-1">MOQ: {{ rtrim(rtrim((string) $listing->min_order_quantity, '0'), '.') }} {{ $listing->unit?->symbol }}</p>
        @endif

        <dl class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <dt class="text-gray-500">Product Rating</dt>
                <dd class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star text-[10px] {{ $i <= round($listing->product_rating) ? 'text-amber-400' : 'text-gray-200' }}"></i>
                    @endfor
                    <span class="text-gray-600 ml-1">{{ number_format($listing->product_rating, 1) }} ({{ $listing->product_reviews_count }})</span>
                </dd>
            </div>
            <div class="flex items-center justify-between text-xs">
                <dt class="text-gray-500">Supplier Rating</dt>
                <dd class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star text-[10px] {{ $i <= round($profile?->rating ?? 0) ? 'text-amber-400' : 'text-gray-200' }}"></i>
                    @endfor
                    <span class="text-gray-600 ml-1">{{ number_format($profile?->rating ?? 0, 1) }} ({{ $profile?->reviews_count ?? 0 }})</span>
                </dd>
            </div>
        </dl>

        <div class="flex flex-col gap-2 mt-4">
            <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg text-center">Request Quotation</a>
            <form method="POST" action="{{ route('buyer.suppliers.message', $listing->supplierAccount) }}">
                @csrf
                <button type="submit" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Message Supplier</button>
            </form>
            <form method="POST" action="{{ route('buyer.saved-items.toggle') }}">
                @csrf
                <input type="hidden" name="type" value="listing">
                <input type="hidden" name="id" value="{{ $listing->id }}">
                <button type="submit" class="w-full text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-bookmark {{ $isSaved ? 'text-red-500' : 'text-gray-300' }}"></i> {{ $isSaved ? 'Saved' : 'Save' }}
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══ Quick Info ═══ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Quick Info</h3>
    </div>
    <dl class="divide-y divide-gray-50 text-xs">
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-folder w-3.5 text-gray-400 text-center"></i> Category</dt>
            <dd class="font-semibold text-gray-800 text-right max-w-[55%] truncate">{{ $listing->mainCategory?->name ?? '—' }}</dd>
        </div>
        @if($listing->brand)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-regular fa-registered w-3.5 text-gray-400 text-center"></i> Brand</dt>
                <dd class="font-semibold text-gray-800">{{ $listing->brand->name }}</dd>
            </div>
        @endif
        @if($listing->unit)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-ruler w-3.5 text-gray-400 text-center"></i> Unit</dt>
                <dd class="font-semibold text-gray-800">{{ $listing->unit->symbol ?? $listing->unit->name }}</dd>
            </div>
        @endif
        @if($listing->min_order_quantity)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500"><i class="fa-solid fa-boxes-stacked w-3.5 text-gray-400 text-center"></i> Min. Order</dt>
                <dd class="font-semibold text-gray-800">{{ number_format($listing->min_order_quantity, 0) }} units</dd>
            </div>
        @endif
    </dl>
</div>

{{-- ═══ Supplier ═══ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</h3>
    </div>
    <div class="px-5 py-4">
        <a href="{{ route('buyer.suppliers.show', $listing->supplierAccount) }}" class="flex items-center gap-3">
            <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5' }}" class="w-10 h-10 rounded-lg object-contain bg-white border border-gray-100" alt="">
            <span class="text-sm font-medium text-gray-900">{{ $profile?->display_name }}</span>
        </a>
    </div>
</div>
