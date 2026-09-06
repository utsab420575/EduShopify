<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'created_by_user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'featured',
        'status',
        'approved_by_user_id',
        'approved_at',
        'published_at',
        'rejection_reason',
        'views_count',
        'likes_count',
        'comments_count',
        'reading_time_minutes',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'featured'             => 'boolean',
            'approved_at'          => 'datetime',
            'published_at'         => 'datetime',
            'views_count'          => 'integer',
            'likes_count'          => 'integer',
            'comments_count'       => 'integer',
            'reading_time_minutes' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlogPostImage::class, 'blog_post_id')->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'blog_post_id', 'blog_tag_id')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id');
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id')
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with(['approvedReplies' => fn ($q) => $q->with(['account.supplierProfile', 'account.buyerProfile', 'authorUser'])])
            ->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(BlogPostLike::class, 'blog_post_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * cover_image is stored in one of three shapes depending on how the
     * post was authored: a full external URL, a root-relative public
     * asset path (the demo seeder's '/images/herosection.png'), or a
     * public-disk-relative upload path from BlogPostController
     * ('blogs/accounts/<d_m_Y>/<file>'). asset($path) alone only happens
     * to work for the first two — it drops the required '/storage/'
     * prefix for real uploads, so their images never resolved. This is
     * the one place that must get it right; every view should call this
     * instead of touching cover_image directly.
     */
    public function coverImageUrl(): string
    {
        return self::resolveStoredImageUrl($this->cover_image) ?? asset('images/herosection.png');
    }

    /**
     * Shared by BlogPost::coverImageUrl() and BlogPostImage::url() (and any
     * other blog-adjacent image field) — the one place that knows how to
     * turn a stored path into a real URL regardless of which of the three
     * shapes it's in.
     */
    public static function resolveStoredImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}
