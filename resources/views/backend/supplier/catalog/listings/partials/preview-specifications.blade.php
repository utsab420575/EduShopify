{{-- Specifications & Technical Attributes card, grouped by attribute group. See listing-preview.blade.php for the expected variables. --}}
@if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty())
    <x-backend.form-card title="Specifications & Technical Attributes">
        <div class="space-y-6">
            @foreach($groupedSpecifications as $group)
                <div>
                    <div class="flex items-center gap-2 pb-2 mb-3 border-b border-gray-100">
                        <i class="fa-solid fa-sliders text-xs text-indigo-500"></i>
                        <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">{{ $group['group_name'] }}</h4>
                    </div>
                    <dl class="space-y-2.5 text-xs">
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
@else
    <p class="text-xs text-gray-400 p-1">No specifications recorded for this listing.</p>
@endif
