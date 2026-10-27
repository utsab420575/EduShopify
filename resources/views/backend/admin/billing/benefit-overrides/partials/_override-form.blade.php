{{--
    Create ($override null — benefit chosen, can be prefilled by the
    `prefill-override` event) or edit ($override — benefit fixed) a supplier
    benefit override.

    Shows the benefit's current value and source, a typed value input
    (Enable/Disable toggle for yes/no benefits), quick duration presets and a
    plain-language summary. "_form" tells the page which modal to reopen after
    a validation error. $current: benefit id → {type, unit, name, label, source, value}.
--}}
@php
    $override ??= null;
    $formKey = $override ? 'edit-'.$override->id : 'create';
    $isThisForm = old('_form') === $formKey;
    $val = fn ($key, $default = null) => $isThisForm ? old($key, $default) : $default;
    $err = fn ($key) => $isThisForm ? $errors->first($key) : null;

    $overrideValue = null;
    if ($override) {
        $typed = $override->benefit->normalize($override->value);
        $overrideValue = is_bool($typed) ? ($typed ? '1' : '0') : (string) $typed;
    }

    $state = [
        'benefitId' => (string) $val('subscription_benefit_id', $override?->subscription_benefit_id ?? ''),
        'value' => (string) $val('value', $overrideValue ?? ''),
        'startsAt' => (string) $val('starts_at', $override?->starts_at?->format('Y-m-d\TH:i') ?? ''),
        'expiresAt' => (string) $val('expires_at', $override?->expires_at?->format('Y-m-d\TH:i') ?? ''),
        'fixedBenefit' => $override ? [
            'type' => $override->benefit->value_type,
            'unit' => (string) $override->benefit->unit,
            'name' => $override->benefit->name,
            'label' => $current[$override->subscription_benefit_id]['label'] ?? null,
            'source' => $current[$override->subscription_benefit_id]['source'] ?? null,
        ] : null,
    ];
    $inputClass = 'focus-accent w-full px-3 py-2.5 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400';
@endphp

