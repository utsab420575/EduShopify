<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Product photos: stored as an uncropped "_original" (≤2000px JPEG) plus
 * WebP copies: product_banner (the product page box — stage-shaped for cover,
 * full width for contain/fill — scaled to 1600 wide, never enlarged) and
 * card (600×450) / thumb (160×120) from the 4:3 card box, with a per-photo
 * fit (cover/contain/fill) for the product page. Product videos: ≤2 MB,
 * stored as uploaded with a poster frame. See ProductMediaService.
 */
class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $supplier;

    private ListingType $type;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual', 'capability' => 'supplier', 'name' => 'Media Supplier',
            'email' => 'media@example.com', 'phone' => '+15550002222', 'password' => 'Password123!',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);
        $user->account->supplierCapability()->update(['status' => 'active']);
        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->account->id);

        $plan = SubscriptionPlan::create([
            'name' => 'Free', 'slug' => 'free-'.uniqid(), 'billing_type' => 'free', 'price' => 0, 'currency_code' => 'USD',
            'is_free' => true, 'is_active' => true, 'max_active_listings' => 10, 'max_monthly_quotations' => 10, 'rfq_delay_minutes' => 0,
        ]);
        Subscription::create([
            'supplier_account_id' => $user->account->id, 'plan_id' => $plan->id,
            'selected_by_user_id' => $user->id, 'provider' => 'free', 'status' => 'pending',
        ])->activate();

        $this->supplier = $user->fresh();
        $this->type = ListingType::firstOrCreate(['code' => 'product'], ['name' => 'Product', 'is_active' => true]);
    }

    private function step1(array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->supplier)->post(route('supplier.catalog.listings.wizard.step1'), array_merge([
            'listing_type_id' => $this->type->id,
            'name' => 'Lab Microscope',
        ], $extra), ['Accept' => 'application/json']);
    }

    /** A tiny file that finfo recognises as video/mp4 (an MP4 "ftyp" box). */
    private function fakeMp4(string $name = 'demo.mp4'): UploadedFile
    {
        $ftyp = "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom";

        return UploadedFile::fake()->createWithContent($name, $ftyp.str_repeat("\x00", 4096));
    }

    private function dimensions(Media $media, string $conversion = ''): array
    {
        return array_slice(getimagesize($media->getPath($conversion)), 0, 2);
    }

    public function test_image_is_stored_as_original_plus_cropped_webp_copies_with_readable_names(): void
    {
        // 3000×2000 upload, crop box 1200×900 at (300,150) in upload pixels.
        $this->step1([
            'images' => [UploadedFile::fake()->image('Camera Shot.png', 3000, 2000)],
            'image_crops' => [json_encode(['x' => 300, 'y' => 150, 'width' => 1200, 'height' => 900])],
            // Contain: banner box without x/width → the full width.
            'image_banner_crops' => [json_encode(['y' => 600, 'height' => 750])],
            'image_fits' => ['contain'],
        ])->assertOk()->assertJsonPath('media.0.is_primary', true)->assertJsonPath('media.0.banner_fit', 'contain');

        $listing = Listing::where('name', 'Lab Microscope')->firstOrFail();
        $media = $listing->getFirstMedia('gallery');
        $accountId = $this->supplier->account->id;

        // Original: ≤2000px JPEG, crop box scaled to it (×2000/3000).
        $this->assertMatchesRegularExpression("/^{$accountId}_[a-z0-9]{6}_camera_shot_original\\.jpg$/", $media->file_name);
        $this->assertSame([2000, 1333], $this->dimensions($media));
        $this->assertSame(['x' => 200, 'y' => 100, 'width' => 800, 'height' => 600], $media->getCustomProperty('crop'));
        $this->assertSame(['x' => 0, 'y' => 400, 'width' => 2000, 'height' => 500], $media->getCustomProperty('banner_crop'));
        $this->assertSame((int) $media->id, (int) $listing->primary_image_media_id);

        // Cropped copies: exact sizes, WebP, "_original" swapped for the size name.
        $base = preg_replace('/_original\.jpg$/', '', $media->file_name);
        // Banner: 2000×500 box → 1600×400. Card/thumb: exact 4:3 sizes.
        foreach (['product_banner' => [1600, 400], 'card' => [600, 450], 'thumb' => [160, 120]] as $conversion => $dims) {
            $this->assertTrue($media->hasGeneratedConversion($conversion), "{$conversion} not generated");
            $this->assertSame("{$base}_{$conversion}.webp", basename($media->getPath($conversion)));
            $this->assertSame($dims, $this->dimensions($media, $conversion));
        }

        $this->assertStringContainsString("_card.webp", $listing->cardImageUrl());
    }

    public function test_same_file_name_twice_gives_two_separate_files(): void
    {
        $this->step1(['images' => [UploadedFile::fake()->image('2.jpg', 800, 600), UploadedFile::fake()->image('2.jpg', 800, 600)]])->assertOk();

        $files = Listing::firstOrFail()->getMedia('gallery')->pluck('file_name');
        $this->assertCount(2, $files);
        $this->assertCount(2, $files->unique());
    }

    public function test_recrop_regenerates_copies_and_deleting_the_cover_picks_the_next_photo(): void
    {
        $this->step1(['images' => [UploadedFile::fake()->image('a.jpg', 1600, 1200), UploadedFile::fake()->image('b.jpg', 1600, 1200)]])->assertOk();
        $listing = Listing::firstOrFail();
        [$first, $second] = $listing->getMedia('gallery')->all();

        // No banner box chosen → the largest stage-shaped (845:400) box, centred.
        $this->assertSame([1600, 757], $this->dimensions($first, 'product_banner'));

        $this->actingAs($this->supplier)->postJson(route('supplier.catalog.listings.media.crop', [$listing, $first]), [
            'card' => ['x' => 400, 'y' => 300, 'width' => 800, 'height' => 600],
            'banner' => ['y' => 200, 'height' => 800],
            'fit' => 'fill',
        ])->assertOk()->assertJsonPath('media.crop.width', 800)->assertJsonPath('media.banner_fit', 'fill');
        $fresh = $first->fresh();
        $this->assertSame(['x' => 400, 'y' => 300, 'width' => 800, 'height' => 600], $fresh->getCustomProperty('crop'));
        $this->assertSame(['x' => 0, 'y' => 200, 'width' => 1600, 'height' => 800], $fresh->getCustomProperty('banner_crop'));
        $this->assertSame([1600, 800], $this->dimensions($fresh, 'product_banner'));
        $this->assertSame([600, 450], $this->dimensions($fresh, 'card'));

        // Delete the cover → the remaining photo becomes the cover; the files are gone.
        $path = $first->getPath();
        $this->actingAs($this->supplier)->deleteJson(route('supplier.catalog.listings.media.destroy', [$listing, $first]))
            ->assertOk()->assertJsonPath('primary_image_media_id', $second->id);
        $this->assertFileDoesNotExist($path);
        $this->assertSame((int) $second->id, (int) $listing->fresh()->primary_image_media_id);
    }

    public function test_cover_box_is_kept_where_the_supplier_put_it_and_wide_photos_default_to_a_centred_stage_box(): void
    {
        // A wide 2000×848 photo (2.36:1): cover box moved to the left edge.
        $this->step1([
            'images' => [UploadedFile::fake()->image('wide.jpg', 2000, 848), UploadedFile::fake()->image('wide2.jpg', 2000, 848)],
            'image_banner_crops' => [json_encode(['x' => 0, 'y' => 0, 'width' => 1791, 'height' => 848])],
            'image_fits' => ['cover'],
        ])->assertOk();
        [$moved, $default] = Listing::firstOrFail()->getMedia('gallery')->all();

        $this->assertSame(['x' => 0, 'y' => 0, 'width' => 1791, 'height' => 848], $moved->getCustomProperty('banner_crop'));
        [$w, $h] = $this->dimensions($moved, 'product_banner');
        $this->assertSame(1600, $w);
        $this->assertEqualsWithDelta(758, $h, 1);

        // No box chosen → stage-shaped, centred: the sides are cut evenly.
        $this->assertSame(['x' => 104, 'y' => 0, 'width' => 1791, 'height' => 848], Listing::bannerCropFor($default));
    }

    public function test_small_images_are_never_enlarged_and_bad_fit_is_rejected(): void
    {
        $this->step1([
            'images' => [UploadedFile::fake()->image('small.jpg', 800, 600)],
            'image_banner_crops' => [json_encode(['y' => 100, 'height' => 400])],
        ])->assertOk()->assertJsonPath('media.0.banner_fit', 'cover');
        $this->assertSame([800, 400], $this->dimensions(Listing::firstOrFail()->getFirstMedia('gallery'), 'product_banner'));

        $this->step1(['images' => [UploadedFile::fake()->image('x.jpg', 800, 600)], 'image_fits' => ['stretch']])
            ->assertStatus(422)->assertJsonValidationErrors('image_fits.0');
    }

    public function test_videos_up_to_2mb_are_stored_with_their_poster(): void
    {
        $this->step1([
            'videos' => [$this->fakeMp4('Product Demo.mp4')],
            'video_posters' => [UploadedFile::fake()->image('poster.jpg', 640, 360)],
        ])->assertOk()->assertJsonCount(1, 'videos');

        $listing = Listing::firstOrFail();
        $video = $listing->getFirstMedia('videos');
        $poster = $listing->getFirstMedia('video_posters');
        $this->assertStringEndsWith('_product_demo_video.mp4', $video->file_name);
        $this->assertStringEndsWith('_product_demo_video_poster.jpg', $poster->file_name);
        $this->assertSame($poster->id, $video->getCustomProperty('poster_id'));

        // Over 2 MB, or not a video → rejected.
        $this->step1(['listing_id' => $listing->id, 'videos' => [UploadedFile::fake()->create('big.mp4', 3000, 'video/mp4')]])
            ->assertStatus(422)->assertJsonValidationErrors('videos.0');
        $this->step1(['listing_id' => $listing->id, 'videos' => [UploadedFile::fake()->create('clip.avi', 500, 'video/x-msvideo')]])
            ->assertStatus(422)->assertJsonValidationErrors('videos.0');

        // Deleting the video removes its poster too.
        $this->actingAs($this->supplier)->deleteJson(route('supplier.catalog.listings.media.destroy', [$listing, $video]))->assertOk();
        $this->assertSame(0, $listing->fresh()->getMedia('videos')->count());
        $this->assertSame(0, $listing->fresh()->getMedia('video_posters')->count());
    }

    public function test_video_size_and_types_follow_system_settings(): void
    {
        // Admin: only WebM allowed → an MP4 is rejected.
        \App\Models\Setting::set('product_media', 'video_extensions', ['webm']);
        $this->step1(['videos' => [$this->fakeMp4('a.mp4')]])
            ->assertStatus(422)->assertJsonValidationErrors('videos.0');

        // Admin: MP4 back but max 1 KB → the ~4 KB clip is too large.
        \App\Models\Setting::set('product_media', 'video_extensions', ['mp4']);
        \App\Models\Setting::set('product_media', 'video_max_kb', 1);
        $this->step1(['videos' => [$this->fakeMp4('a.mp4')]])
            ->assertStatus(422)->assertJsonValidationErrors('videos.0');

        \App\Models\Setting::set('product_media', 'video_max_kb', 100);
        $this->step1(['videos' => [$this->fakeMp4('a.mp4')]])->assertOk()->assertJsonCount(1, 'videos');
    }

    public function test_image_and_video_count_limits(): void
    {
        // Admin System Settings: 2 photos, 1 video per product.
        \App\Models\Setting::set('product_media', 'max_images', 2);
        \App\Models\Setting::set('product_media', 'max_videos', 1);

        $this->step1(['images' => array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg", 400, 300), range(1, 3))])
            ->assertStatus(422)->assertJsonValidationErrors('images');
        $this->step1(['videos' => [$this->fakeMp4('a.mp4'), $this->fakeMp4('b.mp4')]])
            ->assertStatus(422)->assertJsonValidationErrors('videos');
    }

    public function test_refresh_command_regenerates_missing_sizes_and_prunes_missing_files(): void
    {
        $this->step1(['images' => [UploadedFile::fake()->image('a.jpg', 1600, 1200), UploadedFile::fake()->image('b.jpg', 1600, 1200)]])->assertOk();
        [$kept, $broken] = Listing::firstOrFail()->getMedia('gallery')->all();

        // An older image without its sizes, and one whose original file vanished.
        @unlink($kept->getPath('card'));
        $kept->generated_conversions = [];
        $kept->save();
        @unlink($broken->getPath());

        Artisan::call('product-media:refresh', ['--prune-missing' => true]);

        $this->assertTrue($kept->fresh()->hasGeneratedConversion('card'));
        $this->assertFileExists($kept->fresh()->getPath('card'));
        $this->assertNull(Media::find($broken->id));
    }
}
