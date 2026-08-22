<x-backend.form-card title="Currency Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="code" label="Code" required :value="$currency->code" placeholder="USD" @if($currency->exists) readonly @endif />
        <x-backend.input name="name" label="Name" required :value="$currency->name" placeholder="US Dollar" />
        <x-backend.input name="symbol" label="Symbol" required :value="$currency->symbol" placeholder="$" />
        <x-backend.input name="exchange_rate" label="Exchange Rate (to base)" type="number" step="0.00000001" required :value="$currency->exchange_rate ?? 1" />
        <x-backend.input name="decimal_places" label="Decimal Places" type="number" required :value="$currency->decimal_places ?? 2" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $currency->exists ? $currency->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
