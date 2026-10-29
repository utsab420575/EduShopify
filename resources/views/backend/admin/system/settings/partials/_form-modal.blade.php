{{--
    One modal for adding and editing settings: "New Setting" dispatches
    `setting-form` with null, a row's Edit button with that row's data
    ($editData). After a validation error the page reloads and the modal
    reopens with the submitted values and errors (_form = setting-form).
--}}
@php
    $reopen = old('_form') === 'setting-form' && $errors->any();
    $oldId = (int) old('_id');
    $initial = $reopen ? array_merge($editData[$oldId] ?? ['id' => null, 'system' => false, 'action' => route('admin.system.settings.store')], [
        'group_name' => (string) old('group_name'),
        'name' => (string) old('name'),
        'label' => (string) old('label'),
        'type' => (string) old('type', 'text'),
        'value' => (string) old('value'),
        'description' => (string) old('description'),
    ]) : null;
    if ($reopen && $oldId && ! isset($editData[$oldId])) {
        $initial['id'] = $oldId;
        $initial['action'] = route('admin.system.settings.update', $oldId);
    }
    $fieldError = fn (string $field) => $reopen ? $errors->first($field) : null;
    $inputClass = 'w-full px-3 py-2.5 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400 focus-accent';
@endphp

<x-backend.modal id="setting-form" title="Setting" width="max-w-2xl">
    <form method="POST" :action="form.action"
          x-data="{
              blank: { id: null, group_name: '', name: '', label: '', type: 'text', value: '', description: '', system: false, action: @js(route('admin.system.settings.store')) },
              form: {},
              groups: @js($groups->values()),
              load(data) {
                  this.form = Object.assign({}, this.blank, data || {});
                  if (this.form.type === 'boolean' && this.form.value === '') this.form.value = '0';
              },
              init() {
                  this.load(null);
                  @if($reopen)
                      this.load(@js($initial));
                      this.$nextTick(() => this.$dispatch('open-modal-setting-form'));
                  @endif
              },
          }"
          @setting-form.window="load($event.detail); $dispatch('open-modal-setting-form')"
          class="space-y-5">
        @csrf
        <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="setting-form">
        <input type="hidden" name="_id" :value="form.id">
        <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">

        <p class="text-sm font-semibold text-gray-900 -mt-1" x-text="form.id ? 'Edit setting' : 'New setting'"></p>

        <div x-show="form.system" class="flex items-start gap-2 rounded-lg bg-indigo-50 border border-indigo-100 px-3 py-2 text-xs text-indigo-700">
            <i class="fa-solid fa-circle-info mt-0.5"></i>
            <span>The system reads this setting — its group, name and type can't be changed.</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Group <span class="text-red-500">*</span></label>
                <input type="text" name="group_name" x-model="form.group_name" :readonly="form.system" list="setting-groups" required maxlength="120" placeholder="e.g. product_media"
                       class="{{ $inputClass }} font-mono read-only:bg-gray-50 {{ $fieldError('group_name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <datalist id="setting-groups">
                    <template x-for="g in groups" :key="g"><option :value="g"></option></template>
                </datalist>
                @if($fieldError('group_name')) <p class="mt-1 text-xs text-red-600">{{ $fieldError('group_name') }}</p> @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name (key) <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="form.name" :readonly="form.system" required maxlength="160" placeholder="e.g. video_max_kb"
                       class="{{ $inputClass }} font-mono read-only:bg-gray-50 {{ $fieldError('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @if($fieldError('name'))
                    <p class="mt-1 text-xs text-red-600">{{ $fieldError('name') }}</p>
                @else
                    <p class="mt-1 text-xs text-gray-400">Lower-case letters, numbers and underscores.</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Label</label>
                <input type="text" name="label" x-model="form.label" maxlength="160" placeholder="Shown in this list"
                       class="{{ $inputClass }} {{ $fieldError('label') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                <input type="hidden" name="type" :value="form.type">
                <select x-model="form.type" :disabled="form.system" @change="if (form.type === 'boolean' && !['0','1'].includes(form.value)) form.value = '0'"
                        class="{{ $inputClass }} border-gray-300 bg-white disabled:bg-gray-50">
                    @foreach(\App\Models\Setting::TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Value <span x-show="['number','boolean'].includes(form.type)" class="text-red-500">*</span></label>
            <template x-if="form.type === 'boolean'">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="value" :value="form.value">
                    <button type="button" @click="form.value = '1'" class="px-4 py-2 rounded-lg border text-sm font-medium"
                            :class="form.value === '1' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-600'">Yes</button>
                    <button type="button" @click="form.value = '0'" class="px-4 py-2 rounded-lg border text-sm font-medium"
                            :class="form.value === '0' ? 'border-gray-500 bg-gray-100 text-gray-800' : 'border-gray-300 text-gray-600'">No</button>
                </div>
            </template>
            <template x-if="form.type === 'number'">
                <input type="number" step="any" name="value" x-model="form.value" required
                       class="{{ $inputClass }} sm:w-60 {{ $fieldError('value') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            </template>
            <template x-if="form.type === 'list'">
                <input type="text" name="value" x-model="form.value" placeholder="e.g. mp4, webm"
                       class="{{ $inputClass }} {{ $fieldError('value') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            </template>
            <template x-if="form.type === 'text'">
                <textarea name="value" x-model="form.value" rows="2"
                          class="{{ $inputClass }} {{ $fieldError('value') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}"></textarea>
            </template>
            @if($fieldError('value'))
                <p class="mt-1 text-xs text-red-600">{{ $fieldError('value') }}</p>
            @else
                <p class="mt-1 text-xs text-gray-400" x-show="form.type === 'list'">Separate items with commas.</p>
                <p class="mt-1 text-xs text-gray-400" x-show="form.group_name === 'product_media' && form.name === 'video_max_kb'">In KB — 1024 KB = 1 MB (e.g. 2048 = 2 MB, 10240 = 10 MB).</p>
                <p class="mt-1 text-xs text-gray-400" x-show="form.group_name === 'product_media' && form.name === 'video_extensions'">Allowed: {{ implode(', ', \App\Support\Media\ProductMediaLimits::knownVideoExtensions()) }}.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <textarea name="description" x-model="form.description" rows="2" maxlength="2000" placeholder="What this setting controls"
                      class="{{ $inputClass }} border-gray-300"></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal-setting-form')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg" x-text="form.id ? 'Save Changes' : 'Add Setting'"></button>
        </div>
    </form>
</x-backend.modal>
