<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'free-tier'],
            [
                'name'                        => 'Free Tier',
                'billing_type'                => 'free',
                'price'                       => 0.00,
                'currency_code'               => 'USD',
                'max_active_listings'         => 10,
                'max_products'                => 10,
                'max_services'                => 5,
                'max_team_members'            => 2,
                'max_monthly_quotations'      => 15,
                'rfq_delay_minutes'           => 120, // 2-hour delay
                'has_rfq_notifications'       => true,
                'has_analytics'               => false,
                'has_verified_badge'          => false,
                'has_homepage_placement'      => false,
                'has_team_members'            => true,
                'is_featured'                 => false,
                'is_free'                     => true,
                'requires_supplier_approval' => true,
                'is_active'                   => true,
                'sort_order'                  => 1,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'growth-pro'],
            [
                'name'                        => 'Growth Pro',
                'billing_type'                => 'monthly',
                'price'                       => 99.00,
                'currency_code'               => 'USD',
                'max_active_listings'         => 100,
                'max_products'                => 100,
                'max_services'                => 50,
                'max_team_members'            => 10,
                'max_monthly_quotations'      => 200,
                'rfq_delay_minutes'           => 0, // Immediate RFQs
                'has_rfq_notifications'       => true,
                'has_analytics'               => true,
                'has_verified_badge'          => true,
                'has_homepage_placement'      => true,
                'has_team_members'            => true,
                'is_featured'                 => true,
                'is_free'                     => false,
                'requires_supplier_approval' => true,
                'is_active'                   => true,
                'sort_order'                  => 2,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'enterprise-unlimited'],
            [
                'name'                        => 'Enterprise Unlimited',
                'billing_type'                => 'yearly',
                'price'                       => 899.00,
                'currency_code'               => 'USD',
                'max_active_listings'         => 9999,
                'max_products'                => 9999,
                'max_services'                => 9999,
                'max_team_members'            => 50,
                'max_monthly_quotations'      => 9999,
                'rfq_delay_minutes'           => 0,
                'has_rfq_notifications'       => true,
                'has_analytics'               => true,
                'has_verified_badge'          => true,
                'has_homepage_placement'      => true,
                'has_team_members'            => true,
                'is_featured'                 => false,
                'is_free'                     => false,
                'requires_supplier_approval' => true,
                'is_active'                   => true,
                'sort_order'                  => 3,
            ]
        );
    }
}
