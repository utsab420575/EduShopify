{{--
    Shared by create/edit. $locked: in use → code and value type can't change.
    The code follows the name (benefitCodeFromName) until it's typed by hand;
    an existing code that matches its name keeps following it.
--}}
@php
    $locked ??= false;
    $initialName = old('name', $benefit->name);
    $initialCode = old('code', $benefit->code);
@endphp
@include('backend.admin.billing.benefits.partials._code-script')

<div x-data="{
         type: @js(old('value_type', $benefit->value_type ?: 'boolean')),
         name: @js((string) $initialName),
         code: @js((string) $initialCode),
         locked: @js($locked),
         codeTouched: false,
         init() {
             this.codeTouched = this.locked || (this.code !== '' && this.code !== benefitCodeFromName(this.name));
             this.$watch('name', (v) => { if (! this.codeTouched) this.code = benefitCodeFromName(v); });
         },
     }" class="space-y-6">
    <x-backend.form-card title="Benefit Details">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-backend.input name="name" label="Name" required maxlength="150" :value="$benefit->name" placeholder="e.g. Featured Supplier" x-model="name" />
            <div>
                <x-backend.input name="code" label="Code" required maxlength="100" :value="$benefit->code" placeholder="e.g. featured_supplier"
                                 class="font-mono" :readonly="$locked" x-model="code" @input="codeTouched = true"
                                 hint="Generated from the name — edit it to use your own. Lowercase letters, numbers and underscores." />
                @if($locked)
                    <p class="mt-1 text-xs text-amber-600"><i class="fa-solid fa-lock mr-1"></i>Locked while assigned to plans or overrides.</p>
                @endif
            </div>
        </div>
        <div class="mt-4">
            <x-backend.textarea name="description" label="Description" rows="3" :value="$benefit->description" placeholder="What this benefit gives a supplier." />
        </div>
    </x-backend.form-card>

    <x-backend.form-card title="Value" description="How this benefit's value is stored, and what a supplier gets when no plan or override sets it.">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="value_type" class="block text-sm font-medium text-gray-700 mb-1.5">Value Type <span class="text-red-500">*</span></label>
                @if($locked)
                    <input type="hidden" name="value_type" value="{{ $benefit->value_type }}">
                @endif
                <select id="value_type" name="{{ $locked ? '' : 'value_type' }}" x-model="type" @disabled($locked)
                        class="focus-accent w-full text-sm rounded-lg border px-3 py-2.5 bg-white disabled:bg-gray-50 {{ $errors->has('value_type') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    @foreach(\App\Models\SubscriptionBenefit::VALUE_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('value_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Default Value</label>
                @include('backend.admin.billing.benefits.partials._value-field', [
                    'name' => 'default_value', 'typeExpr' => 'type', 'value' => $benefit->default_value, 'allowEmpty' => true,
                ])
                <p class="mt-1.5 text-xs text-gray-400">Used when neither a plan nor an override sets it.</p>
            </div>
            <x-backend.input name="unit" label="Unit" maxlength="30" :value="$benefit->unit" placeholder="e.g. listings, minutes" hint="Display only." />
        </div>
    </x-backend.form-card>

    <x-backend.form-card title="Display">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <x-backend.input name="sort_order" label="Sort Order" type="number" min="0" max="65535" :value="$benefit->sort_order ?? 0" />
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 sm:col-span-2 pb-2.5">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $benefit->exists ? $benefit->is_active : true)) style="accent-color:var(--theme-primary)">
                Active <span class="font-normal text-gray-400">— inactive benefits apply to no plan or supplier</span>
            </label>
        </div>
    </x-backend.form-card>
</div>