<form method="POST"
      action="{{ $override ? route('admin.billing.benefit-overrides.update', [$account, $override]) : route('admin.billing.benefit-overrides.store', $account) }}"
      x-data="overrideForm(@js($state), @js($current))"
      @if(! $override) @prefill-override.window="prefill($event.detail)" @endif
      class="space-y-5">
    @csrf
    @if($override) @method('PUT') @endif
    <input type="hidden" name="_form" value="{{ $formKey }}">

    {{-- 1. Benefit --}}
    @if($override)
        <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3">
            <p class="text-sm font-semibold text-gray-900">{{ $override->benefit->name }}</p>
            <p class="text-xs text-gray-500"><span class="font-mono">{{ $override->benefit->code }}</span> · {{ $override->benefit->typeLabel() }}</p>
        </div>
    @else
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Benefit <span class="text-red-500">*</span></label>
            <select name="subscription_benefit_id" x-model="benefitId" @change="useCurrentValue()" required
                    class="{{ $inputClass }} bg-white {{ $err('subscription_benefit_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <option value="">Choose a benefit…</option>
                @foreach($benefits as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
            @if($err('subscription_benefit_id')) <p class="mt-1 text-xs text-red-600">{{ $err('subscription_benefit_id') }}</p> @endif
        </div>
    @endif

    {{-- Current value --}}
    <div x-show="benefit && benefit.label" x-cloak class="flex items-center gap-2 text-xs rounded-lg bg-sky-50 border border-sky-100 text-sky-800 px-3 py-2">
        <i class="fa-solid fa-circle-info"></i>
        <span>Currently <strong x-text="benefit?.label"></strong> <span class="text-sky-600" x-text="'— from ' + (benefit?.source ?? '')"></span></span>
    </div>

    {{-- 2. Value --}}
    <div x-show="type" x-cloak>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Value <span class="text-red-500">*</span></label>

        {{-- Yes / No: segmented toggle --}}
        <div x-show="type === 'boolean'" class="grid grid-cols-2 gap-2">
            <input type="hidden" name="value" :value="value" :disabled="type !== 'boolean'">
            <button type="button" @click="value = '1'"
                    class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-semibold transition"
                    :class="value === '1' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-500' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                <i class="fa-solid fa-circle-check"></i> Enable
            </button>
            <button type="button" @click="value = '0'"
                    class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-semibold transition"
                    :class="value === '0' ? 'border-red-400 bg-red-50 text-red-700 ring-1 ring-red-400' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                <i class="fa-solid fa-ban"></i> Disable
            </button>
        </div>

        <div x-show="type === 'integer' || type === 'decimal'" class="flex items-center gap-2">
            <input type="number" name="value" x-model="value" :step="type === 'integer' ? 1 : 'any'" :disabled="type !== 'integer' && type !== 'decimal'"
                   placeholder="0" class="{{ $inputClass }} {{ $err('value') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            <span class="text-sm text-gray-500 shrink-0" x-show="benefit?.unit" x-text="benefit?.unit"></span>
        </div>

        <input type="text" name="value" x-model="value" x-show="type === 'text'" :disabled="type !== 'text'" maxlength="1000"
               placeholder="Value" class="{{ $inputClass }} {{ $err('value') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">

        @if($err('value')) <p class="mt-1 text-xs text-red-600">{{ $err('value') }}</p> @endif
        <p class="mt-1.5 text-xs text-gray-400">Replaces the subscription value while active — Disable or 0 takes the benefit away.</p>
    </div>

    {{-- 3. Period --}}
    <div x-show="type" x-cloak class="space-y-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-sm font-medium text-gray-700 mr-1">Duration</span>
            <template x-for="preset in presets" :key="preset.label">
                <button type="button" @click="applyPreset(preset)"
                        class="text-xs font-medium px-2.5 py-1 rounded-full border transition"
                        :class="activePreset === preset.label ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        x-text="preset.label"></button>
            </template>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Starts <span class="text-gray-400 font-normal">(empty = now)</span></label>
                <input type="datetime-local" name="starts_at" x-model="startsAt" @input="activePreset = null"
                       class="{{ $inputClass }} {{ $err('starts_at') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @if($err('starts_at')) <p class="mt-1 text-xs text-red-600">{{ $err('starts_at') }}</p> @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Expires <span class="text-gray-400 font-normal">(empty = never)</span></label>
                <input type="datetime-local" name="expires_at" x-model="expiresAt" @input="activePreset = null"
                       class="{{ $inputClass }} {{ $err('expires_at') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @if($err('expires_at')) <p class="mt-1 text-xs text-red-600">{{ $err('expires_at') }}</p> @endif
            </div>
        </div>
    </div>

    {{-- 4. Reason --}}
    <div x-show="type" x-cloak>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason</label>
        <textarea name="reason" rows="2" maxlength="1000" placeholder="e.g. Launch promotion agreed with sales — kept in the override history."
                  class="{{ $inputClass }} border-gray-300">{{ $val('reason', $override?->reason) }}</textarea>
    </div>

    {{-- Summary --}}
    <div x-show="type && value !== ''" x-cloak class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 text-sm text-gray-700">
        <i class="fa-solid fa-list-check text-gray-400 mr-1.5"></i>
        <span class="font-semibold" x-text="benefit?.name"></span> →
        <span class="font-semibold" x-text="displayValue()"></span>,
        <span x-text="periodText()"></span>.
    </div>

    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
        <button type="button" @click="$dispatch('close-modal-{{ $override ? 'override-edit-'.$override->id : 'override-create' }}')"
                class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" :disabled="! type || value === ''" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg disabled:opacity-50">
            {{ $override ? 'Save Override' : 'Apply Override' }}
        </button>
    </div>
</form>

@once
@push('scripts')
<script>
    function overrideForm(state, current) {
        const pad = (n) => String(n).padStart(2, '0');
        const toInput = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        const fmt = (s) => s ? new Date(s).toLocaleString(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : null;

        return {
            benefitId: state.benefitId,
            value: state.value,
            startsAt: state.startsAt,
            expiresAt: state.expiresAt,
            fixedBenefit: state.fixedBenefit,
            current: current,
            activePreset: null,
            presets: [
                { label: 'No expiry', days: null },
                { label: '7 days', days: 7 },
                { label: '30 days', days: 30 },
                { label: '90 days', days: 90 },
                { label: '1 year', days: 365 },
            ],

            get benefit() { return this.fixedBenefit ?? this.current[this.benefitId] ?? null; },
            get type() { return this.benefit?.type ?? ''; },

            /** Opened from a benefit row: preselect it and start from its current value. */
            prefill(detail) {
                this.benefitId = detail.id ? String(detail.id) : '';
                this.useCurrentValue();
                this.startsAt = '';
                this.expiresAt = '';
                this.activePreset = null;
            },
            useCurrentValue() {
                this.value = this.current[this.benefitId]?.value ?? '';
            },
            applyPreset(preset) {
                this.activePreset = preset.label;
                if (preset.days === null) { this.expiresAt = ''; return; }
                const from = this.startsAt ? new Date(this.startsAt) : new Date();
                from.setDate(from.getDate() + preset.days);
                this.expiresAt = toInput(from);
            },
            displayValue() {
                if (this.type === 'boolean') return this.value === '1' ? 'Enabled' : 'Disabled';
                return [this.value, this.benefit?.unit].filter(Boolean).join(' ');
            },
            periodText() {
                const start = fmt(this.startsAt) ? 'from ' + fmt(this.startsAt) : 'from now';
                return start + (fmt(this.expiresAt) ? ' until ' + fmt(this.expiresAt) : ' with no expiry');
            },
        };
    }
</script>
@endpush
@endonce
