<?php

namespace Tests\Feature;

use App\Services\AccountRegistrationService;
use App\Services\SubscriptionSelectionService;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The dedicated /supplier/pending (and /supplier/onboarding/rejected) pages
 * used to render a frozen static view whose status detection was broken
 * (it never matched real capability rows), so a refresh after approval kept
 * showing "Under Review" forever. Both routes now redirect to
 * supplier.dashboard, which re-checks the live capability status on every
 * request.
 */
class SupplierPendingRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeSupplier(string $email): \App\Models\User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization', 'capability' => 'supplier',
            'name' => 'Status Test User', 'email' => $email,
            'phone' => '+1555000' . random_int(1000, 9999), 'password' => 'Password123!',
            'organization_display_name' => 'Status Test Co',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);

        return $user->fresh();
    }

    public function test_pending_page_redirects_to_dashboard_and_shows_under_review(): void
    {
        $this->seedBase();
        $user = $this->makeSupplier('status-pending@example.com');
        $user->account->supplierCapability->update(['status' => 'pending']);

        $response = $this->actingAs($user)->get(route('supplier.pending'));

        $response->assertRedirect(route('supplier.dashboard'));
        $this->followRedirects($response)->assertSee('Under Review');
    }

    /**
     * The exact bug report: an approved account that lands on (or refreshes)
     * /supplier/pending must reach the real dashboard, not a stuck status page.
     */
    public function test_pending_page_redirects_to_the_real_dashboard_once_approved(): void
    {
        $this->seedBase();
        $user = $this->makeSupplier('status-approved@example.com');
        $account = $user->account;
        $account->supplierCapability->update(['status' => 'active']);

        $plan = SubscriptionPlan::create([
            'name' => 'Free Tier', 'slug' => 'free-tier-status', 'billing_type' => 'free',
            'price' => 0, 'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
            'max_active_listings' => 5, 'max_monthly_quotations' => 10, 'rfq_delay_minutes' => 0,
        ]);
        app(SubscriptionSelectionService::class)->select($account, $plan, $user);

        $response = $this->actingAs($user)->get(route('supplier.pending'));

        $response->assertRedirect(route('supplier.dashboard'));
        $dashboard = $this->followRedirects($response);
        $dashboard->assertSee('Welcome back');
        $dashboard->assertDontSee('Under Review');
    }

    public function test_rejected_page_redirects_to_dashboard_and_shows_rejection_reason(): void
    {
        $this->seedBase();
        $user = $this->makeSupplier('status-rejected@example.com');
        $user->account->supplierCapability->update([
            'status' => 'rejected',
            'rejection_reason' => 'Trade license expired.',
        ]);

        $response = $this->actingAs($user)->get(route('supplier.onboarding.rejected'));

        $response->assertRedirect(route('supplier.dashboard'));
        $this->followRedirects($response)->assertSee('Trade license expired.');
    }
}
