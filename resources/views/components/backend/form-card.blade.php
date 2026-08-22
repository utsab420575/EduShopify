@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 p-5']) }}>
    @if($title)
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
            @if($description)
                <p class="text-xs text-gray-500 mt-1">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
