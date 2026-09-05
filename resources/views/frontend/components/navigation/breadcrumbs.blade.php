@props(['items'])

<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs text-gray-500">
        <li>
            <a href="{{ route('home') }}" class="hover:text-emerald-600">Home</a>
        </li>
        @foreach($items as $label => $url)
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-chevron-right text-[9px] text-gray-400"></i>
                @if($url && !$loop->last)
                    <a href="{{ $url }}" class="hover:text-emerald-600">{{ $label }}</a>
                @else
                    <span class="font-medium text-gray-900">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
