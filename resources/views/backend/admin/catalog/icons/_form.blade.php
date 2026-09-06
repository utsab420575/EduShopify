<div x-data="{
    iconType: '{{ old('icon_type', $icon->icon_type ?: 'fontawesome') }}',
    iconValue: '{{ addslashes(old('icon_value', $icon->icon_value ?: '')) }}'
}">
    <x-backend.form-card title="Icon Details">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="library_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Icon Library <span class="text-red-500">*</span>
                </label>
                <select name="library_id" id="library_id" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Select a library</option>
                    @foreach($libraries as $lib)
                        <option value="{{ $lib->id }}" @selected(old('library_id', $icon->library_id) == $lib->id)>
                            {{ $lib->name }} ({{ $lib->type }})
                        </option>
                    @endforeach
                </select>
                @error('library_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-backend.input name="name" label="Icon Display Name" required :value="$icon->name" placeholder="e.g. Fast Delivery, Quality Guarantee" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="icon_type" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Icon Type <span class="text-red-500">*</span>
                </label>
                <select name="icon_type" id="icon_type" x-model="iconType" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="fontawesome">FontAwesome (CSS Class)</option>
                    <option value="svg">Raw SVG Markup</option>
                    <option value="image_url">Image / Icon URL</option>
                </select>
                @error('icon_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Live Preview
                </label>
                <div class="h-10 w-16 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                    <template x-if="iconType === 'fontawesome' && iconValue">
                        <i :class="iconValue" class="text-xl text-indigo-600"></i>
                    </template>
                    <template x-if="iconType === 'svg' && iconValue">
                        <div class="w-6 h-6 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full text-indigo-600" x-html="iconValue"></div>
                    </template>
                    <template x-if="iconType === 'image_url' && iconValue">
                        <img :src="iconValue" alt="Preview" class="w-6 h-6 object-contain">
                    </template>
                    <template x-if="!iconValue">
                        <span class="text-xs text-gray-400">None</span>
                    </template>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label for="icon_value" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                Icon Value / Content <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-1.5" x-show="iconType === 'fontawesome'">
                Enter the FontAwesome or font classes, e.g. <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">fa-solid fa-truck-fast</code> or <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">bi bi-shield-check</code>
            </p>
            <p class="text-xs text-gray-500 mb-1.5" x-show="iconType === 'svg'">
                Paste the raw <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">&lt;svg ...&gt;...&lt;/svg&gt;</code> markup.
            </p>
            <p class="text-xs text-gray-500 mb-1.5" x-show="iconType === 'image_url'">
                Enter full image URL or CDN link (e.g. <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">https://.../icon.png</code>).
            </p>
            <textarea name="icon_value" id="icon_value" rows="3" required x-model="iconValue"
                      class="focus-accent w-full text-sm rounded-lg border border-gray-300 p-3 font-mono text-xs"
                      placeholder="e.g. fa-solid fa-truck-fast">{{ old('icon_value', $icon->icon_value) }}</textarea>
            @error('icon_value')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2 mt-5 pt-3 border-t border-gray-100">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $icon->exists ? $icon->is_active : true)) style="accent-color:var(--theme-primary)" class="rounded border-gray-300 w-4 h-4">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active (Available in service icon pickers across the platform)</label>
        </div>
    </x-backend.form-card>
</div>
