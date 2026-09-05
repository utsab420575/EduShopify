@props(['icon' => 'fa-inbox', 'title', 'description' => null, 'actionLabel' => null, 'actionUrl' => null])

<div class="flex flex-col items-center justify-center text-center py-16 px-4">
    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4 bg-gray-100">
        <i class="fa-solid {{ $icon }} text-xl text-gray-400"></i>
    </div>
    <p class="text-base font-semibold text-gray-900">{{ $title }}</p>
    @if($description)
        <p class="text-sm mt-1 max-w-sm text-gray-500">{{ $description }}</p>
    @endif
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="mt-4 text-sm font-semibold text-emerald-600">{{ $actionLabel }}</a>
    @endif
</div>
