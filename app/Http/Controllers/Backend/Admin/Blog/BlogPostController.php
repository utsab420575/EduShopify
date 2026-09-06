<?php

namespace App\Http\Controllers\Backend\Admin\Blog;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Blog\BlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    use InteractsWithAdmin;

    /**
     * Cover images and Summernote-inserted content images both land here,
     * partitioned by upload date so the folder never accumulates too many
     * files in one place — same convention as verification documents and
     * certification proof files elsewhere in the app.
     */
    private const STORAGE_FOLDER = 'blogs/accounts';

    public function index(Request $request)
    {
        $this->authorize('platform.blog.manage');

        $posts = BlogPost::query()
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['category', 'authorUser'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.blog.posts.index', [
            'posts' => $posts,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'counts' => [
                'all' => BlogPost::count(),
                'draft' => BlogPost::where('status', 'draft')->count(),
                'pending' => BlogPost::where('status', 'pending')->count(),
                'approved' => BlogPost::where('status', 'approved')->count(),
                'rejected' => BlogPost::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('platform.blog.manage');

        return view('backend.admin.blog.posts.create', [
            'post' => new BlogPost(),
            'categories' => BlogCategory::active()->orderBy('sort_order')->get(),
            'tagNames' => '',
        ]);
    }

    public function store(BlogPostRequest $request)
    {
        $this->authorize('platform.blog.manage');

        $data = $request->validated();
        $admin = $this->admin();

        $post = new BlogPost([
            'account_id' => $admin->account->id,
            'created_by_user_id' => $admin->id,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'featured' => $request->boolean('featured'),
            'reading_time_minutes' => $this->estimateReadingMinutes($data['content']),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ]);

        if ($request->hasFile('cover_image')) {
            $post->cover_image = $this->storeImage($request->file('cover_image'));
        }

        $this->applyStatus($post, $data['status'], $admin);

        $post->save();

        $this->syncTags($post, $data['tags'] ?? '');

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $post)
    {
        $this->authorize('platform.blog.manage');

        return view('backend.admin.blog.posts.edit', [
            'post' => $post,
            'categories' => BlogCategory::active()->orderBy('sort_order')->get(),
            'tagNames' => $post->tags()->pluck('name')->implode(', '),
        ]);
    }

    public function update(BlogPostRequest $request, BlogPost $post)
    {
        $this->authorize('platform.blog.manage');

        $data = $request->validated();
        $admin = $this->admin();

        $post->fill([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'featured' => $request->boolean('featured'),
            'reading_time_minutes' => $this->estimateReadingMinutes($data['content']),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ]);

        if ($request->hasFile('cover_image')) {
            $this->deleteImage($post->cover_image);
            $post->cover_image = $this->storeImage($request->file('cover_image'));
        }

        $this->applyStatus($post, $data['status'], $admin);

        $post->save();

        $this->syncTags($post, $data['tags'] ?? '');

        return redirect()->route('admin.blog.posts.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $post)
    {
        $this->authorize('platform.blog.manage');

        $this->deleteImage($post->cover_image);
        $post->delete();

        return back()->with('success', 'Blog post deleted.');
    }

    /**
     * Summernote posts each inserted image here individually (rather than
     * embedding it as base64 in the content HTML) so blog_posts.content
     * stays a reasonable size and images are real files on disk.
     */
    public function uploadContentImage(Request $request)
    {
        $this->authorize('platform.blog.manage');

        $request->validate(['file' => 'required|image|max:4096']);

        return response()->json([
            'url' => Storage::disk('public')->url($this->storeImage($request->file('file'))),
        ]);
    }

    private function applyStatus(BlogPost $post, string $status, $admin): void
    {
        $post->status = $status;

        if ($status === 'approved') {
            $post->approved_by_user_id = $admin->id;
            $post->approved_at ??= now();
            $post->published_at ??= now();
        } else {
            $post->approved_by_user_id = null;
            $post->approved_at = null;
            $post->published_at = null;
        }
    }

    private function syncTags(BlogPost $post, string $tagNames): void
    {
        $names = collect(explode(',', $tagNames))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique();

        $tagIds = $names->map(function (string $name) {
            return BlogTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            )->id;
        });

        $post->tags()->sync($tagIds);
    }

    private function storeImage($file): string
    {
        return $file->store(self::STORAGE_FOLDER.'/'.now()->format('d_m_Y'), 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function estimateReadingMinutes(string $content): int
    {
        $words = str_word_count(strip_tags($content));

        return max(1, (int) ceil($words / 200));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
