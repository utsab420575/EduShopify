@props(['items'])

<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs" style="color:var(--fe-text-muted);">
        <li>
            <a href="{{ route('home') }}" class="hover:text-[--fe-primary]">Home</a>
        </li>
        @foreach($items as $label => $url)
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-chevron-right text-[9px]" style="color:var(--fe-text-subtle);"></i>
                @if($url && !$loop->last)
                    <a href="{{ $url }}" class="hover:text-[--fe-primary]">{{ $label }}</a>
                @else
                    <span class="font-medium" style="color:var(--fe-text);">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
