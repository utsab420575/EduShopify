{{--
    Sortable column header per design.md §0.3.9 — Mandatory Standard: every
    sortable header shows a dual-arrow icon by default (not just the active
    one), swapping to a directional, colored icon once it's the active sort.
    No sort affordance on columns that aren't sortable — those stay plain
    <th> tags in the caller. Resets the table's own page back to 1 on any
    sort change, without disturbing another table sharing the page.
--}}
@props(['column', 'label', 'sortParam', 'directionParam', 'currentSort', 'currentDirection', 'pageParam', 'align' => 'left'])
@php
    $isActive = $currentSort === $column;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery([$sortParam => $column, $directionParam => $nextDirection, $pageParam => null]);
    $icon = $isActive ? ($currentDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort';
    $iconColor = $isActive ? 'text-indigo-600' : 'text-gray-400';
@endphp
<th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider {{ $align === 'right' ? 'text-right' : '' }}">
    <a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-gray-700 transition-colors {{ $align === 'right' ? 'flex-row-reverse' : '' }} {{ $isActive ? 'text-gray-700' : '' }}">
        <span>{{ $label }}</span>
        <i class="fa-solid {{ $icon }} {{ $iconColor }} ml-1 text-[11px]"></i>
    </a>
</th>
