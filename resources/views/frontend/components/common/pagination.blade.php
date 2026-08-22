@props(['paginator'])

@if($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4 mt-8" aria-label="Pagination">
        <div>
            @if($paginator->onFirstPage())
                <span class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-300 border" style="border-color:var(--fe-border);">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="fe-focus-ring px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 border hover:bg-slate-50" style="border-color:var(--fe-border-strong);">Previous</a>
            @endif
        </div>

        <p class="hidden sm:block text-sm" style="color:var(--fe-text-muted);">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </p>

        <div>
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="fe-focus-ring px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 border hover:bg-slate-50" style="border-color:var(--fe-border-strong);">Next</a>
            @else
                <span class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-300 border" style="border-color:var(--fe-border);">Next</span>
            @endif
        </div>
    </nav>
@endif
