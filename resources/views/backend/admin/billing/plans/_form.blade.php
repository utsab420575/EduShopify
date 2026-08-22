<x-backend.form-card title="Plan Details">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-backend.input name="name" label="Name" required :value="$plan->name" />
        <x-backend.select name="billing_type" label="Billing Type" required :selected="$plan->billing_type ?: 'monthly'" :options="['free' => 'Free', 'monthly' => 'Monthly', 'yearly' => 'Yearly']" />
        <x-backend.input name="price" label="Price" type="number" step="0.01" required :value="$plan->price ?? 0" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
        <x-backend.input name="trial_days" label="Trial Days" type="number" :value="$plan->trial_days ?? 0" />
        <x-backend.input name="bonus_days" label="Bonus Days" type="number" :value="$plan->bonus_days ?? 0" />
        <x-backend.input name="sort_order" label="Sort Order" type="number" :value="$plan->sort_order ?? 0" />
    </div>
</x-backend.form-card>

<x-backend.form-card title="Limits">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-backend.input name="max_active_listings" label="Max Active Listings" type="number" :value="$plan->max_active_listings" hint="Leave empty for unlimited" />
        <x-backend.input name="max_products" label="Max Products" type="number" :value="$plan->max_products" />
        <x-backend.input name="max_services" label="Max Services" type="number" :value="$plan->max_services" />
        <x-backend.input name="max_team_members" label="Max Team Members" type="number" :value="$plan->max_team_members" />
        <x-backend.input name="max_monthly_quotations" label="Max Monthly Quotations" type="number" :value="$plan->max_monthly_quotations" />
        <x-backend.input name="rfq_delay_minutes" label="RFQ Delay (minutes)" type="number" :value="$plan->rfq_delay_minutes ?? 0" hint="Queue delay before this plan sees new RFQs" />
    </div>
</x-backend.form-card>

<x-backend.form-card title="Features">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach([
            'has_rfq_notifications' => 'RFQ Notifications',
            'has_analytics' => 'Analytics',
            'has_verified_badge' => 'Verified Badge',
            'has_homepage_placement' => 'Homepage Placement',
            'has_team_members' => 'Team Members',
            'is_featured' => 'Featured Plan',
            'is_free' => 'Free Plan',
            'requires_supplier_approval' => 'Requires Admin Approval',
        ] as $field => $label)
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2 cursor-pointer">
                <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $plan->$field)) style="accent-color:var(--theme-primary)">
                {{ $label }}
            </label>
        @endforeach
    </div>
    <div class="flex items-center gap-2 mt-4">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $plan->exists ? $plan->is_active : true)) style="accent-color:var(--theme-primary)">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
    </div>
</x-backend.form-card>
