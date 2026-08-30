{{--
    Shared duplicate-check body for the "approve/promote a custom attribute
    value" modal. Expects: attributeName, attributeId, customValue,
    usageCount, existing (collection of existing value strings for this
    attribute). Highlights any existing value that looks similar to the
    pending one so a near-duplicate (different spelling/casing) is visible
    before the admin confirms, not just exact-matched away silently.
--}}
<div class="space-y-4">
    <div>
        <p class="text-xs text-gray-500 mb-1">Attribute</p>
        <p class="text-sm font-bold text-gray-900">{{ $attributeName }}</p>
    </div>

    <div>
        <p class="text-xs text-gray-500 mb-2">Existing values for this attribute</p>
        @if($existing->isEmpty())
            <p class="text-xs text-gray-400">No existing values yet — this would be the first.</p>
        @else
            <div class="flex flex-wrap gap-1.5" x-data="{ pending: {{ Js::from(strtolower($customValue)) }} }">
                @foreach($existing as $val)
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full border"
                          x-data="{ existingVal: {{ Js::from(strtolower($val)) }} }"
                          :class="existingVal.includes(pending) || pending.includes(existingVal) ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-gray-50 text-gray-600 border-gray-200'"
                          :title="existingVal.includes(pending) || pending.includes(existingVal) ? 'Looks similar to the value being promoted' : ''">
                        {{ $val }}
                    </span>
                @endforeach
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Amber chips look similar to the value below — check they're not the same thing spelled differently.</p>
        @endif
    </div>

    <div class="p-3 rounded-lg border border-indigo-200 bg-indigo-50/60">
        <p class="text-[10px] font-semibold text-indigo-500 uppercase mb-1">About to promote</p>
        <p class="text-sm font-bold text-indigo-900">{{ $customValue }}</p>
        <p class="text-xs text-indigo-600 mt-0.5">Used by {{ $usageCount }} {{ Str::plural('listing', $usageCount) }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.catalog.custom-attribute-values.approve') }}" class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-gray-100">
    @csrf
    <input type="hidden" name="attribute_id" value="{{ $attributeId }}">
    <input type="hidden" name="custom_value" value="{{ $customValue }}">
    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-medium border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Confirm Promote</button>
</form>
