<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Table: categories — self-referencing tree. Supplier-created categories stay
 * pending until Admin approves them.
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'type',
        'approval_status',
        'rejection_reason',
        'created_by_account_id',
        'created_by_user_id',
        'reviewed_by_user_id',
        'reviewed_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    /* ── Tree ───────────────────────────────────────────────────────────── */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /* ── Actors ─────────────────────────────────────────────────────────── */

    public function createdByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /* ── Catalogue ──────────────────────────────────────────────────────── */

    /**
     * Named productAttributes rather than attributes: a relation called
     * "attributes" shadows Eloquent's internal $attributes bag.
     */
    public function productAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute', 'category_id', 'attribute_id')
            ->withPivot(['is_required', 'is_filterable', 'is_variant', 'sort_order'])
            ->withTimestamps();
    }

    public function attributes(): BelongsToMany
    {
        return $this->productAttributes();
    }

    public function categoryAttributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class, 'category_id');
    }

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'listing_categories', 'category_id', 'listing_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function listingCategories(): HasMany
    {
        return $this->hasMany(ListingCategory::class, 'category_id');
    }

    /**
     * Listings that nominate this category as their main category.
     */
    public function mainCategoryListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'main_category_id');
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'category_id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(CategorySuggestion::class, 'parent_category_id');
    }

    /* ── Scopes ─────────────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }
}
