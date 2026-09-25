<?php

namespace App\Http\Controllers\Backend\Supplier\Blog;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Blog\SupplierBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    use InteractsWithSupplierAccount;

    private const STORAGE_FOLDER = 'blogs/accounts';

    public function index(Request $request)
    {
        $account = $this->currentAccount();
        $status  = $request->string('status')->toString();
        $search  = $request->string('search')->toString();

        $posts = BlogPost::query()
            ->where('account_id', $account->id)
            ->when($search !== '', fn ($q) => $q->where('title', 'like', '%'.$search.'%'))
            ->when(in_array($status, ['draft', 'pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->with(['category', 'authorUser', 'approvedBy'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.supplier.blog.posts.index', [
            'posts'  => $posts,
            'status' => $status,
            'search' => $search,
            'counts' => [
                'all'      => BlogPost::where('account_id', $account->id)->count(),
                'draft'    => BlogPost::where('account_id', $account->id)->where('status', 'draft')->count(),
                'pending'  => BlogPost::where('account_id', $account->id)->where('status', 'pending')->count(),
                'approved' => BlogPost::where('account_id', $account->id)->where('status', 'approved')->count(),
                'rejected' => BlogPost::where('account_id', $account->id)->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('backend.supplier.blog.posts.create', [
            'post'       => new BlogPost(),
            'categories' => BlogCategory::active()->orderBy('sort_order')->get(),
            'tagNames'   => '',
        ]);
    }

    public function store(SupplierBlogPostRequest $request)
    {
        $account = $this->currentAccount();
        $user    = $this->currentUser();
        $data    = $request->validated();

        $post = new BlogPost([
            'account_id'           => $account->id,
            'created_by_user_id'   => $user->id,
            'category_id'          => $data['category_id'] ?? null,
            'title'                => $data['title'],
            'slug'                 => $this->uniqueSlug($data['title']),
            'excerpt'              => $data['excerpt'] ?? null,
            'content'              => $data['content'],
            'featured'             => false,
            'status'               => $data['status'], // draft or pending
            'reading_time_minutes' => $this->estimateReadingMinutes($data['content']),
            'meta_title'           => $data['meta_title'] ?? null,
            'meta_description'     => $data['meta_description'] ?? null,
            'meta_keywords'        => $data['meta_keywords'] ?? null,
        ]);

        if ($request->hasFile('cover_image')) {
            $post->cover_image = $this->storeImage($request->file('cover_image'));
        }

        $post->save();

        $this->syncTags($post, $data['tags'] ?? '');

        $message = $data['status'] === 'pending'
            ? 'Blog post submitted for administrator approval. It will appear on the frontend once approved.'
            : 'Blog post saved as draft.';

        return redirect()->route('supplier.blog.posts.index')->with('success', $message);
    }

    public function edit(BlogPost $post)
    {
        $account = $this->currentAccount();
        abort_unless($post->account_id === $account->id, 403, 'Unauthorized access to this blog post.');

        return view('backend.supplier.blog.posts.edit', [
            'post'       => $post,
            'categories' => BlogCategory::active()->orderBy('sort_order')->get(),
            'tagNames'   => $post->tags()->pluck('name')->implode(', '),
        ]);
    }

    public function update(SupplierBlogPostRequest $request, BlogPost $post)
    {
        $account = $this->currentAccount();
        abort_unless($post->account_id === $account->id, 403, 'Unauthorized access to this blog post.');

        $data = $request->validated();

        $post->fill([
            'category_id'          => $data['category_id'] ?? null,
            'title'                => $data['title'],
            'excerpt'              => $data['excerpt'] ?? null,
            'content'              => $data['content'],
            'status'               => $data['status'],
            'reading_time_minutes' => $this->estimateReadingMinutes($data['content']),
            'meta_title'           => $data['meta_title'] ?? null,
            'meta_description'     => $data['meta_description'] ?? null,
            'meta_keywords'        => $data['meta_keywords'] ?? null,
        ]);

        // When submitted for review, reset previous rejection reason and approvals
        if ($data['status'] === 'pending') {
            $post->rejection_reason   = null;
            $post->approved_by_user_id = null;
            $post->approved_at        = null;
            $post->published_at       = null;
        } elseif ($data['status'] === 'draft') {
            $post->approved_by_user_id = null;
            $post->approved_at        = null;
            $post->published_at       = null;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteImage($post->cover_image);
            $post->cover_image = $this->storeImage($request->file('cover_image'));
        }

        $post->save();

        $this->syncTags($post, $data['tags'] ?? '');

        $message = $data['status'] === 'pending'
            ? 'Blog post submitted for administrator approval.'
            : 'Blog post saved as draft.';

        return redirect()->route('supplier.blog.posts.index')->with('success', $message);
    }

    public function destroy(BlogPost $post)
    {
        $account = $this->currentAccount();
        abort_unless($post->account_id === $account->id, 403, 'Unauthorized access to this blog post.');

        $this->deleteImage($post->cover_image);
        $post->delete();

        return back()->with('success', 'Blog post deleted.');
    }

    public function uploadContentImage(Request $request)
    {
        $request->validate(['file' => 'required|image|max:10240']);

        return response()->json([
            'url' => Storage::disk('public')->url($this->storeImage($request->file('file'))),
        ]);
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

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (BlogPost::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
