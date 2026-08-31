<?php

namespace Tests\Feature;

use App\Livewire\Buyer\BuyerProfileOnboarding;
use App\Models\CapabilityType;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\DocumentTypeEnable;
use App\Models\SocialPlatform;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Drives the consolidated Buyer onboarding wizard end to end, both with and
 * without a configured buyer document type — the wizard must be 3 steps in
 * the former case and 2 steps (no documents step at all) in the latter.
 */
class BuyerProfileOnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeDraftBuyer(string $email): array
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'organization', 'capability' => 'buyer',
            'name' => 'Wizard Buyer User', 'email' => $email,
            'phone' => '+1555000' . random_int(1000, 9999), 'password' => 'Password123!',
            'organization_display_name' => 'Wizard Buyer Co',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);

        return [$user->fresh(), $user->account->fresh()];
    }

    public function test_wizard_is_only_2_steps_when_no_buyer_document_types_are_configured(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftBuyer('buyer-2step@example.com');
        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $buyerType = \App\Models\BuyerType::first() ?? \App\Models\BuyerType::create(['name' => 'School', 'slug' => 'school-2step', 'is_active' => true]);

        $component = Livewire::actingAs($user)->test(BuyerProfileOnboarding::class);
        $component->assertSet('totalSteps', 2);

        $component
            ->set('display_name', 'Wizard Buyer Co')
            ->set('organization_name', 'Wizard Buyer Co LLC')
            ->set('buyer_type_ids', [$buyerType->id])
            ->set('contact_person', 'Jane Doe')
            ->set('email', 'buyer-2step@example.com')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.address', '1 Buyer Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        $this->assertTrue($account->buyerProfile->fresh()->isComplete());

        // Step 2 is the final step — Final Submit must validate its own
        // required profile_photo before submitting, even with no doc step.
        $component->call('confirmFinalSubmit')->assertHasErrors('profile_photo');
        $this->assertSame('draft', $account->buyerCapability->fresh()->status);

        $facebook = SocialPlatform::where('slug', 'facebook')->firstOrFail();
        $other = SocialPlatform::where('slug', 'other')->firstOrFail();

        // An invalid URL must surface the friendly custom message, not
        // Laravel's default "The social_links.0.url field must be a valid URL."
        $component->call('addSocialLink')
            ->set('social_links.0.platform_id', (string) $facebook->id)
            ->set('social_links.0.url', 'not-a-url')
            ->call('confirmFinalSubmit')
            ->assertHasErrors(['social_links.0.url'])
            ->assertSee('Please enter a valid URL');

        $component
            ->set('social_links.0.url', 'https://facebook.com/wizardbuyer')
            ->call('addSocialLink')
            ->set('social_links.1.platform_id', (string) $other->id)
            ->set('social_links.1.label', 'My Telegram Channel')
            ->set('social_links.1.url', 'https://t.me/wizardbuyer');

        // An unsupported image format (e.g. AVIF) must be rejected with a
        // friendly validation error, not the raw Livewire
        // FileNotPreviewableException that used to crash the page.
        $component
            ->set('profile_photo', UploadedFile::fake()->image('photo.avif'))
            ->assertHasErrors(['profile_photo'])
            ->assertSee('image format')
            ->assertSet('profile_photo', null);

        // Selecting a valid image afterward must clear that stale format
        // error, not leave it stuck on screen.
        $component
            ->set('profile_photo', UploadedFile::fake()->image('photo.png'))
            ->assertHasNoErrors(['profile_photo'])
            ->set('gallery_files', [UploadedFile::fake()->image('g1.png')])
            ->call('confirmFinalSubmit')
            ->assertHasNoErrors();

        $this->assertSame('active', $account->buyerCapability->fresh()->status);
        $this->assertNotNull($account->buyerProfile->fresh()->profile_photo);
        $this->assertSame(1, $account->buyerGalleryImages()->count());
        $this->assertSame(2, $account->socialLinks()->count());
        $this->assertSame('https://facebook.com/wizardbuyer', $account->socialLinks()->where('social_platform_id', $facebook->id)->first()->url);
        $this->assertSame('My Telegram Channel', $account->socialLinks()->where('social_platform_id', $other->id)->first()->label);
    }

    public function test_wizard_is_3_steps_with_required_documents_gate_when_buyer_document_types_exist(): void
    {
        $this->seedBase();
        Storage::fake('public');

        [$user, $account] = $this->makeDraftBuyer('buyer-3step@example.com');
        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $buyerType = \App\Models\BuyerType::first() ?? \App\Models\BuyerType::create(['name' => 'School', 'slug' => 'school-3step', 'is_active' => true]);

        $docType = DocumentType::create(['name' => 'Business Registration', 'slug' => 'business-registration-w', 'code' => 'BRW', 'is_required' => true, 'is_active' => true]);
        DocumentTypeEnable::create([
            'document_type_id' => $docType->id,
            'capability_type_id' => CapabilityType::where('code', 'buyer')->value('id'),
            'is_required' => true,
        ]);

        $component = Livewire::actingAs($user)->test(BuyerProfileOnboarding::class);
        $component->assertSet('totalSteps', 3);

        $component
            ->set('display_name', 'Wizard Buyer Co')
            ->set('organization_name', 'Wizard Buyer Co LLC')
            ->set('buyer_type_ids', [$buyerType->id])
            ->set('contact_person', 'Jane Doe')
            ->set('email', 'buyer-3step@example.com')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.address', '1 Buyer Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        $component
            ->set('profile_photo', UploadedFile::fake()->image('photo.png'))
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 3);

        // Once saved, the component must stop referencing the temporary
        // upload objects (their temp files expire after a few minutes) so
        // any later preview renders the now-permanently-saved photo and
        // logo instead of a broken/expired image.
        $component->assertSet('profile_photo', null)->assertSet('logo', null);

        // No document uploaded yet — Final Submit must be blocked.
        $component->call('confirmFinalSubmit')->assertHasErrors('documents');
        $this->assertSame('draft', $account->buyerCapability->fresh()->status);

        $component
            ->set('new_document_type_id', (string) $docType->id)
            ->set('new_file', UploadedFile::fake()->create('reg.pdf', 200, 'application/pdf'))
            ->call('addDocument')
            ->assertHasNoErrors();

        // Required docs are now satisfied, so the preview modal renders —
        // it must show the saved photo and logo, not "No photo"/"No logo".
        $profile = $account->buyerProfile->fresh();
        $component
            ->assertSee(Storage::url($profile->profile_photo), false)
            ->assertSee(Storage::url($profile->logo), false)
            ->assertDontSee('No photo')
            ->assertDontSee('No logo');

        $this->assertDatabaseHas('account_documents', [
            'documentable_id' => $account->id,
            'document_type_id' => $docType->id,
            'is_current' => true,
        ]);

        $component->call('confirmFinalSubmit')->assertHasNoErrors();
        $this->assertSame('active', $account->buyerCapability->fresh()->status);
    }

    public function test_resumes_at_the_exact_step_left_off_after_a_simulated_logout(): void
    {
        $this->seedBase();
        [$user, $account] = $this->makeDraftBuyer('buyer-resume@example.com');
        $country = Country::create(['name' => 'UAE', 'iso2' => 'AE', 'iso3' => 'ARE', 'phone_code' => '971', 'currency_code' => 'AED', 'is_active' => true]);
        $buyerType = \App\Models\BuyerType::first() ?? \App\Models\BuyerType::create(['name' => 'School', 'slug' => 'school-resume', 'is_active' => true]);

        $session1 = Livewire::actingAs($user)->test(BuyerProfileOnboarding::class);
        $session1
            ->set('display_name', 'Wizard Buyer Co')
            ->set('organization_name', 'Wizard Buyer Co LLC')
            ->set('buyer_type_ids', [$buyerType->id])
            ->set('contact_person', 'Jane Doe')
            ->set('email', 'buyer-resume@example.com')
            ->set('locations.0.country_id', $country->id)
            ->set('locations.0.address', '1 Buyer Ave, Dubai')
            ->call('nextStep')->assertHasNoErrors()->assertSet('step', 2);

        $this->assertDatabaseHas('buyer_profiles', [
            'account_id' => $account->id,
            'current_step' => 2,
            'max_step_reached' => 2,
        ]);

        $session2 = Livewire::actingAs($user->fresh())->test(BuyerProfileOnboarding::class);
        $session2->assertSet('step', 2);
        $session2->assertSet('display_name', 'Wizard Buyer Co');
    }
}
