@props(['icon' => 'fa-inbox', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center text-center py-16 px-4">
    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4" style="background:var(--theme-primary-soft)">
        <i class="fa-solid {{ $icon }} text-xl" style="color:var(--theme-primary)"></i>
    </div>
    <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
    @if($description)
        <p class="text-sm text-gray-500 mt-1 max-w-sm">{{ $description }}</p>
    @endif
    @if(isset($actions))
        <div class="mt-4 flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
