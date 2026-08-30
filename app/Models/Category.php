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

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Grouped, form-ready attribute definitions for this category (falling
     * back up the parent chain when this category has none directly
     * assigned) — the shared shape both the supplier listing wizard and the
     * buyer RFQ item form build their dynamic spec forms from:
     * {category_id, category_name, groups: [{group_id, group_name,
     * sort_order, attributes: [...]}], total_count}.
     */
    public function attributesGroupedForForm(): array
    {
        $assignedAttributes = $this->attributes()
            ->with([
                'attributeGroup',
                'unit',
                'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('value'),
            ])
            ->get();

        if ($assignedAttributes->isEmpty() && $this->parent_id) {
            $curr = $this;
            while ($assignedAttributes->isEmpty() && $curr->parent_id) {
                $curr = $curr->parent;
                if ($curr) {
                    $assignedAttributes = $curr->attributes()
                        ->with([
                            'attributeGroup',
                            'unit',
                            'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('value'),
                        ])
                        ->get();
                }
            }
        }

        $groupedAttributes = $assignedAttributes
            ->groupBy(fn ($attr) => $attr->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;
                $sortedItems = $items->sortBy([
                    ['pivot.sort_order', 'asc'],
                    ['sort_order', 'asc'],
                    ['name', 'asc'],
                ])->values()->map(function ($attr) {
                    return [
                        'id'                 => $attr->id,
                        'name'               => $attr->name,
                        'slug'               => $attr->slug,
                        'input_type'         => $attr->input_type,
                        'unit_id'            => $attr->unit_id,
                        'unit_symbol'        => $attr->unit?->symbol,
                        'unit_name'          => $attr->unit?->name,
                        'placeholder'        => $attr->placeholder,
                        'is_required'        => (bool) ($attr->is_required || !empty($attr->pivot?->is_required)),
                        'is_filterable'      => (bool) ($attr->is_filterable || !empty($attr->pivot?->is_filterable)),
                        'is_variant'         => (bool) ($attr->is_variant || !empty($attr->pivot?->is_variant)),
                        'sort_order'         => (int) ($attr->pivot->sort_order ?? $attr->sort_order),
                        'allow_custom_value' => (bool) $attr->allow_custom_value,
                        'values'             => $attr->values->map(fn ($v) => [
                            'id'        => $v->id,
                            'value'     => $v->value,
                            'slug'      => $v->slug,
                            'color_hex' => $v->color_hex,
                        ])->values()->toArray(),
                    ];
                });

                return [
                    'group_id'   => $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $sortedItems,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        return [
            'category_id'   => $this->id,
            'category_name' => $this->name,
            'groups'        => $groupedAttributes,
            'total_count'   => $assignedAttributes->count(),
        ];
    }

    /**
     * All descendant category ids (not including this category itself),
     * walked level by level rather than via a recursive CTE to stay
     * DB-agnostic. Used for "match this category or anything under it"
     * queries (e.g. open_matching RFQ supplier eligibility).
     */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];

        while (! empty($frontier)) {
            $children = static::whereIn('parent_id', $frontier)->pluck('id')->all();
            if (empty($children)) {
                break;
            }
            $ids = array_merge($ids, $children);
            $frontier = $children;
        }

        return $ids;
    }

    /**
     * Flat, indented tree for supplier-facing category pickers — active,
     * admin-approved categories only. Defaults to product-eligible
     * categories (the listing wizard's original use case); pass a broader
     * $types list (e.g. including 'service') for pickers that aren't
     * product-listing-specific, like the "categories you supply" list.
     */
    public static function getTreeSelectOptions(array $types = ['product', 'both']): array
    {
        $all = static::withCount('attributes')
            ->where('is_active', true)
            ->where('approval_status', 'approved')
            ->whereIn('type', $types)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = $all->groupBy('parent_id');
        $options = [];

        $buildTree = function ($parentId, $depth = 0, $path = '') use (&$buildTree, $grouped, &$options) {
            if (!isset($grouped[$parentId])) {
                return;
            }

            foreach ($grouped[$parentId] as $cat) {
                $currentPath = $path ? "{$path} › {$cat->name}" : $cat->name;
                $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';

                $options[] = [
                    'id'               => $cat->id,
                    'name'             => $cat->name,
                    'path'             => $currentPath,
                    'depth'            => $depth,
                    'indent_name'      => $prefix . $cat->name,
                    'attributes_count' => (int) ($cat->attributes_count ?? 0),
                    'has_children'     => isset($grouped[$cat->id]) && $grouped[$cat->id]->isNotEmpty(),
                ];

                $buildTree($cat->id, $depth + 1, $currentPath);
            }
        };

        $buildTree(null, 0, '');

        return $options;
    }

    /**
     * Get full breadcrumb path string e.g. "Electronics › Audio › Portable Wireless Speaker".
     */
    public function getBreadcrumbPath(): string
    {
        $parts = [$this->name];
        $curr = $this;

        while ($curr->parent_id && $curr->parent) {
            $curr = $curr->parent;
            array_unshift($parts, $curr->name);
        }

        return implode(' › ', $parts);
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
