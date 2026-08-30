<?php

namespace App\Support\Approvals;

use App\Models\AttributeCustomValueReview;
use App\Models\ListingAttributeValue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Two views onto supplier-submitted custom ("Other") attribute values:
 *
 * - query(): still-pending ones, grouped live off listing_attribute_values
 *   (attribute + trimmed text). Self-cleaning — a value drops out on its own
 *   once it's promoted (its source rows get backfilled to
 *   attribute_value_id) or ignored (moves to decidedQuery() instead).
 * - decidedQuery(): the admin's decision history, read from
 *   attribute_custom_value_reviews directly — this is the only place
 *   "approved" rows are still visible, since their source listing rows no
 *   longer carry a custom_value to group by.
 */
class CustomAttributeValueQueue
{
    /** Columns the "pending" table may be sorted by, per design.md §15. */
    public const PENDING_SORTS = ['attribute_name', 'custom_value', 'usage_count'];

    /** Columns the "decided" table may be sorted by (usage_count is computed after the query, so it isn't sortable in SQL). */
    public const DECIDED_SORTS = ['attribute_name', 'custom_value', 'status'];

    public static function query(string $search = '', string $sort = '', string $direction = 'desc'): Builder
    {
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        $query = DB::table('listing_attribute_values as lav')
            ->join('attributes as a', 'a.id', '=', 'lav.attribute_id')
            ->leftJoin('attribute_custom_value_reviews as r', function ($join) {
                $join->on('r.attribute_id', '=', 'lav.attribute_id')
                    ->on('r.custom_value', '=', DB::raw('TRIM(lav.custom_value)'));
            })
            ->whereNotNull('lav.custom_value')
            ->where('lav.custom_value', '!=', '')
            ->where(function (Builder $q) {
                $q->whereNull('r.status')->orWhere('r.status', 'pending');
            })
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $q2) use ($search) {
                    $q2->where('a.name', 'like', "%{$search}%")
                        ->orWhere('lav.custom_value', 'like', "%{$search}%");
                });
            })
            ->select([
                'lav.attribute_id',
                'a.name as attribute_name',
                DB::raw('TRIM(lav.custom_value) as custom_value'),
                DB::raw('COUNT(*) as usage_count'),
            ])
            ->groupBy('lav.attribute_id', 'a.name', DB::raw('TRIM(lav.custom_value)'));

        return match ($sort) {
            'attribute_name' => $query->orderBy('a.name', $direction),
            'custom_value'   => $query->orderBy(DB::raw('TRIM(lav.custom_value)'), $direction),
            'usage_count'    => $query->orderBy('usage_count', $direction),
            default          => $query->orderByDesc('usage_count'),
        };
    }

    public static function pendingCount(): int
    {
        return static::query()->get()->count();
    }

    public static function decidedQuery(string $search = '', string $sort = '', string $direction = 'asc', string $status = ''): EloquentBuilder
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $statuses = in_array($status, ['ignored', 'approved'], true) ? [$status] : ['ignored', 'approved'];

        $query = AttributeCustomValueReview::query()
            ->whereIn('status', $statuses)
            ->with(['attribute', 'resultingAttributeValue'])
            ->when($search !== '', function (EloquentBuilder $q) use ($search) {
                $q->where(function (EloquentBuilder $q2) use ($search) {
                    $q2->where('custom_value', 'like', "%{$search}%")
                        ->orWhereHas('attribute', fn ($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            });

        return match ($sort) {
            'attribute_name' => $query->join('attributes', 'attributes.id', '=', 'attribute_custom_value_reviews.attribute_id')
                ->select('attribute_custom_value_reviews.*')
                ->orderBy('attributes.name', $direction),
            'custom_value' => $query->orderBy('custom_value', $direction),
            'status'       => $query->orderBy('status', $direction),
            default        => $query->orderByRaw("FIELD(status, 'ignored', 'approved')")->orderByDesc('reviewed_at'),
        };
    }

    /**
     * Attach a live usage_count to each row of a decided-queue page. Ignored
     * rows still carry custom_value (untouched), so count is a live match
     * against it — it can keep growing. Approved rows had custom_value
     * cleared on promotion, so count instead matches the resulting official
     * attribute_value_id.
     */
    public static function withUsageCounts(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->getCollection()->transform(function (AttributeCustomValueReview $review) {
            $review->usage_count = $review->status === 'approved'
                ? ListingAttributeValue::where('attribute_id', $review->attribute_id)
                    ->where('attribute_value_id', $review->resulting_attribute_value_id)
                    ->count()
                : ListingAttributeValue::where('attribute_id', $review->attribute_id)
                    ->whereRaw('TRIM(custom_value) = ?', [$review->custom_value])
                    ->count();

            return $review;
        });

        return $paginator;
    }
}
