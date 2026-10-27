<?php

namespace App\Http\Controllers\Backend\Admin\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Server half of the AJAX live-filtered table (design.md §17.1), shared by
 * the admin Users & Accounts index pages. Paired with <x-backend.live-filter>
 * on the view side: one index() action renders the full page on a normal
 * request and returns just the table partial plus fresh option counts as
 * JSON when the filter bar fetches it.
 */
trait LiveFilters
{
    /**
     * Selected values for a multi-select filter, limited to known options.
     * Accepts both ?status[]=a&status[]=b (the filter bar) and the scalar
     * ?status=a form older links (e.g. the admin dashboard) still use.
     */
    protected function selectedFilter(Request $request, string $key, array $options): array
    {
        $allowed = array_map('strval', array_keys($options));

        return array_values(array_intersect(array_map('strval', (array) $request->input($key, [])), $allowed));
    }

    /** Row count per value of a plain column, e.g. ['active' => 12, 'suspended' => 3]. */
    protected function countBy(Builder $query, string $column): array
    {
        return (clone $query)->reorder()
            ->selectRaw("{$column} as bucket, count(*) as aggregate")
            ->groupBy($column)
            ->pluck('aggregate', 'bucket')
            ->all();
    }

    /** Row count per option where the option isn't a plain column (relations, derived values). */
    protected function countEach(Builder $query, array $options, Closure $constrain): array
    {
        $counts = [];

        foreach (array_keys($options) as $key) {
            $counts[$key] = $constrain(clone $query, (string) $key)->count();
        }

        return $counts;
    }

    protected function liveFilterResponse(Request $request, string $view, string $partial, array $data, array $counts)
    {
        if ($request->ajax()) {
            // Vary keeps the browser's back/forward cache from serving this
            // JSON fragment in place of the full page at the same URL.
            return response()->json([
                'table_html' => view($partial, $data)->render(),
                'counts' => $counts,
            ])->header('Vary', 'X-Requested-With');
        }

        return view($view, $data + ['counts' => $counts]);
    }
}
