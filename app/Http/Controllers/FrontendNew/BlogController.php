<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogCommentLike;
use App\Models\BlogPost;
use App\Models\BlogPostLike;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search       = trim($request->input('search', ''));
        $categorySlug = $request->input('category', 'all');
        $tagSlug      = $request->input('tag');

        // Fetch categories with published post counts
        $categories = BlogCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(function (BlogCategory $category) {
                $category->post_count = BlogPost::published()
                    ->where('category_id', $category->id)
                    ->count();

                return $category;
            });

        $totalArticles = BlogPost::published()->count();

        // Build articles query
        $query = BlogPost::published()
            ->with([
                'category',
                'account.supplierProfile',
                'account.buyerProfile',
                'tags',
                'authorUser',
            ])
            ->orderByDesc('featured')
            ->latest('published_at');

        // Filter by category
        if ($categorySlug && $categorySlug !== 'all') {
            $query->whereHas('category', fn (Builder $c) => $c->where('slug', $categorySlug));
        }

        // Filter by tag
        if ($tagSlug) {
            $query->whereHas('tags', fn (Builder $t) => $t->where('slug', $tagSlug));
        }

        // Live text search
        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('meta_keywords', 'like', "%{$search}%")
                  ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('tags', fn (Builder $t) => $t->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('account', fn (Builder $a) => $a->where('display_name', 'like', "%{$search}%"));
            });
        }

        $articles = $query->paginate(9)->withQueryString();

        // AJAX response for instantaneous live filtering & search
        if ($request->expectsJson() || $request->ajax()) {
            $activeCategoryName = 'All Articles';
            if ($categorySlug && $categorySlug !== 'all') {
                $matched = $categories->firstWhere('slug', $categorySlug);
                $activeCategoryName = $matched ? $matched->name : 'Category';
            }

            return response()->json([
                'total'                => $articles->total(),
                'active_category'      => $categorySlug,
                'active_category_name' => $activeCategoryName,
                'search'               => $search,
                'articles'             => $articles->map(function (BlogPost $post) {
                    $authorName = $post->account?->display_name
                        ?? $post->account?->supplierProfile?->display_name
                        ?? $post->account?->buyerProfile?->display_name
                        ?? 'EduShopify Member';

                    $cover = $post->coverImageUrl();

                    return [
                        'id'             => $post->id,
                        'title'          => $post->title,
                        'slug'           => $post->slug,
                        'excerpt'        => $post->excerpt,
                        'cover_image'    => $cover,
                        'category'       => $post->category?->name ?? 'General',
                        'category_slug'  => $post->category?->slug ?? 'all',
                        'author'         => $authorName,
                        'date'           => $post->published_at?->format('M d, Y') ?? '',
                        'reading_time'   => $post->reading_time_minutes ?? 5,
                        'featured'       => (bool) $post->featured,
                        'likes_count'    => $post->likes_count,
                        'comments_count' => $post->comments_count,
                        'views_count'    => $post->views_count,
                        'url'            => route('v2.blogs.show', $post->slug),
                    ];
                }),
            ]);
        }

        return view('frontend_new.blogs.index', [
            'categories'     => $categories,
            'articles'       => $articles,
            'totalArticles'  => $totalArticles,
            'activeCategory' => $categorySlug,
            'search'         => $search,
            'activeTag'      => $tagSlug,
        ]);
    }

    public function show(BlogPost $blogPost)
    {
        abort_unless($blogPost->status === 'approved' && $blogPost->published_at && $blogPost->published_at->lte(now()), 404);

        // Increment views count silently
        BlogPost::whereKey($blogPost->id)->increment('views_count');

        $blogPost->load([
            'category',
            'account.supplierProfile',
            'account.buyerProfile',
            'tags',
            'images',
            'approvedComments.authorUser',
            'approvedComments.account.supplierProfile',
            'approvedComments.account.buyerProfile',
            'approvedComments.approvedReplies.authorUser',
            'approvedComments.approvedReplies.account.supplierProfile',
            'approvedComments.approvedReplies.account.buyerProfile',
        ]);

        // Related articles
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $blogPost->id)
            ->where(function (Builder $q) use ($blogPost) {
                if ($blogPost->category_id) {
                    $q->where('category_id', $blogPost->category_id);
                }
            })
            ->with(['category', 'account'])
            ->orderByDesc('featured')
            ->latest('published_at')
            ->limit(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $filler = BlogPost::published()
                ->where('id', '!=', $blogPost->id)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->with(['category', 'account'])
                ->latest('published_at')
                ->limit(3 - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->concat($filler);
        }

        $allCategories = BlogCategory::active()->withCount(['posts' => fn ($q) => $q->published()])->get();
        $popularTags = BlogTag::whereHas('posts', fn ($q) => $q->published())->withCount('posts')->orderByDesc('posts_count')->limit(12)->get();

        $currentAccount = $this->getCurrentAccount(request());
        $hasLikedPost = $currentAccount
            ? $blogPost->likes()->where('account_id', $currentAccount->id)->exists()
            : false;

        $likedCommentIds = $currentAccount
            ? BlogCommentLike::where('account_id', $currentAccount->id)
                ->whereIn('blog_comment_id', $blogPost->comments()->pluck('id'))
                ->pluck('blog_comment_id')
                ->all()
            : [];

        return view('frontend_new.blogs.show', [
            'post'            => $blogPost,
            'relatedPosts'    => $relatedPosts,
            'allCategories'   => $allCategories,
            'popularTags'     => $popularTags,
            'currentAccount'  => $currentAccount,
            'hasLikedPost'    => $hasLikedPost,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }

    public function togglePostLike(Request $request, BlogPost $blogPost)
    {
        $user = $request->user();
        if (! $user) {
            // A leftover frontend_intent from some earlier, abandoned action
            // (e.g. clicking "Post an RFQ" on a previous visit) takes
            // priority over url.intended in the login handler — clear it so
            // this current, more recent intent (return to this article)
            // actually wins instead of silently losing to stale state.
            session()->forget('frontend_intent');
            session(['url.intended' => route('v2.blogs.show', $blogPost->slug)]);

            return response()->json([
                'success'  => false,
                'message'  => 'Please sign in to like this article.',
                'redirect' => route('login', ['redirect' => route('v2.blogs.show', $blogPost->slug)]),
            ], 401);
        }

        $account = $this->getCurrentAccount($request);
        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found for your profile.',
            ], 403);
        }

        $existingLike = BlogPostLike::where('blog_post_id', $blogPost->id)
            ->where('account_id', $account->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            BlogPostLike::create([
                'blog_post_id' => $blogPost->id,
                'account_id'   => $account->id,
            ]);
            $liked = true;
        }

        $likesCount = $blogPost->likes()->count();
        $blogPost->update(['likes_count' => $likesCount]);

        return response()->json([
            'success'     => true,
            'liked'       => $liked,
            'likes_count' => $likesCount,
            'message'     => $liked ? 'Article liked!' : 'Like removed.',
        ]);
    }

    public function storeComment(Request $request, BlogPost $blogPost)
    {
        $user = $request->user();
        if (! $user) {
            // A leftover frontend_intent from some earlier, abandoned action
            // (e.g. clicking "Post an RFQ" on a previous visit) takes
            // priority over url.intended in the login handler — clear it so
            // this current, more recent intent (return to this article)
            // actually wins instead of silently losing to stale state.
            session()->forget('frontend_intent');
            session(['url.intended' => route('v2.blogs.show', $blogPost->slug)]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Please sign in to comment.',
                    'redirect' => route('login', ['redirect' => route('v2.blogs.show', $blogPost->slug)]),
                ], 401);
            }
            return redirect()->route('login', ['redirect' => route('v2.blogs.show', $blogPost->slug)]);
        }

        $account = $this->getCurrentAccount($request);
        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found for your profile.',
            ], 403);
        }

        $validated = $request->validate([
            'content'   => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => ['nullable', 'exists:blog_comments,id'],
        ]);

        $comment = BlogComment::create([
            'blog_post_id'       => $blogPost->id,
            'account_id'         => $account->id,
            'created_by_user_id' => $user->id,
            'parent_id'          => $validated['parent_id'] ?? null,
            'content'            => $validated['content'],
            'status'             => 'approved',
            'likes_count'        => 0,
        ]);

        $commentsCount = $blogPost->comments()->where('status', 'approved')->count();
        $blogPost->update(['comments_count' => $commentsCount]);

        $authorName = $account->display_name
            ?? $account->supplierProfile?->display_name
            ?? $account->buyerProfile?->display_name
            ?? $user->name;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Comment posted successfully!',
                'comment'        => [
                    'id'             => $comment->id,
                    'content'        => $comment->content,
                    'parent_id'      => $comment->parent_id,
                    'author'         => $authorName,
                    'author_initial' => strtoupper(substr($authorName, 0, 1)),
                    'date'           => 'Just now',
                    'likes_count'    => 0,
                ],
                'total_comments' => $commentsCount,
            ]);
        }

        return back()->with('success', 'Your comment has been posted!');
    }

    public function toggleCommentLike(Request $request, BlogComment $comment)
    {
        $user = $request->user();
        if (! $user) {
            session()->forget('frontend_intent');
            session(['url.intended' => route('v2.blogs.show', $comment->post->slug)]);

            return response()->json([
                'success'  => false,
                'message'  => 'Please sign in to like this comment.',
                'redirect' => route('login', ['redirect' => route('v2.blogs.show', $comment->post->slug)]),
            ], 401);
        }

        $account = $this->getCurrentAccount($request);
        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found for your profile.',
            ], 403);
        }

        $existingLike = BlogCommentLike::where('blog_comment_id', $comment->id)
            ->where('account_id', $account->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            BlogCommentLike::create([
                'blog_comment_id' => $comment->id,
                'account_id'      => $account->id,
            ]);
            $liked = true;
        }

        $likesCount = $comment->likes()->count();
        $comment->update(['likes_count' => $likesCount]);

        return response()->json([
            'success'     => true,
            'liked'       => $liked,
            'likes_count' => $likesCount,
        ]);
    }

    private function getCurrentAccount(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        return $user->activateTeamContext()
            ?? $user->accountMember?->account
            ?? $user->account
            ?? \App\Models\Account::where('is_system_account', true)->first()
            ?? \App\Models\Account::first();
    }
}
