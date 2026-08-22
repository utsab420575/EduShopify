@props(['title', 'subtitle' => null])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>
