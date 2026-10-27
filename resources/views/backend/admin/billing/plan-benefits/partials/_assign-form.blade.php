{{--
    Plan benefit assignment form — used by the plan-benefits list's modal and
    the standalone page. One row per benefit: tick to assign, typed value.

    $plan, $benefits (rows to show), $assigned (plan rows keyed by benefit id)
    $redirect — optional URL to return to after saving (the list page)
    $formKey  — identifies this form for reopening after a validation error
    $cancelModal / $cancelUrl — optional Cancel button (close that modal / link)
--}}
@php
    $redirect ??= null;
    $formKey ??= 'plan-'.$plan->id;
    // Old input/errors belong to this form only if it was the one submitted.
    $useOld = old('_form') === null || old('_form') === $formKey;
@endphp

<form method="POST" action="{{ route('admin.billing.plan-benefits.update', $plan) }}" class="space-y-4">
    @csrf @method('PUT')
    <input type="hidden" name="_form" value="{{ $formKey }}">
    @if($redirect)
        <input type="hidden" name="_redirect" value="{{ $redirect }}">
    @endif

    <div class="rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Assigned</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Benefit</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-64">Value for this plan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($benefits as $benefit)
                        @php
                            $row = $assigned->get($benefit->id);
                            $isAssigned = filter_var($useOld ? old("benefits.{$benefit->id}.assigned", $row ? 1 : 0) : ($row ? 1 : 0), FILTER_VALIDATE_BOOLEAN);
                        @endphp
                        <tr x-data="{ assigned: {{ $isAssigned ? 'true' : 'false' }} }" :class="assigned ? '' : 'bg-gray-50/60'">
                            <td class="px-4 py-3">
                                <input type="hidden" name="benefits[{{ $benefit->id }}][assigned]" value="0">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="benefits[{{ $benefit->id }}][assigned]" value="1" x-model="assigned"
                                           class="w-4 h-4" style="accent-color:var(--theme-primary)">
                                    <span class="text-xs" :class="assigned ? 'text-emerald-700 font-semibold' : 'text-gray-400'" x-text="assigned ? 'Yes' : 'No'"></span>
                                </label>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium" :class="assigned ? 'text-gray-900' : 'text-gray-500'">
                                    {{ $benefit->name }}
                                    @unless($benefit->is_active)
                                        <span class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600">INACTIVE</span>
                                    @endunless
                                </p>
                                <p class="text-xs text-gray-400"><span class="font-mono">{{ $benefit->code }}</span> · {{ $benefit->typeLabel() }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @include('backend.admin.billing.benefits.partials._value-field', [
                                    'name' => "benefits[{$benefit->id}][value]",
                                    'oldKey' => "benefits.{$benefit->id}.value",
                                    'typeExpr' => "'{$benefit->value_type}'",
                                    'enabledExpr' => 'assigned',
                                    'value' => $row ? $benefit->normalize($row->value) : $benefit->fallbackValue(),
                                    'unit' => $benefit->unit,
                                    'useOld' => $useOld,
                                ])
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $benefit->formatValue($benefit->fallbackValue()) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-gray-500"><i class="fa-solid fa-circle-info mr-1"></i>New subscriptions get these values; existing subscribers keep theirs until renewal or plan change.</p>

    <div class="flex items-center justify-end gap-2">
        @if($cancelModal ?? null)
            <button type="button" @click="$dispatch('close-modal-{{ $cancelModal }}')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
        @elseif($cancelUrl ?? null)
            <a href="{{ $cancelUrl }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
        @endif
        <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Plan Benefits</button>
    </div>
</form>
