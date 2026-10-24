{{-- How the banner fills the public hero — bound to bannerCropper()'s `fit`. --}}
@php
    $fitOptions = [
        'cover' => ['Cover', 'fa-crop-simple', 'Fills the whole area; edges may be cut off.'],
        'contain' => ['Contain', 'fa-expand', 'Shows the whole image; blurred edges fill any gap.'],
        'fill' => ['Fill', 'fa-arrows-left-right-to-line', 'Stretches to the area’s width and height; may distort.'],
    ];
@endphp
<div>
    <p class="text-xs font-medium text-gray-700 mb-1.5">How should the banner fit the hero area?</p>
    <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Banner fit">
        @foreach($fitOptions as $value => [$label, $icon, $hint])
            <button type="button" role="radio" :aria-checked="fit === '{{ $value }}'" @click="fit = '{{ $value }}'"
                    class="flex flex-col items-start gap-0.5 text-left px-3 py-2 rounded-lg border transition cursor-pointer"
                    :class="fit === '{{ $value }}' ? 'border-violet-500 bg-violet-50 ring-1 ring-violet-500' : 'border-gray-200 hover:border-gray-300 bg-white'">
                <span class="flex items-center gap-1.5 text-xs font-semibold"
                      :class="fit === '{{ $value }}' ? 'text-violet-700' : 'text-gray-800'">
                    <i class="fa-solid {{ $icon }}"></i> {{ $label }}
                    @if($value === 'cover')
                        <span class="text-[9px] font-medium px-1.5 py-px rounded-full bg-gray-100 text-gray-500">Default</span>
                    @endif
                </span>
                <span class="text-[11px] text-gray-500 leading-snug hidden sm:block">{{ $hint }}</span>
            </button>
        @endforeach
    </div>
    <p class="text-[11px] text-gray-500 mt-1.5 sm:hidden"
       x-text="{ cover: @js($fitOptions['cover'][2]), contain: @js($fitOptions['contain'][2]), fill: @js($fitOptions['fill'][2]) }[fit]"></p>
</div>
