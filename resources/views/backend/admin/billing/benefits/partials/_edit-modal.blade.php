{{--
    One edit modal for every row of the benefits list: a row's Edit button
    dispatches `edit-benefit` with that benefit's data ($editData). It saves
    to the normal update route and returns to this same list (_redirect).
    After a validation error the page reloads and the modal reopens with the
    submitted values and errors (_form = benefit-edit).
--}}
@include('backend.admin.billing.benefits.partials._code-script')

@php
    $reopen = old('_form') === 'benefit-edit' && $errors->any();
    $initial = $reopen ? array_merge($editData[(int) old('_id')] ?? ['locked' => false, 'action' => ''], [
        'id' => (int) old('_id'),
        'name' => (string) old('name'),
        'code' => (string) old('code'),
        'description' => (string) old('description'),
        'value_type' => (string) old('value_type', $editData[(int) old('_id')]['value_type'] ?? 'boolean'),
        'default_value' => (string) old('default_value'),
        'unit' => (string) old('unit'),
        'sort_order' => old('sort_order', 0),
        'is_active' => (bool) old('is_active'),
    ]) : null;
    $fieldError = fn (string $field) => $reopen ? $errors->first($field) : null;
    $inputClass = 'w-full px-3 py-2.5 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400 focus-accent';
@endphp

<x-backend.modal id="benefit-edit" title="Edit Benefit" width="max-w-2xl">
    <form method="POST" :action="form.action"
          x-data="{
              form: { id: null, name: '', code: '', description: '', value_type: 'boolean', default_value: '', unit: '', sort_order: 0, is_active: true, locked: false, action: '' },
              codeTouched: true,
              load(data) {
                  this.form = Object.assign({}, this.form, data);
                  this.form.default_value = data.default_value ?? '';
                  this.codeTouched = this.form.locked || (this.form.code !== '' && this.form.code !== benefitCodeFromName(this.form.name));
              },
              init() {
                  this.$watch('form.name', (v) => { if (! this.codeTouched) this.form.code = benefitCodeFromName(v); });
                  @if($reopen)
                      this.load(@js($initial));
                      this.$nextTick(() => this.$dispatch('open-modal-benefit-edit'));
                  @endif
              },
          }"
          @edit-benefit.window="load($event.detail); $dispatch('open-modal-benefit-edit')"
          class="space-y-5">
        @csrf
        @method('PUT')
        <input type="hidden" name="_form" value="benefit-edit">
        <input type="hidden" name="_id" :value="form.id">
        <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="form.name" required maxlength="150" class="{{ $inputClass }} {{ $fieldError('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @if($fieldError('name')) <p class="mt-1 text-xs text-red-600">{{ $fieldError('name') }}</p> @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" x-model="form.code" @input="codeTouched = true" :readonly="form.locked" required maxlength="100"
                       class="{{ $inputClass }} font-mono read-only:bg-gray-50 {{ $fieldError('code') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @if($fieldError('code'))
                    <p class="mt-1 text-xs text-red-600">{{ $fieldError('code') }}</p>
                @else
                    <p class="mt-1 text-xs" :class="form.locked ? 'text-amber-600' : 'text-gray-400'"
                       x-text="form.locked ? 'Locked while assigned to plans or overrides.' : 'Generated from the name — edit it to use your own.'"></p>
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <textarea name="description" x-model="form.description" rows="2" maxlength="2000" class="{{ $inputClass }} border-gray-300"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Value Type <span class="text-red-500">*</span></label>
                <input type="hidden" name="value_type" :value="form.value_type">
                <select x-model="form.value_type" :disabled="form.locked" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white disabled:bg-gray-50">
                    @foreach(\App\Models\SubscriptionBenefit::VALUE_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @if($fieldError('value_type')) <p class="mt-1 text-xs text-red-600">{{ $fieldError('value_type') }}</p> @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Default Value</label>
                <select name="default_value" x-model="form.default_value" x-show="form.value_type === 'boolean'" :disabled="form.value_type !== 'boolean'"
                        class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                    <option value="">— Not set (Disabled) —</option>
                    <option value="1">Enabled</option>
                    <option value="0">Disabled</option>
                </select>
                <input type="number" name="default_value" x-model="form.default_value" step="1" placeholder="Not set"
                       x-show="form.value_type === 'integer'" :disabled="form.value_type !== 'integer'" class="{{ $inputClass }} border-gray-300">
                <input type="number" name="default_value" x-model="form.default_value" step="any" placeholder="Not set"
                       x-show="form.value_type === 'decimal'" :disabled="form.value_type !== 'decimal'" class="{{ $inputClass }} border-gray-300">
                <input type="text" name="default_value" x-model="form.default_value" maxlength="1000" placeholder="Not set"
                       x-show="form.value_type === 'text'" :disabled="form.value_type !== 'text'" class="{{ $inputClass }} border-gray-300">
                @if($fieldError('default_value')) <p class="mt-1 text-xs text-red-600">{{ $fieldError('default_value') }}</p> @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit</label>
                <input type="text" name="unit" x-model="form.unit" maxlength="30" placeholder="e.g. listings" class="{{ $inputClass }} border-gray-300">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" name="sort_order" x-model="form.sort_order" min="0" max="65535" class="{{ $inputClass }} border-gray-300">
                @if($fieldError('sort_order')) <p class="mt-1 text-xs text-red-600">{{ $fieldError('sort_order') }}</p> @endif
            </div>
            <label class="sm:col-span-2 flex items-center gap-2 text-sm font-medium text-gray-700 pb-2.5">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" style="accent-color:var(--theme-primary)">
                Active <span class="font-normal text-gray-400">— inactive benefits apply to no plan or supplier</span>
            </label>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal-benefit-edit')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Save Changes</button>
        </div>
    </form>
</x-backend.modal>
