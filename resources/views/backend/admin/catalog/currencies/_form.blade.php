<x-backend.form-card title="Currency Details">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-backend.input name="code" label="Currency Code (ISO 3-Letter)" required :value="$currency->code" placeholder="e.g. USD, BDT, EUR" @if($currency->exists) readonly @endif />
        <x-backend.input name="name" label="Currency Name" required :value="$currency->name" placeholder="e.g. US Dollar, Bangladeshi Taka" />
        <x-backend.input name="symbol" label="Currency Symbol" required :value="$currency->symbol" placeholder="e.g. $, ৳, €" />
        <x-backend.input name="exchange_rate" label="Exchange Rate (relative to base 1.0)" type="number" step="0.00000001" required :value="$currency->exchange_rate ?? 1" />
        <x-backend.input name="decimal_places" label="Decimal Places" type="number" required :value="$currency->decimal_places ?? 2" />
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $currency->exists ? $currency->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active across platform and catalog forms</label>
    </div>
</x-backend.form-card>
