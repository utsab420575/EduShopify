<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapabilitySettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group_name' => 'capability',
                'name'       => 'capability_application_max_attempts',
                'payload'    => json_encode(['value' => 3]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'capability',
                'name'       => 'buyer_application_review_days',
                'payload'    => json_encode(['value' => 3]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'capability',
                'name'       => 'account_conversion_max_attempts',
                'payload'    => json_encode(['value' => 3]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'award',
                'name'       => 'award_response_hours',
                'payload'    => json_encode(['value' => 72]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'rfq',
                'name'       => 'rfq_requires_admin_approval',
                'payload'    => json_encode(['value' => false]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'rfq',
                'name'       => 'supplier_requires_active_subscription_for_rfq',
                'payload'    => json_encode(['value' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'quotation',
                'name'       => 'quotation_review_enabled',
                'payload'    => json_encode(['value' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'purchase_order',
                'name'       => 'purchase_review_enabled',
                'payload'    => json_encode(['value' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group_name' => 'messaging',
                'name'       => 'allow_unrelated_messaging',
                'payload'    => json_encode(['value' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group_name' => $setting['group_name'], 'name' => $setting['name']],
                $setting
            );
        }
    }
}
