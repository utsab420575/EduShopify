@props(['paginator'])

@if($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4 mt-8" aria-label="Pagination">
        <div>
            @if($paginator->onFirstPage())
                <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="fe-focus-ring px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50">Previous</a>
            @endif
        </div>

        <p class="hidden sm:block text-sm text-gray-500">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </p>

        <div>
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="fe-focus-ring px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50">Next</a>
            @else
                <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Next</span>
            @endif
        </div>
    </nav>
@endif
