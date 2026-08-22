<x-backend.form-card title="Category Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="name" label="Name" required :value="$category->name" />
        <x-backend.select name="parent_id" label="Parent Category" placeholder="None (root category)" :selected="$category->parent_id">
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </x-backend.select>
        <x-backend.select name="type" label="Type" required :selected="$category->type ?: 'both'" :options="['product' => 'Product', 'service' => 'Service', 'both' => 'Both']" />
        <div class="flex items-center gap-2 pt-7">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true)) style="accent-color:var(--theme-primary)">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>
    </div>
    <div class="mt-4">
        <x-backend.textarea name="description" label="Description" :value="$category->description" />
    </div>
</x-backend.form-card>
