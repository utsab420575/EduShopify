{{-- Overview + Specifications & Attributes cards. See listing-preview.blade.php for the expected variables. --}}
<x-backend.form-card title="Overview">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
        <div>
            <span class="text-gray-400 block">Type</span>
            <span class="font-semibold text-gray-800 uppercase">{{ $listing->listing_type }}</span>
        </div>
        <div>
            <span class="text-gray-400 block">Category</span>
            <span class="font-semibold text-gray-800">{{ $listing->mainCategory?->name ?? 'None' }}</span>
        </div>
        <div>
            <span class="text-gray-400 block">Price</span>
            <span class="font-semibold text-indigo-700 text-sm">{{ $listing->base_price ? $listing->currency_code . ' ' . number_format($listing->base_price, 2) : 'RFQ' }}</span>
        </div>
        <div>
            <span class="text-gray-400 block">Status</span>
            <x-backend.status-badge :status="$listing->approval_status" />
        </div>
    </div>

    @if($listing->short_description)
        <p class="text-sm text-gray-600 mb-3 font-medium">{{ $listing->short_description }}</p>
    @endif
    <div class="text-sm text-gray-700 whitespace-pre-line">{{ $listing->description }}</div>
</x-backend.form-card>

@if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty())
    <x-backend.form-card title="Specifications & Technical Attributes">
        <div class="space-y-6">
            @foreach($groupedSpecifications as $group)
                <div>
                    <div class="flex items-center gap-2 pb-2 mb-3 border-b border-gray-100">
                        <i class="fa-solid fa-sliders text-xs text-indigo-500"></i>
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">{{ $group['group_name'] }}</h4>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs">
                        @foreach($group['items'] as $item)
                            <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <span>{{ $item->attribute?->name }}</span>
                                    @if($item->attribute?->unit)
                                        <span class="text-gray-400 font-normal">({{ $item->attribute->unit->symbol ?? $item->attribute->unit->name }})</span>
                                    @endif
                                </dt>
                                <dd class="font-semibold text-gray-800 flex items-center gap-1.5">
                                    @if($item->attribute?->input_type === 'color' && $item->attributeValue?->color_hex)
                                        <span class="w-3 h-3 rounded-full border border-gray-300 inline-block" style="background-color: {{ $item->attributeValue->color_hex }}"></span>
                                    @endif
                                    <span>{{ $item->formattedValue() }}</span>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </x-backend.form-card>
@endif
