@props(['paginator'])

@if($paginator->hasPages())
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs text-gray-500">
            Showing <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-gray-700">{{ $paginator->total() }}</span> entries
        </p>
        <div class="flex items-center gap-1">
            @if($paginator->onFirstPage())
                <span class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Previous</a>
            @endif

            @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if($page == $paginator->currentPage())
                    <span class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white" style="background:var(--theme-primary)">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">{{ $page }}</a>
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">Next</a>
            @else
                <span class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed">Next</span>
            @endif
        </div>
    </div>
@endif
