{{-- Basic product identity — Tab 1 (Overview & Media), shown with the photo gallery. See listings/_panel.blade.php for expected variables. --}}
<x-backend.form-card title="Product Information">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Primary Category</dt>
            <dd class="font-bold text-gray-900">{{ $listing->mainCategory?->name ?? '—' }}</dd>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Brand</dt>
            <dd class="font-bold text-gray-900">{{ $listing->brand?->name ?? 'Unbranded / Generic' }}</dd>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">SKU / Model Number</dt>
            <dd class="font-bold font-mono text-gray-900">{{ $listing->sku ?? '—' }}</dd>
        </div>
    </dl>

    @if($listing->short_description)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <dt class="text-xs font-bold text-gray-700 mb-1">Short Summary</dt>
            <dd class="text-xs text-gray-600 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-100">{{ $listing->short_description }}</dd>
        </div>
    @endif

    @if($listing->description)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <dt class="text-xs font-bold text-gray-700 mb-1">Detailed Description</dt>
            <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-3.5 rounded-lg border border-gray-100 prose prose-xs max-w-none">
                {!! nl2br(e($listing->description)) !!}
            </div>
        </div>
    @endif
</x-backend.form-card>
