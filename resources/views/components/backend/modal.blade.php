@props(['id', 'title' => null, 'width' => 'max-w-lg'])

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @open-modal-{{ $id }}.window="open = true"
    @close-modal-{{ $id }}.window="open = false"
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
>
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-gray-900/40"></div>

    <div x-show="open" x-transition class="relative bg-white rounded-xl border border-gray-200 shadow-lg w-full {{ $width }} max-h-[90vh] overflow-y-auto">
        @if($title)
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <div class="p-5">
            {{ $slot }}
        </div>
    </div>
</div>
