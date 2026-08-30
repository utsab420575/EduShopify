{{-- Technical Specifications & Category Attributes. See listings/_panel.blade.php for expected variables. --}}
<x-backend.form-card title="Technical Specifications & Category Attributes">
    @if($groupedSpecifications->isEmpty())
        <p class="text-xs text-gray-400 py-3 text-center">No specification attributes filled for this listing.</p>
    @else
        <div class="space-y-4">
            @foreach($groupedSpecifications as $group)
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <div class="bg-gray-50/80 px-3.5 py-2 border-b border-gray-100 flex items-center justify-between">
                        <h4 class="text-xs font-bold text-gray-800">{{ $group['group_name'] }}</h4>
                        <span class="text-[10px] text-gray-400 font-medium">{{ count($group['items']) }} specifications</span>
                    </div>
                    <div class="p-3.5 bg-white divide-y divide-gray-50">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                            @foreach($group['items'] as $item)
                                <div class="flex items-start justify-between gap-2 py-1 text-xs">
                                    <span class="text-gray-500 font-medium">{{ $item->attribute?->name ?? 'Attribute #' . $item->attribute_id }}:</span>
                                    <div class="text-right">
                                        @if($item->attribute?->data_type === 'boolean')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->value_boolean ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $item->value_boolean ? 'Yes' : 'No' }}
                                            </span>
                                        @elseif($item->attribute?->data_type === 'multi_select' || is_array($item->value_json))
                                            <div class="flex flex-wrap justify-end gap-1">
                                                @foreach((array) ($item->value_json ?? explode(',', $item->value_text ?? '')) as $tag)
                                                    @if(trim($tag))
                                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-medium border border-indigo-100">{{ trim($tag) }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif($item->attributeValue)
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($item->attributeValue->color_code)
                                                    <span class="w-3 h-3 rounded-full border border-gray-300" style="background-color: {{ $item->attributeValue->color_code }}"></span>
                                                @endif
                                                <span class="font-bold text-gray-900">{{ $item->attributeValue->value }}</span>
                                            </div>
                                        @elseif($item->value_number !== null)
                                            <span class="font-bold text-gray-900">{{ $item->value_number }} {{ $item->attribute?->unit?->symbol }}</span>
                                        @else
                                            <span class="font-bold text-gray-900">{{ $item->value_text ?? '—' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-backend.form-card>
