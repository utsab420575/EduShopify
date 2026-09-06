@props(['href', 'active' => false])

<a href="{{ $href }}"
   class="text-sm font-medium px-4 py-2 rounded-md transition-colors {{ $active ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
    {{ $slot }}
</a>
