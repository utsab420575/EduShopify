{{-- Specifications tab: flat attribute/value list, unchanged content from the previous single-page layout. Expects $listing. --}}
@if($listing->attributeValues->isNotEmpty())
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        @foreach($listing->attributeValues as $value)
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">{{ $value->attribute?->name }}</dt>
                <dd class="text-gray-900 font-medium">{{ $value->resolvedValue() }}</dd>
            </div>
        @endforeach
    </dl>
@else
    <p class="text-sm text-gray-400">No specifications listed for this product.</p>
@endif
