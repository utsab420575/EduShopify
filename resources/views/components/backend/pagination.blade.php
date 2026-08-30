@props(['paginator'])

@if($paginator && $paginator->total() > 0)
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs text-gray-500">
            Showing <span class="font-semibold text-gray-900">{{ $paginator->firstItem() ?? 1 }}</span>
            to <span class="font-semibold text-gray-900">{{ $paginator->lastItem() ?? $paginator->total() }}</span>
            of <span class="font-semibold text-gray-900">{{ $paginator->total() }}</span> {{ Str::plural('entry', $paginator->total()) }}
        </p>
        <div class="flex items-center gap-1">
            {{-- Previous Page Link --}}
            @if($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed select-none">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i> Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i> Previous
                </a>
            @endif

            {{-- Page Number Elements --}}
            @if($paginator->hasPages())
                @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span class="min-w-[32px] text-center text-xs font-semibold px-2.5 py-1.5 rounded-lg text-white shadow-xs" style="background:var(--theme-primary)">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="min-w-[32px] text-center text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">{{ $page }}</a>
                    @endif
                @endforeach
            @else
                <span class="min-w-[32px] text-center text-xs font-semibold px-2.5 py-1.5 rounded-lg text-white shadow-xs" style="background:var(--theme-primary)">1</span>
            @endif

            {{-- Next Page Link --}}
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                    Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed select-none">
                    Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>
    </div>
@endif
