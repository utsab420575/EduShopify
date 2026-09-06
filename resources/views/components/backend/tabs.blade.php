{{--
    Segmented-control tab group — small-radius rectangular container housing
    small-radius rectangular tab buttons (per design.md's tab pattern), used
    in place of the old ad-hoc rounded-full pill markup that was duplicated
    across several buyer index pages. Children are <x-backend.tab> links.
--}}
<div class="inline-flex flex-wrap items-center gap-1 bg-gray-100 rounded-lg p-1 mb-4">
    {{ $slot }}
</div>
