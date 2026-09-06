<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BlogAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makePlatformAdmin(): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);

        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name' => 'Blog Test Admin',
            'email' => 'admin_blog_test_'.uniqid().'@example.com',
            'phone' => '+10000000009',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        AccountMember::create([
            'account_id' => $systemAccount->id,
            'user_id' => $admin->id,
            'member_type' => 'owner',
            'is_primary_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $admin->unsetRelation('roles')->unsetRelation('permissions');

        return $admin->fresh();
    }

    public function test_admin_can_create_edit_and_delete_a_blog_post(): void
    {
        Storage::fake('public');
        $admin = $this->makePlatformAdmin();
        $category = BlogCategory::create(['name' => 'Test Category', 'slug' => 'test-category']);

        $this->actingAs($admin)->get(route('admin.blog.posts.index'))->assertOk()->assertSee('Blog Posts');
        $this->actingAs($admin)->get(route('admin.blog.posts.create'))->assertOk()->assertSee('summernote-lite', false);

        $this->actingAs($admin)->post(route('admin.blog.posts.store'), [
            'title' => 'My First Post',
            'category_id' => $category->id,
            'content' => '<p>Hello world</p>',
            'status' => 'approved',
            'featured' => '1',
            'tags' => 'Alpha, Beta',
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionDoesntHaveErrors()->assertRedirect(route('admin.blog.posts.index'));

        $post = BlogPost::where('title', 'My First Post')->firstOrFail();
        $this->assertSame('approved', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->cover_image);
        $this->assertSame(['Alpha', 'Beta'], $post->tags()->pluck('name')->sort()->values()->all());
        $this->assertSame($admin->account->id, $post->account_id);

        $this->actingAs($admin)->put(route('admin.blog.posts.update', $post), [
            'title' => 'My First Post Updated',
            'content' => '<p>Updated content</p>',
            'status' => 'draft',
            'tags' => 'Alpha',
        ])->assertSessionDoesntHaveErrors();

        $post->refresh();
        $this->assertSame('My First Post Updated', $post->title);
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);
        $this->assertSame(['Alpha'], $post->tags()->pluck('name')->all());

        $this->actingAs($admin)->delete(route('admin.blog.posts.destroy', $post));
        $this->assertNull(BlogPost::find($post->id));
    }

    public function test_content_image_upload_stores_under_blogs_accounts_dated_folder(): void
    {
        Storage::fake('public');
        $admin = $this->makePlatformAdmin();

        $response = $this->actingAs($admin)->post(route('admin.blog.posts.upload-image'), [
            'file' => UploadedFile::fake()->image('inline.png'),
        ])->assertOk()->assertJsonStructure(['url']);

        $url = $response->json('url');
        $expectedPrefix = '/storage/blogs/accounts/'.now()->format('d_m_Y').'/';
        $this->assertStringContainsString($expectedPrefix, $url);

        $path = 'blogs/accounts/'.now()->format('d_m_Y');
        $this->assertNotEmpty(Storage::disk('public')->files($path));
    }

    public function test_quick_add_category_endpoint(): void
    {
        $admin = $this->makePlatformAdmin();

        $this->actingAs($admin)->postJson(route('admin.blog.categories.quick-add'), [
            'name' => 'Quick Added Category',
        ])->assertOk()->assertJsonStructure(['id', 'name']);

        $this->assertDatabaseHas('blog_categories', ['name' => 'Quick Added Category']);

        // Duplicate names are rejected.
        $this->actingAs($admin)->postJson(route('admin.blog.categories.quick-add'), [
            'name' => 'Quick Added Category',
        ])->assertJsonValidationErrors(['name']);
    }

    public function test_approval_queue_approve_reject_and_undo(): void
    {
        $admin = $this->makePlatformAdmin();
        $author = Account::create([
            'account_type' => 'individual',
            'display_name' => 'Pending Post Author',
            'slug' => 'pending-post-author-'.uniqid(),
            'status' => 'active',
        ]);

        $post = BlogPost::create([
            'account_id' => $author->id,
            'title' => 'Pending Submission',
            'slug' => 'pending-submission',
            'content' => '<p>Body</p>',
            'status' => 'pending',
            'reading_time_minutes' => 1,
        ]);

        $this->actingAs($admin)->get(route('admin.blog.approval.index'))->assertOk()->assertSee('Pending Submission');

        $this->actingAs($admin)->post(route('admin.blog.approval.approve', $post))
            ->assertRedirect();
        $post->refresh();
        $this->assertSame('approved', $post->status);
        $this->assertNotNull($post->approved_at);

        // Approving twice is refused — only a pending post can be approved.
        $this->actingAs($admin)->post(route('admin.blog.approval.approve', $post))->assertStatus(422);

        $this->actingAs($admin)->post(route('admin.blog.approval.undo', $post))->assertRedirect();
        $this->assertSame('pending', $post->fresh()->status);

        $this->actingAs($admin)->post(route('admin.blog.approval.reject', $post), [
            'reason' => 'Not aligned with editorial guidelines.',
        ])->assertRedirect();
        $post->refresh();
        $this->assertSame('rejected', $post->status);
        $this->assertSame('Not aligned with editorial guidelines.', $post->rejection_reason);

        $this->actingAs($admin)->post(route('admin.blog.approval.undo', $post))->assertRedirect();
        $this->assertSame('pending', $post->fresh()->status);
    }
}
